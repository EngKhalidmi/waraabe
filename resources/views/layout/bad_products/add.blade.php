@extends ('admin.admin_master')
@section('title', 'Saacid - Patient Treatments ')
@section('admin')

    @if (session('status'))
        <div class="toast-container">
            <div class="toast-message success">
                <div class="toast-icon">
                    <i class="icon-checkmark fas fa-check-circle"></i>
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
                    <i class="icon-error fa fa-exclamation-circle"></i>
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

    <style>
        #customerDropdown .dropdown-item:hover {
            background-color: #f1f1f1;
            /* Light grey on hover */
            cursor: pointer;
        }

        #customerDropdown .dropdown-item {
            padding: 10px;
            /* More space for readability */
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Create Bad Products</h4>
                    <h6>Bad Products Form </h6>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form id="PurchaseForm" method="POST" action="{{ route("bad_products.store") }}" data-bad-product-form="create">
                        @csrf
                        
                        <div class="row">
                        
                       

                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Select Product</label>
                                    <div class="input-groupicon">
                                        <input type="text" name="product_name" id="inventorySearch"
                                            placeholder="Search Products..." autocomplete="off" autocorrect="off"
                                            spellcheck="false">
                                        <div id="productDropdown" class="dropdown-menu show"
                                            style="display: none; position: absolute; width: 100%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                        </div>
                                        <div class="addonset">
                                            <img src="{{ asset('/assets/img/icons/product.svg') }}" alt="img">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="proID" name="proID">
                            
                            
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <div class="input-groupicon">
                                        <input type="number" class="form-control" id="qty" name="quantity"
                                            placeholder="Enter Qty">
                                    </div>
                                </div>
                            </div>

                           
                        </div>


                        <div class="row">
                           
                            
                            <div class="col-lg-12 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Reason.</label>
                                    {{-- text Area --}}
                                    <textarea name="reason" id="reason" rows="5" cols="30" placeholder="Additional Info"></textarea>
                                </div>
                            </div>
                        </div>
                        
                      
                    

                        <!-- Purchase & Payment Summery Info -->
                        <div class="row">
                         
                            <div class="col-lg-12">
                                <button type="submit" id="Create" class="btn btn-primary me-2"><i
                                        class="fas fa-save"></i> Register Bad Product </button>.
                                
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>

        <style>
        .section-content {
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .section-content.collapsed {
            display: none;
        }
        .section-header i {
            transition: transform 0.3s ease;
            margin-top: 5px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.StoreManagementInventoryModule && typeof window.StoreManagementInventoryModule.boot === 'function') {
                window.StoreManagementInventoryModule.boot();
            }
        });
    </script>
@endsection
