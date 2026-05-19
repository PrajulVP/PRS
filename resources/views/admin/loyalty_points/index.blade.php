@extends('layouts.admin')

@section('page-body')
    <style>
        /* Modern Select2 Styling */
        .select2-container--default .select2-selection--single {
            border-radius: 12px !important;
            height: 52px !important;
            border: 1px solid var(--med-border, #dee2e6) !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 16px !important;
            background: var(--med-bg-card, #ffffff) !important;
            transition: all 0.3s ease;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--med-text-main, #333) !important;
            font-weight: 500;
        }

        /* Dashboard Summary Cards (Glassmorphism) */
        .summary-card {
            background: var(--med-bg-card, #ffffff);
            border: 1px solid var(--med-border, #f1f5f9);
            border-radius: 20px !important;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.05) !important;
        }
        .summary-icon-box {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        /* DataTable Enhancements */
        .standard-table thead th {
            padding: 18px 25px !important;
            background: rgba(148, 163, 184, 0.05) !important;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--med-border, #cbd5e0) !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: var(--med-text-muted, #475569);
        }
        .standard-table tbody td {
            padding: 20px 25px !important;
            border-bottom: 1px solid var(--med-border, #f1f5f9) !important;
            vertical-align: middle !important;
        }
        .table-controls-row {
            padding: 10px 25px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            border-bottom: 1px solid var(--med-border, #cbd5e0) !important;
            background: rgba(148, 163, 184, 0.1) !important;
        }
        .dt-buttons {
            display: flex !important;
            gap: 12px !important;
            margin: 15px 20px !important; /* Vertical and Horizontal margin */
        }
        .dt-buttons .btn {
            margin: 0 !important;
            border-radius: 8px !important;
            padding: 8px 16px !important;
            font-weight: 600 !important;
        }
        .dataTables_filter {
            margin: 15px 20px !important; /* Vertical and Horizontal margin */
        }
        .dataTables_filter label {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            margin: 0 !important;
        }
        .dataTables_filter input {
            padding: 8px 20px !important;
            border-radius: 20px !important;
            border: 1px solid var(--med-border, #cbd5e0) !important;
            min-width: 250px !important;
            background: #fff !important;
        }
        .dt-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        }

        /* Branding Colors */
        .bg-glass-primary { background: rgba(0, 73, 122, 0.05); color: #00497a; }
        .bg-glass-warning { background: rgba(255, 215, 0, 0.1); color: #daa520; }
        .bg-glass-success { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }

        /* Animation Helpers */
        .entrance-fade { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .summary-card.bg-navy { 
            background: linear-gradient(135deg, #00497a 0%, #002b5c 100%) !important; 
        }
        .summary-card.bg-navy h4, .summary-card.bg-navy h5, .summary-card.bg-navy small {
            color: #ffffff !important;
        }

        /* Filter Alignment Fix */
        .btn-clear-filter {
            position: absolute;
            right: 35px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #dc3545;
            padding: 0;
            cursor: pointer;
            z-index: 10;
        }
        .filter-select {
            padding-right: 60px !important;
        }
        
        /* Pale Points Style */
        .points-box-simple {
            background: #f8f9fa;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        /* Hide Unnecessary Loading Icon (Oval) */
        /* Pagination and Spacing Fixes */
        .dataTables_info {
            padding-left: 30px !important;
            padding-bottom: 25px !important;
            padding-top: 25px !important;
            color: var(--med-text-muted, #64748b) !important;
            font-size: 0.85rem !important;
        }
        .dataTables_paginate {
            padding-right: 30px !important;
            padding-bottom: 25px !important;
            padding-top: 25px !important;
        }
        .dataTables_paginate .paginate_button {
            border: none !important;
            border-radius: 8px !important;
            margin: 0 2px !important;
            background: transparent !important;
            padding: 5px 12px !important;
        }
        .dataTables_paginate .paginate_button.current {
            background: var(--med-primary, #00497a) !important;
            color: white !important;
            border: none !important;
        }
        .dataTables_paginate .paginate_button:hover {
            background: rgba(0, 73, 122, 0.1) !important;
            color: var(--med-primary, #00497a) !important;
            border: none !important;
        }
    </style>

    <div class="container-fluid">
        <div class="page-title">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="fw-bold m-0 heading-theme">Loyalty & Credits</h3>
                    <p class="text-muted small m-0">Performance analytics and reward tracking for retailers</p>
                </div>
                <div class="col-sm-6 text-end">
                    @if($selectedRetailer)
                        <a href="{{ route('admin.loyalty-points.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                            <i class="fa fa-arrow-left me-2"></i>Back to Overview
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>



    <div class="container-fluid">
        <div class="row">
            @if(!$selectedRetailer)
            <!-- COMPACT GLOBAL STATS BAR -->
            <div class="col-12 mb-4 entrance-fade">
                <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-radius: 16px; border: 1px solid rgba(0, 73, 122, 0.05);">
                    <div class="card-body py-3 px-4">
                        <div class="row align-items-center text-center text-md-start">
                            <!-- Total Retailers -->
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                    <div class="bg-glass-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: rgba(0, 73, 122, 0.08);">
                                        <i data-feather="users" class="text-primary" style="width: 18px; height: 18px;"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small fw-bold mb-0 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Network Reach</p>
                                        <h5 class="fw-800 mb-0 heading-theme">{{ count($retailers) }} <span class="small fw-600 text-muted" style="font-size: 0.75rem;">Retailers</span></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Total Loyalty Points -->
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                    <div class="bg-glass-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: rgba(218, 165, 32, 0.1);">
                                        <i data-feather="star" style="width: 18px; height: 18px; color: #daa520;"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small fw-bold mb-0 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">System Liability</p>
                                        <h5 class="fw-800 mb-0 heading-theme">{{ number_format($globalLoyaltyPoints, 2) }} <span class="small fw-600 text-muted" style="font-size: 0.75rem;">Points</span></h5>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Wallet Credits -->
                            <div class="col-md-4">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                    <div class="bg-glass-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: rgba(46, 204, 113, 0.1);">
                                        <i data-feather="credit-card" class="text-success" style="width: 18px; height: 18px;"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small fw-bold mb-0 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Refund Pool</p>
                                        <h5 class="fw-800 mb-0 heading-theme">₹{{ number_format($retailers->sum('credit_balance'), 2) }} <span class="small fw-600 text-muted" style="font-size: 0.75rem;">Credits</span></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OVERVIEW LIST VIEW (Shown by Default) -->
            <div id="overview-view" class="col-12 entrance-fade">
                <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden; background: var(--med-bg-card);">
                    <div class="card-header loyalty-card-header py-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1 heading-theme">Retailer Loyalty & Credits</h5>
                            <p class="text-muted small mb-0">Monitor loyalty points and transaction history across all retailers.</p>
                        </div>
                        <div class="d-flex gap-3 align-items-center" style="width: 50%;">
                             <div class="flex-grow-1">
                                <select id="retailer_selector" class="form-select select2">
                                    <option value="">-- Quick Search Retailer --</option>
                                    @foreach($retailers as $r)
                                        <option value="{{ $r->id }}" data-points="{{ number_format($r->loyalty_points, 2) }}" data-credits="{{ number_format($r->credit_balance, 2) }}">
                                            {{ $r->shop_name }} ({{ $r->user->name }})
                                        </option>
                                    @endforeach
                                </select>
                             </div>
                        </div>
                    </div>
                    <div id="overview-table-controls" class="table-controls-row">
                        @if(auth()->user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                        <div class="d-flex align-items-center gap-3">
                            <form id="filter-form" action="{{ route('admin.loyalty-points.index') }}" method="GET" class="d-flex align-items-center gap-3 mb-0">
                                @if(auth()->user()->hasAnyRole(['admin', 'superadmin']))
                                <div class="position-relative" style="min-width: 200px;">
                                    <select id="sm-filter" name="sales_manager_id" class="form-select select2-basic filter-select">
                                        <option value="">Sales Managers</option>
                                        @foreach($salesManagers as $sm)
                                            <option value="{{ $sm->id }}" {{ request('sales_manager_id') == $sm->id ? 'selected' : '' }}>
                                                {{ $sm->user->name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(request('sales_manager_id'))
                                        <button type="button" class="btn-clear-filter" data-target="sm-filter" title="Clear Sales Manager">
                                            <i class="fa fa-times-circle"></i>
                                        </button>
                                    @endif
                                </div>
                                @endif
                                <div class="position-relative" style="min-width: 200px;">
                                    <select id="fs-filter" name="field_staff_id" class="form-select select2-basic filter-select">
                                        <option value="">Field Staffs</option>
                                        @foreach($fieldStaffs as $fs)
                                            <option value="{{ $fs->id }}" {{ request('field_staff_id') == $fs->id ? 'selected' : '' }}>
                                                {{ $fs->user->name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(request('field_staff_id'))
                                        <button type="button" class="btn-clear-filter" data-target="fs-filter" title="Clear Field Staff">
                                            <i class="fa fa-times-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                        @endif
                        <div class="d-flex align-items-center gap-3 ms-auto right-controls">
                            <!-- DT Buttons and Search will be moved here -->
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 standard-table" id="overview-table" style="width: 100%;">
                                @php
                                    $user = auth()->user();
                                    if ($user->hasAnyRole(['admin', 'superadmin'])) {
                                        $loyaltyColIndex = 7;
                                    } elseif ($user->hasRole('salesmanager')) {
                                        $loyaltyColIndex = 6;
                                    } else {
                                        $loyaltyColIndex = 5;
                                    }
                                @endphp
                                <thead>
                                    <tr>
                                        <th>Retailer Shop</th>
                                        <th>Owner Name</th>
                                        @if(auth()->user()->hasAnyRole(['admin', 'superadmin']))
                                            <th>Sales Manager</th>
                                        @endif
                                        @if(auth()->user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                                            <th>Field Staff</th>
                                        @endif
                                        <th>Region & Area</th>
                                        <th class="text-center">Total Orders</th>
                                        <th>Last Order</th>
                                        <th class="text-center py-3">Accumulated Points</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Server-side AJAX --}}
                                @if(false)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    {{-- <div class="avatar-xs bg-glass-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fa fa-shopping-bag small"></i>
                                                    </div> --}}
                                                    <div class="fw-bold heading-theme">{{ $r->shop_name }}</div>
                                                </div>
                                            </td>
                                            <td class="sub-heading-theme">{{ $r->user->name ?? 'N/A' }}</td>
                                            @if(auth()->user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                                                <td class="small sub-heading-theme">
                                                    {{ $r->salesManager->user->name ?? 'N/A' }}
                                                </td>
                                                <td class="small sub-heading-theme">
                                                    {{ $r->fieldStaff->user->name ?? 'N/A' }}
                                                </td>
                                            @endif
                                            <td class="small sub-heading-theme">
                                                {{ $r->district->name ?? 'N/A' }}, {{ $r->area->name ?? 'N/A' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-points px-3 py-2" style="font-size: 0.9rem;">
                                                    {{ number_format($r->dynamic_loyalty_points, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-primary btn-xs rounded-pill px-3 fw-bold detail-btn" data-id="{{ $r->id }}">
                                                    View Details
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- DETAILED RETAILER VIEW (Shown when a retailer is selected) --            <!-- DETAILED RETAILER VIEW (Shown when a retailer is selected) -->
            <!-- DETAILED RETAILER VIEW (Shown when a retailer is selected) -->
            <div id="detail-view" class="col-12 entrance-fade">
                <div class="row g-3">
                    <!-- Retailer Profile Card -->
                    <div class="col-xl-6 mb-3">
                        <div class="card shadow-sm border-0 h-100" style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(0, 73, 122, 0.1); border-radius: 20px !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-light">
                                    <div class="d-flex align-items-center justify-content-center bg-glass-primary rounded-circle me-3" style="width: 42px; height: 42px; background: rgba(0, 73, 122, 0.05); border: 1px solid rgba(0, 73, 122, 0.1);">
                                        <i data-feather="shopping-bag" class="text-primary" style="width: 20px; height: 20px;"></i>
                                    </div>
                                    <div class="text-truncate">
                                        <h6 id="display_shop_name" class="fw-800 mb-0 heading-theme text-truncate" style="letter-spacing: -0.3px;">...</h6>
                                        <p id="display_owner_name" class="text-muted mb-0 small fw-600" style="font-size: 0.75rem;">...</p>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="text-muted small d-block text-uppercase fw-bold mb-0" style="font-size: 0.6rem; letter-spacing: 0.5px;">Phone</label>
                                        <span id="display_phone" class="heading-theme fw-700 small">...</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block text-uppercase fw-bold mb-0" style="font-size: 0.6rem; letter-spacing: 0.5px;">Region</label>
                                        <span id="display_region" class="heading-theme fw-700 small text-truncate d-block">...</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block text-uppercase fw-bold mb-0" style="font-size: 0.6rem; letter-spacing: 0.5px;">Email</label>
                                        <span id="display_email" class="heading-theme fw-700 small text-break">...</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block text-uppercase fw-bold mb-0" style="font-size: 0.6rem; letter-spacing: 0.5px;">Joined</label>
                                        <span id="display_joined" class="heading-theme fw-700 small">...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loyalty Points Card -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card shadow-sm border-0 h-100 overflow-hidden summary-card" style="background: linear-gradient(135deg, #00497a 0%, #002b5c 100%); border-radius: 20px !important;">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 16px; box-shadow: 0 8px 16px rgba(0,0,0,0.15);">
                                        <i data-feather="star" class="text-white" style="width: 22px; height: 22px; filter: drop-shadow(0 0 5px rgba(255,255,255,0.4));"></i>
                                    </div>
                                    <span class="badge rounded-pill px-3 py-1 fw-bold" style="font-size: 10px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); color: #fff; letter-spacing: 0.5px;">POINTS</span>
                                </div>
                                <div class="flex-grow-1 text-white">
                                    <h1 id="display_total_points" class="fw-300 mb-0 display-6" style="line-height: 1; color: #fff !important; letter-spacing: -1px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">0.00</h1>
                                    <p class="text-white opacity-75 small mb-0 fw-600 mt-2">Earned from orders</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Credit Balance Card -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card shadow-sm border-0 h-100 overflow-hidden summary-card" style="background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); border-radius: 20px !important;">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 16px; box-shadow: 0 8px 16px rgba(0,0,0,0.15);">
                                        <i data-feather="credit-card" class="text-white" style="width: 22px; height: 22px; filter: drop-shadow(0 0 5px rgba(255,255,255,0.4));"></i>
                                    </div>
                                    <span class="badge rounded-pill px-3 py-1 fw-bold" style="font-size: 10px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); color: #fff; letter-spacing: 0.5px;">CREDITS</span>
                                </div>
                                <div class="flex-grow-1 text-white">
                                    <h1 id="display_credit_balance" class="fw-300 mb-0 display-6" style="line-height: 1; color: #fff !important; letter-spacing: -1px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">₹0.00</h1>
                                    <p class="text-white opacity-75 small mb-0 fw-600 mt-2">Refunds & credits</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>        


                    <!-- Transaction Logs -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0 overflow-hidden" style="border-radius: 20px; background: var(--med-bg-card);">
                            <div class="card-header loyalty-card-header py-4 px-4 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 heading-theme">Transaction Statement</h5>
                            </div>
                            <div id="detail-table-controls" class="table-controls-row d-flex justify-content-between align-items-center">
                                <div class="left-controls d-flex align-items-center gap-3"></div>
                                <div class="right-controls d-flex align-items-center gap-3"></div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover align-middle mb-0" id="points-table" style="width: 100%;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>   
                                            <th>Reference</th>
                                            <th>Details</th>
                                            <th class="py-3">Points Earned</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="heading-theme">
                                        <!-- AJAX Loaded -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        $(document).ready(function () {
            // Main Overview Table
            @if(!$selectedRetailer)
            var overviewTable = $('#overview-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.loyalty-points.index') }}",
                    data: function (d) {
                        d.sales_manager_id = $('#sm-filter').val();
                        d.field_staff_id = $('#fs-filter').val();
                    }
                },
                pageLength: 10,
                dom: "Bfrtip",
                columns: [
                    { data: 'shop_name', name: 'shop_name' },
                    { data: 'owner_name', name: 'owner_name' },
                    @if(auth()->user()->hasAnyRole(['admin', 'superadmin']))
                        { data: 'sales_manager', name: 'sales_manager' },
                    @endif
                    @if(auth()->user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                        { data: 'field_staff', name: 'field_staff' },
                    @endif
                    { data: 'region_area', name: 'region_area' },
                    { data: 'total_orders', name: 'total_orders', className: 'text-center', searchable: false },
                    { data: 'last_order', name: 'last_order', className: 'text-center', searchable: false },
                    { data: 'dynamic_loyalty_points', name: 'dynamic_loyalty_points', className: 'text-center', searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                initComplete: function() {
                    let containerRight = $('#overview-table-controls .right-controls');
                    let $tableApi = $(this).DataTable();
                    let $wrapper = $($tableApi.table().container());
                    
                    // Move search and buttons to overview row (right)
                    $wrapper.find('.dt-buttons').appendTo(containerRight);
                    $wrapper.find('.dataTables_filter').appendTo(containerRight);
                    $wrapper.find('.dataTables_filter input').addClass('form-control-sm rounded-pill px-3');
                },
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search retailers...",
                    processing: '<div class="spinner-border text-primary" role="status"></div>'
                }
            });

            // Reload table on filter change
            $('#sm-filter, #fs-filter').on('change', function() {
                overviewTable.ajax.reload();
            });

            // Auto-submit filter form (Dynamic AJAX Reload)
            $('.filter-select').on('change', function() {
                if ($(this).attr('id') === 'sm-filter') {
                    let smId = $(this).val();
                    // Update FS dropdown dynamically
                    $.get("{{ route('admin.loyalty-points.field-staffs-by-manager') }}", { sales_manager_id: smId }, function(data) {
                        let fsSelect = $('#fs-filter');
                        fsSelect.empty();
                        fsSelect.append('<option value="">All Field Staff</option>');
                        data.forEach(function(item) {
                            fsSelect.append(`<option value="${item.id}">${item.name}</option>`);
                        });
                        fsSelect.trigger('change.select2');
                        overviewTable.ajax.reload();
                    });
                } else {
                    overviewTable.ajax.reload();
                }
            });

            // Handle individual clear buttons
            $('.btn-clear-filter').on('click', function(e) {
                e.preventDefault();
                let target = $(this).data('target');
                $(`#${target}`).val('').trigger('change');
            });
            
            // Basic Select2 for filters
            $('.select2-basic').select2({
                width: '100%'
            });

            // Select2 custom template
            $('#retailer_selector').select2({
                placeholder: "-- Quick Search Retailer --",
                allowClear: true,
                width: '100%',
                templateResult: formatRetailer,
                templateSelection: formatRetailerSelection
            });

            function formatRetailer(state) {
                if (!state.id) return state.text;
                let points = $(state.element).data('points') || '0.00';
                let credits = $(state.element).data('credits') || '0.00';
                return $(
                    `<div class="d-flex justify-content-between align-items-center">
                        <div class="fw-bold">${state.text}</div>
                        <div class="d-flex gap-2">
                            <div class="points-box-simple">${points} Pts</div>
                            <div class="points-box-simple" style="background: #e0f2fe; color: #0369a1;">₹${credits} Cr</div>
                        </div>
                    </div>`
                );
            }

            function formatRetailerSelection(state) {
                if (!state.id) return state.text;
                let points = $(state.element).data('points') || '0.00';
                let credits = $(state.element).data('credits') || '0.00';
                return $(
                    `<div class="d-flex justify-content-between align-items-center w-100">
                        <span class="fw-bold text-truncate" style="max-width: 60%">${state.text}</span>
                        <div class="d-flex gap-1">
                            <div class="points-box-simple">${points} Pts</div>
                            <div class="points-box-simple" style="background: #e0f2fe; color: #0369a1;">₹${credits}</div>
                        </div>
                    </div>`
                );
            }

            // Handle Selection/Drill-down - Change to REAL REDIRECT for separate page feel
            $('#retailer_selector').on('change', function () {
                let id = $(this).val();
                if (id) {
                    window.location.href = "{{ route('admin.loyalty-points.detail', ':id') }}".replace(':id', id);
                }
            });
            @endif

            @if($selectedRetailer)
                // Initialize detail view immediately if selected
                fetchData("{{ $selectedRetailer->id }}");
            @endif

            function fetchData(retailerId) {
                $.get("{{ route('admin.loyalty-points.summary', ':id') }}".replace(':id', retailerId), function (data) {
                    $('#display_shop_name').text(data.shop_name);
                    $('#display_owner_name').text(data.owner_name);
                    $('#display_phone').text(data.phone);
                    $('#display_email').text(data.email);
                    $('#display_region').text(data.district + ', ' + data.area);
                    $('#display_joined').text(data.joined_date);
                    $('#display_total_points, #available_points').text(parseFloat(data.total_points).toFixed(2));
                    $('#display_credit_balance, #available_credits').text('₹' + parseFloat(data.credit_balance).toFixed(2));
                    
                    // Show trophy if top retailer
                    if (data.is_top_retailer) {
                        $('#top_performer_badge').css('display', 'flex');
                    } else {
                        $('#top_performer_badge').hide();
                    }
                });

                if ($.fn.DataTable.isDataTable('#points-table')) {
                    $('#points-table').DataTable().destroy();
                    // Clear custom containers to prevent duplication
                    $('#detail-table-controls .left-controls, #detail-table-controls .right-controls').empty();
                }

                $('#points-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.loyalty-points.index') }}",
                        data: function (d) { d.retailer_id = retailerId; }
                    },
                    dom: "Bfrtip", // We'll move them manually for better control
                    initComplete: function() {
                        // Move controls TO LOCAL detail container
                        let containerLeft = $('#detail-table-controls .left-controls');
                        let containerRight = $('#detail-table-controls .right-controls');
                        
                        let $tableApi = $(this).DataTable();
                        let $wrapper = $($tableApi.table().container());
                        
                        // Buttons on the left, Search on the right (proximal to the detail table)
                        $wrapper.find('.dt-buttons').appendTo(containerLeft);
                        $wrapper.find('.dataTables_filter').appendTo(containerRight);
                        $wrapper.find('.dataTables_filter input').addClass('form-control-sm rounded-pill px-3');
                    },
                    buttons: [
                        { extend: 'copy', className: 'btn btn-outline-secondary btn-xs rounded-pill px-3' },
                        { extend: 'csv', className: 'btn btn-outline-secondary btn-xs rounded-pill px-3' },
                        { extend: 'excel', className: 'btn btn-outline-secondary btn-xs rounded-pill px-3' },
                        { extend: 'pdf', className: 'btn btn-outline-secondary btn-xs rounded-pill px-3' },
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Filter transactions..."
                    },
                    columns: [
                        { data: 'updated_at', name: 'updated_at' },
                        { data: 'order_code', name: 'order_code', render: d => `<strong class="text-primary">#${d}</strong>` },
                        { data: 'product_summary', name: 'product_summary', orderable: false, className: 'small' },
                        {
                            data: 'loyalty_points_earned',
                            name: 'loyalty_points_earned',
                            className: 'text-center fw-bold',
                            render: data => parseFloat(data).toFixed(2)
                        },
                        {
                            data: 'status',
                            name: 'status',
                            className: 'text-center'
                        }
                    ],
                    order: [[0, 'desc']],
                    pageLength: 10
                });
            }
        });
    </script>
@endpush