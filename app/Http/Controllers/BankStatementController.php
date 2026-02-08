<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\BankStatement;
use App\Models\CashAccount;
use App\Models\FinanceTrans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AccountingTransaction;


class BankStatementController extends Controller
{
        //List all Pateints
    public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager' || auth()->user()->role == 'acc') {
            $query = BankStatement::query();
            } else {
                $query = BankStatement::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('type')) {
                $query->where('type', 'LIKE', "%{$request->input('type')}%");
            }
    
            if ($request->input('description')) {
                $query->where('description', $request->input('description'));
            }

            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('created_at', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'type',
                2 => 'amount',
                3 => 'description',
                4 => 'depID',
                5 => 'date',
                6 => 'check_no',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $bankStatement = $query->get();

        $data = [];
        foreach ($bankStatement as $bank) {
            $nestedData['id'] = $bank->id;
            $nestedData['type'] = $bank->type;
            $nestedData['amount'] = $bank->amount;
            $nestedData['description'] = $bank->description;
            $nestedData['depID'] = $bank->department->name;
             $nestedData['date'] = $bank->date;
             $nestedData['check_no'] = $bank->check_no;
            $nestedData['action'] = $bank->id;

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

        return view('layout.bankStatement.index');
    }

    public function create(){
        return view('layout.bankStatement.add');
    }
        
    
    public function store(Request $request) {
        
    if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        // Validate the incoming request data
        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|string|in:Debit,Credit', // Added validation for specific values
            'description' => 'required|string',
            'date' => 'required|date',
        ]);
    
        DB::beginTransaction();
    
        try {
            $amount = $request->input('amount');
            $type = $request->input('type');
            $description = $request->input('description');
            $date = $request->input('date');
            $check_no = $request->input('check_no');
            
            $User = Auth::user()->username;
            $UserID = Auth::user()->id;
            $depID = Auth::user()->depID;
    
            // Fetch Cash and Bank accounts
            $cashAccount = CashAccount::where('AccCode', 'Cash')->first();
            $bankAccount = CashAccount::where('AccCode', 'Bank')->first();
    
            if (!$cashAccount || !$bankAccount) {
                throw new \Exception('Cash or Bank account not found');
            }
    
            if ($type === 'Debit') {
                // Debit Logic: Increase Cash Account and Decrease Bank Account
                $cashAccount->increment('credit', $amount);
                $bankAccount->increment('debit', $amount);
            } elseif ($type === 'Credit') {
                // Credit Logic: Decrease Cash Account and Increase Bank Account
                $cashAccount->increment('debit', $amount);
                $bankAccount->increment('credit', $amount);
            }
    
            // Save the bank statement with all required fields including depID
            BankStatement::create([
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'depID' => $depID,
                 'date' => $date, 
                 'check_no' => $check_no, 
            ]);
    
            // Create a Finance Transaction record
            if ($type == 'Debit') {
                FinanceTrans::create([
                    'user' => $UserID,
                    'depID' => $depID,
                    'depitAcc' => 'Bank Account',
                    'depitAmount' => $amount,
                    'creditAcc' => 'Cash Accounts',
                    'creditAmount' => $amount,
                    'date' => now(),
                    'formType' => 'Bank Statements',
                    'action' => 'Insert',
                    'info' => $User . ' Debited '.  $amount . ' Accounting Effected',
                ]);
            } else {
                FinanceTrans::create([
                    'user' => $UserID,
                    'depID' => $depID,
                    'depitAcc' => 'Cash Accounts',
                    'depitAmount' => $amount,
                    'creditAcc' => 'Bank Account',
                    'creditAmount' => $amount,
                    'date' => now(),
                    'formType' => 'Bank Statements',
                    'action' => 'Insert',
                    'info' => $User . ' Withdraw '.  $amount . ' Accounting Effected',
                ]);
            }
    
            DB::commit();
            
            if ($type == 'Debit') {
                return redirect('bankStatement')->with('status', '' . $amount . ' Debited Bank Account Successfully');
            } else {
                return redirect('bankStatement')->with('status', '' . $amount . ' Withdraw Bank Account Successfully');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
        
            // Log the error (optional)
            Log::error('Bank Registration Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
        
            return redirect()->back()->withErrors(['error' => 'An error occurred while registering the Bank Record: ' . $e->getMessage()]);
        }
    }


    // Delete Assets data
    public function destroy(int $id) {
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        DB::beginTransaction();
        try {
            // Find the Payment record by ID
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $Assets = BankStatement::findOrFail($id);
            }else {
                $Assets = BankStatement::where('depID', auth()->user()->depID)->findOrFail($id);
            }
    
            // Accumulate the total paid amount and other fees for all students
            $total = $Assets->amount;
            // Update accounting records
            $User = Auth::user()->username;
            $AssetAccount = CashAccount::where('AccCode', 'Bank')->firstOrFail();
            $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
        
            // Adjust accounts
            if($Assets->type == 'Debit') {
                $CashAccount->decrement('credit', $total);
                $AssetAccount->decrement('debit', $total);
            }else {
                $CashAccount->decrement('debit', $total);
                $AssetAccount->decrement('credit', $total);
            }
                $User = Auth::user()->username;
                $UserID = Auth::user()->id;    
                $depID = Auth::user()->depID;
            // Atomic operations for accounts
            if($total > 0) {
                // Create a single Finance Transaction for the deleted operation
                if($Assets->type == 'Debit') {
                FinanceTrans::create([
                    'user' => $UserID,
                    'depID' => $depID,
                    'depitAcc' => 'Bank Account',
                    'depitAmount' => $total,
                    'creditAcc' => 'Cash Account',
                    'creditAmount' => $total,
                    'formType' => 'Bank Statement',
                    'action' => 'Delete',
                    'date' => now(),
                    'info' => $User . ' Deleted Transaction' . $total . ' Accounting effected',
                ]); 
                }else {
                    FinanceTrans::create([
                        'user' => $UserID,
                        'depID' => $depID,
                        'depitAcc' => 'Cash Account',
                        'depitAmount' => $total,
                        'creditAcc' => 'Bank Account',
                        'creditAmount' => $total,
                        'formType' => 'Bank Statement',
                        'action' => 'Delete',
                        'date' => now(),
                        'info' => $User . ' Deleted Transaction' . $total . ' Accounting effected',
                    ]); 
                }
            }else{
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'The transaction cannot be deleted as it has already been processed.']);  // if total paid amount and other fees is 0, it means the transaction has been processed and cannot be deleted.
            }
            
    
    
            // Delete the Assets record
            $Assets->delete();
    
            DB::commit();
            return redirect()->back()->with('status', 'Bank Statement Deleted Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the transaction: ' . $e->getMessage()]);
        }
    }
}
