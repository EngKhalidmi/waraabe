@extends ('admin.admin_master')
@section('title', 'Saacid - Create Quotation ')
@section('admin')

@if (session('status'))
    <div class="toast-container">
        <div class="toast-message success">
            <div class="toast-icon">
                <i data-lucide="circle-check" class="icon-checkmark"></i>
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
                <i data-lucide="circle-alert" class="icon-error"></i> 
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
<form id="salesForm" data-quotation-form="create" method="POST" action="{{ route('quotationorders.store') }}">
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
    <button type="submit" id="Create" class="btn btn-primary me-2"><i data-lucide="save"></i> Create Quotation </button>
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
@endsection
