@extends ('admin.admin_master')
@section('title', 'Saacid - Combined Fuel Sales Report')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Combined Fuel Sales Report</h4>
                    <h6>Regular and Credit Fuel Sales</h6>
                </div>
            </div>

            @if (session('status'))
                <div class="toast-container">
                    <div class="toast-message success">
                        <div class="toast-icon">
                            <i class="icon-checkmark fas fa-check-circle"></i>
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
                            <i class="icon-error fa fa-exclamation-circle"></i>
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
                        </div>
                    </div>

                    <div class="card" id="filter_inputs">
                        <div class="card-body pb-0">
                            <form id="filterForm">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Report Type</label>
                                            <select name="report_type" id="report_type" class="select">
                                                <option value="all">All Sales</option>
                                                <option value="regular">Regular Sales Only</option>
                                                <option value="credit">Credit Sales Only</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Fuel Type</label>
                                            <select name="product_id" id="product_id" class="select">
                                                <option value="">All Fuel Types</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Start Date</label>
                                            <input type="date" class="form-control" id="startDate" name="startDate">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>End Date</label>
                                            <input type="date" class="form-control" id="endDate" name="endDate">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary"
                                                onclick="getCombinedFuelReport()">
                                                Generate Report
                                            </button>
                                            <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                                <i class="fas fa-table"></i> Export to Excel
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                                                <i class="fas fa-file-pdf"></i> Export to PDF
                                            </button>
                                            <button type="button" class="btn btn-info" onclick="printReport()">
                                                <i class="fas fa-print"></i> Print Report
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Display results -->
                    <div class="table-responsive mt-4">
                        <table class="table" id="visibleFuelReport">
                            <thead>
                                <tr>

                                    <th>Type</th>

                                    <th>Product</th>
                                    <th>Liters</th>
                                    <th>Rate</th>
                                    <th>Total</th>
                                    <th>Payment Type</th>

                                </tr>
                            </thead>
                            <tbody id="visibleTableBody">
                                <!-- Data will be appended here by JS -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-right"><strong>Grand Totals:</strong></td>
                                    <td id="visibleTotalLiters"></td>
                                    <td></td>
                                    <td id="visibleGrandTotal"></td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-right"><strong>Regular Sales:</strong></td>
                                    <td id="visibleRegularLiters"></td>
                                    <td></td>
                                    <td id="visibleRegularTotal"></td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-right"><strong>Credit Sales:</strong></td>
                                    <td id="visibleCreditLiters"></td>
                                    <td></td>
                                    <td id="visibleCreditTotal"></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Print Area (hidden) -->
                    <!-- Print Area (hidden) -->
<div class="d-none">
    <div id="printArea">
        <div class="invoice-container" style="max-width: 800px; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333;">
            <!-- Header Section -->
            <div class="invoice-header" style="text-align: center; margin-bottom: 25px; padding: 20px 0; border-bottom: 2px solid #2c5aa0;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <!-- Logo -->
                    <div style="width: 120px; height: 120px; background: #2c5aa0; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; overflow: hidden;">
                        <img src="{{ asset('/Logo/maal.png') }}" alt="Company Logo" width="110" height="110" style="object-fit: contain;">
                    </div>
                    <div>
                        <h1 style="font-size: 28px; font-weight: bold; margin: 0; color: #2c5aa0;">TABANTAABO FUEL STATION BURAO</h1>
                        <p style="margin: 5px 0; font-size: 14px;">
                            Kaalinta Shiidaalka Tabantaabo<br>
                            Burco Somaliland
                        </p>
                        
                        <p style="margin: 3px 0;">
                            <i class="fas fa-phone-alt" style="color: #2c5aa0; margin-right: 5px;"></i>
                            <strong>Tell:</strong> 713013 | 063-4042473 | 063-4357338
                        </p>
                        
                        <p style="margin: 5px 0; font-size: 14px;">
                            <i class="fas fa-wallet" style="color: #2c5aa0; margin-right: 5px;"></i>
                            <strong>Merchant Accounts: Zaad : 400723 &nbsp; | &nbsp; E-dahab : 731684</strong>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Report Title Section -->
            <div class="invoice-info" style="display: flex; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div>
                    <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #2c5aa0;">FUEL SALES REPORT</h3>
                </div>
                
                <div style="text-align: right;">
                    <p style="margin: 5px 0; font-size: 14px;">
                        <i class="fas fa-calendar-alt" style="color: #2c5aa0; margin-right: 5px;"></i>
                        <span id="printDateRange">Date Range</span>
                    </p>
                    <p style="margin: 5px 0; font-size: 14px;">
                        <strong>Report Date:</strong> {{ now()->format('F j, Y') }}
                    </p>
                </div>
            </div>
            
            <!-- Sales Table -->
            <table class="invoice-table" style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                <thead>
                    <tr style="background: #2c5aa0; color: white;">
                        <th style="text-align: left; padding: 12px; font-weight: bold;">Type</th>
                        <th style="text-align: left; padding: 12px; font-weight: bold;">Product</th>
                        <th style="text-align: right; padding: 12px; font-weight: bold;">Liters</th>
                        <th style="text-align: right; padding: 12px; font-weight: bold;">Rate</th>
                        <th style="text-align: right; padding: 12px; font-weight: bold;">Total</th>
                        <th style="text-align: left; padding: 12px; font-weight: bold;">Payment Type</th>
                    </tr>
                </thead>
                <tbody id="printTableBody">
                    <!-- Data will be appended here by JS -->
                </tbody>
            </table>
            
            <!-- Totals Section -->
            <div style="display: flex; justify-content: flex-end; padding-right: 10px;">
                <div style="width: 400px;">
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                        <span style="font-weight: bold;">Grand Total Liters:</span>
                        <span id="printTotalLiters">0.00 L</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                        <span style="font-weight: bold;">Grand Total Amount:</span>
                        <span id="printGrandTotal">$0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 2px solid #2c5aa0;">
                        <span style="font-weight: bold;">Regular Sales Liters:</span>
                        <span id="printRegularLiters">0.00 L</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 2px solid #2c5aa0;">
                        <span style="font-weight: bold;">Regular Sales Total:</span>
                        <span id="printRegularTotal">$0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 2px solid #2c5aa0;">
                        <span style="font-weight: bold;">Credit Sales Liters:</span>
                        <span id="printCreditLiters">0.00 L</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 0;">
                        <span style="font-weight: bold;">Credit Sales Total:</span>
                        <span style="font-weight: bold; color: #2c5aa0;" id="printCreditTotal">$0.00</span>
                    </div>
                </div>
            </div>
            
            <!-- Footer Section -->
            <div class="invoice-footer" style="margin-top: 40px; padding: 20px 0; border-top: 2px solid #2c5aa0;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <p style="font-size: 16px; font-weight: bold; color: #2c5aa0;">
                        <i class="fas fa-handshake" style="margin-right: 8px;"></i>Thank you for your business!
                    </p>
                </div>
                
                <div style="display: flex; justify-content: space-between;">
                    <div>
                        <p style="margin: 5px 0; font-weight: bold;">Authorized Signature</p>
                        <div style="height: 60px; width: 200px; border-bottom: 1px solid #dee2e6;"></div>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Include SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        
        
        function getCombinedFuelReport() {
    let startDate = document.getElementById('startDate').value;
    let endDate = document.getElementById('endDate').value;
    let reportType = document.getElementById('report_type').value;
    let productId = document.getElementById('product_id').value;

    if (!startDate || !endDate) {
        Swal.fire({
            icon: 'error',
            title: 'Sorry...',
            text: 'Please Select Both Start Date and End Date.',
        });
        return;
    }

    let visibleTableBody = document.querySelector('#visibleFuelReport tbody');
    let printTableBody = document.querySelector('#printTableBody');
    visibleTableBody.innerHTML = '';
    printTableBody.innerHTML = '';

    let url = `{{ route('fuel-sales.combined-report-data') }}`;

    axios.get(url, {
            params: {
                startDate: startDate,
                endDate: endDate,
                report_type: reportType,
                product_id: productId
            }
        })
        .then(response => {
            if (response.data.success) {
                console.log('Full response:', response.data);

                let totalLiters = 0;
                let grandTotal = 0;
                let regularLiters = 0;
                let regularTotal = 0;
                let creditLiters = 0;
                let creditTotal = 0;

                if (response.data.data && response.data.data.length > 0) {
                    response.data.data.forEach(group => {
                        const row = `
                            <tr>
                                <td><span class="badge bg-primary">${group.type.toUpperCase()}</span></td>
                                <td>${group.product}</td>
                                <td>${group.total_liters}</td>
                                <td>$${parseFloat(group.rate).toFixed(2)}</td>
                                <td>$${parseFloat(group.total_sales).toFixed(2)}</td>
                                <td>${group.payment_type}</td>
                            </tr>
                        `;
                        visibleTableBody.innerHTML += row;
                        printTableBody.innerHTML += row;

                        totalLiters += parseFloat(group.total_liters);
                        grandTotal += parseFloat(group.total_sales);

                        if (group.type === 'regular') {
                            regularTotal += parseFloat(group.total_sales);
                            regularLiters += parseFloat(group.total_liters);
                        } else {
                            creditTotal += parseFloat(group.total_sales);
                            creditLiters += parseFloat(group.total_liters);
                        }
                    });

                    // Update totals for visible table
                    document.getElementById('visibleTotalLiters').textContent = totalLiters.toFixed(2) + ' L';
                    document.getElementById('visibleGrandTotal').textContent = '$' + grandTotal.toFixed(2);
                    document.getElementById('visibleRegularLiters').textContent = regularLiters.toFixed(2) + ' L';
                    document.getElementById('visibleRegularTotal').textContent = '$' + regularTotal.toFixed(2);
                    document.getElementById('visibleCreditLiters').textContent = creditLiters.toFixed(2) + ' L';
                    document.getElementById('visibleCreditTotal').textContent = '$' + creditTotal.toFixed(2);

                    // Update totals for print table
                    document.getElementById('printTotalLiters').textContent = totalLiters.toFixed(2) + ' L';
                    document.getElementById('printGrandTotal').textContent = '$' + grandTotal.toFixed(2);
                    document.getElementById('printRegularLiters').textContent = regularLiters.toFixed(2) + ' L';
                    document.getElementById('printRegularTotal').textContent = '$' + regularTotal.toFixed(2);
                    document.getElementById('printCreditLiters').textContent = creditLiters.toFixed(2) + ' L';
                    document.getElementById('printCreditTotal').textContent = '$' + creditTotal.toFixed(2);

                    // Update print date range
                    document.getElementById('printDateRange').textContent = 'From ' + response.data.start_date + ' to ' + response.data.end_date;
                } else {
                    showNoDataMessage(visibleTableBody, printTableBody);
                }
            } else {
                showNoDataMessage(visibleTableBody, printTableBody, response.data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNoDataMessage(visibleTableBody, printTableBody, 'Error fetching data');
        });
}

        // Update the showNoDataMessage function to use vanilla JS
        function showNoDataMessage(visibleTableBody, printTableBody, message = 'No data found for the selected criteria') {
            const messageRow = `<tr><td colspan="6" class="text-center">${message}</td></tr>`;
            visibleTableBody.innerHTML = messageRow;
            printTableBody.innerHTML = messageRow;
        
            // Reset totals
            const totalElements = [
                'visibleTotalLiters', 'visibleGrandTotal', 'visibleRegularLiters', 
                'visibleRegularTotal', 'visibleCreditLiters', 'visibleCreditTotal',
                'printTotalLiters', 'printGrandTotal', 'printRegularLiters', 
                'printRegularTotal', 'printCreditLiters', 'printCreditTotal'
            ];
            
            totalElements.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    if (id.includes('Liters')) {
                        element.textContent = '0.00 L';
                    } else {
                        element.textContent = '$0.00';
                    }
                }
            });
        }


        function appendToTable(sale, transaction, tableBody) {
            const row = `
            <tr>
               
                <td><span class="badge bg-${sale.type === 'regular' ? 'primary' : 'warning'}">${sale.type.toUpperCase()}</span></td>
             
                <td>${transaction.product}</td>
                <td>${transaction.liters} L</td>
                <td>$${parseFloat(transaction.rate).toFixed(2)}</td>
                <td>$${parseFloat(transaction.total).toFixed(2)}</td>
                <td>${sale.payment_type}</td>
          
            </tr>
        `;
            tableBody.append(row);
        }

        function getStatusClass(status) {
            switch (status.toLowerCase()) {
                case 'paid':
                    return 'success';
                case 'partial':
                    return 'warning';
                case 'unpaid':
                    return 'danger';
                default:
                    return 'secondary';
            }
        }

     
        
        function printReport() {
    // Update the date range in the print section
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    if (startDate && endDate) {
        document.getElementById('printDateRange').textContent = `From ${formatDate(startDate)} to ${formatDate(endDate)}`;
    }
    
    // Get the print area content
    const printContent = document.getElementById('printArea').innerHTML;
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=900,height=650');
    
    // Write the HTML content to the new window
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Fuel Sales Report</title>
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
        // Don't close immediately to allow user to check print preview
        // printWindow.close();
    }, 500);
}

        // Helper function to format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function exportToExcel() {
            // Select the table element
            const table = document.getElementById("visibleFuelReport");

            if (!table) {
                console.error("Table element not found.");
                return;
            }

            // Convert table to XLSX
            const wb = XLSX.utils.table_to_book(table);
            XLSX.writeFile(wb, "CombinedFuelReport.xlsx");
        }

        function exportToPDF() {
            // Select the table element
            const table = document.getElementById("visibleFuelReport");

            // Initialize jsPDF
            const doc = new jsPDF();

            // Add title
            doc.setFontSize(16);
            doc.text('Combined Fuel Sales Report', 14, 15);

            // Add date
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            doc.setFontSize(10);
            doc.text(`Date Range: ${startDate} to ${endDate}`, 14, 22);

            // Add autoTable plugin
            doc.autoTable({
                html: table,
                startY: 30,
                styles: {
                    fontSize: 8
                },
                headStyles: {
                    fillColor: [41, 128, 185]
                }
            });

            // Save the PDF file
            doc.save('CombinedFuelReport.pdf');
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>
@endsection
