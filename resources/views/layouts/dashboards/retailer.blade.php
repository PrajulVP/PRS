@extends('layouts.admin')

@section('page-body')

<div class="page-title">
    <div class="row">
        <div class="col-6">
            <h4>Retailer Dashboard</h4>
        </div>
        <div class="col-6">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                        </svg>
                    </a>
                </li>
                <li class="breadcrumb-item">Dashboard</li>
                <li class="breadcrumb-item active">Retailer</li>
            </ol>
        </div>
    </div>
</div>

<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row size-column">
        <div class="col-xxl-12 box-col-12">
            <div class="row">

                <!-- Total Orders -->
                <div class="col-xl-12 col-sm-12">
                    <div class="card o-hidden small-widget">
                        <div class="card-body total-project border-b-primary border-2">
                            <span class="f-light f-w-500 f-14">Total Orders</span>
                            <div class="project-details">
                                <div class="project-counter">
                                    <h2 class="f-w-600">{{ $totalOrders }}</h2>
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

            </div>
        </div>
    </div>
</div>

@endsection