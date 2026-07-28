<?php

use App\Models\AccountingTransaction;
use App\Models\AccountPayables;
use App\Models\Assets;
use App\Models\BadProduct;
use App\Models\BankStatement;
use App\Models\Capital;
use App\Models\CashAccount;
use App\Models\CreditPayment;
use App\Models\Credits;
use App\Models\Customers;
use App\Models\Departments;
use App\Models\Expenses;
use App\Models\FuelCashSale;
use App\Models\FuelCreditPayment;
use App\Models\FuelCreditSale;
use App\Models\FuelSale;
use App\Models\FuelSalePayment;
use App\Models\FuelSaleTransaction;
use App\Models\OpeningInventory;
use App\Models\PayLiability;
use App\Models\Products;
use App\Models\PurchaseTransactions;
use App\Models\Purchases;
use App\Models\Quotation;
use App\Models\QuotationOrders;
use App\Models\Sales;
use App\Models\SalesPayments;
use App\Models\SalesReturn;
use App\Models\SalesTransactions;
use App\Models\Salesman;
use App\Models\SalesmanPayment;
use App\Models\Store;
use App\Models\SubCategory;
use App\Models\Suppliers;
use App\Models\User;

$sharedRoles = ['admin', 'manager', 'acc', 'sales', 'branch-manager'];
$financeRoles = ['admin', 'manager', 'acc'];
$adminRoles = ['admin', 'acc'];

return [
    'batch_size' => 20,
    'tables' => [
        'users' => [
            'model' => User::class,
            'priority' => 1,
            'depends_on' => [],
            'allowed_roles' => $adminRoles,
        ],
        'customers' => [
            'model' => Customers::class,
            'priority' => 1,
            'depends_on' => [],
            'allowed_roles' => $sharedRoles,
        ],
        'suppliers' => [
            'model' => Suppliers::class,
            'priority' => 1,
            'depends_on' => [],
            'allowed_roles' => $sharedRoles,
        ],
        'products' => [
            'model' => Products::class,
            'priority' => 1,
            'depends_on' => [],
            'allowed_roles' => $sharedRoles,
        ],
        'subcategory' => [
            'model' => SubCategory::class,
            'priority' => 1,
            'depends_on' => [],
            'allowed_roles' => ['admin', 'manager'],
        ],
        'department' => [
            'model' => Departments::class,
            'priority' => 1,
            'depends_on' => [],
            'allowed_roles' => $financeRoles,
        ],
        'store' => [
            'model' => Store::class,
            'priority' => 1,
            'depends_on' => [],
            'allowed_roles' => $sharedRoles,
        ],
        'salesman' => [
            'model' => Salesman::class,
            'priority' => 1,
            'depends_on' => [],
            'allowed_roles' => $sharedRoles,
        ],
        'purchases' => [
            'model' => Purchases::class,
            'priority' => 2,
            'depends_on' => ['suppliers', 'products', 'returned_credits', 'store'],
            'allowed_roles' => $sharedRoles,
        ],
        'quotation' => [
            'model' => Quotation::class,
            'priority' => 2,
            'depends_on' => ['customers', 'products'],
            'allowed_roles' => $sharedRoles,
        ],
        'quotation_orders' => [
            'model' => QuotationOrders::class,
            'priority' => 2,
            'depends_on' => ['quotation', 'customers', 'products'],
            'allowed_roles' => $sharedRoles,
        ],
        'opening_inventory' => [
            'model' => OpeningInventory::class,
            'priority' => 2,
            'depends_on' => ['products', 'store'],
            'allowed_roles' => $sharedRoles,
        ],
        'fuel_sales' => [
            'model' => FuelSale::class,
            'priority' => 3,
            'depends_on' => ['customers', 'products', 'store'],
            'allowed_roles' => $sharedRoles,
        ],
        'fuel_cash_sale' => [
            'model' => FuelCashSale::class,
            'priority' => 3,
            'depends_on' => ['fuel_sales', 'customers', 'products'],
            'allowed_roles' => $sharedRoles,
        ],
        'fuel_credit_sales' => [
            'model' => FuelCreditSale::class,
            'priority' => 3,
            'depends_on' => ['fuel_sales', 'customers', 'products'],
            'allowed_roles' => $sharedRoles,
        ],
        'fuel_sale_payment' => [
            'model' => FuelSalePayment::class,
            'priority' => 3,
            'depends_on' => ['fuel_sales'],
            'allowed_roles' => $sharedRoles,
        ],
        'fuel_sale_transactions' => [
            'model' => FuelSaleTransaction::class,
            'priority' => 3,
            'depends_on' => ['fuel_sales', 'products'],
            'allowed_roles' => $sharedRoles,
        ],
        'sales' => [
            'model' => Sales::class,
            'priority' => 3,
            'depends_on' => ['customers', 'products', 'sales_transactions'],
            'allowed_roles' => $sharedRoles,
        ],
        'sales_transactions' => [
            'model' => SalesTransactions::class,
            'priority' => 3,
            'depends_on' => ['customers', 'products'],
            'allowed_roles' => $sharedRoles,
        ],
        'sales_returns' => [
            'model' => SalesReturn::class,
            'priority' => 4,
            'depends_on' => ['sales', 'sales_transactions', 'products', 'customers'],
            'allowed_roles' => $sharedRoles,
        ],
        'credits' => [
            'model' => Credits::class,
            'priority' => 4,
            'depends_on' => ['customers', 'sales_transactions'],
            'allowed_roles' => $sharedRoles,
        ],
        'suppliers_credits' => [
            'model' => PurchaseTransactions::class,
            'priority' => 4,
            'depends_on' => ['suppliers', 'returned_credits'],
            'allowed_roles' => $sharedRoles,
        ],
        'account_payables' => [
            'model' => AccountPayables::class,
            'priority' => 4,
            'depends_on' => ['suppliers', 'purchases'],
            'allowed_roles' => $financeRoles,
        ],
        'expenses' => [
            'model' => Expenses::class,
            'priority' => 5,
            'depends_on' => ['cash_account', 'bank_statements', 'capital'],
            'allowed_roles' => $financeRoles,
        ],
        'cash_account' => [
            'model' => CashAccount::class,
            'priority' => 5,
            'depends_on' => [],
            'allowed_roles' => $financeRoles,
        ],
        'capital' => [
            'model' => Capital::class,
            'priority' => 5,
            'depends_on' => [],
            'allowed_roles' => $financeRoles,
        ],
        'assets' => [
            'model' => Assets::class,
            'priority' => 5,
            'depends_on' => ['capital'],
            'allowed_roles' => $financeRoles,
        ],
        'bank_statements' => [
            'model' => BankStatement::class,
            'priority' => 5,
            'depends_on' => ['cash_account'],
            'allowed_roles' => $financeRoles,
        ],
        'accounting_transaction' => [
            'model' => AccountingTransaction::class,
            'priority' => 5,
            'depends_on' => ['cash_account', 'bank_statements', 'capital', 'expenses', 'sales', 'purchases'],
            'allowed_roles' => $financeRoles,
        ],
        'salesman_payment' => [
            'model' => SalesmanPayment::class,
            'priority' => 5,
            'depends_on' => ['salesman', 'cash_account'],
            'allowed_roles' => $sharedRoles,
        ],
        'bad_products' => [
            'model' => BadProduct::class,
            'priority' => 2,
            'depends_on' => ['products', 'store'],
            'allowed_roles' => $sharedRoles,
        ],
        'returned_credits' => [
            'model' => PurchaseTransactions::class,
            'priority' => 2,
            'depends_on' => ['suppliers', 'store'],
            'allowed_roles' => $sharedRoles,
        ],
        'fuel_credit_payment' => [
            'model' => FuelCreditPayment::class,
            'priority' => 4,
            'depends_on' => ['fuel_credit_sales'],
            'allowed_roles' => $sharedRoles,
        ],
        'credit_payments' => [
            'model' => CreditPayment::class,
            'priority' => 4,
            'depends_on' => ['fuel_credit_sales'],
            'allowed_roles' => $sharedRoles,
        ],
        'sales_payments' => [
            'model' => SalesPayments::class,
            'priority' => 3,
            'depends_on' => ['fuel_sales', 'products'],
            'allowed_roles' => $sharedRoles,
        ],
        'pay_liabilities' => [
            'model' => PayLiability::class,
            'priority' => 4,
            'depends_on' => ['suppliers'],
            'allowed_roles' => $financeRoles,
        ],
    ],
    'priority_groups' => [
        1 => ['users', 'customers', 'suppliers', 'products', 'subcategory', 'department', 'store', 'salesman'],
        2 => ['purchases', 'quotation', 'quotation_orders', 'opening_inventory', 'bad_products', 'returned_credits'],
        3 => ['fuel_sales', 'fuel_cash_sale', 'fuel_credit_sales', 'fuel_sale_payment', 'fuel_sale_transactions', 'sales', 'sales_transactions', 'sales_payments'],
        4 => ['sales_returns', 'credits', 'suppliers_credits', 'account_payables', 'fuel_credit_payment', 'credit_payments', 'pay_liabilities'],
        5 => ['expenses', 'cash_account', 'capital', 'assets', 'bank_statements', 'accounting_transaction', 'salesman_payment'],
    ],
];
