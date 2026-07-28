@extends('admin.admin_master')
@section('admin')
<link rel="stylesheet" href="{{ asset('uniquestyle/style.css') }}">
<div class="page-wrapper">
    <div class="content">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="card-title mb-0">Filter by Month & Year</h5>
                        <small class="text-muted">
                            Currently viewing: <strong id="dashboardFilterLabel"></strong>
                        </small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-center" id="dashboardFilterControls"></div>
                </div>
            </div>
        </div>
    </div>
</div>

        {{-- Low Stock Notification --}}
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'branch-manager' || auth()->user()->role === 'sales' )
            <!--@if(isset($lowStockItems) && count($lowStockItems) > 0)-->
            <!--    <div class="alert alert-danger shadow-sm" role="alert">-->
            <!--        <div class="d-flex justify-content-between align-items-center mb-2">-->
            <!--            <div>-->
            <!--                <strong><i class="fas fa-exclamation-triangle"></i> Low Stock Alert!</strong>-->
            <!--                <span class="ms-2">The following items are below the minimum quantity of 10:</span>-->
            <!--            </div>-->
            <!--            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>-->
            <!--        </div>-->
            <!--        <div style="max-height: 200px; overflow-y: auto;">-->
            <!--            <ul class="list-group">-->
            <!--                @foreach($lowStockItems as $item)-->
            <!--                    <li class="list-group-item d-flex justify-content-between align-items-center">-->
            <!--                        <div>-->
            <!--                            <strong>{{ $item->name }}</strong>-->
            <!--                            <span class="text-muted">(Min: {{ $item->min_quantity ?? 10 }})</span>-->
            <!--                        </div>-->
            <!--                        <span class="badge bg-danger rounded-pill">{{ $item->quantity }} in stock</span>-->
            <!--                    </li>-->
            <!--                @endforeach-->
            <!--            </ul>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--@endif-->

            {{-- Top Widgets --}}
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash1.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalPurchase'], 2) }}</span></h5>
                            <h6>Total Purchases</h6>
                            <small class="text-muted">{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget dash1">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash2.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalFuelPurchase'], 2) }}</span></h5>
                            <h6>Fuel Purchases</h6>
                            <small class="text-muted">{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget dash2">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash3.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalOilPurchase'], 2) }}</span></h5>
                            <h6>Oil Purchases</h6>
                            <small class="text-muted">{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget dash3">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash4.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalSales'], 2) }}</span></h5>
                            <h6>Total Oil Sales</h6>
                            <small class="text-muted">{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Second Row Widgets --}}
            <div class="row mt-3">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget" style="background: #e8f5e8;">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash2.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalAllFuelSales'], 2) }}</span></h5>
                            <h6>Total Fuel Sales</h6>
                            <small class="text-muted">{{ $data['selectedMonthName'] }} {{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget" style="background: #fff3cd;">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash2.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalReceivable'], 2) }}</span></h5>
                            <h6>Total Receivable</h6>
                            <small class="text-muted">All Time</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget" style="background: #f8d7da;">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash2.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5>$<span class="counter">{{ number_format($data['totalPayable'], 2) }}</span></h5>
                            <h6>Total Payable</h6>
                            <small class="text-muted">All Time</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget" style="background: #d1ecf1;">
                        <div class="dash-widgetcontent text-center">
                            <h5 class="text-primary"><span>{{ $data['selectedMonthName'] }}</span></h5>
                            <h6>Selected Month</h6>
                            <small class="text-muted">{{ $data['currentYear'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Counters --}}
        <div class="row mt-4">
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
                        <h5>Sales Invoice</h5>
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
                        <h5>Purchase Invoice</h5>
                    </div>
                    <div class="dash-imgs">
                        <i data-feather="file"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Graph and Recent Products --}}
        <div class="row mt-4">
            {{-- Chart --}}
            <div class="col-lg-7 col-sm-12 col-12 d-flex">
                <div class="card flex-fill w-100">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Monthly Sales & Purchases - {{ $data['currentYear'] }}</h5>
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
                    <div class="card-body" style="padding: 20px;">
                        <div id="salesPurchasesChart" style="height: 400px !important;"></div>
                    </div>
                </div>
            </div>

            {{-- Recently Added Products --}}
            <div class="col-lg-5 col-sm-12 col-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Recently Added Products</h4>
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
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Products</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data['products'] as $product)
                                        <tr>
                                            <!--<td>-->
                                            <!--    <div class="product-info">-->
                                            <!--        <strong>{{ $product->name }}</strong>-->
                                            <!--        @if($product->quantity < 10)-->
                                            <!--            <small class="text-danger d-block">Low Stock</small>-->
                                            <!--        @endif-->
                                            <!--    </div>-->
                                            <!--</td>-->
                                            <!--<td>$ {{ number_format($product->selling_price, 2) }}</td>-->
                                            <!--<td>-->
                                            <!--    @if($product->quantity < 10)-->
                                            <!--        <span class="badge bg-warning text-dark d-inline-block text-center" style="min-width:50px;">{{ $product->quantity }}</span>-->
                                            <!--    @else-->
                                            <!--        <span class="badge bg-success text-white d-inline-block text-center" style="min-width:50px;">{{ $product->quantity }}</span>-->
                                            <!--    @endif-->
                                            <!--</td>-->
                                            <!--<td>-->
                                            <!--    @if($product->quantity < 10)-->
                                            <!--        <span class="badge bg-danger">Low</span>-->
                                            <!--    @elseif($product->quantity < 20)-->
                                            <!--        <span class="badge bg-warning">Medium</span>-->
                                            <!--    @else-->
                                            <!--        <span class="badge bg-success">Good</span>-->
                                            <!--    @endif-->
                                            <!--</td>-->
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No products found</td>
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
.dash-widget {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.dash-widget:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.dash-count {
    transition: all 0.3s ease;
}
.dash-count:hover {
    transform: scale(1.05);
}
.summary-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}
.summary-card:hover {
    transform: translateY(-3px);
}
.summary-icon {
    font-size: 2rem;
    opacity: 0.8;
}
.product-info {
    max-width: 150px;
}
.badge {
    font-size: 0.75em;
}
.table-responsive {
    border-radius: 8px;
}
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.page-wrapper {
    position: relative;
    z-index: 1;
}

.sidebar {
    z-index: 1050 !important;
    position: fixed !important;
}

.main-content {
    position: relative;
    z-index: 1;
}

/* Ensure sidebar is above content */
.sidebar-area {
    z-index: 1000 !important;
}

.page-wrapper .sidebar {
    z-index: 1001 !important;
}
</style>
@endsection
