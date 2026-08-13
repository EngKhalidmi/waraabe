@extends ('admin.admin_master')
@section('title', 'Saacid - Fixed Assets ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Assets Management</h4>
<h6>Add Asset</h6>
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
<form action="{{route('asset.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
    <div class="row">
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Asset Name</label>
    <input type="text" name="name" placeholder="Enter Asset Name" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Asset Type</label>
    <input type="text" name="type" placeholder="Enter Asset Name" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Asset Amount</label>
    <input type="number" step="0.01" class="form-control" name="amount" placeholder="Enter Asset Amount" required>
    </div>
    </div>
    <div class="col-lg-12 col-12">
    <div class="form-group">
    <label>Description</label>
    <textarea class="form-control"  name="description"></textarea>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i data-lucide="save"></i> Save Asset</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection