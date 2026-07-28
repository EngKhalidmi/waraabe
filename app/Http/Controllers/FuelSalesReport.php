<?php

namespace App\Http\Controllers;

use App\Models\FuelSale;
use App\Models\FuelCreditSale;
use App\Models\Salesman;
use App\Models\Customers;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FuelSalesReport extends Controller
{
    /**
     * ============================
     * Fuel Sales Report Page
     * ============================
     */
    public function fuelSalesReport()
    {
        $user = auth()->user();
        $isAdmin = in_array($user->role, ['admin', 'manager']);

        $salesmen = Salesman::when(!$isAdmin, fn ($q) =>
            $q->where('depID', $user->depID)
        )->get();

        $products = Products::where('type', 'fuel')
            ->when(!$isAdmin, fn ($q) =>
                $q->where('depID', $user->depID)
            )
            ->get();

        return view('admin.fuel_report.report', compact('salesmen', 'products'));
    }

    /**
     * ============================
     * Combined Fuel Sales Page
     * ============================
     */
    public function combinedFuelReport()
    {
        $user = auth()->user();
        $isAdmin = in_array($user->role, ['admin', 'manager']);

        $salesmen = Salesman::when(!$isAdmin, fn ($q) =>
            $q->where('depID', $user->depID)
        )->get();

        $customers = Customers::when(!$isAdmin, fn ($q) =>
            $q->where('depID', $user->depID)
        )->get();

        $products = Products::where('type', 'fuel')
            ->when(!$isAdmin, fn ($q) =>
                $q->where('depID', $user->depID)
            )
            ->get();

        return view('layout.fuel_sale_report.combined', compact(
            'salesmen',
            'customers',
            'products'
        ));
    }

    /**
     * ============================
     * Combined Fuel Sales Report (API)
     * ============================
     */
    public function getCombinedFuelReport(Request $request)
    {
        $user = auth()->user();
        $isAdmin = in_array($user->role, ['admin', 'manager']);

        $startDate = $request->startDate;
        $endDate   = $request->endDate;
        $productId = $request->product_id;

        /**
         * ----------------------------
         * REGULAR FUEL SALES
         * ----------------------------
         */
        $regularSales = FuelSale::with(['transactions.product'])
            ->when(!$isAdmin, fn ($q) =>
                $q->where('depID', $user->depID)
            )
            ->when($startDate && $endDate, fn ($q) =>
                $q->whereBetween('date', [$startDate, $endDate])
            )
            ->get();

        /**
         * ----------------------------
         * CREDIT FUEL SALES
         * ----------------------------
         */
        $creditSales = FuelCreditSale::with(['product'])
            ->when(!$isAdmin, fn ($q) =>
                $q->where('depID', $user->depID)
            )
            ->when($startDate && $endDate, fn ($q) =>
                $q->whereBetween('date', [$startDate, $endDate])
            )
            ->get();

        /**
         * ----------------------------
         * BUILD UNIFIED ROWS
         * ----------------------------
         */
        $rows = collect();

        // Regular (Cash)
        foreach ($regularSales as $sale) {
            foreach ($sale->transactions as $t) {

                if ($productId && $t->product_id != $productId) {
                    continue;
                }

                $rows->push([
                    'product' => $t->product->name ?? 'Unknown',
                    'liters' => (float) $t->liters,
                    'rate' => (float) $t->rate,
                    'total' => (float) $t->total,
                    'payment_type' => 'Cash',
                    'type' => 'regular',
                ]);
            }
        }

        // Credit
        foreach ($creditSales as $sale) {

            if ($productId && $sale->product_id != $productId) {
                continue;
            }

            $rows->push([
                'product' => $sale->product->name ?? 'Unknown',
                'liters' => (float) $sale->quantity,
                'rate' => (float) $sale->rate,
                'total' => (float) $sale->total,
                'payment_type' => 'Credit',
                'type' => 'credit',
            ]);
        }

        /**
         * ----------------------------
         * SAFETY FILTER (IMPORTANT)
         * ----------------------------
         */
        $rows = $rows->filter(fn ($r) =>
            !empty($r['product']) &&
            !empty($r['payment_type']) &&
            !empty($r['type'])
        );

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the selected criteria'
            ]);
        }

        /**
         * ----------------------------
         * GROUPING (FINAL & CORRECT)
         * ----------------------------
         */
        $grouped = $rows
            ->groupBy(fn ($r) => $r['product'] . '_' . $r['payment_type'])
            ->map(function ($group) {

                $types = $group->pluck('type')->unique()->values();

                return [
                    'product' => $group->first()['product'],
                    'payment_type' => $group->first()['payment_type'],
                    'type' => $types->count() === 1 ? $types->first() : 'mixed',
                    'total_liters' => $group->sum('liters'),
                    'total_sales' => $group->sum('total'),
                    'rate' => round($group->avg('rate'), 2),
                ];
            })
            ->values();

        /**
         * ----------------------------
         * RESPONSE
         * ----------------------------
         */
        return response()->json([
            'success' => true,
            'data' => $grouped,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
