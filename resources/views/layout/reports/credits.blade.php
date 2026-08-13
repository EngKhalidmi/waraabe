@extends ('admin.admin_master')
@section('title', 'Saacid - Credit Payments Report')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Credits Transactions Report</h4>
<h6>Manage Your Credit Transactions</h6>
</div>
</div>

@if (session('status'))
    <div class="toast-container">
        <div class="toast-message success">
            <div class="toast-icon">
                <i data-lucide="circle-check" class="icon-checkmark"></i> <!-- Success checkmark icon -->
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
                <i data-lucide="circle-alert" class="icon-error"></i> <!-- Error exclamation mark icon -->
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
<img src="{{asset('/assets/img/icons/filter.svg')}}" alt="img">
<span><img src="{{asset('/assets/img/icons/closes.svg')}}" alt="img"></span>
</a>
</div>
<div class="search-input">
<a class="btn btn-searchset"><img src="{{asset('/assets/img/icons/search-white.svg')}}" alt="img"></a>
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
<select name="clientID" id="clientID" class="select">
        <option value="" >Select Customer</option>
        @foreach($customers as $client)
        <option value="{{$client->id }}">{{ $client->customer_name }}</option>
        @endforeach
    </select>
</div>
</div>
<div class="col-lg-3 col-sm-6 col-13">
    <div class="form-group">
        <select name="depID" id="depID" class="select">
            <option value="" >Select Department</option>
            @foreach($departments as $dep)
                <option value="{{$dep->id }}">{{ $dep->name }}</option>
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

<div class="col-lg-3 col-sm-6 col-13">
    <div class="form-group">
        <select name="selectedUser" id="selectedUser" class="form-control">
            <option value="" >Select User</option>
            @foreach($users as $user)
                <option value="{{$user->id }}">{{ $user->username }}</option>
            @endforeach
        </select>
</div>
</div>



<div class="col-lg-1 col-sm-6 col-12  ms-auto">
<div class="form-group">
<button type="button" class="btn btn-filters ms-auto" onclick="getFinanceReport()" id="searchBtn"><img src="{{asset('/assets/img/icons/search-whites.svg')}}" alt="img"></button>
</div>
</div>
    <div class="form-group col-md-12">
            <button type="button" class="btn btn-primary" onclick="printReport()"><i data-lucide="printer"></i> <span class="ml-2">Print</span></button>
            <button type="button" class="btn btn-success" onclick="exportToExcel()"><i data-lucide="table"></i>  <span class="ml-2">Excel</span></button>
            <button type="button" class="btn btn-danger" onclick="exportToPDF()"><i data-lucide="file-text"></i> <span class="ml-2">PDF</span></button>
    </div>
    </div>
    </div>
    </div>
</form>
                            

                        <!-- Display results -->
                        <div  class="table-responsive" id="printArea" style="display:none;">
                            <center>
                                     <img src="{{asset('/Logo/Report-logo.png')}}" alt="Company Logo" width="200">
                                        <h4 class="card-title">Customer Transaction History</h4>
                                        </center>
                            <table  class="table mt-4" id="FinanceReport">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer </th>
                                        <th>Phone </th>
                                        <th>Amount</th>
                                        <th>Transaction Type</th>
                                    </tr>
                                </thead>
                                <tbody id="printTableBody">
                                    <!-- Data will be appended here by JS -->
                                </tbody>
                            </table>
                        </div>

                        <div  class="table-responsive mt-4">
                           
                            <table class="table mt-4" id="visibleFinanceReport">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer </th>
                                        <th>Phone </th>
                                        <th>Amount</th>
                                        <th>Transaction Type</th>
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
    
    <!-- Hidden Print Area with Enhanced Design -->
<div class="d-none">
    <div id="printArea">
        <div class="invoice-container" style="max-width: 800px; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333;">
            <!-- Header Section -->
            <div class="invoice-header" style="text-align: center; margin-bottom: 25px; padding: 20px 0; border-bottom: 2px solid #2c5aa0;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <div style="width: 80px; height: 80px; background: #2c5aa0; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                        <i data-lucide="receipt-text" style="font-size: 36px;"></i>
                    </div>
                    <div>
                        <h1 style="font-size: 28px; font-weight: bold; margin: 0; color: #2c5aa0;">WARAABE FUEL STATION</h1>
                        <p style="margin: 5px 0; font-size: 14px;">
                            <strong>Credit Payments Report</strong>
                        </p>
                    </div>
                </div>
                
            </div>
            
            <!-- Report Info Section -->
            <div class="report-info" style="display: flex; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div>
                    <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #2c5aa0;">CREDIT PAYMENTS REPORT</h3>
                    <p style="margin: 5px 0; font-size: 14px;">
                        <strong>Report Date:</strong> <span id="reportDate">{{ now()->format('F j, Y') }}</span>
                    </p>
                    <p style="margin: 5px 0; font-size: 14px;">
                        <strong>Report #:</strong> CP-<span id="reportNumber">{{ sprintf('%05d', rand(1000, 99999)) }}</span>
                    </p>
                </div>
                
                <div style="text-align: right;">
                    <p style="margin: 5px 0; font-size: 14px;">
                        <i data-lucide="calendar-days" style="color: #2c5aa0; margin-right: 5px;"></i>
                        <span id="printDateRange">Date Range</span>
                    </p>
                    <p style="margin: 5px 0; font-size: 14px;" id="printCustomerInfo">
                        <i data-lucide="users" style="color: #2c5aa0; margin-right: 5px;"></i>
                        All Customers
                    </p>
                    <p style="margin: 5px 0; font-size: 14px;" id="printDepartmentInfo">
                        <i data-lucide="building-2" style="color: #2c5aa0; margin-right: 5px;"></i>
                        All Departments
                    </p>
                </div>
            </div>
            
            <!-- Summary Stats -->
            <div class="summary-stats" style="display: flex; justify-content: space-around; margin-bottom: 25px; text-align: center;">
                <div style="padding: 15px; background: #e8f4ff; border-radius: 8px; min-width: 150px;">
                    <p style="margin: 0; font-size: 14px; color: #2c5aa0;">Total Transactions</p>
                    <p style="margin: 5px 0 0 0; font-size: 20px; font-weight: bold;" id="totalTransactions">0</p>
                </div>
                <div style="padding: 15px; background: #e8f4ff; border-radius: 8px; min-width: 150px;">
                    <p style="margin: 0; font-size: 14px; color: #2c5aa0;">Total Amount</p>
                    <p style="margin: 5px 0 0 0; font-size: 20px; font-weight: bold;" id="totalAmount">$0.00</p>
                </div>
                <div style="padding: 15px; background: #e8f4ff; border-radius: 8px; min-width: 150px;">
                    <p style="margin: 0; font-size: 14px; color: #2c5aa0;">Avg. Transaction</p>
                    <p style="margin: 5px 0 0 0; font-size: 20px; font-weight: bold;" id="avgTransaction">$0.00</p>
                </div>
            </div>
            
            <!-- Transactions Table -->
            <table class="report-table" style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                <thead>
                    <tr style="background: #2c5aa0; color: white;">
                        <th style="text-align: left; padding: 12px; font-weight: bold;">Date</th>
                        <th style="text-align: left; padding: 12px; font-weight: bold;">Customer</th>
                        <th style="text-align: left; padding: 12px; font-weight: bold;">Phone</th>
                        <th style="text-align: right; padding: 12px; font-weight: bold;">Amount ($)</th>
                        <th style="text-align: left; padding: 12px; font-weight: bold;">Transaction Type</th>
                    </tr>
                </thead>
                <tbody id="printTableBody">
                    <!-- Data will be appended here by JS -->
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: #6c757d;">
                            No transaction data available
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #2c5aa0;">
                        <td colspan="3" style="text-align: right; padding: 12px; font-weight: bold;">Grand Total:</td>
                        <td style="text-align: right; padding: 12px; font-weight: bold; color: #2c5aa0;" id="printGrandTotal">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            
            <!-- Footer Section -->
            <div class="report-footer" style="margin-top: 40px; padding: 20px 0; border-top: 2px solid #2c5aa0;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <p style="font-size: 16px; font-weight: bold; color: #2c5aa0;">
                        <i data-lucide="trending-up" style="margin-right: 8px;"></i>Financial Summary
                    </p>
                </div>
                
                <div style="display: flex; justify-content: space-between;">
                    <div>
                        <p style="margin: 5px 0; font-weight: bold;">Prepared By</p>
                        <div style="height: 60px; width: 200px; border-bottom: 1px solid #dee2e6;"></div>
                        <p style="margin: 5px 0; font-size: 12px; color: #6c757d;">
                            {{ Auth::user()->name ?? 'System Administrator' }}
                        </p>
                    </div>
                    
                    <div style="text-align: right;">
                        <p style="margin: 5px 0; font-size: 12px; color: #6c757d;">
                            Report generated on: {{ now()->format('F j, Y \\a\\t g:i A') }}
                        </p>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <p style="font-size: 12px; color: #6c757d;">
                        <i data-lucide="info" style="margin-right: 5px;"></i>
                        This is an automated financial report. For inquiries, contact the finance department.
                    </p>
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
    function getFinanceReport() {
        let client = document.getElementById('clientID').value;
        let startDate = document.getElementById('startDate').value;
        let endDate = document.getElementById('endDate').value;
        let depID = document.getElementById('depID').value;
        let selectedUser = document.getElementById('selectedUser').value; // Fixed syntax error (missing quote)
        
        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'error',
                title: 'Sorry...',
                text: 'Please Select Both Start Date and End Date.',
            });
            return;
        }
    
        let visibleTableBody = $('#visibleFinanceReport tbody');
        let printTableBody = $('#printTableBody');
        visibleTableBody.empty();
        printTableBody.empty();
    
        let url = `{{ route('info.credit') }}`;
    
        axios.get(url, {
            params: {
                startDate: startDate,
                endDate: endDate,
                clientID: client,
                depID: depID,
                seller: selectedUser
            }
        })
        .then(response => {
        if (response.data.success) {
            console.log('Full response:', response.data); // Debugging
            
            // Check if we have grouped data with transactions
            if (response.data.data.grouped_by_type) {
                // Flatten all transactions from all groups
                let allTransactions = [];
                
                // Loop through each type group (Debit, Credit, etc.)
                Object.values(response.data.data.grouped_by_type).forEach(group => {
                    if (group.transactions && group.transactions.length > 0) {
                        allTransactions = allTransactions.concat(group.transactions);
                    }
                });
    
                if (allTransactions.length > 0) {
                    allTransactions.forEach(transaction => {
                        appendToTable(transaction, visibleTableBody);
                        appendToTable(transaction, printTableBody);
                    });
                } else {
                    showNoDataMessage(visibleTableBody, printTableBody);
                }
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

    function showNoDataMessage(visibleTableBody, printTableBody, message = 'No records found') {
        visibleTableBody.html(`<tr><td colspan="5" class="text-center">${message}</td></tr>`);
        printTableBody.html(`<tr><td colspan="5" class="text-center">${message}</td></tr>`);
    }


    function appendToTable(data, tableBody) {
        // Format date properly
        const date = data.date ? new Date(data.date).toLocaleDateString('en-GB') : 
                  data.created_at ? new Date(data.created_at).toLocaleDateString('en-GB') : 'N/A';
        
        const row = `
            <tr>
                <td>${date}</td>
                <td>${data.client || data.customer?.customer_name || 'N/A'}</td>
                <td>${data.phone || data.customer?.phone || 'N/A'}</td>
                <td>${data.amount || data.paidAmount || '0'}</td>
                <td>${data.type || data.payMethod || 'N/A'}</td>
            </tr>
        `;
        
        tableBody.append(row);
    }


    function printReport() {
        // Check if there's data to print
        if (document.getElementById('printTableBody').children.length === 0 || 
            document.getElementById('printTableBody').children[0].children.length === 1) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data to Print',
                text: 'Please generate a report first before printing.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }
        
        // Update report date and number
        document.getElementById('reportDate').textContent = new Date().toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        document.getElementById('reportNumber').textContent = {{ sprintf('%05d', rand(1000, 99999)) }};
        
        // Update date range in the report
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        if (startDate && endDate) {
            const start = new Date(startDate).toLocaleDateString();
            const end = new Date(endDate).toLocaleDateString();
            document.getElementById('printDateRange').textContent = `${start} to ${end}`;
        }
        
        // Update customer info if selected
        const clientSelect = document.getElementById('clientID');
        if (clientSelect.value) {
            const selectedOption = clientSelect.options[clientSelect.selectedIndex];
            document.getElementById('printCustomerInfo').innerHTML = `
                <i data-lucide="user" style="color: #2c5aa0; margin-right: 5px;"></i>
                ${selectedOption.text}
            `;
        }
        
        // Update department info if selected
        const depSelect = document.getElementById('depID');
        if (depSelect.value) {
            const selectedDep = depSelect.options[depSelect.selectedIndex];
            document.getElementById('printDepartmentInfo').innerHTML = `
                <i data-lucide="building-2" style="color: #2c5aa0; margin-right: 5px;"></i>
                ${selectedDep.text}
            `;
        }
        
        // Calculate and update summary statistics
        updateSummaryStatistics();
        
        // Print the document
        const printContents = document.getElementById('printArea').innerHTML;
        const originalContents = document.body.innerHTML;
        
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }
    
    // Function to update summary statistics
    function updateSummaryStatistics() {
        const tableBody = document.getElementById('printTableBody');
        const rows = tableBody.querySelectorAll('tr');
        let totalAmount = 0;
        let transactionCount = 0;
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 4) {
                const amountCell = cells[3].textContent;
                const amount = parseFloat(amountCell.replace('$', '').replace(',', ''));
                if (!isNaN(amount)) {
                    totalAmount += amount;
                    transactionCount++;
                }
            }
        });
        
        document.getElementById('totalTransactions').textContent = transactionCount;
        document.getElementById('totalAmount').textContent = `$${totalAmount.toFixed(2)}`;
        document.getElementById('printGrandTotal').textContent = `$${totalAmount.toFixed(2)}`;
        
        const avgTransaction = transactionCount > 0 ? totalAmount / transactionCount : 0;
        document.getElementById('avgTransaction').textContent = `$${avgTransaction.toFixed(2)}`;
    }
    
    // Enhanced function to populate the print table
    function appendToPrintTable(data, tableBody) {
        const date = data.date ? new Date(data.date).toLocaleDateString('en-GB') : 
                  data.created_at ? new Date(data.created_at).toLocaleDateString('en-GB') : 'N/A';
        
        // Determine transaction type with appropriate styling
        let typeClass = '';
        let typeIcon = '';
        if (data.type) {
            if (data.type.toLowerCase().includes('debit')) {
                typeClass = 'style="color: #dc3545;"';
                typeIcon = '<i data-lucide="arrow-down" class="me-1"></i>';
            } else if (data.type.toLowerCase().includes('credit')) {
                typeClass = 'style="color: #28a745;"';
                typeIcon = '<i data-lucide="arrow-up" class="me-1"></i>';
            }
        }

        let row = document.createElement('tr');
        row.style.borderBottom = '1px solid #f1f1f1';
        row.innerHTML = `
            <td style="padding: 10px;">${date}</td>
            <td style="padding: 10px; font-weight: 500;">${data.client || data.customer?.customer_name || 'N/A'}</td>
            <td style="padding: 10px;">${data.phone || data.customer?.phone || 'N/A'}</td>
            <td style="padding: 10px; text-align: right; font-weight: 500;">$${data.amount || data.paidAmount || '0'}</td>
            <td style="padding: 10px;" ${typeClass}>${typeIcon}${data.type || data.payMethod || 'N/A'}</td>
        `;
        tableBody.appendChild(row);
    }


</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>
<script>
    
    // JavaScript code for exporting to Excel

function exportToExcel() {
    // Select the table element by its ID
    const table = document.getElementById("FinanceReport");
    console.log("Table element:", table);

    // Check if the table element was found
    if (!table) {
        console.error("Table element not found.");
        return;
    }

    // Convert table to XLSX
    const wb = XLSX.utils.table_to_book(table);
    console.log("Workbook:", wb);

    // Save the XLSX file
    XLSX.writeFile(wb, "PaymentCreditsReport.xlsx");
}



// Your script containing exportToPDF function here
    
function exportToPDF() {

    // Select the table element by its ID
    const table = document.getElementById("FinanceReport");

    // Initialize jsPDF
    const doc = new jsPDF();

    // Add autoTable plugin
    doc.autoTable({ html: table });

    // Save the PDF file
    doc.save('PaymentCredits.pdf');
}

</script>

<style>
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
        
        .report-table {
            page-break-inside: avoid;
        }
        
        .report-footer {
            page-break-before: avoid;
        }
        
        /* Ensure colors print correctly */
        .invoice-header, 
        .report-table thead,
        .summary-stats > div {
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }
    }
</style>


@endsection
