@extends ('admin.admin_master')
@section('title', 'Saacid - Edit Opening Inventory')
@section('admin')

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Opening Inventory</h4>
                <h6>Update opening inventory information</h6>
            </div>
            <div class="page-btn">
                <a href="{{ route('opening_inventory') }}" class="btn btn-added">
                    <img src="{{ asset('assets/img/icons/arrow-left.svg') }}" alt="img"> Back to List
                </a>
            </div>
        </div>

        <!-- Success and Error Messages -->
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('opening_inventory.update', $inventory->id) }}" method="POST" data-opening-inventory-form="update" data-opening-inventory-id="{{ $inventory->id }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Product <span class="text-danger">*</span></label>
                                <select class="form-select" name="product_id" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                            {{ $inventory->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} 
                                           
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Opening Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="opening_quantity" 
                                    value="{{ old('opening_quantity', $inventory->opening_quantity) }}" 
                                    min="0" step="0.01" required>
                                @error('opening_quantity')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Opening Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="opening_date" 
                                    value="{{ old('opening_date', $inventory->opening_date) }}" required>
                                @error('opening_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Current Product Quantity</label>
                                <input type="text" class="form-control" 
                                    value="{{ optional($inventory->product)->quantity ?? 'N/A' }}" 
                                    disabled readonly>
                                <small class="text-muted">This will be updated automatically when you save changes</small>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-primary">Update Opening Inventory</button>
                            <a href="{{ route('opening_inventory') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add confirmation before leaving the page if changes were made
        const form = document.querySelector('form');
        let formChanged = false;

        // Track form changes
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                formChanged = true;
            });
        });

        // Warn user before leaving if changes were made
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Reset formChanged when form is submitted
        form.addEventListener('submit', function() {
            formChanged = false;
        });
    });
</script>

@endsection
