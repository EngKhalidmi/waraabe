@extends ('admin.admin_master')
@section('title', 'Saacid - Credit Transactions ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Salesman Payments List</h4>
                    <h6>Manage your Salesman Payments</h6>
                </div>
                <div class="page-btn">
                    <a href="{{ route('salesman_payment.add') }}" class="btn btn-added"><img src="assets/img/icons/plus.svg"
                            alt="img">Add Payment</a>
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
                                        <select name="name" id="name" class="select">
                                            <option value="">Select Salesman</option>
                                            @foreach ($salesman as $salesman)
                                                <option value="{{ $salesman->full_name }}">{{ $salesman->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="number" class="form-control" id="phone"
                                            placeholder="Filter By Phone No">
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
                        <table class="table" id="creditTable">
                            <thead>
                                <tr class="bg-primary">
                                    <th class="text-white">#</th>
                                    <th class="text-white">Name</th>
                                    <th class="text-white">Phone</th>
                                    <th class="text-white">Previous</th>
                                    <th class="text-white">Discount</th>
                                    <th class="text-white">Amount</th>
                                    <th class="text-white">Balance</th>
                                    <th class="text-white">Method</th>
                                    <th class="text-white">Date</th>
                                    <th class="text-white">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            if (!navigator.onLine && window.StoreManagementFinanceModule) {
                window.StoreManagementFinanceModule.mountListPage('salesmanPayment');
                return;
            }
            // Initialize the DataTable
            let table = $('#creditTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [10, 25, 50],
                ajax: {
                    url: "{{ route('salesman_payment') }}",
                    type: 'GET',
                    data: function(d) {
                        d.name = $('#name').val();
                        d.phone = $('#phone').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();

                    },
                    dataSrc: function(json) {
                        if (window.StoreManagementFinanceModule) {
                            window.StoreManagementFinanceModule.cacheList('salesmanPayment', json.data || []);
                        }
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
                        data: 'full_name',
                        name: 'full_name'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'pbalance',
                        name: 'pbalance'
                    },
                    {
                        data: 'discount',
                        name: 'discount'
                    },
                    {
                        data: 'paid_amount',
                        name: 'paid_amount'
                    },
                    {
                        data: 'current',
                        name: 'current'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },

                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let deleteUrl =
                                `{{ url('salesman_payment/salesman_payment') }}/${data}`;

                            return `
                            <div class="row justify-content-center">
                                <div class="col-6">
                                    <form id="deleteForm-${data}" action="${deleteUrl}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-rounded btn-sm bg-outline-light me-2" onclick="confirmDelete(${data})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>`;
                        }
                    }
                ]
            });

            // Filter search button click
            $('#searchBtn').click(function() {
                table.draw();
            });
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
@endsection
