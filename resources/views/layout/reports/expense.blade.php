@extends ('admin.admin_master')
@section('title', 'Saacid - Expense Transactions Report')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Expense Transactions Report</h4>
                    <h6>Manage Your Expense Transactions</h6>
                </div>
            </div>

            <!-- Toast messages code remains the same -->

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
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" id="type" name="type" placeholder="Expense Type"
                                                class="form-control">
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
                                    <div class="col-lg-3 col-sm-6 col-13">
                                        <div class="form-group">
                                            <select name="salesman_id" id="salesman_id" class="select">
                                                <option value="">Select Salesman</option>
                                                @foreach ($salesman as $sales)
                                                    <option value="{{ $sales->id }}">{{ $sales->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-sm-6 col-12  ms-auto">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-filters ms-auto"
                                                onclick="getExpenseReport()" id="searchBtn"><img
                                                    src="{{ asset('/assets/img/icons/search-whites.svg') }}"
                                                    alt="img"></button>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <button type="button" class="btn btn-primary" onclick="printReport()"><i
                                                class="fas fa-print"></i> <span class="ml-2">Print</span></button>
                                        <button type="button" class="btn btn-success" onclick="exportToExcel()"><i
                                                class="fas fa-table"></i> <span class="ml-2">Excel</span></button>
                                        <button type="button" class="btn btn-danger" onclick="exportToPDF()"><i
                                                class="fas fa-file-pdf"></i> <span class="ml-2">PDF</span></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Print Area (hidden) -->
                    <div class="d-none">
                        <div id="printArea">
                            <div class="invoice-container"
                                style="max-width: 800px; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333;">
                                <!-- Header Section -->
                                <div class="invoice-header"
                                    style="text-align: center; margin-bottom: 25px; padding: 20px 0; border-bottom: 2px solid #2c5aa0;">
                                    <div
                                        style="display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                                        <!-- Logo -->
                                        <div
                                            style="width: 120px; height: 120px; background: #2c5aa0; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; overflow: hidden;">
                                            <img src="{{ asset('/Logo/Logo1.png') }}" alt="Company Logo" width="110"
                                                height="110" style="object-fit: contain;">
                                        </div>
                                        <div>
                                            <h1 style="font-size: 28px; font-weight: bold; margin: 0; color: #2c5aa0;">
                                                WARAABE FUEL STATION</h1>
                                            <p style="margin: 5px 0; font-size: 14px;">
                                                Kaalinta Shiidaalka Waraabe<br>
                                                Berbera Somaliland
                                            </p>

                                            <p style="margin: 3px 0;">
                                                <i class="fas fa-phone-alt" style="color: #2c5aa0; margin-right: 5px;"></i>
                                                <strong>Tell:</strong> XXXXX | 063-XXXXXX | 063-XXXXXX
                                            </p>

                                            <p style="margin: 5px 0; font-size: 14px;">
                                                <i class="fas fa-wallet" style="color: #2c5aa0; margin-right: 5px;"></i>
                                                <strong>Merchant Accounts: Zaad : XXXXX &nbsp; | &nbsp; E-dahab :
                                                    XXXXX</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Report Title Section -->
                                <div class="invoice-info"
                                    style="display: flex; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                    <div>
                                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #2c5aa0;">EXPENSE
                                            TRANSACTIONS REPORT</h3>
                                    </div>

                                    <div style="text-align: right;">
                                        <p style="margin: 5px 0; font-size: 14px;">
                                            <i class="fas fa-calendar-alt" style="color: #2c5aa0; margin-right: 5px;"></i>
                                            <span id="printDateRange">Date Range</span>
                                        </p>
                                        <p style="margin: 5px 0; font-size: 14px;">
                                            <strong>Report Date:</strong> {{ now()->format('F j, Y') }}
                                        </p>
                                        <p style="margin: 5px 0; font-size: 12px; color: #6c757d;" id="printFilterInfo">
                                            <!-- Filter info will be added here -->
                                        </p>
                                    </div>
                                </div>

                                <!-- Expenses Table -->
                                <table class="invoice-table"
                                    style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                                    <thead>
                                        <tr style="background: #2c5aa0; color: white;">
                                            <th style="text-align: left; padding: 12px; font-weight: bold;">Date</th>
                                            <th style="text-align: left; padding: 12px; font-weight: bold;">Type</th>
                                            <th style="text-align: left; padding: 12px; font-weight: bold;">Salesman</th>
                                            <th style="text-align: right; padding: 12px; font-weight: bold;">Amount</th>
                                            <th style="text-align: left; padding: 12px; font-weight: bold;">Description
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="printTableBody">
                                        <!-- Data will be appended here by JS -->
                                    </tbody>
                                </table>

                                <!-- Totals Section -->
                                <div style="display: flex; justify-content: flex-end; padding-right: 10px;">
                                    <div style="width: 300px;">
                                        <div
                                            style="display: flex; justify-content: space-between; padding: 12px 0; border-top: 2px solid #2c5aa0;">
                                            <span style="font-weight: bold; font-size: 16px;">Total Expenses:</span>
                                            <span style="font-weight: bold; font-size: 16px; color: #dc3545;"
                                                id="printTotalExpenses">$0.00</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer Section -->
                                <div class="invoice-footer"
                                    style="margin-top: 40px; padding: 20px 0; border-top: 2px solid #2c5aa0;">
                                    <div style="text-align: center; margin-bottom: 20px;">
                                        <p style="font-size: 16px; font-weight: bold; color: #2c5aa0;">
                                            <i class="fas fa-file-invoice-dollar" style="margin-right: 8px;"></i>Expense
                                            Management Report
                                        </p>
                                    </div>

                                    <div style="display: flex; justify-content: space-between;">
                                        <div>
                                            <p style="margin: 5px 0; font-weight: bold;">Authorized Signature</p>
                                            <div style="height: 60px; width: 200px; border-bottom: 1px solid #dee2e6;">
                                            </div>
                                        </div>

                                        <div style="text-align: right;">
                                            <p style="margin: 5px 0; font-size: 12px; color: #6c757d;">
                                                Report generated on: {{ now()->format('F j, Y \\a\\t g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visible table -->
                    <div class="table-responsive mt-4">
                        <table class="table mt-4" id="visibleExpenseReport">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Salesman</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody id="visibleTableBody">
                                <!-- Data will be appended here by JS -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total Expenses:</strong></td>
                                    <td id="visibleTotalExpenses"><strong style="color: #dc3545;">$0.00</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <!-- Include other JavaScript here -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Get CSRF token from Laravel
        function getCsrfToken() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                return metaTag.getAttribute('content');
            }

            const csrfInput = document.querySelector('input[name="_token"]');
            if (csrfInput) {
                return csrfInput.value;
            }

            console.warn('CSRF token not found');
            return '';
        }

        function getExpenseReport() {
            let type = document.getElementById('type').value;
            let salesman_id = document.getElementById('salesman_id').value;
            let startDate = document.getElementById('startDate').value;
            let endDate = document.getElementById('endDate').value;

            // Validate if both dates are provided
            if (!startDate || !endDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Sorry...',
                    text: 'Please Select Both Start Date and End Date.',
                });
                return;
            }

            let visibleTableBody = document.querySelector('#visibleExpenseReport tbody');
            let printTableBody = document.querySelector('#printTableBody');

            if (!visibleTableBody || !printTableBody) {
                console.error('Table bodies not found');
                return;
            }

            visibleTableBody.innerHTML = ''; // Clear the table body before adding new data
            printTableBody.innerHTML = ''; // Clear the print table body before adding new data

            // Show loading indicator
            const loading = Swal.fire({
                title: 'Loading...',
                text: 'Fetching expense data',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let url = `{{ route('info.expense') }}`;
            const csrfToken = getCsrfToken();

            axios.get(url, {
                    params: {
                        startDate: startDate,
                        endDate: endDate,
                        type: type,
                        salesman_id: salesman_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    loading.close();

                    if (response.data.success) {
                        // Update print date range
                        document.getElementById('printDateRange').textContent = 'From ' + formatDate(startDate) +
                            ' to ' + formatDate(endDate);

                        // Update filter info
                        let filterInfo = 'All Expenses';
                        if (type) filterInfo = `Type: ${type}`;
                        if (salesman_id) {
                            const salesmanSelect = document.getElementById('salesman_id');
                            const salesmanName = salesmanSelect.options[salesmanSelect.selectedIndex].text;
                            filterInfo = salesmanName;
                        }
                        document.getElementById('printFilterInfo').textContent = filterInfo;

                        let totalExpenses = 0;

                        if (response.data.data.length > 0) {
                            response.data.data.forEach(record => {
                                appendToTable(record, visibleTableBody);
                                appendToTable(record, printTableBody);
                                totalExpenses += parseFloat(record.amount.replace(/,/g, '')) || 0;
                            });
                        } else {
                            visibleTableBody.innerHTML =
                                '<tr><td colspan="5" class="text-center">No data found</td></tr>';
                            printTableBody.innerHTML =
                                '<tr><td colspan="5" style="text-align: center; padding: 20px;">No data found</td></tr>';
                        }

                        // Update total expenses
                        document.getElementById('visibleTotalExpenses').innerHTML =
                            '<strong style="color: #dc3545;">$' + totalExpenses.toFixed(2) + '</strong>';
                        document.getElementById('printTotalExpenses').textContent = '$' + totalExpenses.toFixed(2);

                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data',
                            text: response.data.message,
                        });
                        visibleTableBody.innerHTML = '<tr><td colspan="5" class="text-center">' + response.data
                            .message + '</td></tr>';
                        printTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">' +
                            response.data.message + '</td></tr>';
                    }
                })
                .catch(error => {
                    loading.close();
                    console.error('API Error:', error);

                    let errorMessage = 'Error fetching data';
                    if (error.response) {
                        errorMessage = error.response.data.message || `Server error: ${error.response.status}`;
                    } else if (error.request) {
                        errorMessage = 'Network error: No response from server';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                    });

                    visibleTableBody.innerHTML = '<tr><td colspan="5" class="text-center">' + errorMessage +
                        '</td></tr>';
                    printTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">' +
                        errorMessage + '</td></tr>';
                });
        }

        function appendToTable(data, tableBody) {
            if (!tableBody) return;

            var tableRow = '<tr>' +
                '<td style="padding: 10px;">' + (data.date || '') + '</td>' +
                '<td style="padding: 10px;">' + (data.type || '') + '</td>' +
                '<td style="padding: 10px;">' + (data.salesman_name || 'N/A') + '</td>' +
                '<td style="padding: 10px; text-align: right; color: #dc3545; font-weight: 500;">$' +
                parseFloat(data.amount.replace(/,/g, '') || 0).toFixed(2) + '</td>' +
                '<td style="padding: 10px;">' + (data.info || '') + '</td>' +
                '</tr>';

            tableBody.innerHTML += tableRow;
        }

        function printReport() {
            const printArea = document.getElementById('printArea');
            if (!printArea) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Print area not found',
                });
                return;
            }

            // Get the print area content
            const printContent = printArea.innerHTML;

            // Create a new window for printing
            const printWindow = window.open('', '_blank', 'width=900,height=650');

            // Write the HTML content to the new window
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Expense Transactions Report</title>
                    <style>
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            color: #333;
                            margin: 0;
                            padding: 20px;
                        }
                        .invoice-container {
                            max-width: 800px;
                            margin: 0 auto;
                        }
                        .invoice-header {
                            text-align: center;
                            margin-bottom: 25px;
                            padding: 20px 0;
                            border-bottom: 2px solid #2c5aa0;
                        }
                        .invoice-info {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 20px;
                            padding: 15px;
                            background: #f8f9fa;
                            border-radius: 8px;
                        }
                        .invoice-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 25px;
                        }
                        .invoice-table th {
                            text-align: left;
                            padding: 12px;
                            font-weight: bold;
                            background: #2c5aa0;
                            color: white;
                        }
                        .invoice-table td {
                            padding: 10px;
                            border-bottom: 1px solid #f1f1f1;
                        }
                        .invoice-footer {
                            margin-top: 40px;
                            padding: 20px 0;
                            border-top: 2px solid #2c5aa0;
                        }
                        @media print {
                            body {
                                padding: 0;
                                margin: 0;
                            }
                            .invoice-container {
                                max-width: 100%;
                            }
                            @page {
                                margin: 20mm;
                            }
                        }
                    </style>
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                </head>
                <body>
                    ${printContent}
                </body>
                </html>
            `);

            printWindow.document.close();

            // Wait for images to load before printing
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }

        // Helper function to format date
        function formatDate(dateString) {
            if (!dateString) return '';

            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (error) {
                console.error('Error formatting date:', error);
                return dateString;
            }
        }

        function exportToExcel() {
            const table = document.getElementById("visibleExpenseReport");
            if (!table) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Table not found for export',
                });
                return;
            }

            try {
                const wb = XLSX.utils.table_to_book(table);
                XLSX.writeFile(wb, "ExpenseReport.xlsx");
            } catch (error) {
                console.error('Excel export error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Error exporting to Excel: ' + error.message,
                });
            }
        }

        function exportToPDF() {
            const table = document.getElementById("visibleExpenseReport");
            if (!table) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Table not found for export',
                });
                return;
            }

            try {
                const doc = new jsPDF();
                doc.autoTable({
                    html: table,
                    theme: 'grid',
                    styles: {
                        fontSize: 8
                    },
                    headStyles: {
                        fillColor: [44, 90, 160] // #2c5aa0
                    },
                    footStyles: {
                        fillColor: [240, 240, 240],
                        textColor: [220, 53, 69], // Red color for total
                        fontStyle: 'bold'
                    }
                });
                doc.save('ExpenseReport.pdf');
            } catch (error) {
                console.error('PDF export error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Error exporting to PDF: ' + error.message,
                });
            }
        }



        // Set default headers for all axios requests
        axios.defaults.headers.common['X-CSRF-TOKEN'] = getCsrfToken();
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>

@endsection
