<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use App\Models\SalesTransactions;

class PDFController extends Controller
{
    //
    public function generateInvoicePDF($id)
    {
        // Fetch sales transaction details from the database
        $salesTransactionDetails = SalesTransactions::findOrFail($id);

        // Load the HTML content for the invoice using Laravel's view() method
        $html = view('admin.salesTransaction.invoice', compact('salesTransactionDetails'))->render();

        // Create a new Dompdf instance
        $dompdf = new Dompdf();

        // Load HTML content
        $dompdf->loadHtml($html);

        // Set paper size and orientation (optional)
        $dompdf->setPaper('A4', 'portrait');

        // Render HTML as PDF
        $dompdf->render();

        // Output the generated PDF (inline download)
        return $dompdf->stream('invoice.pdf');
    }
}
