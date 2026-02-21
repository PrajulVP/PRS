@extends('layouts.admin')

@section('page-body')

    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row size-column">
            <div class="col-xxl-12 box-col-12">
                <!-- Dashboard Content -->
                @php
                    $user = auth()->user();
                @endphp

                <style>
                    .med-widget-card {
                        background: linear-gradient(var(--med-primary) 0%, var(--med-accent) 100%) !important;
                        transition: transform 0.3s ease, box-shadow 0.3s ease;
                    }

                    .med-widget-card:hover {
                        transform: translateY(-5px);
                        box-shadow: 0 10px 20px rgba(0, 73, 122, 0.3) !important;
                    }

                    .med-widget-card .card-body {
                        background: transparent !important;
                    }

                    .med-widget-card .media-body span,
                    .med-widget-card .media-body h4,
                    .med-widget-card i {
                        color: #ffffff !important;
                    }
                </style>
                <!-- Dashboard Content for All Roles -->
                <div class="row">
                    <!-- Count Cards -->
                    <div class="col-sm-6 col-xl-3 col-lg-6">
                        <div class="card o-hidden border-0 med-widget-card" style="cursor: pointer;"
                            onclick="window.location.href='{{ Auth::user()->hasRole(['admin', 'superadmin', 'salesmanager']) ? route('admin.distributors.index') : '#' }}'">
                            <div class="b-r-4 card-body">
                                <div class="media static-top-widget">
                                    <div class="align-self-center text-center text-white"><i data-feather="users"></i></div>
                                    <div class="media-body m-2"><span class="m-0">Distributors</span>
                                        <h4 class="mb-0 counter">{{ $counts['distributors'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 col-lg-6">
                        <div class="card o-hidden border-0 med-widget-card med-widget-card" style="cursor: pointer;"
                            onclick="window.location.href='{{ Auth::user()->hasRole(['admin', 'superadmin', 'salesmanager']) ? route('admin.retailers.index') : '#' }}'">
                            <div class="b-r-4 card-body">
                                <div class="media static-top-widget">
                                    <div class="align-self-center text-center text-white px-2"><i
                                            data-feather="shopping-bag"></i></div>
                                    <div class="media-body m-2"><span class="m-0">Retailers</span>
                                        <h4 class="mb-0 counter">{{ $counts['retailers'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 col-lg-6">
                        <div class="card o-hidden border-0 med-widget-card" style="cursor: pointer;"
                            onclick="window.location.href='{{ route('products.index') }}'">
                            <div class="b-r-4 card-body">
                                <div class="media static-top-widget">
                                    <div class="align-self-center text-center text-white px-2"><i data-feather="box"></i>
                                    </div>
                                    <div class="media-body m-2"><span class="m-0">Products</span>
                                        <h4 class="mb-0 counter">{{ $counts['products'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 col-lg-6">
                        <div class="card o-hidden border-0 med-widget-card med-widget-card" style="cursor: pointer;"
                            onclick="window.location.href='{{ Auth::user()->hasRole(['admin', 'superadmin']) ? route('admin.retailer-orders.index') : (Auth::user()->hasRole('distributor') ? route('distributor.orders.index') : (Auth::user()->hasRole('retailer') ? route('retailer.orders.index') : '#')) }}'">
                            <div class="b-r-4 card-body">
                                <div class="media static-top-widget">
                                    <div class="align-self-center text-center text-white px-2"><i
                                            data-feather="file-text"></i></div>
                                    <div class="media-body m-2"><span class="m-0">Total Orders</span>
                                        <h4 class="mb-0 counter">{{ $retailerOrderStats['total'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Charts -->
                    <div class="col-xl-8 col-lg-12 box-col-6 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Monthly Retailer Orders</h5>
                            </div>
                            <div class="card-body">
                                <div id="monthlyOrdersChart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-12 box-col-6 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Order Status</h5>
                            </div>
                            <div class="card-body">
                                <div id="orderStatusChart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 box-col-12 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Retailer Orders</h5>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Retailer</th>
                                                <th>Date</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentRetailerOrders as $order)
                                                <tr>
                                                    <td>#{{ $order->id }}</td>
                                                    <td>{{ $order->retailer->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                                    <td>{{ number_format($order->total_amount, 2) }}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $order->status == 'delivered' ? 'badge-success' : ($order->status == 'cancelled' ? 'badge-danger' : 'badge-primary') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No recent orders found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scripts for Charts -->
                @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            // Monthly Orders Line/Bar Chart
                            var monthlyOptions = {
                                series: [{
                                    name: "Orders",
                                    data: @json($chartData['counts'])
                                }],
                                chart: {
                                    height: 350,
                                    type: 'area', // Area chart looks nicer
                                    toolbar: {
                                        show: false
                                    }
                                },
                                dataLabels: {
                                    enabled: false
                                },
                                stroke: {
                                    curve: 'smooth'
                                },
                                xaxis: {
                                    categories: @json($chartData['months'])
                                },
                                colors: ['#00497a'], // Deep corporate blue primary color
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.7,
                                        opacityTo: 0.9,
                                        stops: [0, 90, 100]
                                    }
                                }
                            };

                            var monthlyChart = new ApexCharts(document.querySelector("#monthlyOrdersChart"), monthlyOptions);
                            monthlyChart.render();

                            // Order Status Donut Chart
                            var statusOptions = {
                                series: [{
                                                        {
                                    $retailerOrderStats['pending']
                                                        }
                                                    },
                                {
                                                        {
                                $retailerOrderStats['approved']
                            }
                        },
                            {
                                                        {
                                $retailerOrderStats['delivered']
                                                        }
                                                    },
                            {
                                                        {
                                $retailerOrderStats['cancelled']
                                                        }
                                                    }
                                                ],
                            labels: ['Pending', 'Approved', 'Delivered', 'Cancelled'],
                            chart: {
                            type: 'donut',
                            height: 350
                        },
                            colors: ['#f59e0b', '#00497a', '#10b981', '#ef4444'], // Corporate navy theme colors
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: 200
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }]
                                            };


                        var statusChart = new ApexCharts(document.querySelector("#orderStatusChart"), statusOptions);
                        statusChart.render();
                                        });
                    </script>
                @endpush

                <!-- End Dashboard Content -->

            </div>
        </div>
    </div>

@endsection