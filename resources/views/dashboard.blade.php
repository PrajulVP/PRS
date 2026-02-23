@extends('layouts.admin')

@section('page-body')
<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row size-column">
        <div class="col-xxl-12 box-col-12">
            
            <style>
                .med-widget-card {
                    background: linear-gradient(135deg, var(--med-primary) 0%, var(--med-accent) 100%) !important;
                    transition: all 0.3s ease;
                    border-radius: 12px;
                    overflow: hidden;
                    position: relative;
                }
                .med-widget-card::after {
                    content: '';
                    position: absolute;
                    top: -50%;
                    right: -50%;
                    width: 100%;
                    height: 100%;
                    background: rgba(255,255,255,0.05);
                    transform: rotate(30deg);
                    pointer-events: none;
                }
                .med-widget-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 15px 30px rgba(0, 73, 122, 0.4) !important;
                }
                .med-widget-card .card-body {
                    background: transparent !important;
                    position: relative;
                    z-index: 1;
                }
                .med-widget-card .media-body span,
                .med-widget-card .media-body h4,
                .med-widget-card i, .med-widget-card svg {
                    color: #ffffff !important;
                    stroke: #ffffff !important;
                }
                .med-widget-card i, .med-widget-card svg {
                    font-size: 2.5rem;
                    opacity: 0.8;
                }
                
                .retailer-hero {
                    background: linear-gradient(to right, #ffffff, #f0f7fb);
                    border-radius: 15px;
                    padding: 30px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                    margin-bottom: 30px;
                    border-left: 5px solid var(--med-accent);
                }
                .section-title {
                    font-weight: 700;
                    color: var(--med-primary);
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #edf2f9;
                }


            </style>

            @php $user = auth()->user(); @endphp

            {{-- ========================================== --}}
            {{-- RETAILER DASHBOARD (Clients - Attractive!) --}}
            {{-- ========================================== --}}
            @if(Auth::user()->hasRole('retailer'))
                <div class="retailer-hero d-flex align-items-center justify-content-between">
                    <div>
                        <h2>Welcome back, <span style="color: var(--med-primary)">{{ $user->name }}</span>!</h2>
                        <p class="text-muted f-16 mb-0 mt-2">Manage your orders, track your loyalty points, and explore our catalog from your dashboard.</p>
                    </div>
                    <div class="text-center p-3 rounded" style="background: rgba(0,73,122,0.1);">
                        <h6 class="mb-1" style="color: var(--med-primary)"><i data-feather="award" class="mr-2"></i>My Loyalty Points</h6>
                        <h2 class="mb-0" style="color: var(--med-accent); font-weight:800;">{{ number_format($totalLoyaltyPoints) }}</h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-sm-6 mb-4">
                        <div class="card med-widget-card border-0 h-100" onclick="window.location.href='{{ route('retailer.orders.index') }}'" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="media align-items-center static-top-widget">
                                    <div class="media-body m-2">
                                        <span class="m-0 text-uppercase font-weight-bold">Total Orders</span>
                                        <h4 class="mb-0 counter mt-2">{{ $retailerOrderStats['total'] }}</h4>
                                    </div>
                                    <i data-feather="shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-4">
                        <div class="card border-0 h-100" style="background:#fff; border-left: 5px solid #f59e0b !important; box-shadow: 0 4px 10px rgba(0,0,0,0.05); cursor: pointer;" onclick="window.location.href='{{ route('retailer.orders.index') }}'">
                            <div class="card-body">
                                <div class="media">
                                    <div class="media-body">
                                        <p class="mb-1 text-muted font-weight-bold text-uppercase">Pending</p>
                                        <h3 class="mb-0" style="color: #f59e0b">{{ $retailerOrderStats['pending'] }}</h3>
                                    </div>
                                    <div class="align-self-center"><i data-feather="clock" style="color: #f59e0b; width:40px; height:40px;"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-4">
                        <div class="card border-0 h-100" style="background:#fff; border-left: 5px solid #10b981 !important; box-shadow: 0 4px 10px rgba(0,0,0,0.05); cursor: pointer;" onclick="window.location.href='{{ route('retailer.orders.index') }}'">
                            <div class="card-body">
                                <div class="media">
                                    <div class="media-body">
                                        <p class="mb-1 text-muted font-weight-bold text-uppercase">Delivered</p>
                                        <h3 class="mb-0" style="color: #10b981">{{ $retailerOrderStats['delivered'] }}</h3>
                                    </div>
                                    <div class="align-self-center"><i data-feather="check-circle" style="color: #10b981; width:40px; height:40px;"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-4">
                        <div class="card border-0 h-100" style="background:#fff; border-left: 5px solid var(--med-primary) !important; box-shadow: 0 4px 10px rgba(0,0,0,0.05); cursor: pointer;" onclick="window.location.href='{{ route('products.index') }}'">
                            <div class="card-body">
                                <div class="media">
                                    <div class="media-body">
                                        <p class="mb-1 text-muted font-weight-bold text-uppercase">Catalog</p>
                                        <h3 class="mb-0" style="color: var(--med-primary)">{{ $counts['products'] }} Items</h3>
                                    </div>
                                    <div class="align-self-center"><i data-feather="grid" style="color: var(--med-primary); width:40px; height:40px;"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card p-4 mb-4">
                            <h5 class="section-title">My Last 6 Months Orders</h5>
                            <div id="monthlyOrdersChart"></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card p-4 mb-4">
                            <h5 class="section-title">Order Status Overview</h5>
                            <div id="orderStatusChart"></div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ========================================== --}}
            {{-- DISTRIBUTOR DASHBOARD --}}
            {{-- ========================================== --}}
            @if(Auth::user()->hasRole('distributor'))
                <h4 class="mb-4 text-primary">Distributor Control Panel</h4>
                <div class="row">
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" onclick="window.location.href='{{ route('distributor.orders.index') }}'" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2">
                                        <span class="m-0 font-weight-bold text-uppercase">Order Volume</span>
                                        <h4 class="mb-0 counter mt-2">{{ $retailerOrderStats['total'] }}</h4>
                                    </div>
                                    <i data-feather="truck"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; cursor: pointer;">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2">
                                        <span class="m-0 font-weight-bold text-uppercase">Delivered</span>
                                        <h4 class="mb-0 counter mt-2">{{ $retailerOrderStats['delivered'] }}</h4>
                                    </div>
                                    <i data-feather="check-square"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; cursor: pointer;">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2">
                                        <span class="m-0 font-weight-bold text-uppercase">Pending</span>
                                        <h4 class="mb-0 counter mt-2">{{ $retailerOrderStats['pending'] }}</h4>
                                    </div>
                                    <i data-feather="clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" onclick="window.location.href='{{ route('products.index') }}'" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2">
                                        <span class="m-0 font-weight-bold text-uppercase">My Stock Items</span>
                                        <h4 class="mb-0 counter mt-2">{{ $counts['products'] }}</h4>
                                    </div>
                                    <i data-feather="layers"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card p-4 mb-4">
                            <h5 class="section-title">Orders Received (Monthly Trend)</h5>
                            <div id="monthlyOrdersChart"></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card p-4 mb-4">
                            <h5 class="section-title">Order Fulfillment Rate</h5>
                            <div id="orderStatusChart"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card p-4 mb-4">
                            <h5 class="section-title">Top Producing Retailers</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Retailer Name</th>
                                            <th>Email</th>
                                            <th>Total Orders</th>
                                            <th>Revenue Generated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topRetailers as $tr)
                                            <tr>
                                                <td class="font-weight-bold">{{ $tr->retailer->user->name ?? 'Unknown' }}</td>
                                                <td>{{ $tr->retailer->user->email ?? 'N/A' }}</td>
                                                <td><span class="badge badge-primary">{{ $tr->total_orders }} Orders</span></td>
                                                <td class="text-success font-weight-bold">₹{{ number_format($tr->total_revenue, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-4">No top retailers data available yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ========================================== --}}
            {{-- FIELD STAFF DASHBOARD --}}
            {{-- ========================================== --}}
            @if(Auth::user()->hasRole('fieldstaff'))
                <h4 class="mb-4 text-primary">Field Force Overview</h4>
                <div class="row">
                    <div class="col-sm-6 col-xl-4 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2">
                                        <span class="m-0 font-weight-bold text-uppercase">My Retailers</span>
                                        <h4 class="mb-0 counter mt-2">{{ $counts['retailers'] }}</h4>
                                    </div>
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; cursor: pointer;">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2">
                                        <span class="m-0 font-weight-bold text-uppercase">Orders Generated</span>
                                        <h4 class="mb-0 counter mt-2">{{ $retailerOrderStats['total'] }}</h4>
                                    </div>
                                    <i data-feather="shopping-bag"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4 col-lg-12 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2">
                                        <span class="m-0 font-weight-bold text-uppercase">Total Catalog</span>
                                        <h4 class="mb-0 counter mt-2">{{ $counts['products'] }}</h4>
                                    </div>
                                    <i data-feather="grid"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card p-4 mb-4">
                            <h5 class="section-title">My Performance (Orders per Month)</h5>
                            <div id="monthlyOrdersChart"></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card p-4 mb-4">
                            <h5 class="section-title">Client Order Status</h5>
                            <div id="orderStatusChart"></div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ========================================== --}}
            {{-- ADMIN & SUPERADMIN & SALES MANAGER DASHBOARD --}}
            {{-- ========================================== --}}
            @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                <h4 class="mb-4 text-primary font-weight-bold">Analytics & Command Center</h4>

                <div class="row">
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="cursor: pointer;" onclick="window.location.href='{{ route('admin.distributors.index') }}'">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2"><span class="m-0 text-uppercase font-weight-bold">Distributors</span>
                                        <h4 class="mb-0 counter mt-2">{{ $counts['distributors'] }}</h4>
                                    </div>
                                    <i data-feather="briefcase"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="cursor: pointer;" onclick="window.location.href='{{ route('admin.retailers.index') }}'">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2"><span class="m-0 text-uppercase font-weight-bold">Retailers</span>
                                        <h4 class="mb-0 counter mt-2">{{ $counts['retailers'] }}</h4>
                                    </div>
                                    <i data-feather="shopping-bag"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin']))
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="cursor: pointer;" onclick="window.location.href='{{ route('admin.field-staffs.index') }}'">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2"><span class="m-0 text-uppercase font-weight-bold">Field Staff</span>
                                        <h4 class="mb-0 counter mt-2">{{ $counts['field_staff'] }}</h4>
                                    </div>
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%) !important;">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2"><span class="m-0 text-uppercase font-weight-bold">Global Loyalty Pts</span>
                                        <h4 class="mb-0 counter mt-2">{{ number_format($totalLoyaltyPoints) }}</h4>
                                    </div>
                                    <i data-feather="award"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if(Auth::user()->hasRole('salesmanager'))
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2"><span class="m-0 text-uppercase font-weight-bold">My Field Staff</span>
                                        <h4 class="mb-0 counter mt-2">{{ $counts['field_staff'] }}</h4>
                                    </div>
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3 col-lg-6 mb-4">
                        <div class="card o-hidden border-0 med-widget-card h-100" onclick="window.location.href='{{ route('products.index') }}'">
                            <div class="card-body">
                                <div class="media static-top-widget">
                                    <div class="media-body m-2"><span class="m-0 text-uppercase font-weight-bold">Platform Products</span>
                                        <h4 class="mb-0 counter mt-2">{{ $counts['products'] }}</h4>
                                    </div>
                                    <i data-feather="box"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card p-4 mb-4">
                            <h5 class="section-title">Retailer Orders</h5>
                            <div class="row text-center mb-3">
                                <div class="col-4 border-right">
                                    <h6 class="text-muted mb-1">Total</h6>
                                    <h4>{{ $retailerOrderStats['total'] }}</h4>
                                </div>
                                <div class="col-4 border-right">
                                    <h6 class="text-muted mb-1">Pending</h6>
                                    <h4 class="text-warning">{{ $retailerOrderStats['pending'] }}</h4>
                                </div>
                                <div class="col-4">
                                    <h6 class="text-muted mb-1">Delivered</h6>
                                    <h4 class="text-success">{{ $retailerOrderStats['delivered'] }}</h4>
                                </div>
                            </div>
                            <div id="monthlyOrdersChart"></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card p-4 mb-4">
                            <h5 class="section-title">Distributor Orders</h5>
                            <div class="row text-center mb-3">
                                <div class="col-4 border-right">
                                    <h6 class="text-muted mb-1">Total Request</h6>
                                    <h4>{{ $distributorOrderStats['total'] }}</h4>
                                </div>
                                <div class="col-4 border-right">
                                    <h6 class="text-muted mb-1">Pending</h6>
                                    <h4 class="text-warning">{{ $distributorOrderStats['pending'] }}</h4>
                                </div>
                                <div class="col-4">
                                    <h6 class="text-muted mb-1">Delivered</h6>
                                    <h4 class="text-success">{{ $distributorOrderStats['delivered'] }}</h4>
                                </div>
                            </div>
                            <!-- Assuming you will pass monthlyDistributorOrdersChart -->
                            @if(isset($monthlyDistributorOrdersChart))
                                <div id="monthlyDistOrdersChart"></div>
                            @else
                                <div class="text-center py-5 text-muted"><i data-feather="bar-chart-2"></i><br>Insufficient Data</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Top performers tables -->
                    <div class="col-lg-4">
                        <div class="card p-4 h-100 mb-4">
                            <h5 class="section-title"><i data-feather="star" class="text-warning mr-2"></i>Top Field Staff</h5>
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tbody>
                                        @forelse($topFieldStaff as $fs)
                                            <tr class="border-bottom">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary font-weight-bold" style="width:40px;height:40px; margin-right: 15px;">
                                                            {{ substr($fs->fieldStaff->user->name ?? '?', 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold">{{ $fs->fieldStaff->user->name ?? 'Unknown' }}</h6>
                                                            <small class="text-muted">{{ $fs->total_orders }} Orders Managed</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td class="text-center py-3 text-muted">No data available</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin']))
                    <div class="col-lg-8">
                        <div class="card p-4 h-100 mb-4">
                            <h5 class="section-title"><i data-feather="trending-up" class="text-primary mr-2"></i>Top Producing Partners</h5>
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <h6 class="text-muted font-weight-bold mb-3 text-uppercase" style="font-size:12px;">Top Distributors</h6>
                                    <table class="table table-sm">
                                        @forelse($topDistributors as $td)
                                            <tr>
                                                <td class="font-weight-bold">{{ $td->distributor->user->name ?? 'Unknown' }}</td>
                                                <td class="text-right text-success font-weight-bold">₹{{ number_format($td->total_revenue, 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted">No data</td></tr>
                                        @endforelse
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted font-weight-bold mb-3 text-uppercase" style="font-size:12px;">Top Retailers</h6>
                                    <table class="table table-sm">
                                        @forelse($topRetailers as $tr)
                                            <tr>
                                                <td class="font-weight-bold">{{ $tr->retailer->user->name ?? 'Unknown' }}</td>
                                                <td class="text-right text-success font-weight-bold">₹{{ number_format($tr->total_revenue, 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted">No data</td></tr>
                                        @endforelse
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            {{-- ========================================== --}}
            {{-- RECENT ORDERS (Visible to Most) --}}
            {{-- ========================================== --}}
            <div class="row mt-4">
                @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager', 'distributor', 'fieldstaff', 'retailer']))
                    <div class="col-lg-{{ Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']) ? '6' : '12' }} mb-4">
                        <div class="card p-4 h-100 mb-4">
                            <h5 class="section-title">{{ Auth::user()->hasRole('retailer') ? 'My Recent Orders' : 'Recent Retailer Orders' }}</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            @if(!Auth::user()->hasRole('retailer'))
                                                <th>Retailer</th>
                                            @endif
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentRetailerOrders as $order)
                                            <tr>
                                                <td class="font-weight-bold" style="color: var(--med-primary);">{{ $order->order_code ?? '#'.$order->id }}</td>
                                                @if(!Auth::user()->hasRole('retailer'))
                                                    <td class="font-weight-bold">{{ $order->retailer->user->name ?? 'N/A' }}</td>
                                                @endif
                                                <td>{{ $order->created_at->format('d M Y') }}</td>
                                                <td class="font-weight-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                                                <td>
                                                    <span class="badge badge-pill px-3 py-2 
                                                        {{ $order->status == 'delivered' ? 'badge-success' : ($order->status == 'cancelled' ? 'badge-danger' : 'badge-primary') }}"
                                                        style="font-size:12px;">
                                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No recent orders found. Start generating orders to see them here!</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                    <div class="col-lg-6 mb-4">
                        <div class="card p-4 h-100 mb-4">
                            <h5 class="section-title">Recent Distributor Orders</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Distributor</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentDistributorOrders as $order)
                                            <tr>
                                                <td class="font-weight-bold" style="color: var(--med-primary);">{{ $order->order_code ?? '#'.$order->id }}</td>
                                                <td class="font-weight-bold">{{ $order->distributor->user->name ?? 'N/A' }}</td>
                                                <td>{{ $order->created_at->format('d M Y') }}</td>
                                                <td class="font-weight-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                                                <td>
                                                    <span class="badge badge-pill px-3 py-2 
                                                        {{ $order->status == 'delivered' ? 'badge-success' : ($order->status == 'cancelled' ? 'badge-danger' : 'badge-primary') }}"
                                                        style="font-size:12px;">
                                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No recent distributor orders found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Scripts for Charts -->
            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        // Monthly Retailer Orders
                        if(document.querySelector("#monthlyOrdersChart")) {
                            var monthlyOptions = {
                                series: [{
                                    name: "Retailer Orders",
                                    data: @json($chartData['counts'])
                                }],
                                chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false } },
                                dataLabels: { enabled: false },
                                stroke: { curve: 'smooth', width: 3 },
                                xaxis: { categories: @json($chartData['months']) },
                                colors: ['#00497a'],
                                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1, stops: [0, 90, 100] } }
                            };
                            new ApexCharts(document.querySelector("#monthlyOrdersChart"), monthlyOptions).render();
                        }

                        // Order Status Donut Chart (For specific roles)
                        if(document.querySelector("#orderStatusChart")) {
                            var orderTotal = {{ $retailerOrderStats['total'] }};
                            var statusSeries = orderTotal > 0 ? [
                                {{ $retailerOrderStats['pending'] }},
                                {{ $retailerOrderStats['approved'] }},
                                {{ $retailerOrderStats['delivered'] }},
                                {{ $retailerOrderStats['cancelled'] }}
                            ] : [1];
                            var statusLabels = orderTotal > 0 ? ['Pending', 'Approved', 'Delivered', 'Cancelled'] : ['No Orders Yet'];
                            var statusColors = orderTotal > 0 ? ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'] : ['#e2e8f0'];

                            var statusOptions = {
                                series: statusSeries,
                                labels: statusLabels,
                                chart: { type: 'donut', height: 320 },
                                colors: statusColors,
                                stroke: { width: 0 },
                                plotOptions: { pie: { donut: { size: '65%' } } },
                                dataLabels: { enabled: false },
                                tooltip: { enabled: orderTotal > 0 }
                            };
                            new ApexCharts(document.querySelector("#orderStatusChart"), statusOptions).render();
                        }
                        
                        // Monthly Dist Orders Chart (Admin/SM)
                        @if(isset($monthlyDistributorOrdersChart))
                        if(document.querySelector("#monthlyDistOrdersChart")) {
                            var monthlyDistOptions = {
                                series: [{
                                    name: "Distributor Orders",
                                    data: @json($monthlyDistributorOrdersChart['counts'])
                                }],
                                chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false } },
                                dataLabels: { enabled: false },
                                stroke: { curve: 'smooth', width: 3 },
                                xaxis: { categories: @json($monthlyDistributorOrdersChart['months']) },
                                colors: ['#8b5cf6'],
                                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1, stops: [0, 90, 100] } }
                            };
                            new ApexCharts(document.querySelector("#monthlyDistOrdersChart"), monthlyDistOptions).render();
                        }
                        @endif
                    });
                </script>
            @endpush

        </div>
    </div>
</div>

@endsection