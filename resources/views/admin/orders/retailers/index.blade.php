@extends('layouts.admin')

@section('page-body')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .dataTables_filter {
            text-align: right !important;
        }

        .dataTables_filter input {
            width: 100% !important;
            max-width: 210px !important;
            margin-left: 10px !important;
        }

        .bg-custom-yellow {
            background-color: #f59e0b !important;
            color: #ffffff !important;
        }

        /* Segmented Control for Payment Filter */
        .segmented-control {
            display: flex !important;
            flex-direction: row !important;
            background-color: var(--med-border, #e2e8f0);
            border-radius: 50px;
            padding: 4px;
            position: relative;
            width: 100%;
            max-width: 260px;
            min-width: 220px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .segmented-control input {
            display: none;
        }
        .segmented-control label {
            flex: 1 !important;
            width: 33.33% !important;
            text-align: center;
            padding: 8px 0;
            margin: 0;
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--med-text-muted, #64748b);
            cursor: pointer;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .segmented-control input:checked + label {
            color: var(--med-text-main, #0f172a);
        }
        #pay_paid:checked + label {
            color: var(--med-paid-text, #15803d);
        }
        #pay_pending:checked + label {
            color: var(--med-pending-text, #b45309);
        }
        .selection-indicator {
            position: absolute;
            top: 4px;
            bottom: 4px;
            left: 4px;
            width: calc(33.333% - 2.66px);
            background: var(--med-bg-card, #ffffff);
            border-radius: 50px;
            z-index: 1;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        #pay_all:checked ~ .selection-indicator {
            transform: translateX(0);
            background: var(--med-bg-card, #ffffff);
        }
        #pay_paid:checked ~ .selection-indicator {
            transform: translateX(100%);
            background: var(--med-paid-bg, #dcfce7);
        }
        #pay_pending:checked ~ .selection-indicator {
            transform: translateX(200%);
            background: var(--med-pending-bg, #fef9c3);
        }

        .dataTables_length {
            text-align: left !important;
        }

        .dataTables_length select {
            padding: 5px 10px !important;
            padding-right: 30px !important;
            display: inline-block !important;
            width: auto !important;
        }

        .action-buttons {
            display: inline-flex !important;
            gap: 4px;
            align-items: center;
        }


        .action-buttons .btn {
            margin: 0 !important;
        }

        /* Modal sizing and table compacting */
        .modal-xl {
            max-width: 1140px;
        }

        #orders-table td:last-child {
            white-space: nowrap !important;
        }

        .product-col {
            min-width: 200px !important;
            max-width: 200px !important;
            width: 200px !important;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
            vertical-align: top !important;
            line-height: 1.3 !important;
        }

        /* Preview / full content helper */
        .preview-content {
            display: inline-block;
        }

        .full-content {
            display: block;
        }

        .full-content.d-none {
            display: none;
        }

        /* Spacing Fix for DataTable Footer */
        .dataTables_info {
            padding-left: 20px !important;
            padding-bottom: 20px !important;
            padding-top: 20px !important;
            color: var(--med-text-muted, #64748b) !important;
            font-size: 0.85rem !important;
        }
        .dataTables_paginate {
            padding-right: 20px !important;
            padding-bottom: 20px !important;
            padding-top: 20px !important;
        }

        /* Responsive Fixes */
        #orderStatusTabs {
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
            padding-bottom: 2px;
        }
        #orderStatusTabs::-webkit-scrollbar {
            display: none; /* Chrome/Safari */
        }
        #orderStatusTabs .nav-item {
            flex-shrink: 0;
        }

        @media (max-width: 991px) {
            .dataTables_filter label {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 0;
            }
            .dataTables_filter input {
                width: 100% !important;
                max-width: 200px;
            }
        }

        .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0;
            white-space: nowrap;
        }

        .dataTables_filter input {
            border-radius: 50px !important;
            padding: 5px 15px !important;
            border: 1px solid var(--med-border) !important;
            margin-bottom: 0 !important;
        }

        @media (max-width: 767px) {
            .dataTables_filter, .dataTables_length {
                text-align: center !important;
                margin-bottom: 10px;
            }
            .dataTables_filter input {
                max-width: 100% !important;
                margin-left: 0 !important;
            }
            .segmented-control {
                width: 100% !important;
                max-width: 280px;
                margin: 10px auto !important;
            }
            .payment-filter-container {
                justify-content: center !important;
                margin: 15px 0 !important;
            }
        }

        /* Minimal Filter Bar Styles */
        .filter-bar {
            background: var(--med-bg-card);
            border: 1px solid var(--med-border);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .filter-bar:hover {
            box-shadow: var(--med-shadow-soft) !important;
        }

        .select2-minimal + .select2-container .select2-selection--single {
            border: 1px solid var(--med-border);
            border-radius: 10px;
            height: 38px;
            padding-left: 5px;
            background-color: var(--med-bg-body);
            transition: all 0.2s;
        }

        .select2-minimal + .select2-container .select2-selection--single:hover {
            border-color: var(--med-primary);
            background-color: var(--med-bg-card);
        }

        .select2-minimal + .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--med-text-main);
        }

        .select2-minimal + .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        #btn-clear-all {
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--med-border);
            background: var(--med-bg-card);
            color: var(--med-text-muted);
            transition: all 0.2s;
            font-size: 0.8rem;
            border-radius: 10px;
        }

        #btn-clear-all:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: #fca5a5;
        }

        /* --- New Order View UI Styles --- */
        /* --- New Horizontal Order View UI Styles --- */
        .order-timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 30px;
            margin-bottom: 20px;
            padding-left: 0;
        }
        .order-timeline::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 5%;
            right: 5%;
            height: 2px;
            background: #e2e8f0;
            z-index: 0;
        }
        .timeline-item {
            position: relative;
            z-index: 1;
            flex: 1;
            text-align: center;
            margin-bottom: 0;
        }
        .timeline-marker {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #cbd5e1;
            border: 4px solid var(--med-bg-card);
            box-shadow: 0 0 0 2px #e2e8f0;
            margin: 0 auto 10px;
            transition: all 0.3s ease;
        }
        .timeline-item.active .timeline-marker {
            background: var(--med-primary, #00497a);
            box-shadow: 0 0 0 2px rgba(0, 73, 122, 0.2);
            transform: scale(1.2);
        }
        .timeline-content {
            padding-left: 0;
        }
        .timeline-content h6 {
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: var(--med-text-main);
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .timeline-content span {
            font-size: 0.65rem;
            color: var(--med-text-muted);
            display: block;
            line-height: 1.2;
        }
        .variant-highlight {
            font-weight: 800;
            color: #0ea5e9;
            text-transform: uppercase;
            font-size: 1rem;
            margin-left: 10px;
            padding: 3px 10px;
            background: rgba(14, 165, 233, 0.12);
            border-radius: 6px;
            vertical-align: middle;
            letter-spacing: 0.04em;
        }
        /* --- End New Order View UI Styles --- */
        /* --- End New Order View UI Styles --- */
        #submitReturnModal {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
        .bg-soft-success { background-color: rgba(22, 163, 74, 0.1) !important; color: #16a34a !important; }
        .bg-soft-warning { background-color: rgba(202, 138, 4, 0.1) !important; color: #ca8a04 !important; }
        .bg-soft-danger { background-color: rgba(220, 38, 38, 0.1) !important; color: #dc2626 !important; }
        .bg-soft-info { background-color: rgba(8, 145, 178, 0.1) !important; color: #0891b2 !important; }
        .bg-soft-primary { background-color: rgba(0, 73, 122, 0.1) !important; color: #00497a !important; }
        .text-main-theme { color: var(--med-text-main, #0f172a) !important; }
        .text-muted-theme { color: var(--med-text-muted, #64748b) !important; }
        .bg-card-theme { background-color: var(--med-bg-card, #ffffff) !important; }
</style>
    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header border-bottom pb-0" style="background-color: var(--med-bg-card) !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-primary fw-bold">Retailer Orders</h5>
                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'retailer']) || Auth::user()->hasPermissionToCategory('retailer_orders', 'add'))
                    <a href="{{ route('admin.retailer.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>
                        Create Order</a>
                    @endif
                </div>
                
                <ul class="nav nav-tabs border-bottom-0" id="orderStatusTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-4 fw-bold text-primary border-bottom-0" id="tab-all" data-bs-toggle="tab" data-status="" type="button" role="tab">All</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-pending" data-bs-toggle="tab" data-status="pending" type="button" role="tab">Pending</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-processing" data-bs-toggle="tab" data-status="processing" type="button" role="tab">Processing</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-approved" data-bs-toggle="tab" data-status="approved" type="button" role="tab">Approved</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-delivered" data-bs-toggle="tab" data-status="delivered" type="button" role="tab">Delivered</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-cancelled" data-bs-toggle="tab" data-status="cancelled" type="button" role="tab">Cancelled</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-rejected" data-bs-toggle="tab" data-status="rejected" type="button" role="tab">Rejected</button>
                    </li>
                </ul>
            </div>
            <div class="card-body pt-4">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div> @endif

                <!-- Date Filter Section -->
                <div class="p-3 mb-4 rounded-3 border bg-card-theme" style="border-color: var(--med-border) !important;">
                    <div class="row align-items-end g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-1">
                                <i class="fa fa-calendar text-primary me-1"></i> Placed From
                            </label>
                            <input type="text" id="start_date_filter" class="form-control bg-transparent flatpickr-input" placeholder="Select Start Date" readonly style="border-radius: 8px; border: 1.5px solid var(--med-border); font-size: 0.9rem; font-weight: 600; color: var(--med-text-main);">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-1">
                                <i class="fa fa-calendar text-primary me-1"></i> Placed To
                            </label>
                            <input type="text" id="end_date_filter" class="form-control bg-transparent flatpickr-input" placeholder="Select End Date" readonly style="border-radius: 8px; border: 1.5px solid var(--med-border); font-size: 0.9rem; font-weight: 600; color: var(--med-text-main);">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label d-none d-md-block mb-1">&nbsp;</label>
                            <button type="button" id="clear_dates_btn" class="btn btn-clear-dates w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                                <i class="fa fa-refresh"></i> Clear Dates
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden filter element to be moved into datatable wrapper -->
                <div class="d-none" id="payment-filter-wrapper">
                    <div class="d-flex flex-column flex-sm-row align-items-center mb-0 ms-sm-2">
                        <span class="text-muted fw-bold me-sm-3 mb-2 mb-sm-0 small text-uppercase">Payment:</span>
                        <div class="segmented-control" id="payment_status_filter_group">
                            <input type="radio" name="payment_status" id="pay_all" value="" checked>
                            <label for="pay_all">All</label>
                            
                            <input type="radio" name="payment_status" id="pay_paid" value="paid">
                            <label for="pay_paid">Paid</label>
                            
                            <input type="radio" name="payment_status" id="pay_pending" value="pending">
                            <label for="pay_pending">Pending</label>
                            
                            <div class="selection-indicator"></div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="orders-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Order Code</th>
                                <th>Retailer</th>
                                <th>Distributor</th>
                                <th style="width: 200px;">Products</th>
                                <th style="width: 120px;">Brand</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Placed At</th>
                                <th>Payment Status</th>
                                <th>Invoice</th>
                                <th>Actions</th>
                                <th class="d-none">Shop Name</th>
                                <th class="d-none">Sales Manager</th>
                                <th class="d-none">Field Staff</th>
                                <th class="d-none">Phone</th>
                                <th class="d-none">GST</th>
                                <th class="d-none">Drug License</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    {{-- Admin Edit Modal --}}
    <div class="modal fade" id="editOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Order</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editOrderForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Retailer</label>
                                <select name="retailer_id" id="edit_retailer_id" class="form-select" required>
                                    @foreach($retailers as $r) <option value="{{ $r->id }}">{{ $r->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Distributor</label>
                                <select name="distributor_id" id="edit_distributor_id" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach($distributors as $d) <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <select name="status" id="edit_status" class="form-select" required>
                                    @foreach(['pending', 'processing', 'approved', 'delivered', 'cancelled'] as $st)
                                        <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Items Section --}}
                        <div class="mb-3 p-3 bg-light">
                            <h6>Items</h6>
                            <div class="input-group mb-2">
                                <select id="edit_product_select" class="form-control">
                                    <option value="">Select Product</option>
                                    @foreach($products as $p) <option value="{{ $p->id }}" data-price="{{ $p->mrp }}">
                                        {{ $p->product_name }}
                                    </option> @endforeach
                                </select>
                                <button type="button" class="btn btn-primary" id="btn_add_prod_edit">Add</button>
                            </div>
                            <table class="table table-bordered table-sm" id="edit_items_table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                        <td id="edit_grand_total">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>


                        <div class="mb-3">
                            <label>Delivery Notes</label>
                            <textarea name="delivery_notes" id="edit_delivery_notes" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Assign Field Staff Modal --}}
    <div class="modal fade" id="assignFieldStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Field Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalOrderId">
                    <div class="mb-3">
                        <label class="form-label">Select Field Staff</label>
                        <select id="modalFieldStaffSelect" class="form-select">
                            <option value="">-- Select --</option>
                            @foreach($fieldstaffs as $fs) <option value="{{ $fs->id }}">{{ $fs->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirmAssignFieldStaffBtn">Assign</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Show Modal --}}
    <div class="modal fade" id="showOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="fw-bold mb-0">Order Details <span id="modalOrderCode" class="text-primary ms-2"></span></h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3" id="showOrderContent">
                    <!-- Dynamic content will be injected here -->
                </div>
            </div>
        </div>
    </div>

    {{-- Submit Return Modal --}}
    <div class="modal fade" id="submitReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Return Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="submitReturnForm">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="return_order_id">
                        <input type="hidden" name="product_id" id="return_product_id">
                        
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Product</label>
                            <div id="return_product_name" class="fw-bold text-main-theme"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Return Quantity (<span id="return_unit_text">Nos</span>)</label>
                            <input type="number" name="quantity" id="return_qty_input" class="form-control" step="0.01" required min="0.01">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason for Return</label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="E.g., Damaged product, Expired..."></textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Upload Images (Proof)</label>
                            <input type="file" name="images[]" id="return_images_input" class="form-control" accept="image/*" required multiple>
                            <div id="image_preview_container" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Submit Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this order? This process cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Order</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Confirmation Modal --}}
    <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 py-3 px-4 position-relative" style="background-color: #1e293b;">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fa fa-exclamation-triangle fs-4 text-white"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" style="color: #ffffff !important;">Cancel Order</h5>
                            <p class="small text-white text-opacity-85 mb-0" id="cancel_order_code_display" style="color: rgba(255,255,255,0.85) !important;"></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 rounded-3 mb-4 d-flex align-items-start bg-secondary-subtle border border-secondary border-opacity-25">
                        <i class="fa fa-info-circle text-secondary mt-1 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1 text-secondary-emphasis">Are you sure?</h6>
                            <p class="text-body-secondary small mb-0">Cancelling this order will stop its progress. This action is irreversible once confirmed.</p>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-uppercase" style="color: var(--med-text-main);">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea id="cancel_reason_input" class="form-control border-0 bg-light shadow-none" rows="4" required
                            placeholder="E.g., Ordered by mistake, found better price elsewhere, stock delay..." 
                            style="border-radius: 12px; resize: none;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none px-4" data-bs-dismiss="modal">Keep Order</button>
                    <button type="button" class="btn px-4 py-2 fw-bold shadow-sm" id="confirmCancelBtn" 
                        style="border-radius: 10px; background-color: #1e293b; color: #fff;">
                        Yes, Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Distributor Batch Selection Modal --}}
    <div class="modal fade" id="distributorApproveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Retailer Order & Allocate Batches</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="distributorApproveForm">
                    <div class="modal-body">
                        <input type="hidden" id="approve_order_id" name="order_id">
                        <div class="alert alert-info py-2">
                            <small><i class="fa fa-info-circle"></i> Please allocate specific batches for each product to
                                fulfill this order. Total allocated quantity must match ordered quantity.</small>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Upload Invoice (PDF, JPG, PNG)</label>
                                <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="batch_allocation_table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Product</th>
                                        <th style="width: 10%;" class="text-center">Ordered</th>
                                        <th style="width: 60%;">Batch Allocation</th>
                                    </tr>
                                </thead>
                                <tbody id="batch_allocation_body">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="btnSubmitDistributorApprove">Approve & Allocate
                            Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const canAcceptOrder = {{ Auth::user()->hasAnyRole(['distributor', 'admin', 'superadmin', 'manager']) ? 'true' : 'false' }};
        const canAssignFieldStaff = {{ Auth::user()->hasAnyRole(['admin', 'superadmin', 'manager', 'distributor']) ? 'true' : 'false' }};
        const canCancelRetailerOrder = {{ (Auth::user()->hasAnyRole(['admin', 'superadmin', 'retailer']) || Auth::user()->hasPermissionToCategory('retailer_orders', 'delete')) ? 'true' : 'false' }};
        const isRetailer = {{ Auth::user()->hasRole('retailer') ? 'true' : 'false' }};
        const isAdmin = {{ Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']) ? 'true' : 'false' }};

        $(document).ready(function () {
            var editItems = {};

            var ajaxUrl = "{{ route('admin.retailer.index') }}";
            const urlParams = new URLSearchParams(window.location.search);
            
            // Handle status from URL
            const urlStatus = urlParams.get('status');
            if (urlStatus) {
                $(`#orderStatusTabs button[data-status="${urlStatus}"]`).tab('show');
            }

            let exportOptions = {
                columns: [0, 1, 2, 12, 13, 4, 5, 14, 15, 16, 17, 3, 6, 7, 8, 9],
                format: {
                    body: function(data, row, column, node) {
                        if (column === 2) {
                            let tableApi = $('#orders-table').DataTable();
                            let rowData = tableApi.row(row).data();
                            return rowData ? (rowData.retailer_name || '').trim() : '';
                        }
                        if (column === 5) {
                            let tableApi = $('#orders-table').DataTable();
                            let rowData = tableApi.row(row).data();
                            if (rowData && rowData.product_summary) {
                                return rowData.product_summary.split('|||').map(it => it.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim()).join('\n');
                            }
                        }
                        if (column === 6) {
                            let tableApi = $('#orders-table').DataTable();
                            let rowData = tableApi.row(row).data();
                            if (rowData && rowData.brand_summary) {
                                return rowData.brand_summary.split('|||').join('\n');
                            }
                        }
                        if (column === 14) {
                            let tableApi = $('#orders-table').DataTable();
                            let rowData = tableApi.row(row).data();
                            if (rowData) {
                                let st = (rowData.status || '').toLowerCase();
                                if (st === 'delivered' && rowData.delivered_at) {
                                    return `Placed: ${rowData.placed_at}\nDelivered: ${rowData.delivered_at}`;
                                }
                                return `Placed: ${rowData.placed_at}`;
                            }
                        }
                        if (typeof data === 'string') {
                            let clean = data.replace(/<[^>]*>?/gm, '').trim();
                            if (clean.includes('₹') || clean.includes('â‚¹')) {
                                clean = clean.replace(/₹/g, '').replace(/â‚¹/g, '').replace(/,/g, '').trim();
                            }
                            let isNumericCode = /^\d+$/.test(clean) && clean.length >= 10;
                            let isOrderCode = /^[A-Z0-9\-]+$/i.test(clean) && clean.length >= 8 && (clean.indexOf('-') !== -1 || clean.startsWith('ORD'));
                            let isGstOrDl = /^[A-Z0-9]+$/i.test(clean) && (clean.length === 15 || clean.length === 21);
                            if (isNumericCode || isOrderCode || isGstOrDl) {
                                return '\t' + clean;
                            }
                            return clean;
                        }
                        return data;
                    }
                }
            };

            var table = $('#orders-table').DataTable({
                order: [[8, 'desc']],
                autoWidth: false,
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-4 gy-4 d-flex align-items-center'<'col-12 col-lg-4 d-flex justify-content-center justify-content-lg-start'l><'col-12 col-lg-4 d-flex justify-content-center payment-filter-container'><'col-12 col-lg-4 d-flex justify-content-center justify-content-lg-end'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-sm-12 col-md-5 d-flex justify-content-center justify-content-md-start align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end align-items-center'p>>",
                buttons: {
                    dom: {
                        button: {
                            className: 'btn btn-sm btn-icon'
                        }
                    },
                    buttons: [{
                        extend: 'copy',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fa fa-copy"></i> Copy',
                        exportOptions: exportOptions
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-info btn-sm text-white',
                        text: '<i class="fa fa-file-csv"></i> CSV',
                        exportOptions: exportOptions
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fa fa-file-excel"></i> Excel',
                        exportOptions: exportOptions
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fa fa-file-pdf"></i> PDF',
                        exportOptions: exportOptions
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-dark btn-sm',
                        text: '<i class="fa fa-print"></i> Print',
                        exportOptions: exportOptions
                    }
                    ]
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: ajaxUrl,
                    data: function(d) {
                        d.payment_status = $('input[name="payment_status"]:checked').val();
                        d.status = $('#orderStatusTabs .nav-link.active').data('status');
                        d.retailer_id = urlParams.get('retailer_id');
                        d.start_date = $('#start_date_filter').val();
                        d.end_date = $('#end_date_filter').val();
                    }
                },
                columns: [{
                    data: null,
                    name: 'sl_no',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'order_code',
                    name: 'order_code'
                },
                {
                    data: 'retailer_name',
                    name: 'retailer_name',
                    visible: !{{ Auth::user()->hasRole('retailer') ? 'true' : 'false' }},
                    render: function(data, type, row) {
                        return `<div class="d-flex flex-column">
                                    <span class="fw-bold text-primary entity-info-popover" 
                                          style="cursor: pointer;"
                                          data-bs-toggle="popover" 
                                          data-bs-trigger="hover" 
                                          data-bs-html="true"
                                          title="Retailer Details"
                                          data-bs-content="<b>Shop:</b> ${row.retailer_shop}<br><b>SM:</b> ${row.retailer_sm_name}<br><b>FS:</b> ${row.retailer_fs_name}<br><b>Phone:</b> ${row.retailer_phone}<br><b>GST:</b> ${row.retailer_gst}<br><b>DL:</b> ${row.retailer_dl}">
                                        ${data}
                                    </span>
                                    <span class="small text-muted">${row.retailer_shop}</span>
                                </div>`;
                    }
                },
                {
                    data: 'distributor_name',
                    name: 'distributor_name',
                    render: function (data, type, row) {
                        if (!data || data === 'N/A') return '-';
                        if (type !== 'display') return data;
                        return `<span class="fw-bold" style="color: var(--med-text-main);">${data}</span>`;
                    }
                },
                {
                    data: 'product_summary',
                    name: 'product_summary',
                    className: 'product-col',
                    orderable: false,
                    render: function (data, type, row) {
                        if (!data) return '-';
                        let items = data.split('|||');
                        if (type !== 'display') {
                            return items.map(it => it.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim()).join('\n');
                        }
                        return items.map(it => `<div class="py-1">${it}</div>`).join('<div class="border-bottom border-light opacity-50 my-1"></div>');
                    }
                },
                {
                    data: 'brand_summary',
                    name: 'brand_summary',
                    className: 'brand-col',
                    orderable: false,
                    render: function (data, type, row) {
                        if (!data) return '-';
                        let items = data.split('|||');
                        if (type !== 'display') {
                            return items.join(', ');
                        }
                        return items.map(it => `<div class="py-1 fw-semibold text-muted" style="font-size: 0.78rem;">${it}</div>`).join('<div class="border-bottom border-light opacity-50 my-1"></div>');
                    }
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    render: function (data, type, row) {
                        if (type !== 'display') return data;
                        let status = (row.status || '').toLowerCase();
                        let isEstimated = status.includes('pending') || status.includes('processing');
                        if (isEstimated) {
                            return `<div class="d-flex flex-column">
                                <span class="fw-bold text-secondary">₹${parseFloat(data).toFixed(2)}</span>
                                <span class="text-muted" style="font-size: 0.65rem; font-weight: normal; margin-top: 1px;">(Est. Total)</span>
                            </div>`;
                        } else {
                            let estAmt = (row.metadata && row.metadata.estimated_amount !== undefined) ? parseFloat(row.metadata.estimated_amount).toFixed(2) : parseFloat(data).toFixed(2);
                            return `<div class="d-flex flex-column">
                                <span class="fw-bold text-success">₹${parseFloat(data).toFixed(2)} <small class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-0 ms-1" style="font-size: 0.55rem; font-weight: bold; vertical-align: middle;">INVOICED</small></span>
                                <span class="text-muted small" style="font-size: 0.65rem; font-weight: 500; opacity: 0.85; margin-top: 2px;">Est: ₹${estAmt}</span>
                            </div>`;
                        }
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data, type, row) {
                        if (type !== 'display') return data;
                        let status = (data || '').toLowerCase();
                        let badgeClass = 'bg-secondary text-white';
                        if (status === 'pending') badgeClass = 'bg-secondary text-white';
                        else if (status === 'processing') badgeClass = 'bg-warning text-white';
                        else if (status === 'approved') badgeClass = 'bg-info text-white';
                        else if (status === 'delivered') badgeClass = 'bg-success text-white';
                        else if (status === 'cancelled') badgeClass = 'bg-danger text-white';
                        else if (status === 'rejected') badgeClass = 'bg-dark-red text-white';

                        return `<span class="badge ${badgeClass}" style="font-size: 0.8rem; padding: 0.5em 0.9em; font-weight: 600;">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                {
                    data: 'placed_at',
                    name: 'placed_at',
                    render: function (data, type, row) {
                        if (type !== 'display') return data;
                        let html = `<div>${data}</div>`;
                        let st = (row.status || '').toLowerCase();
                        if (st === 'delivered' && row.delivered_at) {
                            html += `<div class="text-success fw-bold mt-1" style="font-size: 0.72rem;"><i class="fa fa-check-circle me-1"></i>Delivered: ${row.delivered_at}</div>`;
                        }
                        return html;
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    render: function (data, type, row) {
                        if (type !== 'display') return data || 'pending';
                        let status = (data || 'pending').toLowerCase();
                        let badgeClass = 'bg-secondary';
                        if (status === 'paid') badgeClass = 'bg-success text-white';
                        else if (status === 'failed') badgeClass = 'bg-danger text-white';
                        else badgeClass = 'bg-secondary text-white';

                        return `<span class="badge ${badgeClass}" style="font-size: 0.8rem; padding: 0.5em 0.9em; font-weight: 600;">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
                    }
                },
                {
                    data: 'invoice_url',
                    name: 'invoice_url',
                    className: 'no-export',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let st = (row.status || '').toLowerCase().replace(/ /g, '_');
                        if (data && st === 'delivered') {
                            let ext = data.split('.').pop().toLowerCase();
                            let icon = ext === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-image-o';
                            let code = row.order_code || 'Order';
                            let filename = `Invoice_${code}.${ext}`;
                            return `<a href="${data}" download="${filename}" target="_blank" class="btn btn-sm btn-success"><i class="fa ${icon}"></i> &nbsp;Download</a>`;
                        }
                        return '<span class="text-muted small">No Invoice</span>';
                    }
                },
                {
                    data: null,
                    className: 'no-export',
                    orderable: false,
                    searchable: false,
                    render: function (d, t, row) {
                        let btns = `<div class="action-buttons">`;
                        btns += `<button class="btn btn-info btn-sm view-btn" title="View Details"><i class="fa fa-eye"></i></button>`;

                        let st = (row.status || '').toLowerCase().replace(/ /g, '_');

                        // Print Invoice
                        if (st === 'delivered') {
                            let invoiceUrl = "{{ route('admin.retailer.invoice', ':id') }}".replace(':id', row.id);
                            btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="Print Invoice"><i class="fa fa-print"></i></a>`;
                        }

                        // Cancel Order (controlled by permission)
                        if (st === 'pending' && canCancelRetailerOrder) {
                            btns += `<button class="btn btn-danger btn-sm cancel-order-btn" data-id="${row.id}" title="Cancel Order"><i class="fa fa-times-circle"></i></button>`;
                        }

                        // Retailer Confirmation
                        if (st === 'approved' && isRetailer) {
                            btns += `<button class="btn btn-success btn-sm confirm-receipt-btn" data-id="${row.id}" title="Confirm Receipt"><i class="fa fa-check-square"></i></button>`;
                        }

                        btns += `</div>`;
                        return btns;
                    }
                },
                {
                    data: 'retailer_shop',
                    visible: false,
                    render: function(d) { return d || '-'; }
                },
                {
                    data: 'retailer_sm_name',
                    visible: false,
                    render: function(d) { return d || '-'; }
                },
                {
                    data: 'retailer_fs_name',
                    visible: false,
                    render: function(d) { return d || '-'; }
                },
                {
                    data: 'retailer_phone',
                    visible: false,
                    render: function(d) { return d || '-'; }
                },
                {
                    data: 'retailer_gst',
                    visible: false,
                    render: function(d) { return d || '-'; }
                },
                {
                    data: 'retailer_dl',
                    visible: false,
                    render: function(d) { return d || '-'; }
                }
                ],
                drawCallback: function() {
                    $('[data-bs-toggle="popover"]').popover();
                }
            });

            // Move Custom Filter
            $('#payment-filter-wrapper').children().appendTo('.payment-filter-container');

            // Initialize Flatpickr for Date Filters
            const startPicker = flatpickr("#start_date_filter", {
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    endPicker.set('minDate', dateStr);
                    table.ajax.reload();
                }
            });
            const endPicker = flatpickr("#end_date_filter", {
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    startPicker.set('maxDate', dateStr);
                    table.ajax.reload();
                }
            });

            // Clear Date Filters
            $('#clear_dates_btn').click(function() {
                startPicker.clear();
                endPicker.clear();
                startPicker.set('maxDate', null);
                endPicker.set('minDate', null);
                table.ajax.reload();
            });

            // Initialize Minimal Select2
            $('.select2-minimal').select2({
                width: '100%',
                allowClear: true
            });


            // Payment Status listener
            $('input[name="payment_status"]').on('change', function() {
                table.ajax.reload();
            });


            // Tab Click
            $('#orderStatusTabs .nav-link').on('click', function(e) {
                e.preventDefault();
                $('#orderStatusTabs .nav-link').removeClass('active text-primary border-bottom-0').addClass('text-muted');
                $(this).removeClass('text-muted').addClass('active text-primary border-bottom-0');
                table.ajax.reload();
            });

            // --- Admin Edit Logic ---
            $(document).on('click', '.edit-btn', function () {
                let tr = $(this).closest('tr');
                if ($(tr).hasClass('child')) {
                    tr = $(tr).prev();
                }
                let row = $('#orders-table').DataTable().row(tr).data();
                $('#edit_retailer_id').val(row.retailer_id);
                $('#edit_distributor_id').val(row.distributor_id);
                $('#edit_status').val(row.status.toLowerCase().replace(/ /g, '_'));

                $('#edit_delivery_notes').val(row.delivery_notes);

                editItems = {};
                row.items.forEach(function (i) {
                    let vInfo = [i.side, i.size].filter(v => v).join(' / ');
                    let displayName = i.product_name || i.name;
                    if (vInfo) displayName += ` [${vInfo}]`;
                    
                    editItems[i.product_id] = {
                        id: i.product_id,
                        name: displayName,
                        side: i.side,
                        size: i.size,
                        qty: i.quantity || i.qty,
                        price: parseFloat(i.unit_price || i.price),
                        order_item_id: i.order_item_id
                    };
                });
                renderEditItems();
                $('#editOrderForm').attr('action', `/admin/retailer/${row.id}`);
                $('#editOrderModal').modal('show');
            });

            $('#btn_add_prod_edit').click(function () {
                let sel = $('#edit_product_select option:selected');
                let id = sel.val();
                if (!id) return;
                if (editItems[id]) return alert('Already added');
                editItems[id] = {
                    id: id,
                    name: sel.text(),
                    qty: 1,
                    price: parseFloat(sel.data('price'))
                };
                renderEditItems();
            });

            function renderEditItems() {
                let tbody = $('#edit_items_table tbody');
                tbody.empty();
                let total = 0;
                if (Object.keys(editItems).length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center text-muted">No Items Added</td></tr>');
                } else {
                    $.each(editItems, function (id, item) {
                        let price = parseFloat(item.price) || 0;
                        let qty = parseInt(item.qty) || 1;
                        let sub = price * qty;
                        total += sub;
                        let unit = item.unit || 'Box';
                        let options = '';
                        ['Box', 'Carton', 'Strips'].forEach(function (u) {
                            options += `<option value="${u}" ${unit === u ? 'selected' : ''}>${u}</option>`;
                        });

                        tbody.append(`
                                                                                                                                                                                                            <tr>
                                                                                                                                                                                                                <td>${item.name}
                                                                                                                                                                                                                    <input type="hidden" name="items[${id}][product_id]" value="${id}">
                                                                                                                                                                                                                    <input type="hidden" name="items[${id}][side]" value="${item.side || ''}">
                                                                                                                                                                                                                    <input type="hidden" name="items[${id}][size]" value="${item.size || ''}">
                                                                                                                                                                                                                    ${item.order_item_id ? `<input type="hidden" name="items[${id}][order_item_id]" value="${item.order_item_id}">` : ''}
                                                                                                                                                                                                                </td>
                                                                                                                                                                                                                <td>
                                                                                                                                                                                                                    <select class="form-select form-select-sm unit-select-edit" data-id="${id}" name="items[${id}][unit]" style="width:90px; margin: 0 auto;">
                                                                                                                                                                                                                        ${options}
                                                                                                                                                                                                                    </select>
                                                                                                                                                                                                                </td>
                                                                                                                                                                                                                <td>
                                                                                                                                                                                                                    <input type="number" class="form-control form-control-sm edit-qty" data-id="${id}" value="${qty}" name="items[${id}][quantity]" min="1" style="width:80px; margin: 0 auto;">
                                                                                                                                                                                                                </td>
                                                                                                                                                                                                                <td class="text-end">${price.toFixed(2)}<input type="hidden" name="items[${id}][unit_price]" value="${price}"></td>
                                                                                                                                                                                                                <td class="text-end">${sub.toFixed(2)}</td>
                                                                                                                                                                                                                <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-edit" data-id="${id}">X</button></td>
                                                                                                                                                                                                            </tr>
                                                                                                                                                                                                            `);
                    });
                }
                $('#edit_grand_total').text(total.toFixed(2));
            }

            $(document).on('change', '.edit-qty', function () {
                let id = $(this).data('id');
                editItems[id].qty = parseInt($(this).val());
                renderEditItems();
            });

            $(document).on('change', '.unit-select-edit', function () {
                let id = $(this).data('id');
                let val = $(this).val();
                if (editItems[id]) {
                    editItems[id].unit = val;
                }
            });
            $(document).on('click', '.remove-edit', function () {
                delete editItems[$(this).data('id')];
                renderEditItems();
            });


            // --- Admin Edit Logic ---
            $('#editOrderForm').submit(function (e) {
                e.preventDefault();
                let form = $(this);
                let url = form.attr('action');
                let data = form.serialize();

                $.ajax({
                    url: url,
                    type: 'POST', // Method spoofing is handled by _method input
                    data: data,
                    success: function (response) {
                        if (response.success || response.message) {
                            $('#editOrderModal').modal('hide');
                            table.ajax.reload();
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: response.success || response.message || 'Order updated successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error || 'Something went wrong.'
                            });
                        }
                    },
                    error: function (xhr) {
                        let err = 'An error occurred.';
                        if (xhr.responseJSON) {
                            err = xhr.responseJSON.message || xhr.responseJSON.error || err;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err
                        });
                    }
                });
            });

            // --- Delete & Cancel Logic (modals) ---
            let deleteOrderId = null;
            $(document).on('click', '.delete-order-btn', function () {
                deleteOrderId = $(this).data('id');
                $('#deleteConfirmModal').modal('show');
            });

            $('#confirmDeleteBtn').click(function () {
                if (!deleteOrderId) return;
                $.ajax({
                    url: `/admin/retailer/${deleteOrderId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        $('#deleteConfirmModal').modal('hide');
                        if (res.success) {
                            table.ajax.reload();
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                            // // showToast('success', res.success || 'Order deleted');
                        } else {
                            showToast('error', res.error || 'Failed to delete order');
                        }
                    },
                    error: function (xhr) {
                        $('#deleteConfirmModal').modal('hide');
                        let err = 'An error occurred.';
                        if (xhr.responseJSON) {
                            err = xhr.responseJSON.message || xhr.responseJSON.error || err;
                        }
                        showToast('error', err);
                    }
                });
            });

            // Cancel Order (Admin direct cancel of pending)
            let cancelOrderId = null;
            $(document).on('click', '.cancel-order-btn', function () {
                let tr = $(this).closest('tr');
                if ($(tr).hasClass('child')) tr = $(tr).prev();
                let row = table.row(tr).data();
                
                cancelOrderId = $(this).data('id');
                $('#cancel_order_code_display').text('#' + (row ? row.order_code : 'Order'));
                $('#cancel_reason_input').val('');
                $('#cancelConfirmModal').modal('show');
            });

            $('#confirmCancelBtn').click(function () {
                if (!cancelOrderId) return;
                let reason = $('#cancel_reason_input').val().trim();
                
                if (!reason) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Reason Required',
                        text: 'Please provide a valid reason for cancelling this order.',
                        confirmButtonColor: '#ffc107',
                        confirmButtonText: 'Understood'
                    });
                    return;
                }

                let $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cancelling...');

                $.post(`/admin/retailer/${cancelOrderId}/cancel-order`, {
                    _token: '{{ csrf_token() }}',
                    cancellation_reason: reason
                }, function (res) {
                    $('#cancelConfirmModal').modal('hide');
                    if (res.success) {
                        table.ajax.reload(null, false);
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                        Swal.fire({
                            icon: 'success',
                            title: 'Order Cancelled',
                            text: res.success || 'The order has been successfully cancelled.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', res.error || 'Failed to cancel order', 'error');
                    }
                }).fail(function (xhr) {
                    $('#cancelConfirmModal').modal('hide');
                    let err = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Request failed';
                    Swal.fire('Error', err, 'error');
                }).always(function() {
                    $btn.prop('disabled', false).text('Yes, Cancel Order');
                });
            });

            // Request cancellation (Distributor requests)
            $(document).on('click', '.request-cancel-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Request Cancellation',
                    input: 'text',
                    inputLabel: 'Reason',
                    inputPlaceholder: 'Enter cancellation reason',
                    showCancelButton: true,
                    inputValidator: (value) => {
                        if (!value) return 'You need to write something!';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`/admin/retailer/${id}/request-cancellation`, {
                            _token: '{{ csrf_token() }}',
                            cancellation_reason: result.value
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                if (window.updateSidebarCounts) window.updateSidebarCounts();
                                // // showToast('success', res.success || 'Cancellation requested');
                                table.ajax.reload(null, false);
                            } else showToast('error', res.error || 'Failed to request cancellation');
                        }).fail(function () {
                            showToast('error', 'Request failed');
                        });
                    }
                });
            });

            // Approve cancellation (Sales Manager)
            $(document).on('click', '.approve-cancel-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Approve cancellation and restore stock?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`/admin/retailer/${id}/approve-cancellation`, {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                if (window.updateSidebarCounts) window.updateSidebarCounts();
                                // // showToast('success', res.success || 'Cancellation approved');
                                table.ajax.reload(null, false);
                            } else showToast('error', res.error || 'Failed to approve cancellation');
                        }).fail(function () {
                            showToast('error', 'Request failed');
                        });
                    }
                });
            });


            // Confirm Receipt (Retailer)
            $(document).on('click', '.confirm-receipt-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Confirm Order?',
                    text: "Are you sure you have received this order?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, I received it',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ route('admin.retailer.confirm-receipt', ':id') }}".replace(':id', id);
                        $.post(url, { _token: '{{ csrf_token() }}' }, function (res) {
                            if (res.success) {
                                table.ajax.reload(null, false);
                                if (window.updateSidebarCounts) window.updateSidebarCounts();
                                // // showToast('success', res.success);
                                if (res.new_points) {
                                    $('.notification-box .badge').text(parseFloat(res.new_points).toFixed(2));
                                }
                            } else {
                                showToast('error', res.error || 'Failed to confirm order');
                            }
                        }).fail(function (xhr) {
                            showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                        });
                    }
                });
            });


            // --- Distributor Approve & Batch Allocation Logic ---


            $(document).on('click', '.distributor-approve-btn', function () {
                let row = $(this).data('row');
                $('#approve_order_id').val(row.id);
                let tbody = $('#batch_allocation_body');
                tbody.empty().html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading items...</td></tr>');

                $('#distributorApproveModal').modal('show');

                let itemsHtml = '';
                let itemsProcessed = 0;

                if (!row.items || row.items.length === 0) {
                    tbody.html('<tr><td colspan="3" class="text-center text-danger">No items found in this order.</td></tr>');
                    return;
                }

                row.items.forEach(function (item, index) {
                    let productId = item.product_id;
                    let orderItemId = item.order_item_id;
                    let orderedQty = item.quantity;
                    let distributorId = row.distributor_id;

                    // Fetch batches for this product and distributor
                    $.get("{{ route('inventories.index') }}", {
                        product_id: productId,
                        distributor_id: distributorId,
                        length: -1 // Fetch all batches
                    }, function (res) {
                        let batches = res.data || [];
                        let batchOptions = '<option value="">-- Select Batch --</option>';
                        batches.forEach(b => {
                            if (b.stock > 0) {
                                batchOptions += `<option value="${b.id}" data-stock="${b.stock}">Batch: ${b.batch_no} | Exp: ${b.expiry_date} | Stock: ${b.stock}</option>`;
                            }
                        });

                        let itemRow = `
                                                                                                        <tr class="product-row" data-item-id="${orderItemId}" data-ordered-qty="${orderedQty}">
                                                                                                            <td>
                                                                                                                <strong>${item.product_name}</strong>
                                                                                                                <div class="small text-muted">
                                                                                                                    Pack: ${item.pack || 'N/A'} | 
                                                                                                                    Strip Size: ${item.strip_size || 1} | 
                                                                                                                    Box Size: ${item.box_size || 1} | 
                                                                                                                    Carton Size: ${item.carton_size || 1} |
                                                                                                                    ${(item.side || item.size) ? `<span class="badge bg-soft-info text-info border-0 px-2" style="font-size: 0.65rem;">${[item.side, item.size].filter(Boolean).join(' / ')}</span>` : ''}
                                                                                                                </div>
                                                                                                                <input type="hidden" name="items[${orderItemId}][product_id]" value="${productId}">
                                                                                                            </td>
                                                                                                            <td class="text-center font-weight-bold">
                                                                                                                <span class="fs-5">${orderedQty}</span><br>
                                                                                                                <span class="badge bg-light text-dark border">${item.unit || 'Strips'}</span>
                                                                                                            </td>
                                                                                                            <td>
                                                                                                                <div class="allocation-container" id="allocation_for_${orderItemId}">
                                                                                                                    <input type="hidden" name="items_batches[${orderItemId}][order_item_id]" value="${orderItemId}">
                                                                                                                    <div class="allocation-item mb-2 d-flex gap-2">
                                                                                                                        <select name="items_batches[${orderItemId}][batches][0][inventory_id]" class="form-select form-select-sm batch-select" required style="flex: 2;">
                                                                                                                            ${batchOptions}
                                                                                                                        </select>
                                                                                                                        <input type="number" name="items_batches[${orderItemId}][batches][0][quantity]" class="form-control form-control-sm qty-input" value="${orderedQty}" min="1" required style="flex: 1;" placeholder="Qty">
                                                                                                                        <button type="button" class="btn btn-sm btn-outline-primary add-more-batch" data-item-id="${orderItemId}" data-options='${JSON.stringify(batchOptions).replace(/'/g, "&apos;")}' title="Split into multiple batches"><i class="fa fa-plus"></i></button>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                                <div class="small text-danger validation-msg" style="display:none;">Total must match ${orderedQty}</div>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    `;

                        if (itemsProcessed === 0) tbody.empty();
                        tbody.append(itemRow);
                        itemsProcessed++;
                    }).fail(function () {
                        if (itemsProcessed === 0) tbody.empty();
                        tbody.append(`<tr><td>${item.product_name}</td><td colspan="2" class="text-center text-danger">Failed to load batches</td></tr>`);
                        itemsProcessed++;
                    });
                });
            });

            // Add more batch rows for an item
            $(document).on('click', '.add-more-batch', function () {
                let itemId = $(this).data('item-id');
                let options = $(this).data('options');
                let container = $(`#allocation_for_${itemId}`);
                let idx = container.find('.allocation-item').length;

                let html = `
                                                                                                <div class="allocation-item mb-2 d-flex gap-2">
                                                                                                    <select name="items_batches[${itemId}][batches][${idx}][inventory_id]" class="form-select form-select-sm batch-select" required style="flex: 2;">
                                                                                                        ${options}
                                                                                                    </select>
                                                                                                    <input type="number" name="items_batches[${itemId}][batches][${idx}][quantity]" class="form-control form-control-sm qty-input" value="0" min="1" required style="flex: 1;" placeholder="Qty">
                                                                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-allocation-row"><i class="fa fa-times"></i></button>
                                                                                                </div>
                                                                                            `;
                container.append(html);
            });

            $(document).on('click', '.remove-allocation-row', function () {
                $(this).closest('.allocation-item').remove();
            });

            // Form Validation and Submission
            $('#distributorApproveForm').submit(function (e) {
                e.preventDefault();
                let isValid = true;

                // Validate each product row
                $('.product-row').each(function () {
                    let ordered = parseInt($(this).data('ordered-qty'));
                    let allocated = 0;
                    $(this).find('.qty-input').each(function () {
                        allocated += parseInt($(this).val() || 0);
                    });

                    if (allocated !== ordered) {
                        isValid = false;
                        $(this).find('.validation-msg').show().text(`Quantity mismatch: Allocated ${allocated} of ${ordered}`);
                        $(this).find('.allocation-container').addClass('border border-danger rounded p-1');
                    } else {
                        $(this).find('.validation-msg').hide();
                        $(this).find('.allocation-container').removeClass('border border-danger rounded p-1');
                    }

                    // Also check if batches are selected
                    $(this).find('.batch-select').each(function () {
                        if (!$(this).val()) {
                            isValid = false;
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });
                });

                if (!isValid) {
                    Swal.fire('Validation Error', 'Please ensure all items have correctly allocated batches matching the ordered quantity.', 'error');
                    return;
                }

                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');
                let orderId = $('#approve_order_id').val();
                let url = "{{ route('admin.retailer.accept', ':id') }}".replace(':id', orderId);

                let $btn = $('#btnSubmitDistributorApprove');
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            $('#distributorApproveModal').modal('hide');
                            table.ajax.reload(null, false);
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                            // showToast('success', res.success);
                        } else {
                            showToast('error', res.error || 'Failed to approve order');
                        }
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred during approval.');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Approve & Allocate Stock');
                    }
                });
            });

            // --- Accept & Assign Logic ---

            $(document).on('click', '.accept-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Accept this order?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Accept'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`/admin/retailer/${id}/accept`, {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                if (window.updateSidebarCounts) window.updateSidebarCounts();
                                // // showToast('success', res.success || 'Order approved successfully.');
                                table.ajax.reload(null, false);
                            } else {
                                showToast('error', res.error || 'Error accepting order');
                            }
                        }).fail(function () {
                            showToast('error', 'Request failed.');
                        });
                    }
                });
            });

            $(document).on('click', '.assign-fs-btn', function () {
                $('#modalOrderId').val($(this).data('id'));
                $('#assignFieldStaffModal').modal('show');
            });

            $('#confirmAssignFieldStaffBtn').click(function () {
                let form = {
                    _token: '{{ csrf_token() }}',
                    fieldstaff_id: $('#modalFieldStaffSelect').val()
                };

                if (!form.fieldstaff_id) {
                    Swal.fire('Warning', 'Please select a field staff.', 'warning');
                    return;
                }

                $.post(`/admin/retailer/${$('#modalOrderId').val()}/assign-fieldstaff`, form, function (res) {
                    if (res.success) {
                        $('#assignFieldStaffModal').modal('hide');
                        table.ajax.reload();
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                        // // showToast('success', 'Field Staff assigned successfully');
                        table.ajax.reload(null, false);
                    } else {
                        showToast('error', res.error || 'Failed to assign field staff');
                    }
                }).fail(function () {
                    showToast('error', 'Request failed.');
                });
            });

            // --- Show Logic ---
            $(document).on('click', '.view-btn', function () {
                let tr = $(this).closest('tr');
                if ($(tr).hasClass('child')) {
                    tr = $(tr).prev();
                }
                let row = $('#orders-table').DataTable().row(tr).data();
                if (!row) return;

                let payStatus = (row.payment_status || 'pending').toLowerCase();
                let payBadgeClass = payStatus === 'paid' ? 'bg-success text-white' : 'bg-secondary text-white';
                $('#modalOrderCode').html(`#${row.order_code} <span class="badge ${payBadgeClass} ms-2" style="font-size: 0.75rem; vertical-align: middle; padding: 0.3em 0.7em;">${payStatus.toUpperCase()}</span>`);

                let detailsHtml = `
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="card h-100 border-0 shadow-sm bg-card-theme" style="border-radius: 10px !important;">
                                <div class="card-body py-2 px-3">
                                    <h6 class="text-uppercase text-muted-theme fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 0.05em;"><i class="fa fa-shop me-2"></i>Retailer Info</h6>
                                    <h5 class="fw-bold mb-0 text-main-theme" style="font-size: 1rem;">${row.retailer_shop || row.retailer_name}</h5>
                                    ${row.retailer_shop ? `<div class="text-muted-theme small mb-0" style="font-size: 0.75rem;"><i class="fa fa-user me-2"></i>${row.retailer_name}</div>` : ''}
                                    <div class="d-flex align-items-center mb-0 text-main-theme"><i class="fa fa-phone text-muted-theme me-2" style="width: 12px; font-size: 0.75rem;"></i> <span class="small" style="font-size: 0.8rem;">${row.retailer_phone || 'N/A'}</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm bg-card-theme" style="border-radius: 10px !important;">
                                <div class="card-body py-2 px-3">
                                    <h6 class="text-uppercase text-muted-theme fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 0.05em;"><i class="fa fa-building me-2"></i>Distributor Info</h6>
                                    <h5 class="fw-bold mb-0 text-main-theme" style="font-size: 1rem;">${row.distributor_name || 'N/A'}</h5>
                                    <div class="d-flex align-items-center mb-0 text-main-theme"><i class="fa fa-phone text-muted-theme me-2" style="width: 12px; font-size: 0.75rem;"></i> <span class="small" style="font-size: 0.8rem;">${row.distributor_phone || 'N/A'}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3 bg-card-theme" style="border-radius: 12px !important; overflow: hidden;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0 align-middle">
                                    <thead style="background: rgba(var(--med-primary-rgb), 0.02); border-bottom: 1px solid var(--med-border);">
                                        <tr>
                                            <th class="py-2 px-3 text-muted-theme fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.1em; width: 200px;">Pharmaceutical Item</th>
                                            <th class="py-2 px-3 text-center text-muted-theme fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.1em;">Standard Batch</th>
                                            <th class="py-2 px-3 text-center text-muted-theme fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.1em;">Order Qty</th>
                                            <th class="py-2 px-3 text-center text-muted-theme fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.1em;">Bonus</th>
                                            <th class="py-2 px-3 text-end text-muted-theme fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.1em;">Price (₹)</th>
                                            <th class="py-2 px-3 text-end text-muted-theme fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.1em;">Total (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-0">
                `;

                (row.items || []).forEach(function (i) {
                    let name = i.product_name || i.name || '-';
                    let qty = i.quantity || i.qty || 0;
                    let unitPrice = parseFloat(i.unit_price || 0);
                    let totalAmt = parseFloat(i.total_amount || i.total || (i.unit_price ? (i.unit_price * qty) : 0));

                    let batchHtml = '<span class="text-muted small">N/A</span>';
                    if (i.batches && i.batches.length > 0) {
                        batchHtml = i.batches.map(b => `
                            <div class="mb-1 last-child-mb-0">
                                <span class="badge bg-soft-info text-info border-0 px-2 py-1" style="font-size: 0.85rem; font-weight: 800; letter-spacing: 0.5px;">${b.batch_no}</span>
                                <div class="text-muted d-block" style="font-size: 0.75rem; margin-top: 1px;">Exp: ${b.expiry_date}</div>
                            </div>
                        `).join('');
                    }

                    let cleanedName = window.cleanProductName(name, i.side, i.size);
                    let variantBadge = window.renderProductVariantBadge(i);

                        detailsHtml += `
                        <tr style="border-bottom: 1px solid var(--med-border-light, #f1f5f9);">
                            <td class="py-2 px-3" style="max-width: 200px;">
                                <div class="d-flex align-items-start">
                                    <div class="ms-0 w-100">
                                        <div class="text-main-theme fw-bold mb-0" style="font-size: 0.9rem; white-space: normal; line-height: 1.2;">
                                            ${cleanedName} ${variantBadge}
                                        </div>
                                        <div class="small text-muted-theme" style="font-size: 0.7rem;">
                                            (${i.brand || 'Generic'}) • <span class="fw-bold text-primary">${qty} ${i.unit || 'Nos'}</span>
                                            ${i.free_quantity > 0 ? `<span class="text-success fw-bold ms-1" style="font-size: 0.65rem;">(+${i.free_quantity} Free)</span>` : ''}
                                        </div>
                                        <div class="text-muted small mt-0 opacity-75 d-flex flex-wrap gap-2" style="font-size: 0.6rem;">
                                            ${i.generic_name ? `<span>${i.generic_name}</span>` : ''}
                                            ${i.product_code && i.product_code !== 'N/A' && i.product_code !== '---' ? `<span>Code: ${i.product_code}</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 px-3 text-center">${batchHtml}</td>
                            <td class="py-2 px-3 text-center">
                                <div class="fw-bold" style="font-size: 0.85rem;">${qty}</div>
                                <div class="text-muted small" style="font-size: 0.6rem;">${i.unit || 'Units'}</div>
                            </td>
                            <td class="py-2 px-3 text-center">
                                ${i.free_quantity > 0 ? `<div class="badge bg-soft-success text-success border-0 px-2 py-1" style="font-size: 0.7rem;">+${i.free_quantity}</div>` : '<span class="text-muted small">-</span>'}
                            </td>
                            <td class="py-2 px-3 text-end">
                                <div class="text-muted small" style="font-size: 0.75rem;">${unitPrice.toFixed(2)}</div>
                            </td>
                             <td class="py-2 px-3 text-end">
                                <div class="fw-bold text-primary" style="font-size: 0.85rem;">${totalAmt.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
 </td>
                        </tr>
                    `;
                });

                detailsHtml += `
                                    </tbody>
                                    <tfoot style="background: rgba(var(--med-primary-rgb), 0.01);">
                                        <tr>
                                            <td colspan="5" class="text-end py-3 px-3 text-uppercase fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 0.05em;">Grand Total:</td>
                                            <td class="py-3 px-3 text-end">
                                                <div class="fw-bold text-success fs-5" style="letter-spacing: -0.02em;">₹${parseFloat(row.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm bg-card-theme" style="border-radius: 16px !important;">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-uppercase text-muted-theme fw-bold mb-4" style="font-size: 0.72rem; letter-spacing: 0.08em;"><i class="fa fa-history me-2"></i>Order Lifecycle Progress</h6>
                            <div class="order-timeline">
                                ${(() => {
                                    const status = (row.status || '').toLowerCase();
                                    const steps = [
                                        { key: 'pending', label: 'Order Placed', desc: `Initial request at ${row.placed_at || 'N/A'}` },
                                        { key: 'processing', label: 'Processing', desc: 'Laboratory processing' },
                                        { key: 'approved', label: 'Approved', desc: 'Cleared for dispatch' },
                                        { key: 'delivered', label: 'Delivered', desc: `Fulfillment confirmed ${row.delivered_at ? 'at ' + row.delivered_at : ''}` }
                                    ];

                                    let activeIdx = 0;
                                    if (status === 'processing') activeIdx = 1;
                                    else if (status === 'approved') activeIdx = 2;
                                    else if (status === 'delivered') activeIdx = 3;

                                    let html = steps.map((step, idx) => `
                                        <div class="timeline-item ${idx <= activeIdx ? 'active' : ''}">
                                            <div class="timeline-marker"></div>
                                            <div class="timeline-content">
                                                <h6>${step.label}</h6>
                                                <span class="small">${idx === activeIdx ? `<span class="badge bg-soft-primary text-primary border-0 p-0 me-1">Current</span> ` : ''}${step.desc}</span>
                                            </div>
                                        </div>
                                    `).join('');

                                    if (status === 'cancelled' || status === 'rejected') {
                                        html += `
                                            <div class="timeline-item active">
                                                <div class="timeline-marker" style="background: #ef4444 !important; border-color: #ef4444 !important; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;"></div>
                                                <div class="timeline-content">
                                                    <h6 class="text-danger">${status.toUpperCase()}</h6>
                                                    <span>Reason: ${row.cancellation_reason || 'The order process was terminated.'}</span>
                                                </div>
                                            </div>
                                        `;
                                    }
                                    return html;
                                })()}
                            </div>
                        </div>
                    </div>
                `;

                $('#showOrderContent').html(detailsHtml);
                $('#showOrderModal').modal('show');
            });


            $(document).on('click', '.init-return-btn', function() {
                let data = $(this).data();

                if (data.isReturnable == 0) {
                    Swal.fire({
                        title: '<span style="color: #ef4444;">Non-Returnable Item</span>',
                        html: `
                            <div class="text-center p-3">
                                <div class="mb-4">
                                    <i class="fa fa-exclamation-triangle fa-3x text-warning"></i>
                                </div>
                                <h5 class="fw-bold mb-3">${data.productName}</h5>
                                <p class="text-muted">This product is not eligible for returns per company policy.</p>
                                <div class="mt-4 p-3 bg-light rounded-3 small text-start border-start border-4 border-danger">
                                    <strong>Note:</strong> Some items such as specialized medicines or promotional goods may be restricted from returns for safety or commercial reasons.
                                </div>
                            </div>
                        `,
                        showConfirmButton: true,
                        confirmButtonText: 'Understood',
                        confirmButtonColor: '#00497a',
                        customClass: {
                            popup: 'rounded-4 shadow-lg border-0',
                            confirmButton: 'btn btn-primary px-4 py-2 rounded-3 fw-bold'
                        },
                        showCloseButton: true
                    });
                    return;
                }

                $('#return_order_id').val(data.orderId);
                $('#return_product_id').val(data.productId);
                $('#return_product_name').text(data.productName);
                $('#return_qty_input').val(data.quantity).attr('max', data.quantity);
                $('#return_unit_text').text(data.unit);
                $('#image_preview_container').empty();
                $('#submitReturnModal').modal('show');
            });

            // Image preview logic
            $('#return_images_input').on('change', function() {
                const container = $('#image_preview_container');
                container.empty();
                
                if (this.files) {
                    Array.from(this.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            container.append(`
                                <div class="position-relative" style="width: 80px; height: 80px;">
                                    <img src="${e.target.result}" class="img-thumbnail w-100 h-100 object-fit-cover rounded-3">
                                </div>
                            `);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            });

            $('#submitReturnForm').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('order_type', 'retailer');

                let $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');

                $.ajax({
                    url: "{{ route('admin.returns.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#submitReturnModal').modal('hide');
                        Swal.fire('Success', res.success, 'success');
                        $('#submitReturnForm')[0].reset();
                        $('#image_preview_container').empty();
                    },
                    error: function(xhr) {
                        let err = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Submission failed.';
                        Swal.fire('Error', err, 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Submit Return');
                    }
                });
            });
        });
    </script>
@endpush

@push('styles')
<style>
    .btn-clear-dates {
        border-radius: 8px;
        padding: 0.375rem 0.75rem;
        font-size: 0.9rem;
        font-weight: 600;
        border: 1.5px solid #ff9e88 !important;
        color: #ff6f4c !important;
        background-color: #ffe5dd !important;
        transition: all 0.2s ease;
    }
    .btn-clear-dates:hover {
        background-color: #ff6f4c !important;
        color: #ffffff !important;
    }

    body.dark-only .btn-clear-dates {
        border-color: rgba(239, 68, 68, 0.4) !important;
        color: #f87171 !important;
        background-color: rgba(239, 68, 68, 0.1) !important;
    }
    body.dark-only .btn-clear-dates:hover {
        background-color: #ef4444 !important;
        color: #ffffff !important;
    }
</style>
@endpush
