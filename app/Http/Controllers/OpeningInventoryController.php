<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpeningInventory;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
class OpeningInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager') {
                $query = OpeningInventory::query();
            } else {
                $query = OpeningInventory::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('name')) {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->input('name')}%");
                });
            }
    
            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('opening_date', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'product_id',
                2 => 'opening_quantity',
                3 => 'opening_date',
                4 => 'created_at',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
            $openingInventories = $query->get();

            $data = [];
            foreach ($openingInventories as $inventory) {
                $nestedData['id'] = $inventory->id;
                $nestedData['product'] = optional($inventory->product)->name ?? 'N/A';
                $nestedData['opening_quantity'] = $inventory->opening_quantity;
                $nestedData['opening_date'] = $inventory->opening_date;
                $nestedData['created_at'] = $inventory->created_at->format('Y-m-d H:i:s');

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
        
        return view('layout.opening_inventory.index');
    }
    
    /**
     * Show the form for creating a new opening inventory.
     */
    public function create()
    {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $products = Products::get();
        } else {
            $products = Products::where('depID', auth()->user()->depID)->get();
        }
        return view('layout.opening_inventory.add', compact('products'));
    }
    
    /**
     * Store a newly created opening inventory in storage.
     */

    public function store(Request $request)
    {
            if (Auth::user()->role === 'acc') {
                return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
            }
    
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'opening_quantity' => 'required|numeric|min:0',
            'opening_date' => 'required|date',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Create the opening inventory record
            $openingInventory = OpeningInventory::create([
                'product_id' => $validatedData['product_id'],
                'opening_quantity' => $validatedData['opening_quantity'],
                'opening_date' => $validatedData['opening_date'],
                'depID' => auth()->user()->depID, // Set department ID to the user's department
            ]);
            
            // Find the product and increment its quantity
            $product = Products::find($validatedData['product_id']);
            if ($product) {
                $product->increment('quantity', $validatedData['opening_quantity']);
            }
            
            DB::commit();
            
            return redirect()->route('opening_inventory')->with('status', 'New Opening Inventory Added Successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating opening inventory: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $inventory = OpeningInventory::findOrFail($id);
            $products = Products::all();
        } else {
            $inventory = OpeningInventory::where('id', $id)->first();
            $products = Products::where('depID', auth()->user()->depID)->get();
            
            if (!$inventory) {
                return redirect()->route('opening_inventory')->with('error', 'Opening inventory not found or access denied');
            }
        }
      
        return view('layout.opening_inventory.edit', compact('inventory', 'products'));
    }
    
    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'opening_quantity' => 'required|numeric|min:0',
            'opening_date' => 'required|date',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Find the existing opening inventory record
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
                $inventory = OpeningInventory::findOrFail($id);
            } else {
                $inventory = OpeningInventory::where('id', $id)->first();
                
                if (!$inventory) {
                    return redirect()->route('opening_inventory')->with('error', 'Opening inventory not found or access denied');
                }
            }
    
            // Get the old quantity before update
            $oldQuantity = $inventory->opening_quantity;
            $newQuantity = $validatedData['opening_quantity'];
            $quantityDifference = $newQuantity - $oldQuantity;
    
            // Update the opening inventory record
            $inventory->update($validatedData);
            
            // Update the product quantity if there's a change
            if ($quantityDifference != 0) {
                $product = Products::find($validatedData['product_id']);
                if ($product) {
                    $product->increment('quantity', $quantityDifference);
                }
            }
            
            DB::commit();
            
            return redirect()->route('opening_inventory')->with('success', 'Opening Inventory Updated Successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating opening inventory: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
            if (Auth::user()->role === 'acc') {
                return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
            }
    
        try {
            DB::beginTransaction();
            
            if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
                $inventory = OpeningInventory::findOrFail($id);
            } else {
                $inventory = OpeningInventory::where('id', $id)->first();
                
                if (!$inventory) {
                    return redirect()->route('opening_inventory')->with('error', 'Opening inventory not found or access denied');
                }
            }
            
            // Get the product associated with this opening inventory
            $product = Products::find($inventory->product_id);
            
            if ($product) {
                // Decrement the product quantity by the opening inventory amount
                $product->decrement('quantity', $inventory->opening_quantity);
            }
            
            $inventory->delete();
            
            DB::commit();
            
            return redirect()->route('opening_inventory')->with('success', 'Opening Inventory Deleted Successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('opening-inventory')->with('error', 'Error deleting opening inventory: ' . $e->getMessage());
        }
    }
}