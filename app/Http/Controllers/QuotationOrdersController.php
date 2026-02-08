<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Customers;
use App\Models\Quotation;
use App\Models\QuotationOrders;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class QuotationOrdersController extends Controller
{
    //
    public function index(Request $request) {
        if ($request->ajax()) {
            $query = QuotationOrders::query();
    
            // Applying filters
            if ($request->input('name')) {
                $query->whereHas('pro', function ($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->input('name')}%");
                });
            }
            
            if ($request->input('phone')) {
                $query->whereHas('transaction', function ($q) use ($request) {
                    $q->where('id', 'LIKE', "%{$request->input('phone')}%");
                });
            }
            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('created_at', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'proID',
                2 => 'qty',
                3 => 'unit',
                4 => 'price',
                5 => 'total',
                6 => 'transID',
                7 => 'created_at',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $QuotationOrders = $query->get();

        $data = [];
        foreach ($QuotationOrders as $Quotation) {
            $nestedData['id'] = $Quotation->id;
            $nestedData['pro'] = $Quotation->proID  ? $Quotation->pro->name : 'N/A';
            $nestedData['qty'] = $Quotation->qty;
            $nestedData['unit'] = $Quotation->unit;
            $nestedData['price'] = $Quotation->price;
            $nestedData['total'] = $Quotation->total;
            $nestedData['transID'] = $Quotation->transID;
            $nestedData['created_at'] = $Quotation->created_at-> format('Y-m-d');

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

        return view('layout.quotation.trans');
    }
    

    public function create(){
        $customers = Customers::get(); // Fetch all customer details
            // Fetch products where quantity is greater than 0 and sort them by name in ascending order
        $products = Products::where('quantity', '>', 0)
        ->orderBy('name', 'asc')
        ->get();
        return view('layout.quotation.add', compact('customers', 'products'));
    }


    public function searchProduct(Request $request){
        $query = $request->get('query');
              $products = Products::where('name', 'like', '%' . $query . '%')->get(['id', 'name', 'selling_price', 'type', 'unit', 'quantity']);
        return response()->json($products);
    }

    public function searchCustomer(Request $request){
        $query = $request->get('query');
        $customers = Customers::where('customer_name', 'like', '%' . $query . '%')->get(['id', 'customer_name', 'phone']);
        return response()->json($customers);
    }

    public function store(Request $request) {
        // Validate inputs
        $request->validate([
            'customer' =>'required',
            'products' =>'required',
            'products.*.proID' =>'required',
            'products.*.quantity' =>'required',
            'products.*.price' =>'required',
            'products.*.total_price' =>'required',
            'discount' =>'required',
            'net_price' =>'required',
            'due_date' => 'required|date',
            'info' => 'nullable|date',
        ]);
    
        // Create sales transaction and sales records
        DB::beginTransaction();
        try {        
    
            // Insert into sales_transactions
            $transaction = Quotation::create([
                'customer' => $request->customer,
                'phone' => $request->phone,
                'sub_total' => $request->subtotal,
                'discount' => $request->discount,
                'net_price' => $request->net_price,
                'info' => $request->info,
                'date' => $request->due_date ? $request->due_date : now(),
            ]);
            foreach ($request->products as $product) {
                // Create sales record
                QuotationOrders::create([
                    'proID' => $product['proID'],
                    'qty' => $product['quantity'],
                    'unit' => $product['unit'],
                    'price' => $product['price'],
                    'total' => $product['total_price'],
                    'transID' => $transaction->id,
                ]);
    
            }
            // Commit transaction
            DB::commit();
            return redirect('/quotation')->with(['status' => 'Quotation Successfully Created.']);
    
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error details (optional)
            Log::error('Quotation Orders LetterError: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors('Something went wrong: ' . $e->getMessage());
        }
    }
    
    



}
