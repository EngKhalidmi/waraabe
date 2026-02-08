<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\AccountPayables;
use App\Models\AccountingTransaction;
use App\Models\CashAccount;
use App\Models\Suppliers;
use App\Models\FinanceTrans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountPayablesController extends Controller
{
    // 
    public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager' || auth()->user()->role == 'acc') {
            $query = AccountPayables::query();
            } else {
                $query = AccountPayables::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('received_from')) {
                $query->where('received_from', 'LIKE', "%{$request->input('received_from')}%");
            }
    
            if ($request->input('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->input('transaction')) {
                $query->where('transaction_type', $request->input('transaction'));
            }

            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('created_at', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'received_from',
                2 => 'amount',
                3=> 'discount',
                4 => 'pbalance',
                5 => 'current',
                6 => 'depID',
                7 => 'type',
                8 => 'transaction_type',
                9 => 'date',
                10 => 'description',
                // 11 => 'user,
                12 => 'depID',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $liability = $query->get();

        $data = [];
        foreach ($liability as $dayn) {
            $nestedData['id'] = $dayn->id;
            $nestedData['name'] = optional($dayn->supplier)->name ?? 'N/A';
            $nestedData['amount'] = $dayn->amount;
            $nestedData['discount'] = $dayn->discount;
            $nestedData['pbalance'] = $dayn->pbalance;
            $nestedData['current'] = $dayn->current;
            $nestedData['type'] = $dayn->type;
            $nestedData['transaction'] = $dayn->transaction_type;
            $nestedData['date'] = $dayn->date;
            $nestedData['description'] = $dayn->description;
            // $nestedData['user'] = $dayn->user ? $dayn->users->name : 'N/A';
            $nestedData['depID'] = $dayn->department->name;
            $nestedData['action'] = $dayn->id;

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
        if (auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $suppliers = Suppliers::get();
        } else {
            $suppliers = Suppliers::where('depID', auth()->user()->depID)->get();
        }
        return view('layout.liability.index', compact('suppliers'));
    }
    
    public function create(){
        return view('layout.liability.add');
    }

    public function searchSupplier(Request $request){
        $query = $request->get('query');
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $customers = Suppliers::where('name', 'like', '%' . $query . '%')->get(['id', 'name','balance']);
        } else {
            $customers = Suppliers::where('depID', auth()->user()->depID)->where('name', 'like', '%' . $query . '%')->get(['id', 'name','balance']);
        }
        return response()->json($customers);
    }
    
   

    public function store(Request $request)
    {
        
    if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        $request->validate([
            'received_from' => 'required',
            'amount' => 'required|numeric', 
            'pbalance' => 'required|numeric', 
            'current' => 'required|numeric', 
            'discount' => 'required|numeric', 
            'transaction_type' => 'required|string',
            'date' => 'required',
        ]);
    
        DB::beginTransaction(); // Start the transaction
    
        try {
            // findOrfail the supplier
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $supplier = Suppliers::where('id', $request->input('received_from'))->first();
            } else {
                $supplier = Suppliers::where('id', $request->input('received_from'))->where('depID', auth()->user()->depID)->first();
            }
    
            // prevent negative balance
            // prevent negative current balance
            // if($request->input('current') < 0) {
            //     throw new \Exception('Current balance cannot be negative.');
            // }

             $User = Auth::user()->username;
            $UserID = Auth::user()->id;
            $depID = Auth::user()->depID;
            $type = "Short Term";

            $payables = [
                'received_from' => $request->input('received_from'),
                'amount' => $request->input('amount'),
                'discount' => $request->input('discount'),
                'pbalance' => $request->input('pbalance'),
                'current' => $request->input('current'),
                'type' => $type,
                'date' => $request->input('date'),
                'depID' => $supplier->depID,
                'account' => 'Cash & Cash Equivalent',
                'transaction_type' => $request->input('transaction_type'),
                'description' => $request->input('description'),
                'user' => $UserID,
            ];
    
            // Create a purchase entry
            $payable = AccountPayables::create($payables);
    
            // Fetch Accounts
            $cashAccount = CashAccount::where('AccCode', 'Cash')->first();
            $longTerm = CashAccount::where('AccCode', 'Long Term')->first();
            $shortTerm = CashAccount::where('AccCode', 'Short Term')->first();
    
            $amount = $request->input('amount');
            if($request->input('transaction_type') == 'Credit'){
                if($request->type == 'Long Term') {
                    $cashAccount->increment('debit', $amount);
                    $longTerm->increment('credit', $amount);
                }elseif($request->type == 'Short Term') {
                    $cashAccount->increment('debit', $amount);
                    $shortTerm->increment('credit', $amount);
                }
            }elseif($request->input('transaction_type') == 'Debit') {
                if($request->type == 'Long Term') {
                    $cashAccount->increment('credit', $amount);
                    $longTerm->increment('debit', $amount);
                } elseif($request->type == 'Short Term') {
                    $cashAccount->increment('credit', $amount);
                    $shortTerm->increment('debit', $amount);
                }
            }
            

            // Update Suppliers balance
            $paid = $amount + $request->input('discount');
            if($request->transaction_type == 'Debit') {
                // $supplier = Suppliers::where('name', $request->input('received_from'))->first();
                $supplier->decrement('balance', $paid);
            }else {
                $supplier->increment('balance', $amount);
            }
            

            
            if ($request->transaction_type == 'Debit') {
                FinanceTrans::create([
                    'user' => $UserID,
                    'depID' => $depID,
                    'depitAcc' => 'Liability Account',
                    'depitAmount' => $amount,
                    'creditAcc' => 'Cash Accounts',
                    'creditAmount' => $amount,
                    'formType' => 'Liability Form',
                    'action' => 'Insert',
                    'date' => now(),
                    'info' => $User . ' Register '.  $request->received_from . 'Amount Of' . $amount . ' Accounting effected',
                ]); 
            }else {
                FinanceTrans::create([
                    'user' => $UserID,
                    'depID' => $depID,
                    'depitAcc' => 'Liability Accounts',
                    'depitAmount' => $amount,
                    'creditAcc' => 'Cash Account',
                    'creditAmount' => $amount,
                    'formType' => 'Liability Form',
                    'action' => 'Insert',
                    'date' => now(),
                    'info' => $User . ' Register '.  $request->received_from . 'Amount Of' . $amount . ' Accounting effected',
                ]);
            }

    
            DB::commit(); // Commit the transaction
    
            return redirect()->route('account_payables')->with('status', 'Liability Recorded Successfully');
    
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if there's an error
            // display errors the laravel.log file
            Log::error('Failed to add Supplier Credit: '. $e->getMessage());
            return redirect()->back()->withErrors('Failed to add Supplier Credit: ' . $e->getMessage());
        }
    }


    public function destroy(string $id)
    {
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        DB::beginTransaction(); // Start the transaction
        
        try {
            // Fetch the payable entry to be deleted
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $suppliersCredit = AccountPayables::findOrFail($id);
            } else {
                $suppliersCredit = AccountPayables::where('id', $id)->where('depID', auth()->user()->depID)->first();
            }
            // Fetch Accounts
            $cashAccount = CashAccount::where('AccCode', 'Cash')->first();
            $longTerm = CashAccount::where('AccCode', 'Long Term')->first();
            $shortTerm = CashAccount::where('AccCode', 'Short Term')->first();
    
            $amount = $suppliersCredit->amount;
            $transactionType = $suppliersCredit->transaction_type;
            $type = $suppliersCredit->type;
    
            // Reverse the transaction for Cash Account and respective term account
            if ($transactionType == 'Credit') {
                if ($type == 'Long Term') {
                    $cashAccount->decrement('debit', $amount);
                    $longTerm->decrement('credit', $amount);
                } elseif ($type == 'Short Term') {
                    $cashAccount->decrement('debit', $amount);
                    $shortTerm->decrement('credit', $amount);
                }
            } elseif ($transactionType == 'Debit') {
                if ($type == 'Long Term') {
                    $cashAccount->decrement('credit', $amount);
                    $longTerm->decrement('debit', $amount);
                } elseif ($type == 'Short Term') {
                    $cashAccount->decrement('credit', $amount);
                    $shortTerm->decrement('debit', $amount);
                }
            }
    
            // Update Suppliers balance
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $supplier = Suppliers::where('name', $suppliersCredit->received_from)->first();
            } else {
                $supplier = Suppliers::where('name', $suppliersCredit->received_from)->where('depID', auth()->user()->depID)->first();
            }
            if ($supplier) {
                if ($transactionType == 'Debit') {
                    $supplier->increment('balance', $amount);
                } else {
                    $supplier->decrement('balance', $amount);
                }
            }
    
            $User = Auth::user()->username;
            $UserID = Auth::user()->id;
            $depID = Auth::user()->depID;
            if ($transactionType == 'Debit') {
                FinanceTrans::create([
                    'user' => $UserID,
                    'depID' => $depID,
                    'depitAcc' => 'Liability Account',
                    'depitAmount' => $amount,
                    'creditAcc' => 'Cash Accounts',
                    'creditAmount' => $amount,
                    'formType' => 'Liability Form',
                    'action' => 'Deleted',
                    'date' => now(),
                    'info' => $User . ' Delete '.  $suppliersCredit->received_from . 'Amount Of' . $amount . ' Accounting effected',
                ]);
            }else {
                FinanceTrans::create([
                    'user' => $UserID,
                    'depID' => $depID,
                    'depitAcc' => 'Liability Accounts',
                    'depitAmount' => $amount,
                    'creditAcc' => 'Cash Account',
                    'creditAmount' => $amount,
                    'formType' => 'Liability Form',
                    'action' => 'Delete',
                    'date' => now(),
                    'info' => $User . ' Deleted '.  $suppliersCredit->received_from . 'Amount Of' . $amount . ' Accounting effected',
                ]);
            }
            // Delete the payable entry
            $suppliersCredit->delete();
    
            DB::commit(); // Commit the transaction
    
            return redirect()->route('account_payables')->with('status', 'Liability Record Transaction Deleted Successfully');
            
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if there's an error
            return redirect()->back()->withErrors('Failed to delete Supplier Credit: ' . $e->getMessage());
        }
    }
        

}
