@extends ('admin.admin_master')
@section('title', 'Saacid - Fuel Credit Sales ')
@section('admin')
    <style>
        #filter_inputs {
            display: block !important;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .customer-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .customer-details h6 {
            margin-bottom: 5px;
            font-weight: 600;
        }
    </style>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Fuel Credit Sales List</h4>
                    <h6>Manage your Fuel Credit Sales</h6>
                </div>
                <div class="page-btn">
                    <a href="{{ route('fuel.sales.create') }}" class="btn btn-added">Create New Credit Sale</a>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Search Info -->
            <div class="card">
                <div class="card-body">
                    <div class="table-top">
                        <div class="search-set">
                            <div class="search-path">
                                <a class="btn btn-filter" id="filter_search">
                                    <img src="{{ asset('assets/img/icons/filter.svg') }}" alt="img">
                                    <span><img src="{{ asset('assets/img/icons/closes.svg') }}" alt="img"></span>
                                </a>
                            </div>
                            <div class="search-input">
                                <a class="btn btn-searchset"><img src="{{ asset('assets/img/icons/search-white.svg') }}"
                                        alt="img"></a>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="filter_inputs">
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="text" id="customer_name" placeholder="Filter By Customer">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="text" id="fuel_type" placeholder="Filter By Fuel Type">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <select class="form-control" id="status">
                                            <option value="">Select Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="partial">Partial</option>
                                            <option value="paid">Paid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="date" class="form-control" id="startDate" placeholder="Start Date">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="date" class="form-control" id="endDate" placeholder="End Date">
                                    </div>
                                </div>
                                <div class="col-lg-1 col-sm-6 col-12 ms-auto">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-filters ms-auto" id="searchBtn">
                                            <img src="{{ asset('assets/img/icons/search-whites.svg') }}" alt="img">
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table" id="fuelCreditSalesTable">
                            <thead>
                                <tr class="bg-primary">
                                    <th class="text-white">#</th>
                                    <th class="text-white">Customer Name</th>
                                    <th class="text-white">Phone</th>
                                    <th class="text-white">Fuel Type</th>
                                    <th class="text-white">Quantity</th>
                                    <th class="text-white">Rate</th>
                                    <th class="text-white">Total Amount</th>
                                    <th class="text-white">Status</th>
                                    <th class="text-white">Date</th>
                                    <th class="text-white">Time</th>
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
            let table = $('#fuelCreditSalesTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [10, 25, 50],
                ajax: {
                    url: "{{ route('fuel.sales.credit.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.customer_name = $('#customer_name').val();
                        d.fuel_type = $('#fuel_type').val();
                        d.status = $('#status').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'customer_phone',
                        name: 'customer_phone'
                    },
                    {
                        data: 'fuel_type',
                        name: 'fuel_type'
                    },
                    {
                        data: 'quantity',
                        name: 'quantity'
                    },
                    {
                        data: 'rate',
                        name: 'rate'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    // Add any custom row styling if needed
                    if (data.status.includes('badge-danger')) {
                        $(row).addClass('bg-light-danger');
                    } else if (data.status.includes('badge-warning')) {
                        $(row).addClass('bg-light-warning');
                    }
                }
            });

            // Filter search button click
            $('#searchBtn').click(function() {
                table.draw();
            });
        });
    </script>
@endsection
