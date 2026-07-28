(function (global) {
    'use strict';

    var namespace = global.StoreManagementProductModule = global.StoreManagementProductModule || {};
    var repositoryInstance = null;
    var offlineIndexState = {
        active: false,
        records: []
    };
    var currentMode = null;
    var onlineTableInstance = null;
    var moduleStarted = false;

    function hasRepository() {
        return typeof global.ProductRepository === 'function';
    }

    function getRepository() {
        if (!hasRepository()) {
            return null;
        }

        if (!repositoryInstance) {
            repositoryInstance = new global.ProductRepository();
        }

        return repositoryInstance;
    }

    function isIndexPage() {
        return Boolean(document.getElementById('purchaseDate'));
    }

    function isCreatePage() {
        return Boolean(document.querySelector('form[data-product-form="create"]'));
    }

    function isUpdatePage() {
        return Boolean(document.querySelector('form[data-product-form="update"]'));
    }

    function isOffline() {
        return navigator.onLine === false;
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

    function formatCurrency(value) {
        return normalizeNumber(value).toFixed(2);
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

        if (!container || container.querySelector('[data-product-offline-banner]')) {
            return;
        }

        var banner = document.createElement('div');
        banner.className = 'alert alert-warning mb-3';
        banner.setAttribute('data-product-offline-banner', 'true');
        banner.innerHTML = '<strong>Offline mode:</strong> ' + escapeHtml(message);

        var firstCard = container.querySelector('.card');
        if (firstCard && firstCard.parentNode) {
            firstCard.parentNode.insertBefore(banner, firstCard);
            return;
        }

        container.insertBefore(banner, container.firstChild);
    }

    function removeBanner() {
        var banner = document.querySelector('[data-product-offline-banner]');

        if (banner && banner.parentNode) {
            banner.parentNode.removeChild(banner);
        }
    }

    function getOfflineDepartmentId() {
        if (global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.departmentId !== 'undefined') {
            return global.__OFFLINE_ENGINE_CONFIG__.departmentId;
        }

        return null;
    }

    function normalizeProductRecord(record) {
        var source = Object.assign({}, record || {});

        if (!source.name && source.product_name) {
            source.name = source.product_name;
        }

        if (!source.sku_code && source.sku) {
            source.sku_code = source.sku;
        }

        if (!source.sku && source.sku_code) {
            source.sku = source.sku_code;
        }

        return source;
    }

    function getValue(id) {
        var node = document.getElementById(id);
        return node ? String(node.value == null ? '' : node.value).trim() : '';
    }

    function buildFilterState() {
        return {
            name: getValue('name').toLowerCase(),
            sku_code: getValue('sku_code').toLowerCase(),
            type: getValue('type').toLowerCase(),
            supplier: getValue('supplier').toLowerCase(),
            quantity: getValue('quantity').toLowerCase()
        };
    }

    function matchesFilter(value, keyword) {
        if (!keyword) {
            return true;
        }

        return String(value == null ? '' : value).toLowerCase().indexOf(keyword) !== -1;
    }

    function prepareOfflineRecord(record) {
        var normalized = normalizeProductRecord(record);

        return {
            id: normalized.id,
            local_id: normalized.local_id || null,
            server_id: typeof normalized.server_id !== 'undefined' ? normalized.server_id : null,
            name: normalized.name || '',
            sku_code: normalized.sku_code || '',
            type: normalized.type || '',
            status: typeof normalized.status !== 'undefined' ? normalized.status : 1,
            quantity: typeof normalized.quantity !== 'undefined' ? normalized.quantity : '',
            actual_price: normalized.actual_price || '',
            selling_price: normalized.selling_price || '',
            unit: normalized.unit || '',
            supplier: normalized.supplier || '',
            depID: typeof normalized.depID !== 'undefined' ? normalized.depID : null,
            info: normalized.info || '',
            created_at: normalized.created_at || normalized.updated_at || '',
            updated_at: normalized.updated_at || normalized.created_at || '',
            synced: typeof normalized.synced === 'boolean' ? normalized.synced : Boolean(normalized.server_id),
            sync_status: normalized.sync_status || (normalized.synced ? 'synced' : 'pending'),
            local_action: normalized.local_action || (normalized.synced ? 'import' : 'create'),
            is_deleted: Boolean(normalized.is_deleted),
            deleted_at: normalized.deleted_at || null
        };
    }

    function buildOfflineRow(record, index) {
        var productId = record.id || record.local_id || index + 1;
        var statusLabel = normalizeNumber(record.status) === 1 ? 'Active' : 'Disabled';
        var statusClass = normalizeNumber(record.status) === 1 ? 'bg-success' : 'bg-danger';
        var syncLabel = record.sync_status === 'failed'
            ? 'Failed'
            : (record.sync_status === 'synced' ? 'Synced' : 'Pending');
        var syncClass = record.sync_status === 'failed'
            ? 'bg-danger'
            : (record.sync_status === 'synced' ? 'bg-primary' : 'bg-warning text-dark');

        return [
            '<tr data-product-row="' + escapeHtml(productId) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td>' + escapeHtml(record.name || '') + '</td>',
            '  <td>' + escapeHtml(record.sku_code || '') + '</td>',
            '  <td>' + escapeHtml(record.type || '') + '</td>',
            '  <td>',
            '    <span class="badge ' + statusClass + '">' + escapeHtml(statusLabel) + '</span>',
            '    <div class="mt-1"><span class="badge ' + syncClass + '">' + escapeHtml(syncLabel) + '</span></div>',
            '  </td>',
            '  <td>' + escapeHtml(record.quantity === '' ? '' : record.quantity) + '</td>',
            '  <td>' + escapeHtml(formatCurrency(record.actual_price)) + '</td>',
            '  <td>' + escapeHtml(formatCurrency(record.selling_price)) + '</td>',
            '  <td>' + escapeHtml(record.created_at || '') + '</td>',
            '  <td>',
            '    <button type="button" class="btn btn-rounded btn-sm bg-outline-light me-2 js-product-offline-edit" data-product-id="' + escapeHtml(productId) + '">',
            '      <i class="fas fa-edit"></i>',
            '    </button>',
            '    <button type="button" class="btn btn-rounded btn-sm bg-outline-light js-product-offline-delete" data-product-id="' + escapeHtml(productId) + '">',
            '      <i class="fas fa-trash"></i>',
            '    </button>',
            '  </td>',
            '</tr>'
        ].join('');
    }

    function renderOfflineRows(records) {
        var table = document.getElementById('purchaseDate');
        var tbody = table ? table.querySelector('tbody') : null;

        if (!table || !tbody) {
            return;
        }

        offlineIndexState.records = records.slice();
        offlineIndexState.active = true;

        if (global.jQuery && global.jQuery.fn && global.jQuery.fn.DataTable && global.jQuery.fn.DataTable.isDataTable('#purchaseDate')) {
            global.jQuery('#purchaseDate').DataTable().destroy();
        }

        onlineTableInstance = null;
        currentMode = 'offline';

        if (!records.length) {
            tbody.innerHTML = [
                '<tr>',
                '  <td colspan="10" class="text-center text-muted py-4">No offline product records found.</td>',
                '</tr>'
            ].join('');
            return;
        }

        tbody.innerHTML = records.map(function (record, index) {
            return buildOfflineRow(prepareOfflineRecord(record), index);
        }).join('');
    }

    function cacheServerRows(rows) {
        var repo = getRepository();

        if (!repo || !rows || !rows.length) {
            return Promise.resolve([]);
        }

        if (typeof repo.cacheFromServerRows === 'function') {
            return repo.cacheFromServerRows(rows);
        }

        return repo.cacheMany(rows.map(function (record) {
            var normalized = Object.assign({}, record || {});

            if (typeof normalized.server_id === 'undefined') {
                normalized.server_id = typeof normalized.id !== 'undefined' ? normalized.id : null;
            }

            if (typeof normalized.id !== 'undefined') {
                delete normalized.id;
            }

            return normalized;
        }));
    }

    function applyOfflineFilters() {
        var repo = getRepository();

        if (!repo) {
            return Promise.resolve([]);
        }

        var filters = buildFilterState();

        return repo.getAll().then(function (records) {
            var filtered = records.filter(function (record) {
                var normalized = prepareOfflineRecord(record);

                return matchesFilter(normalized.name, filters.name) &&
                    matchesFilter(normalized.sku_code, filters.sku_code) &&
                    matchesFilter(normalized.type, filters.type) &&
                    matchesFilter(normalized.supplier, filters.supplier) &&
                    matchesFilter(normalized.quantity, filters.quantity);
            });

            renderOfflineRows(filtered);
            return filtered;
        });
    }

    function syncRecordToOfflineList(savedRecord) {
        var normalized = prepareOfflineRecord(savedRecord);
        var index = offlineIndexState.records.findIndex(function (record) {
            var candidate = prepareOfflineRecord(record);
            return String(candidate.id || candidate.local_id) === String(normalized.id || normalized.local_id);
        });

        if (index >= 0) {
            offlineIndexState.records[index] = normalized;
        } else {
            offlineIndexState.records.unshift(normalized);
        }
    }

    function removeRecordFromOfflineList(identifier) {
        offlineIndexState.records = offlineIndexState.records.filter(function (record) {
            var candidate = prepareOfflineRecord(record);
            return String(candidate.id || candidate.local_id) !== String(identifier);
        });
    }

    function ensureOfflineEditModal() {
        var existing = document.getElementById('product-offline-edit-modal');

        if (existing) {
            return existing;
        }

        var modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'product-offline-edit-modal';
        modal.tabIndex = -1;
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = [
            '<div class="modal-dialog modal-lg modal-dialog-centered">',
            '  <div class="modal-content">',
            '    <div class="modal-header">',
            '      <h5 class="modal-title">Edit Product Offline</h5>',
            '      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>',
            '    </div>',
            '    <form id="product-offline-edit-form" data-product-form="offline-update">',
            '      <input type="hidden" name="product_id" id="product-offline-id">',
            '      <div class="modal-body">',
            '        <div class="row g-3">',
            '          <div class="col-md-6">',
            '            <label class="form-label">Product Name</label>',
            '            <input type="text" class="form-control" name="name" id="product-offline-name" required>',
            '          </div>',
            '          <div class="col-md-3">',
            '            <label class="form-label">Type</label>',
            '            <select class="form-select" name="type" id="product-offline-type" required>',
            '              <option value="Inventory">Inventory</option>',
            '              <option value="Service">Service</option>',
            '              <option value="Property">Property</option>',
            '            </select>',
            '          </div>',
            '          <div class="col-md-3">',
            '            <label class="form-label">Status</label>',
            '            <select class="form-select" name="status" id="product-offline-status" required>',
            '              <option value="1">Active</option>',
            '              <option value="0">Disabled</option>',
            '            </select>',
            '          </div>',
            '          <div class="col-md-6">',
            '            <label class="form-label">Actual Price</label>',
            '            <input type="text" class="form-control" name="actual_price" id="product-offline-actual-price" required>',
            '          </div>',
            '          <div class="col-md-6">',
            '            <label class="form-label">Selling Price</label>',
            '            <input type="text" class="form-control" name="selling_price" id="product-offline-selling-price" required>',
            '          </div>',
            '          <div class="col-12">',
            '            <label class="form-label">Description</label>',
            '            <textarea class="form-control" name="info" id="product-offline-info" rows="4"></textarea>',
            '          </div>',
            '        </div>',
            '      </div>',
            '      <div class="modal-footer">',
            '        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>',
            '        <button type="submit" class="btn btn-primary">Save Changes</button>',
            '      </div>',
            '    </form>',
            '  </div>',
            '</div>'
        ].join('');

        document.body.appendChild(modal);
        return modal;
    }

    function openOfflineEditModal(record) {
        var modalElement = ensureOfflineEditModal();
        var modal = global.bootstrap && global.bootstrap.Modal
            ? global.bootstrap.Modal.getOrCreateInstance(modalElement)
            : null;

        document.getElementById('product-offline-id').value = record.id || record.local_id || '';
        document.getElementById('product-offline-name').value = record.name || '';
        document.getElementById('product-offline-type').value = record.type || 'Inventory';
        document.getElementById('product-offline-status').value = String(normalizeNumber(record.status) === 1 ? 1 : 0);
        document.getElementById('product-offline-actual-price').value = record.actual_price || '';
        document.getElementById('product-offline-selling-price').value = record.selling_price || '';
        document.getElementById('product-offline-info').value = record.info || '';
        modalElement.setAttribute('data-product-id', record.id || record.local_id || '');

        if (modal) {
            modal.show();
            return;
        }

        modalElement.style.display = 'block';
        modalElement.classList.add('show');
    }

    function closeOfflineEditModal() {
        var modalElement = document.getElementById('product-offline-edit-modal');
        var modal = modalElement && global.bootstrap && global.bootstrap.Modal
            ? global.bootstrap.Modal.getInstance(modalElement)
            : null;

        if (modal) {
            modal.hide();
            return;
        }

        if (modalElement) {
            modalElement.classList.remove('show');
            modalElement.style.display = 'none';
        }
    }

    function resolveFormIdentifier(form) {
        if (!form) {
            return '';
        }

        if (form.getAttribute('data-product-id')) {
            return form.getAttribute('data-product-id');
        }

        var hidden = form.querySelector('[name="product_id"], [name="id"]');
        if (hidden && hidden.value) {
            return hidden.value;
        }

        try {
            var actionUrl = new URL(form.action, global.location.origin);
            var segments = actionUrl.pathname.split('/').filter(Boolean);
            return segments.length ? segments[segments.length - 1] : '';
        } catch (error) {
            return '';
        }
    }

    function buildCreatePayload(form) {
        var formData = global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.formDataToObject === 'function'
            ? global.StoreManagementOfflineDatabase.formDataToObject(new FormData(form))
            : {};
        var name = String(formData.name || '').trim();
        var suffix = Math.floor(100 + (Math.random() * 900));

        return {
            name: name,
            sku_code: formData.sku_code || (name ? name + '-' + suffix : 'INV-' + suffix),
            type: formData.type || 'Inventory',
            unit: formData.unit || 'Others',
            actual_price: formData.actual_price || '',
            selling_price: formData.selling_price || '',
            supplier: formData.supplier || '',
            info: formData.info || '',
            status: 1,
            quantity: formData.quantity || 0,
            depID: typeof formData.depID !== 'undefined' && formData.depID !== '' ? formData.depID : getOfflineDepartmentId()
        };
    }

    function buildUpdatePayload(form) {
        var formData = global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.formDataToObject === 'function'
            ? global.StoreManagementOfflineDatabase.formDataToObject(new FormData(form))
            : {};

        return {
            name: formData.name || '',
            type: formData.type || '',
            status: formData.status || 0,
            actual_price: formData.actual_price || '',
            selling_price: formData.selling_price || '',
            info: formData.info || ''
        };
    }

    function submitOfflineCreate(form) {
        var repo = getRepository();

        if (!repo) {
            notify('error', 'Offline unavailable', 'Product repository is not ready yet.');
            return Promise.reject(new Error('Product repository unavailable.'));
        }

        return repo.create(buildCreatePayload(form)).then(function (savedRecord) {
            if (savedRecord) {
                syncRecordToOfflineList(savedRecord);
            }

            notify('success', 'Saved offline', 'Product was stored locally and queued for sync.');
            form.reset();
            applyOfflineFilters();

            return savedRecord;
        });
    }

    function submitOfflineUpdate(form, identifier) {
        var repo = getRepository();

        if (!repo) {
            notify('error', 'Offline unavailable', 'Product repository is not ready yet.');
            return Promise.reject(new Error('Product repository unavailable.'));
        }

        if (!identifier) {
            notify('error', 'Missing product', 'The local product record could not be identified.');
            return Promise.reject(new Error('Missing product identifier.'));
        }

        return repo.findById(identifier).then(function (existingRecord) {
            if (!existingRecord) {
                notify('error', 'Product not cached', 'Open the product list online first so the product can be updated offline.');
                return Promise.reject(new Error('Product record not found in IndexedDB.'));
            }

            return repo.update(identifier, buildUpdatePayload(form)).then(function (savedRecord) {
                if (savedRecord) {
                    syncRecordToOfflineList(savedRecord);
                }

                notify('success', 'Saved offline', 'Product changes were stored locally and queued for sync.');
                closeOfflineEditModal();
                applyOfflineFilters();

                return savedRecord;
            });
        });
    }

    function bindProductForms() {
        var forms = document.querySelectorAll('form[data-product-form]');

        if (!forms.length) {
            return;
        }

        forms.forEach(function (form) {
            if (form.dataset.productOfflineBound === 'true') {
                return;
            }

            form.dataset.productOfflineBound = 'true';
            form.addEventListener('submit', function (event) {
                if (navigator.onLine) {
                    return;
                }

                event.preventDefault();

                var formMode = form.getAttribute('data-product-form');
                var identifier = resolveFormIdentifier(form);

                if (formMode === 'update' || formMode === 'offline-update') {
                    submitOfflineUpdate(form, identifier);
                    return;
                }

                submitOfflineCreate(form);
            });
        });
    }

    function initOnlineTable() {
        var table = document.getElementById('purchaseDate');

        if (!table || !global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            currentMode = 'offline';
            return Promise.resolve(null);
        }

        if (global.jQuery.fn.DataTable.isDataTable('#purchaseDate')) {
            onlineTableInstance = global.jQuery('#purchaseDate').DataTable();
            currentMode = 'online';
            removeBanner();
            return Promise.resolve(onlineTableInstance);
        }

        currentMode = 'online';
        removeBanner();

        onlineTableInstance = global.jQuery('#purchaseDate').DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: global.__PRODUCTS_ROUTE__ || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.name = getValue('name');
                    d.sku_code = getValue('sku_code');
                    d.type = getValue('type');
                    d.quantity = getValue('quantity');
                    d.supplier = getValue('supplier');
                },
                dataSrc: function (json) {
                    cacheServerRows(Array.isArray(json.data) ? json.data : []).catch(function () {});
                    return json.data || [];
                },
                error: function () {
                    notify('error', 'Product list unavailable', 'Unable to load the product list from the server.');
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'sku_code', name: 'sku_code' },
                { data: 'type', name: 'type' },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data) {
                        return data == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Disabled</span>';
                    }
                },
                { data: 'quantity', name: 'quantity' },
                { data: 'actual_price', name: 'actual_price' },
                { data: 'selling_price', name: 'selling_price' },
                { data: 'created_at', name: 'created_at' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        var deleteUrl = global.__PRODUCT_DELETE_BASE__ ? global.__PRODUCT_DELETE_BASE__ + '/' + data : (global.location.origin + '/products/products/' + data);
                        var editUrl = global.__PRODUCT_EDIT_BASE__ ? global.__PRODUCT_EDIT_BASE__ + '/' + data + '/edit' : (global.location.origin + '/products/products/' + data + '/edit');

                        return [
                            '<a href="' + editUrl + '" class="btn btn-rounded btn-sm bg-outline-light me-2"><i class="fas fa-edit"></i></a>',
                            '<form id="deleteForm-' + escapeHtml(data) + '" action="' + deleteUrl + '" method="POST" style="display:inline;">',
                            '  <input type="hidden" name="_token" value="' + (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '') + '">',
                            '  <input type="hidden" name="_method" value="DELETE">',
                            '  <button style="float:right !important;" type="button" class="btn btn-rounded btn-sm bg-outline-light me-2" onclick="confirmDelete(' + escapeHtml(data) + ')">',
                            '    <i class="fas fa-trash"></i>',
                            '  </button>',
                            '</form>'
                        ].join('');
                    }
                }
            ]
        });

        return Promise.resolve(onlineTableInstance);
    }

    function renderOfflineIndex() {
        var repo = getRepository();

        if (!repo) {
            return Promise.resolve([]);
        }

        showBanner('product data is being read from IndexedDB.');

        return applyOfflineFilters();
    }

    function refreshFormBannerState() {
        if (!isCreatePage() && !isUpdatePage()) {
            return;
        }

        if (isOffline()) {
            showBanner('product changes will be stored locally and queued for sync.');
            return;
        }

        removeBanner();
    }

    function openOfflineEditor(productId) {
        var repo = getRepository();

        if (!repo) {
            notify('error', 'Offline unavailable', 'IndexedDB is not ready yet.');
            return;
        }

        repo.findById(productId).then(function (record) {
            if (!record) {
                notify('error', 'Product not found', 'This product is not available in the offline store yet.');
                return;
            }

            openOfflineEditModal(prepareOfflineRecord(record));
        }).catch(function (error) {
            notify('error', 'Could not open product', error && error.message ? error.message : 'Unknown error.');
        });
    }

    function handleOfflineDelete(productId) {
        var repo = getRepository();

        if (!repo) {
            notify('error', 'Offline unavailable', 'IndexedDB is not ready yet.');
            return;
        }

        function performDelete() {
            repo.delete(productId).then(function () {
                removeRecordFromOfflineList(productId);
                notify('success', 'Deleted offline', 'Product was removed from the local store.');
                applyOfflineFilters();
            }).catch(function (error) {
                notify('error', 'Delete failed', error && error.message ? error.message : 'Unable to delete the product locally.');
            });
        }

        if (!global.Swal || typeof global.Swal.fire !== 'function') {
            performDelete();
            return;
        }

        global.Swal.fire({
            title: 'Delete this product?',
            text: 'This will mark the product as deleted locally and queue the change for sync.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3d5ee1',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            if (result.isConfirmed) {
                performDelete();
            }
        });
    }

    function bindIndexInteractions() {
        var table = document.getElementById('purchaseDate');

        if (!table || table.dataset.productOfflineBound === 'true') {
            return;
        }

        table.dataset.productOfflineBound = 'true';

        table.addEventListener('click', function (event) {
            var editButton = event.target.closest('.js-product-offline-edit');
            var deleteButton = event.target.closest('.js-product-offline-delete');

            if (editButton) {
                event.preventDefault();
                openOfflineEditor(editButton.getAttribute('data-product-id'));
                return;
            }

            if (deleteButton) {
                event.preventDefault();
                handleOfflineDelete(deleteButton.getAttribute('data-product-id'));
            }
        });

        var searchButton = document.getElementById('searchBtn');
        if (searchButton && searchButton.dataset.productOfflineBound !== 'true') {
            searchButton.dataset.productOfflineBound = 'true';
            searchButton.addEventListener('click', function (event) {
                event.preventDefault();
                if (currentMode === 'online' && onlineTableInstance && navigator.onLine) {
                    onlineTableInstance.draw();
                    return;
                }

                applyOfflineFilters();
            });
        }

        ['name', 'sku_code', 'type', 'supplier', 'quantity'].forEach(function (fieldId) {
            var field = document.getElementById(fieldId);

            if (!field || field.dataset.productOfflineBound === 'true') {
                return;
            }

            field.dataset.productOfflineBound = 'true';
            field.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (currentMode === 'online' && onlineTableInstance && navigator.onLine) {
                        onlineTableInstance.draw();
                        return;
                    }

                    applyOfflineFilters();
                }
            });
        });
    }

    function refreshPageMode() {
        if (!isIndexPage()) {
            return Promise.resolve(null);
        }

        if (isOffline()) {
            currentMode = 'offline';
            if (global.jQuery && global.jQuery.fn && global.jQuery.fn.DataTable && global.jQuery.fn.DataTable.isDataTable('#purchaseDate')) {
                global.jQuery('#purchaseDate').DataTable().destroy();
                onlineTableInstance = null;
            }

            return renderOfflineIndex();
        }

        currentMode = 'online';
        removeBanner();
        return initOnlineTable();
    }

    function bindConnectivityListeners() {
        if (bindConnectivityListeners.bound) {
            return;
        }

        bindConnectivityListeners.bound = true;

        global.addEventListener('online', function () {
            if (isIndexPage()) {
                refreshPageMode();
            }

            refreshFormBannerState();
        });

        global.addEventListener('offline', function () {
            if (isIndexPage()) {
                refreshPageMode();
            }

            refreshFormBannerState();
        });

        global.addEventListener('offline-queue:changed', function () {
            if (currentMode === 'offline' && isIndexPage()) {
                applyOfflineFilters();
            }
        });
    }

    function boot() {
        if (moduleStarted) {
            return;
        }

        moduleStarted = true;

        bindConnectivityListeners();
        bindProductForms();
        bindIndexInteractions();

        if (isIndexPage()) {
            refreshPageMode();
        }

        if (isCreatePage() || isUpdatePage()) {
            refreshFormBannerState();
        }
    }

    namespace.boot = boot;
    namespace.refreshPageMode = refreshPageMode;
    namespace.applyOfflineFilters = applyOfflineFilters;
    namespace.getRepository = getRepository;
    namespace.normalizeProductRecord = normalizeProductRecord;

    global.StoreManagementProductModuleClass = {
        boot: boot
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
