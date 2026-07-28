(function (global) {
    'use strict';

    var namespace = global.StoreManagementFinanceModule = global.StoreManagementFinanceModule || {};
    var repositoryCache = namespace._repositoryCache || {};
    var booted = false;
    var activeListPage = null;

    var config = {
        expenses: {
            storeName: 'expenses',
            tableId: 'expenseTable',
            filterButtonId: 'searchBtn',
            filterFields: ['type', 'amount', 'startDate', 'endDate'],
            redirectUrl: '/expenses',
            title: 'Expense List',
            renderRow: renderExpenseRow,
            filterRecords: filterExpenseRecords,
            columns: 7
        },
        cashAccount: {
            storeName: 'cash_account',
            tableId: 'accountTable',
            filterButtonId: 'searchBtn',
            filterFields: ['name'],
            redirectUrl: '/cashAccount',
            title: 'Account List',
            renderRow: renderCashAccountRow,
            filterRecords: filterCashAccountRecords,
            columns: 6
        },
        capital: {
            storeName: 'capital',
            tableId: 'CapitaTable',
            filterButtonId: 'searchBtn',
            filterFields: ['name', 'amount', 'startDate', 'endDate'],
            redirectUrl: '/capital',
            title: 'Capital List',
            renderRow: renderCapitalRow,
            filterRecords: filterCapitalRecords,
            columns: 5
        },
        bankStatement: {
            storeName: 'bank_statements',
            tableId: 'bankStatement',
            filterButtonId: 'searchBtn',
            filterFields: ['description', 'type', 'startDate', 'endDate'],
            redirectUrl: '/bankStatement',
            title: 'Bank Statement List',
            renderRow: renderBankStatementRow,
            filterRecords: filterBankStatementRecords,
            columns: 7
        },
        liability: {
            storeName: 'account_payables',
            tableId: 'liabilityTable',
            filterButtonId: 'searchBtn',
            filterFields: ['received_from', 'type', 'transaction', 'startDate', 'endDate'],
            redirectUrl: '/account_payables',
            title: 'Liability List',
            renderRow: renderLiabilityRow,
            filterRecords: filterLiabilityRecords,
            columns: 8
        },
        assets: {
            storeName: 'assets',
            tableId: 'assetTable',
            filterButtonId: 'searchBtn',
            filterFields: ['name', 'type', 'startDate', 'endDate'],
            redirectUrl: '/asset',
            title: 'Asset List',
            renderRow: renderAssetRow,
            filterRecords: filterAssetRecords,
            columns: 6
        },
        salesmanPayment: {
            storeName: 'salesman_payment',
            tableId: 'creditTable',
            filterButtonId: 'searchBtn',
            filterFields: ['name', 'phone', 'startDate', 'endDate'],
            redirectUrl: '/salesman_payment',
            title: 'Salesman Payment List',
            renderRow: renderSalesmanPaymentRow,
            filterRecords: filterSalesmanPaymentRecords,
            columns: 10
        }
    };

    function isOnline() {
        return navigator.onLine !== false;
    }

    function nowIso() {
        if (global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.nowIso === 'function') {
            return global.StoreManagementOfflineDatabase.nowIso();
        }

        return new Date().toISOString();
    }

    function todayValue() {
        return nowIso().slice(0, 10);
    }

    function uuid() {
        if (global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.generateUuid === 'function') {
            return global.StoreManagementOfflineDatabase.generateUuid();
        }

        return 'finance-' + Math.random().toString(36).slice(2) + Date.now().toString(36);
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

    function formatMoney(value) {
        return toNumber(value).toFixed(2);
    }

    function formatDate(value) {
        if (!value) {
            return '';
        }

        var text = String(value);
        if (text.indexOf('T') !== -1) {
            return text.slice(0, 10);
        }

        return text.slice(0, 10);
    }

    function formatDateTime(value) {
        if (!value) {
            return '';
        }

        var text = String(value);
        if (text.indexOf('T') !== -1) {
            return text.replace('T', ' ').slice(0, 16);
        }

        return text;
    }

    function getContextValue(key) {
        var context = global.__OFFLINE_ENGINE_CONFIG__ || {};

        return typeof context[key] === 'undefined' ? null : context[key];
    }

    function applyContext(data) {
        var source = Object.assign({}, data || {});
        var userId = getContextValue('userId');
        var departmentId = getContextValue('departmentId');

        if (departmentId !== null && typeof source.depID === 'undefined') {
            source.depID = departmentId;
        }

        if (userId !== null) {
            if (typeof source.user_id === 'undefined') {
                source.user_id = userId;
            }

            if (typeof source.user === 'undefined') {
                source.user = userId;
            }

            if (typeof source.created_by === 'undefined') {
                source.created_by = userId;
            }
        }

        return source;
    }

    function cleanFormData(payload) {
        var data = Object.assign({}, payload || {});
        delete data._token;
        delete data._method;
        delete data.__method;
        delete data.submit;
        return data;
    }

    function getRepository(storeName) {
        if (repositoryCache[storeName]) {
            return repositoryCache[storeName];
        }

        if (!global.StoreManagementOfflineRepositories || typeof global.StoreManagementOfflineRepositories.createRepository !== 'function') {
            return null;
        }

        try {
            repositoryCache[storeName] = global.StoreManagementOfflineRepositories.createRepository(storeName);
            namespace._repositoryCache = repositoryCache;
            return repositoryCache[storeName];
        } catch (error) {
            return null;
        }
    }

    function getTableBody(table) {
        if (!table) {
            return null;
        }

        return table.tBodies && table.tBodies.length ? table.tBodies[0] : table.querySelector('tbody');
    }

    function getField(record, fields) {
        var values = Array.isArray(fields) ? fields : [fields];
        var index;

        for (index = 0; index < values.length; index += 1) {
            if (record && typeof record[values[index]] !== 'undefined' && record[values[index]] !== null && record[values[index]] !== '') {
                return record[values[index]];
            }
        }

        return '';
    }

    function getValue(id) {
        var node = document.getElementById(id);

        if (!node) {
            return '';
        }

        return String(node.value == null ? '' : node.value).trim();
    }

    function destroyDataTable(tableId) {
        if (!global.$ || !global.$.fn || !global.$.fn.DataTable) {
            return;
        }

        var $table = global.$('#' + tableId);
        if (!$table.length || !global.$.fn.DataTable.isDataTable($table)) {
            return;
        }

        $table.DataTable().clear().destroy();
    }

    function setOfflineMessage(table, message, colspan) {
        var body = getTableBody(table);

        if (!body) {
            return;
        }

        body.innerHTML = [
            '<tr>',
            '  <td colspan="' + colspan + '" class="text-center text-muted py-4">',
            '    ' + escapeHtml(message),
            '  </td>',
            '</tr>'
        ].join('');
    }

    function getOfflineTableConfig(pageName) {
        return config[pageName] || null;
    }

    function getAllRecords(pageName) {
        var pageConfig = getOfflineTableConfig(pageName);
        var repo = pageConfig ? getRepository(pageConfig.storeName) : null;

        if (!repo || typeof repo.getAll !== 'function') {
            return Promise.resolve([]);
        }

        return repo.getAll();
    }

    function cacheList(storeName, rows) {
        var repo = getRepository(storeName);

        if (!repo || typeof repo.cacheMany !== 'function') {
            return Promise.resolve([]);
        }

        return repo.cacheMany(Array.isArray(rows) ? rows : []);
    }

    function appendSearchButton(pageName, handler) {
        var pageConfig = getOfflineTableConfig(pageName);
        if (!pageConfig || !pageConfig.filterButtonId) {
            return;
        }

        var button = document.getElementById(pageConfig.filterButtonId);
        if (!button) {
            return;
        }

        if (button.getAttribute('data-finance-bound') === pageName) {
            return;
        }

        button.setAttribute('data-finance-bound', pageName);
        button.addEventListener('click', function () {
            handler();
        });
    }

    function applyTextFilter(records, fieldNames, keyword) {
        var normalized = String(keyword || '').trim().toLowerCase();
        if (!normalized) {
            return records;
        }

        return records.filter(function (record) {
            return fieldNames.some(function (fieldName) {
                return String(getField(record, fieldName)).toLowerCase().indexOf(normalized) !== -1;
            });
        });
    }

    function applyDateRangeFilter(records, fieldName, startDate, endDate) {
        if (!startDate && !endDate) {
            return records;
        }

        return records.filter(function (record) {
            var value = formatDate(getField(record, fieldName));

            if (startDate && value < startDate) {
                return false;
            }

            if (endDate && value > endDate) {
                return false;
            }

            return true;
        });
    }

    function filterExpenseRecords(records) {
        var type = getValue('type');
        var amount = getValue('amount');
        var startDate = getValue('startDate');
        var endDate = getValue('endDate');
        var filtered = records.slice();

        filtered = applyTextFilter(filtered, ['type'], type);

        if (amount) {
            filtered = filtered.filter(function (record) {
                return String(getField(record, ['amount'])).indexOf(amount) !== -1;
            });
        }

        filtered = applyDateRangeFilter(filtered, 'date', startDate, endDate);
        return filtered;
    }

    function filterCashAccountRecords(records) {
        return applyTextFilter(records.slice(), ['account', 'AccCode', 'name'], getValue('name'));
    }

    function filterCapitalRecords(records) {
        var filtered = applyTextFilter(records.slice(), ['owner_name', 'name'], getValue('name'));
        var amount = getValue('amount');
        var startDate = getValue('startDate');
        var endDate = getValue('endDate');

        if (amount) {
            filtered = filtered.filter(function (record) {
                return String(getField(record, ['capital_amount', 'amount'])).indexOf(amount) !== -1;
            });
        }

        filtered = applyDateRangeFilter(filtered, 'created_at', startDate, endDate);
        return filtered;
    }

    function filterBankStatementRecords(records) {
        var filtered = applyTextFilter(records.slice(), ['description'], getValue('description'));
        filtered = applyTextFilter(filtered, ['type'], getValue('type'));
        filtered = applyDateRangeFilter(filtered, 'date', getValue('startDate'), getValue('endDate'));
        return filtered;
    }

    function filterLiabilityRecords(records) {
        var filtered = applyTextFilter(records.slice(), ['received_from', 'supplier_name', 'name'], getValue('received_from'));
        filtered = applyTextFilter(filtered, ['type'], getValue('type'));
        filtered = applyTextFilter(filtered, ['transaction_type'], getValue('transaction'));
        filtered = applyDateRangeFilter(filtered, 'date', getValue('startDate'), getValue('endDate'));
        return filtered;
    }

    function filterAssetRecords(records) {
        var filtered = applyTextFilter(records.slice(), ['name'], getValue('name'));
        filtered = applyTextFilter(filtered, ['type'], getValue('type'));
        filtered = applyDateRangeFilter(filtered, 'date', getValue('startDate'), getValue('endDate'));
        return filtered;
    }

    function filterSalesmanPaymentRecords(records) {
        var filtered = applyTextFilter(records.slice(), ['full_name', 'salesman_name'], getValue('name'));
        filtered = applyTextFilter(filtered, ['phone'], getValue('phone'));
        filtered = applyDateRangeFilter(filtered, 'date', getValue('startDate'), getValue('endDate'));
        return filtered;
    }

    function renderActionButton(pageName, identifier) {
        return [
            '<button type="button" class="btn btn-rounded btn-sm bg-outline-light me-2" ',
            'data-finance-offline-delete="true" ',
            'data-finance-page="' + escapeHtml(pageName) + '" ',
            'data-record-id="' + escapeHtml(identifier) + '">',
            '<i class="fas fa-trash"></i>',
            '</button>'
        ].join('');
    }

    function renderExpenseRow(record, index) {
        var identifier = getField(record, ['local_id', 'id', 'server_id']);

        return [
            '<tr data-finance-record-id="' + escapeHtml(identifier) + '">',
            '<td>' + (index + 1) + '</td>',
            '<td>' + escapeHtml(getField(record, ['type'])) + '</td>',
            '<td>' + escapeHtml(getField(record, ['salesman_name', 'salesman'])) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['amount']))) + '</td>',
            '<td>' + escapeHtml(formatDate(getField(record, ['date', 'created_at']))) + '</td>',
            '<td>' + escapeHtml(getField(record, ['description'])) + '</td>',
            '<td>' + renderActionButton('expenses', identifier) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderCashAccountRow(record, index) {
        var identifier = getField(record, ['local_id', 'id', 'server_id']);

        return [
            '<tr data-finance-record-id="' + escapeHtml(identifier) + '">',
            '<td>' + (index + 1) + '</td>',
            '<td>' + escapeHtml(getField(record, ['account', 'AccCode', 'name'])) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['debit']))) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['credit']))) + '</td>',
            '<td>' + escapeHtml(formatDate(getField(record, ['date', 'created_at']))) + '</td>',
            '<td>' + renderActionButton('cashAccount', identifier) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderCapitalRow(record, index) {
        var identifier = getField(record, ['local_id', 'id', 'server_id']);

        return [
            '<tr data-finance-record-id="' + escapeHtml(identifier) + '">',
            '<td>' + (index + 1) + '</td>',
            '<td>' + escapeHtml(getField(record, ['owner_name', 'name'])) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['capital_amount', 'amount']))) + '</td>',
            '<td>' + escapeHtml(formatDate(getField(record, ['created_at', 'date']))) + '</td>',
            '<td>' + renderActionButton('capital', identifier) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderBankStatementRow(record, index) {
        var identifier = getField(record, ['local_id', 'id', 'server_id']);

        return [
            '<tr data-finance-record-id="' + escapeHtml(identifier) + '">',
            '<td>' + (index + 1) + '</td>',
            '<td>' + escapeHtml(getField(record, ['type'])) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['amount']))) + '</td>',
            '<td>' + escapeHtml(getField(record, ['check_no'])) + '</td>',
            '<td>' + escapeHtml(formatDate(getField(record, ['date', 'created_at']))) + '</td>',
            '<td>' + escapeHtml(getField(record, ['description'])) + '</td>',
            '<td>' + renderActionButton('bankStatement', identifier) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderLiabilityRow(record, index) {
        var identifier = getField(record, ['local_id', 'id', 'server_id']);

        return [
            '<tr data-finance-record-id="' + escapeHtml(identifier) + '">',
            '<td>' + (index + 1) + '</td>',
            '<td>' + escapeHtml(getField(record, ['received_from', 'supplier_name', 'name'])) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['amount']))) + '</td>',
            '<td>' + escapeHtml(getField(record, ['transaction_type', 'transaction'])) + '</td>',
            '<td>' + escapeHtml(getField(record, ['type'])) + '</td>',
            '<td>' + escapeHtml(formatDate(getField(record, ['date', 'created_at']))) + '</td>',
            '<td>' + escapeHtml(getField(record, ['description'])) + '</td>',
            '<td>' + renderActionButton('liability', identifier) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderAssetRow(record, index) {
        var identifier = getField(record, ['local_id', 'id', 'server_id']);

        return [
            '<tr data-finance-record-id="' + escapeHtml(identifier) + '">',
            '<td>' + (index + 1) + '</td>',
            '<td>' + escapeHtml(getField(record, ['name'])) + '</td>',
            '<td>' + escapeHtml(getField(record, ['type'])) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['amount']))) + '</td>',
            '<td>' + escapeHtml(getField(record, ['description'])) + '</td>',
            '<td>' + renderActionButton('assets', identifier) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderSalesmanPaymentRow(record, index) {
        var identifier = getField(record, ['local_id', 'id', 'server_id']);

        return [
            '<tr data-finance-record-id="' + escapeHtml(identifier) + '">',
            '<td>' + (index + 1) + '</td>',
            '<td>' + escapeHtml(getField(record, ['full_name', 'salesman_name'])) + '</td>',
            '<td>' + escapeHtml(getField(record, ['phone'])) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['pbalance']))) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['discount']))) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['paid_amount']))) + '</td>',
            '<td>' + escapeHtml(formatMoney(getField(record, ['current', 'remaining']))) + '</td>',
            '<td>' + escapeHtml(getField(record, ['payment_method'])) + '</td>',
            '<td>' + escapeHtml(formatDateTime(getField(record, ['created_at', 'date']))) + '</td>',
            '<td>' + renderActionButton('salesmanPayment', identifier) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderEmptyRow(pageName, message) {
        var pageConfig = getOfflineTableConfig(pageName);
        var colspan = pageConfig ? pageConfig.columns : 1;

        return [
            '<tr>',
            '<td colspan="' + colspan + '" class="text-center text-muted py-4">',
            escapeHtml(message),
            '</td>',
            '</tr>'
        ].join('');
    }

    function renderOfflineRows(pageName) {
        var pageConfig = getOfflineTableConfig(pageName);
        if (!pageConfig) {
            return Promise.resolve();
        }

        var table = document.getElementById(pageConfig.tableId);
        if (!table) {
            return Promise.resolve();
        }

        var body = getTableBody(table);
        if (!body) {
            return Promise.resolve();
        }

        setOfflineMessage(table, 'Loading offline records...', pageConfig.columns);

        return getAllRecords(pageName).then(function (records) {
            var filtered = pageConfig.filterRecords ? pageConfig.filterRecords(records || []) : (records || []);
            var rows = filtered.map(function (record, index) {
                return pageConfig.renderRow(record, index);
            });

            if (!rows.length) {
                body.innerHTML = renderEmptyRow(pageName, 'No offline records found.');
                return;
            }

            body.innerHTML = rows.join('');
        }).catch(function () {
            body.innerHTML = renderEmptyRow(pageName, 'Unable to load offline records.');
        });
    }

    function mountListPage(pageName) {
        var pageConfig = getOfflineTableConfig(pageName);

        if (!pageConfig) {
            return Promise.resolve();
        }

        activeListPage = pageName;
        destroyDataTable(pageConfig.tableId);
        appendSearchButton(pageName, function () {
            renderOfflineRows(pageName);
        });

        return renderOfflineRows(pageName);
    }

    function getCurrentPageName() {
        var pageNames = Object.keys(config);
        var index;
        var pageConfig;

        for (index = 0; index < pageNames.length; index += 1) {
            pageConfig = config[pageNames[index]];
            if (document.getElementById(pageConfig.tableId)) {
                return pageNames[index];
            }
        }

        return null;
    }

    function normalizeRequestPath(url) {
        if (!url) {
            return '';
        }

        try {
            return new URL(String(url), global.location.origin).pathname.replace(/\/+$/, '');
        } catch (error) {
            return String(url).split('?')[0].replace(/\/+$/, '');
        }
    }

    function getMethod(form) {
        var method = (form.getAttribute('method') || 'POST').toUpperCase();
        var override = form.querySelector('input[name="_method"]');

        if (override && override.value) {
            method = String(override.value).toUpperCase();
        }

        return method;
    }

    function getFormAction(pathname) {
        var normalized = normalizeRequestPath(pathname);

        if (normalized.indexOf('/expenses') !== -1) {
            return 'expenses';
        }

        if (normalized.indexOf('/capital') !== -1) {
            return 'capital';
        }

        if (normalized.indexOf('/bankStatement') !== -1) {
            return 'bankStatement';
        }

        if (normalized.indexOf('/account_payables') !== -1) {
            return 'liability';
        }

        if (normalized.indexOf('/asset') !== -1) {
            return 'assets';
        }

        if (normalized.indexOf('/salesman_payment') !== -1) {
            return 'salesmanPayment';
        }

        if (normalized.indexOf('/cashAccount') !== -1) {
            return 'cashAccount';
        }

        return null;
    }

    function getFormDataObject(form) {
        if (!global.FormData) {
            return {};
        }

        return cleanFormData(global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.formDataToObject === 'function'
            ? global.StoreManagementOfflineDatabase.formDataToObject(new FormData(form))
            : {});
    }

    function getFormIdentifier(formData, form) {
        var identifier = formData.local_id || formData.id || formData.server_id || formData.record_id || formData._record_id || null;

        if (identifier) {
            return identifier;
        }

        if (form && form.id) {
            var formMatch = String(form.id).match(/(\d+)$/);
            if (formMatch) {
                return formMatch[1];
            }
        }

        if (form && form.action) {
            var path = normalizeRequestPath(form.action);
            var segments = path.split('/').filter(Boolean);
            if (segments.length) {
                return segments[segments.length - 1];
            }
        }

        return null;
    }

    function getLookupRepositoryName(entity) {
        if (entity === 'suppliers') {
            return 'suppliers';
        }

        if (entity === 'salesmen') {
            return 'salesman';
        }

        return null;
    }

    function searchLocalEntity(entity, keyword) {
        var repoName = getLookupRepositoryName(entity);
        var repo = repoName ? getRepository(repoName) : null;

        if (!repo || typeof repo.search !== 'function') {
            return Promise.resolve([]);
        }

        return repo.search(keyword || '').then(function (records) {
            if (entity === 'suppliers') {
                return (records || []).map(function (record) {
                    return {
                        id: getField(record, ['id', 'local_id', 'server_id']),
                        name: getField(record, ['name', 'supplier_name', 'full_name']),
                        balance: getField(record, ['balance'])
                    };
                });
            }

            if (entity === 'salesmen') {
                return (records || []).map(function (record) {
                    return {
                        id: getField(record, ['id', 'local_id', 'server_id']),
                        full_name: getField(record, ['full_name', 'name']),
                        phone: getField(record, ['phone']),
                        balance: getField(record, ['balance'])
                    };
                });
            }

            return records;
        });
    }

    function renderLookupDropdown(dropdown, items, renderItem) {
        if (!dropdown) {
            return;
        }

        dropdown.innerHTML = '';

        if (!items || !items.length) {
            dropdown.innerHTML = '<div class="dropdown-item text-center text-muted">No results found</div>';
            dropdown.style.display = 'block';
            return;
        }

        items.forEach(function (item) {
            dropdown.appendChild(renderItem(item));
        });

        dropdown.style.display = 'block';
    }

    function createLookupItem(label, onClick) {
        var item = document.createElement('a');
        item.className = 'dropdown-item';
        item.href = '#';
        item.textContent = label;
        item.addEventListener('click', function (event) {
            event.preventDefault();
            onClick();
        });
        return item;
    }

    function notifySuccess(title, text) {
        if (global.Swal && typeof global.Swal.fire === 'function') {
            global.Swal.fire({
                icon: 'success',
                title: title,
                text: text,
                confirmButtonText: 'OK',
                confirmButtonColor: '#3d5ee1'
            });
            return;
        }

        if (global.toastr && typeof global.toastr.success === 'function') {
            global.toastr.success(text, title);
            return;
        }

        alert(title + ': ' + text);
    }

    function notifyError(title, text) {
        if (global.Swal && typeof global.Swal.fire === 'function') {
            global.Swal.fire({
                icon: 'error',
                title: title,
                text: text,
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
            return;
        }

        if (global.toastr && typeof global.toastr.error === 'function') {
            global.toastr.error(text, title);
            return;
        }

        alert(title + ': ' + text);
    }

    function upsertNamedAccount(accountName, accCode, delta, extraData) {
        var repo = getRepository('cash_account');
        var amount = delta || {};
        var payload = applyContext(Object.assign({
            account: accountName,
            AccCode: accCode,
            date: (extraData && extraData.date) || todayValue(),
            debit: 0,
            credit: 0
        }, extraData || {}));

        if (!repo) {
            return Promise.resolve(null);
        }

        return repo.getAll().then(function (records) {
            var existing = (records || []).find(function (record) {
                var accMatches = String(getField(record, ['AccCode'])).toLowerCase() === String(accCode).toLowerCase();
                var accountMatches = String(getField(record, ['account', 'name'])).toLowerCase() === String(accountName).toLowerCase();
                return accMatches || accountMatches;
            });

            if (!existing) {
                payload.debit = toNumber(payload.debit) + toNumber(amount.debit);
                payload.credit = toNumber(payload.credit) + toNumber(amount.credit);
                return repo.create(payload);
            }

            var updated = Object.assign({}, existing, extraData || {});
            updated.debit = toNumber(existing.debit) + toNumber(amount.debit);
            updated.credit = toNumber(existing.credit) + toNumber(amount.credit);
            updated.date = payload.date || existing.date || todayValue();
            updated.account = updated.account || accountName;
            updated.AccCode = updated.AccCode || accCode;

            return repo.update(getField(existing, ['local_id', 'id', 'server_id']), updated);
        });
    }

    function updateSupplierBalance(supplierId, deltaAmount) {
        var repo = getRepository('suppliers');
        if (!repo || !supplierId) {
            return Promise.resolve(null);
        }

        return repo.findById(supplierId).then(function (supplier) {
            if (!supplier) {
                return null;
            }

            var updated = Object.assign({}, supplier, {
                balance: toNumber(supplier.balance) + toNumber(deltaAmount)
            });

            return repo.update(getField(supplier, ['local_id', 'id', 'server_id']), updated);
        });
    }

    function updateSalesmanBalance(salesmanId, deltaAmount) {
        var repo = getRepository('salesman');
        if (!repo || !salesmanId) {
            return Promise.resolve(null);
        }

        return repo.findById(salesmanId).then(function (salesman) {
            if (!salesman) {
                return null;
            }

            var updated = Object.assign({}, salesman, {
                balance: toNumber(salesman.balance) + toNumber(deltaAmount)
            });

            return repo.update(getField(salesman, ['local_id', 'id', 'server_id']), updated);
        });
    }

    function createAccountingEntry(payload) {
        var repo = getRepository('accounting_transaction');

        if (!repo) {
            return Promise.resolve(null);
        }

        return repo.create(applyContext(Object.assign({
            date: payload.date || todayValue(),
            account: payload.account || 'Finance',
            debit: payload.debit || 0,
            credit: payload.credit || 0,
            description: payload.description || '',
            source_table: payload.source_table || '',
            source_local_id: payload.source_local_id || null,
            reference: payload.reference || null,
            action: payload.action || 'create'
        }, payload || {})));
    }

    function saveOfflineRecord(storeName, payload) {
        var repo = getRepository(storeName);

        if (!repo || typeof repo.create !== 'function') {
            return Promise.reject(new Error('Offline repository is not available for ' + storeName + '.'));
        }

        return repo.create(applyContext(cleanFormData(payload)));
    }

    function updateOfflineRecord(storeName, identifier, payload) {
        var repo = getRepository(storeName);

        if (!repo || typeof repo.update !== 'function') {
            return Promise.reject(new Error('Offline repository is not available for ' + storeName + '.'));
        }

        return repo.update(identifier, applyContext(cleanFormData(payload)));
    }

    function deleteOfflineRecord(storeName, identifier) {
        var repo = getRepository(storeName);

        if (!repo || typeof repo.delete !== 'function') {
            return Promise.reject(new Error('Offline repository is not available for ' + storeName + '.'));
        }

        return repo.delete(identifier);
    }

    function handleExpenseCreate(formData) {
        var amount = toNumber(formData.amount);
        var salesmanSelect = document.getElementById('salesman_id');
        var salesmanName = salesmanSelect && salesmanSelect.selectedIndex >= 0 && salesmanSelect.options[salesmanSelect.selectedIndex]
            ? String(salesmanSelect.options[salesmanSelect.selectedIndex].text || '').trim()
            : '';
        var record = Object.assign({}, formData, {
            amount: amount,
            payment_account: 'Cash Account',
            salesman_name: salesmanName && salesmanName !== 'Select Salesman' ? salesmanName : '',
            local_action: 'create',
            synced: false,
            sync_status: 'pending'
        });

        return saveOfflineRecord('expenses', record).then(function (savedExpense) {
            var description = 'Expense: ' + (savedExpense.type || 'Expense');

            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', { credit: amount }, { date: savedExpense.date }),
                upsertNamedAccount('Expense', 'Expense', { debit: amount }, { date: savedExpense.date }),
                createAccountingEntry({
                    account: 'Expense Account',
                    debit: amount,
                    credit: amount,
                    date: savedExpense.date,
                    description: description,
                    source_table: 'expenses',
                    source_local_id: savedExpense.local_id,
                    reference: savedExpense.id || savedExpense.local_id,
                    action: 'create'
                })
            ]).then(function () {
                return savedExpense;
            });
        });
    }

    function handleExpenseDelete(record) {
        var amount = toNumber(record.amount);

        return deleteOfflineRecord('expenses', getField(record, ['local_id', 'id', 'server_id'])).then(function () {
            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', { debit: amount }, { date: nowIso() }),
                upsertNamedAccount('Expense', 'Expense', { credit: amount }, { date: nowIso() }),
                createAccountingEntry({
                    account: 'Expense Account',
                    debit: amount,
                    credit: amount,
                    date: nowIso(),
                    description: 'Reversal for deleted expense',
                    source_table: 'expenses',
                    source_local_id: record.local_id || record.id || null,
                    reference: record.id || record.local_id || null,
                    action: 'delete'
                })
            ]);
        });
    }

    function handleCashAccountMutation(method, formData) {
        var record = Object.assign({}, formData, {
            debit: toNumber(formData.debit),
            credit: toNumber(formData.credit),
            local_action: method === 'DELETE' ? 'delete' : (method === 'PUT' ? 'update' : 'create'),
            synced: false,
            sync_status: 'pending'
        });
        var identifier = getFormIdentifier(formData, null);

        if (method === 'PUT' || method === 'PATCH') {
            return updateOfflineRecord('cash_account', identifier, record).then(function (savedRecord) {
                return createAccountingEntry({
                    account: savedRecord.account || 'Cash Account',
                    debit: savedRecord.debit || 0,
                    credit: savedRecord.credit || 0,
                    date: savedRecord.date,
                    description: 'Cash account updated offline',
                    source_table: 'cash_account',
                    source_local_id: savedRecord.local_id,
                    reference: savedRecord.id || savedRecord.local_id,
                    action: 'update'
                }).then(function () {
                    return savedRecord;
                });
            });
        }

        return saveOfflineRecord('cash_account', record).then(function (savedRecord) {
            return createAccountingEntry({
                account: savedRecord.account || 'Cash Account',
                debit: savedRecord.debit || 0,
                credit: savedRecord.credit || 0,
                date: savedRecord.date,
                description: 'Cash account created offline',
                source_table: 'cash_account',
                source_local_id: savedRecord.local_id,
                reference: savedRecord.id || savedRecord.local_id,
                action: 'create'
            }).then(function () {
                return savedRecord;
            });
        });
    }

    function handleCapitalCreate(formData) {
        var amount = toNumber(formData.capital_amount || formData.amount);
        var record = Object.assign({}, formData, {
            capital_amount: amount,
            amount: amount,
            local_action: 'create',
            synced: false,
            sync_status: 'pending'
        });

        return saveOfflineRecord('capital', record).then(function (savedCapital) {
            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', { debit: amount }, { date: savedCapital.created_at || savedCapital.date }),
                upsertNamedAccount('Capital', 'Capital', { credit: amount }, { date: savedCapital.created_at || savedCapital.date }),
                createAccountingEntry({
                    account: 'Capital Account',
                    debit: amount,
                    credit: amount,
                    date: savedCapital.created_at || savedCapital.date,
                    description: 'Capital created offline',
                    source_table: 'capital',
                    source_local_id: savedCapital.local_id,
                    reference: savedCapital.id || savedCapital.local_id,
                    action: 'create'
                })
            ]).then(function () {
                return savedCapital;
            });
        });
    }

    function handleCapitalDelete(record) {
        var amount = toNumber(getField(record, ['capital_amount', 'amount']));

        return deleteOfflineRecord('capital', getField(record, ['local_id', 'id', 'server_id'])).then(function () {
            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', { credit: amount }, { date: nowIso() }),
                upsertNamedAccount('Capital', 'Capital', { debit: amount }, { date: nowIso() }),
                createAccountingEntry({
                    account: 'Capital Account',
                    debit: amount,
                    credit: amount,
                    date: nowIso(),
                    description: 'Reversal for deleted capital',
                    source_table: 'capital',
                    source_local_id: record.local_id || record.id || null,
                    reference: record.id || record.local_id || null,
                    action: 'delete'
                })
            ]);
        });
    }

    function handleBankStatementCreate(formData) {
        var amount = toNumber(formData.amount);
        var type = String(formData.type || '').trim();
        var record = Object.assign({}, formData, {
            amount: amount,
            local_action: 'create',
            synced: false,
            sync_status: 'pending'
        });

        return saveOfflineRecord('bank_statements', record).then(function (savedRecord) {
            var cashDelta = type === 'Debit' ? { credit: amount } : { debit: amount };
            var bankDelta = type === 'Debit' ? { debit: amount } : { credit: amount };

            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', cashDelta, { date: savedRecord.date }),
                upsertNamedAccount('Bank', 'Bank', bankDelta, { date: savedRecord.date }),
                createAccountingEntry({
                    account: 'Bank Account',
                    debit: amount,
                    credit: amount,
                    date: savedRecord.date,
                    description: 'Bank statement recorded offline',
                    source_table: 'bank_statements',
                    source_local_id: savedRecord.local_id,
                    reference: savedRecord.id || savedRecord.local_id,
                    action: 'create'
                })
            ]).then(function () {
                return savedRecord;
            });
        });
    }

    function handleBankStatementDelete(record) {
        var amount = toNumber(record.amount);
        var type = String(record.type || '').trim();
        var cashDelta = type === 'Debit' ? { debit: amount } : { credit: amount };
        var bankDelta = type === 'Debit' ? { credit: amount } : { debit: amount };

        return deleteOfflineRecord('bank_statements', getField(record, ['local_id', 'id', 'server_id'])).then(function () {
            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', cashDelta, { date: nowIso() }),
                upsertNamedAccount('Bank', 'Bank', bankDelta, { date: nowIso() }),
                createAccountingEntry({
                    account: 'Bank Account',
                    debit: amount,
                    credit: amount,
                    date: nowIso(),
                    description: 'Reversal for deleted bank statement',
                    source_table: 'bank_statements',
                    source_local_id: record.local_id || record.id || null,
                    reference: record.id || record.local_id || null,
                    action: 'delete'
                })
            ]);
        });
    }

    function handleLiabilityCreate(formData) {
        var amount = toNumber(formData.amount);
        var discount = toNumber(formData.discount);
        var current = toNumber(formData.current);
        var transactionType = String(formData.transaction_type || '').trim();
        var liabilityType = String(formData.type || 'Short Term').trim() || 'Short Term';
        var record = Object.assign({}, formData, {
            amount: amount,
            discount: discount,
            current: current,
            type: liabilityType,
            local_action: 'create',
            synced: false,
            sync_status: 'pending'
        });

        return saveOfflineRecord('account_payables', record).then(function (savedRecord) {
            var cashDelta;
            var liabilityDelta;
            var supplierDelta = transactionType === 'Debit' ? -(amount + discount) : amount;
            var liabilityAccountName = liabilityType;

            if (transactionType === 'Debit') {
                cashDelta = { credit: amount };
                liabilityDelta = { debit: amount };
            } else {
                cashDelta = { debit: amount };
                liabilityDelta = { credit: amount };
            }

            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', cashDelta, { date: savedRecord.date }),
                upsertNamedAccount(liabilityAccountName, liabilityAccountName, liabilityDelta, { date: savedRecord.date }),
                updateSupplierBalance(savedRecord.received_from, supplierDelta),
                createAccountingEntry({
                    account: 'Liability Account',
                    debit: amount,
                    credit: amount,
                    date: savedRecord.date,
                    description: 'Liability recorded offline',
                    source_table: 'account_payables',
                    source_local_id: savedRecord.local_id,
                    reference: savedRecord.id || savedRecord.local_id,
                    action: 'create'
                })
            ]).then(function () {
                return savedRecord;
            });
        });
    }

    function handleLiabilityDelete(record) {
        var amount = toNumber(record.amount);
        var discount = toNumber(record.discount);
        var transactionType = String(record.transaction_type || '').trim();
        var liabilityAccountName = String(record.type || 'Short Term').trim() || 'Short Term';
        var cashDelta;
        var liabilityDelta;
        var supplierDelta = transactionType === 'Debit' ? (amount + discount) : -amount;

        if (transactionType === 'Debit') {
            cashDelta = { debit: amount };
            liabilityDelta = { credit: amount };
        } else {
            cashDelta = { credit: amount };
            liabilityDelta = { debit: amount };
        }

        return deleteOfflineRecord('account_payables', getField(record, ['local_id', 'id', 'server_id'])).then(function () {
            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', cashDelta, { date: nowIso() }),
                upsertNamedAccount(liabilityAccountName, liabilityAccountName, liabilityDelta, { date: nowIso() }),
                updateSupplierBalance(record.received_from, supplierDelta),
                createAccountingEntry({
                    account: 'Liability Account',
                    debit: amount,
                    credit: amount,
                    date: nowIso(),
                    description: 'Reversal for deleted liability',
                    source_table: 'account_payables',
                    source_local_id: record.local_id || record.id || null,
                    reference: record.id || record.local_id || null,
                    action: 'delete'
                })
            ]);
        });
    }

    function handleAssetCreate(formData) {
        var amount = toNumber(formData.amount);
        var record = Object.assign({}, formData, {
            amount: amount,
            local_action: 'create',
            synced: false,
            sync_status: 'pending'
        });

        return saveOfflineRecord('assets', record).then(function (savedRecord) {
            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', { credit: amount }, { date: savedRecord.date }),
                upsertNamedAccount('Fixed', 'Fixed', { debit: amount }, { date: savedRecord.date }),
                createAccountingEntry({
                    account: 'Fixed Assets',
                    debit: amount,
                    credit: amount,
                    date: savedRecord.date,
                    description: 'Asset created offline',
                    source_table: 'assets',
                    source_local_id: savedRecord.local_id,
                    reference: savedRecord.id || savedRecord.local_id,
                    action: 'create'
                })
            ]).then(function () {
                return savedRecord;
            });
        });
    }

    function handleAssetDelete(record) {
        var amount = toNumber(record.amount);

        return deleteOfflineRecord('assets', getField(record, ['local_id', 'id', 'server_id'])).then(function () {
            return Promise.all([
                upsertNamedAccount('Cash', 'Cash', { debit: amount }, { date: nowIso() }),
                upsertNamedAccount('Fixed', 'Fixed', { credit: amount }, { date: nowIso() }),
                createAccountingEntry({
                    account: 'Fixed Assets',
                    debit: amount,
                    credit: amount,
                    date: nowIso(),
                    description: 'Reversal for deleted asset',
                    source_table: 'assets',
                    source_local_id: record.local_id || record.id || null,
                    reference: record.id || record.local_id || null,
                    action: 'delete'
                })
            ]);
        });
    }

    function handleSalesmanPaymentCreate(formData) {
        var paidAmount = toNumber(formData.paid_amount);
        var discount = toNumber(formData.discount);
        var previousBalance = toNumber(formData.pbalance);
        var current = typeof formData.current !== 'undefined' && formData.current !== ''
            ? toNumber(formData.current)
            : (previousBalance - paidAmount - discount);
        var record = Object.assign({}, formData, {
            paid_amount: paidAmount,
            discount: discount,
            pbalance: previousBalance,
            current: current,
            remaining: current,
            full_name: formData.salesman_name || formData.full_name || '',
            local_action: 'create',
            synced: false,
            sync_status: 'pending'
        });

        return saveOfflineRecord('salesman_payment', record).then(function (savedRecord) {
            return Promise.all([
                updateSalesmanBalance(savedRecord.salesman_id, -(paidAmount + discount)),
                upsertNamedAccount('Cash', 'Cash', { credit: paidAmount + discount }, { date: savedRecord.date }),
                createAccountingEntry({
                    account: 'Salesman Payment',
                    debit: paidAmount + discount,
                    credit: paidAmount + discount,
                    date: savedRecord.date,
                    description: 'Salesman payment recorded offline',
                    source_table: 'salesman_payment',
                    source_local_id: savedRecord.local_id,
                    reference: savedRecord.id || savedRecord.local_id,
                    action: 'create'
                })
            ]).then(function () {
                return savedRecord;
            });
        });
    }

    function handleSalesmanPaymentDelete(record) {
        var paidAmount = toNumber(record.paid_amount);
        var discount = toNumber(record.discount);

        return deleteOfflineRecord('salesman_payment', getField(record, ['local_id', 'id', 'server_id'])).then(function () {
            return Promise.all([
                updateSalesmanBalance(record.salesman_id, paidAmount + discount),
                upsertNamedAccount('Cash', 'Cash', { debit: paidAmount + discount }, { date: nowIso() }),
                createAccountingEntry({
                    account: 'Salesman Payment',
                    debit: paidAmount + discount,
                    credit: paidAmount + discount,
                    date: nowIso(),
                    description: 'Reversal for deleted salesman payment',
                    source_table: 'salesman_payment',
                    source_local_id: record.local_id || record.id || null,
                    reference: record.id || record.local_id || null,
                    action: 'delete'
                })
            ]);
        });
    }

    function handleCreateOffline(pageName, formData) {
        if (pageName === 'expenses') {
            return handleExpenseCreate(formData);
        }

        if (pageName === 'cashAccount') {
            return handleCashAccountMutation('POST', formData);
        }

        if (pageName === 'capital') {
            return handleCapitalCreate(formData);
        }

        if (pageName === 'bankStatement') {
            return handleBankStatementCreate(formData);
        }

        if (pageName === 'liability') {
            return handleLiabilityCreate(formData);
        }

        if (pageName === 'assets') {
            return handleAssetCreate(formData);
        }

        if (pageName === 'salesmanPayment') {
            return handleSalesmanPaymentCreate(formData);
        }

        return Promise.reject(new Error('Unsupported finance form.'));
    }

    function handleDeleteOffline(pageName, formData, form) {
        var identifier = getFormIdentifier(formData, form);

        if (pageName === 'expenses') {
            return getRepository('expenses').findById(identifier).then(function (record) {
                return record ? handleExpenseDelete(record) : null;
            });
        }

        if (pageName === 'cashAccount') {
            return deleteOfflineRecord('cash_account', identifier);
        }

        if (pageName === 'capital') {
            return getRepository('capital').findById(identifier).then(function (record) {
                return record ? handleCapitalDelete(record) : null;
            });
        }

        if (pageName === 'bankStatement') {
            return getRepository('bank_statements').findById(identifier).then(function (record) {
                return record ? handleBankStatementDelete(record) : null;
            });
        }

        if (pageName === 'liability') {
            return getRepository('account_payables').findById(identifier).then(function (record) {
                return record ? handleLiabilityDelete(record) : null;
            });
        }

        if (pageName === 'assets') {
            return getRepository('assets').findById(identifier).then(function (record) {
                return record ? handleAssetDelete(record) : null;
            });
        }

        if (pageName === 'salesmanPayment') {
            return getRepository('salesman_payment').findById(identifier).then(function (record) {
                return record ? handleSalesmanPaymentDelete(record) : null;
            });
        }

        return Promise.resolve(null);
    }

    function handleUpdateOffline(pageName, formData, form) {
        var identifier = getFormIdentifier(formData, form);
        var record = applyContext(cleanFormData(formData));

        if (pageName === 'cashAccount') {
            return updateOfflineRecord('cash_account', identifier, record);
        }

        if (pageName === 'expenses') {
            return updateOfflineRecord('expenses', identifier, record);
        }

        if (pageName === 'capital') {
            return updateOfflineRecord('capital', identifier, record);
        }

        if (pageName === 'bankStatement') {
            return updateOfflineRecord('bank_statements', identifier, record);
        }

        if (pageName === 'liability') {
            return updateOfflineRecord('account_payables', identifier, record);
        }

        if (pageName === 'assets') {
            return updateOfflineRecord('assets', identifier, record);
        }

        if (pageName === 'salesmanPayment') {
            return updateOfflineRecord('salesman_payment', identifier, record);
        }

        return Promise.resolve(null);
    }

    function handleSubmit(event) {
        if (isOnline()) {
            return;
        }

        var form = event.target;
        if (!form || form.nodeName !== 'FORM') {
            return;
        }

        var pageName = getFormAction(form.action || '');
        if (!pageName) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        var method = getMethod(form);
        var formData = getFormDataObject(form);
        var operation;
        var promise;

        if (method === 'DELETE') {
            operation = 'delete';
            promise = handleDeleteOffline(pageName, formData, form);
        } else if (method === 'PUT' || method === 'PATCH') {
            operation = 'update';
            promise = handleUpdateOffline(pageName, formData, form);
        } else {
            operation = 'create';
            promise = handleCreateOffline(pageName, formData);
        }

        promise.then(function (savedRecord) {
            var message = operation === 'delete'
                ? 'Deleted locally and queued for synchronization.'
                : 'Saved offline and queued for synchronization.';

            notifySuccess('Offline saved', message);

            if (operation !== 'delete') {
                var pageConfig = getOfflineTableConfig(pageName);
                if (pageConfig && pageConfig.redirectUrl) {
                    global.setTimeout(function () {
                        global.location.href = pageConfig.redirectUrl;
                    }, 700);
                }
            } else if (activeListPage) {
                renderOfflineRows(activeListPage);
            }

            return savedRecord;
        }).catch(function (error) {
            notifyError('Offline save failed', error && error.message ? error.message : 'Unable to store the record offline.');
        });
    }

    function bindLookupSearches() {
        var supplierSearch = document.getElementById('customerSearch');
        var supplierDropdown = document.getElementById('customerDropdown');
        var salesmanSearch = document.getElementById('salesmanSearch');
        var salesmanDropdown = document.getElementById('salesmanDropdown');

        if (supplierSearch && supplierDropdown) {
            supplierSearch.addEventListener('input', function () {
                if (isOnline() || supplierSearch.value.trim().length < 2) {
                    return;
                }

                searchLocalEntity('suppliers', supplierSearch.value.trim()).then(function (items) {
                    renderLookupDropdown(supplierDropdown, items, function (item) {
                        return createLookupItem(item.name + (item.balance ? ' - ' + item.balance : ''), function () {
                            supplierSearch.value = item.name || '';
                            var customerID = document.getElementById('customerID');
                            var pbalance = document.getElementById('pbalance');
                            if (customerID) {
                                customerID.value = item.id || '';
                            }
                            if (pbalance) {
                                pbalance.value = item.balance || 0;
                            }
                            supplierDropdown.style.display = 'none';
                        });
                    });
                });
            });
        }

        if (salesmanSearch && salesmanDropdown) {
            salesmanSearch.addEventListener('input', function () {
                if (isOnline() || salesmanSearch.value.trim().length < 2) {
                    return;
                }

                searchLocalEntity('salesmen', salesmanSearch.value.trim()).then(function (items) {
                    renderLookupDropdown(salesmanDropdown, items, function (item) {
                        return createLookupItem(item.full_name + (item.phone ? ' - ' + item.phone : ''), function () {
                            salesmanSearch.value = item.full_name || '';
                            var salesmanID = document.getElementById('salesmanID');
                            var pbalance = document.getElementById('pbalance');
                            if (salesmanID) {
                                salesmanID.value = item.id || '';
                            }
                            if (pbalance) {
                                pbalance.value = item.balance || 0;
                            }
                            salesmanDropdown.style.display = 'none';
                            if (typeof global.updateRemainingBalance === 'function') {
                                global.updateRemainingBalance();
                            }
                        });
                    });
                });
            });
        }
    }

    function bindOfflineDeleteClicks() {
        document.addEventListener('click', function (event) {
            var target = event.target;

            if (target && typeof target.closest === 'function') {
                target = target.closest('[data-finance-offline-delete="true"]');
            } else if (!(target && target.getAttribute && target.getAttribute('data-finance-offline-delete') === 'true')) {
                target = null;
            }

            if (!target || isOnline()) {
                return;
            }

            event.preventDefault();

            var pageName = target.getAttribute('data-finance-page');
            var recordId = target.getAttribute('data-record-id');
            var pageConfig = getOfflineTableConfig(pageName);

            if (!pageName || !recordId || !pageConfig) {
                return;
            }

            var runDelete = function () {
                handleDeleteOffline(pageName, {
                    id: recordId,
                    local_id: recordId,
                    server_id: recordId
                }, target.closest('form')).then(function () {
                    if (activeListPage === pageName) {
                        renderOfflineRows(pageName);
                    }
                }).catch(function (error) {
                    notifyError('Offline delete failed', error && error.message ? error.message : 'Unable to delete the record offline.');
                });
            };

            if (global.Swal && typeof global.Swal.fire === 'function') {
                global.Swal.fire({
                    title: 'Delete offline record?',
                    text: 'This record will be marked for synchronization.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        runDelete();
                    }
                });
                return;
            }

            if (global.confirm('Delete this record offline?')) {
                runDelete();
            }
        });
    }

    function bindGlobalFormSubmit() {
        document.addEventListener('submit', handleSubmit, true);
    }

    function bindConnectivityHandlers() {
        global.addEventListener('offline', function () {
            var pageName = getCurrentPageName();
            if (pageName) {
                mountListPage(pageName);
            }
        });

        global.addEventListener('online', function () {
            if (activeListPage) {
                global.location.reload();
            }
        });
    }

    function boot() {
        if (booted) {
            return;
        }

        booted = true;
        bindGlobalFormSubmit();
        bindOfflineDeleteClicks();
        bindConnectivityHandlers();

        if (!isOnline()) {
            var pageName = getCurrentPageName();
            if (pageName) {
                mountListPage(pageName);
            }
        }
    }

    namespace.cacheList = cacheList;
    namespace.mountListPage = mountListPage;
    namespace.searchLocalEntity = searchLocalEntity;
    namespace.searchSuppliers = function (keyword) {
        return searchLocalEntity('suppliers', keyword);
    };
    namespace.searchSalesmen = function (keyword) {
        return searchLocalEntity('salesmen', keyword);
    };
    namespace.boot = boot;
    namespace.isOnline = isOnline;

    global.StoreManagementFinanceModule = namespace;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
