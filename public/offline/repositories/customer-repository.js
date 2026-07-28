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

        return self.cacheMany(records.map(function (record) {
            var normalized = Object.assign({}, record || {});

            if (typeof normalized.customer_name === 'undefined' && typeof normalized.name !== 'undefined') {
                normalized.customer_name = normalized.name;
            }

            if (typeof normalized.name === 'undefined' && typeof normalized.customer_name !== 'undefined') {
                normalized.name = normalized.customer_name;
            }

            if (typeof normalized.server_id === 'undefined') {
                normalized.server_id = typeof normalized.id !== 'undefined' ? normalized.id : null;
            }

            if (typeof normalized.local_id === 'undefined' || normalized.local_id === null || normalized.local_id === '') {
                normalized.local_id = normalized.server_id !== null && typeof normalized.server_id !== 'undefined'
                    ? normalized.server_id
                    : (typeof normalized.id !== 'undefined' ? normalized.id : null);
            }

            if (typeof normalized.id !== 'undefined') {
                delete normalized.id;
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

            return normalized;
        }));
    };

    namespace.registerRepository('customers', CustomerRepository);

    global.CustomerRepository = CustomerRepository;
    global.StoreManagementCustomerRepository = CustomerRepository;
})(window);
