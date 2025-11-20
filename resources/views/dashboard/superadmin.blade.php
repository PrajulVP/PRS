@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">

    <!-- TOP STATISTICS -->
    <div class="row">

        <!-- Total Orders -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-primary border-2">
                    <span class="f-light f-w-500 f-14">Total Orders</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $totalOrders ?? 0 }}</h2>
                            <span class="f-12 f-w-400">This Month</span>
                        </div>
                        <div class="product-sub bg-primary-light">
                            <i data-feather="shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-success border-2">
                    <span class="f-light f-w-500 f-14">Completed Orders</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $completedOrders ?? 0 }}</h2>
                            <span class="f-12 f-w-400">This Month</span>
                        </div>
                        <div class="product-sub bg-success-light">
                            <i data-feather="check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-warning border-2">
                    <span class="f-light f-w-500 f-14">Pending Orders</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $pendingOrders ?? 0 }}</h2>
                            <span class="f-12 f-w-400">Awaiting Approval</span>
                        </div>
                        <div class="product-sub bg-warning-light">
                            <i data-feather="clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loyalty Points -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-info border-2">
                    <span class="f-light f-w-500 f-14">Total Loyalty Points</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $totalLoyaltyPoints ?? 0 }}</h2>
                            <span class="f-12 f-w-400">Earned This Month</span>
                        </div>
                        <div class="product-sub bg-info-light">
                            <i data-feather="award"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- TOP CLIENTS -->
    <div class="row mt-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Top Distributors</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Top Retailers</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                       
                    </ul>
                </div>
            </div>
        </div>

    </div>

    <!-- LOYALTY POINT LEADERS -->
    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5>Top Loyalty Point Customers</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Loyalty Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection