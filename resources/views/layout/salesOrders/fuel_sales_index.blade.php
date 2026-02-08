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
    function formatNumber(d, suffix = '') {
        return (d !== null && d !== undefined ? parseFloat(d).toFixed(2) : '0.00') + suffix;
    }

 $(document).ready(function() {
    console.log('DataTable initializing...');
    
    let table = $('#fuelSalesTable').DataTable({
        processing: true,
        serverSide: true,
        lengthMenu: [10, 25, 50],
        ajax: {
            url: "{{ route('fuel.sales.index') }}",
            type: 'GET',
            data: function(d) {
                d.product_name = $('#product_name').val();
                d.transaction_id = $('#transaction_id').val();
                d.salesman = $('#salesman').val();
                d.shift = $('#shift').val();
                d.startDate = $('#startDate').val();
                d.endDate = $('#endDate').val();
            },
            dataSrc: function (response) {
                console.log('AJAX Response:', response);
                return response.data;
            },
            error: function(xhr, error, thrown) {
                console.error("AJAX Error:", xhr);
                console.error("Error:", error);
                console.error("Thrown:", thrown);
            }
        },
        columns: [
            { data: 'id', name: 'id', render: d => '#' + d },
            { data: 'date', name: 'date' },
            { data: 'salesman', name: 'salesman' },
            { data: 'shift', name: 'shift', render: d => d ? d.charAt(0).toUpperCase() + d.slice(1) : 'N/A' },
            // Petrol columns
            { 
                data: 'product_transactions', 
                name: 'petrol_total',
                render: function(data, type, row) {
                    console.log('Petrol data:', data);
                    const petrol = Array.isArray(data) ? data.find(p => p.product_name && p.product_name.toLowerCase().includes('petrol')) : null;
                    return petrol ? formatNumber(petrol.total_liters, ' L') : formatNumber(0, ' L');
                }
            },
            { 
                data: 'product_transactions', 
                name: 'petrol_cash',
                render: function(data, type, row) {
                    const petrol = Array.isArray(data) ? data.find(p => p.product_name && p.product_name.toLowerCase().includes('petrol')) : null;
                    return petrol ? formatNumber(petrol.cash_liters, ' L') : formatNumber(0, ' L');
                }
            },
            { 
                data: 'product_transactions', 
                name: 'petrol_credit',
                render: function(data, type, row) {
                    const petrol = Array.isArray(data) ? data.find(p => p.product_name && p.product_name.toLowerCase().includes('petrol')) : null;
                    return petrol ? formatNumber(petrol.credit_liters, ' L') : formatNumber(0, ' L');
                }
            },
            // Diesel columns
            { 
                data: 'product_transactions', 
                name: 'diesel_total',
                render: function(data, type, row) {
                    const diesel = Array.isArray(data) ? data.find(p => p.product_name && p.product_name.toLowerCase().includes('diesel')) : null;
                    return diesel ? formatNumber(diesel.total_liters, ' L') : formatNumber(0, ' L');
                }
            },
            { 
                data: 'product_transactions', 
                name: 'diesel_cash',
                render: function(data, type, row) {
                    const diesel = Array.isArray(data) ? data.find(p => p.product_name && p.product_name.toLowerCase().includes('diesel')) : null;
                    return diesel ? formatNumber(diesel.cash_liters, ' L') : formatNumber(0, ' L');
                }
            },
            { 
                data: 'product_transactions', 
                name: 'diesel_credit',
                render: function(data, type, row) {
                    const diesel = Array.isArray(data) ? data.find(p => p.product_name && p.product_name.toLowerCase().includes('diesel')) : null;
                    return diesel ? formatNumber(diesel.credit_liters, ' L') : formatNumber(0, ' L');
                }
            },
            // Other columns
            { data: 'balance', name: 'balance', render: d => formatNumber(d) },
              { 
            data: 'id',
            name: 'actions',
            orderable: false,
            searchable: false,
            render: function(d, type, row) {
                  // Use the correct route URL
        const printUrl = "{{ route('fuel.sales.print', ':id') }}".replace(':id', d);
                return `
                   <ul class="action-list list-unstyled d-flex gap-2 mb-0">
                        <li>
                            <a href="${printUrl}" target="_blank" class="text-success print-sheet mx-4" data-id="${d}" title="Print Sheet">
                              <i class="fas fa-print"></i>
                            </a>
                          </li>
                      
                      <li class='mx-4'>
                        <a href="javascript:void(0);" class="text-primary view-details" data-id="${d}" title="View Details">
                          <i class="fas fa-eye"></i>
                        </a>
                      </li>
                    
                      <li>
                        <a href="javascript:void(0);" class="text-danger delete-sale" data-id="${d}" title="Delete Sale">
                          <i class="fas fa-trash"></i>
                        </a>
                      </li>
                    </ul>
                `;
            }
        }
        ],
        order: [[0, 'desc']],
        createdRow: function(row, data, dataIndex) {
            if (parseFloat(data.balance) > 0) {
                $(row).addClass('table-warning');
            }
        },
        language: {
            emptyTable: 'No fuel sales data available',
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
        },
        // Important: Initialize dropdowns after table draw
        drawCallback: function(settings) {
            // Re-initialize Bootstrap dropdowns for the table
            $('#fuelSalesTable .dropdown-toggle').dropdown();
        }
    });
    
    // Initialize dropdowns on page load
    $('.dropdown-toggle').dropdown();
    
    $('#searchBtn').click(function() {
        console.log('Search button clicked');
        table.draw();
    });
    
    // Add error handling for the table
    table.on('error.dt', function(e, settings, techNote, message) {
        console.error('DataTables error: ', message);
        alert('Error loading data. Please check console for details.');
    });
    
    // **FIXED: Proper dropdown toggle handling for dynamically generated content**
    $(document).on('click', '.dropdown-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close other open dropdowns
        $('.dropdown-toggle').not(this).each(function() {
            $(this).dropdown('hide');
        });
        
        // Toggle current dropdown
        $(this).dropdown('toggle');
    });
    
    // Close dropdowns when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.btn-group').length) {
            $('.dropdown-menu').removeClass('show');
            $('.dropdown-toggle').attr('aria-expanded', 'false');
        }
    });
    
    // View details handler
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close the dropdown
        $(this).closest('.dropdown-menu').removeClass('show');
        $(this).closest('.btn-group').find('.dropdown-toggle').attr('aria-expanded', 'false');
        
        const saleId = $(this).data('id');
        
        // Find the row data
        const tr = $(this).closest('tr');
        const rowData = table.row(tr).data();
        
        if (rowData) {
            // Populate modal with data
            $('#modalTransactionId').text(rowData.id);
            $('#modalDate').text(rowData.date);
            $('#modalSalesman').text(rowData.salesman);
            $('#modalShift').text(rowData.shift.charAt(0).toUpperCase() + rowData.shift.slice(1));
            $('#modalCreatedAt').text(rowData.created_at);
            $('#modalDiscount').text(parseFloat(rowData.discount || 0).toFixed(2));
            $('#modalNetTotal').text(parseFloat(rowData.net_total || 0).toFixed(2));
            $('#modalCashOnHand').text(parseFloat(rowData.cash_on_hand || 0).toFixed(2));
            $('#modalBalance').text(parseFloat(rowData.balance || 0).toFixed(2));
            
            // Build transactions HTML
            let transactionsHtml = '';
            if (rowData.product_transactions && Array.isArray(rowData.product_transactions)) {
                rowData.product_transactions.forEach(product => {
                    transactionsHtml += `
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">${product.product_name || 'Unknown Product'}</h6>
                                <small>Total: ${product.total_liters || 0}L (Cash: ${product.cash_liters || 0}L, Credit: ${product.credit_liters || 0}L) - ${parseFloat(product.total_amount || 0).toFixed(2)}</small>
                            </div>
                            <div class="card-body p-2">
                    `;
                    
                    if (product.transactions && Array.isArray(product.transactions)) {
                        product.transactions.forEach(transaction => {
                            const transactionClass = transaction.type === 'cash' ? 'cash-transaction' : 'credit-transaction';
                            transactionsHtml += `
                                <div class="p-2 mb-2 ${transactionClass}">
                                    <div class="d-flex justify-content-between">
                                        <strong>${(transaction.type || '').toUpperCase()} Sale</strong>
                                        <span>${parseFloat(transaction.liters || 0).toFixed(2)}L × ${parseFloat(transaction.rate || 0).toFixed(3)} = ${parseFloat(transaction.total || 0).toFixed(2)}</span>
                                    </div>
                            `;
                            
                            if (transaction.type === 'cash') {
                                transactionsHtml += `
                                    <div class="small">
                                        Readings: ${transaction.previous_reading || 0} → ${transaction.current_reading || 0}
                                        ${transaction.dphase ? '| Phase: ' + transaction.dphase : ''}
                                    </div>
                                `;
                            } else {
                                transactionsHtml += `
                                    <div class="small">
                                        Customer: ${transaction.customer || 'Unknown'}
                                        ${transaction.description ? '| ' + transaction.description : ''}
                                    </div>
                                `;
                            }
                            
                            transactionsHtml += `</div>`;
                        });
                    } else {
                        transactionsHtml += `<p class="text-muted">No transaction details available</p>`;
                    }
                    
                    transactionsHtml += `</div></div>`;
                });
            } else {
                transactionsHtml = '<p class="text-muted">No product transactions available</p>';
            }
            
            $('#modalTransactions').html(transactionsHtml);
            $('#viewDetailsModal').modal('show');
        }
    });

    // Delete sale handler
    $(document).on('click', '.delete-sale', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close the dropdown
        $(this).closest('.dropdown-menu').removeClass('show');
        $(this).closest('.btn-group').find('.dropdown-toggle').attr('aria-expanded', 'false');
        
        const saleId = $(this).data('id');
        $('#deleteSaleId').val(saleId);
        $('#deleteConfirmationModal').modal('show');
    });

    // Confirm delete handler
    $('#confirmDeleteBtn').click(function() {
        const saleId = $('#deleteSaleId').val();
        
        $.ajax({
            url: "{{ route('fuel.sales.destroy', '') }}/" + saleId,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showToast('success', response.message);
                    // Reload the table
                    table.draw();
                } else {
                    showToast('error', response.message || 'Error deleting sale');
                }
                $('#deleteConfirmationModal').modal('hide');
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || 'Error deleting sale';
                showToast('error', errorMessage);
                $('#deleteConfirmationModal').modal('hide');
            }
        });
    });

    // Toast notification function
    function showToast(type, message) {
        // Remove existing toasts
        $('.toast-container').remove();
        
        const toastHtml = `
            <div class="toast-container">
                <div class="toast-message ${type}">
                    <div class="toast-icon">
                        <i class="icon-${type === 'success' ? 'checkmark fas fa-check-circle' : 'error fa fa-exclamation-circle'}"></i>
                    </div>
                    <div class="toast-content">
                        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong>
                        <p>${message}</p>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(toastHtml);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            $('.toast-container').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Test if DataTable is working
    setTimeout(function() {
        if (table.data().count() === 0) {
            console.log('No data in table. Checking AJAX request...');
            // Force a redraw to see if data loads
            table.ajax.reload();
        }
    }, 3000);
});
    </script>
@endsection