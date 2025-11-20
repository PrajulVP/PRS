@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">

    <!-- STATISTICS -->
    <div class="row">

        <!-- Users -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-primary border-2">
                    <span class="f-light f-w-500 f-14">Total Users</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $totalUsers ?? 0 }}</h2>
                            <span class="f-12 f-w-400">System Users</span>
                        </div>
                        <div class="product-sub bg-primary-light">
                            <i data-feather="users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roles -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-success border-2">
                    <span class="f-light f-w-500 f-14">Roles</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $totalRoles ?? 0 }}</h2>
                            <span class="f-12 f-w-400">Active Roles</span>
                        </div>
                        <div class="product-sub bg-success-light">
                            <i data-feather="shield"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body border-b-warning border-2">
                    <span class="f-light f-w-500 f-14">Permissions</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $totalPermissions ?? 0 }}</h2>
                            <span class="f-12 f-w-400">Access Rules</span>
                        </div>
                        <div class="product-sub bg-warning-light">
                            <i data-feather="lock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
