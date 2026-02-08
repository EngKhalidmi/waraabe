<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}"><img src="{{ asset('/assets/img/icons/dashboard.svg') }}"
                            alt="img"><span> Dashboard</span> </a>
                </li>

                @if (Auth::user()->role == 'admin' || Auth::user()->role == 'acc')
                    {{-- Pharmacy --}}
                    <p style=" font-family:'JetBrains Mono';margin-bottom:0;text-transform:uppercase;">Sales</p>
                    <hr class="mt-0">
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-dollar-sign"></i><span> Sales Transactions</span>
                            <span class="menu-arrow"></span></a>
                        <ul>

                            <li><a class="{{ Route::is('fuel.sales.create') ? 'active' : '' }}"
                                    href="{{ route('fuel.sales.create') }}">Daily Fuel Entry</a>

                            <li><a class="{{ Route::is('fuel.sales.index') ? 'active' : '' }}"
                                    href="{{ route('fuel.sales.index') }}">Fuel Entry List</a>

                            <li><a class="{{ Route::is('fuel.sales.credit.index') ? 'active' : '' }}"
                                    href="{{ route('fuel.sales.credit.index') }}">Fuel Credit List</a>

                            <li><a class="{{ Route::is('sales.add') ? 'active' : '' }}"
                                    href="{{ route('sales.add') }}">Create Oil Sales</a></li>

                            <li><a class="{{ Route::is('sales') ? 'active' : '' }}" href="{{ route('sales') }}">Oil Sales
                                    List</a></li>

                            <li><a class="{{ Route::is('salesTransactions') ? 'active' : '' }}"
                                    href="{{ route('salesTransactions') }}">Oil Sales Reciepts</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/purchase1.svg') }}"
                                alt="img"><span> Purchase</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('products.add') ? 'active' : '' }}"
                                    href="{{ route('products.add') }}">Create Purchase</a></li>

                            <li><a class="{{ Route::is('purchaseTransactions') ? 'active' : '' }}"
                                    href="{{ route('purchaseTransactions') }}">Purchase Transactions</a></li>


                            <li><a class="{{ Route::is('purchases') ? 'active' : '' }}"
                                    href="{{ route('purchases') }}">Purchase List</a></li>

                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-building"></i><span> Suppliers</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('suppliers') ? 'active' : '' }}"
                                    href="{{ route('suppliers') }}">Supplier List</a></li>
                            <li><a href="{{ route('supplier.add') }}"
                                    class="{{ Route::is('supplier.add') ? 'active' : '' }}">Add Supplier</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ asset('/assets/img/icons/quotation1.svg') }}" alt="img">
                            <span> Quotations</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ Route::is('quotationorders') ? 'active' : '' }}"
                                    href="{{ route('quotationorders') }}">
                                    Transactions List
                                </a>
                            </li>
                            <li>
                                <a class="{{ Route::is('quotation') ? 'active' : '' }}"
                                    href="{{ route('quotation') }}">
                                    Quotations List
                                </a>
                            </li>
                            <li>
                                <a class="{{ Route::is('quotationorders.add') ? 'active' : '' }}"
                                    href="{{ route('quotationorders.add') }}">
                                    Created Quotation
                                </a>
                            </li>
                        </ul>
                    </li>


                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/product.svg') }}"
                                alt="img"><span> Inventory</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('products') ? 'active' : '' }}"
                                    href="{{ route('products') }}">Inventory List</a></li>
                            <li><a class="{{ Route::is('products.new') ? 'active' : '' }}"
                                    href="{{ route('products.new') }}">Add New Inventory</a></li>

                        </ul>

                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/product.svg') }}"
                                alt="img"><span> Bad Inventory</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('bad_products') ? 'active' : '' }}"
                                    href="{{ route('bad_products') }}">Bad Inventory List</a></li>
                            <li><a class="{{ Route::is('bad_products.add') ? 'active' : '' }}"
                                    href="{{ route('bad_products.add') }}">Add New Bad Inventory</a></li>

                        </ul>

                    </li>
                    
                         <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/product.svg') }}"
                                alt="img"><span> Opening Inventory</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('opening_inventory') ? 'active' : '' }}"
                                    href="{{ route('opening_inventory') }}">Opening Inventory List</a></li>
                            <li><a class="{{ Route::is('opening_inventory.add') ? 'active' : '' }}"
                                    href="{{ route('opening_inventory.add') }}">Add New Inventory</a></li>

                        </ul>

                    </li>
                    
                    
                    <li class="submenu ">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Customers</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('customers') ? 'active' : '' }}"
                                    href="{{ route('customers') }}">Customer List</a></li>
                            <li><a href="{{ route('customer.add') }}"
                                    class="{{ Route::is('customer.add') ? 'active' : '' }}">Add Customer</a></li>
                        </ul>
                    </li>

                    <li class="submenu ">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Employees</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('salesman') ? 'active' : '' }}"
                                    href="{{ route('salesman') }}">Employee List</a></li>
                            <li><a href="{{ route('salesman.add') }}"
                                    class="{{ Route::is('salesman.add') ? 'active' : '' }}">Add New Employee</a></li>

                        </ul>
                    </li>
                    
                     <li class="submenu ">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Salesman Payments</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                           
                            <li><a href="{{ route('salesman_payment') }}"
                                    class="{{ Route::is('salesman_payment') ? 'active' : '' }}"> Payments List</a>
                            </li>


                            <li><a href="{{ route('salesman_payment.add') }}"
                                    class="{{ Route::is('salesman_payment.add') ? 'active' : '' }}">Add
                                    Salesman Payment</a></li>
                        </ul>
                    </li>
                    
                    
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/return1.svg') }}"
                                alt="img"><span> Payments</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('credits') ? 'active' : '' }}" href="{{ route('credits') }}">
                                    Payments List</a></li>
                            <li><a class="{{ Route::is('credits.add') ? 'active' : '' }}"
                                    href="{{ route('credits.add') }}"> Add New Payment</a></li>
                        </ul>
                    </li>


                    {{-- Financial Menu --}}
                    <p style=" font-family:'JetBrains Mono';margin-bottom:0;text-transform:uppercase;">Financial
                        Transactions</p>
                    <hr class="mt-0">

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-file-invoice-dollar"></i><span> Expense</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('expenses') ? 'active' : '' }}"
                                    href="{{ route('expenses') }}">Expense List</a></li>
                            <li><a href="{{ route('expense.add') }}"
                                    class="{{ Route::is('expense.add') ? 'active' : '' }}">Add Expense</a></li>
                        </ul>
                    </li>

                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-hand-holding-usd"></i><span> Liability</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('account_payables') ? 'active' : '' }}"
                                    href="{{ route('account_payables') }}">Liability List</a></li>
                            <li><a href="{{ route('account_payables.add') }}"
                                    class="{{ Route::is('account_payables.add') ? 'active' : '' }}">Add Liability</a>
                            </li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fa fa-university"></i><span> Bank Statement</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('bankStatement') ? 'active' : '' }}"
                                    href="{{ route('bankStatement') }}">Bank Statement</a></li>
                            <li><a href="{{ route('bankStatement.add') }}"
                                    class="{{ Route::is('bankStatement.add') ? 'active' : '' }}">Add Bank
                                    Statement</a></li>
                        </ul>
                    </li>


                    <p style=" font-family:'JetBrains Mono'; margin-bottom:0;text-transform:uppercase;">Activities</p>
                    <hr class="mt-0">
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-users"></i><span> Users</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('users') ? 'active' : '' }}"
                                    href="{{ route('users') }}">Users List</a></li>
                            <li><a href="{{ route('users.add') }}"
                                    class="{{ Route::is('users.add') ? 'active' : '' }}">Add User</a></li>
                        </ul>
                    </li>

                    {{-- Reports --}}
                    <p style=" font-family:'JetBrains Mono'; margin-bottom:0;text-transform:uppercase;">Reports</p>
                    <hr class="mt-0">
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-file-pdf"></i><span> System Reports</span>
                            <span class="menu-arrow"></span></a>
                        <ul>

                            <li><a class="{{ Route::is('fuel-sales.combined-report') ? 'active' : '' }}"
                                    href="{{ route('fuel-sales.combined-report') }}">Fuel Sales Report</a>
                            </li>

                            <li><a class="{{ Route::is('report.fuel_credit') ? 'active' : '' }}"
                                    href="{{ route('report.fuel_credit') }}">Invoices</a>
                            </li>

                            <li><a class="{{ Route::is('report.bank') ? 'active' : '' }}"
                                    href="{{ route('report.bank') }}">Bank Statement Report</a>
                            </li>
                            <li><a class="{{ Route::is('report.expense') ? 'active' : '' }}"
                                    href="{{ route('report.expense') }}">Expense Report</a></li>
                            {{-- <li><a href="{{ route('report.liability') }}"
                                    class="{{ Route::is('report.liability') ? 'active' : '' }}">Liability Reports</a> --}}
                    </li>
                    <li><a href="{{ route('report.customerBalance') }}"
                            class="{{ Route::is('report.customerBalance') ? 'active' : '' }}">Customer Balance
                            Reports</a></li>
                    <li><a href="{{ route('report.credit') }}"
                            class="{{ Route::is('report.credit') ? 'active' : '' }}">Payment Report</a></li>




                    <li><a href="{{ route('report.purchasePayment') }}"
                            class="{{ Route::is('report.purchasePayment') ? 'active' : '' }}">Purchase
                            Payment Report</a></li>

                    <li><a href="{{ route('report.salesPayment') }}"
                            class="{{ Route::is('report.salesPayment') ? 'active' : '' }}">Sales Payment
                            Report</a></li>


                    <li><a href="{{ route('report.sales') }}"
                            class="{{ Route::is('report.sales') ? 'active' : '' }}">Sales Report</a></li>

                    <li><a href="{{ route('report.inventory') }}"
                            class="{{ Route::is('report.inventory') ? 'active' : '' }}">Inventory Report</a>
                    </li>

                    <li><a href="{{ route('report.purchase') }}"
                            class="{{ Route::is('report.purchase') ? 'active' : '' }}">Purchase Report</a>

                    </li>



                    <li><a href="{{ route('income.statement') }}"
                            class="{{ Route::is('income.statement') ? 'active' : '' }}">Income Statement</a>
                    </li>
            </ul>
            </li>
                @elseif (Auth::user()->role == 'sales')
                  {{-- Pharmacy --}}
                    <p style=" font-family:'JetBrains Mono';margin-bottom:0;text-transform:uppercase;">Sales</p>
                    <hr class="mt-0">
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-dollar-sign"></i><span> Sales Transactions</span>
                            <span class="menu-arrow"></span></a>
                        <ul>

                            <li><a class="{{ Route::is('fuel.sales.create') ? 'active' : '' }}"
                                    href="{{ route('fuel.sales.create') }}">Daily Fuel Entry</a>

                            <li><a class="{{ Route::is('fuel.sales.index') ? 'active' : '' }}"
                                    href="{{ route('fuel.sales.index') }}">Fuel Entry List</a>

                            <li><a class="{{ Route::is('fuel.sales.credit.index') ? 'active' : '' }}"
                                    href="{{ route('fuel.sales.credit.index') }}">Fuel Credit List</a>

                            <li><a class="{{ Route::is('sales.add') ? 'active' : '' }}"
                                    href="{{ route('sales.add') }}">Create Oil Sales</a></li>

                            <li><a class="{{ Route::is('sales') ? 'active' : '' }}" href="{{ route('sales') }}">Oil Sales
                                    List</a></li>

                            <li><a class="{{ Route::is('salesTransactions') ? 'active' : '' }}"
                                    href="{{ route('salesTransactions') }}">Oil Sales Reciepts</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/purchase1.svg') }}"
                                alt="img"><span> Purchase</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('products.add') ? 'active' : '' }}"
                                    href="{{ route('products.add') }}">Create Purchase</a></li>

                            <li><a class="{{ Route::is('purchaseTransactions') ? 'active' : '' }}"
                                    href="{{ route('purchaseTransactions') }}">Purchase Transactions</a></li>


                            <li><a class="{{ Route::is('purchases') ? 'active' : '' }}"
                                    href="{{ route('purchases') }}">Purchase List</a></li>

                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-building"></i><span> Suppliers</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('suppliers') ? 'active' : '' }}"
                                    href="{{ route('suppliers') }}">Supplier List</a></li>
                            <li><a href="{{ route('supplier.add') }}"
                                    class="{{ Route::is('supplier.add') ? 'active' : '' }}">Add Supplier</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <img src="{{ asset('/assets/img/icons/quotation1.svg') }}" alt="img">
                            <span> Quotations</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ Route::is('quotationorders') ? 'active' : '' }}"
                                    href="{{ route('quotationorders') }}">
                                    Transactions List
                                </a>
                            </li>
                            <li>
                                <a class="{{ Route::is('quotation') ? 'active' : '' }}"
                                    href="{{ route('quotation') }}">
                                    Quotations List
                                </a>
                            </li>
                            <li>
                                <a class="{{ Route::is('quotationorders.add') ? 'active' : '' }}"
                                    href="{{ route('quotationorders.add') }}">
                                    Created Quotation
                                </a>
                            </li>
                        </ul>
                    </li>


                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/product.svg') }}"
                                alt="img"><span> Inventory</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('products') ? 'active' : '' }}"
                                    href="{{ route('products') }}">Inventory List</a></li>
                            <li><a class="{{ Route::is('products.new') ? 'active' : '' }}"
                                    href="{{ route('products.new') }}">Add New Inventory</a></li>

                        </ul>

                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/product.svg') }}"
                                alt="img"><span> Bad Inventory</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('bad_products') ? 'active' : '' }}"
                                    href="{{ route('bad_products') }}">Bad Inventory List</a></li>
                            <li><a class="{{ Route::is('bad_products.add') ? 'active' : '' }}"
                                    href="{{ route('bad_products.add') }}">Add New Bad Inventory</a></li>

                        </ul>

                    </li>
                    
                         <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/product.svg') }}"
                                alt="img"><span> Opening Inventory</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('opening_inventory') ? 'active' : '' }}"
                                    href="{{ route('opening_inventory') }}">Opening Inventory List</a></li>
                            <li><a class="{{ Route::is('opening_inventory.add') ? 'active' : '' }}"
                                    href="{{ route('opening_inventory.add') }}">Add New Inventory</a></li>

                        </ul>

                    </li>
                    
                    
                    <li class="submenu ">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Customers</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('customers') ? 'active' : '' }}"
                                    href="{{ route('customers') }}">Customer List</a></li>
                            <li><a href="{{ route('customer.add') }}"
                                    class="{{ Route::is('customer.add') ? 'active' : '' }}">Add Customer</a></li>
                        </ul>
                    </li>

                    <li class="submenu ">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Employees</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('salesman') ? 'active' : '' }}"
                                    href="{{ route('salesman') }}">Employee List</a></li>
                            <li><a href="{{ route('salesman.add') }}"
                                    class="{{ Route::is('salesman.add') ? 'active' : '' }}">Add New Employee</a></li>

                        </ul>
                    </li>
                    
                     <li class="submenu ">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Salesman Payments</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                           
                            <li><a href="{{ route('salesman_payment') }}"
                                    class="{{ Route::is('salesman_payment') ? 'active' : '' }}"> Payments List</a>
                            </li>


                            <li><a href="{{ route('salesman_payment.add') }}"
                                    class="{{ Route::is('salesman_payment.add') ? 'active' : '' }}">Add
                                    Salesman Payment</a></li>
                        </ul>
                    </li>
                    
                    
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/return1.svg') }}"
                                alt="img"><span> Payments</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('credits') ? 'active' : '' }}" href="{{ route('credits') }}">
                                    Payments List</a></li>
                            <li><a class="{{ Route::is('credits.add') ? 'active' : '' }}"
                                    href="{{ route('credits.add') }}"> Add New Payment</a></li>
                        </ul>
                    </li>


                    {{-- Financial Menu --}}
                    <p style=" font-family:'JetBrains Mono';margin-bottom:0;text-transform:uppercase;">Financial
                        Transactions</p>
                    <hr class="mt-0">

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-file-invoice-dollar"></i><span> Expense</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('expenses') ? 'active' : '' }}"
                                    href="{{ route('expenses') }}">Expense List</a></li>
                            <li><a href="{{ route('expense.add') }}"
                                    class="{{ Route::is('expense.add') ? 'active' : '' }}">Add Expense</a></li>
                        </ul>
                    </li>

                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-hand-holding-usd"></i><span> Liability</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('account_payables') ? 'active' : '' }}"
                                    href="{{ route('account_payables') }}">Liability List</a></li>
                            <li><a href="{{ route('account_payables.add') }}"
                                    class="{{ Route::is('account_payables.add') ? 'active' : '' }}">Add Liability</a>
                            </li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fa fa-university"></i><span> Bank Statement</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a class="{{ Route::is('bankStatement') ? 'active' : '' }}"
                                    href="{{ route('bankStatement') }}">Bank Statement</a></li>
                            <li><a href="{{ route('bankStatement.add') }}"
                                    class="{{ Route::is('bankStatement.add') ? 'active' : '' }}">Add Bank
                                    Statement</a></li>
                        </ul>
                    </li>


                  

                    {{-- Reports --}}
                    <p style=" font-family:'JetBrains Mono'; margin-bottom:0;text-transform:uppercase;">Reports</p>
                    <hr class="mt-0">
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-file-pdf"></i><span> System Reports</span>
                            <span class="menu-arrow"></span></a>
                        <ul>

                            <li><a class="{{ Route::is('fuel-sales.combined-report') ? 'active' : '' }}"
                                    href="{{ route('fuel-sales.combined-report') }}">Fuel Sales Report</a>
                            </li>

                            <li><a class="{{ Route::is('report.fuel_credit') ? 'active' : '' }}"
                                    href="{{ route('report.fuel_credit') }}">Invoices</a>
                            </li>

                            <li><a class="{{ Route::is('report.bank') ? 'active' : '' }}"
                                    href="{{ route('report.bank') }}">Bank Statement Report</a>
                            </li>
                            <li><a class="{{ Route::is('report.expense') ? 'active' : '' }}"
                                    href="{{ route('report.expense') }}">Expense Report</a></li>
                            {{-- <li><a href="{{ route('report.liability') }}"
                                    class="{{ Route::is('report.liability') ? 'active' : '' }}">Liability Reports</a> --}}
                    </li>
                    <li><a href="{{ route('report.customerBalance') }}"
                            class="{{ Route::is('report.customerBalance') ? 'active' : '' }}">Customer Balance
                            Reports</a></li>
                    <li><a href="{{ route('report.credit') }}"
                            class="{{ Route::is('report.credit') ? 'active' : '' }}">Payment Report</a></li>




                    <li><a href="{{ route('report.purchasePayment') }}"
                            class="{{ Route::is('report.purchasePayment') ? 'active' : '' }}">Purchase
                            Payment Report</a></li>

                    <li><a href="{{ route('report.salesPayment') }}"
                            class="{{ Route::is('report.salesPayment') ? 'active' : '' }}">Sales Payment
                            Report</a></li>


                    <li><a href="{{ route('report.sales') }}"
                            class="{{ Route::is('report.sales') ? 'active' : '' }}">Sales Report</a></li>

                    <li><a href="{{ route('report.inventory') }}"
                            class="{{ Route::is('report.inventory') ? 'active' : '' }}">Inventory Report</a>
                    </li>

                    <li><a href="{{ route('report.purchase') }}"
                            class="{{ Route::is('report.purchase') ? 'active' : '' }}">Purchase Report</a>

                    </li>



                    <li><a href="{{ route('income.statement') }}"
                            class="{{ Route::is('income.statement') ? 'active' : '' }}">Income Statement</a>
                    </li>
            </ul>
            </li>
        @elseif(Auth::user()->role == 'manager')
            <li class="submenu">
                <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/product.svg') }}"
                        alt="img"><span> Products</span> <span class="menu-arrow"></span></a>
                <ul>
                    <li><a class="{{ Route::is('products') ? 'active' : '' }}"
                            href="{{ route('products') }}">Products List</a></li>
                    {{-- <li><a class="{{ Route::is('products.new') ? 'active' : '' }}" href="{{route('products.new')}}">Add Product</a></li> --}}
                </ul>
            </li>
            <li class="submenu">
                <a href="javascript:void(0);">
                    <img src="{{ asset('/assets/img/icons/quotation1.svg') }}" alt="img">
                    <span> Quotations</span> <span class="menu-arrow"></span>
                </a>
                <ul>
                    <li>
                        <a class="{{ Route::is('quotationorders') ? 'active' : '' }}"
                            href="{{ route('quotationorders') }}">
                            Transactions List
                        </a>
                    </li>
                    <li>
                        <a class="{{ Route::is('quotation') ? 'active' : '' }}" href="{{ route('quotation') }}">
                            Quotations List
                        </a>
                    </li>
                    <li>
                        <a class="{{ Route::is('quotationorders.add') ? 'active' : '' }}"
                            href="{{ route('quotationorders.add') }}">
                            Created Quotation
                        </a>
                    </li>
                </ul>
            </li>


            <li class="submenu ">
                <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Customers</span> <span
                        class="menu-arrow"></span></a>
                <ul>
                    <li><a class="{{ Route::is('customers') ? 'active' : '' }}"
                            href="{{ route('customers') }}">Customer List</a></li>
                    <li><a href="{{ route('customer.add') }}"
                            class="{{ Route::is('customer.add') ? 'active' : '' }}">Add Customer</a></li>
                </ul>
            </li>
            <li class="submenu">
                <a href="javascript:void(0);"><i class="fas fa-building"></i><span> Suppliers</span> <span
                        class="menu-arrow"></span></a>
                <ul>
                    <li><a class="{{ Route::is('suppliers') ? 'active' : '' }}"
                            href="{{ route('suppliers') }}">Supplier List</a></li>
                    <li><a href="{{ route('supplier.add') }}"
                            class="{{ Route::is('supplier.add') ? 'active' : '' }}">Add Supplier</a></li>
                </ul>
            </li>

            <li class="submenu">
                <a href="javascript:void(0);"><i class="fas fa-file-invoice-dollar"></i><span> Expense</span>
                    <span class="menu-arrow"></span></a>
                <ul>
                    <li><a class="{{ Route::is('expenses') ? 'active' : '' }}"
                            href="{{ route('expenses') }}">Expense List</a></li>
                    <li><a href="{{ route('expense.add') }}"
                            class="{{ Route::is('expense.add') ? 'active' : '' }}">Add Expense</a></li>
                </ul>
            </li>



            </li>

            <li class="submenu">
                <a href="javascript:void(0);"><i class="fas fa-file-pdf"></i><span> System Reports</span>
                    <span class="menu-arrow"></span></a>
                <ul>

                    <li><a class="{{ Route::is('report.expense') ? 'active' : '' }}"
                            href="{{ route('report.expense') }}">Expense Reports</a></li>

                    <li><a href="{{ route('report.customerBalance') }}"
                            class="{{ Route::is('report.customerBalance') ? 'active' : '' }}">Customer
                            Balance
                            Reports</a></li>
                    <li><a href="{{ route('report.credit') }}"
                            class="{{ Route::is('report.credit') ? 'active' : '' }}">Lab Payment Reports</a>
                    </li>

                    <li><a href="{{ route('report.purchasePayment') }}"
                            class="{{ Route::is('report.purchasePayment') ? 'active' : '' }}">Purchase
                            Payment Report</a></li>

                    <li><a href="{{ route('report.salesPayment') }}"
                            class="{{ Route::is('report.salesPayment') ? 'active' : '' }}">Sales Payment
                            Report</a></li>

                    <li><a href="{{ route('report.sales') }}"
                            class="{{ Route::is('report.sales') ? 'active' : '' }}">Sales Report</a></li>

                    <li><a href="{{ route('report.purchase') }}"
                            class="{{ Route::is('report.purchase') ? 'active' : '' }}">Purchase Report</a>
                    </li>


                    <li><a href="{{ route('income.statement') }}"
                            class="{{ Route::is('income.statement') ? 'active' : '' }}">Income Statement</a>
                    </li>
                </ul>
            </li>
        @elseif (Auth::user()->role == 'branch-manager')
            <li class="submenu">
                <a href="javascript:void(0);"><img src="{{ asset('/assets/img/icons/product.svg') }}"
                        alt="img"><span> Products</span> <span class="menu-arrow"></span></a>
                <ul>
                    <li><a class="{{ Route::is('products') ? 'active' : '' }}"
                            href="{{ route('products') }}">Products List</a></li>
                    {{-- <li><a class="{{ Route::is('products.new') ? 'active' : '' }}" href="{{route('products.new')}}">Add Product</a></li> --}}
                </ul>
            </li>


            <li class="submenu ">
                <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Customers</span> <span
                        class="menu-arrow"></span></a>
                <ul>
                    <li><a class="{{ Route::is('customers') ? 'active' : '' }}"
                            href="{{ route('customers') }}">Customer List</a></li>
                    <li><a href="{{ route('customer.add') }}"
                            class="{{ Route::is('customer.add') ? 'active' : '' }}">Add Customer</a></li>
                </ul>
            </li>
            <li class="submenu">
                <a href="javascript:void(0);"><i class="fas fa-building"></i><span> Suppliers</span> <span
                        class="menu-arrow"></span></a>
                <ul>
                    <li><a class="{{ Route::is('suppliers') ? 'active' : '' }}"
                            href="{{ route('suppliers') }}">Supplier List</a></li>
                    <li><a href="{{ route('supplier.add') }}"
                            class="{{ Route::is('supplier.add') ? 'active' : '' }}">Add Supplier</a></li>
                </ul>
            </li>

            <li class="submenu">
                <a href="javascript:void(0);"><i class="fas fa-file-invoice-dollar"></i><span> Expense</span>
                    <span class="menu-arrow"></span></a>
                <ul>
                    <li><a class="{{ Route::is('expenses') ? 'active' : '' }}"
                            href="{{ route('expenses') }}">Expense List</a></li>
                    <li><a href="{{ route('expense.add') }}"
                            class="{{ Route::is('expense.add') ? 'active' : '' }}">Add Expense</a></li>
                </ul>
            </li>


            {{-- <li class="submenu">
                <a href="javascript:void(0);"><i class="fas fa-hand-holding-usd"></i><span> Liability</span>
                    <span class="menu-arrow"></span></a>
                <ul>
                    <li><a class="{{ Route::is('account_payables') ? 'active' : '' }}"
                            href="{{ route('account_payables') }}">Liability List</a></li>
                    <li><a href="{{ route('account_payables.add') }}"
                            class="{{ Route::is('account_payables.add') ? 'active' : '' }}">Add Liability</a>
                    </li>
                </ul>
            </li> --}}
            <li class="submenu">
                <a href="javascript:void(0);"><i class="fas fa-file-pdf"></i><span> System Reports</span>
                    <span class="menu-arrow"></span></a>
                <ul>

                    <li><a class="{{ Route::is('report.expense') ? 'active' : '' }}"
                            href="{{ route('report.expense') }}">Expense Report</a></li>

            </li>
            <li><a href="{{ route('report.customerBalance') }}"
                    class="{{ Route::is('report.customerBalance') ? 'active' : '' }}">Customer Balance
                    Report</a></li>

            <li><a href="{{ route('report.purchasePayment') }}"
                    class="{{ Route::is('report.purchasePayment') ? 'active' : '' }}">Purchase
                    Payment Report</a></li>

            <li><a href="{{ route('report.salesPayment') }}"
                    class="{{ Route::is('report.salesPayment') ? 'active' : '' }}">Sales Payment
                    Report</a></li>

            <li><a href="{{ route('report.sales') }}"
                    class="{{ Route::is('report.sales') ? 'active' : '' }}">Sales Report</a></li>

            </ul>
            </li>
            @endif
            </ul>
        </div>
    </div>
</div>
