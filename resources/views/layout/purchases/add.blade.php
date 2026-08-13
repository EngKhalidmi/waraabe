@extends ('admin.admin_master')
@section('title', 'Saacid - Purchases ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Purchases Management</h4>
                    <h6>Add Purchase1</h6>
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
                    <form action="{{ route('purchases.store') }}" method="POST" enctype="multipart/form-data" data-purchase-form="create">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Product Name</label>
                                    <select name="name" id="name" class="select">
                                        <option value="" selected>Select</option>
                                        @foreach ($products as $pro)
                                            <option
                                                value="{{ $pro->name }}"
                                                data-product-id="{{ $pro->id }}"
                                                data-product-name="{{ $pro->name }}"
                                                data-product-quantity="{{ $pro->quantity }}"
                                                data-product-type="{{ $pro->type }}"
                                                data-product-unit="{{ $pro->unit }}"
                                                data-product-supplier="{{ $pro->supplier }}"
                                            >{{ $pro->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label> Quantity</label>
                                    <input type="number" step="0.01" class="form-control" id="quantity" name="quantity"
                                        placeholder="Enter quantity" required>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label> Unit Cost</label>
                                    <input type="number" step="0.01" class="form-control" id="unit_cost"
                                        name="unit_cost" placeholder="Enter unit Per cost" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label> Total Cost</label>
                                    <input type="number" step="0.01" class="form-control" id="total_cost"
                                        name="total_cost" placeholder="Enter Ttal Cost" readonly>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label> Supplier</label>
                                    <select name="supplier" id="supplier" class="select">
                                        <option value="" selected>Select</option>
                                        @foreach ($suppliers as $sup)
                                            <option
                                                value="{{ $sup->name }}"
                                                data-supplier-id="{{ $sup->id }}"
                                                data-supplier-name="{{ $sup->name }}"
                                                data-supplier-balance="{{ $sup->balance }}"
                                            >{{ $sup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary "><i data-lucide="save"></i> Save
                                    Purchase</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantity');
            const unitCostInput = document.getElementById('unit_cost');
            const totalCostInput = document.getElementById('total_cost');

            function calculateTotalCost() {
                const quantity = parseFloat(quantityInput.value) || 0;
                const unitCost = parseFloat(unitCostInput.value) || 0;
                const totalCost = quantity * unitCost;
                totalCostInput.value = totalCost.toFixed(2); // Set total cost with 2 decimal places
            }

            // Attach event listeners to inputs
            quantityInput.addEventListener('input', calculateTotalCost);
            unitCostInput.addEventListener('input', calculateTotalCost);
        });
    </script>

@endsection
