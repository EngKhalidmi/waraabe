@extends ('admin.admin_master')
@section('title', 'Saacid - Purchase Payments Report')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Purchase Payments Report</h4>
                    <h6>Manage Your Purchases Payment Statement</h6>
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
                                                <option value="">Select Supplier</option>
                                                @foreach ($suppliers as $client)
                                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
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
                                        <button type="button" class="btn btn-primary" onclick="printReport()"><i data-lucide="printer"></i> <span class="ml-2">Print</span></button>
                                        <button type="button" class="btn btn-success" onclick="exportToExcel()"><i data-lucide="table"></i> <span class="ml-2">Excel</span></button>
                                        <button type="button" class="btn btn-danger" onclick="exportToPDF()"><i data-lucide="file-text"></i> <span class="ml-2">PDF</span></button>
                                    </div>
                                </div>
                        </div>
                    </div>
                    </form>


                    <!-- Display results -->
                    <div class="table-responsive" id="printArea" style="display:none;">

                        <table class="table mt-4" id="FinanceReport">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Supplier </th>
                                    <th>Phone </th>
                                    <th>Type </th>
                                    <th>Sub Total</th>
                                    <th>Discount</th>
                                    <th>Net Price</th>
                                    <th>Additionals</th>
                                    <th>Paid Amount</th>
                                    <th>Balance</th>
                                    <th>Pay Method</th>
                                    <th>Purchased By</th>
                                </tr>
                            </thead>
                            <tbody id="printTableBody">
                                <!-- Data will be appended here by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <center>
                             <img src="{{ asset('/Logo/Report-logo.png') }}" width="150" alt="Company Logo">
                <h1>{{ $settings->company_name ?? 'WARAABE FUEL STATIONS' }}</h1>
                <p>{{ $settings->company_address ?? 'Kaalinta Shiidaalka Waraabe, Berbera Somaliland' }}
                    <br>
                    Tel: {{ $settings->phone1 ?? '' }}{{ !empty($settings->phone2) ? ' | ' . $settings->phone2 : '' }} <br>
                    ZAAD: {{ $settings->zaad ?? '' }} | EDAHAB: {{ $settings->edahab ?? '' }}
                </p>
                            <hr>
                            <h4 class="card-title">Purchase Payment Report</h4>
                        </center>
                        <table class="table mt-4" id="visibleFinanceReport">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Phone</th>
                                    <th>Type </th>
                                    <th>Sub Total</th>
                                    <th>Discount</th>
                                    <th>Net Price</th>
                                    <th>Additionals</th>
                                    <th>Paid Amount</th>
                                    <th>Balance</th>
                                    <th>Pay Method</th>
                                    <th>Purchased
                                    <th>
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
        function getFinanceReport() {
            let client = document.getElementById('clientID').value;
            let type = document.getElementById('type').value;
            let startDate = document.getElementById('startDate').value;
            let endDate = document.getElementById('endDate').value;
            let depID = document.getElementById('depID').value;

            // Validate if both dates are provided
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
            visibleTableBody.empty(); // Clear the table body before adding new data
            printTableBody.empty(); // Clear the print table body before adding new data

            let url = `{{ route('info.purchasePayment') }}`;

            axios.get(url, {
                    params: {
                        startDate: startDate,
                        endDate: endDate,
                        clientID: client,
                        type: type,
                        depID: depID
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .then(response => {
                    if (response.data.success) {
                        if (response.data.data.length > 0) {
                            response.data.data.forEach(record => {
                                appendToTable(record, visibleTableBody);
                                appendToTable(record, printTableBody);
                            });
                        } else {
                            visibleTableBody.append('<tr><td colspan="7" class="text-center">No data found</td></tr>');
                            printTableBody.append('<tr><td colspan="7" class="text-center">No data found</td></tr>');
                        }
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data',
                            text: response.data.message,
                        });
                        visibleTableBody.append('<tr><td colspan="7" class="text-center">' + response.data.message +
                            '</td></tr>');
                        printTableBody.append('<tr><td colspan="7" class="text-center">' + response.data.message +
                            '</td></tr>');
                    }
                })
                .catch(error => {
                    console.error(error);
                    visibleTableBody.append('<tr><td colspan="7" class="text-center">Error fetching data</td></tr>');
                    printTableBody.append('<tr><td colspan="7" class="text-center">Error fetching data</td></tr>');
                });
        }


       function appendToTable(data, tableBody) {
            var tableRow = '<tr>' +
                '<td>' + (data.date ?? 'N/A') + '</td>' +
               '<td>' + (data.supplier?.name ?? 'N/A') + '</td>' +
                '<td>' + (data.supplier?.phone ?? 'N/A') + '</td>' +
                '<td>' + (data.type ?? 'N/A') + '</td>' +
                '<td>' + (data.subTotal ?? '0') + '</td>' +
                '<td>' + (data.discount ?? '0') + '</td>' +
                '<td>' + (data.net_price ?? '0') + '</td>' +
                '<td>' + (data.add_cost ?? '0') + '</td>' +
                '<td>' + (data.paidAmount ?? '0') + '</td>' +
                '<td>' + (data.balance ?? '0') + '</td>' +
                '<td>' + (data.payMethod ?? 'N/A') + '</td>' +
                '<td>' + (data.purchased_by_user?.name ?? 'N/A') + '</td>' +
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
            XLSX.writeFile(wb, "PurchasePaymentReport.xlsx");
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
            doc.save('PurchasePaymentReport.pdf');
        }
    </script>
@endsection
