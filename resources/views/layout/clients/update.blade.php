@extends ('admin.admin_master')
@section('title', 'Saacid - Customers ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Customer Management</h4>
<h6>Update Customer</h6>
</div>
</div>

<div class="card">
<div class="card-body">
<form action="{{route('customers.update', $record->id)}}" method="POST" enctype="multipart/form-data" data-customer-form="update" data-customer-id="{{ $record->id }}">
        @csrf
        @method('PUT')
    <div class="row">
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Customer Name</label>
    <input type="text" name="customer_name" id="name" value="{{$record->customer_name}}" placeholder="Enter Client Name" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Phone</label>
    <input type="number" class="form-control" name="phone" value="{{$record->phone}}" placeholder="063 ......." required>
    </div>
    </div>
    <div class="col-lg-4 col-12">
    <div class="form-group">
    <label>Address</label>
    <input type="text" name="address" value="{{$record->address}}" placeholder="Enter Client Address (Place)" required>
    </div>
    </div>
    <div class="col-lg-4 col-12">
    <div class="form-group">
    <label>Age</label>
    <input type="text" class="form-control" name="age" value="{{$record->age}}" required>
    </div>
    </div>
    <div class="col-lg-4 col-12">
    <div class="form-group">
    <label>Balance</label>
    <input type="text" class="form-control" name="balance" value="{{$record->balance}}" required>
    </div>
    </div>
    <div class="col-lg-4 col-12">
    <div class="form-group">
    <label>Sex</label>
    <select name="sex" id="sex" class="select">
        <option value="Male" {{ $record->sex =='Male'?'selected' : '' }}>Male</option>
        <option value="Female" {{ $record->sex == 'Female'?'selected' : '' }}>Female</option>
    </select>
    </select>
    </div>
    </div>
    
    <div class="col-lg-12 col-12">
    <div class="form-group">
    <label>Description</label>
    <textarea cols="10" rows="3" type="text" class="form-control" name="description" >{{ $record->description }} </textarea>
    </div>
    </div>
    
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i class="fas fa-edit"></i> Update Customer</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection
