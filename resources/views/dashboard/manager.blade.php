@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">

    <!-- PAGE TITLE -->
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h4>Manager Dashboard</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><i data-feather="home"></i></li>
                    <li class="breadcrumb-item">Dashboard</li>
                    <li class="breadcrumb-item active">Manager</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- STATISTICS -->
    <div class="row">

        <!-- Orders To Assign -->
        <div class="col-xl-4 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-primary border-2">
                    <span class="f-light f-w-500 f-14">Orders To Assign</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $pendingAssignments ?? 0 }}</h2>
                        </div>
                        <div class="product-sub bg-primary-light">
                            <i data-feather="clipboard"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Orders -->
        <div class="col-xl-4 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-success border-2">
                    <span class="f-light f-w-500 f-14">Assigned Orders</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $assignedOrders ?? 0 }}</h2>
                        </div>
                        <div class="product-sub bg-success-light">
                            <i data-feather="check-square"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Field Staff -->
        <div class="col-xl-4 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-info border-2">
                    <span class="f-light f-w-500 f-14">Field Staff</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $fieldStaffCount ?? 0 }}</h2>
                        </div>
                        <div class="product-sub bg-info-light">
                            <i data-feather="users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
