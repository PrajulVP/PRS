@extends('layouts.admin')

@section('page-body')

  @if(auth()->guard('web')->check())
                                @if(auth()->guard('web')->user()->role === 'superadmin')      <div class="container-fluid">
        <div class="page-title">
          <div class="row">
            <div class="col-6">
              <h4>
                Project Management </h4>
            </div>
            <div class="col-6">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">
                    <svg class="stroke-icon">
                      <use href="../../assets/svg/icon-sprite.svg#stroke-home"></use>
                    </svg></a></li>
                <li class="breadcrumb-item">Dashboard</li>
                <li class="breadcrumb-item active">Project-Management</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <!-- Container-fluid starts-->
      <div class="container-fluid">
        <div class="row size-column">
          <div class="col-xxl-12 box-col-12">
            <div class="row">
              <div class="col-xl-3 col-sm-6">
                <div class="card o-hidden small-widget">
                  <div class="card-body total-project border-b-primary border-2"><span class="f-light f-w-500 f-14">Total
                      Project</span>
                    <div class="project-details">
                      <div class="project-counter">
                        <h2 class="f-w-600">1,523</h2><span class="f-12 f-w-400">(This month)</span>
                      </div>
                      <div class="product-sub bg-primary-light">
                        <svg class="invoice-icon">
                          <use href="../../assets/svg/icon-sprite.svg#color-swatch"></use>
                        </svg>
                      </div>
                    </div>
                    <ul class="bubbles">
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6">
                <div class="card o-hidden small-widget">
                  <div class="card-body total-Progress border-b-warning border-2"> <span class="f-light f-w-500 f-14">In
                      Progress</span>
                    <div class="project-details">
                      <div class="project-counter">
                        <h2 class="f-w-600">836</h2><span class="f-12 f-w-400">(This month) </span>
                      </div>
                      <div class="product-sub bg-warning-light">
                        <svg class="invoice-icon">
                          <use href="../../assets/svg/icon-sprite.svg#tick-circle"></use>
                        </svg>
                      </div>
                    </div>
                    <ul class="bubbles">
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6">
                <div class="card o-hidden small-widget">
                  <div class="card-body total-Complete border-b-secondary border-2"><span
                      class="f-light f-w-500 f-14">Complete</span>
                    <div class="project-details">
                      <div class="project-counter">
                        <h2 class="f-w-600">475</h2><span class="f-12 f-w-400">(This month) </span>
                      </div>
                      <div class="product-sub bg-secondary-light">
                        <svg class="invoice-icon">
                          <use href="../../assets/svg/icon-sprite.svg#add-square"></use>
                        </svg>
                      </div>
                    </div>
                    <ul class="bubbles">
                      <li class="bubble"> </li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"> </li>
                      <li class="bubble"></li>
                      <li class="bubble"> </li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"> </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6">
                <div class="card o-hidden small-widget">
                  <div class="card-body total-upcoming"><span class="f-light f-w-500 f-14">Upcoming</span>
                    <div class="project-details">
                      <div class="project-counter">
                        <h2 class="f-w-600">189</h2><span class="f-12 f-w-400">(This month) </span>
                      </div>
                      <div class="product-sub bg-light-light">
                        <svg class="invoice-icon">
                          <use href="../../assets/svg/icon-sprite.svg#edit-2"></use>
                        </svg>
                      </div>
                    </div>
                    <ul class="bubbles">
                      <li class="bubble"> </li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                      <li class="bubble"></li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="col-md-6">

              </div>
              <div class="col-md-6">

              </div>
              <div class="col-xl-7 box-col-6">
                <div class="card">

                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- <div class="col-md-4 p-3">
                      <h5>Target vs Achievement</h5>
                      <canvas id="targetChart" width="400" height="200"></canvas> {{-- ✅ THIS GOES IN BODY --}}
                  </div> -->
      </div>
                                @endif
    
                                @if(auth()->guard('web')->user()->role === 'admin')
                                    <p>As an Admin, you can manage users (excluding superadmin), orders, and other operational tasks.</p>
                                    <a href="{{ route('admin.users') }}" class="btn btn-info">View Users</a>
                                    {{-- Add more admin specific links/content here --}}
                                @endif
    
                                @if(auth()->guard('web')->user()->role === 'manager')
                                    <p>As a Manager, you can oversee and assign orders.</p>
                                    <a href="{{ route('manager.orders.index') }}" class="btn btn-info">View Pending Orders</a>
                                @endif
    
                                @if(auth()->guard('web')->user()->role === 'distributor')
                                    <p>As a Distributor, you can manage assigned orders and assign them to field staff.</p>
                                    <a href="{{ route('distributor.orders.index') }}" class="btn btn-info">View Assigned Orders</a>
                                @endif
    
                                @if(auth()->guard('web')->user()->role === 'fieldstaff')
                                    <p>As a Field Staff, you can manage your assigned orders and update their delivery status.</p>
                                    <a href="{{ route('fieldstaff.orders.index') }}" class="btn btn-info">View Assigned Orders</a>
                                @endif
    
                                @if(auth()->guard('web')->user()->role === 'retailer')
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Your Dashboard</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p>As a Retailer, you can send medicine requirements to the company and view the status of your orders.</p>
                                                        <a href="{{ route('orders.create') }}" class="btn btn-primary mb-3">Send Medicine Requirement</a>

                                                        <h6>Your Current Orders</h6>
                                                        <p>Orders will be displayed here with their delivery status.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
    
                                <hr>
    
                                <!-- {{-- Example of permission-based content --}}
                                @if(auth()->guard('web')->user()->can('view orders'))
                                   
                                    <a href="/orders" class="btn btn-success">View Orders</a>
                                @endif
    
                                @if(auth()->guard('web')->user()->can('create orders'))
                                   
                                    <a href="/orders/create" class="btn btn-warning">Create New Order</a>
                                @endif -->
  @else
    <p>Please log in to access the dashboard features.</p>
  @endif

  </div>
  </div>
  </div>
  </div>
  <!-- Container-fluid Ends-->
@endsection