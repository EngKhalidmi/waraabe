<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Sale Receipt - Transaction #{{ $fuelSale->id }}</title>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-address {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .transaction-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .info-box {
            flex: 1;
            margin: 0 10px;
        }
        
        .info-label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        
        .info-value {
            font-size: 16px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .products-table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        .products-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }
        
        .products-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        
        .total-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 5px;
        }
        
        .total-label {
            width: 200px;
            text-align: right;
            padding-right: 20px;
            font-weight: bold;
        }
        
        .total-value {
            width: 150px;
            text-align: right;
            font-weight: bold;
        }
        
        .grand-total {
            font-size: 18px;
            color: #000;
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            font-size: 12px;
            color: #666;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
            width: 200px;
        }
        
        .signature-label {
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }
        
        .print-btn:hover {
            background: #0056b3;
        }
        
        .section-title {
            background-color: #f0f0f0;
            padding: 8px 15px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            border-left: 4px solid #007bff;
        }
        
        .cash-transactions, .credit-transactions {
            margin-bottom: 20px;
        }
        
        .transaction-detail {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }
        
        .transaction-label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">TABANTAABO PETROLEUM</div>
            <div class="company-address">BURAO SOMALILAND</div>
            <div class="company-contact">Phone: 713013 | 063-4042473 | 063-4357338</div>
            <div class="receipt-title">FUEL SALE ENTRY</div>
        </div>

        <!-- Transaction Info -->
        <div class="transaction-info">
            <div class="info-box">
                <span class="info-label">Transaction ID</span>
                <span class="info-value">#{{ $fuelSale->id }}</span>
            </div>
            <div class="info-box">
                <span class="info-label">Date</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($fuelSale->date)->format('d/m/Y') }}</span>
            </div>
            <div class="info-box">
                <span class="info-label">Salesman</span>
                <span class="info-value">{{ $fuelSale->salesman->name ?? 'N/A' }}</span>
            </div>
            <div class="info-box">
                <span class="info-label">Shift</span>
                <span class="info-value">{{ ucfirst($fuelSale->shift) }}</span>
            </div>
        </div>

        <!-- Cash Sales -->
        @if($cashTransactions->count() > 0)
        <div class="section-title">CASH SALES</div>
        <div class="cash-transactions">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Previous Reading</th>
                        <th>Current Reading</th>
                        <th>Liters</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $cashTotal = 0;
                    @endphp
                    @foreach($cashTransactions as $transaction)
                        @php
                            $amount = $transaction->liters * $transaction->rate;
                            $cashTotal += $amount;
                        @endphp
                        <tr>
                            <td>{{ $transaction->product->name ?? 'N/A' }}</td>
                            <td>{{ number_format($transaction->previous_reading, 2) }}</td>
                            <td>{{ number_format($transaction->current_reading, 2) }}</td>
                            <td>{{ number_format($transaction->liters, 2) }} L</td>
                            <td>{{ number_format($transaction->rate, 3) }}</td>
                            <td>{{ number_format($amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: bold;">Cash Total:</td>
                        <td style="font-weight: bold;">{{ number_format($cashTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- Credit Sales -->
        @if($creditSales->count() > 0)
        <div class="section-title">CREDIT SALES</div>
        <div class="credit-transactions">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Liters</th>
                        <th>Rate</th>
                        <th>Amount</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $creditTotal = 0;
                    @endphp
                    @foreach($creditSales as $credit)
                        @php
                            $amount = $credit->liters * $credit->rate;
                            $creditTotal += $amount;
                        @endphp
                        <tr>
                            <td>{{ $credit->customer->name ?? 'N/A' }}</td>
                            <td>{{ $credit->product->name ?? 'N/A' }}</td>
                            <td>{{ number_format($credit->liters, 2) }} L</td>
                            <td>{{ number_format($credit->rate, 3) }}</td>
                            <td>{{ number_format($amount, 2) }}</td>
                            <td>{{ $credit->description ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: bold;">Credit Total:</td>
                        <td style="font-weight: bold;">{{ number_format($creditTotal, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- Summary Section -->
        <div class="section-title">TRANSACTION SUMMARY</div>
        <div class="transaction-summary">
            <table class="products-table">
                <tbody>
                    @php
                        $grossTotal = $cashTotal + $creditTotal;
                        $discount = $fuelSale->discount ?? 0;
                        $netTotal = $grossTotal - $discount;
                    @endphp
                    <tr>
                        <td><strong>Total Cash Sales:</strong></td>
                        <td>{{ number_format($cashTotal, 2) }}</td>
                        <td><strong>Total Credit Sales:</strong></td>
                        <td>{{ number_format($creditTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Gross Total:</strong></td>
                        <td>{{ number_format($grossTotal, 2) }}</td>
                        <td><strong>Discount:</strong></td>
                        <td>{{ number_format($discount, 2) }}</td>
                    </tr>
                    <tr class="grand-total-row">
                        <td colspan="3" style="text-align: right;"><strong>NET TOTAL:</strong></td>
                        <td style="font-weight: bold; font-size: 16px;">{{ number_format($netTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cash on Hand:</strong></td>
                        <td>{{ number_format($fuelSale->cash_on_hand ?? 0, 2) }}</td>
                        <td><strong>Balance:</strong></td>
                        <td>{{ number_format($fuelSale->balance ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Product Summary -->
        <div class="section-title">PRODUCT SUMMARY</div>
        <div class="product-summary">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Cash (Liters)</th>
                        <th>Credit (Liters)</th>
                        <th>Total (Liters)</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productSummary as $product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td>{{ number_format($product['cash_liters'], 2) }} L</td>
                            <td>{{ number_format($product['credit_liters'], 2) }} L</td>
                            <td>{{ number_format($product['total_liters'], 2) }} L</td>
                            <td>{{ number_format($product['total_amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-label">Salesman Signature</div>
                    <div style="height: 50px;"></div>
                    <div>_________________________</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Customer Signature</div>
                    <div style="height: 50px;"></div>
                    <div>_________________________</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Manager Signature</div>
                    <div style="height: 50px;"></div>
                    <div>_________________________</div>
                </div>
            </div>
            <div style="margin-top: 30px;">
                <p>Thank you for your business!</p>
                <p>This is a computer generated receipt. No signature required.</p>
                <p>Printed on: {{ \Carbon\Carbon::now()->format('d/m/Y h:i A') }}</p>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Receipt
    </button>

    <script>
        // Auto-print after loading (optional)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // }
        
        // Close window after print (optional)
        window.onafterprint = function() {
            // window.close(); // Uncomment if you want to close window after printing
        };
    </script>
</body>
</html>