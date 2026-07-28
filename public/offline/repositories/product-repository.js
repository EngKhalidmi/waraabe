(function (global) {
    'use strict';

    var namespace = global.StoreManagementOfflineRepositories;

    if (!namespace || !namespace.OfflineRepository) {
        throw new Error('repository.js must be loaded before product-repository.js.');
    }

    function ProductRepository(options) {
        options = options || {};
        namespace.OfflineRepository.call(this, 'products', Object.assign({
            searchFields: ['name', 'sku_code', 'barcode', 'sku', 'supplier', 'unit', 'type', 'status', 'info'],
            orderBy: 'updated_at'
        }, options));
    }

    ProductRepository.prototype = Object.create(namespace.OfflineRepository.prototype);
    ProductRepository.prototype.constructor = ProductRepository;

    ProductRepository.prototype.findByBarcode = function (barcode) {
        var self = this;
        var needle = String(barcode == null ? '' : barcode).trim().toLowerCase();

        if (!needle) {
            return Promise.resolve(null);
        }

        return self.search(needle).then(function (records) {
            return records.find(function (record) {
                return String(record.barcode == null ? '' : record.barcode).trim().toLowerCase() === needle ||
                    String(record.sku_code == null ? '' : record.sku_code).trim().toLowerCase() === needle;
            }) || null;
        });
    };

    ProductRepository.prototype.findBySkuCode = function (skuCode) {
        var needle = String(skuCode == null ? '' : skuCode).trim().toLowerCase();

        if (!needle) {
            return Promise.resolve(null);
        }

        return this.search(needle).then(function (records) {
            return records.find(function (record) {
                return String(record.sku_code == null ? '' : record.sku_code).trim().toLowerCase() === needle;
            }) || null;
        });
    };

    ProductRepository.prototype.normalizeProductRecord = function (record, overrides) {
        var source = Object.assign({}, record || {});

        if (!source.sku_code && source.sku) {
            source.sku_code = source.sku;
        }

        if (!source.sku && source.sku_code) {
            source.sku = source.sku_code;
        }

        return this._normalizeRecord(source, overrides);
    };

    ProductRepository.prototype.cacheFromServerRows = function (rows) {
        var self = this;
        var records = Array.isArray(rows) ? rows : [];

        return self.cacheMany(records.map(function (record) {
            var normalized = Object.assign({}, record || {});

            if (typeof normalized.sku_code === 'undefined' && typeof normalized.sku !== 'undefined') {
                normalized.sku_code = normalized.sku;
            }

            if (typeof normalized.sku === 'undefined' && typeof normalized.sku_code !== 'undefined') {
                normalized.sku = normalized.sku_code;
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

    namespace.registerRepository('products', ProductRepository);

    global.ProductRepository = ProductRepository;
    global.StoreManagementProductRepository = ProductRepository;
})(window);
