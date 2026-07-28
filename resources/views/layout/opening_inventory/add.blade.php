@extends ('admin.admin_master')
@section('title', 'Saacid - Opening Inventory ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Opening Inventory Management</h4>
                    <h6>Add Opening Inventory</h6>
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
                    <form action="{{ route('opening_inventory.store') }}" method="POST" enctype="multipart/form-data" data-opening-inventory-form="create">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Product Name</label>
                                    <select name="product_id" id="product_id" class="select">
                                        <option value="" selected>Select</option>
                                        @foreach ($products as $pro)
                                            <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label> Opening Quantity</label>
                                    <input type="number" step="0.01" class="form-control" id="opening_quantity" name="opening_quantity"
                                        placeholder="Enter Opening quantity" required>
                                </div>
                            </div>
                          
                             <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label> Opening Date</label>
                                    <input type="date" class="form-control" id="opening_date" name="opening_date"
                                        placeholder="Enter Opening Date" required>
                                </div>
                            </div>
                          
                            
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary "><i class="fas fa-save"></i> Save
                                    Opening Inventory</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
