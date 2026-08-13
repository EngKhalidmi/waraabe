@extends ('admin.admin_master')
@section('title', 'Saacid - Accounts ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Accounts Management</h4>
<h6>Add Accounts</h6>
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
<form action="{{route('cashAccount.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
    <div class="row">
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Accounts Name</label>
    <input type="text" name="account" placeholder="Enter Accounts Name" required>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label> Debit</label>
    <input type="number" step="0.01" class="form-control" name="debit" placeholder="Enter debit Amount" required>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label> Credit</label>
    <input type="number" step="0.01" class="form-control" name="credit" placeholder="Enter Credit Amount" required>
    </div>
    </div>
    <div class="col-lg-3 col-12">
    <div class="form-group">
    <label>Date</label>
    <input type="date" class="form-control" name="date" required>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i data-lucide="save"></i> Register Account</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection