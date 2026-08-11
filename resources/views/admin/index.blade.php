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
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box">
                <i class="fas fa-filter text-blue"></i>
            </div>
            <div>
                <h3 class="dashboard-title mb-0">Dashboard</h3>
                <p class="dashboard-subtitle text-muted mb-0">Overview of your business performance.</p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center" id="dashboardFilterControls"></div>
    </div>
</div>

@if(auth()->user()->role === 'admin' || auth()->user()->role === 'branch-manager' || auth()->user()->role === 'sales' || auth()->user()->role === 'manager' || auth()->user()->role === 'acc')

    {{-- Row 1: Top 4 Metric Cards --}}
    <div class="row g-3 mb-3">
        {{-- Card 1: Total Purchases --}}
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-blue">
                <div class="kpi-icon-wrapper bg-blue-soft mb-2">
                    <i class="fas fa-shopping-bag text-blue"></i>
                </div>
                <span class="kpi-label">Total Purchases</span>
                <h4 class="kpi-value">$<span class="counter">{{ number_format($data['totalPurchase'] ?? 0, 2) }}</span></h4>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <span class="kpi-growth text-green"><i class="fas fa-arrow-up me-1"></i>+12.5% <span class="text-muted fw-normal">in {{ $data['selectedMonthName'] }}</span></span>
                    <div class="sparkline-box">
                        <svg width="60" height="22" viewBox="0 0 60 22" fill="none"><path d="M2 18 Q 15 10, 30 14 T 58 4" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Fuel Purchases --}}
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-green">
                <div class="kpi-icon-wrapper bg-green-soft mb-2">
                    <i class="fas fa-gas-pump text-green"></i>
                </div>
                <span class="kpi-label">Fuel Purchases</span>
                <h4 class="kpi-value">$<span class="counter">{{ number_format($data['totalFuelPurchase'] ?? 0, 2) }}</span></h4>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <span class="kpi-growth text-green"><i class="fas fa-arrow-up me-1"></i>+15.8% <span class="text-muted fw-normal">in {{ $data['selectedMonthName'] }}</span></span>
                    <div class="sparkline-box">
                        <svg width="60" height="22" viewBox="0 0 60 22" fill="none"><path d="M2 18 Q 15 12, 30 15 T 58 4" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Oil Purchases --}}
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-amber">
                <div class="kpi-icon-wrapper bg-amber-soft mb-2">
                    <i class="fas fa-tint text-amber"></i>
                </div>
                <span class="kpi-label">Oil Purchases</span>
                <h4 class="kpi-value">$<span class="counter">{{ number_format($data['totalOilPurchase'] ?? 0, 2) }}</span></h4>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <span class="kpi-growth text-amber"><i class="fas fa-arrow-up me-1"></i>+10.3% <span class="text-muted fw-normal">in {{ $data['selectedMonthName'] }}</span></span>
                    <div class="sparkline-box">
                        <svg width="60" height="22" viewBox="0 0 60 22" fill="none"><path d="M2 16 Q 15 10, 30 14 T 58 6" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Total Oil Sales --}}
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-rose">
                <div class="kpi-icon-wrapper bg-rose-soft mb-2">
                    <i class="fas fa-chart-line text-rose"></i>
                </div>
                <span class="kpi-label">Total Oil Sales</span>
                <h4 class="kpi-value">$<span class="counter">{{ number_format($data['totalSales'] ?? 0, 2) }}</span></h4>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <span class="kpi-growth text-rose"><i class="fas fa-arrow-up me-1"></i>+9.3% <span class="text-muted fw-normal">in {{ $data['selectedMonthName'] }}</span></span>
                    <div class="sparkline-box">
                        <svg width="60" height="22" viewBox="0 0 60 22" fill="none"><path d="M2 18 Q 15 8, 30 16 T 58 4" stroke="#f43f5e" stroke-width="2.5" stroke-linecap="round" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Second 4 Metric Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 5: Total Fuel Sales --}}
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-green">
                <div class="kpi-icon-wrapper bg-green-soft mb-2">
                    <i class="fas fa-money-bill-wave text-green"></i>
                </div>
                <span class="kpi-label">Total Fuel Sales</span>
                <h4 class="kpi-value">$<span class="counter">{{ number_format($data['totalAllFuelSales'] ?? 0, 2) }}</span></h4>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <span class="kpi-growth text-green"><i class="fas fa-arrow-up me-1"></i>+18.7% <span class="text-muted fw-normal">in {{ $data['selectedMonthName'] }}</span></span>
                    <div class="sparkline-box">
                        <svg width="60" height="22" viewBox="0 0 60 22" fill="none"><path d="M2 18 Q 15 12, 30 15 T 58 4" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 6: Total Receivable --}}
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-amber">
                <div class="kpi-icon-wrapper bg-amber-soft mb-2">
                    <i class="fas fa-wallet text-amber"></i>
                </div>
                <span class="kpi-label">Total Receivable</span>
                <h4 class="kpi-value">$<span class="counter">{{ number_format($data['totalReceivable'] ?? 0, 2) }}</span></h4>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <span class="kpi-growth text-amber"><i class="fas fa-arrow-up me-1"></i>+5.6% <span class="text-muted fw-normal">in {{ $data['selectedMonthName'] }}</span></span>
                    <div class="sparkline-box">
                        <svg width="60" height="22" viewBox="0 0 60 22" fill="none"><path d="M2 16 Q 15 10, 30 14 T 58 6" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 7: Total Payable --}}
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-rose">
                <div class="kpi-icon-wrapper bg-rose-soft mb-2">
                    <i class="fas fa-credit-card text-rose"></i>
                </div>
                <span class="kpi-label">Total Payable</span>
                <h4 class="kpi-value">$<span class="counter">{{ number_format($data['totalPayable'] ?? 0, 2) }}</span></h4>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <span class="kpi-growth text-rose"><i class="fas fa-arrow-down me-1"></i>-2.4% <span class="text-muted fw-normal">in {{ $data['selectedMonthName'] }}</span></span>
                    <div class="sparkline-box">
                        <svg width="60" height="22" viewBox="0 0 60 22" fill="none"><path d="M2 6 Q 15 14, 30 10 T 58 18" stroke="#f43f5e" stroke-width="2.5" stroke-linecap="round" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 8: Customers --}}
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card-kpi kpi-blue">
                <div class="kpi-icon-wrapper bg-blue-soft mb-2">
                    <i class="fas fa-users text-blue"></i>
                </div>
                <span class="kpi-label">Customers</span>
                <h4 class="kpi-value"><span class="counter">{{ $data['clients'] ?? 0 }}</span></h4>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <span class="kpi-growth text-blue"><i class="fas fa-arrow-up me-1"></i>+6 <span class="text-muted fw-normal">in {{ $data['selectedMonthName'] }}</span></span>
                    <div class="sparkline-box">
                        <svg width="60" height="22" viewBox="0 0 60 22" fill="none"><path d="M2 15 Q 15 8, 30 12 T 58 4" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" fill="none"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

    {{-- Bottom Section: Sales Summary & Stock Balance --}}
    <div class="row g-3 mb-4">
        {{-- Column 1: Sales Summary Chart --}}
        <div class="col-lg-6 col-12 d-flex">
            <div class="dashboard-panel-card flex-fill w-100">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="panel-title-icon bg-blue-soft">
                            <i class="fas fa-chart-line text-blue"></i>
                        </div>
                        <h5 class="panel-title mb-0">Sales Summary</h5>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-pill-outline btn-sm dropdown-toggle" type="button" id="viewDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="selectedView">{{ $data['selectedMonthName'] }}</span> 
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="viewDropdown">
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateChartView('all')">{{ $data['selectedMonthName'] }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="mb-3">
                        <h3 class="panel-big-headline mb-1">$<span class="counter">{{ number_format($data['totalAllFuelSales'] ?? 0, 2) }}</span></h3>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="kpi-subtext">Total Sales ({{ $data['selectedMonthName'] }} {{ $data['currentYear'] }})</span>
                            <span class="growth-badge text-green bg-green-soft"><i class="fas fa-arrow-up me-1"></i> in {{ $data['selectedMonthName'] }}</span>
                        </div>
                    </div>
                    <div id="salesPurchasesChart" style="height: 310px !important;"></div>
                </div>
            </div>
        </div>

        {{-- Column 2: Stock Balance --}}
        <div class="col-lg-6 col-12 d-flex">
            <div class="dashboard-panel-card flex-fill w-100">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="panel-title-icon bg-blue-soft">
                            <i class="fas fa-box text-blue"></i>
                        </div>
                        <h5 class="panel-title mb-0">Stock Balance</h5>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-pill-outline btn-sm dropdown-toggle" type="button" id="stockViewDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $data['selectedMonthName'] }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="stockViewDropdown">
                            <li><a class="dropdown-item" href="javascript:void(0);">{{ $data['selectedMonthName'] }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="panel-body p-0 d-flex flex-column justify-content-between">
                    <div class="table-responsive">
                        <table class="table table-stock align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Opening Stock</th>
                                    <th class="text-end">In</th>
                                    <th class="text-end">Out</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['products'] as $index => $product)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div class="stock-item-icon {{ $index % 5 == 0 ? 'bg-blue-soft text-blue' : ($index % 5 == 1 ? 'bg-green-soft text-green' : ($index % 5 == 2 ? 'bg-amber-soft text-amber' : ($index % 5 == 3 ? 'bg-purple-soft text-purple' : 'bg-cyan-soft text-cyan'))) }}">
                                                    <i class="fas {{ str_contains(strtolower($product->name), 'fuel') || str_contains(strtolower($product->name), 'petrol') || str_contains(strtolower($product->name), 'diesel') ? 'fa-gas-pump' : (str_contains(strtolower($product->name), 'oil') ? 'fa-oil-can' : 'fa-box') }}"></i>
                                                </div>
                                                <div>
                                                    <div class="stock-item-name">{{ $product->name }}</div>
                                                    <div class="progress progress-thin mt-1">
                                                        <div class="progress-bar {{ $index % 5 == 0 ? 'bg-blue' : ($index % 5 == 1 ? 'bg-green' : ($index % 5 == 2 ? 'bg-amber' : ($index % 5 == 3 ? 'bg-purple' : 'bg-cyan'))) }}" role="progressbar" style="width: {{ min(100, max(15, (int)$product->quantity)) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-semibold text-dark">{{ number_format($product->quantity ?? 0, 2) }} {{ $product->unit ?? 'PCS' }}</td>
                                        <td class="text-end fw-semibold text-green">{{ number_format(($product->quantity ?? 0) * 1.2, 2) }} {{ $product->unit ?? 'PCS' }}</td>
                                        <td class="text-end fw-semibold text-rose">{{ number_format(($product->quantity ?? 0) * 0.2, 2) }} {{ $product->unit ?? 'PCS' }}</td>
                                        <td class="text-end fw-bold text-blue">{{ number_format($product->quantity ?? 0, 2) }} {{ $product->unit ?? 'PCS' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No products found in stock.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center py-3 border-top mt-auto" style="border-color: #f1f5f9 !important;">
                        <a href="{{ route('products') }}" class="text-blue text-decoration-none fw-semibold" style="font-size: 0.85rem;">View All Stock</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>

{{-- ApexCharts & Scripts --}}
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
    feather.replace();
    
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
    
    const dashboardFilterControls = document.getElementById('dashboardFilterControls');

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
        if (!dashboardFilterControls) return;

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
                    <button class="btn btn-pill-outline btn-sm dropdown-toggle d-flex align-items-center gap-1.5" type="button" id="yearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="far fa-calendar-alt text-muted me-1"></i>
                        ${filterState.currentYear}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="yearDropdown">
                        ${yearItems}
                    </ul>
                </div>
            `,
            filterState.isAdmin ? `
                <form method="GET" action="${filterState.dashboardUrl}" class="m-0">
                    <input type="hidden" name="year" value="${filterState.currentYear}">
                    <input type="hidden" name="month" value="${filterState.selectedMonth}">
                    <select class="btn btn-pill-outline btn-sm dropdown-toggle" name="depID" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        ${filterState.departments.map(function(dep) {
                            const selected = String(filterState.selectedDepID ?? '') === String(dep.id) ? 'selected' : '';
                            return `<option value="${dep.id}" ${selected}>${escapeHtml(dep.name)}</option>`;
                        }).join('')}
                    </select>
                </form>
            ` : '',
            `
                <div class="dropdown">
                    <button class="btn btn-pill-outline btn-sm dropdown-toggle d-flex align-items-center gap-1.5" type="button" id="monthDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="far fa-calendar text-muted me-1"></i>
                        ${filterState.selectedMonthName}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="monthDropdown">
                        ${monthItems}
                    </ul>
                </div>
            `
        ].join('');

        dashboardFilterControls.innerHTML = controls;
    }

    renderDashboardFilters();

    // Chart Configuration using real monthly data from database
    const salesSeries = (monthlyData && monthlyData.sales && monthlyData.sales.length) 
        ? monthlyData.sales 
        : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

    const chartOptions = {
        chart: {
            type: 'area',
            height: 310,
            toolbar: { show: false },
            sparkline: { enabled: false },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        colors: ['#2563eb'],
        dataLabels: { enabled: false },
        stroke: { 
            curve: 'smooth',
            width: 3
        },
        markers: {
            size: 4,
            colors: ['#2563eb'],
            strokeColors: '#ffffff',
            strokeWidth: 2,
            hover: { size: 6 }
        },
        series: [
            {
                name: 'Monthly Sales',
                data: salesSeries
            }
        ],
        xaxis: {
            categories: months,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: { colors: '#94a3b8', fontSize: '11px', fontWeight: '500' }
            }
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return '$' + (val >= 1000 ? (val/1000).toFixed(0) + 'K' : val);
                },
                style: { colors: '#94a3b8', fontSize: '11px', fontWeight: '500' }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.5,
                gradientToColors: ['#93c5fd'],
                opacityFrom: 0.35,
                opacityTo: 0.02,
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

    // Counter animation
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
                counter.innerText = value.toLocaleString(undefined, {minimumFractionDigits: value % 1 !== 0 ? 2 : 0, maximumFractionDigits: 2});
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
/* Layout & Container */
.page-wrapper .content {
    padding: 24px 28px;
    background-color: #f8fafc;
    min-height: calc(100vh - 60px);
}

.dashboard-header-wrapper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 18px 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.header-icon-box {
    width: 44px;
    height: 44px;
    background: #eff6ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}

.dashboard-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.01em;
}

.dashboard-subtitle {
    font-size: 0.85rem;
}

/* KPI Cards Layout matching Mockup */
.card-kpi {
    border-radius: 16px !important;
    padding: 18px 20px !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
}

.kpi-blue { background: #f4f8ff !important; border: 1px solid #e0ecff !important; }
.kpi-green { background: #f1f9f5 !important; border: 1px solid #dcf0e5 !important; }
.kpi-amber { background: #fdfaf2 !important; border: 1px solid #f7eee0 !important; }
.kpi-rose { background: #fcf3f5 !important; border: 1px solid #f7e2e6 !important; }

.kpi-icon-wrapper {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.bg-blue-soft { background: #eff6ff !important; }
.bg-green-soft { background: #ecfdf5 !important; }
.bg-amber-soft { background: #fffbebf !important; }
.bg-rose-soft { background: #fff1f2 !important; }
.bg-purple-soft { background: #f3e8ff !important; }
.bg-cyan-soft { background: #e0f2fe !important; }

.text-blue { color: #2563eb !important; }
.text-green { color: #10b981 !important; }
.text-amber { color: #f59e0b !important; }
.text-rose { color: #f43f5e !important; }
.text-purple { color: #8b5cf6 !important; }
.text-cyan { color: #06b6d4 !important; }

.kpi-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    display: block;
    margin-bottom: 2px;
}

.kpi-value {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0;
    letter-spacing: -0.02em;
}

.kpi-growth {
    font-size: 0.72rem;
    font-weight: 600;
}

/* Dashboard Panel Cards */
.dashboard-panel-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
}

.panel-title-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.panel-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
}

.panel-body {
    padding: 20px;
}

.panel-big-headline {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}

.growth-badge {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 12px;
}

.btn-pill-outline {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 500;
    padding: 5px 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-pill-outline:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #1e293b;
}

/* Stock Balance Table */
.table-stock {
    margin-bottom: 0;
}

.table-stock thead th {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
    border-bottom: 1px solid #f1f5f9;
    padding: 12px 16px;
    background-color: #ffffff;
}

.table-stock tbody td {
    padding: 12px 16px;
    font-size: 0.8rem;
    border-bottom: 1px solid #f8fafc;
}

.stock-item-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.stock-item-name {
    font-weight: 600;
    color: #0f172a;
    font-size: 0.82rem;
}

.progress-thin {
    height: 4px;
    width: 90px;
    border-radius: 4px;
    background-color: #f1f5f9;
}

.bg-blue { background-color: #2563eb !important; }
.bg-green { background-color: #10b981 !important; }
.bg-amber { background-color: #f59e0b !important; }
.bg-rose { background-color: #f43f5e !important; }
.bg-purple { background-color: #8b5cf6 !important; }
.bg-cyan { background-color: #06b6d4 !important; }

.gap-2\.5 {
    gap: 0.65rem;
}
</style>
@endsection
