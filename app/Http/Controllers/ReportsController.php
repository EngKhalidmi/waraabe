<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Models\User;
use App\Models\Sales;
use App\Models\Assets;
use App\Models\Expenses;
use App\Models\FuelSale;
use App\Models\Products;
use App\Models\Salesman;
use App\Models\Purchases;
use App\Models\Suppliers;
use App\Models\CashAccount;
use App\Models\Departments;
use App\Models\FinanceTrans;
use Illuminate\Http\Request;
use App\Models\BankStatement;
use App\Models\FuelCreditSale;
use App\Models\AccountPayables;
use App\Models\SalesTransactions;
use Illuminate\Support\Facades\DB;
use App\Models\Credits;            
use Illuminate\Support\Facades\Log;
use App\Models\PurchaseTransactions;
use App\Models\Customers;            
use Illuminate\Support\Facades\Schema;
class ReportsController extends Controller
{
    /* =====================================================
     |  ROLE & DEPARTMENT HELPERS
     ===================================================== */
    private function isPrivileged(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'manager', 'acc']);
    }

    private function resolveDepartmentId(?int $requestedDepID = null): ?int
    {
        return $this->isPrivileged()
            ? $requestedDepID
            : auth()->user()->depID;
    }

    private function getAccessibleDepartments()
    {
        return $this->isPrivileged()
            ? Departments::get()
            : Departments::where('id', auth()->user()->depID)->get();
    }

    /* =====================================================
     |  VIEW LOADERS
     ===================================================== */
    public function Credits()
    {
        $depID = $this->resolveDepartmentId();

        return view('layout.reports.credits', [
            'departments' => $this->getAccessibleDepartments(),
            'customers' => Customers::when($depID, fn($q) => $q->where('depID', $depID))->get(),
            'users' => User::where('role', 'sales')
                ->when($depID, fn($q) => $q->where('depID', $depID))
                ->get()
        ]);
    }

    public function Liability()
    {
        $depID = $this->resolveDepartmentId();

        return view('layout.reports.liability', [
            'departments' => $this->getAccessibleDepartments(),
            'suppliers' => Suppliers::when($depID, fn($q) => $q->where('depID', $depID))->get()
        ]);
    }

    public function Expense()
    {
        $depID = $this->resolveDepartmentId();

        return view('layout.reports.expense', [
            'departments' => $this->getAccessibleDepartments(),
            'salesman' => Salesman::when($depID, fn($q) => $q->where('depID', $depID))->get()
        ]);
    }

    public function BankStatement()
    {
        return view('layout.reports.bankSatement', [
            'departments' => $this->getAccessibleDepartments()
        ]);
    }

    public function SalesPayment()
    {
        $depID = $this->resolveDepartmentId();

        return view('layout.reports.salesPayment', [
            'departments' => $this->getAccessibleDepartments(),
            'customers' => Customers::when($depID, fn($q) => $q->where('depID', $depID))->get(),
            'users' => User::when($depID, fn($q) => $q->where('depID', $depID))->get()
        ]);
    }

    public function Sales()
    {
        $depID = $this->resolveDepartmentId();

        return view('layout.reports.sales', [
            'departments' => $this->getAccessibleDepartments(),
            'customers' => Customers::when($depID, fn($q) => $q->where('depID', $depID))->get(),
            'products' => Products::when($depID, fn($q) => $q->where('depID', $depID))->get()
        ]);
    }

    public function PurchasePayment()
    {
        $depID = $this->resolveDepartmentId();

        return view('layout.reports.purchasePayment', [
            'departments' => $this->getAccessibleDepartments(),
            'suppliers' => Suppliers::when($depID, fn($q) => $q->where('depID', $depID))->get()
        ]);
    }

    public function Purchase()
    {
        $depID = $this->resolveDepartmentId();

        return view('layout.reports.purchase', [
            'departments' => $this->getAccessibleDepartments(),
            'products' => Products::when($depID, fn($q) => $q->where('depID', $depID))->get()
        ]);
    }

    public function FinanceActivity()
    {
        $depID = $this->resolveDepartmentId();

        return view('layout.reports.FinanceAcc', [
            'departments' => $this->getAccessibleDepartments(),
            'users' => User::when($depID, fn($q) => $q->where('depID', $depID))->get()
        ]);
    }

    public function IncomeStatement()
    {
        return view('layout.reports.incomeStatement', [
            'formattedIncomeStatement' => [
                'SalesRevenue' => 0,
                'netIncome' => 0,
                'expense' => 0,
                'startDate' => '',
                'endDate' => ''
            ]
        ]);
    }

    public function customerBalanceReport()
    {
        return view('layout.reports.customerBalance', [
            'departments' => $this->getAccessibleDepartments()
        ]);
    }

    public function InventoryReport()
    {
        return view('layout.reports.inventory');
    }

    public function FuelCreditReport()
    {
        $depID = $this->resolveDepartmentId();

        return view('layout.reports.fuel_credit_sale', [
            'customers' => Customers::when($depID, fn($q) => $q->where('depID', $depID))->get(),
            'products' => Products::when($depID, fn($q) => $q->where('depID', $depID))->get()
        ]);
    }

    /* =====================================================
     |  REPORT ENGINES
     ===================================================== */
public function getCreditsReport(Request $request)
{
    $user = auth()->user();
    $isAdmin = in_array($user->role, ['admin', 'manager']);

    $clientID  = $request->input('clientID');
    $startDate = $request->input('startDate');
    $endDate   = $request->input('endDate');
    $sellerID  = $request->input('seller');

    // 🔐 ROLE-BASED DEPARTMENT RESOLUTION
    $depID = $isAdmin 
        ? $request->input('depID')     // admin/manager can filter any dep
        : $user->depID;                // others forced to own dep

    // Fetch query for the Credits model
    $query = Credits::with(['customer', 'sellerUser']);

    if ($clientID) {
        $query->where('customerID', 'like', '%' . $clientID . '%');
    }

    if ($depID) {
        $query->where('depID', $depID);
    }

    if ($sellerID) {
        $query->where('seller', $sellerID);
    }

    // Handle date filtering
    if ($startDate && $endDate) {
        $query->whereBetween('date', [$startDate, $endDate]);
    } elseif ($startDate || $endDate) {
        return response()->json([
            'success' => false,
            'message' => 'Both start date and end date are required'
        ]);
    }

    // Execute the query
    $report = $query->get();

    if ($report->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No records found'
        ]);
    }

    // Group by payment type (UNCHANGED)
    $grouped = $report->groupBy('type')->map(function ($group) {
        return [
            'count' => $group->count(),
            'total_amount' => $group->sum('amount'),
            'transactions' => $group->map(function ($payment) {
                return [
                    'date'   => $payment->date,
                    'client' => $payment->customer->customer_name ?? 'Cash Sales',
                    'phone'  => $payment->customer->phone ?? 'N/A',
                    'amount' => number_format($payment->amount, 2),
                    'type'   => $payment->type,
                    'seller' => $payment->sellerUser->name ?? 'Admin',
                ];
            })->values()
        ];
    });

    return response()->json([
        'success' => true,
        'data' => [
            'grouped_by_type' => $grouped,
            'total_types' => $grouped->count(),
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    ]);
}



   public function getPurchasePaymentReport(Request $request)
{
    $depID = $this->resolveDepartmentId($request->depID);

    $query = PurchaseTransactions::with(['supplier', 'purchasedByUser'])
// ✅ IMPORTANT
        ->when($depID, fn ($q) => $q->where('depID', $depID));

    if ($request->startDate && $request->endDate) {
        $query->whereBetween('date', [$request->startDate, $request->endDate]);
    }

    return response()->json([
        'success' => true,
        'data' => $query->get()
    ]);
}


public function getPurchaseReport(Request $request)
{
    $user = auth()->user();
    $isAdmin = in_array($user->role, ['admin', 'manager']);

    $query = PurchaseTransactions::with([
        'purchases.pro',
        'supplier',
        'department',
        'purchasedByUser'
    ]);

    // 🔐 Role restriction
    if (!$isAdmin) {
        $query->where('depID', $user->depID);
    }

    if ($request->depID) {
        $query->where('depID', $request->depID);
    }

    if ($request->salesID) {
        $query->where('id', $request->salesID);
    }

    if ($request->payMethod) {
        $query->where('payMethod', $request->payMethod);
    }

    if ($request->startDate && $request->endDate) {
        $query->whereBetween('date', [$request->startDate, $request->endDate]);
    } elseif ($request->startDate || $request->endDate) {
        return response()->json([
            'success' => false,
            'message' => 'Both start date and end date are required'
        ]);
    }

    $transactions = $query->latest()->get();

    if ($transactions->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No records found'
        ]);
    }

    $data = $transactions->map(function ($trans) use ($request) {
        $items = $trans->purchases;

        if ($request->proID) {
            $items = $items->where('proID', $request->proID);
        }

        return [
            'transaction_id' => $trans->id,
            'supplier' => $trans->supplier->name ?? 'N/A',
            'phone' => $trans->supplier->phone ?? 'N/A',
           'department' => optional($trans->department)->name ?? 'N/A',
            'user' => $trans->purchasedByUser->username ?? 'N/A',
            'date' => $trans->date,
            'subtotal' => number_format($trans->subTotal, 2),
            'discount' => number_format($trans->discount, 2),
            'net_price' => number_format($trans->net_price, 2),
            'paidAmount' => number_format($trans->paidAmount, 2),
            'balance' => number_format($trans->balance, 2),
            'payMethod' => $trans->payMethod,
            'type' => $trans->type,
            'items' => $items->map(fn ($p) => [
                'item' => $p->pro->name ?? 'U/K',
                'unit' => $p->pro->unit ?? 'N/A',
                'quantity' => number_format($p->quantity),
                'unit_cost' => number_format($p->unit_cost, 2),
                'add_cost' => number_format($p->add_cost, 2),
                'total_cost' => number_format($p->total_cost, 2),
                'remaining' => number_format($p->remaining),
            ])->values()
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $data
    ]);
}



   public function getSalesPaymentReport(Request $request) 
    {
            try {
                $request->validate([
                    'startDate' => 'nullable|date',
                    'endDate' => 'nullable|date',
                    'clientID' => 'nullable|integer',
                    'depID' => 'nullable|integer',
                    'type' => 'nullable|string',
                    'payMethod' => 'nullable|string',
                    'seller' => 'nullable|integer'
                ]);
        
                // Get the authenticated user
                $user = auth()->user();
                
                // Eager load relationships with specific columns to optimize query
                $query = SalesTransactions::with([
                    'customer:id,customer_name,phone',
                    'sellerUser:id,name'
                ]);
        
                // Apply security filter - only show records created by this user
                // Unless they're admin/manager who can see all records
                if ($user->role !== 'admin' && $user->role !== 'manager') {
                    $query->where('seller', $user->id);
                }
        
                // Apply filters
                $query->when($request->clientID, function($q) use ($request) {
                    $q->where('customerID', $request->clientID);
                })
                ->when($request->depID, function($q) use ($request) {
                    $q->where('depID', $request->depID);
                })
                ->when($request->type, function($q) use ($request) {
                    $q->where('type', 'like', '%'.$request->type.'%');
                })
                ->when($request->payMethod, function($q) use ($request) {
                    $q->where('payment_method', $request->payMethod);
                })
                ->when($request->seller && ($user->role === 'admin' || $user->role === 'manager'), 
                    function($q) use ($request) {
                        $q->where('seller', $request->seller);
                    }
                );
        
                // Rest of your method remains the same...
                // Date filtering with Carbon
                if ($request->startDate && $request->endDate) {
                    $query->whereBetween('paid_date', [
                        Carbon::parse($request->startDate)->startOfDay(),
                        Carbon::parse($request->endDate)->endOfDay()
                    ]);
                } elseif ($request->startDate || $request->endDate) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Both start date and end date are required'
                    ], 400);
                }
        
                // Get results
                $transactions = $query->get();
        
                if ($transactions->isEmpty()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'No records found'
                    ], 404);
                }
                
        
                // Update the transactions mapping to properly handle dates
                    $paymentMethods = $transactions->groupBy('payment_method')->map(function($group) {
                        return [
                            'count' => $group->count(),
                            'total_paid' => $group->sum('paid_amount'),
                            'transactions' => $group->map(function($transaction) {
                                // Convert string date to Carbon instance if needed
                                $paidDate = is_string($transaction->created_at) 
                                    ? Carbon::parse($transaction->created_at)
                                    : $transaction->created_at;
                    
                                return [
                                    'date' => $paidDate->format('d-m-Y H:i'),
                                    'client' => $transaction->customer->customer_name ?? 'U/K',
                                    'phone' => $transaction->customer->phone ?? 'N/A',
                                    'type' => $transaction->type,
                                    'subTotal' => number_format($transaction->sub_total, 2),
                                    'discount' => number_format($transaction->discount, 2),
                                    'net_price' => number_format($transaction->net_price, 2),
                                    'paidAmount' => number_format($transaction->paid_amount, 2),
                                    'balance' => number_format($transaction->balance, 2),
                                    'seller' => $transaction->sellerUser->name ?? 'Admin',
                                    'payMethod' => $transaction->payment_method
                                ];
                            })->values()
                        ];
                    });
            
                    // Calculate grand totals
                    $grandTotals = [
                        'total_transactions' => $transactions->count(),
                        'total_paid' => $transactions->sum('paid_amount'),
                        'total_discount' => $transactions->sum('discount'),
                        'total_net' => $transactions->sum('net_price'),
                        'total_balance' => $transactions->sum('balance')
                    ];
            
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'payment_methods' => $paymentMethods,
                            'grand_totals' => $grandTotals,
                            'total_payment_methods' => $paymentMethods->count(),
                            'start_date' => $request->startDate,
                            'end_date' => $request->endDate
                        ]
                    ]);
            } catch (\Exception $e) {
                Log::error('Sales Payment Report Error: '.$e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating report',
                    'error' => $e->getMessage()
                ], 500);
            }
        }




 public function getSalesReport(Request $request)
{
    $user = auth()->user();

    $proID = $request->input('proID');
    $salesID = $request->input('salesID');
    $payMethod = $request->input('payMethod');
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    /**
     * =========================
     * ROLE RESTRICTION (ONLY)
     * =========================
     */
    $isAdmin = in_array($user->role, ['admin', 'manager']);

    // If NOT admin/manager → force user's department
    $depID = $isAdmin
        ? $request->input('depID')
        : $user->depID;

    /**
     * =========================
     * ORIGINAL QUERY (UNCHANGED)
     * =========================
     */
    $query = Sales::with(['product', 'transaction'])
        ->select('*', DB::raw('(quantity * price) as calculated_total'));

    if ($proID) {
        $query->where('proID', 'like', '%' . $proID . '%');
    }

    if ($depID) {
        $query->where('depID', $depID);
    }

    if ($salesID) {
        $query->where('sales_transaction_id', 'like', '%' . $salesID . '%');
    }

    if ($payMethod) {
        $query->whereHas('transaction', function ($q) use ($payMethod) {
            $q->where('payment_method', $payMethod);
        });
    }

    // Date filtering
    if ($startDate && $endDate) {
        $endDateAdjusted = Carbon::parse($endDate)->endOfDay();
        $query->whereBetween('created_at', [$startDate, $endDateAdjusted]);
    } elseif ($startDate || $endDate) {
        return response()->json([
            'success' => false,
            'message' => 'Both start date and end date are required'
        ]);
    }

    $salesData = $query->get();

    if ($salesData->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No records found'
        ]);
    }

    /**
     * =========================
     * REST OF YOUR LOGIC
     * (UNCHANGED)
     * =========================
     */

    $groupedProducts = $salesData->groupBy(function ($item) {
        return $item->product->name ?? 'U/K';
    })->map(function ($group) {
        $firstItem = $group->first();

        $totalProfit = $group->sum(function ($sale) {
            return ($sale->price - ($sale->product->actual_price ?? 0)) * $sale->quantity;
        });

        $totalRevenue = $group->sum('total_price');
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        return [
            'product_name' => $firstItem->product->name ?? 'U/K',
            'unit' => $firstItem->product->unit ?? 'N/A',
            'total_quantity' => $group->sum('quantity'),
            'average_price' => $group->avg('price'),
            'average_actual_price' => $group->avg(fn ($sale) => $sale->product->actual_price ?? 0),
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'profit_margin' => number_format($profitMargin, 2) . '%',
            'sales_count' => $group->count(),
            'product_ids' => $group->pluck('proID')->unique()->values()
        ];
    })->sortBy('product_name')->values();

    $detailedRecords = $salesData->sortBy(fn ($item) => [
        $item->product->name ?? 'U/K',
        $item->created_at
    ])->map(function ($sale) {
        $actualPrice = $sale->product->actual_price ?? 0;
        $profitPerUnit = $sale->price - $actualPrice;
        $totalProfit = $profitPerUnit * $sale->quantity;

        return [
            'date' => $sale->created_at->format('Y-m-d H:i:s'),
            'item' => $sale->product->name ?? 'U/K',
            'unit' => $sale->product->unit ?? 'N/A',
            'quantity' => number_format($sale->quantity, 2),
            'price' => number_format($sale->price, 2),
            'actual_price' => number_format($actualPrice, 2),
            'profit_per_unit' => number_format($profitPerUnit, 2),
            'total' => number_format($sale->total_price, 2),
            'total_profit' => number_format($totalProfit, 2),
            'salesID' => $sale->sales_transaction_id,
            'payment_method' => $sale->transaction->payment_method ?? 'N/A',
            'product_id' => $sale->proID
        ];
    })->values();

    $grandTotalRevenue = $groupedProducts->sum('total_revenue');
    $grandTotalProfit = $groupedProducts->sum('total_profit');
    $grandTotalProfitMargin = $grandTotalRevenue > 0
        ? ($grandTotalProfit / $grandTotalRevenue) * 100
        : 0;

    return response()->json([
        'success' => true,
        'data' => [
            'summary' => $groupedProducts,
            'detailed' => $detailedRecords
        ],
        'metadata' => [
            'total_products' => $groupedProducts->count(),
            'total_quantity' => $groupedProducts->sum('total_quantity'),
            'grand_total_revenue' => $grandTotalRevenue,
            'grand_total_profit' => $grandTotalProfit,
            'overall_profit_margin' => number_format($grandTotalProfitMargin, 2) . '%',
            'timeframe' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ]
    ]);
}



    // Student Balance Report
 public function getCustomerBalanceReport(Request $request)
{
    $user = auth()->user();

    $name  = $request->input('name');
    $depID = $request->input('depID');

    $query = Customers::query();

    // 🔐 Role-based department restriction
    if (in_array($user->role, ['admin', 'manager'])) {
        // Admin / Manager can choose department
        if ($depID) {
            $query->where('depID', $depID);
        }
    } else {
        // Other users can ONLY see their own department
        $query->where('depID', $user->depID);
    }

    // 🔍 Filter by customer name
    if ($name) {
        $query->where('customer_name', 'like', '%' . $name . '%');
    }

    // 💰 Balance filter (>= 0 means include zero balances)
    $query->where('balance', '>=', 0);

    // ⬇ Order by highest balance first
    $query->orderBy('balance', 'desc');

    $report = $query->get();

    // ✅ ALWAYS return 200 (even if empty)
    return response()->json(
        $report->map(function ($customer) {
            return [
                'serial'  => $customer->serial,
                'name'    => $customer->customer_name,
                'phone'   => $customer->phone,
                'address' => $customer->address,
                'balance' => number_format($customer->balance, 2),
            ];
        }),
        200
    );
}







    // Liability Transactions
   public function getLiabilityReport(Request $request)
{
    $depID = $this->resolveDepartmentId($request->depID);

    $query = AccountPayables::when($depID, fn ($q) => $q->where('depID', $depID))
        ->when($request->supplier, fn ($q) =>
            $q->where('received_from', 'like', "%{$request->supplier}%")
        )
        ->when($request->type, fn ($q) =>
            $q->where('type', 'like', "%{$request->type}%")
        )
        ->when($request->trnsType, fn ($q) =>
            $q->where('transaction_type', 'like', "%{$request->trnsType}%")
        );

    if ($request->startDate && $request->endDate) {
        $query->whereBetween('date', [$request->startDate, $request->endDate]);
    }

    $data = $query->get();

    if ($data->isEmpty()) {
        return response()->json(['success' => false, 'message' => 'No records found']);
    }

    return response()->json([
        'success' => true,
        'data' => $data->map(fn ($p) => [
            'date' => $p->date,
            'client' => $p->received_from,
            'amount' => number_format($p->amount, 2),
            'type' => $p->type,
            'trnsType' => $p->transaction_type,
            'info' => $p->description,
        ])
    ]);
}


    // Expense Transactions
   public function getExpenseReport(Request $request)
{
    $depID = $this->resolveDepartmentId();

    $type = $request->input('type');
    $salesman_id = $request->input('salesman_id');
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    // Base query
    $query = Expenses::with('salesman');

    /**
     * ===================== ROLE + DEPARTMENT FILTER =====================
     * Admin -> all departments
     * Non-admin -> only their department
     */
    if ($depID) {
        $query->where('depID', $depID);
    }

    /**
     * ===================== OPTIONAL FILTERS =====================
     */
    if ($type) {
        $query->where('type', 'like', "%{$type}%");
    }

    if ($salesman_id) {
        $query->where('salesman_id', $salesman_id);
    }

    /**
     * ===================== DATE FILTER =====================
     */
    if ($startDate && $endDate) {
        $query->whereBetween('date', [$startDate, $endDate]);
    } elseif ($startDate || $endDate) {
        return response()->json([
            'success' => false,
            'message' => 'Both start date and end date are required'
        ]);
    }

    /**
     * ===================== EXECUTE =====================
     */
    $report = $query->get();

    if ($report->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No records found'
        ]);
    }

    /**
     * ===================== TOTAL =====================
     */
    $totalExpenses = $report->sum('amount');

    /**
     * ===================== FORMAT RESPONSE =====================
     */
    $formattedReport = $report->map(function ($expense) {
        return [
            'date' => $expense->date,
            'type' => $expense->type,
            'salesman_name' => optional($expense->salesman)->full_name ?? 'N/A',
            'amount' => number_format($expense->amount, 2),
            'info' => $expense->description,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $formattedReport,
        'total_expenses' => number_format($totalExpenses, 2)
    ]);
}


    // Finance Activity Transactions
   // Finance Activity Transactions
public function getFinanceAccReport(Request $request)
{
    $userID    = $request->input('userID');
    $startDate = $request->input('startDate');
    $endDate   = $request->input('endDate');

    // 🔐 Resolve department based on role
    // Admin → can pass depID
    // Non-admin → forced to own depID
    $depID = $this->resolveDepartmentId($request->input('depID'));

    $query = FinanceTrans::with('users'); // eager load user relation

    /**
     * ===================== ROLE + DEPARTMENT =====================
     */
    if ($depID) {
        $query->where('depID', $depID);
    }

    /**
     * ===================== OPTIONAL FILTERS =====================
     */
    if ($userID) {
        $query->where('user', $userID); // numeric comparison ✔
    }

    /**
     * ===================== DATE FILTER =====================
     */
    if ($startDate && $endDate) {
        $query->whereBetween('date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    } elseif ($startDate || $endDate) {
        return response()->json([
            'success' => false,
            'message' => 'Both start date and end date are required'
        ]);
    }

    /**
     * ===================== EXECUTE =====================
     */
    $report = $query->orderBy('date', 'asc')->get();

    if ($report->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No records found'
        ]);
    }

    /**
     * ===================== FORMAT =====================
     */
    $formattedReport = $report->map(function ($activity) {
        return [
            'date' => $activity->date,
            'depitAcc' => $activity->depitAcc,
            'depitAmount' => number_format($activity->depitAmount, 2),
            'creditAcc' => $activity->creditAcc,
            'creditAmount' => number_format($activity->creditAmount, 2),
            'formType' => $activity->formType,
            'action' => $activity->action,
            'user' => optional($activity->users)->name ?? 'System',
            'info' => $activity->info,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $formattedReport
    ]);
}



    // bank Statement Transactions
// Bank Statement Transactions
public function getBankSatementReport(Request $request)
{
    $type      = $request->input('type');
    $startDate = $request->input('startDate');
    $endDate   = $request->input('endDate');

    // 🔐 Resolve department (admin vs non-admin)
    $depID = $this->resolveDepartmentId($request->input('depID'));

    /**
     * ===================== DATE VALIDATION =====================
     */
    if (($startDate && !$endDate) || (!$startDate && $endDate)) {
        return response()->json([
            'success' => false,
            'message' => 'Both start date and end date are required'
        ]);
    }

    /**
     * ===================== OPENING BALANCE =====================
     */
    $beginningBalance = 0;

    if ($startDate) {
        $openingQuery = BankStatement::query()
            ->when($type, fn ($q) =>
                $q->where('type', 'like', "%{$type}%")
            )
            ->when($depID, fn ($q) =>
                $q->where('depID', $depID)
            )
            ->where('date', '<', Carbon::parse($startDate)->startOfDay())
            ->get();

        foreach ($openingQuery as $row) {
            // Debit increases, Credit decreases
            $beginningBalance += $row->type === 'Debit'
                ? $row->amount
                : -$row->amount;
        }
    }

    /**
     * ===================== MAIN QUERY =====================
     */
    $query = BankStatement::with('department')
        ->when($type, fn ($q) =>
            $q->where('type', 'like', "%{$type}%")
        )
        ->when($depID, fn ($q) =>
            $q->where('depID', $depID)
        );

    if ($startDate && $endDate) {
        $query->whereBetween('date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    $statements = $query->orderBy('date')->get();

    if ($statements->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No records found',
            'opening_balance' => number_format($beginningBalance, 2)
        ]);
    }

    /**
     * ===================== RUNNING BALANCE =====================
     */
    $balance = $beginningBalance;
    $formattedReport = [];

    foreach ($statements as $statement) {
        $balance += $statement->type === 'Debit'
            ? $statement->amount
            : -$statement->amount;

        $formattedReport[] = [
            'date' => $statement->date,
            'ref' => $statement->id,
            'branch' => $statement->branch ?? '001',
            'particulars' => $statement->description,
            'cheque_no' => $statement->check_no,
            'withdrawal' => $statement->type === 'Credit'
                ? number_format($statement->amount, 2)
                : '0.00',
            'deposit' => $statement->type === 'Debit'
                ? number_format($statement->amount, 2)
                : '0.00',
            'balance' => number_format($balance, 2),
            'type' => $statement->type,
            'department' => optional($statement->department)->name ?? '',
        ];
    }

    return response()->json([
        'success' => true,
        'data' => $formattedReport,
        'opening_balance' => number_format($beginningBalance, 2)
    ]);
}


    
  public function getIncomeStatementReport(Request $request)
{
    try {
        $startDate = $request->input('startDate');
        $endDate   = $request->input('endDate');

        // 🔐 Department resolution (ADMIN = all, others = own)
        $depID = $this->resolveDepartmentId();

        /**
         * ===================== DATE VALIDATION =====================
         */
        if (($startDate && !$endDate) || (!$startDate && $endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Both start date and end date are required.'
            ], 422);
        }

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end   = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        /**
         * ===================== PAYMENT METHODS =====================
         */
        $paymentMethods = [
            'Zaad Service',
            'Cash On Hand',
            'Credit on Book',
            'Premier Wallet',
            'E-Dahab',
            'MERCHANT'
        ];

        /**
         * ===================== BASE QUERIES =====================
         */
        $salesQuery               = Sales::with('product');
        $salesTransactionsQuery   = SalesTransactions::query();
        $creditPaymentsQuery      = Credits::query();
        $regularFuelSalesQuery    = FuelSale::with(['transactions.product', 'creditSales.product']);
        $fuelCreditSalesQuery     = FuelCreditSale::with('product');
        $expenseQuery             = Expenses::query();

        /**
         * ===================== DEPARTMENT FILTER =====================
         */
        if ($depID) {
            $salesQuery->where('depID', $depID);
            $salesTransactionsQuery->where('depID', $depID);
            $creditPaymentsQuery->where('depID', $depID);
            $regularFuelSalesQuery->where('depID', $depID);
            $fuelCreditSalesQuery->where('depID', $depID);
            $expenseQuery->where('depID', $depID);
        }

        /**
         * ===================== DATE FILTER =====================
         */
        if ($start && $end) {
            $salesQuery->whereBetween('created_at', [$start, $end]);
            $salesTransactionsQuery->whereBetween('paid_date', [$start, $end]);
            $creditPaymentsQuery->whereBetween('date', [$start, $end]);
            $regularFuelSalesQuery->whereBetween('date', [$start, $end]);
            $fuelCreditSalesQuery->whereBetween('date', [$start, $end]);
            $expenseQuery->whereBetween('date', [$start, $end]);
        }

        /**
         * ===================== EXECUTE =====================
         */
        $sales             = $salesQuery->get();
        $salesTransactions = $salesTransactionsQuery->get();
        $creditPayments    = $creditPaymentsQuery->get();
        $regularFuelSales  = $regularFuelSalesQuery->get();
        $fuelCreditSales   = $fuelCreditSalesQuery->get();
        $expenses          = $expenseQuery->get();

        if (
            $sales->isEmpty() &&
            $expenses->isEmpty() &&
            $regularFuelSales->isEmpty() &&
            $fuelCreditSales->isEmpty() &&
            $start && $end
        ) {
            return response()->json([
                'success' => false,
                'message' => 'No records found for the selected date range.'
            ], 404);
        }

        /**
         * ===================== PAYMENT METHOD TOTALS =====================
         */
        $paymentMethodTotalsSales = [];
        foreach ($paymentMethods as $method) {
            $paymentMethodTotalsSales[$method] =
                $salesTransactions->where('payment_method', $method)->sum('net_price');
        }

        $paymentMethodTotalsCredits = [];
        foreach ($paymentMethods as $method) {
            $paymentMethodTotalsCredits[$method] =
                $creditPayments->where('payment_method', $method)->sum('amount');
        }

        /**
         * ===================== SALES REVENUE =====================
         */
        $totalSalesRevenue   = $salesTransactions->sum('net_price');
        $totalDiscount       = $salesTransactions->sum('discount');
        $totalCreditPayments = $creditPayments->sum('amount');
        $netSalesRevenue     = $totalSalesRevenue - $totalDiscount;

        /**
         * ===================== FUEL REVENUE =====================
         */
        $totalRegularFuelRevenue = 0;
        $regularFuelDiscount     = 0;

        foreach ($regularFuelSales as $fuelSale) {
            foreach ($fuelSale->transactions as $transaction) {
                $totalRegularFuelRevenue += $transaction->total;
            }
            $regularFuelDiscount += $fuelSale->discount;
        }

        $totalCreditFuelRevenue = $fuelCreditSales->sum('total');

        $netRegularFuelRevenue = $totalRegularFuelRevenue - $regularFuelDiscount;
        $netCreditFuelRevenue  = $totalCreditFuelRevenue;

        $totalFuelSalesRevenue = $totalRegularFuelRevenue + $totalCreditFuelRevenue;
        $totalFuelDiscount     = $regularFuelDiscount;
        $netFuelSalesRevenue   = $netRegularFuelRevenue + $netCreditFuelRevenue;

        /**
         * ===================== TOTAL REVENUE =====================
         */
        $totalRevenue = $netSalesRevenue + $netFuelSalesRevenue;

        /**
         * ===================== COGS =====================
         */
        $totalCOGS = 0;

        foreach ($sales as $sale) {
            if ($sale->product) {
                $totalCOGS += $sale->product->actual_price * $sale->quantity;
            }
        }

        $totalRegularFuelCOGS = 0;
        foreach ($regularFuelSales as $fuelSale) {
            foreach ($fuelSale->transactions as $transaction) {
                if ($transaction->product) {
                    $cost = $transaction->product->cost_price
                        ?? $transaction->product->actual_price;
                    $totalRegularFuelCOGS += $cost * $transaction->liters;
                }
            }
        }

        $totalFuelCreditCOGS = 0;
        foreach ($fuelCreditSales as $fuelSale) {
            if ($fuelSale->product) {
                $cost = $fuelSale->product->cost_price
                    ?? $fuelSale->product->actual_price;
                $totalFuelCreditCOGS += $cost * $fuelSale->quantity;
            }
        }

        $totalFuelCOGS = $totalRegularFuelCOGS + $totalFuelCreditCOGS;
        $totalCOGS += $totalFuelCOGS;

        /**
         * ===================== PROFIT =====================
         */
        $totalExpenses = $expenses->sum('amount');
        $grossProfit   = $totalRevenue - $totalCOGS;
        $netIncome     = $grossProfit - $totalExpenses;

        /**
         * ===================== RESPONSE =====================
         */
        return response()->json([
            'success' => true,
            'data' => [
                'SalesRevenue' => $totalSalesRevenue,
                'RegularFuelSalesRevenue' => $totalRegularFuelRevenue,
                'CreditFuelSalesRevenue' => $totalCreditFuelRevenue,
                'TotalFuelSalesRevenue' => $totalFuelSalesRevenue,
                'NetRegularFuelRevenue' => $netRegularFuelRevenue,
                'NetCreditFuelRevenue' => $netCreditFuelRevenue,
                'NetFuelSalesRevenue' => $netFuelSalesRevenue,
                'RegularFuelDiscount' => $regularFuelDiscount,
                'TotalFuelDiscount' => $totalFuelDiscount,
                'totalCreditPayments' => $totalCreditPayments,
                'netSales' => $netSalesRevenue,
                'COGS' => $totalCOGS,
                'RegularFuelCOGS' => $totalRegularFuelCOGS,
                'CreditFuelCOGS' => $totalFuelCreditCOGS,
                'TotalFuelCOGS' => $totalFuelCOGS,
                'gross_profit' => $grossProfit,
                'netIncome' => $netIncome,
                'total_discount' => $totalDiscount,
                'expense' => $totalExpenses,
                'startDate' => $startDate ?? '',
                'endDate' => $endDate ?? '',
                'paymentMethodTotals' => $paymentMethodTotalsSales,
                'paymentMethodTotalsCredits' => $paymentMethodTotalsCredits,
                'totalRevenue' => $totalRevenue,
                'regularSalesCount' => $sales->count(),
                'regularFuelSalesCount' => $regularFuelSales->count(),
                'creditFuelSalesCount' => $fuelCreditSales->count(),
            ]
        ]);

    } catch (\Throwable $e) {
        Log::error('Income Statement Report Failed', [
            'message' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Unexpected error while generating report.'
        ], 500);
    }
}

    

    
    // Report Of System Balance Sheet
public function getBalanceSheet(Request $request)
{
    $startDate = $request->input('startDate');
    $endDate   = $request->input('endDate');

    $depID = $this->resolveDepartmentId();

    /**
     * ===================== DATE VALIDATION =====================
     */
    if (($startDate && !$endDate) || (!$startDate && $endDate)) {
        return back()->withErrors(['Both start date and end date are required']);
    }

    $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
    $end   = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

    /**
     * ===================== ACCOUNTS (FILTER BY DEP) =====================
     */
    $accounts = CashAccount::when($depID, function ($q) use ($depID) {
        $q->where('depID', $depID);
    })->get();

    /**
     * ===================== CLIENTS & ASSETS =====================
     */
    $clients = Customers::when($depID, fn($q) => $q->where('depID', $depID))->get();
    $assets  = Assets::when($depID, fn($q) => $q->where('depID', $depID))->get();

    /**
     * ===================== EXPENSE =====================
     */
    $totalExpense = Expenses::when($depID, fn($q) => $q->where('depID', $depID))
        ->when($start && $end, fn($q) => $q->whereBetween('date', [$start, $end]))
        ->sum('amount');

    /**
     * ===================== CASH SALES =====================
     */
    $cashRevenue = SalesTransactions::when($depID, fn($q) => $q->where('depID', $depID))
        ->when($start && $end, fn($q) => $q->whereBetween('paid_date', [$start, $end]))
        ->sum('net_price');

    /**
     * ===================== SALES (FOR COGS) =====================
     */
    $sales = Sales::with('product')
        ->when($depID, fn($q) => $q->where('depID', $depID))
        ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
        ->get();

    /**
     * ===================== CASH =====================
     */
    $totalCash = $accounts->where('AccCode', 'Cash')->sum('debit')
               - $accounts->where('AccCode', 'Cash')->sum('credit');

    /**
     * ===================== BANK =====================
     */
    $totalBank = $accounts->where('AccCode', 'Bank')->sum('debit')
               - $accounts->where('AccCode', 'Bank')->sum('credit');

    /**
     * ===================== INVENTORY =====================
     */
    $totalInventory = $accounts->where('AccCode', 'Inventory')->sum('debit')
                    - $accounts->where('AccCode', 'Inventory')->sum('credit');

    /**
     * ===================== FIXED ASSETS =====================
     */
    $fixedAsset = $accounts->where('AccCode', 'Fixed')->sum('debit')
                - $accounts->where('AccCode', 'Fixed')->sum('credit');

    /**
     * ===================== RECEIVABLE =====================
     */
    $receivable = $clients->sum('balance');

    /**
     * ===================== TOTAL ASSETS =====================
     */
    $totalAssets = $totalCash + $totalBank + $totalInventory + $fixedAsset + $receivable;

    /**
     * ===================== LIABILITIES =====================
     */
    $longTerm = $accounts->where('AccCode', 'Long Term')->sum('debit')
               - $accounts->where('AccCode', 'Long Term')->sum('credit');

    $shortTerm = $accounts->where('AccCode', 'Short Term')->sum('credit')
                - $accounts->where('AccCode', 'Short Term')->sum('debit');

    $totalLiabilities = $longTerm + $shortTerm;

    /**
     * ===================== CAPITAL =====================
     */
    $capital = $accounts->where('AccCode', 'Capital')->sum('credit');

    /**
     * ===================== COGS =====================
     */
    $totalCOGS = 0;
    foreach ($sales as $sale) {
        if ($sale->product) {
            $totalCOGS += $sale->product->actual_price * $sale->quantity;
        }
    }

    /**
     * ===================== NET INCOME =====================
     */
    $totalCost = $totalExpense + $totalCOGS;
    $netIncome = $cashRevenue - $totalCost;

    /**
     * ===================== EQUITY =====================
     */
    $ownerEquity = $capital + $netIncome;
    $capitalLiability = $ownerEquity + $totalLiabilities;

    /**
     * ===================== RESPONSE =====================
     */
    $balanceSheetData = [
        'totalCash' => $totalCash,
        'Bank' => $totalBank,
        'Inventory' => $totalInventory,
        'fixedAsset' => $fixedAsset,
        'receivable' => $receivable,
        'totalAssets' => $totalAssets,
        'longTerm' => $longTerm,
        'shortTerm' => $shortTerm,
        'totalLiabilities' => $totalLiabilities,
        'capital' => $capital,
        'retained' => $netIncome,
        'TotalEquity' => $ownerEquity,
        'Assets' => $totalAssets,
        'capitalLiability' => $capitalLiability,
    ];

    return view('layout.reports.balancesheet', compact('balanceSheetData'));
}


//   public function getMedicationReport(Request $request)
// {
//     $query = MedicationLog::with(['medication', 'user', 'customer']);

//     if ($request->filled('patient_id')) {
//         $query->where('patient_id', $request->patient_id);
//     }

//     if ($request->filled('start_date') && $request->filled('end_date')) {
//         $query->whereBetween('taken_at', [
//             Carbon::parse($request->start_date)->startOfDay(),
//             Carbon::parse($request->end_date)->endOfDay()
//         ]);
//     }

//     return response()->json([
//         'success' => true,
//         'data' => $query->latest()->get()
//     ]);
// }




public function getInventoryReport(Request $request)
{
    // ============================
    // Role restriction
    // ============================
    $user = auth()->user();
    $isAdmin = in_array($user->role, ['admin', 'manager']);

    $depID = $isAdmin ? null : $user->depID;

    // ============================
    // Validate
    // ============================
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
    ]);

    // Dates as Carbon (inclusive)
    $startDate = $request->filled('startDate')
        ? Carbon::parse($request->input('startDate'))->startOfDay()
        : Carbon::now()->subWeek()->startOfDay();

    $endDate = $request->filled('endDate')
        ? Carbon::parse($request->input('endDate'))->endOfDay()
        : Carbon::now()->endOfDay();

    // ============================
    // Products (role restricted)
    // ============================
    $products = Products::where('created_at', '<=', $endDate->toDateTimeString())
        ->when($depID, fn ($q) => $q->where('depID', $depID))
        ->get();

    if ($products->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No products found for the selected period.'
        ]);
    }

    // Group by product name
    $groups = $products->groupBy('name');

    $allProductIds = $products->pluck('id')->unique()->values()->all();

    // ============================
    // Detect purchases qty column
    // ============================
    $purchaseQtyCol = null;
    if (Schema::hasTable('purchases')) {
        foreach (['quantity', 'qty', 'received', 'received_qty'] as $c) {
            if (Schema::hasColumn('purchases', $c)) {
                $purchaseQtyCol = $c;
                break;
            }
        }
    }

    // ============================
    // Sales (role restricted)
    // ============================
    $salesUpToStart = Sales::whereIn('proID', $allProductIds)
        ->when($depID, fn ($q) => $q->where('depID', $depID))
        ->whereHas('transaction', fn ($q) =>
            $q->where('created_at', '<', $startDate)
        )
        ->select('proID', DB::raw('SUM(quantity) as qty'))
        ->groupBy('proID')
        ->pluck('qty', 'proID')
        ->toArray();

    $salesDuring = Sales::whereIn('proID', $allProductIds)
        ->when($depID, fn ($q) => $q->where('depID', $depID))
        ->whereHas('transaction', fn ($q) =>
            $q->whereBetween('created_at', [$startDate, $endDate])
        )
        ->select('proID', DB::raw('SUM(quantity) as qty'))
        ->groupBy('proID')
        ->pluck('qty', 'proID')
        ->toArray();

    // ============================
    // Purchases (role restricted)
    // ============================
    $purchasesUpToStart = [];
    $purchasesDuring = [];

    if ($purchaseQtyCol) {
        $purchasesUpToStart = Purchases::whereIn('proID', $allProductIds)
            ->when($depID, fn ($q) => $q->where('depID', $depID))
            ->where('created_at', '<', $startDate)
            ->select('proID', DB::raw("SUM($purchaseQtyCol) as qty"))
            ->groupBy('proID')
            ->pluck('qty', 'proID')
            ->toArray();

        $purchasesDuring = Purchases::whereIn('proID', $allProductIds)
            ->when($depID, fn ($q) => $q->where('depID', $depID))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('proID', DB::raw("SUM($purchaseQtyCol) as qty"))
            ->groupBy('proID')
            ->pluck('qty', 'proID')
            ->toArray();
    }

    // ============================
    // Helper
    // ============================
    $sumMapForIds = function (array $map, array $ids) {
        return collect($ids)->sum(fn ($id) => (float)($map[$id] ?? 0));
    };

    // ============================
    // Build result
    // ============================
    $result = $groups->map(function ($group) use (
        $startDate, $endDate,
        $sumMapForIds,
        $salesUpToStart, $salesDuring,
        $purchasesUpToStart, $purchasesDuring,
        $purchaseQtyCol
    ) {
        $productIds = $group->pluck('id')->all();
        $p = $group->first();

        $soldBefore = $sumMapForIds($salesUpToStart, $productIds);
        $soldDuring = $sumMapForIds($salesDuring, $productIds);

        $purchasedBefore = $purchaseQtyCol ? $sumMapForIds($purchasesUpToStart, $productIds) : 0;
        $purchasedDuring = $purchaseQtyCol ? $sumMapForIds($purchasesDuring, $productIds) : 0;

        $initial = $purchasedBefore - $soldBefore;
        $remaining = $initial + $purchasedDuring - $soldDuring;

        $unitCost = $p->actual_price ?? 0;
        $totalValue = $unitCost * max(0, $remaining);

        return [
            'date' => $endDate->toDateString(),
            'item' => $p->name,
            'initial_quantity' => number_format($initial, 2),
            'purchased_quantity' => number_format($purchasedDuring, 2),
            'sold_quantity' => number_format($soldDuring, 2),
            'remaining_quantity' => number_format($remaining, 2),
            'price' => number_format($p->actual_price ?? 0, 2),
            'selling_price' => number_format($p->selling_price ?? 0, 2),
            'unit_cost_used' => number_format($unitCost, 2),
            'total_value' => number_format($totalValue, 2),
        ];
    })
    ->filter(fn ($i) => (float)str_replace(',', '', $i['remaining_quantity']) > 0)
    ->sortBy('item')
    ->values();

    return response()->json([
        'success' => true,
        'data' => $result,
        'meta' => [
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ]
        ]
    ]);
}



// public function getFuelCreditsReport(Request $request)
// {
//     $depID = $this->resolveDepartmentId();

//     $clientID = $request->clientID;
//     $start    = $request->startDate ? Carbon::parse($request->startDate)->startOfDay() : null;
//     $end      = $request->endDate ? Carbon::parse($request->endDate)->endOfDay() : null;

//     if (($start && !$end) || (!$start && $end)) {
//         return response()->json(['success' => false, 'message' => 'Both start date and end date are required']);
//     }

//     /**
//      * ===================== PAYMENTS =====================
//      */
//     $totalPayments = Credits::when($clientID, fn($q) => $q->where('customerID', $clientID))
//         ->when($depID, fn($q) => $q->where('depID', $depID))
//         ->when($start && $end, fn($q) => $q->whereBetween('date', [$start, $end]))
//         ->sum('amount');

//     /**
//      * ===================== FUEL CREDITS =====================
//      */
//     $fuelCredits = FuelCreditSale::with(['customer', 'product'])
//         ->when($clientID, fn($q) => $q->where('customer_id', $clientID))
//         ->when($depID, fn($q) => $q->where('depID', $depID))
//         ->when($start && $end, fn($q) => $q->whereBetween('date', [$start, $end]))
//         ->get();

//     /**
//      * ===================== OIL CREDITS =====================
//      */
//     $oilCredits = SalesTransactions::with(['customer', 'sales.product'])
//         ->where('balance', '>', 0)
//         ->when($clientID, fn($q) => $q->where('customerID', $clientID))
//         ->when($depID, fn($q) => $q->where('depID', $depID))
//         ->when($start && $end, fn($q) => $q->whereBetween('paid_date', [$start, $end]))
//         ->get();

//     /**
//      * ===================== TRANSFORM =====================
//      */
//     $records = collect();

//     foreach ($fuelCredits as $c) {
//         $records->push([
//             'type' => 'fuel',
//             'date' => Carbon::parse($c->date)->toDateString(),
//             'client' => $c->customer->customer_name ?? 'N/A',
//             'product' => $c->product->name ?? 'N/A',
//             'quantity' => $c->quantity,
//             'total' => $c->total,
//             'status' => $c->status,
//             'description' => $c->description ?? ''
//         ]);
//     }

//     foreach ($oilCredits as $t) {
//         foreach ($t->sales as $s) {
//             $records->push([
//                 'type' => 'oil',
//                 'date' => Carbon::parse($t->paid_date)->toDateString(),
//                 'client' => $t->customer->customer_name ?? 'N/A',
//                 'product' => $s->product->name ?? 'N/A',
//                 'quantity' => $s->quantity,
//                 'total' => ($t->balance * ($s->total_price / $t->net_price)),
//                 'status' => $t->type,
//                 'description' => ''  
//             ]);
//         }
//     }

//     if ($records->isEmpty()) {
//         return response()->json(['success' => false, 'message' => 'No credit records found']);
//     }

//     /**
//      * ===================== BALANCES =====================
//      */
//     $customerBalance = Customers::when($clientID, fn($q) => $q->where('id', $clientID))
//         ->when($depID, fn($q) => $q->where('depID', $depID))
//         ->value('balance') ?? 0;

//     $grandTotal = $records->sum('total');
//     $previousBalance = max(0, $customerBalance - $grandTotal + $totalPayments);

//     return response()->json([
//         'success' => true,
//         'data' => [
//             'transactions' => $records,
//             'grand_total' => $grandTotal,
//             'payments_made' => $totalPayments,
//             'previous_balance' => $previousBalance,
//             'customer_balance' => $customerBalance
//         ]
//     ]);
// }

public function getFuelCreditsReport(Request $request)
{
    try {
        $depID = $this->resolveDepartmentId();

        $clientID = $request->clientID;
        $fuelType = $request->fuel_type;
        $start    = $request->startDate ? Carbon::parse($request->startDate)->startOfDay() : null;
        $end      = $request->endDate ? Carbon::parse($request->endDate)->endOfDay() : null;

        if (($start && !$end) || (!$start && $end)) {
            return response()->json(['success' => false, 'message' => 'Both start date and end date are required']);
        }

        $paymentsWithinPeriod = Credits::when($clientID, fn($q) => $q->where('customerID', $clientID))
            ->when($depID, fn($q) => $q->where('depID', $depID))
            ->when($start && $end, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');

        $fuelCredits = FuelCreditSale::with(['customer', 'product'])
            ->when($clientID, fn($q) => $q->where('customer_id', $clientID))
            ->when($depID, fn($q) => $q->where('depID', $depID))
            ->when($fuelType, fn($q) => $q->where('product_id', $fuelType))
            ->when($start && $end, fn($q) => $q->whereBetween('date', [$start, $end]))
            ->get();

        $oilCredits = SalesTransactions::with(['customer', 'sales.product'])
            ->where('balance', '>', 0)
            ->when($clientID, fn($q) => $q->where('customerID', $clientID))
            ->when($depID, fn($q) => $q->where('depID', $depID))
            ->when($start && $end, fn($q) => $q->whereBetween('paid_date', [$start, $end]))
            ->when($fuelType, fn($q) => $q->whereHas('sales', fn($sq) => $sq->where('proID', $fuelType)))
            ->get();

        $records = collect();

        foreach ($fuelCredits as $c) {
            $records->push([
                'type' => 'fuel',
                'date' => Carbon::parse($c->date)->toDateString(),
                'client' => $c->customer?->customer_name ?? 'N/A',
                'product' => $c->product?->name ?? 'N/A',
                'quantity' => $c->quantity,
                'total' => $c->total,
                'status' => $c->status,
                'description' => $c->description ?? '',
            ]);
        }

        foreach ($oilCredits as $t) {
            $netPrice = (float) ($t->net_price ?? 0);

            foreach ($t->sales as $s) {
                if ($fuelType && (int) $s->proID !== (int) $fuelType) {
                    continue;
                }

                $lineTotal = $netPrice > 0
                    ? ($t->balance * ((float) $s->total_price / $netPrice))
                    : (float) ($s->total_price ?? 0);

                $records->push([
                    'type' => 'oil',
                    'date' => $t->paid_date ? Carbon::parse($t->paid_date)->toDateString() : null,
                    'client' => $t->customer?->customer_name ?? 'N/A',
                    'product' => $s->product?->name ?? 'N/A',
                    'quantity' => $s->quantity,
                    'total' => $lineTotal,
                    'status' => $t->type,
                    'description' => '',
                ]);
            }
        }

        if ($records->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No credit records found']);
        }

        $currentBalance = Customers::when($clientID, fn($q) => $q->where('id', $clientID))
            ->when($depID, fn($q) => $q->where('depID', $depID))
            ->when(!$clientID && $depID, fn($q) => $q->limit(1))
            ->value('balance') ?? 0;

        $newCreditsDuringPeriod = $records->sum('total');
        $previousBalance = max(0, $currentBalance - $newCreditsDuringPeriod + $paymentsWithinPeriod);

        $showPreviousBalanceSection = ($previousBalance > 0 && $currentBalance > 0)
            || ($previousBalance > 0 && $paymentsWithinPeriod < $previousBalance);

        if ($previousBalance > 0 && $paymentsWithinPeriod >= $previousBalance && $currentBalance == $newCreditsDuringPeriod) {
            $showPreviousBalanceSection = false;
            $balanceDue = $newCreditsDuringPeriod;
        } else {
            $balanceDue = $currentBalance;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $records->values()->all(),
                'grand_total' => $newCreditsDuringPeriod,
                'payments_made' => $paymentsWithinPeriod,
                'previous_balance' => $previousBalance,
                'customer_balance' => $currentBalance,
                'show_previous_balance_section' => $showPreviousBalanceSection,
                'balance_due' => $balanceDue,
            ],
        ]);
    } catch (\Throwable $e) {
        Log::error('Fuel credits report failed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate report. Please try again.',
        ], 500);
    }
}

    private function isAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }

    // private function resolveDepartmentId(?int $requestedDepID = null): ?int
    // {
    //     $user = auth()->user();

    //     if (!$user) {
    //         return $requestedDepID;
    //     }

    //     return $user->role === 'admin' ? $requestedDepID : $user->depID;
    // }

    // private function getAccessibleDepartments()
    // {
    //     $user = auth()->user();

    //     if ($user && $user->role === 'admin') {
    //         return Departments::get();
    //     }

    //     return Departments::where('id', $user->depID)->get();
    // }

    private function mapOilStatus($type)
    {
        $statusMap = [
            'credit' => 'pending',
            'partial' => 'partial',
            'cash' => 'paid'
        ];
        
        return $statusMap[$type] ?? 'pending';
    }



}
