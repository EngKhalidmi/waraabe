<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saacid - Quotation Invoice</title>
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
            height: 60px;
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
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            <img src="{{ asset('/Logo/warsame.png') }}" alt="Company Logo">
            <div class="company-name">
                <h1>TABANTAABO FUEL STATION BURAO</h1>
                <p>Kaalinta Shiidaalka Tabantaabo
                    <br>Burco Somaliland
                    <br>
                    +252 634042473 | 634357338 | 713013 <br>
                    ZAAD: 400723 | Edahab: 731684
                </p>
                <hr>
                <p>Qutation</p>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <p>#{{ $transaction->id }}</p>
                <p>Date: {{ $transaction->date }}</p>
            </div>
        </div>

        <div class="customer-info">
            <p><strong>Invoice To:</strong></p>
            <p>{{ $transaction->customer }}</p>
            <p><strong>Phone:</strong> {{ $transaction->phone }}</p>
            {{-- <p><strong>Address:</strong> {{ $transaction->address }}</p> --}}
        </div>

        <table class="item-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>QTY</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->orders as $sale)
                    <tr>
                        <td>{{ optional($sale->pro)->name ?? 'N/A' }}</td>
                        <td>{{ $sale->qty }}, {{ $sale->unit }}</td>
                        <td>${{ number_format($sale->price, 2) }}</td>
                        <td>${{ number_format($sale->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-item">
                <span class="label">SUB TOTAL:</span>
                <span>${{ number_format($transaction->sub_total, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="label">DISCOUNT:</span>
                <span>${{ number_format($transaction->discount, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="label">NET PRICE:</span>
                <span>${{ number_format($transaction->net_price, 2) }}</span>
            </div>
        </div>

        <div class="admin-signature">
            <p><strong>Admin Signature:</strong></p>
            <div class="signature-line"></div>
        </div>
        <div class="thank-you">
            <p>@Saacid {{ now()->format('Y') }} - Powered By Taam Solutions!</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };

        document.addEventListener('keydown', function(event) {
            if (event.key === "F12" || (event.ctrlKey && event.shiftKey && event.key === "I")) {
                event.preventDefault();
            }
        });

        document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
        });
    </script>
</body>

</html>
