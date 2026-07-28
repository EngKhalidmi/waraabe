(function (global) {
    'use strict';

    var namespace = global.StoreManagementFuelSalesModule = global.StoreManagementFuelSalesModule || {};
    var repositoryCache = {};
    var salesTableInstance = null;
    var creditTableInstance = null;
    var offlineSalesRows = [];
    var offlineCreditRows = [];
    var booted = false;

    var STORE_CLASS_MAP = {
        customers: 'CustomerRepository',
        products: 'ProductRepository',
        fuel_sales: 'FuelSaleRepository',
        fuel_sale_transactions: 'FuelSaleTransactionRepository',
        fuel_cash_sale: 'FuelCashSaleRepository',
        fuel_credit_sales: 'FuelCreditSaleRepository',
        fuel_sale_payment: 'FuelSalePaymentRepository'
    };

    function isOnline() {
        return navigator.onLine !== false;
    }

    function isCreatePage() {
        return Boolean(document.getElementById('fuelSalesForm'));
    }

    function isSalesIndexPage() {
        return Boolean(document.getElementById('fuelSalesTable'));
    }

    function isCreditIndexPage() {
        return Boolean(document.getElementById('fuelCreditSalesTable'));
    }

    function getConfig() {
        return global.__FUEL_SALES_CONFIG__ || {};
    }

    function getSeed() {
        return global.StoreManagementFuelSalesSeed || {};
    }

    function getState() {
        return global.StoreManagementFuelSalesState || null;
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

    function formatNumber(value, suffix) {
        return toNumber(value).toFixed(2) + (suffix || '');
    }

    function formatDateValue(value) {
        if (!value) {
            return '';
        }

        var stringValue = String(value);
        if (stringValue.indexOf('T') !== -1) {
            return stringValue.slice(0, 10);
        }

        return stringValue;
    }

    function displayDate(value) {
        var text = formatDateValue(value);
        var parts = text.split('-');

        if (parts.length === 3 && parts[0].length === 4) {
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }

        return text;
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

    function getRepository(storeName) {
        var className = STORE_CLASS_MAP[storeName];
        var RepositoryClass = className ? global[className] : null;
        var key = storeName;

        if (repositoryCache[key]) {
            return repositoryCache[key];
        }

        if (typeof RepositoryClass === 'function') {
            repositoryCache[key] = new RepositoryClass();
            return repositoryCache[key];
        }

        if (global.StoreManagementOfflineRepositories && typeof global.StoreManagementOfflineRepositories.createRepository === 'function') {
            try {
                repositoryCache[key] = global.StoreManagementOfflineRepositories.createRepository(storeName);
                return repositoryCache[key];
            } catch (error) {
                return null;
            }
        }

        return null;
    }

    function normalizeImportedRecord(record) {
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
    }

    function cacheRows(repo, rows) {
        var records = Array.isArray(rows) ? rows : [];

        if (!repo || !records.length) {
            return Promise.resolve([]);
        }

        if (typeof repo.cacheFromServerRows === 'function') {
            return repo.cacheFromServerRows(records);
        }

        if (typeof repo.cacheMany !== 'function') {
            return Promise.resolve([]);
        }

        return repo.cacheMany(records.map(normalizeImportedRecord));
    }

    function seedLookupData() {
        if (!isOnline()) {
            return Promise.resolve([]);
        }

        var seed = getSeed();
        var customerRepo = getRepository('customers');
        var productRepo = getRepository('products');

        return Promise.all([
            cacheRows(customerRepo, seed.customers || []),
            cacheRows(productRepo, seed.products || [])
        ]);
    }

    function readFuelSalesState() {
        var state = getState();

        if (!state || typeof state.getFormValues !== 'function' || typeof state.prepareFinalTransactions !== 'function') {
            return null;
        }

        return {
            form: state.getFormValues(),
            paymentData: typeof state.getPaymentData === 'function' ? state.getPaymentData() : {},
            transactions: typeof state.getTransactions === 'function' ? state.getTransactions() : [],
            creditTransactions: typeof state.getCreditTransactions === 'function' ? state.getCreditTransactions() : [],
            prepared: state.prepareFinalTransactions()
        };
    }

    function buildSaleSummary(payload) {
        var summaryMap = {};
        var cashTransactions = Array.isArray(payload.prepared && payload.prepared.transactions) ? payload.prepared.transactions : [];
        var creditTransactions = Array.isArray(payload.creditTransactions) ? payload.creditTransactions : [];

        function ensureSummary(source) {
            var productId = String(source.productId || source.product_id || '');
            var productName = source.productName || source.product_name || 'Unknown Product';

            if (!productId) {
                return null;
            }

            if (!summaryMap[productId]) {
                summaryMap[productId] = {
                    product_id: source.productId || source.product_id,
                    product_name: productName,
                    cash_liters: 0,
                    credit_liters: 0,
                    total_liters: 0,
                    cash_amount: 0,
                    credit_amount: 0,
                    total_amount: 0,
                    transactions: []
                };
            }

            return summaryMap[productId];
        }

        cashTransactions.forEach(function (transaction) {
            var summary = ensureSummary(transaction);

            if (!summary) {
                return;
            }

            var liters = toNumber(transaction.liters);
            var total = toNumber(transaction.total);

            summary.cash_liters += liters;
            summary.total_liters += liters;
            summary.cash_amount += total;
            summary.total_amount += total;
            summary.transactions.push(Object.assign({ type: 'cash' }, transaction));
        });

        creditTransactions.forEach(function (transaction) {
            var summary = ensureSummary(transaction);

            if (!summary) {
                return;
            }

            var liters = toNumber(transaction.quantity);
            var total = toNumber(transaction.total);

            summary.credit_liters += liters;
            summary.total_liters += liters;
            summary.credit_amount += total;
            summary.total_amount += total;
            summary.transactions.push(Object.assign({ type: 'credit' }, transaction));
        });

        return Object.keys(summaryMap).map(function (key) {
            return summaryMap[key];
        });
    }

    function getFuelNameById(productId) {
        var productRepo = getRepository('products');

        if (!productRepo || !productId) {
            return Promise.resolve('');
        }

        return productRepo.findById(productId).then(function (record) {
            return record ? (record.name || record.product_name || '') : '';
        }).catch(function () {
            return '';
        });
    }

    function buildMainFuelSaleRecord(payload, summary) {
        var form = payload.form || {};
        var salesmanName = form.salesman_name || '';
        var productNames = summary.map(function (item) {
            return item.product_name;
        }).filter(Boolean).join(', ');

        return {
            date: form.date || formatDateValue(global.Date.now ? new Date().toISOString() : ''),
            shift: form.shift || '',
            salesman_id: form.salesman_id || null,
            salesman: salesmanName,
            depID: global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.departmentId !== 'undefined'
                ? global.__OFFLINE_ENGINE_CONFIG__.departmentId
                : null,
            total_diesel_liters: payload.prepared ? payload.prepared.totalDieselLiters : 0,
            total_petrol_liters: payload.prepared ? payload.prepared.totalPetrolLiters : 0,
            discount: form.discount || 0,
            net_total: form.net_total || 0,
            cash_on_hand: form.cash_on_hand || 0,
            balance: form.balance || 0,
            created_by: form.created_by || null,
            product_name: productNames,
            customer_name: payload.creditTransactions && payload.creditTransactions.length ? payload.creditTransactions[0].customerName || '' : '',
            product_transactions: summary,
            transaction_count: summary.reduce(function (count, item) {
                return count + (Array.isArray(item.transactions) ? item.transactions.length : 0);
            }, 0),
            source: 'offline'
        };
    }

    function buildSaleTransactionRows(payload, saleLocalId) {
        var form = payload.form || {};
        var rows = [];

        (payload.prepared && Array.isArray(payload.prepared.transactions) ? payload.prepared.transactions : []).forEach(function (transaction) {
            rows.push({
                fuel_sale_id: saleLocalId,
                sale_local_id: saleLocalId,
                depID: global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.departmentId !== 'undefined'
                    ? global.__OFFLINE_ENGINE_CONFIG__.departmentId
                    : null,
                dphase: transaction.dphase || '',
                product_id: transaction.productId || transaction.product_id || null,
                product_name: transaction.productName || transaction.product_name || '',
                previous_reading: transaction.preading || transaction.previous_reading || 0,
                current_reading: transaction.creading || transaction.current_reading || 0,
                liters: transaction.liters || 0,
                rate: transaction.rate || 0,
                total: transaction.total || 0,
                date: form.date || '',
                synced: false,
                sync_status: 'pending',
                local_action: 'create'
            });
        });

        return rows;
    }

    function buildFuelCashRows(payload, saleLocalId) {
        var form = payload.form || {};
        var rows = [];

        (payload.prepared && Array.isArray(payload.prepared.transactions) ? payload.prepared.transactions : []).forEach(function (transaction) {
            rows.push({
                fuel_sale_id: saleLocalId,
                sale_local_id: saleLocalId,
                customer_id: null,
                customer_local_id: null,
                product_id: transaction.productId || transaction.product_id || null,
                product_name: transaction.productName || transaction.product_name || '',
                quantity: transaction.liters || 0,
                rate: transaction.rate || 0,
                total: transaction.total || 0,
                description: 'Cash sale',
                status: 'paid',
                date: form.date || '',
                synced: false,
                sync_status: 'pending',
                local_action: 'create'
            });
        });

        return rows;
    }

    function buildFuelCreditRows(payload, saleLocalId) {
        var form = payload.form || {};
        var rows = [];

        (payload.creditTransactions || []).forEach(function (transaction) {
            rows.push({
                fuel_sale_id: saleLocalId,
                sale_local_id: saleLocalId,
                customer_id: transaction.customerId || null,
                customer_local_id: transaction.customerLocalId || transaction.customerId || null,
                customer_name: transaction.customerName || '',
                product_id: transaction.productId || transaction.product_id || null,
                product_local_id: transaction.productLocalId || transaction.productId || null,
                product_name: transaction.productName || transaction.product_name || '',
                quantity: transaction.quantity || 0,
                rate: transaction.rate || 0,
                total: transaction.total || 0,
                description: transaction.description || '',
                status: 'pending',
                date: form.date || '',
                synced: false,
                sync_status: 'pending',
                local_action: 'create'
            });
        });

        return rows;
    }

    function buildFuelPaymentRow(payload, saleLocalId) {
        var form = payload.form || {};
        var paymentData = payload.paymentData || {};

        return {
            fuel_sale_id: saleLocalId,
            sale_local_id: saleLocalId,
            depID: global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.departmentId !== 'undefined'
                ? global.__OFFLINE_ENGINE_CONFIG__.departmentId
                : null,
            zaad_dollar: paymentData.zaad_dollar || 0,
            zaad_slsh: paymentData.zaad_slsh || 0,
            edahab_dollar: paymentData.edahab_dollar || 0,
            edahab_slsh: paymentData.edahab_slsh || 0,
            cash_dollar: paymentData.cash_dollar || 0,
            cash_slsh: paymentData.cash_slsh || 0,
            merchant_dollar: paymentData.merchant_dollar || 0,
            merchant_slsh: paymentData.merchant_slsh || 0,
            payment_rate: paymentData.payment_rate || 1,
            date: form.date || '',
            synced: false,
            sync_status: 'pending',
            local_action: 'create'
        };
    }

    function buildStockMovements(payload) {
        var movements = {};

        function addMovement(productId, quantity, productName) {
            var key = String(productId || '');

            if (!key) {
                return;
            }

            if (!movements[key]) {
                movements[key] = {
                    product_id: productId,
                    product_name: productName || '',
                    quantity: 0
                };
            }

            movements[key].quantity += toNumber(quantity);
        }

        (payload.prepared && Array.isArray(payload.prepared.transactions) ? payload.prepared.transactions : []).forEach(function (transaction) {
            addMovement(transaction.productId || transaction.product_id, transaction.liters, transaction.productName || transaction.product_name);
        });

        (payload.creditTransactions || []).forEach(function (transaction) {
            addMovement(transaction.productId || transaction.product_id, transaction.quantity, transaction.productName || transaction.product_name);
        });

        return Object.keys(movements).map(function (key) {
            return movements[key];
        });
    }

    function findInSeedCustomers(customerId) {
        var seed = getSeed();
        var customers = Array.isArray(seed.customers) ? seed.customers : [];

        return customers.find(function (customer) {
            return String(customer.id) === String(customerId) || String(customer.local_id) === String(customerId);
        }) || null;
    }

    function findInSeedProducts(productId) {
        var seed = getSeed();
        var products = Array.isArray(seed.products) ? seed.products : [];

        return products.find(function (product) {
            return String(product.id) === String(productId) || String(product.local_id) === String(productId);
        }) || null;
    }

    function searchCustomersOffline(query) {
        var repo = getRepository('customers');
        var needle = String(query || '').trim().toLowerCase();
        var seedCustomers = Array.isArray(getSeed().customers) ? getSeed().customers : [];

        function filterRecords(records) {
            return records.filter(function (record) {
                var name = String(record.customer_name || record.name || '').toLowerCase();
                var phone = String(record.phone || '').toLowerCase();
                var serial = String(record.serial || '').toLowerCase();

                return !needle || name.indexOf(needle) !== -1 || phone.indexOf(needle) !== -1 || serial.indexOf(needle) !== -1;
            });
        }

        if (repo && typeof repo.search === 'function') {
            return repo.search(needle).then(function (records) {
                return filterRecords(records || []);
            }).catch(function () {
                return filterRecords(seedCustomers);
            });
        }

        return Promise.resolve(filterRecords(seedCustomers));
    }

    function renderCustomerDropdown(records, query) {
        var dropdown = document.getElementById('customerDropdown');

        if (!dropdown) {
            return;
        }

        if (!records.length) {
            dropdown.innerHTML = '<div class="dropdown-item text-center text-muted">No results found</div>';
            dropdown.style.display = 'block';
            return;
        }

        dropdown.innerHTML = records.map(function (record) {
            var customerId = record.id || record.local_id || '';
            var customerName = record.customer_name || record.name || '';
            var phone = record.phone || '';

            return [
                '<a class="dropdown-item customer-row" ',
                'data-offline-customer-id="' + escapeHtml(customerId) + '" ',
                'data-offline-customer-name="' + escapeHtml(customerName) + '" ',
                'data-offline-customer-phone="' + escapeHtml(phone) + '" ',
                'data-offline-customer-local-id="' + escapeHtml(record.local_id || customerId) + '">',
                escapeHtml(customerName),
                phone ? ' <small class="text-muted">(' + escapeHtml(phone) + ')</small>' : '',
                '</a>'
            ].join('');
        }).join('');

        dropdown.style.display = 'block';
    }

    function selectCustomerOffline(record) {
        var state = getState();

        if (state && typeof state.setCustomerSelection === 'function') {
            state.setCustomerSelection(record);
        } else {
            var search = document.getElementById('customerSearch');
            var customerId = document.getElementById('customerID');
            var customerName = document.getElementById('customerName');
            var creditForm = document.getElementById('creditForm');

            if (search) {
                search.value = record.customer_name || record.name || '';
            }

            if (customerId) {
                customerId.value = record.id || record.local_id || '';
            }

            if (customerName) {
                customerName.value = record.customer_name || record.name || '';
            }

            if (creditForm) {
                creditForm.style.display = 'block';
            }
        }

        var dropdown = document.getElementById('customerDropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }

    function bindOfflineCustomerSearch() {
        document.addEventListener('input', function (event) {
            var input = event.target;

            if (!isCreatePage() || !input || input.id !== 'customerSearch' || isOnline()) {
                return;
            }

            event.stopImmediatePropagation();
            event.preventDefault();

            var query = String(input.value || '').trim();
            var dropdown = document.getElementById('customerDropdown');

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

            searchCustomersOffline(query).then(function (records) {
                renderCustomerDropdown(records, query);
            }).catch(function () {
                dropdown.innerHTML = '<div class="dropdown-item text-center text-danger">Search failed</div>';
                dropdown.style.display = 'block';
            });
        }, true);

        document.addEventListener('click', function (event) {
            if (!isCreatePage() || isOnline()) {
                return;
            }

            var item = event.target.closest('#customerDropdown .customer-row');
            if (!item) {
                return;
            }

            event.stopImmediatePropagation();
            event.preventDefault();

            selectCustomerOffline({
                id: item.getAttribute('data-offline-customer-id'),
                local_id: item.getAttribute('data-offline-customer-local-id'),
                customer_name: item.getAttribute('data-offline-customer-name'),
                name: item.getAttribute('data-offline-customer-name'),
                phone: item.getAttribute('data-offline-customer-phone')
            });
        }, true);
    }

    function findOfflineProductRecord(productId) {
        var repo = getRepository('products');

        if (!repo || !productId) {
            return Promise.resolve(null);
        }

        return repo.findById(productId).catch(function () {
            return null;
        });
    }

    function validateStockMovements(payload) {
        var movements = buildStockMovements(payload);
        var productRepo = getRepository('products');

        if (!movements.length) {
            return Promise.resolve([]);
        }

        if (!productRepo) {
            return Promise.reject(new Error('Product repository is not available.'));
        }

        return movements.reduce(function (chain, movement) {
            return chain.then(function (results) {
                return productRepo.findById(movement.product_id).then(function (record) {
                    if (!record) {
                        throw new Error('Product "' + (movement.product_name || movement.product_id) + '" is not cached locally.');
                    }

                    var currentQuantity = toNumber(record.quantity);
                    var nextQuantity = currentQuantity - toNumber(movement.quantity);

                    if (nextQuantity < 0) {
                        throw new Error('Insufficient local stock for ' + (movement.product_name || movement.product_id) + '.');
                    }

                    results.push({
                        record: record,
                        movement: movement,
                        nextQuantity: nextQuantity
                    });

                    return results;
                });
            });
        }, Promise.resolve([]));
    }

    function persistStockMovements(stockChecks) {
        var productRepo = getRepository('products');

        if (!productRepo || !stockChecks.length) {
            return Promise.resolve([]);
        }

        return stockChecks.reduce(function (chain, item) {
            return chain.then(function (saved) {
                return productRepo.update(item.record.id || item.record.local_id || item.record.server_id, {
                    quantity: item.nextQuantity
                }).then(function (result) {
                    if (result) {
                        saved.push(result);
                    }

                    return saved;
                });
            });
        }, Promise.resolve([]));
    }

    function buildOfflineSalePromise(payload) {
        var fuelSaleRepo = getRepository('fuel_sales');
        var fuelTransactionRepo = getRepository('fuel_sale_transactions');
        var fuelCashRepo = getRepository('fuel_cash_sale');
        var fuelCreditRepo = getRepository('fuel_credit_sales');
        var fuelPaymentRepo = getRepository('fuel_sale_payment');

        if (!fuelSaleRepo || !fuelTransactionRepo || !fuelCashRepo || !fuelCreditRepo || !fuelPaymentRepo) {
            return Promise.reject(new Error('Fuel sale repositories are not ready yet.'));
        }

        var summary = buildSaleSummary(payload);
        var mainRecord = buildMainFuelSaleRecord(payload, summary);

        return validateStockMovements(payload).then(function (stockChecks) {
            return fuelSaleRepo.create(mainRecord).then(function (savedSale) {
                var localId = savedSale.local_id || savedSale.id;
                var transactionRows = buildSaleTransactionRows(payload, localId);
                var cashRows = buildFuelCashRows(payload, localId);
                var creditRows = buildFuelCreditRows(payload, localId);
                var paymentRow = buildFuelPaymentRow(payload, localId);

                return Promise.all([
                    Promise.all(transactionRows.map(function (row) {
                        return fuelTransactionRepo.create(row);
                    })),
                    Promise.all(cashRows.map(function (row) {
                        return fuelCashRepo.create(row);
                    })),
                    Promise.all(creditRows.map(function (row) {
                        return fuelCreditRepo.create(row);
                    })),
                    fuelPaymentRepo.create(paymentRow),
                    persistStockMovements(stockChecks)
                ]).then(function () {
                    return savedSale;
                });
            });
        });
    }

    function submitOfflineFuelSale() {
        var payload = readFuelSalesState();

        if (!payload) {
            return Promise.reject(new Error('Fuel sales state is not ready yet.'));
        }

        if ((!payload.transactions || !payload.transactions.length) && (!payload.creditTransactions || !payload.creditTransactions.length)) {
            return Promise.reject(new Error('Add at least one fuel transaction before saving offline.'));
        }

        return buildOfflineSalePromise(payload);
    }

    function bindOfflineFuelSaleSubmit() {
        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!isCreatePage() || !form || form.id !== 'fuelSalesForm' || isOnline()) {
                return;
            }

            event.stopImmediatePropagation();
            event.preventDefault();

            var submitButton = form.querySelector('button[type="submit"]');
            var originalHtml = submitButton ? submitButton.innerHTML : '';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
            }

            submitOfflineFuelSale().then(function () {
                showToast('success', 'Fuel sale saved offline and queued for synchronization.');

                if (getState() && typeof getState().resetForm === 'function') {
                    getState().resetForm();
                }

                var customerSearch = document.getElementById('customerSearch');
                var customerId = document.getElementById('customerID');
                var customerName = document.getElementById('customerName');
                var creditForm = document.getElementById('creditForm');
                var customerDropdown = document.getElementById('customerDropdown');

                if (customerSearch) {
                    customerSearch.value = '';
                }

                if (customerId) {
                    customerId.value = '';
                }

                if (customerName) {
                    customerName.value = '';
                }

                if (creditForm) {
                    creditForm.style.display = 'none';
                }

                if (customerDropdown) {
                    customerDropdown.style.display = 'none';
                    customerDropdown.innerHTML = '';
                }
            }).catch(function (error) {
                notify('error', 'Offline save failed', error && error.message ? error.message : 'Could not store the fuel sale locally.');
            }).finally(function () {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalHtml;
                }
            });
        }, true);
    }

    function sumByKeyword(records, keyword) {
        var lowered = String(keyword || '').toLowerCase();

        return (records || []).find(function (entry) {
            var name = String(entry.product_name || '').toLowerCase();
            return name.indexOf(lowered) !== -1;
        }) || null;
    }

    function buildOfflineSalesRow(record, index) {
        var productTransactions = Array.isArray(record.product_transactions) ? record.product_transactions : [];
        var petrol = sumByKeyword(productTransactions, 'petrol');
        var diesel = sumByKeyword(productTransactions, 'diesel');
        var saleId = record.id || record.local_id || index + 1;

        return [
            '<tr data-sale-id="' + escapeHtml(saleId) + '">',
            '  <td>#' + escapeHtml(record.id || saleId) + '</td>',
            '  <td>' + escapeHtml(displayDate(record.date)) + '</td>',
            '  <td>' + escapeHtml(record.salesman || '') + '</td>',
            '  <td>' + escapeHtml(record.shift ? record.shift.charAt(0).toUpperCase() + record.shift.slice(1) : 'N/A') + '</td>',
            '  <td>' + escapeHtml(formatNumber(petrol ? petrol.total_liters : 0, ' L')) + '</td>',
            '  <td>' + escapeHtml(formatNumber(petrol ? petrol.cash_liters : 0, ' L')) + '</td>',
            '  <td>' + escapeHtml(formatNumber(petrol ? petrol.credit_liters : 0, ' L')) + '</td>',
            '  <td>' + escapeHtml(formatNumber(diesel ? diesel.total_liters : 0, ' L')) + '</td>',
            '  <td>' + escapeHtml(formatNumber(diesel ? diesel.cash_liters : 0, ' L')) + '</td>',
            '  <td>' + escapeHtml(formatNumber(diesel ? diesel.credit_liters : 0, ' L')) + '</td>',
            '  <td>' + escapeHtml(formatNumber(record.balance, '')) + '</td>',
            '  <td>',
            '    <ul class="action-list list-unstyled d-flex gap-2 mb-0">',
            '      <li><a href="javascript:void(0);" class="text-primary view-details" data-id="' + escapeHtml(saleId) + '" title="View Details"><i class="fas fa-eye"></i></a></li>',
            '    </ul>',
            '  </td>',
            '</tr>'
        ].join('');
    }

    function buildOfflineCreditRow(record, index) {
        var saleId = record.id || record.local_id || index + 1;

        return [
            '<tr data-credit-sale-id="' + escapeHtml(saleId) + '">',
            '  <td>' + escapeHtml(index + 1) + '</td>',
            '  <td>' + escapeHtml(record.customer_name || 'N/A') + '</td>',
            '  <td>' + escapeHtml(record.customer_phone || 'N/A') + '</td>',
            '  <td>' + escapeHtml(record.fuel_type || record.product_name || 'N/A') + '</td>',
            '  <td>' + escapeHtml(formatNumber(record.quantity, ' L')) + '</td>',
            '  <td>' + escapeHtml('$' + formatNumber(record.rate, '')) + '</td>',
            '  <td>' + escapeHtml('$' + formatNumber(record.total, '')) + '</td>',
            '  <td><span class="badge bg-warning text-dark">' + escapeHtml(record.status || 'pending') + '</span></td>',
            '  <td>' + escapeHtml(displayDate(record.date)) + '</td>',
            '  <td>' + escapeHtml(displayDateTime(record.created_at || record.updated_at || record.date)) + '</td>',
            '</tr>'
        ].join('');
    }

    function renderOfflineSalesTable(records) {
        var table = document.getElementById('fuelSalesTable');
        var tbody = table ? table.querySelector('tbody') : null;

        if (!tbody) {
            return;
        }

        offlineSalesRows = records.slice();

        if (global.jQuery && global.jQuery.fn && global.jQuery.fn.DataTable && global.jQuery.fn.DataTable.isDataTable('#fuelSalesTable')) {
            global.jQuery('#fuelSalesTable').DataTable().destroy();
        }

        if (!records.length) {
            tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">No fuel sales data available offline.</td></tr>';
            return;
        }

        tbody.innerHTML = records.map(function (record, index) {
            return buildOfflineSalesRow(record, index);
        }).join('');
    }

    function renderOfflineCreditTable(records) {
        var table = document.getElementById('fuelCreditSalesTable');
        var tbody = table ? table.querySelector('tbody') : null;

        if (!tbody) {
            return;
        }

        offlineCreditRows = records.slice();

        if (global.jQuery && global.jQuery.fn && global.jQuery.fn.DataTable && global.jQuery.fn.DataTable.isDataTable('#fuelCreditSalesTable')) {
            global.jQuery('#fuelCreditSalesTable').DataTable().destroy();
        }

        if (!records.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No fuel credit sales data available offline.</td></tr>';
            return;
        }

        tbody.innerHTML = records.map(function (record, index) {
            return buildOfflineCreditRow(record, index);
        }).join('');
    }

    function filterSalesOffline(records) {
        var productName = String(document.getElementById('product_name') ? document.getElementById('product_name').value : '').trim().toLowerCase();
        var transactionId = String(document.getElementById('transaction_id') ? document.getElementById('transaction_id').value : '').trim().toLowerCase();
        var salesman = String(document.getElementById('salesman') ? document.getElementById('salesman').value : '').trim().toLowerCase();
        var shift = String(document.getElementById('shift') ? document.getElementById('shift').value : '').trim().toLowerCase();
        var startDate = String(document.getElementById('startDate') ? document.getElementById('startDate').value : '').trim();
        var endDate = String(document.getElementById('endDate') ? document.getElementById('endDate').value : '').trim();

        return records.filter(function (record) {
            var productTransactions = Array.isArray(record.product_transactions) ? record.product_transactions : [];
            var haystack = productTransactions.map(function (entry) {
                return String(entry.product_name || '').toLowerCase();
            }).join(' ');
            var recordDate = formatDateValue(record.date);

            var matchProduct = !productName || haystack.indexOf(productName) !== -1;
            var matchTransaction = !transactionId || String(record.id || record.local_id || '').toLowerCase().indexOf(transactionId) !== -1;
            var matchSalesman = !salesman || String(record.salesman || '').toLowerCase().indexOf(salesman) !== -1;
            var matchShift = !shift || String(record.shift || '').toLowerCase() === shift;
            var matchStart = !startDate || !recordDate || recordDate >= startDate;
            var matchEnd = !endDate || !recordDate || recordDate <= endDate;

            return matchProduct && matchTransaction && matchSalesman && matchShift && matchStart && matchEnd;
        }, true);
    }

    function filterCreditOffline(records) {
        var customerName = String(document.getElementById('customer_name') ? document.getElementById('customer_name').value : '').trim().toLowerCase();
        var fuelType = String(document.getElementById('fuel_type') ? document.getElementById('fuel_type').value : '').trim().toLowerCase();
        var status = String(document.getElementById('status') ? document.getElementById('status').value : '').trim().toLowerCase();
        var startDate = String(document.getElementById('startDate') ? document.getElementById('startDate').value : '').trim();
        var endDate = String(document.getElementById('endDate') ? document.getElementById('endDate').value : '').trim();

        return records.filter(function (record) {
            var recordDate = formatDateValue(record.date || record.created_at);
            var matchCustomer = !customerName || String(record.customer_name || '').toLowerCase().indexOf(customerName) !== -1;
            var matchFuel = !fuelType || String(record.fuel_type || record.product_name || '').toLowerCase().indexOf(fuelType) !== -1;
            var matchStatus = !status || String(record.status || '').toLowerCase() === status;
            var matchStart = !startDate || !recordDate || recordDate >= startDate;
            var matchEnd = !endDate || !recordDate || recordDate <= endDate;

            return matchCustomer && matchFuel && matchStatus && matchStart && matchEnd;
        });
    }

    function getProductMetric(record, keyword, field) {
        var transactions = Array.isArray(record.product_transactions) ? record.product_transactions : [];
        var match = sumByKeyword(transactions, keyword);

        if (!match) {
            return 0;
        }

        return toNumber(match[field]);
    }

    function buildFuelSaleDetails(record) {
        var transactions = Array.isArray(record.product_transactions) ? record.product_transactions : [];
        var html = '';

        transactions.forEach(function (product) {
            html += [
                '<div class="card mb-3">',
                '  <div class="card-header bg-light">',
                '    <h6 class="mb-0">' + escapeHtml(product.product_name || 'Unknown Product') + '</h6>',
                '    <small>Total: ' + escapeHtml(formatNumber(product.total_liters || 0, 'L')) + ' (Cash: ' + escapeHtml(formatNumber(product.cash_liters || 0, 'L')) + ', Credit: ' + escapeHtml(formatNumber(product.credit_liters || 0, 'L')) + ') - ' + escapeHtml(formatNumber(product.total_amount || 0, '')) + '</small>',
                '  </div>',
                '  <div class="card-body p-2">'
            ].join('');

            if (Array.isArray(product.transactions) && product.transactions.length) {
                product.transactions.forEach(function (transaction) {
                    html += [
                        '<div class="border-bottom py-2">',
                        '  <div><strong>Type:</strong> ' + escapeHtml(transaction.type || 'cash') + '</div>',
                        '  <div><strong>Quantity:</strong> ' + escapeHtml(formatNumber(transaction.quantity || transaction.liters || 0, '')) + '</div>',
                        '  <div><strong>Rate:</strong> ' + escapeHtml(formatNumber(transaction.rate || 0, '')) + '</div>',
                        '  <div><strong>Total:</strong> ' + escapeHtml(formatNumber(transaction.total || 0, '')) + '</div>',
                        transaction.customerName ? '<div><strong>Customer:</strong> ' + escapeHtml(transaction.customerName) + '</div>' : '',
                        '</div>'
                    ].join('');
                });
            } else {
                html += '<div class="text-muted">No transaction breakdown available.</div>';
            }

            html += '</div></div>';
        });

        return html;
    }

    function openDetailsModal(record) {
        var row = record || {};

        document.getElementById('modalTransactionId').textContent = row.id || row.local_id || '';
        document.getElementById('modalDate').textContent = row.date || '';
        document.getElementById('modalSalesman').textContent = row.salesman || 'N/A';
        document.getElementById('modalShift').textContent = row.shift ? row.shift.charAt(0).toUpperCase() + row.shift.slice(1) : 'N/A';
        document.getElementById('modalCreatedAt').textContent = row.created_at || '';
        document.getElementById('modalDiscount').textContent = formatNumber(row.discount || 0, '');
        document.getElementById('modalNetTotal').textContent = formatNumber(row.net_total || 0, '');
        document.getElementById('modalCashOnHand').textContent = formatNumber(row.cash_on_hand || 0, '');
        document.getElementById('modalBalance').textContent = formatNumber(row.balance || 0, '');
        document.getElementById('modalTransactions').innerHTML = buildFuelSaleDetails(row);

        showModal('#viewDetailsModal');
    }

    function showModal(selector) {
        if (global.jQuery && global.jQuery.fn && typeof global.jQuery(selector).modal === 'function') {
            global.jQuery(selector).modal('show');
            return;
        }

        var element = document.querySelector(selector);
        if (element) {
            element.style.display = 'block';
            element.classList.add('show');
        }
    }

    function hideModal(selector) {
        if (global.jQuery && global.jQuery.fn && typeof global.jQuery(selector).modal === 'function') {
            global.jQuery(selector).modal('hide');
            return;
        }

        var element = document.querySelector(selector);
        if (element) {
            element.style.display = 'none';
            element.classList.remove('show');
        }
    }

    function getRowDataFromOfflineSales(saleId) {
        return offlineSalesRows.find(function (record) {
            return String(record.id || record.local_id) === String(saleId);
        }) || null;
    }

    function getRowDataFromOfflineCredits(creditId) {
        return offlineCreditRows.find(function (record) {
            return String(record.id || record.local_id) === String(creditId);
        }) || null;
    }

    function handleSalesListView(event) {
        var target = event.target.closest('.view-details');
        if (!target) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        var saleId = target.getAttribute('data-id');

        if (isOnline() && salesTableInstance && typeof salesTableInstance.row === 'function') {
            var row = target.closest('tr');
            var rowData = salesTableInstance.row(row).data();

            if (rowData) {
                openDetailsModal(rowData);
                return;
            }
        }

        var offlineRecord = getRowDataFromOfflineSales(saleId);
        if (offlineRecord) {
            openDetailsModal(offlineRecord);
            return;
        }

        notify('error', 'Record not found', 'The selected fuel sale could not be loaded.');
    }

    function handleSalesListDelete(event) {
        var target = event.target.closest('.delete-sale');
        if (!target) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        if (!isOnline()) {
            notify('error', 'Offline unavailable', 'Fuel sale deletion is only available while online.');
            return;
        }

        var saleId = target.getAttribute('data-id');
        var deleteSaleId = document.getElementById('deleteSaleId');

        if (deleteSaleId) {
            deleteSaleId.value = saleId;
        }

        showModal('#deleteConfirmationModal');
    }

    function confirmSalesDelete() {
        var saleIdInput = document.getElementById('deleteSaleId');
        var saleId = saleIdInput ? saleIdInput.value : '';
        var config = getConfig();

        if (!saleId) {
            return;
        }

        if (!config.destroyBaseUrl) {
            notify('error', 'Missing route', 'The delete route is not configured.');
            return;
        }

        global.jQuery.ajax({
            url: config.destroyBaseUrl + '/' + saleId,
            type: 'DELETE',
            data: {
                _token: document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
            },
            success: function (response) {
                if (response && response.success) {
                    showToast('success', response.message || 'Fuel sale deleted successfully.');
                    if (salesTableInstance && typeof salesTableInstance.draw === 'function') {
                        salesTableInstance.draw();
                    }
                } else {
                    showToast('error', (response && response.message) || 'Error deleting sale');
                }

                hideModal('#deleteConfirmationModal');
            },
            error: function (xhr) {
                var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error deleting sale';
                showToast('error', message);
                hideModal('#deleteConfirmationModal');
            }
        });
    }

    function initSalesOnlineTable() {
        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return Promise.resolve(null);
        }

        var config = getConfig();
        var tableSelector = '#fuelSalesTable';

        if (global.jQuery.fn.DataTable.isDataTable(tableSelector)) {
            salesTableInstance = global.jQuery(tableSelector).DataTable();
            return Promise.resolve(salesTableInstance);
        }

        salesTableInstance = global.jQuery(tableSelector).DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: config.indexRoute || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.product_name = global.jQuery('#product_name').val();
                    d.transaction_id = global.jQuery('#transaction_id').val();
                    d.salesman = global.jQuery('#salesman').val();
                    d.shift = global.jQuery('#shift').val();
                    d.startDate = global.jQuery('#startDate').val();
                    d.endDate = global.jQuery('#endDate').val();
                },
                dataSrc: function (response) {
                    var rows = Array.isArray(response && response.data) ? response.data : [];
                    cacheRows(getRepository('fuel_sales'), rows).catch(function () {});
                    return rows;
                },
                error: function () {
                    notify('error', 'Fuel sales unavailable', 'Unable to load fuel sales from the server.');
                }
            },
            columns: [
                { data: 'id', name: 'id', render: function (d) { return '#' + d; } },
                { data: 'date', name: 'date' },
                { data: 'salesman', name: 'salesman' },
                {
                    data: 'shift',
                    name: 'shift',
                    render: function (d) {
                        return d ? d.charAt(0).toUpperCase() + d.slice(1) : 'N/A';
                    }
                },
                {
                    data: 'product_transactions',
                    name: 'petrol_total',
                    render: function (data) {
                        var petrol = Array.isArray(data) ? sumByKeyword(data, 'petrol') : null;
                        return petrol ? formatNumber(petrol.total_liters, ' L') : formatNumber(0, ' L');
                    }
                },
                {
                    data: 'product_transactions',
                    name: 'petrol_cash',
                    render: function (data) {
                        var petrol = Array.isArray(data) ? sumByKeyword(data, 'petrol') : null;
                        return petrol ? formatNumber(petrol.cash_liters, ' L') : formatNumber(0, ' L');
                    }
                },
                {
                    data: 'product_transactions',
                    name: 'petrol_credit',
                    render: function (data) {
                        var petrol = Array.isArray(data) ? sumByKeyword(data, 'petrol') : null;
                        return petrol ? formatNumber(petrol.credit_liters, ' L') : formatNumber(0, ' L');
                    }
                },
                {
                    data: 'product_transactions',
                    name: 'diesel_total',
                    render: function (data) {
                        var diesel = Array.isArray(data) ? sumByKeyword(data, 'diesel') : null;
                        return diesel ? formatNumber(diesel.total_liters, ' L') : formatNumber(0, ' L');
                    }
                },
                {
                    data: 'product_transactions',
                    name: 'diesel_cash',
                    render: function (data) {
                        var diesel = Array.isArray(data) ? sumByKeyword(data, 'diesel') : null;
                        return diesel ? formatNumber(diesel.cash_liters, ' L') : formatNumber(0, ' L');
                    }
                },
                {
                    data: 'product_transactions',
                    name: 'diesel_credit',
                    render: function (data) {
                        var diesel = Array.isArray(data) ? sumByKeyword(data, 'diesel') : null;
                        return diesel ? formatNumber(diesel.credit_liters, ' L') : formatNumber(0, ' L');
                    }
                },
                { data: 'balance', name: 'balance', render: function (d) { return formatNumber(d, ''); } },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function (d) {
                        var printUrl = (config.printRouteTemplate || '').replace(':id', d);

                        return [
                            '<ul class="action-list list-unstyled d-flex gap-2 mb-0">',
                            '  <li><a href="' + escapeHtml(printUrl) + '" target="_blank" class="text-success print-sheet mx-4" data-id="' + escapeHtml(d) + '" title="Print Sheet"><i class="fas fa-print"></i></a></li>',
                            '  <li class="mx-4"><a href="javascript:void(0);" class="text-primary view-details" data-id="' + escapeHtml(d) + '" title="View Details"><i class="fas fa-eye"></i></a></li>',
                            '  <li><a href="javascript:void(0);" class="text-danger delete-sale" data-id="' + escapeHtml(d) + '" title="Delete Sale"><i class="fas fa-trash"></i></a></li>',
                            '</ul>'
                        ].join('');
                    }
                }
            ],
            order: [[0, 'desc']],
            createdRow: function (row, data) {
                if (toNumber(data.balance) > 0) {
                    global.jQuery(row).addClass('table-warning');
                }
            },
            language: {
                emptyTable: 'No fuel sales data available',
                processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
            }
        });

        return Promise.resolve(salesTableInstance);
    }

    function initCreditOnlineTable() {
        if (!global.jQuery || !global.jQuery.fn || !global.jQuery.fn.DataTable) {
            return Promise.resolve(null);
        }

        var config = getConfig();
        var tableSelector = '#fuelCreditSalesTable';

        if (global.jQuery.fn.DataTable.isDataTable(tableSelector)) {
            creditTableInstance = global.jQuery(tableSelector).DataTable();
            return Promise.resolve(creditTableInstance);
        }

        creditTableInstance = global.jQuery(tableSelector).DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50],
            ajax: {
                url: config.creditIndexRoute || global.location.href,
                type: 'GET',
                data: function (d) {
                    d.customer_name = global.jQuery('#customer_name').val();
                    d.fuel_type = global.jQuery('#fuel_type').val();
                    d.status = global.jQuery('#status').val();
                    d.startDate = global.jQuery('#startDate').val();
                    d.endDate = global.jQuery('#endDate').val();
                },
                dataSrc: function (response) {
                    var rows = Array.isArray(response && response.data) ? response.data : [];
                    cacheRows(getRepository('fuel_credit_sales'), rows).catch(function () {});
                    return rows;
                },
                error: function () {
                    notify('error', 'Fuel credit sales unavailable', 'Unable to load fuel credit sales from the server.');
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'customer_phone', name: 'customer_phone' },
                { data: 'fuel_type', name: 'fuel_type' },
                { data: 'quantity', name: 'quantity' },
                { data: 'rate', name: 'rate' },
                { data: 'total', name: 'total' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'date', name: 'date' },
                { data: 'created_at', name: 'created_at' }
            ]
        });

        return Promise.resolve(creditTableInstance);
    }

    function renderSalesOffline() {
        var repo = getRepository('fuel_sales');

        if (!repo) {
            return Promise.resolve([]);
        }

        var promise = typeof repo.getAll === 'function' ? repo.getAll() : Promise.resolve([]);

        return promise.then(function (records) {
            var filtered = filterSalesOffline(records || []);
            renderOfflineSalesTable(filtered);
            return filtered;
        });
    }

    function renderCreditOffline() {
        var repo = getRepository('fuel_credit_sales');

        if (!repo) {
            return Promise.resolve([]);
        }

        var promise = typeof repo.getAll === 'function' ? repo.getAll() : Promise.resolve([]);

        return promise.then(function (records) {
            var filtered = filterCreditOffline(records || []);
            renderOfflineCreditTable(filtered);
            return filtered;
        });
    }

    function bindSalesIndexInteractions() {
        var table = document.getElementById('fuelSalesTable');

        if (!table || table.dataset.fuelSalesOfflineBound === 'true') {
            return;
        }

        table.dataset.fuelSalesOfflineBound = 'true';

        table.addEventListener('click', function (event) {
            if (event.target.closest('.view-details')) {
                handleSalesListView(event);
                return;
            }

            if (event.target.closest('.delete-sale')) {
                handleSalesListDelete(event);
            }
        }, true);

        var searchButton = document.getElementById('searchBtn');
        if (searchButton && searchButton.dataset.fuelSalesOfflineBound !== 'true') {
            searchButton.dataset.fuelSalesOfflineBound = 'true';
            searchButton.addEventListener('click', function (event) {
                event.preventDefault();

                if (isOnline() && salesTableInstance && typeof salesTableInstance.draw === 'function') {
                    salesTableInstance.draw();
                    return;
                }

                renderSalesOffline();
            });
        }

        ['product_name', 'transaction_id', 'salesman', 'shift', 'startDate', 'endDate'].forEach(function (fieldId) {
            var field = document.getElementById(fieldId);

            if (!field || field.dataset.fuelSalesOfflineBound === 'true') {
                return;
            }

            field.dataset.fuelSalesOfflineBound = 'true';
            field.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (isOnline() && salesTableInstance && typeof salesTableInstance.draw === 'function') {
                        salesTableInstance.draw();
                        return;
                    }

                    renderSalesOffline();
                }
            });
        });

        var confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn && confirmDeleteBtn.dataset.fuelSalesOfflineBound !== 'true') {
            confirmDeleteBtn.dataset.fuelSalesOfflineBound = 'true';
            confirmDeleteBtn.addEventListener('click', function () {
                confirmSalesDelete();
            });
        }
    }

    function bindCreditIndexInteractions() {
        var table = document.getElementById('fuelCreditSalesTable');

        if (!table || table.dataset.fuelCreditOfflineBound === 'true') {
            return;
        }

        table.dataset.fuelCreditOfflineBound = 'true';

        var searchButton = document.getElementById('searchBtn');
        if (searchButton && searchButton.dataset.fuelCreditOfflineBound !== 'true') {
            searchButton.dataset.fuelCreditOfflineBound = 'true';
            searchButton.addEventListener('click', function (event) {
                event.preventDefault();

                if (isOnline() && creditTableInstance && typeof creditTableInstance.draw === 'function') {
                    creditTableInstance.draw();
                    return;
                }

                renderCreditOffline();
            });
        }

        ['customer_name', 'fuel_type', 'status', 'startDate', 'endDate'].forEach(function (fieldId) {
            var field = document.getElementById(fieldId);

            if (!field || field.dataset.fuelCreditOfflineBound === 'true') {
                return;
            }

            field.dataset.fuelCreditOfflineBound = 'true';
            field.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (isOnline() && creditTableInstance && typeof creditTableInstance.draw === 'function') {
                        creditTableInstance.draw();
                        return;
                    }

                    renderCreditOffline();
                }
            });
        });
    }

    function refreshActivePage() {
        if (isSalesIndexPage()) {
            if (isOnline()) {
                return initSalesOnlineTable();
            }

            return renderSalesOffline();
        }

        if (isCreditIndexPage()) {
            if (isOnline()) {
                return initCreditOnlineTable();
            }

            return renderCreditOffline();
        }

        return Promise.resolve(null);
    }

    function bindConnectivity() {
        if (bindConnectivity.bound) {
            return;
        }

        bindConnectivity.bound = true;

        global.addEventListener('online', function () {
            if (isCreatePage() || isSalesIndexPage() || isCreditIndexPage()) {
                seedLookupData().then(function () {
                    refreshActivePage();
                });
            }
        });

        global.addEventListener('offline', function () {
            if (isCreatePage() || isSalesIndexPage() || isCreditIndexPage()) {
                refreshActivePage();
            }
        });

        global.addEventListener('offline-queue:changed', function () {
            if (isSalesIndexPage() && !isOnline()) {
                renderSalesOffline();
            }

            if (isCreditIndexPage() && !isOnline()) {
                renderCreditOffline();
            }
        });
    }

    function boot() {
        if (booted) {
            return;
        }

        booted = true;

        bindConnectivity();
        bindOfflineCustomerSearch();
        bindOfflineFuelSaleSubmit();
        bindSalesIndexInteractions();
        bindCreditIndexInteractions();

        if (isCreatePage() || isSalesIndexPage() || isCreditIndexPage()) {
            seedLookupData().then(function () {
                refreshActivePage();
            });
        }
    }

    namespace.boot = boot;
    namespace.refreshActivePage = refreshActivePage;
    namespace.renderSalesOffline = renderSalesOffline;
    namespace.renderCreditOffline = renderCreditOffline;
    namespace.submitOfflineFuelSale = submitOfflineFuelSale;

    global.StoreManagementFuelSalesModuleClass = {
        boot: boot
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
