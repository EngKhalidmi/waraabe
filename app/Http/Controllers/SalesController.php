<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\SalesTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Customers;
use App\Models\AccountingTransaction;
use App\Models\Credits;
use App\Models\CashAccount;
use App\Models\Products;
use App\Models\Purchases;
use App\Models\BankStatement;
use App\Models\FinanceTrans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Departments;
class SalesController extends Controller
{

    public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager' || auth()->user()->role == 'acc') {
            $query = Sales::query();
            } else {
                $query = Sales::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('name')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->input('name')}%");
                });
            }
            
            if ($request->input('phone')) {
                $query->whereHas('transaction', function ($q) use ($request) {
                    $q->where('id', 'LIKE', "%{$request->input('phone')}%");
                });
            }
            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('created_at', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'proID',
                2 => 'quantity',
                3 => 'price',
                4 => 'total_price',
                5 => 'sales_transaction_id',
                6 => 'created_at',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $sales = $query->get();

        $data = [];
        
    foreach ($sales as $sale) {
    $nestedData['id'] = $sale->id;
    
    // Check if product exists and is not deleted
    if ($sale->proID && $sale->product) {
        $nestedData['product_name'] = $sale->product->name;
        // If you need to check if the product was deleted, add an additional check like:
        // $nestedData['product_name'] = $sale->product->is_deleted ? 'Deleted' : $sale->product->name;
    } else {
        $nestedData['product_name'] = 'N/A'; // No product or product was deleted
    }

    $nestedData['quantity'] = $sale->quantity;
    
    // Uncomment the next lines if you need to add cost details for the product
    // $nestedData['cost_per_unit'] = $sale->proID && $sale->product ? $sale->product->actual_price : 'N/A';
    // $nestedData['total_cost_per_unit'] = $sale->proID && $sale->product ? $sale->product->total_actual_price : 'N/A';

    $nestedData['price'] = $sale->price;
    $nestedData['total_price'] = $sale->total_price;
    $nestedData['sales_transaction_id'] = $sale->sales_transaction_id;
    
    // Format the creation date
    $nestedData['created_at'] = \Carbon\Carbon::parse($sale->created_at)->format('d-m-Y-H-i');

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

        return view('layout.salesOrders.index');
    }
    
    public function create(){
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $deps = Departments::get();
        } else {
            $deps = Departments::where('id', auth()->user()->depID)->get();
        }
        return view('layout.salesOrders.add', compact('deps'));
    }

    public function searchProduct(Request $request)
    {
        $query = $request->get('query');
    
        if (auth()->user()->role == 'admin' || auth()->user()->role == 'manager' || auth()->user()->role == 'sales') {
            $products = Products::where('name', 'like', '%' . $query . '%')
                ->get(['id', 'name', 'quantity', 'type', 'unit', 'selling_price']) // Keep selling_price from Products
                ->map(function ($product) {
                    // Fetch the oldest purchase with remaining stock > 0
                    $oldestPurchase = Purchases::where('proID', $product->id)
                        ->where('remaining', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->first();
    
                    // Add actual_price but do NOT overwrite selling_price
                    if ($oldestPurchase) {
                        $product->actual_price = $oldestPurchase->unit_cost + $oldestPurchase->add_cost;
                    } else {
                        $product->actual_price = 0; // Default value if no purchase found
                    }
    
                    return $product;
                });
        } else {
            $products = Products::where('name', 'like', '%' . $query . '%')
                ->where('depID', auth()->user()->depID)
                ->get(['id', 'name', 'quantity', 'type', 'unit', 'selling_price']) // Keep selling_price from Products
                ->map(function ($product) {
                    // Fetch the oldest purchase with remaining stock > 0
                    $oldestPurchase = Purchases::where('proID', $product->id)
                        ->where('remaining', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->first();
    
                    // Add actual_price but do NOT overwrite selling_price
                    if ($oldestPurchase) {
                        $product->actual_price = $oldestPurchase->unit_cost + $oldestPurchase->add_cost;
                    } else {
                        $product->actual_price = 0; // Default value if no purchase found
                    }
    
                    return $product;
                });
        }
    
        return response()->json($products);
    }

    public function searchCustomer(Request $request)
    {
    try {
        $request->validate([
            'query' => 'required|string|min:2|max:100'
        ]);

        $query = $request->get('query');
        $user = auth()->user();
        
        $customers = Customers::query()
            ->when(in_array($user->role, ['admin', 'manager']), function ($q) {
                // No department restriction for admins/managers
            }, function ($q) use ($user) {
                $q->where('depID', $user->depID); // Department restriction for others
            })
            ->where(function($q) use ($query) {
                $q->where('customer_name', 'like', '%' . $query . '%')
                  ->orWhere('phone', 'like', '%' . $query . '%');
            })
            ->select(['id', 'customer_name', 'phone', 'serial'])
            ->limit(50) // Prevent too many results
            ->get();

        return response()->json($customers);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Search failed',
            'message' => $e->getMessage()
        ], 500);
    }
}


   
    public function store(Request $request) {
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
        
    // First validate all required fields EXCEPT total_price
    $validated = $request->validate([
        'customerID' => 'required',
        'products' => 'required|array|min:1',
        'products.*.proID' => 'required',
        'products.*.quantity' => 'required|numeric|min:0.01',
        'products.*.price' => 'required|numeric|min:0.01',
        // Remove total_price from validation here
        'discount' => 'required',
        'net_price' => 'required',
        'paid_amount' => 'required',
        'balance' => 'required',
        'due_date' => 'nullable|date',
        'depID' => 'required',
    ]);

    // Calculate total_price for each product if not provided or invalid
    $products = collect($request->products)->map(function ($product) {
        // Calculate if not provided or invalid
        if (!isset($product['total_price']) || !is_numeric($product['total_price'])) {
            $product['total_price'] = $product['quantity'] * $product['price'];
        }
        return $product;
    })->toArray();

    // Replace the products in the request
    $request->merge(['products' => $products]);

    \Log::debug('Processed request data:', $request->all());

    DB::beginTransaction();
    try {        
        $User = Auth::user()->username;
        $UserID = Auth::user()->id;
        $UserDep = Auth::user()->depID;
        $paidAmount = $request->paid_amount;
        $balance = $request->balance;
        $netPrice = $request->net_price;
        $paymentMethod = $request->paymentMethod;
        $depID = $request->depID;
        $note = $request->note;
        
        $type = $this->determineTransactionType($paidAmount, $balance);

        $transaction = SalesTransactions::create([
            'customerID' => $request->customerID,
            'sub_total' => $request->subtotal,
            'discount' => $request->discount,
            'net_price' => $netPrice,
            'paid_amount' => $paidAmount,
            'balance' => $balance,
            'paid_date' => $request->due_date ?: now(),
            'type' => $type,
            'payment_method' => $paymentMethod,
            'depID' => $depID,
            'seller' => $UserID,
            'note' => $note
        ]);
        
        $invoiceId = $transaction->id;
        $totalCost = 0;

        foreach ($request->products as $product) {
            // Validate total_price calculation
            $calculatedTotal = $product['quantity'] * $product['price'];
            $totalPrice = $product['total_price'] ?? $calculatedTotal;
            
            if (abs($totalPrice - $calculatedTotal) > 0.01) {
                \Log::warning('Price mismatch for product '.$product['proID'], [
                    'provided' => $product['total_price'],
                    'calculated' => $calculatedTotal
                ]);
                $totalPrice = $calculatedTotal; // Use calculated value if mismatch
            }

            Sales::create([
                'proID' => $product['proID'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'unit' => $product['unit'],
                'depID' => $depID,
                'total_price' => $totalPrice,
                'sales_transaction_id' => $transaction->id,
            ]);

            $this->updateInventoryAndCost($product, $totalCost);
        }

        $this->updateCustomerBalance($request->customerID, $netPrice, $paidAmount);
        $this->recordFinancialTransactions(
            $UserID, 
            $UserDep, 
            $paidAmount, 
            $netPrice, 
            $totalCost, 
            $User,
            $request->customerID  // Add this parameter
        );

        DB::commit();
        
        return redirect('/sales/register')
            ->with([
                'status' => 'Sales transaction successfully recorded.',
                'invoice_url' => "/public/salesTransactions/invoice/$invoiceId"
            ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Sales Transaction Error: '.$e->getMessage(), [
            'exception' => $e,
            'request' => $request->all()
        ]);
        return redirect()->back()
            ->withInput()
            ->withErrors('Transaction failed: '.$e->getMessage());
    }
}

// Helper Methods (add these to your controller)

private function determineTransactionType($paidAmount, $balance) {
    if ($paidAmount > 0 && $balance > 0) return 'Cash & Credit';
    if ($paidAmount > 0 && $balance == 0) return 'Cash';
    return 'Credit';
}

// private function updateInventoryAndCost($product, &$totalCost) {
//     $productModel = Products::find($product['proID']);
//     if (!$productModel || $productModel->type == 'Service') return;

//     if ($productModel->quantity < $product['quantity']) {
//         throw new \Exception('Insufficient inventory for product: '.$productModel->name);
//     }

//     $productModel->decrement('quantity', $product['quantity']);
//     $remainingQty = $product['quantity'];

//     Purchases::where('proID', $productModel->id)
//         ->where('remaining', '>', 0)
//         ->orderBy('created_at')
//         ->each(function ($purchase) use (&$remainingQty, &$totalCost) {
//             if ($remainingQty <= 0) return false; // Break loop
            
//             $deductQty = min($remainingQty, $purchase->remaining);
//             $unitCost = $purchase->unit_cost + $purchase->add_cost;
            
//             $totalCost += $deductQty * $unitCost;
//             $purchase->decrement('remaining', $deductQty);
//             $remainingQty -= $deductQty;
//         });

//     if ($remainingQty > 0) {
//         throw new \Exception('Incomplete inventory cost calculation for product: '.$productModel->name);
//     }
// }
private function updateInventoryAndCost($product, &$totalCost) {
    
    
    $productModel = Products::find($product['proID']);
    if (!$productModel || $productModel->type == 'Service') return;

    if ($productModel->quantity < $product['quantity']) {
        throw new \Exception('Insufficient inventory for product: '.$productModel->name);
    }

    // Only decrement from Products stock
    $productModel->decrement('quantity', $product['quantity']);

    // If you still want to calculate cost (optional, without touching Purchases)
    $avgCost = $productModel->cost ?? 0; // adjust if you store cost somewhere
    $totalCost += $product['quantity'] * $avgCost;
}



private function updateCustomerBalance($customerID, $netPrice, $paidAmount) {
    $customer = Customers::find($customerID);
    if ($customer) {
        $customer->balance += max($netPrice - $paidAmount, 0);
        $customer->save();
    }
}

private function recordFinancialTransactions($UserID, $UserDep, $paidAmount, $netPrice, $totalCost, $username, $customerID) {
    $accounts = [
        'Cash' => CashAccount::where('AccCode', 'Cash')->firstOrFail(),
        'Inventory' => CashAccount::where('AccCode', 'Inventory')->firstOrFail(),
        'Revenue' => CashAccount::where('AccCode', 'Revenue')->firstOrFail()
    ];

    $accounts['Inventory']->increment('credit', $totalCost);
    $accounts['Cash']->increment('debit', $paidAmount);
    $accounts['Revenue']->increment('credit', $netPrice);

    FinanceTrans::create([
        'user' => $UserID,
        'depID' => $UserDep,
        'depitAcc' => 'Cash Account',
        'depitAmount' => $paidAmount,
        'creditAcc' => 'Revenue & Inventory Accounts',
        'creditAmount' => $netPrice,
        'date' => now(),
        'formType' => 'POS - Creating New Sales',
        'action' => 'Insert',
       'info' => $username.' Purchases '.$customerID.' Net Total Of '.$netPrice.' Accounting Effected. Total Cost of Goods Sold: '.$totalCost,
    ]);
}
}
