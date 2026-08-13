@extends('admin.admin_master')
@section('title', 'Saacid - Report Of Incomestatement ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h4>Income Statement</h4>
                    <h6>Manage your Income Statement</h6>
                </div>
            </div>

            <!-- Toast Notifications for Status and Errors -->
            @if (session('status'))
                <div class="toast-container">
                    <div class="toast-message success">
                        <div class="toast-icon">
                            <i data-lucide="circle-check" class="icon-checkmark"></i>
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
                            <i data-lucide="circle-alert" class="icon-error"></i>
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

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

                            <!-- Date Filter Form -->
                            <form id="filterForm" class="mb-4">
                                @csrf
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-4">
                                            <div class="form-group">
                                                <input type="date" class="form-control" name="startDate" id="startDate"
                                                    value="{{ $formattedIncomeStatement['startDate'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-4">
                                            <div class="form-group">
                                                <input type="date" class="form-control" name="endDate" id="endDate"
                                                    value="{{ $formattedIncomeStatement['endDate'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <button type="submit" class="btn btn-info"><i data-lucide="sliders-horizontal"></i>
                                                Filter</button>
                                            <button type="button" class="btn btn-primary ml-2"
                                                onclick="printIncomeStatement()"><i data-lucide="printer" class="ml-2"></i> <span
                                                    class="ml-2">Print</span></button>
                                            <button type="button" class="btn btn-success ml-2" onclick="exportToExcel()"><i data-lucide="table"></i> <span class="ml-2">Excel</span></button>
                                            <button type="button" class="btn btn-danger ml-2" onclick="exportToPDF()"><i data-lucide="file-text"></i> <span class="ml-2">PDF</span></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <hr>

                            <!-- Loading Spinner -->
                            <div id="loadingSpinner" style="display: none; text-align: center;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="mt-2">Generating Report...</p>
                            </div>

                            <!-- Income Statement Report Section -->
                            <div class="mt-4" id="incomeStatementContainer">
                                <!-- Enhanced Print Area (hidden) -->
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
                                                        
                                                       <p id="reportPeriod">For Period: {{ $formattedIncomeStatement['startDate'] ?? '' }} to
                                            {{ $formattedIncomeStatement['endDate'] ?? '' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Report Title Section -->
                                            <div class="invoice-info" style="display: flex; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                                <div>
                                                    <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #2c5aa0;">INCOME STATEMENT REPORT</h3>
                                                </div>
                                                
                                                <div style="text-align: right;">
                                                    <p style="margin: 5px 0; font-size: 14px;">
                                                        <i data-lucide="calendar-days" style="color: #2c5aa0; margin-right: 5px;"></i>
                                                        <span id="printReportPeriod">For Period: {{ $formattedIncomeStatement['startDate'] ?? '' }} to {{ $formattedIncomeStatement['endDate'] ?? '' }}</span>
                                                    </p>
                                                    <p style="margin: 5px 0; font-size: 14px;">
                                                        <strong>Report Date:</strong> {{ now()->format('F j, Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <!-- Income Statement Table -->
                                            <table class="invoice-table" style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                                                <thead>
                                                    <tr style="background: #2c5aa0; color: white;">
                                                        <th style="text-align: left; padding: 12px; font-weight: bold;">Category</th>
                                                        <th style="text-align: right; padding: 12px; font-weight: bold;">Income</th>
                                                        <th style="text-align: right; padding: 12px; font-weight: bold;">Expense</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="printReportBody">
                                                    <!-- Data will be appended here by JS -->
                                                </tbody>
                                            </table>
                                            
                                            <!-- Footer Section -->
                                            <div class="invoice-footer" style="margin-top: 40px; padding: 20px 0; border-top: 2px solid #2c5aa0;">
                                                <div style="text-align: center; margin-bottom: 20px;">
                                                    <p style="font-size: 16px; font-weight: bold; color: #2c5aa0;">
                                                        <i data-lucide="trending-up" style="margin-right: 8px;"></i>Financial Performance Report
                                                    </p>
                                                </div>
                                                
                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visible Report Section -->
                                <div class="table-responsive" id="incomeStatement">
                                    <!-- Company Header -->
                                  

                                    <!-- Main Income Statement Table -->
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Income</th>
                                                <th>Expense</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reportBody">
                                            <!-- Data will be dynamically inserted here by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Javascript for Print, Excel and PDF export functions -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
        <script>
            // Utility function to format numbers to 2 decimal places
            function formatNumber(num) {
                return parseFloat(num || 0).toFixed(2);
            }

            // Function to dynamically render the report
            function renderReport(data) {
                const reportBody = document.getElementById('reportBody');
                const printReportBody = document.getElementById('printReportBody');
                const reportPeriod = document.getElementById('reportPeriod');
                const printReportPeriod = document.getElementById('printReportPeriod');

                reportBody.innerHTML = '';
                printReportBody.innerHTML = '';

                // Update report period headers
                const periodText = `For Period: ${data.startDate} to ${data.endDate}`;
                reportPeriod.textContent = periodText;
                printReportPeriod.textContent = periodText;

                // Generate report content
                const reportContent = `
                    <tr style="color: #111 !important;">
                        <td style="padding: 10px;"><strong>Report Period</strong></td>
                        <td style="padding: 10px; text-align: right;"><strong>${data.startDate}</strong></td>
                        <td style="padding: 10px; text-align: right;"><strong>${data.endDate}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Total Sales Revenue (Oil)</td>
                        <td style="padding: 10px; text-align: right;">$${formatNumber(data.SalesRevenue)}</td>
                        <td style="padding: 10px;"></td>
                    </tr>
                   
                    <tr>
                        <td style="padding: 10px;"><strong>Fuel Sales Revenue</strong></td>
                        <td style="padding: 10px; text-align: right;"><strong>$${formatNumber(data.TotalFuelSalesRevenue)}</strong></td>
                        <td style="padding: 10px;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; padding-left: 30px;">Regular Fuel Sales (Cash)</td>
                        <td style="padding: 10px; text-align: right;">$${formatNumber(data.RegularFuelSalesRevenue)}</td>
                        <td style="padding: 10px;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; padding-left: 30px;">Fuel Credit Sales</td>
                        <td style="padding: 10px; text-align: right;">$${formatNumber(data.CreditFuelSalesRevenue)}</td>
                        <td style="padding: 10px;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Fuel Sales Discount</td>
                        <td style="padding: 10px;"></td>
                        <td style="padding: 10px; text-align: right; color: #dc3545;">($${formatNumber(data.TotalFuelDiscount)})</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Net Fuel Sales Revenue</strong></td>
                        <td style="padding: 10px; text-align: right;"><strong>$${formatNumber(data.NetFuelSalesRevenue)}</strong></td>
                        <td style="padding: 10px;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Total Discounts (Oil)</td>
                        <td style="padding: 10px;"></td>
                        <td style="padding: 10px; text-align: right; color: #dc3545;">($${formatNumber(data.total_discount)})</td>
                    </tr>
                    <tr style="background-color: #e3f2fd;">
                        <td style="padding: 10px;"><strong>Total Revenue</strong></td>
                        <td style="padding: 10px; text-align: right;"><strong>$${formatNumber(data.totalRevenue)}</strong></td>
                        <td style="padding: 10px;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Cost Of Goods Sold</strong></td>
                        <td style="padding: 10px;"></td>
                        <td style="padding: 10px; text-align: right;"><strong style="color: #dc3545;">($${formatNumber(data.COGS)})</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; padding-left: 30px;">Cost Of Goods Sold (Oil)</td>
                        <td style="padding: 10px;"></td>
                        <td style="padding: 10px; text-align: right; color: #dc3545;">($${formatNumber(data.COGS - data.TotalFuelCOGS)})</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; padding-left: 30px;">Cost Of Goods Sold (Fuel)</td>
                        <td style="padding: 10px;"></td>
                        <td style="padding: 10px; text-align: right; color: #dc3545;">($${formatNumber(data.TotalFuelCOGS)})</td>
                    </tr>
                    <tr style="background-color: #d4edda;">
                        <td style="padding: 10px;"><strong>Gross Profit</strong></td>
                        <td style="padding: 10px;"></td>
                        <td style="padding: 10px; text-align: right;"><strong>$${formatNumber(data.gross_profit)}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Total Expenses</td>
                        <td style="padding: 10px;"></td>
                        <td style="padding: 10px; text-align: right; color: #dc3545;">($${formatNumber(data.expense)})</td>
                    </tr>
                    <tr style="background-color: ${data.netIncome < 0 ? '#f8d7da' : '#d1ecf1'};">
                        <td colspan="1" style="padding: 10px; text-align: center;"><strong>${data.netIncome < 0 ? 'NET LOSS' : 'NET PROFIT'}</strong></td>
                        <td colspan="2" style="padding: 10px; text-align: center;">
                            <strong style="color: ${data.netIncome < 0 ? '#dc3545' : '#28a745'};">
                                $${formatNumber(Math.abs(data.netIncome))}
                            </strong>
                        </td>
                    </tr>
                `;

                reportBody.innerHTML = reportContent;
                printReportBody.innerHTML = reportContent;
            }

            // Event listener for form submission
            document.getElementById('filterForm').addEventListener('submit', async function(event) {
                event.preventDefault(); // Prevent default form submission

                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const loadingSpinner = document.getElementById('loadingSpinner');
                const incomeStatementContainer = document.getElementById('incomeStatementContainer');

                // Show loading spinner and hide report container
                loadingSpinner.style.display = 'block';
                incomeStatementContainer.style.display = 'none';

                try {
                    const response = await axios.get('{{ route('Incomestatement.store') }}', {
                        params: {
                            startDate: startDate,
                            endDate: endDate
                        }
                    });

                    // Render the report with the fetched data
                    renderReport(response.data.data);

                } catch (error) {
                    console.error('Axios error:', error);
                    let errorMessage = 'Failed to fetch report data. Please try again.';
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMessage = error.response.data.error;
                    }
                    alert(errorMessage);
                } finally {
                    // Hide loading spinner and show report container
                    loadingSpinner.style.display = 'none';
                    incomeStatementContainer.style.display = 'block';
                }
            });

            // Function for Print export
            function printIncomeStatement() {
                const printArea = document.getElementById('printArea');
                if (!printArea) {
                    alert('Print area not found');
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
                        <title>Income Statement Report</title>
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

            // Functions for Excel and PDF export
            function exportToExcel() {
                try {
                    const table = document.getElementById("incomeStatement");
                    if (!table) {
                        console.error("Table element not found.");
                        return;
                    }
                    const wb = XLSX.utils.table_to_book(table);
                    XLSX.writeFile(wb, "IncomeStatement.xlsx");
                } catch (error) {
                    console.error("Error exporting to Excel:", error);
                    alert("Error exporting to Excel: " + error.message);
                }
            }

            function exportToPDF() {
                try {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF();
                    doc.setFontSize(16);
                    doc.text("Income Statement Report", 105, 15, {
                        align: 'center'
                    });

                    const reportPeriodElement = document.getElementById('reportPeriod');
                    if (reportPeriodElement) {
                        doc.setFontSize(12);
                        doc.text(reportPeriodElement.textContent, 105, 22, {
                            align: 'center'
                        });
                    }

                    doc.autoTable({
                        html: '#incomeStatement table',
                        startY: 30,
                        theme: 'grid',
                        styles: {
                            fontSize: 8
                        },
                        headStyles: {
                            fillColor: [44, 90, 160] // #2c5aa0
                        }
                    });
                    doc.save("IncomeStatement.pdf");
                } catch (error) {
                    console.error("Error exporting to PDF:", error);
                    alert("Error exporting to PDF: " + error.message);
                }
            }
        </script>

        <!-- CDN Links for Export Functions -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    @endsection