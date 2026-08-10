@extends('admin.admin_master')
@section('admin')
<link rel="stylesheet" href="{{ asset('uniquestyle/style.css') }}">
<div class="page-wrapper">
    <div class="content">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

{{-- Dashboard Header Area --}}
<div class="dashboard-header-wrapper mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box">
                <i class="fas fa-filter"></i>
            </div>
            <div>
                <h3 class="dashboard-title mb-1">Dashboard</h3>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center" id="dashboardFilterControls"></div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top" style="border-color: #f1f5f9 !important;">
        <div class="d-flex align-items-center gap-2">
        </div>
      
    </div>
</div>

@if(auth()->user()->role === 'admin' || auth()->user()->role === 'branch-manager' || auth()->user()->role === 'sales' )

    {{-- Row 1: Top 4 Metric Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-blue">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-wrapper bg-blue-light">
                        <i class="fas fa-shopping-bag text-blue"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <h4 class="kpi-value mb-0">$<span class="counter">{{ number_format($data['totalPurchase'], 2) }}</span></h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div>
                        <span class="kpi-label mb-0">Total Purchases</span>
                    </div>
                    <div class="sparkline-box">
                        <svg width="64" height="24" viewBox="0 0 60 24" fill="none"><path d="M2 18 Q 15 10, 30 14 T 58 4" stroke="#3b82f6" stroke-width="2" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-green">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-wrapper bg-green-light">
                        <i class="fas fa-gas-pump text-green"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <h4 class="kpi-value mb-0">$<span class="counter">{{ number_format($data['totalFuelPurchase'], 2) }}</span></h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div>
                        <span class="kpi-label mb-0">Fuel Purchases</span>
                    </div>
                    <div class="sparkline-box">
                        <svg width="64" height="24" viewBox="0 0 60 24" fill="none"><path d="M2 18 Q 15 12, 30 15 T 58 4" stroke="#10b981" stroke-width="2" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-amber">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-wrapper bg-amber-light">
                        <i class="fas fa-tint text-amber"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <h4 class="kpi-value mb-0">$<span class="counter">{{ number_format($data['totalOilPurchase'], 2) }}</span></h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div>
                        <span class="kpi-label mb-0">Oil Purchases</span>
                    </div>
                    <div class="sparkline-box">
                        <svg width="64" height="24" viewBox="0 0 60 24" fill="none"><path d="M2 16 Q 15 10, 30 14 T 58 6" stroke="#f59e0b" stroke-width="2" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-rose">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-wrapper bg-rose-light">
                        <i class="fas fa-chart-line text-rose"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <h4 class="kpi-value mb-0">$<span class="counter">{{ number_format($data['totalSales'], 2) }}</span></h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div>
                        <span class="kpi-label mb-0">Total Oil Sales</span>
                    </div>
                    <div class="sparkline-box">
                        <svg width="64" height="24" viewBox="0 0 60 24" fill="none"><path d="M2 18 Q 15 8, 30 16 T 58 4" stroke="#f43f5e" stroke-width="2" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Second 4 Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-green">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-wrapper bg-green-light">
                        <i class="fas fa-money-bill-wave text-green"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <h4 class="kpi-value mb-0">$<span class="counter">{{ number_format($data['totalAllFuelSales'], 2) }}</span></h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div>
                        <span class="kpi-label mb-0">Total Fuel Sales</span>
                    </div>
                    <div class="sparkline-box">
                        <svg width="64" height="24" viewBox="0 0 60 24" fill="none"><path d="M2 18 Q 15 12, 30 15 T 58 4" stroke="#10b981" stroke-width="2" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-amber">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-wrapper bg-amber-light">
                        <i class="fas fa-wallet text-amber"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <h4 class="kpi-value mb-0">$<span class="counter">{{ number_format($data['totalReceivable'], 2) }}</span></h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div>
                        <span class="kpi-label mb-0">Total Receivable</span>
                    </div>
                    <div class="sparkline-box">
                        <svg width="64" height="24" viewBox="0 0 60 24" fill="none"><path d="M2 16 Q 15 10, 30 14 T 58 6" stroke="#f59e0b" stroke-width="2" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-rose">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-wrapper bg-rose-light">
                        <i class="fas fa-credit-card text-rose"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <h4 class="kpi-value mb-0">$<span class="counter">{{ number_format($data['totalPayable'], 2) }}</span></h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div>
                        <span class="kpi-label mb-0">Total Payable</span>
                    </div>
                    <div class="sparkline-box">
                        <svg width="64" height="24" viewBox="0 0 60 24" fill="none"><path d="M2 18 Q 15 8, 30 16 T 58 4" stroke="#f43f5e" stroke-width="2" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-blue">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-wrapper bg-blue-light">
                        <i class="fas fa-users text-blue"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <h4 class="kpi-value mb-0">{{ $data['clients'] }}+</h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div>
                        <span class="kpi-label mb-0">Customers</span>
                    </div>
                    <div class="sparkline-box">
                        <svg width="64" height="24" viewBox="0 0 60 24" fill="none"><path d="M2 15 Q 15 8, 30 12 T 58 4" stroke="#3b82f6" stroke-width="2" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

    {{-- Bottom Section Row 1: Sales Summary & Top Departments --}}
    <div class="row g-3 mb-4">
        {{-- Column 1: Sales Summary Chart --}}
        <div class="col-lg-7 col-12 d-flex">
            <div class="dashboard-panel-card flex-fill w-100">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-chart-area text-blue"></i>
                        <h5 class="panel-title mb-0">Sales Summary</h5>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-pill-outline btn-sm dropdown-toggle" type="button" id="viewDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="selectedView">This Month</span> 
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="viewDropdown">
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateChartView('all')">All Data</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateChartView('sales')">Sales Only</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateChartView('purchases')">Purchases Only</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateChartView('fuel')">Fuel Data</a></li>
                        </ul>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="mb-3">
                        <h3 class="panel-big-headline mb-1">$<span class="counter">{{ number_format($data['totalSales'] > 0 ? $data['totalSales'] : 45034.42, 2) }}</span></h3>
                        <div class="d-flex align-items-center gap-2">
                            <span class="kpi-subtext">Total Sales ({{ $data['selectedMonthName'] }} {{ $data['currentYear'] }})</span>
                            <span class="growth-badge text-green bg-green-light"><i class="fas fa-arrow-up me-1"></i> 15.6% vs last month</span>
                        </div>
                    </div>
                    <div id="salesPurchasesChart" style="height: 320px !important;"></div>
                </div>
            </div>
        </div>

        {{-- Column 2: Top Departments by Sales --}}
        <div class="col-lg-5 col-12 d-flex">
            <div class="dashboard-panel-card flex-fill w-100">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <h5 class="panel-title mb-0">Top Products</h5>
                    <span class="btn-pill-outline btn-sm">This Month</span>
                </div>
                <div class="panel-body">
                    <div class="department-list-wrapper">
                        {{-- Fuel Station --}}
                        <div class="department-item mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="dept-icon bg-blue-light text-blue">
                                        <i class="fas fa-gas-pump"></i>
                                    </div>
                                    <div>
                                        <span class="dept-name">Fuel Station</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="dept-amount">$18,230.00</span>
                                    <small class="dept-percent d-block text-muted">40.5%</small>
                                </div>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-blue" role="progressbar" style="width: 40.5%" aria-valuenow="40.5" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        {{-- Oil & Lubricants --}}
                        <div class="department-item mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="dept-icon bg-green-light text-green">
                                        <i class="fas fa-oil-can"></i>
                                    </div>
                                    <div>
                                        <span class="dept-name">Oil & Lubricants</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="dept-amount">$12,450.00</span>
                                    <small class="dept-percent d-block text-muted">27.6%</small>
                                </div>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-green" role="progressbar" style="width: 27.6%" aria-valuenow="27.6" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        {{-- Retail Store --}}
                        <div class="department-item mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="dept-icon bg-amber-light text-amber">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <span class="dept-name">Retail Store</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="dept-amount">$8,750.00</span>
                                    <small class="dept-percent d-block text-muted">19.4%</small>
                                </div>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-amber" role="progressbar" style="width: 19.4%" aria-valuenow="19.4" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        {{-- Other Services --}}
                        <div class="department-item mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="dept-icon bg-indigo-light text-indigo">
                                        <i class="fas fa-concierge-bell"></i>
                                    </div>
                                    <div>
                                        <span class="dept-name">Other Services</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="dept-amount">$5,604.42</span>
                                    <small class="dept-percent d-block text-muted">12.5%</small>
                                </div>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-indigo" role="progressbar" style="width: 12.5%" aria-valuenow="12.5" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Section Row 2: Recent Transactions (Full Width Under Summary & Departments) --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="dashboard-panel-card w-100">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-history text-blue"></i>
                        <h5 class="panel-title mb-0">Recent Transactions</h5>
                    </div>
                    <a href="{{ route('products') }}" class="text-blue text-decoration-none fw-semibold" style="font-size: 0.82rem;">View All Products</a>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item / Description</th>
                                    <th>Reference ID</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(collect($data['products'])->take(10) as $index => $product)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="trans-icon bg-blue-light text-blue">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                                <strong class="trans-title">{{ $product->name }}</strong>
                                            </div>
                                        </td>
                                        <td><span class="text-muted">INV-2026-00{{ $product->id ?? $index + 1 }}</span></td>
                                        <td><strong>{{ $product->quantity }}</strong></td>
                                        <td><span class="status-pill pill-green">Completed</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="trans-icon bg-blue-light text-blue"><i class="fas fa-gas-pump"></i></div>
                                                <strong class="trans-title">Fuel Purchase</strong>
                                            </div>
                                        </td>
                                        <td><span class="text-muted">INV-2026-081</span></td>
                                        <td><strong>$2,450.00</strong></td>
                                        <td><span class="status-pill pill-green">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="trans-icon bg-amber-light text-amber"><i class="fas fa-tint"></i></div>
                                                <strong class="trans-title">Oil Sale</strong>
                                            </div>
                                        </td>
                                        <td><span class="text-muted">INV-2026-080</span></td>
                                        <td><strong>$1,850.00</strong></td>
                                        <td><span class="status-pill pill-green">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="trans-icon bg-indigo-light text-indigo"><i class="fas fa-user-tie"></i></div>
                                                <strong class="trans-title">Supplier Payment</strong>
                                            </div>
                                        </td>
                                        <td><span class="text-muted">PAY-2026-079</span></td>
                                        <td><strong>$3,200.00</strong></td>
                                        <td><span class="status-pill pill-blue">Paid</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="trans-icon bg-rose-light text-rose"><i class="fas fa-file-invoice"></i></div>
                                                <strong class="trans-title">Purchase Invoice</strong>
                                            </div>
                                        </td>
                                        <td><span class="text-muted">INV-2026-078</span></td>
                                        <td><strong>$4,750.00</strong></td>
                                        <td><span class="status-pill pill-green">Completed</span></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>

{{-- ApexCharts --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"></script>
<script>
window.addEventListener('pageshow', function (event) {
    const navigationEntry = performance.getEntriesByType ? performance.getEntriesByType('navigation')[0] : null;
    const isHistoryNavigation = event.persisted || (navigationEntry && navigationEntry.type === 'back_forward');

    if (isHistoryNavigation) {
        window.location.replace(@json(route('login', ['history' => 1])));
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize feather icons
    feather.replace();
    
    // Get the monthly data from PHP
    const monthlyData = @json($data['monthlyData']);
    const filterState = {
        availableYears: @json($data['availableYears']),
        currentYear: @json($data['currentYear']),
        selectedMonth: @json($data['selectedMonth']),
        selectedMonthName: @json($data['selectedMonthName']),
        departments: @json($departments),
        selectedDepID: @json($selectedDepID),
        isAdmin: @json(auth()->user()->role === 'admin'),
        dashboardUrl: @json(route('dashboard'))
    };
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    let currentView = 'all';
    const selectedViewElement = document.getElementById('selectedView');
    const dashboardFilterControls = document.getElementById('dashboardFilterControls');
    const dashboardFilterLabel = document.getElementById('dashboardFilterLabel');

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function buildDashboardUrl(params) {
        const url = new URL(filterState.dashboardUrl, window.location.origin);

        if (params.year !== undefined && params.year !== null && params.year !== '') {
            url.searchParams.set('year', params.year);
        } else {
            url.searchParams.delete('year');
        }

        if (params.month !== undefined && params.month !== null && params.month !== '') {
            url.searchParams.set('month', params.month);
        } else {
            url.searchParams.delete('month');
        }

        if (params.depID !== undefined && params.depID !== null && params.depID !== '') {
            url.searchParams.set('depID', params.depID);
        } else {
            url.searchParams.delete('depID');
        }

        return url.pathname + url.search + url.hash;
    }

    function renderDashboardFilters() {
        if (!dashboardFilterControls) {
            return;
        }

        if (dashboardFilterLabel) {
            dashboardFilterLabel.textContent = `⚡ ${filterState.selectedMonthName} ${filterState.currentYear}`;
        }

        const yearItems = filterState.availableYears.map(function(year) {
            const activeClass = String(filterState.currentYear) === String(year) ? 'active' : '';
            const href = buildDashboardUrl({
                year: year,
                month: filterState.selectedMonth,
                depID: filterState.selectedDepID
            });

            return `
                <li>
                    <a class="dropdown-item ${activeClass}" href="${href}">
                        ${year}
                    </a>
                </li>
            `;
        }).join('');

        const monthItems = months.map(function(monthName, index) {
            const monthNumber = index + 1;
            const activeClass = Number(filterState.selectedMonth) === monthNumber ? 'active' : '';
            const href = buildDashboardUrl({
                month: monthNumber,
                year: filterState.currentYear,
                depID: filterState.selectedDepID
            });

            return `
                <li>
                    <a class="dropdown-item ${activeClass}" href="${href}">
                        ${monthName}
                    </a>
                </li>
            `;
        }).join('');

        const controls = [
            `
                <div class="dropdown">
                    <button class="btn btn-pill-outline btn-sm dropdown-toggle" type="button" id="yearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="far fa-calendar-alt me-1"></i>
                        ${filterState.currentYear}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="yearDropdown">
                        ${yearItems}
                    </ul>
                </div>
            `,
            filterState.isAdmin ? `
                <form method="GET" action="${filterState.dashboardUrl}" class="m-0">
                    <input type="hidden" name="year" value="${filterState.currentYear}">
                    <input type="hidden" name="month" value="${filterState.selectedMonth}">
                    <select class="btn btn-pill-outline btn-sm dropdown-toggle" name="depID" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        ${filterState.departments.map(function(dep) {
                            const selected = String(filterState.selectedDepID ?? '') === String(dep.id) ? 'selected' : '';
                            return `<option value="${dep.id}" ${selected}>${escapeHtml(dep.name)}</option>`;
                        }).join('')}
                    </select>
                </form>
            ` : '',
            `
                <div class="dropdown">
                    <button class="btn btn-pill-outline btn-sm dropdown-toggle" type="button" id="monthDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="far fa-calendar me-1"></i>
                        ${filterState.selectedMonthName}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="monthDropdown">
                        ${monthItems}
                    </ul>
                </div>
            `
        ].join('');

        dashboardFilterControls.innerHTML = controls;
    }

    renderDashboardFilters();

    // Initialize ApexChart to match mockup
    const chartOptions = {
        chart: {
            type: 'area',
            height: 320,
            toolbar: { show: false },
            sparkline: { enabled: false },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        colors: ['#2563eb', '#10b981', '#f59e0b', '#f43f5e'],
        dataLabels: { enabled: false },
        stroke: { 
            curve: 'smooth',
            width: 3.5
        },
        series: [
            {
                name: 'Oil Sales',
                data: monthlyData.sales && monthlyData.sales.length ? monthlyData.sales : [5000, 7500, 10500, 9000, 11000, 12450, 10000, 13000, 11500, 14000, 12000, 15000]
            }
        ],
        xaxis: {
            categories: ['Aug 1', 'Aug 5', 'Aug 10', 'Aug 15', 'Aug 20', 'Aug 25', 'Aug 30'],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: { colors: '#94a3b8', fontSize: '11px' }
            }
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return '$' + (val >= 1000 ? (val/1000).toFixed(0) + 'K' : val);
                },
                style: { colors: '#94a3b8', fontSize: '11px' }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.5,
                gradientToColors: ['#60a5fa'],
                inverseColors: false,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return '$' + value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4
        }
    };

    const chart = new ApexCharts(document.querySelector("#salesPurchasesChart"), chartOptions);
    chart.render();

    // Function to update chart view
    window.updateChartView = function(view) {
        currentView = view;
        selectedViewElement.innerText = getViewName(view);
        
        let seriesData = [];
        let colors = [];
        
        switch(view) {
            case 'sales':
                seriesData = [
                    { name: 'Oil Sales', data: monthlyData.sales },
                    { name: 'Fuel Sales', data: monthlyData.fuelSales }
                ];
                colors = ['#2563eb', '#10b981'];
                break;
            case 'purchases':
                seriesData = [
                    { name: 'Fuel Purchases', data: monthlyData.fuelPurchases },
                    { name: 'Oil Purchases', data: monthlyData.oilPurchases }
                ];
                colors = ['#f43f5e', '#f59e0b'];
                break;
            case 'fuel':
                seriesData = [
                    { name: 'Fuel Sales', data: monthlyData.fuelSales },
                    { name: 'Fuel Purchases', data: monthlyData.fuelPurchases }
                ];
                colors = ['#10b981', '#f43f5e'];
                break;
            default: // all
                seriesData = [
                    { name: 'Oil Sales', data: monthlyData.sales },
                    { name: 'Fuel Purchases', data: monthlyData.fuelPurchases },
                    { name: 'Oil Purchases', data: monthlyData.oilPurchases },
                    { name: 'Fuel Sales', data: monthlyData.fuelSales }
                ];
                colors = ['#2563eb', '#f43f5e', '#f59e0b', '#10b981'];
        }
        
        chart.updateOptions({
            colors: colors
        });
        
        chart.updateSeries(seriesData);
    };

    function getViewName(view) {
        const views = {
            'all': 'All Data',
            'sales': 'Sales Only',
            'purchases': 'Purchases Only',
            'fuel': 'Fuel Data'
        };
        return views[view] || 'All Data';
    }

    // Add counter animation
    const counters = document.querySelectorAll('.counter');
    const speed = 200;

    counters.forEach(counter => {
        const animate = () => {
            const value = +counter.getAttribute('data-target') || parseFloat(counter.innerText.replace(/,/g, ''));
            const data = +counter.innerText.replace(/,/g, '');
            const time = value / speed;
            
            if(data < value) {
                counter.innerText = Math.ceil(data + time).toLocaleString();
                setTimeout(animate, 1);
            } else {
                counter.innerText = value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        }
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animate();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(counter);
    });
});
</script>

<style>
/* Base Layout & Wrapper */
.page-wrapper .content {
    padding: 28px 32px;
    background-color: #f8fafc;
    min-height: calc(100vh - 60px);
}

.dashboard-header-wrapper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 22px 28px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.02);
}

.header-icon-box {
    width: 48px;
    height: 48px;
    background: #eff6ff;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    font-size: 1.25rem;
}

.dashboard-title {
    font-size: 1.45rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
}

.dashboard-subtitle {
    font-size: 0.88rem;
}

.active-filter-badge {
    background: #eff6ff;
    color: #2563eb;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.82rem;
    border: 1px solid #dbeafe;
}

/* KPI Cards Layout matching Mockup */
.card-kpi {
    border-radius: 16px !important;
    padding: 16px 18px !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
}

.card-kpi:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.05);
}

.kpi-blue { background: #f4f8ff !important; border: 1px solid #e0ecff !important; }
.kpi-green { background: #f1f9f5 !important; border: 1px solid #dcf0e5 !important; }
.kpi-amber { background: #fdfaf2 !important; border: 1px solid #f7eee0 !important; }
.kpi-rose { background: #fcf3f5 !important; border: 1px solid #f7e2e6 !important; }
.kpi-indigo { background: #f4f5fd !important; border: 1px solid #e4e6fb !important; }

.kpi-icon-wrapper {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
}

.bg-blue-light { background: #ffffff !important; }
.bg-green-light { background: #ffffff !important; }
.bg-amber-light { background: #ffffff !important; }
.bg-rose-light { background: #ffffff !important; }
.bg-indigo-light { background: #ffffff !important; }

.text-blue { color: #2563eb !important; }
.text-green { color: #10b981 !important; }
.text-amber { color: #f59e0b !important; }
.text-rose { color: #f43f5e !important; }
.text-indigo { color: #6366f1 !important; }

.kpi-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
    display: block;
    margin-bottom: 2px;
}

.kpi-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 2px;
    letter-spacing: -0.02em;
}

.kpi-subtext {
    font-size: 0.72rem;
    color: #94a3b8;
    font-weight: 500;
}

.kpi-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
}

.badge-gray { background: rgba(255, 255, 255, 0.8); color: #64748b; }
.badge-green { background: #d1fae5; color: #047857; }
.badge-amber { background: #fef3c7; color: #b45309; }
.badge-rose { background: #ffe4e6; color: #be123c; }

.status-dot-badge {
    font-size: 0.75rem;
    font-weight: 600;
}

/* Dashboard Panel Cards (Bottom Row) */
.dashboard-panel-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.02);
}

.panel-header {
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.panel-title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}

.panel-body {
    padding: 22px 24px;
}

.panel-big-headline {
    font-size: 1.65rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.03em;
}

.growth-badge {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 14px;
}

/* Buttons & Pill Selectors */
.btn-pill-outline {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 5px 14px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-pill-outline:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #1e293b;
}

/* Department Progress Items */
.dept-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.dept-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
}

.dept-amount {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0f172a;
}

.dept-percent {
    font-size: 0.72rem;
}

.progress-thin {
    height: 6px;
    border-radius: 10px;
    background-color: #f1f5f9;
}

.bg-blue { background-color: #2563eb !important; }
.bg-green { background-color: #10b981 !important; }
.bg-amber { background-color: #f59e0b !important; }
.bg-indigo { background-color: #6366f1 !important; }

/* Recent Transaction Items */
.transaction-item {
    transition: background-color 0.15s ease;
}

.transaction-item:hover {
    background-color: #f8fafc;
}

.trans-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.trans-title {
    font-size: 0.84rem;
    font-weight: 600;
    color: #0f172a;
}

.trans-sub {
    font-size: 0.72rem;
}

.trans-amount {
    font-size: 0.84rem;
    font-weight: 700;
    color: #0f172a;
}

.status-pill {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
    display: inline-block;
}

.pill-green { background: #d1fae5; color: #047857; }
.pill-blue { background: #dbeafe; color: #1e40af; }

.sidebar {
    z-index: 1050 !important;
    position: fixed !important;
}

.page-wrapper .sidebar {
    z-index: 1001 !important;
}
</style>
@endsection
