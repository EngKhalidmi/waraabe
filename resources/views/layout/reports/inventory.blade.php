@extends ('admin.admin_master')
@section('title', 'Saacid - Inventory Report')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Inventory Report</h4>
                    <h6>Manage Your Inventory Report</h6>
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
                    <form id="reportFilterForm">
                        @csrf
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Report Date</label>
                                    <select class="form-control" id="datePreset">
                                        <option value="today">Today</option>
                                        <option value="lastWeek" selected>Last Week</option>
                                        <option value="custom">Custom Date</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12" id="customDateContainer" style="display:none;">
                                <div class="form-group">
                                    <label>Custom Date</label>
                                    <input type="date" class="form-control" id="customDate">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary form-control"
                                        onclick="getInventoryReport()">
                                        <i class="fas fa-filter"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

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

                    <!-- Display results -->
                    <div class="table-responsive mt-4">
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
                            <h4 class="card-title">Inventory Report</h4>
                            <p id="reportDateRange"></p>
                        </center>
                        <table class="table mt-4" id="visibleFinanceReport">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Sold</th>
                                    <th>Remaining</th>
                                    <th>Unit Price</th>
                                    <th>Selling Price</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody id="visibleTableBody">
                                <!-- Data will be appended here by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
            <script>
                function getInventoryReport() {
                    const preset = document.getElementById('datePreset').value;
                    let startDate, endDate;

                    // Set dates based on preset
                    switch (preset) {
                        case 'today':
                            startDate = endDate = new Date().toISOString().split('T')[0];
                            break;
                        case 'lastWeek':
                            const lastWeek = new Date();
                            lastWeek.setDate(lastWeek.getDate() - 7);
                            startDate = lastWeek.toISOString().split('T')[0];
                            endDate = new Date().toISOString().split('T')[0];
                            break;
                        case 'custom':
                            startDate = endDate = document.getElementById('customDate').value;
                            if (!startDate) {
                                alert('Please select a custom date');
                                return;
                            }
                            break;
                    }

                    // Show loading state
                    Swal.fire({
                        title: 'Generating Report...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // Fetch report data
                    axios.get("{{ route('inventory.report') }}", {
                            params: {
                                startDate,
                                endDate
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        })
                        .then(response => {
                            Swal.close();
                            if (response.data.success) {
                                updateReportTable(response.data);
                            } else {
                                showError(response.data.message);
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            showError('Failed to load inventory data');
                            console.error(error);
                        });
                }

                function updateReportTable(data) {
                    const tableBody = $('#visibleTableBody');
                    tableBody.empty();

                    if (data.data.length > 0) {
                        data.data.forEach(item => {
                            tableBody.append(`
                <tr>
                    <td>${item.date}</td>
                    <td>${item.item}</td>
                    <td>${item.initial_quantity}</td>
                    <td>${item.sold_quantity}</td>
                    <td>${item.remaining_quantity}</td>
                    <td>${item.price}</td>
                    <td>${item.selling_price}</td>
                    <td>${item.total_value}</td>
                </tr>
            `);
                        });

                        // Update date range display
                        const range = data.meta.date_range;
                        $('#reportDateRange').text(range.description);
                    } else {
                        tableBody.append('<tr><td colspan="6" class="text-center">No inventory data found</td></tr>');
                    }
                }

                // Toggle custom date field visibility
                $('#datePreset').change(function() {
                    $('#customDateContainer').toggle(this.value === 'custom');
                });

                // Initialize with last week's data
                $(document).ready(function() {
                    getInventoryReport();
                });

                function formatDate(dateString) {
                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    };
                    return new Date(dateString).toLocaleDateString(undefined, options);
                }

                function printReport() {
                    // Get the visible report content
                    var printContents = document.getElementById('visibleFinanceReport').parentElement.innerHTML;
                    var originalContents = document.body.innerHTML;

                    // Create a new window for printing
                    var printWindow = window.open('', '_blank');
                    printWindow.document.write(`
        <html>
            <head>
                <title>Inventory Report</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .text-center { text-align: center; }
                    img { display: block; margin: 0 auto; }
                </style>
            </head>
            <body>
                ${printContents}
            </body>
        </html>
    `);
                    printWindow.document.close();
                    printWindow.focus();

                    // Wait for content to load before printing
                    printWindow.onload = function() {
                        printWindow.print();
                        printWindow.close();
                    };
                }

                function exportToExcel() {
                    // Select the table element
                    const table = document.getElementById("visibleFinanceReport");

                    // Create a workbook
                    const wb = XLSX.utils.table_to_book(table);

                    // Save the Excel file
                    XLSX.writeFile(wb,
                        `Inventory_Report_${document.getElementById('startDate').value}_to_${document.getElementById('endDate').value}.xlsx`
                    );
                }

                function exportToPDF() {
                    // Get the table element
                    const table = document.getElementById("visibleFinanceReport");
                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;

                    // Initialize jsPDF
                    const doc = new jsPDF({
                        orientation: 'landscape'
                    });

                    // Add title and date range
                    doc.setFontSize(16);
                    doc.text('Inventory Report', 14, 15);
                    doc.setFontSize(10);
                    doc.text(`Date Range: ${formatDate(startDate)} to ${formatDate(endDate)}`, 14, 22);

                    // Add the table
                    doc.autoTable({
                        html: table,
                        startY: 30,
                        styles: {
                            cellPadding: 3,
                            fontSize: 8,
                            valign: 'middle'
                        },
                        headStyles: {
                            fillColor: [220, 220, 220],
                            textColor: 0,
                            fontStyle: 'bold'
                        }
                    });

                    // Save the PDF
                    doc.save(`Inventory_Report_${startDate}_to_${endDate}.pdf`);
                }

                // Call the function when the page loads
                $(document).ready(function() {
                    getInventoryReport(); // Auto-fetch with default dates
                });
            </script>

            <!-- Include required libraries for export functionality -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>

        @endsection
