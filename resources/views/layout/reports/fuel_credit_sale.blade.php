@extends ('admin.admin_master')
@section('title', 'Saacid - Fuel Credit Sales Report')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Fuel Credit Sales Report</h4>
                    <h6>Manage Your Fuel Credit Transactions</h6>
                </div>
            </div>

            <!-- Toast Notifications -->
            @if (session('status'))
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11">
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('status') }}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11">
                    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Error!</strong>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filter Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    <form id="filterForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="clientID" class="form-label">Customer</label>
                                <select name="clientID" id="clientID" class="form-select">
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $client)
                                        <option value="{{ $client->id }}">{{ $client->customer_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="fuel_type" class="form-label">Fuel Type</label>
                                <select name="fuel_type" id="fuel_type" class="form-select">
                                    <option value="">Select Fuel Type</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate" name="startDate">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate" name="endDate">
                            </div>

                            <div class="col-12 mt-4">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-primary" onclick="getFuelCreditsReport()">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" onclick="printReport()">
                                        <i class="fas fa-print me-2"></i>Print
                                    </button>
                                    <button type="button" class="btn btn-outline-success" onclick="exportToExcel()">
                                        <i class="fas fa-table me-2"></i>Excel
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="exportToPDF()">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="generateInvoice()">
                                        <i class="fas fa-file-invoice me-2"></i>Generate Invoice
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="fas fa-sync-alt me-2"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Section -->
            <div class="card shadow-sm">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Report Results
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="visibleFuelCreditsReport">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Rate</th>
                                    <th>Total</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody id="visibleTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle me-2"></i>Use the filters above to generate a report
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-group-divider">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                    <td id="visibleTotalQuantity" class="fw-bold">0.00 L</td>
                                    <td></td>
                                    <td id="visibleGrandTotal" class="fw-bold">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Print Area with Enhanced Design -->
    <div class="d-none">
        <div id="printArea">
            <div class="invoice-container" style="max-width: 800px; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333;">
                <!-- Header Section -->
                <div class="invoice-header" style="text-align: center; padding: 20px 0; border-bottom: 2px solid #2c5aa0;">
                    <div style="display: flex; align-items: center; justify-content: center;">
                        <div style="width: 120px; height: 120px; background: #2c5aa0; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; overflow: hidden;">
                            <img src="{{ asset('/Logo/Logo1.png') }}" alt="Company Logo" width="110" height="110" style="object-fit: contain;">
                        </div>
                        <div>
                            <h1 style="font-size: 28px; font-weight: bold; margin: 0; color: #2c5aa0;">WARAABE FUEL STATION</h1>
                            <p style="margin: 5px 0; font-size: 14px;"></p>
                            <p style="margin: 3px 0;">
                                <i class="fas fa-phone-alt" style="color: #2c5aa0; margin-right: 5px;"></i>
                                <strong>Tell:</strong> 713013 | 063-XXXXXXX | 063-XXXXX
                            </p>
                            <p style="margin: 5px 0; font-size: 14px;">
                                <i class="fas fa-wallet" style="color: #2c5aa0; margin-right: 5px;"></i>
                                <strong>Merchant Accounts: Zaad : XXXXXX &nbsp; | &nbsp; E-dahab : XXXXXX</strong>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Invoice Info Section -->
                <div class="invoice-info" style="display: flex; justify-content: space-between; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-top: 10px;">
                    <div>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #2c5aa0;">INVOICE</h3>
                        <p style="margin: 5px 0; font-size: 14px;">
                            <strong>Date:</strong> <span id="invoiceDate">{{ now()->format('F j, Y') }}</span>
                        </p>
                        <p style="margin: 5px 0; font-size: 14px;">
                            <strong>Invoice #:</strong> FC-<span id="invoiceNumber">{{ sprintf('%05d', rand(1000, 99999)) }}</span>
                        </p>
                    </div>
                    
                    <div style="text-align: right;">
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #2c5aa0;" id="printCustomerName">Customer Name</h3>
                        <p style="margin: 5px 0; font-size: 14px;">
                            <i class="fas fa-calendar-alt" style="color: #2c5aa0; margin-right: 5px;"></i>
                            <span id="printDateRange">Date Range</span>
                        </p>
                    </div>
                </div>
                
                <!-- Items Table -->
                <table class="invoice-table" style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                    <thead>
                        <tr style="background: #2c5aa0; color: white;">
                            <th style="text-align: left; padding: 12px; font-weight: bold;">Date</th>
                            <th style="text-align: left; padding: 12px; font-weight: bold;">Item Name</th>
                            <th style="text-align: left; padding: 12px; font-weight: bold;">Description</th>
                            <th style="text-align: right; padding: 12px; font-weight: bold;">Qty (L)</th>
                            <th style="text-align: right; padding: 12px; font-weight: bold;">Rate ($)</th>
                            <th style="text-align: right; padding: 12px; font-weight: bold;">Amount ($)</th>
                        </tr>
                    </thead>
                    <tbody id="printTableBody">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #6c757d;">
                                No transaction data available
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8f9fa; border-top: 2px solid #2c5aa0;">
                            <td colspan="3" style="text-align: right; padding: 12px; font-weight: bold; border: none;">Total Liters</td>
                            <td id="printTotalQuantity" style="text-align: right; padding: 12px; font-weight: bold; border: none;">0.00</td>
                            <td style="text-align: right; padding: 12px; font-weight: bold; border: none;"></td>
                            <td id="printGrandTotal" style="text-align: right; padding: 12px; font-weight: bold; border: none;">$0.00</td>
                        </tr>
                    </tfoot>
                </table>
                
                <!-- Dynamic Summary Container - FIXED -->
                <div style="display: flex; justify-content: flex-end; padding-right: 10px;">
                    <div style="width: 300px;" class="summary-container">
                        <!-- This will be dynamically populated by JavaScript -->
                        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #2c5aa0; padding: 5px 0;">
                            <span style="font-weight: bold;">Balance Due:</span>
                            <span id="printCustomerBalance">$0.00</span>
                        </div>
                    </div>
                </div>
                
                <!-- Signature and Stamp Section -->
                <div style="display: flex; justify-content: space-between; padding-top: 20px; border-top: 1px dashed #ccc;">
                    <div style="text-align: center; width: 45%;">
                        <div style="height: 1px; background: #ccc; margin: 15px 0;"></div>
                        <p style="font-weight: bold; margin-bottom: 5px;">Signature</p>
                    </div>
                    
                    <div style="text-align: center; width: 45%; margin-top: 10px;">
                        <p style="font-weight: bold; margin-bottom: 5px;">Official Stamp</p>
                        <p style="font-size: 12px; color: #666; margin: 0;">TABANTAABO FUEL STATION</p>
                    </div>
                </div>
                
                <!-- Footer Section -->
                <div class="invoice-footer" style="margin-top: 30px; border-top: 2px solid #2c5aa0;">
                    <div style="text-align: center; margin-bottom: 20px; padding-top: 15px;">
                        <p style="font-size: 16px; font-weight: bold; color: #2c5aa0;">
                            <i class="fas fa-handshake" style="margin-right: 8px;"></i>Thank you for your business!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Fuel Credit Sale Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="invoiceContent">
                    <!-- Invoice content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printInvoice()">Print Invoice</button>
                </div>
            </div>
        </div>
    </div>

    <!-- External Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    <script>
        // Initialize toast notifications
        document.addEventListener('DOMContentLoaded', function() {
            const toastElList = [].slice.call(document.querySelectorAll('.toast'));
            const toastList = toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl, { delay: 5000 });
            });
            toastList.forEach(toast => toast.show());
        });

        function showReportLoading() {
            return Swal.fire({
                title: 'Loading...',
                text: 'Processing your request...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading(),
            });
        }

        function normalizeTransactions(transactions) {
            if (Array.isArray(transactions)) {
                return transactions;
            }
            if (transactions && typeof transactions === 'object') {
                return Object.values(transactions);
            }
            return [];
        }

        function getFuelCreditsReport() {
            let client = document.getElementById('clientID').value;
            let startDate = document.getElementById('startDate').value;
            let endDate = document.getElementById('endDate').value;
            let fuelType = document.getElementById('fuel_type').value;

            if (!startDate || !endDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Date Range Required',
                    text: 'Please select both start date and end date.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            const loading = showReportLoading();
            let visibleTableBody = document.getElementById('visibleTableBody');
            let printTableBody = document.getElementById('printTableBody');
            visibleTableBody.innerHTML = '';
            printTableBody.innerHTML = '';

            let url = `{{ route('fuel_credit.report') }}`;

            axios.get(url, {
                params: {
                    startDate: startDate,
                    endDate: endDate,
                    clientID: client,
                    fuel_type: fuelType,
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(response => {
                if (response.data.success) {
                    const reportData = response.data.data;
                    
                    // Store the report data globally for printing
                    window.currentReportData = reportData;

                    const transactions = normalizeTransactions(reportData.transactions);
                    reportData.transactions = transactions;
                    
                    if (transactions.length > 0) {
                        // Display all transactions
                        transactions.forEach(transaction => {
                            appendToTable(transaction, visibleTableBody, true);
                            appendToPrintTable(transaction, printTableBody);
                        });
                        
                        console.log('Report Data:', reportData);
                        
                        // Calculate total quantity
                        let totalQuantity = transactions.reduce((sum, t) => sum + (parseFloat(t.quantity) || 0), 0);
                        document.getElementById('printTotalQuantity').textContent = totalQuantity.toFixed(2);
                        document.getElementById('visibleTotalQuantity').textContent = totalQuantity.toFixed(2) + ' L';
                        document.getElementById('visibleGrandTotal').textContent = 
                            "$" + parseFloat(reportData.grand_total || 0).toFixed(2);

                    } else {
                        showNoDataMessage(visibleTableBody, printTableBody);
                    }
                } else {
                    showNoDataMessage(visibleTableBody, printTableBody, response.data.message || 'No data available');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const serverMessage = error.response?.data?.message;
                showNoDataMessage(visibleTableBody, printTableBody, serverMessage || 'Error fetching data');
                Swal.fire({
                    icon: 'error',
                    title: 'Request Failed',
                    text: serverMessage || 'An error occurred while fetching the report data.',
                    confirmButtonColor: '#0d6efd'
                });
            })
            .finally(() => {
                if (loading && typeof loading.close === 'function') {
                    loading.close();
                }
                Swal.close();
            });
        }

        function appendToTable(data, tableBody, showActions) {
            const date = data.date ? new Date(data.date).toLocaleDateString('en-GB') : 'N/A';
            const quantity = data.quantity ? parseFloat(data.quantity).toFixed(3) : '0.000';
            const rate = data.total && data.quantity ? (parseFloat(data.total) / parseFloat(data.quantity)).toFixed(2) : '0.00';
            const total = data.total ? parseFloat(data.total).toFixed(2) : '0.00';
            const description = data.description || '';

            let row = document.createElement('tr');
            row.innerHTML = `
                <td>${date}</td>
                <td>${data.client || 'N/A'}</td>
                <td>${data.product || 'N/A'}</td>
                <td>${quantity}</td>
                <td>$${rate}</td>
                <td>$${total}</td>
                <td>${description}</td>
            `;
            tableBody.appendChild(row);
        }

        function showNoDataMessage(visibleTableBody, printTableBody, message = 'No data available for the selected filters') {
            visibleTableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-info-circle me-2"></i>${message}</td></tr>`;
            printTableBody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px;">${message}</td></tr>`;
            
            // Reset totals
            document.getElementById('visibleTotalQuantity').textContent = '0.00 L';
            document.getElementById('visibleGrandTotal').textContent = '$0.00';
            document.getElementById('printTotalQuantity').textContent = '0.00';
        }

        function appendToPrintTable(data, tableBody) {
            const date = data.date ? new Date(data.date).toLocaleDateString('en-GB') : 'N/A';
            const quantity = data.quantity ? parseFloat(data.quantity).toFixed(3) : '0.000';
            const rate = (data.total && data.quantity) ? (parseFloat(data.total) / parseFloat(data.quantity)).toFixed(2) : '0.00';
            const total = data.total ? parseFloat(data.total).toFixed(2) : '0.00';
            const description = data.description || '';

            let row = document.createElement('tr');
            row.style.borderBottom = '1px solid #f1f1f1';

            row.innerHTML = `
                <td style="padding-right:10px;padding-left:10px;">${date}</td>
                <td style="font-weight:500;">${data.product || 'N/A'}</td>
                <td>${description}</td>
                <td style="text-align:right;">${quantity}</td>
                <td style="text-align:right;">$${rate}</td>
                <td style="padding-right:10px;padding-left:10px;text-align:right;font-weight:500;">$${total}</td>
            `;

            tableBody.appendChild(row);
        }

        function printReport() {
            // Check if there's data to print
            if (!window.currentReportData || !window.currentReportData.transactions || window.currentReportData.transactions.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data to Print',
                    text: 'Please generate a report first before printing.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            // Update invoice date and number
            document.getElementById('invoiceDate').textContent = new Date().toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            document.getElementById('invoiceNumber').textContent = Math.floor(Math.random() * 90000) + 10000;
            
            // Update date range in the invoice
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            if (startDate && endDate) {
                const start = new Date(startDate).toLocaleDateString();
                const end = new Date(endDate).toLocaleDateString();
                document.getElementById('printDateRange').textContent = `${start} to ${end}`;
            }

            // Update customer name if selected
            const clientSelect = document.getElementById('clientID');
            if (clientSelect.value) {
                const selectedOption = clientSelect.options[clientSelect.selectedIndex];
                document.getElementById('printCustomerName').textContent = selectedOption.text;
            }

            // Get values from the report data
            const showPreviousBalanceSection = window.currentReportData?.show_previous_balance_section || false;
            const previousBalance = window.currentReportData?.previous_balance || 0;
            const paymentsMade = window.currentReportData?.payments_made || 0;
            const balanceDue = window.currentReportData?.balance_due || window.currentReportData?.customer_balance || 0;
            const grandTotal = window.currentReportData?.grand_total || 0;
            const totalQuantity = window.currentReportData?.transactions?.reduce((sum, t) => sum + (parseFloat(t.quantity) || 0), 0) || 0;

            // Format and display values
            document.getElementById('printGrandTotal').textContent = "$" + grandTotal.toFixed(2);
            document.getElementById('printTotalQuantity').textContent = totalQuantity.toFixed(2);
            
            // Conditionally show/hide the previous balance section - FIXED
            const summaryContainer = document.querySelector('.summary-container');
            if (summaryContainer) {
                if (showPreviousBalanceSection) {
                    // Show the full summary with previous balance and paid
                    summaryContainer.innerHTML = `
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #dee2e6; padding: 5px 0;">
                            <span style="font-weight: bold;">Previous Balance:</span>
                            <span>$${previousBalance.toFixed(2)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #dee2e6; padding: 5px 0;">
                            <span style="font-weight: bold;">Paid:</span>
                            <span>$${paymentsMade.toFixed(2)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #2c5aa0; padding: 5px 0;">
                            <span style="font-weight: bold;">Balance Due:</span>
                            <span>$${balanceDue.toFixed(2)}</span>
                        </div>
                    `;
                } else {
                    // Show only the balance due
                    summaryContainer.innerHTML = `
                        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #2c5aa0; padding: 5px 0;">
                            <span style="font-weight: bold;">Balance Due:</span>
                            <span>$${balanceDue.toFixed(2)}</span>
                        </div>
                    `;
                }
            }

            // Print
            const printContents = document.getElementById('printArea').innerHTML;
            const originalContents = document.body.innerHTML;
            
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        function viewInvoice(saleId) {
            const loading = showReportLoading();
            axios.get(`{{ url('fuel-credit-sales') }}/${saleId}/invoice`)
                .then(response => {
                    document.getElementById('invoiceContent').innerHTML = response.data;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('invoiceModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Loading Invoice',
                        text: error.response?.data?.message || 'Could not load the invoice details.',
                        confirmButtonColor: '#0d6efd'
                    });
                })
                .finally(() => {
                    if (loading && typeof loading.close === 'function') {
                        loading.close();
                    }
                    Swal.close();
                });
        }

        function printInvoice() {
            const invoiceContent = document.getElementById('invoiceContent');
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Fuel Credit Sale Invoice</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .invoice-header { text-align: center; margin-bottom: 20px; }
                        .invoice-details { margin-bottom: 20px; }
                        .invoice-table { width: 100%; border-collapse: collapse; }
                        .invoice-table th, .invoice-table td { border: 1px solid #ddd; padding: 8px; }
                        .text-right { text-align: right; }
                    </style>
                </head>
                <body>
                    ${invoiceContent.innerHTML}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        function exportToExcel() {
            const table = document.getElementById("visibleFuelCreditsReport");
            
            if (!table) {
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Could not find table data to export.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            const wb = XLSX.utils.table_to_book(table, {sheet: "Fuel Credits Report"});
            XLSX.writeFile(wb, "FuelCreditsReport.xlsx");
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Add title
            doc.setFontSize(16);
            doc.text('Fuel Credits Report', 14, 15);
            
            // Add date
            doc.setFontSize(10);
            const now = new Date();
            doc.text(`Generated: ${now.toLocaleDateString()} ${now.toLocaleTimeString()}`, 14, 22);
            
            // Add table
            doc.autoTable({
                html: '#visibleFuelCreditsReport',
                startY: 30,
                theme: 'grid',
                headStyles: {
                    fillColor: [13, 110, 253],
                    textColor: 255
                },
                alternateRowStyles: {
                    fillColor: [240, 240, 240]
                }
            });
            
            doc.save('FuelCreditsReport.pdf');
        }

        function generateInvoice() {
            // Get filter values
            let client = document.getElementById('clientID').value;
            let startDate = document.getElementById('startDate').value;
            let endDate = document.getElementById('endDate').value;
            let fuelType = document.getElementById('fuel_type').value;

            if (!startDate || !endDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Date Range Required',
                    text: 'Please select both start date and end date.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            // Generate report first, then print
            getFuelCreditsReport();
            
            // Show success message and offer to print
            setTimeout(() => {
                if (window.currentReportData && window.currentReportData.transactions && window.currentReportData.transactions.length > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Report Generated',
                        text: 'Your invoice has been generated. Would you like to print it now?',
                        showCancelButton: true,
                        confirmButtonText: 'Print',
                        cancelButtonText: 'Later',
                        confirmButtonColor: '#0d6efd'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            printReport();
                        }
                    });
                }
            }, 1000);
        }
    </script>

    <style>
        .card {
            border: none;
            border-radius: 10px;
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        .btn {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
        }
        .toast {
            border-radius: 8px;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        /* Print-specific styles */
        @media print {
            @page {
                margin: 1cm;
                size: auto;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                color: #333;
                line-height: 1.4;
            }
            
            .invoice-container {
                max-width: 100%;
                margin: 0;
            }
            
            .invoice-header {
                page-break-after: avoid;
            }
            
            .invoice-table {
                page-break-inside: avoid;
            }
            
            .invoice-footer {
                page-break-before: avoid;
            }
            
            /* Ensure colors print correctly */
            .invoice-header, 
            .invoice-table thead {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        @media print {
            @page {
                size: portrait;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .invoice-container {
                margin: 0;
                padding: 15px;
                width: 100%;
            }
            .page-break {
                page-break-after: always;
            }
            .invoice-table tbody tr {
                height: 20px;
            }
        }
    </style>
@endsection
