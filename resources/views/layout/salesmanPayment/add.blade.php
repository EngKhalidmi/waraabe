@extends ('admin.admin_master')
@section('title', 'Saacid - Salesman Payments ')
@section('admin')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Salesman Payments Management</h4>
                    <h6>Add Salesman Payments</h6>
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
                    <form action="{{ route('salesman_payment.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group position-relative">
                                    <label>Salesman Name</label>
                                    <input type="text" name="salesman_name" id="salesmanSearch"
                                        placeholder="Search Salesman Here" class="form-control" autocomplete="off"
                                        autocomplete="off" autocorrect="off" spellcheck="false">
                                    <div id="salesmanDropdown" class="dropdown-menu show"
                                        style="display: none; position: absolute; width: 100%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="salesman_id" id="salesmanID">

                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Previous Balance</label>
                                    <input type="number" step="0.01" class="form-control" id="pbalance" name="pbalance"
                                        placeholder="Previous Balance Amount" readonly>
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Discount</label>
                                    <input type="number" step="0.01" class="form-control" name="discount" id="discount"
                                        placeholder="Enter Discount Amount" value="0">
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Paid Amount</label>
                                    <input type="number" step="0.01" class="form-control" name="paid_amount"
                                        id="paid_amount" placeholder="Enter Payment Amount" required>
                                </div>
                            </div>

                            <div class="col-lg-6 col-12">
                                <div class="form-group">
                                    <label>Remaining Balance</label>
                                    <input type="text" class="form-control" id="remaining" name="remaining" readonly>
                                </div>
                            </div>

                            <div class="col-lg-6 col-12">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}"
                                        required>
                                </div>
                            </div>

                            <div class="col-lg-6 col-12">
                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="select" required>
                                        <option value="" selected disabled>Select Payment Method</option>
                                        <option value="ZAAD">ZAAD</option>
                                        <option value="Edahab">Edahab</option>
                                        <option value="Cash on Hand">Cash on Hand</option>
                                        <option value="Bank Account">Bank Account</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Process
                                    Payment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const salesmanSearchInput = document.getElementById('salesmanSearch');
        const salesmanDropdown = document.getElementById('salesmanDropdown');
        const remainingInput = document.getElementById('remaining');
        const pbalanceInput = document.getElementById('pbalance');
        const paidAmountInput = document.getElementById('paid_amount');
        const discountInput = document.getElementById('discount');

        // Search Salesman Code JS
        salesmanSearchInput.addEventListener('input', function() {
            const query = salesmanSearchInput.value.trim();

            if (query.length >= 2) {
                salesmanDropdown.innerHTML = `
                    <div class="dropdown-item text-center">
                        <i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...
                    </div>
                `;

                // You'll need to create this route in your web.php
                axios.get(`{{ route('salesman_payment.searchSalesman') }}?query=${query}`)
                    .then(response => {
                        salesmanDropdown.innerHTML = '';
                        if (response.data.length > 0) {
                            response.data.forEach(salesman => {
                                const salesmanOption = document.createElement('a');
                                salesmanOption.className = 'dropdown-item';
                                salesmanOption.textContent = salesman.full_name + (salesman.phone ?
                                    ' - ' + salesman.phone : '');
                                salesmanOption.href = '#';
                                salesmanOption.dataset.id = salesman.id;
                                salesmanOption.dataset.balance = salesman.balance || 0;
                                salesmanOption.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    selectSalesman(salesman);
                                });

                                salesmanDropdown.appendChild(salesmanOption);
                            });
                            salesmanDropdown.style.display = 'block';
                        } else {
                            salesmanDropdown.innerHTML = `
                                <div class="dropdown-item text-center text-muted">
                                    No results found
                                </div>
                            `;
                            salesmanDropdown.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching salesmen:', error);
                        salesmanDropdown.style.display = 'none';
                    });
            } else {
                salesmanDropdown.style.display = 'none';
            }
        });

        // Hide the dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!salesmanSearchInput.contains(event.target) && !salesmanDropdown.contains(event.target)) {
                salesmanDropdown.style.display = 'none';
            }
        });

        // Function to select a salesman from the dropdown
        function selectSalesman(salesman) {
            salesmanSearchInput.value = salesman.full_name;
            document.getElementById('salesmanID').value = salesman.id;
            pbalanceInput.value = salesman.balance || 0;
            salesmanDropdown.style.display = 'none';
            updateRemainingBalance();
        }

        // Update remaining balance calculation
        function updateRemainingBalance() {
            const paid = parseFloat(paidAmountInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;
            const previousBalance = parseFloat(pbalanceInput.value) || 0;

            const remaining = previousBalance - paid - discount;
            remainingInput.value = remaining.toFixed(2);

            // Visual feedback for negative balance
            if (remaining < 0) {
                remainingInput.style.color = 'red';
            } else {
                remainingInput.style.color = '';
            }
        }

        // Add event listeners for amount inputs
        paidAmountInput.addEventListener('input', updateRemainingBalance);
        discountInput.addEventListener('input', updateRemainingBalance);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateRemainingBalance();
        });
    </script>

    <style>
        #salesmanDropdown .dropdown-item:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        #salesmanDropdown .dropdown-item {
            padding: 10px;
        }

        #remaining[style*="color: red"] {
            font-weight: bold;
        }
    </style>

@endsection
