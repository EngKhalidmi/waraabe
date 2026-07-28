(function (global) {
    'use strict';

    var Database = global.StoreManagementOfflineDatabase;
    var QueueManager = global.StoreManagementQueueManager;

    if (!Database) {
        throw new Error('StoreManagementOfflineDatabase must be loaded before repository.js.');
    }

    var namespace = global.StoreManagementOfflineRepositories = global.StoreManagementOfflineRepositories || {};
    var repositoryRegistry = namespace.repositoryRegistry || {};

    function cloneRecord(record) {
        if (!record) {
            return null;
        }

        return JSON.parse(JSON.stringify(record));
    }

    function normalizeReadRecord(storeName, record) {
        var source = Object.assign({}, record || {});
        var hasServerId = typeof source.server_id !== 'undefined' && source.server_id !== null && source.server_id !== '';

        if (typeof source.local_id === 'undefined' || source.local_id === null || source.local_id === '') {
            source.local_id = hasServerId
                ? source.server_id
                : (typeof source.id !== 'undefined' && source.id !== null ? source.id : Database.generateUuid());
        }

        if (typeof source.server_id === 'undefined') {
            source.server_id = null;
        }

        if (typeof source.synced === 'undefined') {
            source.synced = Boolean(source.server_id);
        }

        source.sync_status = source.sync_status || source.syncStatus || (source.synced ? 'synced' : 'pending');
        source.local_action = source.local_action || source.localAction || (source.server_id ? 'import' : 'create');
        source.sync_error = source.sync_error || source.last_error || source.lastError || null;
        source.last_error = source.sync_error;
        source.queue_state = source.queue_state || source.queueState || (source.sync_status === 'synced' ? 'synced' : 'queued');

        if (typeof source.created_at === 'undefined' || source.created_at === null || source.created_at === '') {
            source.created_at = source.updated_at || Database.nowIso();
        }

        if (typeof source.updated_at === 'undefined' || source.updated_at === null || source.updated_at === '') {
            source.updated_at = source.created_at || Database.nowIso();
        }

        return source;
    }

    function normalizeKeyword(keyword) {
        return String(keyword == null ? '' : keyword).trim().toLowerCase();
    }

    function getUserId(options) {
        if (options && typeof options.userId !== 'undefined') {
            return options.userId;
        }

        if (global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.userId !== 'undefined') {
            return global.__OFFLINE_ENGINE_CONFIG__.userId;
        }

        return null;
    }

    function isDeleted(record) {
        return Boolean(record && (record.is_deleted || record.deleted_at));
    }

    function valueMatchesKeyword(value, keyword) {
        if (value === null || typeof value === 'undefined') {
            return false;
        }

        return String(value).toLowerCase().indexOf(keyword) !== -1;
    }

    function sortRecords(records, orderBy) {
        var sortField = orderBy || 'updated_at';

        return records.sort(function (left, right) {
            var leftValue = left && left[sortField] ? String(left[sortField]) : '';
            var rightValue = right && right[sortField] ? String(right[sortField]) : '';

            if (leftValue === rightValue) {
                var leftId = typeof left.id !== 'undefined' ? Number(left.id) : 0;
                var rightId = typeof right.id !== 'undefined' ? Number(right.id) : 0;
                return rightId - leftId;
            }

            return leftValue < rightValue ? 1 : -1;
        });
    }

    function OfflineRepository(storeName, options) {
        options = options || {};

        this.storeName = String(storeName || '').trim();
        if (!this.storeName) {
            throw new Error('A store name is required for OfflineRepository.');
        }

        this.definition = Database.getBusinessStoreDefinition(this.storeName) || {};
        this.searchFields = Array.isArray(options.searchFields) && options.searchFields.length
            ? options.searchFields
            : (this.definition.searchFields || ['name']);
        this.orderBy = options.orderBy || this.definition.orderBy || 'updated_at';
        this.trackChanges = options.trackChanges !== false;
        this.userId = getUserId(options);
        this.database = options.database || Database.getDatabase();
        this.queueManager = options.queueManager || QueueManager || null;
    }

    OfflineRepository.prototype._table = function (database) {
        var table = database[this.storeName];

        if (!table) {
            throw new Error('The IndexedDB store "' + this.storeName + '" does not exist.');
        }

        return table;
    };

    OfflineRepository.prototype._withTable = function (callback) {
        var self = this;

        return self.database.then(function (database) {
            return callback(database, self._table(database));
        });
    };

    OfflineRepository.prototype._readAll = function () {
        return this._withTable(function (database, table) {
            return table.toArray();
        });
    };

    OfflineRepository.prototype._findMatch = function (records, identifier) {
        var needle = String(identifier == null ? '' : identifier).trim();
        var numericNeedle = needle !== '' && !Number.isNaN(Number(needle)) ? Number(needle) : null;

        return records.find(function (record) {
            if (record && record.id === identifier) {
                return true;
            }

            if (record && String(record.id) === needle) {
                return true;
            }

            if (record && String(record.local_id) === needle) {
                return true;
            }

            if (record && record.server_id !== null && typeof record.server_id !== 'undefined') {
                if (String(record.server_id) === needle) {
                    return true;
                }

                if (numericNeedle !== null && Number(record.server_id) === numericNeedle) {
                    return true;
                }
            }

            return false;
        }) || null;
    };

    OfflineRepository.prototype._normalizeRecord = function (data, overrides) {
        return Database.normalizeBusinessRecord(this.storeName, data, overrides);
    };

    OfflineRepository.prototype._queueMutation = function (operation, record, changes) {
        if (!this.trackChanges || !this.queueManager || typeof this.queueManager.addRequest !== 'function') {
            return Promise.resolve(null);
        }

        return this.queueManager.addRequest(Database.buildLocalChangeRequest(this.storeName, operation, record, changes)).catch(function () {
            return null;
        });
    };

    OfflineRepository.prototype._saveAndQueue = function (operation, record, changes) {
        var self = this;

        return self._withTable(function (database, table) {
            return table.put(record).then(function (id) {
                record.id = typeof record.id !== 'undefined' ? record.id : id;

                return table.get(record.id).then(function (savedRecord) {
                    return self._queueMutation(operation, savedRecord || record, changes).then(function () {
                        return savedRecord || record;
                    });
                });
            });
        });
    };

    OfflineRepository.prototype._prepareCreateRecord = function (data) {
        return this._normalizeRecord(data, {
            synced: false,
            sync_status: 'pending',
            local_action: 'create',
            is_deleted: false,
            deleted_at: null,
            queue_state: 'queued',
            sync_error: null,
            last_error: null
        });
    };

    OfflineRepository.prototype._prepareUpdateRecord = function (existingRecord, data) {
        var merged = Object.assign({}, existingRecord, data || {});

        return this._normalizeRecord(merged, {
            id: existingRecord.id,
            local_id: existingRecord.local_id,
            server_id: typeof merged.server_id !== 'undefined' ? merged.server_id : existingRecord.server_id,
            created_at: existingRecord.created_at,
            synced: false,
            sync_status: 'pending',
            local_action: 'update',
            is_deleted: false,
            deleted_at: null,
            queue_state: 'queued',
            sync_error: null,
            last_error: null,
            updated_at: Database.nowIso()
        });
    };

    OfflineRepository.prototype._prepareDeleteRecord = function (existingRecord) {
        return this._normalizeRecord(existingRecord, {
            id: existingRecord.id,
            local_id: existingRecord.local_id,
            server_id: existingRecord.server_id,
            created_at: existingRecord.created_at,
            updated_at: Database.nowIso(),
            synced: false,
            sync_status: 'pending',
            local_action: 'delete',
            is_deleted: true,
            deleted_at: Database.nowIso(),
            queue_state: 'queued',
            sync_error: null,
            last_error: null
        });
    };

    OfflineRepository.prototype._matchesSearch = function (record, keyword) {
        var normalizedKeyword = normalizeKeyword(keyword);

        if (!normalizedKeyword) {
            return true;
        }

        return this.searchFields.some(function (field) {
            return valueMatchesKeyword(record[field], normalizedKeyword);
        });
    };

    OfflineRepository.prototype.getAll = function () {
        var self = this;

        return self._readAll().then(function (records) {
            return sortRecords(records.filter(function (record) {
                return !isDeleted(record);
            }), self.orderBy).map(function (record) {
                return cloneRecord(normalizeReadRecord(self.storeName, record));
            });
        });
    };

    OfflineRepository.prototype.findById = function (identifier) {
        var self = this;

        if (identifier === null || typeof identifier === 'undefined' || identifier === '') {
            return Promise.resolve(null);
        }

        return self._readAll().then(function (records) {
            var match = self._findMatch(records, identifier);

            if (!match || isDeleted(match)) {
                return null;
            }

            return cloneRecord(normalizeReadRecord(self.storeName, match));
        });
    };

    OfflineRepository.prototype.search = function (keyword) {
        var self = this;

        return self._readAll().then(function (records) {
            var filtered = records.filter(function (record) {
                return !isDeleted(record) && self._matchesSearch(record, keyword);
            });

            return sortRecords(filtered, self.orderBy).map(function (record) {
                return cloneRecord(normalizeReadRecord(self.storeName, record));
            });
        });
    };

    OfflineRepository.prototype.create = function (data) {
        var record = this._prepareCreateRecord(data);

        return this._saveAndQueue('create', record, data);
    };

    OfflineRepository.prototype.update = function (identifier, data) {
        var self = this;

        return self._withTable(function (database, table) {
            return self._readAll().then(function (records) {
                var existingRecord = self._findMatch(records, identifier);

                if (!existingRecord || isDeleted(existingRecord)) {
                    return null;
                }

                var record = self._prepareUpdateRecord(existingRecord, data);

                return table.put(record).then(function () {
                    return table.get(record.id).then(function (savedRecord) {
                        return self._queueMutation('update', savedRecord || record, data).then(function () {
                            return savedRecord || record;
                        });
                    });
                });
            });
        });
    };

    OfflineRepository.prototype.delete = function (identifier) {
        var self = this;

        return self._withTable(function (database, table) {
            return self._readAll().then(function (records) {
                var existingRecord = self._findMatch(records, identifier);

                if (!existingRecord || isDeleted(existingRecord)) {
                    return null;
                }

                var record = self._prepareDeleteRecord(existingRecord);

                return table.put(record).then(function () {
                    return table.get(record.id).then(function (savedRecord) {
                        return self._queueMutation('delete', savedRecord || record, { deleted: true }).then(function () {
                            return savedRecord || record;
                        });
                    });
                });
            });
        });
    };

    OfflineRepository.prototype.count = function () {
        return this.getAll().then(function (records) {
            return records.length;
        });
    };

    OfflineRepository.prototype.cacheMany = function (records) {
        var self = this;
        var items = Array.isArray(records) ? records : [];

        if (!items.length) {
            return Promise.resolve([]);
        }

        return self._withTable(function (database, table) {
            var prepared = items.map(function (record) {
                return self._normalizeRecord(record, {
                    synced: true,
                    sync_status: 'synced',
                    local_action: record.local_action || 'import',
                    queue_state: 'synced',
                    is_deleted: Boolean(record.is_deleted),
                    deleted_at: record.deleted_at || null,
                    sync_error: record.sync_error || record.last_error || null,
                    last_error: record.sync_error || record.last_error || null,
                    updated_at: record.updated_at || Database.nowIso()
                });
            });

            return table.bulkPut(prepared).then(function () {
                return prepared;
            });
        });
    };

    OfflineRepository.prototype.applySyncResult = function (identifier, serverRecord) {
        var self = this;
        var payload = Object.assign({}, serverRecord || {});

        return self._withTable(function (database, table) {
            return self._readAll().then(function (records) {
                var existingRecord = self._findMatch(records, identifier);

                if (!existingRecord) {
                    return null;
                }

                var merged = Object.assign({}, existingRecord, payload);
                var serverId = typeof payload.server_id !== 'undefined'
                    ? payload.server_id
                    : (typeof payload.id !== 'undefined' ? payload.id : (typeof merged.server_id !== 'undefined' ? merged.server_id : null));
                var updatedAt = payload.updated_at || Database.nowIso();

                merged.id = existingRecord.id;
                merged.local_id = existingRecord.local_id || merged.local_id || Database.generateUuid();
                merged.server_id = serverId;
                merged.synced = true;
                merged.sync_status = 'synced';
                merged.local_action = merged.local_action || existingRecord.local_action || 'update';
                merged.queue_state = 'synced';
                merged.sync_error = null;
                merged.last_error = null;
                merged.updated_at = updatedAt;
                merged.created_at = merged.created_at || existingRecord.created_at || updatedAt;

                if (payload.deleted === true || payload.is_deleted === true) {
                    merged.is_deleted = true;
                    merged.deleted_at = merged.deleted_at || updatedAt;
                }

                var normalized = self._normalizeRecord(merged, {
                    id: existingRecord.id,
                    local_id: merged.local_id,
                    server_id: merged.server_id,
                    synced: true,
                    sync_status: 'synced',
                    local_action: merged.local_action,
                    queue_state: 'synced',
                    sync_error: null,
                    last_error: null,
                    updated_at: updatedAt,
                    created_at: merged.created_at,
                    is_deleted: merged.is_deleted,
                    deleted_at: merged.deleted_at || null
                });

                return table.put(normalized).then(function () {
                    return table.get(normalized.id).then(function (savedRecord) {
                        return cloneRecord(normalizeReadRecord(self.storeName, savedRecord || normalized));
                    });
                });
            });
        });
    };

    OfflineRepository.prototype.applySyncFailure = function (identifier, errorMessage) {
        var self = this;
        var message = String(errorMessage || 'Sync failed.').trim() || 'Sync failed.';

        return self._withTable(function (database, table) {
            return self._readAll().then(function (records) {
                var existingRecord = self._findMatch(records, identifier);

                if (!existingRecord) {
                    return null;
                }

                var updatedAt = Database.nowIso();
                var merged = Object.assign({}, existingRecord, {
                    id: existingRecord.id,
                    local_id: existingRecord.local_id || Database.generateUuid(),
                    server_id: typeof existingRecord.server_id !== 'undefined' ? existingRecord.server_id : null,
                    synced: false,
                    sync_status: 'failed',
                    local_action: existingRecord.local_action || 'update',
                    queue_state: 'failed',
                    sync_error: message,
                    last_error: message,
                    updated_at: updatedAt,
                    created_at: existingRecord.created_at || updatedAt
                });

                if (existingRecord.is_deleted) {
                    merged.is_deleted = true;
                    merged.deleted_at = existingRecord.deleted_at || updatedAt;
                }

                var normalized = self._normalizeRecord(merged, {
                    id: existingRecord.id,
                    local_id: merged.local_id,
                    server_id: merged.server_id,
                    synced: false,
                    sync_status: 'failed',
                    local_action: merged.local_action,
                    queue_state: 'failed',
                    sync_error: message,
                    last_error: message,
                    updated_at: updatedAt,
                    created_at: merged.created_at,
                    is_deleted: merged.is_deleted,
                    deleted_at: merged.deleted_at || null
                });

                return table.put(normalized).then(function () {
                    return table.get(normalized.id).then(function (savedRecord) {
                        return cloneRecord(normalizeReadRecord(self.storeName, savedRecord || normalized));
                    });
                });
            });
        });
    };

    OfflineRepository.prototype.clear = function () {
        return this._withTable(function (database, table) {
            return table.clear();
        });
    };

    function registerRepository(name, RepositoryClass) {
        repositoryRegistry[String(name || '').trim()] = RepositoryClass;
        namespace.repositoryRegistry = repositoryRegistry;
        return RepositoryClass;
    }

    function getRepository(name) {
        return repositoryRegistry[String(name || '').trim()] || null;
    }

    function createRepository(name, options) {
        var RepositoryClass = getRepository(name);

        if (!RepositoryClass) {
            throw new Error('Repository "' + name + '" has not been registered.');
        }

        return new RepositoryClass(options || {});
    }

    namespace.OfflineRepository = OfflineRepository;
    namespace.registerRepository = registerRepository;
    namespace.getRepository = getRepository;
    namespace.createRepository = createRepository;
    namespace.listRepositories = function () {
        return Object.keys(repositoryRegistry);
    };

    OfflineRepository.createRepository = createRepository;
    OfflineRepository.getRepository = getRepository;
    OfflineRepository.registerRepository = registerRepository;

    global.OfflineRepository = OfflineRepository;
    global.StoreManagementOfflineRepository = OfflineRepository;
    global.StoreManagementRepositoryFactory = namespace;
})(window);
