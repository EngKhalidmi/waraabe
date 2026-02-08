<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expenses;
use App\Models\CashAccount;
use App\Models\AccountingTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\FinanceTrans;
use App\Models\Salesman;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\DB;

class ExpensesController extends Controller
{
     //List all Pateints
  public function index(Request $request)
{
    if ($request->ajax()) {

        $user = auth()->user();
        $isPrivileged = in_array($user->role, ['admin', 'manager', 'acc']);

        /**
         * ===================== BASE QUERY =====================
         */
        $query = Expenses::with(['department', 'salesman']);

        // Department restriction for non-admin users
        if (!$isPrivileged) {
            $query->where('depID', $user->depID);
        }

        /**
         * ===================== FILTERS =====================
         */
        if ($request->filled('type')) {
            $query->where('type', 'LIKE', '%' . $request->type . '%');
        }

        if ($request->filled('amount')) {
            $query->where('amount', $request->amount);
        }

        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween('date', [
                $request->startDate,
                $request->endDate
            ]);
        }

        /**
         * ===================== COUNTS =====================
         */
        $totalData = $query->count();
        $totalFiltered = $totalData;

        /**
         * ===================== DATATABLES =====================
         */
        $columns = [
            0 => 'id',
            1 => 'type',
            2 => 'amount',
            3 => 'date',
            4 => 'description',
            5 => 'depID',
            6 => 'salesman_id',
        ];

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderIndex = $request->input('order.0.column', 0);
        $order = $columns[$orderIndex] ?? 'created_at';
        $dir = $request->input('order.0.dir', 'desc');

        $expenses = $query
            ->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();

        /**
         * ===================== RESPONSE =====================
         */
        $data = [];
        foreach ($expenses as $expense) {
            $data[] = [
                'id' => $expense->id,
                'type' => $expense->type,
                'amount' => number_format($expense->amount, 2),
                'date' => $expense->date,
                'description' => $expense->description,
                'depID' => optional($expense->department)->name ?? '',
                'salesman' => optional($expense->salesman)->full_name ?? '',
                'action' => $expense->id,
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    return view('layout.expenses.index');
}


    public function create(){
        if(auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
            $salesman = Salesman::where('type', 'Salesman')
            ->where('depID', auth()->user()->depID)
            ->get();
        }else{
             $salesman = Salesman::where('type', 'Salesman')->get();
        }
         return view('layout.expenses.add', compact('salesman') );
    }
    


    public function store(Request $request) {
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        $request->validate([
            'type' => 'required|max:255|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|max:255|string',
        ]);
    
        DB::beginTransaction();

        try {
            $depID = auth()->user()->depID;
        // Initialize an array for expense data
        $expenseData = [
            'type' => $request->type,
            'amount' => $request->amount,
            'payment_account' => 'Cash Account',
            'date' => $request->date,
            'salesman_id' => $request->salesman_id,
            'depID' => $depID,
            'description' => $request->description,
        ];
        // Create a new expense record

        Expenses::create($expenseData);
        
        $ExpenseAccount = CashAccount::where('AccCode', 'Expense')->firstOrFail();
        $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
    
        // Adjust accounts
        $CashAccount->increment('credit', $request->amount);
        $ExpenseAccount->increment('debit', $request->amount);

        // Create a Finance Transaction record

        $User = Auth::user()->username;
        $UserID = Auth::user()->id;
        FinanceTrans::create([
                'user' => $UserID,
                'depID' => auth()->user()->depID,
                'depitAcc' => 'Expense Account',
                'depitAmount' => $request->amount,
                'creditAcc' => 'Cash Accounts',
                'creditAmount' => $request->amount,
                'formType' => 'Expense Form',
                'action' => 'Insert',
                'date' => now(),
                'info' => $User . ' Register '.  $request->type . 'Amount Of' . $request->amount . ' Accounting effected',
            ]);
        
            DB::commit();
        
            return redirect('/expenses')->with('status', 'Expense Registered Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
        
            // Log the error (optional)
            Log::error('Expense Registration Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
        
            return redirect()->back()->withErrors(['error' => 'An error occurred while registering the Expense.']);
        }
        
    }


    // Delete Assets data
    public function destroy(int $id)
    {
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
        DB::beginTransaction();
        try {
            // Find the Payment record by ID
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $Expense = Expenses::findOrFail($id);
            } else {
                $Expense = Expenses::where('depID', auth()->user()->depID)->where('id', $id)->firstOrFail();
            }
            // Accumulate the total paid amount and other fees for all students
            $total = $Expense->amount;
            // Update accounting records
            $AssetAccount = CashAccount::where('AccCode', 'Expense')->firstOrFail();
            $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
        
            // Adjust accounts
            $CashAccount->decrement('credit', $total);
            $AssetAccount->decrement('debit', $total);
  
            // Atomic operations for accounts
            if($total > 0) {
              // Create a single Finance Transaction for the deleted operation
              $User = Auth::user()->username;
              $UserID = Auth::user()->id;
              FinanceTrans::create([
                'user' => $UserID,
                'depitAcc' => 'Expense Account',
                'depitAmount' => $total,
                'creditAcc' => 'Cash Account',
                'creditAmount' => $total,
                'formType' => 'Form Of Expense',
                'action' => 'Delete',
                'date' => now(),
                'info' => $User . ' Deleted ' . $Expense->type . ' Amount Of ' . $total . ' Accounting effected',
              ]);
              
            }else{
              DB::rollBack();
              return redirect()->back()->withErrors(['error' => 'The transaction cannot be deleted as it has already been processed.']);  // if total paid amount and other fees is 0, it means the transaction has been processed and cannot be deleted.
            }
            
  
  
            // Delete the Expense record
            $Expense->delete();
    
            DB::commit();
            return redirect()->back()->with('status', 'Expense Deleted Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            // / Log the error (optional)
            Log::error('Expense Registration Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the transaction: ' . $e->getMessage()]);
        }
    }


}
