@extends('layouts.admin')

@section('title', 'Return Requests')

@section('page-body')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            @php
                $heading = 'Returns';
                if(auth()->user()->hasRole('retailer') || auth()->user()->hasRole('distributor')) {
                    $heading = 'My Return Requests';
                }
            @endphp
            <h4 class="fw-bold text-main-theme mb-1">{{ $heading }}</h4>
            <p class="text-muted-theme small mb-0">Track and manage product returns.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            @if(auth()->user()->hasRole('retailer') || auth()->user()->hasRole('distributor'))
            <button class="btn btn-primary shadow-sm rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#createReturnModal">
                <i class="fa fa-plus-circle me-2"></i>New Return Request
            </button>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-card-theme rounded-4 h-100 summary-card" style="transition: transform 0.2s;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-soft-warning p-3 rounded-3 me-3">
                            <i class="fa fa-clock-o text-warning fs-5"></i>
                        </div>
                        <div>
                            <span class="text-muted-theme small fw-bold d-block">Pending</span>
                            <h4 class="fw-bold mb-0 text-main-theme" id="count-pending">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-card-theme rounded-4 h-100 summary-card" style="transition: transform 0.2s;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-soft-info p-3 rounded-3 me-3">
                            <i class="fa fa-check-circle text-info fs-5"></i>
                        </div>
                        <div>
                            <span class="text-muted-theme small fw-bold d-block">Approved</span>
                            <h4 class="fw-bold mb-0 text-main-theme" id="count-approved">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-card-theme rounded-4 h-100 summary-card" style="transition: transform 0.2s;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-soft-success p-3 rounded-3 me-3">
                            <i class="fa fa-check-square-o text-success fs-5"></i>
                        </div>
                        <div>
                            <span class="text-muted-theme small fw-bold d-block">Completed</span>
                            <h4 class="fw-bold mb-0 text-main-theme" id="count-completed">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-card-theme rounded-4 h-100 summary-card" style="transition: transform 0.2s;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-soft-primary p-3 rounded-3 me-3">
                            <i class="fa fa-money text-primary fs-5"></i>
                        </div>
                        <div>
                            <span class="text-muted-theme small fw-bold d-block">Total Credits</span>
                            <h4 class="fw-bold mb-0 text-main-theme" id="total-refunds">₹0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs (For Admin, Super Admin, Sales Manager, and Distributors) -->
    @php
        $user = auth()->user();
        $showTabs = $user->hasAnyRole(['admin', 'superadmin', 'salesmanager', 'distributor']);
        
        $defaultType = 'retailer';
        $tabs = [
            ['type' => 'retailer', 'label' => 'Retailer Returns', 'icon' => 'fa-shopping-basket'],
            ['type' => 'distributor', 'label' => $user->hasRole('distributor') ? 'My Returns' : 'Distributor Returns', 'icon' => 'fa-truck'],
        ];

        if($user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $defaultType = 'distributor';
            $tabs = array_reverse($tabs); // Show Distributor first
        }
    @endphp
    @if($showTabs)
    <div class="card border-0 shadow-sm bg-card-theme mb-3" style="border-radius: 12px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-justified" id="returnTypeTabs">
                @foreach($tabs as $tab)
                <li class="nav-item">
                    <a class="nav-link {{ $defaultType === $tab['type'] ? 'active' : '' }} rounded-3 fw-bold small py-2" data-type="{{ $tab['type'] }}" href="javascript:void(0)">
                        <i class="fa {{ str_replace('fa-shopping-basket', 'fa-shopping-bag', $tab['icon']) }} me-1"></i> {{ $tab['label'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm bg-card-theme" style="border-radius: 16px !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="returnsTable">
                    <thead>
                        <tr>
                            <th class="ps-4">Return Code</th>
                            <th>Requester</th>
                            <th>Staff Assignment</th>
                            <th>Product Details</th>
                            <th>Credit</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables populated --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Return Modal -->
<div class="modal fade shadow-lg" id="viewReturnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 bg-card-theme" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="bg-soft-primary p-2 rounded-3 me-3">
                        <i class="fa fa-file-text-o text-primary fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-main-theme" id="viewReturnCodeHeader">RET-XXXXX</h5>
                        <p class="text-muted-theme small mb-0">Return Request Details</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5">
                        <label class="text-muted-theme small text-uppercase fw-bold mb-3 d-block">Evidence Photos</label>
                        <div id="returnGallery" class="row g-2 overflow-auto" style="max-height: 400px;">
                            <!-- Images will be injected here -->
                        </div>
                        <div id="noImagePlaceholder" class="text-center text-muted p-5 bg-light-theme rounded-4 w-100">
                            <i class="fa fa-image fa-3x mb-2"></i>
                            <p class="small mb-0">No image uploaded</p>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card border-0 bg-light-theme mb-3" style="border-radius: 16px;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-main-theme" id="viewProductName">Product Name</h6>
                                        <div id="viewProductVariant"></div>
                                    </div>
                                    <div class="text-end" id="viewStatusBadge">
                                        {{-- Status badge injected here --}}
                                    </div>
                                </div>
                                <div class="row g-3 py-3 border-top border-bottom border-light-theme">
                                    <div class="col-6">
                                        <label class="text-muted-theme small text-uppercase fw-bold mb-1 d-block">Quantity</label>
                                        <span id="viewQuantity" class="fw-bold text-main-theme"></span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <label class="text-muted-theme small text-uppercase fw-bold mb-1 d-block">Est. Credit</label>
                                        <h5 id="viewRefund" class="fw-bold text-success mb-0">₹0.00</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted-theme small text-uppercase fw-bold mb-2 d-block">Management Chain</label>
                            <div class="d-flex flex-wrap gap-2" id="viewTracking"></div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted-theme small text-uppercase fw-bold mb-2 d-block">Reason for Return</label>
                            <div class="bg-light-theme p-3 rounded-4">
                                <p id="viewReason" class="text-main-theme mb-0 small" style="line-height: 1.6;"></p>
                            </div>
                        </div>

                        <div id="rejectionSection" class="mb-4 d-none">
                            <label class="text-danger small text-uppercase fw-bold mb-2 d-block">Rejection Feedback</label>
                            <div class="bg-soft-danger p-3 rounded-4">
                                <p id="viewRejectionReason" class="text-danger mb-0 small"></p>
                            </div>
                        </div>

                        <div id="approvalTimeline" class="mb-0">
                            <label class="text-muted-theme small text-uppercase fw-bold mb-3 d-block">Approval History</label>
                            <div id="timelineList" class="ps-2">
                                {{-- Timeline items injected here --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 pt-0">
                <button type="button" class="btn btn-light w-100 py-2 rounded-3 fw-bold" data-bs-dismiss="modal">Close Details</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Return Modal (Search & Initiate) -->
<div class="modal fade" id="createReturnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">New Return Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                {{-- Step 1: Search Order --}}
                <div id="searchStep">
                    <div class="mb-4">
                        <label class="form-label mb-2 fw-bold small text-uppercase">Find Delivered Order</label>
                        <div class="input-group mb-3 shadow-sm premium-search-bar">
                            <span class="input-group-text bg-white">
                                <i class="fa fa-search text-muted"></i>
                            </span>
                            <input type="text" id="searchOrderCode" class="form-control ps-0" placeholder="Search by Order Code, Product or Brand..." style="height: 48px;">
                            <button class="btn btn-primary px-4" id="btnSearchOrder">
                                <i class="fa fa-search me-1"></i> Search
                            </button>
                        </div>

                        {{-- Advanced Filters --}}
                        <div class="bg-light p-3 rounded-4 mb-4 border shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 small text-uppercase text-muted"><i class="fa fa-filter me-1"></i> Advanced Filters</h6>
                                <button class="btn btn-link btn-sm text-decoration-none p-0" id="btnClearFilters">Clear All</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted mb-1">Brand</label>
                                    <select id="filterBrand" class="form-select form-select-sm rounded-3">
                                        <option value="">All Brands</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted mb-1">Product</label>
                                    <select id="filterProduct" class="form-select form-select-sm rounded-3">
                                        <option value="">All Products</option>
                                    </select>
                                </div>
                                @if(auth()->user()->hasRole('retailer'))
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted mb-1">Distributor</label>
                                    <select id="filterDistributor" class="form-select form-select-sm rounded-3">
                                        <option value="">All Distributors</option>
                                    </select>
                                </div>
                                @endif
                                <div class="col-md-3 {{ auth()->user()->hasRole('retailer') ? 'col-md-3' : 'col-md-6' }}">
                                    <label class="form-label small fw-bold text-muted mb-1">Date Range</label>
                                    <div class="input-group input-group-sm">
                                        <input type="date" id="filterStartDate" class="form-control rounded-start-3" placeholder="From">
                                        <input type="date" id="filterEndDate" class="form-control rounded-end-3" placeholder="To">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="orderHistoryContainer" class="mb-4">
                        <label class="text-muted small text-uppercase fw-bold mb-3 d-block">Delivered Order History</label>
                        <div id="orderHistoryList" class="row g-2">
                            {{-- Orders loaded via AJAX --}}
                            <div class="col-12 text-center py-4 text-muted">
                                <i class="fa fa-spinner fa-spin fa-2x mb-2"></i>
                                <p class="small">Loading your orders...</p>
                            </div>
                        </div>
                        <div id="orderHistoryPagination" class="mt-3 d-flex justify-content-center"></div>
                    </div>
                    
                    <div id="searchResults" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Order Items</h6>
                            <button class="btn btn-link btn-sm text-decoration-none p-0" id="btnBackToHistory">
                                <i class="fa fa-arrow-left me-1"></i> Back to History
                            </button>
                        </div>
                        <div class="mb-3 px-3 py-2 bg-light-theme rounded-3 border-start border-primary border-4" id="selectedOrderMeta">
                            {{-- Order details like Distributor, Date etc. injected here --}}
                        </div>
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0" id="searchItemsTable">
                                <thead class="bg-light">
                                    <tr style="font-size: 0.75rem;">
                                        <th class="ps-3">Product</th>
                                        <th class="text-center">Ordered</th>
                                        <th class="text-center">Already Returned</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Items injected here --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Return Form (Hidden by default) --}}
                <div id="returnFormStep" class="d-none">
                    <button class="btn btn-link p-0 text-decoration-none mb-3" id="btnBackToSearch">
                        <i class="fa fa-arrow-left me-1"></i> Back to items
                    </button>
                    <form id="directReturnForm">
                        <input type="hidden" name="order_id" id="dr_order_id">
                        <input type="hidden" name="order_type" id="dr_order_type">
                        <input type="hidden" name="product_id" id="dr_product_id">
                        <input type="hidden" name="side" id="dr_side">
                        <input type="hidden" name="size" id="dr_size">

                        <div class="p-3 bg-light rounded-4 mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Product</label>
                                    <h6 class="fw-bold mb-0" id="dr_product_name_display"></h6>
                                    <div class="small text-muted" id="dr_variant_display"></div>
                                </div>
                                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Available to Return</label>
                                    <span class="badge bg-primary rounded-pill px-3" id="dr_max_qty_display"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Return Quantity</label>
                                <input type="number" name="quantity" id="dr_quantity" class="form-control" step="0.01" required min="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Reason for Return</label>
                                <select name="reason_preset" id="dr_reason_preset" class="form-select">
                                    <option value="">Select a reason...</option>
                                    <option value="Broken/Damaged">Broken/Damaged</option>
                                    <option value="Expired">Expired</option>
                                    <option value="Wrong Product">Wrong Product</option>
                                    <option value="Quality Issue">Quality Issue</option>
                                    <option value="other">Other (Specify below)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 d-none" id="otherReasonContainer">
                            <label class="form-label fw-bold">Specific Reason</label>
                            <textarea name="reason" id="dr_reason_text" class="form-control" rows="2" placeholder="Describe the issue..."></textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Upload Images (Proof)</label>
                            <input type="file" name="images[]" id="dr_images" class="form-control" accept="image/*" required multiple>
                            <div id="dr_image_preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-3" id="btnSubmitDirectReturn">
                                Submit Return Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .modal-content {
        border-radius: 24px !important;
        overflow: hidden !important;
        border: none !important;
    }
    .recent-order-card:hover {
        border-color: var(--med-primary) !important;
        background-color: var(--soft-primary) !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
    }
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }

    .premium-search-bar {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        transition: all 0.3s ease;
    }
    .premium-search-bar:focus-within {
        border-color: var(--med-primary, #00497a) !important;
        box-shadow: 0 4px 12px rgba(0, 73, 122, 0.1) !important;
    }
    .premium-search-bar .input-group-text, 
    .premium-search-bar .form-control {
        border: none !important;
    }

    /* Premium Select2 Styling */
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid #dee2e6 !important;
        padding: 4px 8px !important;
        background-color: #fff !important;
        transition: all 0.2s ease;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: var(--med-primary, #00497a) !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 73, 122, 0.1) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        color: #495057 !important;
        font-size: 0.85rem !important;
    }
    .select2-dropdown {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        z-index: 2000 !important; /* Ensure it shows above modals if needed */
    }
    .select2-results__option {
        padding: 10px 15px !important;
        font-size: 0.85rem !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--med-primary, #00497a) !important;
    }

    .btn-soft-success {
        background-color: rgba(25, 135, 84, 0.2) !important;
        color: #198754 !important;
        border: none !important;
        font-weight: 700;
    }
    .btn-soft-success:hover {
        background-color: #198754 !important;
        color: #fff !important;
    }
    .btn-soft-danger {
        background-color: rgba(220, 53, 69, 0.2) !important;
        color: #dc3545 !important;
        border: none !important;
        font-weight: 700;
    }
    .btn-soft-danger:hover {
        background-color: #dc3545 !important;
        color: #fff !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let userRoles = {!! json_encode(auth()->user()->getRoleNames()) !!};
    
    let table = $('#returnsTable').DataTable({
        processing: true,
        ajax: {
            url: "{{ route('admin.returns.index') }}",
            data: function(d) {
                d.order_type = $('#returnTypeTabs .nav-link.active').data('type') || 'retailer';
            }
        },
        columns: [
            { 
                data: 'return_code',
                render: function(data, type, row) {
                    let date = row.created_at ? row.created_at.split('T')[0] : 'N/A';
                    return `<div>
                        <span class="fw-bold text-primary">${data}</span>
                        <div class="small text-muted" style="font-size: 0.65rem;">${date}</div>
                    </div>`;
                }
            },
            { 
                data: 'user',
                render: function(data, type, row) {
                    let typeBadge = row.order_type === 'retailer' ? 'bg-soft-success text-success' : 'bg-soft-info text-info';
                    let requester = data ? data.name : 'N/A';
                    if (row.order_type === 'retailer' && row.user && row.user.retailer) {
                        requester = row.user.retailer.shop_name || requester;
                    }
                    return `<div class="d-flex align-items-center">
                        <div class="bg-soft-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                            <i class="fa fa-user text-primary" style="font-size: 0.8rem;"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-main-theme small">${requester}</div>
                            <span class="badge ${typeBadge} border-0" style="font-size: 0.55rem; text-transform: uppercase; padding: 2px 4px;">${row.order_type}</span>
                        </div>
                    </div>`;
                }
            },
            {
                data: null,
                title: 'Staff Assignment',
                render: function(data, type, row) {
                    let html = '<div class="d-flex flex-column gap-1">';
                    if (row.field_staff) {
                        let name = row.field_staff.user ? row.field_staff.user.name : (row.field_staff.name || 'N/A');
                        html += `<div class="small text-muted-theme" style="font-size: 0.7rem;"><i class="fa fa-user me-1 text-success"></i>${name}</div>`;
                    }
                    if (row.sales_manager) {
                        let name = row.sales_manager.user ? row.sales_manager.user.name : (row.sales_manager.name || 'N/A');
                        html += `<div class="small text-muted-theme" style="font-size: 0.7rem;"><i class="fa fa-shield me-1 text-info"></i>${name}</div>`;
                    }
                    if (row.distributor) {
                        let name = row.distributor.user ? row.distributor.user.name : (row.distributor.name || 'N/A');
                        html += `<div class="small text-muted-theme" style="font-size: 0.7rem;"><i class="fa fa-truck me-1 text-primary"></i>${name}</div>`;
                    }
                    if (html === '<div class="d-flex flex-column gap-1">') html += '<span class="text-muted small">-</span>';
                    html += '</div>';
                    return html;
                }
            },
            { 
                data: 'product_name',
                render: function(data, type, row) {
                    let variant = [row.side, row.size].filter(v => v && !['Both', 'None', 'NA', 'Universal'].includes(v)).join(' / ');
                    return `<div>
                        <div class="fw-bold text-main-theme small" style="max-width: 150px; white-space: normal; line-height: 1.2;">${data}</div>
                        ${variant ? `<span class="badge bg-soft-info text-info border-0 px-2 mt-1" style="font-size: 0.6rem;">${variant}</span>` : ''}
                        <div class="mt-1">
                            <span class="text-muted small" style="font-size: 0.65rem;"><i class="fa fa-shopping-cart me-1"></i>Order: #${row.order_id}</span>
                        </div>
                    </div>`;
                }
            },
            { 
                data: 'refund_amount',
                render: function(data, type, row) {
                    let unit = row.unit;
                    if (row.product && (row.product.brand === 'Sudhneelgiri' || row.product.brand === 'Atomshield')) {
                        unit = 'Nos';
                    }
                    return `<div class="fw-bold text-success">₹${parseFloat(data || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
                    <div class="small text-muted" style="font-size: 0.65rem;">${row.quantity} ${unit}</div>`;
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    let badgeClass = 'bg-secondary';
                    let label = data.replace('_', ' ');
                    
                    if(data === 'pending') badgeClass = 'bg-warning';
                    else if(data === 'verified') badgeClass = 'bg-info';
                    else if(data === 'completed') badgeClass = 'bg-success';
                    else if(data === 'rejected') badgeClass = 'bg-danger';
                    
                    return `<span class="badge ${badgeClass} text-white text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">${label}</span>`;
                }
            },
            { 
                data: null,
                className: 'text-end pe-4',
                render: function(data, type, row) {
                    let rowData = JSON.stringify(row).replace(/'/g, "&apos;");
                    let btns = `<div class="d-flex justify-content-end gap-1">
                        <button class="btn btn-light btn-sm rounded-3 view-btn shadow-sm" data-row='${rowData}' title="View Details">
                            <i class="fa fa-eye text-primary"></i>
                        </button>`;
                    
                    let showApproval = false;
                    if(row.order_type === 'retailer') {
                        if((userRoles.includes('fieldstaff') || userRoles.includes('admin') || userRoles.includes('superadmin')) && row.status === 'pending') showApproval = true;
                        if((userRoles.includes('distributor') || userRoles.some(r => r.toLowerCase() === 'distributor') || userRoles.includes('admin') || userRoles.includes('superadmin')) && row.status === 'verified') showApproval = true;
                    } else {
                        if(userRoles.includes('salesmanager') && row.status === 'pending') showApproval = true;
                        if((userRoles.includes('admin') || userRoles.includes('superadmin')) && row.status === 'verified') showApproval = true;
                    }

                    if(showApproval) {
                        btns += `<button class="btn btn-soft-success btn-sm rounded-3 approve-row-btn shadow-sm" data-row='${rowData}' title="Approve">
                            <i class="fa fa-check"></i>
                        </button>
                        <button class="btn btn-soft-danger btn-sm rounded-3 reject-row-btn shadow-sm" data-row='${rowData}' title="Reject">
                            <i class="fa fa-times"></i>
                        </button>`;
                    }

                    btns += `</div>`;
                    return btns;
                }
            }
        ],
        order: [],
        dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-3"<"d-flex align-items-center"l><"d-flex align-items-center"B><"d-flex align-items-center"f>>t<"d-flex justify-content-between align-items-center p-3"ip>',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fa fa-file-excel me-1"></i> Excel',
                className: 'btn btn-success btn-sm rounded-3 shadow-sm border-0'
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf me-1"></i> PDF',
                className: 'btn btn-danger btn-sm rounded-3 shadow-sm border-0'
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print me-1"></i> Print',
                className: 'btn btn-dark btn-sm rounded-3 shadow-sm border-0'
            }
        ],
        language: {
            lengthMenu: "Show _MENU_ entries",
            search: "",
            searchPlaceholder: "Search returns...",
            emptyTable: `<div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No return requests found.</p>
            </div>`
        },
        drawCallback: function(settings) {
            // Context-aware summary cards - Use this.api() to avoid reference error during initialization
            let api = this.api();
            let visibleData = api.rows({ filter: 'applied' }).data().toArray();
            
            let pending = visibleData.filter(r => r.status === 'pending').length;
            let approved = visibleData.filter(r => r.status.startsWith('approved')).length;
            let completed = visibleData.filter(r => r.status === 'completed').length;
            let totalRefund = visibleData.filter(r => r.status === 'completed').reduce((sum, r) => sum + parseFloat(r.refund_amount || 0), 0);
            
            $('#count-pending').text(pending);
            $('#count-approved').text(approved);
            $('#count-completed').text(completed);
            $('#total-refunds').text('₹' + totalRefund.toLocaleString('en-IN', {minimumFractionDigits: 0}));
        }
    });

    $('#returnTypeTabs .nav-link').click(function() {
        $('#returnTypeTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        table.ajax.reload();
    });

    $(document).on('click', '.view-btn', function() {
        let row = $(this).data('row');
        $('#viewReturnCodeHeader').text(row.return_code);
        $('#viewProductName').text(row.product_name);
        
        let statusBadge = '';
        switch(row.status) {
            case 'pending': statusBadge = '<span class="badge bg-soft-warning text-warning rounded-pill px-3 py-2">PENDING TIER 1</span>'; break;
            case 'verified': statusBadge = '<span class="badge bg-soft-primary text-primary rounded-pill px-3 py-2">VERIFIED</span>'; break;
            case 'completed': statusBadge = '<span class="badge bg-soft-success text-success rounded-pill px-3 py-2">COMPLETED</span>'; break;
            case 'rejected': statusBadge = '<span class="badge bg-soft-danger text-danger rounded-pill px-3 py-2">REJECTED</span>'; break;
        }
        $('#viewStatusBadge').html(statusBadge);
        
        let variant = [row.side, row.size].filter(v => v && !['Both', 'None', 'NA', 'Universal'].includes(v)).join(' / ');
        $('#viewProductVariant').html(variant ? `<span class="badge bg-soft-info text-info border-0">${variant}</span>` : '');
        
        let unit = row.unit;
        if (row.product && (row.product.brand === 'Sudhneelgiri' || row.product.brand === 'Atomshield')) {
            unit = 'Nos';
        }
        $('#viewQuantity').text(`${row.quantity} ${unit}`);
        $('#viewRefund').text(`₹${row.refund_amount || '0.00'}`);
        $('#viewReason').text(row.reason);

        let tracking = $('#viewTracking').empty();
        if (row.distributor) {
            let distName = row.distributor.user ? row.distributor.user.name : (row.distributor.name || 'N/A');
            tracking.append(`<span class="badge bg-soft-primary text-primary border-0 px-2 py-1" style="font-size: 0.7rem;"><i class="fa fa-truck me-1"></i>Distro: ${distName}</span>`);
        }
        if (row.field_staff) {
            let name = row.field_staff.user ? row.field_staff.user.name : (row.field_staff.name || 'N/A');
            tracking.append(`<span class="badge bg-soft-success text-success border-0 px-2 py-1" style="font-size: 0.7rem;"><i class="fa fa-user me-1"></i>Staff: ${name}</span>`);
        }
        if (row.sales_manager) {
            let name = row.sales_manager.user ? row.sales_manager.user.name : (row.sales_manager.name || 'N/A');
            tracking.append(`<span class="badge bg-soft-info text-info border-0 px-2 py-1" style="font-size: 0.7rem;"><i class="fa fa-shield me-1"></i>Manager: ${name}</span>`);
        }

        let gallery = $('#returnGallery');
        gallery.empty();
        
        let images = row.image_paths || (row.image_path ? [row.image_path] : []);
        
        if (images.length > 0) {
            $('#noImagePlaceholder').addClass('d-none');
            gallery.removeClass('d-none');
            
            images.forEach(path => {
                gallery.append(`
                    <div class="col-6 col-md-4">
                        <div class="gallery-item rounded-3 overflow-hidden border shadow-sm cursor-pointer" onclick="window.open('/storage/${path}', '_blank')">
                            <img src="/storage/${path}" class="img-fluid w-100 h-100 object-fit-cover" style="aspect-ratio: 1/1;" alt="Evidence">
                            <div class="gallery-overlay">
                                <i class="fa fa-search-plus text-white"></i>
                            </div>
                        </div>
                    </div>
                `);
            });
        } else {
            $('#noImagePlaceholder').removeClass('d-none');
            gallery.addClass('d-none');
        }

        if(row.status === 'rejected') {
            $('#rejectionSection').removeClass('d-none');
            $('#viewRejectionReason').text(row.rejection_reason || 'No reason provided.');
            $('#approvalTimeline').addClass('d-none');
        } else {
            $('#rejectionSection').addClass('d-none');
            $('#approvalTimeline').removeClass('d-none');
            
            let timeline = $('#timelineList').empty();
            if (row.verified_at) {
                let verifier = row.verified_by_user ? row.verified_by_user.name : 'System';
                timeline.append(`
                    <div class="border-start border-success border-2 ps-3 pb-3 position-relative">
                        <div class="position-absolute start-0 top-0 translate-middle-x bg-success rounded-circle" style="width: 10px; height: 10px; margin-left: -1px;"></div>
                        <div class="fw-bold small text-main-theme">VERIFIED</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">By ${verifier} on ${row.verified_at.split('T')[0]}</div>
                    </div>
                `);
            }
            if (row.distributor_approved_at) {
                let approver = row.distributor_approved_by_user ? row.distributor_approved_by_user.name : 'System';
                timeline.append(`
                    <div class="border-start border-success border-2 ps-3 pb-3 position-relative">
                        <div class="position-absolute start-0 top-0 translate-middle-x bg-success rounded-circle" style="width: 10px; height: 10px; margin-left: -1px;"></div>
                        <div class="fw-bold small text-main-theme">DISTRIBUTOR APPROVED</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">By ${approver} on ${row.distributor_approved_at.split('T')[0]}</div>
                    </div>
                `);
            }
            if (row.admin_approved_at) {
                let approver = row.admin_approver ? row.admin_approver.name : 'Admin';
                timeline.append(`
                    <div class="border-start border-success border-2 ps-3 position-relative">
                        <div class="position-absolute start-0 top-0 translate-middle-x bg-success rounded-circle" style="width: 10px; height: 10px; margin-left: -1px;"></div>
                        <div class="fw-bold small text-main-theme">ADMIN FINALIZED</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">By ${approver} on ${row.admin_approved_at.split('T')[0]}</div>
                    </div>
                `);
            }
            if (timeline.is(':empty')) {
                timeline.append('<div class="text-muted small italic px-2">No approval history yet.</div>');
            }
        }

        $('#viewReturnModal').modal('show');
    });

    $(document).on('click', '.approve-row-btn', function() {
        let row = $(this).data('row');
        let approveUrl = "{{ route('admin.returns.approve', ':id') }}".replace(':id', row.id);
        
        let isFinal = (row.status === 'verified');
        let title = isFinal ? 'Complete Return?' : 'Approve Return?';
        let confirmText = isFinal ? 'Yes, Complete Return' : 'Yes, Approve';
        
        let unit = row.unit;
        if (row.product && (row.product.brand === 'Sudhneelgiri' || row.product.brand === 'Atomshield')) {
            unit = 'Nos';
        }

        let html = `
            <div class="text-start mt-3">
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border mb-3">
                    <img src="${row.image_path ? '/storage/'+row.image_path : '/assets/images/placeholder.png'}" 
                         class="rounded-3 shadow-sm border cursor-pointer return-evidence-preview" 
                         style="width: 70px; height: 70px; object-fit: cover;"
                         onclick="Swal.fire({imageUrl: this.src, showConfirmButton: false, width: 'auto', background: 'transparent', padding: 0})">
                    <div>
                        <div class="fw-bold text-main-theme">${row.product_name}</div>
                        <div class="small text-muted">${row.quantity} ${unit} • ₹${parseFloat(row.refund_amount).toLocaleString()}</div>
                        <div class="d-flex gap-1 mt-1">
                            <div class="badge bg-soft-info text-info small">RET: ${row.return_code}</div>
                            <div class="badge bg-soft-secondary text-secondary small">ORD: #${row.order_id}</div>
                        </div>
                    </div>
                </div>
                <p class="small text-muted px-2"><i class="fa fa-info-circle me-1"></i> ${isFinal ? 'This will finalize the return and allow the credit note to be issued.' : 'This will move the request to the next stage for final approval.'}</p>
            </div>
        `;
        
        Swal.fire({
            title: title,
            html: html,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            confirmButtonText: confirmText,
            width: '450px'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(approveUrl, { _token: '{{ csrf_token() }}' }, function(res) {
                    if(res.success) {
                        table.ajax.reload();
                        showToast('success', res.success);
                    } else {
                        showToast('error', res.error);
                    }
                });
            }
        });
    });

    $(document).on('click', '.reject-row-btn', function() {
        let row = $(this).data('row');
        let unit = row.unit;
        if (row.product && (row.product.brand === 'Sudhneelgiri' || row.product.brand === 'Atomshield')) {
            unit = 'Nos';
        }

        let html = `
            <div class="text-start mt-3">
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border mb-3">
                    <img src="${row.image_path ? '/storage/'+row.image_path : '/assets/images/placeholder.png'}" 
                         class="rounded-3 shadow-sm border cursor-pointer return-evidence-preview" 
                         style="width: 70px; height: 70px; object-fit: cover;"
                         onclick="Swal.fire({imageUrl: this.src, showConfirmButton: false, width: 'auto', background: 'transparent', padding: 0})">
                    <div>
                        <div class="fw-bold text-main-theme">${row.product_name}</div>
                        <div class="small text-muted">${row.quantity} ${unit} • ${row.return_code}</div>
                    </div>
                </div>
                <div class="px-2">
                    <label class="small fw-bold text-muted mb-2">REASON FOR REJECTION</label>
                    <textarea id="swal-rejection-reason" class="form-control rounded-3" rows="3" placeholder="Please explain why this request is being rejected..."></textarea>
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Reject Return?',
            html: html,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Reject Request',
            width: '450px',
            preConfirm: () => {
                const reason = Swal.getPopup().querySelector('#swal-rejection-reason').value;
                if (!reason || reason.length < 5) {
                    Swal.showValidationMessage(`Please enter a valid reason (min 5 characters)`);
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                let rejectUrl = "{{ route('admin.returns.reject', ':id') }}".replace(':id', row.id);
                $.post(rejectUrl, { 
                    _token: '{{ csrf_token() }}',
                    reason: result.value
                }, function(res) {
                    if(res.success) {
                        table.ajax.reload();
                        showToast('success', res.success);
                    } else {
                        showToast('error', res.error);
                    }
                });
            }
        });
    });

    // --- Create Return Flow ---
    let currentSearchOrder = null;
    let currentSearchType = null;
    let historyPage = 1;

    window.loadOrderHistory = function(page = 1, search = '') {
        const list = $('#orderHistoryList');
        if (page === 1) {
            list.html('<div class="col-12 text-center py-5 text-muted"><i class="fa fa-spinner fa-spin fa-3x mb-3 text-primary opacity-25"></i><p class="small fw-bold">Searching your orders...</p></div>');
        }
        
        let filters = {
            page: page,
            search: search,
            brand: $('#filterBrand').val(),
            product_id: $('#filterProduct').val(),
            distributor_id: $('#filterDistributor').val(),
            start_date: $('#filterStartDate').val(),
            end_date: $('#filterEndDate').val()
        };
        
        $.get("{{ route('admin.returns.delivered-orders') }}", filters, function(res) {
            if (page === 1) list.empty();
            
            if (res.data.length === 0) {
                list.html('<div class="col-12 text-center py-5 text-muted"><i class="fa fa-search fa-3x mb-3 opacity-25"></i><p class="small fw-bold">No delivered orders found matching your criteria.</p></div>');
                $('#orderHistoryPagination').empty();
                return;
            }
            
            res.data.forEach(o => {
                list.append(`
                    <div class="col-sm-6">
                        <div class="card border border-2 shadow-none rounded-4 recent-order-card h-100" 
                             data-code="${o.order_code}" 
                             style="transition: all 0.3s ease; cursor: pointer; border-style: dashed !important;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="bg-soft-primary px-2 py-1 rounded-2 fw-bold text-primary" style="font-size: 0.75rem;">${o.order_code}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">${o.date}</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div>
                                        <div class="fw-bold text-success mb-1">₹${parseFloat(o.total_amount).toLocaleString('en-IN')}</div>
                                        <div class="text-muted small" style="font-size: 0.65rem;">
                                            <i class="fa fa-shopping-bag me-1"></i>${o.item_count} Items • <i class="fa fa-truck me-1"></i>${o.distributor}
                                        </div>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 d-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px;">
                                        <i class="fa fa-arrow-right text-primary" style="font-size: 0.7rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });

            // Simple "Load More" style pagination
            if (res.meta.current_page < res.meta.last_page) {
                $('#orderHistoryPagination').html(`<button class="btn btn-primary rounded-pill px-5 shadow-sm" onclick="loadOrderHistory(${res.meta.current_page + 1}, '${search}')">Load More Results</button>`);
            } else {
                $('#orderHistoryPagination').empty();
            }
        });
    }

    // Fetch filters dynamically
    window.loadReturnFilters = function(brand = '') {
        $.get("{{ route('admin.returns.get-filters') }}", { brand: brand }, function(res) {
            let brandSelect = $('#filterBrand');
            let productSelect = $('#filterProduct');
            let distSelect = $('#filterDistributor');
            
            if (!brand) {
                brandSelect.find('option:not(:first)').remove();
                res.brands.forEach(b => brandSelect.append(`<option value="${b}">${b}</option>`));
            }
            
            productSelect.find('option:not(:first)').remove();
            res.products.forEach(p => productSelect.append(`<option value="${p.id}">${p.product_name}</option>`));
            
            if (!brand && distSelect.length) {
                distSelect.find('option:not(:first)').remove();
                res.distributors.forEach(d => distSelect.append(`<option value="${d.id}">${d.name}</option>`));
            }
            
            // Re-initialize Select2 to reflect new options
            if (!brand) brandSelect.trigger('change.select2');
            productSelect.trigger('change.select2');
            if (!brand && distSelect.length) distSelect.trigger('change.select2');
        });
    };

    $('#createReturnModal').on('shown.bs.modal', function() {
        // Initialize Select2 when modal is shown
        $('#filterBrand, #filterProduct, #filterDistributor').select2({
            dropdownParent: $('#createReturnModal'),
            width: '100%'
        });

        // Reset state
        $('#searchOrderCode').val('');
        $('#searchResults').addClass('d-none');
        $('#orderHistoryContainer').removeClass('d-none');
        $('#returnFormStep').addClass('d-none');
        $('#searchStep').removeClass('d-none');
        $('#directReturnForm')[0].reset();
        $('#dr_image_preview').empty();
        
        // Reset filters
        $('#filterBrand, #filterProduct, #filterDistributor, #filterStartDate, #filterEndDate').val('').trigger('change.select2');
        
        loadReturnFilters();
        loadOrderHistory(1);
    });

    // Handle filter changes
    $('#filterBrand').on('change', function() {
        let brand = $(this).val();
        loadReturnFilters(brand);
        loadOrderHistory(1, $('#searchOrderCode').val());
    });

    $('#filterProduct, #filterDistributor, #filterStartDate, #filterEndDate').on('change', function() {
        loadOrderHistory(1, $('#searchOrderCode').val());
    });

    $('#btnClearFilters').click(function() {
        $('#filterBrand, #filterProduct, #filterDistributor, #filterStartDate, #filterEndDate').val('');
        loadOrderHistory(1, $('#searchOrderCode').val());
    });

    $(document).on('click', '.recent-order-card', function() {
        let code = $(this).data('code');
        // Clear search bar as requested
        $('#searchOrderCode').val('');
        
        let $btn = $('#btnSearchOrder');
        let oldText = $btn.text();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.get("{{ route('admin.returns.search-order') }}", { code: code }, function(res) {
            $btn.prop('disabled', false).text(oldText);
            if(res.success) {
                currentSearchOrder = res.order;
                currentSearchType = res.type;
                
                // Show order details before product listing
                $('#selectedOrderMeta').html(`
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <span class="badge bg-primary rounded-pill px-3">${res.order.order_code}</span>
                        </div>
                        <div class="col">
                            <div class="d-flex flex-wrap gap-3 text-main-theme" style="font-size: 0.75rem;">
                                <span><i class="fa fa-truck me-1 text-muted"></i><strong>Distro:</strong> ${res.order.distributor_name}</span>
                                <span><i class="fa fa-calendar me-1 text-muted"></i><strong>Date:</strong> ${res.order.delivered_at}</span>
                            </div>
                        </div>
                    </div>
                `);

                renderSearchItems(res.order.items);
                $('#searchResults').removeClass('d-none');
                $('#orderHistoryContainer').addClass('d-none');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text(oldText);
            showToast('error', 'An error occurred');
        });
        
        // Visual feedback
        $('.recent-order-card').removeClass('border-primary shadow-sm');
        $(this).addClass('border-primary shadow-sm');
    });

    $('#btnSearchOrder').click(function() {
        let code = $('#searchOrderCode').val();
        // If searching without selection, reload history
        loadOrderHistory(1, code);

        if(!code) return;

        let $btn = $(this);
        let oldText = $btn.text();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.get("{{ route('admin.returns.search-order') }}", { code: code }, function(res) {
            $btn.prop('disabled', false).text(oldText);
            if(res.success) {
                currentSearchOrder = res.order;
                currentSearchType = res.type;

                // Show order details before product listing
                $('#selectedOrderMeta').html(`
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <span class="badge bg-primary rounded-pill px-3">${res.order.order_code}</span>
                        </div>
                        <div class="col">
                            <div class="d-flex flex-wrap gap-3 text-main-theme" style="font-size: 0.75rem;">
                                <span><i class="fa fa-truck me-1 text-muted"></i><strong>Distro:</strong> ${res.order.distributor_name}</span>
                                <span><i class="fa fa-calendar me-1 text-muted"></i><strong>Date:</strong> ${res.order.delivered_at}</span>
                            </div>
                        </div>
                    </div>
                `);

                renderSearchItems(res.order.items);
                $('#searchResults').removeClass('d-none');
                $('#orderHistoryContainer').addClass('d-none'); 
            } else {
                $('#searchResults').addClass('d-none');
                $('#orderHistoryContainer').removeClass('d-none');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text(oldText);
            showToast('error', 'An error occurred while searching');
        });
    });

    $('#btnBackToSearch').click(function() {
        $('#returnFormStep').addClass('d-none');
        $('#searchStep').removeClass('d-none');
        $('#orderHistoryContainer').removeClass('d-none'); // Show history again
    });

    $('#btnBackToHistory').click(function() {
        $('#searchResults').addClass('d-none');
        $('#orderHistoryContainer').removeClass('d-none');
        $('#searchOrderCode').val('');
        loadOrderHistory(1);
    });

    function renderSearchItems(items) {
        let tbody = $('#searchItemsTable tbody');
        tbody.empty();
        
        items.forEach(item => {
            let remaining = item.quantity - item.returned_qty - item.pending_return_qty;
            let statusHtml = '';
            
            if (item.returned_qty > 0) {
                statusHtml += `<div class="badge bg-soft-success text-success small mb-1">${item.returned_qty} Returned</div><br>`;
            }
            if (item.pending_return_qty > 0) {
                statusHtml += `<div class="badge bg-soft-warning text-warning small">${item.pending_return_qty} Pending</div>`;
            }
            if (statusHtml === '') statusHtml = '<span class="text-muted small">No returns</span>';

            let variant = [item.side, item.size].filter(Boolean).join(' / ');

            tbody.append(`
                <tr>
                    <td class="ps-3">
                        <div class="fw-bold">${item.product_name}</div>
                        ${variant ? `<div class="small text-muted">${variant}</div>` : ''}
                    </td>
                    <td class="text-center">${item.quantity} ${item.unit}</td>
                    <td class="text-center">${item.returned_qty} ${item.unit}</td>
                    <td class="text-center">${statusHtml}</td>
                    <td class="text-end pe-3">
                        ${(item.is_returnable && remaining > 0) ? `
                            <button class="btn btn-sm btn-outline-primary btn-init-dr" 
                                data-id="${item.product_id}" 
                                data-name="${item.product_name}"
                                data-side="${item.side || ''}"
                                data-size="${item.size || ''}"
                                data-unit="${item.unit}"
                                data-max="${remaining}">
                                Return
                            </button>
                        ` : `
                            <span class="text-muted small">${item.is_returnable ? 'Fully Returned' : 'Not Returnable'}</span>
                        `}
                    </td>
                </tr>
            `);
        });
    }

    $(document).on('click', '.btn-init-dr', function() {
        let data = $(this).data();
        
        $('#dr_order_id').val(currentSearchOrder.id);
        $('#dr_order_type').val(currentSearchType);
        $('#dr_product_id').val(data.id);
        $('#dr_side').val(data.side);
        $('#dr_size').val(data.size);
        
        $('#dr_product_name_display').text(data.name);
        $('#dr_variant_display').text([data.side, data.size].filter(Boolean).join(' / '));
        $('#dr_max_qty_display').text(`${data.max} ${data.unit} Available`);
        $('#dr_quantity').attr('max', data.max).val(data.max);
        
        $('#searchStep').addClass('d-none');
        $('#returnFormStep').removeClass('d-none');
    });

    $('#btnBackToSearch').click(function() {
        $('#returnFormStep').addClass('d-none');
        $('#searchStep').removeClass('d-none');
    });

    $('#dr_reason_preset').change(function() {
        if($(this).val() === 'other') {
            $('#otherReasonContainer').removeClass('d-none');
            $('#dr_reason_text').prop('required', true);
        } else {
            $('#otherReasonContainer').addClass('d-none');
            $('#dr_reason_text').prop('required', false);
        }
    });

    $('#dr_images').change(function() {
        let container = $('#dr_image_preview');
        container.empty();
        Array.from(this.files).forEach(file => {
            let reader = new FileReader();
            reader.onload = e => {
                container.append(`<img src="${e.target.result}" class="rounded-3 border" style="width: 60px; height: 60px; object-fit: cover;">`);
            };
            reader.readAsDataURL(file);
        });
    });

    $('#directReturnForm').submit(function(e) {
        e.preventDefault();
        
        let preset = $('#dr_reason_preset').val();
        let text = $('#dr_reason_text').val();
        let finalReason = preset === 'other' ? text : (preset + (text ? ': ' + text : ''));
        
        if(!finalReason || finalReason.length < 5) {
            return showToast('error', 'Please provide a reason (min 5 characters)');
        }

        let formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('reason', finalReason);

        let $btn = $('#btnSubmitDirectReturn');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');

        $.ajax({
            url: "{{ route('admin.returns.store') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if(res.success) {
                    $('#createReturnModal').modal('hide');
                    table.ajax.reload();
                    showToast('success', 'Return request submitted successfully');
                    
                    $('#searchOrderCode').val('');
                    $('#searchResults').addClass('d-none');
                    $('#returnFormStep').addClass('d-none');
                    $('#searchStep').removeClass('d-none');
                    $('#directReturnForm')[0].reset();
                    $('#dr_image_preview').empty();
                } else {
                    showToast('error', res.message);
                }
                $btn.prop('disabled', false).text('Submit Return Request');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Submit Return Request');
                let err = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'An error occurred';
                showToast('error', err);
            }
        });
    });

    // Deep Linking
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight');
    if (highlightId) {
        table.on('draw', function() {
            let rowData = table.rows().data().toArray().find(r => r.return_code === highlightId || r.id == highlightId);
            if (rowData) {
                let $btn = $(`.view-btn[data-id="${rowData.id}"]`).first();
                if ($btn.length) {
                    $btn.click();
                    let $tr = $btn.closest('tr');
                    $tr.addClass('highlight-pulse');
                    $('html, body').animate({ scrollTop: $tr.offset().top - 150 }, 500);
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }
            }
        });
    }
});
</script>

<style>
.bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
.bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
.bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
.text-main-theme { color: #2c3e50; }
.bg-card-theme { background-color: #fff; }
.text-muted-theme { color: #6c757d; }
.object-fit-cover { object-fit: cover; }

:root {
    --login-text: #1e293b;
    --login-muted: #64748b;
    --med-border: #e2e8f0;
    --bg-light: #f8fafc;
}

body.dark-only {
    --login-text: #f1f5f9;
    --login-muted: #94a3b8;
    --med-border: #334155;
    --bg-light: #0f172a;
    --bg-card: #1e293b;
    --text-main: #f8fafc;
}

#selectedOrderMeta .text-muted { color: var(--login-muted) !important; }
body.dark-only #selectedOrderMeta .text-muted { color: #94a3b8 !important; }
#selectedOrderMeta strong { color: var(--login-text); }
body.dark-only #selectedOrderMeta strong { color: #f8fafc; }

.bg-card-theme { background-color: #ffffff; }
.bg-light-theme { background-color: #f8f9fa; }
.text-main-theme { color: #2d3748; }
.text-muted-theme { color: #718096; }
.border-light-theme { border-color: #edf2f7; }

body.dark-only .bg-card-theme { background-color: #1e293b !important; }
body.dark-only .bg-light-theme { background-color: #0f172a !important; }
body.dark-only .text-main-theme { color: #f8fafc !important; }
body.dark-only .text-muted-theme { color: #94a3b8 !important; }
body.dark-only .border-light-theme { border-color: #334155 !important; }

body.dark-only #returnsTable thead th {
    background-color: #0f172a;
    color: #cbd5e1;
    border-bottom-color: #334155;
}

.badge.bg-soft-primary { background-color: rgba(115, 102, 255, 0.1); color: #7366ff; }
.badge.bg-soft-success { background-color: rgba(81, 187, 37, 0.1); color: #51bb25; }
.badge.bg-soft-info { background-color: rgba(0, 157, 181, 0.1); color: #009db5; }
.badge.bg-soft-warning { background-color: rgba(248, 214, 43, 0.1); color: #f8d62b; }
.badge.bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }

@keyframes pulse-highlight {
    0% { background-color: rgba(115, 102, 255, 0); }
    50% { background-color: rgba(115, 102, 255, 0.15); }
    100% { background-color: rgba(115, 102, 255, 0); }
}
.highlight-pulse {
    animation: pulse-highlight 2s ease-in-out infinite;
    position: relative;
    z-index: 1;
}

#returnsTable thead th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding: 16px 12px;
    border-bottom: 2px solid #edf2f9;
}

.dt-buttons { display: flex; gap: 5px; }
.dt-buttons .btn {
    font-weight: 600;
    font-size: 0.75rem;
    padding: 6px 12px;
}

.dataTables_length label { 
    display: flex !important; 
    align-items: center !important; 
    gap: 8px; 
}

#returnTypeTabs .nav-link.active { 
    background-color: var(--med-primary, #0d6efd) !important; 
    color: #fff !important; 
}

.cursor-pointer { cursor: pointer; }
.recent-order-card:hover { border-color: #7366ff !important; background: rgba(115, 102, 255, 0.05); }

.modal-content {
    border-radius: 20px !important;
    overflow: hidden !important;
}
</style>
@endpush
