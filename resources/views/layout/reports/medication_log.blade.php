@extends ('admin.admin_master')
@section('title', 'Saacid - Patient Medication')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Patient Medication Report</h4>
<h6>Manage Your Medication Report</h6>
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
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Patient Name</label>
                                    <div class="row">
                                        <div class="col-lg-10 col-sm-10 col-10">
                                            <input type="text" name="customer_name" id="customerSearch"
                                                placeholder="Search Patient By Name" autocomplete="off"
                                                autocorrect="off" spellcheck="false">
                                            <div id="customerDropdown" class="dropdown-menu show"
                                                style="display: none; position: absolute; width: 39%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                            </div>
                                        </div>
                                        <input type="hidden" name="customerID" id="customerID" required>
                                        <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                            <div class="add-icon">
                                                <a href="{{ route('customer.add') }}" target="_blank"><img
                                                        src="{{ asset('/assets/img/icons/plus1.svg') }}" alt="img"></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Patient Phone</label>
                                    <div class="row">
                                        <div class="col-lg-12 col-sm-10 col-10">
                                            <input type="text" name="phone" id="phoneSearch"
                                                placeholder="Search Patient By Phone" autocomplete="off"
                                                autocorrect="off" spellcheck="false">
                                            <div id="phoneDropdown" class="dropdown-menu show"
                                                style="display: none; position: absolute; width: 23%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                  
                            <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="startDate">
                            </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <label>End Date</label>
                            <div class="form-group">
                            <input type="date" class="form-control" id="endDate" name="endDate">
                            </div>
                            </div>
                            <div class="col-lg-1 col-sm-6 col-12  ms-auto">
                            <div class="form-group">
                            <button type="button" class="btn btn-filters ms-auto" onclick="getFinanceReport()" id="searchBtn"><img src="{{asset('/assets/img/icons/search-whites.svg')}}" alt="img"></button>
                            </div>
                            </div>
                                <div class="form-group col-md-12">
                                        <button type="button" class="btn btn-primary" onclick="printReport()"><i class="fas fa-print"></i> <span class="ml-2">Print</span></button>
                                        <button type="button" class="btn btn-success" onclick="exportToExcel()"><i class="fas fa-table"></i>  <span class="ml-2">Excel</span></button>
                                        <button type="button" class="btn btn-danger" onclick="exportToPDF()"><i class="fas fa-file-pdf"></i> <span class="ml-2">PDF</span></button>
                                </div>
                                </div>
                             
                            </form>
                            

                        <!-- Display results -->
                        <div  class="table-responsive" id="printArea" style="display:none;">
                            {{-- logo --}}
                            <center>
                                <img src="{{asset('/Logo/warsame.png')}}" alt="Company Logo" width="200">
                        <h1>Warsame Medical Clinic</h1>
                        <p>Pharmacy & Clinic 
                            <br>Tell: 000000 | 0000000 |000000000 | Merchant: 00000000|00000000
                            </p><hr>
                                <h3>Sales Transactions Report </h3>
                            </center>
                            <table  class="table mt-4" id="FinanceReport">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Taken At </th>
                                        <th>Name</th>
                                        <th>Prescriptor</th>
                                        <th>Doctor </th>
                                        <th>Medication</th>
                                        <th>Route</th>
                                       
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
                                        
                                        <th>#</th>
                                        <th>Taken At </th>
                                        <th>Name</th>
                                        <th>Prescriptor</th>
                                        <th>Doctor </th>
                                        <th>Medication</th>
                                        <th>Route</th>
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
    
        const customerSearchInput = document.getElementById('customerSearch');
        const customerDropdown = document.getElementById('customerDropdown');
        const phoneSearchInput = document.getElementById('phoneSearch');
        const phoneDropdown = document.getElementById('phoneDropdown');
        
        // Function to select a customer from the dropdown
        customerSearchInput.addEventListener('input', function() {
            const query = customerSearchInput.value;

            if (query.length >= 2) {
                customerDropdown.innerHTML = `
                <div class="dropdown-item text-center">
                    <i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...
                </div>
            `;
                axios.get(`{{ route('search.name') }}?query=${query}`)
                    .then(response => {
                        customerDropdown.innerHTML = '';
                        if (response.data.length > 0) {
                            response.data.forEach(customer => {
                                const customerOption = document.createElement('a');
                                customerOption.className = 'dropdown-item';
                                customerOption.textContent = customer.customer_name;
                                customerOption.href = '#';
                                customerOption.dataset.id = customer.id;
                                customerOption.dataset.phone = customer.phone;
                                customerOption.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    selectcustomer(customer);
                                });

                                customerDropdown.appendChild(customerOption);
                            });

                            customerDropdown.style.display = 'block'; // Show the dropdown
                        } else {
                            customerDropdown.innerHTML = `
                            <div class="dropdown-item text-center text-muted">
                                No Petients found
                            </div>
                        `;
                            customerDropdown.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching customers:', error);
                        customerDropdown.style.display = 'none'; // Hide on error
                    });
            } else {
                customerDropdown.style.display = 'none'; // Hide if query is too short
            }
        });

        // Hide the dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!customerSearchInput.contains(event.target) && !customerDropdown.contains(event.target)) {
                customerDropdown.style.display = 'none';
            }
        });
        
        function selectcustomer(customer) {
            document.getElementById('customerSearch').value = customer.customer_name;
            document.getElementById('customerID').value = customer.id;
            document.getElementById('phoneSearch').value = customer.phone;
       
        
            customerDropdown.style.display = 'none';
            phoneDropdown.style.display = 'none'; // hide both if needed
        }




        // Function to select a customer from the dropdown
        phoneSearchInput.addEventListener('input', function() {
            const query = phoneSearchInput.value;

            if (query.length >= 2) {
                phoneDropdown.innerHTML = `
                <div class="dropdown-item text-center">
                    <i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...
                </div>
            `;
                axios.get(`{{ route('search.phone') }}?query=${query}`)
                    .then(response => {
                        phoneDropdown.innerHTML = '';
                        if (response.data.length > 0) {
                            response.data.forEach(customer => {
                                const customerOption = document.createElement('a');
                                customerOption.className = 'dropdown-item';
                                customerOption.textContent = customer.customer_name;
                                customerOption.href = '#';
                                customerOption.dataset.id = customer.id;
                                customerOption.dataset.phone = customer.phone;
                                customerOption.dataset.address = customer.address;
                                customerOption.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    selectcustomer(customer);
                                });

                                phoneDropdown.appendChild(customerOption);
                            });

                            phoneDropdown.style.display = 'block'; // Show the dropdown
                        } else {
                            phoneDropdown.innerHTML = `
                            <div class="dropdown-item text-center text-muted">
                                No Petients found
                            </div>
                        `;
                            phoneDropdown.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching customers:', error);
                        phoneDropdown.style.display = 'none'; // Hide on error
                    });
            } else {
                phoneDropdown.style.display = 'none'; // Hide if query is too short
            }
        });

        // Hide the dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!phoneSearchInput.contains(event.target) && !phoneDropdown.contains(event.target)) {
                phoneDropdown.style.display = 'none';
            }
        });
        
        
        
        
    function getFinanceReport() {
        let startDate = document.getElementById('startDate').value;
        let endDate = document.getElementById('endDate').value;
        let customerID = document.getElementById('customerID').value;
    
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
    
        let url = `{{ route('medication.log.report') }}`;
    
        axios.get(url, {
            params: {
                start_date: startDate,
                end_date: endDate,
                patientId: customerID,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .then(response => {
            if (response.data.success) {
                if (response.data.data.length > 0) {
                    response.data.data.forEach((record, index) => {
                        appendToTable(record, visibleTableBody, index);
                        appendToTable(record, printTableBody, index);
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
                visibleTableBody.append('<tr><td colspan="7" class="text-center">' + response.data.message + '</td></tr>');
                printTableBody.append('<tr><td colspan="7" class="text-center">' + response.data.message + '</td></tr>');
            }
        })
        .catch(error => {
            console.error(error);
            visibleTableBody.append('<tr><td colspan="7" class="text-center">Error fetching data</td></tr>');
            printTableBody.append('<tr><td colspan="7" class="text-center">Error fetching data</td></tr>');
        });
    }


    function appendToTable(data, tableBody, index) {
    var tableRow = '<tr>' +
        '<td>' + (index + 1) + '</td>' +  // index starts at 0, so add 1
        '<td>' + data.taken_at + '</td>' +
        '<td>' + data.customer.customer_name + '</td>' +
        '<td>' + data.user.name + '</td>' +
        '<td>' + data.medication.doctorID + '</td>' +
        '<td>' + data.medication.medication + '</td>' +
        '<td>' + data.medication.medication_route + '</td>' +
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
    XLSX.writeFile(wb, "SalesReport.xlsx");
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
    doc.save('SalesReport.pdf');
}

</script>
@endsection
