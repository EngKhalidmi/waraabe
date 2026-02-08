@extends('admin.admin_master');
@section('title', 'Register Sales')
@section('admin');

<div class="page-content">
     <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
    
                        <div class="text-center mt-4">
                            <div class="mb-3">
                                <a href="index.html" class="auth-logo">
                                    <img src="{{ asset('Logo/SmallLogo.png') }}" height="80" class=" mx-auto" alt="">
                                   
                                </a>
                            </div>
                        </div>
    
                        <h4 class="text-muted text-center font-size-18"><b>Register User</b></h4>
    
                        <div class="p-3">
 
        <form  class="form-horizontal mt-3 needs-validation" action="{{ route('user.update', $user->id) }}"  method="POST" >
            @csrf
            @method('PUT')
    <div class="form-group mb-3 row">
        <div class="col-12">
            <input class="form-control" id="name" type="text" value="{{ $user->name }}"  name="name" required="" placeholder="Name">
        </div>
    </div>
    <div class="form-group mb-3 row">
        <div class="col-12">
            <input class="form-control" id="username"  value="{{ $user->username }}" type="text" name="username" required="" placeholder="Username">
        </div>
    </div>

     <div class="form-group mb-3 row">
        <div class="col-12">
            <input class="form-control" id="email"  value="{{ $user->email }}" type="email" name="email" required="" placeholder="Email">
        </div>
    </div>

  
     
   

    <div class="form-group mb-3 row">
        <div class="col-12">
            <div class="custom-control custom-checkbox">
                
            </div>
        </div>
    </div>

    <div class="form-group text-center row mt-3 pt-1">
        <div class="col-12">
            <button class="btn btn-info w-100 waves-effect waves-light" type="submit">Register</button>
        </div>
    </div>

    
</form>
                            <!-- end form -->
                        </div>
                    </div>
                    <!-- end cardbody -->
                </div>
                <!-- end card -->
            </div>
            <!-- end container -->
        </div>
        <!-- end -->
        

        @endsection
