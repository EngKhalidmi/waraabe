<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesTransactions;
use App\Models\Credits;
use App\Models\Customers;
use App\Models\User;
use App\Models\BankStatement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\CashAccount;
use App\Models\FinanceTrans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;


class CreditsController extends Controller
{
    //List all Pateints
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Base query with eager loading
            $query = auth()->user()->role === 'admin' || auth()->user()->role === 'manager' || auth()->user()->role === 'acc'
                ? Credits::query()->with(['customer', 'department', 'sellerUser'])
                : Credits::where('depID', auth()->user()->depID)->with(['customer', 'department', 'sellerUser']);

            // Filters
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

            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('date', [$request->input('startDate'), $request->input('endDate')]);
            }
            
        if ($request->input('seller')) {
                $query->where('seller', [$request->input('seller')]);
            }

            $totalData = $query->count();
            $totalFiltered = $totalData;

            $columns = [
                0 => 'id',
                1 => 'customer_name',
                2 => 'phone',
                3 => 'pbalance',
                4 => 'discount',
                5 => 'amount',
                6 => 'current',
                7 => 'depID',
                8 => 'payment_method',
                9 => 'seller',
                10 => 'created_at',
                11 => 'action'
            ];

            $limit = $request->input('length');
            $start = $request->input('start');

            // ALWAYS apply the default ordering first (newest first)
            $query->orderBy('created_at', 'desc');

            // Then apply the requested ordering if specified
            if ($request->has('order.0.column')) {
                $colIndex = $request->input('order.0.column');
                $dir = $request->input('order.0.dir', 'desc');

                if (isset($columns[$colIndex])) {
                    $requestedOrder = $columns[$colIndex];

                    switch ($requestedOrder) {
                        case 'customer_name':
                            $query->leftJoin('customers', 'credits.customer_id', '=', 'customers.id')
                                ->orderBy('customers.customer_name', $dir)
                                ->select('credits.*');
                            break;
                        case 'phone':
                            $query->leftJoin('customers', 'credits.customer_id', '=', 'customers.id')
                                ->orderBy('customers.phone', $dir)
                                ->select('credits.*');
                            break;
                        case 'depID':
                            $query->leftJoin('departments', 'credits.depID', '=', 'departments.id')
                                ->orderBy('departments.name', $dir)
                                ->select('credits.*');
                            break;
                        case 'seller':
                            $query->leftJoin('users', 'credits.seller', '=', 'users.id')
                                ->orderBy('users.name', $dir)
                                ->select('credits.*');
                            break;
                        default:
                            $query->orderBy($requestedOrder, $dir);
                            break;
                    }
                }
            }

            $credits = $query->offset($start)
                            ->limit($limit)
                            ->get();

            $data = [];
            foreach ($credits as $pay) {
                $nestedData['id'] = $pay->id;
                $nestedData['name'] = optional($pay->customer)->customer_name ?? 'N/A';
                $nestedData['phone'] = optional($pay->customer)->phone ?? 'N/A';
                $nestedData['pbalance'] = $pay->pbalance;
                $nestedData['discount'] = $pay->discount;
                $nestedData['amount'] = $pay->amount;
                $nestedData['current'] = $pay->current;
                $nestedData['payment_method'] = $pay->payment_method;
                $nestedData['depID'] = optional($pay->department)->name ?? 'N/A';
                $nestedData['seller'] = optional($pay->sellerUser)->name ?? 'N/A';
                $nestedData['action'] = $pay->id;
                $nestedData['created_at'] = $pay->created_at ? $pay->created_at->format('j-n-Y H:i') : 'N/A';
                $data[] = $nestedData;
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data
            ]);
        }

        // Non-ajax view load
        $clients = auth()->user()->role === 'admin' || auth()->user()->role === 'manager'
            ? Customers::get()
            : Customers::where('depID', auth()->user()->depID)->get();
            $users = User::where('role', 'sales')->get();

        return view('layout.creditPayment.index', compact('clients', 'users'));
    }

    public function create(){
        if(auth()->user()->role === 'admin' || auth()->user()->role ==='manager'){
            $customers = Customers::get();
            } else {
                $customers = Customers::where('depID', auth()->user()->depID)->get();
            }
        return view('layout.creditPayment.add', compact( 'customers'));
    }

    public function searchCustomer(Request $request){
        $query = $request->get('query');
    
        $searchQuery = Customers::where(function ($q) use ($query) {
            $q->where('customer_name', 'like', '%' . $query . '%')
              ->orWhere('phone', 'like', '%' . $query . '%');
        });
    
        if (auth()->user()->role === 'admin' || auth()->user()->role === 'manager') {
            $customers = $searchQuery->get(['id', 'customer_name', 'balance']);
        } else {
            $customers = $searchQuery->where('depID', auth()->user()->depID)
                                     ->get(['id', 'customer_name', 'balance']);
        }
    
        return response()->json($customers);
    }
    

    public function store(Request $request)
    {
        
    if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        // Validate the incoming request data
        $request->validate([
            'customerID' => 'required',
            'amount' => 'required',
            'type' => 'required',
            'date' => 'required',
            'pbalance' => 'required',
            'payment_method' => 'required|string',
            'discount' => 'nullable',
        ]);
    
        try {
            // Start a database transaction
            DB::beginTransaction();
            // find supplier details
            if(auth()->user()->role === 'admin' || auth()->user()->role ==='manager'){
                $customer = Customers::find($request->customerID);
            } else {
                $customer = Customers::where('depID', auth()->user()->depID)->where('id', $request->customerID)->first();
            }
        $current = 0;
        if($request->type == 'Debit') {
            $current = $request->pbalance - $request->discount - $request->amount;
        }else {
            $current = $request->pbalance + $request->amount;
        }
        // Create a new credit transaction
        $user_id = auth()->user()->id;
        $credit = new Credits();
        $credit->customerID = $request->customerID;
        $credit->amount = $request->amount;
        $credit->type = $request->type;
        $credit->date = $request->date;
        $credit->pbalance = $request->pbalance;
        $credit->discount = $request->discount;
        $credit->current = $current;
        $credit->depID = $customer->depID;
         $credit->seller = $user_id;
        $credit->payment_method = $request->payment_method;
        
        $credit->save();

        // Get paid amount from request
        $amount = (float) $request->input('amount');
        $balanceInfo = $amount + (float) $request->input('discount');

        // Prevent if balanceinfo is greater than customer's balance
        

            
    
        // Update Customers balance
        if($request->type == 'Debit') {
            // $customer = Customers::where('customer_name', $request->input('customer_name'))->first();
            if($balanceInfo > (float) $request->input('pbalance')){
                DB::rollBack();
                return redirect()->back()->withErrors('Balance is greater than customer balance.');
            }
            $customer->decrement('balance', $balanceInfo);
        }else {
            // $customer = Customers::where('customer_name', $request->input('customer_name'))->first();
            $customer->increment('balance', $amount);
        }
        // Fetch the accounts
        if($request->type == 'Debit') {
                $cashAccount = CashAccount::where('AccCode', 'Cash')->first();
                $revenueAccount = CashAccount::where('AccCode', 'Revenue')->first();       
                // Check if accounts exist
            if (!$cashAccount || !$revenueAccount) {
                DB::rollBack();
                return redirect()->back()->withErrors('Cash Account or Revenue Account Not Found.');
            }


            $revenueAccount->increment('credit', $amount);
            $cashAccount->increment('debit', $amount);
            $cashAccount->date = now(); 
            $cashAccount->save();
            $revenueAccount->date = now();
            $revenueAccount->save();

            $User = Auth::user()->username;
            $UserID = Auth::user()->id;
            $userDep = Auth::user()->depID;
                    // Create a Finance Transaction record
        FinanceTrans::create([
            'user' => $UserID,
            'depID' => $userDep,
            'depitAcc' => 'Cash Account',
            'depitAmount' => $amount,
            'creditAcc' => 'Revenue Accounts',
            'creditAmount' => $amount,
            'date' => now(),
            'formType' => 'Receive Credits',
            'action' => 'Insert',
            'info' => $User . ' Received From '.  $request->customerID . 'Amount Of' . $amount . ' Accounting Effected',
        ]);
                // Commit the transaction if all operations succeed
                DB::commit();

                // Redirect to the credits index route with a success message
                return redirect()->route('credits')->with('status', 'Credit Received Successfully');
        }else {

                // Commit the transaction if all operations succeed
                DB::commit();
        
                // Redirect to the credits index route with a success message
                return redirect()->route('credits')->with('status', 'Credit registered & Balance Updated Successfully');
        }


    
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollBack();
    
            // Log the error for debugging purposes
            Log::error('Error processing credit transaction: ' . $e->getMessage());
    
            // Redirect back with an error message
            return redirect()->back()->withErrors('An error occurred while processing the credit transaction.');
        }
    }
    
    
    public function destroy(string $id)
    {
        
        if (Auth::user()->role === 'acc') {
            return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
        }
    
        try {
            // Start a database transaction
            DB::beginTransaction();
    
            // Fetch the credit transaction to be deleted
            if(auth()->user()->role === 'admin' || auth()->user()->role ==='manager'){
                $creditTransaction = Credits::findOrFail($id);
            } else {
                $creditTransaction = Credits::where('depID', auth()->user()->depID)->where('id', $id)->first();
            }
    
            // Get the necessary transaction details
            $amount = (float) $creditTransaction->amount;
            $transactionType = $creditTransaction->type;
    
            // Fetch the customer record
            if(auth()->user()->role === 'admin' || auth()->user()->role ==='manager'){
                $customer = Customers::findOrFail($creditTransaction->customerID);
            } else {
                $customer = Customers::where('depID', auth()->user()->depID)->where('id', $creditTransaction->customerID)->first();
            }
            
            // Reverse the customer's balance update
            $balanceInfo = $amount + (float) $creditTransaction->discount;
            if ($transactionType == 'Debit') {
                $customer->increment('balance', $balanceInfo);
            } else {
                $customer->decrement('balance', $amount);
            }
    
            // Reverse the accounting entries
            if ($transactionType == 'Debit') {
                $cashAccount = CashAccount::where('AccCode', 'Cash')->first();
                $revenueAccount = CashAccount::where('AccCode', 'Revenue')->first();
    
                // Check if accounts exist
                if (!$cashAccount || !$revenueAccount) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Cash Account or Revenue Account Not Found.');
                }
    
                // Reverse the accounting records
                $revenueAccount->decrement('credit', $amount);
                $cashAccount->decrement('debit', $amount);
                $cashAccount->date = now();
                $cashAccount->save();
                $revenueAccount->date = now();
                $revenueAccount->save();
    
                // Log the reversed finance transaction
                $User = Auth::user()->username;
                $UserID = Auth::user()->id;
                $userDep = Auth::user()->depID;
    
                FinanceTrans::create([
                    'user' => $UserID,
                    'depID' => $userDep,
                    'depitAcc' => 'Revenue Accounts',
                    'depitAmount' => $amount,
                    'creditAcc' => 'Cash Account',
                    'creditAmount' => $amount,
                    'date' => now(),
                    'formType' => 'Reverse Credits',
                    'action' => 'Delete',
                    'info' => $User . ' Reversed From CustomerID: ' . $creditTransaction->customerID . ' Amount: ' . $amount . ' Accounting Effected',
                ]);
            }
    
            // Delete the credit transaction record
            $creditTransaction->delete();
    
            // Commit the transaction if all operations succeed
            DB::commit();
    
            // Redirect with a success message
            return redirect()->route('credits')->with('status', 'Credit Transaction Deleted Successfully');
        } catch (\Exception $e) {
            // Rollback the transaction if there's an error
            DB::rollBack();
    
            // Log the error for debugging purposes
            Log::error('Error deleting credit transaction: ' . $e->getMessage());
    
            // Redirect back with an error message
            return redirect()->back()->withErrors('An error occurred while deleting the credit transaction.');
        }
    }
      
}
