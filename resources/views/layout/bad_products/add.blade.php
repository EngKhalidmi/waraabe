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
                    <form id="PurchaseForm" method="POST" action="{{ route("bad_products.store") }}">
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


    </script>

    
    <script>
        const productSearchInput = document.getElementById('inventorySearch');
        const productDropdown = document.getElementById('productDropdown');
        
        productSearchInput.addEventListener('input', function() {
            const query = productSearchInput.value;

            if (query.length >= 2) {
                productDropdown.innerHTML = `
                <div class="dropdown-item text-center">
                    <i class="fa fa-spinner fa-spin " style="margin-right:8px !important;"></i> Searching...
                </div>
            `;
                axios.get(`{{ route('products.searchProduct') }}?query=${query}`)
                    .then(response => {
                        productDropdown.innerHTML = '';
                        if (response.data.length > 0) {
                            response.data.forEach(product => {
                                const productOption = document.createElement('a');
                                productOption.className = 'dropdown-item';
                                productOption.textContent = product.name;
                                productOption.href = '#';
                                productOption.dataset.id = product.id;
                                productOption.dataset.selling_price = product.selling_price;
                                productOption.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    selectproduct(product);
                                });

                                productDropdown.appendChild(productOption);
                            });

                            productDropdown.style.display = 'block'; // Show the dropdown
                        } else {
                            productDropdown.innerHTML = `
                            <div class="dropdown-item text-center text-muted">
                                Lab Doesn't Exist....
                            </div>
                        `;
                            productDropdown.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching products:', error);
                        productDropdown.style.display = 'none'; // Hide on error
                    });
            } else {
                productDropdown.style.display = 'none'; // Hide if query is too short
            }
        });

        // Hide the dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!productSearchInput.contains(event.target) && !productDropdown.contains(event.target)) {
                productDropdown.style.display = 'none';
            }
        });

        // Function to select a product from the dropdown
        function selectproduct(product) {
            productSearchInput.value = product.name;
          
            document.getElementById('proID').value = product.id;
            productDropdown.style.display = 'none';
        }




    </script>
@endsection
