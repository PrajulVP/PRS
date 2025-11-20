@extends('layouts.admin')

@section('page-body')

<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row size-column">
        <div class="col-xxl-12 box-col-12">
            <div class="row">

                <!-- Total Users -->
                <div class="col-xl-4 col-sm-6">
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
                            <ul class="bubbles">
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Total Products -->
                <div class="col-xl-4 col-sm-6">
                    <div class="card o-hidden small-widget">
                        <div class="card-body total-Progress border-b-warning border-2">
                            <span class="f-light f-w-500 f-14">Total Products</span>
                            <div class="project-details">
                                <div class="project-counter">
                                    <h2 class="f-w-600">{{ $totalProducts }}</h2>
                                </div>
                                <div class="product-sub bg-warning-light">
                                    <svg class="invoice-icon">
                                        <use href="{{ asset('admin/assets/svg/icon-sprite.svg#tick-circle') }}"></use>
                                    </svg>
                                </div>
                            </div>
                            <ul class="bubbles">
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="col-xl-4 col-sm-6">
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
                            <ul class="bubbles">
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Overall Sales Target -->
                <div class="col-xl-4 col-sm-6">
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
                            <ul class="bubbles">
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Overall Achieved Sales -->
                <div class="col-xl-4 col-sm-6">
                    <div class="card o-hidden small-widget">
                        <div class="card-body total-Progress border-b-success border-2">
                            <span class="f-light f-w-500 f-14">Overall Achieved Sales</span>
                            <div class="project-details">
                                <div class="project-counter">
                                    <h2 class="f-w-600">re{{ number_format($overallAchievedAmount, 2) }}</h2>
                                </div>
                                <div class="product-sub bg-success-light">
                                    <svg class="invoice-icon">
                                        <use href="{{ asset('admin/assets/svg/icon-sprite.svg#check-circle') }}"></use>
                                    </svg>
                                </div>
                            </div>
                            <ul class="bubbles">
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                                <li class="bubble"></li><li class="bubble"></li><li class="bubble"></li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="row size-column">
        <div class="col-xxl-12 box-col-12">
            <div class="row">
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
                        <h5>Orders by Distributor</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="ordersByDistributorChart"></canvas>
                    </div>
                </div>
            </div>

                <div class="col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top 10 Retailers by Order Value</h5>
                        </div>
                        <div class="card-body">
                            <ul id="topRetailersList" class="list-group">
                                <!-- Data will be loaded here by JavaScript -->
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top 10 Distributors by Order Value</h5>
                        </div>
                        <div class="card-body">
                            <ul id="topDistributorsList" class="list-group">
                                <!-- Data will be loaded here by JavaScript -->
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top 10 Users by Credit</h5>
                        </div>
                        <div class="card-body">
                            <ul id="usersByCreditList" class="list-group">
                                <!-- Data will be loaded here by JavaScript -->
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top 10 Users by Loyalty Points</h5>
                        </div>
                        <div class="card-body">
                            <ul id="usersByLoyaltyPointsList" class="list-group">
                                <!-- Data will be loaded here by JavaScript -->
                            </ul>
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
    // Function to fetch data and render a pie chart
    function renderPieChart(chartId, title, apiUrl) {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                const labels = Object.keys(data);
                const values = Object.values(data);

                new Chart(document.getElementById(chartId), {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: title,
                            data: values,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                                'rgba(255, 159, 64, 0.7)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: title
                            }
                        }
                    }
                });
            })
            .catch(error => console.error(`Error fetching ${title} data:`, error));
    }

    // Function to fetch data and render a bar chart
    function renderBarChart(chartId, title, apiUrl) {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                const labels = Object.keys(data);
                const values = Object.values(data);

                new Chart(document.getElementById(chartId), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: title,
                            data: values,
                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: title
                            },
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            })
            .catch(error => console.error(`Error fetching ${title} data:`, error));
    }

    // Function to fetch data and render a list
    function renderList(listId, apiUrl, keyField, valueField) {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                const listElement = document.getElementById(listId);
                listElement.innerHTML = ''; // Clear existing items
                if (data.length === 0) {
                    listElement.innerHTML = '<li class="list-group-item">No data available.</li>';
                    return;
                }
                data.forEach(item => {
                    const listItem = document.createElement('li');
                    listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                    listItem.textContent = item[keyField];
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-primary rounded-pill';
                    badge.textContent = item[valueField];
                    listItem.appendChild(badge);
                    listElement.appendChild(listItem);
                });
            })
            .catch(error => console.error(`Error fetching list data for ${listId}:`, error));
    }

    // Admin Charts
    renderPieChart('orderStatusDistributionChart', 'Order Status Distribution', '{{ route('dashboard.api.orderStatusDistribution') }}');
    renderBarChart('ordersByDistributorChart', 'Orders by Distributor', '{{ route('dashboard.api.ordersByDistributor') }}');

    // Admin Lists
    renderList('topRetailersList', '{{ route('dashboard.api.topRetailers') }}', 'retailer_name', 'total_order_value');
    renderList('topDistributorsList', '{{ route('dashboard.api.topDistributors') }}', 'distributor_name', 'total_order_value');
    renderList('usersByCreditList', '{{ route('dashboard.api.usersByCredit') }}', 'name', 'credit');
    renderList('usersByLoyaltyPointsList', '{{ route('dashboard.api.usersByLoyaltyPoints') }}', 'name', 'loyalty_points');
});
</script>