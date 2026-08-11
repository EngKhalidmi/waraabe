@extends ('admin.admin_master')
@section('title', 'Saacid - Sales Orders ')
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

    @if (session('invoice_url'))
        <script>
            window.open("{{ session('invoice_url') }}", '_blank');
        </script>
    @endif

    <style>
        #customerDropdown .dropdown-item:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        #customerDropdown .dropdown-item {
            padding: 10px;
        }

        #productTable .search-row td {
            padding: 8px;
            vertical-align: middle;
        }

        #productTable .search-row input {
            width: 100%;
        }

        #productDropdown {
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
        }

        .product-row:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Create Sales POS</h4>
                    <h6>Sales & Payment Transaction </h6>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form id="salesForm" data-sales-form="create" method="POST" action="{{ route('sales.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <div class="row">
                                        <div class="col-lg-6 col-sm-10 col-10">
                                            <input type="text" name="customer_name" id="customerSearch"
                                                placeholder="Search Customer Name" autocomplete="off" autocorrect="off"
                                                spellcheck="false">
                                            <div id="customerDropdown" class="dropdown-menu show"
                                                style="display: none; position: absolute; width: 39%; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                            </div>
                                        </div>
                                        <input type="hidden" name="customerID" id="customerID">
                                        <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                            <div class="add-icon">
                                                <a href="javascript:void(0);" data-toggle="modal"
                                                    data-target="#addCustomerModal">
                                                    <img src="{{ asset('/assets/img/icons/plus1.svg') }}" alt="img">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Due Date</label>
                                    <div class="input-groupicon">
                                        <input type="date" name="due_date" id="due_date" value="{{ date('Y-m-d') }}"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Select Branch No.</label>
                                    <select name="depID" id="depID" class="form-control select">
                                        @foreach ($deps as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="table-responsive">
                                <table class="table" id="productTable">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>QTY</th>
                                            <th>Unit</th>
                                            <th>Unit Price($)</th>
                                            <th>Total Price($)</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productTableBody">
                                        <!-- Search row -->
                                        <tr class="search-row">
                                            <td colspan="6">
                                                <input type="text" id="productSearch" class="form-control"
                                                    placeholder="Search and add products..." autocomplete="off">
                                                <div id="productDropdown" class="dropdown-menu" style="display:none;"></div>
                                            </td>
                                        </tr>
                                        <!-- Products will be added here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>SubTotal</label>
                                    <input type="text" name="subtotal" id="subtotal" readonly>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Discount</label>
                                    <input type="number" step="0.001" name="discount" value="0.00" class="form-control"
                                        id="discount">
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Net Price</label>
                                    <input type="text" name="net_price" id="net_price" readonly>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Paid Amount</label>
                                    <input type="number" step="0.001" name="paid_amount" class="form-control"
                                        id="paid_amount">
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Balance</label>
                                    <input type="text" name="balance" id="balance" readonly>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <select name="paymentMethod" id="paymentMethod" class="select">
                                        <option value="ZAAD Services">ZAAD Services</option>
                                        <option value="Cash On Hand">Cash On Hand</option>
                                        <option value="E-Dahab">E-Dahab</option>
                                        <option value="Darasalam Bank">Darasalam Bank</option>
                                        <option value="Bank Account">Bank Account</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <label>Note</label>
                                <textarea class="form-control" cols="10" rows="3" name="note"></textarea>
                            </div>
                        </div>

                        <!-- Sales & Payment Summery Info -->
                        <div class="row">
                            <div class="col-lg-12 float-md-right">
                                <div class="total-order">
                                    <ul>
                                        <li>
                                            <h4>SubTotal</h4>
                                            <h5 id="summary_subtotal">$ 0.00</h5>
                                        </li>
                                        <li>
                                            <h4>Discount</h4>
                                            <h5 id="summary_discount">$ 0.00</h5>
                                        </li>
                                        <li class="total">
                                            <h4>Net Total</h4>
                                            <h5 id="summary_net_total">$ 0.00</h5>
                                        </li>
                                        <li>
                                            <h4 class="total">Paid Amount</h4>
                                            <h5 id="summary_paid_amount">$ 0.00</h5>
                                        </li>
                                        <li>
                                            <h4 class="total">Balance</h4>
                                            <h5 id="summary_balance">$ 0.00</h5>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <button type="submit" id="Create" class="btn btn-primary me-2"><i
                                        class="fas fa-save"></i> Save & Print </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Register New Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="quickCustomerForm" method="POST" action="{{ route('customer.quickStore') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
