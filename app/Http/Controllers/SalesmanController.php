<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Salesman;
use DB;
class SalesmanController extends Controller
{
    //List all Salesman
public function index(Request $request)
{
    if ($request->ajax()) {

        $user = auth()->user();

        // Admin / manager / sales see all
        $isPrivileged = in_array($user->role, ['admin']);

        $query = Salesman::query();

        /**
         * 🔐 HARD RULE:
         * Non-admin users → ONLY same department employees
         */
        if (!$isPrivileged) {
            $query->where('depID', $user->depID);
        }

        /**
         * 🔍 Optional filters
         */
        if ($request->filled('name')) {
            $query->where('full_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('phone')) {
            $query->where('phone', $request->phone);
        }

        if ($request->filled('type')) {
            $query->where('balance', '>', 0);
        }

        /**
         * 📊 DataTables logic
         */
        $totalData = $query->count();
        $totalFiltered = $totalData;

        $columns = [
            0 => 'id',
            1 => 'full_name',
            2 => 'phone',
            3 => 'sex',
            5 => 'balance',
            7 => 'age',
            10 => 'created_at',
        ];

        $limit = $request->length;
        $start = $request->start;

        $orderIndex = $request->input('order.0.column', 5);
        $order = $columns[$orderIndex] ?? 'balance';
        $dir = $request->input('order.0.dir', 'desc');

        $employees = $query
            ->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();

        /**
         * 📦 Response
         */
        $data = [];
        foreach ($employees as $emp) {
            $data[] = [
                'id' => $emp->id,
                'full_name' => $emp->full_name,
                'phone' => $emp->phone,
                'sex' => $emp->sex,
                'balance' => $emp->balance,
                'age' => $emp->age,
                'type' => $emp->type,
                'created_at' => $emp->created_at->format('Y-m-d H:i:s'),
                'action' => $emp->id,
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    return view('layout.salesman.index');
}



           
    public function create(){
        // Pass the next serial number to the view
        return view('layout.salesman.add');
    }

    public function store(Request $request){
        
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
    
        $validatedData = $request->validate([
            'full_name' =>'required|max:50',
            'phone' =>'required|numeric',
            'sex' =>'required',
            'age' =>'required',
        ]);

        $depID = Auth::user()->depID;
        // create a new customer
        $customer = Salesman::create([
            'full_name' => $validatedData['full_name'],
            'phone' => $validatedData['phone'],
            'sex' => $validatedData['sex'],
            'type' => $request['type'],
            'age' => $validatedData['age'],
            'sex' => $validatedData['sex'],
            'depID' => auth()->user()->depID,
        ]);
        return redirect()->route('salesman')->with('status', 'Salesman Registered Successfully');
    }
    

            // Update
    public function edit(string $id){
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $record = Salesman::findOrFail($id);
        }
        return view('layout.salesman.update', compact('record'));
    }
        
    public function update(Request $request, string $id)
    {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $customer = Salesman::findOrFail($id);
        } 
        $validatedData = $request->validate([
            'full_name' =>'required|max:50',
            'phone' =>'required|numeric',
            'sex' =>'nullable|max:100',
            'age' =>'nullable',
            'balance' =>'nullable',
        ]);

        // create a new customer
        $customer->update($validatedData);
        return redirect()->route('salesman')->with('status', 'Salesman Updated Successfully');
    }
        

    public function destroy(string $id)
    {
            if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
    
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $customer = Salesman::findOrFail($id);
        } 
        $customer->delete();
        return redirect()->route('salesman')->with('status', 'Salesman Deleted Successfully');
    }


    
}
