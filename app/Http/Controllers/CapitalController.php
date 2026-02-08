<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Capital;
use App\Models\CashAccount;
use App\Models\FinanceTrans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CapitalController extends Controller
{
    //
    public function index(Request $request) {
        if ($request->ajax()) {
            $query = Capital::query();
    
            // Applying filters
            if ($request->input('owner_name')) {
                $query->where('owner_name', 'LIKE', "%{$request->input('owner_name')}%");
            }
    
            if ($request->input('amount')) {
                $query->where('amount', $request->input('amount'));
            }

            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('created_at', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'owner_name',
                2 => 'capital_amount',
                3 => 'created_at',
                4 => 'depID',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $capitals = $query->get();

        $data = [];
        foreach ($capitals as $owner) {
            $nestedData['id'] = $owner->id;
            $nestedData['name'] = $owner->owner_name;
            $nestedData['amount'] = $owner->capital_amount;
            
            // Format the created_at date
            $nestedData['created_at'] = $owner->created_at ? $owner->created_at->format('Y-m-d') : 'N/A';
        
            // Safely access the 'department' relationship
            $nestedData['depID'] = optional($owner->department)->name ?? 'N/A';
            
            $nestedData['action'] = $owner->id;
        
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

        return view('layout.capital.index');
    }

    public function create(){
        return view('layout.capital.add');
    }
     
    public function store(Request $request) {
        
        if (Auth::user()->role === 'acc') {
            return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
        }
    
        try {
            // Start a database transaction
            DB::beginTransaction();
    
            // Validate the input
            $request->validate([
                'capital_amount' => 'required|numeric|min:0',
                'owner_name' => 'required|string|max:255',
            ]);
    
            // Fetch the accounts
            $cashAccount = CashAccount::where('AccCode', 'Cash')->first();
            $capitalAccount = CashAccount::where('AccCode', 'Capital')->first();
    
            // Check if accounts exist
            if (!$cashAccount || !$capitalAccount) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Cash account or capital account not found.');
            }
    
            // Get paid amount from request
            $amount = (float) $request->input('capital_amount');
            $capitalAccount->increment('credit', $amount);
            $cashAccount->increment('debit', $amount);
            // $cashAccount->date = now(); 
            // $cashAccount->save();
            // $capitalAccount->date = now();
            // $capitalAccount->save();
    
            // Create Capital record
            Capital::create([
                'owner_name' => $request['owner_name'],
                'capital_amount' => $amount, // Ensure correct amount is used
            ]);
    

             // Create a single Finance Transaction for the deleted operation
             $User = Auth::user()->username;
             $UserID = Auth::user()->id;
             $depID = Auth::user()->depID;
             FinanceTrans::create([
               'user' => $UserID,
               'depID' => $depID,
               'depitAcc' => 'Cash Account',
               'depitAmount' => $amount,
               'creditAcc' => 'Capital Account',
               'creditAmount' => $amount,
               'formType' => 'Form Of Capital',
               'action' => 'Insert',
               'date' => now(),
               'info' => $User . ' Insert ' . $request->owner_name . ' Amount Of ' . $amount . ' Accounting effected',
             ]);
            // Commit the transaction if everything is successful
            DB::commit();
    
            // Return success message
            return redirect()->route('capital')->with('status', 'Capital Registered Successfully');
    
        } catch (\Exception $e) {
            // Rollback transaction in case of any error
            DB::rollBack();
            Log::error('Error processing transaction: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'An error occurred while processing the transaction.' . $e->getMessage());
        }
    }
    
    // Delete Assets data
    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {
            // Find the Payment record by ID
            $Capital = Capital::findOrFail($id);
    
            // Accumulate the total paid amount and other fees for all students
            $total = $Capital->capital_amount;
            // Update accounting records
            $CapitalAccount = CashAccount::where('AccCode', 'Capital')->firstOrFail();
            $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
        
            // Adjust accounts
            $CashAccount->decrement('debit', $total);
            $CapitalAccount->decrement('credit', $total);
    
            // Atomic operations for accounts
            if($total > 0) {
                // Create a single Finance Transaction for the deleted operation
                $User = Auth::user()->username;
                $UserID = Auth::user()->id;
                $depID = Auth::user()->depID;
                FinanceTrans::create([
                'user' => $UserID,
                'depID' => $depID,
                'depitAcc' => 'Cash Account',
                'depitAmount' => $total,
                'creditAcc' => 'Capital Account',
                'creditAmount' => $total,
                'formType' => 'Form Of Capital',
                'action' => 'Delete',
                'date' => now(),
                'info' => $User . ' Deleted ' . $Capital->owner_name . ' Amount Of ' . $total . ' Accounting effected',
                ]);
                
            }else{
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'The transaction cannot be deleted as it has already been processed.']);  // if total paid amount and other fees is 0, it means the transaction has been processed and cannot be deleted.
            }
            
    
    
            // Delete the Capital record
            $Capital->delete();
    
            DB::commit();
            return redirect()->back()->with('status', 'Capital Deleted Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            // / Log the error (optional)
            \Log::error('Capital Registration Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the transaction: ' . $e->getMessage()]);
        }
    }
}
