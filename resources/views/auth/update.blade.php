@extends ('admin.admin_master')
@section('title', 'Saacid - Users Update ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Users Management</h4>
<h6>Add User</h6>
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
<form action="{{route('users.update', $record->id)}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
    <div class="row">
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>User Name</label>
    <input type="text" value="{{$record->name}}" name="name" placeholder="Enter Name" required>
    </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Username</label>
    <input type="text" name="username" value="{{$record->username}}" placeholder="Enter Username" required>
    </div>
    </div>

    <div class="col-lg-4 col-sm-6 col-12">
    <div class="form-group">
    <label>Phone</label>
    <input type="number" class="form-control" name="phone" value="{{$record->phone}}" placeholder="Enter Phone" required>
    </div>
    </div>
        <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Email</label>
    <input type="email" class="form-control" value="{{$record->email}}" name="email" placeholder="Enter Email" required>
    </div>
    </div>
        <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Department</label>
    <select name="depID" id="depID" class="select">
        @foreach($departments as $dep)
        <option value="{{$dep->id}}"{{$record->depID == $dep->id ? 'selected' : '' }} > {{$dep->name}} </option>
        @endforeach
    </select>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>User Role</label>
    <select name="role" id="role" class="select">
        <option value="" selected>Select Role</option>
        <option value="admin" {{$record->role == 'admin' ? 'selected' : ''}}>Admin</option>
        <option value="manager" {{$record->role == 'manager' ? 'selected' : ''}}>Manager</option>
        <option value="branch-manager" {{$record->role == 'branch-manager' ? 'selected' : ''}}>Branch-Manager</option>
        <option value="acc"  {{$record->role == 'acc' ? 'selected' : ''}}>Accounting</option>
        <option value="sales"  {{$record->role == 'sales' ? 'selected' : ''}}>Sales</option>
      
         
          
    </select>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>User Status</label>
    <select name="status" id="status" class="select">
        <option value="">Select Status</option>
        <option value="1" {{$record->status == '1' ? 'selected' : ''}}>Approved</option>
        <option value="0"  {{$record->status == '0' ? 'selected' : ''}}>Disabled</option>
    </select>
    </div>
    </div>
    <div class="col-lg-12">
    <div class="form-group">
    <label> User Profile Image</label>
    <div class="image-upload">
    <input type="file" name="image">
    <div class="image-uploads">
    <img src="{{asset('/assets/img/icons/upload.svg')}}" alt="img">
    <h4>Drag and drop a Image to upload</h4>
    </div>
    </div>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i data-lucide="save"></i> Update User</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection