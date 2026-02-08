@extends ('admin.admin_master')
@section('title', 'Saacid - Suppliers ')
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
<form action="{{route('suppliers.update', $record->id)}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
    <div class="row">
    <div class="col-lg-6 col-sm-6 col-12">
    <div class="form-group">
    <label>Supplier Name</label>
    <input type="text" name="name" value="{{$record->name}}" placeholder="Enter Supplier Name" required>
    </div>
    </div>
    <div class="col-lg-6 col-sm-6 col-12">
    <div class="form-group">
    <label>Phone</label>
    <input type="text" name="phone" value="{{$record->phone}}" placeholder="061XXXXXXX" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Email</label>
    <input type="text" name="email" value="{{$record->email}}" placeholder="Email" required>
    </div>
    </div>
     <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Balance</label>
    <input type="text" name="balance" value="{{$record->balance}}" placeholder="Balance" required>
    </div>
    </div>
    <div class="col-lg-4 col-12">
    <div class="form-group">
    <label>Address</label>
    <input type="text" name="address" value="{{$record->address}}" placeholder="Enter Client Address (Place)" required>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i class="fas fa-edit"></i> Update Supplier</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection