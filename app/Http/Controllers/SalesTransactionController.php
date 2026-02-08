<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesTransactions;
use App\Models\Sales;
use App\Models\Customers;
use App\Models\Credits;
use App\Models\BankStatement;
use App\Models\CashAccount;
use App\Models\Products;
use App\Models\FinanceTrans;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Purchases;
use App\Models\User;

class SalesTransactionController extends Controller
{
    //
    //List all Pateints



   public function index(Request $request)
    {
        if ($request->ajax()) {
            // Base query with eager loading
            // $query = auth()->user()->role === 'admin' || auth()->user()->role === 'manager'
            //     ? SalesTransactions::with(['customer', 'department', 'sellerUser'])
            //     : SalesTransactions::where('depID', auth()->user()->depID)->with(['customer', 'department', 'sellerUser']);
            
             $user = auth()->user();
                    // Base query with eager loading
        if ($user->role === 'admin' || $user->role === 'manager' || $user->role === 'acc') {
            $query = SalesTransactions::with(['customer', 'department', 'sellerUser']);
        } else {
            $query = SalesTransactions::where('depID', $user->depID)
                                      ->where('seller', $user->id)
                                      ->with(['customer', 'department', 'sellerUser']);
        }
        
        
    
            // Apply default ordering by created_at desc if no other sorting is requested
            if (!$request->has('order.0.column')) {
                $query->orderBy('created_at', 'desc');
            }
    
            // Apply filters
            if ($request->input('name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'LIKE', "%{$request->input('name')}%");
                });
            }
    
            if ($request->input('phone')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('phone', 'LIKE', "%{$request->input('phone')}%");
                });
            }
    
            if ($request->input('type')) {
                $query->where('type', 'LIKE', "%{$request->input('type')}%");
            }
    
            if ($request->input('status')) {
                $query->where('status', 'LIKE', "%{$request->input('status')}%");
            }
    
            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('paid_date', [$request->input('startDate'), $request->input('endDate')]);
            }
        
         if ($request->input('seller')) {
                $query->where('seller', [$request->input('seller')]);
            }
            
            
            $totalData = $query->count();
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'customerID',
                2 => 'order_number',
                3 => 'type',
                4 => 'sub_total',
                5 => 'discount',
                6 => 'total_amount',
                7 => 'status',
                8 => 'order_date',
                9 => 'seller',
                10 => 'depID',
                11 => 'created_at'
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            
            // Only apply DataTables ordering if it was requested
            if ($request->has('order.0.column')) {
                $orderColumn = $columns[$request->input('order.0.column')];
                $dir = $request->input('order.0.dir');
    
                // Handle relationship ordering
                switch ($orderColumn) {
                    case 'customerID':
                        $query->leftJoin('customers', 'orders.customerID', '=', 'customers.id')
                              ->orderBy('customers.customer_name', $dir)
                              ->select('orders.*');
                        break;
                    case 'seller':
                        $query->leftJoin('users', 'orders.seller_id', '=', 'users.id')
                              ->orderBy('users.name', $dir)
                              ->select('orders.*');
                        break;
                    case 'depID':
                        $query->leftJoin('departments', 'orders.depID', '=', 'departments.id')
                              ->orderBy('departments.dep_name', $dir)
                              ->select('orders.*');
                        break;
                    default:
                        // For direct columns on the orders table
                        $query->orderBy($orderColumn, $dir);
                        break;
                }
            }
    
            $orders = $query->offset($start)
                           ->limit($limit)
                           ->get();
    
            $data = [];
            foreach ($orders as $order) {
                $nestedData['id'] = $order->id;
                $nestedData['customer'] = optional($order->customer)->customer_name ?? 'N/A';
                $nestedData['phone'] = optional($order->customer)->phone ?? 'N/A';
                $nestedData['order_number'] = $order->order_number;
                $nestedData['type'] = $order->type;
                $nestedData['sub_total'] = $order->sub_total;
                $nestedData['net_price'] = $order->net_price;
                $nestedData['paid_amount'] = $order->paid_amount;
                $nestedData['payment_method'] = $order->payment_method;
                $nestedData['discount'] = $order->discount;
                $nestedData['total_amount'] = $order->total_amount;
                $nestedData['status'] = $order->status;
                $nestedData['order_date'] = $order->order_date;
                $nestedData['seller'] = optional($order->sellerUser)->name ?? 'N/A';
                $nestedData['depID'] = optional($order->department)->dep_name ?? 'N/A';
                $nestedData['action'] = $order->id;
                $nestedData['created_at'] = $order->created_at ? $order->created_at->format('j-n-Y H:i') : 'N/A';
                
                $data[] = $nestedData;
            }
    
            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data
            ]);
        }
        
        $users = auth()->user()->role == 'sales' 
            ? User::where('id', auth()->id())->get()
            : User::all();
            
        return view('layout.cashPayment.index', compact('users'));
    }




    public function destroy(string $id){
        
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
    
        DB::beginTransaction(); // Start the transaction
    
        try {
            // Fetch the sales transaction to be deleted
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $transaction = SalesTransactions::findOrFail($id);
            } else {
                $transaction = SalesTransactions::where('depID', auth()->user()->depID)
                    ->where('id', $id)
                    ->firstOrFail();
            }
            $customerID = $transaction->customerID;
            $paidAmount = $transaction->paid_amount;
            $netPrice = $transaction->net_price;

            // Fetch related sales records
            $salesRecords = Sales::where('sales_transaction_id', $id)->get();

            $customer = Customers::find($customerID);
            if ($customer) {
                $customer->balance -= max($netPrice - $paidAmount, 0);
                $customer->save();
            }
            // Reverse Inventory Changes
            // Fetch financial accounts
            $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
            $inventoryAccount = CashAccount::where('AccCode', 'Inventory')->firstOrFail();
            $revenue = CashAccount::where('AccCode', 'Revenue')->firstOrFail();

            // Loop through each sold product to reverse the sale
            foreach ($transaction->sales as $sale) {
                $productModel = Products::find($sale->proID);

                if ($productModel && $productModel->type != 'Service') {
                    // Restore product quantity in inventory
                    $productModel->increment('quantity', $sale->quantity);

                    $remainingQtyToRestore = $sale->quantity;
                    $totalCostReversed = 0;

                    // Reverse the deducted purchases (LIFO - Latest purchase first for refund)
                    $purchases = Purchases::where('proID', $productModel->id)
                        ->orderBy('created_at', 'desc')  // LIFO to restore
                        ->get();

                    foreach ($purchases as $purchase) {
                        if ($remainingQtyToRestore <= 0) {
                            break;  // Fully restored
                        }

                        $purchaseUnitCost = $purchase->unit_cost + $purchase->add_cost;

                        // Calculate how much stock can be restored
                        $availableCapacity = $purchase->quantity - $purchase->remaining;

                        if ($availableCapacity >= $remainingQtyToRestore) {
                            // Restore quantity and cost
                            $purchase->increment('remaining', $remainingQtyToRestore);
                            $totalCostReversed += $remainingQtyToRestore * $purchaseUnitCost;
                            $remainingQtyToRestore = 0;
                        } else {
                            // Restore as much as possible
                            $purchase->increment('remaining', $availableCapacity);
                            $totalCostReversed += $availableCapacity * $purchaseUnitCost;
                            $remainingQtyToRestore -= $availableCapacity;
                        }
                    }

                    // If some quantity could not be restored, log the issue
                    if ($remainingQtyToRestore > 0) {
                        \Log::warning('Unable to fully restore stock for product: ' . $productModel->name);
                    }

                    // Reverse financial accounts with actual cost
                    $inventoryAccount->decrement('credit', $totalCostReversed);  // Restore inventory value
                    $CashAccount->decrement('debit', $sale->total_price);        // Reduce cash
                    $revenue->decrement('credit', $sale->total_price);           // Reduce revenue
                }
            }

            // Log the reversal in financial transactions
            FinanceTrans::create([
                'user' => Auth::id(),
                'depitAcc' => 'Revenue & Inventory Accounts',
                'depitAmount' => $netPrice,
                'creditAcc' => 'Cash Account',
                'creditAmount' => $paidAmount,
                'date' => now(),
                'formType' => 'POS - Sales Reversal',
                'action' => 'Reverse | Delete',
                'info' => Auth::user()->username . ' reversed the sale transaction ID: ' . $transaction->id,
            ]);


            // Finally, delete the main sales transaction record
            $transaction->delete();

            // Commit the transaction
            DB::commit();

            // Redirect with a success message
            return redirect('/salesTransactions')->with(['status' => 'Sales transaction successfully deleted.']);
        } catch (\Exception $e) {
            // Rollback the transaction if there's an error
            DB::rollBack();

            // Log the error for debugging purposes
            \Log::error('Sales Transaction Deletion Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);

            // Redirect back with an error message
            return redirect()->back()->withErrors('Something went wrong while deleting the sales transaction: ' . $e->getMessage());
        }
    }

    // Invoice
    public function invoice(string $id) {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $transaction = SalesTransactions::find($id);
        } else {
            $transaction = SalesTransactions::where('depID', auth()->user()->depID)
                ->where('id', $id)
                ->firstOrFail();
        }
        return view('layout.cashPayment.invoice', compact('transaction'));
    }

}
