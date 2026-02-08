@extends ('admin.admin_master')
@section('title', 'Saacid - Purchases ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Purchase List</h4>
<h6>Manage your Purchases</h6>
</div>
<div class="page-btn">
<a href="{{route('purchase.add')}}" class="btn btn-added"><img src="assets/img/icons/plus.svg" alt="img">Add Purchase</a>
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

<!-- Tabs Navigation -->
<div class="card mb-3">
    <div class="card-body p-2">
        <ul class="nav nav-tabs nav-tabs-bottom mb-0" id="purchaseTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="fuel-tab" data-bs-toggle="tab" 
                        data-bs-target="#fuel-purchases" type="button" role="tab" 
                        aria-controls="fuel-purchases" aria-selected="true">
                    <i class="fas fa-gas-pump me-2"></i>Fuel Purchases
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="oil-tab" data-bs-toggle="tab" 
                        data-bs-target="#oil-purchases" type="button" role="tab" 
                        aria-controls="oil-purchases" aria-selected="false">
                    <i class="fas fa-oil-can me-2"></i>Oil Purchases
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="all-tab" data-bs-toggle="tab" 
                        data-bs-target="#all-purchases" type="button" role="tab" 
                        aria-controls="all-purchases" aria-selected="false">
                    <i class="fas fa-list-alt me-2"></i>All Purchases
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content" id="purchaseTabsContent">
    <!-- Fuel Purchases Tab -->
    <div class="tab-pane fade show active" id="fuel-purchases" role="tabpanel" aria-labelledby="fuel-tab">
        <!-- Search Info -->
        <div class="card">
            <div class="card-body">
                <div class="table-top">
                    <div class="search-set">
                        <div class="search-path">
                            <a class="btn btn-filter" id="filter_search_fuel">
                                <img src="assets/img/icons/filter.svg" alt="img">
                                <span><img src="assets/img/icons/closes.svg" alt="img"></span>
                            </a>
                        </div>
                        <div class="search-input">
                            <a class="btn btn-searchset"><img src="assets/img/icons/search-white.svg" alt="img"></a>
                        </div>
                    </div>
                </div>

                <div class="card" id="filter_inputs_fuel">
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-13">
                                <div class="form-group">
                                    <input type="text" id="name_fuel" placeholder="Filter By Name">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <select name="supplier_fuel" id="supplier_fuel" class="select">
                                        <option value="" selected>Filter By Supplier</option>
                                        @foreach($suppliers as $supplier)
                                        <option value="{{$supplier->name}}">{{$supplier->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="date" class="form-control" id="startDate_fuel" >
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="date" class="form-control" id="endDate_fuel" >
                                </div>
                            </div>
                            <div class="col-lg-1 col-sm-6 col-12  ms-auto">
                                <div class="form-group">
                                    <button type="button" class="btn btn-filters ms-auto" id="searchBtn_fuel">
                                        <img src="assets/img/icons/search-whites.svg" alt="img">
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="fuelPurchaseTable">
                        <thead class="">
                            <tr class="bg-primary">
                                <th class="text-white">#</th>
                                <th class="text-white">Product</th>
                                <th class="text-white">Quantity</th>
                                <th class="text-white">Unit Costs</th>
                                <th class="text-white">Additionals</th>
                                <th class="text-white">Total Costs</th>
                                <th class="text-white">Remaining</th>
                                <th class="text-white">Supplier</th>
                                <th class="text-white">Purchased</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Oil Purchases Tab -->
    <div class="tab-pane fade" id="oil-purchases" role="tabpanel" aria-labelledby="oil-tab">
        <!-- Search Info -->
        <div class="card">
            <div class="card-body">
                <div class="table-top">
                    <div class="search-set">
                        <div class="search-path">
                            <a class="btn btn-filter" id="filter_search_oil">
                                <img src="assets/img/icons/filter.svg" alt="img">
                                <span><img src="assets/img/icons/closes.svg" alt="img"></span>
                            </a>
                        </div>
                        <div class="search-input">
                            <a class="btn btn-searchset"><img src="assets/img/icons/search-white.svg" alt="img"></a>
                        </div>
                    </div>
                </div>

                <div class="card" id="filter_inputs_oil">
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-13">
                                <div class="form-group">
                                    <input type="text" id="name_oil" placeholder="Filter By Name">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <select name="supplier_oil" id="supplier_oil" class="select">
                                        <option value="" selected>Filter By Supplier</option>
                                        @foreach($suppliers as $supplier)
                                        <option value="{{$supplier->name}}">{{$supplier->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="date" class="form-control" id="startDate_oil" >
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="date" class="form-control" id="endDate_oil" >
                                </div>
                            </div>
                            <div class="col-lg-1 col-sm-6 col-12  ms-auto">
                                <div class="form-group">
                                    <button type="button" class="btn btn-filters ms-auto" id="searchBtn_oil">
                                        <img src="assets/img/icons/search-whites.svg" alt="img">
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="oilPurchaseTable">
                        <thead class="">
                            <tr class="bg-primary">
                                <th class="text-white">#</th>
                                <th class="text-white">Product</th>
                                <th class="text-white">Quantity</th>
                                <th class="text-white">Unit Costs</th>
                                <th class="text-white">Additionals</th>
                                <th class="text-white">Total Costs</th>
                                <th class="text-white">Remaining</th>
                                <th class="text-white">Supplier</th>
                                <th class="text-white">Purchased</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- All Purchases Tab -->
    <div class="tab-pane fade" id="all-purchases" role="tabpanel" aria-labelledby="all-tab">
        <!-- Search Info -->
        <div class="card">
            <div class="card-body">
                <div class="table-top">
                    <div class="search-set">
                        <div class="search-path">
                            <a class="btn btn-filter" id="filter_search_all">
                                <img src="assets/img/icons/filter.svg" alt="img">
                                <span><img src="assets/img/icons/closes.svg" alt="img"></span>
                            </a>
                        </div>
                        <div class="search-input">
                            <a class="btn btn-searchset"><img src="assets/img/icons/search-white.svg" alt="img"></a>
                        </div>
                    </div>
                </div>

                <div class="card" id="filter_inputs_all">
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-13">
                                <div class="form-group">
                                    <input type="text" id="name_all" placeholder="Filter By Name">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <select name="supplier_all" id="supplier_all" class="select">
                                        <option value="" selected>Filter By Supplier</option>
                                        @foreach($suppliers as $supplier)
                                        <option value="{{$supplier->name}}">{{$supplier->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="date" class="form-control" id="startDate_all" >
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="date" class="form-control" id="endDate_all" >
                                </div>
                            </div>
                            <div class="col-lg-1 col-sm-6 col-12  ms-auto">
                                <div class="form-group">
                                    <button type="button" class="btn btn-filters ms-auto" id="searchBtn_all">
                                        <img src="assets/img/icons/search-whites.svg" alt="img">
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="allPurchaseTable">
                        <thead class="">
                            <tr class="bg-primary">
                                <th class="text-white">#</th>
                                <th class="text-white">Product</th>
                                <th class="text-white">Quantity</th>
                                <th class="text-white">Unit Costs</th>
                                <th class="text-white">Additionals</th>
                                <th class="text-white">Total Costs</th>
                                <th class="text-white">Remaining</th>
                                <th class="text-white">Supplier</th>
                                <th class="text-white">Purchased</th>
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
</div>
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTables for each tab
        initializeFuelTable();
        initializeOilTable();
        initializeAllTable();

        // Tab change event
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).attr("aria-controls");
            
            // Refresh the table when tab is shown
            if (target === 'fuel-purchases') {
                $('#fuelPurchaseTable').DataTable().ajax.reload();
            } else if (target === 'oil-purchases') {
                $('#oilPurchaseTable').DataTable().ajax.reload();
            } else if (target === 'all-purchases') {
                $('#allPurchaseTable').DataTable().ajax.reload();
            }
        });

        function initializeFuelTable() {
            $('#fuelPurchaseTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [10, 25, 50],
                ajax: {
                    url: "{{ route('purchases') }}",
                    type: 'GET',
                    data: function (d) {
                        d.name = $('#name_fuel').val();
                        d.supplier = $('#supplier_fuel').val();
                        d.startDate = $('#startDate_fuel').val();
                        d.endDate = $('#endDate_fuel').val();
                        d.type = 'fuel'; // Add type filter for fuel
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'unit_cost', name: 'unit_cost' },
                    { data: 'add_cost', name: 'add_cost' },
                    { data: 'total_cost', name: 'total_cost' },
                    { data: 'remaining', name: 'remaining' },
                    { data: 'supplier', name: 'supplier' },
                    { data: 'created_at', name: 'created_at' },
                ]
            });

            $('#searchBtn_fuel').click(function() {
                $('#fuelPurchaseTable').DataTable().ajax.reload();
            });
        }

        function initializeOilTable() {
            $('#oilPurchaseTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [10, 25, 50],
                ajax: {
                    url: "{{ route('purchases') }}",
                    type: 'GET',
                    data: function (d) {
                        d.name = $('#name_oil').val();
                        d.supplier = $('#supplier_oil').val();
                        d.startDate = $('#startDate_oil').val();
                        d.endDate = $('#endDate_oil').val();
                        d.type = 'oil'; // Add type filter for oil
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'unit_cost', name: 'unit_cost' },
                    { data: 'add_cost', name: 'add_cost' },
                    { data: 'total_cost', name: 'total_cost' },
                    { data: 'remaining', name: 'remaining' },
                    { data: 'supplier', name: 'supplier' },
                    { data: 'created_at', name: 'created_at' },
                ]
            });

            $('#searchBtn_oil').click(function() {
                $('#oilPurchaseTable').DataTable().ajax.reload();
            });
        }

        function initializeAllTable() {
            $('#allPurchaseTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [10, 25, 50],
                ajax: {
                    url: "{{ route('purchases') }}",
                    type: 'GET',
                    data: function (d) {
                        d.name = $('#name_all').val();
                        d.supplier = $('#supplier_all').val();
                        d.startDate = $('#startDate_all').val();
                        d.endDate = $('#endDate_all').val();
                        d.type = 'all'; // No type filter for all purchases
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'unit_cost', name: 'unit_cost' },
                    { data: 'add_cost', name: 'add_cost' },
                    { data: 'total_cost', name: 'total_cost' },
                    { data: 'remaining', name: 'remaining' },
                    { data: 'supplier', name: 'supplier' },
                    { data: 'created_at', name: 'created_at' },
                ]
            });

            $('#searchBtn_all').click(function() {
                $('#allPurchaseTable').DataTable().ajax.reload();
            });
        }
    });

    function confirmDelete(catId) {
        // Trigger SweetAlert
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // If confirmed, submit the form
                document.getElementById('deleteForm-' + catId).submit();
            }
        });
    }
</script>



<style>
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #495057;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link.active {
        border-bottom: 3px solid #4361ee;
        color: #4361ee;
        background: transparent;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #4361ee;
    }
    
    .tab-content {
        padding-top: 1rem;
    }
</style>
@endsection