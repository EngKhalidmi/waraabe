<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\QuotationOrders;
use App\Models\Quotation;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QuotationController extends Controller
{
    //
    //List all Pateints

    public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager' || auth()->user()->role == 'acc' ) {
            $query = Quotation::query();
            } else {
                $query = Quotation::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
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
                $query->whereBetween('paid_date', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'customer',
                2 => 'phone',
                3 => 'sub_total',
                4 => 'discount',
                5 => 'net_price',
                6 => 'date',
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
            $nestedData['customer'] = $pay->customer;
            $nestedData['phone'] = $pay->phone;
            $nestedData['sub_total'] = $pay->sub_total;
            $nestedData['discount'] = $pay->discount;
            $nestedData['net_price'] = $pay->net_price;
            $nestedData['date'] = $pay->date;
            $nestedData['info'] = $pay->info;
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

        return view('layout.quotation.index');
    }

    public function destroy(string $id){
        
            if (Auth::user()->role === 'acc') {
                return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
            }
    
    
        DB::beginTransaction(); // Start the transaction

        try {
            // Fetch the sales transaction to be deleted
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $transaction = Quotation::findOrFail($id);
            $salesRecords = QuotationOrders::where('transID', $id)->get();
            } else {
                $transaction = Quotation::where('depID', auth()->user()->depID)->findOrFail($id);
                $salesRecords = QuotationOrders::where('transID', $id)->where('depID', auth()->user()->depID)->get();
            }

            // Fetch related sales records

            // Reverse Inventory Changes
            foreach ($salesRecords as $sales) {
                // Update the inventory account accordingly (decrement by the amount initially credited)
                $product = Products::find($sales->proID);
                // Delete the sales record
                $sales->delete();
            }

           
            // Finally, delete the main sales transaction record
            $transaction->delete();

            // Commit the transaction
            DB::commit();

            // Redirect with a success message
            return redirect('/quotation')->with(['status' => 'Sales transaction successfully deleted.']);
        } catch (\Exception $e) {
            // Rollback the transaction if there's an error
            DB::rollBack();

            // Log the error for debugging purposes
            \Log::error('Quotation Orders Deletion Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);

            // Redirect back with an error message
            return redirect()->back()->withErrors('Something went wrong while deleting the sales transaction: ' . $e->getMessage());
        }
    }

    // Invoice
    public function invoice(string $id) {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $transaction = Quotation::find($id);
        } else {
            $transaction = Quotation::where('depID', auth()->user()->depID)->find($id);
        }
        return view('layout.quotation.invoice', compact('transaction'));
    }

}
