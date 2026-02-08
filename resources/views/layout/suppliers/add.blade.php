@extends ('admin.admin_master')
@section('title', 'Saacid - Suppliers ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Supplier Management</h4>
<h6>Add Supplier</h6>
</div>
</div>

<div class="card">
<div class="card-body">
<form action="{{route('suppliers.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
    <div class="row">
    <div class="col-lg-8 col-sm-6 col-12">
    <div class="form-group">
    <label>Supplier Name</label>
    <input type="text" name="name" placeholder="Enter Supplier Name" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Supplier Email</label>
    <input type="text" name="email" required>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Phone</label>
    <input type="number" class="form-control" name="phone" placeholder="252 00 000" required>
    </div>
    </div>
    <div class="col-lg-9 col-12">
    <div class="form-group">
    <label>Address</label>
    <input type="text" name="address" placeholder="Enter Supplier Address (Place)" required>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i class="fas fa-save"></i> Save Supplier</button>
    </div>
    </div>
</form>
</div>
</div>





</div>
</div>


@endsection