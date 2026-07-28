@extends('admin.admin_master')
@section('title', 'Saacid - Report Of Balancesheet ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>BalanceSheet Report</h4>
                    <h6>Manage Your Balancesheet Statement</h6>
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


            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('BalanceSheet') }}" class="mb-4">
                                @csrf
                                <div class="student-group-form">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-4">
                                            <div class="form-group">
                                                <input type="date" class="form-control" name="startDate" id="startDate">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4">
                                            <div class="form-group">
                                                <input type="date" class="form-control" name="endDate" id="endDate">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary btn-block">Generate</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>


                            <div class="table-responsive" id="BalanceSheet">
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
                                    <h4 class="card-title">Balance Sheet</h4>
                                </center>
                                <hr>
                                <table class="table ">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Assets Section -->
                                        <tr>
                                            <td colspan="2" class="text-center"><strong>Assets</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Total Cash</td>
                                            <td>${{ number_format($balanceSheetData['totalCash'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Accounts Receivable</td>
                                            <td>${{ number_format($balanceSheetData['receivable'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Cash On Bank</td>
                                            <td>${{ number_format($balanceSheetData['Bank'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Inventory</td>
                                            <td>${{ number_format($balanceSheetData['Inventory'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Fixed Assets</td>
                                            <td>${{ number_format($balanceSheetData['fixedAsset'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Assets</strong></td>
                                            <td><strong>${{ number_format($balanceSheetData['totalAssets'], 2) }}</strong>
                                            </td>
                                        </tr>

                                        <!-- Liabilities Section -->
                                        <tr>
                                            <td colspan="2" class="text-center"><strong>Liabilities</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Long Term Liabilities</td>
                                            <td>${{ number_format($balanceSheetData['longTerm'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Short Term Liabilities</td>
                                            <td>${{ number_format($balanceSheetData['shortTerm'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Liabilities</strong></td>
                                            <td><strong>${{ number_format($balanceSheetData['totalLiabilities'], 2) }}</strong>
                                            </td>
                                        </tr>

                                        <!-- Equity Section -->
                                        <tr>
                                            <td colspan="2" class="text-center"><strong>Owner's Equity</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Owner's Equity</td>
                                            <td>${{ number_format($balanceSheetData['capital'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Retained Earning</td>
                                            <td>${{ number_format($balanceSheetData['retained'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Equity</strong></td>
                                            <td><strong>${{ number_format($balanceSheetData['TotalEquity'], 2) }}</strong>
                                            </td>
                                        </tr>

                                        <!-- Balance Sheet Total (Assets = Liabilities + Equity) -->
                                        <tr>
                                            <td colspan="2" class="text-center"><strong>Balance Sheet Total</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Total Assets</td>
                                            <td><strong>${{ number_format($balanceSheetData['Assets'], 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Total Liabilities + Equity</td>
                                            <td><strong>${{ number_format($balanceSheetData['capitalLiability'], 2) }}</strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Print Button -->
                            <button type="button" class="btn btn-primary ml-2 mt-4" onclick="printBalanceSheet()"><i
                                    class="fas fa-print ml-2"></i> <span class="ml-2">Print</span></button>
                            <button type="button" class="btn btn-success mt-4 ml-2" onclick="exportToExcel()"><i
                                    class="fas fa-table"></i> <span class="ml-2">Excel</span></button>
                            <button type="button" class="btn btn-danger mt-4 ml-2" onclick="exportToPDF()"><i
                                    class="fas fa-file-pdf"></i> <span class="ml-2">PDF</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function printBalanceSheet() {
                var printContents = document.getElementById('BalanceSheet').innerHTML;
                var originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                location.reload();
            }
        </script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
        <script>
            // JavaScript code for exporting to Excel

            function exportToExcel() {
                // Select the table element by its ID
                const table = document.getElementById("BalanceSheet");
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
                XLSX.writeFile(wb, "BalanceSheet.xlsx");
            }




            // Your script containing exportToPDF function here
            function exportToPDF() {
                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF();

                doc.autoTable({
                    html: '#BalanceSheet table',
                    startY: 20,
                    theme: 'striped',
                    headStyles: {
                        fillColor: [034, 233, 200]
                    },
                    styles: {
                        overflow: 'linebreak'
                    }
                });

                doc.save('BalanceSheet.pdf');
            }
        </script>

    @endsection
