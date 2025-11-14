@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">

    <!-- PAGE TITLE -->
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h4>Field Staff Dashboard</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><i data-feather="home"></i></li>
                    <li class="breadcrumb-item">Dashboard</li>
                    <li class="breadcrumb-item active">Field Staff</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div class="row">

        <!-- Assigned Orders -->
        <div class="col-xl-6 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-primary border-2">
                    <span class="f-light f-w-500 f-14">Assigned Orders</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $assignedOrders ?? 0 }}</h2>
                        </div>
                        <div class="product-sub bg-primary-light">
                            <i data-feather="file-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivered -->
        <div class="col-xl-6 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-success border-2">
                    <span class="f-light f-w-500 f-14">Delivered</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $delivered ?? 0 }}</h2>
                        </div>
                        <div class="product-sub bg-success-light">
                            <i data-feather="check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
