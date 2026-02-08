@extends ('admin.admin_master')
@section('title', 'Saacid - Users List')
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
<form action="{{route('users.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
    <div class="row">
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>User Name</label>
    <input type="text" name="name" placeholder="Enter Name" required>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Username</label>
    <input type="text" name="username" placeholder="Enter Username" required>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Email</label>
    <input type="email" class="form-control" name="email" placeholder="Enter Email" required>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Phone</label>
    <input type="number" class="form-control" name="phone" placeholder="Enter Phone" required>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>User Role</label>
    <select name="role" id="role" class="select">
        <option value="" selected>Select Role</option>
        <option value="admin">admin</option>
        <option value="branch-manager">branch-manager</option>
        <option value="manager">Manager</option>
    
        <option value="sales">sales</option>
        <option value="acc">Accountant</option>
       
       
    </select>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>User Departments</label>
    <select name="depID" id="depID" class="select">
        <option value="" selected>Select Department</option>
        @foreach($departments as $dep)
        <option value="{{$dep->id}}">{{$dep->name}}</option>
        @endforeach
    </select>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Passowrd</label>
    <div class="pass-group">
    <input type="password" class=" pass-input" name="password" placeholder="Make Complex: %@$#$me%$......">
    <span class="fas toggle-password fa-eye-slash"></span>
    </div>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label>Confirm Passowrd</label>
    <div class="pass-group">
    <input type="password" class=" pass-input" name="password_confirmation" Placeholder=" Re-enter Your Password....">
    <span class="fas toggle-password fa-eye-slash"></span>
    </div>
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
    <button type="submit" class="btn btn-primary "><i class="fas fa-save"></i> Save User</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>
@endsection