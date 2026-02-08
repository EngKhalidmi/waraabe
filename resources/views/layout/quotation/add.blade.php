@extends ('admin.admin_master')
@section('title', 'Saacid - Create Quotation ')
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
        background-color: #f1f1f1; /* Light grey on hover */
        cursor: pointer;
    }

    #customerDropdown .dropdown-item {
        padding: 10px; /* More space for readability */
    }
</style>
<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Create Quotation</h4>
<h6>Quotation Paper </h6>
</div>
</div>
<div class="card">
<div class="card-body">
<form id="salesForm" method="POST" action="{{ route('quotationorders.store') }}">
@csrf
<div class="row">
<div class="col-lg-6 col-sm-6 col-12">
<div class="form-group">
<label>Customer Name</label>
<div class="row">
<div class="col-lg-10 col-sm-10 col-10">
<input type="text" name="customer" id="customerSearch" placeholder="Search Customer Hear">
<div id="customerDropdown" class="dropdown-menu show" style="display: none; position: absolute; width: 39%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);"></div>
</div>
<div class="col-lg-2 col-sm-2 col-2 ps-0">
<div class="add-icon">
<a href="javascript:void(0);"><img src="{{asset('/assets/img/icons/plus1.svg')}}" alt="img"></a>
</div>
</div>
</div>
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<label>Due Date</label>
<div class="input-groupicon">
<input type="date" name="due_date" id="due_date" class="form-control">
</div>
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<label>Phone No.</label>
<input type="text" name="phone" id="phone">
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<label>Product</label>
<div class="input-groupicon">
<input type="text" name="product_name" id="inventorySearch" placeholder="Search Product Hear">
<div id="productDropdown" class="dropdown-menu show" style="display: none; position: absolute; width: 100%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);"></div>
<div class="addonset">
<img src="{{asset('/assets/img/icons/product.svg')}}" alt="img">
</div>
</div>
</div>
</div>
<input type="hidden" id="proID">
<div class="col-lg-2 col-sm-6 col-12">
<div class="form-group">
<label>Quantity</label>
<div class="input-groupicon">
<input type="number" step="0.01" class="form-control" id="quantity" placeholder="Enter Quantity">
</div>
</div>
</div>
<div class="col-lg-2 col-sm-6 col-12">
<div class="form-group">
<label>Unit</label>
<div class="input-groupicon">
<input type="text" id="unit" placeholder="Unit" readonly>
</div>
</div>
</div>
<div class="col-lg-2 col-sm-6 col-12">
<div class="form-group">
<label>Selling Price</label>
<div class="input-groupicon">
<input type="text" id="selling_price" placeholder="Selling Price" readonly>
</div>
</div>
</div>
<div class="col-lg-3 col-sm-6 col-12">
<div class="form-group">
<label>Total Price</label>
<div class="row">
<div class="col-lg-10 col-sm-10 col-10">
<input type="text" id="total_price" placeholder="Total Item Price" readonly>
</div>
<div class="col-lg-2 col-sm-2 col-2 ps-0">
<div class="add-icon">
<a href="javascript:void(0);" id="addBtn"><img src="{{asset('/assets/img/icons/plus1.svg')}}" alt="img"></a> 
</div>
</div>
</div>
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
    <tbody>
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
    <input type="number" step="0.01" name="discount" class="form-control" id="discount">
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Net Price</label>
    <input type="text" name="net_price" id="net_price" readonly>
    </div>
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
    </ul>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" id="Create" class="btn btn-primary me-2"><i class="fas fa-save"></i> Create Quotation </button>
    <!-- <a href="#" class="btn btn-cancel">Cancel</a> -->
    </div>
</div>
</form>
</div>
</div>
</div>
</div>

<!-- Include SweetAlert2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const productSearchInput = document.getElementById('inventorySearch');
    const productDropdown = document.getElementById('productDropdown');
    const customerSearchInput = document.getElementById('customerSearch');
    const customerDropdown = document.getElementById('customerDropdown');
    const customerPhone = document.getElementById('phone');
    let inStock = 0;
            productSearchInput.addEventListener('input', function() {
            const query = productSearchInput.value;
    
            if (query.length >= 2) { 
                axios.get(`{{ route('quotationorders.searchProduct') }}?query=${query}`)
                    .then(response => {
                        productDropdown.innerHTML = ''; 
                        response.data.forEach(product => {
                            const productOption = document.createElement('a');
                            productOption.className = 'dropdown-item';
                            productOption.textContent = product.name;
                            productOption.href = '#';
                            productOption.dataset.id = product.id;
                            productOption.dataset.unit = product.unit;
                            productOption.dataset.quantity = product.quantity;
                            productOption.dataset.selling_price = product.selling_price;
                            productOption.addEventListener('click', function(e) {
                                e.preventDefault();
                                selectproduct(product);
                            });
    
                            productDropdown.appendChild(productOption);
                        });
    
                        productDropdown.style.display = 'block'; // Show the dropdown
                    })
                    .catch(error => {
                        console.error('Error fetching products:', error);
                        productDropdown.style.display = 'none'; // Hide on error
                    });
            } else {
                productDropdown.style.display = 'none'; // Hide if query is too short
            }
        });


         // Hide the dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!productSearchInput.contains(event.target) && !productDropdown.contains(event.target)) {
            productDropdown.style.display = 'none';
        }
    });

    // Function to select a student from the dropdown
    function selectproduct(product) {
        productSearchInput.value = product.name;
        document.getElementById('selling_price').value = product.selling_price;
        document.getElementById('proID').value = product.id;
        document.getElementById('unit').value = product.unit;
        inStock = product.quantity;
        type = product.type;
        productDropdown.style.display = 'none';
    }


    // search Customer Code JS
            customerSearchInput.addEventListener('input', function() {
            const query = customerSearchInput.value;
    
            if (query.length >= 2) { 
                axios.get(`{{ route('quotationorders.searchCustomer') }}?query=${query}`)
                    .then(response => {
                        customerDropdown.innerHTML = ''; 
                        response.data.forEach(customer => {
                            const customerOption = document.createElement('a');
                            customerOption.className = 'dropdown-item';
                            customerOption.textContent = customer.customer_name;
                            customerOption.href = '#';
                            customerOption.dataset.phone = customer.phone;
                            customerOption.addEventListener('click', function(e) {
                                e.preventDefault();
                                selectcustomer(customer);
                            });
    
                            customerDropdown.appendChild(customerOption);
                        });
    
                        customerDropdown.style.display = 'block'; // Show the dropdown
                    })
                    .catch(error => {
                        console.error('Error fetching customers:', error);
                        customerDropdown.style.display = 'none'; // Hide on error
                    });
            } else {
                customerDropdown.style.display = 'none'; // Hide if query is too short
            }
        });


         // Hide the dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!customerSearchInput.contains(event.target) && !customerDropdown.contains(event.target)) {
            customerDropdown.style.display = 'none';
        }
    });

    // Function to select a student from the dropdown
    function selectcustomer(customer) {
        customerSearchInput.value = customer.customer_name;
        customerPhone.value = customer.phone;
        customerDropdown.style.display = 'none';
    }

    // Event listener for quantity input
    document.getElementById('quantity').addEventListener('input', function () {
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
        // // Check if requested quantity is greater than available stock
        // if (type != 'Service' && quantity > inStock) {
        //     console.log(type);
        //     Swal.fire({
        //         icon: 'error',
        //         title: 'Sorry...',
        //         text: `Insufficient stock. Available stock: ${inStock}`,
            // });

            // Clear the quantity input if it exceeds available stock
            // document.getElementById('quantity').value = '';
            // document.getElementById('total_price').value = '';
            // return;
        // }

        // Calculate and display the total price if quantity is within limits
        document.getElementById('total_price').value = (quantity * sellingPrice).toFixed(2);
    });

    document.getElementById('addBtn').addEventListener('click', function() {
        const productName = document.getElementById('inventorySearch').value;
        const productId = document.getElementById('proID').value;
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const unit = document.getElementById('unit').value;
        const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
        const totalPrice = parseFloat(document.getElementById('total_price').value) || 0;

        if (productName && quantity > 0) {
            const tableBody = document.querySelector('#productTable tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="hidden" name="products[${tableBody.children.length}][proID]" value="${productId}">${productName}</td>
                <td><input type="hidden" name="products[${tableBody.children.length}][quantity]" value="${quantity}">${quantity}</td>
                <td><input type="hidden" name="products[${tableBody.children.length}][unit]" value="${unit}">${unit}</td>
                <td><input type="hidden" name="products[${tableBody.children.length}][price]" value="${sellingPrice}">${sellingPrice.toFixed(2)}</td>
                <td><input type="hidden" name="products[${tableBody.children.length}][total_price]" value="${totalPrice}">${totalPrice.toFixed(2)}</td>
                <td><a href="javascript:void(0);" class="delete-set">Delete</a></td>
            `;

            tableBody.appendChild(newRow);
            updateSubtotal();

            // Clear input fields
            document.getElementById('inventorySearch').value = '';
            document.getElementById('quantity').value = '';
            document.getElementById('unit').value = '';
            document.getElementById('selling_price').value = '';
            document.getElementById('total_price').value = '';
        }
    });

    document.getElementById('discount').addEventListener('input', function() {
            updateNetPrice();
        });

    //     document.getElementById('paid_amount').addEventListener('blur', function () {
    //     updateBalance();
    //     let mybalance = parseFloat(document.getElementById('balance').value) || 0;

    //     // Prevent SweetAlert from triggering on every small change
    //     if (client === 'Cash Sales' && mybalance > 0) {
    //         Swal.fire({
    //             icon: 'error',
    //             title: 'Sorry...',
    //             text: `You Can't Store Cash Sales As Credit, Try An Other Customer!`,
    //         });
    //         document.getElementById('paid_amount').value = ''; // Reset the value
    //         document.getElementById('balance').value = ''; // Reset the value
    //     }
    // });


    function updateSubtotal() {
        let subtotal = 0;
        document.querySelectorAll('#productTable tbody tr').forEach(row => {
            subtotal += parseFloat(row.children[4].innerText) || 0;
        });
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('summary_subtotal').innerText = `$ ${subtotal.toFixed(2)}`;
        updateNetPrice();
    }

    function updateNetPrice() {
        const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const netPrice = subtotal - discount;
        document.getElementById('net_price').value = netPrice.toFixed(2);
        document.getElementById('summary_net_total').innerText = `$ ${netPrice.toFixed(2)}`;
        document.getElementById('summary_discount').innerText = `$ ${discount.toFixed(2)}`;
        // updateBalance();
    }

    document.querySelector('#productTable').addEventListener('click', function(event) {
        if (event.target.classList.contains('delete-set')) {
            event.target.closest('tr').remove();
            updateSubtotal();
        }
    });

</script>
@endsection
