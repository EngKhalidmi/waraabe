<?php

namespace App\Http\Controllers;
use App\Models\Products;
use App\Models\Suppliers;
use App\Models\CashAccount;
use App\Models\Purchases;
use App\Models\PurchaseTransactions;
use App\Models\Sales;
use App\Models\AccountingTransaction;
use App\Models\AccountPayables;
use App\Models\FinanceTrans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Departments;
use Illuminate\Support\Str;

class ProductsController extends Controller
{

    //List all Pateints
    public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager' || auth()->user()->role == 'acc') {
            $query = Products::query();
            } else {
                $query = Products::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('name')) {
                $query->where('name', 'LIKE', "%{$request->input('name')}%");
            }
    
            if ($request->input('sku_code')) {
                $query->where('sku_code', $request->input('sku_code'));
            }

            if ($request->input('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->input('supplier')) {
                $query->where('supplier', $request->input('supplier'));
            }

            if ($request->input('quantity')) {
                $query->where('quantity', $request->input('quantity'));
            }

            // if ($request->input('startDate') && $request->input('endDate')) {
            //     $query->whereBetween('created_at', [$request->input('startDate'), $request->input('endDate')]);
            // }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'name',
                2 => 'sku_code',
                3 => 'type',
                4 => 'status',
                5 => 'quantity',
                6 => 'actual_price',
                7 =>'selling_price',
                8 =>'supplier',
                9 => 'depID',
                10 => 'created_at',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $inventory = $query->get();

        $data = [];
        foreach ($inventory as $pro) {
            $nestedData['id'] = $pro->id;
            $nestedData['name'] = $pro->name;
            $nestedData['sku_code'] = $pro->sku_code;
            $nestedData['type'] = $pro->type;
            $nestedData['status'] = $pro->status;
            $nestedData['quantity'] = $pro->quantity;
            $nestedData['actual_price'] = $pro->actual_price;
            $nestedData['selling_price'] = $pro->selling_price;
            $nestedData['supplier'] = $pro->supplier;
            $nestedData['depID'] = $pro->depID ? $pro->department->name : 'N/A';  // Assuming department ID is stored in 'depID' column in Products table  // Replace with actual column name in your database.
            $nestedData['created_at'] = $pro->created_at->format('Y-m-d');
            $nestedData['action'] = $pro->id;

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

        return view('layout.inventery.index');
    }

    public function register(){
         if (auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $suppliers = Suppliers::get();
        } else {
            $suppliers = Suppliers::where('depID', auth()->user()->depID)->get();
        }
        return view('layout.inventery.register', compact('suppliers'));
    }

    public function create(){
         if (auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $suppliers = Suppliers::get();
        $deps = Departments::get();
        } else {
            $suppliers = Suppliers::where('depID', auth()->user()->depID)->get();
            $deps = Departments::where('id', auth()->user()->depID)->get();
        }
        return view('layout.inventery.add', compact('suppliers', 'deps'));
    }

    public function searchProduct(Request $request){
        $query = $request->get('query');
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
              $products = Products::where('type', 'Inventory')->where('name', 'like', '%' . $query . '%')->get(['id', 'name', 'unit', 'type', 'selling_price', 'actual_price']);
        }else {
            $products = Products::where('type', 'Inventory')->where('depID', auth()->user()->depID)->where('name', 'like', '%' . $query . '%')->get(['id', 'name', 'unit', 'type', 'selling_price', 'actual_price']);
        }
        return response()->json($products);
    }

    public function searchSupplier(Request $request){
        $query = $request->get('query');
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $customers = Suppliers::where('name', 'like', '%' . $query . '%')->get(['id', 'name']);
        } else {
            $customers = Suppliers::where('depID', auth()->user()->depID)->where('name', 'like', '%' . $query . '%')->get(['id', 'name']);
        }
        return response()->json($customers);
    }

    // store new inventory only
    public function storeInventory(Request $request) {
            if (Auth::user()->role === 'acc') {
                return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
            }
    
        // Validate the request
        $request->validate([
            'name' =>'required|string',
            'type' =>'required|string',
            'actual_price' =>'required|numeric',
            'selling_price' =>'nullable|numeric',
            'supplier' =>'nullable|string'
        ]);

        $depID = auth()->user()->depID;

        // Create a new inventory
        $product = new Products;
        $product->name = $request->name;
        $product->sku_code = $request->name. '-'. mt_rand(100, 999);
        $product->type = $request->type;
        $product->status = 1;
        $product->unit = $request->unit;
        $product->depID = $depID;
        $product->actual_price = $request->actual_price;
        $product->selling_price = $request->selling_price;
        $product->supplier = $request->supplier;
        $product->info = $request->info;
        $product->save();

        return redirect()->route('products')->with('status', 'New Products Has Been Registered Successfully.');
    }
    
    public function store(Request $request)
    {
        if (Auth::user()->role === 'acc') {
            return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
        }
    
        // Validate the request
        $request->validate([
            'customerID' => 'required',
            'due_date' => 'required|date',
            'products' => 'required|array',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'net_price' => 'required|numeric',
            'add_cost' => 'required|numeric',
            'paid_amount' => 'required|numeric',
            'balance' => 'required|numeric',
            'depID' => 'required',
        ]);
    
        DB::beginTransaction();
    
        try {
            $User       = Auth::user()->username;
            $UserdepID  = Auth::user()->depID;
            $UserID     = Auth::user()->id;
    
            $customerID     = $request->customerID;
            $dueDate        = $request->due_date;
            $referenceNo    = $request->reference;
            $subTotal       = $request->subtotal;
            $add_cost       = $request->add_cost;
            $discount       = $request->discount;
            $netPrice       = $request->net_price;
            $paidAmount     = $request->paid_amount;
            $balance        = $request->balance;
            $paymentMethod  = $request->payment_method;
            $products       = $request->products;
            $depID          = $request->depID;
    
            // Payment type logic
            $type = 'Credit';
            if ($paidAmount > 0 && $balance > 0) {
                $type = 'Cash & Credit';
            } elseif ($paidAmount > 0 && $balance == 0) {
                $type = 'Cash';
            } else {
                $type = 'Credit';
                $paymentMethod = 'Credit on Book';
            }
    
            // Create a new Purchase Transaction
            $transaction = PurchaseTransactions::create([
                'customerID' => $customerID,
                'subTotal'   => $subTotal,
                'discount'   => $discount,
                'net_price'  => $netPrice,
                'add_cost'   => $add_cost,
                'paidAmount' => $paidAmount,
                'balance'    => $balance,
                'date'       => $dueDate,
                'payMethod'  => $paymentMethod,
                'type'       => $type,
                'depID'      => $depID,
                'purchased'  => $UserID,
            ]);
    
            // Calculate total base cost for proportional distribution
            $totalBaseCost = 0;
            foreach ($products as $product) {
                $totalBaseCost += $product['actual_price'] * $product['quantity'];
            }
    
            // Process each product
            foreach ($products as $product) {
                $productId     = $product['proID'] ?? null;
                $quantity      = $product['quantity'];
                $actualPrice   = $product['actual_price'];
                $sellingPrice  = $product['price'];
                $unit          = $product['unit'];
                $name          = $product['name'];
    
                // Calculate cost allocations
                $productBaseCost     = $actualPrice * $quantity;
                $proportionalAddCost = ($productBaseCost / $totalBaseCost) * $add_cost;
                $totalUnitCost       = $actualPrice + ($proportionalAddCost / $quantity);
    
                /**
                 * PRODUCT HANDLING
                 */
                if (empty($productId)) {
                    // Try to find product by name + depID
                    $existingProduct = Products::where('name', $name)
                        ->where('depID', $depID)
                        ->first();
    
                    if ($existingProduct) {
                        // Update quantity and prices
                        $existingProduct->quantity      += $quantity;
                        $existingProduct->actual_price   = $actualPrice;
                        $existingProduct->selling_price  = $sellingPrice;
                        $existingProduct->save();
    
                        $productId = $existingProduct->id;
                    } else {
                        // Create new product
                        $newProduct = Products::create([
                            'sku_code'      => 'INV-' . strtoupper(Str::random(6)),
                            'name'          => $name,
                            'unit'          => $unit,
                            'type'          => 'Inventory',
                            'status'        => 1,
                            'quantity'      => $quantity,
                            'actual_price'  => $actualPrice,
                            'selling_price' => $sellingPrice,
                            'supplier'      => $customerID,
                            'depID'         => $depID,
                        ]);
                        $productId = $newProduct->id;
                    }
                } else {
                    // Update existing product by ID
                    $existingProduct = Products::find($productId);
                    if ($existingProduct) {
                        $existingProduct->quantity      += $quantity;
                        $existingProduct->actual_price   = $actualPrice;
                        $existingProduct->selling_price  = $sellingPrice;
                        $existingProduct->save();
                    }
                }
    
                /**
                 * PURCHASE HANDLING - FIXED SECTION
                 * Always create a new purchase record for petrol (id:4) and diesel (id:5)
                 * For other products, check for existing purchase with remaining > 0
                 */
                if (in_array($productId, [4, 5])) {
                    // Always create new purchase record for petrol and diesel
                    Purchases::create([
                        'proID'      => $productId,
                        'transID'    => $transaction->id,
                        'quantity'   => $quantity,
                        'unit_cost'  => $actualPrice,
                        'add_cost'   => $proportionalAddCost / $quantity,
                        'total_cost' => $productBaseCost + $proportionalAddCost,
                        'remaining'  => $quantity,
                        'supplier'   => $customerID,
                        'depID'      => $depID,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // For other products, use the existing logic
                    $existingPurchase = Purchases::where('proID', $productId)
                        ->where('supplier', $customerID)
                        ->where('remaining', '>', 0)
                        ->where('depID', $depID)
                        ->first();
    
                    if ($existingPurchase) {
                        $existingPurchase->quantity   += $quantity;
                        $existingPurchase->remaining  += $quantity;
                        $existingPurchase->unit_cost   = $actualPrice;
                        $existingPurchase->add_cost    = $proportionalAddCost / $quantity;
                        $existingPurchase->total_cost  = $productBaseCost + $proportionalAddCost;
                        $existingPurchase->transID     = $transaction->id;
                        $existingPurchase->save();
                    } else {
                        Purchases::create([
                            'proID'      => $productId,
                            'transID'    => $transaction->id,
                            'quantity'   => $quantity,
                            'unit_cost'  => $actualPrice,
                            'add_cost'   => $proportionalAddCost / $quantity,
                            'total_cost' => $productBaseCost + $proportionalAddCost,
                            'remaining'  => $quantity,
                            'supplier'   => $customerID,
                            'depID'      => $depID,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
    
            /**
             * SUPPLIER BALANCE
             */
            if ($balance > 0) {
                $supplier = Suppliers::find($customerID);
                if ($supplier) {
                    $supplier->balance += $balance;
                    $supplier->save();
                }
            }
    
            /**
             * ACCOUNTING
             */
            $CashAccount       = CashAccount::where('AccCode', 'Cash')->firstOrFail();
            $inventoryAccount  = CashAccount::where('AccCode', 'Inventory')->firstOrFail();
            $shortTerm         = CashAccount::where('AccCode', 'Short Term')->firstOrFail();
    
            $totalInventoryCost = 0;
            foreach ($products as $product) {
                $q   = $product['quantity'];
                $ap  = $product['actual_price'];
                $productBaseCost     = $ap * $q;
                $proportionalAddCost = ($productBaseCost / $totalBaseCost) * $add_cost;
                $totalInventoryCost += ($ap + ($proportionalAddCost / $q)) * $q;
            }
    
            $inventoryAccount->increment('debit', $totalInventoryCost);
    
            if ($balance > 0 && $paidAmount > 0) {
                $CashAccount->increment('credit', $paidAmount + $add_cost);
                $shortTerm->increment('credit', $balance);
            } elseif ($balance > 0) {
                $CashAccount->increment('credit', $add_cost);
                $shortTerm->increment('credit', $balance);
            } elseif ($paidAmount > 0) {
                $CashAccount->increment('credit', $paidAmount + $add_cost);
            }
    
            // Finance Transaction record
            FinanceTrans::create([
                'user'        => $UserID,
                'depID'       => $UserdepID,
                'depitAcc'    => 'Inventory Account',
                'depitAmount' => $totalInventoryCost,
                'creditAcc'   => $balance > 0 ? 'Accounts Payable' : 'Cash Accounts',
                'creditAmount'=> $balance > 0 ? $balance : ($paidAmount + $add_cost),
                'date'        => now(),
                'formType'    => 'Purchasing New Inventory',
                'action'      => 'Insert',
                'info'        => $User . ' Purchases from Supplier ' . $customerID . ' Net Total Of ' . $netPrice . ' Accounting Effected',
            ]);
    
            DB::commit();
    
            return redirect()->route('products')->with('status', 'Purchase transaction created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error occurred while creating purchase transaction: ' . $e->getMessage());
            return redirect()->route('products.add')->withError('An error occurred while creating the purchase transaction. Please try again.');
        }
    }

  public function edit($id)
{
    if (auth()->user()->role === 'admin') {
        $record = Products::findOrFail($id);
    } else {
        $record = Products::where('depID', auth()->user()->depID)
            ->findOrFail($id);
    }

    $suppliers = Suppliers::where('depID', $record->depID)->get();

    return view('layout.inventery.update', compact('record', 'suppliers'));
}

    public function update(Request $request, $id) {
           if (Auth::user()->role === 'acc') {
            return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
        }
            
        $request->validate([
            'name' =>'required|string',
            'type' =>'required|string',
            'status' =>'required',
           'selling_price' =>'required|numeric',
           'actual_price' =>'required|numeric',
        ]);
        if(auth()->user()->role == 'admin' && auth()->user()->role =='manager') {
        $products = Products::find($id);
        } else {
        $products = Products::where('depID', auth()->user()->depID)->find($id);
        }
        $products->name = $request->name;
        $products->type = $request->type;
        $products->status = $request->status;
        $products->selling_price = $request->selling_price;
        $products->actual_price = $request->actual_price;
        $products->info = $request->info;
        $products->save();

        return redirect()->route('products')->with('status', 'Product Updated Successfully!');
    }

    public function destroy($id)
    {
               if (Auth::user()->role === 'acc') {
                return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
            }
            
        try {
            // Find product depending on user role
            if (auth()->user()->role == 'admin' || auth()->user()->role == 'manager') {
                $product = Products::find($id);
            } else {
                $product = Products::where('depID', auth()->user()->depID)->find($id);
            }
    
            if (!$product) {
                return redirect()->back()->withErrors('Product not found or not accessible.');
            }
    
            $product->delete();
    
            return redirect()->back()->with('status', 'Product deleted successfully!');
    
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
    
            return redirect()->back()->withErrors('An error occurred while deleting the product.');
        }
    }

}
