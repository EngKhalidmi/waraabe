@extends ('admin.admin_master')
@section('title', 'Saacid - Salesmans ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Salesman Management</h4>
                    <h6>Update Salesman</h6>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('salesman.update', $record->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Salesman Name</label>
                                    <input type="text" name="full_name" id="name" value="{{ $record->full_name }}"
                                        placeholder="Enter Salesman Full Name" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="number" class="form-control" name="phone" value="{{ $record->phone }}"
                                        placeholder="063 ......." required>
                                </div>
                            </div>

                            <div class="col-lg-4 col-12">
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="text" class="form-control" name="age" value="{{ $record->age }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-4 col-12">
                                <div class="form-group">
                                    <label>Balance</label>
                                    <input type="text" class="form-control" name="balance" value="{{ $record->balance }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-4 col-12">
                                <div class="form-group">
                                    <label>Sex</label>
                                    <select name="sex" id="sex" class="select">
                                        <option value="Male" {{ $record->sex == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ $record->sex == 'Female' ? 'selected' : '' }}>Female
                                        </option>
                                    </select>
                                    </select>
                                </div>
                            </div>



                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary "><i class="fas fa-edit"></i> Update
                                    Salesman</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
