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
        .standard-table tbody tr {
            transition: all 0.25s ease;
        }
        .standard-table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.06);
            background-color: #ffffff !important;
            z-index: 10;
            position: relative;
        }
        .standard-table tbody td {
            padding: 20px 25px !important;
            border-bottom: 1px solid var(--med-border, #f1f5f9) !important;
            vertical-align: middle !important;
            transition: background-color 0.2s ease;
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
            gap: 6px !important;
            margin: 0 !important;
            flex-wrap: wrap !important;
            justify-content: flex-end;
        }
        .dt-buttons .btn {
            margin: 0 !important;
            border-radius: 6px !important;
            padding: 5px 12px !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            background: #f8fafc !important;
            color: #475569 !important;
            border: 1px solid #cbd5e0 !important;
            text-transform: uppercase !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            transition: all 0.2s ease;
        }
        .dt-buttons .btn:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(0,0,0,0.08) !important;
        }
        
        /* Restore search input styles */
        .dataTables_filter {
            margin: 15px 20px !important;
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
        
        /* Modern Reset Button */
        .reset-filter-btn {
            border-radius: 12px !important;
            padding: 0 24px !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            height: 52px !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            color: #64748b !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
            transition: all 0.2s ease !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .reset-filter-btn:hover {
            background-color: #fff1f2 !important;
            color: #e11d48 !important;
            border-color: #fecdd3 !important;
            transform: translateY(-1px);
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
                    <h3 class="fw-bold m-0 heading-theme">Retailer Loyalty</h3>
                    <p class="text-muted small m-0">Track points and manage reward redemptions for retailers</p>
                </div>
                <div class="col-sm-6 text-end">
                    @if($selectedRetailer)
                        <div id="detail-export-container" class="d-inline-flex gap-2 me-3"></div>
                        <a href="{{ route('admin.loyalty-points.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                            <i class="fa fa-arrow-left me-2"></i>Back to Overview
                        </a>
                    @else
                        <div id="overview-export-container" class="d-inline-flex gap-2 align-items-center">
                            <!-- Export buttons will be injected here -->
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>



    <div class="container-fluid">
        <div class="row">
            @if(!$selectedRetailer)
            <!-- UNIFIED REWARD CLAIMS ACTIVITY SECTION -->
            <div class="col-12 mb-4 entrance-fade">
                <div class="card shadow-sm border-0 summary-card" style="border-radius: 20px !important;">
                    <div class="card-header border-0 pt-4 pb-2 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3" style="background: transparent;">
                        <div class="d-flex align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-primary"><i class="fa fa-gift me-2 text-primary"></i>Reward Claims Activity</h5>
                                <p class="small text-muted mt-1 mb-0">Track pending claims and fulfilled reward history.</p>
                            </div>
                            <!-- Clean Toggle Pills -->
                            <div class="nav nav-pills bg-light p-1 rounded-pill ms-sm-2 border" id="activity-tab-group" style="background: var(--med-bg-subtle, #f8fafc) !important; border-color: var(--med-border, #e2e8f0) !important;">
                                <button class="nav-link active rounded-pill px-3 py-1 text-uppercase fw-bold activity-tab-btn" data-tab="pending" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    Pending Claims
                                </button>
                                <button class="nav-link rounded-pill px-3 py-1 text-uppercase fw-bold activity-tab-btn" data-tab="approved" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    Approved
                                </button>
                                <button class="nav-link rounded-pill px-3 py-1 text-uppercase fw-bold activity-tab-btn" data-tab="delivered" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    Delivered
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                            <input type="date" id="activity_from_date" value="{{ request('from_date') }}" class="form-control form-control-sm rounded-3 activity-date-input" placeholder="From Date" style="max-width: 140px;">
                            <span class="text-muted small">to</span>
                            <input type="date" id="activity_to_date" value="{{ request('to_date') }}" class="form-control form-control-sm rounded-3 activity-date-input" placeholder="To Date" style="max-width: 140px;">
                            <button type="button" id="btn-activity-filter" class="btn btn-sm btn-primary rounded-pill px-3"><i class="fa fa-filter me-1"></i> Filter</button>
                            <button type="button" id="btn-activity-clear" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-none">Clear</button>
                        </div>
                    </div>
                    <div class="card-body p-4 position-relative">
                        <div id="activity-table-container">
                            @include('admin.loyalty_points.partials.activity_table', [
                                'pendingRedemptions' => $pendingRedemptions,
                                'completedRedemptions' => $completedRedemptions
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <!-- OVERVIEW LIST VIEW (Shown by Default) -->
            <div id="overview-view" class="col-12 entrance-fade">
                <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden; background: var(--med-bg-card);">
                    <div class="card-header loyalty-card-header py-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1 heading-theme">Retailer Loyalty Points</h5>
                            <p class="text-muted small mb-0">Monitor loyalty points and reward eligibility across all retailers.</p>
                        </div>
                        <div class="d-flex gap-3 align-items-center" style="width: 50%;">
                             <div class="flex-grow-1">
                                <select id="retailer_selector" class="form-select select2">
                                    <option value="">-- Quick Search Retailer --</option>
                                    @foreach($retailers as $r)
                                        <option value="{{ $r->id }}" data-points="{{ number_format($r->loyalty_points, 2) }}">
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
                                <a href="{{ route('admin.loyalty-points.index') }}" class="btn reset-filter-btn">
                                    <i class="fa fa-undo"></i> Reset
                                </a>
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
                                        <th class="text-center" style="min-width: 120px;">Available Points</th>
                                        <th class="text-center" style="min-width: 200px;">Reward Progress</th>
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
            <!-- DETAILED RETAILER VIEW (Shown when a retailer is selected) -->
            <div id="detail-view" class="col-12 entrance-fade">
                <!-- Retailer Profile Card & Points Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 summary-card" style="border-radius: 16px !important; border: 1px solid var(--med-border, #e2e8f0) !important;">
                            <div class="card-body p-4">
                                <div class="row align-items-center g-3">
                                    <div class="col-lg-4" style="border: none !important;">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 52px; height: 52px; background-color: rgba(13, 110, 253, 0.1); border: 1px solid rgba(13, 110, 253, 0.25); flex-shrink: 0;">
                                                <i class="fa fa-shopping-bag fa-lg text-primary"></i>
                                            </div>
                                            <div class="text-truncate">
                                                <h5 id="display_shop_name" class="fw-bold mb-1 text-truncate text-main-theme" style="font-weight: 800;">{{ $selectedRetailer->shop_name }}</h5>
                                                <p id="display_owner_name" class="mb-0 small fw-semibold text-muted"><i class="fa fa-user me-1"></i>{{ $selectedRetailer->user->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <label class="small d-block text-uppercase fw-bold mb-0 text-muted" style="font-size: 0.65rem; letter-spacing: 0.5px;">Phone</label>
                                                <span id="display_phone" class="fw-bold small text-main-theme">{{ $selectedRetailer->contact_no ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="small d-block text-uppercase fw-bold mb-0 text-muted" style="font-size: 0.65rem; letter-spacing: 0.5px;">Region & Area</label>
                                                <span id="display_region" class="fw-bold small text-truncate d-block text-main-theme">{{ ($selectedRetailer->district->name ?? '') . ', ' . ($selectedRetailer->area->name ?? '') }}</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="small d-block text-uppercase fw-bold mb-0 text-muted" style="font-size: 0.65rem; letter-spacing: 0.5px;">Email</label>
                                                <span id="display_email" class="fw-bold small text-break text-main-theme">{{ $selectedRetailer->user->email ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="small d-block text-uppercase fw-bold mb-0 text-muted" style="font-size: 0.65rem; letter-spacing: 0.5px;">Joined</label>
                                                <span id="display_joined" class="fw-bold small text-main-theme">{{ $selectedRetailer->created_at ? $selectedRetailer->created_at->format('d M Y') : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 text-lg-end" style="border: none !important;">
                                        <div class="p-3 rounded-3 d-inline-block w-100 text-center text-lg-end" style="background-color: rgba(13, 110, 253, 0.08) !important; border: 1px solid rgba(13, 110, 253, 0.2) !important;">
                                            <span class="small text-uppercase fw-bold d-block mb-1 text-primary">Available Points</span>
                                            <h4 class="fw-bold mb-0"><span id="available_points" style="color: #0d6efd !important; font-weight: 800;">{{ number_format($selectedRetailer->loyalty_points ?? 0, 2) }}</span> <span class="fs-6 fw-normal text-primary">pts</span></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>        

                <!-- INLINE BRAND REWARDS PROGRESS -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 summary-card" style="border-radius: 16px; border: 1px solid var(--med-border, #e2e8f0) !important;">
                            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center border-bottom" style="border-radius: 16px 16px 0 0; border-color: var(--med-border, #e2e8f0) !important;">
                                <div>
                                    <h5 class="fw-bold mb-0 text-primary"><i class="fa fa-gift me-2 text-primary"></i>Brand Rewards Progress</h5>
                                    <p class="small text-muted mb-0 mt-1">Loyalty slabs and progress milestones for all available brands.</p>
                                </div>
                            </div>
                            <div class="card-body p-4" style="border-radius: 0 0 16px 16px;">
                                <div class="row g-3">
                                    @include('admin.loyalty_points.partials.rewards_progress', ['retailer' => $selectedRetailer, 'upcomingRewards' => $upcomingRewards])
                                </div>
                            </div>
                        </div>
                    </div>
                <!-- TRANSACTION & CLAIM HISTORY TABLE -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 summary-card" style="border-radius: 16px; border: 1px solid var(--med-border, #e2e8f0) !important;">
                            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center border-bottom" style="border-radius: 16px 16px 0 0; border-color: var(--med-border, #e2e8f0) !important;">
                                <div>
                                    <h5 class="fw-bold mb-0 text-main-theme"><i class="fa fa-history me-2 text-primary"></i>Claim History</h5>
                                    <p class="small text-muted mb-0 mt-1">Detailed history of reward redemptions claimed.</p>
                                </div>
                                <div id="detail-table-controls" class="d-flex align-items-center gap-2">
                                    <div class="right-controls"></div>
                                </div>
                            </div>
                            <div class="card-body p-0" style="border-radius: 0 0 16px 16px;">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 standard-table" id="points-table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Reference #</th>
                                                <th>Details</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- REWARD CLAIM DETAILS POPUP MODAL -->
    <div class="modal fade" id="redemptionDetailModal" tabindex="-1" aria-labelledby="redemptionDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden summary-card">
                <div class="modal-header text-white px-4 py-3 border-0" style="background: linear-gradient(135deg, #00497a 0%, #002b5c 100%) !important;">
                    <h5 class="modal-title fw-bold text-white mb-0" id="redemptionDetailModalLabel">
                        <i class="fa fa-gift text-warning me-2"></i><span class="text-white">Reward Claim Details</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1); opacity: 0.9;"></button>
                </div>
                <div class="modal-body p-4" id="modal-loading-state">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary me-2" role="status"></div>
                        <span class="text-muted fw-semibold">Loading claim details...</span>
                    </div>
                </div>
                <div class="modal-body p-4 d-none" id="modal-content-state">
                    <!-- Retailer Info Card & Status Badge -->
                    <div class="card border-0 rounded-3 p-3 mb-4" style="background-color: var(--med-bg-subtle, #f8fafc); border: 1px solid var(--med-border, #e2e8f0) !important;">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <span class="text-muted small text-uppercase fw-bold d-block">Retailer Shop</span>
                                <h6 class="fw-bold mb-0 text-main-theme" id="modal_shop_name">-</h6>
                                <span class="text-muted small d-block" id="modal_owner_name">-</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small text-uppercase fw-bold d-block">Contact</span>
                                <span class="fw-semibold small text-main-theme d-block" id="modal_contact">-</span>
                                <span class="text-muted small d-block text-truncate" id="modal_email">-</span>
                            </div>
                            <div class="col-md-2">
                                <span class="text-muted small text-uppercase fw-bold d-block">Location</span>
                                <span class="fw-semibold small text-main-theme d-block" id="modal_location">-</span>
                            </div>
                            <div class="col-md-2 text-md-end">
                                <span class="text-muted small text-uppercase fw-bold d-block mb-1">Status</span>
                                <div id="modal_action_container"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Reward Claim Info -->
                    <div class="card border-0 rounded-3 p-3 mb-2" style="background-color: rgba(13, 110, 253, 0.05); border: 1px solid rgba(13, 110, 253, 0.15) !important;">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fa fa-star me-1"></i> Claimed Reward Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <span class="text-muted small d-block fw-bold">Brand</span>
                                <span class="badge bg-primary text-white fs-6 mt-1" id="modal_brand">-</span>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small d-block fw-bold">Target Threshold</span>
                                <span class="fw-bold fs-6 text-main-theme" id="modal_threshold">- Points</span>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small d-block fw-bold">Selected Reward</span>
                                <span class="fw-bold fs-6 text-success" id="modal_selected_reward">-</span>
                            </div>
                            <div class="col-12 pt-2">
                                <span class="text-muted small d-block fw-bold mb-1">Available Slab Reward Options:</span>
                                <div id="modal_options_list" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 border-0 justify-content-end" style="background-color: var(--med-bg-card, #ffffff);">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
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
        $(document).on('click', '.btn-view-redemption-modal', function(e) {
            e.preventDefault();
            let redemptionId = $(this).data('id');
            let modal = $('#redemptionDetailModal');
            
            $('#modal-loading-state').removeClass('d-none');
            $('#modal-content-state').addClass('d-none');
            modal.modal('show');

            $.get("{{ route('admin.loyalty-points.redemption-details', ':id') }}".replace(':id', redemptionId), function(response) {
                let r = response.redemption;
                let ret = response.retailer;

                $('#modal_shop_name').text(ret.shop_name);
                $('#modal_owner_name').text(ret.owner_name);
                $('#modal_contact').text(ret.contact_no);
                $('#modal_email').text(ret.email);
                $('#modal_location').text(ret.location);

                $('#modal_brand').text(r.brand);
                $('#modal_threshold').text(r.threshold + ' Pts');
                $('#modal_selected_reward').text(r.selected_reward);

                let optionsHtml = '';
                if (r.options && r.options.length > 0) {
                    r.options.forEach(opt => {
                        let isSelected = opt.trim().toLowerCase() === r.selected_reward.trim().toLowerCase();
                        let badgeClass = isSelected ? 'bg-success text-white' : 'bg-light text-dark border';
                        optionsHtml += `<span class="badge ${badgeClass} px-3 py-2 fs-6 me-1 mb-1"><i class="fa ${isSelected ? 'fa-check-circle' : 'fa-circle-o'} me-1"></i>${opt}</span>`;
                    });
                }
                $('#modal_options_list').html(optionsHtml);

                let actionHtml = '';
                if (r.status === 'pending') {
                    @if(Auth::user()->hasAnyRole(['superadmin', 'admin']))
                        actionHtml = `<form action="{{ route('admin.loyalty-points.mark-reward-given', ':retId') }}" method="POST" class="d-inline approve-reward-form">
                            @csrf
                            <input type="hidden" name="redemption_id" value="${r.id}">
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold btn-approve-reward"
                                    data-shop="${ret.shop_name}"
                                    data-owner="${ret.owner_name ?? ''}"
                                    data-reward="${r.selected_reward || r.gift_name}"
                                    data-brand="${r.brand}"
                                    data-points="${r.threshold}">
                                <i class="fa fa-check me-1"></i> Approve
                            </button>
                        </form>`;
                        actionHtml = actionHtml.replace(':retId', ret.id);
                    @else
                        actionHtml = `<span class="fw-bold" style="color: #d97706;"><i class="fa fa-clock-o me-1"></i>Pending</span>`;
                    @endif
                } else if (r.status === 'approved') {
                    actionHtml = `<span class="fw-bold text-success"><i class="fa fa-check-circle me-1"></i>Approved</span><div class="small text-muted" style="font-size:0.75rem;"><i class="fa fa-truck me-1"></i>Waiting for Delivery</div>`;
                } else if (r.status === 'delivered') {
                    actionHtml = `<span class="fw-bold text-success"><i class="fa fa-check-circle me-1"></i>Delivered</span>`;
                } else {
                    actionHtml = `<span class="fw-bold text-secondary">${r.status.toUpperCase()}</span>`;
                }
                $('#modal_action_container').html(actionHtml);

                $('#modal-loading-state').addClass('d-none');
                $('#modal-content-state').removeClass('d-none');
            }).fail(function() {
                alert('Failed to fetch claim details.');
                modal.modal('hide');
            });
        });

        // --- REWARD CLAIMS ACTIVITY TAB TOGGLE AJAX ---
        let currentActivityTab = 'pending';

        function loadActivityData() {
            let fromDate = $('#activity_from_date').val();
            let toDate = $('#activity_to_date').val();
            let container = $('#activity-table-container');

            container.html('<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div><span>Loading claims...</span></div>');

            $.get("{{ route('admin.loyalty-points.activity-data') }}", {
                tab: currentActivityTab,
                from_date: fromDate,
                to_date: toDate
            }, function(response) {
                let items = response.items;
                let isAdmin = response.is_admin;
                let csrf = response.csrf_token;
                let routeTemplate = "{{ route('admin.loyalty-points.mark-reward-given', ':retId') }}";

                if (!items || items.length === 0) {
                    let emptyMsg = currentActivityTab === 'pending' ? 'No pending reward claims found.' : (currentActivityTab === 'approved' ? 'No approved reward claims found.' : 'No delivered reward claims found.');
                    container.html(`
                        <div class="text-center py-4 text-muted">
                            <i class="fa fa-check-circle fa-2x mb-2 text-success"></i>
                            <p class="mb-0">${emptyMsg}</p>
                        </div>
                    `);
                    return;
                }

                let rowsHtml = '';
                items.forEach(function(item) {
                    let statusBadge = '';
                    if (item.status === 'pending') {
                        statusBadge = `<span class="fw-bold" style="color: #d97706;"><i class="fa fa-clock-o me-1"></i>Pending</span><div class="small text-muted" style="font-size:0.7rem;">Action Required</div>`;
                    } else if (item.status === 'approved') {
                        statusBadge = `<span class="fw-bold text-success"><i class="fa fa-check-circle me-1"></i>Approved</span><div class="small text-muted" style="font-size:0.75rem;"><i class="fa fa-truck me-1"></i>Waiting for Delivery</div>`;
                    } else if (item.status === 'delivered') {
                        statusBadge = `<span class="fw-bold text-success"><i class="fa fa-check-circle me-1"></i>Delivered</span><div class="small text-muted" style="font-size:0.7rem;">Fulfillment Completed</div>`;
                    } else {
                        statusBadge = `<span class="fw-bold text-secondary">${item.status.toUpperCase()}</span>`;
                    }

                    let actionBtn = '';
                    if (item.status === 'pending' && isAdmin) {
                        let actionUrl = routeTemplate.replace(':retId', item.retailer_id);
                        actionBtn += `
                            <form action="${actionUrl}" method="POST" class="d-inline approve-reward-form">
                                <input type="hidden" name="_token" value="${csrf}">
                                <input type="hidden" name="redemption_id" value="${item.redemption_id}">
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm btn-approve-reward"
                                        data-shop="${item.shop_name}"
                                        data-owner="${item.owner_name ?? ''}"
                                        data-reward="${item.reward_name}"
                                        data-brand="${item.brand}"
                                        data-points="${item.threshold_formatted}">
                                    <i class="fa fa-check me-1"></i> Approve
                                </button>
                            </form>
                        `;
                    }

                    actionBtn += `
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 ms-1 shadow-sm btn-view-redemption-modal" data-id="${item.redemption_id}">
                            View Details
                        </button>
                    `;

                    rowsHtml += `
                        <tr>
                            <td class="small">${item.date_formatted}</td>
                            <td>
                                <div class="fw-bold text-dark">${item.shop_name}</div>
                                <div class="small text-muted">${item.owner_name ?? ''}</div>
                            </td>
                            <td><span class="badge bg-soft-primary text-primary">${item.brand}</span></td>
                            <td>
                                <div class="fw-bold">${item.reward_name}</div>
                                <div class="small text-muted">Cost: ${item.threshold_formatted} Points</div>
                            </td>
                            <td>${statusBadge}</td>
                            <td class="text-end">${actionBtn}</td>
                        </tr>
                    `;
                });

                let tableHtml = `
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="small text-muted text-uppercase">Date & Time</th>
                                    <th class="small text-muted text-uppercase">Retailer</th>
                                    <th class="small text-muted text-uppercase">Brand</th>
                                    <th class="small text-muted text-uppercase">Reward Claimed</th>
                                    <th class="small text-muted text-uppercase">Status</th>
                                    <th class="small text-muted text-uppercase text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="activity-table-body">
                                ${rowsHtml}
                            </tbody>
                        </table>
                    </div>
                `;
                container.html(tableHtml);
            }).fail(function() {
                container.html('<div class="text-center py-4 text-danger"><p class="mb-0">Failed to load claims data.</p></div>');
            });
        }

        $(document).on('click', '.activity-tab-btn', function(e) {
            e.preventDefault();
            $('.activity-tab-btn').removeClass('active');
            $(this).addClass('active');
            currentActivityTab = $(this).data('tab');
            loadActivityData();
        });

        $('#btn-activity-filter').on('click', function() {
            if ($('#activity_from_date').val() || $('#activity_to_date').val()) {
                $('#btn-activity-clear').removeClass('d-none');
            }
            loadActivityData();
        });

        $('#btn-activity-clear').on('click', function() {
            $('#activity_from_date').val('');
            $('#activity_to_date').val('');
            $(this).addClass('d-none');
            loadActivityData();
        });

        // --- SWEETALERT CONFIRMATION POPUP FOR REWARD APPROVAL ---
        $(document).on('click', '.btn-approve-reward', function(e) {
            e.preventDefault();
            let button = $(this);
            let form = button.closest('form');
            let shop = button.data('shop') || 'Retailer';
            let owner = button.data('owner') ? `(${button.data('owner')})` : '';
            let reward = button.data('reward') || 'Selected Reward';
            let brand = button.data('brand') || 'Brand';
            let points = button.data('points') || '0';

            // Check if dark mode is active
            let isDarkMode = document.body.classList.contains('dark-only') || document.documentElement.getAttribute('data-theme') === 'dark';
            let bgCard = isDarkMode ? 'var(--med-bg-card, #1e293b)' : '#ffffff';
            let textColor = isDarkMode ? 'var(--med-text-main, #f8fafc)' : '#1e293b';
            let subTextColor = isDarkMode ? 'var(--med-text-muted, #94a3b8)' : '#64748b';
            let boxBg = isDarkMode ? 'rgba(255, 255, 255, 0.05)' : '#f8fafc';
            let boxBorder = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : '#e2e8f0';

            Swal.fire({
                title: `<span style="color: ${textColor}; font-weight: 700; font-size: 1.25rem;"><i class="fa fa-gift text-success me-2"></i>Approve Reward Fulfillment?</span>`,
                html: `
                    <div class="text-start mt-3 p-3 rounded-3" style="background: ${boxBg}; border: 1px solid ${boxBorder}; font-size: 0.9rem;">
                        <div class="mb-4">
                            <span style="color: ${subTextColor}; font-size: 0.75rem; margin-bottom: 5px;" class="text-uppercase fw-bold d-block">Retailer</span>
                            <strong style="color: ${textColor}; font-size: 1rem;">${shop}</strong> <span style="color: ${subTextColor}; font-size: 0.85rem;">${owner}</span>
                        </div>
                        <div class="row g-2 pt-2 border-top" style="border-color: ${boxBorder} !important;">
                            <div class="col-6">
                                <span style="color: ${subTextColor}; font-size: 0.75rem;" class="text-uppercase fw-bold d-block">Brand</span>
                                <span class="text-white mt-1">${brand}</span>
                            </div>
                            <div class="col-6">
                                <span style="color: ${subTextColor}; font-size: 0.75rem;" class="text-uppercase fw-bold d-block">Points Cost</span>
                                <strong style="color: #0d6efd;">${points} Pts</strong>
                            </div>
                            <div class="col-12 pt-2">
                                <span style="color: ${subTextColor}; font-size: 0.75rem;" class="text-uppercase fw-bold d-block">Claimed Item</span>
                                <strong style="color: #10b981; font-size: 1rem;">${reward}</strong>
                            </div>
                        </div>
                    </div>
                    <p class="small mt-3 mb-0" style="color: ${subTextColor};">Approving this action will mark the reward as <strong>Approved (Waiting for Delivery)</strong>.</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa fa-check me-1"></i> Yes, Approve Reward',
                cancelButtonText: 'Cancel',
                background: bgCard,
                color: textColor,
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        $(document).ready(function () {
            // Export Action Override for Server-Side DataTables
            var exportAction = function (e, dt, button, config) {
                var self = this;
                var oldStart = dt.settings()[0]._iDisplayStart;
                dt.one('preXhr', function (e, s, data) {
                    data.start = 0;
                    data.length = -1;
                    dt.one('preDraw', function (e, settings) {
                        if (button[0].className.indexOf('buttons-copy') >= 0) {
                            $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
                        } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config);
                        } else if (button[0].className.indexOf('buttons-csv') >= 0) {
                            $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config);
                        } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
                            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config);
                        } else if (button[0].className.indexOf('buttons-print') >= 0) {
                            $.fn.dataTable.ext.buttons.print.action.call(self, e, dt, button, config);
                        }
                        dt.one('preXhr', function (e, s, data) {
                            settings._iDisplayStart = oldStart;
                            data.start = oldStart;
                        });
                        setTimeout(dt.ajax.reload, 0);
                        return false;
                    });
                });
                dt.ajax.reload();
            };

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
                    { data: 'total_points', name: 'loyalty_points', className: 'text-center', searchable: false },
                    { data: 'reward_summary', name: 'reward_summary', className: 'text-center', searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                buttons: [
                    { extend: 'copy', className: 'btn', action: exportAction },
                    { extend: 'csv', className: 'btn', action: exportAction },
                    { extend: 'excel', className: 'btn', action: exportAction },
                    { extend: 'pdf', className: 'btn', action: exportAction },
                    { extend: 'print', className: 'btn', action: exportAction }
                ],
                initComplete: function() {
                    let containerRight = $('#overview-table-controls .right-controls');
                    let exportContainer = $('#overview-export-container');
                    let $tableApi = $(this).DataTable();
                    let $wrapper = $($tableApi.table().container());
                    
                    // Move buttons to the outside top container
                    $wrapper.find('.dt-buttons').appendTo(exportContainer);
                    
                    // Move search to overview row (right)
                    $wrapper.find('.dataTables_filter').appendTo(containerRight);
                    $wrapper.find('.dataTables_filter input').addClass('form-control-sm rounded-pill px-3');
                },
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search retailers...",
                    processing: '<div class="d-flex justify-content-center align-items-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div><span class="text-muted fw-semibold">Loading data...</span></div>'
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
                return $(
                    `<div class="d-flex justify-content-between align-items-center">
                        <div class="fw-bold">${state.text}</div>
                        <div class="d-flex gap-2">
                            <div class="points-box-simple">${points} Pts</div>
                        </div>
                    </div>`
                );
            }

            function formatRetailerSelection(state) {
                if (!state.id) return state.text;
                let points = $(state.element).data('points') || '0.00';
                return $(
                    `<div class="d-flex justify-content-between align-items-center w-100">
                        <span class="fw-bold text-truncate" style="max-width: 60%">${state.text}</span>
                        <div class="d-flex gap-1">
                            <div class="points-box-simple">${points} Pts</div>
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
                        let containerRight = $('#detail-table-controls .right-controls');
                        let exportContainer = $('#detail-export-container');
                        
                        let $tableApi = $(this).DataTable();
                        let $wrapper = $($tableApi.table().container());
                        
                        // Move buttons to top outside container
                        $wrapper.find('.dt-buttons').appendTo(exportContainer);
                        
                        // Move Search to the right (proximal to the detail table)
                        $wrapper.find('.dataTables_filter').appendTo(containerRight);
                        $wrapper.find('.dataTables_filter input').addClass('form-control-sm rounded-pill px-3');
                    },
                    buttons: [
                        { extend: 'copy', className: 'btn', action: exportAction },
                        { extend: 'csv', className: 'btn', action: exportAction },
                        { extend: 'excel', className: 'btn', action: exportAction },
                        { extend: 'pdf', className: 'btn', action: exportAction },
                        { extend: 'print', className: 'btn', action: exportAction }
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Filter transactions..."
                    },
                    columns: [
                        { data: 'updated_at', name: 'updated_at' },
                        { data: 'order_code', name: 'order_code', render: d => `<strong class="text-primary">${d}</strong>` },
                        { data: 'product_summary', name: 'product_summary', orderable: false, className: 'small' },
                        { data: 'status', name: 'status', className: 'text-center' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[0, 'desc']],
                    pageLength: 10
                });
            }
        });
    </script>
@endpush