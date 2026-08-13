@extends ('admin.admin_master')
@section('title', 'Saacid - Purchases Report')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Purchases Report</h4>
                    <h6>Manage Your Inventory Purchases Report</h6>
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
                                    <div class="col-lg-2 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" id="salesID" name="salesID" placeholder="Transaction ID">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <select name="payMethod" id="payMethod" class="select">
                                                <option value="">Payment Method</option>
                                                <option value="ZAAD">ZAAD</option>
                                                <option value="Credit on Book">Credit on Book</option>
                                                <option value="Cash">Cash</option>
                                                <option value="Edahab">Edahab</option>
                                            </select>
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
                                                onclick="getPurchaseReport()" id="searchBtn">
                                                <img src="{{ asset('/assets/img/icons/search-whites.svg') }}" alt="img">
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

                    <!-- Print Area (Hidden) -->
                    <div id="printArea" style="display:none;">
                        <div class="print-container" style="width: 100%; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b;">
                            <!-- Printable Header Section -->
                            <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #2c5aa0;">
                                <table style="width: 100%; border-collapse: collapse; border: none;">
                                    <tr>
                                        <td style="width: 100px; vertical-align: middle; border: none; padding: 0;">
                                            <img src="{{ asset('/Logo/Report-logo.png') }}" width="90" alt="Company Logo" style="object-fit: contain;">
                                        </td>
                                        <td style="vertical-align: middle; border: none; padding-left: 15px;">
                                            <h2 style="font-size: 22px; font-weight: 700; margin: 0; color: #2c5aa0; text-transform: uppercase; letter-spacing: 0.5px;">{{ $settings->company_name ?? 'WARAABE FUEL STATIONS' }}</h2>
                                            <p style="margin: 3px 0 0 0; font-size: 13px; color: #475569; font-weight: 500;">
                                                {{ $settings->company_address ?? 'Kaalinta Shiidaalka Waraabe • Berbera Somaliland' }}
                                            </p>
                                            <p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;">
                                                Tel: {{ $settings->phone1 ?? '' }}{{ !empty($settings->phone2) ? ' | ' . $settings->phone2 : '' }} &nbsp;|&nbsp; ZAAD: {{ $settings->zaad ?? '' }} &bull; EDAHAB: {{ $settings->edahab ?? '' }}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Printable Title Bar -->
                            <div style="margin-bottom: 20px; padding: 10px 15px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                                <table style="width: 100%; border-collapse: collapse; border: none;">
                                    <tr>
                                        <td style="border: none; padding: 0;">
                                            <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; text-transform: uppercase;">PURCHASES REPORT</h4>
                                        </td>
                                        <td style="text-align: right; border: none; padding: 0;">
                                            <span style="font-size: 11px; color: #64748b; font-weight: 500;">Printed: {{ date('d M Y, h:i A') }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Printable Table -->
                            <table class="print-table" id="PurchaseReportPrint" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                                <thead>
                                    <tr style="background-color: #2c5aa0; color: #ffffff;">
                                        <th style="padding: 9px 8px; text-align: left; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">Date</th>
                                        <th style="padding: 9px 8px; text-align: left; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">Trans ID</th>
                                        <th style="padding: 9px 8px; text-align: left; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">Supplier</th>
                                        <th style="padding: 9px 8px; text-align: left; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">User</th>
                                        <th style="padding: 9px 8px; text-align: left; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">Product</th>
                                        <th style="padding: 9px 8px; text-align: right; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">QTY</th>
                                        <th style="padding: 9px 8px; text-align: right; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">Unit Cost</th>
                                        <th style="padding: 9px 8px; text-align: right; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">Total Cost</th>
                                        <th style="padding: 9px 8px; text-align: right; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">Paid Amount</th>
                                        <th style="padding: 9px 8px; text-align: right; font-size: 12px; font-weight: 700; border: 1px solid #2c5aa0;">Balance</th>
                                    </tr>
                                </thead>
                                <tbody id="printTableBody">
                                    <!-- Data will be appended here by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Visible Report Area -->
                    <div class="table-responsive mt-4">
                        <div class="report-header-box" style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #2c5aa0;">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <img src="{{ asset('/Logo/Report-logo.png') }}" width="100" alt="Company Logo" style="object-fit: contain;">
                                    <div>
                                        <h2 style="font-size: 22px; font-weight: 700; margin: 0; color: #2c5aa0; text-transform: uppercase; letter-spacing: 0.5px;">{{ $settings->company_name ?? 'WARAABE FUEL STATIONS' }}</h2>
                                        <p style="margin: 3px 0 0 0; font-size: 13px; color: #475569; font-weight: 500;">
                                            {{ $settings->company_address ?? 'Kaalinta Shiidaalka Waraabe • Berbera Somaliland' }}
                                        </p>
                                        <p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;">
                                            Tel: {{ $settings->phone1 ?? '' }}{{ !empty($settings->phone2) ? ' | ' . $settings->phone2 : '' }} &nbsp;|&nbsp; ZAAD: {{ $settings->zaad ?? '' }} &bull; EDAHAB: {{ $settings->edahab ?? '' }}
                                        </p>
                                    </div>
                                </div>
                                <div style="text-align: right; background: #f8fafc; padding: 10px 18px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; text-transform: uppercase;">PURCHASES REPORT</h4>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 500;">Generated: {{ date('d M Y, h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div id="reportResults">
                            <!-- Transaction cards will be appended here by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        function getPurchaseReport() {
            let proID = document.getElementById('proID').value;
            let salesID = document.getElementById('salesID').value;
            let depID = document.getElementById('depID') ? document.getElementById('depID').value : '';
            let payMethod = document.getElementById('payMethod').value;
            let startDate = document.getElementById('startDate').value;
            let endDate = document.getElementById('endDate').value;

            // Validate if both dates are provided when one is filled
            if ((startDate && !endDate) || (!startDate && endDate)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Sorry...',
                    text: 'Both start date and end date are required when filtering by date.',
                });
                return;
            }

            let reportResults = document.getElementById('reportResults');
            let printTableBody = document.getElementById('printTableBody');
            
            reportResults.innerHTML = ''; // Clear previous results
            printTableBody.innerHTML = ''; // Clear print table body

            let url = `{{ route('info.purchase') }}`;

            axios.get(url, {
                    params: {
                        proID: proID,
                        salesID: salesID,
                        depID: depID,
                        payMethod: payMethod,
                        startDate: startDate,
                        endDate: endDate
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .then(response => {
                    if (response.data.success) {
                        if (response.data.data.length > 0) {
                            response.data.data.forEach(transaction => {
                                appendTransactionCard(transaction, reportResults);
                                appendToPrintTable(transaction, printTableBody);
                            });
                        } else {
                            reportResults.innerHTML = '<div class="alert alert-warning text-center">No purchase records found</div>';
                            printTableBody.innerHTML = '<tr><td colspan="10" class="text-center">No purchase records found</td></tr>';
                        }
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data',
                            text: response.data.message,
                        });
                        reportResults.innerHTML = '<div class="alert alert-warning text-center">' + response.data.message + '</div>';
                    }
                })
                .catch(error => {
                    console.error(error);
                    reportResults.innerHTML = '<div class="alert alert-danger text-center">Error fetching purchase data</div>';
                    printTableBody.innerHTML = '<tr><td colspan="10" class="text-center">Error fetching purchase data</td></tr>';
                });
        }

        function appendTransactionCard(transaction, container) {
            let transactionCard = `
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-0">Transaction #${transaction.transaction_id}</h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">${transaction.date}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Supplier:</strong> ${transaction.supplier}
                            </div>
                            <div class="col-md-4">
                                <strong>User:</strong> ${transaction.user}
                            </div>
                            <div class="col-md-4">
                                <strong>Payment:</strong> ${transaction.payMethod}
                            </div>
                        </div>
                        <div class="row mb-3 p-3 bg-light rounded border">
                            <div class="col-md-2 col-6">
                                <strong>Subtotal:</strong><br><span class="text-dark font-weight-bold">$${transaction.subtotal}</span>
                            </div>
                            <div class="col-md-2 col-6">
                                <strong>Discount:</strong><br><span class="text-dark font-weight-bold">$${transaction.discount}</span>
                            </div>
                            <div class="col-md-2 col-6">
                                <strong>Net Price:</strong><br><span class="text-dark font-weight-bold">$${transaction.net_price}</span>
                            </div>
                            <div class="col-md-3 col-6">
                                <strong>Paid Amount:</strong><br><span class="text-success font-weight-bold">$${transaction.paidAmount}</span>
                            </div>
                            <div class="col-md-3 col-6">
                                <strong>Balance:</strong><br><span class="badge bg-danger text-white fs-6 p-2">$${transaction.balance}</span>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Unit</th>
                                        <th>Quantity</th>
                                        <th>Unit Cost</th>
                                        <th>Add Cost</th>
                                        <th>Total Cost</th>
                                        <th>Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>`;
            
            if (transaction.items && transaction.items.length > 0) {
                transaction.items.forEach(item => {
                    transactionCard += `
                        <tr>
                            <td>${item.item}</td>
                            <td>${item.unit}</td>
                            <td>${item.quantity}</td>
                            <td>$${item.unit_cost}</td>
                            <td>$${item.add_cost}</td>
                            <td>$${item.total_cost}</td>
                            <td>${item.remaining}</td>
                        </tr>`;
                });
            } else {
                transactionCard += `<tr><td colspan="7" class="text-center">No items found</td></tr>`;
            }
            
            transactionCard += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>`;
            
            container.innerHTML += transactionCard;
        }

        function appendToPrintTable(transaction, tableBody) {
            if (transaction.items && transaction.items.length > 0) {
                transaction.items.forEach(item => {
                    let tableRow = '<tr>' +
                        '<td style="padding: 6px 8px;">' + (transaction.date || 'N/A') + '</td>' +
                        '<td style="padding: 6px 8px;">#' + (transaction.transaction_id || 'N/A') + '</td>' +
                        '<td style="padding: 6px 8px;">' + (transaction.supplier || 'N/A') + '</td>' +
                        '<td style="padding: 6px 8px;">' + (transaction.user || 'N/A') + '</td>' +
                        '<td style="padding: 6px 8px;">' + (item.item || 'N/A') + '</td>' +
                        '<td style="padding: 6px 8px; text-align: right;">' + (item.quantity || '0') + '</td>' +
                        '<td style="padding: 6px 8px; text-align: right;">$' + (item.unit_cost || '0.00') + '</td>' +
                        '<td style="padding: 6px 8px; text-align: right;">$' + (item.total_cost || '0.00') + '</td>' +
                        '<td style="padding: 6px 8px; text-align: right;">$' + (transaction.paidAmount || '0.00') + '</td>' +
                        '<td style="padding: 6px 8px; text-align: right;">$' + (transaction.balance || '0.00') + '</td>' +
                        '</tr>';
                    
                    tableBody.innerHTML += tableRow;
                });
            } else {
                let tableRow = '<tr>' +
                    '<td style="padding: 6px 8px;">' + (transaction.date || 'N/A') + '</td>' +
                    '<td style="padding: 6px 8px;">#' + (transaction.transaction_id || 'N/A') + '</td>' +
                    '<td style="padding: 6px 8px;">' + (transaction.supplier || 'N/A') + '</td>' +
                    '<td style="padding: 6px 8px;">' + (transaction.user || 'N/A') + '</td>' +
                    '<td colspan="6" style="padding: 6px 8px; text-align: center;">No items</td>' +
                    '</tr>';
                
                tableBody.innerHTML += tableRow;
            }
        }

        function printReport() {
            var printContent = document.getElementById('printArea').innerHTML;
            var printWindow = window.open('', '_blank', 'height=800,width=1100');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Purchases Report</title>
                    <style>
                        @page {
                            size: A4 portrait;
                            margin: 10mm;
                        }
                        body {
                            font-family: 'Segoe UI', Arial, sans-serif;
                            color: #1e293b;
                            margin: 0;
                            padding: 10px;
                            background: #ffffff;
                        }
                        table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                        }
                        .print-table th {
                            background-color: #2c5aa0 !important;
                            color: #ffffff !important;
                            padding: 8px 6px !important;
                            font-size: 11px !important;
                            font-weight: 700 !important;
                            border: 1px solid #2c5aa0 !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .print-table td {
                            padding: 6px 6px !important;
                            font-size: 11px !important;
                            border: 1px solid #cbd5e1 !important;
                        }
                        @media print {
                            body { padding: 0; margin: 0; }
                            .print-table th {
                                background-color: #2c5aa0 !important;
                                color: #ffffff !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 400);
        }

        // JavaScript code for exporting to Excel
        function exportToExcel() {
            const table = document.getElementById("PurchaseReportPrint");
            
            if (!table) {
                console.error("Table element not found.");
                return;
            }

            // Convert table to XLSX
            const wb = XLSX.utils.table_to_book(table);
            XLSX.writeFile(wb, "PurchaseReport.xlsx");
        }

        function exportToPDF() {
            const table = document.getElementById("PurchaseReportPrint");
            const doc = new jsPDF();
            
            doc.autoTable({
                html: table,
                styles: { fontSize: 8 },
                headStyles: { fillColor: [41, 128, 185] }
            });
            
            doc.save('PurchaseReport.pdf');
        }
    </script>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>
@endsection