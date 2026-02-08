@extends ('admin.admin_master')
@section('title', 'Saacid - Products ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Products Management</h4>
<h6>Add Products</h6>
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


<div class="card">
<div class="card-body">
<form action="{{route('store.Inventory')}}" method="POST" enctype="multipart/form-data">
        @csrf
    <div class="row">
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Product Name</label>
    <input type="text" name="name" placeholder="Enter Product Name" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Type</label>
    <select name="type" id="type" class="select">
        <option value="" disabled selected>Select Type</option>
        <option value="Inventory">Inventory</option>
        <option value="Service">Service</option>
        <option value="Property">Property</option>
    </select>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Unit</label>
    <select name="unit" id="unit" class="select">
        <option value="" disabled selected>Select Unit</option>
            <option value="Pcs">Pcs</option>
            <option value="Meter">Meter</option>
            <option value="Liter">Liter</option>
           
            <option value="Others">Others</option>
    </select>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Actual Price</label>
    <input type="text"  class="form-control" name="actual_price" placeholder="Enter Cost Price" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Selling Price</label>
    <input type="text" class="form-control" name="selling_price" placeholder="Enter Selling Price" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Supplier <span class="text-muted">(Optional)</span></label>
    <select name="supplier" id="supplier" class="select">
        <option value="" disabled selected>Select Supplier</option>
        @foreach($suppliers as $supplier)
        <option value="{{$supplier->id }}">{{ $supplier->name }}</option>
        @endforeach
    </select>
    </div>
    </div>
    <div class="col-lg-12 col-12">
    <div class="form-group">
    <label>Description</label>
    <textarea class="form-control"  name="info"></textarea>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i class="fas fa-save"></i> Register Product</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection