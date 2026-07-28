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
                        var source = Object.assign({}, record || {});
                        var existing = self._findMatchingServerRecord(existingRecords, source);
                        var normalized = self.normalizeServerCustomerRow(source);

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
