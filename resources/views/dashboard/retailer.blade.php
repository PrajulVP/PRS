@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">

    <!-- PAGE TITLE -->
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h4>Retailer Dashboard</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><i data-feather="home"></i></li>
                    <li class="breadcrumb-item">Dashboard</li>
                    <li class="breadcrumb-item active">Retailer</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div class="row">

        <!-- My Orders -->
        <div class="col-xl-6 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-info border-2">
                    <span class="f-light f-w-500 f-14">My Orders</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $myOrders ?? 0 }}</h2>
                        </div>
                        <div class="product-sub bg-info-light">
                            <i data-feather="package"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loyalty Points -->
        <div class="col-xl-6 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-warning border-2">
                    <span class="f-light f-w-500 f-14">Loyalty Points</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $loyaltyPoints ?? 0 }}</h2>
                        </div>
                        <div class="product-sub bg-warning-light">
                            <i data-feather="award"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
