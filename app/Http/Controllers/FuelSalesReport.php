<?php

namespace App\Http\Controllers;

use App\Models\FuelSale;
use App\Models\FuelSaleTransaction;
use App\Models\FuelCreditSale;
use App\Models\Salesman;
use App\Models\Customers;
use App\Models\Products;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Collection;
class FuelSalesReport extends Controller
{
    /**
     * Display fuel sales report
     */
    public function fuelSalesReport(Request $request)
    {
        $salesmen = Salesman::all();
        $products = Products::where('type', 'fuel')->get();
        
        return view('admin.fuel_report.report', compact('salesmen', 'products'));
    }

    /**
     * Get fuel sales report data
     */
    public function getFuelSalesReport(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $shift = $request->input('shift');
        $salesmanId = $request->input('salesman_id');
        $productId = $request->input('product_id');

        $query = FuelSale::with(['salesman', 'transaction.product']);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        if ($shift) {
            $query->where('shift', $shift);
        }

        if ($salesmanId) {
            $query->where('salesman_id', $salesmanId);
        }

        $fuelSales = $query->orderBy('date', 'desc')->get();

        // Filter by product if specified
        if ($productId) {
            $fuelSales = $fuelSales->filter(function ($sale) use ($productId) {
                return $sale->transactions->where('product_id', $productId)->count() > 0;
            });
        }

        $reportData = $fuelSales->map(function ($sale) use ($productId) {
            $transactions = $sale->transactions;
            
            // Filter transactions by product if specified
            if ($productId) {
                $transactions = $transactions->where('product_id', $productId);
            }
            
            return [
                'id' => $sale->id,
                'date' => $sale->date,
                'shift' => $sale->shift,
                'salesman' => $sale->salesman->full_name ?? 'N/A',
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'product' => $transaction->product->name ?? 'N/A',
                        'liters' => $transaction->liters,
                        'rate' => $transaction->rate,
                        'total' => $transaction->total,
                    ];
                }),
                'discount' => $sale->discount,
                'net_total' => $sale->net_total,
                'cash_on_hand' => $sale->cash_on_hand,
                'balance' => $sale->balance,
            ];
        });

        $totals = [
            'total_liters' => $fuelSales->sum(function ($sale) use ($productId) {
                $transactions = $sale->transactions;
                if ($productId) {
                    $transactions = $transactions->where('product_id', $productId);
                }
                return $transactions->sum('liters');
            }),
            'total_sales' => $fuelSales->sum('net_total'),
            'total_cash' => $fuelSales->sum('cash_on_hand'),
            'total_discount' => $fuelSales->sum('discount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $reportData,
            'totals' => $totals,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Display combined fuel sales report (regular + credit)
     */
    public function combinedFuelReport(Request $request)
    {
        $salesmen = Salesman::all();
        $products = Products::where('type', 'fuel')->get();
        $customers = Customers::all();
        
        return view('layout.fuel_sale_report.combined', compact('salesmen', 'products', 'customers'));
    }

    /**
     * Get combined fuel sales report data
     */
    public function getCombinedFuelReport(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $reportType = $request->input('report_type', 'all');
        $productId = $request->input('product_id');

        $regularSales = collect();
        $creditSales = collect();

        // Get regular fuel sales
        if ($reportType === 'all' || $reportType === 'regular') {
            $regularQuery = FuelSale::with(['salesman', 'transactions.product']);

            if ($startDate && $endDate) {
                $regularQuery->whereBetween('date', [$startDate, $endDate]);
            }

            $regularSales = $regularQuery->orderBy('date', 'desc')->get();

            // Filter by product if specified
            if ($productId) {
                $regularSales = $regularSales->filter(function ($sale) use ($productId) {
                    return $sale->transactions && $sale->transactions->where('product_id', $productId)->count() > 0;
                });
            }
        }

        // Get credit fuel sales
        if ($reportType === 'all' || $reportType === 'credit') {
            $creditQuery = FuelCreditSale::with(['customer', 'product']);

            if ($startDate && $endDate) {
                $creditQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            $creditSales = $creditQuery->orderBy('created_at', 'desc')->get();

            // Filter by product if specified
            if ($productId) {
                $creditSales = $creditSales->where('product_id', $productId);
            }
        }

        // Prepare regular sales data
        $regularData = $regularSales->map(function ($sale) use ($productId) {
            // Ensure transactions is always a collection, even if null
            $transactions = $sale->transactions ?? new Collection();
            
            // Filter transactions by product if specified
            if ($productId && $transactions->isNotEmpty()) {
                $transactions = $transactions->where('product_id', $productId);
            }
            
            return [
                'type' => 'regular',
                'id' => $sale->id,
                'date' => $sale->date,
                'shift' => $sale->shift,
                'salesman' => $sale->salesman->full_name ?? 'N/A',
                'customer' => 'Walk-in Customer',
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'product' => $transaction->product->name ?? 'N/A',
                        'liters' => $transaction->liters,
                        'rate' => $transaction->rate,
                        'total' => $transaction->total,
                    ];
                })->toArray(), // Convert to array to avoid issues
                'discount' => $sale->discount,
                'net_total' => $sale->net_total,
                'payment_type' => 'Cash',
                'status' => 'Paid',
            ];
        });

        // Prepare credit sales data
        $creditData = $creditSales->map(function ($sale) {
            return [
                'type' => 'credit',
                'id' => $sale->id,
                'date' => $sale->created_at->format('Y-m-d'),
                'shift' => 'N/A',
                'salesman' => 'N/A',
                'customer' => $sale->customer->customer_name ?? 'N/A',
                'transactions' => [
                    [
                        'product' => $sale->product->name ?? 'N/A',
                        'liters' => $sale->quantity,
                        'rate' => $sale->rate,
                        'total' => $sale->total,
                    ]
                ],
                'discount' => 0,
                'net_total' => $sale->total,
                'payment_type' => 'Credit',
                'status' => ucfirst($sale->status),
            ];
        });
        $combinedData = $regularData->merge($creditData);

        // Group by product + payment_type
        $groupedData = $combinedData->flatMap(function ($sale) {
            return collect($sale['transactions'])->map(function ($transaction) use ($sale) {
                return [
                    'product' => $transaction['product'],
                    'liters' => $transaction['liters'],
                    'rate' => $transaction['rate'],
                    'total' => $transaction['total'],
                    'payment_type' => $sale['payment_type'], // Cash or Credit
                    'type' => $sale['type'], // regular / credit
                    'date' => $sale['date'],
                ];
            });
        })->groupBy(function ($item) {
            // group key = "Product + PaymentType"
            return $item['product'].'_'.$item['payment_type'];
        })->map(function ($group) {
            return [
                'product' => $group->first()['product'],
                'payment_type' => $group->first()['payment_type'],
                'type' => $group->first()['type'],
                'total_liters' => $group->sum('liters'),
                'total_sales' => $group->sum('total'),
                'rate' => $group->avg('rate'), // average rate if needed
            ];
        })->values();


        // Calculate totals
        $totals = [
            'regular_sales_count' => $regularSales->count(),
            'credit_sales_count' => $creditSales->count(),
            'total_liters' => $regularSales->sum(function ($sale) use ($productId) {
                    $transactions = $sale->transactions ?? new Collection();
                    if ($productId && $transactions->isNotEmpty()) {
                        $transactions = $transactions->where('product_id', $productId);
                    }
                    return $transactions->sum('liters');
                }) + $creditSales->sum('quantity'),
            'total_sales' => $regularSales->sum('net_total') + $creditSales->sum('total'),
            'regular_sales_total' => $regularSales->sum('net_total'),
            'credit_sales_total' => $creditSales->sum('total'),
            'total_discount' => $regularSales->sum('discount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $groupedData,
            'totals' => $totals,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
        
    }
}