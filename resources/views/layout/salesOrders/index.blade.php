@extends ('admin.admin_master')
@section('title', 'Saacid - Sales Orders ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Sales Orders Transaction List</h4>
                    <h6>Manage your Sales Orders Transactions</h6>
                </div>
                <div class="page-btn">
                    <a href="{{ route('sales.add') }}" class="btn btn-added"><img src="assets/img/icons/plus.svg"
                            alt="img">Create New Sales</a>
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


            <!-- Search Info -->
            <div class="card">
                <div class="card-body">
                    <div class="table-top">
                        <div class="search-set">
                            <div class="search-path">
                                <a class="btn btn-filter" id="filter_search">
                                    <img src="assets/img/icons/filter.svg" alt="img">
                                    <span><img src="assets/img/icons/closes.svg" alt="img"></span>
                                </a>
                            </div>
                            <div class="search-input">
                                <a class="btn btn-searchset"><img src="assets/img/icons/search-white.svg"
                                        alt="img"></a>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="filter_inputs">
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-lg-3 col-sm-6 col-13">
                                    <div class="form-group">
                                        <input type="text" id="name" placeholder="Filter By Product">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-13">
                                    <div class="form-group">
                                        <input type="text" id="phone" placeholder="Filter By Trnsaction ID">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="date" class="form-control" id="startDate">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="date" class="form-control" id="endDate">
                                    </div>
                                </div>
                                <div class="col-lg-1 col-sm-6 col-12  ms-auto">
                                    <div class="form-group">
                                        <button type="buttom" class="btn btn-filters ms-auto" id="searchBtn"><img
                                                src="assets/img/icons/search-whites.svg" alt="img"></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>






                    <div class="table-responsive">
                        <table class="table" id="salesPaymentTable">
                            <thead>
                                <tr class="bg-primary">
                                    <th class="text-white">#</th>
                                    <th class="text-white">Product Name</th>
                                    <th class="text-white">Quantity</th>
                                    {{-- <th>Cost Unit</th>
<th class="text-white">Total Cost Unit</th> --}}
                                    <th class="text-white">Price</th>
                                    <th class="text-white">Total Price</th>
                                    <th class="text-white">Payment Id</th>
                                    <th class="text-white">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            // Initialize the DataTable
            let table = $('#salesPaymentTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [10, 25, 50],
                ajax: {
                    url: "{{ route('sales') }}",
                    type: 'GET',
                    data: function(d) {
                        d.name = $('#name').val();
                        d.phone = $('#phone').val();
                        d.payment_method = $('#payment_method').val();
                        d.type = $('#type').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                    },
                    dataSrc: function(json) {
                        console.log("AJAX response received:", json);
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.error("AJAX Error:", xhr.responseText);
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'product_name',
                        name: 'product_name'
                    },
                    {
                        data: 'quantity',
                        name: 'quantity'
                    },
                    // { data: 'cost_per_unit', name: 'cost_per_unit' },
                    // { data: 'total_cost_per_unit', name: 'total_cost_per_unit' },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'total_price',
                        name: 'total_price'
                    },
                    {
                        data: 'sales_transaction_id',
                        name: 'sales_transaction_id'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                ]
            });

            // Filter search button click
            $('#searchBtn').click(function() {
                table.draw();
            });
        });
    </script>
@endsection
