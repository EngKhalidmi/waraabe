@extends ('admin.admin_master')
@section('title', 'Saacid - Report Of Bank Statement')
@section('admin')

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Bank Statement Report</h4>
                <h6>Manage Your Bank Statement</h6>
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
                                <div class="col-lg-3 col-sm-6 col-13">
                                    <div class="form-group">
                                        <select name="type" id="type" class="select">
                                            <option value="">Select Transaction Type</option>
                                            <option value="Credit">Credit</option>
                                            <option value="Debit">Debit</option>
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
                                            onclick="getBankStatementReport()" id="searchBtn"><img
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
                    </div>
                </div>
                </form>

                <!-- Print Area (hidden) -->
                <div class="d-none">
                    <div id="printArea">
                        <div class="invoice-container" style="max-width: 800px; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333;">
                            <!-- Header Section -->
                            <div class="invoice-header" style="text-align: center; margin-bottom: 25px; padding: 20px 0; border-bottom: 2px solid #2c5aa0;">
                                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                                    <!-- Logo -->
                                    <div style="width: 120px; height: 120px; background: #2c5aa0; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; overflow: hidden;">
                                        <img src="{{ asset('/Logo/Logo1.png') }}" alt="Company Logo" width="110" height="110" style="object-fit: contain;">
                                    </div>
                                    <div>
                                        <h1 style="font-size: 28px; font-weight: bold; margin: 0; color: #2c5aa0;">WARAABE FUEL STATION</h1>
                                        <p style="margin: 5px 0; font-size: 14px;">
                                            Kaalinta Shiidaalka Waraabe<br>
                                            Berbera Somaliland
                                        </p>
                                        
                                     
                                        
                                       
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Report Title Section -->
                            <div class="invoice-info" style="display: flex; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                <div>
                                    <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #2c5aa0;">BANK STATEMENT REPORT</h3>
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
                            
                            <!-- Bank Statement Table -->
                            <table class="invoice-table" style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                                <thead>
                                    <tr style="background: #2c5aa0; color: white;">
                                        <th style="text-align: left; padding: 12px; font-weight: bold;">Date</th>
                                        <th style="text-align: left; padding: 12px; font-weight: bold;">Ref</th>
                                        <th style="text-align: left; padding: 12px; font-weight: bold;">Branch</th>
                                        <th style="text-align: left; padding: 12px; font-weight: bold;">Particulars</th>
                                        <th style="text-align: left; padding: 12px; font-weight: bold;">Cheque No</th>
                                        <th style="text-align: right; padding: 12px; font-weight: bold;">Withdrawal</th>
                                        <th style="text-align: right; padding: 12px; font-weight: bold;">Deposit</th>
                                        <th style="text-align: right; padding: 12px; font-weight: bold;">Balance</th>
                                    </tr>
                                </thead>
                                <tbody id="printTableBody">
                                    <!-- Data will be appended here by JS -->
                                </tbody>
                            </table>
                            
                            <!-- Footer Section -->
                            <div class="invoice-footer" style="margin-top: 40px; padding: 20px 0; border-top: 2px solid #2c5aa0;">
                                <div style="text-align: center; margin-bottom: 20px;">
                                    <p style="font-size: 16px; font-weight: bold; color: #2c5aa0;">
                                        <i class="fas fa-handshake" style="margin-right: 8px;"></i>Bank Statement Report
                                    </p>
                                </div>
                                
                              
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visible table -->
                <div class="table-responsive mt-4">
                    <table class="table mt-4" id="visibleBankStatementReport">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Ref</th>
                                <th>Branch</th>
                                <th>Particulars</th>
                                <th>Cheque No</th>
                                <th>Withdrawal</th>
                                <th>Deposit</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody id="visibleTableBody">
                            <!-- Data will be appended here by JS -->
                        </tbody>
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
    function getBankStatementReport() {
        let type = document.getElementById('type').value;
        let depID = document.getElementById('depID').value;
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

        let visibleTableBody = document.querySelector('#visibleBankStatementReport tbody');
        let printTableBody = document.querySelector('#printTableBody');
        visibleTableBody.innerHTML = ''; // Clear the table body before adding new data
        printTableBody.innerHTML = ''; // Clear the print table body before adding new data

        let url = `{{ route('info.bank') }}`;

        axios.get(url, {
                params: {
                    startDate: startDate,
                    endDate: endDate,
                    type: type,
                    depID: depID,
                },
                
            })
            .then(response => {
                if (response.data.success) {
                    // Update print date range
                    document.getElementById('printDateRange').textContent = 'From ' + formatDate(startDate) + ' to ' + formatDate(endDate);
                    
                    if (response.data.data.length > 0) {
                        // Add opening balance row
                        let openingRow = '<tr>' +
                            '<td colspan="5" style="text-align: right; font-weight: bold; padding: 10px;">Opening Balance:</td>' +
                            '<td style="text-align: right; padding: 10px;"></td>' +
                            '<td style="text-align: right; padding: 10px;"></td>' +
                            '<td style="text-align: right; padding: 10px; font-weight: bold;">' + response.data.opening_balance + '</td>' +
                            '</tr>';
                        
                        visibleTableBody.innerHTML += openingRow;
                        printTableBody.innerHTML += openingRow;
                        
                        // Add transaction rows
                        response.data.data.forEach(record => {
                            appendToTable(record, visibleTableBody);
                            appendToTable(record, printTableBody);
                        });
                    } else {
                        visibleTableBody.innerHTML = '<tr><td colspan="8" class="text-center">No data found</td></tr>';
                        printTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">No data found</td></tr>';
                    }
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Data',
                        text: response.data.message,
                    });
                    visibleTableBody.innerHTML = '<tr><td colspan="8" class="text-center">' + response.data.message + '</td></tr>';
                    printTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">' + response.data.message + '</td></tr>';
                }
            })
            .catch(error => {
                console.error(error);
                visibleTableBody.innerHTML = '<tr><td colspan="8" class="text-center">Error fetching data</td></tr>';
                printTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">Error fetching data</td></tr>';
            });
    }

    function appendToTable(data, tableBody) {
        var tableRow = '<tr>' +
            '<td style="padding: 10px;">' + (data.date || '') + '</td>' +
            '<td style="padding: 10px;">' + (data.ref || '') + '</td>' +
            '<td style="padding: 10px;">' + (data.branch || '') + '</td>' +
            '<td style="padding: 10px;">' + (data.particulars || '') + '</td>' +
            '<td style="padding: 10px;">' + (data.cheque_no || '') + '</td>' +
            '<td style="padding: 10px; text-align: right;">' + (data.withdrawal || '0.00') + '</td>' +
            '<td style="padding: 10px; text-align: right;">' + (data.deposit || '0.00') + '</td>' +
            '<td style="padding: 10px; text-align: right; font-weight: 500;">' + (data.balance || '0.00') + '</td>' +
            '</tr>';

        tableBody.innerHTML += tableRow;
    }

    function printReport() {
        // Get the print area content
        const printContent = document.getElementById('printArea').innerHTML;
        
        // Create a new window for printing
        const printWindow = window.open('', '_blank', 'width=900,height=650');
        
        // Write the HTML content to the new window
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Bank Statement Report</title>
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
</script>

<!-- Excel and PDF export scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>
<script>
    function exportToExcel() {
        const table = document.getElementById("visibleBankStatementReport");
        console.log("Table element:", table);

        if (!table) {
            console.error("Table element not found.");
            return;
        }

        const wb = XLSX.utils.table_to_book(table);
        console.log("Workbook:", wb);

        XLSX.writeFile(wb, "BankStatementReport.xlsx");
    }

    function exportToPDF() {
        const table = document.getElementById("visibleBankStatementReport");
        const doc = new jsPDF();
        
        doc.autoTable({
            html: table
        });
        
        doc.save('BankStatementReport.pdf');
    }
</script>
@endsection