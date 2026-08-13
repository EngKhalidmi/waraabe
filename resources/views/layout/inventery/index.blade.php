@extends ('admin.admin_master')
@section('title', 'Saacid - Products ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Inventory List</h4>
<h6>Manage your Inventory</h6>
</div>
<div class="page-btn">
<a href="{{route('products.new')}}" class="btn btn-added"><img src="assets/img/icons/plus.svg" alt="img">Add Inventory</a>
</div>
</div>

@if (session('status'))
    <div class="toast-container">
        <div class="toast-message success">
            <div class="toast-icon">
                <i data-lucide="circle-check" class="icon-checkmark"></i> <!-- Success checkmark icon -->
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
                <i data-lucide="circle-alert" class="icon-error"></i> <!-- Error exclamation mark icon -->
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
<a class="btn btn-searchset"><img src="assets/img/icons/search-white.svg" alt="img"></a>
</div>
</div>
</div>

<div class="card" id="filter_inputs">
<div class="card-body pb-0">
<div class="row">
<div class="col-lg-3 col-sm-6 col-13">
<div class="form-group">
<input type="text" id="name" placeholder="Filter By Name">
</div>
</div>
<div class="col-lg-3 col-sm-6 col-13">
<div class="form-group">
<select name="type" id="type" class="select">
    <option value="">Select Type</option>
    <option value="Service">Service</option>
    <option value="Inventory">Inventory</option>
</select>
</div>
</div>
<div class="col-lg-3 col-sm-6 col-13">
<div class="form-group">
<input type="text" id="sku_code" placeholder="Filter By Code">
</div>
</div>
<div class="col-lg-3 col-sm-6 col-13">
<div class="form-group">
<input type="text" id="supplier" placeholder="Filter By Supplier">
</div>
</div>
<div class="col-lg-3 col-sm-6 col-13">
<div class="form-group">
<input type="number" class="form-control" id="quantity" placeholder="Filter By Quantity">
</div>
</div>
<div class="col-lg-1 col-sm-6 col-12  ms-auto">
<div class="form-group">
<button type="buttom" class="btn btn-filters ms-auto" id="searchBtn"><img src="assets/img/icons/search-whites.svg" alt="img"></button>
</div>
</div>
</div>
</div>
</div>

<div class="table-responsive">
<table class="table" id="purchaseDate" data-product-page="index">
<thead>
<tr class="bg-primary">
<th class="text-white">#</th>
<th class="text-white">Product</th>
<th class="text-white">Sku Code</th>
<th class="text-white">Type</th>
<th class="text-white">Status</th>
<th class="text-white">Quantity</th>
<th class="text-white">Actual Price</th>
<th class="text-white">Selling Price</th>
<th class="text-white">Created</th>
<th class="text-white">Action</th>
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
    document.addEventListener('DOMContentLoaded', function () {
        if (window.StoreManagementProductModule && typeof window.StoreManagementProductModule.boot === 'function') {
            window.StoreManagementProductModule.boot();
        }
    });

    function confirmDelete(catId) {
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
                document.getElementById('deleteForm-' + catId).submit();
            }
        });
    }
</script>
@endsection
