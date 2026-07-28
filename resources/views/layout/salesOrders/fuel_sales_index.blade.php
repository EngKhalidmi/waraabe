@extends ('admin.admin_master')
@section('title', 'Saacid - Fuel Sales')
@section('admin')
    <style>
        .product-badge {
            background-color: #f8f9fa !important;
            color: #495057 !important;
            border: 1px solid #dee2e6 !important;
        }
        
        .product-badge:hover {
            background-color: #e9ecef !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Ensure black color for liters column */
        #fuelSalesTable td:nth-child(6) {
            color: black;
            font-weight: bold;
        }
        
        /* Actions dropdown styling */
        .actions-dropdown .btn-group {
            display: flex;
        }
        
        .actions-dropdown .dropdown-toggle::after {
            margin-left: 0.255em;
        }
        
        .actions-dropdown .btn {
    padding: 0.25rem 0.5rem;
    font-size: 1.2rem; /* Adjust icon size */
    line-height: 1;
}

.actions-dropdown .btn i {
    vertical-align: middle;
}

.actions-dropdown .btn:hover {
    color: #007bff !important; /* Hover effect for the dots icon */
}

.actions-dropdown .dropdown-menu {
    min-width: 120px;
    position: absolute;
    z-index: 1050; /* Ensure dropdown appears above other elements */
    top: 100%;
    left: auto;
    right: 0; /* Align dropdown to the right of the icon */
}

.actions-dropdown .dropdown-item {
    padding: 0.25rem 1rem;
    font-size: 0.875rem;
    cursor: pointer;
}

.actions-dropdown .dropdown-item.text-danger:hover {
    background-color: #f8d7da;
}

/* Ensure dropdown works in DataTable */
.dataTables_wrapper .dropdown-menu {
    z-index: 1050 !important;
}

table.dataTable tbody td {
    position: relative;
}
        
     
        
        

        /* Ensure dropdown works in DataTable */
        .dataTables_wrapper .dropdown-menu {
            z-index: 1050 !important;
        }

        table.dataTable tbody td {
            position: relative;
        }
    </style>
    
    
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Fuel Sales Transaction List</h4>
                    <h6>Manage your Fuel Sales Transactions</h6>
                </div>
                <div class="page-btn">
                    <a href="{{ route('fuel.sales.create') }}" class="btn btn-added">Create New Fuel Sales</a>
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
                                        <input type="text" id="product_name" placeholder="Filter By Product">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="text" id="transaction_id" placeholder="Filter By Transaction ID">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="text" id="salesman" placeholder="Filter By Salesman">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <select class="form-control" id="shift">
                                            <option value="">Select Shift</option>
                                            <option value="morning">Morning</option>
                                            <option value="evening">Evening</option>
                                            <option value="night">Night</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="date" class="form-control" id="startDate">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="form-group">
                                        <input type="date" class="form-control" id="endDate">
                                    </div>
                                </div>
                                <div class="col-lg-1 col-sm-6 col-12 ms-auto">
                                    <div class="form-group">
                                        <button type="buttom" class="btn btn-filters ms-auto" id="searchBtn"><img
                                                src="{{ asset('assets/img/icons/search-whites.svg') }}" alt="img"></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                   <div class="table-responsive">
                        <table class="table" id="fuelSalesTable">
                            <thead>
                                <tr class="bg-primary">
                                    <th class="text-white">ID</th>
                                    <th class="text-white">Date</th>
                                    <th class="text-white">Salesman</th>
                                    <th class="text-white">Shift</th>
                    
                                    <th class="text-white">Petrol (Total)</th>
                                    <th class="text-white">Petrol (Cash)</th>
                                    <th class="text-white">Petrol (Credit)</th>
                    
                                    <th class="text-white">Diesel (Total)</th>
                                    <th class="text-white">Diesel (Cash)</th>
                                    <th class="text-white">Diesel (Credit)</th>
                    
                                    <th class="text-white">Balance</th>
                                    <th class="text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDetailsModalLabel">Fuel Sale Details - Transaction #<span id="modalTransactionId"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date:</strong> <span id="modalDate"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Salesman:</strong> <span id="modalSalesman"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Shift:</strong> <span id="modalShift"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Created At:</strong> <span id="modalCreatedAt"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Discount:</strong> <span id="modalDiscount"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Net Total:</strong> <span id="modalNetTotal"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Balance:</strong> <span id="modalBalance"></span>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2">Product Transactions</h6>
                    <div class="transaction-details" id="modalTransactions">
                        <!-- Transactions will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this fuel sale transaction?</p>
                    <p class="text-danger"><strong>Warning:</strong> This action will restore all product quantities, customer balances, and salesman balances affected by this transaction. This action cannot be undone.</p>
                    <input type="hidden" id="deleteSaleId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.StoreManagementFuelSalesModule && typeof window.StoreManagementFuelSalesModule.boot === 'function') {
                window.StoreManagementFuelSalesModule.boot();
            }
        });
    </script>
@endsection
