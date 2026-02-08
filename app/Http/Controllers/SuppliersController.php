<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Suppliers;

class SuppliersController extends Controller
{
    //List all Pateints
    public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager') {
                $query = Suppliers::query();
            } else {
                $query = Suppliers::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('name')) {
                $query->where('name', 'LIKE', "%{$request->input('name')}%");
            }
    
            if ($request->input('phone')) {
                $query->where('phone', $request->input('phone'));
            }
    
            if ($request->input('address')) {
                $query->where('address', $request->input('address'));
            }

            if ($request->input('email')) {
                $query->where('email', $request->input('email'));
            }

            if ($request->input('balance')) {
                $query->where('balance', $request->input('balance'));
            }

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'name',
                2 => 'phone',
                3 => 'address',
                4 => 'email',
                5 => 'balance',
                6 => 'created_at',
                7 => 'depID',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $suppliers = $query->get();

        $data = [];
        foreach ($suppliers as $supplier) {
            $nestedData['id'] = $supplier->id;
            $nestedData['name'] = $supplier->name;
            $nestedData['phone'] = $supplier->phone;
            $nestedData['address'] = $supplier->address;
            $nestedData['email'] = $supplier->email;
            $nestedData['balance'] = $supplier->balance;
            $nestedData['depID'] = $supplier->depID ? $supplier->department->name : 'None';
            $nestedData['created_at'] = $supplier->created_at->format('Y-m-d H:i:s');
            $nestedData['action'] = $supplier->id;

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

        return view('layout.suppliers.index');
    }
        
    public function create(){
        return view('layout.suppliers.add');
    }
        
    public function store(Request $request){
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        // validate date
        $this->validate($request, [
            'name' =>'required|string|max:255',
            'phone' =>'required|numeric',
            'address' =>'required|string|max:255',
            'email' =>'required|email',
        ]);

        $depID = auth()->user()->depID;
        // create new supplier
        $supplier = Suppliers::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'email' => $request->email,
            'depID' => $depID,
        ]);
        return redirect()->route('suppliers')->with('status', 'Supplier Added Successfully');
    }
    
        public function quickStore(Request $request)
    {
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        $validatedData = $request->validate([
            'name' => 'required|max:50',
            'phone' => 'required|numeric',
        ]);
    
        $depID = Auth::user()->depID;
        
        
        // create a new customer
        $supplier = Suppliers::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => 'BOORAMA',
            'email' => 'supplier@gmail.com',
            'depID' => $depID,
        ]);
    
        return response()->json([
            'success' => true,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                 'phone' => $supplier->phone
            ]
        ]);
    }
    
    
    public function edit(string $id){
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $record = Suppliers::findOrFail($id);
        } else {
            $record = Suppliers::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        return view('layout.suppliers.update', compact('record'));
    }
        
    public function update(Request $request, string $id){
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $supplier = Suppliers::findOrFail($id);
        } else {
            $supplier = Suppliers::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        // validate data
        $this->validate($request, [
            'name' =>'required|string|max:255',
            'phone' =>'required|numeric',
            'balance' =>'required|numeric',
            'address' =>'required|string|max:255',
            'email' =>'required|email',
        ]);
        $supplier->update($request->all());
        return redirect()->route('suppliers')->with('status', 'Supplier Updated Successfully');
    }
        
    public function destroy(string $id) {
        
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
    
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $supplier = Suppliers::findOrFail($id);
        } else {
            $supplier = Suppliers::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        $supplier->delete();
        return redirect()->route('suppliers')->with('status', 'Supplier Deleted Successfully');
    }
}
