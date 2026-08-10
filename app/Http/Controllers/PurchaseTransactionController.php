<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseTransactions;
use App\Models\Purchases;
use App\Models\Suppliers;
use App\Models\Credits;
use App\Models\Departments;
use App\Models\BankStatement;
use App\Models\FinanceTrans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Products;
use App\Models\CashAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Carbon\Carbon;

class PurchaseTransactionController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager' || auth()->user()->role == 'acc') {
                $query = PurchaseTransactions::query();
            } else {
                $query = PurchaseTransactions::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('name')) {
                $query->where('customer_name', 'LIKE', "%{$request->input('name')}%");
            }
            if ($request->input('phone')) {
                $query->where('phone', 'LIKE', "%{$request->input('phone')}%");
            }
            if ($request->input('type')) {
                $query->where('type', 'LIKE', "%{$request->input('type')}%");
            }
            if ($request->input('payment_method')) {
                $query->where('payment_method', 'LIKE', "%{$request->input('payment_method')}%");
            }
            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('paid_date', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'customerID',
                2 => 'type',
                3 => 'subTotal',
                4 => 'discount',
                5 => 'net_price',
                6 => 'add_cost',
                6 => 'paidAmount',
                7 => 'balance',
                8 => 'date',
                9 => 'payMethod',
                10 => 'reference',
                11 => 'purchased',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $payments = $query->get();

        $data = [];
        foreach ($payments as $pay) {
        $nestedData['id'] = $pay->id;
        $nestedData['customer'] = $pay->customer ? $pay->customer->name : 'N/A';
        $nestedData['phone'] = $pay->customer ? $pay->customer->phone : 'N/A';
        $nestedData['type'] = $pay->type;
        $nestedData['subTotal'] = $pay->subTotal;
        $nestedData['discount'] = $pay->discount;
        $nestedData['net_price'] = $pay->net_price;
        $nestedData['add_cost'] = $pay->add_cost;
        $nestedData['paidAmount'] = $pay->paidAmount;
        $nestedData['balance'] = $pay->balance;
        $nestedData['date'] = $pay->date;
        $nestedData['purchased'] = $pay->user ? $pay->user->name : 'N/A';
        $nestedData['payMethod'] = $pay->payMethod;
        $nestedData['action'] = $pay->id;
    
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

        return view('layout.purchases.trans');
    }

    public function destroy(string $id)
    {
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
        DB::beginTransaction(); // Start the transaction
    
        try {
            // Fetch the transaction to be deleted
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager')  {
            $transaction = PurchaseTransactions::findOrFail($id);
        }else {
            $transaction = PurchaseTransactions::where('depID', auth()->user()->depID)->findOrFail($id);
        }
            $customerID = $transaction->customerID;
            $paidAmount = $transaction->paidAmount;
            $balance = $transaction->balance;
            $addCost = $transaction->add_cost;
    
            // Fetch the related purchases
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager')  {
            $purchases = Purchases::where('transID', $id)->get();
            } else {
                $purchases = Purchases::where('depID', auth()->user()->depID)->where('transID', $id)->get();
            }
    
            // Prevent product quantity from becoming negative
            foreach ($purchases as $purchase) {
                $product = Products::find($purchase->proID);
                if ($product && $product->quantity < $purchase->quantity) {
                    throw new \Exception('Cannot delete transaction. Product quantity is less than the purchase quantity.');
                } elseif ($purchase->remaining < $purchase->quantity) {
                    throw new \Exception('Cannot delete transaction. Remaining quantity is less than the purchase quantity.');
                }
            }
    
            // Calculate total inventory cost (including additional cost)
            $totalInventoryCost = 0;
    
            foreach ($purchases as $purchase) {
                $product = Products::find($purchase->proID);
                $totalUnitCost = $purchase->unit_cost + $purchase->add_cost; // Unit cost + Additional cost
                $totalInventoryCost += $totalUnitCost * $purchase->quantity;
    
                // Revert product quantity
                if ($product) {
                    $product->quantity -= $purchase->quantity;
                    $product->save();
                }
    
                // Delete the purchase record
                $purchase->delete();
            }
    
            // Prevent supplier balance from going negative
            if ($balance < 0 && $paidAmount > 0) {
                throw new \Exception('Cannot delete transaction. Supplier balance is less than the transaction amount! Please check if you paid any liability.');
            }
    
            // Reverse Supplier Balance if applicable
            if ($balance > 0) {
                $supplier = Suppliers::find($customerID);
                if ($supplier) {
                    $supplier->balance -= $balance;
                    $supplier->save();
                }
            }
    
            // Adjust Accounting Entries
            $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
            $inventoryAccount = CashAccount::where('AccCode', 'Inventory')->firstOrFail();
            $shortTerm = CashAccount::where('AccCode', 'Short Term')->firstOrFail();
    
            // Reverse the correct inventory debit (including additional cost)
            $inventoryAccount->decrement('debit', $totalInventoryCost);
    
            // Reverse Cash and Short Term Credits
            if ($balance > 0 && $paidAmount > 0) {
                $CashAccount->decrement('credit', $paidAmount + $addCost);
                $shortTerm->decrement('credit', $balance);
            } elseif ($balance > 0) {
                $shortTerm->decrement('credit', $balance);
            } elseif ($paidAmount > 0) {
                $CashAccount->decrement('credit', $paidAmount + $addCost);
            }
    
            // Log the reversal of the finance transaction
            $User = Auth::user()->username;
            $UserID = Auth::user()->id;
    
            FinanceTrans::create([
                'user' => $UserID,
                'depitAcc' => 'Cash Accounts',
                'depitAmount' => $paidAmount + $addCost,
                'creditAcc' => 'Inventory Account',
                'creditAmount' => $totalInventoryCost,
                'date' => now(),
                'formType' => 'Reverse Purchase Transaction',
                'action' => 'Delete',
                'info' => $User . ' reversed purchase transaction for CustomerID: ' . $customerID . ' Amount: ' . $totalInventoryCost . ' Accounting Effected',
            ]);
    
            // Delete the main transaction record
            $transaction->delete();
    
            // Commit the transaction
            DB::commit();
    
            // Redirect with a success message
            return redirect()->route('purchaseTransactions')->with('status', 'Purchase transaction deleted successfully!');
        } catch (\Exception $e) {
            // Rollback the transaction if there's an error
            DB::rollBack();
    
            // Log the error for debugging purposes
            Log::error('Error deleting purchase transaction: ' . $e->getMessage());
    
            // Redirect back with an error message
            return redirect()->route('purchaseTransactions')->withErrors(' An error occurred while deleting the purchase transaction. Please try again.');
        }
    }
    

    public function invoice(string $id) {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager')  {
            $transaction = PurchaseTransactions::with(['purchases.pro', 'supplier'])->find($id);
        } else {
            $transaction = PurchaseTransactions::with(['purchases.pro', 'supplier'])->where('depID', auth()->user()->depID)->find($id);
        }
        return view('layout.purchases.invoice', compact('transaction'));
    }

}
