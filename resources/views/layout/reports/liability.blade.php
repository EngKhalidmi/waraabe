@extends ('admin.admin_master')
@section('title', 'Saacid - Liability Transactins Report')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Liability Transactions Report</h4>
                    <h6>Manage Your Liability Transactions</h6>
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
                                            <select name="supplier" id="supplier" class="select">
                                                <option value="">Select Supplier</option>
                                                @foreach ($suppliers as $client)
                                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-13">
                                        <div class="form-group">
                                            <select name="type" id="type" class="select">
                                                <option value="">Select Type</option>
                                                <option value="Short Term">Short Term</option>
                                                <option value="Long Term">Long Term</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6 col-13">
                                        <div class="form-group">
                                            <select name="trnsType" id="trnsType" class="select">
                                                <option value="">Select Transation Type</option>
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
                                    <th>Amount</th>
                                    <th>Liability Type </th>
                                    <th>Transaction Type </th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody id="printTableBody">
                                <!-- Data will be appended here by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <center>
                            <img src="{{ asset('/Logo/Report-logo.png') }}" alt="Company Logo" width="200">
                            <h1>Maal Spareparts</h1>
                            <p>Xarunta Iibinta Sparepart-ga Gaadiidka
                                <br>Sisibta ,Borama,Somaliland
                                <br>
                                0634583522 | Merchant 472320 | Edahab 749109
                            </p>
                            <hr>
                            <h4 class="card-title">Liabilities Report</h4>
                        </center>
                        <table class="table mt-4" id="visibleFinanceReport">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Supplier </th>
                                    <th>Amount</th>
                                    <th>Liability Type </th>
                                    <th>Transaction Type </th>
                                    <th>Description</th>
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
            let supplier = document.getElementById('supplier').value;
            let type = document.getElementById('type').value;
            let trnsType = document.getElementById('trnsType').value;
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

            let url = `{{ route('info.liability') }}`;

            axios.get(url, {
                    params: {
                        startDate: startDate,
                        endDate: endDate,
                        supplier: supplier,
                        trnsType: trnsType,
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
                '<td>' + data.date + '</td>' +
                '<td>' + data.client + '</td>' +
                '<td>' + data.amount + '</td>' +
                '<td>' + data.type + '</td>' +
                '<td>' + data.trnsType + '</td>' +
                '<td>' + data.info + '</td>' +
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
            XLSX.writeFile(wb, "LiabilityReport.xlsx");
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
            doc.save('LiabilityReport.pdf');
        }
    </script>
@endsection
