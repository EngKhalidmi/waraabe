<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Customers;
use DB;
class CustomersController extends Controller
{
    //List all Customers
   public function index(Request $request) {
    if ($request->ajax()) {
        if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager' || auth()->user()->role == 'acc') {
            $query = Customers::query();
        } else {
            $query = Customers::where('depID', auth()->user()->depID);
        }

        // Applying filters
        if ($request->input('name')) {
            $query->where('customer_name', 'LIKE', "%{$request->input('name')}%");
        }

        if ($request->input('phone')) {
            $query->where('phone', $request->input('phone'));
        }

        if ($request->input('address')) {
            $query->where('address', $request->input('address'));
        }

        $isBalanceFilter = $request->input('type');
        if ($isBalanceFilter) {
            $query->where('balance', '>', 0);
        }

        // Get total count before applying pagination and ordering
        $totalData = $query->count();
        $totalFiltered = $totalData;

        $columns = [
            0 => 'id',
            1 => 'customer_name',
            2 => 'phone',
            3 => 'address',
            4 => 'serial',
            5 => 'balance',
            6 => 'birthDate',
            7 => 'age',
            8 => 'depID',
            9 => 'description',
            10 => 'created_at',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        
        // Default ordering (from DataTables request)
        $order = $columns[$request->input('order.0.column', 5)]; // Default to balance column if not specified
        $dir = $request->input('order.0.dir', 'desc'); // Default to desc if not specified

        // Override ordering if balance filter is applied
        if ($isBalanceFilter) {
            $order = 'balance';
            $dir = 'desc';
        }

        // Apply ordering and pagination
        $query->orderBy($order, $dir)
              ->offset($start)
              ->limit($limit);

        $clients = $query->get();

        $data = [];
        foreach ($clients as $customer) {
            $nestedData['id'] = $customer->id;
            $nestedData['name'] = $customer->customer_name;
            $nestedData['phone'] = $customer->phone;
            $nestedData['address'] = $customer->address;
            $nestedData['serial'] = $customer->serial;
            $nestedData['balance'] = $customer->balance;
            $nestedData['birthDate'] = $customer->age;
            $nestedData['sex'] = $customer->sex;
            $nestedData['depID'] = $customer->depID ? $customer->department->name : '';
            $nestedData['description'] = $customer->description;
            $nestedData['created_at'] = $customer->created_at->format('Y-m-d H:i:s');
            $nestedData['action'] = $customer->id;

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

    return view('layout.clients.index');
}
           
    public function create(){
        // Retrieve the last customer serial number in the format "Cus-XXX"
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager' || auth()->user()->role =='sales') {
        $lastCustomer = Customers::select(DB::raw('SUBSTRING(serial, 5) as serial_number_numeric'))
                            ->whereRaw("serial REGEXP '^TC-[0-9]+$'")
                            ->latest()
                            ->first();
        }else  {
            $lastCustomer = Customers::where('depID', auth()->user()->depID)
                            ->select(DB::raw('SUBSTRING(serial, 5) as serial_number_numeric'))
                            ->whereRaw("serial REGEXP '^TC-[0-9]+$'")
                            ->latest()
                            ->first();
        }

        // Extract the numeric part from the last serial number
        $lastSerialNumber = $lastCustomer ? intval($lastCustomer->serial_number_numeric) : 0;

        // Increment the serial number to get the next one
        $nextSerialNumber = $lastSerialNumber + 1;

        // Format the next serial number with leading zeros and the "Cus-" prefix
        $nextSerialNumberFormatted = 'TC-' . sprintf('%03d', $nextSerialNumber);

        // Pass the next serial number to the view
        return view('layout.clients.add', ['nextSerialNumber' => $nextSerialNumberFormatted], );
    }

    public function store(Request $request)
    {
        if (Auth::user()->role === 'acc') {
            return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
        }
    
        $validatedData = $request->validate([
            'customer_name' => 'required|max:50',
            'phone' => 'required|numeric|unique:customers,phone',
            'address' => 'required|max:100',
            'balance' => 'required|numeric',
            'serial' => 'required|unique:customers,serial',
            'description' => 'nullable|string|max:200',
        ]);
    
        $depID = Auth::user()->depID;
    
        $customer = Customers::create([
            'customer_name' => $validatedData['customer_name'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
            'serial' => $validatedData['serial'],
            'balance' => $validatedData['balance'],
            'depID' => auth()->user()->depID,
            'description' => $validatedData['description'] ?? null,
        ]);
    
        return redirect()->route('customers')->with('status', 'Customer Registered Successfully');
    }

    
    
    public function quickStore(Request $request)
    {
            if (Auth::user()->role === 'acc') {
            return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
        }
        
        $validatedData = $request->validate([
            'customer_name' => 'required|max:50',
            'phone' => 'required|numeric',
        ]);
    
        $depID = Auth::user()->depID;
    
        // Retrieve the last customer serial number in the format "MC-XXX"
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager' || auth()->user()->role =='sales') {
            $lastCustomer = Customers::where('serial', 'LIKE', 'MC-%')
                            ->orderByRaw('CAST(SUBSTRING(serial, 4) AS UNSIGNED) DESC')
                            ->first();
        } else {
            $lastCustomer = Customers::where('depID', auth()->user()->depID)
                            ->where('serial', 'LIKE', 'MC-%')
                            ->orderByRaw('CAST(SUBSTRING(serial, 4) AS UNSIGNED) DESC')
                            ->first();
        }
        
        // Get the numeric part (assuming format "MC-123")
        $lastSerialNumber = $lastCustomer ? (int) substr($lastCustomer->serial, 3) : 0;
    
        // Increment the serial number
        $nextSerialNumber = $lastSerialNumber + 1;
    
        // Format the next serial number (e.g., "MC-001")
        $nextSerialNumberFormatted = 'MC-' . str_pad($nextSerialNumber, 3, '0', STR_PAD_LEFT);
        
        // create a new customer
        $customer = Customers::create([
            'customer_name' => $validatedData['customer_name'],
            'phone' => $validatedData['phone'],
            'address' => 'Boorama',
            'serial' => $nextSerialNumberFormatted,
            'age' => '30',
            'sex' => 'Male',
            'depID' => $depID,
        ]);
    
        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'serial' => $customer->serial
            ]
        ]);
    }
    
    
    

    public function show(string $id) {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $customer = Customers::findOrFail($id);
        } else {
            $customer = Customers::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        return view('admin.customers.show_customers', compact('customer'));
    }


            // Update
    public function edit(string $id){
            if (Auth::user()->role === 'acc') {
                return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
            }
    
    
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $record = Customers::findOrFail($id);
        }else {
            $record = Customers::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        return view('layout.clients.update', compact('record'));
    }
        
    public function update(Request $request, string $id)
    {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $customer = Customers::findOrFail($id);
        } else {
            $customer = Customers::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        $validatedData = $request->validate([
            'customer_name' =>'required|max:50',
            'phone' =>'required|numeric',
            'address' =>'nullable|max:100',
            'age' =>'nullable',
            'balance' =>'nullable',
            'sex' =>'nullable',
            'description' =>'nullable',
        ]);

        // create a new customer
        $customer->update($validatedData);
        return redirect()->route('customers')->with('status', 'Customer Updated Successfully');
    }
        

    public function destroy(string $id)
    {
        
        if (Auth::user()->role === 'acc') {
            return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
        }
    
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $customer = Customers::findOrFail($id);
        } else {
            $customer = Customers::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        $customer->delete();
        return redirect()->route('customers')->with('status', 'Customer Deleted Successfully');
    }
}
