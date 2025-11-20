@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">

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
