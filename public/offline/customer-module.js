(function (global) {
    'use strict';

    var namespace = global.StoreManagementCustomerModule = global.StoreManagementCustomerModule || {};
    var repositoryInstance = null;
    var offlineIndexState = {
        active: false,
        bound: false,
        records: []
    };

    function hasCustomerRepository() {
        return typeof global.CustomerRepository === 'function';
    }

    function isOffline() {
        return navigator.onLine === false;
    }

    function isCustomerIndexPage() {
        return Boolean(document.getElementById('clientTable'));
    }

    function isCustomerCreatePage() {
        return Boolean(document.querySelector('form[data-customer-form="create"]'));
    }

    function isCustomerEditPage() {
        return Boolean(document.querySelector('form[data-customer-form="update"]'));
    }

    function getRepository() {
        if (!hasCustomerRepository()) {
            return null;
        }

        if (!repositoryInstance) {
            repositoryInstance = new global.CustomerRepository();
        }

        return repositoryInstance;
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function parseMoney(value) {
        var numeric = Number(value);
        return Number.isFinite(numeric) ? numeric : 0;
    }

    function formatMoney(value) {
        return parseMoney(value).toFixed(2);
    }

    function notify(type, title, text) {
        var messageType = type === 'error' ? 'error' : 'success';

        if (global.Swal && typeof global.Swal.fire === 'function') {
            global.Swal.fire({
                icon: messageType,
                title: title,
                text: text,
                confirmButtonText: 'OK',
                confirmButtonColor: '#3d5ee1'
            });
            return;
        }

        alert(title + ': ' + text);
    }

    function showOfflineBanner() {
        var card = document.querySelector('#clientTable');

        if (!card) {
            return;
        }

        var wrapper = card.closest('.card');

        if (!wrapper || wrapper.querySelector('[data-offline-customer-banner]')) {
            return;
        }

        var banner = document.createElement('div');
        banner.className = 'alert alert-warning mb-3';
        banner.setAttribute('data-offline-customer-banner', 'true');
        banner.innerHTML = '<strong>Offline mode:</strong> customer data is being read from IndexedDB.';

        wrapper.parentNode.insertBefore(banner, wrapper);
    }

    function ensureEditModal() {
        var existing = document.getElementById('customer-offline-edit-modal');

        if (existing) {
            return existing;
        }

        var modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'customer-offline-edit-modal';
        modal.tabIndex = -1;
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = [
            '<div class="modal-dialog modal-lg modal-dialog-centered">',
            '  <div class="modal-content">',
            '    <div class="modal-header">',
            '      <h5 class="modal-title">Edit Customer Offline</h5>',
            '      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>',
            '    </div>',
            '    <form id="customer-offline-edit-form" data-customer-form="offline-modal-update">',
            '      <div class="modal-body">',
            '        <input type="hidden" name="customer_id" id="customer-offline-id">',
            '        <div class="row g-3">',
            '          <div class="col-md-6">',
            '            <label class="form-label">Customer Name</label>',
            '            <input type="text" class="form-control" name="customer_name" id="customer-offline-name" required>',
            '          </div>',
            '          <div class="col-md-6">',
            '            <label class="form-label">Phone</label>',
            '            <input type="text" class="form-control" name="phone" id="customer-offline-phone" required>',
            '          </div>',
            '          <div class="col-md-6">',
            '            <label class="form-label">Address</label>',
            '            <input type="text" class="form-control" name="address" id="customer-offline-address" required>',
            '          </div>',
            '          <div class="col-md-3">',
            '            <label class="form-label">Age</label>',
            '            <input type="text" class="form-control" name="age" id="customer-offline-age">',
            '          </div>',
            '          <div class="col-md-3">',
            '            <label class="form-label">Balance</label>',
            '            <input type="text" class="form-control" name="balance" id="customer-offline-balance">',
            '          </div>',
            '          <div class="col-md-4">',
            '            <label class="form-label">Sex</label>',
            '            <select class="form-select" name="sex" id="customer-offline-sex">',
            '              <option value="Male">Male</option>',
            '              <option value="Female">Female</option>',
            '            </select>',
            '          </div>',
            '          <div class="col-12">',
            '            <label class="form-label">Description</label>',
            '            <textarea class="form-control" name="description" id="customer-offline-description" rows="3"></textarea>',
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

    function openEditModal(record) {
        var modalElement = ensureEditModal();
        var modal = global.bootstrap && global.bootstrap.Modal
            ? global.bootstrap.Modal.getOrCreateInstance(modalElement)
            : null;

        document.getElementById('customer-offline-id').value = record.id || record.local_id || '';
        document.getElementById('customer-offline-name').value = record.customer_name || record.name || '';
        document.getElementById('customer-offline-phone').value = record.phone || '';
        document.getElementById('customer-offline-address').value = record.address || '';
        document.getElementById('customer-offline-age').value = record.age || '';
        document.getElementById('customer-offline-balance').value = record.balance || '0';
        document.getElementById('customer-offline-sex').value = record.sex || 'Male';
        document.getElementById('customer-offline-description').value = record.description || '';
        modalElement.setAttribute('data-customer-id', record.id || record.local_id || '');

        if (modal) {
            modal.show();
            return;
        }

        modalElement.style.display = 'block';
        modalElement.classList.add('show');
    }

    function closeEditModal() {
        var modalElement = document.getElementById('customer-offline-edit-modal');
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

    function buildFilterState() {
        return {
            name: String(document.getElementById('name') ? document.getElementById('name').value : '').trim().toLowerCase(),
            phone: String(document.getElementById('phone') ? document.getElementById('phone').value : '').trim().toLowerCase(),
            address: String(document.getElementById('address') ? document.getElementById('address').value : '').trim().toLowerCase(),
            type: String(document.getElementById('type') ? document.getElementById('type').value : '').trim().toLowerCase()
        };
    }

    function matchesFilter(value, keyword) {
        if (!keyword) {
            return true;
        }

        return String(value == null ? '' : value).toLowerCase().indexOf(keyword) !== -1;
    }

    function normalizeOfflineCustomer(record) {
        return {
            id: record.id,
            local_id: record.local_id || null,
            server_id: typeof record.server_id !== 'undefined' ? record.server_id : null,
            customer_name: record.customer_name || record.name || '',
            name: record.name || record.customer_name || '',
            phone: record.phone || '',
            address: record.address || '',
            serial: record.serial || '',
            balance: record.balance || 0,
            birthDate: record.birthDate || record.birth_date || '',
            age: record.age || record.birthDate || '',
            sex: record.sex || '',
            depID: record.depID || '',
            description: record.description || '',
            created_at: record.created_at || record.updated_at || '',
            updated_at: record.updated_at || record.created_at || '',
            synced: typeof record.synced === 'boolean' ? record.synced : Boolean(record.server_id),
            sync_status: record.sync_status || (record.synced ? 'synced' : 'pending'),
            is_deleted: Boolean(record.is_deleted),
            deleted_at: record.deleted_at || null
        };
    }

    function buildOfflineRow(record, index) {
        var customerId = record.id || record.local_id || index + 1;
        var editLabel = record.sync_status === 'failed' ? 'Retry Edit' : 'Edit';

        return [
            '<tr data-customer-row="' + escapeHtml(customerId) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td>' + escapeHtml(record.customer_name || record.name || '') + '</td>',
            '  <td>' + escapeHtml(record.phone || '') + '</td>',
            '  <td>' + escapeHtml(record.address || '') + '</td>',
            '  <td>',
            '    <span class="badge ' + (parseMoney(record.balance) > 0 ? 'bg-danger' : (parseMoney(record.balance) < 0 ? 'bg-success' : 'bg-secondary')) + '">',
            '      ' + escapeHtml(formatMoney(record.balance)) +
            '    </span>',
            '  </td>',
            '  <td>' + escapeHtml(record.description || '') + '</td>',
            '  <td>' + escapeHtml(record.created_at || record.updated_at || '') + '</td>',
            '  <td>',
            '    <button type="button" class="btn btn-sm btn-rounded btn-sm bg-outline-light me-2 js-customer-offline-edit" data-customer-id="' + escapeHtml(customerId) + '">',
            '      <i class="fas fa-edit"></i> ' + escapeHtml(editLabel),
            '    </button>',
            '    <button type="button" class="btn btn-sm btn-rounded btn-sm bg-outline-light js-customer-offline-delete" data-customer-id="' + escapeHtml(customerId) + '">',
            '      <i class="fas fa-trash"></i>',
            '    </button>',
            '  </td>',
            '</tr>'
        ].join('');
    }

    function renderOfflineRows(records) {
        var table = document.getElementById('clientTable');
        var tbody = table ? table.querySelector('tbody') : null;

        if (!tbody) {
            return;
        }

        offlineIndexState.records = records.slice();
        offlineIndexState.active = true;

        if (global.jQuery && global.jQuery.fn && global.jQuery.fn.DataTable && global.jQuery.fn.DataTable.isDataTable('#clientTable')) {
            global.jQuery('#clientTable').DataTable().destroy();
        }

        if (!records.length) {
            tbody.innerHTML = [
                '<tr>',
                '  <td colspan="8" class="text-center text-muted py-4">',
                '    No offline customer records found.',
                '  </td>',
                '</tr>'
            ].join('');
            return;
        }

        tbody.innerHTML = records.map(function (record, index) {
            return buildOfflineRow(normalizeOfflineCustomer(record), index);
        }).join('');
    }

    function applyOfflineFilters() {
        var repo = getRepository();

        if (!repo) {
            return Promise.resolve([]);
        }

        var filters = buildFilterState();

        return repo.getAll().then(function (records) {
            var filtered = records.filter(function (record) {
                var normalized = normalizeOfflineCustomer(record);
                var matchesName = matchesFilter(normalized.customer_name || normalized.name, filters.name);
                var matchesPhone = matchesFilter(normalized.phone, filters.phone);
                var matchesAddress = matchesFilter(normalized.address, filters.address);
                var matchesType = true;

                if (filters.type) {
                    matchesType = parseMoney(normalized.balance) > 0;
                }

                return matchesName && matchesPhone && matchesAddress && matchesType;
            });

            renderOfflineRows(filtered);
            return filtered;
        });
    }

    function bindOfflineIndex() {
        var table = document.getElementById('clientTable');

        if (!table) {
            return;
        }

        if (offlineIndexState.bound) {
            applyOfflineFilters();
            return;
        }

        offlineIndexState.bound = true;

        showOfflineBanner();

        var searchButton = document.getElementById('searchBtn');
        var filterInputs = ['name', 'phone', 'address', 'type'];

        filterInputs.forEach(function (id) {
            var input = document.getElementById(id);

            if (!input) {
                return;
            }

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyOfflineFilters();
                }
            });
        });

        if (searchButton) {
            searchButton.addEventListener('click', function (event) {
                event.preventDefault();
                applyOfflineFilters();
            });
        }

        table.addEventListener('click', function (event) {
            var editButton = event.target.closest('.js-customer-offline-edit');
            var deleteButton = event.target.closest('.js-customer-offline-delete');

            if (editButton) {
                event.preventDefault();
                openOfflineEditor(editButton.getAttribute('data-customer-id'));
                return;
            }

            if (deleteButton) {
                event.preventDefault();
                handleOfflineDelete(deleteButton.getAttribute('data-customer-id'));
            }
        });

        applyOfflineFilters();
    }

    function openOfflineEditor(customerId) {
        var repo = getRepository();

        if (!repo) {
            notify('error', 'Offline unavailable', 'IndexedDB is not ready yet.');
            return;
        }

        repo.findById(customerId).then(function (record) {
            if (!record) {
                notify('error', 'Customer not found', 'This customer is not available in the offline store yet.');
                return;
            }

            openEditModal(normalizeOfflineCustomer(record));
        }).catch(function (error) {
            notify('error', 'Could not open customer', error && error.message ? error.message : 'Unknown error.');
        });
    }

    function syncRecordToLocalList(updatedRecord) {
        var normalized = normalizeOfflineCustomer(updatedRecord);
        var index = offlineIndexState.records.findIndex(function (record) {
            var candidate = normalizeOfflineCustomer(record);
            return String(candidate.id) === String(normalized.id);
        });

        if (index >= 0) {
            offlineIndexState.records[index] = normalized;
        } else {
            offlineIndexState.records.unshift(normalized);
        }
    }

    function submitCustomerOffline(form, mode, identifier) {
        var repo = getRepository();

        if (!repo) {
            notify('error', 'Offline unavailable', 'IndexedDB is not ready yet.');
            return Promise.reject(new Error('Repository not ready.'));
        }

        var formData = new FormData(form);
        var data = global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.formDataToObject === 'function'
            ? global.StoreManagementOfflineDatabase.formDataToObject(formData)
            : {};

        if (mode === 'update' || mode === 'offline-modal-update') {
            return repo.findById(identifier).then(function (record) {
                if (record) {
                    return repo.update(identifier, data).then(function (savedRecord) {
                        if (savedRecord) {
                            syncRecordToLocalList(savedRecord);
                        }

                        notify('success', 'Saved offline', 'Customer changes were stored locally and queued for sync.');
                        applyOfflineFilters();
                        closeEditModal();

                        return savedRecord;
                    });
                }

                notify('error', 'Customer not cached', 'Open the customer list online first so the record can be stored locally.');
                return Promise.reject(new Error('Customer record not found in IndexedDB.'));
            });
        }

        return repo.create(data).then(function (savedRecord) {
            if (savedRecord) {
                syncRecordToLocalList(savedRecord);
            }

            notify('success', 'Saved offline', 'Customer was saved locally and queued for sync.');
            form.reset();
            applyOfflineFilters();

            return savedRecord;
        });
    }

    function bindCustomerForms() {
        var forms = document.querySelectorAll('form[data-customer-form]');

        if (!forms.length) {
            return;
        }

        forms.forEach(function (form) {
            if (form.dataset.customerOfflineBound === 'true') {
                return;
            }

            form.dataset.customerOfflineBound = 'true';
            form.addEventListener('submit', function (event) {
                if (navigator.onLine) {
                    return;
                }

                event.preventDefault();

                var mode = form.getAttribute('data-customer-form');
                var identifierField = form.querySelector('[name="customer_id"]');
                var identifier = form.getAttribute('data-customer-id') || (identifierField ? identifierField.value : '') || '';

                submitCustomerOffline(form, mode, identifier);
            });
        });
    }

    function handleOfflineDelete(customerId) {
        var repo = getRepository();

        if (!repo) {
            notify('error', 'Offline unavailable', 'IndexedDB is not ready yet.');
            return;
        }

        if (!global.Swal || typeof global.Swal.fire !== 'function') {
            repo.delete(customerId).then(function () {
                notify('success', 'Deleted offline', 'Customer removed from the local store.');
                applyOfflineFilters();
            });
            return;
        }

        global.Swal.fire({
            title: 'Delete this customer?',
            text: 'This will remove the customer from the local offline database and queue the change.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d'
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            repo.delete(customerId).then(function () {
                notify('success', 'Deleted offline', 'Customer removed from the local store.');
                applyOfflineFilters();
            }).catch(function (error) {
                notify('error', 'Delete failed', error && error.message ? error.message : 'Unable to delete the customer offline.');
            });
        });
    }

    function cacheServerRows(rows) {
        var repo = getRepository();
        var records = Array.isArray(rows) ? rows : [];

        if (!repo || !records.length) {
            return Promise.resolve([]);
        }

        if (typeof repo.cacheFromServerRows === 'function') {
            return repo.cacheFromServerRows(records);
        }

        return repo.cacheMany(records.map(function (record) {
            return {
                server_id: record.id,
                customer_name: record.name || record.customer_name || '',
                name: record.name || record.customer_name || '',
                phone: record.phone || '',
                address: record.address || '',
                serial: record.serial || '',
                balance: record.balance || 0,
                birthDate: record.birthDate || record.birth_date || '',
                sex: record.sex || '',
                depID: record.depID || '',
                description: record.description || '',
                created_at: record.created_at || '',
                updated_at: record.created_at || '',
                synced: true,
                sync_status: 'synced',
                local_action: 'import'
            };
        }));
    }

    function bindModalSubmit() {
        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!form || form.id !== 'customer-offline-edit-form') {
                return;
            }

            if (navigator.onLine) {
                return;
            }

            event.preventDefault();

            var identifier = document.getElementById('customer-offline-id').value;
            submitCustomerOffline(form, 'offline-modal-update', identifier);
        });
    }

    function boot() {
        if (isCustomerIndexPage()) {
            if (isOffline()) {
                bindOfflineIndex();
            }
        }

        bindCustomerForms();
        bindModalSubmit();
    }

    namespace.isOffline = isOffline;
    namespace.isCustomerIndexPage = isCustomerIndexPage;
    namespace.isCustomerCreatePage = isCustomerCreatePage;
    namespace.isCustomerEditPage = isCustomerEditPage;
    namespace.cacheServerRows = cacheServerRows;
    namespace.bindCustomerForms = bindCustomerForms;
    namespace.bindOfflineIndex = bindOfflineIndex;
    namespace.submitCustomerOffline = submitCustomerOffline;
    namespace.showOfflineBanner = showOfflineBanner;

    global.StoreManagementCustomerOfflineModule = namespace;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
