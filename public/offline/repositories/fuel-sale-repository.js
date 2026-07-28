(function (global) {
    'use strict';

    var namespace = global.StoreManagementOfflineRepositories;

    if (!namespace || !namespace.OfflineRepository) {
        throw new Error('repository.js must be loaded before fuel-sale-repository.js.');
    }

    function FuelSaleRepository(options) {
        options = options || {};
        namespace.OfflineRepository.call(this, 'fuel_sales', Object.assign({
            searchFields: ['ticket_number', 'invoice_number', 'customer_name', 'product_name', 'salesman', 'shift', 'date', 'balance', 'net_total', 'cash_on_hand'],
            orderBy: 'updated_at'
        }, options));
    }

    FuelSaleRepository.prototype = Object.create(namespace.OfflineRepository.prototype);
    FuelSaleRepository.prototype.constructor = FuelSaleRepository;

    FuelSaleRepository.prototype.cacheFromServerRows = function (rows) {
        var self = this;
        var records = Array.isArray(rows) ? rows : [];

        return self.cacheMany(records.map(function (record) {
            var normalized = Object.assign({}, record || {});

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

    namespace.registerRepository('fuel_sales', FuelSaleRepository);

    global.FuelSaleRepository = FuelSaleRepository;
    global.StoreManagementFuelSaleRepository = FuelSaleRepository;
})(window);
