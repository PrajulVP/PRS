@extends('layouts.admin')

@section('page-body')

<div class="page-title">
    <div class="row">
        <div class="col-6">
            <h4>Project Management</h4>
        </div>
        <div class="col-6">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">
                    <svg class="stroke-icon">
                      <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                    </svg>
                </a></li>
                <li class="breadcrumb-item">Dashboard</li>
                <li class="breadcrumb-item active">Project-Management</li>
            </ol>
        </div>
    </div>
</div>

<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row size-column">
        <div class="col-xxl-12 box-col-12">
            <div class="row">

                <!-- Total Users -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card o-hidden small-widget">
                        <div class="card-body total-project border-b-primary border-2">
                            <span class="f-light f-w-500 f-14">Total Users</span>
                            <div class="project-details">
                                <div class="project-counter">
                                    <h2 class="f-w-600">{{ $totalUsers }}</h2>
                                </div>
                                <div class="product-sub bg-primary-light">
                                    <svg class="invoice-icon">
                                        <use href="{{ asset('admin/assets/svg/icon-sprite.svg#color-swatch') }}"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Roles -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card o-hidden small-widget">
                        <div class="card-body total-Progress border-b-warning border-2">
                            <span class="f-light f-w-500 f-14">Total Roles</span>
                            <div class="project-details">
                                <div class="project-counter">
                                    <h2 class="f-w-600">{{ $totalRoles }}</h2>
                                </div>
                                <div class="product-sub bg-warning-light">
                                    <svg class="invoice-icon">
                                        <use href="{{ asset('admin/assets/svg/icon-sprite.svg#tick-circle') }}"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Products -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card o-hidden small-widget">
                        <div class="card-body total-upcoming">
                            <span class="f-light f-w-500 f-14">Total Products</span>
                            <div class="project-details">
                                <div class="project-counter">
                                    <h2 class="f-w-600">{{ $totalProducts }}</h2>
                                </div>
                                <div class="product-sub bg-warning-light">
                                    <svg class="invoice-icon">
                                        <use href="{{ asset('admin/assets/svg/icon-sprite.svg#edit-2') }}"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card o-hidden small-widget">
                        <div class="card-body total-Complete border-b-secondary border-2">
                            <span class="f-light f-w-500 f-14">Total Orders</span>
                            <div class="project-details">
                                <div class="project-counter">
                                    <h2 class="f-w-600">{{ $totalOrders }}</h2>
                                </div>
                                <div class="product-sub bg-secondary-light">
                                    <svg class="invoice-icon">
                                        <use href="{{ asset('admin/assets/svg/icon-sprite.svg#add-square') }}"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Sales Target -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card o-hidden small-widget">
                        <div class="card-body total-project border-b-primary border-2">
                            <span class="f-light f-w-500 f-14">Overall Sales Target</span>
                            <div class="project-details">
                                <div class="project-counter">
                                    <h2 class="f-w-600">{{ number_format($overallTargetAmount, 2) }}</h2>
                                </div>
                                <div class="product-sub bg-primary-light">
                                    <svg class="invoice-icon">
                                        <use href="{{ asset('admin/assets/svg/icon-sprite.svg#dollar-sign') }}"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Achieved Sales -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card o-hidden small-widget">
                        <div class="card-body total-Progress border-b-success border-2">
                            <span class="f-light f-w-500 f-14">Overall Achieved Sales</span>
                            <div class="project-details">
                                <div class="project-counter">
                                    <h2 class="f-w-600">{{ number_format($overallAchievedAmount, 2) }}</h2>
                                </div>
                                <div class="product-sub bg-success-light">
                                    <svg class="invoice-icon">
                                        <use href="{{ asset('admin/assets/svg/icon-sprite.svg#check-circle') }}"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- CHARTS ROW -->
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Order Status Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px;">
                                    <canvas id="orderStatusDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Orders by District</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="ordersByDistrictChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-12 box-col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Total Orders Over Time</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="totalOrdersOverTimeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- LISTS -->
                <div class="col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top 10 Retailers by Order Value</h5>
                        </div>
                        <div class="card-body">
                            <ul id="topRetailersList" class="list-group"></ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top 10 Distributors by Order Value</h5>
                        </div>
                        <div class="card-body">
                            <ul id="topDistributorsList" class="list-group"></ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top 10 Users by Credit</h5>
                        </div>
                        <div class="card-body">
                            <ul id="usersByCreditList" class="list-group"></ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top 10 Users by Loyalty Points</h5>
                        </div>
                        <div class="card-body">
                            <ul id="usersByLoyaltyPointsList" class="list-group"></ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // PIE CHART
    function renderPieChart(chartId, title, apiUrl) {
        fetch(apiUrl)
            .then(r => r.json())
            .then(data => {
                new Chart(document.getElementById(chartId), {
                    type: 'pie',
                    data: {
                        labels: Object.keys(data),
                        datasets: [{
                            data: Object.values(data),
                            backgroundColor: [
                                'rgba(255,99,132,0.7)',
                                'rgba(54,162,235,0.7)',
                                'rgba(255,206,86,0.7)',
                                'rgba(75,192,192,0.7)'
                            ]
                        }]
                    }
                });
            });
    }

    // BAR CHART
    function renderBarChart(chartId, title, apiUrl) {
        fetch(apiUrl)
            .then(r => r.json())
            .then(data => {
                new Chart(document.getElementById(chartId), {
                    type: 'bar',
                    data: {
                        labels: Object.keys(data),
                        datasets: [{
                            label: title,
                            data: Object.values(data),
                            backgroundColor: 'rgba(75,192,192,0.7)'
                        }]
                    }
                });
            });
    }

    // LINE CHART
    function renderLineChart(chartId, title, apiUrl) {
        fetch(apiUrl)
            .then(r => r.json())
            .then(data => {
                new Chart(document.getElementById(chartId), {
                    type: 'line',
                    data: {
                        labels: Object.keys(data),
                        datasets: [{
                            label: title,
                            data: Object.values(data),
                            borderColor: 'rgb(75,192,192)',
                            tension: 0.2
                        }]
                    }
                });
            });
    }

    // LISTS
    function renderList(listId, apiUrl, keyField, valueField) {
        fetch(apiUrl)
            .then(r => r.json())
            .then(items => {
                const list = document.getElementById(listId);
                list.innerHTML = '';

                if (!items.length) {
                    list.innerHTML = '<li class="list-group-item">No data available</li>';
                    return;
                }

                items.forEach(item => {
                    list.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between">
                            ${item[keyField]}
                            <span class="badge bg-primary">${item[valueField]}</span>
                        </li>
                    `;
                });
            });
    }

    // CHARTS
    renderPieChart('orderStatusDistributionChart', 'Order Status Distribution', '{{ route('dashboard.api.orderStatusDistribution') }}');
    renderBarChart('ordersByDistrictChart', 'Orders by District', '{{ route('dashboard.api.ordersByDistrict') }}');
    renderLineChart('totalOrdersOverTimeChart', 'Total Orders Over Time', '{{ route('dashboard.api.totalOrdersOverTime') }}');

    // LISTS
    renderList('topRetailersList', '{{ route('dashboard.api.topRetailers') }}', 'retailer_name', 'total_order_value');
    renderList('topDistributorsList', '{{ route('dashboard.api.topDistributors') }}', 'distributor_name', 'total_order_value');
    renderList('usersByCreditList', '{{ route('dashboard.api.usersByCredit') }}', 'name', 'credit');
    renderList('usersByLoyaltyPointsList', '{{ route('dashboard.api.usersByLoyaltyPoints') }}', 'name', 'loyalty_points');

});
</script>
@endpush
