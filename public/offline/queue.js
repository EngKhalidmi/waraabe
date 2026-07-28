(function (global) {
    'use strict';

    var OfflineDatabase = global.StoreManagementOfflineDatabase;

    if (!OfflineDatabase) {
        throw new Error('StoreManagementOfflineDatabase must be loaded before queue.js.');
    }

    function ensureDatabase(database) {
        if (database && typeof database.then === 'function') {
            return database;
        }

        if (database && typeof database.transaction === 'function') {
            return Promise.resolve(database);
        }

        return OfflineDatabase.getDatabase();
    }

    function emitQueueChange(detail) {
        global.dispatchEvent(new CustomEvent('offline-queue:changed', {
            detail: detail || {}
        }));
    }

    function normalizeQueueRecord(record) {
        return OfflineDatabase.normalizeRecord(record || {});
    }

    function buildSyncQueueRecord(request) {
        return {
            uuid: request.uuid,
            request_uuid: request.request_uuid || request.uuid,
            request_key: request.request_key,
            table_name: request.table_name || request.tableName || request.formName || null,
            record_local_id: request.record_local_id || request.recordLocalId || null,
            operation: request.operation || request.action || 'create',
            status: request.sync_status,
            sync_status: request.sync_status,
            sync_error: request.sync_error || request.last_error || null,
            last_error: request.sync_error || request.last_error || null,
            user_id: request.user_id,
            retry_count: request.retry_count,
            next_retry_at: request.next_retry_at,
            payload: request.payload || request.formData || request.data || null,
            created_at: request.created_at,
            updated_at: OfflineDatabase.nowIso()
        };
    }

    function QueueManager(options) {
        options = options || {};
        this.database = ensureDatabase(options.database || OfflineDatabase.getDatabase());
    }

    QueueManager.prototype._withDatabase = function (callback) {
        var self = this;

        return self.database.then(function (database) {
            return callback(database);
        });
    };

    QueueManager.prototype._findRequest = function (database, requestKeyOrUuid) {
        return database.pending_requests
            .where('request_key')
            .equals(requestKeyOrUuid)
            .first()
            .then(function (record) {
                if (record) {
                    return record;
                }

                return database.pending_requests
                    .where('uuid')
                    .equals(requestKeyOrUuid)
                    .first();
            });
    };

    QueueManager.prototype.addRequest = function (request) {
        var normalizedRequest = OfflineDatabase.normalizeRecord(request);
        var self = this;

        return self._withDatabase(function (database) {
            return database.pending_requests
                .where('request_key')
                .equals(normalizedRequest.request_key)
                .first()
                .then(function (existingRecord) {
                    if (existingRecord && existingRecord.sync_status !== 'synced') {
                        normalizedRequest.id = existingRecord.id;
                        normalizedRequest.uuid = existingRecord.uuid;
                        normalizedRequest.request_uuid = existingRecord.request_uuid || existingRecord.uuid || normalizedRequest.request_uuid;
                        normalizedRequest.request_key = existingRecord.request_key;
                        normalizedRequest.retry_count = existingRecord.retry_count;
                        normalizedRequest.created_at = existingRecord.created_at;
                        normalizedRequest.queue_state = existingRecord.queue_state || normalizedRequest.queue_state;
                        normalizedRequest.sync_error = existingRecord.sync_error || existingRecord.last_error || normalizedRequest.sync_error || null;
                        normalizedRequest.last_error = normalizedRequest.sync_error;
                    }

                    return database.transaction('rw', database.pending_requests, database.sync_queue, function () {
                        return database.pending_requests.put(normalizedRequest).then(function () {
                            return database.sync_queue.put(buildSyncQueueRecord(normalizedRequest));
                        });
                    }).then(function () {
                        emitQueueChange({
                            type: 'added',
                            request: normalizedRequest
                        });

                        return {
                            record: normalizedRequest,
                            duplicate: Boolean(existingRecord)
                        };
                    });
                });
        });
    };

    QueueManager.prototype.removeRequest = function (requestKeyOrUuid) {
        var self = this;

        return self._withDatabase(function (database) {
            return self._findRequest(database, requestKeyOrUuid).then(function (record) {
                if (!record) {
                    return null;
                }

                return database.transaction('rw', database.pending_requests, database.sync_queue, function () {
                    return Promise.all([
                        database.pending_requests.delete(record.id),
                        database.sync_queue.where('request_uuid').equals(record.uuid).delete()
                    ]);
                }).then(function () {
                    emitQueueChange({
                        type: 'removed',
                        request: record
                    });

                    return record;
                });
            });
        });
    };

    QueueManager.prototype.retryRequest = function (requestKeyOrUuid) {
        var self = this;

        return self._withDatabase(function (database) {
            return self._findRequest(database, requestKeyOrUuid).then(function (record) {
                if (!record) {
                    return null;
                }

                var updatedRecord = Object.assign({}, record, {
                    retry_count: (record.retry_count || 0) + 1,
                    sync_status: 'pending',
                    queue_state: 'queued',
                    sync_error: null,
                    last_error: null,
                    updated_at: OfflineDatabase.nowIso(),
                    next_retry_at: null
                });

                return database.transaction('rw', database.pending_requests, database.sync_queue, function () {
                    return database.pending_requests.put(updatedRecord).then(function () {
                        return database.sync_queue.put(buildSyncQueueRecord(updatedRecord));
                    });
                }).then(function () {
                    emitQueueChange({
                        type: 'retried',
                        request: updatedRecord
                    });

                    return updatedRecord;
                });
            });
        });
    };

    QueueManager.prototype.markSynced = function (requestKeyOrUuid) {
        var self = this;

        return self._withDatabase(function (database) {
            return self._findRequest(database, requestKeyOrUuid).then(function (record) {
                if (!record) {
                    return null;
                }

                var syncedRecord = Object.assign({}, record, {
                    sync_status: 'synced',
                    queue_state: 'synced',
                    updated_at: OfflineDatabase.nowIso(),
                    sync_error: null,
                    last_error: null
                });

                return database.transaction('rw', database.pending_requests, database.sync_queue, function () {
                    return database.pending_requests.put(syncedRecord).then(function () {
                        return database.sync_queue.put(buildSyncQueueRecord(syncedRecord));
                    });
                }).then(function () {
                    emitQueueChange({
                        type: 'synced',
                        request: syncedRecord
                    });

                    return syncedRecord;
                });
            });
        });
    };

    QueueManager.prototype.markFailed = function (requestKeyOrUuid, errorMessage) {
        var self = this;

        return self._withDatabase(function (database) {
            return self._findRequest(database, requestKeyOrUuid).then(function (record) {
                if (!record) {
                    return null;
                }

                var failedRecord = Object.assign({}, record, {
                    retry_count: (record.retry_count || 0) + 1,
                    sync_status: 'failed',
                    queue_state: 'failed',
                    updated_at: OfflineDatabase.nowIso(),
                    sync_error: errorMessage || 'Offline queue item failed to sync.',
                    last_error: errorMessage || 'Offline queue item failed to sync.'
                });

                return database.transaction('rw', database.pending_requests, database.sync_queue, function () {
                    return database.pending_requests.put(failedRecord).then(function () {
                        return database.sync_queue.put(buildSyncQueueRecord(failedRecord));
                    });
                }).then(function () {
                    emitQueueChange({
                        type: 'failed',
                        request: failedRecord
                    });

                    return failedRecord;
                });
            });
        });
    };

    QueueManager.prototype.getPendingRequests = function () {
        return this._withDatabase(function (database) {
            return database.pending_requests
                .where('sync_status')
                .anyOf(['pending', 'queued', 'failed', 'syncing'])
                .sortBy('created_at')
                .then(function (records) {
                    return (records || []).map(function (record) {
                        return normalizeQueueRecord(record);
                    });
                });
        });
    };

    QueueManager.prototype.countPendingRequests = function () {
        return this._withDatabase(function (database) {
            return database.pending_requests
                .where('sync_status')
                .anyOf(['pending', 'queued', 'failed', 'syncing'])
                .count();
        });
    };

    QueueManager.prototype.removeSyncedRequests = function () {
        var self = this;

        return self._withDatabase(function (database) {
            return database.pending_requests
                .where('sync_status')
                .equals('synced')
                .toArray()
                .then(function (syncedRequests) {
                    if (!syncedRequests.length) {
                        return 0;
                    }

                    return database.transaction('rw', database.pending_requests, database.sync_queue, function () {
                        return Promise.all([
                            database.pending_requests.where('sync_status').equals('synced').delete(),
                            database.sync_queue.where('status').equals('synced').delete()
                        ]);
                    }).then(function () {
                        emitQueueChange({
                            type: 'pruned',
                            count: syncedRequests.length
                        });

                        return syncedRequests.length;
                    });
                });
        });
    };

    QueueManager.prototype.clearAll = function () {
        return this._withDatabase(function (database) {
            return database.transaction('rw', database.pending_requests, database.sync_queue, database.settings, function () {
                return Promise.all([
                    database.pending_requests.clear(),
                    database.sync_queue.clear(),
                    database.settings.clear()
                ]);
            }).then(function () {
                emitQueueChange({
                    type: 'cleared'
                });
            });
        });
    };

    global.QueueManager = QueueManager;
    global.StoreManagementQueueManager = new QueueManager();
})(window);
