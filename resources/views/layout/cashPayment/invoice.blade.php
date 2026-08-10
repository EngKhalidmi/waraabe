<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saacid - Sales Payment Invoice</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('/Logo/icon.png') }}">
    <style>
        @font-face {
            font-family: 'SF UI Text';
            src: url('{{ asset('/assets/fonts/SFUI/SFUIText-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        /* Import SF UI Text Bold */
        @font-face {
            font-family: 'SF UI Text';
            src: url('{{ asset('/assets/fonts/SFUI/SFUIText-Bold.ttf') }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        body {
            font-family: 'SF UI Text', sans-serif !important;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .invoice-box {
            width: 148mm;
            height: 210mm;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            /* box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); */
            color: #333;
            box-sizing: border-box;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header img {
            height: 100px;
            width: 100px;
        }

        .company-name {
            flex-grow: 1;
            text-align: center;
        }

        .company-name h1 {
            font-size: 24px;
            margin: 0;
            text-transform: uppercase;
        }

        .company-name p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            font-size: 20px;
            margin: 0;
            color: #333;
        }

        .invoice-title p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }

        .customer-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .customer-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        .customer-info strong {
            font-weight: bold;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .item-table th {
            background: #333;
            color: #fff;
            text-transform: uppercase;
            font-size: 12px;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .item-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 12px;
        }

        .summary {
            margin-top: 20px;
        }

        .summary .summary-item {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .summary .summary-item .label {
            font-weight: bold;
        }

        .admin-signature {
            margin-top: 30px;
            text-align: left;
        }

        .admin-signature .signature-line {
            margin-top: 10px;
            border-top: 2px solid #000;
            width: 200px;
        }

        .thank-you {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            font-weight: regular;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            <img src="{{ asset('/Logo/Report-logo.png') }}" alt="Company Logo">
            <div class="company-name">


                <h1>{{ $settings->company_name ?? 'WARAABE FUEL STATIONS' }}</h1>
                <p>{{ $settings->company_address ?? 'Kaalinta Shiidaalka Waraabe, Berbera Somaliland' }}
                    <br>
                    Tel: {{ $settings->phone1 ?? '' }}{{ !empty($settings->phone2) ? ' | ' . $settings->phone2 : '' }} <br>
                    ZAAD: {{ $settings->zaad ?? '' }} | EDAHAB: {{ $settings->edahab ?? '' }}
                </p>
                <hr>


                <center>
                    <p>Sales Payment Reciept</p>
                    <h1>INVOICE #{{ $transaction->id }}</h1>

                    <p>Date: {{ $transaction->paid_date }}</p>
                </center>
            </div>

        </div>

        <div class="customer-info">
            <strong>Invoice To :</strong>
            {{ $transaction->customer ? $transaction->customer->customer_name : 'N/A' }} <br>
            <strong>Phone No :</strong>
            {{ $transaction->customer ? $transaction->customer->phone : 'N/A' }}

        </div>


        <!-- <div class="bank-details">
            <strong>Bank Details:</strong><br>
            Salford & Co.<br>
            0123 4567 8901 2345
        </div> -->

        <table class="item-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>QTY</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->sales as $sale)
                    <tr>
                        <td>{{ optional($sale->product)->name ?? 'N/A' }}</td>
                        <td>{{ $sale->quantity }}, {{ $sale->unit }}</td>
                        <td>${{ number_format($sale->price, 2) }}</td>
                        <td>${{ number_format($sale->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>


        <div class="summary">
            <div class="summary-item">
                <span class="label">SUB TOTAL:</span><br>
                <span class="value">${{ number_format($transaction->sub_total, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="label">DISCOUNT:</span><br>
                <span class="value">${{ number_format($transaction->discount, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="label">NET PRICE:</span><br>
                <span class="value">${{ number_format($transaction->net_price, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="label">PAID AMOUNT:</span><br>
                <span class="value">${{ number_format($transaction->paid_amount, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="label">BALANCE:</span><br>
                <span class="value">${{ number_format($transaction->balance, 2) }}</span>
            </div>
        </div>
        <p><span class="label">Note:</span> {{ $transaction->note ? $transaction->note : '' }}</p>
        <div class="admin-signature">
            <p><strong>Admin Signature:</strong></p>
            <div class="signature-line"></div>
        </div>
        <!--<div class="thank-you"><p>@Saacid {{ now()->format('Y') }} - Powered By Taam Solutions!</p></div>-->
    </div>

    {{-- add footer the invoice --}}


    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
