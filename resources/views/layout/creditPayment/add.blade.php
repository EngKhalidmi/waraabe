@extends ('admin.admin_master')
@section('title', 'Saacid - Credit Transactions ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Payments Management</h4>
                    <h6>Add Payments</h6>
                </div>
            </div>

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

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('credits.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group position-relative">
                                    <label>Customer Name</label>
                                    <input type="text" name="customer_name" id="customerSearch"
                                        placeholder="Search Customer Here" class="form-control" autocomplete="off"
                                        autocomplete="off" autocorrect="off" spellcheck="false">
                                    <div id="customerDropdown" class="dropdown-menu show"
                                        style="display: none; position: absolute; width: 100%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="customerID" id="customerID">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Transaction Type</label>
                                    <select name="type" id="type" class="select">
                                       
                                        <option value="Debit">Return Credits</option>
                                
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Previus Balance</label>
                                    <input type="number" step="0.01" class="form-control" id="pbalance" name="pbalance"
                                        placeholder="Previus Balance Amount" readonly>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Discount</label>
                                    <input type="number" step="0.01" class="form-control" name="discount" id="discount"
                                        placeholder="Enter Discount Amount" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Paid Amount</label>
                                    <input type="number" step="0.01" class="form-control" name="amount" id="paid_amount"
                                        placeholder="Enter Paid Or Previus Amount" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-12">
                                <div class="form-group">
                                    <label>Ramaining Balance</label>
                                    <input type="text" class="form-control" id="remaining" name="date" readonly>
                                </div>
                            </div>
                            
                               <div class="col-lg-6 col-12">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" class="form-control" name="date" required>
                                </div>
                            </div>
                            
                            
                            <div class="col-lg-6 col-12">
                                <div class="form-group">
                                     <label>Payment Method</label>
                                        <select name="payment_method" id="payment_method" class="select" required>
                                            <option value="" selected disabled>Select Payment Method</option>
                                            <option value="Cash On Hand">Cash On Hand</option>
                                            <option value="Zaad Services">Zaad Services</option>
                                            <option value="E-Dahab">E-Dahab</option>
                                            <option value="PREMIER WALLET">PREMIER WALLET</option>
                                            <option value="Bank Account">Bank Account</option>
                                        </select>
                                </div>
                            </div>
                            
                            
                               
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Register
                                    Credits</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.getElementById('type').addEventListener('change', function() {
            if (this.value == 'Credit') {
                document.getElementById('discount').disabled = true;
            } else {
                document.getElementById('discount').disabled = false;
            }
        });
        const customerSearchInput = document.getElementById('customerSearch');
        const customerDropdown = document.getElementById('customerDropdown');
        const remaining = document.getElementById('remaining');

        // Search Customer Code JS
        customerSearchInput.addEventListener('input', function() {
            const query = customerSearchInput.value.trim();

            if (query.length >= 2) {
                customerDropdown.innerHTML = `
                <div class="dropdown-item text-center">
                    <i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...
                </div>
            `;

                axios.get(`{{ route('credits.searchCustomer') }}?query=${query}`)
                    .then(response => {
                        customerDropdown.innerHTML = '';
                        if (response.data.length > 0) {
                            response.data.forEach(customer => {
                                const customerOption = document.createElement('a');
                                customerOption.className = 'dropdown-item';
                                customerOption.textContent = customer.customer_name;
                                customerOption.href = '#';
                                customerOption.dataset.id = customer.id;
                                customerOption.dataset.balance = customer.balance;
                                customerOption.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    selectCustomer(customer);
                                });

                                customerDropdown.appendChild(customerOption);
                            });
                            customerDropdown.style.display = 'block'; // Show the dropdown
                        } else {
                            customerDropdown.innerHTML = `
                            <div class="dropdown-item text-center text-muted">
                                No results found
                            </div>
                        `;
                            customerDropdown.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching customers:', error);
                        customerDropdown.style.display = 'none'; // Hide on error
                    });
            } else {
                customerDropdown.style.display = 'none'; // Hide if query is too short
            }
        });

        // Hide the dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!customerSearchInput.contains(event.target) && !customerDropdown.contains(event.target)) {
                customerDropdown.style.display = 'none';
            }
        });

        // Function to select a customer from the dropdown
        function selectCustomer(customer) {
            customerSearchInput.value = customer.customer_name;
            document.getElementById('customerID').value = customer.id;
            document.getElementById('remaining').value = customer.balance;
            document.getElementById('pbalance').value = customer.balance || 0; // Set previous balance
            customerDropdown.style.display = 'none';
        }
        
        document.addEventListener('DOMContentLoaded', function () {
        const paidAmountInput = document.getElementById('paid_amount');
        const discountInput = document.getElementById('discount');
        const remainingInput = document.getElementById('remaining');
        const pbalanceInput = document.getElementById('pbalance');

        function updateRemaining() {
            const paid = parseFloat(paidAmountInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;
            const previousBalance = parseFloat(pbalanceInput.value) || 0;

            const remaining = previousBalance - (paid + discount);
            remainingInput.value = remaining.toFixed(2); // Keep 2 decimal places
        }

        // Trigger update on input change
        paidAmountInput.addEventListener('input', updateRemaining);
        discountInput.addEventListener('input', updateRemaining);
    });
    
    
        // make disable discount field if transaction type is credit
    </script>

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

@endsection
