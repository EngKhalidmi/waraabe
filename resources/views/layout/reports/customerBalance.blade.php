@extends ('admin.admin_master')
@section('title', 'Saacid - Balances Report')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Balances Report</h4>
                    <h6>Manage Your Customer Balances Report</h6>
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
                                    <div class="col-lg-8 col-sm-6 col-12">
                                        <div class="form-group">
                                            <input type="text" id="name" name="name" placeholder="Search by customer name" class="form-control">
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
                            </form>
                        </div>
                    </div>

                    <!-- Print Area (hidden) -->
                    <div class="d-none">
                        <div id="printArea">
                            <div class="invoice-container" style="max-width: 800px; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333;">
                                <!-- Header Section -->
                                <div class="invoice-header" style="text-align: center; margin-bottom: 25px; padding: 20px 0; border-bottom: 2px solid #2c5aa0;">
                                    <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                                        <!-- Logo -->
                                        <div style="width: 120px; height: 120px; background: #2c5aa0; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; overflow: hidden;">
                                            <img src="{{ asset('/Logo/Report-logo.png') }}" alt="Company Logo" width="110" height="110" style="object-fit: contain;">
                                        </div>
                                        <div>
                                             <h1 style="font-size: 28px; font-weight: bold; margin: 0; color: #2c5aa0;">{{ $settings->company_name ?? 'WARAABE FUEL STATIONS' }}</h1>
                                             <p style="margin: 5px 0; font-size: 14px;">
                                                 {{ $settings->company_address ?? 'Kaalinta Shiidaalka Waraabe, Berbera Somaliland' }}<br>
                                                 Tel: {{ $settings->phone1 ?? '' }}{{ !empty($settings->phone2) ? ' | ' . $settings->phone2 : '' }} &nbsp;|&nbsp; ZAAD: {{ $settings->zaad ?? '' }} &bull; EDAHAB: {{ $settings->edahab ?? '' }}
                                             </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Report Title Section -->
                                <div class="invoice-info" style="display: flex; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                    <div>
                                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #2c5aa0;">CUSTOMER BALANCES REPORT</h3>
                                    </div>
                                    
                                    <div style="text-align: right;">
                                        <p style="margin: 5px 0; font-size: 14px;">
                                            <strong>Report Date:</strong> {{ now()->format('F j, Y') }}
                                        </p>
                                        <p style="margin: 5px 0; font-size: 12px; color: #6c757d;" id="printFilterInfo">
                                            <!-- Filter info will be added here -->
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Balances Table -->
                                <table class="invoice-table" style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                                    <thead>
                                        <tr style="background: #2c5aa0; color: white;">
                                            <th style="text-align: left; padding: 12px; font-weight: bold;">#</th>
                                            <th style="text-align: left; padding: 12px; font-weight: bold;">Customer Name</th>
                                            <th style="text-align: left; padding: 12px; font-weight: bold;">Phone</th>
                                            <th style="text-align: left; padding: 12px; font-weight: bold;">Address</th>
                                            <th style="text-align: right; padding: 12px; font-weight: bold;">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody id="printTableBody">
                                        <!-- Data will be appended here by JS -->
                                    </tbody>
                                </table>
                                
                                <!-- Totals Section -->
                                <div style="display: flex; justify-content: flex-end; padding-right: 10px;">
                                    <div style="width: 300px;">
                                        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-top: 2px solid #2c5aa0;">
                                            <span style="font-weight: bold; font-size: 16px;">Total Balance:</span>
                                            <span style="font-weight: bold; font-size: 16px; color: #2c5aa0;" id="printTotalBalance">$0.00</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Footer Section -->
                                <div class="invoice-footer" style="margin-top: 40px; padding: 20px 0; border-top: 2px solid #2c5aa0;">
                                    <div style="text-align: center; margin-bottom: 20px;">
                                        <p style="font-size: 16px; font-weight: bold; color: #2c5aa0;">
                                            <i data-lucide="handshake" style="margin-right: 8px;"></i>Customer Balances Report
                                        </p>
                                    </div>
                                    
                          
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visible table -->
                    <div class="table-responsive mt-4">
                        <table class="table mt-4" id="visibleFinanceReport">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer Name</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody id="visibleTableBody">
                                <!-- Data will be appended here by JS -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                    <td id="visibleTotalBalance"><strong>$0.00</strong></td>
                                </tr>
                            </tfoot>
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
        // Get CSRF token from Laravel
        function getCsrfToken() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                return metaTag.getAttribute('content');
            }
            
            const csrfInput = document.querySelector('input[name="_token"]');
            if (csrfInput) {
                return csrfInput.value;
            }
            
            console.warn('CSRF token not found');
            return '';
        }

        function getFinanceReport() {
            let name = document.getElementById('name').value;
            let depID = document.getElementById('depID').value;
            
            let visibleTableBody = document.querySelector('#visibleTableBody');
            let printTableBody = document.querySelector('#printTableBody');
            
            if (!visibleTableBody || !printTableBody) {
                console.error('Table bodies not found');
                return;
            }
            
            visibleTableBody.innerHTML = ''; // Clear the visible table body
            printTableBody.innerHTML = ''; // Clear the print table body

            // Show loading indicator
            const loading = Swal.fire({
                title: 'Loading...',
                text: 'Fetching customer balances',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let url = `{{ route('info.customerbalance') }}`;
            const csrfToken = getCsrfToken();

            axios.get(url, {
                    params: {
                        name: name,
                        depID: depID
                    },
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    loading.close();
                    
                    const data = response.data;
                    let totalBalance = 0;
                    let serialNumber = 1;

                    // Update filter info for print
                    let filterInfo = 'All Customers';
                    if (name) filterInfo = `Filtered by: ${name}`;
                    if (depID) {
                        const depSelect = document.getElementById('depID');
                        const depName = depSelect.options[depSelect.selectedIndex].text;
                        filterInfo = depName;
                    }
                    document.getElementById('printFilterInfo').textContent = filterInfo;

                    if (data && data.length > 0) {
                        data.forEach(item => {
                            appendToTable(item, visibleTableBody, serialNumber);
                            appendToTable(item, printTableBody, serialNumber);
                           totalBalance += parseMoney(item.balance);
                            serialNumber++;
                        });
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'No Data',
                            text: 'No data available for the selected filter.',
                        });
                        visibleTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No data available</td></tr>';
                        printTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No data available</td></tr>';
                    }

                    // Update total fields
                 document.getElementById('visibleTotalBalance').innerHTML = '<strong>$' + formatMoney(totalBalance) + '</strong>';
document.getElementById('printTotalBalance').textContent = '$' + formatMoney(totalBalance);
                })
                .catch(error => {
                    loading.close();
                    console.error('API Error:', error);
                    
                    let errorMessage = 'Error fetching data';
                    if (error.response) {
                        errorMessage = error.response.data.message || `Server error: ${error.response.status}`;
                    } else if (error.request) {
                        errorMessage = 'Network error: No response from server';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                    });
                    
                    visibleTableBody.innerHTML = '<tr><td colspan="5" class="text-center">' + errorMessage + '</td></tr>';
                    printTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">' + errorMessage + '</td></tr>';
                });
        }
            
            
            function parseMoney(value) {
    if (value === null || value === undefined || value === '') return 0;

    return parseFloat(
        value.toString().replace(/,/g, '')
    ) || 0;
}

function formatMoney(value) {
    return parseMoney(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}


        function appendToTable(data, tableBody, serialNumber) {
            if (!tableBody) return;
            
            var tableRow = '<tr>' +
                '<td style="padding: 10px;">' + (serialNumber || '') + '</td>' +
                '<td style="padding: 10px;">' + (data.name || '') + '</td>' +
                '<td style="padding: 10px;">' + (data.phone || '') + '</td>' +
                '<td style="padding: 10px;">' + (data.address || '') + '</td>' +
               '<td style="padding: 10px; text-align: right; font-weight: 500;">$' + formatMoney(data.balance) + '</td>' +
                '</tr>';

            tableBody.innerHTML += tableRow;
        }

        function printReport() {
            const printArea = document.getElementById('printArea');
            if (!printArea) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Print area not found',
                });
                return;
            }
            
            // Get the print area content
            const printContent = printArea.innerHTML;
            
            // Create a new window for printing
            const printWindow = window.open('', '_blank', 'width=900,height=650');
            
            // Write the HTML content to the new window
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Customer Balances Report</title>
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
                            font-size: 10px;
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
                            @page {
                                margin: 20mm;
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

        // Set default headers for all axios requests
        axios.defaults.headers.common['X-CSRF-TOKEN'] = getCsrfToken();
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>
    <script>
        function exportToExcel() {
            const table = document.getElementById("visibleFinanceReport");
            if (!table) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Table not found for export',
                });
                return;
            }

            try {
                const wb = XLSX.utils.table_to_book(table);
                XLSX.writeFile(wb, "CustomerBalancesReport.xlsx");
            } catch (error) {
                console.error('Excel export error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Error exporting to Excel: ' + error.message,
                });
            }
        }

        function exportToPDF() {
            const table = document.getElementById("visibleFinanceReport");
            if (!table) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Table not found for export',
                });
                return;
            }

            try {
                const doc = new jsPDF();
                doc.autoTable({
                    html: table,
                    theme: 'grid',
                    headStyles: {
                        fillColor: [44, 90, 160] // #2c5aa0
                    },
                    footStyles: {
                        fillColor: [240, 240, 240], // Light gray for footer
                        textColor: [0, 0, 0],
                        fontStyle: 'bold'
                    }
                });
                doc.save('CustomerBalancesReport.pdf');
            } catch (error) {
                console.error('PDF export error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Error exporting to PDF: ' + error.message,
                });
            }
        }
    </script>
@endsection