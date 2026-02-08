<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\SubCategory;
use App\Models\LabCategory;
use DB;

class SubCategoryController extends Controller
{
    //List all Categories
    public function index(Request $request) {
        if ($request->ajax()) {
            if(auth()->user()->role == 'admin' || auth()->user()->role == 'manager') {
            $query = SubCategory::query();
            } else {
                $query = SubCategory::where('depID', auth()->user()->depID);
            }
    
            // Applying filters
            if ($request->input('name')) {
                $query->where('name', 'LIKE', "%{$request->input('name')}%");
            }

            $totalData = $query->count();  
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'name',
                2 => 'category_id',
                3 => 'depID',
                4 => 'created_at',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
        $query = SubCategory::with('category');
        $categories = $query->get();

        $data = [];
        foreach ($categories as $Subcategory) {
            $nestedData['id'] = $Subcategory->id;
            $nestedData['name'] = $Subcategory->name;
            $nestedData['category'] = optional($Subcategory->category)->name ?? 'N/A';
            $nestedData['depID'] = $Subcategory->depID;
            $nestedData['created_at'] = $Subcategory->created_at->format('Y-m-d H:i:s');
            $nestedData['action'] = $Subcategory->id;

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

        return view('layout.subcategories.index');
    }
           
    public function create(){
        $categories = LabCategory::all();
        return view('layout.subcategories.add', compact('categories'));
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' =>'required|max:50',
            'category_id' =>'required|numeric',
        ]);

        $depID = Auth::user()->depID;
        // create a new Subcategory
        $Subcategory = SubCategory::create([
            'name' => $validatedData['name'],
            'category_id' => $validatedData['category_id'],
            'depID' => $depID,
        ]);
        return redirect()->route('subcategories')->with('status', 'Sub category Registered Successfully');
    }
        

    // Update
    public function edit(string $id){
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $record = SubCategory::findOrFail($id);
        }else {
            $record = SubCategory::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        return view('layout.subcategories.update', compact('record'));
    }
        
    public function update(Request $request, string $id)
    {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
        $subcategory = SubCategory::findOrFail($id);
        } else {
            $subcategory = SubCategory::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        $validatedData = $request->validate([
            'name' =>'required|max:50',
            'phone' =>'required|numeric',
        ]);

        // create a new Subcategory
        $subcategory->update($validatedData);
        return redirect()->route('subcategories')->with('status', 'Subcategory Updated Successfully');
    }
        

    public function destroy(string $id)
    {
        if(auth()->user()->role == 'admin' || auth()->user()->role =='manager') {
            $Subcategory = SubCategory::findOrFail($id);
        } else {
            $Subcategory = SubCategory::where('depID', auth()->user()->depID)->findOrFail($id);
        }
        $Subcategory->delete();
        return redirect()->route('subcategories')->with('status', 'Subcategory Deleted Successfully');
    }
}
