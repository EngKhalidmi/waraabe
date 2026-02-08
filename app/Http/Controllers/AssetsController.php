<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Assets;
use App\Models\CashAccount;
use App\Models\FinanceTrans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class AssetsController extends Controller
{

    public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager') {
            $query = Assets::query();
            }else {
                $query = Assets::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('type')) {
                $query->where('type', 'LIKE', "%{$request->input('type')}%");
            }
    
            if ($request->input('name')) {
                $query->where('name', $request->input('name'));
            }

            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('date', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'name',
                2 => 'type',
                3 => 'amount',
                4 => 'description',
                5 => 'depID',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $assets = $query->get();

        $data = [];
        foreach ($assets as $asset) {
            $nestedData['id'] = $asset->id;
            $nestedData['name'] = $asset->name;
            $nestedData['type'] = $asset->type;
            $nestedData['amount'] = $asset->amount;
            $nestedData['description'] = $asset->description;
            $nestedData['depID'] = $asset->department->name;
            $nestedData['action'] = $asset->id;

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

        return view('layout.assets.index');
    }

    public function create(){
        return view('layout.assets.add');
    }
    
     

    public function store(Request $request) {
        // Validating Form
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string', // Expecting 'Debit' or 'Credit'
            'description' => 'required|string',
                'amount' => 'required', // Corrected 'numberic' to 'numeric'
                
        ]);
    
        DB::beginTransaction();

        try {
        // Initialize an array for expense data
        // Initialize an array for expense data
          $depID = auth()->user()->depID;
        $assetsData = [
            'name' => $request->name,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'depID' => $depID,  // Corrected 'department' to 'depID'
        ];
        // Create a new Asset record
        Assets::create($assetsData);


        $User = Auth::user()->username;
        $UserID = Auth::user()->id;
        $depID = Auth::user()->depID;
        $AssetAccount = CashAccount::where('AccCode', 'Fixed')->firstOrFail();
        $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
    
        // Adjust accounts
        $CashAccount->increment('credit', $request->amount);
        $AssetAccount->increment('debit', $request->amount);

        // Create a Finance Transaction record
        FinanceTrans::create([
            'user' => $UserID,
            'depID' => $depID,
            'depitAcc' => 'Fixed Assets',
            'depitAmount' => $request->amount,
            'creditAcc' => 'Cash Accounts',
            'creditAmount' => $request->amount,
            'date' => now(),
            'formType' => 'Fixed Assets',
            'action' => 'Insert',
            'info' => $User . ' Register '.  $request->name . 'Cost Of' . $request->amount . ' Accounting Effected',
        ]);
    
        
            DB::commit();
        
            return redirect('asset')->with('status', 'New Asset Registered Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
        
            // Log the error (optional)
            \Log::error('Asset Registration Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
        
            return redirect()->back()->withErrors(['error' => 'An error occurred while registering the Expense.']);
        }
        
    }

       
    
    // Delete Assets data
    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {
            // Find the Payment record by ID
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $Assets = Assets::findOrFail($id);
            }else {
                $Assets = Assets::where('depID', auth()->user()->depID)->findOrFail($id);
            }
            // Accumulate the total paid amount and other fees for all students
            $total = $Assets->amount;
            // Update accounting records
            $User = Auth::user()->username;
            $AssetAccount = CashAccount::where('AccCode', 'Fixed')->firstOrFail();
            $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
        
            // Adjust accounts
            $CashAccount->decrement('credit', $total);
            $AssetAccount->decrement('debit', $total);
  
            // Atomic operations for accounts
            if($total > 0) {
              // Create a single Finance Transaction for the deleted operation
              $User = Auth::user()->username;
              $UserID = Auth::user()->id;
              $depID = Auth::user()->depID;
              FinanceTrans::create([
                'user' => $UserID,
                'depID' => $depID,
                'depitAcc' => 'Fixed Assets',
                'depitAmount' => $total,
                'creditAcc' => 'Cash Account',
                'creditAmount' => $total,
                'formType' => 'Fixed Assets',
                'action' => 'Delete',
                'date' => now(),
                'info' => $User . ' Deleted ' . $Assets->name . ' Accounting effected',
              ]);
              
            }else{
              DB::rollBack();
              return redirect()->back()->withErrors(['error' => 'The transaction cannot be deleted as it has already been processed.']);  // if total paid amount and other fees is 0, it means the transaction has been processed and cannot be deleted.
            }
            
  
  
            // Delete the Assets record
            $Assets->delete();
    
            DB::commit();
            return redirect()->back()->with('status', 'Asset Deleted Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the transaction: ' . $e->getMessage()]);
        }
    }
}
