(function (global) {
    'use strict';

    var namespace = global.StoreManagementOfflineRepositories;

    if (!namespace || !namespace.OfflineRepository) {
        throw new Error('repository.js must be loaded before customer-repository.js.');
    }

    function CustomerRepository(options) {
        options = options || {};
        namespace.OfflineRepository.call(this, 'customers', Object.assign({
            searchFields: ['customer_name', 'name', 'phone', 'serial', 'customer_code', 'email', 'address', 'city', 'description'],
            orderBy: 'updated_at'
        }, options));
    }

    function snapshotTableSchema(table) {
        var schema = table && table.schema ? table.schema : null;
        var primKey = schema && schema.primKey ? schema.primKey : null;
        var indexes = schema && Array.isArray(schema.indexes) ? schema.indexes : [];

        return {
            tableName: table && table.name ? table.name : 'customers',
            primaryKey: primKey ? {
                name: primKey.name || null,
                keyPath: primKey.keyPath || null,
                unique: Boolean(primKey.unique),
                multi: Boolean(primKey.multi),
                auto: Boolean(primKey.auto)
            } : null,
            indexes: indexes.map(function (index) {
                return {
                    name: index && index.name ? index.name : null,
                    keyPath: index && typeof index.keyPath !== 'undefined' ? index.keyPath : null,
                    unique: Boolean(index && index.unique),
                    multi: Boolean(index && index.multi)
                };
            })
        };
    }

    function logCustomerCacheError(error, row, normalized, table) {
        console.error('Unable to cache customer rows for offline use', {
            errorName: error && error.name ? error.name : 'UnknownError',
            errorMessage: error && error.message ? error.message : String(error || 'Unknown error'),
            errorStack: error && error.stack ? error.stack : null,
            customerObject: row || null,
            normalizedCustomerObject: normalized || null,
            indexedDbTableSchema: snapshotTableSchema(table)
        });
    }

    CustomerRepository.prototype = Object.create(namespace.OfflineRepository.prototype);
    CustomerRepository.prototype.constructor = CustomerRepository;

    CustomerRepository.prototype.normalizeCustomerRecord = function (record, overrides) {
        var source = Object.assign({}, record || {});

        if (!source.customer_name && source.name) {
            source.customer_name = source.name;
        }

        if (!source.name && source.customer_name) {
            source.name = source.customer_name;
        }

        return this._normalizeRecord(source, overrides);
    };

    CustomerRepository.prototype.normalizeServerCustomerRow = function (record) {
        var source = Object.assign({}, record || {});
        var serverId = typeof source.server_id !== 'undefined'
            ? source.server_id
            : (typeof source.id !== 'undefined' ? source.id : null);
        var createdAt = source.created_at || source.createdAt || source.updated_at || null;
        var updatedAt = source.updated_at || source.updatedAt || createdAt;

        if (!source.customer_name && source.name) {
            source.customer_name = source.name;
        }

        if (!source.name && source.customer_name) {
            source.name = source.customer_name;
        }

        if (typeof source.birthDate === 'undefined' && typeof source.birth_date !== 'undefined') {
            source.birthDate = source.birth_date;
        }

        if (typeof source.depID === 'undefined' && typeof source.dep_id !== 'undefined') {
            source.depID = source.dep_id;
        }

        if (typeof source.age === 'undefined' && typeof source.birthDate !== 'undefined') {
            source.age = source.birthDate;
        }

        if (typeof source.server_id === 'undefined') {
            source.server_id = serverId;
        }

        if (typeof source.local_id === 'undefined' || source.local_id === null || source.local_id === '') {
            source.local_id = serverId !== null && typeof serverId !== 'undefined'
                ? serverId
                : (typeof source.id !== 'undefined' ? source.id : null);
        }

        source.balance = typeof source.balance !== 'undefined' ? source.balance : 0;
        source.created_at = createdAt || updatedAt || null;
        source.updated_at = updatedAt || createdAt || null;
        source.synced = true;
        source.sync_status = 'synced';
        source.local_action = source.local_action || 'import';
        source.queue_state = 'synced';
        source.sync_error = null;
        source.last_error = null;

        return this._normalizeRecord(source, {
            id: typeof source.id !== 'undefined' ? source.id : undefined,
            server_id: source.server_id,
            local_id: source.local_id,
            synced: true,
            sync_status: 'synced',
            local_action: source.local_action,
            queue_state: 'synced',
            sync_error: null,
            last_error: null,
            created_at: source.created_at,
            updated_at: source.updated_at
        });
    };

    CustomerRepository.prototype._findMatchingServerRecord = function (records, row) {
        var normalizedRow = Object.assign({}, row || {});
        var serverId = typeof normalizedRow.server_id !== 'undefined'
            ? normalizedRow.server_id
            : (typeof normalizedRow.id !== 'undefined' ? normalizedRow.id : null);
        var localId = typeof normalizedRow.local_id !== 'undefined' ? normalizedRow.local_id : null;
        var serial = String(normalizedRow.serial == null ? '' : normalizedRow.serial).trim().toLowerCase();
        var phone = String(normalizedRow.phone == null ? '' : normalizedRow.phone).trim().toLowerCase();

        return records.find(function (record) {
            if (!record) {
                return false;
            }

            if (serverId !== null && typeof serverId !== 'undefined') {
                if (String(record.server_id) === String(serverId) || String(record.id) === String(serverId)) {
                    return true;
                }
            }

            if (localId !== null && typeof localId !== 'undefined' && String(record.local_id) === String(localId)) {
                return true;
            }

            if (serial && String(record.serial == null ? '' : record.serial).trim().toLowerCase() === serial) {
                return true;
            }

            if (phone && String(record.phone == null ? '' : record.phone).trim().toLowerCase() === phone) {
                return true;
            }

            return false;
        }) || null;
    };

    CustomerRepository.prototype._resolveConflictByIndexes = function (table, row) {
        var serverId = typeof row.server_id !== 'undefined' ? row.server_id : (typeof row.id !== 'undefined' ? row.id : null);
        var localId = typeof row.local_id !== 'undefined' ? row.local_id : null;
        var serial = String(row.serial == null ? '' : row.serial).trim();
        var phone = String(row.phone == null ? '' : row.phone).trim();

        if (serverId !== null && typeof serverId !== 'undefined') {
            return table.where('server_id').equals(serverId).first().then(function (record) {
                return record || null;
            });
        }

        if (localId !== null && typeof localId !== 'undefined') {
            return table.where('local_id').equals(localId).first().then(function (record) {
                return record || null;
            });
        }

        if (serial) {
            return table.where('serial').equalsIgnoreCase(serial).first().then(function (record) {
                return record || null;
            });
        }

        if (phone) {
            return table.where('phone').equals(phone).first().then(function (record) {
                return record || null;
            });
        }

        return Promise.resolve(null);
    };

    CustomerRepository.prototype._upsertServerRow = function (table, row, existingRecords) {
        var self = this;
        var existing = self._findMatchingServerRecord(existingRecords, row);
        var normalized = self.normalizeServerCustomerRow(row);

        if (existing) {
            normalized.id = existing.id;
            normalized.local_id = existing.local_id || normalized.local_id;
            normalized.server_id = typeof normalized.server_id !== 'undefined' && normalized.server_id !== null
                ? normalized.server_id
                : existing.server_id;
            normalized.created_at = existing.created_at || normalized.created_at;
            normalized.updated_at = normalized.updated_at || existing.updated_at || existing.created_at;
        } else if (typeof normalized.id !== 'undefined') {
            delete normalized.id;
        }

        return table.put(normalized).then(function (savedKey) {
            var savedRecord = Object.assign({}, normalized, {
                id: typeof normalized.id !== 'undefined' ? normalized.id : savedKey
            });

            existingRecords = existingRecords.filter(function (candidate) {
                return !candidate || String(candidate.id) !== String(savedRecord.id);
            });
            existingRecords.push(savedRecord);

            return savedRecord;
        }).catch(function (error) {
            logCustomerCacheError(error, row, normalized, table);

            if (!error || (error.name !== 'ConstraintError' && error.name !== 'BulkError' && error.name !== 'DuplicateError')) {
                return Promise.reject(error);
            }

            return self._resolveConflictByIndexes(table, normalized).then(function (conflict) {
                if (!conflict) {
                    logCustomerCacheError(error, row, normalized, table);
                    return Promise.reject(error);
                }

                normalized.id = conflict.id;
                normalized.local_id = conflict.local_id || normalized.local_id;
                normalized.server_id = typeof normalized.server_id !== 'undefined' && normalized.server_id !== null
                    ? normalized.server_id
                    : conflict.server_id;
                normalized.created_at = conflict.created_at || normalized.created_at;
                normalized.updated_at = normalized.updated_at || conflict.updated_at || conflict.created_at;

                return table.put(normalized).then(function (savedKey) {
                    var savedRecord = Object.assign({}, normalized, {
                        id: typeof normalized.id !== 'undefined' ? normalized.id : savedKey
                    });

                    existingRecords = existingRecords.filter(function (candidate) {
                        return !candidate || String(candidate.id) !== String(savedRecord.id);
                    });
                    existingRecords.push(savedRecord);

                    return savedRecord;
                }).catch(function (secondError) {
                    logCustomerCacheError(secondError, row, normalized, table);
                    return Promise.reject(secondError);
                });
            });
        });
    };

    CustomerRepository.prototype.cacheFromServerRows = function (rows) {
        var self = this;
        var records = Array.isArray(rows) ? rows : [];

        if (!records.length) {
            return Promise.resolve([]);
        }

        return self._withTable(function (database, table) {
            return self._readAll().then(function (existingRecords) {
                return records.reduce(function (promise, record) {
                    return promise.then(function (savedRecords) {
                        return self._upsertServerRow(table, record, existingRecords).then(function (savedRecord) {
                            savedRecords.push(savedRecord);
                            return savedRecords;
                        });
                    });
                }, Promise.resolve([]));
            });
        });
    };

    namespace.registerRepository('customers', CustomerRepository);

    global.CustomerRepository = CustomerRepository;
    global.StoreManagementCustomerRepository = CustomerRepository;
})(window);
