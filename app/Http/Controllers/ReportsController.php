<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesTransactions;
use App\Models\Expenses;
use App\Models\Credits;            
use App\Models\Customers;            
use App\Models\Suppliers;
use App\Models\AccountPayables;
use App\Models\FinanceTrans;
use App\Models\PurchaseTransactions;
use App\Models\Assets;
use App\Models\CashAccount;
use App\Models\Inventory;
use App\Models\Capital;
use App\Models\Departments;
use App\Models\Products;
use App\Models\Purchases;
use App\Models\BankStatement;
use App\Models\FuelCreditSale;
use App\Models\FuelSale;
use App\Models\FuelSaleTransaction;
use App\Models\Sales;
use App\Models\Salesman;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportsController extends Controller
{

    public function Credits() {
        
        $departments = Departments::get();
        $customers = Customers::get();
         $users = User::where('role', 'sales')->get();
        return view('layout.reports.credits', compact('customers', 'departments', 'users'));
    }
    public function Liability() {
        if(auth()->user()->role != 'admin' && auth()->user()->role != 'manager') {
            $departments = Departments::where('id', auth()->user()->depID)->get();
             $suppliers = Suppliers::where('depID', auth()->user()->depID)->get();
        } else {
            $departments = Departments::get();
            $suppliers = Suppliers::get();
        }
        return view('layout.reports.liability', compact('suppliers', 'departments'));
    }
    public function Expense() {
        if(auth()->user()->role != 'admin' && auth()->user()->role != 'manager') {
            $departments = Departments::where('id', auth()->user()->depID)->get();
             $salesman = Salesman::where('depID', auth()->user()->depID)->get();
        } else {
            $departments = Departments::get();
            $salesman = Salesman::get();
        }
        
        return view('layout.reports.expense', compact('departments', 'salesman'));
    }
    public function BankStatement() {
        if(auth()->user()->role != 'admin' && auth()->user()->role != 'manager') {
            $departments = Departments::where('id', auth()->user()->depID)->get();
        } else {
            $departments = Departments::get();
        }
        return view('layout.reports.bankSatement', compact('departments'));
    }
    public function SalesPayment() {
        $departments = Departments::get();
        $customers = Customers::get();
        
        // Get users based on role
        $users = auth()->user()->role == 'sales' 
            ? User::where('id', auth()->id())->get()
            : User::all();
    
        return view('layout.reports.salesPayment', compact('customers', 'departments', 'users'));
    }
    public function Sales() {
        $departments = Departments::get();
        $customers = Customers::get();
        $products = Products::get();
        return view('layout.reports.sales', compact('customers', 'departments', 'products'));
    }
    public function PurchasePayment() {
        $departments = Departments::get();
        
        $suppliers = Suppliers::get();
        return view('layout.reports.purchasePayment', compact('suppliers', 'departments'));
    }
    public function Purchase() {
        $departments = Departments::get();
   
        $products = Products::get();
        return view('layout.reports.purchase', compact('products', 'departments'));
    }
    public function FinanceActivity() {
        $departments = Departments::get();
        $users = User::get();
        return view('layout.reports.FinanceAcc', compact('users', 'departments'));
    }
    public function IncomeStatement() {

              // Initialize an empty data structure for the initial page load
        $formattedIncomeStatement = [
            'SalesRevenue' => 0,
            'RegularFuelSalesRevenue' => 0,
            'FuelCreditSalesRevenue' => 0,
            'TotalFuelSalesRevenue' => 0,
            'NetRegularFuelRevenue' => 0,
            'NetFuelCreditRevenue' => 0,
            'NetFuelSalesRevenue' => 0,
            'RegularFuelDiscount' => 0,
            'FuelCreditDiscount' => 0,
            'TotalFuelDiscount' => 0,
            'totalCreditPayments' => 0,
            'netSales' => 0,
            'COGS' => 0,
            'RegularFuelCOGS' => 0,
            'FuelCreditCOGS' => 0,
            'TotalFuelCOGS' => 0,
            'gross_profit' => 0,
            'netIncome' => 0,
            'total_discount' => 0,
            'expense' => 0,
            'startDate' => '',
            'endDate' => '',
            'paymentMethodTotals' => [],
            'paymentMethodTotalsCredits' => [],
            'thirdValueSales' => 0,
            'thirdValueCredits' => 0,
            'totalRevenue' => 0,
            'regularSalesCount' => 0,
            'regularFuelSalesCount' => 0,
            'fuelCreditSalesCount' => 0,
        ];

        return view('layout.reports.incomeStatement', compact('formattedIncomeStatement'));
    }

    public function customerBalanceReport(){
        $departments = Departments::get();
        return view('layout.reports.customerBalance', compact('departments'));
    }
    

    
     public function InventoryReport(){
        
        return view('layout.reports.inventory');
    }

     public function FuelCreditReport(){
        $customers = Customers::all();
        $products = Products::all();
        return view('layout.reports.fuel_credit_sale', compact('products', 'customers'));
    }
    
    
    // Credit Transactions
    public function getCreditsReport(Request $request) {
        $clientID = $request->input('clientID');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $depID = $request->input('depID');
        $sellerID = $request->input('seller');

        // Fetch query for the Credits model
        $query = Credits::with(['customer', 'sellerUser']); // assuming sellerUser relation exists

        if ($clientID) {
            $query->where('customerID', 'like', '%' . $clientID . '%');
        }
        if ($depID) {
            $query->where('depID', $depID);
        }
        if ($sellerID) {
            $query->where('seller', $sellerID); // make sure this matches your DB column
        }

        // Handle date filtering
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate || $endDate) {
            return response()->json(['success' => false, 'message' => 'Both start date and end date are required']);
        }

        // Execute the query
        $report = $query->get();

        if ($report->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No records found']);
        }

        // Group by payment type
        $grouped = $report->groupBy('type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'transactions' => $group->map(function ($payment) {
                    return [
                        'date' => $payment->date,
                        'client' => $payment->customer->customer_name ?? 'Cash Sales',
                        'phone' => $payment->customer->phone ?? 'N/A',
                        'amount' => number_format($payment->amount, 2), 
                        'type' => $payment->type,
                        'seller' => $payment->sellerUser->name ?? 'Admin'
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


    // Purchase Payment Transactions
    public function getPurchasePaymentReport(Request $request) {
        $clientID = $request->input('clientID');
        $type = $request->input('type');
        $payMethod = $request->input('payMethod');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $depID = $request->input('depID');
    
        // Fetch query for the Payment model
        $query = PurchaseTransactions::query();
        if ($clientID) {
            $query->where('customerID', 'like', '%' . $clientID . '%');
        }
        if ($depID) {
            $query->where('depID', $depID);
        }
    
        if ($type) {
            $query->where('type', 'like', '%' . $type . '%');
        }
    
        // Handle date filtering
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate || $endDate) {
            return response()->json(['success' => false, 'message' => 'Both start date and end date are required']);
        }
    
        // Execute the query
        $report = $query->get();
    
        // Check if the query returned any records
        if ($report->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No records found']);
        }
    
        // Format the report data
        $formattedReport = $report->map(function ($payment) {
            return [
                'date' => $payment->date,
                'client' => $payment->customer->name ?? 'Cash Sales',
                'phone' => $payment->customer->phone ?? 'N/A',
                'type' => $payment->type, 
                'subTotal' => number_format($payment->subTotal, 2), 
                'discount' => number_format($payment->discount, 2), 
                'net_price' => number_format($payment->net_price, 2), 
                'add_cost' => number_format($payment->add_cost, 2), 
                'paidAmount' => number_format($payment->paidAmount, 2), 
                'balance' => number_format($payment->balance, 2), 
                'payMethod' => $payment->payMethod,
                'purchased' => $payment->purchased ? $payment->user->name : 'N/A',
            ];
        });
    
        // Return the response
        return response()->json(['success' => true, 'data' => $formattedReport]);
    }

    
    // Purchase Transactions
    public function getPurchaseReport(Request $request)
{
    $proID = $request->input('proID');
    $salesID = $request->input('salesID');
    $depID = $request->input('depID');
    $payMethod = $request->input('payMethod');
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    // ✅ Query from PurchaseTransactions instead of Purchases
    $query = PurchaseTransactions::with(['purchase.pro', 'customer', 'department', 'user']);

    if ($depID) {
        $query->where('depID', $depID);
    }

    if ($salesID) {
        $query->where('id', 'like', '%' . $salesID . '%'); // since transID = transaction id
    }

    if ($payMethod) {
        $query->where('payMethod', 'like', '%' . $payMethod . '%');
    }

    // ✅ Date filtering (by transaction date)
    if ($startDate && $endDate) {
        $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
    } elseif ($startDate || $endDate) {
        return response()->json(['success' => false, 'message' => 'Both start date and end date are required']);
    }

    $transactions = $query->latest()->get();

    if ($transactions->isEmpty()) {
        return response()->json(['success' => false, 'message' => 'No records found']);
    }

    // ✅ Format each transaction and include its purchase details
    $formattedReport = $transactions->map(function ($trans) use ($proID) {
        $purchases = $trans->purchase;

        // Filter products if proID is provided
        if ($proID) {
            $purchases = $purchases->where('proID', $proID);
        }

        $purchaseItems = $purchases->map(function ($p) {
            return [
                'item' => $p->pro->name ?? 'U/K',
                'unit' => $p->pro->unit ?? 'N/A',
                'quantity' => number_format($p->quantity),
                'unit_cost' => number_format($p->unit_cost, 2),
                'add_cost' => number_format($p->add_cost, 2),
                'total_cost' => number_format($p->total_cost, 2),
                'remaining' => number_format($p->remaining),
            ];
        });

        return [
            'transaction_id' => $trans->id,
            'supplier' => $trans->customer->name ?? 'Unknown Supplier',
            'dep' => $trans->department->name ?? 'N/A',
            'user' => $trans->user->username ?? 'N/A',
            'date' => $trans->created_at->format('Y-m-d'),
            'subtotal' => number_format($trans->subTotal, 2),
            'discount' => number_format($trans->discount, 2),
            'add_cost' => number_format($trans->add_cost, 2),
            'net_price' => number_format($trans->net_price, 2),
            'paidAmount' => number_format($trans->paidAmount, 2),
            'balance' => number_format($trans->balance, 2),
            'payMethod' => $trans->payMethod,
            'type' => $trans->type,
            'items' => $purchaseItems,
        ];
    });

    return response()->json(['success' => true, 'data' => $formattedReport]);
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


    public function getSalesReport(Request $request) {
        $proID = $request->input('proID');
        $salesID = $request->input('salesID');
        $depID = $request->input('depID');
        $payMethod = $request->input('payMethod');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $query = Sales::with(['product', 'transaction'])
            ->select('*',
                DB::raw('(quantity * price) as calculated_total')
            );

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
            $query->whereHas('transaction', function($q) use ($payMethod) {
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

        // Group by product name instead of proID and sort by product name
        $groupedProducts = $salesData->groupBy(function($item) {
            return $item->product->name ?? 'U/K';
        })->map(function ($group) {
            $firstItem = $group->first();
            $totalProfit = $group->sum(function($sale) {
                return ($sale->price - ($sale->product->actual_price ?? 0)) * $sale->quantity;
            });
            
            $totalRevenue = $group->sum('total_price');
            $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
            
            return [
                'product_name' => $firstItem->product->name ?? 'U/K',
                'unit' => $firstItem->product->unit ?? 'N/A',
                'total_quantity' => $group->sum('quantity'),
                'average_price' => $group->avg('price'),
                'average_actual_price' => $group->avg(function($sale) {
                    return $sale->product->actual_price ?? 0;
                }),
                'total_revenue' => $totalRevenue,
                'total_profit' => $totalProfit,
                'profit_margin' => number_format($profitMargin, 2) . '%',
                'sales_count' => $group->count(),
                'product_ids' => $group->pluck('proID')->unique()->values()
            ];
        })
        ->sortBy('product_name') // Sort grouped products by name in ascending order
        ->values();

        // Detailed records with profit per item, sorted by product name and date
        $detailedRecords = $salesData->sortBy(function($item) {
            return [$item->product->name ?? 'U/K', $item->created_at];
        })->map(function ($sale) {
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

        // Calculate grand totals
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
    public function getCustomerBalanceReport(Request $request) {
        $name = $request->input('name');
        $depID = $request->input('depID');

        $query = Customers::query();

        if ($name) {
            $query->where('customer_name', 'like', '%' . $name . '%');
        }
        if ($depID) {
            $query->where('depID', $depID);
        }

        // Only customers with balance > 0
        $query->where('balance', '>', 0);

        // 👉 Order by balance (highest first)
        $query->orderBy('balance', 'desc');

        $report = $query->get();

        if ($report->isNotEmpty()) {
            $formattedReport = $report->map(function ($student) {
                return [
                    'serial' => $student->serial,
                    'name' => $student->customer_name,
                    'phone' => $student->phone,
                    'address' => $student->address,
                    'balance' => $student->balance,
                ];
            });

            return response()->json($formattedReport);
        } else {
            return response()->json(['error' => 'No records found'], 404);
        }
    }



    // Liability Transactions
    public function getLiabilityReport(Request $request) {
        $supplier = $request->input('supplier');
        $type = $request->input('type');
        $depID = $request->input('depID');
        $trnsType = $request->input('trnsType');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
    
        // Fetch query for the Payment model
        $query = AccountPayables::query();
        if ($supplier) {
            $query->where('received_from', 'like', '%' . $supplier . '%');
        }
        if ($depID) {
            $query->where('depID', $depID);
        }
        if ($type) {
            $query->where('type', 'like', '%' . $type . '%');
        }
        if ($trnsType) {
            $query->where('transaction_type', 'like', '%' . $trnsType . '%');
        }
    
        // Handle date filtering
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate || $endDate) {
            return response()->json(['success' => false, 'message' => 'Both start date and end date are required']);
        }
    
        // Execute the query
        $report = $query->get();
    
        // Check if the query returned any records
        if ($report->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No records found']);
        }
    
        // Format the report data
        $formattedReport = $report->map(function ($payment) {
            return [
                'date' => $payment->date,
                'client' => $payment->received_from,
                'amount' => number_format($payment->amount, 2), 
                'type' => $payment->type,
                'trnsType' => $payment->transaction_type, 
                'info' => $payment->description, 
            ];
        });
    
        // Return the response
        return response()->json(['success' => true, 'data' => $formattedReport]);
    }


    // Expense Transactions
   public function getExpenseReport(Request $request)
{
    $user = auth()->user();

    $type = $request->input('type');
    $salesman_id = $request->input('salesman_id');
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    // Base query
    $query = Expenses::with('salesman');

    /**
     * ===================== ROLE + DEPARTMENT FILTER =====================
     * Sales → only their department
     * Admin/Manager/Acc → all departments
     */
    if ($user->role === 'sales') {
        $query->where('depID', $user->depID);
    }

    /**
     * ===================== OPTIONAL FILTERS =====================
     */
    if ($type) {
        $query->where('type', 'like', "%{$type}%");
    }

    // Prevent sales from filtering other salesmen
    if ($salesman_id) {
        if ($user->role === 'sales') {
            $query->where('salesman_id', $user->id);
        } else {
            $query->where('salesman_id', $salesman_id);
        }
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
    public function getFinanceAccReport(Request $request) {
        $userID = $request->input('userID');
        $depID = $request->input('depID');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
    
        // Fetch query for the Payment model
        $query = FinanceTrans::query();
        if ($userID) {
            $query->where('user', 'like', '%' . $userID . '%');
        }
        if ($depID) {
            $query->where('depID', $depID);
        }
    
        // Handle date filtering
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate || $endDate) {
            return response()->json(['success' => false, 'message' => 'Both start date and end date are required']);
        }
    
        // Execute the query
        $report = $query->get();
    
        // Check if the query returned any records
        if ($report->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No records found']);
        }
    
        // Format the report data
        $formattedReport = $report->map(function ($activity) {
            return [
                'date' => $activity->date,
                'depitAcc' => $activity->depitAcc,
                'depitAmount' => number_format($activity->depitAmount, 2), 
                'creditAcc' => $activity->creditAcc,
                'creditAmount' => number_format($activity->creditAmount, 2), 
                'formType' => $activity->formType,
                'action' => $activity->action,
                'user' => $activity->users->name,
                'info' => $activity->info, 
            ];
        });
    
        // Return the response
        return response()->json(['success' => true, 'data' => $formattedReport]);
    }


    // bank Statement Transactions
public function getBankSatementReport(Request $request) {
    $type = $request->input('type');
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');
    $depID = $request->input('depID');

    // Validate date inputs
    if (($startDate && !$endDate) || (!$startDate && $endDate)) {
        return response()->json(['success' => false, 'message' => 'Both start date and end date are required']);
    }

    // Calculate beginning balance (all transactions before start date)
    $beginningBalance = 0;
    if ($startDate) {
        $beginningQuery = BankStatement::query();
        
        if ($type) {
            $beginningQuery->where('type', 'like', '%' . $type . '%');
        }
        if ($depID) {
            $beginningQuery->where('depID', $depID);
        }
        
        // Get all transactions before the start date
        $beginningTransactions = $beginningQuery->where('date', '<', $startDate)->get();
        
        foreach ($beginningTransactions as $transaction) {
            // REVERSED LOGIC: Debit increases balance, Credit decreases balance
            if ($transaction->type == 'Debit') {
                $beginningBalance += $transaction->amount;
            } else {
                $beginningBalance -= $transaction->amount;
            }
        }
    }

    // Fetch query for the BankStatement model for the date range
    $query = BankStatement::query();
    if ($type) {
        $query->where('type', 'like', '%' . $type . '%');
    }
    if ($depID) {
        $query->where('depID', $depID);
    }

    // Handle date filtering
    if ($startDate && $endDate) {
        $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Execute the query and order by date
    $report = $query->orderBy('date')->get();

    // Check if the query returned any records
    if ($report->isEmpty()) {
        return response()->json([
            'success' => false, 
            'message' => 'No records found',
            'opening_balance' => number_format($beginningBalance, 2)
        ]);
    }

    // Calculate running balance starting from beginning balance
    $balance = $beginningBalance;
    $formattedReport = [];
    
    foreach ($report as $statement) {
        // Use the row ID as reference instead of extracting from description
        $ref = $statement->id;
        
        // REVERSED LOGIC: Debit increases balance, Credit decreases balance
        if ($statement->type == 'Debit') {
            $balance += $statement->amount;
        } else {
            $balance -= $statement->amount;
        }
        
        $formattedReport[] = [
            'date' => $statement->date,
            'ref' => $ref, // Using the row ID as reference
            'branch' => $statement->branch ?? '001',
            'particulars' => $statement->description,
            'cheque_no' => $statement->check_no,
            'withdrawal' => $statement->type == 'Credit' ? number_format($statement->amount, 2) : '0.00', // REVERSED
            'deposit' => $statement->type == 'Debit' ? number_format($statement->amount, 2) : '0.00',    // REVERSED
            'balance' => number_format($balance, 2),
            'type' => $statement->type,
            'department' => $statement->department->name ?? '',
        ];
    }
    
    // Return the response with calculated opening balance
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
            $endDate = $request->input('endDate');
    
            // Helper function for date validation
            if (($startDate && !$endDate) || (!$startDate && $endDate)) {
                return response()->json(['error' => 'Both start date and end date are required.'], 422);
            }
    
            // Define payment methods
            $paymentMethods = ['Zaad Service', 'Cash On Hand', 'Credit on Book', 'Premier Wallet', 'E-Dahab', 'MERCHANT'];
    
            // Initialize base queries
            $salesQuery = Sales::with('product');
            $salesTransactionsQuery = SalesTransactions::query();
            $creditPaymentsQuery = Credits::query();
            $regularFuelSalesQuery = FuelSale::with(['transactions.product', 'creditSales.product']);
            $expenseQuery = Expenses::query();
            $fuelCreditSalesQuery = FuelCreditSale::with('product');
    
            // Conditionally apply date filters to the queries
            if ($startDate && $endDate) {
                $salesQuery->whereBetween('created_at', [$startDate, $endDate]);
                $salesTransactionsQuery->whereBetween('paid_date', [$startDate, $endDate]);
                $creditPaymentsQuery->whereBetween('date', [$startDate, $endDate]);
                $regularFuelSalesQuery->whereBetween('date', [$startDate, $endDate]);
                $fuelCreditSalesQuery->whereBetween('date', [$startDate, $endDate]);
                $expenseQuery->whereBetween('date', [$startDate, $endDate]);
            }
    
            // Execute queries and get results
            $sales = $salesQuery->get();
            $salesTransactions = $salesTransactionsQuery->get();
            $creditPayments = $creditPaymentsQuery->get();
            $regularFuelSales = $regularFuelSalesQuery->get();
            $fuelCreditSales = $fuelCreditSalesQuery->get();
            $expenseData = $expenseQuery->get();
            
            // Check if any data exists
            if ($sales->isEmpty() && $expenseData->isEmpty() && $regularFuelSales->isEmpty() && $fuelCreditSales->isEmpty() && $startDate && $endDate) {
                return response()->json(['error' => 'No records found for the selected date range.'], 404);
            }
    
            // Payment method totals
            $paymentMethodTotalsSales = [];
            foreach ($paymentMethods as $method) {
                $paymentMethodTotalsSales[$method] = $salesTransactions->where('payment_method', $method)->sum('net_price');
            }
    
            $paymentMethodTotalsCredits = [];
            foreach ($paymentMethods as $method) {
                $paymentMethodTotalsCredits[$method] = $creditPayments->where('payment_method', $method)->sum('amount');
            }
    
            // Revenue calculations
            $totalSalesRevenue = $salesTransactions->sum('net_price');
            $totalDiscount = $salesTransactions->sum('discount');
            $totalCreditPayments = $creditPayments->sum('amount');
            $netSalesRevenue = $totalSalesRevenue - $totalDiscount;
    
            // Fuel revenue calculations
            $totalRegularFuelRevenue = 0;
            $regularFuelDiscount = 0;
            foreach ($regularFuelSales as $fuelSale) {
                foreach ($fuelSale->transactions as $transaction) {
                    $totalRegularFuelRevenue += $transaction->total;
                }
                $regularFuelDiscount += $fuelSale->discount;
            }
    
            $totalCreditFuelRevenue = $fuelCreditSales->sum('total');
            $netRegularFuelRevenue = $totalRegularFuelRevenue - $regularFuelDiscount;
            $netCreditFuelRevenue = $totalCreditFuelRevenue; // Credit sales typically don't get discount at sale time
    
            $totalFuelSalesRevenue = $totalRegularFuelRevenue + $totalCreditFuelRevenue;
            $totalFuelDiscount = $regularFuelDiscount;
            $netFuelSalesRevenue = $netRegularFuelRevenue + $netCreditFuelRevenue;
    
            // Total revenue
            $totalRevenue = $netSalesRevenue + $netFuelSalesRevenue;
    
            // CORRECT COGS CALCULATION (as you provided)
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
                        $costPrice = $transaction->product->cost_price ?? $transaction->product->actual_price;
                        $totalRegularFuelCOGS += $costPrice * $transaction->liters;
                    }
                }
            }
    
            $totalFuelCreditCOGS = 0;
            foreach ($fuelCreditSales as $fuelSale) {
                if ($fuelSale->product) {
                    $costPrice = $fuelSale->product->cost_price ?? $fuelSale->product->actual_price;
                    $totalFuelCreditCOGS += $costPrice * $fuelSale->quantity;
                }
            }
    
            $totalFuelCOGS = $totalRegularFuelCOGS + $totalFuelCreditCOGS;
            $totalCOGS += $totalFuelCOGS;
    
            // Expenses and profit calculations
            $totalExpenses = $expenseData->sum('amount');
            $grossProfit = $totalRevenue - $totalCOGS;
            $netIncome = $grossProfit - $totalExpenses;
    
            // Populate the formattedIncomeStatement array
            $formattedIncomeStatement = [
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
            ];
    
            Log::info('Income Statement Data:', $formattedIncomeStatement);
    
            return response()->json(['data' => $formattedIncomeStatement]);
    
        } catch (\Throwable $e) {
            Log::error('Income Statement Report Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json(['error' => 'Unexpected error while generating report.'], 500);
        }
    }
    

    
    // Report Of System Balance Sheet
    public function getBalanceSheet(Request $request) {

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');


        $accounts = CashAccount::get();
        $clients = Customers::get();
        $assets = Assets::get();
        // Filtered queries with date range
        $Expense = Expenses::whereBetween('date', [$startDate, $endDate])->sum('amount');
        $CashPayment = SalesTransactions::whereBetween('paid_date', [$startDate, $endDate])->sum('net_price');
        $sales = Sales::whereBetween('created_at', [$startDate, $endDate])->get();
        // $liability = Recliability::get();

        // Cash accounts
        $depitCash = $accounts->where('AccCode', 'Cash')->sum('debit');
        $CreditCash = $accounts->where('AccCode', 'Cash')->sum('credit');
        $totalCash = $depitCash - $CreditCash;

        // Bank accounts
        $depitBank = $accounts->where('AccCode', 'Bank')->sum('debit');
        $CreditBank = $accounts->where('AccCode', 'Bank')->sum('credit');
        $totalBank = $depitBank - $CreditBank;

        // Inventory accounts
        $depitInventory = $accounts->where('AccCode', 'Inventory')->sum('debit');
        $CreditInventory = $accounts->where('AccCode', 'Inventory')->sum('credit');
        $totalInventory = $depitInventory - $CreditInventory;

        // Inventory accounts
        $depitasset = $accounts->where('AccCode', 'Fixed')->sum('debit');
        $Creditasset = $accounts->where('AccCode', 'Fixed')->sum('credit');
        $fixedAsset = $depitasset - $Creditasset;
        
        // account Receivable
        $receivable = $clients->sum('balance');

        $totalAssets = $totalCash + $receivable + $fixedAsset + $totalInventory + $totalBank;

        // Long Term Liability  
        $depitliability = $accounts->where('AccCode', 'Long Term')->sum('debit');
        $Creditliability = $accounts->where('AccCode', 'long Term')->sum('credit');
        $longTerm =  $depitliability - $Creditliability;
        
        // Short Term Liability
        $depitshort = $accounts->where('AccCode', 'Short Term')->sum('debit');
        $Creditshort = $accounts->where('AccCode', 'Short Term')->sum('credit');
        $shortTerm =  $Creditshort - $depitshort;

        $totalLiabilities = $longTerm + $shortTerm;



        // Owner's Equity
        $capital = $accounts->where('AccCode', 'Capital')->sum('credit');
        $totalrevenue = $CashPayment;
        // / Calculate Total Cost Of Goods Sold (COGS)
            $totalCOGS = 0;
            foreach ($sales as $sale) {
                $product = $sale->product; // Get the related product
                if ($product) {
                    $totalCOGS += $product->actual_price * $sale->quantity;
                }
            }
        $totalexpense = $Expense + $totalCOGS;
        $netIncome = $totalrevenue - $totalexpense;
        $ownerEquity = $capital + $netIncome;

        // Balncesheet Totals
        $Assets = $totalAssets; 
        $capitalLiability = $ownerEquity + $totalLiabilities; 

        // Prepare data for the view
        $balanceSheetData = [
            'totalCash' => $totalCash,
            'capital' => $capital,
            'retained' => $netIncome,
            'receivable' => $receivable,
            'Bank' => $totalBank,
            'Inventory' => $totalInventory,
            'fixedAsset' => $fixedAsset,
            'totalAssets' => $totalAssets,
            'longTerm' => $longTerm,
            'shortTerm' => $shortTerm,
            'totalLiabilities' => $totalLiabilities,
            'TotalEquity' => $ownerEquity,
            'Assets' => $Assets,
            'capitalLiability' => $capitalLiability,
        ];

        // Pass data to the view
        return view('layout.reports.balancesheet', compact('balanceSheetData'));
    }

    public function getMedicationReport(Request $request)
    {
        $query = MedicationLog::with(['medication', 'user', 'customer']);
    
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }
    
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('taken_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }
    
        $logs = $query->orderBy('taken_at', 'desc')->get();
    
        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }



    public function getInventoryReport(Request $request) {
        // Validate
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
    
        // Products that existed on or before endDate
        $products = Products::where('created_at', '<=', $endDate->toDateTimeString())->get();
    
        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No products found for the selected period.'
            ]);
        }
    
        // Group by product name
        $groups = $products->groupBy('name');
    
        // All product ids we're interested in (used for aggregated queries)
        $allProductIds = $products->pluck('id')->unique()->values()->all();
    
        // --- determine purchases quantity column (some schemas use different names) ---
        $purchaseQtyCol = null;
        if (Schema::hasTable('purchases')) {
            foreach (['quantity', 'qty', 'received', 'received_qty'] as $c) {
                if (Schema::hasColumn('purchases', $c)) {
                    $purchaseQtyCol = $c;
                    break;
                }
            }
        }
    
        // --- Pre-aggregate sales and purchases to avoid N+1 queries ---
        // Sales up to start (exclusive)
        $salesUpToStart = Sales::whereIn('proID', $allProductIds)
            ->whereHas('transaction', function ($q) use ($startDate) {
                $q->where('created_at', '<', $startDate->toDateTimeString());
            })
            ->select('proID', DB::raw('SUM(quantity) as qty'))
            ->groupBy('proID')
            ->pluck('qty', 'proID')
            ->toArray();
    
        // Sales during period (inclusive)
        $salesDuring = Sales::whereIn('proID', $allProductIds)
            ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [
                    $startDate->toDateTimeString(),
                    $endDate->toDateTimeString()
                ]);
            })
            ->select('proID', DB::raw('SUM(quantity) as qty'))
            ->groupBy('proID')
            ->pluck('qty', 'proID')
            ->toArray();
    
        // Purchases up to start (if we detect a purchases qty column)
        $purchasesUpToStart = [];
        $purchasesDuring = [];
        if ($purchaseQtyCol) {
            $purchasesUpToStart = Purchases::whereIn('proID', $allProductIds)
                ->where('created_at', '<', $startDate->toDateTimeString())
                ->select('proID', DB::raw("SUM($purchaseQtyCol) as qty"))
                ->groupBy('proID')
                ->pluck('qty', 'proID')
                ->toArray();
    
            $purchasesDuring = Purchases::whereIn('proID', $allProductIds)
                ->whereBetween('created_at', [
                    $startDate->toDateTimeString(),
                    $endDate->toDateTimeString()
                ])
                ->select('proID', DB::raw("SUM($purchaseQtyCol) as qty"))
                ->groupBy('proID')
                ->pluck('qty', 'proID')
                ->toArray();
        } else {
            // If we cannot find a purchases quantity column, we'll warn (logged) and treat purchases as 0.
            \Log::warning('getInventoryReport: purchases quantity column not found; purchases treated as 0 for calculations.');
        }
    
        // Helper to sum aggregated maps by a list of ids
        $sumMapForIds = function(array $map, array $ids) {
            $s = 0;
            foreach ($ids as $id) {
                if (isset($map[$id])) $s += floatval($map[$id]);
            }
            return $s;
        };
    
        $result = $groups->map(function ($group) use (
            $startDate, $endDate, $sumMapForIds,
            $salesUpToStart, $salesDuring, $purchasesUpToStart, $purchasesDuring, $purchaseQtyCol
        ) {
            $productIds = $group->pluck('id')->all();
            $representative = $group->first();
    
            // Aggregates
            $soldBefore = $sumMapForIds($salesUpToStart, $productIds);
            $soldDuring = $sumMapForIds($salesDuring, $productIds);
    
            $purchasedBefore = $purchaseQtyCol ? $sumMapForIds($purchasesUpToStart, $productIds) : 0;
            $purchasedDuring = $purchaseQtyCol ? $sumMapForIds($purchasesDuring, $productIds) : 0;
    
            // initial = purchases before - sales before
            $initialQuantity = $purchasedBefore - $soldBefore;
    
            // remaining = initial + purchases during - sold during
            $remainingQuantity = $initialQuantity + $purchasedDuring - $soldDuring;
    
            // If remaining is negative, keep it (but log) — alternatively clamp to 0
            if ($remainingQuantity < 0) {
                \Log::warning("Negative remaining inventory for '{$representative->name}' calculated: {$remainingQuantity}. Check data integrity.");
            }
    
            // Estimate unit cost: average of purchase costs for these product ids, fallback to product actual_price
            $avgUnitCost = null;
            if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'unit_cost')) {
                $avgUnitCost = Purchases::whereIn('proID', $productIds)
                    ->whereNotNull('unit_cost')
                    ->select(DB::raw('AVG(unit_cost + COALESCE(add_cost,0)) as avg_cost'))
                    ->value('avg_cost');
            }
            $unitCostToUse = $avgUnitCost ? floatval($avgUnitCost) : floatval($representative->actual_price ?? 0);
    
            $totalValue = $unitCostToUse * max(0, $remainingQuantity);
    
            return [
                'date' => $endDate->toDateString(),
                'item' => $representative->name ?? 'Unknown',
                'initial_quantity' => number_format($initialQuantity, 2),
                'purchased_quantity' => number_format($purchasedDuring, 2),
                'sold_quantity' => number_format($soldDuring, 2),
                'remaining_quantity' => number_format($remainingQuantity, 2),
                'price' => number_format($representative->actual_price ?? 0, 2),
                'selling_price' => number_format($representative->selling_price ?? 0, 2),
                'unit_cost_used' => number_format($unitCostToUse, 2),
                'total_value' => number_format($totalValue, 2),
            ];
        })
        // keep only items with remaining > 0 (or change this if you want all items)
        ->filter(function ($item) {
            return (float)str_replace(',', '', $item['remaining_quantity']) > 0;
        })
        ->sortBy('item')
        ->values();
    
        return response()->json([
            'success' => true,
            'data' => $result,
            'meta' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                    'description' => $startDate->eq($endDate)
                        ? 'Inventory snapshot on ' . $endDate->toDateString()
                        : 'Inventory changes between ' . $startDate->toDateString() . ' and ' . $endDate->toDateString()
                ]
            ]
        ]);
    }


 public function getFuelCreditsReport(Request $request) {
    $clientID = $request->input('clientID');
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');
    $productType = $request->input('product_type');
    $status = $request->input('status');
    
    // Validate date range
    if (($startDate && !$endDate) || (!$startDate && $endDate)) {
        return response()->json(['success' => false, 'message' => 'Both start date and end date are required']);
    }

    // Fetch total payments made by customer for this period
    $totalPaymentsRaw = 0;
    if ($clientID && $startDate && $endDate) {
        $payments = Credits::where('customerID', $clientID)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
        
        $totalPaymentsRaw = (float) $payments;
    }

    // Fetch Fuel Credits
    $fuelQuery = FuelCreditSale::with(['customer', 'product'])
        ->when($clientID, function($q) use ($clientID) {
            $q->where('customer_id', $clientID);
        })
        ->when($productType, function($q) use ($productType) {
            $q->whereHas('product', function($q) use ($productType) {
                $q->where('name', 'LIKE', '%' . $productType . '%');
            });
        })
        ->when($status, function($q) use ($status) {
            $q->where('status', $status);
        })
        ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate]);
        });

    $fuelCredits = $fuelQuery->get();

    // Fetch Oil Sales Credits
    $oilQuery = SalesTransactions::with(['customer', 'sales.product'])
        ->where('balance', '>', 0)
        ->when($clientID, function($q) use ($clientID) {
            $q->where('customerID', $clientID);
        })
        ->when($status, function($q) use ($status) {
            $statusMap = [
                'pending' => 'credit',
                'partial' => 'partial',
                'paid' => 'cash'
            ];
            if (isset($statusMap[$status])) {
                $q->where('type', $statusMap[$status]);
            }
        })
        ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            $q->whereBetween('paid_date', [$startDate, $endDate]);
        });

    $oilCredits = $oilQuery->get();

    // Transform data (same as before)
    $fuelData = $fuelCredits->map(function ($credit) {
        return [
            'type' => 'fuel',
            'id' => $credit->id,
            'date' => $credit->date->format('Y-m-d'),
            'invoice_number' => $credit->invoice_number ?? 'N/A',
            'client' => $credit->customer->customer_name ?? 'N/A',
            'phone' => $credit->customer->phone ?? 'N/A',
            'product' => $credit->product->name ?? 'N/A',
            'quantity' => $credit->quantity . ' L',
            'quantity_raw' => (float) $credit->quantity, // Raw quantity for calculations
            'rate' => number_format($credit->rate, 2),
            'description' => $credit->description,
            'total' => (float) $credit->total,
            'status' => $credit->status,
            'payment_method' => $credit->payment_method ?? 'N/A'
        ];
    });

    $oilData = $oilCredits->flatMap(function ($transaction) {
        return $transaction->sales->map(function ($sale) use ($transaction) {
            $itemBalance = $transaction->balance * ($sale->total_price / $transaction->net_price);
            $paidDate = \Carbon\Carbon::parse($transaction->paid_date)->format('Y-m-d');
            
            return [
                'type' => 'oil',
                'id' => $transaction->id . '-' . $sale->id,
                'date' => $paidDate,
                'invoice_number' => $transaction->id,
                'client' => $transaction->customer->customer_name ?? 'N/A',
                'phone' => $transaction->customer->phone ?? 'N/A',
                'product' => $sale->product->name ?? 'N/A',
                'quantity' => $sale->quantity . ' ' . ($sale->unit ?? 'units'),
                'quantity_raw' => (float) $sale->quantity, // Raw quantity for calculations
                'rate' => number_format($sale->price, 2),
                'description' => $transaction->note,
                'total' => (float) $itemBalance,
                'status' => $this->mapOilStatus($transaction->type),
                'payment_method' => $transaction->payment_method ?? 'N/A'
            ];
        });
    });

    // Combine all data
    $allCredits = collect($fuelData->merge($oilData));

    if ($allCredits->isEmpty()) {
        return response()->json(['success' => false, 'message' => 'No credit records found']);
    }

    // Get current customer balance
    $customerBalance = 0;
    if ($clientID) {
        $customer = Customers::find($clientID);
        $customerBalance = $customer ? (float) $customer->balance : 0;
    }

    // Calculate grand total of current period credit transactions
    $grandTotal = $allCredits->sum('total');
    
    // Calculate total quantity
    $totalQuantity = $allCredits->sum('quantity_raw');

    // CORRECTED LOGIC: Calculate previous balance and adjust paid amount
    $previousBalance = $customerBalance - $grandTotal + $totalPaymentsRaw;
    
    // NEW: Adjust paid amount to show only the portion that exceeds previous balance
    $adjustedPaidAmount = $totalPaymentsRaw;
    
    // If payment covers previous balance, adjust both values
    if ($totalPaymentsRaw >= $previousBalance && $previousBalance > 0) {
        $adjustedPaidAmount = $totalPaymentsRaw - $previousBalance;
        $previousBalance = 0;
    }
    
    $totalInvoice = $previousBalance + $grandTotal;
    
    // Group by status
    $grouped = $allCredits->groupBy('status')->map(function ($group, $status) {
        return [
            'count' => $group->count(),
            'total_amount' => $group->sum('total'),
            'total_quantity' => $group->sum('quantity_raw'),
            'transactions' => $group->map(function ($item) {
                return array_merge($item, [
                    'total_formatted' => number_format($item['total'], 2)
                ]);
            })->values()
        ];
    });

    // Calculate totals
    $totalTransactions = $allCredits->count();

    return response()->json([
        'success' => true,
        'data' => [
            'grouped_by_status' => $grouped,
            'total_transactions' => $totalTransactions,
            'grand_total' => $grandTotal,
            'total_quantity' => $totalQuantity,
            'totalInvoice' => $totalInvoice,
            'customer_balance' => $customerBalance,
            'previous_balance' => number_format($previousBalance, 2),
            'total_payments_made' => number_format($adjustedPaidAmount, 2), // ADJUSTED: Shows only excess payment
            'total_payments_made_raw' => $adjustedPaidAmount, // ADJUSTED: Raw adjusted amount
            'original_payments_raw' => $totalPaymentsRaw, // NEW: Keep original for reference
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary' => [
                'fuel_credits_count' => $fuelData->count(),
                'fuel_credits_total' => $fuelData->sum('total'),
                'fuel_credits_quantity' => $fuelData->sum('quantity_raw'),
                'oil_credits_count' => $oilData->count(),
                'oil_credits_total' => $oilData->sum('total'),
                'oil_credits_quantity' => $oilData->sum('quantity_raw'),
                'payments_made' => number_format($adjustedPaidAmount, 2) // ADJUSTED
            ]
        ]
    ]);
}


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
