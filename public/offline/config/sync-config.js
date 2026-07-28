(function (global) {
    'use strict';

    var TABLE_DEFINITIONS = {
        users: {
            table: 'users',
            label: 'Users',
            priority: 1,
            dependsOn: []
        },
        customers: {
            table: 'customers',
            label: 'Customers',
            priority: 1,
            dependsOn: []
        },
        suppliers: {
            table: 'suppliers',
            label: 'Suppliers',
            priority: 1,
            dependsOn: []
        },
        products: {
            table: 'products',
            label: 'Products',
            priority: 1,
            dependsOn: []
        },
        subcategory: {
            table: 'subcategory',
            label: 'Subcategories',
            priority: 1,
            dependsOn: []
        },
        department: {
            table: 'department',
            label: 'Departments',
            priority: 1,
            dependsOn: []
        },
        store: {
            table: 'store',
            label: 'Store',
            priority: 1,
            dependsOn: []
        },
        purchases: {
            table: 'purchases',
            label: 'Purchases',
            priority: 2,
            dependsOn: ['suppliers', 'products', 'store']
        },
        quotation: {
            table: 'quotation',
            label: 'Quotations',
            priority: 2,
            dependsOn: ['customers', 'products']
        },
        quotation_orders: {
            table: 'quotation_orders',
            label: 'Quotation Orders',
            priority: 2,
            dependsOn: ['quotation', 'customers', 'products']
        },
        opening_inventory: {
            table: 'opening_inventory',
            label: 'Opening Inventory',
            priority: 2,
            dependsOn: ['products', 'store']
        },
        fuel_sales: {
            table: 'fuel_sales',
            label: 'Fuel Sales',
            priority: 3,
            dependsOn: ['customers', 'products', 'store']
        },
        fuel_cash_sale: {
            table: 'fuel_cash_sale',
            label: 'Fuel Cash Sales',
            priority: 3,
            dependsOn: ['fuel_sales', 'store']
        },
        fuel_credit_sales: {
            table: 'fuel_credit_sales',
            label: 'Fuel Credit Sales',
            priority: 3,
            dependsOn: ['fuel_sales', 'customers', 'store']
        },
        sales: {
            table: 'sales',
            label: 'Sales',
            priority: 3,
            dependsOn: ['customers', 'products', 'store']
        },
        sales_transactions: {
            table: 'sales_transactions',
            label: 'Sales Transactions',
            priority: 3,
            dependsOn: ['sales', 'customers', 'products', 'store']
        },
        sales_returns: {
            table: 'sales_returns',
            label: 'Sales Returns',
            priority: 4,
            dependsOn: ['sales', 'products', 'customers', 'store']
        },
        credits: {
            table: 'credits',
            label: 'Credits',
            priority: 4,
            dependsOn: ['customers', 'sales']
        },
        suppliers_credits: {
            table: 'suppliers_credits',
            label: 'Supplier Credits',
            priority: 4,
            dependsOn: ['suppliers', 'purchases']
        },
        account_payables: {
            table: 'account_payables',
            label: 'Account Payables',
            priority: 4,
            dependsOn: ['suppliers', 'purchases']
        },
        expenses: {
            table: 'expenses',
            label: 'Expenses',
            priority: 5,
            dependsOn: ['cash_account', 'bank_statements', 'capital']
        },
        cash_account: {
            table: 'cash_account',
            label: 'Cash Account',
            priority: 5,
            dependsOn: []
        },
        capital: {
            table: 'capital',
            label: 'Capital',
            priority: 5,
            dependsOn: []
        },
        assets: {
            table: 'assets',
            label: 'Assets',
            priority: 5,
            dependsOn: ['capital']
        },
        bank_statements: {
            table: 'bank_statements',
            label: 'Bank Statements',
            priority: 5,
            dependsOn: ['cash_account']
        },
        accounting_transaction: {
            table: 'accounting_transaction',
            label: 'Accounting Transactions',
            priority: 5,
            dependsOn: ['cash_account', 'bank_statements', 'capital', 'expenses', 'sales', 'purchases']
        },
        salesman_payment: {
            table: 'salesman_payment',
            label: 'Salesman Payments',
            priority: 5,
            dependsOn: ['sales', 'cash_account']
        }
    };

    var PRIORITY_GROUPS = {
        1: ['users', 'customers', 'suppliers', 'products', 'subcategory', 'department', 'store'],
        2: ['purchases', 'quotation', 'quotation_orders', 'opening_inventory'],
        3: ['fuel_sales', 'fuel_cash_sale', 'fuel_credit_sales', 'sales', 'sales_transactions'],
        4: ['sales_returns', 'credits', 'suppliers_credits', 'account_payables'],
        5: ['expenses', 'cash_account', 'capital', 'assets', 'bank_statements', 'accounting_transaction', 'salesman_payment']
    };

    function clone(value) {
        if (value === null || typeof value === 'undefined') {
            return value;
        }

        return JSON.parse(JSON.stringify(value));
    }

    function normalizeTableName(tableName) {
        return String(tableName || '').trim().toLowerCase();
    }

    function getTableDefinition(tableName) {
        var normalized = normalizeTableName(tableName);

        return TABLE_DEFINITIONS[normalized] || null;
    }

    function getPriority(tableName) {
        var definition = getTableDefinition(tableName);

        return definition ? Number(definition.priority || 999) : 999;
    }

    function getDependencies(tableName) {
        var definition = getTableDefinition(tableName);

        return definition ? clone(definition.dependsOn || []) : [];
    }

    function getDependencyDepth(tableName, memo, stack) {
        var normalized = normalizeTableName(tableName);
        memo = memo || {};
        stack = stack || {};

        if (memo[normalized]) {
            return memo[normalized];
        }

        if (stack[normalized]) {
            return 0;
        }

        stack[normalized] = true;

        var dependencies = getDependencies(normalized);
        var depth = 0;

        dependencies.forEach(function (dependency) {
            var dependencyDepth = getDependencyDepth(dependency, memo, stack) + 1;

            if (dependencyDepth > depth) {
                depth = dependencyDepth;
            }
        });

        memo[normalized] = depth;
        stack[normalized] = false;

        return depth;
    }

    function uniqueTableNames(tableNames) {
        var seen = {};

        return (tableNames || []).reduce(function (result, tableName) {
            var normalized = normalizeTableName(tableName);

            if (!normalized || seen[normalized]) {
                return result;
            }

            seen[normalized] = true;
            result.push(normalized);
            return result;
        }, []);
    }

    function compareTableNames(left, right) {
        var leftPriority = getPriority(left);
        var rightPriority = getPriority(right);

        if (leftPriority !== rightPriority) {
            return leftPriority - rightPriority;
        }

        var depthMemo = {};
        var leftDepth = getDependencyDepth(left, depthMemo);
        var rightDepth = getDependencyDepth(right, depthMemo);

        if (leftDepth !== rightDepth) {
            return leftDepth - rightDepth;
        }

        return String(left).localeCompare(String(right));
    }

    function compareRequests(left, right) {
        var leftTable = normalizeTableName(left && (left.table_name || left.tableName || left.store || left.formName));
        var rightTable = normalizeTableName(right && (right.table_name || right.tableName || right.store || right.formName));
        var tableComparison = compareTableNames(leftTable, rightTable);

        if (tableComparison !== 0) {
            return tableComparison;
        }

        var leftTimestamp = left && (left.timestamp || left.created_at || left.updated_at || '');
        var rightTimestamp = right && (right.timestamp || right.created_at || right.updated_at || '');

        if (leftTimestamp !== rightTimestamp) {
            return String(leftTimestamp).localeCompare(String(rightTimestamp));
        }

        return String(left && (left.request_uuid || left.uuid || '')).localeCompare(String(right && (right.request_uuid || right.uuid || '')));
    }

    function getOrderedTables(tableNames) {
        return uniqueTableNames(tableNames).sort(compareTableNames);
    }

    function sortRequests(requests) {
        return (requests || []).slice().sort(compareRequests);
    }

    function groupByTable(requests) {
        var grouped = {};

        (requests || []).forEach(function (request) {
            var tableName = normalizeTableName(request && (request.table_name || request.tableName || request.store || request.formName));

            if (!tableName) {
                tableName = 'unknown';
            }

            if (!grouped[tableName]) {
                grouped[tableName] = [];
            }

            grouped[tableName].push(clone(request));
        });

        var orderedTables = getOrderedTables(Object.keys(grouped));
        var orderedGroups = {};

        orderedTables.forEach(function (tableName) {
            orderedGroups[tableName] = sortRequests(grouped[tableName]);
        });

        return {
            tables: orderedTables,
            groups: orderedGroups
        };
    }

    function getAllTables() {
        return clone(TABLE_DEFINITIONS);
    }

    var SyncConfig = {
        tables: TABLE_DEFINITIONS,
        priorityGroups: PRIORITY_GROUPS,
        getTable: getTableDefinition,
        getPriority: getPriority,
        getDependencies: getDependencies,
        getDependencyDepth: getDependencyDepth,
        getOrderedTables: getOrderedTables,
        sortRequests: sortRequests,
        groupByTable: groupByTable,
        getAllTables: getAllTables
    };

    global.SyncConfig = SyncConfig;
    global.StoreManagementSyncConfig = SyncConfig;
})(window);
