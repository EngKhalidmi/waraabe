@extends ('admin.admin_master')
@section('title', 'Saacid - Sales Report')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Sales Report</h4>
                    <h6>Manage Your Inventory Sales Report</h6>
                </div>
            </div>

            @if (session('status'))
                <div class="toast-container">
                    <div class="toast-message success">
                        <div class="toast-icon">
                            <i data-lucide="circle-check" class="icon-checkmark"></i>
                        </div>
                        <div class="toast-content">
                            <strong>Success!</strong>
                            <p>{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="toast-container">
                    <div class="toast-message error">
                        <div class="toast-icon">
                            <i data-lucide="circle-alert" class="icon-error"></i>
                        </div>
                        <div class="toast-content">
                            <strong>Error!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-top">
                        <div class="search-set">
                            <div class="search-path">
                                <a class="btn btn-filter" id="filter_search">
                                    <img src="{{ asset('/assets/img/icons/filter.svg') }}" alt="img">
                                    <span><img src="{{ asset('/assets/img/icons/closes.svg') }}" alt="img"></span>
                                </a>
                            </div>
                            <div class="search-input">
                                <a class="btn btn-searchset"><img src="{{ asset('/assets/img/icons/search-white.svg') }}"
                                        alt="img"></a>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="filter_inputs">
                        <div class="card-body pb-0">
                            <form id="filterForm">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-3 col-sm-6 col-13">
                                        <div class="form-group">
                                            <select name="proID" id="proID" class="select">
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-13">
                                        <div class="form-group">
                                            <select name="depID" id="depID" class="select">
                                                <option value="">Select Department</option>
                                                @foreach ($departments as $dep)
                                                    <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" id="salesID" name="salesID" placeholder="Sales ID">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="date" class="form-control" id="startDate" name="startDate">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="date" class="form-control" id="endDate" name="endDate">
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-sm-6 col-12  ms-auto">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-filters ms-auto"
                                                onclick="getSalesReport()" id="searchBtn">
                                                <img src="{{ asset('/assets/img/icons/search-whites.svg') }}"
                                                    alt="img">
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <button type="button" class="btn btn-primary" onclick="printReport()">
                                            <i data-lucide="printer"></i> <span class="ml-2">Print</span>
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                            <i data-lucide="table"></i> <span class="ml-2">Excel</span>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                                            <i data-lucide="file-text"></i> <span class="ml-2">PDF</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card summary-card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Products</h5>
                                    <h2 id="totalProducts">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card summary-card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Quantity</h5>
                                    <h2 id="totalQuantity">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card summary-card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Revenue</h5>
                                    <h2 id="totalRevenue">$0.00</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card summary-card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Profit</h5>
                                    <h2 id="totalProfit">$0.00</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Print Area (Hidden) -->
                    <div class="table-responsive" id="printArea" style="display:none;">
                        <center>
                             <img src="{{ asset('/Logo/Report-logo.png') }}" width="150" alt="Company Logo">


                <h1>{{ $settings->company_name ?? 'WARAABE FUEL STATIONS' }}</h1>
                <p>{{ $settings->company_address ?? 'Kaalinta Shiidaalka Waraabe, Berbera Somaliland' }}
                    <br>
                    Tel: {{ $settings->phone1 ?? '' }}{{ !empty($settings->phone2) ? ' | ' . $settings->phone2 : '' }} <br>
                    ZAAD: {{ $settings->zaad ?? '' }} | EDAHAB: {{ $settings->edahab ?? '' }}
                </p>
                            <hr>
                            <h4 class="card-title">Sales Summary Report</h4>
                            <p id="printTimeframe">Select date range</p>
                        </center>
                        <table class="table mt-4" id="SalesReport">
                            <thead>
                                <tr class="bg-primary text-white">
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Avg Price</th>
                                    <th>Total Revenue</th>
                                    <th>Total Profit</th>
                                    <th>Profit Margin</th>
                                    <th>Sales Count</th>
                                </tr>
                            </thead>
                            <tbody id="printTableBody">
                                <tr>
                                    <td colspan="9" class="text-center">No data available</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td colspan="5" class="text-end"><strong>Grand Totals:</strong></td>
                                    <td id="printTotalRevenue">$0.00</td>
                                    <td id="printTotalProfit">$0.00</td>
                                    <td id="printProfitMargin">0%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Visible Report -->
                    <div class="table-responsive mt-4">
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Sales Summary</h5>
                            <p id="visibleTimeframe" class="text-muted">Select date range</p>
                        </div>
                        <table class="table table-hover" id="visibleSalesReport">
                            <thead class="table-primary">
                                <tr>
                                    <th class="text-white">#</th>
                                    <th class="text-white">Product</th>
                                    <th class="text-white">Unit</th>
                                    <th class="text-white">Quantity</th>
                                    <th class="text-white">Avg Price</th>
                                    <th class="text-white">Total Revenue</th>
                                    <th class="text-white">Total Profit</th>
                                    <th class="text-white">Profit Margin</th>
                                    <th class="text-white">Sales Count</th>
                                </tr>
                            </thead>
                            <tbody id="visibleTableBody">
                                <tr>
                                    <td colspan="9" class="text-center">No data available</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-active">
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Grand Totals:</strong></td>
                                    <td id="visibleTotalRevenue">$0.00</td>
                                    <td id="visibleTotalProfit">$0.00</td>
                                    <td id="visibleProfitMargin">0%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Detailed Sales Table -->
                    <div class="table-responsive mt-5">
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Detailed Sales Records</h5>
                            <p id="detailedTimeframe" class="text-muted">Select date range</p>
                        </div>
                        <table class="table table-hover" id="detailedSalesReport">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Actual Price</th>
                                    <th>Profit/Unit</th>
                                    <th>Total</th>
                                    <th>Total Profit</th>
                                    <th>Sales ID</th>
                                    <th>Payment Method</th>
                                </tr>
                            </thead>
                            <tbody id="detailedTableBody">
                                <tr>
                                    <td colspan="11" class="text-center">No data available</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>

    <script>
        // Format currency
        function formatCurrency(amount) {
            return '$' + parseFloat(amount).toFixed(2);
        }

        // Format date
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }

        // Format timeframe
        function formatTimeframe(start, end) {
            if (!start || !end) return 'Select date range';
            return `From ${formatDate(start)} to ${formatDate(end)}`;
        }

        function getSalesReport() {
            let proID = document.getElementById('proID').value;
            let salesID = document.getElementById('salesID').value;
            let startDate = document.getElementById('startDate').value;
            let endDate = document.getElementById('endDate').value;
            let depID = document.getElementById('depID').value;

            // Validate if both dates are provided
            if ((startDate && !endDate) || (!startDate && endDate)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Sorry...',
                    text: 'Please Select Both Start Date and End Date.',
                });
                return;
            }

            let url = `{{ route('info.sales') }}`;

            axios.get(url, {
                    params: {
                        startDate: startDate,
                        endDate: endDate,
                        proID: proID,
                        salesID: salesID,
                        depID: depID
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .then(response => {
                    if (response.data.success) {
                        populateSalesData(response.data);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data',
                            text: response.data.message,
                        });
                        clearTables();
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while fetching data',
                    });
                    clearTables();
                });
        }

        function populateSalesData(data) {
            let visibleTableBody = $('#visibleSalesReport tbody');
            let printTableBody = $('#printTableBody');
            let detailedTableBody = $('#detailedTableBody');

            // Clear tables
            visibleTableBody.empty();
            printTableBody.empty();
            detailedTableBody.empty();

            // Update summary cards
            $('#totalProducts').text(data.metadata.total_products);
            $('#totalQuantity').text(data.metadata.total_quantity);
            $('#totalRevenue').text(formatCurrency(data.metadata.grand_total_revenue));
            $('#totalProfit').text(formatCurrency(data.metadata.grand_total_profit));

            const timeframe = formatTimeframe(data.metadata.timeframe.start, data.metadata.timeframe.end);
            $('#timeframe').text(timeframe);
            $('#visibleTimeframe').text(timeframe);
            $('#printTimeframe').text(timeframe);
            $('#detailedTimeframe').text(timeframe);

            // Update totals in tables
            $('#visibleTotalRevenue').text(formatCurrency(data.metadata.grand_total_revenue));
            $('#visibleTotalProfit').text(formatCurrency(data.metadata.grand_total_profit));
            $('#visibleProfitMargin').text(data.metadata.overall_profit_margin);

            $('#printTotalRevenue').text(formatCurrency(data.metadata.grand_total_revenue));
            $('#printTotalProfit').text(formatCurrency(data.metadata.grand_total_profit));
            $('#printProfitMargin').text(data.metadata.overall_profit_margin);

            // Populate summary tables with data
            if (data.data.summary && data.data.summary.length > 0) {
                data.data.summary.forEach((product, index) => {
                    const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${product.product_name}</td>
                    <td>${product.unit}</td>
                    <td>${product.total_quantity}</td>
                    <td>${formatCurrency(product.average_price)}</td>
                    <td>${formatCurrency(product.total_revenue)}</td>
                    <td>${formatCurrency(product.total_profit)}</td>
                    <td>${product.profit_margin}</td>
                    <td>${product.sales_count}</td>
                </tr>
            `;
                    visibleTableBody.append(row);
                    printTableBody.append(row);
                });
            } else {
                const noDataRow = '<tr><td colspan="9" class="text-center">No data found</td></tr>';
                visibleTableBody.append(noDataRow);
                printTableBody.append(noDataRow);
            }

            // Populate detailed records
            if (data.data.detailed && data.data.detailed.length > 0) {
                data.data.detailed.forEach((sale, index) => {
                    const row = `
                <tr>
                    <td>${sale.date}</td>
                    <td>${sale.item}</td>
                    <td>${sale.unit}</td>
                    <td>${sale.quantity}</td>
                    <td>${sale.price}</td>
                    <td>${sale.actual_price}</td>
                    <td>${sale.profit_per_unit}</td>
                    <td>${sale.total}</td>
                    <td>${sale.total_profit}</td>
                    <td>${sale.salesID}</td>
                    <td>${sale.payment_method}</td>
                </tr>
            `;
                    detailedTableBody.append(row);
                });
            } else {
                detailedTableBody.append('<tr><td colspan="11" class="text-center">No detailed records found</td></tr>');
            }
        }

        function clearTables() {
            $('#visibleSalesReport tbody').empty().append(
                '<tr><td colspan="9" class="text-center">No data available</td></tr>');
            $('#printTableBody').empty().append('<tr><td colspan="9" class="text-center">No data available</td></tr>');
            $('#detailedTableBody').empty().append('<tr><td colspan="11" class="text-center">No data available</td></tr>');

            $('#totalProducts').text('0');
            $('#totalQuantity').text('0');
            $('#totalRevenue').text('$0.00');
            $('#totalProfit').text('$0.00');

            $('#timeframe').text('Select date range');
            $('#visibleTimeframe').text('Select date range');
            $('#printTimeframe').text('Select date range');
            $('#detailedTimeframe').text('Select date range');

            $('#visibleTotalRevenue').text('$0.00');
            $('#visibleTotalProfit').text('$0.00');
            $('#visibleProfitMargin').text('0%');

            $('#printTotalRevenue').text('$0.00');
            $('#printTotalProfit').text('$0.00');
            $('#printProfitMargin').text('0%');
        }

        function printReport() {
            // Show the print area temporarily
            $('#printArea').show();

            // Print the document
            window.print();

            // Hide the print area again
            $('#printArea').hide();
        }

        function exportToExcel() {
            // Create workbook
            const wb = XLSX.utils.book_new();

            // Add summary sheet
            const summaryTable = document.getElementById("SalesReport");
            const summaryWs = XLSX.utils.table_to_sheet(summaryTable);
            XLSX.utils.book_append_sheet(wb, summaryWs, "Summary");

            // Add detailed sheet
            const detailedTable = document.getElementById("detailedSalesReport");
            const detailedWs = XLSX.utils.table_to_sheet(detailedTable);
            XLSX.utils.book_append_sheet(wb, detailedWs, "Detailed Records");

            // Save the file
            XLSX.writeFile(wb, "SalesReport.xlsx");
        }

        function exportToPDF() {
            // Create new PDF
            const doc = new jsPDF('p', 'pt');

            // Add title
            doc.setFontSize(18);
            doc.text("Sales Summary Report", 300, 30, {
                align: 'center'
            });

            // Add timeframe
            doc.setFontSize(12);
            doc.text($('#printTimeframe').text(), 300, 50, {
                align: 'center'
            });

            // Add summary table
            doc.autoTable({
                html: '#SalesReport',
                startY: 70,
                styles: {
                    halign: 'center',
                    cellPadding: 3,
                    fontSize: 8
                },
                headStyles: {
                    fillColor: [41, 128, 185],
                    textColor: 255
                },
                alternateRowStyles: {
                    fillColor: [245, 245, 245]
                }
            });

            // Add detailed table on a new page
            doc.addPage();
            doc.setFontSize(18);
            doc.text("Detailed Sales Records", 300, 30, {
                align: 'center'
            });
            doc.setFontSize(12);
            doc.text($('#printTimeframe').text(), 300, 50, {
                align: 'center'
            });

            doc.autoTable({
                html: '#detailedSalesReport',
                startY: 70,
                styles: {
                    cellPadding: 3,
                    fontSize: 7,
                    overflow: 'linebreak'
                },
                headStyles: {
                    fillColor: [108, 117, 125],
                    textColor: 255
                },
                columnStyles: {
                    0: {
                        cellWidth: 60
                    },
                    1: {
                        cellWidth: 80
                    },
                    2: {
                        cellWidth: 40
                    },
                    3: {
                        cellWidth: 40
                    },
                    4: {
                        cellWidth: 40
                    },
                    5: {
                        cellWidth: 40
                    },
                    6: {
                        cellWidth: 40
                    },
                    7: {
                        cellWidth: 40
                    },
                    8: {
                        cellWidth: 40
                    },
                    9: {
                        cellWidth: 60
                    },
                    10: {
                        cellWidth: 60
                    }
                }
            });

            // Save the PDF
            doc.save('SalesReport.pdf');
        }

        // Initialize with empty data
        document.addEventListener('DOMContentLoaded', function() {
            clearTables();
        });
    </script>


    <style>
        .summary-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card .card-body {
            padding: 1.5rem;
        }

        .summary-card .card-title {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .summary-card h2 {
            font-size: 1.8rem;
            margin-bottom: 0;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(41, 128, 185, 0.1);
        }

        .table-primary th {
            background-color: #2980b9;
            color: white;
        }

        .table-active {
            background-color: #f8f9fa;
        }

        #printArea {
            padding: 20px;
            background-color: white;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }

        /* Additional styling for better appearance */
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .btn-primary {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .btn-success {
            background-color: #27ae60;
            border-color: #27ae60;
        }

        .btn-danger {
            background-color: #e74c3c;
            border-color: #e74c3c;
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
            padding: 15px;
            border-radius: 8px;
            color: white;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-width: 400px;
        }

        .toast-message.success {
            background-color: #27ae60;
        }

        .toast-message.error {
            background-color: #e74c3c;
        }

        .toast-icon {
            font-size: 24px;
            margin-right: 15px;
        }

        .toast-content strong {
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        .toast-content p,
        .toast-content ul {
            margin: 0;
            font-size: 14px;
        }

        .toast-content ul {
            padding-left: 20px;
        }
    </style>

@endsection
