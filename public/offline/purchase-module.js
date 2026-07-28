(function (global) {
    'use strict';

    var namespace = global.StoreManagementPurchaseModule = global.StoreManagementPurchaseModule || {};
    var repositoryCache = {};
    var onlineTables = {
        fuel: null,
        oil: null,
        all: null
    };
    var offlineState = {
        records: [],
        fuel: [],
        oil: [],
        all: []
    };
    var booted = false;

    function getConfig() {
        return global.__PURCHASE_CONFIG__ || {};
    }

    function isOnline() {
        return navigator.onLine !== false;
    }

    function isIndexPage() {
        return Boolean(document.getElementById('fuelPurchaseTable') || document.getElementById('oilPurchaseTable') || document.getElementById('allPurchaseTable'));
    }

    function isCreatePage() {
        return Boolean(document.querySelector('form[data-purchase-form="create"]'));
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function normalizeNumber(value) {
        var parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function formatNumber(value) {
        return normalizeNumber(value).toFixed(2);
    }

    function nowIso() {
        return new Date().toISOString();
    }

    function formatDateTime(value) {
        if (!value) {
            return '';
        }

        var text = String(value);
        if (text.indexOf('T') !== -1) {
            return text.replace('T', ' ').slice(0, 19);
        }

        return text;
    }

    function uuid() {
        if (global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.generateUuid === 'function') {
            return global.StoreManagementOfflineDatabase.generateUuid();
        }

        if (global.crypto && typeof global.crypto.randomUUID === 'function') {
            return global.crypto.randomUUID();
        }

        return 'purchase-' + Math.random().toString(36).slice(2) + Date.now().toString(36);
    }

    function notify(type, title, text) {
        var icon = type === 'error' ? 'error' : 'success';

        if (global.Swal && typeof global.Swal.fire === 'function') {
            global.Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonText: 'OK',
                confirmButtonColor: '#3d5ee1'
            });
            return;
        }

        alert(title + ': ' + text);
    }

    function showBanner(message) {
        var container = document.querySelector('.page-wrapper .content');

        if (!container || container.querySelector('[data-purchase-offline-banner]')) {
            return;
        }

        var banner = document.createElement('div');
        banner.className = 'alert alert-warning mb-3';
        banner.setAttribute('data-purchase-offline-banner', 'true');
        banner.innerHTML = '<strong>Offline mode:</strong> ' + escapeHtml(message);

        var pageHeader = container.querySelector('.page-header');
        if (pageHeader && pageHeader.parentNode) {
            pageHeader.parentNode.insertBefore(banner, pageHeader.nextSibling);
            return;
        }

        container.insertBefore(banner, container.firstChild);
    }

    function removeBanner() {
        var banner = document.querySelector('[data-purchase-offline-banner]');

        if (banner && banner.parentNode) {
            banner.parentNode.removeChild(banner);
        }
    }

    function getRepository(storeName) {
        if (repositoryCache[storeName]) {
            return repositoryCache[storeName];
        }

        if (global.StoreManagementOfflineRepositories && typeof global.StoreManagementOfflineRepositories.createRepository === 'function') {
            try {
                repositoryCache[storeName] = global.StoreManagementOfflineRepositories.createRepository(storeName);
                return repositoryCache[storeName];
            } catch (error) {
                return null;
            }
        }

        return null;
    }

    function getPurchaseRepo() {
        return getRepository('purchases');
    }

    function getProductRepo() {
        return getRepository('products');
    }

    function getSupplierRepo() {
        return getRepository('suppliers');
    }

    function getStoreRepo() {
        return getRepository('store');
    }

    function getAccountPayableRepo() {
        return getRepository('account_payables');
    }

    function getSupplierCreditRepo() {
        return getRepository('suppliers_credits');
    }

    function getDepartmentId() {
        if (global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.departmentId !== 'undefined') {
            return global.__OFFLINE_ENGINE_CONFIG__.departmentId;
        }

        return null;
    }

    function getUserId() {
        if (global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.userId !== 'undefined') {
            return global.__OFFLINE_ENGINE_CONFIG__.userId;
        }

        return null;
    }

    function getFuelProductIds() {
        return getDepartmentId() === 3 ? [39, 40] : [4, 5, 39, 40];
    }

    function isFuelProductRecord(record) {
        var candidate = record || {};
        var ids = getFuelProductIds();
        var identifier = typeof candidate.proID !== 'undefined' && candidate.proID !== null ? candidate.proID : candidate.server_id;
        var name = String(candidate.name || candidate.product_name || '').toLowerCase();

        if (identifier !== null && typeof identifier !== 'undefined') {
            if (ids.indexOf(Number(identifier)) !== -1) {
                return true;
            }

            if (ids.indexOf(String(identifier)) !== -1) {
                return true;
            }
        }

        return /(diesel|petrol|fuel|gas|kerosene)/i.test(name);
    }

    function detectPurchaseCategory(record) {
        return isFuelProductRecord(record) ? 'fuel' : 'oil';
    }

    function normalizeText(value) {
        return String(value == null ? '' : value).trim().toLowerCase();
    }

    function getSelectedOptionData(selectId) {
        var select = document.getElementById(selectId);

        if (!select) {
            return {
                value: '',
                text: '',
                id: '',
                balance: 0,
                quantity: 0,
                type: '',
                unit: ''
            };
        }

        var option = select.options && select.options.length ? select.options[select.selectedIndex] : null;
        var dataset = option && option.dataset ? option.dataset : {};

        return {
            value: String(select.value == null ? '' : select.value).trim(),
            text: String(option && option.textContent ? option.textContent : select.value || '').trim(),
            id: String(dataset.productId || dataset.supplierId || option && option.value || '').trim(),
            balance: normalizeNumber(dataset.supplierBalance),
            quantity: normalizeNumber(dataset.productQuantity),
            type: String(dataset.productType || '').trim(),
            unit: String(dataset.productUnit || '').trim(),
            raw: option || null
        };
    }

    function ensureRecordSeeded(repo, seed, identifier) {
        if (!repo || !seed) {
            return Promise.resolve(null);
        }

        var lookup = identifier || seed.server_id || seed.local_id || seed.id;

        if (!lookup && seed.name) {
            lookup = seed.name;
        }

        return repo.findById(lookup).then(function (record) {
            if (record) {
                return record;
            }

            return repo.cacheMany([seed]).then(function () {
                return repo.findById(lookup);
            });
        });
    }

    function sanitizeServerPurchaseRow(row) {
        var record = Object.assign({}, row || {});
        var productName = String(record.name || record.product_name || '').trim();
        var supplierName = String(record.supplier || record.supplier_name || '').trim();
        var identifier = typeof record.id !== 'undefined' ? record.id : null;

        record.local_id = typeof record.local_id !== 'undefined' && record.local_id !== null && record.local_id !== ''
            ? record.local_id
            : identifier;
        record.server_id = typeof record.server_id !== 'undefined' ? record.server_id : identifier;
        record.name = productName;
        record.product_name = record.product_name || productName;
        record.supplier = supplierName;
        record.supplier_name = record.supplier_name || supplierName;
        record.purchase_category = record.purchase_category || detectPurchaseCategory({
            proID: record.proID,
            server_id: record.server_id,
            name: productName,
            product_name: productName
        });
        record.remaining = typeof record.remaining !== 'undefined' ? record.remaining : record.quantity;
        record.synced = true;
        record.sync_status = 'synced';
        record.local_action = record.local_action || 'import';

        if (typeof record.id !== 'undefined') {
            delete record.id;
        }

        return record;
    }

    function resolveProductInfoByName(productName) {
        var repo = getProductRepo();
        var needle = normalizeText(productName);

        if (!repo || !needle) {
            return Promise.resolve(null);
        }

        return repo.search(needle).then(function (records) {
            var match = (records || []).find(function (record) {
                return normalizeText(record.name) === needle || normalizeText(record.product_name) === needle;
            });

            return match || (records && records.length ? records[0] : null);
        }).catch(function () {
            return null;
        });
    }

    function cacheServerRows(rows) {
        var repo = getPurchaseRepo();
        var records = Array.isArray(rows) ? rows : [];

        if (!repo || !records.length) {
            return Promise.resolve([]);
        }

        return Promise.all(records.map(function (row) {
            return resolveProductInfoByName(row && (row.name || row.product_name)).then(function (product) {
                var normalized = sanitizeServerPurchaseRow(row);

                if (product) {
                    normalized.proID = product.id || product.server_id || normalized.proID || null;
                    normalized.purchase_category = detectPurchaseCategory({
                        proID: normalized.proID,
                        server_id: normalized.proID,
                        name: product.name || product.product_name || normalized.name,
                        product_name: product.name || product.product_name || normalized.product_name
                    });
                }

                return normalized;
            });
        })).then(function (prepared) {
            return repo.cacheMany(prepared);
        });
    }

    function getFieldValue(id) {
        var node = document.getElementById(id);
        return node ? String(node.value == null ? '' : node.value).trim() : '';
    }

    function buildFilters(type) {
        return {
            type: String(type || 'all'),
            name: getFieldValue('name_' + type).toLowerCase(),
            supplier: getFieldValue('supplier_' + type).toLowerCase(),
            startDate: getFieldValue('startDate_' + type),
            endDate: getFieldValue('endDate_' + type)
        };
    }

    function matchesKeyword(value, keyword) {
        if (!keyword) {
            return true;
        }

        return String(value == null ? '' : value).toLowerCase().indexOf(keyword) !== -1;
    }

    function matchesDateRange(value, startDate, endDate) {
        var current = String(value == null ? '' : value);
        if (!current) {
            return true;
        }

        var normalized = current.indexOf('T') !== -1 ? current.slice(0, 10) : current.slice(0, 10);

        if (startDate && normalized < startDate) {
            return false;
        }

        if (endDate && normalized > endDate) {
            return false;
        }

        return true;
    }

    function normalizePurchaseRecord(record) {
        var source = Object.assign({}, record || {});

        source.name = source.name || source.product_name || '';
        source.product_name = source.product_name || source.name || '';
        source.supplier = source.supplier || source.supplier_name || '';
        source.supplier_name = source.supplier_name || source.supplier || '';
        source.purchase_category = source.purchase_category || detectPurchaseCategory(source);
        source.remaining = typeof source.remaining !== 'undefined' ? source.remaining : source.quantity;
        source.created_at = source.created_at || source.updated_at || nowIso();
        source.updated_at = source.updated_at || source.created_at;
        source.synced = typeof source.synced === 'boolean' ? source.synced : Boolean(source.server_id);
        source.sync_status = source.sync_status || (source.synced ? 'synced' : 'pending');
        source.local_action = source.local_action || (source.synced ? 'import' : 'create');

        return source;
    }

    function buildBadge(label, badgeClass) {
        return '<span class="badge ' + badgeClass + '">' + escapeHtml(label) + '</span>';
    }

    function purchaseStatusBadge(record) {
        if (record.sync_status === 'failed') {
            return buildBadge('Failed', 'bg-danger');
        }

        if (record.sync_status === 'synced') {
            return buildBadge('Synced', 'bg-primary');
        }

        return buildBadge('Pending', 'bg-warning text-dark');
    }

    function categoryBadge(record) {
        var category = String(record.purchase_category || detectPurchaseCategory(record)).toLowerCase();

        if (category === 'fuel') {
            return buildBadge('Fuel', 'bg-danger');
        }

        return buildBadge('Oil', 'bg-info text-dark');
    }

    function buildOfflineRow(record, index) {
        var normalized = normalizePurchaseRecord(record);

        return [
            '<tr data-purchase-row="' + escapeHtml(normalized.id || normalized.local_id || index + 1) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td>',
            '    <strong>' + escapeHtml(normalized.name || '') + '</strong>',
            '    <div class="mt-1">',
            '      <small class="text-muted">Category: ' + escapeHtml(String(normalized.purchase_category || 'oil').toUpperCase()) + '</small> ',
            categoryBadge(normalized),
            '      <div class="mt-1">' + purchaseStatusBadge(normalized) + '</div>',
            '    </div>',
            '  </td>',
            '  <td>' + escapeHtml(formatNumber(normalized.quantity)) + '</td>',
            '  <td>' + escapeHtml(formatNumber(normalized.unit_cost)) + '</td>',
            '  <td>' + escapeHtml(formatNumber(normalized.add_cost)) + '</td>',
            '  <td>' + escapeHtml(formatNumber(normalized.total_cost)) + '</td>',
            '  <td>' + escapeHtml(formatNumber(normalized.remaining)) + '</td>',
            '  <td>' + escapeHtml(normalized.supplier || '') + '</td>',
            '  <td>' + escapeHtml(formatDateTime(normalized.created_at)) + '</td>',
            '</tr>'
        ].join('');
    }

    function filterRecords(records, type) {
        var filters = buildFilters(type);
        var category = String(type || 'all').toLowerCase();

        return records.filter(function (record) {
            var normalized = normalizePurchaseRecord(record);
            var recordCategory = String(normalized.purchase_category || detectPurchaseCategory(normalized)).toLowerCase();

            if (category !== 'all' && recordCategory !== category) {
                return false;
            }

            return matchesKeyword(normalized.name, filters.name) &&
                matchesKeyword(normalized.supplier, filters.supplier) &&
                matchesDateRange(normalized.created_at, filters.startDate, filters.endDate);
        });
    }

    function renderOfflineTable(type, records) {
        var table = document.getElementById(type + 'PurchaseTable');
        var tbody = table ? table.querySelector('tbody') : null;

        if (!table || !tbody) {
            return Promise.resolve([]);
        }

        var filtered = filterRecords(Array.isArray(records) ? records : [], type);

        if (!filtered.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No offline purchase records found.</td></tr>';
            return Promise.resolve(filtered);
        }

        tbody.innerHTML = filtered.map(function (record, index) {
            return buildOfflineRow(record, index);
        }).join('');

        return Promise.resolve(filtered);
    }

    function renderOfflineIndex() {
        var repo = getPurchaseRepo();

        if (!repo) {
            return Promise.resolve([]);
        }

        showBanner('purchase data is being read from IndexedDB.');

        return repo.getAll().then(function (records) {
            offlineState.records = Array.isArray(records) ? records.slice() : [];
            offlineState.fuel = filterRecords(records, 'fuel');
            offlineState.oil = filterRecords(records, 'oil');
            offlineState.all = filterRecords(records, 'all');

            return Promise.all([
                renderOfflineTable('fuel', records),
                renderOfflineTable('oil', records),
                renderOfflineTable('all', records)
            ]);
        }).catch(function (error) {
            notify('error', 'Offline purchase list unavailable', error && error.message ? error.message : 'Unable to read offline purchases.');
            return [];
        });
    }

    function destroyOnlineTables() {
        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return;
        }

        ['#fuelPurchaseTable', '#oilPurchaseTable', '#allPurchaseTable'].forEach(function (selector) {
            if (global.jQuery.fn.DataTable.isDataTable(selector)) {
                global.jQuery(selector).DataTable().destroy();
            }
        });

        onlineTables.fuel = null;
        onlineTables.oil = null;
        onlineTables.all = null;
    }

    function initOnlineTable(type) {
        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return Promise.resolve(null);
        }

        var selector = '#' + type + 'PurchaseTable';
        var searchPrefix = type;
        var config = getConfig();

        if (global.jQuery.fn.DataTable.isDataTable(selector)) {
            onlineTables[type] = global.jQuery(selector).DataTable();
            return Promise.resolve(onlineTables[type]);
        }

        onlineTables[type] = global.jQuery(selector).DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: config.indexRoute || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.name = getFieldValue('name_' + searchPrefix);
                    d.supplier = getFieldValue('supplier_' + searchPrefix);
                    d.startDate = getFieldValue('startDate_' + searchPrefix);
                    d.endDate = getFieldValue('endDate_' + searchPrefix);
                    d.type = type;
                },
                dataSrc: function (json) {
                    cacheServerRows(Array.isArray(json && json.data) ? json.data : []).catch(function () {});
                    return json.data || [];
                },
                error: function () {
                    notify('error', 'Purchase list unavailable', 'Unable to load purchases from the server.');
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'quantity', name: 'quantity' },
                { data: 'unit_cost', name: 'unit_cost' },
                { data: 'add_cost', name: 'add_cost' },
                { data: 'total_cost', name: 'total_cost' },
                { data: 'remaining', name: 'remaining' },
                { data: 'supplier', name: 'supplier' },
                { data: 'created_at', name: 'created_at' }
            ]
        });

        return Promise.resolve(onlineTables[type]);
    }

    function initOnlineIndex() {
        return Promise.all([
            initOnlineTable('fuel'),
            initOnlineTable('oil'),
            initOnlineTable('all')
        ]).then(function () {
            removeBanner();
            return onlineTables;
        });
    }

    function refreshPageMode() {
        if (!isIndexPage()) {
            return Promise.resolve(null);
        }

        if (isOnline()) {
            destroyOnlineTables();
            return initOnlineIndex();
        }

        destroyOnlineTables();
        return renderOfflineIndex();
    }

    function getProductSelectionPayload() {
        var select = getSelectedOptionData('name');
        var productId = select.id || '';
        var productName = select.text || select.value || '';
        var quantity = select.quantity;
        var type = select.type || '';
        var unit = select.unit || '';

        return {
            id: productId,
            name: productName,
            quantity: quantity,
            type: type,
            unit: unit
        };
    }

    function getSupplierSelectionPayload() {
        var select = getSelectedOptionData('supplier');

        return {
            id: select.id || '',
            name: select.text || select.value || '',
            balance: normalizeNumber(select.balance)
        };
    }

    function buildStoreLog(record, previousQuantity, newQuantity) {
        return {
            key: 'purchase-stock:' + uuid(),
            code: 'purchase_stock',
            name: record.product_name || record.name || '',
            type: 'inventory_increase',
            value: JSON.stringify({
                source: 'purchase',
                purchase_local_id: record.local_id || null,
                purchase_server_id: record.server_id || null,
                product_id: record.proID || null,
                product_name: record.product_name || record.name || '',
                supplier_id: record.supplier_id || null,
                supplier_name: record.supplier_name || record.supplier || '',
                quantity: normalizeNumber(record.quantity),
                unit_cost: normalizeNumber(record.unit_cost),
                add_cost: normalizeNumber(record.add_cost),
                total_cost: normalizeNumber(record.total_cost),
                previous_quantity: normalizeNumber(previousQuantity),
                new_quantity: normalizeNumber(newQuantity)
            }),
            depID: getDepartmentId(),
            synced: false,
            sync_status: 'pending',
            local_action: 'create'
        };
    }

    function buildPurchaseRecord(form, productSelection, supplierSelection, currentQuantity, category) {
        var formData = global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.formDataToObject === 'function'
            ? global.StoreManagementOfflineDatabase.formDataToObject(new FormData(form))
            : {};
        var quantity = normalizeNumber(formData.quantity);
        var unitCost = normalizeNumber(formData.unit_cost);
        var addCost = normalizeNumber(formData.add_cost || 0);
        var totalCost = normalizeNumber(formData.total_cost);
        var currentStock = normalizeNumber(currentQuantity);
        var now = nowIso();

        if (!totalCost) {
            totalCost = quantity * unitCost;
        }

        return {
            proID: productSelection.id || null,
            name: productSelection.name || '',
            product_name: productSelection.name || '',
            supplier: supplierSelection.name || '',
            supplier_name: supplierSelection.name || '',
            supplier_id: supplierSelection.id || null,
            quantity: quantity,
            unit_cost: unitCost,
            add_cost: addCost,
            total_cost: totalCost,
            remaining: quantity,
            purchase_category: category,
            depID: getDepartmentId(),
            synced: false,
            sync_status: 'pending',
            local_action: 'create',
            created_at: now,
            updated_at: now
        };
    }

    function buildAccountPayableRecord(purchaseRecord, supplierSelection, previousBalance) {
        var amount = normalizeNumber(purchaseRecord.total_cost);
        var balanceBefore = normalizeNumber(previousBalance);
        var balanceAfter = balanceBefore + amount;
        var now = nowIso();

        return {
            received_from: supplierSelection.id || supplierSelection.name || '',
            supplier_id: supplierSelection.id || null,
            supplier_name: supplierSelection.name || '',
            amount: amount,
            discount: 0,
            pbalance: balanceBefore,
            current: balanceAfter,
            type: 'Short Term',
            transaction_type: 'Credit',
            account: 'Cash & Cash Equivalent',
            date: now,
            depID: getDepartmentId(),
            description: 'Offline purchase for ' + (purchaseRecord.product_name || purchaseRecord.name || ''),
            user: getUserId(),
            synced: false,
            sync_status: 'pending',
            local_action: 'create',
            created_at: now,
            updated_at: now
        };
    }

    function buildSupplierCreditRecord(purchaseRecord, supplierSelection, previousBalance) {
        var amount = normalizeNumber(purchaseRecord.total_cost);
        var balanceBefore = normalizeNumber(previousBalance);
        var balanceAfter = balanceBefore + amount;
        var now = nowIso();

        return {
            supplier_id: supplierSelection.id || null,
            supplier_name: supplierSelection.name || '',
            amount: amount,
            balance: balanceAfter,
            previous_balance: balanceBefore,
            status: 'pending',
            date: now,
            description: 'Offline purchase for ' + (purchaseRecord.product_name || purchaseRecord.name || ''),
            depID: getDepartmentId(),
            synced: false,
            sync_status: 'pending',
            local_action: 'create',
            created_at: now,
            updated_at: now
        };
    }

    function resolveCurrentProductQuantity(productRepo, productSelection) {
        if (!productRepo || !productSelection) {
            return Promise.resolve(normalizeNumber(productSelection ? productSelection.quantity : 0));
        }

        return productRepo.findById(productSelection.id || productSelection.name).then(function (record) {
            if (record && typeof record.quantity !== 'undefined') {
                return normalizeNumber(record.quantity);
            }

            return normalizeNumber(productSelection.quantity);
        });
    }

    function resolveSupplierBalance(supplierRepo, supplierSelection) {
        if (!supplierRepo || !supplierSelection) {
            return Promise.resolve(normalizeNumber(supplierSelection ? supplierSelection.balance : 0));
        }

        return supplierRepo.findById(supplierSelection.id || supplierSelection.name).then(function (record) {
            if (record && typeof record.balance !== 'undefined') {
                return normalizeNumber(record.balance);
            }

            return normalizeNumber(supplierSelection.balance);
        });
    }

    function submitOfflinePurchase(form) {
        var purchaseRepo = getPurchaseRepo();
        var productRepo = getProductRepo();
        var supplierRepo = getSupplierRepo();
        var storeRepo = getStoreRepo();
        var payableRepo = getAccountPayableRepo();
        var supplierCreditRepo = getSupplierCreditRepo();

        if (!purchaseRepo || !productRepo || !supplierRepo || !storeRepo || !payableRepo || !supplierCreditRepo) {
            notify('error', 'Offline unavailable', 'Purchase repositories are not ready yet.');
            return Promise.reject(new Error('Purchase repositories unavailable.'));
        }

        var productSelection = getProductSelectionPayload();
        var supplierSelection = getSupplierSelectionPayload();
        var category = detectPurchaseCategory({
            proID: productSelection.id,
            name: productSelection.name,
            product_name: productSelection.name
        });

        if (!productSelection.name) {
            notify('error', 'Missing product', 'Please select a product before saving the purchase offline.');
            return Promise.reject(new Error('Missing product selection.'));
        }

        if (!supplierSelection.name) {
            notify('error', 'Missing supplier', 'Please select a supplier before saving the purchase offline.');
            return Promise.reject(new Error('Missing supplier selection.'));
        }

        var productSeed = {
            local_id: productSelection.id || productSelection.name,
            server_id: productSelection.id || null,
            name: productSelection.name,
            product_name: productSelection.name,
            quantity: normalizeNumber(productSelection.quantity),
            type: productSelection.type || '',
            unit: productSelection.unit || '',
            supplier: supplierSelection.name || '',
            synced: true,
            sync_status: 'synced',
            local_action: 'import',
            created_at: nowIso(),
            updated_at: nowIso()
        };

        var supplierSeed = {
            local_id: supplierSelection.id || supplierSelection.name,
            server_id: supplierSelection.id || null,
            name: supplierSelection.name,
            supplier_name: supplierSelection.name,
            balance: normalizeNumber(supplierSelection.balance),
            synced: true,
            sync_status: 'synced',
            local_action: 'import',
            created_at: nowIso(),
            updated_at: nowIso()
        };

        return Promise.all([
            ensureRecordSeeded(productRepo, productSeed, productSelection.id || productSelection.name),
            ensureRecordSeeded(supplierRepo, supplierSeed, supplierSelection.id || supplierSelection.name)
        ]).then(function () {
            return Promise.all([
                resolveCurrentProductQuantity(productRepo, productSelection),
                resolveSupplierBalance(supplierRepo, supplierSelection)
            ]);
        }).then(function (results) {
            var currentQuantity = normalizeNumber(results[0]);
            var supplierBalance = normalizeNumber(results[1]);
            var purchaseRecord = buildPurchaseRecord(form, productSelection, supplierSelection, currentQuantity, category);
            var newQuantity = currentQuantity + normalizeNumber(purchaseRecord.quantity);
            var productIdentifier = productSelection.id || productSelection.name;

            return purchaseRepo.create(purchaseRecord).then(function (savedPurchase) {
                return productRepo.update(productIdentifier, {
                    quantity: newQuantity,
                    updated_at: nowIso()
                }).then(function () {
                    return Promise.all([
                        storeRepo.create(buildStoreLog(savedPurchase || purchaseRecord, currentQuantity, newQuantity)),
                        payableRepo.create(buildAccountPayableRecord(savedPurchase || purchaseRecord, supplierSelection, supplierBalance)),
                        supplierCreditRepo.create(buildSupplierCreditRecord(savedPurchase || purchaseRecord, supplierSelection, supplierBalance))
                    ]).then(function () {
                        return savedPurchase;
                    });
                });
            });
        }).then(function (savedPurchase) {
            notify('success', 'Saved offline', 'Purchase was stored locally, stock was increased, and the change was queued for sync.');

            form.reset();
            return savedPurchase;
        }).catch(function (error) {
            notify('error', 'Purchase save failed', error && error.message ? error.message : 'Unable to save the purchase locally.');
            throw error;
        });
    }

    function bindPurchaseForms() {
        var forms = document.querySelectorAll('form[data-purchase-form="create"]');

        if (!forms.length) {
            return;
        }

        forms.forEach(function (form) {
            if (form.dataset.purchaseOfflineBound === 'true') {
                return;
            }

            form.dataset.purchaseOfflineBound = 'true';

            form.addEventListener('submit', function (event) {
                if (isOnline()) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                submitOfflinePurchase(form);
            }, true);
        });
    }

    function bindIndexActions() {
        if (!isIndexPage()) {
            return;
        }

        ['fuel', 'oil', 'all'].forEach(function (type) {
            var searchButton = document.getElementById('searchBtn_' + type);

            if (searchButton && searchButton.dataset.purchaseOfflineBound !== 'true') {
                searchButton.dataset.purchaseOfflineBound = 'true';
                searchButton.addEventListener('click', function (event) {
                    event.preventDefault();

                    if (isOnline() && onlineTables[type] && typeof onlineTables[type].draw === 'function') {
                        onlineTables[type].draw();
                        return;
                    }

                    renderOfflineTable(type, offlineState.records.length ? offlineState.records : []);
                });
            }

            ['name_' + type, 'supplier_' + type, 'startDate_' + type, 'endDate_' + type].forEach(function (fieldId) {
                var field = document.getElementById(fieldId);

                if (!field || field.dataset.purchaseOfflineBound === 'true') {
                    return;
                }

                field.dataset.purchaseOfflineBound = 'true';
                field.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();

                    if (isOnline() && onlineTables[type] && typeof onlineTables[type].draw === 'function') {
                        onlineTables[type].draw();
                        return;
                    }

                    renderOfflineTable(type, offlineState.records.length ? offlineState.records : []);
                });
            });
        });

        var tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(function (button) {
            if (button.dataset.purchaseOfflineBound === 'true') {
                return;
            }

            button.dataset.purchaseOfflineBound = 'true';
            button.addEventListener('shown.bs.tab', function () {
                if (isOnline()) {
                    var target = button.getAttribute('aria-controls');
                    if (target === 'fuel-purchases' && onlineTables.fuel && typeof onlineTables.fuel.draw === 'function') {
                        onlineTables.fuel.draw();
                    } else if (target === 'oil-purchases' && onlineTables.oil && typeof onlineTables.oil.draw === 'function') {
                        onlineTables.oil.draw();
                    } else if (target === 'all-purchases' && onlineTables.all && typeof onlineTables.all.draw === 'function') {
                        onlineTables.all.draw();
                    }
                    return;
                }

                renderOfflineIndex();
            });
        });
    }

    function bindConnectivityListeners() {
        if (bindConnectivityListeners.bound) {
            return;
        }

        bindConnectivityListeners.bound = true;

        global.addEventListener('online', function () {
            refreshPageMode();
            refreshFormBannerState();
        });

        global.addEventListener('offline', function () {
            refreshPageMode();
            refreshFormBannerState();
        });

        global.addEventListener('offline-queue:changed', function () {
            if (!isOnline() && isIndexPage()) {
                renderOfflineIndex();
            }

            refreshFormBannerState();
        });
    }

    function refreshFormBannerState() {
        if (!isCreatePage()) {
            return;
        }

        if (isOnline()) {
            removeBanner();
            return;
        }

        showBanner('purchase changes will be stored locally and queued for sync.');
    }

    function boot() {
        if (booted) {
            return;
        }

        booted = true;

        bindConnectivityListeners();
        bindPurchaseForms();
        bindIndexActions();

        if (isIndexPage()) {
            refreshPageMode();
        }

        if (isCreatePage()) {
            refreshFormBannerState();
        }
    }

    namespace.boot = boot;
    namespace.refreshPageMode = refreshPageMode;
    namespace.renderOfflineIndex = renderOfflineIndex;
    namespace.getRepository = getRepository;

    global.StoreManagementPurchaseModuleClass = {
        boot: boot
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
