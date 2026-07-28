(function (global) {
    'use strict';

    global.StoreManagementBusinessTableConfig = {
        customers: {
            tableName: 'customers',
            displayName: 'Customers',
            repositoryClassName: 'CustomerRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/customers',
            searchFields: ['customer_name', 'name', 'phone', 'serial', 'birthDate', 'sex', 'depID', 'address', 'description'],
            primaryFields: ['customer_name'],
            referenceFields: ['depID'],
            schemaFields: ['birthDate', 'sex', 'depID']
        },
        products: {
            tableName: 'products',
            displayName: 'Products',
            repositoryClassName: 'ProductRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/products',
            searchFields: ['name', 'sku_code', 'barcode', 'supplier', 'unit', 'type', 'status'],
            primaryFields: ['name'],
            referenceFields: ['depID', 'supplier']
        },
        subcategory: {
            tableName: 'subcategory',
            displayName: 'Subcategories',
            repositoryClassName: 'SubcategoryRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/subcategory',
            searchFields: ['name', 'code', 'description', 'type'],
            primaryFields: ['name'],
            referenceFields: ['depID']
        },
        department: {
            tableName: 'department',
            displayName: 'Departments',
            repositoryClassName: 'DepartmentRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/department',
            searchFields: ['name', 'phone', 'address'],
            primaryFields: ['name'],
            referenceFields: []
        },
        suppliers: {
            tableName: 'suppliers',
            displayName: 'Suppliers',
            repositoryClassName: 'SupplierRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/suppliers',
            searchFields: ['name', 'phone', 'email', 'address', 'balance'],
            primaryFields: ['name'],
            referenceFields: ['depID']
        },
        salesman: {
            tableName: 'salesman',
            displayName: 'Salesmen',
            repositoryClassName: 'SalesmanRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/salesman',
            searchFields: ['full_name', 'phone', 'type', 'sex', 'balance'],
            primaryFields: ['full_name'],
            referenceFields: ['depID']
        },
        store: {
            tableName: 'store',
            displayName: 'Store',
            repositoryClassName: 'StoreRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/store',
            searchFields: ['name', 'key', 'code', 'type', 'value', 'product_name', 'stock_status', 'adjustment_type', 'reason'],
            primaryFields: ['name'],
            referenceFields: []
        },
        fuel_sales: {
            tableName: 'fuel_sales',
            displayName: 'Fuel Sales',
            repositoryClassName: 'FuelSaleRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/fuel-sales',
            searchFields: ['date', 'shift', 'balance', 'net_total', 'cash_on_hand'],
            primaryFields: ['date'],
            referenceFields: ['salesman_id', 'depID', 'created_by']
        },
        fuel_cash_sale: {
            tableName: 'fuel_cash_sale',
            displayName: 'Fuel Cash Sales',
            repositoryClassName: 'FuelCashSaleRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/fuel-cash-sale',
            searchFields: ['customer_id', 'product_id', 'quantity', 'rate', 'total', 'status', 'date', 'description'],
            primaryFields: ['fuel_sale_id'],
            referenceFields: ['fuel_sale_id', 'customer_id', 'product_id']
        },
        fuel_credit_sales: {
            tableName: 'fuel_credit_sales',
            displayName: 'Fuel Credit Sales',
            repositoryClassName: 'FuelCreditSaleRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/fuel-credit-sales',
            searchFields: ['customer_id', 'product_id', 'quantity', 'rate', 'total', 'status', 'date', 'description'],
            primaryFields: ['fuel_sale_id'],
            referenceFields: ['fuel_sale_id', 'customer_id', 'product_id', 'depID']
        },
        fuel_sale_payment: {
            tableName: 'fuel_sale_payment',
            displayName: 'Fuel Sale Payments',
            repositoryClassName: 'FuelSalePaymentRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/fuel-sale-payment',
            searchFields: ['fuel_sale_id', 'payment_rate', 'zaad_dollar', 'edahab_dollar', 'cash_dollar', 'merchant_dollar'],
            primaryFields: ['fuel_sale_id'],
            referenceFields: ['fuel_sale_id', 'depID']
        },
        fuel_sale_transactions: {
            tableName: 'fuel_sale_transactions',
            displayName: 'Fuel Sale Transactions',
            repositoryClassName: 'FuelSaleTransactionRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/fuel-sale-transactions',
            searchFields: ['dphase', 'product_id', 'previous_reading', 'current_reading', 'liters', 'rate', 'total'],
            primaryFields: ['fuel_sale_id'],
            referenceFields: ['fuel_sale_id', 'product_id', 'depID']
        },
        sales: {
            tableName: 'sales',
            displayName: 'Sales',
            repositoryClassName: 'SaleRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/sales',
            searchFields: ['proID', 'product_name', 'customer_name', 'customerID', 'quantity', 'unit', 'price', 'total_price', 'sales_transaction_id'],
            primaryFields: ['sales_transaction_id'],
            referenceFields: ['proID', 'sales_transaction_id', 'depID', 'customerID']
        },
        sales_transactions: {
            tableName: 'sales_transactions',
            displayName: 'Sales Transactions',
            repositoryClassName: 'SalesTransactionRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/sales-transactions',
            searchFields: ['customerID', 'customer', 'customer_name', 'phone', 'paid_date', 'payment_method', 'type', 'note', 'balance', 'seller'],
            primaryFields: ['customerID'],
            referenceFields: ['customerID', 'depID', 'seller']
        },
        sales_returns: {
            tableName: 'sales_returns',
            displayName: 'Sales Returns',
            repositoryClassName: 'SalesReturnRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/sales-returns',
            searchFields: ['sales_id', 'sales_transaction_id', 'customer_name', 'product_name', 'quantity', 'reason', 'refund_amount', 'return_date'],
            primaryFields: ['sales_id'],
            referenceFields: ['sales_id', 'sales_transaction_id', 'proID', 'customerID']
        },
        purchases: {
            tableName: 'purchases',
            displayName: 'Purchases',
            repositoryClassName: 'PurchaseRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/purchases',
            searchFields: ['proID', 'name', 'product_name', 'supplier', 'supplier_name', 'purchase_category', 'quantity', 'unit_cost', 'add_cost', 'total_cost', 'remaining'],
            primaryFields: ['proID', 'supplier_id'],
            referenceFields: ['proID', 'transID', 'depID', 'supplier_id']
        },
        quotation: {
            tableName: 'quotation',
            displayName: 'Quotations',
            repositoryClassName: 'QuotationRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/quotation',
            searchFields: ['customer', 'customer_name', 'phone', 'sub_total', 'discount', 'net_price', 'date', 'info'],
            primaryFields: ['customer'],
            referenceFields: ['depID']
        },
        quotation_orders: {
            tableName: 'quotation_orders',
            displayName: 'Quotation Orders',
            repositoryClassName: 'QuotationOrderRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/quotation-orders',
            searchFields: ['transID', 'proID', 'pro', 'product_name', 'qty', 'unit', 'price', 'total'],
            primaryFields: ['transID'],
            referenceFields: ['transID', 'proID', 'depID']
        },
        expenses: {
            tableName: 'expenses',
            displayName: 'Expenses',
            repositoryClassName: 'ExpenseRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/expenses',
            searchFields: ['amount', 'type', 'payment_account', 'date', 'description'],
            primaryFields: ['type'],
            referenceFields: ['depID', 'salesman_id']
        },
        credits: {
            tableName: 'credits',
            displayName: 'Credits',
            repositoryClassName: 'CreditRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/credits',
            searchFields: ['customerID', 'name', 'customer_name', 'amount', 'pbalance', 'current', 'discount', 'type', 'date', 'payment_method', 'status', 'reference'],
            primaryFields: ['customerID'],
            referenceFields: ['customerID', 'depID', 'seller']
        },
        account_payables: {
            tableName: 'account_payables',
            displayName: 'Account Payables',
            repositoryClassName: 'AccountPayableRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/account-payables',
            searchFields: ['received_from', 'supplier_name', 'amount', 'discount', 'pbalance', 'current', 'type', 'transaction_type', 'account', 'date', 'description'],
            primaryFields: ['received_from'],
            referenceFields: ['depID', 'user', 'supplier_id']
        },
        suppliers_credits: {
            tableName: 'suppliers_credits',
            displayName: 'Supplier Credits',
            repositoryClassName: 'SupplierCreditRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/suppliers-credits',
            searchFields: ['supplier_name', 'supplier_id', 'amount', 'balance', 'previous_balance', 'date', 'description', 'status'],
            primaryFields: ['supplier_name'],
            referenceFields: ['supplier_id', 'depID']
        },
        cash_account: {
            tableName: 'cash_account',
            displayName: 'Cash Accounts',
            repositoryClassName: 'CashAccountRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/cash-account',
            searchFields: ['account', 'date', 'debit', 'credit'],
            primaryFields: ['account'],
            referenceFields: []
        },
        bank_statements: {
            tableName: 'bank_statements',
            displayName: 'Bank Statements',
            repositoryClassName: 'BankStatementRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/bank-statements',
            searchFields: ['amount', 'type', 'description', 'check_no', 'date'],
            primaryFields: ['date'],
            referenceFields: ['depID']
        },
        capital: {
            tableName: 'capital',
            displayName: 'Capital',
            repositoryClassName: 'CapitalRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/capital',
            searchFields: ['owner_name', 'capital_amount'],
            primaryFields: ['owner_name'],
            referenceFields: []
        },
        accounting_transaction: {
            tableName: 'accounting_transaction',
            displayName: 'Accounting Transactions',
            repositoryClassName: 'AccountingTransactionRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/accounting-transaction',
            searchFields: ['date', 'account', 'debit', 'credit'],
            primaryFields: ['date'],
            referenceFields: ['depID']
        },
        assets: {
            tableName: 'assets',
            displayName: 'Assets',
            repositoryClassName: 'AssetRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/assets',
            searchFields: ['name', 'type', 'amount', 'description'],
            primaryFields: ['name'],
            referenceFields: ['depID']
        },
        bad_products: {
            tableName: 'bad_products',
            displayName: 'Bad Products',
            repositoryClassName: 'BadProductRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/bad-products',
            searchFields: ['proID', 'product_name', 'quantity', 'reason', 'reported_date', 'supplier'],
            primaryFields: ['proID'],
            referenceFields: ['proID', 'depID']
        },
        returned_credits: {
            tableName: 'returned_credits',
            displayName: 'Returned Credits',
            repositoryClassName: 'ReturnedCreditRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/returned-credits',
            searchFields: ['customerID', 'customer_name', 'subTotal', 'discount', 'net_price', 'add_cost', 'paidAmount', 'balance', 'date', 'reference', 'type', 'product_name'],
            primaryFields: ['reference'],
            referenceFields: ['customerID', 'depID', 'purchased']
        },
        opening_inventory: {
            tableName: 'opening_inventory',
            displayName: 'Opening Inventory',
            repositoryClassName: 'OpeningInventoryRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/opening-inventory',
            searchFields: ['product_id', 'product_name', 'opening_quantity', 'opening_date', 'stock_status'],
            primaryFields: ['product_id'],
            referenceFields: ['product_id', 'depID']
        },
        salesman_payment: {
            tableName: 'salesman_payment',
            displayName: 'Salesman Payments',
            repositoryClassName: 'SalesmanPaymentRepository',
            offlineEnabled: true,
            requestUrl: '/offline/local/salesman-payment',
            searchFields: ['salesman_id', 'pbalance', 'current', 'discount', 'paid_amount', 'date', 'payment_method'],
            primaryFields: ['salesman_id'],
            referenceFields: ['salesman_id', 'depID']
        }
    };
})(window);
