@extends ('admin.admin_master')
@section('title', 'Saacid - Salesmans ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Salesman Management</h4>
                    <h6>Add Salesman</h6>
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
                    <form action="{{ route('salesman.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Salesman Name</label>
                                    <input type="text" name="full_name" placeholder="Enter Salesman Full Name" required>
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="number" class="form-control" name="phone" placeholder="063 ......."
                                        required>
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="text" class="form-control" name="age" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Sex</label>
                                    <select name="sex" id="sex" class="select">
                                        <option value="">Select a Sex</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Employee Title</label>
                                    <select name="type" id="type" class="select">
                                        <option value="">Select a Employee Title</option>
                                        <option value="Branch Manager">Branch Manager</option>
                                        <option value="Accountant">Accountant</option>
                                        <option value="Salesman">Salesman</option>
                                        <option value="Cleaner">Cleaner</option>
                                    </select>
                                </div>
                            </div>
                            

                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary "><i class="fas fa-plus"></i> Save
                                    Salesman</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
