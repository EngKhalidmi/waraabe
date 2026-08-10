<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesTransactionController;
use App\Http\Controllers\BankStatementController;
use App\Http\Controllers\CreditsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\CapitalController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\PurchaseTransactionController;
use App\Http\Controllers\AccountPayablesController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuotationOrdersController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\BadProductController;
use App\Http\Controllers\SalesmanController;
use App\Http\Controllers\FuelSalesReport;
use App\Http\Controllers\FuelSalesController;
use App\Http\Controllers\OpeningInventoryController;
use App\Http\Controllers\SettingsController;

// MODELS
use App\Models\Sales;
use App\Models\Customers;
use App\Models\Products;
use App\Models\SalesTransactions;
use App\Http\Controllers\SalesmanPaymentController;
use App\Models\FuelCreditSale;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');

Route::view('/offline', 'offline')->name('offline');

// Clear Cache
Route::get('/cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');

    return redirect()->route('dashboard')->with('status', 'Cache and Config cleared!');
})->middleware(['auth'])->name('clean');

Route::get('/myaccount/Profile', function () {
    return view('auth.profile');
})->middleware('role:branch-manager,manager,admin,sales,acc,Nursing,doctor,lab')->name('profile.users');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('role:branch-manager,doctor,manager,admin,sales,acc,Nursing,Lab')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// CUSTOMERS ROUTES
Route::prefix('customers')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('customers', CustomersController::class)->except(['index']);
    Route::get('', [CustomersController::class, 'index'])->name('customers');
    Route::get('/register', [CustomersController::class,'create'])->name('customer.add');
    Route::post('/customer/quick-store', [CustomersController::class,'quickStore'])->name('customer.quickStore');
  
});


Route::prefix('salesman')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('salesman', SalesmanController::class)->except(['index']);
    Route::get('', [SalesmanController::class, 'index'])->name('salesman');
    Route::get('/register', [SalesmanController::class,'create'])->name('salesman.add');
  
});

Route::prefix('opening_inventory')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('opening_inventory', OpeningInventoryController::class)->except(['index']);
    Route::get('', [OpeningInventoryController::class, 'index'])->name('opening_inventory');
    Route::get('/register', [OpeningInventoryController::class,'create'])->name('opening_inventory.add');
  
});



// CREDITS ROUTES
Route::prefix('salesman_payment')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('salesman_payment', SalesmanPaymentController::class)->except(['index']);
    Route::get('', [SalesmanPaymentController::class, 'index'])->name('salesman_payment');
    Route::get('/search-salesman', [SalesmanPaymentController::class, 'searchSalesman'])->name('salesman_payment.searchSalesman');
    Route::get('/register', [SalesmanPaymentController::class, 'create'])->name('salesman_payment.add');
});




// User Routes
Route::prefix('users')->middleware(['role:admin,acc'])->group(function () {
    Route::resource('users', RegisteredUserController::class)->except(['index']);
    Route::get('', [RegisteredUserController::class, 'index'])->name('users');
    Route::get('/register', [RegisteredUserController::class,'create'])->name('users.add');
});

// CAPITAL ROUTES
Route::prefix('capital')->middleware(['role:manager,admin,acc'])->group(function () {
    Route::resource('capital', CapitalController::class)->except(['index']);
    Route::get('', [CapitalController::class, 'index'])->name('capital');
    Route::get('/register', [CapitalController::class, 'create'])->name('capital.add');
});



// SUPPLIERS ROUTES
Route::prefix('suppliers')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('suppliers', SuppliersController::class)->except(['index']);
    Route::get('', [SuppliersController::class, 'index'])->name('suppliers');
    Route::get('/register', [SuppliersController::class,'create'])->name('supplier.add');
    Route::post('/supplier/quick-store', [SuppliersController::class,'quickStore'])->name('supplier.quickStore');
});

// Purchases ROUTES
Route::prefix('purchases')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('purchases', PurchasesController::class)->except(['index']);
    Route::get('', [PurchasesController::class, 'index'])->name('purchases');
    Route::get('/register', [PurchasesController::class, 'create'])->name('purchase.add');
});

// PRODUCTS ROUTES 
Route::prefix('products')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('products', ProductsController::class)->except(['index']);
    Route::get('', [ProductsController::class, 'index'])->name('products');
    Route::get('/purchase', [ProductsController::class,'create'])->name('products.add');
    Route::get('/register', [ProductsController::class,'register'])->name('products.new');
    Route::get('/search-product', [ProductsController::class, 'searchProduct'])->name('products.searchProduct');
    Route::get('/search-supplier', [ProductsController::class, 'searchSupplier'])->name('products.searchSupplier');
    Route::post('products/storeInventory', [ProductsController::class,'storeInventory'])->name('store.Inventory');
});

Route::prefix('bad_products')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('bad_products', BadProductController::class)->except(['index']);
    Route::get('', [BadProductController::class, 'index'])->name('bad_products');
    Route::get('/register/bad/product', [BadProductController::class,'create'])->name('bad_products.add');
});



// EXPENSES ROUTES
Route::prefix('expenses')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('expenses', ExpensesController::class)->except(['index']);
    Route::get('', [ExpensesController::class, 'index'])->name('expenses');
    Route::get('/register', [ExpensesController::class, 'create'])->name('expense.add');
});

// SALES ROUTES
Route::prefix('sales')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('sales', SalesController::class)->except(['index', 'sales_transaction_list']);
    Route::get('', [SalesController::class, 'index'])->name('sales');
    Route::get('/register', [SalesController::class, 'create'])->name('sales.add');
    Route::get('/search-product', [SalesController::class, 'searchProduct'])->name('sales.searchProduct');
    Route::get('/search-customer', [SalesController::class, 'searchCustomer'])->name('sales.searchCustomer');
    Route::get('/selling-price/{id}', [SalesController::class, 'getSellingPrice'])->name('sales.sumOfCostAndAddCost');
});


// SALES TRANSACTION ROUTES
Route::prefix('salesTransactions')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('salesTransactions', SalesTransactionController::class)->except(['index']);
    Route::get('', [SalesTransactionController::class, 'index'])->name('salesTransactions');
    Route::get('/invoice/{id}', [SalesTransactionController::class, 'invoice'])->name('salesTransactions.invoice');
});


// SALES ROUTES
Route::prefix('quotationorders')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('quotationorders', QuotationOrdersController::class)->except(['index']);
    Route::get('', [QuotationOrdersController::class, 'index'])->name('quotationorders');
    Route::get('/register', [QuotationOrdersController::class, 'create'])->name('quotationorders.add');
    Route::get('/search-product', [QuotationOrdersController::class, 'searchProduct'])->name('quotationorders.searchProduct');
    Route::get('/search-customer', [QuotationOrdersController::class, 'searchCustomer'])->name('quotationorders.searchCustomer');
});


// SALES TRANSACTION ROUTES
Route::prefix('quotation')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('quotation', QuotationController::class)->except(['index']);
    Route::get('', [QuotationController::class, 'index'])->name('quotation');
    Route::get('/invoice/{id}', [QuotationController::class, 'invoice'])->name('quotation.invoice');
});


// PURCHASES TRANSACTION ROUTES
Route::prefix('purchaseTransactions')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('purchaseTransactions', PurchaseTransactionController::class)->except(['index']);
    Route::get('', [PurchaseTransactionController::class, 'index'])->name('purchaseTransactions');
    Route::get('/invoice/{id}', [PurchaseTransactionController::class, 'invoice'])->name('purchaseTransactions.add');
});

// CREDITS ROUTES
Route::prefix('credits')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('credits', CreditsController::class)->except(['index']);
    Route::get('', [CreditsController::class, 'index'])->name('credits');
    Route::get('/search-customer', [CreditsController::class, 'searchCustomer'])->name('credits.searchCustomer');
    Route::get('/register', [CreditsController::class, 'create'])->name('credits.add');
    Route::get('/invoice/{id}', [CreditsController::class, 'invoice'])->name('credits.invoice');
});

// SUPPLIER CREDITS
Route::prefix('account_payables')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('account_payables', AccountPayablesController::class)->except(['index']);
    Route::get('', [AccountPayablesController::class, 'index'])->name('account_payables');
    Route::get('suppliers/Credits/{id}/status', [AccountPayablesController::class, 'updateStatus'])->name('account_payables.status');
    Route::get('/search-supplier', [AccountPayablesController::class, 'searchSupplier'])->name('payable.searchSupplier');
    Route::get('/register', [AccountPayablesController::class, 'create'])->name('account_payables.add');
});



// BANK STATEMEN REPORT
Route::prefix('bankStatement')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('bankStatement', BankStatementController::class)->except(['index']);
    Route::get('', [BankStatementController::class, 'index'])->name('bankStatement');
    Route::get('/register', [BankStatementController::class, 'create'])->name('bankStatement.add');
});


//ASSETS ROUTES
Route::prefix('asset')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::resource('asset', AssetsController::class)->except(['index']);
    Route::get('', [AssetsController::class, 'index'])->name('asset');
    Route::get('/register', [AssetsController::class, 'create'])->name('asset.add');
});

//Account ROUTES
Route::prefix('cashAccount')->middleware(['role:manager,admin,sales,acc'])->group(function () {
    Route::resource('cashAccount', CashAccountController::class)->except(['index']);
    Route::get('', [CashAccountController::class, 'index'])->name('cashAccount');
    Route::get('/register', [CashAccountController::class, 'create'])->name('cashAccount.add');
});


Route::prefix('reports')->middleware(['role:branch-manager,manager,branch-branch-manager,manager,admin,sales,acc'])->group(function () {
    // Credits
    Route::get('/creditStatement', [ReportsController::class, 'Credits'])->name('report.credit');
    Route::get('/creditsInfo', [ReportsController::class, 'getCreditsReport'])->name('info.credit');
    
    // Liability
    Route::get('/liabilityTransactions', [ReportsController::class, 'Liability'])->name('report.liability');
    Route::get('/liabilityInfo', [ReportsController::class, 'getLiabilityReport'])->name('info.liability');

    // Expense
    Route::get('/expenseTransactions', [ReportsController::class, 'Expense'])->name('report.expense');
    Route::get('/expenseInfo', [ReportsController::class, 'getExpenseReport'])->name('info.expense');

    // Expense
    Route::get('/bankStatment', [ReportsController::class, 'BankStatement'])->name('report.bank');
    Route::get('/bankInfo', [ReportsController::class, 'getBankSatementReport'])->name('info.bank');

    // Purchase Payment Report
    Route::get('/purchasePayments', [ReportsController::class, 'PurchasePayment'])->name('report.purchasePayment');
    Route::get('/purchasePaymentInfo', [ReportsController::class, 'getPurchasePaymentReport'])->name('info.purchasePayment');

    // Sales  Report
    Route::get('/purchases', [ReportsController::class, 'Purchase'])->name('report.purchase');
    Route::get('/purchasesInfo', [ReportsController::class, 'getPurchaseReport'])->name('info.purchase');

    // Sales Payment Report
    Route::get('/salesPayments', [ReportsController::class, 'SalesPayment'])->name('report.salesPayment');
    Route::get('/salesPaymentInfo', [ReportsController::class, 'getSalesPaymentReport'])->name('info.salesPayment');

    // Sales  Report
    Route::get('/sales', [ReportsController::class, 'Sales'])->name('report.sales');
    Route::get('/salesInfo', [ReportsController::class, 'getSalesReport'])->name('info.sales');

    // Finance Acctivity  Report
    Route::get('/FinanceActivity', [ReportsController::class, 'FinanceActivity'])->name('report.financeActivity');
    Route::get('/ActivityInfo', [ReportsController::class, 'getFinanceAccReport'])->name('info.financeActivity');

    // Balance Sheet
    Route::get('/BalanceSheet', [ReportsController::class, 'getBalanceSheet'])->name('BalanceSheet');

    // Income Statement
    Route::get('/IncomeStatement', [ReportsController::class, 'IncomeStatement'])->name('income.statement');
    Route::get('/income-statement', [ReportsController::class, 'getIncomeStatementReport'])->name('Incomestatement.store');

    // Customer Balance report
    Route::get('/customerBalanceReport', [ReportsController::class, 'customerBalanceReport'])->name('report.customerBalance');
    Route::get('/customerBalance', [ReportsController::class, 'getCustomerBalanceReport'])
    ->name('info.customerbalance');

    
        // Pateint Taken Prescription report
    Route::get('/taken/prescription', [ReportsController::class, 'MedicationLogReport'])->name('report.medication.log');
    Route::get('/medication/report', [ReportsController::class, 'getMedicationReport'])->name('medication.log.report');
    
    Route::get('/inventory/report/get', [ReportsController::class, 'InventoryReport'])->name('report.inventory');
    Route::get('/inventory/report', [ReportsController::class, 'getInventoryReport'])->name('inventory.report');

    Route::get('/fuel/credits/report', [ReportsController::class, 'FuelCreditReport'])->name('report.fuel_credit');
    Route::get('/fuel/credit/report/get', [ReportsController::class, 'getFuelCreditsReport'])->name('fuel_credit.report');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

Route::prefix('admin')->middleware(['role:branch-manager,manager,admin,sales,acc'])->group(function () {
    Route::get('sales', [FuelSalesController::class, 'create'])->name('fuel.sales.create');
    Route::post('sales', [FuelSalesController::class, 'store'])->name('fuel.sales.store');
    Route::get('sales/list', [FuelSalesController::class, 'index'])->name('fuel.sales.index');
    Route::get('credit/sales/list', [FuelSalesController::class, 'fuelCreditSalesList'])->name('fuel.sales.credit.index');
    Route::get('sales/{id}', [FuelSalesController::class, 'show'])->name('fuel.sales.show');
    Route::get('customers/search', [FuelSalesController::class, 'searchCustomers'])->name('customers.search');
    Route::get('products/{id}/price', [FuelSalesController::class, 'getProductPrice'])->name('products.price');
    Route::delete('delete/{id}', [FuelSalesController::class, 'destroy'])->name('fuel.sales.destroy');
    Route::get('/fuel-sales/{id}/print', [FuelSalesController::class, 'printSheet'])->name('fuel.sales.print');
});



// Fuel Sales Report Routes
Route::get('fuel-sales/report', [FuelSalesReport::class, 'fuelSalesReport'])->name('fuel-sales.report');
Route::get('fuel-sales/report-data', [FuelSalesReport::class, 'getFuelSalesReport'])->name('fuel-sales.report-data');

// Combined Fuel Sales Report Routes
Route::get('fuel-sales/combined-report', [FuelSalesReport::class, 'combinedFuelReport'])->name('fuel-sales.combined-report');
Route::get('fuel-sales/combined-report-data', [FuelSalesReport::class, 'getCombinedFuelReport'])->name('fuel-sales.combined-report-data');


require __DIR__ . '/auth.php';
