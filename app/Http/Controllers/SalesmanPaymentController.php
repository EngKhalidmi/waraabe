<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesTransactions;
use App\Models\SalesmanPayment;
use App\Models\Salesman;
use App\Models\User;
use App\Models\BankStatement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\CashAccount;
use App\Models\FinanceTrans;
use App\Models\SalesmanPayments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;


class SalesmanPaymentController extends Controller
{
    
public function index(Request $request)
{
    if ($request->ajax()) {

        $user = auth()->user();
        $isAdmin = in_array($user->role, ['admin', 'manager']);

        /**
         * ===================== BASE QUERY =====================
         */
        $query = SalesmanPayment::with('salesman');

        /**
         * ===================== ACCESS CONTROL =====================
         * Sales users → only payments of salesmen in SAME department
         */
        if (!$isAdmin) {
            $query->whereHas('salesman', function ($q) use ($user) {
                $q->where('depID', $user->depID);
            });
        }

        /**
         * ===================== FILTERS =====================
         */
        if ($request->filled('name')) {
            $query->whereHas('salesman', function ($q) use ($request) {
                $q->where('full_name', 'LIKE', '%' . $request->name . '%');
            });
        }

        if ($request->filled('phone')) {
            $query->whereHas('salesman', function ($q) use ($request) {
                $q->where('phone', 'LIKE', '%' . $request->phone . '%');
            });
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
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderColumn = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'desc');

        // Default order
        $query->orderBy('created_at', 'desc');

        $columns = [
            0 => 'id',
            1 => 'full_name',
            2 => 'phone',
            3 => 'pbalance',
            4 => 'discount',
            5 => 'paid_amount',
            6 => 'current',
            7 => 'payment_method',
            8 => 'created_at',
        ];

        if ($orderColumn !== null && isset($columns[$orderColumn])) {
            $orderBy = $columns[$orderColumn];

            if (in_array($orderBy, ['full_name', 'phone'])) {
                $query->join('salesmen', 'salesman_payment.salesman_id', '=', 'salesmen.id')
                    ->orderBy('salesmen.' . $orderBy, $orderDir)
                    ->select('salesman_payment.*');
            } else {
                $query->orderBy($orderBy, $orderDir);
            }
        }

        /**
         * ===================== PAGINATION =====================
         */
        $payments = $query
            ->offset($start)
            ->limit($limit)
            ->get();

        /**
         * ===================== RESPONSE =====================
         */
        $data = [];
        foreach ($payments as $pay) {
            $data[] = [
                'id' => $pay->id,
                'full_name' => optional($pay->salesman)->full_name ?? 'N/A',
                'phone' => optional($pay->salesman)->phone ?? 'N/A',
                'pbalance' => number_format($pay->pbalance, 2),
                'discount' => number_format($pay->discount, 2),
                'paid_amount' => number_format($pay->paid_amount, 2),
                'current' => number_format($pay->current, 2),
                'payment_method' => $pay->payment_method,
                'created_at' => $pay->created_at
                    ? $pay->created_at->format('d-m-Y H:i')
                    : 'N/A',
                'action' => $pay->id,
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    /**
     * ===================== NORMAL PAGE LOAD =====================
     */
    $salesman = Salesman::when(
        auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager',
        fn ($q) => $q->where('depID', auth()->user()->depID)
    )->get();

    return view('layout.salesmanPayment.index', compact('salesman'));
}


    public function create(){
        if(auth()->user()->role === 'admin' || auth()->user()->role ==='manager' || auth()->user()->role ==='sales'){
            $salesman = Salesman::get();
            } 
            
        return view('layout.salesmanPayment.add', compact( 'salesman'));
    }

  public function searchSalesman(Request $request)
{
    $query = $request->get('query');
    $user = auth()->user();

    $searchQuery = Salesman::where(function ($q) use ($query) {
        $q->where('full_name', 'like', '%' . $query . '%')
          ->orWhere('phone', 'like', '%' . $query . '%');
    });

    // Admin & Manager → see all
    if (in_array($user->role, ['admin', 'manager'])) {
        $salesmen = $searchQuery->get(['id', 'full_name', 'balance']);
    }
    // All other users → only same department
    else {
        $salesmen = $searchQuery
            ->where('depID', $user->depID)
            ->get(['id', 'full_name', 'balance']);
    }

    return response()->json($salesmen);
}


    public function store(Request $request)
    {
        
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
    
        // Validate the incoming request data
        $request->validate([
            'salesman_id' => 'required|exists:salesman,id',
            'paid_amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'pbalance' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'discount' => 'nullable|numeric|min:0',
        ]);
    
        try {
            // Start a database transaction
            DB::beginTransaction();
    
            // Find salesman details
            if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager') {
                $salesman = Salesman::find($request->salesman_id);
            } else {
                // Add appropriate restriction if needed for non-admin users
                $salesman = Salesman::find($request->salesman_id);
                
                if (!$salesman) {
                    DB::rollBack();
                    return redirect()->back()->withErrors('Salesman not found or you do not have permission to access this salesman.');
                }
            }
    
            if (!$salesman) {
                DB::rollBack();
                return redirect()->back()->withErrors('Salesman not found.');
            }
    
            // Calculate current balance
            $current = $request->pbalance - $request->discount - $request->paid_amount;
            
            // Prevent negative balance
            if ($current < 0) {
                DB::rollBack();
                return redirect()->back()->withErrors('Payment amount cannot exceed the available balance.');
            }
    
            // Create a new salesman payment
            $payment = new SalesmanPayment();
            $payment->salesman_id = $request->salesman_id;
            $payment->paid_amount = $request->paid_amount;
            $payment->date = $request->date;
            $payment->pbalance = $request->pbalance;
            $payment->discount = $request->discount ?? 0;
            $payment->current = $current;
            $payment->payment_method = $request->payment_method;
            $payment->depID = auth()->user()->depID;
            
            $payment->save();
    
            // Update salesman's balance (assuming salesman has a balance field)
            // If your Salesman model doesn't have a balance field, you might need to adjust this
            if ($salesman->balance !== null) {
                $salesman->decrement('balance', $request->paid_amount + ($request->discount ?? 0));
            }
    

    
            // Commit the transaction if all operations succeed
            DB::commit();
    
            // Redirect to the salesman payments index route with a success message
            return redirect()->route('salesman_payment')->with('status', 'Payment processed successfully.');
    
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollBack();
    
            // Log the error for debugging purposes
            Log::error('Error processing salesman payment: ' . $e->getMessage());
    
            // Redirect back with an error message
            return redirect()->back()->withErrors('An error occurred while processing the payment: ' . $e->getMessage())->withInput();
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
    
            // Fetch the payment transaction to be deleted
            if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager') {
                $payment = SalesmanPayment::findOrFail($id);
            } else {
                $payment = SalesmanPayment::where('created_by', auth()->user()->id)
                            ->where('id', $id)
                            ->firstOrFail();
            }
    
            // Get the necessary payment details
            $amount = (float) $payment->paid_amount;
            $discount = (float) $payment->discount;
    
            // Fetch the salesman record
            $salesman = Salesman::findOrFail($payment->salesman_id);
            
            // Check if user has permission to access this salesman's payments
            if(auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
                // Add any additional permission checks if needed
                // For example, if salesmen are department-specific:
                // if($salesman->depID !== auth()->user()->depID) {
                //     throw new \Exception('Unauthorized access to salesman payment');
                // }
            }
    
            // Reverse the salesman's balance update (if balance field exists)
            if ($salesman->balance !== null) {
                $salesman->increment('balance', $amount + $discount);
            }
    
            // // Reverse the accounting entries if accounting features exist
            // if (class_exists('App\Models\CashAccount') && class_exists('App\Models\FinanceTrans')) {
            //     $cashAccount = CashAccount::where('AccCode', 'Cash')->first();
            //     $expenseAccount = CashAccount::where('AccCode', 'Expense')->first(); // Or appropriate account
    
            //     // Check if accounts exist
            //     if ($cashAccount && $expenseAccount) {
            //         // Reverse the accounting records
            //         $cashAccount->increment('balance', $amount);
            //         $expenseAccount->decrement('balance', $amount);
                    
            //         $cashAccount->date = now();
            //         $cashAccount->save();
            //         $expenseAccount->date = now();
            //         $expenseAccount->save();
    
            //         // Log the reversed finance transaction
            //         $User = Auth::user()->username;
            //         $UserID = Auth::user()->id;
    
            //         FinanceTrans::create([
            //             'user' => $UserID,
            //             'depitAcc' => 'Cash Account',
            //             'depitAmount' => $amount,
            //             'creditAcc' => 'Expense Account',
            //             'creditAmount' => $amount,
            //             'date' => now(),
            //             'formType' => 'Reverse Salesman Payment',
            //             'action' => 'Delete',
            //             'info' => $User . ' Reversed Payment to Salesman: ' . $salesman->full_name . ' Amount: ' . $amount,
            //         ]);
            //     }
            // }
    
            // Delete the payment record
            $payment->delete();
    
            // Commit the transaction if all operations succeed
            DB::commit();
    
            // Redirect with a success message
            return redirect()->route('salesman_payment')->with('status', 'Payment deleted successfully.');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Payment not found or you do not have permission to delete it.');
            
        } catch (\Exception $e) {
            // Rollback the transaction if there's an error
            DB::rollBack();
    
            // Log the error for debugging purposes
            Log::error('Error deleting salesman payment: ' . $e->getMessage());
    
            // Redirect back with an error message
            return redirect()->back()->withErrors('An error occurred while deleting the payment: ' . $e->getMessage());
        }
    }
      
}
