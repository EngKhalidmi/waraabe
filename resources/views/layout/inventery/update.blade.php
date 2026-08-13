@extends ('admin.admin_master')
@section('title', 'Saacid - Products ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Supplier Management</h4>
<h6>Update Supplier</h6>
</div>
</div>

<div class="card">
<div class="card-body">
<form action="{{route('products.update', $record->id)}}" method="POST" enctype="multipart/form-data" data-product-form="update" data-product-id="{{ $record->id }}">
        @csrf
        @method('PUT')
    <div class="row">
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Product Name</label>
    <input type="text" name="name" id="name" value="{{$record->name}}" placeholder="Enter Product Name" required>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Type</label>
    <select name="type" id="type" class="form-control select">
        <option value="" selected disabled>Select Type</option>
        <option value="Service" {{$record->type == 'Service' ? 'selected' : ''}} >Service</option>
        <option value="Inventory" {{$record->type == 'Inventory' ? 'selected' : ''}}>Inventory</option>
    </select>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Status</label>
    <select name="status" id="status" class="form-control select">
        <option value="" selected disabled>Select Status</option>
        <option value="0" {{$record->status == '0' ? 'selected' : ''}} >Disable</option>
        <option value="1" {{$record->status == '1' ? 'selected' : ''}}>Active</option>
    </select>
    </div>
    </div>
    
      <div class="col-lg-6 col-12">
    <div class="form-group">
    <label>Actual Price</label>
    <input type="text" class="form-control"  name="actual_price" value="{{$record->actual_price}}" placeholder="Actual Price" required>
    </div>
    </div>
    
    
    <div class="col-lg-6 col-12">
    <div class="form-group">
    <label>Selling Price</label>
    <input type="text" class="form-control"  name="selling_price" value="{{$record->selling_price}}" placeholder="Selling Price" required>
    </div>
    </div>
    
      
    
    
    <div class="col-lg-12 col-12">
    <div class="form-group">
    <label>Description</label>
    <textarea class="form-control"  name="info">{{$record->info}}</textarea>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i data-lucide="square-pen"></i> Update Products</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection

