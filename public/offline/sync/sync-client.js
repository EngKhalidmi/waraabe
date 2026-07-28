(function (global) {
    'use strict';

    var OfflineDatabase = global.StoreManagementOfflineDatabase;
    var QueueManager = global.StoreManagementQueueManager;
    var SyncManager = global.SyncManager || global.StoreManagementSyncManager;
    var RepositoryFactory = global.StoreManagementOfflineRepositories;
    var config = global.__OFFLINE_ENGINE_CONFIG__ || {};
    var syncConfig = global.SyncConfig || global.StoreManagementSyncConfig || {};

    if (!OfflineDatabase || !QueueManager || !SyncManager || !RepositoryFactory) {
        throw new Error('Offline sync prerequisites must be loaded before sync-client.js.');
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

    function getSyncEndpoint() {
        return global.location.origin.replace(/\/$/, '') + '/public/api/sync';
    }

    function resolveEndpoint() {
        var configuredEndpoint = String(config.syncEndpoint || '').trim();

        if (configuredEndpoint) {
            try {
                var parsed = new URL(configuredEndpoint, global.location.href);
                if (parsed.origin === global.location.origin && parsed.pathname.indexOf('/public/') === -1) {
                    return getSyncEndpoint();
                }

                return parsed.toString();
            } catch (error) {
                // Fall through to deployment base URL.
            }
        }

        return getSyncEndpoint();
    }

    function emit(detail) {
        global.dispatchEvent(new CustomEvent('sync-client:changed', {
            detail: detail || {}
        }));
    }

    function chunkArray(items, size) {
        var chunks = [];
        var index = 0;

        while (index < items.length) {
            chunks.push(items.slice(index, index + size));
            index += size;
        }

        return chunks;
    }

    function getRepository(tableName) {
        if (RepositoryFactory && typeof RepositoryFactory.createRepository === 'function') {
            return RepositoryFactory.createRepository(tableName);
        }

        throw new Error('Repository factory is not available.');
    }

    function SyncClient(options) {
        options = options || {};

        this.queueManager = options.queueManager || QueueManager;
        this.syncManager = options.syncManager || SyncManager;
        this.endpoint = options.endpoint || resolveEndpoint();
        this.batchSize = Number(options.batchSize || syncConfig.batch_size || 20);
        this.userId = typeof options.userId !== 'undefined' ? options.userId : config.userId;
        this.state = {
            online: navigator.onLine,
            syncing: false,
            phase: navigator.onLine ? 'idle' : 'offline',
            total: 0,
            current: 0,
            uploaded: 0,
            failed: 0,
            message: '',
            lastError: null,
            startedAt: null,
            finishedAt: null
        };
        this._started = false;
        this._syncPromise = null;
    }

    SyncClient.prototype.isOnline = function () {
        return navigator.onLine;
    };

    SyncClient.prototype.getStatus = function () {
        return clone(this.state);
    };

    SyncClient.prototype._setState = function (patch) {
        this.state = Object.assign({}, this.state, patch);
        emit(this.getStatus());
    };

    SyncClient.prototype._refreshSyncPlan = function () {
        return this.syncManager.refresh().then(function () {
            return SyncManager.getStatus();
        });
    };

    SyncClient.prototype._flattenPlan = function (plan) {
        var records = [];

        (plan || []).forEach(function (group) {
            (group.requests || []).forEach(function (request) {
                records.push(Object.assign({}, request, {
                    table: group.table_name || request.table_name || request.table || null,
                    operation: request.operation || 'create'
                }));
            });
        });

        return records;
    };

    SyncClient.prototype._preparePayload = function (request) {
        return {
            table: request.table || request.table_name,
            operation: String(request.operation || 'create').toLowerCase(),
            local_id: request.local_id || request.record_local_id || request.recordLocalId || request.uuid,
            server_id: request.server_id || null,
            request_uuid: request.request_uuid || request.uuid || null,
            request_key: request.request_key || null,
            data: clone(request.payload || request.formData || request.data || {}),
            timestamp: request.timestamp || request.created_at || nowIso(),
            retry_count: Number(request.retry_count || 0)
        };
    };

    SyncClient.prototype._sendBatch = function (records) {
        var payload = {
            user_id: this.userId,
            records: records
        };

        return fetch(this.endpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        }).then(function (response) {
            if (!response.ok) {
                return response.json().catch(function () {
                    return {
                        success: false,
                        message: 'Sync request failed with status ' + response.status + '.'
                    };
                }).then(function (errorBody) {
                    throw new Error(errorBody.message || ('Sync request failed with status ' + response.status + '.'));
                });
            }

            return response.json();
        });
    };

    SyncClient.prototype._applyResult = function (request, result) {
        var tableName = String(result.table || request.table || request.table_name || '').trim();
        var localId = result.local_id || request.local_id || request.record_local_id || request.request_uuid || request.uuid;
        var requestUuid = result.request_uuid || request.request_uuid || request.uuid || request.request_key || request.local_id || request.record_local_id || null;
        var operation = String(result.operation || request.operation || 'create').toLowerCase();
        var status = String(result.status || 'failed').toLowerCase();
        var message = result.message || result.error || null;
        var serverId = result.server_id || null;
        var repository = getRepository(tableName);

        if (!repository) {
            return Promise.resolve();
        }

        if (status === 'synced') {
            return repository.applySyncResult(localId, {
                server_id: serverId,
                id: serverId,
                synced: true,
                sync_status: 'synced',
                local_action: operation,
                queue_state: 'synced',
                sync_error: null,
                last_error: null,
                updated_at: nowIso(),
                deleted: operation === 'delete',
                is_deleted: operation === 'delete'
            }).then(function () {
                return QueueManager.markSynced(requestUuid).then(function () {
                    return QueueManager.removeRequest(requestUuid);
                });
            });
        }

        return repository.applySyncFailure(localId, message || 'Sync failed.').then(function () {
            return QueueManager.markFailed(requestUuid, message || 'Sync failed.');
        });
    };

    SyncClient.prototype.syncNow = function () {
        var self = this;

        if (self._syncPromise) {
            return self._syncPromise;
        }

        if (!self.isOnline()) {
            self._setState({
                syncing: false,
                phase: 'offline',
                message: 'Offline'
            });
            return Promise.resolve({
                success: false,
                message: 'Offline',
                total: 0,
                processed: 0,
                failed: 0
            });
        }

        self._syncPromise = self._refreshSyncPlan().then(function (status) {
            var pendingRecords = self._flattenPlan(status.plan || []);

            if (!pendingRecords.length) {
                self._setState({
                    online: true,
                    syncing: false,
                    phase: 'completed',
                    total: 0,
                    current: 0,
                    uploaded: 0,
                    failed: 0,
                    message: 'Completed',
                    finishedAt: nowIso()
                });

                return status;
            }

            var batches = chunkArray(pendingRecords, self.batchSize);
            var total = pendingRecords.length;
            var current = 0;
            var failedCount = 0;
            var processedCount = 0;

            self._setState({
                online: true,
                syncing: true,
                phase: 'uploading',
                total: total,
                current: 0,
                uploaded: 0,
                failed: 0,
                message: 'Uploading 0/' + total,
                startedAt: nowIso(),
                finishedAt: null,
                lastError: null
            });

            var chain = Promise.resolve();

            batches.forEach(function (batch) {
                chain = chain.then(function () {
                    self._setState({
                        online: true,
                        syncing: true,
                        phase: 'uploading',
                        current: current,
                        total: total,
                        message: 'Uploading ' + current + '/' + total
                    });

                    var preparedBatch = batch.map(function (request) {
                        return self._preparePayload(request);
                    });

                    return self._sendBatch(preparedBatch).then(function (response) {
                        var results = Array.isArray(response && response.results) ? response.results : [];

                        if (!response || response.success === false) {
                            throw new Error((response && response.message) || 'Sync failed.');
                        }

                        return results.reduce(function (promise, result, index) {
                            return promise.then(function () {
                                var request = batch[index] || batch.find(function (item) {
                                    return String(item.request_uuid || item.uuid || '') === String(result.request_uuid || result.requestUuid || '');
                                }) || batch[index];

                                if (!request) {
                                    return null;
                                }

                                if (String(result.status || '').toLowerCase() === 'synced') {
                                    processedCount += 1;
                                } else {
                                    failedCount += 1;
                                }

                                return self._applyResult(request, result);
                            });
                        }, Promise.resolve()).then(function () {
                            current += batch.length;
                            self._setState({
                                online: true,
                                syncing: true,
                                phase: 'uploading',
                                current: current,
                                total: total,
                                uploaded: processedCount,
                                failed: failedCount,
                                message: 'Uploading ' + current + '/' + total
                            });

                            return response;
                        });
                    });
                });
            });

            return chain.then(function () {
                var phase = failedCount > 0 ? 'failed' : 'completed';
                var message = failedCount > 0 ? ('Failed ' + failedCount) : 'Completed';

                self._setState({
                    online: true,
                    syncing: false,
                    phase: phase,
                    current: total,
                    total: total,
                    uploaded: processedCount,
                    failed: failedCount,
                    message: message,
                    finishedAt: nowIso(),
                    lastError: failedCount > 0 ? message : null
                });

                return self.syncManager.refresh().then(function () {
                    return {
                        success: failedCount === 0,
                        processed: processedCount,
                        failed: failedCount,
                        total: total
                    };
                });
            }).catch(function (error) {
                self._setState({
                    online: self.isOnline(),
                    syncing: false,
                    phase: 'failed',
                    failed: total,
                    message: error.message || 'Sync failed.',
                    lastError: error.message || 'Sync failed.',
                    finishedAt: nowIso()
                });

                return self.queueManager.getPendingRequests().then(function (pendingRequests) {
                    var failureChain = Promise.resolve();

                    pendingRequests.forEach(function (request) {
                        failureChain = failureChain.then(function () {
                            var requestUuid = request.request_uuid || request.uuid || request.requestKey;
                            var tableName = request.table_name || request.tableName || request.store || request.formName;
                            var repository = getRepository(tableName);

                            if (repository && typeof repository.applySyncFailure === 'function') {
                                return repository.applySyncFailure(request.local_id || request.record_local_id || requestUuid, error.message || 'Sync failed.');
                            }

                            return null;
                        }).then(function () {
                            var requestUuid = request.request_uuid || request.uuid || request.requestKey;
                            return self.queueManager.markFailed(requestUuid, error.message || 'Sync failed.');
                        });
                    });

                    return failureChain.then(function () {
                        return {
                            success: false,
                            message: error.message || 'Sync failed.',
                            total: total,
                            processed: processedCount,
                            failed: Math.max(failedCount, total)
                        };
                    });
                });
            });
        }).finally(function () {
            self._syncPromise = null;
        });

        return self._syncPromise;
    };

    SyncClient.prototype.start = function () {
        var self = this;

        if (self._started) {
            return self.syncManager.refresh().then(function () {
                return self.getStatus();
            });
        }

        self._started = true;

        global.addEventListener('online', function () {
            self._setState({
                online: true,
                phase: self.state.syncing ? 'uploading' : 'idle'
            });

            self.syncManager.refresh().then(function () {
                if (self.syncManager.getStatus().readyToSync) {
                    self.syncNow().catch(function () {});
                }
            });
        });

        global.addEventListener('offline', function () {
            self._setState({
                online: false,
                phase: 'offline',
                message: 'Offline'
            });
        });

        global.addEventListener('sync-manager:changed', function () {
            self.syncManager.refresh().catch(function () {});
        });

        return self.syncManager.refresh().then(function () {
            return self.getStatus();
        });
    };

    SyncClient.prototype.stop = function () {
        this._started = false;
    };

    global.SyncClientClass = SyncClient;
    global.StoreManagementSyncClient = new SyncClient();
    global.SyncClient = global.StoreManagementSyncClient;

    function boot() {
        global.SyncClient.start();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
