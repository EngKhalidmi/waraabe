<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Expenses;
use App\Models\SalesTransactions;
use Carbon\Carbon;

class MonthlyReportController extends Controller
{



    // Show Monthly Payment Controller
    public function showMonthlyPaymentsReport(){
        
        return view('admin.monthly_reports.monthly_payments_report');
    }



    // Show Monthly Payment Controller
    public function showMonthlySalesReport(){
        
        return view('admin.monthly_reports.monthly_sales_report');
    }
    //Show Al Expenses Report
    public function showMonthlyExpensesReport(){
        
        return view('admin.monthly_reports.monthlyExpenses');
    }
    


  // Update the namespace according to your Doctor model's location

    public function getPaymentMonthlyreport($startDate, $endDate)
    {
        $startOfMonth = Carbon::parse($startDate)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($endDate)->endOfMonth()->toDateString();

        $totalPaidAmount = SalesTransactions::whereBetween('paid_date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('paid_amount')
            ->sum('paid_amount');

        $totalPaidAmountZAAD = SalesTransactions::whereBetween('paid_date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('paid_amount')
            ->where('payment_method', 'ZAAD')
            ->sum('paid_amount');

        $totalPaidAmountEdahab = SalesTransactions::whereBetween('paid_date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('paid_amount')
            ->where('payment_method', 'Edahab')
            ->sum('paid_amount');

        $totalPaidAmountCash = SalesTransactions::whereBetween('paid_date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('paid_amount')
            ->where('payment_method', 'Cash On Hand')
            ->sum('paid_amount');

        $totalPaidAmountBank = SalesTransactions::whereBetween('paid_date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('paid_amount')
            ->where('payment_method', 'Bank Account')
            ->sum('paid_amount');



        $response = [
            'start_date' => $startOfMonth,
            'end_date' => $endOfMonth,
            'total_paid_amount' => $totalPaidAmount,
            'total_paid_amount_zaad' => $totalPaidAmountZAAD,
            'total_paid_amount_edahab' => $totalPaidAmountEdahab,
            'total_paid_amount_cash_on_hand' => $totalPaidAmountCash,
            'total_paid_amount_bank_account' => $totalPaidAmountBank,
        ];

        
        return response()->json($response);
    }


// All Expenses Controller
public function getMonthlyExpensesReport(Request $request)
{
    $startDate = Carbon::parse($request->input('start_date'))->startOfMonth()->toDateString();
    $endDate = Carbon::parse($request->input('end_date'))->endOfMonth()->toDateString();

    // Calculate totals
    $totalGeneralAmount = Expenses::whereBetween('date', [$startDate, $endDate])->sum('amount');
    $totalGeneralBalance = Expenses::whereBetween('date', [$startDate, $endDate])->sum('balance');



    // Return Response to the view
    return response()->json([
        'Total Expenses Amount' => $totalGeneralAmount,
        'Total Expenses Balance' => $totalGeneralBalance,
       
    ]);
    
}




    
}                                                                                                                        
