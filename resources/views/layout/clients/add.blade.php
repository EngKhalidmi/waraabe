@extends ('admin.admin_master')
@section('title', 'Saacid - Customers ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Customer Management</h4>
<h6>Add Customer</h6>
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
<div class="card">
<div class="card-body">
<form action="{{route('customers.store')}}" method="POST" enctype="multipart/form-data" data-customer-form="create">
        @csrf
    <div class="row">
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Customer Name</label>
    <input type="text" name="customer_name" placeholder="Enter Client Name" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Serial No</label>
    <input type="text" name="serial" readonly value="{{ $nextSerialNumber }}">
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Phone</label>
    <input type="number" class="form-control" name="phone" placeholder="Phone Number" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Opening Balance</label>
     <input type="text" name="balance" placeholder="Enter Begining Balance" required>
    </div>
    </div>
    <div class="col-lg-4 col-12">
    <div class="form-group">
    <label>Address</label>
    <input type="text" name="address" placeholder="Enter Client Address (Place)" required>
    </div>
    </div>
    <div class="col-lg-12 col-12">
    <div class="form-group">
    <label>Description</label>
    <textarea cols="10" rows="3" type="text" name="description" placeholder="Enter Description"> </textarea>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i data-lucide="plus"></i> Save Customer</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection
