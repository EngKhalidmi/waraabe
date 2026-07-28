(function (global) {
    'use strict';

    var namespace = global.StoreManagementSalesQuotationModule = global.StoreManagementSalesQuotationModule || {};
    var repositoryCache = {};
    var salesListTable = null;
    var salesTransactionsTable = null;
    var quotationListTable = null;
    var quotationOrdersTable = null;
    var booted = false;

    var pageState = {
        mode: null,
        salesProductIndex: 0,
        quotationProductIndex: 0,
        selectedSalesCustomer: null,
        selectedQuotationCustomer: null,
        selectedQuotationProduct: null
    };

    function isOnline() {
        return navigator.onLine !== false;
    }

    function nowIso() {
        return global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.nowIso === 'function'
            ? global.StoreManagementOfflineDatabase.nowIso()
            : new Date().toISOString();
    }

    function uuid() {
        return global.StoreManagementOfflineDatabase && typeof global.StoreManagementOfflineDatabase.generateUuid === 'function'
            ? global.StoreManagementOfflineDatabase.generateUuid()
            : 'sq-' + Math.random().toString(36).slice(2) + Date.now().toString(36);
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

    function formatMoney(value) {
        return normalizeNumber(value).toFixed(2);
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

    function sortRecent(records) {
        return (Array.isArray(records) ? records.slice() : []).sort(function (left, right) {
            var leftValue = left && (left.created_at || left.updated_at || '') ? String(left.created_at || left.updated_at) : '';
            var rightValue = right && (right.created_at || right.updated_at || '') ? String(right.created_at || right.updated_at) : '';

            if (leftValue === rightValue) {
                return normalizeNumber(right && right.id) - normalizeNumber(left && left.id);
            }

            return leftValue < rightValue ? 1 : -1;
        });
    }

    function notify(type, title, text) {
        if (global.Swal && typeof global.Swal.fire === 'function') {
            global.Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: title,
                text: text,
                confirmButtonText: 'OK',
                confirmButtonColor: '#3d5ee1'
            });
            return;
        }

        if (global.toastr && typeof global.toastr[type] === 'function') {
            global.toastr[type](text, title);
            return;
        }

        alert(title + ': ' + text);
    }

    function showBanner(message) {
        var container = document.querySelector('.page-wrapper .content');

        if (!container) {
            return;
        }

        var existing = container.querySelector('[data-sales-quotation-offline-banner]');
        if (existing) {
            existing.querySelector('[data-banner-message]').textContent = message;
            return;
        }

        var banner = document.createElement('div');
        banner.className = 'alert alert-warning mb-3';
        banner.setAttribute('data-sales-quotation-offline-banner', 'true');
        banner.innerHTML = '<strong>Offline mode:</strong> <span data-banner-message></span>';
        banner.querySelector('[data-banner-message]').textContent = message;

        var pageHeader = container.querySelector('.page-header');
        if (pageHeader && pageHeader.parentNode) {
            pageHeader.parentNode.insertBefore(banner, pageHeader.nextSibling);
            return;
        }

        container.insertBefore(banner, container.firstChild);
    }

    function removeBanner() {
        var banner = document.querySelector('[data-sales-quotation-offline-banner]');
        if (banner && banner.parentNode) {
            banner.parentNode.removeChild(banner);
        }
    }

    function getConfig(name) {
        if (name === 'sales') {
            return global.__SALES_CONFIG__ || {};
        }

        if (name === 'quotation') {
            return global.__QUOTATION_CONFIG__ || {};
        }

        return {};
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
            return repositoryCache[storeName];
        } catch (error) {
            return null;
        }
    }

    function cacheRows(storeName, rows) {
        var repo = getRepository(storeName);
        var records = Array.isArray(rows) ? rows : [];

        if (!repo || typeof repo.cacheMany !== 'function' || !records.length) {
            return Promise.resolve([]);
        }

        return repo.cacheMany(records);
    }

    function getValue(id) {
        var node = document.getElementById(id);
        return node ? String(node.value == null ? '' : node.value).trim() : '';
    }

    function setValue(id, value) {
        var node = document.getElementById(id);
        if (node) {
            node.value = value == null ? '' : value;
        }
    }

    function clearElement(id) {
        var node = document.getElementById(id);
        if (node) {
            node.innerHTML = '';
        }
    }

    function hideDropdown(id) {
        var node = document.getElementById(id);
        if (node) {
            node.style.display = 'none';
            node.innerHTML = '';
        }
    }

    function showDropdown(id) {
        var node = document.getElementById(id);
        if (node) {
            node.style.display = 'block';
        }
    }

    function currentMode() {
        if (document.querySelector('form[data-sales-form="create"]')) {
            return 'sales-create';
        }

        if (document.querySelector('form[data-quotation-form="create"]')) {
            return 'quotation-create';
        }

        if (document.getElementById('quotationLieTable')) {
            return 'quotation-list';
        }

        if (document.getElementById('QuotationOrdersTable')) {
            return 'quotation-orders';
        }

        if (document.getElementById('salesPaymentTable')) {
            return document.getElementById('seller') ? 'sales-transactions' : 'sales-list';
        }

        return null;
    }

    function isCashCustomer(record) {
        var customerName = String((record && (record.customer_name || record.customer || record.name)) || '').toLowerCase();
        var serial = String((record && record.serial) || '').toLowerCase();
        return /cash/.test(customerName) || /cash/.test(serial);
    }

    function normalizeCustomerRecord(record) {
        var source = Object.assign({}, record || {});

        if (!source.customer_name && source.name) {
            source.customer_name = source.name;
        }

        if (!source.name && source.customer_name) {
            source.name = source.customer_name;
        }

        if (typeof source.server_id === 'undefined') {
            source.server_id = typeof source.id !== 'undefined' ? source.id : null;
        }

        if (typeof source.local_id === 'undefined' || source.local_id === null || source.local_id === '') {
            source.local_id = source.server_id !== null && typeof source.server_id !== 'undefined' ? source.server_id : uuid();
        }

        source.synced = typeof source.synced === 'boolean' ? source.synced : true;
        source.sync_status = source.sync_status || 'synced';
        source.local_action = source.local_action || 'import';

        return source;
    }

    function normalizeProductRecord(record) {
        var source = Object.assign({}, record || {});

        if (!source.product_name && source.name) {
            source.product_name = source.name;
        }

        if (!source.name && source.product_name) {
            source.name = source.product_name;
        }

        if (typeof source.server_id === 'undefined') {
            source.server_id = typeof source.id !== 'undefined' ? source.id : null;
        }

        if (typeof source.local_id === 'undefined' || source.local_id === null || source.local_id === '') {
            source.local_id = source.server_id !== null && typeof source.server_id !== 'undefined' ? source.server_id : uuid();
        }

        source.synced = typeof source.synced === 'boolean' ? source.synced : true;
        source.sync_status = source.sync_status || 'synced';
        source.local_action = source.local_action || 'import';

        return source;
    }

    function normalizeSalesTransactionRecord(record) {
        var source = Object.assign({}, record || {});

        source.customer_name = source.customer_name || source.customer || '';
        source.customer = source.customer || source.customer_name || '';
        source.phone = source.phone || '';
        source.paid_date = source.paid_date || source.date || source.created_at || nowIso();

        if (typeof source.server_id === 'undefined') {
            source.server_id = typeof source.id !== 'undefined' ? source.id : null;
        }

        if (typeof source.local_id === 'undefined' || source.local_id === null || source.local_id === '') {
            source.local_id = source.server_id !== null && typeof source.server_id !== 'undefined' ? source.server_id : uuid();
        }

        source.synced = typeof source.synced === 'boolean' ? source.synced : true;
        source.sync_status = source.sync_status || 'synced';
        source.local_action = source.local_action || 'import';

        return source;
    }

    function normalizeQuotationRecord(record) {
        var source = Object.assign({}, record || {});

        source.customer_name = source.customer_name || source.customer || '';
        source.customer = source.customer || source.customer_name || '';
        source.phone = source.phone || '';
        source.date = source.date || source.due_date || source.created_at || nowIso();

        if (typeof source.server_id === 'undefined') {
            source.server_id = typeof source.id !== 'undefined' ? source.id : null;
        }

        if (typeof source.local_id === 'undefined' || source.local_id === null || source.local_id === '') {
            source.local_id = source.server_id !== null && typeof source.server_id !== 'undefined' ? source.server_id : uuid();
        }

        source.synced = typeof source.synced === 'boolean' ? source.synced : true;
        source.sync_status = source.sync_status || 'synced';
        source.local_action = source.local_action || 'import';

        return source;
    }

    function normalizeQuotationOrderRecord(record) {
        var source = Object.assign({}, record || {});

        source.pro = source.pro || source.product_name || '';
        source.product_name = source.product_name || source.pro || '';

        if (typeof source.server_id === 'undefined') {
            source.server_id = typeof source.id !== 'undefined' ? source.id : null;
        }

        if (typeof source.local_id === 'undefined' || source.local_id === null || source.local_id === '') {
            source.local_id = source.server_id !== null && typeof source.server_id !== 'undefined' ? source.server_id : uuid();
        }

        source.synced = typeof source.synced === 'boolean' ? source.synced : true;
        source.sync_status = source.sync_status || 'synced';
        source.local_action = source.local_action || 'import';

        return source;
    }

    function cacheOnlineRows(mode, rows) {
        var prepared = Array.isArray(rows) ? rows.map(function (row) {
            return Object.assign({}, row || {});
        }) : [];

        if (!prepared.length) {
            return Promise.resolve([]);
        }

        if (mode === 'sales-list') {
            return cacheRows('sales', prepared);
        }

        if (mode === 'sales-transactions') {
            return cacheRows('sales_transactions', prepared.map(normalizeSalesTransactionRecord));
        }

        if (mode === 'quotation-list') {
            return cacheRows('quotation', prepared.map(normalizeQuotationRecord));
        }

        if (mode === 'quotation-orders') {
            return cacheRows('quotation_orders', prepared.map(normalizeQuotationOrderRecord));
        }

        if (mode === 'sales-customer-search' || mode === 'quotation-customer-search') {
            return cacheRows('customers', prepared.map(normalizeCustomerRecord));
        }

        if (mode === 'sales-product-search' || mode === 'quotation-product-search') {
            return cacheRows('products', prepared.map(normalizeProductRecord));
        }

        return Promise.resolve([]);
    }

    function matchesKeyword(value, keyword) {
        if (!keyword) {
            return true;
        }

        return String(value == null ? '' : value).toLowerCase().indexOf(keyword) !== -1;
    }

    function matchesDateRange(value, startDate, endDate) {
        var text = String(value == null ? '' : value);
        var dateOnly = text.indexOf('T') !== -1 ? text.slice(0, 10) : text.slice(0, 10);

        if (startDate && dateOnly && dateOnly < startDate) {
            return false;
        }

        if (endDate && dateOnly && dateOnly > endDate) {
            return false;
        }

        return true;
    }

    function destroyTable(selector, tableInstance) {
        if (global.jQuery && global.jQuery.fn && global.jQuery.fn.DataTable && global.jQuery.fn.DataTable.isDataTable(selector)) {
            global.jQuery(selector).DataTable().destroy();
        }

        return null;
    }

    function renderRows(tableSelector, rows, builder, emptyMessage) {
        var table = document.querySelector(tableSelector);
        var tbody = table ? table.querySelector('tbody') : null;

        if (!table || !tbody) {
            return Promise.resolve([]);
        }

        destroyTable(tableSelector);

        var records = sortRecent(Array.isArray(rows) ? rows : []);

        if (!records.length) {
            tbody.innerHTML = '<tr><td colspan="' + table.querySelectorAll('thead th').length + '" class="text-center text-muted py-4">' + escapeHtml(emptyMessage) + '</td></tr>';
            return Promise.resolve(records);
        }

        tbody.innerHTML = records.map(function (record, index) {
            return builder(record, index);
        }).join('');

        return Promise.resolve(records);
    }

    function statusBadge(record) {
        var status = String(record && record.sync_status ? record.sync_status : (record && record.synced ? 'synced' : 'pending')).toLowerCase();

        if (status === 'failed') {
            return '<span class="badge bg-danger">Failed</span>';
        }

        if (status === 'synced') {
            return '<span class="badge bg-primary">Synced</span>';
        }

        if (status === 'syncing') {
            return '<span class="badge bg-info text-dark">Syncing</span>';
        }

        return '<span class="badge bg-warning text-dark">Pending</span>';
    }

    function salesListFilter(records) {
        var name = getValue('name').toLowerCase();
        var phone = getValue('phone').toLowerCase();
        var startDate = getValue('startDate');
        var endDate = getValue('endDate');

        return (Array.isArray(records) ? records : []).filter(function (record) {
            return matchesKeyword(record.product_name || record.name, name) &&
                matchesKeyword(record.sales_transaction_id, phone) &&
                matchesDateRange(record.created_at, startDate, endDate);
        });
    }

    function salesTransactionsFilter(records) {
        var name = getValue('name').toLowerCase();
        var phone = getValue('phone').toLowerCase();
        var type = getValue('type').toLowerCase();
        var seller = getValue('seller').toLowerCase();
        var startDate = getValue('startDate');
        var endDate = getValue('endDate');

        return (Array.isArray(records) ? records : []).filter(function (record) {
            var customerName = record.customer_name || record.customer || '';
            return matchesKeyword(customerName, name) &&
                matchesKeyword(record.phone, phone) &&
                matchesKeyword(record.type, type) &&
                matchesKeyword(record.seller, seller) &&
                matchesDateRange(record.paid_date || record.created_at, startDate, endDate);
        });
    }

    function quotationFilter(records) {
        var name = getValue('name').toLowerCase();
        var phone = getValue('phone').toLowerCase();
        var startDate = getValue('startDate');
        var endDate = getValue('endDate');

        return (Array.isArray(records) ? records : []).filter(function (record) {
            var customerName = record.customer_name || record.customer || '';
            return matchesKeyword(customerName, name) &&
                matchesKeyword(record.phone, phone) &&
                matchesDateRange(record.date, startDate, endDate);
        });
    }

    function quotationOrdersFilter(records) {
        var name = getValue('name').toLowerCase();
        var transId = getValue('transID').toLowerCase();
        var startDate = getValue('startDate');
        var endDate = getValue('endDate');

        return (Array.isArray(records) ? records : []).filter(function (record) {
            return matchesKeyword(record.pro || record.product_name, name) &&
                matchesKeyword(record.transID, transId) &&
                matchesDateRange(record.created_at, startDate, endDate);
        });
    }

    function buildSalesListRow(record, index) {
        return [
            '<tr data-sales-row="' + escapeHtml(record.id || record.local_id || index + 1) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td>',
            '    <strong>' + escapeHtml(record.product_name || '') + '</strong>',
            '    <div class="mt-1">' + statusBadge(record) + '</div>',
            '  </td>',
            '  <td>' + escapeHtml(record.quantity || '') + '</td>',
            '  <td>' + escapeHtml(record.price || '') + '</td>',
            '  <td>' + escapeHtml(record.total_price || '') + '</td>',
            '  <td>' + escapeHtml(record.sales_transaction_id || '') + '</td>',
            '  <td>' + escapeHtml(formatDateTime(record.created_at)) + '</td>',
            '</tr>'
        ].join('');
    }

    function buildSalesTransactionRow(record, index) {
        var id = record.id || record.local_id || index + 1;

        return [
            '<tr data-sales-transaction-row="' + escapeHtml(id) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td><strong>' + escapeHtml(record.customer_name || record.customer || 'N/A') + '</strong></td>',
            '  <td>' + escapeHtml(record.phone || 'N/A') + '</td>',
            '  <td>' + escapeHtml(record.type || '') + '</td>',
            '  <td>' + escapeHtml(record.sub_total || '') + '</td>',
            '  <td>' + escapeHtml(record.discount || '') + '</td>',
            '  <td>' + escapeHtml(record.net_price || '') + '</td>',
            '  <td>' + escapeHtml(record.paid_amount || '') + '</td>',
            '  <td>' + escapeHtml(record.payment_method || '') + '</td>',
            '  <td>' + escapeHtml(record.seller || '') + '</td>',
            '  <td>' + escapeHtml(formatDateTime(record.created_at || record.paid_date)) + '</td>',
            '  <td>' + statusBadge(record) + '</td>',
            '</tr>'
        ].join('');
    }

    function buildQuotationRow(record, index) {
        var id = record.id || record.local_id || index + 1;

        return [
            '<tr data-quotation-row="' + escapeHtml(id) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td><strong>' + escapeHtml(record.customer_name || record.customer || 'N/A') + '</strong></td>',
            '  <td>' + escapeHtml(record.phone || 'N/A') + '</td>',
            '  <td>' + escapeHtml(record.sub_total || '') + '</td>',
            '  <td>' + escapeHtml(record.discount || '') + '</td>',
            '  <td>' + escapeHtml(record.net_price || '') + '</td>',
            '  <td>' + escapeHtml(formatDateTime(record.date)) + '</td>',
            '  <td>' + escapeHtml(record.info || '') + '</td>',
            '  <td>' + statusBadge(record) + '</td>',
            '</tr>'
        ].join('');
    }

    function buildQuotationOrderRow(record, index) {
        return [
            '<tr data-quotation-order-row="' + escapeHtml(record.id || record.local_id || index + 1) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td><strong>' + escapeHtml(record.pro || record.product_name || '') + '</strong></td>',
            '  <td>' + escapeHtml(record.qty || '') + '</td>',
            '  <td>' + escapeHtml(record.unit || '') + '</td>',
            '  <td>' + escapeHtml(record.price || '') + '</td>',
            '  <td>' + escapeHtml(record.total || '') + '</td>',
            '  <td>' + escapeHtml(record.transID || '') + '</td>',
            '  <td>' + escapeHtml(formatDateTime(record.created_at)) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderSalesListOffline() {
        var repo = getRepository('sales');

        if (!repo) {
            return Promise.resolve([]);
        }

        showBanner('Sales data is being read from IndexedDB.');

        return repo.getAll().then(function (records) {
            return renderRows('#salesPaymentTable', salesListFilter(records), buildSalesListRow, 'No offline sales records found.');
        });
    }

    function renderSalesTransactionsOffline() {
        var repo = getRepository('sales_transactions');

        if (!repo) {
            return Promise.resolve([]);
        }

        showBanner('Sales transactions are being read from IndexedDB.');

        return repo.getAll().then(function (records) {
            return renderRows('#salesPaymentTable', salesTransactionsFilter(records), buildSalesTransactionRow, 'No offline sales transactions found.');
        });
    }

    function renderQuotationListOffline() {
        var repo = getRepository('quotation');

        if (!repo) {
            return Promise.resolve([]);
        }

        showBanner('Quotations are being read from IndexedDB.');

        return repo.getAll().then(function (records) {
            return renderRows('#quotationLieTable', quotationFilter(records), buildQuotationRow, 'No offline quotation records found.');
        });
    }

    function renderQuotationOrdersOffline() {
        var repo = getRepository('quotation_orders');

        if (!repo) {
            return Promise.resolve([]);
        }

        showBanner('Quotation orders are being read from IndexedDB.');

        return repo.getAll().then(function (records) {
            return renderRows('#QuotationOrdersTable', quotationOrdersFilter(records), buildQuotationOrderRow, 'No offline quotation order records found.');
        });
    }

    function renderCurrentOfflinePage() {
        var mode = currentMode();

        if (mode === 'sales-list') {
            return renderSalesListOffline();
        }

        if (mode === 'sales-transactions') {
            return renderSalesTransactionsOffline();
        }

        if (mode === 'quotation-list') {
            return renderQuotationListOffline();
        }

        if (mode === 'quotation-orders') {
            return renderQuotationOrdersOffline();
        }

        return Promise.resolve([]);
    }

    function initSalesListOnline() {
        var config = getConfig('sales');
        var selector = '#salesPaymentTable';

        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return renderSalesListOffline();
        }

        if (global.jQuery.fn.DataTable.isDataTable(selector)) {
            salesListTable = global.jQuery(selector).DataTable();
            return Promise.resolve(salesListTable);
        }

        salesListTable = global.jQuery(selector).DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: config.indexRoute || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.name = getValue('name');
                    d.phone = getValue('phone');
                    d.startDate = getValue('startDate');
                    d.endDate = getValue('endDate');
                },
                dataSrc: function (json) {
                    var rows = Array.isArray(json && json.data) ? json.data : [];
                    cacheOnlineRows('sales-list', rows).catch(function () {});
                    return rows;
                },
                error: function () {
                    renderSalesListOffline();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'product_name', name: 'product_name' },
                { data: 'quantity', name: 'quantity' },
                { data: 'price', name: 'price' },
                { data: 'total_price', name: 'total_price' },
                { data: 'sales_transaction_id', name: 'sales_transaction_id' },
                { data: 'created_at', name: 'created_at' }
            ]
        });

        return Promise.resolve(salesListTable);
    }

    function initSalesTransactionsOnline() {
        var config = getConfig('sales');
        var selector = '#salesPaymentTable';

        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return renderSalesTransactionsOffline();
        }

        if (global.jQuery.fn.DataTable.isDataTable(selector)) {
            salesTransactionsTable = global.jQuery(selector).DataTable();
            return Promise.resolve(salesTransactionsTable);
        }

        salesTransactionsTable = global.jQuery(selector).DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: config.transactionsRoute || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.name = getValue('name');
                    d.phone = getValue('phone');
                    d.type = getValue('type');
                    d.startDate = getValue('startDate');
                    d.endDate = getValue('endDate');
                    d.seller = getValue('seller');
                },
                dataSrc: function (json) {
                    var rows = Array.isArray(json && json.data) ? json.data : [];
                    cacheOnlineRows('sales-transactions', rows).catch(function () {});
                    return rows;
                },
                error: function () {
                    renderSalesTransactionsOffline();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'customer', name: 'customer' },
                { data: 'phone', name: 'phone' },
                { data: 'type', name: 'type' },
                { data: 'sub_total', name: 'sub_total' },
                { data: 'discount', name: 'discount' },
                { data: 'net_price', name: 'net_price' },
                { data: 'paid_amount', name: 'paid_amount' },
                { data: 'payment_method', name: 'payment_method' },
                { data: 'seller', name: 'seller' },
                { data: 'created_at', name: 'created_at' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        var deleteUrl = (config.deleteBaseUrl || '').replace(/\/$/, '') + '/' + data;
                        var invoiceUrl = String(config.transactionInvoiceTemplate || '').replace(':id', data);

                        return [
                            '<a href="' + escapeHtml(invoiceUrl) + '" target="_blank" class="btn btn-rounded btn-sm bg-outline-light me-2"><i class="fas fa-print"></i></a>',
                            '<form id="deleteForm-' + escapeHtml(data) + '" action="' + escapeHtml(deleteUrl) + '" method="POST" style="display:inline;">',
                            '  <input type="hidden" name="_token" value="' + escapeHtml(document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '') + '">',
                            '  <input type="hidden" name="_method" value="DELETE">',
                            '  <button type="button" class="btn btn-rounded btn-sm bg-outline-light me-2" onclick="confirmSalesTransactionDelete(' + escapeHtml(data) + ')"><i class="fas fa-trash"></i></button>',
                            '</form>'
                        ].join('');
                    }
                }
            ]
        });

        return Promise.resolve(salesTransactionsTable);
    }

    function initQuotationListOnline() {
        var config = getConfig('quotation');
        var selector = '#quotationLieTable';

        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return renderQuotationListOffline();
        }

        if (global.jQuery.fn.DataTable.isDataTable(selector)) {
            quotationListTable = global.jQuery(selector).DataTable();
            return Promise.resolve(quotationListTable);
        }

        quotationListTable = global.jQuery(selector).DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: config.indexRoute || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.name = getValue('name');
                    d.phone = getValue('phone');
                    d.startDate = getValue('startDate');
                    d.endDate = getValue('endDate');
                },
                dataSrc: function (json) {
                    var rows = Array.isArray(json && json.data) ? json.data : [];
                    cacheOnlineRows('quotation-list', rows).catch(function () {});
                    return rows;
                },
                error: function () {
                    renderQuotationListOffline();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'customer', name: 'customer' },
                { data: 'phone', name: 'phone' },
                { data: 'sub_total', name: 'sub_total' },
                { data: 'discount', name: 'discount' },
                { data: 'net_price', name: 'net_price' },
                { data: 'date', name: 'date' },
                { data: 'info', name: 'info' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        var deleteUrl = (config.deleteBaseUrl || '').replace(/\/$/, '') + '/' + data;
                        var invoiceUrl = String(config.invoiceTemplate || '').replace(':id', data);

                        return [
                            '<a href="' + escapeHtml(invoiceUrl) + '" target="_blank" class="btn btn-rounded btn-sm bg-outline-light me-2"><i class="fas fa-print"></i></a>',
                            '<form id="deleteForm-' + escapeHtml(data) + '" action="' + escapeHtml(deleteUrl) + '" method="POST" style="display:inline;">',
                            '  <input type="hidden" name="_token" value="' + escapeHtml(document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '') + '">',
                            '  <input type="hidden" name="_method" value="DELETE">',
                            '  <button type="button" class="btn btn-rounded btn-sm bg-outline-light me-2" onclick="confirmQuotationDelete(' + escapeHtml(data) + ')"><i class="fas fa-trash"></i></button>',
                            '</form>'
                        ].join('');
                    }
                }
            ]
        });

        return Promise.resolve(quotationListTable);
    }

    function initQuotationOrdersOnline() {
        var config = getConfig('quotation');
        var selector = '#QuotationOrdersTable';

        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return renderQuotationOrdersOffline();
        }

        if (global.jQuery.fn.DataTable.isDataTable(selector)) {
            quotationOrdersTable = global.jQuery(selector).DataTable();
            return Promise.resolve(quotationOrdersTable);
        }

        quotationOrdersTable = global.jQuery(selector).DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: config.ordersRoute || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.name = getValue('name');
                    d.phone = getValue('transID');
                    d.startDate = getValue('startDate');
                    d.endDate = getValue('endDate');
                },
                dataSrc: function (json) {
                    var rows = Array.isArray(json && json.data) ? json.data : [];
                    cacheOnlineRows('quotation-orders', rows).catch(function () {});
                    return rows;
                },
                error: function () {
                    renderQuotationOrdersOffline();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'pro', name: 'pro' },
                { data: 'qty', name: 'qty' },
                { data: 'unit', name: 'unit' },
                { data: 'price', name: 'price' },
                { data: 'total', name: 'total' },
                { data: 'transID', name: 'transID' },
                { data: 'created_at', name: 'created_at' }
            ]
        });

        return Promise.resolve(quotationOrdersTable);
    }

    function getSearchRoute(context, type) {
        var config = getConfig(context);

        if (type === 'customer') {
            return config.searchCustomerRoute || '';
        }

        if (type === 'product') {
            return config.searchProductRoute || '';
        }

        return '';
    }

    function renderCustomerDropdown(dropdownId, records, context) {
        var dropdown = document.getElementById(dropdownId);
        var items = Array.isArray(records) ? records : [];

        if (!dropdown) {
            return;
        }

        if (!items.length) {
            dropdown.innerHTML = '<div class="dropdown-item text-center text-muted">No results found</div>';
            showDropdown(dropdownId);
            return;
        }

        dropdown.innerHTML = items.map(function (record) {
            var label = record.customer_name || record.name || '';
            var phone = record.phone ? ' <small class="text-muted">(' + escapeHtml(record.phone) + ')</small>' : '';
            var serial = record.serial ? ' <small class="text-muted">[' + escapeHtml(record.serial) + ']</small>' : '';
            var className = context === 'sales' ? 'js-sales-customer-row' : 'js-quotation-customer-row';

            return [
                '<a href="#" class="dropdown-item ' + className + '" ',
                'data-id="' + escapeHtml(record.id || record.local_id || '') + '" ',
                'data-name="' + escapeHtml(label) + '" ',
                'data-phone="' + escapeHtml(record.phone || '') + '" ',
                'data-serial="' + escapeHtml(record.serial || '') + '">',
                escapeHtml(label),
                phone,
                serial,
                '</a>'
            ].join('');
        }).join('');

        showDropdown(dropdownId);
    }

    function renderProductDropdown(dropdownId, records, context) {
        var dropdown = document.getElementById(dropdownId);
        var items = Array.isArray(records) ? records : [];

        if (!dropdown) {
            return;
        }

        if (!items.length) {
            dropdown.innerHTML = '<div class="dropdown-item text-center text-muted">No results found</div>';
            showDropdown(dropdownId);
            return;
        }

        dropdown.innerHTML = items.map(function (record) {
            var label = record.name || record.product_name || '';
            var className = context === 'sales' ? 'js-sales-product-row' : 'js-quotation-product-row';

            return [
                '<a href="#" class="dropdown-item ' + className + '" ',
                'data-id="' + escapeHtml(record.id || record.local_id || '') + '" ',
                'data-name="' + escapeHtml(label) + '" ',
                'data-unit="' + escapeHtml(record.unit || '') + '" ',
                'data-quantity="' + escapeHtml(record.quantity || '') + '" ',
                'data-price="' + escapeHtml(record.selling_price || record.price || 0) + '" ',
                'data-type="' + escapeHtml(record.type || '') + '">',
                escapeHtml(label),
                record.unit ? ' <small class="text-muted">(' + escapeHtml(record.unit) + ')</small>' : '',
                typeof record.quantity !== 'undefined' ? ' <small class="text-muted">Stock: ' + escapeHtml(record.quantity) + '</small>' : '',
                '</a>'
            ].join('');
        }).join('');

        showDropdown(dropdownId);
    }

    function searchCustomers(context, query) {
        var route = getSearchRoute(context, 'customer');

        if (isOnline() && route && global.axios) {
            return global.axios.get(route + '?query=' + encodeURIComponent(query)).then(function (response) {
                var rows = Array.isArray(response && response.data) ? response.data : [];
                return cacheOnlineRows(context === 'sales' ? 'sales-customer-search' : 'quotation-customer-search', rows).then(function () {
                    return rows.map(normalizeCustomerRecord);
                });
            }).catch(function () {
                var repo = getRepository('customers');
                if (!repo) {
                    return [];
                }

                return repo.search(query).catch(function () {
                    return [];
                });
            });
        }

        var repo = getRepository('customers');
        if (!repo) {
            return Promise.resolve([]);
        }

        return repo.search(query).catch(function () {
            return [];
        });
    }

    function searchProducts(context, query) {
        var route = getSearchRoute(context, 'product');

        if (isOnline() && route && global.axios) {
            return global.axios.get(route + '?query=' + encodeURIComponent(query)).then(function (response) {
                var rows = Array.isArray(response && response.data) ? response.data : [];
                return cacheOnlineRows(context === 'sales' ? 'sales-product-search' : 'quotation-product-search', rows).then(function () {
                    return rows.map(normalizeProductRecord);
                });
            }).catch(function () {
                var repo = getRepository('products');
                if (!repo) {
                    return [];
                }

                return repo.search(query).catch(function () {
                    return [];
                });
            });
        }

        var repo = getRepository('products');
        if (!repo) {
            return Promise.resolve([]);
        }

        return repo.search(query).catch(function () {
            return [];
        });
    }

    function selectSalesCustomer(record) {
        pageState.selectedSalesCustomer = normalizeCustomerRecord(record);
        setValue('customerSearch', pageState.selectedSalesCustomer.customer_name || '');
        setValue('customerID', pageState.selectedSalesCustomer.id || pageState.selectedSalesCustomer.local_id || '');
        hideDropdown('customerDropdown');
    }

    function selectQuotationCustomer(record) {
        pageState.selectedQuotationCustomer = normalizeCustomerRecord(record);
        setValue('customerSearch', pageState.selectedQuotationCustomer.customer_name || '');
        setValue('phone', pageState.selectedQuotationCustomer.phone || '');
        hideDropdown('customerDropdown');
    }

    function selectQuotationProduct(record) {
        pageState.selectedQuotationProduct = normalizeProductRecord(record);
        setValue('inventorySearch', pageState.selectedQuotationProduct.name || '');
        setValue('proID', pageState.selectedQuotationProduct.id || pageState.selectedQuotationProduct.local_id || '');
        setValue('unit', pageState.selectedQuotationProduct.unit || '');
        setValue('selling_price', formatMoney(pageState.selectedQuotationProduct.selling_price || 0));
        updateQuotationPreview();
        hideDropdown('productDropdown');
        updateQuotationTotal();
    }

    function salesRowHtml(index, product) {
        var quantity = normalizeNumber(product.quantity || 1);
        var price = normalizeNumber(product.selling_price || product.price || 0);
        var total = quantity * price;
        var maxValue = product.type && String(product.type).toLowerCase() === 'service'
            ? 999999999
            : normalizeNumber(product.quantity || quantity);

        return [
            '<tr data-product-id="' + escapeHtml(product.id || product.local_id || index) + '">',
            '  <td>',
            '    <input type="hidden" name="products[' + index + '][proID]" value="' + escapeHtml(product.id || product.local_id || '') + '">',
            '    <span class="js-sales-product-name">' + escapeHtml(product.name || '') + '</span>',
            '  </td>',
            '  <td><input type="number" step="0.01" min="0.01" max="' + escapeHtml(maxValue) + '" name="products[' + index + '][quantity]" class="form-control quantity-input" value="' + escapeHtml(quantity) + '" data-price="' + escapeHtml(price) + '"></td>',
            '  <td><input type="hidden" name="products[' + index + '][unit]" value="' + escapeHtml(product.unit || '') + '">' + escapeHtml(product.unit || '') + '</td>',
            '  <td><input type="number" step="0.01" min="0" name="products[' + index + '][price]" class="form-control price-input" value="' + escapeHtml(formatMoney(price)) + '"></td>',
            '  <td class="total-price-cell">' + escapeHtml(formatMoney(total)) + '<input type="hidden" class="total-price-input" name="products[' + index + '][total_price]" value="' + escapeHtml(formatMoney(total)) + '"></td>',
            '  <td><a href="javascript:void(0);" class="delete-set"><i class="fas fa-trash"></i></a></td>',
            '</tr>'
        ].join('');
    }

    function quotationRowHtml(index, product) {
        var quantity = normalizeNumber(getValue('quantity')) || 1;
        var price = normalizeNumber(getValue('selling_price') || product.selling_price || 0);
        var total = quantity * price;

        return [
            '<tr data-product-id="' + escapeHtml(product.id || product.local_id || index) + '">',
            '  <td>',
            '    <input type="hidden" name="products[' + index + '][proID]" value="' + escapeHtml(product.id || product.local_id || '') + '">',
            '    ' + escapeHtml(product.name || ''),
            '  </td>',
            '  <td><input type="hidden" name="products[' + index + '][quantity]" value="' + escapeHtml(quantity) + '">' + escapeHtml(quantity) + '</td>',
            '  <td><input type="hidden" name="products[' + index + '][unit]" value="' + escapeHtml(product.unit || '') + '">' + escapeHtml(product.unit || '') + '</td>',
            '  <td><input type="hidden" name="products[' + index + '][price]" value="' + escapeHtml(formatMoney(price)) + '">' + escapeHtml(formatMoney(price)) + '</td>',
            '  <td><input type="hidden" name="products[' + index + '][total_price]" value="' + escapeHtml(formatMoney(total)) + '">' + escapeHtml(formatMoney(total)) + '</td>',
            '  <td><a href="javascript:void(0);" class="delete-set">Delete</a></td>',
            '</tr>'
        ].join('');
    }

    function updateSalesRowTotal(row) {
        var quantity = normalizeNumber(row.querySelector('.quantity-input') ? row.querySelector('.quantity-input').value : 0);
        var price = normalizeNumber(row.querySelector('.price-input') ? row.querySelector('.price-input').value : 0);
        var total = quantity * price;
        var totalNode = row.querySelector('.total-price-cell');
        var hidden = row.querySelector('.total-price-input');

        if (totalNode) {
            totalNode.innerHTML = formatMoney(total) + (hidden ? hidden.outerHTML : '');
            hidden = row.querySelector('.total-price-input');
        }

        if (hidden) {
            hidden.value = formatMoney(total);
        }

        updateSalesTotals();
    }

    function updateSalesTotals() {
        var subtotal = 0;

        document.querySelectorAll('#productTableBody tr[data-product-id]').forEach(function (row) {
            var totalInput = row.querySelector('.total-price-input');
            subtotal += normalizeNumber(totalInput ? totalInput.value : 0);
        });

        var discount = normalizeNumber(getValue('discount'));
        var netPrice = subtotal - discount;
        var paidAmount = normalizeNumber(getValue('paid_amount'));
        var balance = netPrice - paidAmount;

        setValue('subtotal', formatMoney(subtotal));
        setValue('net_price', formatMoney(netPrice));
        setValue('balance', formatMoney(balance));
        setValue('paid_amount', formatMoney(paidAmount));

        var summarySubtotal = document.getElementById('summary_subtotal');
        var summaryDiscount = document.getElementById('summary_discount');
        var summaryNetTotal = document.getElementById('summary_net_total');
        var summaryPaidAmount = document.getElementById('summary_paid_amount');
        var summaryBalance = document.getElementById('summary_balance');

        if (summarySubtotal) {
            summarySubtotal.textContent = '$ ' + formatMoney(subtotal);
        }
        if (summaryDiscount) {
            summaryDiscount.textContent = '$ ' + formatMoney(discount);
        }
        if (summaryNetTotal) {
            summaryNetTotal.textContent = '$ ' + formatMoney(netPrice);
        }
        if (summaryPaidAmount) {
            summaryPaidAmount.textContent = '$ ' + formatMoney(paidAmount);
        }
        if (summaryBalance) {
            summaryBalance.textContent = '$ ' + formatMoney(balance);
        }
    }

    function updateQuotationTotal() {
        var subtotal = 0;

        document.querySelectorAll('#productTable tbody tr[data-product-id]').forEach(function (row) {
            var totalInput = row.querySelector('input[name*="[total_price]"]');
            subtotal += normalizeNumber(totalInput ? totalInput.value : 0);
        });

        var discount = normalizeNumber(getValue('discount'));
        var netPrice = subtotal - discount;

        setValue('subtotal', formatMoney(subtotal));
        setValue('net_price', formatMoney(netPrice));

        var summarySubtotal = document.getElementById('summary_subtotal');
        var summaryDiscount = document.getElementById('summary_discount');
        var summaryNetTotal = document.getElementById('summary_net_total');

        if (summarySubtotal) {
            summarySubtotal.textContent = '$ ' + formatMoney(subtotal);
        }
        if (summaryDiscount) {
            summaryDiscount.textContent = '$ ' + formatMoney(discount);
        }
        if (summaryNetTotal) {
            summaryNetTotal.textContent = '$ ' + formatMoney(netPrice);
        }
    }

    function updateQuotationPreview() {
        var quantity = normalizeNumber(getValue('quantity'));
        var sellingPrice = normalizeNumber(getValue('selling_price'));
        var totalPrice = quantity * sellingPrice;

        setValue('total_price', formatMoney(totalPrice));
    }

    function reindexSalesRows() {
        var index = 0;

        document.querySelectorAll('#productTableBody tr[data-product-id]').forEach(function (row) {
            row.querySelectorAll('input').forEach(function (input) {
                var name = input.getAttribute('name');
                if (!name) {
                    return;
                }

                input.setAttribute('name', name.replace(/products\[\d+\]/, 'products[' + index + ']'));
            });

            index++;
        });

        pageState.salesProductIndex = index;
        updateSalesTotals();
    }

    function reindexQuotationRows() {
        var index = 0;

        document.querySelectorAll('#productTable tbody tr[data-product-id]').forEach(function (row) {
            row.querySelectorAll('input').forEach(function (input) {
                var name = input.getAttribute('name');
                if (!name) {
                    return;
                }

                input.setAttribute('name', name.replace(/products\[\d+\]/, 'products[' + index + ']'));
            });

            index++;
        });

        pageState.quotationProductIndex = index;
        updateQuotationTotal();
    }

    function addSalesProductRow(product) {
        var tableBody = document.getElementById('productTableBody');

        if (!tableBody) {
            return;
        }

        var productId = String(product.id || product.local_id || '');
        if (!productId) {
            return;
        }

        if (tableBody.querySelector('tr[data-product-id="' + productId + '"]')) {
            notify('error', 'Duplicate product', 'This product is already in the list.');
            return;
        }

        tableBody.insertAdjacentHTML('beforeend', salesRowHtml(pageState.salesProductIndex, product));
        pageState.salesProductIndex++;
        updateSalesTotals();
    }

    function addQuotationProductRow() {
        var tableBody = document.querySelector('#productTable tbody');
        var product = pageState.selectedQuotationProduct;

        if (!tableBody || !product) {
            notify('error', 'Missing product', 'Please select a product first.');
            return;
        }

        var productId = String(product.id || product.local_id || '');
        if (!productId) {
            return;
        }

        if (tableBody.querySelector('tr[data-product-id="' + productId + '"]')) {
            notify('error', 'Duplicate product', 'This product is already in the list.');
            return;
        }

        tableBody.insertAdjacentHTML('beforeend', quotationRowHtml(pageState.quotationProductIndex, product));
        pageState.quotationProductIndex++;
        setValue('inventorySearch', '');
        setValue('proID', '');
        setValue('unit', '');
        setValue('selling_price', '');
        setValue('quantity', '');
        setValue('total_price', '');
        pageState.selectedQuotationProduct = null;
        updateQuotationTotal();
    }

    function removeRow(row) {
        if (row && row.parentNode) {
            row.parentNode.removeChild(row);
        }
    }

    function handleSalesOfflineSubmit(form) {
        var customerId = getValue('customerID');
        var customerName = getValue('customerSearch');
        var productRepo = getRepository('products');
        var customerRepo = getRepository('customers');
        var salesRepo = getRepository('sales');
        var transactionRepo = getRepository('sales_transactions');
        var creditsRepo = getRepository('credits');

        if (!customerId) {
            return Promise.reject(new Error('Please select a customer.'));
        }

        if (!productRepo || !customerRepo || !salesRepo || !transactionRepo || !creditsRepo) {
            return Promise.reject(new Error('Offline repositories are not ready yet.'));
        }

        var rows = [];
        document.querySelectorAll('#productTableBody tr[data-product-id]').forEach(function (row) {
            var productId = row.getAttribute('data-product-id');
            var quantity = normalizeNumber(row.querySelector('.quantity-input') ? row.querySelector('.quantity-input').value : 0);
            var price = normalizeNumber(row.querySelector('.price-input') ? row.querySelector('.price-input').value : 0);
            var total = normalizeNumber(row.querySelector('.total-price-input') ? row.querySelector('.total-price-input').value : 0);

            rows.push({
                productId: productId,
                quantity: quantity,
                price: price,
                total: total,
                unit: row.querySelector('input[name*="[unit]"]') ? row.querySelector('input[name*="[unit]"]').value : ''
            });
        });

        if (!rows.length) {
            return Promise.reject(new Error('Please add at least one product.'));
        }

        var netPrice = normalizeNumber(getValue('net_price'));
        var paidAmount = normalizeNumber(getValue('paid_amount'));
        var balance = normalizeNumber(getValue('balance'));
        var discount = normalizeNumber(getValue('discount'));
        var dueDate = getValue('due_date') || nowIso().slice(0, 10);
        var paymentMethod = getValue('paymentMethod');
        var note = document.querySelector('textarea[name="note"]') ? document.querySelector('textarea[name="note"]').value : '';
        var depID = getValue('depID') || (global.__OFFLINE_ENGINE_CONFIG__ && global.__OFFLINE_ENGINE_CONFIG__.departmentId ? global.__OFFLINE_ENGINE_CONFIG__.departmentId : null);
        var transactionLocalId = uuid();
        var customerRecord = null;

        return customerRepo.findById(customerId).then(function (record) {
            customerRecord = record;

            if (!customerRecord) {
                throw new Error('The selected customer is not cached locally yet.');
            }

            if (isCashCustomer(customerRecord) && balance > 0) {
                throw new Error('Cash sales must be paid in full.');
            }

            var validationPromise = rows.reduce(function (promise, row) {
                return promise.then(function () {
                    return productRepo.findById(row.productId).then(function (product) {
                        if (!product) {
                            throw new Error('Product "' + row.productId + '" is not cached locally yet.');
                        }

                        var available = normalizeNumber(product.quantity);
                        if (String(product.type || '').toLowerCase() !== 'service' && row.quantity > available) {
                            throw new Error('Quantity exceeds the available local stock for ' + (product.name || 'the selected product') + '.');
                        }

                        return null;
                    });
                });
            }, Promise.resolve());

            return validationPromise.then(function () {
                var transactionRecord = {
                    local_id: transactionLocalId,
                    server_id: null,
                    customerID: customerRecord.id || customerRecord.local_id,
                    customer_name: customerRecord.customer_name || customerRecord.name || '',
                    customer: customerRecord.customer_name || customerRecord.name || '',
                    phone: customerRecord.phone || '',
                    sub_total: formatMoney(rows.reduce(function (sum, row) { return sum + row.total; }, 0)),
                    discount: formatMoney(discount),
                    net_price: formatMoney(netPrice),
                    paid_amount: formatMoney(paidAmount),
                    balance: formatMoney(balance),
                    paid_date: dueDate,
                    type: balance > 0 ? (paidAmount > 0 ? 'Cash & Credit' : 'Credit') : 'Cash',
                    payment_method: paymentMethod,
                    depID: depID,
                    note: note,
                    seller: global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.userId !== 'undefined'
                        ? global.__OFFLINE_ENGINE_CONFIG__.userId
                        : null,
                    synced: false,
                    sync_status: 'pending',
                    local_action: 'create',
                    created_at: nowIso(),
                    updated_at: nowIso()
                };

                return transactionRepo.create(transactionRecord).then(function (savedTransaction) {
                    var savedTransactionId = (savedTransaction && (savedTransaction.local_id || savedTransaction.id)) || transactionLocalId;
                    var sequence = Promise.resolve();

                    rows.forEach(function (row) {
                        sequence = sequence.then(function () {
                            return salesRepo.create({
                                local_id: uuid(),
                                server_id: null,
                                proID: row.productId,
                                product_name: '',
                                customerID: customerRecord.id || customerRecord.local_id,
                                customer_name: customerRecord.customer_name || customerRecord.name || '',
                                quantity: row.quantity,
                                unit: row.unit,
                                price: row.price,
                                total_price: row.total,
                                sales_transaction_id: savedTransactionId,
                                depID: depID,
                                synced: false,
                                sync_status: 'pending',
                                local_action: 'create',
                                created_at: nowIso(),
                                updated_at: nowIso()
                            }).then(function () {
                                return productRepo.findById(row.productId).then(function (product) {
                                    if (!product) {
                                        throw new Error('Product not found locally while updating stock.');
                                    }

                                    var nextQuantity = normalizeNumber(product.quantity) - row.quantity;
                                    return productRepo.update(product.id || product.local_id || product.server_id || row.productId, {
                                        quantity: nextQuantity,
                                        updated_at: nowIso()
                                    });
                                });
                            });
                        });
                    });

                    if (balance > 0) {
                        sequence = sequence.then(function () {
                            return customerRepo.findById(customerId).then(function (customerBeforeUpdate) {
                                var currentBalance = normalizeNumber(customerBeforeUpdate && customerBeforeUpdate.balance);
                                return creditsRepo.create({
                                    local_id: uuid(),
                                    server_id: null,
                                    customerID: customerBeforeUpdate ? (customerBeforeUpdate.id || customerBeforeUpdate.local_id) : customerId,
                                    name: customerRecord.customer_name || customerRecord.name || '',
                                    customer_name: customerRecord.customer_name || customerRecord.name || '',
                                    amount: balance,
                                    pbalance: currentBalance,
                                    current: currentBalance + balance,
                                    discount: discount,
                                    type: 'Credit',
                                    date: dueDate,
                                    payment_method: paymentMethod,
                                    depID: depID,
                                    seller: global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.userId !== 'undefined'
                                        ? global.__OFFLINE_ENGINE_CONFIG__.userId
                                        : null,
                                    reference: savedTransactionId,
                                    status: 'pending',
                                    synced: false,
                                    sync_status: 'pending',
                                    local_action: 'create',
                                    created_at: nowIso(),
                                    updated_at: nowIso()
                                }).then(function () {
                                    return customerRepo.update(customerId, {
                                        balance: currentBalance + balance,
                                        updated_at: nowIso()
                                    });
                                });
                            });
                        });
                    }

                    return sequence.then(function () {
                        notify('success', 'Saved offline', 'The sale was stored locally and queued for synchronization.');
                        resetSalesForm(form);
                        return savedTransaction;
                    });
                });
            });
        });
    }

    function handleQuotationOfflineSubmit(form) {
        var customerName = getValue('customerSearch');
        var phone = getValue('phone');
        var productRepo = getRepository('products');
        var quotationRepo = getRepository('quotation');
        var quotationOrderRepo = getRepository('quotation_orders');

        if (!quotationRepo || !quotationOrderRepo || !productRepo) {
            return Promise.reject(new Error('Offline repositories are not ready yet.'));
        }

        var rows = [];
        document.querySelectorAll('#productTable tbody tr[data-product-id]').forEach(function (row) {
            var productId = row.getAttribute('data-product-id');
            var quantity = normalizeNumber(row.querySelector('input[name*="[quantity]"]') ? row.querySelector('input[name*="[quantity]"]').value : 0);
            var price = normalizeNumber(row.querySelector('input[name*="[price]"]') ? row.querySelector('input[name*="[price]"]').value : 0);
            var total = normalizeNumber(row.querySelector('input[name*="[total_price]"]') ? row.querySelector('input[name*="[total_price]"]').value : 0);

            rows.push({
                productId: productId,
                quantity: quantity,
                price: price,
                total: total,
                unit: row.querySelector('input[name*="[unit]"]') ? row.querySelector('input[name*="[unit]"]').value : ''
            });
        });

        if (!customerName) {
            return Promise.reject(new Error('Please enter a customer name.'));
        }

        if (!rows.length) {
            return Promise.reject(new Error('Please add at least one product.'));
        }

        var transactionLocalId = uuid();
        var subtotal = rows.reduce(function (sum, row) { return sum + row.total; }, 0);
        var discount = normalizeNumber(getValue('discount'));
        var netPrice = normalizeNumber(getValue('net_price') || (subtotal - discount));
        var dueDate = getValue('due_date') || nowIso().slice(0, 10);
        var depID = global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.departmentId !== 'undefined'
            ? global.__OFFLINE_ENGINE_CONFIG__.departmentId
            : null;

        return quotationRepo.create({
            local_id: transactionLocalId,
            server_id: null,
            customer: customerName,
            customer_name: customerName,
            phone: phone,
            sub_total: formatMoney(subtotal),
            discount: formatMoney(discount),
            net_price: formatMoney(netPrice),
            info: 'Offline quotation',
            date: dueDate,
            depID: depID,
            synced: false,
            sync_status: 'pending',
            local_action: 'create',
            created_at: nowIso(),
            updated_at: nowIso()
        }).then(function (savedQuotation) {
            var savedId = (savedQuotation && (savedQuotation.local_id || savedQuotation.id)) || transactionLocalId;
            var sequence = Promise.resolve();

            rows.forEach(function (row) {
                sequence = sequence.then(function () {
                    return productRepo.findById(row.productId).then(function (product) {
                        if (!product) {
                            throw new Error('Product "' + row.productId + '" is not cached locally yet.');
                        }

                        return quotationOrderRepo.create({
                            local_id: uuid(),
                            server_id: null,
                            transID: savedId,
                            proID: product.id || product.local_id || row.productId,
                            pro: product.name || product.product_name || '',
                            product_name: product.name || product.product_name || '',
                            qty: row.quantity,
                            unit: row.unit || product.unit || '',
                            price: row.price,
                            total: row.total,
                            depID: depID,
                            synced: false,
                            sync_status: 'pending',
                            local_action: 'create',
                            created_at: nowIso(),
                            updated_at: nowIso()
                        });
                    });
                });
            });

            return sequence.then(function () {
                notify('success', 'Saved offline', 'The quotation was stored locally and queued for synchronization.');
                resetQuotationForm(form);
                return savedQuotation;
            });
        });
    }

    function recordSalesReturnOffline(payload) {
        var data = Object.assign({}, payload || {});
        var productRepo = getRepository('products');
        var salesReturnRepo = getRepository('sales_returns');

        if (!productRepo || !salesReturnRepo) {
            return Promise.reject(new Error('Offline repositories are not ready yet.'));
        }

        if (!data.proID && data.product_id) {
            data.proID = data.product_id;
        }

        if (!data.sales_id && data.sale_id) {
            data.sales_id = data.sale_id;
        }

        return productRepo.findById(data.proID).then(function (product) {
            if (!product) {
                throw new Error('The product for this sales return is not cached locally yet.');
            }

            var quantity = normalizeNumber(data.quantity);
            var nextQuantity = normalizeNumber(product.quantity) + quantity;

            return salesReturnRepo.create({
                local_id: uuid(),
                server_id: null,
                sales_id: data.sales_id || null,
                sales_transaction_id: data.sales_transaction_id || null,
                proID: data.proID,
                product_name: product.name || product.product_name || '',
                quantity: quantity,
                reason: data.reason || 'Offline sales return',
                refund_amount: normalizeNumber(data.refund_amount),
                return_date: data.return_date || nowIso().slice(0, 10),
                synced: false,
                sync_status: 'pending',
                local_action: 'create',
                created_at: nowIso(),
                updated_at: nowIso()
            }).then(function () {
                return productRepo.update(product.id || product.local_id || product.server_id || data.proID, {
                    quantity: nextQuantity,
                    updated_at: nowIso()
                });
            });
        });
    }

    function resetSalesForm(form) {
        if (!form) {
            return;
        }

        form.reset();
        pageState.selectedSalesCustomer = null;
        pageState.salesProductIndex = 0;
        clearElement('productTableBody');
        hideDropdown('customerDropdown');
        hideDropdown('productDropdown');
        setValue('subtotal', '0.00');
        setValue('net_price', '0.00');
        setValue('balance', '0.00');
        setValue('paid_amount', '');
        setValue('discount', '0.00');
        setValue('customerID', '');
        if (document.getElementById('summary_subtotal')) {
            document.getElementById('summary_subtotal').textContent = '$ 0.00';
        }
        if (document.getElementById('summary_discount')) {
            document.getElementById('summary_discount').textContent = '$ 0.00';
        }
        if (document.getElementById('summary_net_total')) {
            document.getElementById('summary_net_total').textContent = '$ 0.00';
        }
        if (document.getElementById('summary_paid_amount')) {
            document.getElementById('summary_paid_amount').textContent = '$ 0.00';
        }
        if (document.getElementById('summary_balance')) {
            document.getElementById('summary_balance').textContent = '$ 0.00';
        }
    }

    function resetQuotationForm(form) {
        if (!form) {
            return;
        }

        form.reset();
        pageState.selectedQuotationCustomer = null;
        pageState.selectedQuotationProduct = null;
        pageState.quotationProductIndex = 0;
        var tbody = document.querySelector('#productTable tbody');
        if (tbody) {
            tbody.innerHTML = '';
        }
        hideDropdown('customerDropdown');
        hideDropdown('productDropdown');
        setValue('subtotal', '0.00');
        setValue('net_price', '0.00');
        setValue('discount', '');
        if (document.getElementById('summary_subtotal')) {
            document.getElementById('summary_subtotal').textContent = '$ 0.00';
        }
        if (document.getElementById('summary_discount')) {
            document.getElementById('summary_discount').textContent = '$ 0.00';
        }
        if (document.getElementById('summary_net_total')) {
            document.getElementById('summary_net_total').textContent = '$ 0.00';
        }
    }

    function handleCustomerInput(event) {
        var input = event.target;

        if (!input || input.id !== 'customerSearch') {
            return;
        }

        var mode = currentMode();
        if (mode !== 'sales-create' && mode !== 'quotation-create') {
            return;
        }

        var context = mode === 'sales-create' ? 'sales' : 'quotation';
        var query = String(input.value || '').trim();

        if (mode === 'sales-create') {
            pageState.selectedSalesCustomer = null;
            setValue('customerID', '');
        } else {
            pageState.selectedQuotationCustomer = null;
        }

        if (query.length < 2) {
            hideDropdown('customerDropdown');
            return;
        }

        showDropdown('customerDropdown');
        clearElement('customerDropdown');
        document.getElementById('customerDropdown').innerHTML = '<div class="dropdown-item text-center"><i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...</div>';

        searchCustomers(context, query).then(function (records) {
            renderCustomerDropdown('customerDropdown', records, context);
        }).catch(function () {
            hideDropdown('customerDropdown');
            notify('error', 'Search failed', 'Unable to search customers right now.');
        });
    }

    function handleProductInput(event) {
        var input = event.target;
        var mode = currentMode();

        if (!input) {
            return;
        }

        if (mode === 'sales-create' && input.id === 'productSearch') {
            var query = String(input.value || '').trim();
            if (query.length < 2) {
                hideDropdown('productDropdown');
                return;
            }

            showDropdown('productDropdown');
            document.getElementById('productDropdown').innerHTML = '<div class="dropdown-item text-center"><i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...</div>';

            searchProducts('sales', query).then(function (records) {
                renderProductDropdown('productDropdown', records, 'sales');
            }).catch(function () {
                hideDropdown('productDropdown');
                notify('error', 'Search failed', 'Unable to search products right now.');
            });
            return;
        }

        if (mode === 'quotation-create') {
            if (input.id === 'inventorySearch') {
                var productQuery = String(input.value || '').trim();
                if (productQuery.length < 2) {
                    hideDropdown('productDropdown');
                    return;
                }

                showDropdown('productDropdown');
                document.getElementById('productDropdown').innerHTML = '<div class="dropdown-item text-center"><i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...</div>';

                searchProducts('quotation', productQuery).then(function (records) {
                    renderProductDropdown('productDropdown', records, 'quotation');
                }).catch(function () {
                    hideDropdown('productDropdown');
                    notify('error', 'Search failed', 'Unable to search products right now.');
                });
                return;
            }

            if (input.id === 'quantity' || input.id === 'selling_price') {
                updateQuotationPreview();
                return;
            }

            if (input.id === 'discount') {
                updateQuotationTotal();
                return;
            }
        }

        if (mode === 'sales-create') {
            if (input.classList && input.classList.contains('quantity-input')) {
                updateSalesRowTotal(input.closest('tr'));
            }

            if (input.classList && input.classList.contains('price-input')) {
                updateSalesRowTotal(input.closest('tr'));
            }

            if (input.id === 'discount' || input.id === 'paid_amount') {
                updateSalesTotals();
            }
        }
    }

    function handleDropdownClick(event) {
        var target = event.target.closest('.js-sales-customer-row, .js-quotation-customer-row, .js-sales-product-row, .js-quotation-product-row');

        if (!target) {
            return;
        }

        event.preventDefault();

        if (target.classList.contains('js-sales-customer-row')) {
            selectSalesCustomer({
                id: target.getAttribute('data-id'),
                customer_name: target.getAttribute('data-name'),
                phone: target.getAttribute('data-phone'),
                serial: target.getAttribute('data-serial')
            });
            return;
        }

        if (target.classList.contains('js-quotation-customer-row')) {
            selectQuotationCustomer({
                id: target.getAttribute('data-id'),
                customer_name: target.getAttribute('data-name'),
                phone: target.getAttribute('data-phone'),
                serial: target.getAttribute('data-serial')
            });
            return;
        }

        if (target.classList.contains('js-sales-product-row')) {
            addSalesProductRow({
                id: target.getAttribute('data-id'),
                name: target.getAttribute('data-name'),
                unit: target.getAttribute('data-unit'),
                quantity: target.getAttribute('data-quantity'),
                selling_price: target.getAttribute('data-price'),
                type: target.getAttribute('data-type')
            });
            setValue('productSearch', '');
            hideDropdown('productDropdown');
            return;
        }

        if (target.classList.contains('js-quotation-product-row')) {
            selectQuotationProduct({
                id: target.getAttribute('data-id'),
                name: target.getAttribute('data-name'),
                unit: target.getAttribute('data-unit'),
                quantity: target.getAttribute('data-quantity'),
                selling_price: target.getAttribute('data-price'),
                type: target.getAttribute('data-type')
            });
        }
    }

    function handleTableDelete(event) {
        var target = event.target.closest('.delete-set');

        if (!target) {
            return;
        }

        event.preventDefault();

        var row = target.closest('tr');
        removeRow(row);

        if (document.querySelector('form[data-sales-form="create"]')) {
            reindexSalesRows();
        }

        if (document.querySelector('form[data-quotation-form="create"]')) {
            reindexQuotationRows();
        }
    }

    function handleQuickCustomerSubmit(form) {
        if (isOnline()) {
            if (global.jQuery) {
                return global.jQuery.ajax({
                    url: form.getAttribute('action'),
                    type: 'POST',
                    data: global.jQuery(form).serialize()
                }).done(function (response) {
                    if (response && response.customer) {
                        var customerRecord = normalizeCustomerRecord({
                            id: response.customer.id,
                            customer_name: response.customer.customer_name || response.customer.name || '',
                            phone: response.customer.phone || '',
                            serial: response.serial || response.customer.serial || ''
                        });

                        if (document.querySelector('form[data-sales-form="create"]')) {
                            selectSalesCustomer(customerRecord);
                        }
                    }

                    if (global.jQuery && global.jQuery.fn && global.jQuery.fn.modal) {
                        global.jQuery('#addCustomerModal').modal('hide');
                    }

                    if (global.toastr && typeof global.toastr.success === 'function') {
                        global.toastr.success('Customer added successfully!');
                    } else {
                        notify('success', 'Success', 'Customer added successfully!');
                    }
                    form.reset();
                }).fail(function (xhr) {
                    var message = 'An error occurred. Please try again.';
                    if (xhr && xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        message = Object.keys(errors).map(function (key) {
                            return errors[key][0];
                        }).join(' ');
                    }
                    notify('error', 'Error', message);
                });
            }

            return Promise.resolve();
        }

        var repo = getRepository('customers');

        if (!repo) {
            return Promise.reject(new Error('Customer repository is not ready yet.'));
        }

        var customerName = form.querySelector('[name="customer_name"]') ? form.querySelector('[name="customer_name"]').value : '';
        var phone = form.querySelector('[name="phone"]') ? form.querySelector('[name="phone"]').value : '';

        return repo.create({
            local_id: uuid(),
            server_id: null,
            customer_name: customerName,
            name: customerName,
            phone: phone,
            serial: 'OFF-' + uuid().slice(0, 8).toUpperCase(),
            depID: global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.departmentId !== 'undefined'
                ? global.__OFFLINE_ENGINE_CONFIG__.departmentId
                : null,
            synced: false,
            sync_status: 'pending',
            local_action: 'create',
            created_at: nowIso(),
            updated_at: nowIso()
        }).then(function (savedCustomer) {
            if (document.querySelector('form[data-sales-form="create"]')) {
                selectSalesCustomer(savedCustomer);
            }

            if (global.jQuery && global.jQuery.fn && global.jQuery.fn.modal) {
                global.jQuery('#addCustomerModal').modal('hide');
            }

            if (global.toastr && typeof global.toastr.success === 'function') {
                global.toastr.success('Customer added offline and queued for synchronization.');
            } else {
                notify('success', 'Saved offline', 'Customer added offline and queued for synchronization.');
            }

            form.reset();
            return savedCustomer;
        });
    }

    function bindCreatePages() {
        if (document.querySelector('form[data-sales-form="create"]') && document.getElementById('salesForm').dataset.salesOfflineBound !== 'true') {
            document.getElementById('salesForm').dataset.salesOfflineBound = 'true';
            updateSalesTotals();
            setValue('discount', getValue('discount') || '0.00');
            setValue('paid_amount', getValue('paid_amount') || '0.00');
        }

        if (document.querySelector('form[data-quotation-form="create"]') && document.getElementById('salesForm').dataset.quotationOfflineBound !== 'true') {
            document.getElementById('salesForm').dataset.quotationOfflineBound = 'true';
            updateQuotationTotal();
        }
    }

    function bindListControls() {
        var searchButton = document.getElementById('searchBtn');

        if (searchButton && searchButton.dataset.salesQuotationOfflineBound !== 'true') {
            searchButton.dataset.salesQuotationOfflineBound = 'true';
            searchButton.addEventListener('click', function (event) {
                event.preventDefault();

                if (isOnline()) {
                    if (currentMode() === 'sales-list' && salesListTable && typeof salesListTable.draw === 'function') {
                        salesListTable.draw();
                        return;
                    }

                    if (currentMode() === 'sales-transactions' && salesTransactionsTable && typeof salesTransactionsTable.draw === 'function') {
                        salesTransactionsTable.draw();
                        return;
                    }

                    if (currentMode() === 'quotation-list' && quotationListTable && typeof quotationListTable.draw === 'function') {
                        quotationListTable.draw();
                        return;
                    }

                    if (currentMode() === 'quotation-orders' && quotationOrdersTable && typeof quotationOrdersTable.draw === 'function') {
                        quotationOrdersTable.draw();
                        return;
                    }
                }

                renderCurrentOfflinePage();
            });
        }

        ['name', 'phone', 'type', 'seller', 'startDate', 'endDate', 'transID'].forEach(function (fieldId) {
            var field = document.getElementById(fieldId);

            if (!field || field.dataset.salesQuotationOfflineBound === 'true') {
                return;
            }

            field.dataset.salesQuotationOfflineBound = 'true';
            field.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                if (isOnline()) {
                    if (currentMode() === 'sales-list' && salesListTable && typeof salesListTable.draw === 'function') {
                        salesListTable.draw();
                        return;
                    }

                    if (currentMode() === 'sales-transactions' && salesTransactionsTable && typeof salesTransactionsTable.draw === 'function') {
                        salesTransactionsTable.draw();
                        return;
                    }

                    if (currentMode() === 'quotation-list' && quotationListTable && typeof quotationListTable.draw === 'function') {
                        quotationListTable.draw();
                        return;
                    }

                    if (currentMode() === 'quotation-orders' && quotationOrdersTable && typeof quotationOrdersTable.draw === 'function') {
                        quotationOrdersTable.draw();
                        return;
                    }
                }

                renderCurrentOfflinePage();
            });
        });
    }

    function bindConnectivity() {
        if (bindConnectivity.bound) {
            return;
        }

        bindConnectivity.bound = true;

        global.addEventListener('online', function () {
            refreshCurrentPage();
            refreshBanner();
        });

        global.addEventListener('offline', function () {
            refreshCurrentPage();
            refreshBanner();
        });

        global.addEventListener('offline-queue:changed', function () {
            if (!isOnline()) {
                refreshCurrentPage();
            }
        });
    }

    function refreshBanner() {
        var mode = currentMode();

        if (!mode) {
            removeBanner();
            return;
        }

        if (isOnline()) {
            removeBanner();
            return;
        }

        if (mode === 'sales-create' || mode === 'quotation-create') {
            showBanner('Changes will be stored locally and queued for synchronization.');
            return;
        }

        if (mode === 'sales-list') {
            showBanner('Sales data is being read from IndexedDB.');
            return;
        }

        if (mode === 'sales-transactions') {
            showBanner('Sales transactions are being read from IndexedDB.');
            return;
        }

        if (mode === 'quotation-list') {
            showBanner('Quotation data is being read from IndexedDB.');
            return;
        }

        if (mode === 'quotation-orders') {
            showBanner('Quotation orders are being read from IndexedDB.');
        }
    }

    function refreshCurrentPage() {
        var mode = currentMode();

        if (!mode) {
            return Promise.resolve(null);
        }

        refreshBanner();

        if (mode === 'sales-list') {
            if (isOnline()) {
                destroyTable('#salesPaymentTable');
                return initSalesListOnline();
            }
            destroyTable('#salesPaymentTable');
            return renderSalesListOffline();
        }

        if (mode === 'sales-transactions') {
            if (isOnline()) {
                destroyTable('#salesPaymentTable');
                return initSalesTransactionsOnline();
            }
            destroyTable('#salesPaymentTable');
            return renderSalesTransactionsOffline();
        }

        if (mode === 'quotation-list') {
            if (isOnline()) {
                destroyTable('#quotationLieTable');
                return initQuotationListOnline();
            }
            destroyTable('#quotationLieTable');
            return renderQuotationListOffline();
        }

        if (mode === 'quotation-orders') {
            if (isOnline()) {
                destroyTable('#QuotationOrdersTable');
                return initQuotationOrdersOnline();
            }
            destroyTable('#QuotationOrdersTable');
            return renderQuotationOrdersOffline();
        }

        return Promise.resolve(null);
    }

    function bindFormSubmission() {
        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!form) {
                return;
            }

            if (form.id === 'quickCustomerForm') {
                event.preventDefault();
                event.stopImmediatePropagation();

                handleQuickCustomerSubmit(form).catch(function (error) {
                    notify('error', 'Customer save failed', error && error.message ? error.message : 'Could not save the customer.');
                });
                return;
            }

            if (form.matches('form[data-sales-form="create"]')) {
                if (isOnline()) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                handleSalesOfflineSubmit(form).catch(function (error) {
                    notify('error', 'Sale save failed', error && error.message ? error.message : 'Could not save the sale locally.');
                });
                return;
            }

            if (form.matches('form[data-quotation-form="create"]')) {
                if (isOnline()) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                handleQuotationOfflineSubmit(form).catch(function (error) {
                    notify('error', 'Quotation save failed', error && error.message ? error.message : 'Could not save the quotation locally.');
                });
            }
        }, true);
    }

    function bindInputEvents() {
        document.addEventListener('input', handleCustomerInput, true);
        document.addEventListener('input', handleProductInput, true);
    }

    function bindClickEvents() {
        document.addEventListener('click', function (event) {
            if (event.target.closest('.js-sales-customer-row, .js-quotation-customer-row, .js-sales-product-row, .js-quotation-product-row')) {
                handleDropdownClick(event);
                return;
            }

            if (event.target.closest('.delete-set')) {
                handleTableDelete(event);
                return;
            }

            if (event.target.closest('#addBtn') && currentMode() === 'quotation-create') {
                event.preventDefault();
                addQuotationProductRow();
                return;
            }

            if (event.target.closest('#salesForm') && currentMode() === 'sales-create' && event.target.id === 'paid_amount') {
                updateSalesTotals();
            }
        }, true);
    }

    function bindSalesCreateExtras() {
        var paidAmount = document.getElementById('paid_amount');

        if (paidAmount && paidAmount.dataset.salesQuotationBound !== 'true') {
            paidAmount.dataset.salesQuotationBound = 'true';
            paidAmount.addEventListener('blur', function () {
                updateSalesTotals();

                if (pageState.selectedSalesCustomer && isCashCustomer(pageState.selectedSalesCustomer)) {
                    var netPrice = normalizeNumber(getValue('net_price'));
                    var paidValue = normalizeNumber(getValue('paid_amount'));

                    if (paidValue < netPrice) {
                        setValue('paid_amount', '');
                        setValue('balance', '');
                        notify('error', 'Sorry', 'Cash sales must be paid in full.');
                    }
                }
            });
        }

        var discount = document.getElementById('discount');
        if (discount && discount.dataset.salesQuotationBound !== 'true') {
            discount.dataset.salesQuotationBound = 'true';
            discount.addEventListener('input', updateSalesTotals);
        }
    }

    function bindQuotationCreateExtras() {
        var discount = document.getElementById('discount');
        if (discount && discount.dataset.salesQuotationBound !== 'true') {
            discount.dataset.salesQuotationBound = 'true';
            discount.addEventListener('input', updateQuotationTotal);
        }

        var quantity = document.getElementById('quantity');
        if (quantity && quantity.dataset.salesQuotationBound !== 'true') {
            quantity.dataset.salesQuotationBound = 'true';
            quantity.addEventListener('input', function () {
                updateQuotationPreview();
                updateQuotationTotal();
            });
        }

        var sellingPrice = document.getElementById('selling_price');
        if (sellingPrice && sellingPrice.dataset.salesQuotationBound !== 'true') {
            sellingPrice.dataset.salesQuotationBound = 'true';
            sellingPrice.addEventListener('input', function () {
                updateQuotationPreview();
                updateQuotationTotal();
            });
        }
    }

    function bindClickAway() {
        document.addEventListener('click', function (event) {
            var customerDropdown = document.getElementById('customerDropdown');
            var productDropdown = document.getElementById('productDropdown');
            var customerSearch = document.getElementById('customerSearch');
            var productSearch = document.getElementById('productSearch');
            var inventorySearch = document.getElementById('inventorySearch');

            if (customerDropdown && !customerDropdown.contains(event.target) && customerSearch && event.target !== customerSearch) {
                hideDropdown('customerDropdown');
            }

            if (productDropdown && !productDropdown.contains(event.target) &&
                event.target !== productSearch && event.target !== inventorySearch) {
                hideDropdown('productDropdown');
            }
        }, true);
    }

    function boot() {
        if (booted) {
            return;
        }

        booted = true;
        pageState.mode = currentMode();

        bindConnectivity();
        bindFormSubmission();
        bindInputEvents();
        bindClickEvents();
        bindClickAway();
        bindListControls();
        bindCreatePages();
        bindSalesCreateExtras();
        bindQuotationCreateExtras();

        refreshCurrentPage();
    }

    function confirmSalesTransactionDelete(id) {
        var form = document.getElementById('deleteForm-' + id);

        if (!form) {
            return;
        }

        if (global.Swal && typeof global.Swal.fire === 'function') {
            global.Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return;
        }

        form.submit();
    }

    function confirmQuotationDelete(id) {
        var form = document.getElementById('deleteForm-' + id);

        if (!form) {
            return;
        }

        if (global.Swal && typeof global.Swal.fire === 'function') {
            global.Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return;
        }

        form.submit();
    }

    namespace.boot = boot;
    namespace.refreshCurrentPage = refreshCurrentPage;
    namespace.renderCurrentOfflinePage = renderCurrentOfflinePage;
    namespace.recordSalesReturnOffline = recordSalesReturnOffline;
    namespace.handleSalesOfflineSubmit = handleSalesOfflineSubmit;
    namespace.handleQuotationOfflineSubmit = handleQuotationOfflineSubmit;

    global.StoreManagementSalesQuotationModuleClass = {
        boot: boot
    };
    global.confirmSalesTransactionDelete = confirmSalesTransactionDelete;
    global.confirmQuotationDelete = confirmQuotationDelete;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
