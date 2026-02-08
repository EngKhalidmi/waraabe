@extends ('admin.admin_master')
@section('title', 'Saacid - Purchases ')
@section('admin')

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

<style>
    #customerDropdown .dropdown-item:hover {
        background-color: #f1f1f1;
        cursor: pointer;
    }

    #customerDropdown .dropdown-item {
        padding: 10px;
    }
    
    #productTable .search-row td {
        padding: 8px;
        vertical-align: middle;
    }
    
    #productTable .search-row input {
        width: 100%;
    }
    
    #productDropdown {
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .product-row:hover {
        background-color: #f5f5f5;
        cursor: pointer;
    }
</style>

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Create Purchase POS</h4>
<h6>Purchase & Payment Transaction </h6>
</div>
</div>
<div class="card">
<div class="card-body">
<form id="PurchaseForm" method="POST" action="{{ route('products.store') }}">
@csrf
<div class="row">
<div class="col-lg-6 col-sm-6 col-12">
<div class="form-group">
<label>Supplier</label>
<div class="row">
<div class="col-lg-10 col-sm-10 col-10">
<input type="text" name="customer_name" id="customerSearch" placeholder="Search Supplier" autocomplete="off" autocorrect="off" spellcheck="false">
<div id="customerDropdown" class="dropdown-menu show" style="display: none; position: absolute; width: 39%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);"></div>
</div>
<input type="hidden" name="customerID" id="customerID" required>
<div class="col-lg-2 col-sm-2 col-2 ps-0">
    <div class="add-icon">
        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addSupplierModel">
            <img src="{{asset('/assets/img/icons/plus1.svg')}}" alt="img">
        </a>
    </div>
</div>
</div>
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<label>Due Date</label>
<div class="input-groupicon">
<input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}">
</div>
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<label>Select Warehouse</label>
<select name="depID" id="depID" class="form-control select" required>
    <option value="" selected disabled>Select Department</option>
    @foreach($deps as $department)
        <option value="{{$department->id }}">{{ $department->name }}</option>
    @endforeach
</select>
</div>
</div>
</div>

<div class="row">
    <div class="table-responsive">
        <table class="table" id="productTable">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Product Info</th>
                    <th>QTY</th>
                    <th>Unit</th>
                    <th>Cost Price($)</th>
                    <th>Selling Price($) <span class="text-muted">(Optional)</span></th>
                    <th>Total Cost($)</th>
                    <th>Total Selling($)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                <!-- Search row -->
                <tr class="search-row">
                    <td colspan="9">
                        <input type="text" id="productSearch" class="form-control" placeholder="Search and add products..." autocomplete="off">
                        <div id="productDropdown" class="dropdown-menu" style="display:none;"></div>
                    </td>
                </tr>
                <!-- Products will be added here -->
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>SubTotal</label>
    <input type="text" name="subtotal" id="subtotal" readonly>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Discount</label>
    <input type="text" name="discount" class="form-control" id="discount">
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Net Price</label>
    <input type="text" name="net_price" id="net_price" readonly>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Additional Cost</label>
    <input type="text" name="add_cost" class="form-control" id="add_cost">
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Paid Amount</label>
    <input type="text" name="paid_amount" class="form-control" id="paid_amount">
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Balance</label>
    <input type="text" name="balance" id="balance" readonly>
    </div>
    </div> 
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Payment Method</label>
    <select name="payment_method" id="payment_method" class="select">
        <option value="" selected disabled>Select Payment Method</option>
        <option value="Cash On Hand">Cash On Hand</option>
        <option value="ZAAD">ZAAD</option>
        <option value="MERCHANT">MERCHANT</option>
        <option value="Darasalam Bank">Darasalam Bank</option>
        <option value="E-Dahab">E-Dahab</option>
        <option value="Bank Account">Bank Account</option>
    </select>
    </div>
    </div>
    <div class="col-lg-12">
        <button type="submit" id="Create" class="btn btn-primary me-2"><i class="fas fa-save"></i> Create Purchase</button>
        <a href="#" class="btn btn-cancel">Cancel</a> 
    </div>
</div>
</form>
</div>
</div>
</div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register New Supplier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickSupplierForm" method="POST" action="{{ route('supplier.quickStore') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Supplier Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add New Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="newProductForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" id="newProductName" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" step="0.01" min="0.01" id="newProductQuantity" class="form-control" value="1">
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <select id="newProductUnit" class="form-control">
                            <option value="Pcs">Pcs</option>
                            <option value="Meter">Meter</option>
                            <option value="Leter">Leter</option>
                            <option value="War">War</option>
                            <option value="Pair">Pair</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cost Price</label>
                        <input type="number" step="0.01" min="0" id="newProductCostPrice" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Selling Price <span class="text-muted">(Optional)</span></label>
                        <input type="number" step="0.01" min="0" id="newProductSellingPrice" class="form-control" placeholder="Leave empty if not applicable">
                    </div>
                    <div class="form-group">
                        <label>Total Cost</label>
                        <input type="text" id="newProductTotalCost" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Total Selling</label>
                        <input type="text" id="newProductTotalSelling" class="form-control" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="saveNewProduct" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    let productCount = 0;
    let newProductName = '';
    
    // Handle quick supplier form submission
    $('#quickSupplierForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#addSupplierModel').modal('hide');
                $('#customerSearch').val(response.supplier.name);
                $('#customerID').val(response.supplier.id);
                $('#quickSupplierForm')[0].reset();
                toastr.success('Supplier added successfully!');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        toastr.error(value[0]);
                    });
                } else {
                    toastr.error('An error occurred. Please try again.');
                }
            }
        });
    });
    
    // Form submission validation
    $('#PurchaseForm').submit(function(e) {
        if (productCount === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please add at least one product before submitting',
            });
        }
    });
    
    // Product search functionality
    $('#productSearch').on('input', function() {
        const query = $(this).val();
        newProductName = query; // Store the searched name for new products
        
        if (query.length >= 2) {
            $('#productDropdown').html(`
                <div class="dropdown-item text-center">
                    <i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...
                </div>
            `).show();
            
            axios.get(`{{ route('products.searchProduct') }}?query=${query}`)
                .then(response => {
                    $('#productDropdown').empty();
                    
                    if (response.data.length > 0) {
                        response.data.forEach(product => {
                            const productOption = $(`
                                <a class="dropdown-item product-row" data-id="${product.id}" 
                                   data-name="${product.name}" 
                                   data-unit="${product.unit}" 
                                   data-selling_price="${product.selling_price}"
                                   data-actual_price="${product.actual_price}">
                                    ${product.name} (${product.unit})
                                </a>
                            `);
                            
                            $('#productDropdown').append(productOption);
                        });
                    } else {
                        const newProductOption = $(`
                            <a class="dropdown-item new-product-row" href="#">
                                <i class="fas fa-plus-circle"></i> Add New Product: "${query}"
                            </a>
                        `);
                        $('#productDropdown').append(newProductOption);
                    }
                })
                .catch(error => {
                    console.error('Error fetching products:', error);
                    $('#productDropdown').hide();
                });
        } else {
            $('#productDropdown').hide();
        }
    });
    
    // Handle existing product selection from dropdown
    $(document).on('click', '.product-row', function() {
        const product = {
            id: $(this).data('id'),
            name: $(this).data('name'),
            unit: $(this).data('unit'),
            selling_price: $(this).data('selling_price'),
            actual_price: $(this).data('actual_price')
        };
        
        // Check if product already exists in table
        if ($(`input[value="${product.id}"][name^="products["]`).length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'This product is already in the list',
            });
            return;
        }
        
        // Show modal with product details
        $('#newProductName').val(product.name);
        $('#newProductUnit').val(product.unit);
        $('#newProductCostPrice').val(product.actual_price);
        $('#newProductSellingPrice').val(product.selling_price);
        
        // Calculate totals
        calculateNewProductTotals();
        
        $('#addProductModal .modal-title').text('Add Existing Product');
        $('#addProductModal').modal('show');
    });
    
    // Handle new product creation from dropdown
    $(document).on('click', '.new-product-row', function(e) {
        e.preventDefault();
        $('#newProductName').val(newProductName);
        $('#newProductUnit').val('Pcs');
        $('#newProductCostPrice').val('');
        $('#newProductSellingPrice').val('');
        $('#newProductTotalCost').val('');
        $('#newProductTotalSelling').val('');
        
        $('#addProductModal .modal-title').text('Add New Product');
        $('#addProductModal').modal('show');
    });
    
    // Calculate totals for new product
    $('#newProductQuantity, #newProductCostPrice, #newProductSellingPrice').on('input', function() {
        calculateNewProductTotals();
    });
    
    function calculateNewProductTotals() {
        const quantity = parseFloat($('#newProductQuantity').val()) || 0;
        const costPrice = parseFloat($('#newProductCostPrice').val()) || 0;
        const sellingPrice = parseFloat($('#newProductSellingPrice').val()) || 0;
        
        $('#newProductTotalCost').val((quantity * costPrice).toFixed(2));
        
        // Handle null selling price
        if (sellingPrice) {
            $('#newProductTotalSelling').val((quantity * sellingPrice).toFixed(2));
        } else {
            $('#newProductTotalSelling').val('0.00');
        }
    }
    
    // Save new product to table
    $('#saveNewProduct').on('click', function() {
        const productName = $('#newProductName').val();
        const quantity = parseFloat($('#newProductQuantity').val()) || 0;
        const unit = $('#newProductUnit').val();
        const costPrice = parseFloat($('#newProductCostPrice').val()) || 0;
        let sellingPrice = parseFloat($('#newProductSellingPrice').val());
        const totalCost = parseFloat($('#newProductTotalCost').val()) || 0;
        
        // Handle null selling price
        if (isNaN(sellingPrice)) {
            sellingPrice = null;
        }
        
        // Calculate total selling (0 if selling price is null)
        const totalSelling = sellingPrice !== null ? (quantity * sellingPrice) : 0;
        $('#newProductTotalSelling').val(totalSelling.toFixed(2));

        if (!productName) {
            toastr.error('Product name is required');
            return;
        }
        
        if (quantity <= 0) {
            toastr.error('Please enter a valid quantity');
            return;
        }
        
        if (costPrice <= 0) {
            toastr.error('Please enter a valid cost price');
            return;
        }
        
        // Create a new row for the product
        const newRow = $(`
            <tr>
                <td>
                    <input type="hidden" name="products[${productCount}][name]" value="${productName}">
                    ${productName}
                </td>
                <td>
                    <input type="hidden" name="products[${productCount}][proID]" value="">
                    New Product
                </td>
                <td>
                    <input type="hidden" name="products[${productCount}][quantity]" value="${quantity}">
                    ${quantity}
                </td>
                <td>
                    <input type="hidden" name="products[${productCount}][unit]" value="${unit}">
                    ${unit}
                </td>
                <td>
                    <input type="hidden" name="products[${productCount}][actual_price]" value="${costPrice}">
                    ${costPrice.toFixed(2)}
                </td>
                <td>
                    <input type="hidden" name="products[${productCount}][price]" value="${sellingPrice !== null ? sellingPrice : ''}">
                    ${sellingPrice !== null ? sellingPrice.toFixed(2) : '—'}
                </td>
                <td>
                    <input type="hidden" name="products[${productCount}][total_actual_price]" value="${totalCost}">
                    ${totalCost.toFixed(2)}
                </td>
                <td>
                    <input type="hidden" name="products[${productCount}][total_selling_price]" value="${totalSelling}">
                    ${totalSelling.toFixed(2)}
                </td>
                <td>
                    <a href="javascript:void(0);" class="delete-set"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
        `);
        
        // Insert the new row before the search row
        newRow.insertBefore($('.search-row'));
        productCount++;
        
        // Clear search and hide dropdown
        $('#productSearch').val('');
        $('#productDropdown').hide();
        $('#addProductModal').modal('hide');
        
        // Update subtotal
        updateSubtotal();
    });
    
    // Customer search functionality
    $('#customerSearch').on('input', function() {
        const query = $(this).val();
        
        if (query.length >= 2) {
            $('#customerDropdown').html(`
                <div class="dropdown-item text-center">
                    <i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...
                </div>
            `).show();
            
            axios.get(`{{ route('products.searchSupplier') }}?query=${query}`)
                .then(response => {
                    $('#customerDropdown').empty();
                    
                    if (response.data.length > 0) {
                        response.data.forEach(customer => {
                            const customerOption = $(`
                                <a class="dropdown-item customer-row" 
                                   data-id="${customer.id}" 
                                   data-name="${customer.name}">
                                    ${customer.name}
                                </a>
                            `);
                            
                            $('#customerDropdown').append(customerOption);
                        });
                    } else {
                        $('#customerDropdown').html(`
                            <div class="dropdown-item text-center text-muted">
                                No results found
                            </div>
                        `);
                    }
                })
                .catch(error => {
                    console.error('Error fetching customers:', error);
                    $('#customerDropdown').hide();
                });
        } else {
            $('#customerDropdown').hide();
        }
    });
    
    // Handle customer selection from dropdown
    $(document).on('click', '.customer-row', function() {
        $('#customerSearch').val($(this).data('name'));
        $('#customerID').val($(this).data('id'));
        $('#customerDropdown').hide();
    });
    
    // Delete product from table
    $(document).on('click', '.delete-set', function() {
        $(this).closest('tr').remove();
        productCount--;
        reindexProductRows();
        updateSubtotal();
    });
    
    // Update calculations when discount changes
    $('#discount').on('input', updateNetPrice);
    
    // Update calculations when additional cost changes
    $('#add_cost').on('input', updateBalance);
    
    // Update balance when paid amount changes
    $('#paid_amount').on('input', updateBalance);
    
    // Update subtotal calculation
    function updateSubtotal() {
        let subtotal = 0;
        $('#productTableBody tr:not(.search-row)').each(function() {
            subtotal += parseFloat($(this).find('td:eq(6)').text()) || 0;
        });
        
        if (subtotal < 0) subtotal = 0;
        
        $('#subtotal').val(subtotal.toFixed(2));
        updateNetPrice();
    }
    
    // Update net price calculation
    function updateNetPrice() {
        const subtotal = parseFloat($('#subtotal').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        const netPrice = subtotal - discount;
        
        $('#net_price').val(netPrice.toFixed(2));
        updateBalance();
    }
    
    // Update balance calculation
    function updateBalance() {
        const netPrice = parseFloat($('#net_price').val()) || 0;
        const paidAmount = parseFloat($('#paid_amount').val()) || 0;
        const add_cost = parseFloat($('#add_cost').val()) || 0;
        const balance = (netPrice + add_cost) - paidAmount;
        
        $('#balance').val(balance.toFixed(2));
    }
    
    // Reindex product rows after deletion
    function reindexProductRows() {
        let newIndex = 0;
        $('#productTableBody tr:not(.search-row)').each(function() {
            $(this).find('input').each(function() {
                const name = $(this).attr('name').replace(/products\[\d+\]/, `products[${newIndex}]`);
                $(this).attr('name', name);
            });
            newIndex++;
        });
        productCount = newIndex;
    }
});
</script>
@endsection