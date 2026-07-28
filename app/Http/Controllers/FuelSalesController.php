<?php

namespace App\Http\Controllers;

use App\Models\FuelSale;
use App\Models\FuelSaleTransaction;
use App\Models\FuelCreditSale;
use App\Models\Products;
use App\Models\Purchases;
use App\Models\Customers;
use App\Models\FinanceTrans;
use App\Models\FuelSalePayment;
use App\Models\Expenses;
use App\Models\CashAccount;
use App\Models\Sales;
use App\Models\SalesTransactions;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FuelSalesController extends Controller
{
    /**
     * Display the fuel sales form.
     */
    public function create()
    {
        $user = auth()->user();
        $isPrivileged = in_array($user->role, ['admin', 'manager']);

        $products = Products::when(!$isPrivileged, fn($q) => $q->where('depID', $user->depID))->get();

        $salesmen = Salesman::where('type', 'Salesman')
            ->when(!$isPrivileged, fn($q) => $q->where('depID', $user->depID))
            ->get();

        $customers = Customers::all();

        return view('layout.salesOrders.fuel_sales', compact('products', 'salesmen', 'customers'));
    }
    
    public function store(Request $request)
    {
        if (Auth::user()->role === 'acc') {
            return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
        }
        
        // Decode JSON strings if they come as strings
        $data = $request->all();
        
        if (isset($data['transactions']) && is_string($data['transactions'])) {
            $data['transactions'] = json_decode($data['transactions'], true);
        }
        
        if (isset($data['credit_transactions']) && is_string($data['credit_transactions'])) {
            $data['credit_transactions'] = json_decode($data['credit_transactions'], true);
        }
        
        if (isset($data['payment_data']) && is_string($data['payment_data'])) {
            $data['payment_data'] = json_decode($data['payment_data'], true);
        }
        
        $validator = Validator::make($data, [
            'date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'salesman_id' => 'required|exists:salesman,id',
            'discount' => 'nullable|numeric|min:0',
            'net_total' => 'required|numeric',
            'cash_on_hand' => 'required|numeric',
            'balance' => 'required|numeric',
            'total_petrol_liters' => 'required|numeric|min:0',
            'total_diesel_liters' => 'required|numeric|min:0',
            'transactions' => 'required|array|min:1',
            'credit_transactions' => 'nullable|array',
            'payment_data' => 'required|array',
            'payment_data.zaad_dollar' => 'required|numeric|min:0',
            'payment_data.zaad_slsh' => 'required|numeric|min:0',
            'payment_data.edahab_dollar' => 'required|numeric|min:0',
            'payment_data.edahab_slsh' => 'required|numeric|min:0',
            'payment_data.cash_dollar' => 'required|numeric|min:0',
            'payment_data.cash_slsh' => 'required|numeric|min:0',
            'payment_data.merchant_dollar' => 'required|numeric|min:0',
            'payment_data.merchant_slsh' => 'required|numeric|min:0',
            'payment_data.payment_rate' => 'required|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $existingSale = FuelSale::where('date', $request->date)
            ->where('salesman_id', $request->salesman_id)
            ->where('shift', $request->shift)
            ->first();
        
        if ($existingSale) {
            return response()->json([
                'success' => false,
                'message' => 'A sale for this date, shift, and salesman already exists!',
            ], 409);
        }
        
        DB::beginTransaction();
        try {
            $fuelSale = FuelSale::create([
                'date' => $request->date,
                'shift' => $request->shift,
                'salesman_id' => $request->salesman_id,
                'depID' => auth()->user()->depID,
                'total_petrol_liters' => $request->total_petrol_liters,
                'total_diesel_liters' => $request->total_diesel_liters,
                'discount' => $request->discount ?? 0,
                'net_total' => $request->net_total,
                'cash_on_hand' => $request->cash_on_hand,
                'balance' => $request->balance,
                'created_by' => Auth::id(),
            ]);
        
            $fuelSaleId = $fuelSale->id;
        
            // Process payment data
            FuelSalePayment::create([
                'fuel_sale_id' => $fuelSaleId,
                'depID' => auth()->user()->depID,
                'zaad_dollar' => $data['payment_data']['zaad_dollar'],
                'zaad_slsh' => $data['payment_data']['zaad_slsh'],
                'edahab_dollar' => $data['payment_data']['edahab_dollar'],
                'edahab_slsh' => $data['payment_data']['edahab_slsh'],
                'cash_dollar' => $data['payment_data']['cash_dollar'],
                'cash_slsh' => $data['payment_data']['cash_slsh'],
                'merchant_dollar' => $data['payment_data']['merchant_dollar'],
                'merchant_slsh' => $data['payment_data']['merchant_slsh'],
                'payment_rate' => $data['payment_data']['payment_rate'],
            ]);
        
            // Process cash transactions (already adjusted for credit)
            foreach ($data['transactions'] as $transaction) {
                FuelSaleTransaction::create([
                    'fuel_sale_id' => $fuelSaleId,
                    'depID' => auth()->user()->depID,
                    'dphase' => $transaction['dphase'],
                    'product_id' => $transaction['productId'],
                    'previous_reading' => $transaction['preading'],
                    'current_reading' => $transaction['creading'],
                    'liters' => $transaction['liters'],
                    'rate' => $transaction['rate'],
                    'total' => $transaction['total'],
                ]);
                
                // Decrement product stock for cash sales
                $product = Products::find($transaction['productId']);
                if ($product) {
                    $product->decrement('quantity', $transaction['liters']);
                    
                    // FIFO decrement for cash sales
                    $remainingLiters = $transaction['liters'];
                    $purchases = Purchases::where('proID', $transaction['productId'])
                                        ->where('remaining', '>', 0)
                                        ->orderBy('id', 'asc')
                                        ->get();
                    
                    foreach ($purchases as $purchase) {
                        if ($remainingLiters <= 0) break;
                        
                        if ($purchase->remaining >= $remainingLiters) {
                            $purchase->decrement('remaining', $remainingLiters);
                            $remainingLiters = 0;
                        } else {
                            $remainingLiters -= $purchase->remaining;
                            $purchase->decrement('remaining', $purchase->remaining);
                        }
                    }
                }
            }
        
            // Process credit transactions
            if (!empty($data['credit_transactions'])) {
                foreach ($data['credit_transactions'] as $credit) {
                    // Check if customerId exists (it might be customerID in your data)
                    $customerId = $credit['customerId'] ?? $credit['customerID'] ?? null;
                    
                    if (!$customerId) {
                        throw new \Exception('Customer ID is missing in credit transaction');
                    }
                    
                    FuelCreditSale::create([
                        'fuel_sale_id' => $fuelSaleId,
                        'depID' => auth()->user()->depID,
                        'customer_id' => $customerId,
                        'product_id' => $credit['productId'],
                        'quantity' => $credit['quantity'],
                        'rate' => $credit['rate'],
                        'total' => $credit['total'],
                        'description' => $credit['description'] ?? null,
                        'status' => 'pending',
                        'date' => $request->date,
                    ]);
        
                    // Decrement product stock for credit sales
                    $product = Products::find($credit['productId']);
                    if ($product) {
                        $product->decrement('quantity', $credit['quantity']);
                        
                        // FIFO decrement for credit sales
                        $remainingLiters = $credit['quantity'];
                       $purchases = Purchases::where('proID', $transaction['productId'])
                        ->where('depID', auth()->user()->depID) // ✅
                        ->where('remaining', '>', 0)
                        ->orderBy('id', 'asc')
                        ->get();

                        
                        foreach ($purchases as $purchase) {
                            if ($remainingLiters <= 0) break;
                            
                            if ($purchase->remaining >= $remainingLiters) {
                                $purchase->decrement('remaining', $remainingLiters);
                                $remainingLiters = 0;
                            } else {
                                $remainingLiters -= $purchase->remaining;
                                $purchase->decrement('remaining', $purchase->remaining);
                            }
                        }
                    }
        
                    // Update customer balance
                    $customer = Customers::find($customerId);
                    if ($customer) {
                        $customer->increment('balance', $credit['total']);
                    }
                }
            }
        
            // Update Salesman balance
            if ($request->balance > 0) {
                $salesman = Salesman::find($request->salesman_id);
                if ($salesman) {
                    $salesman->increment('balance', $request->balance);
                }
            }
        
            DB::commit();
        
            return response()->json([
                'success' => true,
                'message' => 'Fuel sales recorded successfully!',
                'sale_id' => $fuelSaleId,
                'redirect' => route('fuel.sales.index') // Add your redirect route here
            ]);
        
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fuel Sales Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }



    // Method to handle non-fuel credit sales using SalesTransactions approach
    private function createNonFuelCreditSale($creditTransaction, $request)
    {
        $User = Auth::user()->username;
        $UserID = Auth::user()->id;
        $UserDep = Auth::user()->depID;
        
        // Create a sales transaction for non-fuel products
        $transaction = SalesTransactions::create([
            'customerID' => $creditTransaction['customerId'],
            'sub_total' => $creditTransaction['total'],
            'discount' => 0,
            'net_price' => $creditTransaction['total'],
            'paid_amount' => 0, // Credit sale, so no payment
            'balance' => $creditTransaction['total'], // Full amount as balance
            'paid_date' => $request->date,
            'type' => 'credit',
            'payment_method' => 'credit',
            'depID' => $UserDep,
            'seller' => $UserID,
            'note' => 'Non-fuel credit sale from fuel station - ' . ($creditTransaction['description'] ?? ''),
            
        ]);
        
        // Create sales record
        Sales::create([
            'proID' => $creditTransaction['productId'],
            'quantity' => $creditTransaction['quantity'],
            'price' => $creditTransaction['rate'],
            'unit' => 'unit', // Adjust as needed
            'depID' => auth()->user()->depID,
            'total_price' => $creditTransaction['total'],
            'sales_transaction_id' => $transaction->id,
        ]);
        
        // Decrement product quantity for non-fuel items
        $product = Products::find($creditTransaction['productId']);
        if ($product) {
            $product->decrement('quantity', $creditTransaction['quantity']);
        }
    }

    // Method to handle multiple expense registrations
    private function registerExpensesFromSale($request)
    {
        $depID = auth()->user()->depID;
        $User = Auth::user()->username;
        $UserID = Auth::user()->id;
        
        foreach ($request->expense_transactions as $expenseData) {
            // Initialize an array for expense data
            $expenseRecord = [
                'type' => $expenseData['type'],
                'amount' => $expenseData['amount'],
                'payment_account' => 'Cash Account',
                'date' => $request->date,
                'depID' => auth()->user()->depID,
                'description' => $expenseData['description'] ?? '',
                
            ];
    
            // Create a new expense record
            Expenses::create($expenseRecord);
            
            $ExpenseAccount = CashAccount::where('AccCode', 'Expense')->firstOrFail();
            $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
    
            // Adjust accounts
            $CashAccount->increment('credit', $expenseData['amount']);
            $ExpenseAccount->increment('debit', $expenseData['amount']);
    
            // Create a Finance Transaction record
            FinanceTrans::create([
                'user' => $UserID,
                'depID' => auth()->user()->depID,
                'depitAcc' => 'Expense Account',
                'depitAmount' => $expenseData['amount'],
                'creditAcc' => 'Cash Accounts',
                'creditAmount' => $expenseData['amount'],
                'formType' => 'Expense Form',
                'action' => 'Insert',
                'date' => now(),
                'info' => $User . ' Register '.  $expenseData['type'] . ' Amount Of ' . $expenseData['amount'] . ' Accounting effected',
            ]);
        }
    }



    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Base query
            if (auth()->user()->role == 'admin' || auth()->user()->role == 'manager'  || auth()->user()->role == 'acc') {
                $fuelSalesQuery = FuelSale::with([
                    'transactions.product', 
                    'creditSales.product', 
                    'creditSales.customer',
                    'salesman'
                ]);
            } else {
                $fuelSalesQuery = FuelSale::where('created_by', auth()->user()->id)
                    ->with([
                        'transactions.product', 
                        'creditSales.product', 
                        'creditSales.customer',
                        'salesman'
                    ]);
            }

            // Apply filters
            if ($request->input('product_name')) {
                $productName = $request->input('product_name');
                $fuelSalesQuery->where(function($query) use ($productName) {
                    $query->whereHas('transactions.product', function ($q) use ($productName) {
                        $q->where('name', 'LIKE', "%{$productName}%");
                    })->orWhereHas('creditSales.product', function ($q) use ($productName) {
                        $q->where('name', 'LIKE', "%{$productName}%");
                    });
                });
            }

            if ($request->input('transaction_id')) {
                $transactionId = $request->input('transaction_id');
                $fuelSalesQuery->where('id', 'LIKE', "%{$transactionId}%");
            }

            if ($request->input('salesman')) {
                $salesmanName = $request->input('salesman');
                $fuelSalesQuery->whereHas('salesman', function ($q) use ($salesmanName) {
                    $q->where('name', 'LIKE', "%{$salesmanName}%");
                });
            }

            if ($request->input('shift')) {
                $shift = $request->input('shift');
                $fuelSalesQuery->where('shift', $shift);
            }

            if ($request->input('startDate') && $request->input('endDate')) {
                $startDate = $request->input('startDate');
                $endDate = $request->input('endDate');
                $fuelSalesQuery->whereBetween('date', [$startDate, $endDate]);
            }

            // Total counts
            $totalData = $fuelSalesQuery->count();
            $totalFiltered = $totalData;

            // Pagination and ordering
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');

            $columns = [
                0 => 'date',
                1 => 'id',
                2 => 'salesman',
                3 => 'shift',
                4 => 'net_total',
            ];

            $orderField = $columns[$orderColumn] ?? 'id';
            $fuelSalesQuery->orderBy($orderField, $orderDir);

            $fuelSales = $fuelSalesQuery->offset($start)->limit($limit)->get();

            $data = [];
            foreach ($fuelSales as $fuelSale) {
                $data[] = $this->formatFuelSaleData($fuelSale);
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data
            ]);
        }

        return view('layout.salesOrders.fuel_sales_index');
    }

    private function formatFuelSaleData($fuelSale)
    {
        $productTransactions = [];
    
        // Merge all transactions
        $allSales = collect($fuelSale->transactions)->map(function($t) {
            return [
                'type' => $t->payment_method ?? 'cash', // Use the actual payment_method
                'product_id' => $t->product_id,
                'product_name' => $t->product->name ?? 'Unknown',
                'liters' => floatval($t->liters),
                'rate' => floatval($t->rate),
                'total' => floatval($t->total),
                'previous_reading' => $t->previous_reading,
                'current_reading' => $t->current_reading,
                'dphase' => $t->dphase ?? 'N/A',
                'customer' => $t->customer->name ?? null,
                'description' => $t->description ?? null,
                'date' => $t->date ?? now()->format('Y-m-d')
            ];
        })
        ->merge(
            collect($fuelSale->creditSales)->map(function($c) {
                return [
                    'type' => 'credit',
                    'product_id' => $c->product_id,
                    'product_name' => $c->product->name ?? 'Unknown',
                    'liters' => floatval($c->quantity),
                    'rate' => floatval($c->rate),
                    'total' => floatval($c->total),
                    'previous_reading' => null,
                    'current_reading' => null,
                    'dphase' => null,
                    'customer' => $c->customer->name ?? 'N/A',
                    'description' => $c->description ?? 'N/A',
                    'date' => $c->date
                ];
            })
        );
    
        // Group transactions by product
        $grouped = $allSales->groupBy('product_id');
    
        foreach ($grouped as $pid => $sales) {
            // Get credit transactions only
            $creditSales = $sales->where('type', 'credit');
            $creditLiters = $creditSales->sum('liters');
            $creditAmount = $creditSales->sum('total');
    
            // Get cash transactions only (anything that's not credit)
            $cashSales = $sales->where('type', '!=', 'credit');
            $cashLiters = $cashSales->sum('liters');
            $cashAmount = $cashSales->sum('total');
    
            $totalLiters = $cashLiters + $creditLiters;
            $totalAmount = $cashAmount + $creditAmount;
    
            $productTransactions[$pid] = [
                'product_id' => $pid,
                'product_name' => $sales->first()['product_name'],
                'cash_liters' => $cashLiters,
                'credit_liters' => $creditLiters,
                'total_liters' => $totalLiters,
                'cash_amount' => $cashAmount,
                'credit_amount' => $creditAmount,
                'total_amount' => $totalAmount,
                'transactions' => $sales->values()->all()
            ];
        }
    
        return [
            'id' => $fuelSale->id,
            'date' => $fuelSale->date ? Carbon::parse($fuelSale->date)->format('d-m-Y') : 'N/A',
            'salesman' => $fuelSale->salesman->full_name ?? 'N/A',
            'shift' => $fuelSale->shift ?? 'N/A',
            'discount' => floatval($fuelSale->discount),
            'net_total' => floatval($fuelSale->net_total),
            'cash_on_hand' => floatval($fuelSale->cash_on_hand),
            'balance' => floatval($fuelSale->balance),
            'created_at' => $fuelSale->created_at ? $fuelSale->created_at->format('d-m-Y H:i') : 'N/A',
            'product_transactions' => array_values($productTransactions),
            'transaction_count' => $allSales->count(),
        ];
    }



    public function show($id)
    {
        $fuelSale = FuelSale::with([
            'salesman', 
            'transactions.product', 
            'creditSales.product', 
            'creditSales.customer',
            'createdBy'
        ])->findOrFail($id);

        return view('layout.salesOrders.fuel_sale_show', compact('fuelSale'));
    }




    // FUEL CREDIT SALES LIST CONTROLLER
    public function fuelCreditSalesList(Request $request)
    {
        if ($request->ajax()) {
            // Determine access level
            if (auth()->user()->role == 'admin' || auth()->user()->role == 'manager') {
                $query = FuelCreditSale::with(['customer', 'product']);
            } else {
                $query = FuelCreditSale::where('created_by', auth()->user()->id)
                    ->with(['customer', 'product']);
            }

            // Applying filters
            if ($request->input('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->input('customer_name')}%");
                });
            }
            
            // Changed from fuel_type to product name filter
            if ($request->input('fuel_type')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->input('fuel_type')}%");
                });
            }
            
            if ($request->input('status')) {
                $query->where('status', $request->input('status'));
            }
            
            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('created_at', [
                    $request->input('startDate') . ' 00:00:00', 
                    $request->input('endDate') . ' 23:59:59'
                ]);
            }

            $totalData = $query->count();
            $totalFiltered = $totalData;

            $columns = [
                0 => 'id',
                1 => 'customer_id',
                2 => 'product_id',
                3 => 'quantity',
                4 => 'rate',
                5 => 'total',
                6 => 'status',
                7 => 'created_at',
            ];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);

            $creditSales = $query->get();

            $data = [];
            
            foreach ($creditSales as $sale) {
                $nestedData['id'] = $sale->id;
                
                // Customer information
                if ($sale->customer) {
                    $nestedData['customer_name'] = $sale->customer->customer_name;
                    $nestedData['customer_phone'] = $sale->customer->phone ?? 'N/A';
                    $nestedData['customer_address'] = $sale->customer->address ?? 'N/A';
                } else {
                    $nestedData['customer_name'] = 'N/A';
                    $nestedData['customer_phone'] = 'N/A';
                    $nestedData['customer_address'] = 'N/A';
                }
                
                // Changed from fuel_type to product name
                $nestedData['fuel_type'] = $sale->product ? $sale->product->name : 'N/A';
                $nestedData['quantity'] = $sale->quantity . ' L';
                $nestedData['rate'] = '$' . number_format($sale->rate, 2);
                $nestedData['total'] = '$' . number_format($sale->total, 2);
                
                // Status with badge styling
                $statusClass = '';
                if ($sale->status == 'paid') {
                    $statusClass = 'badge-success';
                } elseif ($sale->status == 'partial') {
                    $statusClass = 'badge-warning';
                } else {
                    $statusClass = 'badge-danger';
                }
                
                $nestedData['status'] = '<span class="badge ' . $statusClass . '">' . ucfirst($sale->status) . '</span>';
                
                // Format the date
                $nestedData['date'] = \Carbon\Carbon::parse($sale->date)->format('d-m-Y');
                $nestedData['created_at'] = \Carbon\Carbon::parse($sale->date)->format('d-m-Y H:i');

                $data[] = $nestedData;
            }

            $json_data = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data
            ];

            return response()->json($json_data);
        }

        return view('layout.salesOrders.fuel_credit_sales_index');
    }
    /**
     * Display the specified fuel sale.
     */
    // public function show($id)
    // {
    //     $fuelSale = FuelSale::with(['salesman', 'transactions.product', 'creditSales.customer', 'creditSales.product'])
    //         ->findOrFail($id);

    //     return view('admin.fuel_sales_show', compact('fuelSale'));
    // }

    /**
     * Search customers for credit sales.
     */
    public function searchCustomers(Request $request)
    {
        $query = $request->get('query');
        
        $customers = Customers::where('depID', auth()->user()->depID)
    ->where(function ($q) use ($query) {
        $q->where('name', 'like', "%{$query}%")
          ->orWhere('phone', 'like', "%{$query}%")
          ->orWhere('company_name', 'like', "%{$query}%");
    })
    ->get();


        return response()->json($customers);
    }

    /**
     * Get product selling price.
     */
    public function getProductPrice($id)
    {
        $product = Products::findOrFail($id);
        
        return response()->json([
            'selling_price' => $product->selling_price
        ]);
    }
    
    
    // Add this destroy method to your Store Controller
        public function destroy($id)
        {
            if (Auth::user()->role === 'acc') {
                return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
            }
            
            DB::beginTransaction();
            try {
                // FIX: Use 'payment' instead of 'payments'
                $fuelSale = FuelSale::with(['transactions', 'creditSales', 'payment'])->findOrFail($id);
                
                // Restore product quantities for cash transactions
                foreach ($fuelSale->transactions as $transaction) {
                    $product = Products::find($transaction->product_id);
                    if ($product) {
                        $product->increment('quantity', $transaction->liters);
                        
                        // Restore FIFO purchases (this is complex and might need adjustment based on your FIFO implementation)
                        $this->restoreFIFOPurchase($transaction->product_id, $transaction->liters);
                    }
                }
                
                // Restore product quantities and customer balances for credit sales
                foreach ($fuelSale->creditSales as $creditSale) {
                    $product = Products::find($creditSale->product_id);
                    if ($product) {
                        $product->increment('quantity', $creditSale->quantity);
                        
                        // Restore FIFO purchases for credit sales
                        $this->restoreFIFOPurchase($creditSale->product_id, $creditSale->quantity);
                    }
                    
                    // Decrement customer balance (reverse the credit)
                    $customer = Customers::find($creditSale->customer_id);
                    if ($customer) {
                        $customer->decrement('balance', $creditSale->total);
                    }
                }
                
                // Restore salesman balance
                if ($fuelSale->balance > 0) {
                    $salesman = Salesman::find($fuelSale->salesman_id);
                    if ($salesman) {
                        $salesman->decrement('balance', $fuelSale->balance);
                    }
                }
                
                // Delete related records
                $fuelSale->transactions()->delete();
                $fuelSale->creditSales()->delete();
                
                // FIX: Check if payment exists before trying to delete
                if ($fuelSale->payment) {
                    $fuelSale->payment()->delete();
                }
                
                // Delete the main fuel sale record
                $fuelSale->delete();
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Fuel sale transaction deleted successfully! All related data has been restored.'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting fuel sale: ' . $e->getMessage()
                ], 500);
            }
        }

    // Helper method to restore FIFO purchases (this is a simplified version)
    private function restoreFIFOPurchase($productId, $liters)
    {
        // This is a simplified approach - you might need to adjust based on your exact FIFO implementation
        // One approach is to create a restoration record or use a different strategy
        
        $purchases = Purchases::where('proID', $productId)
                             ->where('remaining', '<', DB::raw('quantity')) // Find purchases that had remaining reduced
                             ->orderBy('id', 'desc') // Reverse order (LIFO for restoration)
                             ->get();
        
        $remainingLiters = $liters;
        
        foreach ($purchases as $purchase) {
            if ($remainingLiters <= 0) break;
            
            $maxRestore = $purchase->quantity - $purchase->remaining;
            if ($maxRestore > 0) {
                $restoreAmount = min($maxRestore, $remainingLiters);
                $purchase->increment('remaining', $restoreAmount);
                $remainingLiters -= $restoreAmount;
            }
        }
        
        // If there are still liters to restore and no specific purchases found,
        // just add to the most recent purchase
        if ($remainingLiters > 0) {
            $latestPurchase = Purchases::where('proID', $productId)
                                      ->orderBy('id', 'desc')
                                      ->first();
            if ($latestPurchase) {
                $latestPurchase->increment('remaining', $remainingLiters);
            }
        }
    }
    
    public function printSheet($id)
    {
        $fuelSale = FuelSale::with([
            'transactions.product',
            'creditSales.product',
            'creditSales.customer',
            'salesman'
        ])->findOrFail($id);

        $cashTransactions = $fuelSale->transactions ?? collect();
        $creditSales = $fuelSale->creditSales ?? collect();
        $cashDisplayRows = $this->buildCashDisplayRows($cashTransactions, $creditSales);

        $cashTotal = $cashTransactions->sum(fn ($t) => $t->total ?? ($t->liters * $t->rate));
        $creditTotal = $creditSales->sum(fn ($c) => $c->total ?? ($c->quantity * $c->rate));
        $productSummary = $this->calculateProductSummary($fuelSale);

        return view('layout.salesOrders.fuel_sale_print', compact(
            'fuelSale',
            'cashTransactions',
            'cashDisplayRows',
            'creditSales',
            'cashTotal',
            'creditTotal',
            'productSummary'
        ));
    }

    private function buildCashDisplayRows($cashTransactions, $creditSales)
    {
        $phases = ['Phase 1', 'Phase 2'];
        $products = collect();

        foreach ($cashTransactions as $transaction) {
            if ($transaction->product) {
                $products->put($transaction->product_id, $transaction->product);
            }
        }

        foreach ($creditSales as $credit) {
            if ($credit->product) {
                $products->put($credit->product_id, $credit->product);
            }
        }

        $sortedProducts = $products->sortBy(function ($product) {
            $name = strtolower($product->name ?? '');

            if (str_contains($name, 'diesel')) {
                return 0;
            }

            if (str_contains($name, 'petrol')) {
                return 1;
            }

            return 2;
        });

        $rows = collect();

        foreach ($sortedProducts as $productId => $product) {
            $productTransactions = $cashTransactions->where('product_id', $productId);
            $defaultRate = optional($productTransactions->first())->rate ?? $product->selling_price ?? 0;

            foreach ($phases as $phase) {
                $match = $productTransactions->firstWhere('dphase', $phase);

                if ($match) {
                    $rows->push($match);
                    continue;
                }

                $rows->push((object) [
                    'product' => $product,
                    'dphase' => $phase,
                    'previous_reading' => 0,
                    'current_reading' => 0,
                    'liters' => 0,
                    'rate' => $defaultRate,
                    'total' => 0,
                ]);
            }
        }

        return $rows;
    }
    
    private function calculateProductSummary($fuelSale)
    {
        $summary = [];
        
        // Process cash transactions
        foreach ($fuelSale->transactions as $transaction) {
            $productId = $transaction->product_id;
            $productName = $transaction->product->name ?? 'Unknown Product';
            
            if (!isset($summary[$productId])) {
                $summary[$productId] = [
                    'name' => $productName,
                    'cash_liters' => 0,
                    'credit_liters' => 0,
                    'total_liters' => 0,
                    'cash_amount' => 0,
                    'credit_amount' => 0,
                    'total_amount' => 0
                ];
            }
            
            $summary[$productId]['cash_liters'] += $transaction->liters;
            $summary[$productId]['cash_amount'] += ($transaction->total ?? ($transaction->liters * $transaction->rate));
        }
    
        // Process credit sales
        foreach ($fuelSale->creditSales as $credit) {
            $productId = $credit->product_id;
            $productName = $credit->product->name ?? 'Unknown Product';
            
            if (!isset($summary[$productId])) {
                $summary[$productId] = [
                    'name' => $productName,
                    'cash_liters' => 0,
                    'credit_liters' => 0,
                    'total_liters' => 0,
                    'cash_amount' => 0,
                    'credit_amount' => 0,
                    'total_amount' => 0
                ];
            }
            
            $summary[$productId]['credit_liters'] += $credit->quantity;
            $summary[$productId]['credit_amount'] += ($credit->total ?? ($credit->quantity * $credit->rate));
        }
    
        // Calculate totals
        foreach ($summary as $productId => &$data) {
            $data['total_liters'] = $data['cash_liters'] + $data['credit_liters'];
            $data['total_amount'] = $data['cash_amount'] + $data['credit_amount'];
        }
    
        return array_values($summary);
    }


}