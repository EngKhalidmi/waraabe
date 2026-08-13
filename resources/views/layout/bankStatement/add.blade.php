@extends ('admin.admin_master')
@section('title', 'Saacid - Bank Statement ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Bank Statement Management</h4>
<h6>Add Bank Statement</h6>
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
<form action="{{route('bankStatement.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
    <div class="row">
    <div class="col-lg-6 col-sm-6 col-12">
    <div class="form-group">
    <label>Bank Statement Type</label>
    <select name="type" id="type" class="select">
        <option value="" disabled>Select Type</option>
        <option value="Debit">Debit</option>
        <option value="Credit">Credit</option>
    </select>
    </div>
    </div>
    <div class="col-lg-6 col-sm-6 col-12">
    <div class="form-group">
    <label> Amount</label>
    <input type="number" step="0.01" class="form-control" name="amount" placeholder="Enter Bank Statement Amount" required>
    </div>
    </div>
    
        <div class="col-lg-6 col-sm-6 col-12">
    <div class="form-group">
    <label> Cheque Number</label>
    <input type="text" class="form-control" name="check_no" placeholder="Enter Bank Statement Cheque Number">
    </div>
    </div>
    
     <div class="col-lg-6 col-sm-6 col-12">
    <div class="form-group">
    <label> Date</label>
    <input type="date" class="form-control" name="date" placeholder="Select Statement Date">
    </div>
    </div>
    
    
    
    
    <div class="col-lg-12 col-12">
    <div class="form-group">
    <label>Description</label>
    <textarea class="form-control"  name="description"></textarea>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i data-lucide="save"></i> Register Bank Statement</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection