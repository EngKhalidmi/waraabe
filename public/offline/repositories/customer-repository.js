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

    CustomerRepository.prototype.cacheFromServerRows = function (rows) {
        var self = this;
        var records = Array.isArray(rows) ? rows : [];

        if (!records.length) {
            return Promise.resolve([]);
        }

        return records.reduce(function (promise, record) {
            return promise.then(function (savedRecords) {
                var normalized = Object.assign({}, record || {});
                var serverId = typeof normalized.server_id !== 'undefined'
                    ? normalized.server_id
                    : (typeof normalized.id !== 'undefined' ? normalized.id : null);

                if (typeof normalized.customer_name === 'undefined' && typeof normalized.name !== 'undefined') {
                    normalized.customer_name = normalized.name;
                }

                if (typeof normalized.name === 'undefined' && typeof normalized.customer_name !== 'undefined') {
                    normalized.name = normalized.customer_name;
                }

                if (typeof normalized.server_id === 'undefined') {
                    normalized.server_id = serverId;
                }

                if (typeof normalized.local_id === 'undefined' || normalized.local_id === null || normalized.local_id === '') {
                    normalized.local_id = serverId !== null && typeof serverId !== 'undefined'
                        ? serverId
                        : (typeof normalized.id !== 'undefined' ? normalized.id : null);
                }

                if (typeof normalized.synced === 'undefined') {
                    normalized.synced = true;
                }

                if (typeof normalized.sync_status === 'undefined') {
                    normalized.sync_status = 'synced';
                }

                if (typeof normalized.local_action === 'undefined') {
                    normalized.local_action = 'import';
                }

                return self.findById(serverId).then(function (existing) {
                    if (existing) {
                        normalized.id = existing.id;
                        normalized.local_id = existing.local_id || normalized.local_id;
                        normalized.created_at = existing.created_at || normalized.created_at;
                        normalized.updated_at = normalized.updated_at || existing.updated_at;
                        normalized.synced = true;
                        normalized.sync_status = 'synced';
                        normalized.queue_state = 'synced';
                        normalized.sync_error = null;
                        normalized.last_error = null;
                    } else if (typeof normalized.id !== 'undefined') {
                        delete normalized.id;
                    }

                    return self.cacheMany([normalized]).then(function (saved) {
                        Array.prototype.push.apply(savedRecords, saved);
                        return savedRecords;
                    });
                });
            });
        }, Promise.resolve([]));
    };

    namespace.registerRepository('customers', CustomerRepository);

    global.CustomerRepository = CustomerRepository;
    global.StoreManagementCustomerRepository = CustomerRepository;
})(window);
