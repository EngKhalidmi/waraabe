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
                                            <i class="fas fa-print"></i> <span class="ml-2">Print</span>
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                            <i class="fas fa-table"></i> <span class="ml-2">Excel</span>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                                            <i class="fas fa-file-pdf"></i> <span class="ml-2">PDF</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Print Area (Hidden) -->
                    <div class="table-responsive" id="printArea" style="display:none;">
                        <table class="table mt-4" id="PurchaseReportPrint">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction ID</th>
                                    <th>Supplier</th>
                                    <th>Department</th>
                                    <th>User</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost</th>
                                    <th>Total Cost</th>
                                    <th>Payment Method</th>
                                    <th>Paid Amount</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody id="printTableBody">
                                <!-- Data will be appended here by JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Visible Report Area -->
                    <div class="table-responsive mt-4">
                        <center>
                             <img src="{{ asset('/Logo/Logo1.png') }}" width="150 "alt="Company Logo">

                <h1>WARAABE FUEL STATIONS</h1>
                <p>Kaalinta Shiidaalka Waraabe
                    <br>Berbera Somaliland
                    <br>
                    +252 63XXXXX | 63XXXXXX | 5XXXXX <br>
                    ZAAD: XXXXX | Edahab: XXXXX
                </p>
                            <hr>
                            <h4 class="card-title">Purchases Report</h4>
                        </center>
                        
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
            let depID = document.getElementById('depID').value;
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
                            printTableBody.innerHTML = '<tr><td colspan="12" class="text-center">No purchase records found</td></tr>';
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
                    printTableBody.innerHTML = '<tr><td colspan="12" class="text-center">Error fetching purchase data</td></tr>';
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
                            <div class="col-md-3">
                                <strong>Supplier:</strong> ${transaction.supplier}
                            </div>
                            <div class="col-md-3">
                                <strong>Department:</strong> ${transaction.dep}
                            </div>
                            <div class="col-md-3">
                                <strong>User:</strong> ${transaction.user}
                            </div>
                            <div class="col-md-3">
                                <strong>Payment:</strong> ${transaction.payMethod}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Subtotal:</strong> ${transaction.subtotal}
                            </div>
                            <div class="col-md-3">
                                <strong>Discount:</strong> ${transaction.discount}
                            </div>
                            <div class="col-md-3">
                                <strong>Net Price:</strong> ${transaction.net_price}
                            </div>
                            <div class="col-md-3">
                                <strong>Balance:</strong> ${transaction.balance}
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
                            <td>${item.unit_cost}</td>
                            <td>${item.add_cost}</td>
                            <td>${item.total_cost}</td>
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
                        '<td>' + (transaction.date || 'N/A') + '</td>' +
                        '<td>' + (transaction.transaction_id || 'N/A') + '</td>' +
                        '<td>' + (transaction.supplier || 'N/A') + '</td>' +
                        '<td>' + (transaction.dep || 'N/A') + '</td>' +
                        '<td>' + (transaction.user || 'N/A') + '</td>' +
                        '<td>' + (item.item || 'N/A') + '</td>' +
                        '<td>' + (item.quantity || '0') + '</td>' +
                        '<td>' + (item.unit_cost || '0.00') + '</td>' +
                        '<td>' + (item.total_cost || '0.00') + '</td>' +
                        '<td>' + (transaction.payMethod || 'N/A') + '</td>' +
                        '<td>' + (transaction.paidAmount || '0.00') + '</td>' +
                        '<td>' + (transaction.balance || '0.00') + '</td>' +
                        '</tr>';
                    
                    tableBody.innerHTML += tableRow;
                });
            } else {
                let tableRow = '<tr>' +
                    '<td>' + (transaction.date || 'N/A') + '</td>' +
                    '<td>' + (transaction.transaction_id || 'N/A') + '</td>' +
                    '<td>' + (transaction.supplier || 'N/A') + '</td>' +
                    '<td>' + (transaction.dep || 'N/A') + '</td>' +
                    '<td>' + (transaction.user || 'N/A') + '</td>' +
                    '<td colspan="7" class="text-center">No items</td>' +
                    '</tr>';
                
                tableBody.innerHTML += tableRow;
            }
        }

        function printReport() {
            var printContents = document.getElementById('printArea').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
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