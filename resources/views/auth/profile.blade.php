@extends ('admin.admin_master')
@section('title', 'Saacid - My Profile ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Profile</h4>
<h6>User Profile</h6>
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
<div class="profile-set">
<div class="profile-head">
</div>
<div class="profile-top">
<div class="profile-content">
<div class="profile-contentimg">
<img src="{{ auth()->user()->image ? asset('images/users/' . auth()->user()->image) : asset('images/not.jpg') }}" alt="img" id="blah">
</div>
<div class="profile-contentname">
<h2>{{auth()->user()->name}}</h2>
<h4>{{auth()->user()->role}} - {{ auth()->user()->username }}, {{auth()->user()->email}}.</h4>
</div>
</div>
</div>
</div>

<!-- Info Section -->
<form action="{{route('password.update')}}" method="POST">
    @csrf
    @method('put')
 
    <div class="row">
    <div class="col-lg-6 col-sm-12">
    <div class="form-group">
    <label>Username</label>
    <input type="text" placeholder="{{auth()->user()->username}}" readonly>
    </div>
    </div>
    <div class="col-lg-6 col-sm-12">
    <div class="form-group">
    <label>Current Password</label>
    <div class="pass-group">
    <input type="text" class=" pass-input" name="current_password">
    <span class="fas toggle-password fa-eye-slash"></span>
    </div>
    </div>
    </div>
    <div class="col-lg-6 col-sm-12">
    <div class="form-group">
    <label>New Password</label>
    <div class="pass-group">
    <input type="password" class=" pass-input" name="password">
    <span class="fas toggle-password fa-eye-slash"></span>
    </div>
    </div>
    </div>
    <div class="col-lg-6 col-sm-12">
    <div class="form-group">
    <label>Confirm Password</label>
    <div class="pass-group">
    <input type="password" class=" pass-input" name="password_confirmation">
    <span class="fas toggle-password fa-eye-slash"></span>
    </div>
    </div>
    </div>
    <div class="col-12">
    <button type="submit" class="btn btn-primary me-2"><i data-lucide="lock"></i> Change Password</button>
    </div>
    </div>
</form>
</div>
</div>

</div>
</div>

@endsection