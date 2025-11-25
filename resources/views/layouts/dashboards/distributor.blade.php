@extends('layouts.admin')

@section('page-body')

<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row size-column">
        <div class="col-xxl-12 box-col-12">
            <div class="row">

                <!-- Total Orders -->
                <div class="col-xl-6 col-sm-6">
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