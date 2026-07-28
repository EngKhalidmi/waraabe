(function (global) {
    'use strict';

    var OfflineDatabase = global.StoreManagementOfflineDatabase;
    var QueueManager = global.StoreManagementQueueManager;
    var SyncConfig = global.SyncConfig || global.StoreManagementSyncConfig;

    if (!OfflineDatabase || !QueueManager) {
        throw new Error('Offline database and queue manager must be loaded before sync-manager.js.');
    }

    if (!SyncConfig) {
        throw new Error('SyncConfig must be loaded before sync-manager.js.');
    }

    function clone(value) {
        if (value === null || typeof value === 'undefined') {
            return value;
        }

        return JSON.parse(JSON.stringify(value));
    }

    function nowIso() {
        return OfflineDatabase.nowIso ? OfflineDatabase.nowIso() : new Date().toISOString();
    }

    function normalizeTableName(request) {
        return String(request && (request.table_name || request.tableName || request.store || request.formName) || '').trim().toLowerCase();
    }

    function buildPreparedPayload(request) {
        var payload = request && (request.payload || request.formData || request.data || null);

        return {
            uuid: request.uuid || request.request_uuid || null,
            request_uuid: request.request_uuid || request.uuid || null,
            request_key: request.request_key || null,
            table_name: normalizeTableName(request),
            record_local_id: request.record_local_id || request.recordLocalId || null,
            operation: String(request.operation || request.action || 'create').trim().toLowerCase(),
            method: String(request.method || 'POST').toUpperCase(),
            url: request.url || null,
            user_id: typeof request.user_id !== 'undefined' ? request.user_id : (typeof request.userId !== 'undefined' ? request.userId : null),
            sync_status: request.sync_status || 'pending',
            sync_error: request.sync_error || request.last_error || null,
            retry_count: Number(request.retry_count || request.retryCount || 0),
            timestamp: request.timestamp || request.created_at || request.updated_at || nowIso(),
            payload: clone(payload),
            meta: {
                priority: SyncConfig.getPriority(normalizeTableName(request)),
                dependsOn: SyncConfig.getDependencies(normalizeTableName(request))
            }
        };
    }

    function SyncManager(options) {
        options = options || {};

        this.queueManager = options.queueManager || QueueManager || null;
        this.database = options.database || OfflineDatabase.getDatabase();
        this.state = {
            online: navigator.onLine,
            syncing: false,
            status: navigator.onLine ? 'synced' : 'offline',
            pendingCount: 0,
            readyToSync: false,
            completed: navigator.onLine,
            groups: {},
            tables: [],
            plan: [],
            pendingRequests: [],
            lastUpdated: null
        };
        this._started = false;
        this._refreshPromise = null;
    }

    SyncManager.prototype.isOnline = function () {
        return navigator.onLine;
    };

    SyncManager.prototype._emitChange = function () {
        global.dispatchEvent(new CustomEvent('sync-manager:changed', {
            detail: this.getStatus()
        }));
    };

    SyncManager.prototype._updateState = function (patch) {
        this.state = Object.assign({}, this.state, patch, {
            lastUpdated: nowIso()
        });
    };

    SyncManager.prototype.getStatus = function () {
        return clone(this.state);
    };

    SyncManager.prototype.getPendingRequests = function () {
        var self = this;

        if (!self.queueManager || typeof self.queueManager.getPendingRequests !== 'function') {
            return Promise.resolve([]);
        }

        return self.queueManager.getPendingRequests().then(function (requests) {
            return (requests || []).map(function (request) {
                return clone(request);
            });
        });
    };

    SyncManager.prototype.groupByTable = function (requests) {
        return SyncConfig.groupByTable(requests);
    };

    SyncManager.prototype.prepareSyncPlan = function (requests) {
        var sortedRequests = SyncConfig.sortRequests(requests || []);
        var grouped = this.groupByTable(sortedRequests);
        var plan = grouped.tables.map(function (tableName) {
            var tableRequests = grouped.groups[tableName] || [];

            return {
                table_name: tableName,
                priority: SyncConfig.getPriority(tableName),
                depends_on: SyncConfig.getDependencies(tableName),
                count: tableRequests.length,
                requests: tableRequests.map(function (request) {
                    return buildPreparedPayload(request);
                })
            };
        });

        return {
            requests: sortedRequests.map(function (request) {
                return buildPreparedPayload(request);
            }),
            tables: grouped.tables,
            groups: grouped.groups,
            plan: plan
        };
    };

    SyncManager.prototype.refresh = function () {
        var self = this;

        if (self._refreshPromise) {
            return self._refreshPromise;
        }

        self._refreshPromise = self.getPendingRequests().then(function (requests) {
            var online = self.isOnline();
            var pendingCount = requests.length;
            var prepared = self.prepareSyncPlan(requests);
            var syncing = Boolean(self.state.syncing);
            var readyToSync = online && pendingCount > 0;
            var completed = online && pendingCount === 0;
            var status = syncing ? 'syncing' : (online ? (pendingCount > 0 ? 'ready' : 'synced') : 'offline');

            self._updateState({
                online: online,
                syncing: syncing,
                status: status,
                pendingCount: pendingCount,
                readyToSync: readyToSync,
                completed: completed,
                groups: prepared.groups,
                tables: prepared.tables,
                plan: prepared.plan,
                pendingRequests: prepared.requests
            });

            self._emitChange();
            return self.getStatus();
        }).finally(function () {
            self._refreshPromise = null;
        });

        return self._refreshPromise;
    };

    SyncManager.prototype.setSyncing = function (isSyncing) {
        this._updateState({
            syncing: Boolean(isSyncing),
            status: Boolean(isSyncing) ? 'syncing' : (this.isOnline() ? (this.state.pendingCount > 0 ? 'ready' : 'synced') : 'offline')
        });

        this._emitChange();
        return this.getStatus();
    };

    SyncManager.prototype.getPreparedPayloads = function () {
        return this.refresh().then(function (status) {
            return status.plan;
        });
    };

    SyncManager.prototype.start = function () {
        var self = this;

        if (self._started) {
            return self.refresh();
        }

        self._started = true;

        global.addEventListener('online', function () {
            self.refresh();
        });

        global.addEventListener('offline', function () {
            self.refresh();
        });

        global.addEventListener('offline-queue:changed', function () {
            self.refresh();
        });

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                self.refresh();
            }
        });

        return self.refresh();
    };

    SyncManager.prototype.stop = function () {
        this._started = false;
    };

    global.SyncManagerClass = SyncManager;
    global.StoreManagementSyncManager = new SyncManager();
    global.SyncManager = global.StoreManagementSyncManager;

    function boot() {
        global.SyncManager.start();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
