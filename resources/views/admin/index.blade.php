@extends('admin.admin_master')
@section('admin')
<link rel="stylesheet" href="{{ asset('uniquestyle/style.css') }}">
<div class="page-wrapper">
    <div class="content">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

{{-- Filter Card --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="dashboard-filter-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="filter-icon-box">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1" style="font-weight: 700; color: #0f172a;">Filter Dashboard Data</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" style="font-size: 0.85rem;">Currently viewing:</span>
                            <span class="filter-badge" id="dashboardFilterLabel"></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap align-items-center" id="dashboardFilterControls"></div>
            </div>
        </div>
    </div>
</div>

        {{-- Low Stock Notification --}}
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'branch-manager' || auth()->user()->role === 'sales' )

            {{-- Top KPI Widgets --}}
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget widget-blue">
                        <div class="dash-widgetimg">
                            <span style="background: #eff6ff !important;"><img src="{{ asset('assets/img/icons/dash1.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalPurchase'], 2) }}</span></h5>
                            <h6>Total Purchases</h6>
                            <small>{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget widget-green">
                        <div class="dash-widgetimg">
                            <span style="background: #ecfdf5 !important;"><img src="{{ asset('assets/img/icons/dash2.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalFuelPurchase'], 2) }}</span></h5>
                            <h6>Fuel Purchases</h6>
                            <small>{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget widget-amber">
                        <div class="dash-widgetimg">
                            <span style="background: #fef3c7 !important;"><img src="{{ asset('assets/img/icons/dash3.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalOilPurchase'], 2) }}</span></h5>
                            <h6>Oil Purchases</h6>
                            <small>{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget widget-rose">
                        <div class="dash-widgetimg">
                            <span style="background: #ffe4e6 !important;"><img src="{{ asset('assets/img/icons/dash4.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalSales'], 2) }}</span></h5>
                            <h6>Total Oil Sales</h6>
                            <small>{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Second Row KPI Widgets --}}
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget widget-green">
                        <div class="dash-widgetimg">
                            <span style="background: #ecfdf5 !important;"><img src="{{ asset('assets/img/icons/dash2.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalAllFuelSales'], 2) }}</span></h5>
                            <h6>Total Fuel Sales</h6>
                            <small>{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget widget-amber">
                        <div class="dash-widgetimg">
                            <span style="background: #fef3c7 !important;"><img src="{{ asset('assets/img/icons/dash2.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalReceivable'], 2) }}</span></h5>
                            <h6>Total Receivable</h6>
                            <small>All Time</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget widget-rose">
                        <div class="dash-widgetimg">
                            <span style="background: #ffe4e6 !important;"><img src="{{ asset('assets/img/icons/dash2.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalPayable'], 2) }}</span></h5>
                            <h6>Total Payable</h6>
                            <small>All Time</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget widget-indigo">
                        <div class="dash-widgetimg">
                            <span style="background: #e0e7ff !important;"><i class="fas fa-calendar-check" style="font-size: 1.2rem; color: #4338ca;"></i></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5 style="color: #4338ca !important;"><span>{{ $data['selectedMonthName'] }}</span></h5>
                            <h6>Selected Month</h6>
                            <small>{{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Counters --}}
        <div class="row">
            <div class="col-lg-3 col-sm-6 col-12 d-flex">
                <div class="dash-count">
                    <div class="dash-counts">
                        <h4>{{ $data['clients'] }}+</h4>
                        <h5>Customers</h5>
                    </div>
                    <div class="dash-imgs">
                        <i data-feather="user"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12 d-flex">
                <div class="dash-count das1">
                    <div class="dash-counts">
                        <h4>{{ $data['suppliers'] }}+</h4>
                        <h5>Suppliers</h5>
                    </div>
                    <div class="dash-imgs">
                        <i data-feather="user-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12 d-flex">
                <div class="dash-count das2">
                    <div class="dash-counts">
                        <h4>{{ $data['sales'] }}+</h4>
                        <h5>Sales Invoices</h5>
                    </div>
                    <div class="dash-imgs">
                        <i data-feather="file-text"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12 d-flex">
                <div class="dash-count das3">
                    <div class="dash-counts">
                        <h4>{{ $data['purchases'] }}+</h4>
                        <h5>Purchase Invoices</h5>
                    </div>
                    <div class="dash-imgs">
                        <i data-feather="file"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Graph and Recent Products --}}
        <div class="row">
            {{-- Chart --}}
            <div class="col-lg-7 col-sm-12 col-12 d-flex">
                <div class="card flex-fill w-100">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Monthly Sales & Purchases - {{ $data['currentYear'] }}</h5>
                        <div class="graph-sets">
                            <div class="dropdown">
                                <button class="btn btn-white btn-sm dropdown-toggle" type="button" id="viewDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="selectedView">All Data</span> 
                                    <img src="{{ asset('assets/img/icons/dropdown.svg') }}" alt="img" class="ms-2">
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="viewDropdown">
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateChartView('all')">All Data</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateChartView('sales')">Sales Only</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateChartView('purchases')">Purchases Only</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateChartView('fuel')">Fuel Data</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="padding: 24px;">
                        <div id="salesPurchasesChart" style="height: 400px !important;"></div>
                    </div>
                </div>
            </div>

            {{-- Recently Added Products --}}
            <div class="col-lg-5 col-sm-12 col-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Recently Added Products</h4>
                        <div class="dropdown">
                            <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                <i class="fa fa-ellipsis-v"></i>
                            </a>
                            <ul class="dropdown-menu">
                                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'branch-manager')
                                    <li><a href="{{ route('products') }}" class="dropdown-item">Product List</a></li>
                                    <li><a href="{{ url('/sales/register') }}" class="dropdown-item">Create Sales</a></li>
                                @elseif(auth()->user()->role === 'sales')
                                    <li><a href="{{ url('/sales/register') }}" class="dropdown-item">Create Sales</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover align-middle">
                                <thead style="background: #f8fafc;">
                                    <tr>
                                        <th style="color: #475569; font-weight: 600;">Products</th>
                                        <th style="color: #475569; font-weight: 600;">Qty</th>
                                        <th style="color: #475569; font-weight: 600;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data['products'] as $product)
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <strong style="color: #0f172a;">{{ $product->name }}</strong>
                                                    @if($product->quantity < 10)
                                                        <small class="text-danger d-block">Low Stock</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($product->quantity < 10)
                                                    <span class="badge bg-warning text-dark d-inline-block text-center" style="min-width:50px; border-radius: 6px;">{{ $product->quantity }}</span>
                                                @else
                                                    <span class="badge bg-success text-white d-inline-block text-center" style="min-width:50px; border-radius: 6px;">{{ $product->quantity }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->quantity < 10)
                                                    <span class="badge bg-danger" style="border-radius: 6px;">Low</span>
                                                @elseif($product->quantity < 20)
                                                    <span class="badge bg-warning" style="border-radius: 6px;">Medium</span>
                                                @else
                                                    <span class="badge bg-success" style="border-radius: 6px;">Good</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No products found</td>
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
            dashboardFilterLabel.textContent = `${filterState.selectedMonthName} ${filterState.currentYear}`;
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
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="yearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-calendar me-2"></i>
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
                    <select class="btn btn-primary btn-sm dropdown-toggle" style="padding-bottom: 7px" name="depID" onchange="this.form.submit()">
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
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="monthDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-calendar-alt me-2"></i>
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

    // Initialize the chart with all data
    const chartOptions = {
        chart: {
            type: 'area',
            height: 400,
            toolbar: { 
                show: true,
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                }
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        colors: ['#2fde37', '#ff0000', '#ffa500', '#1e90ff'],
        dataLabels: { enabled: false },
        stroke: { 
            curve: 'smooth',
            width: 2
        },
        series: [
            {
                name: 'Oil Sales',
                data: monthlyData.sales
            },
            {
                name: 'Fuel Purchases',
                data: monthlyData.fuelPurchases
            },
            {
                name: 'Oil Purchases',
                data: monthlyData.oilPurchases
            },
            {
                name: 'Fuel Sales',
                data: monthlyData.fuelSales
            }
        ],
        xaxis: {
            categories: months,
            title: {
                text: 'Months'
            }
        },
        yaxis: {
            title: {
                text: 'Amount ($)'
            },
            labels: {
                formatter: function(value) {
                    return '$' + value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return '$' + value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right'
        },
        grid: {
            borderColor: '#f1f1f1',
        },
        responsive: [{
            breakpoint: 480,
            options: {
                legend: {
                    position: 'bottom'
                }
            }
        }]
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
                colors = ['#2fde37', '#1e90ff'];
                break;
            case 'purchases':
                seriesData = [
                    { name: 'Fuel Purchases', data: monthlyData.fuelPurchases },
                    { name: 'Oil Purchases', data: monthlyData.oilPurchases }
                ];
                colors = ['#ff0000', '#ffa500'];
                break;
            case 'fuel':
                seriesData = [
                    { name: 'Fuel Sales', data: monthlyData.fuelSales },
                    { name: 'Fuel Purchases', data: monthlyData.fuelPurchases }
                ];
                colors = ['#1e90ff', '#ff0000'];
                break;
            default: // all
                seriesData = [
                    { name: 'Oil Sales', data: monthlyData.sales },
                    { name: 'Fuel Purchases', data: monthlyData.fuelPurchases },
                    { name: 'Oil Purchases', data: monthlyData.oilPurchases },
                    { name: 'Fuel Sales', data: monthlyData.fuelSales }
                ];
                colors = ['#2fde37', '#ff0000', '#ffa500', '#1e90ff'];
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

    // Add counter animation to numbers
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
        
        // Start animation when element is in viewport
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
/* Dashboard Base Layout */
.page-wrapper .content {
    padding: 24px;
    background-color: #f8fafc;
    min-height: calc(100vh - 60px);
}

/* Filter Card */
.dashboard-filter-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    padding: 18px 24px;
    margin-bottom: 24px;
    transition: all 0.3s ease;
}

.dashboard-filter-card:hover {
    box-shadow: 0 6px 24px rgba(37, 99, 235, 0.06);
}

.filter-icon-box {
    width: 44px;
    height: 44px;
    background: #eff6ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    font-size: 1.1rem;
}

.filter-badge {
    background: #eff6ff;
    color: #2563eb;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
}

/* KPI Stat Cards (Soft Background Tints) */
.dash-widget {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 12px !important;
    padding: 14px 16px !important;
    margin-bottom: 16px !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03) !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease !important;
    position: relative;
    overflow: hidden;
}

.dash-widget.widget-blue { background: #f0f7ff !important; border-color: #dbeafe !important; }
.dash-widget.widget-green { background: #f0fdf4 !important; border-color: #dcfce7 !important; }
.dash-widget.widget-amber { background: #fffbe3 !important; border-color: #fef3c7 !important; }
.dash-widget.widget-rose { background: #fff1f2 !important; border-color: #ffe4e6 !important; }
.dash-widget.widget-indigo { background: #eef2ff !important; border-color: #e0e7ff !important; }

.dash-widget:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.08) !important;
}

.dash-widget::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #2563eb;
    border-radius: 4px 0 0 4px;
}

.dash-widget.widget-blue::after { background: #2563eb; }
.dash-widget.widget-green::after { background: #10b981; }
.dash-widget.widget-amber::after { background: #f59e0b; }
.dash-widget.widget-rose::after { background: #f43f5e; }
.dash-widget.widget-indigo::after { background: #6366f1; }

.dash-widgetimg {
    flex-shrink: 0;
}

.dash-widgetimg span {
    width: 42px !important;
    height: 42px !important;
    border-radius: 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: #ffffff !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.dash-widgetimg span img {
    width: 20px !important;
    height: 20px !important;
}

.dash-widgetcontent h5 {
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    margin-bottom: 2px !important;
    letter-spacing: -0.02em;
}

.dash-widgetcontent h6 {
    font-size: 0.78rem !important;
    font-weight: 600 !important;
    color: #475569 !important;
    margin-bottom: 3px !important;
}

.dash-widgetcontent small {
    font-size: 0.7rem !important;
    color: #64748b !important;
    display: inline-block;
    background: rgba(255, 255, 255, 0.8);
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 500;
}

/* Summary Counter Cards (Background Tints) */
.dash-count {
    background: #f0f9ff !important;
    border: 1px solid #e0f2fe !important;
    border-radius: 12px !important;
    padding: 14px 16px !important;
    margin-bottom: 16px !important;
    width: 100%;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    transition: all 0.25s ease !important;
}

.dash-count.das1 { background: #f0fdf4 !important; border-color: #dcfce7 !important; }
.dash-count.das2 { background: #fffbe3 !important; border-color: #fef3c7 !important; }
.dash-count.das3 { background: #fff1f2 !important; border-color: #ffe4e6 !important; }

.dash-count:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.06) !important;
}

.dash-counts h4 {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    margin-bottom: 2px !important;
}

.dash-counts h5 {
    font-size: 0.78rem !important;
    font-weight: 600 !important;
    color: #475569 !important;
    margin: 0 !important;
}

.dash-imgs {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #ffffff !important;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.dash-count.das1 .dash-imgs { color: #10b981; }
.dash-count.das2 .dash-imgs { color: #f59e0b; }
.dash-count.das3 .dash-imgs { color: #f43f5e; }

/* Dashboard Cards (Chart & Recent Products - Light Blue Background) */
.card {
    border: 1px solid #dbeafe !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 20px rgba(37, 99, 235, 0.05) !important;
    background: #f0f7ff !important;
    overflow: hidden;
    margin-bottom: 24px;
}

.card .card-header {
    background: transparent !important;
    border-bottom: 1px solid #dbeafe !important;
    padding: 20px 24px !important;
}

.card .card-header .card-title {
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    color: #1e3a8a !important;
}

.card .card-body {
    padding: 24px !important;
}

#salesPurchasesChart {
    background: #ffffff !important;
    border-radius: 12px;
    padding: 14px;
    border: 1px solid #dbeafe;
}

.table-responsive {
    background: #ffffff !important;
    border-radius: 12px;
    border: 1px solid #dbeafe;
    padding: 4px;
}

/* Modern Dropdowns & Buttons */
.btn-white {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    padding: 6px 14px !important;
    transition: all 0.2s ease !important;
}

.btn-white:hover {
    background-color: #f8fafc !important;
    border-color: #94a3b8 !important;
}

.dropdown-menu {
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
    border-radius: 12px !important;
    padding: 6px !important;
}

.dropdown-item {
    border-radius: 6px !important;
    padding: 8px 14px !important;
    font-weight: 500 !important;
    color: #334155 !important;
    transition: all 0.15s ease !important;
}

.dropdown-item:hover, .dropdown-item.active {
    background-color: #eff6ff !important;
    color: #2563eb !important;
}

.sidebar {
    z-index: 1050 !important;
    position: fixed !important;
}

.page-wrapper .sidebar {
    z-index: 1001 !important;
}
</style>
@endsection
