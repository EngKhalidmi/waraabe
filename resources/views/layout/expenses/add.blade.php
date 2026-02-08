@extends ('admin.admin_master')
@section('title', 'Saacid - Expenses ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Expense Management</h4>
                    <h6>Add Expense</h6>
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
                    <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Expense Type</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="Rent">Rent</option>
                                        <option value="Utilities">Utilities</option>
                                        <option value="Office Supplies">Office Supplies</option>
                                        <option value="Salaries and Wages">Salaries and Wages</option>
                                        <option value="Repairs and Maintenance">Repairs and Maintenance</option>
                                        <option value="Miscellaneous">Miscellaneous</option>
                                        <option value="Others">Others</option>



                                    </select>
                                </div>

                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Select Salesman</label>
                                    <select name="salesman_id" id="salesman_id" class="form-control">
                                        <option value="">Select Salesman</option>
                                        @foreach ($salesman as $salesman)
                                            <option value="{{ $salesman->id }}">{{ $salesman->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Expense Amount</label>
                                    <input type="number" step="0.01" class="form-control" name="amount"
                                        placeholder="Enter Expense Amount" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" class="form-control" name="date" required>
                                </div>
                            </div>
                            <div class="col-lg-12 col-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="form-control" name="description"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary "><i class="fas fa-save"></i> Save
                                    Expense</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
