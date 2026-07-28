@extends ('admin.admin_master')
@section('title', 'Saacid - Liability ')
@section('admin')

<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title">
<h4>Liability Management</h4>
<h6>Add Liability</h6>
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
<style>
    #customerDropdown .dropdown-item:hover {
        background-color: #f1f1f1; /* Light grey on hover */
        cursor: pointer;
    }

    #customerDropdown .dropdown-item {
        padding: 10px; /* More space for readability */
    }
</style>
<div class="card">
<div class="card-body">
<form action="{{route('account_payables.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
    <div class="row">
        <div class="col-lg-6 col-sm-6 col-12">
            <div class="form-group">
            <label>Supplier</label>
            <div class="row">
            <div class="col-lg-10 col-sm-10 col-10">
            <input type="text" name="customer_name" id="customerSearch" placeholder="Search Supplier Here" autocomplete="off" autocorrect="off" spellcheck="false">
            <div id="customerDropdown" class="dropdown-menu show" style="display: none; position: absolute; width: 39%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);"></div>
            </div>
            <input type="hidden" name="received_from" id="customerID" required>
            <div class="col-lg-2 col-sm-2 col-2 ps-0">
            <div class="add-icon">
            <a href="{{url('suppliers/register')}}" target="_blank" ><img src="{{asset('/assets/img/icons/plus1.svg')}}" alt="img"></a>
            </div>
            </div>
            </div>
            </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label> Date</label>
    <input type="date"  class="form-control" name="date" value={{now()}} required>
    </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label> Previus Balance</label>
    <input type="number" step="0.01" class="form-control" readonly name="pbalance" id="pbalance" placeholder="Previus balance">
</div>
</div>
<!--<div class="col-lg-3 col-sm-6 col-12">-->
<!--    <div class="form-group">-->
<!--    <label> Liability Type</label>-->
<!--    <select name="type" id="type" class="select">-->
<!--        <option value="" selected disabled>Select Liability Type</option>-->
<!--        <option value="Short Term">Short Term</option>-->
<!--        <option value="Long Term">Long Term</option>-->
<!--    </select>-->
<!--</div>-->
<!--</div>-->
<div class="col-lg-3 col-sm-6 col-12">
    <div class="form-group">
    <label> Transaction Type</label>
<select name="transaction_type" id="transaction_type" class="select">
    <option value="" disabled>Select Transaction Type</option>
    <option selected value="Debit">Debit</option>
</select>
</div>
</div>
    <div class="col-lg-2 col-sm-6 col-12">
    <div class="form-group">
    <label> Discount</label>
    <input type="number" step="0.01" class="form-control" name="discount" id="discount" placeholder="Enter Discount Amount">
</div>
</div>
    <div class="col-lg-2 col-sm-6 col-12">
    <div class="form-group">
    <label> Amount</label>
    <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Enter Liability Amount" required>
</div>
</div>
    <div class="col-lg-2 col-sm-6 col-12">
    <div class="form-group">
    <label> Remaining Balance</label>
    <input type="number" step="0.01" class="form-control" id="current" name="current" placeholder="Enter Current Balance" readonly required>
</div>
</div>


<div class="col-lg-12 col-12">
    <div class="form-group">
    <label>Description</label>
    <textarea class="form-control"  name="description"></textarea>
    </div>
    </div>
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary "><i class="fas fa-save"></i> Register Liability</button>
    </div>
    </div>
</form>
</div>
</div>
</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
     const customerSearchInput = document.getElementById('customerSearch');
     const customerDropdown = document.getElementById('customerDropdown');

     function renderSupplierResults(customers) {
        customerDropdown.innerHTML = '';
        if (customers.length > 0) {
            customers.forEach(customer => {
                const customerOption = document.createElement('a');
                customerOption.className = 'dropdown-item';
                customerOption.textContent = customer.name;
                customerOption.href = '#';
                customerOption.dataset.id = customer.id;
                customerOption.dataset.balance = customer.balance;
                customerOption.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectcustomer(customer);
                });

                customerDropdown.appendChild(customerOption);
            });

            customerDropdown.style.display = 'block';
        } else {
            customerDropdown.innerHTML = `
                <div class="dropdown-item text-center text-muted">
                    No results found
                </div>
            `;
            customerDropdown.style.display = 'block';
        }
     }

    //  Function to select a customer from the dropdown
    customerSearchInput.addEventListener('input', function() {
        const query = customerSearchInput.value;

        if (query.length >= 2) { 
            customerDropdown.innerHTML = `
                <div class="dropdown-item text-center">
                    <i class="fa fa-spinner fa-spin" style="margin-right:8px !important;"></i> Searching...
                </div>
            `;
            if (!navigator.onLine && window.StoreManagementFinanceModule) {
                window.StoreManagementFinanceModule.searchSuppliers(query)
                    .then(renderSupplierResults)
                    .catch(error => {
                        console.error('Error fetching customers:', error);
                        customerDropdown.style.display = 'none';
                    });
            } else {
                axios.get(`{{ route('payable.searchSupplier') }}?query=${query}`)
                    .then(response => {
                        renderSupplierResults(response.data || []);
                    })
                    .catch(error => {
                        console.error('Error fetching customers:', error);
                        customerDropdown.style.display = 'none'; // Hide on error
                    });
            }
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

    function selectcustomer(customer) {
        customerSearchInput.value = customer.name;
        document.getElementById('customerID').value = customer.id;
        document.getElementById('pbalance').value = customer.balance;
        customerDropdown.style.display = 'none';
    }
    document.getElementById('amount').addEventListener('input', calculateBalance);
document.getElementById('discount').addEventListener('input', calculateBalance);
document.getElementById('transaction_type').addEventListener('change', calculateBalance);

function calculateBalance() {
    const previousBalance = parseFloat(document.getElementById('pbalance').value) || 0;
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const transactionType = document.getElementById('transaction_type').value;

    let current;

    if (transactionType === 'Debit') {
        current = previousBalance - amount - discount;
    } else if (transactionType === 'Credit') {
        current = previousBalance + amount + discount;
    } else {
        current = previousBalance; // Default to previous balance if no type is selected
    }

    // Prevent negative current balance
    if (current < 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Current balance cannot be negative!',
        });
        document.getElementById('amount').value = '';
        document.getElementById('current').value = previousBalance.toFixed(2);
        return;
    }

    document.getElementById('current').value = current.toFixed(2);
}

// Disable discount input when transaction type is "Debit"
document.getElementById('transaction_type').addEventListener('change', function () {
    const discountInput = document.getElementById('discount');
    if (this.value === 'Debit') {
        discountInput.value = 0;
        discountInput.disabled = true;
    } else {
        discountInput.disabled = false;
    }
});

</script>
@endsection
