@extends('layouts.admin')

@section('page-body')
    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row size-column">
            <div class="col-xxl-12 box-col-12">

                <style>
                    :root {
                        --med-primary-rgb: 0, 73, 122;
                        --med-accent-rgb: 0, 43, 92;
                    }

                    .med-widget-card {
                        background: linear-gradient(135deg, var(--med-primary) 0%, var(--med-accent) 100%) !important;
                        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
                        border-radius: 16px;
                        overflow: hidden;
                        position: relative;
                        border: none !important;
                    }

                    .med-widget-card::after {
                        content: '';
                        position: absolute;
                        top: -50%;
                        right: -50%;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.1);
                        transform: rotate(35deg);
                        pointer-events: none;
                    }

                    .med-widget-card:hover {
                        transform: translateY(-8px);
                        box-shadow: 0 20px 40px rgba(0, 73, 122, 0.25) !important;
                    }

                    .premium-stats-card {
                        background: var(--med-bg-card) !important;
                        border-radius: 16px;
                        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
                        border: 1px solid var(--med-border) !important;
                        position: relative;
                        overflow: hidden;
                    }

                    .premium-stats-card:hover {
                        transform: translateY(-8px);
                        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05) !important;
                        border-color: var(--med-primary) !important;
                    }

                    .premium-stats-card .icon-bg {
                        position: absolute;
                        right: -10px;
                        bottom: -10px;
                        opacity: 0.05;
                        transition: all 0.4s ease;
                    }

                    .premium-stats-card:hover .icon-bg {
                        transform: scale(1.2) rotate(-10deg);
                        opacity: 0.1;
                    }

                    .retailer-hero-glass {
                        background: var(--med-bg-card);
                        backdrop-filter: blur(10px);
                        border-radius: 20px;
                        padding: 15px 25px;
                        margin-bottom: 25px;
                        border: 1px solid var(--med-border);
                        position: relative;
                        overflow: hidden;
                        box-shadow: var(--med-shadow-soft);
                    }

                    .profile-avatar {
                        width: 70px;
                        height: 70px;
                        border-radius: 18px;
                        background: var(--med-primary);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #fff;
                        font-size: 24px;
                        font-weight: 800;
                        box-shadow: 0 8px 20px rgba(0, 73, 122, 0.2);
                        flex-shrink: 0;
                    }

                    .profile-info-item {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        color: var(--med-text-muted);
                        font-size: 0.8rem;
                        font-weight: 600;
                        background: rgba(var(--med-primary-rgb), 0.03);
                        padding: 4px 12px;
                        border-radius: 10px;
                        border: 1px solid rgba(var(--med-primary-rgb), 0.05);
                    }

                    .profile-info-item i {
                        width: 12px;
                        height: 12px;
                        color: var(--med-primary);
                        opacity: 0.8;
                    }

                    .dark-only .retailer-hero-glass {
                        background: rgba(255, 255, 255, 0.03);
                    }

                    .floating-loyalty {
                        background: var(--med-bg-card);
                        border-radius: 20px;
                        padding: 20px 30px;
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
                        border: 1px solid var(--med-border);
                        transition: all 0.3s ease;
                    }

                    .floating-loyalty:hover {
                        transform: scale(1.02);
                        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
                    }

                    .action-btn-premium {
                        border-radius: 18px !important;
                        padding: 1.2rem 1.8rem !important;
                        font-weight: 700 !important;
                        letter-spacing: 0.3px;
                        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
                    }

                    .action-btn-premium:hover {
                        transform: translateY(-4px) scale(1.02);
                    }

                    .section-header-modern {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        margin-bottom: 25px;
                    }

                    .section-header-modern .dash {
                        height: 3px;
                        width: 40px;
                        background: var(--med-primary);
                        border-radius: 2px;
                    }
                </style>

                @php 
                    $user = auth()->user(); 
                    $timeSelectHtml = '<select class="form-control form-control-sm cursor-pointer shadow-sm border-0 bg-light text-primary font-weight-bold" onchange="window.location.href=\'?period=\'+this.value" style="border-radius: 8px; width: 100px; padding: 4px 8px; font-size: 13px;">
                        <option value="monthly" ' . ((!isset($period) || $period == 'monthly') ? 'selected' : '') . '>Monthly</option>
                        <option value="weekly" ' . ((isset($period) && $period == 'weekly') ? 'selected' : '') . '>Weekly</option>
                        <option value="yearly" ' . ((isset($period) && $period == 'yearly') ? 'selected' : '') . '>Yearly</option>
                    </select>';
                @endphp

                @if(Auth::user()->hasRole('retailer'))
                    <div class="retailer-hero-glass d-flex align-items-center justify-content-between flex-wrap gap-4">
                        <div class="d-flex align-items-center gap-4">
                            <div class="profile-avatar">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="fw-800 mb-2" style="color: var(--med-primary); letter-spacing: -0.3px; margin-top: 2px;">{{ $user->name }}</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="profile-info-item">
                                        <i data-feather="home"></i>
                                        <span>{{ $user->retailer->shop_name ?? 'Medical Store' }}</span>
                                    </div>
                                    <div class="profile-info-item">
                                        <i data-feather="phone"></i>
                                        <span>{{ $user->retailer->contact_no ?? 'N/A' }}</span>
                                    </div>
                                    <div class="profile-info-item">
                                        <i data-feather="map-pin"></i>
                                        <span>{{ $user->retailer->district->name ?? 'Local District' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @php
                            $stats = [
                                ['label' => 'Total Orders', 'value' => $retailerOrderStats['total'], 'icon' => 'shopping-cart', 'color' => 'var(--med-primary)', 'bg' => 'rgba(var(--med-primary-rgb), 0.1)', 'route' => route('retailer.orders.index')],
                                ['label' => 'Pending', 'value' => $retailerOrderStats['pending'], 'icon' => 'clock', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)', 'route' => route('retailer.orders.index')],
                                ['label' => 'Delivered', 'value' => $retailerOrderStats['delivered'], 'icon' => 'check-circle', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)', 'route' => route('retailer.orders.index')],
                                ['label' => 'Loyalty Points', 'value' => number_format($totalLoyaltyPoints), 'icon' => 'database', 'color' => '#fbbf24', 'bg' => 'rgba(251, 191, 36, 0.1)', 'route' => '#']
                            ];
                        @endphp

                        @foreach($stats as $stat)
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card premium-stats-card h-100 cursor-pointer" onclick="window.location.href='{{ $stat['route'] }}'">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="p-3 rounded-blink" style="background: {{ $stat['bg'] }}">
                                                <i data-feather="{{ $stat['icon'] }}" style="color: {{ $stat['color'] }}; width: 24px; height: 24px;"></i>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="text-muted text-uppercase fw-700 mb-1" style="font-size: 11px; letter-spacing: 0.5px;">{{ $stat['label'] }}</h6>
                                                <h3 class="mb-0 fw-800" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</h3>
                                            </div>
                                        </div>
                                        <i data-feather="{{ $stat['icon'] }}" class="icon-bg" style="width: 80px; height: 80px;"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="section-header-modern">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Quick Actions</h5>
                            </div>
                        </div>
                        <div class="col-12 mb-5 d-flex gap-4 flex-wrap">
                            <button class="btn btn-outline-primary action-btn-premium shadow-sm d-flex align-items-center px-5" 
                                onclick="window.location.href='{{ route('admin.retailer.create') }}'">
                                <i data-feather="plus-circle" class="me-3" style="width: 22px; height: 22px;"></i>
                                <span>Create New Order</span>
                            </button>
                            
                            <button class="btn btn-primary action-btn-premium shadow-lg d-flex align-items-center px-5" 
                                style="background: linear-gradient(135deg, var(--med-primary) 0%, var(--med-accent) 100%); border: none;"
                                onclick="window.location.href='{{ route('admin.retailer.create', ['action' => 'upload_prescription']) }}'">
                                <i data-feather="cpu" class="me-3" style="width: 22px; height: 22px; stroke-width: 2.5px;"></i>
                                <span>Upload Prescription (AI)</span>
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="section-header-modern mb-3 mt-2">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">My Orders Over Time</h5>
                                <div class="ms-auto">{!! $timeSelectHtml !!}</div>
                            </div>
                            <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
                                <div id="monthlyOrdersChart"></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="section-header-modern mb-3 mt-2">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">Status Overview ({{ isset($period) ? ucfirst($period) : 'Monthly' }})</h5>
                            </div>
                            <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
                                <div id="orderStatusChart"></div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ========================================== --}}
                {{-- DISTRIBUTOR DASHBOARD --}}
                {{-- ========================================== --}}
                @if(Auth::user()->hasRole('distributor'))
                    <div class="section-header-modern">
                        <div class="dash"></div>
                        <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Distributor Control Panel</h5>
                    </div>
                    <div class="row">
                        @php
                            $stats = [
                                ['label' => 'Order Volume', 'value' => $retailerOrderStats['total'], 'icon' => 'truck', 'color' => 'var(--med-primary)', 'bg' => 'rgba(var(--med-primary-rgb), 0.1)', 'route' => route('distributor.orders.index')],
                                ['label' => 'Delivered', 'value' => $retailerOrderStats['delivered'], 'icon' => 'check-square', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)', 'route' => route('distributor.orders.index')],
                                ['label' => 'Pending', 'value' => $retailerOrderStats['pending'], 'icon' => 'clock', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)', 'route' => route('distributor.orders.index')],
                                ['label' => 'My Stock Items', 'value' => $counts['products'], 'icon' => 'layers', 'color' => 'var(--med-accent)', 'bg' => 'rgba(var(--med-accent-rgb), 0.1)', 'route' => route('products.index')]
                            ];
                        @endphp
                        @foreach($stats as $stat)
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card premium-stats-card h-100 cursor-pointer" onclick="window.location.href='{{ $stat['route'] }}'">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="p-3 rounded-blink" style="background: {{ $stat['bg'] }}">
                                                <i data-feather="{{ $stat['icon'] }}" style="color: {{ $stat['color'] }}; width: 24px; height: 24px;"></i>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="text-muted text-uppercase fw-700 mb-1" style="font-size: 11px; letter-spacing: 0.5px;">{{ $stat['label'] }}</h6>
                                                <h3 class="mb-0 fw-800" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</h3>
                                            </div>
                                        </div>
                                        <i data-feather="{{ $stat['icon'] }}" class="icon-bg" style="width: 80px; height: 80px;"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0" style="border-bottom: none;">Orders Received (Trend)</h5>
                                    {!! $timeSelectHtml !!}
                                </div>
                                <div id="monthlyOrdersChart"></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0" style="border-bottom: none;">Fulfillment Rate ({{ isset($period) ? ucfirst($period) : 'Monthly' }})</h5>
                                </div>
                                <div id="orderStatusChart"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Top Retailers -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white border-0 pt-4 pb-2">
                                    <h6 class="m-0 font-weight-bold text-primary text-uppercase d-flex align-items-center"
                                        style="letter-spacing: 0.05em; font-size: 0.85rem;">
                                        <i data-feather="award" class="me-2 text-warning"
                                            style="width: 18px; height: 18px;"></i> Top Producing Retailers
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0">
                                            <thead class="bg-light text-muted"
                                                style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                <tr>
                                                    <th class="px-4 py-3 font-weight-bold" style="width: 40%;">Retailer</th>
                                                    <th class="px-3 py-3 font-weight-bold text-center">Top Product</th>
                                                    <th class="text-right px-4 py-3 font-weight-bold">Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($topRetailers as $tr)
                                                    <tr style="border-bottom: 1px solid #f8f9fc;">
                                                        <td class="px-4 py-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 text-white shadow-sm"
                                                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--med-primary) 0%, var(--med-accent) 100%); font-weight: 600; font-size: 1rem; flex-shrink: 0;">
                                                                    {{ strtoupper(substr($tr->retailer->user->name ?? 'U', 0, 1)) }}
                                                                </div>
                                                                <div style="min-width: 0;">
                                                                    <h6 class="mb-0 font-weight-bold text-dark text-truncate"
                                                                        style="font-size: 0.9rem;">
                                                                        {{ $tr->retailer->user->name ?? 'Unknown' }}</h6>
                                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                                        {{ $tr->total_orders ?? '0' }} Orders
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-3 py-3 text-center align-middle">
                                                            <span class="badge badge-light text-secondary px-3 py-2 rounded-pill border shadow-xs"
                                                                style="font-size: 0.75rem; font-weight: 600; max-width: 140px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                                {{ $tr->top_product_name ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-right px-4 py-3 align-middle">
                                                            <h6 class="mb-0 font-weight-bold text-success" style="font-size: 0.95rem;">
                                                                ₹{{ number_format($tr->total_revenue, 2) }}</h6>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center py-5">
                                                            <div class="text-muted opacity-50">
                                                                <i data-feather="users" style="width: 40px; height: 40px;" class="mb-2"></i>
                                                                <p class="mb-0 font-weight-500">No retailer data available yet.</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
 
                        <!-- Top Products -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white border-0 pt-4 pb-2">
                                    <h6 class="m-0 font-weight-bold text-success text-uppercase d-flex align-items-center"
                                        style="letter-spacing: 0.05em; font-size: 0.85rem;">
                                        <i data-feather="trending-up" class="me-2"
                                            style="width: 18px; height: 18px;"></i> Top Products Ordered
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0">
                                            <thead class="bg-light text-muted"
                                                style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                <tr>
                                                    <th class="px-4 py-3 font-weight-bold" style="width: 45%;">Product</th>
                                                    <th class="px-3 py-3 font-weight-bold text-center">Units Sold</th>
                                                    <th class="text-right px-4 py-3 font-weight-bold">Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($topProducts as $tp)
                                                    <tr style="border-bottom: 1px solid #f8f9fc;">
                                                        <td class="px-4 py-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="rounded d-flex align-items-center justify-content-center me-3 text-success shadow-sm"
                                                                    style="width: 38px; height: 38px; background: #e8f5e9; flex-shrink: 0;">
                                                                    <i data-feather="package" style="width: 18px; height: 18px;"></i>
                                                                </div>
                                                                <h6 class="mb-0 font-weight-bold text-dark"
                                                                    style="font-size: 0.9rem; line-height: 1.4;">
                                                                    {{ \Illuminate\Support\Str::limit($tp->product_name, 40) }}
                                                                </h6>
                                                            </div>
                                                        </td>
                                                        <td class="px-3 py-3 text-center align-middle">
                                                            <div class="d-inline-flex align-items-baseline">
                                                                <span class="font-weight-bold text-dark me-1"
                                                                    style="font-size: 1rem;">{{ $tp->total_quantity_ordered }}</span>
                                                                <small class="text-muted font-weight-600" style="font-size: 0.7rem;">Units</small>
                                                            </div>
                                                        </td>
                                                        <td class="text-right px-4 py-3 align-middle">
                                                            <h6 class="mb-0 font-weight-bold text-success" style="font-size: 0.95rem;">
                                                                ₹{{ number_format($tp->total_revenue, 2) }}</h6>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center py-5">
                                                            <div class="text-muted opacity-50">
                                                                <i data-feather="box" style="width: 40px; height: 40px;" class="mb-2"></i>
                                                                <p class="mb-0 font-weight-500">No product data available yet.</p>
                                                            </div>
                                                        </td>
                                                    </tr>
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
                    <div class="section-header-modern">
                        <div class="dash"></div>
                        <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Field Force Overview</h5>
                    </div>
                    <div class="row">
                        @php
                            $stats = [
                                ['label' => 'My Retailers', 'value' => $counts['retailers'], 'icon' => 'users', 'color' => 'var(--med-primary)', 'bg' => 'rgba(var(--med-primary-rgb), 0.1)', 'route' => '#'],
                                ['label' => 'Orders Generated', 'value' => $retailerOrderStats['total'], 'icon' => 'shopping-bag', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)', 'route' => '#'],
                                ['label' => 'Total Products', 'value' => $counts['products'], 'icon' => 'grid', 'color' => 'var(--med-accent)', 'bg' => 'rgba(var(--med-accent-rgb), 0.1)', 'route' => '#']
                            ];
                        @endphp
                        @foreach($stats as $stat)
                            <div class="col-xl-4 col-md-6 mb-4">
                                <div class="card premium-stats-card h-100 cursor-pointer" onclick="window.location.href='{{ $stat['route'] }}'">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="p-3 rounded-blink" style="background: {{ $stat['bg'] }}">
                                                <i data-feather="{{ $stat['icon'] }}" style="color: {{ $stat['color'] }}; width: 24px; height: 24px;"></i>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="text-muted text-uppercase fw-700 mb-1" style="font-size: 11px; letter-spacing: 0.5px;">{{ $stat['label'] }}</h6>
                                                <h3 class="mb-0 fw-800" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</h3>
                                            </div>
                                        </div>
                                        <i data-feather="{{ $stat['icon'] }}" class="icon-bg" style="width: 80px; height: 80px;"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="section-header-modern mb-3 mt-2">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">My Performance (Trend)</h5>
                                <div class="ms-auto">{!! $timeSelectHtml !!}</div>
                            </div>
                            <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
                                <div id="monthlyOrdersChart"></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="section-header-modern mb-3 mt-2">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">Client Status ({{ isset($period) ? ucfirst($period) : 'Monthly' }})</h5>
                            </div>
                            <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
                                <div id="orderStatusChart"></div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ========================================== --}}
                {{-- ADMIN & SUPERADMIN & SALES MANAGER DASHBOARD --}}
                {{-- ========================================== --}}
                @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                    <div class="section-header-modern">
                        <div class="dash"></div>
                        <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Analytics & Command Center</h5>
                    </div>

                    <div class="row">
                        @php
                            $adminStats = [
                                ['label' => 'Distributors', 'value' => $counts['distributors'], 'icon' => 'briefcase', 'color' => 'var(--med-primary)', 'bg' => 'rgba(var(--med-primary-rgb), 0.1)', 'route' => route('admin.distributors.index')],
                                ['label' => 'Retailers', 'value' => $counts['retailers'], 'icon' => 'shopping-bag', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)', 'route' => route('admin.retailers.index')]
                            ];

                            if (Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                                $adminStats[] = ['label' => 'Field Staff', 'value' => $counts['field_staff'], 'icon' => 'users', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)', 'route' => route('admin.field-staffs.index')];
                                $adminStats[] = ['label' => 'Sales Managers', 'value' => $counts['sales_managers'], 'icon' => 'user-check', 'color' => 'var(--med-accent)', 'bg' => 'rgba(var(--med-accent-rgb), 0.1)', 'route' => route('admin.sales-managers.index')];
                            } elseif (Auth::user()->hasRole('salesmanager')) {
                                $adminStats[] = ['label' => 'Field Staff', 'value' => $counts['field_staff'], 'icon' => 'users', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)', 'route' => route('admin.field-staffs.index')];
                                $adminStats[] = ['label' => 'Products', 'value' => $counts['products'], 'icon' => 'box', 'color' => 'var(--med-accent)', 'bg' => 'rgba(var(--med-accent-rgb), 0.1)', 'route' => '#'];
                            }
                        @endphp

                        @foreach($adminStats as $stat)
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card premium-stats-card h-100 cursor-pointer" onclick="window.location.href='{{ $stat['route'] }}'">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="p-3 rounded-blink" style="background: {{ $stat['bg'] }}">
                                                <i data-feather="{{ $stat['icon'] }}" style="color: {{ $stat['color'] }}; width: 24px; height: 24px;"></i>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="text-muted text-uppercase fw-700 mb-1" style="font-size: 11px; letter-spacing: 0.5px;">{{ $stat['label'] }}</h6>
                                                <h3 class="mb-0 fw-800" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</h3>
                                            </div>
                                        </div>
                                        <i data-feather="{{ $stat['icon'] }}" class="icon-bg" style="width: 80px; height: 80px;"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <h5 class="section-title mb-0" style="border-bottom: none;">Retailer Orders</h5>
                                    {!! $timeSelectHtml !!}
                                </div>
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
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <h5 class="section-title mb-0" style="border-bottom: none;">Distributor Orders</h5>
                                    {!! $timeSelectHtml !!}
                                </div>
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
                                    <div class="text-center py-5 text-muted"><i data-feather="bar-chart-2"></i><br>Insufficient Data
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Top performers tables -->
                        <div class="col-lg-4">
                            <div class="card p-4 h-100 mb-4">
                                <h5 class="section-title"><i data-feather="star" class="text-warning mr-2"></i>Top Field Staff
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            @forelse($topFieldStaff as $fs)
                                                <tr class="border-bottom">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary font-weight-bold"
                                                                style="width:40px;height:40px; margin-right: 15px;">
                                                                {{ substr($fs->fieldStaff->user->name ?? '?', 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 font-weight-bold">
                                                                    {{ $fs->fieldStaff->user->name ?? 'Unknown' }}</h6>
                                                                <small class="text-muted">{{ $fs->total_orders }} Orders
                                                                    Managed</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center py-3 text-muted">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']))
                            <div class="col-lg-8">
                                <div class="card p-4 h-100 mb-4">
                                    <h5 class="section-title"><i data-feather="trending-up" class="text-primary mr-2"></i>Top
                                        Producing Partners</h5>
                                    <div class="row">
                                        <div class="col-md-6 border-right">
                                            <h6 class="text-muted font-weight-bold mb-3 text-uppercase" style="font-size:12px;">Top
                                                Distributors</h6>
                                            <table class="table table-sm">
                                                @forelse($topDistributors as $td)
                                                    <tr>
                                                        <td class="font-weight-bold">{{ $td->distributor->user->name ?? 'Unknown' }}
                                                        </td>
                                                        <td class="text-right text-success font-weight-bold">
                                                            ₹{{ number_format($td->total_revenue, 0) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">No data</td>
                                                    </tr>
                                                @endforelse
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted font-weight-bold mb-3 text-uppercase" style="font-size:12px;">Top
                                                Retailers</h6>
                                            <table class="table table-sm">
                                                @forelse($topRetailers as $tr)
                                                    <tr>
                                                        <td class="font-weight-bold">{{ $tr->retailer->user->name ?? 'Unknown' }}</td>
                                                        <td class="text-right text-success font-weight-bold">
                                                            ₹{{ number_format($tr->total_revenue, 0) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">No data</td>
                                                    </tr>
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
                        <div
                            class="col-lg-{{ Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']) ? '6' : '12' }} mb-4">
                            <div class="section-header-modern mb-3 mt-2">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">
                                    {{ Auth::user()->hasRole('retailer') ? 'My Recent Orders' : 'Recent Retailer Orders' }}
                                </h5>
                            </div>
                            <div class="card p-4 h-100 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
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
                                                    <td class="font-weight-bold" style="color: var(--med-primary);">
                                                        {{ $order->order_code ?? '#' . $order->id }}</td>
                                                    @if(!Auth::user()->hasRole('retailer'))
                                                        <td class="font-weight-bold">{{ $order->retailer->user->name ?? 'N/A' }}</td>
                                                    @endif
                                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                                    <td class="font-weight-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-pill px-3 py-2 
                                                                    {{ $order->status == 'delivered' ? 'badge-success' : ($order->status == 'cancelled' ? 'badge-danger' : ($order->status == 'approved' ? 'badge-info' : 'badge-primary')) }}"
                                                            style="font-size:12px;">
                                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No recent orders found.
                                                        Start generating orders to see them here!</td>
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
                            <div class="section-header-modern mb-3 mt-2">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Recent Distributor Orders</h5>
                            </div>
                            <div class="card p-4 h-100 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
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
                                                    <td class="font-weight-bold" style="color: var(--med-primary);">
                                                        {{ $order->order_code ?? '#' . $order->id }}</td>
                                                    <td class="font-weight-bold">{{ $order->distributor->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                                    <td class="font-weight-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-pill px-3 py-2 
                                                                    {{ $order->status == 'delivered' ? 'badge-success' : ($order->status == 'cancelled' ? 'badge-danger' : ($order->status == 'approved' ? 'badge-info' : 'badge-primary')) }}"
                                                            style="font-size:12px;">
                                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No recent distributor orders
                                                        found.</td>
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
                            if (document.querySelector("#monthlyOrdersChart")) {
                                var monthlyOptions = {
                                    series: [{
                                        name: "Retailer Orders",
                                        data: @json($chartData['counts'])
                                    }],
                                    chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false } },
                                    dataLabels: { enabled: false },
                                    stroke: { curve: 'smooth', width: 3 },
                                    xaxis: { categories: @json($chartData['labels']) },
                                    colors: ['#00497a'],
                                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1, stops: [0, 90, 100] } }
                                };
                                new ApexCharts(document.querySelector("#monthlyOrdersChart"), monthlyOptions).render();
                            }

                            // Order Status Donut Chart (For specific roles)
                            if (document.querySelector("#orderStatusChart")) {
                                var orderTotal = {{ $retailerOrderStats['total'] }};
                                var statusSeries = orderTotal > 0 ? [
                                        {{ $retailerOrderStats['pending'] }},
                                        {{ $retailerOrderStats['processing'] }},
                                        {{ $retailerOrderStats['approved'] }},
                                        {{ $retailerOrderStats['delivered'] }},
                                        {{ $retailerOrderStats['cancelled'] }}
                                ] : [1];
                                var statusLabels = orderTotal > 0 ? ['Pending', 'Processing', 'Accepted', 'Delivered', 'Cancelled'] : ['No Orders Yet'];
                                var statusColors = orderTotal > 0 ? ['#f59e0b', '#3b82f6', '#0ea5e9', '#10b981', '#ef4444'] : ['#e2e8f0'];

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
                                if (document.querySelector("#monthlyDistOrdersChart")) {
                                    var monthlyDistOptions = {
                                        series: [{
                                            name: "Distributor Orders",
                                            data: @json($monthlyDistributorOrdersChart['counts'])
                                        }],
                                        chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false } },
                                        dataLabels: { enabled: false },
                                        stroke: { curve: 'smooth', width: 3 },
                                        xaxis: { categories: @json($monthlyDistributorOrdersChart['labels']) },
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