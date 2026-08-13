<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Saacid - Fuel Sales Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-wrapper {
            background-color: #f5f7fb;
            padding: 20px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 24px;
        }

        .card-body {
            padding: 1.5rem;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-title h4 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .page-title h6 {
            color: #6c757d;
            font-weight: 400;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 6px;
            color: #495057;
        }

        .form-control {
            border-radius: 6px;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .btn {
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-success {
            background-color: #0ab39c;
            border-color: #0ab39c;
        }

        .btn-success:hover {
            background-color: #099885;
            border-color: #099885;
        }

        .btn-danger {
            background-color: #f06548;
            border-color: #f06548;
        }

        .btn-danger:hover {
            background-color: #ee4c2a;
            border-color: #ee4c2a;
        }

        .table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            border-top: none;
        }

        .transaction-table,
        .summary-table {
            border-radius: 8px;
            overflow: hidden;
        }

        .transaction-table th,
        .transaction-table td {
            padding: 12px;
            vertical-align: middle;
        }

        .summary-table th,
        .summary-table td {
            padding: 12px;
            vertical-align: middle;
        }

        .credit-section {
            border-left: 2px solid #e9ecef;
            padding-left: 25px;
        }

        .reading-input {
            width: 100%;
        }

        .dropdown-item {
            cursor: pointer;
            padding: 8px 16px;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast-message {
            display: flex;
            align-items: center;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            color: white;
            max-width: 400px;
        }

        .toast-message.success {
            background-color: #0ab39c;
        }

        .toast-message.error {
            background-color: #f06548;
        }

        .toast-icon {
            margin-right: 12px;
            font-size: 20px;
        }

        .toast-content strong {
            display: block;
            margin-bottom: 4px;
        }

        .product-badge {
            font-size: 0.85em;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 16px;
            font-weight: 600;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
        }

        .form-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        @media (max-width: 991px) {
            .credit-section {
                border-left: none;
                border-top: 2px solid #e9ecef;
                padding-left: 15px;
                padding-top: 25px;
                margin-top: 25px;
            }
        }

        .balance-positive {
            color: #198754;
            font-weight: bold;
        }

        .balance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .balance-zero {
            color: #6c757d;
            font-weight: bold;
        }

        .payment-table input {
            text-align: right;
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 4px 8px;
        }

        .payment-table th {
            text-align: center;
        }

        .payment-table td {
            text-align: center;
        }
    </style>
@include('partials.icons')
</head>

<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <button onclick="window.history.back()" class="btn btn-primary mb-2">
                        <i data-lucide="arrow-left" class="me-2"></i> Back to Dashboard
                    </button>
                    <h4>Fuel Daily Sales Transactions</h4>
                    <h6>Record and Manage Fuel Sales</h6>
                </div>
            </div>

            <div class="row">
                <!-- Left Section - Cash Sales -->
                <div class="col-lg-8 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="fuelSalesForm" method="POST" action="{{ route('fuel.sales.store') }}" data-fuel-sales-form="main">
                                @csrf
                                <div class="form-section">
                                    <h5 class="section-title">Transaction Details</h5>
                                    <div class="row">
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Date</label>
                                                <input type="date" name="date" id="date"
                                                    value="{{ date('Y-m-d') }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Shift</label>
                                                <select name="shift" id="shift" class="form-control select">
                                                    <option value="morning">Morning</option>
                                                    <option value="evening">Evening</option>
                                                     <option value="24Hrs-Shift">24Hrs Shift</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Salesman</label>
                                                <select name="salesman_id" id="salesman_id" class="form-control select">
                                                    @foreach ($salesmen as $salesman)
                                                        <option value="{{ $salesman->id }}">{{ $salesman->full_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Product Type</label>
                                                <select name="type" id="type" class="form-control select">
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            data-rate="{{ $product->selling_price }}">
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Dispenser Phase</label>
                                                <select name="dphase" id="dphase" class="form-control select">
                                                    <option value="Phase 1">Phase 1</option>
                                                    <option value="Phase 2">Phase 2</option>

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Previous Reading</label>
                                                <input type="number" step="0.001" name="preading" id="preading"
                                                    class="form-control reading-input">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Current Reading</label>
                                                <input type="number" step="0.001" name="creading" id="creading"
                                                    class="form-control reading-input">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>Rate</label>
                                                <input type="number" step="0.001" name="rate" id="rate"
                                                    class="form-control"
                                                    value="{{ optional($products->first())->selling_price ?? 0 }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-lg-12">
                                            <button type="button" id="addTransaction" class="btn btn-primary">
                                                <i data-lucide="circle-plus" class="me-2"></i>Add Transaction
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transactions Table -->
                                <div class="form-section">
                                    <h5 class="section-title">Current Transactions</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered transaction-table">
                                            <thead>
                                                <tr>
                                                    <th>DP Phase</th>
                                                    <th>Product</th>
                                                    <th>Prev Reading</th>
                                                    <th>Curr Reading</th>
                                                    <th>Liters</th>
                                                    <th>Rate</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="transactionsTableBody">
                                                <!-- Transactions will be added here dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <input type="hidden" name="created_by" value="{{ auth()->user()->id }}"
                                    id="created_by">
                                <!-- Product Summary Table -->
                                <div class="form-section">
                                    <h5 class="section-title">Product Summary</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered summary-table">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Cash Liters</th>
                                                    <th>Credit Liters</th>
                                                    <th>Selling Rate</th>
                                                    <th>Total Cash</th>
                                                </tr>
                                            </thead>
                                            <tbody id="productSummaryBody">
                                                <!-- Product summary will be added here dynamically -->
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-active">
                                                    <td colspan="4" class="text-end fw-bold">Net Total</td>
                                                    <td id="netTotal" class="fw-bold">0.00</td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td colspan="4" class="text-end fw-bold">Cash on Hand</td>
                                                    <td>
                                                        <input type="number" step="0.01" name="cashOnHand"
                                                            id="cashOnHand" value="0.00"
                                                            class="form-control form-control-sm">
                                                    </td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td colspan="4" class="text-end fw-bold">Discount</td>
                                                    <td>
                                                        <input type="number" step="0.01" name="discount"
                                                            id="discount" value="0.00"
                                                            class="form-control form-control-sm">
                                                    </td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td colspan="4" class="text-end fw-bold">Balance</td>
                                                    <td id="balance" class="fw-bold">0.00</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-lg-12 text-end">
                                        <button type="submit" class="btn btn-success">
                                            <i data-lucide="save" class="me-2"></i>Save Transactions
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Section - Credit Transactions -->
                <div class="col-lg-4 col-sm-12 credit-section">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="section-title">Credit Transactions</h5>
                            <div class="form-section">
                                <div class="form-group">
                                    <label>Search Customer</label>
                                    <input type="text" id="customerSearch" class="form-control"
                                        placeholder="Search customer...">
                                    <div id="customerDropdown" class="dropdown-menu"
                                        style="display:none; width:100%;"></div>
                                    <input type="hidden" id="customerID">
                                </div>

                                <div id="creditForm" style="display:none;">
                                    <div class="form-group">
                                        <label>Customer Name</label>
                                        <input type="text" id="customerName" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Product</label>
                                        <select id="creditProduct" class="form-control">
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    data-rate="{{ $product->selling_price }}">{{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" step="0.001" id="creditQuantity"
                                            class="form-control" value="0">
                                    </div>

                                    <div class="form-group">
                                        <label>Rate</label>
                                        <input type="number" step="0.001" id="creditRate" class="form-control"
                                            value="{{ optional($products->first())->selling_price ?? 0 }}">
                                    </div>

                                    <div class="form-group">
                                        <label>Total</label>
                                        <input type="number" step="0.001" id="creditTotal" class="form-control"
                                            readonly>
                                    </div>

                                    <div class="form-group mb-2">
                                        <label>Description</label>
                                        <textarea id="creditDescription" class="form-control" rows="2"></textarea>
                                    </div>

                                    <button type="button" id="addCreditTransaction" class="btn btn-primary w-100">
                                        <i data-lucide="circle-plus" class="me-2"></i>Add Credit Sale
                                    </button>
                                </div>
                            </div>

                            <div class="form-section mt-4">
                                <h6 class="section-title">Credit Transactions</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Product</th>
                                                <th>Liters</th>
                                                <th>Total</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="creditTransactionsBody">
                                            <!-- Credit transactions will be added here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods Section (Replaces Expenses) -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="section-title">Payment Methods</h5>
                            <div class="form-section">
                                <div class="table-responsive">
                                    <table class="table table-bordered payment-table">
                                        <thead>
                                            <tr>
                                                <th>Payment Method</th>
                                                <th>Dollar ($)</th>
                                                <th>SLSH</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>ZAAD</td>
                                                <td>
                                                    <input type="number" step="0.01" name="zaad_dollar"
                                                        id="zaad_dollar" value="0.00" class="payment-input">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="zaad_slsh"
                                                        id="zaad_slsh" value="0.00" class="payment-input">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Edahab</td>
                                                <td>
                                                    <input type="number" step="0.01" name="edahab_dollar"
                                                        id="edahab_dollar" value="0.00" class="payment-input">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="edahab_slsh"
                                                        id="edahab_slsh" value="0.00" class="payment-input">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Cash</td>
                                                <td>
                                                    <input type="number" step="0.01" name="cash_dollar"
                                                        id="cash_dollar" value="0.00" class="payment-input">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="cash_slsh"
                                                        id="cash_slsh" value="0.00" class="payment-input">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Cashier Merchant</td>
                                                <td>
                                                    <input type="number" step="0.01" name="merchant_dollar"
                                                        id="merchant_dollar" value="0.00" class="payment-input">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="merchant_slsh"
                                                        id="merchant_slsh" value="0.00" class="payment-input">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Rate</label>
                                            <input type="number" step="0.01" name="payment_rate"
                                                id="payment_rate" value="10000.00" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="button" id="calculatePayment" class="btn btn-primary w-100">
                                            <i data-lucide="calculator" class="me-2"></i>Calculate
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        window.__FUEL_SALES_CONFIG__ = {
            createRoute: @json(route('fuel.sales.store')),
            indexRoute: @json(route('fuel.sales.index')),
            creditIndexRoute: @json(route('fuel.sales.credit.index')),
            customerSearchRoute: @json(route('customers.search')),
            customerCreateRoute: @json(route('customer.add')),
            destroyBaseUrl: @json(url('admin/delete')),
            printRouteTemplate: @json(route('fuel.sales.print', ':id'))
        };

        window.StoreManagementFuelSalesSeed = {
            products: @json($products),
            customers: @json($customers),
            salesmen: @json($salesmen)
        };
    </script>
    <script>
        $(document).ready(function() {
            let transactions = [];
            let creditTransactions = [];
            let productSummary = {};

            // Initialize product summary
            @foreach ($products as $product)
                productSummary[{{ $product->id }}] = {
                    name: '{{ $product->name }}',
                    totalLiters: 0,
                    cashLiters: 0,
                    creditLiters: 0,
                    sellingRate: {{ $product->selling_price }},
                    totalCash: 0
                };
            @endforeach

            // Helper: check if product is fuel
            function isFuelProduct(productId) {
                // Assuming 4 and 5 are fuel products (Petrol and Diesel)
                return productId == 4 || productId == 5;
            }

            // Update product rate when product type changes
            $('#type').change(function() {
                let selectedOption = $(this).find('option:selected');
                let rate = selectedOption.data('rate');
                $('#rate').val(rate);
            });

            // Update credit product rate when credit product changes
            $('#creditProduct').change(function() {
                let selectedOption = $(this).find('option:selected');
                let rate = selectedOption.data('rate');
                $('#creditRate').val(rate);
                calculateCreditTotal();
            });

            // Calculate credit total when quantity or rate changes
            $('#creditQuantity, #creditRate').on('input', calculateCreditTotal);

            function calculateCreditTotal() {
                let quantity = parseFloat($('#creditQuantity').val()) || 0;
                let rate = parseFloat($('#creditRate').val()) || 0;
                let total = quantity * rate;
                $('#creditTotal').val(total.toFixed(3));
            }

            // Customer search functionality
            $('#customerSearch').on('input', function() {
                const query = $(this).val();

                if (query.length >= 2) {
                    $('#customerDropdown').html(`
                <div class="dropdown-item text-center">
                    <i data-lucide="loader-circle" class="icon-spin" style="margin-right:8px !important;"></i> Searching...
                </div>
                `).show();

                    axios.get(`{{ route('sales.searchCustomer') }}?query=${query}`)
                        .then(response => {
                            $('#customerDropdown').empty();

                            if (response.data.length > 0) {
                                response.data.forEach(customer => {
                                    const customerOption = $(`
                                <a class="dropdown-item customer-row" 
                                  data-id="${customer.id}" 
                                  data-name="${customer.customer_name}" 
                                  data-serial="${customer.serial}">
                                    ${customer.customer_name}
                                </a>
                            `);

                                    $('#customerDropdown').append(customerOption);
                                });
                            } else {
                                $('#customerDropdown').html(`
                            <div class="dropdown-item text-center text-muted">
                                No results found
                            </div>
                        `);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching customers:', error);
                            $('#customerDropdown').hide();
                        });
                } else {
                    $('#customerDropdown').hide();
                }
            });

            // Handle customer selection
            $(document).on('click', '#customerDropdown .dropdown-item', function() {
                let customerId = $(this).data('id');
                let customerName = $(this).data('name');

                $('#customerSearch').val(customerName);
                $('#customerID').val(customerId);
                $('#customerName').val(customerName);
                $('#customerDropdown').hide();
                $('#creditForm').show();
            });

            // Add transaction to the table
            $('#addTransaction').click(function() {
                let dphase = $('#dphase').val();
                let productId = $('#type').val();
                let productName = $('#type option:selected').text();
                let preading = parseFloat($('#preading').val()) || 0;
                let creading = parseFloat($('#creading').val()) || 0;
                let rate = parseFloat($('#rate').val()) || 0;

                if (preading >= creading) {
                    alert('Current reading must be greater than previous reading');
                    return;
                }

                let liters = creading - preading;
                let total = liters * rate;

                // Add to transactions array (CASH TRANSACTION)
                transactions.push({
                    dphase,
                    productId,
                    productName,
                    preading,
                    creading,
                    liters,
                    rate,
                    total,
                    isCash: true
                });

                // Update product summary
                productSummary[productId].totalLiters += liters;
                productSummary[productId].cashLiters += liters;
                productSummary[productId].totalCash += total;

                updateTransactionsTable();
                updateProductSummary();
                calculateTotals();

                $('#preading').val('');
                $('#creading').val('');
            });

            // Add credit transaction
            $('#addCreditTransaction').click(function() {
                let customerId = $('#customerID').val();
                let customerName = $('#customerName').val();
                let productId = $('#creditProduct').val();
                let productName = $('#creditProduct option:selected').text();
                let quantity = parseFloat($('#creditQuantity').val()) || 0;
                let rate = parseFloat($('#creditRate').val()) || 0;
                let total = quantity * rate;
                let description = $('#creditDescription').val();

                if (quantity <= 0) {
                    alert('Please enter a valid quantity');
                    return;
                }

                // Add to credit transactions array
                creditTransactions.push({
                    customerId,
                    customerName,
                    productId,
                    productName,
                    quantity,
                    rate,
                    total,
                    description,
                    isCash: false
                });

                // Update product summary
                productSummary[productId].totalLiters += quantity;
                productSummary[productId].creditLiters += quantity;

                // For fuel products, we need to adjust cash liters
                if (isFuelProduct(productId)) {
                    // Convert equivalent cash liters to credit
                    productSummary[productId].cashLiters = Math.max(
                        productSummary[productId].cashLiters - quantity,
                        0
                    );
                    productSummary[productId].totalCash = Math.max(
                        productSummary[productId].totalCash - (quantity * productSummary[productId]
                            .sellingRate),
                        0
                    );
                }

                updateCreditTransactionsTable();
                updateProductSummary();
                calculateTotals();

                // Reset form
                $('#creditQuantity').val('0');
                $('#creditTotal').val('0');
                $('#creditDescription').val('');
                $('#creditForm').hide();
                $('#customerSearch').val('');
            });

            // Update the credit transactions table to show product type
            function updateCreditTransactionsTable() {
                let html = '';
                creditTransactions.forEach((transaction, index) => {
                    html += `
                        <tr>
                            <td>${transaction.customerName}</td>
                            <td>${transaction.productName} ${transaction.isFuel ? '(Fuel)' : '(Non-Fuel)'}</td>
                            <td>${transaction.quantity.toFixed(3)}</td>
                            <td>${transaction.total.toFixed(2)}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeCreditTransaction(${index})">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#creditTransactionsBody').html(html);
            }

            // Update transactions table
            function updateTransactionsTable() {
                let html = '';
                transactions.forEach((transaction, index) => {
                    html += `
                <tr>
                    <td>${transaction.dphase}</td>
                    <td>${transaction.productName}</td>
                    <td>${transaction.preading.toFixed(3)}</td>
                    <td>${transaction.creading.toFixed(3)}</td>
                    <td>${transaction.liters.toFixed(3)}</td>
                    <td>${transaction.rate.toFixed(3)}</td>
                    <td>${transaction.total.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeTransaction(${index})">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </td>
                </tr>
             `;
                });
                $('#transactionsTableBody').html(html);
            }

            // Update product summary table
            function updateProductSummary() {
                let html = '';
                let totalNet = 0;

                for (let productId in productSummary) {
                    if (productSummary.hasOwnProperty(productId)) {
                        let product = productSummary[productId];

                        // Only show products with transactions
                        if (product.totalLiters > 0) {
                            let productCash = product.totalCash;
                            totalNet += productCash;

                            html += `
                                <tr>
                                    <td>${product.name}</td>
                                    <td>${product.cashLiters.toFixed(3)}</td>
                                    <td>${product.creditLiters.toFixed(3)}</td>
                                    <td>${product.sellingRate.toFixed(3)}</td>
                                    <td>${productCash.toFixed(2)}</td>
                                </tr>
                            `;
                        }
                    }
                }

                $('#productSummaryBody').html(html);
                $('#netTotal').text(totalNet.toFixed(2));
            }

            // Calculate totals
            function calculateTotals() {
                let netTotal = 0;
                let creditTotal = 0;

                // Calculate net total from all cash transactions
                transactions.forEach(transaction => {
                    netTotal += transaction.total;
                });

                // Calculate credit total
                creditTransactions.forEach(transaction => {
                    creditTotal += transaction.total;
                });

                let cashOnHand = parseFloat($('#cashOnHand').val()) || 0;
                let discount = parseFloat($('#discount').val()) || 0;

                // Calculate balance: Net Total - Cash on Hand - Credit Total - Discount
                let balance = netTotal - cashOnHand - creditTotal - discount;

                // Update UI
                $('#netTotal').text(netTotal.toFixed(2));

                // Format balance with appropriate color
                let balanceElement = $('#balance');
                balanceElement.text(balance.toFixed(2));

                // Apply styling based on balance value
                if (balance > 0) {
                    balanceElement.removeClass('balance-negative balance-zero').addClass('balance-positive');
                } else if (balance < 0) {
                    balanceElement.removeClass('balance-positive balance-zero').addClass('balance-negative');
                } else {
                    balanceElement.removeClass('balance-positive balance-negative').addClass('balance-zero');
                }
            }

            // Update balance when cash on hand changes
            $('#cashOnHand').on('input', calculateTotals);

            // Update balance when discount changes
            $('#discount').on('input', calculateTotals);

            // Remove transaction
            window.removeTransaction = function(index) {
                let transaction = transactions[index];

                // Subtract from product summary
                productSummary[transaction.productId].totalLiters -= transaction.liters;
                productSummary[transaction.productId].cashLiters -= transaction.liters;
                productSummary[transaction.productId].totalCash -= transaction.total;

                // Remove from transactions array
                transactions.splice(index, 1);

                // Update UI
                updateTransactionsTable();
                updateProductSummary();
                calculateTotals();
            };

            // Remove credit transaction
            window.removeCreditTransaction = function(index) {
                let transaction = creditTransactions[index];

                // Subtract from product summary
                productSummary[transaction.productId].totalLiters -= transaction.quantity;
                productSummary[transaction.productId].creditLiters -= transaction.quantity;

                // For fuel products, add back to cash liters
                if (isFuelProduct(transaction.productId)) {
                    productSummary[transaction.productId].cashLiters += transaction.quantity;
                    productSummary[transaction.productId].totalCash += (transaction.quantity * productSummary[
                        transaction.productId].sellingRate);
                }

                // Remove from credit transactions array
                creditTransactions.splice(index, 1);

                // Update UI
                updateCreditTransactionsTable();
                updateProductSummary();
                calculateTotals();
            };

            // Payment calculation function
            $('#calculatePayment').click(function() {
                // Get all SLSH values
                const zaadSlsh = parseFloat($('#zaad_slsh').val()) || 0;
                const edahabSlsh = parseFloat($('#edahab_slsh').val()) || 0;
                const cashSlsh = parseFloat($('#cash_slsh').val()) || 0;
                const merchantSlsh = parseFloat($('#merchant_slsh').val()) || 0;

                // Get all Dollar values
                const zaadDollar = parseFloat($('#zaad_dollar').val()) || 0;
                const edahabDollar = parseFloat($('#edahab_dollar').val()) || 0;
                const cashDollar = parseFloat($('#cash_dollar').val()) || 0;
                const merchantDollar = parseFloat($('#merchant_dollar').val()) || 0;

                // Get rate
                const rate = parseFloat($('#payment_rate').val()) || 1;

                // Calculate total SLSH and convert to Dollar
                const totalSlsh = zaadSlsh + edahabSlsh + cashSlsh + merchantSlsh;
                const slshToDollar = totalSlsh / rate;

                // Calculate total Dollar
                const totalDollar = zaadDollar + edahabDollar + cashDollar + merchantDollar;

                // Calculate final cash on hand
                const cashOnHand = slshToDollar + totalDollar;

                // Update cash on hand field
                $('#cashOnHand').val(cashOnHand.toFixed(2));

                // Trigger balance calculation
                calculateTotals();
            });

            // Prepare final transactions for submission
            function prepareFinalTransactions() {
                // Calculate total petrol and diesel liters from ALL transactions (cash + credit)
                let totalPetrolLiters = 0;
                let totalDieselLiters = 0;

                // Calculate credit liters per product
                let creditLitersPerProduct = {};
                creditTransactions.forEach(credit => {
                    const productId = parseInt(credit.productId);
                    creditLitersPerProduct[productId] = (creditLitersPerProduct[productId] || 0) + credit
                        .quantity;

                    if (productId === 4) { // Petrol
                        totalPetrolLiters += credit.quantity;
                    } else if (productId === 5) { // Diesel
                        totalDieselLiters += credit.quantity;
                    }
                });

                // Process cash transactions and deduct credit liters
                let fuelTransactions = {};

                transactions.forEach(trx => {
                    const productId = parseInt(trx.productId);
                    let adjustedLiters = trx.liters;

                    // Deduct credit liters from cash transactions for this product
                    if (creditLitersPerProduct[productId] && creditLitersPerProduct[productId] > 0) {
                        const creditToDeduct = Math.min(creditLitersPerProduct[productId], adjustedLiters);
                        adjustedLiters -= creditToDeduct;
                        creditLitersPerProduct[productId] -= creditToDeduct;
                    }

                    if (adjustedLiters > 0) {
                        const key = `${trx.productId}-${trx.dphase}`;
                        if (!fuelTransactions[key]) {
                            fuelTransactions[key] = {
                                dphase: trx.dphase,
                                productId: trx.productId,
                                productName: trx.productName,
                                preading: trx.preading,
                                creading: trx.creading,
                                liters: adjustedLiters,
                                rate: trx.rate,
                                total: adjustedLiters * trx.rate
                            };
                        } else {
                            fuelTransactions[key].liters += adjustedLiters;
                            fuelTransactions[key].total += adjustedLiters * trx.rate;
                            fuelTransactions[key].creading = trx.creading;
                        }

                        if (productId === 4) { // Petrol
                            totalPetrolLiters += adjustedLiters;
                        } else if (productId === 5) { // Diesel
                            totalDieselLiters += adjustedLiters;
                        }
                    }
                });

                // Return both the transactions and the calculated totals
                return {
                    transactions: Object.values(fuelTransactions),
                    totalPetrolLiters: totalPetrolLiters,
                    totalDieselLiters: totalDieselLiters
                };
            }

            // // Form submission
            // $('#fuelSalesForm').submit(function(e) {
            //     e.preventDefault();
            //     const submitBtn = $(this).find('button[type="submit"]');
            //     const originalBtnText = submitBtn.html();
            //     submitBtn.prop('disabled', true).html(
            //         `<span class="spinner-border spinner-border-sm"></span> Processing...`);

            //     // Prepare transactions and get calculated totals
            //     const preparedData = prepareFinalTransactions();

            //     // Prepare payment data
            //     const paymentData = {
            //         zaad_dollar: parseFloat($('#zaad_dollar').val()) || 0,
            //         zaad_slsh: parseFloat($('#zaad_slsh').val()) || 0,
            //         edahab_dollar: parseFloat($('#edahab_dollar').val()) || 0,
            //         edahab_slsh: parseFloat($('#edahab_slsh').val()) || 0,
            //         cash_dollar: parseFloat($('#cash_dollar').val()) || 0,
            //         cash_slsh: parseFloat($('#cash_slsh').val()) || 0,
            //         merchant_dollar: parseFloat($('#merchant_dollar').val()) || 0,
            //         merchant_slsh: parseFloat($('#merchant_slsh').val()) || 0,
            //         payment_rate: parseFloat($('#payment_rate').val()) || 1
            //     };

            //     // Prepare payload
            //     let formData = {
            //         date: $('#date').val(),
            //         shift: $('#shift').val(),
            //         salesman_id: $('#salesman_id').val(),
            //         created_by: $('#created_by').val(),
            //         transactions: preparedData.transactions,
            //         credit_transactions: creditTransactions,
            //         payment_data: paymentData,
            //         discount: parseFloat($('#discount').val()) || 0,
            //         net_total: parseFloat($('#netTotal').text().replace(/,/g, '')) || 0,
            //         cash_on_hand: parseFloat($('#cashOnHand').val()) || 0,
            //         balance: parseFloat($('#balance').text().replace(/,/g, '')) || 0,
            //         total_petrol_liters: preparedData.totalPetrolLiters,
            //         total_diesel_liters: preparedData.totalDieselLiters
            //     };

            //     // Send AJAX request
            //     $.ajax({
            //         url: $(this).attr('action'),
            //         method: 'POST',
            //         contentType: 'application/json',
            //         data: JSON.stringify(formData),
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //         },
            //         success: function(response) {
            //             Swal.fire({
            //                 icon: response.success ? 'success' : 'error',
            //                 title: response.success ? 'Success' : 'Error',
            //                 text: response.message,
            //                 confirmButtonColor: response.success ? '#3085d6' : '#d33'
            //             }).then(() => {
            //                 if (response.success && response.redirect) window.location
            //                     .href = response.redirect;
            //             });
            //         },
            //         error: function(xhr) {
            //             let msg = 'An error occurred';
            //             if (xhr.status === 422) {
            //                 let errors = xhr.responseJSON.errors;
            //                 msg = Object.values(errors).map(e => e[0]).join('\n');
            //             } else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;

            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Error',
            //                 html: msg.replace(/\n/g, '<br>'),
            //                 confirmButtonColor: '#d33'
            //             });
            //         },
            //         complete: function() {
            //             submitBtn.prop('disabled', false).html(originalBtnText);
            //         }
            //     });
            // });
            
            // Form submission - UPDATED VERSION
$('#fuelSalesForm').submit(function(e) {
    e.preventDefault();
    const submitBtn = $(this).find('button[type="submit"]');
    const originalBtnText = submitBtn.html();
    submitBtn.prop('disabled', true).html(
        `<span class="spinner-border spinner-border-sm"></span> Processing...`);

    // Prepare transactions and get calculated totals
    const preparedData = prepareFinalTransactions();

    // Prepare payment data
    const paymentData = {
        zaad_dollar: parseFloat($('#zaad_dollar').val()) || 0,
        zaad_slsh: parseFloat($('#zaad_slsh').val()) || 0,
        edahab_dollar: parseFloat($('#edahab_dollar').val()) || 0,
        edahab_slsh: parseFloat($('#edahab_slsh').val()) || 0,
        cash_dollar: parseFloat($('#cash_dollar').val()) || 0,
        cash_slsh: parseFloat($('#cash_slsh').val()) || 0,
        merchant_dollar: parseFloat($('#merchant_dollar').val()) || 0,
        merchant_slsh: parseFloat($('#merchant_slsh').val()) || 0,
        payment_rate: parseFloat($('#payment_rate').val()) || 1
    };

    // Prepare FormData instead of JSON
    const formData = new FormData();
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    formData.append('date', $('#date').val());
    formData.append('shift', $('#shift').val());
    formData.append('salesman_id', $('#salesman_id').val());
    formData.append('created_by', $('#created_by').val());
    formData.append('discount', parseFloat($('#discount').val()) || 0);
    formData.append('net_total', parseFloat($('#netTotal').text().replace(/,/g, '')) || 0);
    formData.append('cash_on_hand', parseFloat($('#cashOnHand').val()) || 0);
    formData.append('balance', parseFloat($('#balance').text().replace(/,/g, '')) || 0);
    formData.append('total_petrol_liters', preparedData.totalPetrolLiters);
    formData.append('total_diesel_liters', preparedData.totalDieselLiters);
    
    // Convert arrays to JSON strings for FormData
    formData.append('transactions', JSON.stringify(preparedData.transactions));
    formData.append('credit_transactions', JSON.stringify(creditTransactions));
    formData.append('payment_data', JSON.stringify(paymentData));

    // Debug: Log what's being sent
    console.log('Sending data:', {
        date: $('#date').val(),
        shift: $('#shift').val(),
        salesman_id: $('#salesman_id').val(),
        transactions: preparedData.transactions,
        credit_transactions: creditTransactions,
        payment_data: paymentData,
        total_petrol_liters: preparedData.totalPetrolLiters,
        total_diesel_liters: preparedData.totalDieselLiters
    });

    // Send AJAX request with FormData
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: formData,
        processData: false,  // Don't process the data
        contentType: false,  // Don't set content type (FormData will set it)
        success: function(response) {
            Swal.fire({
                icon: response.success ? 'success' : 'error',
                title: response.success ? 'Success' : 'Error',
                text: response.message,
                confirmButtonColor: response.success ? '#3085d6' : '#d33'
            }).then(() => {
                if (response.success && response.redirect) {
                    window.location.href = response.redirect;
                }
            });
        },
        error: function(xhr) {
            let msg = 'An error occurred';
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                msg = Object.values(errors).map(e => e[0]).join('\n');
            } else if (xhr.responseJSON?.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.status === 419) {
                msg = 'Session expired. Please refresh the page and try again.';
            } else if (xhr.status === 500) {
                msg = 'Server error. Please check your logs.';
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: msg.replace(/\n/g, '<br>'),
                confirmButtonColor: '#d33'
            });
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
});




            // Reset form function
            function resetForm() {
                transactions = [];
                creditTransactions = [];

                // Reset product summary
                for (let productId in productSummary) {
                    productSummary[productId].cashLiters = 0;
                    productSummary[productId].creditLiters = 0;
                    productSummary[productId].totalCash = 0;
                }

                updateTransactionsTable();
                updateCreditTransactionsTable();
                updateProductSummary();
                calculateTotals();

                // Reset cash on hand and discount
                $('#cashOnHand').val('0.00');
                $('#discount').val('0.00');
            }

            window.StoreManagementFuelSalesState = {
                getTransactions: function() {
                    return transactions.slice();
                },
                getCreditTransactions: function() {
                    return creditTransactions.slice();
                },
                getProductSummary: function() {
                    return JSON.parse(JSON.stringify(productSummary));
                },
                prepareFinalTransactions: function() {
                    return prepareFinalTransactions();
                },
                getPaymentData: function() {
                    return {
                        zaad_dollar: parseFloat($('#zaad_dollar').val()) || 0,
                        zaad_slsh: parseFloat($('#zaad_slsh').val()) || 0,
                        edahab_dollar: parseFloat($('#edahab_dollar').val()) || 0,
                        edahab_slsh: parseFloat($('#edahab_slsh').val()) || 0,
                        cash_dollar: parseFloat($('#cash_dollar').val()) || 0,
                        cash_slsh: parseFloat($('#cash_slsh').val()) || 0,
                        merchant_dollar: parseFloat($('#merchant_dollar').val()) || 0,
                        merchant_slsh: parseFloat($('#merchant_slsh').val()) || 0,
                        payment_rate: parseFloat($('#payment_rate').val()) || 1
                    };
                },
                getFormValues: function() {
                    return {
                        date: $('#date').val(),
                        shift: $('#shift').val(),
                        salesman_id: $('#salesman_id').val(),
                        salesman_name: $('#salesman_id option:selected').text(),
                        created_by: $('#created_by').val(),
                        discount: parseFloat($('#discount').val()) || 0,
                        net_total: parseFloat($('#netTotal').text().replace(/,/g, '')) || 0,
                        cash_on_hand: parseFloat($('#cashOnHand').val()) || 0,
                        balance: parseFloat($('#balance').text().replace(/,/g, '')) || 0
                    };
                },
                resetForm: function() {
                    resetForm();
                },
                setCustomerSelection: function(customer) {
                    if (!customer) {
                        return;
                    }

                    $('#customerSearch').val(customer.customer_name || customer.name || '');
                    $('#customerID').val(customer.id || customer.local_id || '');
                    $('#customerName').val(customer.customer_name || customer.name || '');
                    $('#customerDropdown').hide();
                    $('#creditForm').show();
                }
            };

            // Initial calculation
            calculateTotals();
        });
    </script>
</body>

</html>
