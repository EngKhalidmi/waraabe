@extends ('admin.admin_master')
@section('title', 'Saacid - Sales Payments Report')
@section('admin')

    <!-- Add this CSS for the summary cards -->
    <style>
        .summary-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            flex: 1;
            min-width: 200px;
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .summary-card h5 {
            margin-top: 0;
            color: #6c757d;
            font-size: 14px;
        }

        .summary-card .amount {
            font-size: 24px;
            font-weight: bold;
            color: #4e73df;
        }

        .payment-method-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .payment-method-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .payment-method-title {
            font-weight: bold;
            color: #4e73df;
        }

        .payment-method-total {
            font-weight: bold;
        }
    </style>




    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Sales Payments Report</h4>
                    <h6>Manage Your Sales Payment Statement</h6>
                </div>
            </div>

            @if (session('status'))
                <div class="toast-container">
                    <div class="toast-message success">
                        <div class="toast-icon">
                            <i class="icon-checkmark fas fa-check-circle"></i> <!-- Success checkmark icon -->
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
                            <i class="icon-error fa fa-exclamation-circle"></i> <!-- Error exclamation mark icon -->
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
                                            <select name="clientID" id="clientID" class="select">
                                                <option value="">Select Customer</option>
                                                @foreach ($customers as $client)
                                                    <option value="{{ $client->id }}">{{ $client->customer_name }}
                                                    </option>
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
                                    <div class="col-lg-3 col-sm-6 col-13">
                                        <div class="form-group">
                                            <select name="type" id="type" class="select">
                                                <option value="">Select Type</option>
                                                <option value="Cash">Cash</option>
                                                <option value="Cash & Credit">Cash & Credit</option>
                                                <option value="Credit">Credit</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-13">
                                        <div class="form-group">
                                            <select name="seller" id="seller" class="select">
                                                <option value="">Select User</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->username }}</option>
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
                                                onclick="getFinanceReport()" id="searchBtn"><img
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


                    <!-- Display results -->
                    <div class="table-responsive" id="printArea" style="display:none;">
                        <center>
 <img src="{{ asset('/Logo/Logo1.png') }}" width="150" alt="Company Logo">
                              <h1>WARAABE FUEL STATIONS</h1>
                <p>Kaalinta Shiidaalka Waraabe
                    <br>Berbera Somaliland
                    <br>
                    +252 63XXXXX | 63XXXXXX | 5XXXXX <br>
                    ZAAD: XXXXX | Edahab: XXXXX
                </p>
                            <hr>
                            <h4 class="card-title">Invoices Report</h4>
                        </center>
                        <table class="table mt-4" id="FinanceReport">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer </th>
                                    <th>Phone </th>
                                    <th>Type </th>
                                    <th>Sub Total</th>
                                    <th>Discount</th>
                                    <th>Net Price</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody id="printTableBody">
                                <!-- Data will be appended here by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">

                        <table class="table mt-4" id="visibleFinanceReport">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer </th>
                                    <th>Phone </th>
                                    <th>Type </th>
                                    <th>Sub Total</th>
                                    <th>Discount</th>
                                    <th>Net Price</th>
                                    <th>Paid Amount</th>
                                    <th>Balance</th>
                                    <th>Pay Method</th>
                                    <th>Seller</th>
                                </tr>
                            </thead>
                            <tbody id="visibleTableBody">

                            </tbody>
                        </table>

                        <!-- Add this section for totals and payment methods -->
                        <div class="summary-cards" id="summaryCards">
                            <!-- These will be populated by JavaScript -->
                        </div>

                        <!-- Add this section for payment methods breakdown -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Payment Methods Breakdown</h5>
                            </div>
                            <div class="card-body" id="paymentMethodsContainer">
                                <!-- Payment methods will be inserted here -->
                            </div>
                        </div>


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
            let type = document.getElementById('type').value;
            let startDate = document.getElementById('startDate').value;
            let endDate = document.getElementById('endDate').value;
            let depID = document.getElementById('depID').value;
            let seller = document.getElementById('seller').value;

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
            visibleTableBody.empty(); // Clear tables
            printTableBody.empty();

            let url = `{{ route('info.salesPayment') }}`;

            axios.get(url, {
                    params: {
                        startDate: startDate,
                        endDate: endDate,
                        clientID: client,
                        type: type,
                        depID: depID,
                        seller: seller
                    }
                })
                .then(response => {
                    console.log('API Response:', response.data); // Debugging

                    // Always clear tables first
                    visibleTableBody.empty();
                    printTableBody.empty();

                    if (response.data.success) {
                        // Update summary cards
                        updateSummaryCards(response.data.data.grand_totals || {});

                        // Update payment methods breakdown
                        updatePaymentMethods(response.data.data.payment_methods || {});

                        // Check if we have any transactions
                        const hasTransactions = response.data.data.payment_methods &&
                            Object.values(response.data.data.payment_methods)
                            .some(method => method.transactions.length > 0);

                        if (hasTransactions) {
                            // Flatten and display transactions
                            let allTransactions = [];
                            Object.values(response.data.data.payment_methods).forEach(method => {
                                if (method.transactions && method.transactions.length > 0) {
                                    allTransactions = allTransactions.concat(method.transactions);
                                }
                            });

                            allTransactions.forEach(record => {
                                appendToTable(record, visibleTableBody);
                                appendToTable(record, printTableBody);
                            });
                        } else {
                            showNoDataMessage(visibleTableBody, printTableBody);
                        }
                    } else {
                        showNoDataMessage(visibleTableBody, printTableBody, response.data.message);
                    }
                })
                .catch(error => {
                    console.error('API Error:', error);
                    showNoDataMessage(visibleTableBody, printTableBody, 'Error fetching data');
                });
        }

        function showNoDataMessage(visibleTableBody, printTableBody, message = 'No records found') {
            visibleTableBody.empty().append(
                `<tr><td colspan="10" class="text-center">${message}</td></tr>`
            );
            printTableBody.empty().append(
                `<tr><td colspan="10" class="text-center">${message}</td></tr>`
            );

            // Clear summary cards and payment methods if no data
            updateSummaryCards({
                total_transactions: 0,
                total_paid: 0,
                total_discount: 0,
                total_net: 0,
                total_balance: 0
            });

            updatePaymentMethods({});
        }


        function updateSummaryCards(totals) {
            const summaryCards = document.getElementById('summaryCards');
            summaryCards.innerHTML = `
        <div class="summary-card">
            <h5>Total Transactions</h5>
            <div class="amount">${totals.total_transactions}</div>
        </div>
        <div class="summary-card">
            <h5>Total Paid Amount</h5>
            <div class="amount">${formatCurrency(totals.total_paid)}</div>
        </div>
        <div class="summary-card">
            <h5>Total Discount</h5>
            <div class="amount">${formatCurrency(totals.total_discount)}</div>
        </div>
        <div class="summary-card">
            <h5>Total Net Price</h5>
            <div class="amount">${formatCurrency(totals.total_net)}</div>
        </div>
        <div class="summary-card">
            <h5>Total Credits</h5>
            <div class="amount">${formatCurrency(totals.total_balance)}</div>
        </div>
    `;
        }

        function updatePaymentMethods(paymentMethods) {
            const container = document.getElementById('paymentMethodsContainer');
            container.innerHTML = '';

            if (!paymentMethods) return;

            for (const [method, data] of Object.entries(paymentMethods)) {
                const methodCard = document.createElement('div');
                methodCard.className = 'payment-method-card';
                console.log(data)
                methodCard.innerHTML = `
            <div class="payment-method-header">
                <span class="payment-method-title">${method}</span>
                <span class="payment-method-total">
                    ${data.count} transactions | Total: ${formatCurrency(data.total_paid)}
                </span>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Seller</th>
                    </tr>
                </thead>
                <tbody id="method-${method.replace(/\s+/g, '-')}">
                    <!-- Transactions will be added here -->
                </tbody>
            </table>
        `;

                container.appendChild(methodCard);

                // Add transactions for this payment method
                const tbody = methodCard.querySelector(`tbody`);
                data.transactions.forEach(transaction => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                <td>${transaction.date}</td>
                <td>${transaction.client}</td>
                <td>${transaction.net_price}</td>
                <td>${transaction.seller}</td>
            `;
                    tbody.appendChild(row);
                });
            }
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2
            }).format(amount);
        }

        // function showNoDataMessage(visibleTableBody, printTableBody, message = 'No data found') {
        //     visibleTableBody.append(`<tr><td colspan="11" class="text-center">${message}</td></tr>`);
        //     printTableBody.append(`<tr><td colspan="10" class="text-center">${message}</td></tr>`);
        // }


        function appendToTable(data, tableBody) {
            var tableRow = '<tr>' +
                '<td>' + data.date + '</td>' +
                '<td>' + data.client + '</td>' +
                '<td>' + data.phone + '</td>' +
                '<td>' + data.type + '</td>' +
                '<td>' + data.subTotal + '</td>' +
                '<td>' + data.discount + '</td>' +
                '<td>' + data.net_price + '</td>' +
                '<td>' + data.paidAmount + '</td>' +
                '<td>' + data.balance + '</td>' +
                '<td>' + data.payMethod + '</td>' +
                '<td>' + data.seller + '</td>' +
                '</tr>';

            tableBody.append(tableRow);
        }


        function printReport() {
            // Copy the content of the print area to a new window and print
            var printContents = document.getElementById('printArea').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();

            // Rebind the FinanceAttendanceReport function to the button after restoring the original content
            document.querySelector('button[onclick="getFinanceReport()"]').onclick = getFinanceReport;
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
            XLSX.writeFile(wb, "SalesPaymentReport.xlsx");
        }



        // Your script containing exportToPDF function here

        function exportToPDF() {

            // Select the table element by its ID
            const table = document.getElementById("FinanceReport");

            // Initialize jsPDF
            const doc = new jsPDF();

            // Add autoTable plugin
            doc.autoTable({
                html: table
            });

            // Save the PDF file
            doc.save('SalesPaymentReport.pdf');
        }
    </script>
@endsection
