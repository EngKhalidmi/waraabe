<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customers;
use App\Models\Suppliers;
use App\Models\Products;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = in_array($user->role, ['admin', 'manager', 'acc']);

        /**
         * ===================== DEPARTMENT FILTER =====================
         * Admin  → can choose department or see all
         * Others → forced to their own department
         */
        if ($isAdmin) {
            $selectedDepID = $request->input('depID'); // nullable
            if ($selectedDepID && !DB::table('department')->where('id', $selectedDepID)->exists()) {
                $selectedDepID = null; // if invalid depID, show all
            }
        } else {
            $selectedDepID = $user->depID; // locked
        }

        $departments = $isAdmin
            ? DB::table('department')->orderBy('name')->get()
            : collect();

        /**
         * ===================== YEAR & MONTH =====================
         */
        $selectedYear = (int) $request->input('year', date('Y'));
        $selectedMonth = (int) $request->input('month', date('n'));
        $selectedMonthName = date('F', mktime(0, 0, 0, $selectedMonth, 1));

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = date('n');
        }

        if ($selectedYear < 2020 || $selectedYear > date('Y') + 1) {
            $selectedYear = date('Y');
        }

        $availableYears = $this->getAvailableYears();

        /**
         * ===================== COUNTS =====================
         */
        $customers = Customers::when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))->count();
        $suppliers = Suppliers::when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))->count();
        $purchaseCount = DB::table('returned_credits')->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))->count();
        $salesCount = DB::table('sales_transactions')->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))->count();

        $receivable = DB::table('customers')->where('balance', '>', 0)
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->sum('balance');

        $payable = DB::table('suppliers')
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->sum('balance');

        /**
         * ===================== SALES & PURCHASES =====================
         */
        $total_sales = DB::table('sales_transactions')
            ->whereYear('paid_date', $selectedYear)
            ->whereMonth('paid_date', $selectedMonth)
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->sum('net_price');

        $total_purchase = DB::table('returned_credits')
            ->whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->sum('net_price');

        /**
         * ===================== FUEL & OIL PURCHASE =====================
         */
        $totalFuelPurchase = DB::table('returned_credits')
            ->whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->whereIn('id', function($q) {
                $q->select('transID')->from('purchases')->whereIn('proID', [4,5]);
            })
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->sum('net_price');

        $totalOilPurchase = DB::table('returned_credits')
            ->whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->whereIn('id', function($q) {
                $q->select('transID')->from('purchases')->whereNotIn('proID', [4,5]);
            })
            ->whereNotIn('id', function($q) {
                $q->select('transID')->from('purchases')->whereIn('proID', [4,5]);
            })
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->sum('net_price');

        /**
         * ===================== PRODUCTS =====================
         */
        $lowStockItems = Products::when(!$isAdmin, fn($q) => $q->where('depID', $user->depID))
            ->where('quantity', '<', 10)
            ->get();

        $staticProducts = Products::whereIn('id', [4, 5])
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->get();

        $otherProducts = Products::whereNotIn('id', [4, 5])
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->latest()
            ->take(8)
            ->get();

        $recentProducts = $staticProducts->merge($otherProducts);

        $this->attachStockMovements($recentProducts, $selectedYear, $selectedMonth, $selectedDepID);

        /**
         * ===================== FUEL SALES =====================
         */
        $totalAllFuelSales = DB::table('fuel_sales')
            ->whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->sum('net_total');

        $fuelCashSales = (float)$totalAllFuelSales;

        /**
         * ===================== MONTHLY CHART =====================
         */
        $months = range(1, 12);

        // Monthly sales chart
        $salesData = DB::table('sales_transactions')
            ->selectRaw('MONTH(paid_date) as month, SUM(net_price) as total')
            ->whereYear('paid_date', $selectedYear)
            ->when($selectedDepID, fn($q) => $q->where('depID', $selectedDepID))
            ->groupBy('month')
            ->pluck('total', 'month');

        $sales = [];
        foreach ($months as $month) {
            $sales[] = $salesData->get($month, 0);
        }

        /**
         * ===================== RETURN VIEW =====================
         */
        $responseData = [
            'data' => [
                'clients' => (int)$customers,
                'suppliers' => (int)$suppliers,
                'purchases' => (int)$purchaseCount,
                'sales' => (int)$salesCount,
                'totalReceivable' => (float)$receivable,
                'totalPayable' => (float)$payable,
                'totalSales' => (float)$total_sales,
                'totalPurchase' => (float)$total_purchase,
                'totalFuelPurchase' => (float)$totalFuelPurchase,
                'totalOilPurchase' => (float)$totalOilPurchase,
                'products' => $recentProducts,
                'fuel_sale' => (float)$fuelCashSales,
                'totalAllFuelSales' => (float)$totalAllFuelSales,
                'selectedMonth' => (int)$selectedMonth,
                'selectedMonthName' => $selectedMonthName,
                'currentYear' => (int)$selectedYear,
                'availableYears' => $availableYears,
                'monthlyData' => [
                    'sales' => array_map('floatval', $sales),
                    'months' => $months
                ]
            ],
            'lowStockItems' => $lowStockItems,
            'departments' => $departments,
            'selectedDepID' => $selectedDepID
        ];

        if ($request->wantsJson() || $request->ajax() || $request->has('json') || $request->query('format') === 'json') {
            return response()->json($responseData)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }

        return response()->view('admin.index', $responseData)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * ===================== STOCK MOVEMENTS =====================
     *
     * Stock "Out" must match the Fuel Sales Report, which counts both the
     * regular (cash) fuel sale transactions and the credit fuel sales.
     * Oil sales and bad products are stock-out movements as well.
     *
     * Opening stock is derived so the row always balances:
     *     opening + in - out = current quantity
     */
    private function attachStockMovements($products, int $year, int $month, $depID): void
    {
        $productIds = $products->pluck('id')->filter()->values()->all();

        if (empty($productIds)) {
            return;
        }

        // OUT: regular (cash) fuel sales — dated on the parent fuel_sales row
        $fuelCashOut = DB::table('fuel_sale_transactions as fst')
            ->join('fuel_sales as fs', 'fs.id', '=', 'fst.fuel_sale_id')
            ->selectRaw('fst.product_id as pid, SUM(fst.liters) as qty')
            ->whereYear('fs.date', $year)
            ->whereMonth('fs.date', $month)
            ->whereIn('fst.product_id', $productIds)
            ->when($depID, fn($q) => $q->where('fst.depID', $depID))
            ->groupBy('fst.product_id')
            ->pluck('qty', 'pid');

        // OUT: credit fuel sales
        $fuelCreditOut = DB::table('fuel_credit_sales')
            ->selectRaw('product_id as pid, SUM(quantity) as qty')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('product_id', $productIds)
            ->when($depID, fn($q) => $q->where('depID', $depID))
            ->groupBy('product_id')
            ->pluck('qty', 'pid');

        // OUT: oil / general sales
        $oilOut = DB::table('sales as s')
            ->join('sales_transactions as st', 'st.id', '=', 's.sales_transaction_id')
            ->selectRaw('s.proID as pid, SUM(s.quantity) as qty')
            ->whereYear('st.paid_date', $year)
            ->whereMonth('st.paid_date', $month)
            ->whereIn('s.proID', $productIds)
            ->when($depID, fn($q) => $q->where('s.depID', $depID))
            ->groupBy('s.proID')
            ->pluck('qty', 'pid');

        // OUT: damaged / written-off stock
        $badOut = DB::table('bad_products')
            ->selectRaw('proID as pid, SUM(quantity) as qty')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereIn('proID', $productIds)
            ->when($depID, fn($q) => $q->where('depID', $depID))
            ->groupBy('proID')
            ->pluck('qty', 'pid');

        // IN: purchases — dated on the parent returned_credits row
        $purchaseIn = DB::table('purchases as p')
            ->join('returned_credits as rc', 'rc.id', '=', 'p.transID')
            ->selectRaw('p.proID as pid, SUM(p.quantity) as qty')
            ->whereYear('rc.date', $year)
            ->whereMonth('rc.date', $month)
            ->whereIn('p.proID', $productIds)
            ->when($depID, fn($q) => $q->where('p.depID', $depID))
            ->groupBy('p.proID')
            ->pluck('qty', 'pid');

        foreach ($products as $product) {
            $id = $product->id;

            $out = (float) ($fuelCashOut[$id] ?? 0)
                 + (float) ($fuelCreditOut[$id] ?? 0)
                 + (float) ($oilOut[$id] ?? 0)
                 + (float) ($badOut[$id] ?? 0);

            $in = (float) ($purchaseIn[$id] ?? 0);
            $balance = (float) $product->quantity;

            $product->stock_in = $in;
            $product->stock_out = $out;
            $product->stock_balance = $balance;
            $product->opening_stock = $balance - $in + $out;
        }
    }

    /**
     * ===================== AVAILABLE YEARS =====================
     */
    private function getAvailableYears()
    {
        $salesYears = DB::table('sales_transactions')->selectRaw('YEAR(paid_date) as year')->pluck('year');
        $purchaseYears = DB::table('returned_credits')->selectRaw('YEAR(date) as year')->pluck('year');
        $fuelSalesYears = DB::table('fuel_sales')->selectRaw('YEAR(date) as year')->pluck('year');
        $fuelCreditYears = DB::table('fuel_credit_sales')->selectRaw('YEAR(date) as year')->pluck('year');

        return $salesYears
            ->merge($purchaseYears)
            ->merge($fuelSalesYears)
            ->merge($fuelCreditYears)
            ->unique()
            ->sortDesc()
            ->values();
    }
}
