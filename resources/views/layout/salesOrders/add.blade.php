@extends ('admin.admin_master')
@section('title', 'Saacid - Sales Orders ')
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

    @if (session('invoice_url'))
        <script>
            window.open("{{ session('invoice_url') }}", '_blank');
        </script>
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
                    <h4>Create Sales POS</h4>
                    <h6>Sales & Payment Transaction </h6>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form id="salesForm" method="POST" action="{{ route('sales.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <div class="row">
                                        <div class="col-lg-6 col-sm-10 col-10">
                                            <input type="text" name="customer_name" id="customerSearch"
                                                placeholder="Search Customer Name" autocomplete="off" autocorrect="off"
                                                spellcheck="false">
                                            <div id="customerDropdown" class="dropdown-menu show"
                                                style="display: none; position: absolute; width: 39%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                            </div>
                                        </div>
                                        <input type="hidden" name="customerID" id="customerID">
                                        <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                            <div class="add-icon">
                                                <a href="javascript:void(0);" data-toggle="modal"
                                                    data-target="#addCustomerModal">
                                                    <img src="{{ asset('/assets/img/icons/plus1.svg') }}" alt="img">
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
                                        <input type="date" name="due_date" id="due_date" value="{{ date('Y-m-d') }}"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Select Branch No.</label>
                                    <select name="depID" id="depID" class="form-control select">
                                        @foreach ($deps as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
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
                                            <th>QTY</th>
                                            <th>Unit</th>
                                            <th>Unit Price($)</th>
                                            <th>Total Price($)</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productTableBody">
                                        <!-- Search row -->
                                        <tr class="search-row">
                                            <td colspan="6">
                                                <input type="text" id="productSearch" class="form-control"
                                                    placeholder="Search and add products..." autocomplete="off">
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
                                    <input type="number" step="0.001" name="discount" value="0.00" class="form-control"
                                        id="discount">
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Net Price</label>
                                    <input type="text" name="net_price" id="net_price" readonly>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Paid Amount</label>
                                    <input type="number" step="0.001" name="paid_amount" class="form-control"
                                        id="paid_amount">
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Balance</label>
                                    <input type="text" name="balance" id="balance" readonly>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <select name="paymentMethod" id="paymentMethod" class="select">
                                        <option value="ZAAD Services">ZAAD Services</option>
                                        <option value="Cash On Hand">Cash On Hand</option>
                                        <option value="E-Dahab">E-Dahab</option>
                                        <option value="Darasalam Bank">Darasalam Bank</option>
                                        <option value="Bank Account">Bank Account</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <label>Note</label>
                                <textarea class="form-control" cols="10" rows="3" name="note"></textarea>
                            </div>
                        </div>

                        <!-- Sales & Payment Summery Info -->
                        <div class="row">
                            <div class="col-lg-12 float-md-right">
                                <div class="total-order">
                                    <ul>
                                        <li>
                                            <h4>SubTotal</h4>
                                            <h5 id="summary_subtotal">$ 0.00</h5>
                                        </li>
                                        <li>
                                            <h4>Discount</h4>
                                            <h5 id="summary_discount">$ 0.00</h5>
                                        </li>
                                        <li class="total">
                                            <h4>Net Total</h4>
                                            <h5 id="summary_net_total">$ 0.00</h5>
                                        </li>
                                        <li>
                                            <h4 class="total">Paid Amount</h4>
                                            <h5 id="summary_paid_amount">$ 0.00</h5>
                                        </li>
                                        <li>
                                            <h4 class="total">Balance</h4>
                                            <h5 id="summary_balance">$ 0.00</h5>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <button type="submit" id="Create" class="btn btn-primary me-2"><i
                                        class="fas fa-save"></i> Save & Print </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Register New Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="quickCustomerForm" method="POST" action="{{ route('customer.quickStore') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Customer</button>
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
            let client = '';
            let productCount = 0;

            // Handle quick customer form submission
            $('#quickCustomerForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#addCustomerModal').modal('hide');
                        $('#customerSearch').val(response.customer.customer_name);
                        $('#customerID').val(response.customer.id);
                        client = response.serial;
                        $('#quickCustomerForm')[0].reset();
                        toastr.success('Customer added successfully!');
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
            $('#salesForm').submit(function(e) {
                e.preventDefault();

                console.log("Initial total_price values:");
                $('input[name*="[total_price]"]').each(function() {
                    console.log($(this).attr('name'), $(this).val());
                });


                // Update all total_price fields before submission
                $('#productTableBody tr:not(.search-row)').each(function() {
                    const row = $(this);
                    const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                    const price = parseFloat(row.find('.price-input').val()) || 0;
                    const totalPrice = (quantity * price).toFixed(2);
                    row.find('input[name*="[total_price]"]').val(totalPrice);
                });
                // Reset previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                let isValid = true;
                const errors = [];

                // Validate customer
                if (!$('#customerID').val()) {
                    $('#customerSearch').addClass('is-invalid')
                        .after('<div class="invalid-feedback">Please select a customer</div>');
                    isValid = false;
                    errors.push('Please select a customer');
                }

                // Validate products
                if (productCount === 0) {
                    $('#productSearch').addClass('is-invalid')
                        .after('<div class="invalid-feedback">Please add at least one product</div>');
                    isValid = false;
                    errors.push('Please add at least one product');
                }

                // Validate quantities

                $('#productTableBody tr:not(.search-row)').each(function() {
                    const quantityInput = $(this).find('.quantity-input');
                    const quantity = parseFloat(quantityInput.val());
                    const maxQuantity = parseFloat(quantityInput.attr('max'));

                    if (isNaN(quantity)) { // <-- This was missing the closing parenthesis
                        quantityInput.addClass('is-invalid')
                            .after(
                                '<div class="invalid-feedback">Please enter a valid quantity</div>'
                            );
                        isValid = false;
                        errors.push('Invalid quantity entered');
                    } else if (quantity <= 0) {
                        quantityInput.addClass('is-invalid')
                            .after(
                                '<div class="invalid-feedback">Quantity must be greater than 0</div>'
                            );
                        isValid = false;
                        errors.push('Quantity must be greater than 0');
                    } else if (quantity > maxQuantity) {
                        quantityInput.addClass('is-invalid')
                            .after(
                                `<div class="invalid-feedback">Quantity exceeds available stock (${maxQuantity})</div>`
                            );
                        isValid = false;
                        errors.push('Quantity exceeds available stock');
                    }
                });
                // Debug: Show updated values
                console.log("Updated total_price values:");
                $('input[name*="[total_price]"]').each(function() {
                    console.log($(this).attr('name'), $(this).val());
                });


                // Validate payment
                const paidAmount = parseFloat($('#paid_amount').val()) || 0;
                const netPrice = parseFloat($('#net_price').val()) || 0;

                if (isNaN(paidAmount)) {
                    $('#paid_amount').addClass('is-invalid')
                        .after('<div class="invalid-feedback">Please enter a valid amount</div>');
                    isValid = false;
                    errors.push('Invalid payment amount');
                } else if (paidAmount < 0) {
                    $('#paid_amount').addClass('is-invalid')
                        .after('<div class="invalid-feedback">Payment cannot be negative</div>');
                    isValid = false;
                    errors.push('Payment cannot be negative');
                } else if (client === 'oil Cash Seles' && paidAmount < netPrice) {
                    $('#paid_amount').addClass('is-invalid')
                        .after('<div class="invalid-feedback">Cash sales must be paid in full</div>');
                    isValid = false;
                    errors.push('Cash sales must be paid in full');
                }

                // If validation passes, submit the form
                if (isValid) {
                    // Debug: Show all form data before submission
                    console.log("Form data:", $('#salesForm').serialize());

                    localStorage.setItem('salesFormData', JSON.stringify($('#salesForm').serializeArray()));

                    // Force DOM updates to be recognized
                    $('#salesForm').find('input, select, textarea').trigger('change');

                    this.submit();
                } else {
                    // Show error summary
                    const errorMessage = errors.length > 0 ?
                        `<ul>${errors.map(error => `<li>${error}</li>`).join('')}</ul>` :
                        'Please correct the errors in the form';

                    Swal.fire({
                        icon: 'error',
                        title: 'Form Validation Error',
                        html: errorMessage,
                        scrollbarPadding: false
                    });

                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.is-invalid').first().offset().top - 100
                    }, 500);
                }
            });


            // Restore form data if available
            const savedFormData = localStorage.getItem('salesFormData');
            if (savedFormData) {
                try {
                    const formData = JSON.parse(savedFormData);
                    formData.forEach(item => {
                        $(`[name="${item.name}"]`).val(item.value);
                    });
                    localStorage.removeItem('salesFormData');
                } catch (e) {
                    console.error('Error restoring form data:', e);
                }
            }

            // Product search functionality
            $('#productSearch').on('input', function() {
                const query = $(this).val();

                if (query.length >= 2) {
                    $('#productDropdown').html(`
                <div class="dropdown-item text-center">
                    <i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...
                </div>
            `).show();

                    axios.get(`{{ route('sales.searchProduct') }}?query=${query}`)
                        .then(response => {
                            $('#productDropdown').empty();

                            if (response.data.length > 0) {
                                response.data.forEach(product => {
                                    const productOption = $(`
                                <a class="dropdown-item product-row" data-id="${product.id}" 
                                  data-name="${product.name}" 
                                  data-unit="${product.unit}" 
                                  data-quantity="${product.quantity}" 
                                  data-selling_price="${product.selling_price}"
                                  data-actual_price="${product.actual_price}">
                                    ${product.name} (${product.unit}) - Stock: ${product.quantity}
                                </a>
                            `);

                                    $('#productDropdown').append(productOption);
                                });
                            } else {
                                $('#productDropdown').html(`
                            <div class="dropdown-item text-center text-muted">
                                No results found
                            </div>
                        `);
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

            // Handle product selection from dropdown
            $(document).on('click', '.product-row', function() {
                const product = {
                    id: $(this).data('id'),
                    name: $(this).data('name'),
                    unit: $(this).data('unit'),
                    quantity: $(this).data('quantity'),
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

                // Create a new row for the product
                const newRow = $(`
        <tr data-product-id="${product.id}">
            <td>
                <input type="hidden" name="products[${productCount}][proID]" value="${product.id}">
                ${product.name}
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" max="${product.quantity}" 
                      name="products[${productCount}][quantity]" class="form-control quantity-input" 
                      value="1" data-price="${product.selling_price}">
            </td>
            <td>
                <input type="hidden" name="products[${productCount}][unit]" value="${product.unit}">
                ${product.unit}
            </td>
            <td>
                <input type="number" step="0.01" min="0" 
                      name="products[${productCount}][price]" class="form-control price-input" 
                      value="${product.selling_price.toFixed(2)}" data-original-price="${product.selling_price}">
            </td>
            <td class="total-price">
                ${product.selling_price.toFixed(2)}
                <input type="hidden" class="total-price-input" name="products[${productCount}][total_price]" value="${product.selling_price.toFixed(2)}">
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

                    axios.get(`{{ route('sales.searchCustomer') }}?query=${query}`)
                        .then(response => {
                            $('#customerDropdown').empty();

                            if (response.data.length > 0) {
                                response.data.forEach(customer => {
                                    const customerOption = $(`
                                <a class="dropdown-item customer-row" 
                                  data-id="${customer.id}" 
                                  data-name="${customer.customer_name}" 
                                  data-serial="${customer.serial}">
                                    ${customer.customer_name}
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
                client = $(this).data('serial');
                $('#customerDropdown').hide();
            });

            // Handle price changes
            $(document).on('input', '.price-input', function() {
                const newPrice = parseFloat($(this).val()) || 0;
                const row = $(this).closest('tr');
                const quantity = parseFloat(row.find('.quantity-input').val()) || 1;
                const totalPrice = (quantity * newPrice).toFixed(2);

                // Update display and hidden input
                row.find('.total-price').text(totalPrice);
                row.find('input[name*="[total_price]"]').val(totalPrice);

                updateSubtotal();
            });

            // Handle quantity changes
            $(document).on('input', '.quantity-input', function() {
                const quantity = parseFloat($(this).val()) || 1;
                const row = $(this).closest('tr');
                const price = parseFloat(row.find('.price-input').val()) || 0;
                const totalPrice = (quantity * price).toFixed(2);

                // Update display and hidden input
                row.find('.total-price').text(totalPrice);
                row.find('input[name*="[total_price]"]').val(totalPrice);

                updateSubtotal();
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

            // Update balance when paid amount changes
            $('#paid_amount').on('blur', function() {
                updateBalance();
                let mybalance = parseFloat($('#balance').val()) || 0;

                if (!client) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sorry...',
                        text: 'Please Select a Customer!',
                    });
                    $('#paid_amount').val('');
                    $('#balance').val('');
                }

                if (mybalance < 0) {
                    $('#balance').val('');
                    Swal.fire({
                        icon: 'error',
                        title: 'Sorry...',
                        text: 'You can not enter a negative balance!',
                    });
                }

                if (client === 'Cash Sales' && mybalance > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sorry...',
                        text: `You Can't Store Cash Sales As Credit, Try An Other Customer!`,
                    });
                    $('#paid_amount').val('');
                    $('#balance').val('');
                }
            });

            // Update subtotal calculation
            function updateSubtotal() {
                let subtotal = 0;
                $('#productTableBody tr:not(.search-row)').each(function() {
                    subtotal += parseFloat($(this).find('.total-price').text()) || 0;
                });

                if (subtotal < 0) subtotal = 0;

                $('#subtotal').val(subtotal.toFixed(2));
                $('#summary_subtotal').text(`$ ${subtotal.toFixed(2)}`);
                updateNetPrice();
            }

            // Update net price calculation
            function updateNetPrice() {
                const subtotal = parseFloat($('#subtotal').val()) || 0;
                const discount = parseFloat($('#discount').val()) || 0;
                const netPrice = subtotal - discount;

                $('#net_price').val(netPrice.toFixed(2));
                $('#summary_net_total').text(`$ ${netPrice.toFixed(2)}`);
                $('#summary_discount').text(`$ ${discount.toFixed(2)}`);
                updateBalance();
            }

            // Update balance calculation
            function updateBalance() {
                const netPrice = parseFloat($('#net_price').val()) || 0;
                const paidAmount = parseFloat($('#paid_amount').val()) || 0;
                const balance = netPrice - paidAmount;

                $('#paid_amount').val(paidAmount.toFixed(2));
                $('#balance').val(balance.toFixed(2));
                $('#summary_balance').text(`$ ${balance.toFixed(2)}`);
                $('#summary_paid_amount').text(`$ ${paidAmount.toFixed(2)}`);
            }

            // Reindex product rows after deletion
            function reindexProductRows() {
                let newIndex = 0;
                $('#productTableBody tr:not(.search-row)').each(function() {
                    $(this).find('input').each(function() {
                        const name = $(this).attr('name').replace(/products\[\d+\]/,
                            `products[${newIndex}]`);
                        $(this).attr('name', name);
                    });
                    newIndex++;
                });
                productCount = newIndex;
            }

            // Initialize balance on page load
            updateBalance();
        });
    </script>
@endsection
