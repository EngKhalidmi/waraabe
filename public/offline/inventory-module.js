(function (global) {
    'use strict';

    var namespace = global.StoreManagementInventoryModule = global.StoreManagementInventoryModule || {};
    var repositoryCache = {};
    var openingTableInstance = null;
    var badTableInstance = null;
    var booted = false;

    function getConfig() {
        return global.__INVENTORY_CONFIG__ || {};
    }

    function isOnline() {
        return navigator.onLine !== false;
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function toNumber(value) {
        var parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function formatNumber(value) {
        return toNumber(value).toFixed(2);
    }

    function nowIso() {
        return new Date().toISOString();
    }

    function dateOnly(value) {
        if (!value) {
            return '';
        }

        var text = String(value);
        return text.indexOf('T') !== -1 ? text.slice(0, 10) : text;
    }

    function displayDateTime(value) {
        if (!value) {
            return '';
        }

        var text = String(value);
        if (text.indexOf('T') !== -1) {
            return text.replace('T', ' ').slice(0, 16);
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

        return 'inv-' + Math.random().toString(36).slice(2) + Date.now().toString(36);
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

    function showToast(type, message) {
        if (global.jQuery) {
            global.jQuery('.toast-container').remove();

            var title = type === 'success' ? 'Success!' : 'Error!';
            var iconClass = type === 'success'
                ? 'icon-checkmark fas fa-check-circle'
                : 'icon-error fa fa-exclamation-circle';

            global.jQuery('body').append(
                '<div class="toast-container">' +
                '  <div class="toast-message ' + type + '">' +
                '    <div class="toast-icon"><i class="' + iconClass + '"></i></div>' +
                '    <div class="toast-content"><strong>' + title + '</strong><p>' + escapeHtml(message) + '</p></div>' +
                '  </div>' +
                '</div>'
            );

            global.setTimeout(function () {
                global.jQuery('.toast-container').fadeOut(300, function () {
                    global.jQuery(this).remove();
                });
            }, 5000);
            return;
        }

        notify(type, type === 'success' ? 'Success!' : 'Error!', message);
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

        var className = storeName.replace(/(^|_)([a-z])/g, function (_, prefix, letter) {
            return (prefix ? '' : '') + letter.toUpperCase();
        }) + 'Repository';

        if (typeof global[className] === 'function') {
            repositoryCache[storeName] = new global[className]();
            return repositoryCache[storeName];
        }

        return null;
    }

    function getProductRepo() {
        return getRepository('products');
    }

    function getOpeningRepo() {
        return getRepository('opening_inventory');
    }

    function getBadRepo() {
        return getRepository('bad_products');
    }

    function getReturnedRepo() {
        return getRepository('returned_credits');
    }

    function getStoreRepo() {
        return getRepository('store');
    }

    function getDepartmentId() {
        if (global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.departmentId !== 'undefined') {
            return global.__OFFLINE_ENGINE_CONFIG__.departmentId;
        }

        return null;
    }

    function isOpeningIndexPage() {
        return Boolean(document.getElementById('openingInventoryTable'));
    }

    function isBadIndexPage() {
        return Boolean(document.getElementById('badProductsTable'));
    }

    function isOpeningCreatePage() {
        return Boolean(document.querySelector('form[data-opening-inventory-form="create"]'));
    }

    function isOpeningUpdatePage() {
        return Boolean(document.querySelector('form[data-opening-inventory-form="update"]'));
    }

    function isBadCreatePage() {
        return Boolean(document.querySelector('form[data-bad-product-form="create"]'));
    }

    function openingInventoryUrlBase() {
        var config = getConfig();
        return config.openingInventoryEditBaseUrl || config.openingInventoryDeleteBaseUrl || '';
    }

    function badProductsUrlBase() {
        var config = getConfig();
        return config.badProductsDeleteBaseUrl || '';
    }

    function parseActionId(actionUrl) {
        if (!actionUrl) {
            return '';
        }

        var match = String(actionUrl).match(/\/(\d+)(?:\/edit)?\/?$/);
        return match ? match[1] : '';
    }

    function productDisplayName(record) {
        return record && (record.name || record.product_name || record.product || '') || '';
    }

    function stockStatusLabel(quantity) {
        var value = toNumber(quantity);

        if (value <= 0) {
            return 'Out of Stock';
        }

        if (value < 10) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    function stockStatusBadge(quantity) {
        var value = toNumber(quantity);
        var label = stockStatusLabel(value);
        var badgeClass = value <= 0 ? 'bg-danger' : (value < 10 ? 'bg-warning text-dark' : 'bg-success');

        return '<span class="badge ' + badgeClass + '">' + escapeHtml(label) + '</span>';
    }

    function enrichOpeningRecord(record) {
        var repo = getProductRepo();
        var clone = Object.assign({}, record || {});

        if (!repo || !clone.product_id) {
            clone.product_name = clone.product_name || clone.product || '';
            clone.current_stock = typeof clone.current_stock !== 'undefined' ? clone.current_stock : null;
            clone.stock_status = clone.stock_status || stockStatusLabel(clone.current_stock || clone.opening_quantity);
            return Promise.resolve(clone);
        }

        return repo.findById(clone.product_id).then(function (product) {
            var quantity = product ? toNumber(product.quantity) : toNumber(clone.opening_quantity);

            clone.product_name = clone.product_name || productDisplayName(product) || clone.product || '';
            clone.current_stock = quantity;
            clone.stock_status = stockStatusLabel(quantity);
            clone.supplier = clone.supplier || (product ? product.supplier || '' : '');

            return clone;
        }).catch(function () {
            clone.product_name = clone.product_name || clone.product || '';
            clone.current_stock = typeof clone.current_stock !== 'undefined' ? clone.current_stock : null;
            clone.stock_status = clone.stock_status || stockStatusLabel(clone.current_stock || clone.opening_quantity);
            return clone;
        });
    }

    function enrichBadRecord(record) {
        var repo = getProductRepo();
        var clone = Object.assign({}, record || {});

        if (!repo || !clone.proID) {
            clone.product_name = clone.product_name || clone.name || '';
            clone.supplier = clone.supplier || '';
            return Promise.resolve(clone);
        }

        return repo.findById(clone.proID).then(function (product) {
            clone.product_name = clone.product_name || productDisplayName(product) || clone.name || '';
            clone.supplier = clone.supplier || (product ? product.supplier || '' : '');
            return clone;
        }).catch(function () {
            clone.product_name = clone.product_name || clone.name || '';
            clone.supplier = clone.supplier || '';
            return clone;
        });
    }

    function normalizeOpeningServerRow(row) {
        var cloned = Object.assign({}, row || {});

        cloned.product_name = cloned.product_name || cloned.product || '';
        cloned.local_id = cloned.local_id || cloned.server_id || cloned.id || uuid();
        cloned.server_id = typeof cloned.server_id !== 'undefined' ? cloned.server_id : (typeof cloned.id !== 'undefined' ? cloned.id : null);
        cloned.synced = true;
        cloned.sync_status = 'synced';
        cloned.local_action = cloned.local_action || 'import';

        return cloned;
    }

    function normalizeBadServerRow(row) {
        var cloned = Object.assign({}, row || {});

        cloned.product_name = cloned.product_name || cloned.name || '';
        cloned.local_id = cloned.local_id || cloned.server_id || cloned.id || uuid();
        cloned.server_id = typeof cloned.server_id !== 'undefined' ? cloned.server_id : (typeof cloned.id !== 'undefined' ? cloned.id : null);
        cloned.synced = true;
        cloned.sync_status = 'synced';
        cloned.local_action = cloned.local_action || 'import';

        return cloned;
    }

    function cacheRows(repo, rows, mapper) {
        var records = Array.isArray(rows) ? rows : [];

        if (!repo || !records.length || typeof repo.cacheMany !== 'function') {
            return Promise.resolve([]);
        }

        var prepared = typeof mapper === 'function' ? records.map(mapper) : records;
        return repo.cacheMany(prepared);
    }

    function getValue(id) {
        var node = document.getElementById(id);
        return node ? String(node.value == null ? '' : node.value).trim() : '';
    }

    function buildOpeningFilters() {
        return {
            name: getValue('name').toLowerCase(),
            startDate: getValue('startDate'),
            endDate: getValue('endDate')
        };
    }

    function buildBadFilters() {
        return {
            name: getValue('name').toLowerCase(),
            supplier: getValue('supplier').toLowerCase(),
            startDate: getValue('startDate'),
            endDate: getValue('endDate')
        };
    }

    function matchesKeyword(value, keyword) {
        if (!keyword) {
            return true;
        }

        return String(value == null ? '' : value).toLowerCase().indexOf(keyword) !== -1;
    }

    function matchesDateRange(value, startDate, endDate) {
        var current = dateOnly(value);

        if (startDate && current && current < startDate) {
            return false;
        }

        if (endDate && current && current > endDate) {
            return false;
        }

        return true;
    }

    function openingOfflineFiltered(records) {
        var filters = buildOpeningFilters();

        return records.filter(function (record) {
            return matchesKeyword(record.product_name || record.product, filters.name) &&
                matchesDateRange(record.opening_date || record.created_at, filters.startDate, filters.endDate);
        });
    }

    function badOfflineFiltered(records) {
        var filters = buildBadFilters();

        return records.filter(function (record) {
            return matchesKeyword(record.product_name || record.name, filters.name) &&
                matchesKeyword(record.supplier, filters.supplier) &&
                matchesDateRange(record.created_at, filters.startDate, filters.endDate);
        });
    }

    function openingRowHtml(record, index) {
        var id = record.id || record.local_id || index + 1;
        var editUrl = openingInventoryUrlBase() ? openingInventoryUrlBase() + '/' + id + '/edit' : '#';
        var deleteUrl = openingInventoryUrlBase() ? openingInventoryUrlBase() + '/' + id : '#';

        return [
            '<tr data-opening-inventory-id="' + escapeHtml(id) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td>',
            '    <strong>' + escapeHtml(productDisplayName(record)) + '</strong>',
            '    <div class="mt-1"><small class="text-muted">Stock: ' + escapeHtml(formatNumber(record.current_stock != null ? record.current_stock : record.opening_quantity)) + '</small> <span class="ms-2">' + stockStatusBadge(record.current_stock != null ? record.current_stock : record.opening_quantity) + '</span></div>',
            '  </td>',
            '  <td>' + escapeHtml(formatNumber(record.opening_quantity)) + '</td>',
            '  <td>' + escapeHtml(record.opening_date || '') + '</td>',
            '  <td>' + escapeHtml(displayDateTime(record.created_at)) + '</td>',
            '  <td>',
            '    <a href="' + escapeHtml(editUrl) + '" class="btn btn-sm btn-primary me-2">Edit</a>',
            '    <form action="' + escapeHtml(deleteUrl) + '" method="POST" class="d-inline-block js-opening-inventory-delete-form" data-opening-inventory-id="' + escapeHtml(id) + '">',
            '      <input type="hidden" name="_token" value="' + escapeHtml(document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '') + '">',
            '      <input type="hidden" name="_method" value="DELETE">',
            '      <button type="submit" class="btn btn-sm btn-danger">Delete</button>',
            '    </form>',
            '  </td>',
            '</tr>'
        ].join('');
    }

    function badRowHtml(record, index) {
        return [
            '<tr data-bad-product-id="' + escapeHtml(record.id || record.local_id || index + 1) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td>',
            '    <strong>' + escapeHtml(productDisplayName(record)) + '</strong>',
            '    <div class="mt-1"><small class="text-muted">Supplier: ' + escapeHtml(record.supplier || 'N/A') + '</small></div>',
            '  </td>',
            '  <td>' + escapeHtml(formatNumber(record.quantity)) + '</td>',
            '  <td>' + escapeHtml(displayDateTime(record.created_at)) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderOpeningOfflineTable(records) {
        var table = document.getElementById('openingInventoryTable');
        var tbody = table ? table.querySelector('tbody') : null;

        if (!table || !tbody) {
            return Promise.resolve([]);
        }

        if (global.jQuery && global.jQuery.fn && global.jQuery.fn.DataTable && global.jQuery.fn.DataTable.isDataTable('#openingInventoryTable')) {
            global.jQuery('#openingInventoryTable').DataTable().destroy();
        }

        return Promise.all(records.map(enrichOpeningRecord)).then(function (enriched) {
            var filtered = openingOfflineFiltered(enriched);

            if (!filtered.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No offline opening inventory records found.</td></tr>';
                return filtered;
            }

            tbody.innerHTML = filtered.map(function (record, index) {
                return openingRowHtml(record, index);
            }).join('');

            return filtered;
        });
    }

    function renderBadOfflineTable(records) {
        var table = document.getElementById('badProductsTable');
        var tbody = table ? table.querySelector('tbody') : null;

        if (!table || !tbody) {
            return Promise.resolve([]);
        }

        if (global.jQuery && global.jQuery.fn && global.jQuery.fn.DataTable && global.jQuery.fn.DataTable.isDataTable('#badProductsTable')) {
            global.jQuery('#badProductsTable').DataTable().destroy();
        }

        return Promise.all(records.map(enrichBadRecord)).then(function (enriched) {
            var filtered = badOfflineFiltered(enriched);

            if (!filtered.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No offline bad product records found.</td></tr>';
                return filtered;
            }

            tbody.innerHTML = filtered.map(function (record, index) {
                return badRowHtml(record, index);
            }).join('');

            return filtered;
        });
    }

    function initOpeningOnlineTable() {
        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return Promise.resolve(null);
        }

        var config = getConfig();
        var selector = '#openingInventoryTable';

        if (global.jQuery.fn.DataTable.isDataTable(selector)) {
            openingTableInstance = global.jQuery(selector).DataTable();
            return Promise.resolve(openingTableInstance);
        }

        openingTableInstance = global.jQuery(selector).DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: config.openingInventoryIndexRoute || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.name = getValue('name');
                    d.startDate = getValue('startDate');
                    d.endDate = getValue('endDate');
                },
                dataSrc: function (response) {
                    var rows = Array.isArray(response && response.data) ? response.data : [];
                    cacheRows(getOpeningRepo(), rows, normalizeOpeningServerRow).catch(function () {});
                    return rows;
                },
                error: function () {
                    notify('error', 'Opening inventory unavailable', 'Unable to load opening inventory from the server.');
                }
            },
            columns: [
                { data: 'id', name: 'id', render: function (d) { return d; } },
                {
                    data: 'product',
                    name: 'product',
                    render: function (d, type, row) {
                        var qty = typeof row.opening_quantity !== 'undefined' ? row.opening_quantity : 0;
                        return '<strong>' + escapeHtml(d || row.product_name || '') + '</strong><div><small class="text-muted">Stock: ' + escapeHtml(formatNumber(qty)) + '</small></div>';
                    }
                },
                { data: 'opening_quantity', name: 'opening_quantity', render: function (d) { return formatNumber(d); } },
                { data: 'opening_date', name: 'opening_date' },
                { data: 'created_at', name: 'created_at' },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function (d) {
                        var editUrl = openingInventoryUrlBase() ? openingInventoryUrlBase() + '/' + d + '/edit' : '#';
                        var deleteUrl = openingInventoryUrlBase() ? openingInventoryUrlBase() + '/' + d : '#';
                        return [
                            '<a href="' + escapeHtml(editUrl) + '" class="btn btn-sm btn-primary me-2">Edit</a>',
                            '<form action="' + escapeHtml(deleteUrl) + '" method="POST" class="d-inline-block js-opening-inventory-delete-form" data-opening-inventory-id="' + escapeHtml(d) + '">',
                            '  <input type="hidden" name="_token" value="' + escapeHtml(document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '') + '">',
                            '  <input type="hidden" name="_method" value="DELETE">',
                            '  <button type="submit" class="btn btn-sm btn-danger">Delete</button>',
                            '</form>'
                        ].join('');
                    }
                }
            ]
        });

        return Promise.resolve(openingTableInstance);
    }

    function initBadOnlineTable() {
        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return Promise.resolve(null);
        }

        var config = getConfig();
        var selector = '#badProductsTable';

        if (global.jQuery.fn.DataTable.isDataTable(selector)) {
            badTableInstance = global.jQuery(selector).DataTable();
            return Promise.resolve(badTableInstance);
        }

        badTableInstance = global.jQuery(selector).DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: config.badProductsIndexRoute || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.name = getValue('name');
                    d.supplier = getValue('supplier');
                    d.startDate = getValue('startDate');
                    d.endDate = getValue('endDate');
                },
                dataSrc: function (response) {
                    var rows = Array.isArray(response && response.data) ? response.data : [];
                    cacheRows(getBadRepo(), rows, normalizeBadServerRow).catch(function () {});
                    return rows;
                },
                error: function () {
                    notify('error', 'Bad products unavailable', 'Unable to load bad products from the server.');
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'quantity', name: 'quantity', render: function (d) { return formatNumber(d); } },
                { data: 'created_at', name: 'created_at' }
            ]
        });

        return Promise.resolve(badTableInstance);
    }

    function filterOpeningOffline() {
        var repo = getOpeningRepo();

        if (!repo) {
            return Promise.resolve([]);
        }

        return repo.getAll().then(function (records) {
            return renderOpeningOfflineTable(records || []);
        });
    }

    function filterBadOffline() {
        var repo = getBadRepo();

        if (!repo) {
            return Promise.resolve([]);
        }

        return repo.getAll().then(function (records) {
            return renderBadOfflineTable(records || []);
        });
    }

    function applyActivePage() {
        if (isOpeningIndexPage()) {
            if (isOnline()) {
                return initOpeningOnlineTable();
            }

            return filterOpeningOffline();
        }

        if (isBadIndexPage()) {
            if (isOnline()) {
                return initBadOnlineTable();
            }

            return filterBadOffline();
        }

        return Promise.resolve(null);
    }

    function findOpeningRecordByIdentifier(identifier) {
        var repo = getOpeningRepo();

        if (!repo) {
            return Promise.resolve(null);
        }

        return repo.findById(identifier);
    }

    function currentFormData(form) {
        var productSelect = form.querySelector('[name="product_id"]');
        var openingQuantity = form.querySelector('[name="opening_quantity"]');
        var openingDate = form.querySelector('[name="opening_date"]');
        var productId = productSelect ? productSelect.value : '';
        var productName = productSelect && productSelect.options && productSelect.selectedIndex >= 0
            ? productSelect.options[productSelect.selectedIndex].textContent.trim()
            : '';

        return {
            product_id: productId,
            product_name: productName,
            opening_quantity: toNumber(openingQuantity ? openingQuantity.value : 0),
            opening_date: openingDate ? openingDate.value : ''
        };
    }

    function buildInventoryLog(type, product, delta, referenceId, record) {
        return {
            key: type + ':' + uuid(),
            code: type,
            name: productDisplayName(product) || (record && record.product_name) || '',
            type: 'inventory_adjustment',
            value: JSON.stringify({
                source: type,
                product_id: record && record.product_id ? record.product_id : (product ? product.id : null),
                product_name: productDisplayName(product) || (record && record.product_name) || '',
                delta: delta,
                reference_id: referenceId || null,
                quantity: record && typeof record.opening_quantity !== 'undefined' ? record.opening_quantity : (record && typeof record.quantity !== 'undefined' ? record.quantity : null),
                current_quantity: product && typeof product.quantity !== 'undefined' ? product.quantity : null
            }),
            depID: getDepartmentId(),
            synced: false,
            sync_status: 'pending',
            local_action: 'create'
        };
    }

    function updateProductQuantity(productId, delta) {
        var repo = getProductRepo();

        if (!repo || !productId || !delta) {
            return Promise.resolve(null);
        }

        return repo.findById(productId).then(function (product) {
            if (!product) {
                throw new Error('Product not found in the local database.');
            }

            var nextQuantity = toNumber(product.quantity) + toNumber(delta);
            if (nextQuantity < 0) {
                throw new Error('Local stock cannot go below zero.');
            }

            return repo.update(product.id || product.local_id || product.server_id || productId, {
                quantity: nextQuantity
            });
        });
    }

    function saveOpeningInventoryOffline(form, mode) {
        var repo = getOpeningRepo();
        var productRepo = getProductRepo();
        var storeRepo = getStoreRepo();
        var data = currentFormData(form);
        var recordId = mode === 'update' ? parseActionId(form.getAttribute('action')) : null;

        if (!repo || !productRepo || !storeRepo) {
            return Promise.reject(new Error('Inventory repositories are not ready yet.'));
        }

        if (!data.product_id || !data.opening_quantity || !data.opening_date) {
            return Promise.reject(new Error('Please complete the opening inventory form.'));
        }

        return productRepo.findById(data.product_id).then(function (product) {
            if (!product) {
                throw new Error('The selected product is not cached locally yet.');
            }

            if (mode === 'update' && recordId) {
                return findOpeningRecordByIdentifier(recordId).then(function (existing) {
                    var previousQuantity = existing ? toNumber(existing.opening_quantity) : 0;
                    var previousProductId = existing ? (existing.product_id || existing.proID) : data.product_id;
                    var deltaNew = toNumber(data.opening_quantity);
                    var actions = [];

                    if (String(previousProductId) === String(data.product_id)) {
                        actions.push(updateProductQuantity(data.product_id, deltaNew - previousQuantity));
                    } else {
                        if (previousProductId) {
                            actions.push(updateProductQuantity(previousProductId, -previousQuantity));
                        }
                        actions.push(updateProductQuantity(data.product_id, deltaNew));
                    }

                    return Promise.all(actions).then(function () {
                        var payload = {
                            product_id: data.product_id,
                            product_name: productDisplayName(product),
                            opening_quantity: data.opening_quantity,
                            opening_date: data.opening_date,
                            depID: getDepartmentId(),
                            synced: false,
                            sync_status: 'pending',
                            local_action: 'update'
                        };

                        if (existing && existing.local_id) {
                            payload.local_id = existing.local_id;
                        }

                        if (existing && typeof existing.server_id !== 'undefined') {
                            payload.server_id = existing.server_id;
                        }

                        var targetId = recordId || (existing && (existing.id || existing.local_id || existing.server_id));
                        var savePromise = targetId ? repo.update(targetId, payload) : Promise.resolve(null);

                        if (!targetId || !existing) {
                            savePromise = repo.create(payload);
                        }

                        return savePromise.then(function (saved) {
                            return storeRepo.create(buildInventoryLog('opening_inventory_update', product, toNumber(data.opening_quantity) - previousQuantity, saved ? (saved.id || saved.local_id) : recordId, payload)).then(function () {
                                return saved;
                            });
                        });
                    });
                });
            }

            return repo.create({
                product_id: data.product_id,
                product_name: productDisplayName(product),
                opening_quantity: data.opening_quantity,
                opening_date: data.opening_date,
                depID: getDepartmentId(),
                synced: false,
                sync_status: 'pending',
                local_action: 'create'
            }).then(function (saved) {
                return updateProductQuantity(data.product_id, data.opening_quantity).then(function () {
                    return storeRepo.create(buildInventoryLog('opening_inventory', product, toNumber(data.opening_quantity), saved && (saved.id || saved.local_id), saved)).then(function () {
                        return saved;
                    });
                });
            });
        });
    }

    function deleteOpeningInventoryOffline(form) {
        var repo = getOpeningRepo();
        var productRepo = getProductRepo();
        var storeRepo = getStoreRepo();
        var identifier = parseActionId(form.getAttribute('action'));

        if (!repo || !productRepo || !storeRepo) {
            return Promise.reject(new Error('Inventory repositories are not ready yet.'));
        }

        return findOpeningRecordByIdentifier(identifier).then(function (existing) {
            if (!existing) {
                throw new Error('Opening inventory record not found locally.');
            }

            return productRepo.findById(existing.product_id).then(function (product) {
                var quantity = toNumber(existing.opening_quantity);
                var productId = existing.product_id;

                return repo.delete(identifier).then(function (saved) {
                    return updateProductQuantity(productId, -quantity).then(function () {
                        return storeRepo.create(buildInventoryLog('opening_inventory_delete', product || { name: existing.product_name }, -quantity, identifier, existing)).then(function () {
                            return saved;
                        });
                    });
                });
            });
        });
    }

    function renderProductDropdown(records) {
        var dropdown = document.getElementById('productDropdown');

        if (!dropdown) {
            return;
        }

        if (!records.length) {
            dropdown.innerHTML = '<div class="dropdown-item text-center text-muted">No results found</div>';
            dropdown.style.display = 'block';
            return;
        }

        dropdown.innerHTML = records.map(function (record) {
            return [
                '<a href="#" class="dropdown-item js-inventory-product-row" ',
                'data-product-id="' + escapeHtml(record.id || record.local_id || '') + '" ',
                'data-product-name="' + escapeHtml(productDisplayName(record)) + '" ',
                'data-product-supplier="' + escapeHtml(record.supplier || '') + '">',
                escapeHtml(productDisplayName(record)),
                record.supplier ? ' <small class="text-muted">(' + escapeHtml(record.supplier) + ')</small>' : '',
                '</a>'
            ].join('');
        }).join('');

        dropdown.style.display = 'block';
    }

    function searchProductsOffline(query) {
        var repo = getProductRepo();
        var needle = String(query || '').trim().toLowerCase();

        if (!repo) {
            return Promise.resolve([]);
        }

        return repo.search(needle).then(function (records) {
            return (records || []).filter(function (record) {
                return !needle ||
                    matchesKeyword(record.name, needle) ||
                    matchesKeyword(record.sku_code, needle) ||
                    matchesKeyword(record.sku, needle) ||
                    matchesKeyword(record.barcode, needle);
            });
        });
    }

    function selectProduct(record) {
        var input = document.getElementById('inventorySearch');
        var hidden = document.getElementById('proID');
        var dropdown = document.getElementById('productDropdown');

        if (input) {
            input.value = productDisplayName(record);
        }

        if (hidden) {
            hidden.value = record.id || record.local_id || '';
        }

        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }

    function bindBadProductSearch() {
        document.addEventListener('input', function (event) {
            var input = event.target;

            if (!isBadCreatePage() || !input || input.id !== 'inventorySearch') {
                return;
            }

            var query = String(input.value || '').trim();
            var dropdown = document.getElementById('productDropdown');

            if (!dropdown) {
                return;
            }

            if (query.length < 2) {
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
                return;
            }

            dropdown.innerHTML = '<div class="dropdown-item text-center"><i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...</div>';
            dropdown.style.display = 'block';

            if (isOnline() && global.axios && getConfig().productSearchRoute) {
                global.axios.get(getConfig().productSearchRoute + '?query=' + encodeURIComponent(query))
                    .then(function (response) {
                        var records = Array.isArray(response && response.data) ? response.data : [];
                        renderProductDropdown(records);
                    })
                    .catch(function () {
                        dropdown.innerHTML = '<div class="dropdown-item text-center text-danger">Search failed</div>';
                    });
                return;
            }

            searchProductsOffline(query).then(function (records) {
                renderProductDropdown(records);
            }).catch(function () {
                dropdown.innerHTML = '<div class="dropdown-item text-center text-danger">Search failed</div>';
            });
        }, true);

        document.addEventListener('click', function (event) {
            if (!isBadCreatePage()) {
                return;
            }

            var item = event.target.closest('#productDropdown .js-inventory-product-row');
            var input = document.getElementById('inventorySearch');
            var dropdown = document.getElementById('productDropdown');

            if (!item) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            selectProduct({
                id: item.getAttribute('data-product-id'),
                name: item.getAttribute('data-product-name'),
                supplier: item.getAttribute('data-product-supplier')
            });

            if (input) {
                input.focus();
            }

            if (dropdown) {
                dropdown.style.display = 'none';
            }
        }, true);
    }

    function buildBadProductLog(product, quantity, record) {
        return {
            key: 'bad_product:' + uuid(),
            code: 'bad_products',
            name: productDisplayName(product) || (record && record.product_name) || '',
            type: 'inventory_adjustment',
            value: JSON.stringify({
                source: 'bad_products',
                product_id: record && record.proID ? record.proID : (product ? product.id : null),
                product_name: productDisplayName(product) || (record && record.product_name) || '',
                quantity: quantity,
                reason: record && record.reason ? record.reason : '',
                current_quantity: product && typeof product.quantity !== 'undefined' ? product.quantity : null
            }),
            depID: getDepartmentId(),
            synced: false,
            sync_status: 'pending',
            local_action: 'create'
        };
    }

    function saveBadProductOffline(form) {
        var repo = getBadRepo();
        var productRepo = getProductRepo();
        var storeRepo = getStoreRepo();
        var productId = getValue('proID');
        var quantity = toNumber(getValue('qty'));
        var reason = getValue('reason');

        if (!repo || !productRepo || !storeRepo) {
            return Promise.reject(new Error('Inventory repositories are not ready yet.'));
        }

        if (!productId || !quantity) {
            return Promise.reject(new Error('Please select a product and quantity.'));
        }

        return productRepo.findById(productId).then(function (product) {
            if (!product) {
                throw new Error('The selected product is not cached locally yet.');
            }

            var nextQuantity = toNumber(product.quantity) - quantity;
            if (nextQuantity < 0) {
                throw new Error('Bad product quantity exceeds the available local stock.');
            }

            return repo.create({
                proID: productId,
                product_name: productDisplayName(product),
                supplier: product.supplier || '',
                quantity: quantity,
                reason: reason || 'Bad Product',
                reported_date: nowIso(),
                depID: getDepartmentId(),
                synced: false,
                sync_status: 'pending',
                local_action: 'create'
            }).then(function (saved) {
                return productRepo.update(product.id || product.local_id || product.server_id || productId, {
                    quantity: nextQuantity
                }).then(function () {
                    return storeRepo.create(buildBadProductLog(product, quantity, saved)).then(function () {
                        return saved;
                    });
                });
            });
        });
    }

    function bindOpeningInventoryForms() {
        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!form) {
                return;
            }

            if (form.matches('form.js-opening-inventory-delete-form') || /opening_inventory\/opening_inventory/.test(form.getAttribute('action') || '')) {
                if (!isOpeningIndexPage()) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                var proceed = function () {
                    if (isOnline()) {
                        form.submit();
                        return;
                    }

                    deleteOpeningInventoryOffline(form).then(function () {
                        showToast('success', 'Opening inventory record removed locally and queued for synchronization.');
                        applyActivePage();
                    }).catch(function (error) {
                        showToast('error', error && error.message ? error.message : 'Could not delete the opening inventory record locally.');
                    });
                };

                if (global.Swal && typeof global.Swal.fire === 'function') {
                    global.Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will restore the stock quantity tied to this opening inventory record.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            proceed();
                        }
                    });
                } else {
                    proceed();
                }

                return;
            }

            if (isOpeningCreatePage() && form.matches('form[data-opening-inventory-form="create"]')) {
                if (isOnline()) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                saveOpeningInventoryOffline(form, 'create').then(function () {
                    showToast('success', 'Opening inventory saved offline and queued for synchronization.');
                    form.reset();
                    applyActivePage();
                }).catch(function (error) {
                    showToast('error', error && error.message ? error.message : 'Could not save the opening inventory locally.');
                });
                return;
            }

            if (isOpeningUpdatePage() && form.matches('form[data-opening-inventory-form="update"]')) {
                if (isOnline()) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                saveOpeningInventoryOffline(form, 'update').then(function () {
                    showToast('success', 'Opening inventory updated offline and queued for synchronization.');
                    applyActivePage();
                }).catch(function (error) {
                    showToast('error', error && error.message ? error.message : 'Could not update the opening inventory locally.');
                });
            }
        }, true);
    }

    function bindBadProductForms() {
        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!isBadCreatePage() || !form || !form.matches('form[data-bad-product-form="create"]')) {
                return;
            }

            if (isOnline()) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            saveBadProductOffline(form).then(function () {
                showToast('success', 'Bad product saved offline and queued for synchronization.');
                form.reset();
                var dropdown = document.getElementById('productDropdown');
                if (dropdown) {
                    dropdown.style.display = 'none';
                    dropdown.innerHTML = '';
                }
            }).catch(function (error) {
                showToast('error', error && error.message ? error.message : 'Could not save the bad product locally.');
            });
        }, true);
    }

    function bindFilters() {
        var searchButton = document.getElementById('searchBtn');

        if (searchButton && searchButton.dataset.inventoryOfflineBound !== 'true') {
            searchButton.dataset.inventoryOfflineBound = 'true';
            searchButton.addEventListener('click', function (event) {
                event.preventDefault();

                if (isOpeningIndexPage()) {
                    if (isOnline() && openingTableInstance && typeof openingTableInstance.draw === 'function') {
                        openingTableInstance.draw();
                    } else {
                        filterOpeningOffline();
                    }
                    return;
                }

                if (isBadIndexPage()) {
                    if (isOnline() && badTableInstance && typeof badTableInstance.draw === 'function') {
                        badTableInstance.draw();
                    } else {
                        filterBadOffline();
                    }
                }
            });
        }

        ['name', 'supplier', 'startDate', 'endDate'].forEach(function (fieldId) {
            var field = document.getElementById(fieldId);

            if (!field || field.dataset.inventoryOfflineBound === 'true') {
                return;
            }

            field.dataset.inventoryOfflineBound = 'true';
            field.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                if (isOpeningIndexPage()) {
                    if (isOnline() && openingTableInstance && typeof openingTableInstance.draw === 'function') {
                        openingTableInstance.draw();
                    } else {
                        filterOpeningOffline();
                    }
                }

                if (isBadIndexPage()) {
                    if (isOnline() && badTableInstance && typeof badTableInstance.draw === 'function') {
                        badTableInstance.draw();
                    } else {
                        filterBadOffline();
                    }
                }
            });
        });
    }

    function bindConnectivity() {
        if (bindConnectivity.bound) {
            return;
        }

        bindConnectivity.bound = true;

        global.addEventListener('online', function () {
            applyActivePage();
        });

        global.addEventListener('offline', function () {
            applyActivePage();
        });

        global.addEventListener('offline-queue:changed', function () {
            if (!isOnline()) {
                applyActivePage();
            }
        });
    }

    function recordReturnedCreditOffline(payload) {
        var repo = getReturnedRepo();
        var productRepo = getProductRepo();
        var storeRepo = getStoreRepo();
        var data = Object.assign({}, payload || {});

        if (!repo || !productRepo || !storeRepo) {
            return Promise.reject(new Error('Inventory repositories are not ready yet.'));
        }

        if (!data.reference) {
            data.reference = uuid();
        }

        if (data.product_id) {
            return productRepo.findById(data.product_id).then(function (product) {
                var quantity = toNumber(data.quantity);

                return repo.create(Object.assign({}, data, {
                    product_name: data.product_name || productDisplayName(product),
                    depID: data.depID || getDepartmentId(),
                    synced: false,
                    sync_status: 'pending',
                    local_action: 'create'
                })).then(function (saved) {
                    if (product && quantity) {
                        return updateProductQuantity(product.id || product.local_id || product.server_id || data.product_id, quantity).then(function () {
                            return storeRepo.create(buildInventoryLog('returned_credit', product, quantity, saved && (saved.id || saved.local_id), saved)).then(function () {
                                return saved;
                            });
                        });
                    }

                    return storeRepo.create(buildInventoryLog('returned_credit', product || { name: data.product_name || '' }, quantity, saved && (saved.id || saved.local_id), saved)).then(function () {
                        return saved;
                    });
                });
            });
        }

        return repo.create(Object.assign({}, data, {
            depID: data.depID || getDepartmentId(),
            synced: false,
            sync_status: 'pending',
            local_action: 'create'
        })).then(function (saved) {
            return storeRepo.create(buildInventoryLog('returned_credit', { name: data.product_name || '' }, toNumber(data.quantity), saved && (saved.id || saved.local_id), saved)).then(function () {
                return saved;
            });
        });
    }

    function boot() {
        if (booted) {
            return;
        }

        booted = true;

        bindConnectivity();
        bindFilters();
        bindOpeningInventoryForms();
        bindBadProductForms();
        bindBadProductSearch();

        applyActivePage();
    }

    namespace.boot = boot;
    namespace.applyActivePage = applyActivePage;
    namespace.recordReturnedCreditOffline = recordReturnedCreditOffline;
    namespace.getRepository = getRepository;

    global.StoreManagementInventoryModuleClass = {
        boot: boot
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
