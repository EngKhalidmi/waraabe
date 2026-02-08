<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\BadProduct;
use App\Models\Products;
use App\Models\Purchases;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class BadProductController extends Controller
{
    
     public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager') {
            $query = BadProduct::query();
            } else {
                $query = BadProduct::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('name')) {
                $query->where('name', 'LIKE', "%{$request->input('name')}%");
            }
    
            if ($request->input('supplier')) {
                $query->where('supplier', $request->input('supplier'));
            }

            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('created_at', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'prodID',
                2 => 'quantity',
                3 => 'depID',
                8 => 'created_at',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
       $bad_products = $query->with('product')->get();

        $data = [];
        foreach ($bad_products as $bad_product) {
            $nestedData['id'] = $bad_product->id;
            $nestedData['name'] = optional($bad_product->product)->name ?? 'N/A';
            $nestedData['quantity'] = $bad_product->quantity;
            $nestedData['created_at'] = $bad_product->created_at->format('Y-m-d H:i:s');

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

        return view('layout.bad_products.index');
    }
    
    
    public function create(){
        return view('layout.bad_products.add');
    }
    
     

   public function store(Request $request)
    {
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        $request->validate([
            'proID' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);
    
        $product = Products::findOrFail($request->proID);
    
        // Fetch the most recent or most relevant purchase for this product
        $purchases = Purchases::where('proID', $request->proID)->orderByDesc('id')->first();
    
        if (!$purchases) {
            return response()->json(['error' => 'No purchase record found for this product'], 404);
        }
    
        if ($request->quantity > $product->quantity) {
            return response()->json(['error' => 'Bad quantity exceeds available product quantity'], 422);
        }
    
        // Decrease quantities
        $product->quantity -= $request->quantity;
        $purchases->remaining = max(0, $purchases->remaining - $request->quantity);
    
        $product->save();
        $purchases->save();
    
        BadProduct::create([
            'proID' => $request->proID,
            'depID' => Auth::user()->depID,
            'quantity' => $request->quantity,
            'reason' => $request->reason ?? 'Bad Product',
        ]);
    
        return redirect()->back()->with('status', 'The Bad Product Has been Recorded Successfully');
    }

    
    // Delete Assets data
    // public function destroy(int $id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Find the Payment record by ID
    //         if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
    //         $Assets = Assets::findOrFail($id);
    //         }else {
    //             $Assets = Assets::where('depID', auth()->user()->depID)->findOrFail($id);
    //         }
    //         // Accumulate the total paid amount and other fees for all students
    //         $total = $Assets->amount;
    //         // Update accounting records
    //         $User = Auth::user()->username;
    //         $AssetAccount = CashAccount::where('AccCode', 'Fixed')->firstOrFail();
    //         $CashAccount = CashAccount::where('AccCode', 'Cash')->firstOrFail();
        
    //         // Adjust accounts
    //         $CashAccount->decrement('credit', $total);
    //         $AssetAccount->decrement('debit', $total);
  
    //         // Atomic operations for accounts
    //         if($total > 0) {
    //           // Create a single Finance Transaction for the deleted operation
    //           $User = Auth::user()->username;
    //           $UserID = Auth::user()->id;
    //           $depID = Auth::user()->depID;
    //           FinanceTrans::create([
    //             'user' => $UserID,
    //             'depID' => $depID,
    //             'depitAcc' => 'Fixed Assets',
    //             'depitAmount' => $total,
    //             'creditAcc' => 'Cash Account',
    //             'creditAmount' => $total,
    //             'formType' => 'Fixed Assets',
    //             'action' => 'Delete',
    //             'date' => now(),
    //             'info' => $User . ' Deleted ' . $Assets->name . ' Accounting effected',
    //           ]);
              
    //         }else{
    //           DB::rollBack();
    //           return redirect()->back()->withErrors(['error' => 'The transaction cannot be deleted as it has already been processed.']);  // if total paid amount and other fees is 0, it means the transaction has been processed and cannot be deleted.
    //         }
            
  
  
    //         // Delete the Assets record
    //         $Assets->delete();
    
    //         DB::commit();
    //         return redirect()->back()->with('status', 'Asset Deleted Successfully');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the transaction: ' . $e->getMessage()]);
    //     }
    // }
}
