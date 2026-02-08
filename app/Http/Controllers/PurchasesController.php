<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchases;
use App\Models\Products;
use App\Models\Suppliers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;


class PurchasesController extends Controller
{
    //
    public function index(Request $request) {
    if ($request->ajax()) {
        if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager' || auth()->user()->role == 'acc') {
            $query = Purchases::query();
        } else {
            $query = Purchases::where('depID', auth()->user()->depID);
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

        // Filter by product type
        if ($request->input('type') === 'fuel') {
            // Assuming fuel products have IDs 4 (Petrol) and 5 (Diesel)
            $query->whereIn('proID', [4, 5]);
        } elseif ($request->input('type') === 'oil') {
            // Assuming oil products are all other products
            $query->whereNotIn('proID', [4, 5]);
        }
        // For 'all' type, no additional filtering needed

        $totalData = $query->count();
        $totalFiltered = $totalData;

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'quantity',
            3 => 'unit_cost',
            4 => 'add_cost',
            5 => 'total_cost',
            6 =>'remaining',
            7 => 'supplier',
            8 => 'created_at',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $query->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);

        $purchases = $query->get();

        $data = [];
        foreach ($purchases as $purchase) {
            $nestedData['id'] = $purchase->id;
            $nestedData['name'] = optional($purchase->pro)->name ?? 'N/A';
            $nestedData['quantity'] = $purchase->quantity;
            $nestedData['unit_cost'] = $purchase->unit_cost;
            $nestedData['add_cost'] = $purchase->add_cost;
            $nestedData['total_cost'] = $purchase->total_cost;
            $nestedData['remaining'] = $purchase->remaining;
            $nestedData['supplier'] = $purchase->supplier;
            $nestedData['created_at'] = $purchase->created_at->format('Y-m-d H:i:s');

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
    
    if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $suppliers = Suppliers::all();
    } else {
        $suppliers = Suppliers::where('depID', auth()->user()->depID)->get();
    }
    return view('layout.purchases.index', compact('suppliers'));
}
    
    /**
     * Show the form for creating a new patient.
     */
    public function create() {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $suppliers = Suppliers::get();
        $products = Products::get();
        } else {
            $suppliers = Suppliers::where('depID', auth()->user()->depID)->get();
            $products = Products::where('depID', auth()->user()->depID)->get();
        }
        return view('layout.purchases.add', compact('suppliers', 'products'));
    }
    
        /**
         * Store a newly created patient in storage.
         */
    public function store(Request $request){
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
        Purchases::create($request->all());
    
        return redirect()->route('purchases')->with('status', 'New Manual Purchase Inventory Added Successfully');
    }
    
    
         
      /**
         * Show the form for editing the specified resource.
         */
        public function edit(string $id)
        {
                if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $suppliers = Suppliers::all();
            $purchase = Purchases::findOrFail($id);
            } else {
                $suppliers = Suppliers::where('depID', auth()->user()->depID)->get();
                $purchase = Purchases::where('id', $id)->where('depID', auth()->user()->depID)->first();
            }
      
            return view('admin.purchases.edit', compact('purchase', 'suppliers'));
        }
    
        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, string $id)
        {
            $purchase = Purchases::findOrFail($id);
      
            $purchase->update($request->all());
      
            return redirect()->route('purchases')->with('success', 'Purchase Updated Successfully');
        }
    
        /**
         * Remove the specified resource from storage.
         */
        public function destroy(string $id)
        {
                if (Auth::user()->role === 'acc') {
                return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
            }
    
    
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $purchase = Purchases::findOrFail($id);
            } else {
                $purchase = Purchases::where('id', $id)->where('depID', auth()->user()->depID)->first();
            }
            $purchase->delete();
      
            return redirect()->route('purchases')->with('success', 'Purchase Deleted Successfully');
        }
}
