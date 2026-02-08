<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\CashAccount;

class CashAccountController extends Controller
{
    //
        //
        //List all Pateints
    public function index(Request $request) {
        if ($request->ajax()) {
            $query = CashAccount::query();
    
            // Applying filters
            if ($request->input('account')) {
                $query->where('account', 'LIKE', "%{$request->input('account')}%");
            }

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'account',
                2 => 'debit',
                3 => 'credit',
                4 => 'date',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $accounts = $query->get();

        $data = [];
        foreach ($accounts as $acc) {
            $nestedData['id'] = $acc->id;
            $nestedData['account'] = $acc->account;
            $nestedData['debit'] = $acc->debit;
            $nestedData['credit'] = $acc->credit;
            $nestedData['date'] = $acc->date;
            $nestedData['action'] = $acc->id;

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

        return view('layout.accounts.index');
    }

    public function create(){
        return view('layout.accounts.add');
    }
            
     
    public function store(Request $request) {
        
        
    if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
    
        CashAccount::create($request->all());

        return redirect()->route('cashAccount')->with('status', 'Account Registered Successfully');
    }
         
        public function show(string $id)
        {
            $cashAccount = CashAccount::findOrFail($id);
      
            return view('admin.cashAccount.show_CashAccount', compact('cashAccount'));
        }
      /**
         * Show the form for editing the specified resource.
         */
       
         public function edit(string $id)
         {
             $cashAccount = CashAccount::findOrFail($id);
         
         
         
             return view('admin.cashAccount.edit', compact('cashAccount'));
         }
         
    
        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, string $id)
        {
            $cashAccount = CashAccount::findOrFail($id);
      
            $cashAccount->update($request->all());
      
            return redirect()->route('cashAccount')->with('success', 'Account Updated Successfully');
        }
    
        /**
         * Remove the specified resource from storage.
         */

}
