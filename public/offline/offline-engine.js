(function (global) {
    'use strict';

    var OfflineDatabase = global.StoreManagementOfflineDatabase;
    var queueManager = global.StoreManagementQueueManager;
    var config = global.__OFFLINE_ENGINE_CONFIG__ || {};

    if (!OfflineDatabase || !queueManager) {
        throw new Error('Offline database and queue manager must be loaded before offline-engine.js.');
    }

    function createEventDetail(engine) {
        return {
            online: engine.isOnline(),
            syncing: engine.state.syncing,
            pendingCount: engine.state.pendingCount,
            status: engine.state.status
        };
    }

    function OfflineEngine(options) {
        options = options || {};
        this.database = options.database || OfflineDatabase.getDatabase();
        this.queueManager = options.queueManager || queueManager;
        this.widgetId = options.widgetId || config.widgetId || 'store-offline-sync-widget';
        this.state = {
            online: navigator.onLine,
            syncing: false,
            pendingCount: 0,
            status: navigator.onLine ? 'online' : 'offline',
            syncProgress: null
        };
        this._started = false;
        this._poller = null;
    }

    OfflineEngine.prototype.isOnline = function () {
        return navigator.onLine;
    };

    OfflineEngine.prototype.setSyncing = function (isSyncing) {
        this.state.syncing = Boolean(isSyncing);
        this.state.status = this.state.syncing ? 'syncing' : (this.isOnline() ? (this.state.pendingCount ? 'ready' : 'synced') : 'offline');
        this.renderWidget();
        this.dispatchStateChange();
    };

    OfflineEngine.prototype.dispatchStateChange = function () {
        global.dispatchEvent(new CustomEvent('offline-engine:changed', {
            detail: createEventDetail(this)
        }));
    };

    OfflineEngine.prototype.ensureWidget = function () {
        var existing = document.getElementById(this.widgetId);
        if (existing) {
            return existing;
        }

        var widget = document.createElement('aside');
        widget.id = this.widgetId;
        widget.setAttribute('role', 'status');
        widget.setAttribute('aria-live', 'polite');
        widget.innerHTML = [
            '<div class="offline-engine__dot" aria-hidden="true"></div>',
            '<div class="offline-engine__body">',
            '  <div class="offline-engine__title" data-offline-title>Offline engine loading</div>',
            '  <div class="offline-engine__meta">',
            '    <span data-offline-state>Starting...</span>',
            '    <span data-offline-pending>Pending (0)</span>',
            '    <span data-offline-progress>Ready to sync</span>',
            '  </div>',
            '</div>',
            '<button type="button" class="offline-engine__button" data-sync-now>Sync Now</button>'
        ].join('');

        document.body.appendChild(widget);
        return widget;
    };

    OfflineEngine.prototype.injectStyles = function () {
        if (document.getElementById('offline-engine-widget-style')) {
            return;
        }

        var style = document.createElement('style');
        style.id = 'offline-engine-widget-style';
        style.textContent = [
            '#' + this.widgetId + '{position:fixed;left:16px;bottom:16px;z-index:2147483646;display:flex;align-items:center;gap:12px;min-width:220px;max-width:min(100vw - 32px, 360px);padding:12px 14px;border-radius:16px;background:rgba(15,23,42,.94);color:#fff;box-shadow:0 18px 45px rgba(15,23,42,.24);font-family:Arial,Helvetica,sans-serif;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px)}',
            '#' + this.widgetId + '[data-state=\"offline\"]{background:rgba(185,28,28,.96)}',
            '#' + this.widgetId + '[data-state=\"online\"]{background:rgba(21,128,61,.96)}',
            '#' + this.widgetId + '[data-state=\"ready\"]{background:rgba(37,99,235,.96)}',
            '#' + this.widgetId + '[data-state=\"syncing\"]{background:rgba(61,94,225,.96)}',
            '#' + this.widgetId + '[data-state=\"synced\"]{background:rgba(15,23,42,.94)}',
            '#' + this.widgetId + '[data-state=\"completed\"]{background:rgba(4,120,87,.96)}',
            '#' + this.widgetId + '[data-state=\"failed\"]{background:rgba(153,27,27,.96)}',
            '#' + this.widgetId + ' .offline-engine__dot{width:12px;height:12px;border-radius:999px;background:#93c5fd;box-shadow:0 0 0 5px rgba(255,255,255,.1);flex:0 0 auto}',
            '#' + this.widgetId + '[data-state=\"offline\"] .offline-engine__dot{background:#fecaca}',
            '#' + this.widgetId + '[data-state=\"online\"] .offline-engine__dot{background:#dcfce7}',
            '#' + this.widgetId + '[data-state=\"ready\"] .offline-engine__dot{background:#dbeafe}',
            '#' + this.widgetId + '[data-state=\"syncing\"] .offline-engine__dot{background:#dbeafe;animation:offlineEnginePulse 1.2s ease-in-out infinite}',
            '#' + this.widgetId + '[data-state=\"synced\"] .offline-engine__dot{background:#93c5fd}',
            '#' + this.widgetId + '[data-state=\"completed\"] .offline-engine__dot{background:#bbf7d0}',
            '#' + this.widgetId + '[data-state=\"failed\"] .offline-engine__dot{background:#fecaca}',
            '#' + this.widgetId + ' .offline-engine__body{display:flex;flex-direction:column;gap:4px;min-width:0;flex:1 1 auto}',
            '#' + this.widgetId + ' .offline-engine__title{font-size:14px;font-weight:700;line-height:1.2}',
            '#' + this.widgetId + ' .offline-engine__meta{display:flex;flex-wrap:wrap;gap:8px;font-size:12px;opacity:.9;line-height:1.3}',
            '#' + this.widgetId + ' .offline-engine__progress{font-size:12px;font-weight:700;line-height:1.3;opacity:.95}',
            '#' + this.widgetId + ' .offline-engine__button{appearance:none;border:0;border-radius:999px;padding:8px 12px;background:#fff;color:#1e293b;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;transition:transform .15s ease,opacity .15s ease,background-color .15s ease}',
            '#' + this.widgetId + ' .offline-engine__button:hover{transform:translateY(-1px)}',
            '#' + this.widgetId + ' .offline-engine__button:disabled{cursor:not-allowed;opacity:.55;transform:none}',
            '@keyframes offlineEnginePulse{0%,100%{transform:scale(1)}50%{transform:scale(1.25)}}',
            '@media (max-width: 640px){#' + this.widgetId + '{left:16px;right:16px;bottom:72px;max-width:none}}'
        ].join('');

        document.head.appendChild(style);
    };

    OfflineEngine.prototype._setWidgetState = function (state, title, pendingText) {
        var widget = this.ensureWidget();
        var titleNode = widget.querySelector('[data-offline-title]');
        var stateNode = widget.querySelector('[data-offline-state]');
        var pendingNode = widget.querySelector('[data-offline-pending]');
        var progressNode = widget.querySelector('[data-offline-progress]');
        var buttonNode = widget.querySelector('[data-sync-now]');
        var progress = this.state.syncProgress || {};

        widget.setAttribute('data-state', state);

        if (titleNode) {
            titleNode.textContent = title;
        }

        if (stateNode) {
            stateNode.textContent = state === 'syncing'
                ? 'Syncing...'
                : (state === 'offline' ? 'Offline' : (state === 'ready' ? 'Ready to sync' : (state === 'synced' ? 'Sync completed' : 'Online')));
            if (state === 'completed') {
                stateNode.textContent = 'Completed';
            }
            if (state === 'failed') {
                stateNode.textContent = 'Failed';
            }
        }

        if (pendingNode) {
            pendingNode.textContent = pendingText;
        }

        if (progressNode) {
            if (progress.phase === 'uploading') {
                progressNode.textContent = 'Uploading ' + (progress.current || 0) + '/' + (progress.total || 0);
            } else if (progress.phase === 'completed') {
                progressNode.textContent = 'Completed';
            } else if (progress.phase === 'failed') {
                progressNode.textContent = 'Failed ' + (progress.failed || 0);
            } else if (state === 'ready') {
                progressNode.textContent = 'Ready to sync';
            } else if (state === 'offline') {
                progressNode.textContent = 'Offline';
            } else {
                progressNode.textContent = 'Online';
            }
        }

        if (buttonNode) {
            buttonNode.disabled = !this.isOnline() || this.state.syncing || this.state.pendingCount === 0;
        }
    };

    OfflineEngine.prototype.renderWidget = function () {
        var status;
        var title;
        var pendingText = 'Pending changes: ' + this.state.pendingCount;
        var progress = this.state.syncProgress || {};

        if (progress.phase === 'failed') {
            status = 'failed';
            title = 'Failed';
        } else if (progress.phase === 'completed') {
            status = 'completed';
            title = 'Completed';
        } else if (this.state.syncing || progress.phase === 'uploading') {
            status = 'syncing';
            title = 'Syncing...';
        } else if (!this.state.online) {
            status = 'offline';
            title = 'Offline';
        } else if (this.state.pendingCount > 0) {
            status = 'ready';
            title = 'Ready to sync';
        } else {
            status = 'synced';
            title = 'Sync completed';
        }

        this._setWidgetState(status, title, pendingText);
    };

    OfflineEngine.prototype.setSyncProgress = function (progress) {
        this.state.syncProgress = progress || null;
        this.renderWidget();
        this.dispatchStateChange();
    };

    OfflineEngine.prototype.refresh = function () {
        var self = this;

        return self.queueManager.countPendingRequests().then(function (count) {
            self.state.online = self.isOnline();
            self.state.pendingCount = count;
            self.state.status = self.state.syncing
                ? 'syncing'
                : (self.state.online ? (count > 0 ? 'ready' : 'synced') : 'offline');
            self.renderWidget();
            self.dispatchStateChange();
            return createEventDetail(self);
        });
    };

    OfflineEngine.prototype.saveRequest = function (request) {
        var self = this;

        return self.queueManager.addRequest(request).then(function (result) {
            return self.refresh().then(function () {
                return result;
            });
        });
    };

    OfflineEngine.prototype.getPendingRequests = function () {
        return this.queueManager.getPendingRequests();
    };

    OfflineEngine.prototype.removeSyncedRequests = function () {
        var self = this;

        return self.queueManager.removeSyncedRequests().then(function (count) {
            return self.refresh().then(function () {
                return count;
            });
        });
    };

    OfflineEngine.prototype.countPendingRequests = function () {
        return this.queueManager.countPendingRequests();
    };

    OfflineEngine.prototype.retryFailedRequests = function () {
        var self = this;

        return self.getPendingRequests().then(function (requests) {
            var failedRequests = requests.filter(function (request) {
                return request.sync_status === 'failed';
            });

            return failedRequests.reduce(function (promise, request) {
                return promise.then(function () {
                    return self.queueManager.retryRequest(request.uuid);
                });
            }, Promise.resolve()).then(function () {
                return self.refresh();
            });
        });
    };

    OfflineEngine.prototype.handleConnectivityChange = function () {
        this.state.online = this.isOnline();
        this.renderWidget();
        this.dispatchStateChange();
    };

    OfflineEngine.prototype.handleSyncClientChange = function (detail) {
        this.state.syncProgress = detail || null;
        if (detail && detail.phase === 'uploading') {
            this.state.syncing = true;
        }
        if (detail && (detail.phase === 'completed' || detail.phase === 'failed')) {
            this.state.syncing = false;
        }
        this.renderWidget();
        this.dispatchStateChange();
    };

    OfflineEngine.prototype.bindSyncButton = function () {
        var self = this;
        var widget = self.ensureWidget();
        var button = widget.querySelector('[data-sync-now]');

        if (!button || button.getAttribute('data-sync-bound') === '1') {
            return;
        }

        button.setAttribute('data-sync-bound', '1');
        button.addEventListener('click', function () {
            if (global.SyncClient && typeof global.SyncClient.syncNow === 'function') {
                global.SyncClient.syncNow();
            } else if (global.StoreManagementSyncClient && typeof global.StoreManagementSyncClient.syncNow === 'function') {
                global.StoreManagementSyncClient.syncNow();
            } else if (global.SyncManager && typeof global.SyncManager.refresh === 'function') {
                global.SyncManager.refresh();
            }
        });
    };

    OfflineEngine.prototype.start = function () {
        var self = this;

        if (self._started) {
            return self.refresh();
        }

        self._started = true;
        self.injectStyles();
        self.ensureWidget();
        self.bindSyncButton();

        global.addEventListener('online', function () {
            self.handleConnectivityChange();
        });

        global.addEventListener('offline', function () {
            self.handleConnectivityChange();
        });

        global.addEventListener('offline-queue:changed', function () {
            self.refresh();
        });

        global.addEventListener('sync-manager:changed', function () {
            self.refresh();
        });

        global.addEventListener('sync-client:changed', function (event) {
            self.handleSyncClientChange(event && event.detail ? event.detail : null);
        });

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                self.refresh();
            }
        });

        self._poller = global.setInterval(function () {
            self.refresh();
        }, 5000);

        return self.refresh();
    };

    OfflineEngine.prototype.stop = function () {
        if (this._poller) {
            global.clearInterval(this._poller);
            this._poller = null;
        }
    };

    global.OfflineEngine = OfflineEngine;
    global.StoreManagementOfflineEngineClass = OfflineEngine;
    global.StoreManagementOfflineEngine = new OfflineEngine();

    function boot() {
        global.StoreManagementOfflineEngine.start();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
