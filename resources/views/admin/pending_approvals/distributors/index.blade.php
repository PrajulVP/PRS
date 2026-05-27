@extends('layouts.admin')

@section('page-body')
    <style>
        /* Compact the table */
        .dataTables_filter {
            text-align: right !important;
        }

        .dataTables_filter input {
            width: 100% !important;
            max-width: 210px !important;
            margin-left: 10px !important;
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

        .segmented-control input:checked+label {
            color: var(--med-text-main, #0f172a);
        }

        #pay_paid:checked+label {
            color: var(--med-paid-text, #15803d);
        }

        #pay_pending:checked+label {
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

        #pay_all:checked~.selection-indicator {
            transform: translateX(0);
            background: var(--med-bg-card, #ffffff);
        }

        #pay_paid:checked~.selection-indicator {
            transform: translateX(100%);
            background: var(--med-paid-bg, #dcfce7);
        }

        #pay_unpaid:checked~.selection-indicator {
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

        #distributor-approval-table tbody td {
            font-size: 0.85em;
        }

        #distributor-approval-table td:last-child {
            white-space: nowrap !important;
        }

        .product-col {
            min-width: 200px !important;
            max-width: 200px !important;
            width: 200px !important;
            white-space: normal !important;
            word-break: break-all !important;
            overflow-wrap: break-word !important;
            vertical-align: top !important;
            line-height: 1.3 !important;
        }

        .action-buttons {
            display: inline-flex !important;
            align-items: center;
            gap: 4px;
        }

        .action-buttons .btn {
            margin: 0 !important;
        }

        /* Responsive Fixes */
        .nav-tabs {
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding-bottom: 2px;
        }

        .nav-tabs::-webkit-scrollbar {
            display: none;
        }

        .nav-tabs .nav-item {
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

            .dataTables_filter,
            .dataTables_length {
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

        /* Invoice-style Item List */
        .invoice-list {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 10px;
        }

        body.dark-only .invoice-list {
            background: rgba(0, 0, 0, 0.2);
        }

        .invoice-list-header {
            display: flex;
            background: #cbd5e1;
            border-radius: 8px;
            padding: 10px 15px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.65rem;
            color: #475569;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        body.dark-only .invoice-list-header {
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
        }

        .invoice-list-row {
            display: flex;
            background: #fff;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            align-items: center;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        body.dark-only .invoice-list-row {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: none;
        }

        .invoice-list-row:hover {
            border-color: #3182ce;
            transform: translateY(-1px);
        }

        .invoice-list-footer {
            display: flex;
            justify-content: flex-end;
            padding: 10px 15px;
            font-weight: bold;
            color: #1e293b;
            background: #fff;
            border-radius: 8px;
            margin-top: 4px;
            border: 1px solid #e2e8f0;
        }

        /* Column configuration for AI Invoice processing */
        .ai-col-product {
            flex: 2.5;
            min-width: 0;
            max-width: 200px;
            white-space: normal;
            word-break: break-word;
        }

        .ai-col-batch {
            flex: 1.2;
            text-align: center;
        }

        .ai-col-expiry {
            flex: 1;
            text-align: center;
        }

        .ai-col-qty {
            flex: 1;
            text-align: center;
        }

        .ai-col-value {
            flex: 1.2;
            text-align: right;
        }

        /* Enhanced Dropzone */
        .premium-dropzone {
            border: 2px dashed #3182ce;
            border-radius: 12px;
            background: #f8fafc;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        body.dark-only .premium-dropzone {
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(56, 189, 248, 0.3);
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

        .premium-dropzone:hover {
            background: #eff6ff;
            border-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }

        .premium-dropzone i {
            font-size: 3rem;
            color: #3182ce;
            margin-bottom: 15px;
            display: block;
        }

        .premium-dropzone.has-file {
            border-style: solid;
            background: #f0fdf4;
            border-color: #22c55e;
        }

        .premium-dropzone.has-file i {
            color: #22c55e;
        }

        /* Order Summary Card - Refined Light Industrial Theme */
        .order-summary-header {
            background: var(--med-bg-card);
            color: var(--med-text-main);
            padding: 12px 24px 10px;
            border-radius: 20px 20px 0 0;
            position: relative;
            border-bottom: 1px solid var(--med-border-light);
            border-top: 4px solid var(--med-primary);
        }

        .order-summary-header .btn-close {
            z-index: 1060 !important;
        }

        .order-summary-header .text-sm-end {
            padding-right: 32px !important;
        }

        .bg-custom-yellow {
            background-color: #f59e0b !important;
            color: #ffffff !important;
        }

        .retailer-detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: var(--med-text-muted);
            margin-bottom: 4px;
        }

        .retailer-detail-item i {
            width: 16px;
            text-align: center;
            color: var(--med-primary);
            opacity: 0.7;
        }

        /* Payment Card */
        .payment-status-card {
            background: var(--med-bg-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--med-border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }

        .payment-status-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .status-badge-group {
            display: flex;
            gap: 10px;
        }

        .status-radio-option {
            position: relative;
            cursor: pointer;
        }

        .status-radio-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .status-radio-box {
            display: block;
            padding: 8px 16px;
            border-radius: 8px;
            background: var(--med-bg-body, #f1f5f9);
            color: var(--med-text-muted, #64748b);
            font-weight: 600;
            font-size: 0.85rem;
            border: 1px solid var(--med-border, transparent);
            transition: all 0.2s;
        }

        .status-radio-option input:checked+.status-radio-box {
            background: var(--med-paid-bg, #dcfce7);
            color: var(--med-paid-text, #15803d);
            border-color: var(--med-paid-border, #22c55e);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .status-radio-option input[value="pending"]:checked+.status-radio-box {
            background: var(--med-pending-bg, #fffbeb);
            color: var(--med-pending-text, #d97706);
            border-color: var(--med-pending-border, #fef3c7);
        }

        /* Custom Modal Width */
        .modal-content {
            border-radius: 20px !important;
            overflow: hidden !important;
        }
        /* Premium Error Alert Styles */
        .approval-error-alert {
            border-left: 5px solid #ef4444 !important;
            background: rgba(239, 68, 68, 0.05) !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08) !important;
        }
        body.dark-only .approval-error-alert {
            background: rgba(239, 68, 68, 0.15) !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
        }
        body.dark-only .approval-error-alert .alert-heading {
            color: #f87171 !important;
        }
        body.dark-only .approval-error-alert #retailer_approval_error_message,
        body.dark-only .approval-error-alert #distributor_approval_error_message {
            color: #fecaca !important;
        }

        #submitReturnModal {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
    </style>

    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-card-theme border-bottom pb-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-primary fw-bold">Distributor Order Approvals</h5>
                </div>

                <ul class="nav nav-tabs border-bottom-0" id="orderStatusTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-all" data-bs-toggle="tab" data-status=""
                            type="button" role="tab">All</button>
                    </li>
                    @php
                        $user = Auth::user();
                        $defaultStatus = 'all'; // Default
                        if ($user->hasAnyRole(['admin', 'superadmin'])) {
                            $defaultStatus = 'processing';
                        } elseif ($user->hasRole('salesmanager')) {
                            $defaultStatus = 'pending';
                        }
                    @endphp
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-pending" data-bs-toggle="tab"
                            data-status="pending" type="button" role="tab">Pending</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-processing" data-bs-toggle="tab"
                            data-status="processing" type="button" role="tab">Processing</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-approved" data-bs-toggle="tab"
                            data-status="approved" type="button" role="tab">Approved</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-delivered" data-bs-toggle="tab"
                            data-status="delivered" type="button" role="tab">Delivered</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-cancelled" data-bs-toggle="tab"
                            data-status="cancelled" type="button" role="tab">Cancelled</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-rejected" data-bs-toggle="tab"
                            data-status="rejected" type="button" role="tab">Rejected</button>
                    </li>
                </ul>
            </div>
            <div class="card-body pt-4">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div> @endif

                <div id="filter_container" class="d-none">
                    <div class="d-flex align-items-center mb-0 ms-2">
                        <span class="text-muted fw-bold me-3 small text-uppercase">Payment:</span>
                        <div class="segmented-control" id="payment_status_filter_group">
                            <input type="radio" name="payment_status" id="pay_all" value="" checked>
                            <label for="pay_all">All</label>

                            <input type="radio" name="payment_status" id="pay_paid" value="paid">
                            <label for="pay_paid">Paid</label>

                            <input type="radio" name="payment_status" id="pay_unpaid" value="pending">
                            <label for="pay_unpaid">Pending</label>

                            <div class="selection-indicator"></div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="distributor-approval-table">
                        <thead>
                            <tr>
                                <th style="display:none;">ID</th>
                                <th>No.</th>
                                <th>Order Code</th>
                                <th>Distributor</th>
                                <th style="width: 200px;">products</th>
                                <th style="width: 120px;">Brand</th>
                                <th>Total</th>
                                <th>Placed At</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Invoice</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 overflow-hidden">
                <div class="modal-body p-0">
                    <!-- Order Summary Header -->
                    <div class="order-summary-header py-3 px-4">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                        <div class="row align-items-center">
                            <div class="col-sm-7">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-primary-subtle text-primary me-2 fw-bold"
                                        style="font-size: 0.6rem; letter-spacing: 0.5px;">DETAILS</span>
                                    <h4 class="mb-0 fw-bold text-main-theme" id="view_order_code" style="font-size: 1.25rem;">--</h4>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="retailer-detail-item mb-0">
                                        <i class="fa fa-calendar" style="font-size: 0.75rem;"></i>
                                        <span id="view_placed_at" class="text-muted-theme" style="font-size: 0.75rem;">--</span>
                                    </div>
                                    <div class="retailer-detail-item mb-0">
                                        <i class="fa fa-hashtag" style="font-size: 0.75rem;"></i>
                                        <span id="view_invoice_no" class="text-muted-theme" style="font-size: 0.75rem;">--</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-5 text-sm-end">
                                <div class="text-muted-theme fw-bold mb-1"
                                    style="font-size: 0.6rem; letter-spacing: 0.5px; text-transform: uppercase;">Distributor</div>
                                <h5 class="fw-bold text-main-theme mb-1" id="view_distributor" style="font-size: 1rem;">--</h5>
                                <div class="d-flex justify-content-sm-end gap-2 align-items-center">
                                    <div class="badge" id="view_status_badge"
                                        style="font-size: 0.65rem; padding: 0.3em 0.6em; letter-spacing: 0.5px;">--</div>
                                    <div class="badge" id="view_payment_status_badge"
                                        style="font-size: 0.65rem; padding: 0.3em 0.6em; letter-spacing: 0.5px; border: 1px solid currentColor; background: transparent;">
                                        --</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-body-theme">
                        <h6 class="fw-bold mb-3 d-flex align-items-center text-main-theme">
                            <span class="bg-body-theme p-2 rounded me-2"><i class="fa fa-list-ul text-primary"></i></span>
                            Order Items
                        </h6>
                        <div class="card border-0 shadow-sm overflow-hidden mb-4 bg-card-theme">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-body-theme">
                                        <tr>
                                            <th class="ps-3 text-muted-theme" style="font-size: 0.7rem; font-weight: bold; text-transform: uppercase;">Product Details</th>
                                            <th class="text-muted-theme" style="font-size: 0.7rem; font-weight: bold; text-transform: uppercase;">Batch Details</th>
                                            <th class="text-center text-muted-theme" style="font-size: 0.7rem; font-weight: bold; text-transform: uppercase;">Quantity</th>
                                            <th class="text-end pe-3 text-muted-theme" style="font-size: 0.7rem; font-weight: bold; text-transform: uppercase;">Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view_items_list">
                                        <!-- Items will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 bg-light d-flex justify-content-between align-items-center border-top">
                                <div class="fw-bold text-muted-theme text-uppercase" style="font-size: 0.75rem;">Grand Total:</div>
                                <div class="text-primary fs-5 fw-bold" id="view_total">₹0.00</div>
                            </div>
                        </div>

                        <div id="view_notes_container"
                            class="mt-3 p-3 bg-body-theme rounded-3 d-none border border-light-subtle">
                            <div class="text-muted-theme small text-uppercase fw-bold mb-1">Notes</div>
                            <p id="view_notes" class="text-main-theme mb-0 small"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-card-theme border-top-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
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

    {{-- Approve Order Modal for Sales Managers --}}
    <div class="modal fade" id="approveOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 overflow-hidden">
                <form id="approveOrderForm">
                    <div class="modal-body p-0">
                        <!-- Order Summary Header -->
                        <div class="order-summary-header">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                            <div class="row align-items-center">
                                <div class="col-sm-7">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary-subtle text-primary me-2 fw-bold">ORDER</span>
                                        <h4 class="mb-0 fw-bold text-main-theme" id="approve_order_code_display">--</h4>
                                    </div>
                                    <div class="retailer-detail-item">
                                        <i class="fa fa-calendar"></i>
                                        <span id="approve_order_date_display">--</span>
                                    </div>
                                </div>
                                <div class="col-sm-5 text-sm-end">
                                    <div class="text-muted-theme small text-uppercase fw-bold mb-1"
                                        style="font-size: 0.65rem; letter-spacing: 0.5px;">Distributor Information
                                    </div>
                                    <h5 class="fw-bold text-main-theme mb-1" id="approve_distributor_display">--</h5>
                                    <div class="d-flex justify-content-sm-end gap-2 align-items-center mb-1">
                                        <div class="badge" id="approve_status_badge"
                                            style="font-size: 0.7rem; padding: 0.4em 0.8em; letter-spacing: 0.5px;">--</div>
                                        <div class="badge" id="approve_payment_status_badge"
                                            style="font-size: 0.7rem; padding: 0.4em 0.8em; letter-spacing: 0.5px; border: 1px solid currentColor; background: transparent;">
                                            --</div>
                                    </div>
                                    <div class="retailer-detail-item justify-content-sm-end">
                                        <i class="fa fa-map-marker-alt"></i>
                                        <span id="approve_location_display">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-body-theme">
                            <input type="hidden" id="approve_order_id" name="order_id">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 d-flex align-items-center text-main-theme">
                                    <span class="bg-primary-subtle p-2 rounded me-2"><i
                                            class="fa fa-clipboard-check text-primary"></i></span>
                                    Reviewing Order Items
                                </h6>
                            </div>

                            <div class="invoice-list mb-3">
                                <div class="invoice-list-header bg-dark text-white border-0 py-2">
                                    <div style="flex: 2;" class="ps-3 text-white">Product Name</div>
                                    <div style="flex: 1;" class="text-center text-white">Quantity</div>
                                    <div style="flex: 1;" class="text-end pe-3 text-white">Value (PTS)</div>
                                </div>
                                <div id="approve_items_list">
                                    <!-- Items will be populated here -->
                                </div>
                                <div class="invoice-list-footer bg-body-theme border-0">
                                    <div class="me-3 text-muted-theme">Total Estimated Value:</div>
                                    <div class="text-primary fs-5" id="approve_total_display">₹0</div>
                                </div>
                            </div>

                            <div class="alert alert-info py-2 mb-0 d-flex align-items-center">
                                <i class="fa fa-info-circle me-2 fs-5"></i>
                                <span>Approving this order moves it to the Admin for final processing and invoice
                                    generation.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-card-theme border-top-0 px-4 py-3">
                        <button type="button" class="btn btn-link text-muted-theme fw-bold text-decoration-none"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow">
                            <i class="fa fa-check-circle me-1"></i> Proceed to Admin Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Order Modal --}}
    <div class="modal fade" id="rejectOrderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
                <div class="modal-header border-0 py-3 px-4 position-relative" style="background-color: #7f1d1d;">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fa fa-times-circle fs-4 text-white"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" style="color: #ffffff !important;">Reject Order
                            </h5>
                            <p class="small text-white text-opacity-85 mb-0" id="reject_order_code_display"
                                style="color: rgba(255,255,255,0.85) !important;"></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="rejectOrderForm">
                    <div class="modal-body p-4">
                        <input type="hidden" id="reject_order_id" name="order_id">

                        <div
                            class="p-3 rounded-3 mb-4 d-flex align-items-start bg-danger-subtle border border-danger border-opacity-25">
                            <i class="fa fa-exclamation-triangle text-danger mt-1 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1 text-danger-emphasis">Confirm Rejection</h6>
                                <p class="text-body-secondary small mb-0">This order will be marked as rejected and the
                                    distributor will be notified. This action cannot be undone.</p>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold text-main-theme small text-uppercase">Reason for Rejection
                                <span class="text-danger">*</span></label>
                            <textarea class="form-control border-0 bg-body-theme shadow-none text-main-theme" name="reason"
                                rows="4" required
                                placeholder="E.g., Stock unavailable, credit limit exceeded, invalid order details..."
                                style="border-radius: 12px; resize: none;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none px-4"
                            data-bs-dismiss="modal">Go Back</button>
                        <button type="submit" class="btn px-4 py-2 fw-bold shadow-sm"
                            style="border-radius: 10px; background-color: #b91c1c; color: #fff;">
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Process Order Modal for Admins --}}
    <div class="modal fade" id="processOrderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 overflow-hidden">
                <form id="processOrderForm">
                    <div class="modal-body p-0">
                        <!-- Order Summary Header -->
                        <div class="order-summary-header">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>
                            <div class="row align-items-center">
                                <div class="col-sm-7">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary-subtle text-primary me-2 fw-bold">DISTRIBUTION</span>
                                        <h4 class="mb-0 fw-bold text-main-theme" id="process_order_code_display">--</h4>
                                    </div>
                                    <div class="retailer-detail-item">
                                        <i class="fa fa-calendar"></i>
                                        <span id="process_order_date_display">--</span>
                                    </div>
                                </div>
                                <div class="col-sm-5 text-sm-end">
                                    <div class="text-muted-theme small text-uppercase fw-bold mb-1"
                                        style="font-size: 0.65rem; letter-spacing: 0.5px;">Distributor Information
                                    </div>
                                    <h5 class="fw-bold text-main-theme mb-1" id="process_distributor_display">--</h5>
                                    {{-- Status badges removed as requested --}}
                                    <div class="retailer-detail-item justify-content-sm-end">
                                        <i class="fa fa-map-marker-alt"></i>
                                        <span id="process_location_display">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-body-theme">
                            <input type="hidden" id="process_order_id" name="order_id">

                            <!-- Action Error Alert -->
                            <div id="distributor_approval_error_alert" class="alert alert-danger d-none mb-4 shadow-sm border-0 animate__animated animate__shakeX approval-error-alert">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-1 fw-bold text-danger">Action Required</h6>
                                        <div id="distributor_approval_error_message" class="small fw-bold text-dark"></div>
                                    </div>
                                    <button type="button" class="btn-close ms-auto" onclick="$(this).closest('.alert').addClass('d-none')"></button>
                                </div>
                            </div>

                            <!-- Settlement & Payment Status Information (Toggle) -->
                            <div class="payment-status-info-card mb-4 p-3 rounded-3 bg-light border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-main-theme d-flex align-items-center">
                                            <i class="fa fa-money-check-alt text-primary me-2"></i>
                                            Current Payment State
                                        </h6>
                                        <div class="text-muted-theme small">Update the payment status for this order if
                                            required.</div>
                                    </div>
                                    <div class="status-badge-group">
                                        <label class="status-radio-option">
                                            <input type="radio" name="payment_status" value="paid" id="modal_pay_paid">
                                            <span class="status-radio-box">Mark as Paid</span>
                                        </label>
                                        <label class="status-radio-option">
                                            <input type="radio" name="payment_status" value="pending"
                                                id="modal_pay_pending">
                                            <span class="status-radio-box">Still Pending</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Number (Hidden, populated by AI) -->
                            <input type="hidden" name="invoice_no" id="invoice_no_input" required>
                            <input type="hidden" name="final_amount" id="final_amount_input">
                            <input type="hidden" name="taxable_amount" id="taxable_amount_input">

                            <!-- Smart Invoice Processing Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 d-flex align-items-center text-main-theme">
                                        <span class="bg-primary-subtle p-2 rounded me-2"><i
                                                class="fa fa-robot text-primary"></i></span>
                                        Smart Invoice Processing
                                    </h6>
                                    <span class="badge bg-soft-info text-info rounded-pill px-3">AI POWERED</span>
                                </div>

                                <div id="automation_idle_state">
                                    <div class="premium-dropzone" id="ocr_dropzone">
                                        <i class="fa fa-file-invoice-dollar"></i>
                                        <h5 class="fw-bold mb-1">Upload Invoice to Process</h5>
                                        <p class="text-muted small mb-0">Drag & drop or click to scan (AI will extract
                                            batches & pricing)</p>
                                    </div>
                                    <input type="file" class="d-none" id="scan_file_input" accept=".pdf,.jpg,.jpeg,.png">
                                </div>

                                <div id="ocr_processing_state" class="d-none text-center py-5">
                                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"
                                        role="status"></div>
                                    <h5 class="fw-bold text-primary">Reading Invoice via AI...</h5>
                                    <p class="text-muted small">Extracting line items, taxes, and batches. Please wait.</p>
                                </div>

                                <div id="automation_success_state" class="d-none">
                                    <div class="premium-metadata-card p-3 rounded-4 border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                                        <div class="row g-4 mb-3">
                                            <div class="col-md-3 border-end border-2 border-white">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Invoice Date</div>
                                                <div class="fw-bold text-dark fs-6" id="extract_date" contenteditable="true" title="Click to edit">--</div>
                                            </div>
                                            <div class="col-md-3 border-end border-2 border-white">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Invoice Number</div>
                                                <div class="fw-bold text-primary fs-6" id="extract_invoice_no" contenteditable="true" title="Click to edit">--</div>
                                            </div>
                                            <div class="col-md-3 border-end border-2 border-white">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">GSTIN Extracted</div>
                                                <div class="fw-bold text-dark fs-6" id="extract_gstin" contenteditable="true" title="Click to edit">--</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Drug License</div>
                                                <div class="fw-bold text-dark fs-6" id="extract_dl" contenteditable="true" title="Click to edit">--</div>
                                            </div>
                                        </div>
                                        <hr class="my-3 border-white border-2">
                                        <div class="row g-4">
                                            <div class="col-md-6 border-end border-2 border-white">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Total Amount (Taxable)</div>
                                                <div class="fw-bold text-secondary fs-5" id="extract_total_amount">₹0.00</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Net Amount (Payable)</div>
                                                <div class="fw-bold text-success fs-5" id="extract_net_amount">₹0.00</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- AI Mismatch / Duplicate Warnings -->
                                    <div id="ai_validation_alert" class="alert alert-warning py-2 mb-3 d-none">
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-exclamation-triangle me-2"></i>
                                            <div id="ai_validation_message" class="small fw-bold"></div>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-uppercase small text-muted mb-3"><i
                                            class="fa fa-search me-1"></i> Verify Extracted Details</h6>
                                    <div class="invoice-list mb-3 px-0">
                                        <div class="invoice-list-header bg-dark text-white border-0 py-2">
                                            <div class="ai-col-product ps-3 text-white">Product</div>
                                            <div class="ai-col-batch text-white">Batch</div>
                                            <div class="ai-col-expiry text-white">Expiry</div>
                                            <div class="ai-col-qty text-white text-center">Qty</div>
                                            <div class="ai-col-value text-white text-end pe-3">Net Amt</div>
                                        </div>
                                        <div id="verification_table_body">
                                            <!-- AI data rows will be injected here -->
                                        </div>
                                        <div id="verification_table_footer" class="invoice-list-footer bg-light d-none"
                                            style="font-size: 0.7rem;">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="$('#scan_file_input').val('').click()">
                                                    <i class="fa fa-sync me-1"></i> Re-scan / Different File
                                                </button>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="text-muted">Extracted Totals:</div>
                                                    <div>Txl: <span class="fw-bold" id="tfoot_taxable">0</span></div>
                                                    <div>GST: <span class="fw-bold" id="tfoot_gst_total">0</span></div>
                                                    <div class="ms-2 border-start ps-3 py-1">
                                                        <div class="small text-muted mb-0" id="tfoot_net_label">Doc Total</div>
                                                        <div class="fs-6 fw-bold text-primary" id="tfoot_net">₹0.00</div>
                                                        <div id="tfoot_matched_net" class="text-muted small" style="font-size: 0.65rem; display: none;">Matched Total: ₹0.00</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Automation Error State -->
                                <div id="automation_error_state" class="d-none">
                                    <div
                                        class="text-center py-5 bg-soft-danger rounded-3 border-start border-4 border-danger">
                                        <i class="fa fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                        <h5 class="fw-bold text-danger">Mismatched Invoice!</h5>
                                        <p class="text-muted small">AI could not find matching products in this document.
                                        </p>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="$('#scan_file_input').val('').click()">Try Another File</button>
                                    </div>
                                </div>
                            </div>


                            <!-- Hidden inputs for Form Submission -->
                            <div id="hidden_batch_inputs" class="d-none">
                                <div id="batch_entry_body"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none px-4"
                            data-bs-dismiss="modal">Go Back</button>
                        <button type="submit" id="btn_approve_order" class="btn btn-primary px-5 py-2 fw-bold shadow-sm"
                            disabled
                            style="border-radius: 12px; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                            Confirm & Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Hidden file input for AI Scanning --}}

    {{-- Hidden file input for invoice --}}
    <input type="file" id="invoice_upload_input" class="d-none" accept=".pdf,.jpg,.jpeg,.png">

    {{-- Remove Invoice Confirmation Modal --}}
    <div class="modal fade" id="removeInvoiceConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger py-3">
                    <h5 class="modal-title fw-bold text-white">Confirm Invoice Removal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to remove this invoice? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveInvoiceBtn">Remove Invoice</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Change Payment Status Modal --}}
    <div class="modal fade" id="paymentStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white">Payment Status</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="paymentStatusForm">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="payment_order_id">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="payment_status" id="payment_status_select">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        .action-buttons .btn {
            padding: 4px 8px;
            font-size: 0.7rem;
        }

        .alert-soft-warning {
            background-color: #fff9db;
            color: #856404;
            border-left: 4px solid #ffec99;
        }

        .bg-soft-primary {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .ocr-dropzone:hover {
            border-color: #0d6efd !important;
            background-color: #f8f9fa !important;
        }

        .batch-item {
            transition: 0.3s;
        }

        .batch-item:hover {
            background-color: #f1f3f5;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .sticky-top {
            z-index: 1020;
        }

        /* Customize scrollbar */
        .table-responsive::-webkit-scrollbar {
            width: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        #batch_entry_body tr {
            border-bottom: 1px solid #eee;
        }
    </style>

    {{-- Payment Status Update Modal --}}
    <div class="modal fade" id="paymentStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary">Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentStatusForm">
                    <div class="modal-body py-4">
                        <input type="hidden" id="payment_order_id" name="order_id">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Select New Status</label>
                            <select class="form-select border-0 bg-light" id="payment_status_select" name="payment_status">
                                <option value="pending">Pending / Unpaid</option>
                                <option value="paid">Mark as Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-link text-muted text-decoration-none"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Set PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        const isAdmin = @json(Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin'));
        const isSalesManager = @json(Auth::user()->hasRole('salesmanager'));

        $(document).ready(function () {
            // Fix for modal z-index issues (move to body)
            $('#viewOrderModal').appendTo("body");
            $('#approveOrderModal').appendTo("body");
            $('#processOrderModal').appendTo("body");
            $('#removeInvoiceConfirmModal').appendTo("body");

            const INITIAL_STATUS = "{{ $defaultStatus }}";
            window.currentStatus = INITIAL_STATUS; // Will be refined in initComplete or use manual fallback if needed
            window.initialTabSelected = false;

            let exportOptions = {
                columns: ':not(.no-export)',
                format: {
                    body: function(data, row, column, node) {
                        let tableApi = $('#distributor-approval-table').DataTable();
                        let colIdx = column;
                        let rowData = tableApi.row(row).data();

                        if (colIdx === 4 && rowData && rowData.product_summary) {
                            return rowData.product_summary.split('|||').map(it => it.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim()).join('\n');
                        }
                        if (colIdx === 5 && rowData && rowData.brand_summary) {
                            return rowData.brand_summary.split('|||').join('\n');
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

            var table = $('#distributor-approval-table').DataTable({
                order: [
                    [0, 'desc']
                ],
                autoWidth: false,
                columnDefs: [
                    {
                        targets: 4,
                        width: "300px",
                        createdCell: function (td, cellData, rowData, row, col) {
                            $(td).css({
                                'min-width': '200px',
                                'max-width': '350px',
                                'white-space': 'normal',
                                'word-break': 'break-word',
                                'line-height': '1.3'
                            });
                        }
                    },
                    {
                        targets: '_all',
                        className: 'align-middle'
                    }
                ],
                drawCallback: function (settings) {
                    var api = this.api();
                    api.column(1, {
                        search: 'applied',
                        order: 'applied'
                    }).nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1 + settings._iDisplayStart;
                    });

                    // Initialize Popovers
                    $('[data-bs-toggle="popover"]').popover();
                },
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-4 gy-3 d-flex align-items-center'<'col-12 col-lg-4'l><'col-12 col-lg-4 d-flex justify-content-lg-center payment-filter-container'><'col-12 col-lg-4 d-flex justify-content-lg-end'f>>" +
                    "<'row '<'col-sm-12'tr>>" +
                    "<'row mt-3 '<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end align-items-center'p>>",
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
                initComplete: function () {
                    // Move custom filters to the DataTables filter area
                    var $filter = $('#filter_container').children().first();
                    $('.payment-filter-container').append($filter);
                    $('#filter_container').remove();

                    // Definitive Tab Activation using Bootstrap API
                    // This ensures the correct tab is marked active and internal state is synced
                    const $targetTab = $('#tab-' + INITIAL_STATUS);
                    if ($targetTab.length) {
                        window.currentStatus = $targetTab.attr('data-status') || '';
                        const tabTrigger = bootstrap.Tab.getOrCreateInstance($targetTab[0]);
                        tabTrigger.show();
                    }

                    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                        $('#orderStatusTabs .nav-link').removeClass('active text-primary border-bottom-0').addClass('text-muted');
                        $(this).removeClass('text-muted').addClass('active text-primary border-bottom-0');
                        window.currentStatus = $(this).attr('data-status');
                        table.ajax.reload();
                    });

                    $('input[name="payment_status"]').on('change', function () {
                        table.ajax.reload();
                    });
                },
                ajax: {
                    url: window.location.href,
                    data: function (d) {
                        d.status = window.currentStatus;
                        d.payment_status = $('input[name="payment_status"]:checked').val() || '';
                    },
                    dataSrc: function (json) {
                        // Logic to switch active tab if current is empty and others have data
                        if (json.counts && json.data.length === 0 && !window.initialTabSelected) {
                            if (json.counts.processing > 0) {
                                $('#tab-processing').tab('show');
                            } else if (json.counts.pending > 0) {
                                $('#tab-pending').tab('show');
                            } else if (json.counts.all > 0) {
                                $('#tab-all').tab('show');
                            }
                            window.initialTabSelected = true;
                        }
                        return json.data;
                    }
                },
                columns: [
                    {
                        data: 'id',
                        visible: false,
                        searchable: false
                    },
                    {
                        data: null,
                        defaultContent: '',
                        orderable: false,
                        searchable: false
                    },
                    { data: 'order_code', render: (d, t, r) => d },
                    {
                        data: 'distributor_name',
                        render: function (data, type, row) {
                            if (type !== 'display') return data;
                            return `<span class="fw-bold text-primary">${data}</span>`;
                        }
                    },
                    {
                        data: 'product_summary',
                        className: 'product-col',
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
                        render: function (data, type, row) {
                            if (type !== 'display') return data;
                            let status = (row.status || '').toLowerCase();
                            let isEstimated = status.includes('pending') || status.includes('processing');
                            if (isEstimated) {
                                return `<div class="d-flex flex-column">
                                    <span class="fw-bold text-secondary">₹${data}</span>
                                    <span class="text-muted" style="font-size: 0.65rem; font-weight: normal; margin-top: 1px;">(Est. Total)</span>
                                </div>`;
                            } else if (status === 'cancelled' || status === 'rejected') {
                                let estAmt = (row.metadata && row.metadata.estimated_amount !== undefined) ? parseFloat(row.metadata.estimated_amount).toFixed(2) : data;
                                return `<div class="d-flex flex-column">
                                    <span class="fw-bold text-muted" style="text-decoration: line-through;">₹${data}</span>
                                    <span class="text-muted small" style="font-size: 0.65rem; font-weight: 500; opacity: 0.85; margin-top: 2px;">Est: ₹${estAmt}</span>
                                </div>`;
                            } else {
                                let estAmt = (row.metadata && row.metadata.estimated_amount !== undefined) ? parseFloat(row.metadata.estimated_amount).toFixed(2) : data;
                                return `<div class="d-flex flex-column">
                                    <span class="fw-bold text-success">₹${data} <small class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-0 ms-1" style="font-size: 0.55rem; font-weight: bold; vertical-align: middle;">INVOICED</small></span>
                                    <span class="text-muted small" style="font-size: 0.65rem; font-weight: 500; opacity: 0.85; margin-top: 2px;">Est: ₹${estAmt}</span>
                                </div>`;
                            }
                        }
                    },
                    { data: 'placed_at' },
                    {
                        data: 'status',
                        render: function (d, type, row) {
                            if (type !== 'display') return row.status;
                            let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';
                            let bgClass = 'bg-secondary';
                            let displayStatus = row.status;

                            if (statusRaw.includes('pending')) bgClass = 'bg-secondary text-white';
                            else if (statusRaw === 'processing') {
                                bgClass = 'bg-warning text-white';
                                displayStatus = 'Processing';
                            } else if (statusRaw === 'approved') {
                                bgClass = 'bg-info text-white';
                                displayStatus = 'Approved';
                            } else if (statusRaw.includes('delivered')) bgClass = 'bg-success text-white';
                            else if (statusRaw.includes('cancelled')) bgClass = 'bg-danger text-white';
                            else if (statusRaw.includes('rejected')) bgClass = 'bg-dark-red text-white';

                            return `<span class="badge ${bgClass}" style="font-size: 0.8rem; padding: 0.5em 0.9em; font-weight: 600;">${displayStatus}</span>`;
                        }
                    },
                    {
                        data: 'payment_status',
                        render: function (d, type, row) {
                            if (type !== 'display') return row.payment_status;
                            let payStatus = row.payment_status ? row.payment_status.toLowerCase() : 'pending';
                            let bgClass = 'bg-secondary';
                            let displayLabel = payStatus.charAt(0).toUpperCase() + payStatus.slice(1);

                            if (payStatus === 'paid') bgClass = 'bg-success text-white';
                            else {
                                bgClass = 'bg-secondary text-white';
                                displayLabel = 'Pending';
                            }

                            let cursorStyle = isAdmin ? 'cursor: pointer;' : '';
                            let clickableClass = isAdmin ? 'change-payment-status' : '';

                            return `<span class="badge ${bgClass} ${clickableClass}" data-id="${row.id}" data-status="${payStatus}" style="font-size: 0.8rem; padding: 0.5em 0.9em; font-weight: 600; ${cursorStyle}">${displayLabel}</span>`;
                        }
                    },
                    {
                        data: null,
                        visible: isAdmin, // Only visible to Admins
                        className: 'no-export',
                        orderable: false,
                        render: function (data, type, row) {
                            // Logic for Admins ONLY (since visible: isAdmin)
                            if (row.invoice_url) {
                                let ext = row.invoice_url.split('.').pop().toLowerCase();
                                let icon = ext === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-image-o';
                                let html = `<div class="d-flex align-items-center gap-2">`;
                                html += `<a href="${row.invoice_url}" target="_blank" class="btn btn-sm btn-info text-white" title="View Invoice"><i class="fa ${icon}"></i></a>`;
                                html += `</div>`;
                                return html;
                            }

                            let statusCheck = row.raw_status || (row.status ? row.status.toLowerCase().replace(/ /g, '_') : '');

                            if (statusCheck === 'delivered' || statusCheck.includes('delivered') || statusCheck.includes('approved') || statusCheck.includes('approved')) {
                                return `<span class="text-muted small">No Invoice</span>`;
                            }
                            return `<span class="text-muted small">Pending Approval</span>`;
                        }
                    },
                    {
                        data: null,
                        className: 'no-export',
                        orderable: false,
                        render: function (data, type, row) {
                            let rowData = JSON.stringify(row).replace(/"/g, '&quot;');
                            let invoiceUrl = "{{ route('admin.distributor-orders.invoice', ':id') }}".replace(':id', row.id);
                            let btns = `<div class="action-buttons">`;

                            // View Details
                            btns += `<button class="btn btn-info btn-sm view-details-btn" data-row="${rowData}" title="View Details"><i class="fa fa-eye"></i></button>`;
                            // System Invoice
                            btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="System Invoice"><i class="fa fa-print"></i></a>`;

                            // Sales Manager / Admin Actions
                            if ((isSalesManager || isAdmin) && row.status.toLowerCase().includes('pending')) {
                                btns += `<button class="btn btn-success btn-sm approve-order-btn" data-id="${row.id}" data-row="${rowData}" title="Approve"><i class="fa fa-check"></i></button>`;
                                btns += `<button class="btn btn-danger btn-sm reject-order-btn" data-id="${row.id}" title="Reject"><i class="fa fa-times"></i></button>`;
                            }

                            // Admin Actions
                            if (isAdmin && (row.raw_status === 'processing' || (row.status && row.status.toLowerCase().includes('processing')))) {
                                btns += `<button class="btn btn-success btn-sm accept-admin-btn" data-id="${row.id}" title="Process Order"><i class="fa fa-check"></i></button>`;
                                btns += `<button class="btn btn-danger btn-sm reject-order-btn" data-id="${row.id}" title="Reject"><i class="fa fa-times"></i></button>`;
                            }

                            btns += `</div>`;
                            return btns;
                        }
                    }
                ]
            });

            $('#status_filter').change(function () { table.ajax.reload(); });
            $('#payment_status_filter').change(function () { table.ajax.reload(); });

            // Status Update Logic


            // --- Event Handlers ---

            // Reject Form Submit
            $(document).on('click', '.reject-order-btn', function () {
                $('#reject_order_id').val($(this).data('id'));
                $('#rejectOrderModal').modal('show');
            });

            $('#rejectOrderForm').submit(function (e) {
                e.preventDefault();
                let $form = $(this);
                let reason = $form.find('textarea[name="reason"]').val().trim();

                if (!reason) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Reason Required',
                        text: 'Please provide a valid reason for rejecting this order.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                let id = $('#reject_order_id').val();
                let url = "{{ route('admin.distributor-orders.reject', ':id') }}".replace(':id', id);
                let $btn = $form.find('button[type="submit"]');

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rejecting...');

                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#rejectOrderModal').modal('hide');
                        if (res.success || res.message) {
                            table.ajax.reload(null, false);
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                            Swal.fire({
                                icon: 'success',
                                title: 'Order Rejected',
                                text: res.success || res.message || 'The order has been successfully rejected.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $form[0].reset();
                        } else {
                            Swal.fire('Error', res.error || 'Rejection failed.', 'error');
                        }
                    },
                    error: function (xhr) {
                        let err = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Rejection failed.';
                        Swal.fire('Error', err, 'error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Confirm Rejection');
                    }
                });
            });

            // Payment Status & Other handlers (Keep existing logic simplified/re-added as needed)


            // Logic for regular upload invoice button (if used outside modal)
            let currentOrderIdForInvoice = null;
            $(document).on('click', '.upload-invoice-btn', function () {
                currentOrderIdForInvoice = $(this).data('id');
                $('#invoice_upload_input').click();
            });

            $('#invoice_upload_input').change(function () {
                if (!this.files.length) return;
                let file = this.files[0];
                let formData = new FormData();
                formData.append('invoice', file);
                formData.append('_token', '{{ csrf_token() }}');
                $.ajax({
                    url: "{{ route('admin.distributor-orders.upload-invoice', ':id') }}".replace(':id', currentOrderIdForInvoice),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        // showToast('success', res.success);
                        table.ajax.reload(null, false);
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                    },
                    error: function (xhr) { showToast('error', 'Upload Failed'); }
                });
                $(this).val('');
            });

            // Remove Invoice logic
            let removeInvoiceId = null;
            $(document).on('click', '.remove-invoice-btn', function () {
                removeInvoiceId = $(this).data('id');
                $('#removeInvoiceConfirmModal').modal('show');
            });
            $('#confirmRemoveInvoiceBtn').click(function () {
                if (!removeInvoiceId) return;
                let url = "{{ route('admin.distributor-orders.remove-invoice', ':id') }}".replace(':id', removeInvoiceId);
                $.post(url, { _token: '{{ csrf_token() }}' }, function (res) {
                    $('#removeInvoiceConfirmModal').modal('hide');
                    if (res.success) {
                        // showToast('success', res.success);
                        table.ajax.reload(null, false);
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                    }
                    else { showToast('error', 'Failed'); }
                });
            });

            // Update Payment Status (Clicking on Badge)
            $(document).on('click', '.change-payment-status', function () {
                let id = $(this).data('id');
                let status = $(this).data('status') || 'pending';
                $('#payment_order_id').val(id);
                $('#payment_status_select').val(status.toLowerCase());
                $('#paymentStatusModal').modal('show');
            });

            $('#paymentStatusForm').submit(function (e) {
                e.preventDefault();
                let id = $('#payment_order_id').val();
                let $btn = $(this).find('button[type="submit"]');
                let oldText = $btn.text();
                $btn.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: "{{ route('admin.distributor-orders.update-payment-status', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        payment_status: $('#payment_status_select').val()
                    },
                    success: function (res) {
                        $('#paymentStatusModal').modal('hide');
                        // showToast('success', res.success || 'Updated');
                        table.ajax.reload(null, false);
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Update failed');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text(oldText);
                    }
                });
            });

            // View Details
            $(document).on('click', '.view-details-btn', function () {
                let row = $(this).data('row');
                $('#view_order_code').text(row.order_code || '--');
                $('#view_placed_at').text(row.placed_at || '--');
                $('#view_invoice_no').text(row.invoice_no || '--');
                $('#view_distributor').text(row.distributor_name || '--');
                $('#view_total').text('₹' + (row.total_amount || '0'));

                // Status Badge logic
                let status = (row.status || 'pending').toLowerCase();
                let badgeClass = 'bg-secondary text-white';
                if (status === 'pending') badgeClass = 'bg-secondary text-white';
                else if (status === 'processing') badgeClass = 'bg-warning text-white';
                else if (status === 'approved') badgeClass = 'bg-info text-white';
                else if (status === 'delivered') badgeClass = 'bg-success text-white';
                else if (status === 'cancelled') badgeClass = 'bg-danger text-white';
                else if (status === 'rejected') badgeClass = 'bg-dark-red text-white';

                $('#view_status_badge').text(row.status || 'Pending').removeClass().addClass('badge ' + badgeClass).css('font-size', '0.7rem');

                let payStatus = (row.payment_status || 'pending').toLowerCase();
                let payBadgeClass = payStatus === 'paid' ? 'text-success' : 'text-warning';
                $('#view_payment_status_badge').text((payStatus === 'paid' ? 'PAID' : 'UNPAID')).removeClass().addClass('badge ' + payBadgeClass).css({ 'border': '1px solid currentColor', 'background': 'transparent', 'font-size': '0.7rem' });

                // Notes visibility
                if (row.delivery_notes && row.delivery_notes !== '-' && row.delivery_notes !== 'No notes') {
                    $('#view_notes').text(row.delivery_notes);
                    $('#view_notes_container').removeClass('d-none');
                } else {
                    $('#view_notes_container').addClass('d-none');
                }

                let list = $('#view_items_list');
                list.empty();

                if (row.items && row.items.length) {
                    row.items.forEach(item => {
                        let cleanedName = window.cleanProductName(item.product_name, item.side, item.size);
                        let variantBadge = window.renderProductVariantBadge(item);
                        
                        let batchInfo = '<span class="text-muted-theme small">Not Allocated</span>';
                        if (item.batches && item.batches.length > 0) {
                            batchInfo = item.batches.map(b => `
                                <div class="mb-1 last-child-mb-0">
                                    <span class="badge bg-soft-info text-info border-0 px-2 py-0.5" style="font-size: 0.7rem; font-weight: bold;">${b.batch_no}</span>
                                    <div class="text-muted-theme" style="font-size: 0.65rem; margin-top: 1px;">Exp: ${b.expiry_date}</div>
                                </div>
                            `).join('');
                        }

                        let totalAmtFormatted = parseFloat(item.total_amount || 0).toFixed(2);

                        list.append(`
                            <tr class="align-middle" style="border-bottom: 1px solid var(--med-border-light, #f1f5f9);">
                                <td class="py-2 ps-3">
                                    <div class="fw-bold text-main-theme mb-0" style="font-size: 0.9rem; white-space: normal; line-height: 1.2;">
                                        ${cleanedName} ${variantBadge}
                                    </div>
                                    <div class="small text-muted-theme" style="font-size: 0.7rem;">
                                        (${item.brand || 'Generic'}) • <span class="fw-bold text-primary">${item.quantity} ${item.unit || 'Nos'}</span>
                                        ${item.free_quantity > 0 ? `<span class="text-success fw-bold ms-1" style="font-size: 0.65rem;">(+${item.free_quantity} Free)</span>` : ''}
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap mt-0 opacity-75" style="font-size: 0.6rem;">
                                        ${item.generic_name ? `<span>${item.generic_name}</span>` : ''}
                                        ${item.product_code && item.product_code !== '---' && item.product_code !== 'N/A' ? `<span>Code: ${item.product_code}</span>` : ''}
                                    </div>
                                </td>
                                <td>
                                    ${batchInfo}
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-primary" style="font-size: 0.85rem;">${item.quantity}</div>
                                    <div class="small opacity-75" style="font-size: 0.65rem;">${item.unit || 'Nos'}</div>
                                </td>
                                <td class="text-end pe-3 fw-bold text-main-theme" style="font-size: 0.85rem;">₹${totalAmtFormatted}</td>
                            </tr>
                        `);
                    });
                } else {
                    list.append('<tr><td colspan="4" class="text-center py-3 text-muted">No items</td></tr>');
                }
                $('#viewOrderModal').modal('show');
            });

            // Sales Manager Approve Click
            $(document).on('click', '.approve-order-btn', function () {
                let id = $(this).data('id');
                let row = $(this).data('row');

                const proceed = () => {
                    $('#approve_order_id').val(id);
                    $('#approve_order_code_display').text(row.order_code);
                    $('#approve_order_date_display').text(row.placed_at ? row.placed_at.split(' ')[0] : '--');
                    $('#approve_distributor_display').text(row.distributor_name);
                    $('#approve_location_display').text(row.distributor_location || '--');
                    $('#approve_total_display').text('₹' + row.total_amount);

                    // Status Badges
                    let status = (row.status || 'pending').toLowerCase();
                    let badgeClass = 'bg-secondary text-white';
                    if (status === 'pending') badgeClass = 'bg-secondary text-white';
                    else if (status === 'processing') badgeClass = 'bg-warning text-white';
                    else if (status === 'approved') badgeClass = 'bg-info text-white';
                    else if (status === 'delivered') badgeClass = 'bg-success text-white';
                    else if (status === 'cancelled') badgeClass = 'bg-danger text-white';
                    else if (status === 'rejected') badgeClass = 'bg-dark-red text-white';
                    $('#approve_status_badge').text(row.status || 'Pending').removeClass().addClass('badge ' + badgeClass).css('font-size', '0.7rem');

                    let payStatus = (row.payment_status || 'pending').toLowerCase();
                    let payBadgeClass = payStatus === 'paid' ? 'text-success' : 'text-warning';
                    $('#approve_payment_status_badge').text(payStatus === 'paid' ? 'PAID' : 'UNPAID').removeClass().addClass('badge ' + payBadgeClass).css({ 'border': '1px solid currentColor', 'background': 'transparent', 'font-size': '0.7rem' });

                    let list = $('#approve_items_list');
                    list.empty();

                    if (row.items && row.items.length) {
                        row.items.forEach(item => {
                            let appVariantBadge = window.renderProductVariantBadge(item);

                            list.append(`
                                <div class="invoice-list-row p-2">
                                    <div style="flex: 2;" class="fw-bold text-main-theme small ps-2">
                                        <div style="white-space: normal;">
                                            ${window.cleanProductName(item.product_name, item.side, item.size)} ${appVariantBadge}
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap mt-1">
                                            ${item.generic_name ? `<span class="badge bg-light text-dark border-0 fw-normal" style="font-size: 0.6rem;">${item.generic_name}</span>` : ''}
                                            ${item.pack && item.pack !== 'N/A' && item.pack !== '---' ? `<div class="small text-muted-theme opacity-75" style="font-size: 0.6rem;">P: ${item.pack}</div>` : ''}
                                            ${item.product_code && item.product_code !== '---' && item.product_code !== 'N/A' ? `<div class="small text-muted-theme opacity-75" style="font-size: 0.6rem;">C: ${item.product_code}</div>` : ''}
                                        </div>
                                    </div>
                                    <div style="flex: 1;" class="fw-bold text-primary text-center">
                                        ${item.quantity} ${item.unit || 'Nos'}
                                        ${item.free_quantity > 0 ? `<div class="text-success small fw-bold">+${item.free_quantity} Free</div>` : ''}
                                    </div>
                                    <div style="flex: 1;" class="fw-bold text-main-theme text-end pe-3">₹${item.total_amount}</div>
                                </div>
                            `);
                        });
                    } else {
                        list.append('<div class="invoice-list-row justify-content-center text-muted">No items found</div>');
                    }

                    $('#approveOrderForm')[0].reset();
                    $('#approveOrderModal').modal('show');
                };

                proceed();
            });

            // Sales Manager Approve Form Submit
            $('#approveOrderForm').submit(function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');
                let id = $('#approve_order_id').val();
                let url = "{{ route('admin.distributor-orders.accept-by-sales-manager', ':id') }}".replace(':id', id);

                let $btn = $(this).find('button[type="submit"]');
                let oldText = $btn.text();
                $btn.prop('disabled', true).text('Approving...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#approveOrderModal').modal('hide');
                        table.ajax.reload(null, false);
                        // // showToast('success', res.success || 'Order approved successfully');
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Failed to approve order');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text(oldText);
                    }
                });
            });

            // Admin Process/Accept Click
            $(document).on('click', '.accept-admin-btn', function () {
                let id = $(this).data('id');
                let row = $(this).closest('tr').find('.view-details-btn').data('row');

                const proceed = () => {
                    $('#process_order_id').val(id);
                    $('#processOrderForm')[0].reset();

                    // Populate Premium Header
                    $('#process_order_code_display').text(row.order_code || '--');
                    $('#process_order_date_display').text(row.placed_at ? row.placed_at.split(' ')[0] : '--');
                    $('#process_distributor_display').text(row.distributor_name || '--');
                    $('#process_location_display').text(row.distributor_location || '--');

                    // Status Badges
                    let status = (row.status || 'pending').toLowerCase();
                    let badgeClass = 'bg-secondary text-white';
                    if (status === 'pending') badgeClass = 'bg-secondary text-white';
                    else if (status === 'processing') badgeClass = 'bg-warning text-white';
                    else if (status === 'approved') badgeClass = 'bg-info text-white';
                    else if (status === 'delivered') badgeClass = 'bg-success text-white';
                    else if (status === 'cancelled') badgeClass = 'bg-danger text-white';
                    else if (status === 'rejected') badgeClass = 'bg-dark-red text-white';
                    $('#process_status_badge').text(row.status || 'Pending').removeClass().addClass('badge ' + badgeClass).css('font-size', '0.7rem');

                    let payStatus = (row.payment_status || 'pending').toLowerCase();
                    let payBadgeClass = payStatus === 'paid' ? 'text-success' : 'text-warning';
                    $('#process_payment_status_badge').text(payStatus === 'paid' ? 'PAID' : 'UNPAID').removeClass().addClass('badge ' + payBadgeClass).css({ 'border': '1px solid currentColor', 'background': 'transparent', 'font-size': '0.7rem' });

                    // Radio buttons are unchecked by default to force manual selection


                    // Reset OCR & Automation UI States
                    $('#automation_idle_state').removeClass('d-none');
                    $('#ocr_processing_state').addClass('d-none');
                    $('#verification_table_footer').addClass('d-none');
                    $('#scan_file_input').val('');
                    $('#distributor_approval_error_alert').removeClass('d-none');
                    $('#distributor_approval_error_message').text('Please select payment status and upload the finalized invoice to proceed.');
                    $('#btn_approve_order').prop('disabled', true);
                    
                    // Helper to check readiness
                    window.checkDistributorApprovalReadiness = function() {
                        let paySelected = $('input[name="payment_status"]:checked').length > 0;
                        let fileUploaded = $('#scan_file_input')[0].files.length > 0 || $('#verification_table_body').children().not('.invoice-list-row.justify-content-center').length > 0;
                        
                        if (paySelected && fileUploaded) {
                            $('#distributor_approval_error_alert').addClass('d-none');
                            $('#btn_approve_order').prop('disabled', false);
                        } else {
                            $('#distributor_approval_error_alert').removeClass('d-none');
                            $('#btn_approve_order').prop('disabled', true);
                            
                            let msg = "";
                            if (!paySelected && !fileUploaded) msg = "Please select payment status and upload the finalized invoice to proceed.";
                            else if (!paySelected) msg = "Please select the payment status manually to continue.";
                            else if (!fileUploaded) msg = "Please upload and scan the invoice to continue.";
                            $('#distributor_approval_error_message').text(msg);
                        }
                    };

                    // Populate Expected Details (old ID mappings if still needed, but mostly moved to premium header)
                    $('#expected_order_id').text(row.order_code || '--');
                    $('#expected_date').text(row.placed_at ? row.placed_at.split(' ')[0] : '--');
                    $('#expected_gstin').text(row.distributor_gst || '--');
                    $('#verification_table_footer').addClass('d-none');

                    let tbody = $('#batch_entry_body');
                    let vbody = $('#verification_table_body');
                    tbody.empty();
                    vbody.empty();

                    if (row && row.items) {
                        row.items.forEach(item => {
                            let rowHtml = `
                                <div data-item-id="${item.order_item_id}">
                                    <div class="d-none">
                                        <div class="fw-bold product-name-marker">${item.product_name}</div>
                                        <input type="number" name="batches[${item.order_item_id}][0][quantity]" value="${item.quantity}">
                                    </div>
                                    <div class="d-none" id="batches_for_${item.order_item_id}">
                                        <input type="text" name="batches[${item.order_item_id}][0][batch_no]" class="hidden-batch-val" required>
                                        <input type="date" name="batches[${item.order_item_id}][0][expiry_date]" class="hidden-expiry-val" required>

                                        <input type="hidden" name="batches[${item.order_item_id}][0][mrp]" class="hidden-mrp-val">
                                        <input type="hidden" name="batches[${item.order_item_id}][0][ptr]" class="hidden-ptr-val">
                                        <input type="hidden" name="batches[${item.order_item_id}][0][pts]" class="hidden-pts-val">
                                        <input type="hidden" name="batches[${item.order_item_id}][0][taxable_value]" class="hidden-taxable-val">
                                        <input type="hidden" name="batches[${item.order_item_id}][0][cgst]" class="hidden-cgst-val">
                                        <input type="hidden" name="batches[${item.order_item_id}][0][sgst]" class="hidden-sgst-val">
                                        <input type="hidden" name="batches[${item.order_item_id}][0][igst]" class="hidden-igst-val">
                                        <input type="hidden" name="batches[${item.order_item_id}][0][net_amount]" class="hidden-net-val">
                                    </div>
                                </div>
                            `;
                            tbody.append(rowHtml);

                            let vRowHtml = `
                                <div id="v_row_${item.order_item_id}" class="invoice-list-row p-2">
                                    <div class="ai-col-product fw-bold text-main-theme small ps-2">
                                        <div style="white-space: normal; line-height: 1.2; font-size: 0.9rem;">
                                            ${window.cleanProductName(item.product_name, item.side, item.size)} ${window.renderProductVariantBadge(item)}
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap mt-1">
                                            ${item.generic_name ? `<span class="badge bg-light text-dark border-0 fw-normal" style="font-size: 0.6rem;">${item.generic_name}</span>` : ''}
                                            ${item.product_code && item.product_code !== '---' && item.product_code !== 'N/A' ? `<div class="small text-muted-theme opacity-75" style="font-size: 0.6rem;">C: ${item.product_code}</div>` : ''}
                                        </div>
                                    </div>
                                    <div class="ai-col-batch">
                                        <input type="text" class="form-control form-control-sm v-batch-input border-0 bg-body-theme text-main-theme p-1" 
                                               data-id="${item.order_item_id}" placeholder="Wait AI..." readonly>
                                    </div>
                                    <div class="ai-col-expiry">
                                        <input type="text" class="form-control form-control-sm v-expiry-input border-0 bg-body-theme text-main-theme p-1" 
                                               data-id="${item.order_item_id}" placeholder="MM/YY" readonly>
                                    </div>
                                    <div class="ai-col-qty fw-bold text-primary text-center v-qty-display" data-original-unit="${item.unit || 'Nos'}">
                                        ${item.quantity} ${item.unit || 'Nos'}
                                        ${item.free_quantity > 0 ? `<div class="text-success small fw-bold">+${item.free_quantity} Free</div>` : ''}
                                    </div>
                                    <div class="ai-col-value fw-bold text-main-theme text-end pe-3 v-taxable-display">--</div>
                                </div>
                            `;
                            vbody.append(vRowHtml);
                        });
                    }

                    $('#processOrderModal').modal('show');
                };

                proceed();
            });

            function parseExpiryToDate(expStr) {
                if (!expStr) return '';
                expStr = expStr.trim();
                if (/^\d{4}-\d{2}-\d{2}$/.test(expStr)) return expStr;

                let match = expStr.match(/\b(\d{1,2})[\/\-\.](\d{2,4})\b/);
                if (!match) {
                    let d = new Date(expStr);
                    if (!isNaN(d.getTime())) return d.toISOString().split('T')[0];
                    return '';
                }

                let month = parseInt(match[1]);
                if (month < 1 || month > 12) return '';

                let year = match[2];
                if (year.length === 2) {
                    year = '20' + year;
                }

                let lastDay = new Date(year, month, 0).getDate();
                return `${year}-${month.toString().padStart(2, '0')}-${lastDay.toString().padStart(2, '0')}`;
            }

            // Sync Verification Edits to Hidden Inputs
            $(document).on('input', '.v-batch-input', function () {
                const id = $(this).data('id');
                $(`#batches_for_${id} .hidden-batch-val`).val($(this).val());
            });
            $(document).on('change', '.v-expiry-input', function () {
                const id = $(this).data('id');
                let displayVal = $(this).val();
                let parsedDate = parseExpiryToDate(displayVal);
                $(`#batches_for_${id} .hidden-expiry-val`).val(parsedDate || displayVal);
            });

            // Payment Radio Change
            $(document).on('change', 'input[name="payment_status"]', function() {
                if (typeof window.checkDistributorApprovalReadiness === 'function') {
                    window.checkDistributorApprovalReadiness();
                }
            });

            // OCR Dropzone Click
            $(document).on('click', '#ocr_dropzone', function () {
                $('#scan_file_input').click();
            });

            // Drag and Drop for OCR Dropzone
            const ocrDropzone = document.getElementById('ocr_dropzone');
            if (ocrDropzone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    ocrDropzone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    ocrDropzone.addEventListener(eventName, () => ocrDropzone.classList.add('bg-primary-subtle', 'border-primary'), false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    ocrDropzone.addEventListener(eventName, () => ocrDropzone.classList.remove('bg-primary-subtle', 'border-primary'), false);
                });

                ocrDropzone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    if (files.length) {
                        document.getElementById('scan_file_input').files = files;
                        $('#scan_file_input').trigger('change');
                    }
                }, false);
            }

            // OCR Processing Logic
            $(document).on('change', '#scan_file_input', async function () {
                const file = this.files[0];
                if (!file) return;

                // Unlock the invoice number field
                $('#retailer_invoice_no_input').prop('readonly', false).attr('placeholder', 'Enter invoice number...');

                // Switch UI to processing
                $('#ocr_processing_state').removeClass('d-none');
                $('#automation_idle_state').addClass('d-none');
                $('#automation_success_state').addClass('d-none');
                $('#automation_error_state').addClass('d-none');
                $('#ocr_dropzone').removeClass('has-file');
                $('#btn_approve_order').prop('disabled', true);

                // Unlock the invoice number field
                $('#invoice_no_input').prop('readonly', false).attr('placeholder', 'Enter the official invoice number...');

                $('#ocr_progress_bar').css('width', '50%');
                $('#ocr_status_text').text('AI is analyzing your invoice...');

                let formData = new FormData();
                formData.append('invoice', file);
                formData.append('order_id', $('#process_order_id').val());
                formData.append('order_type', 'distributor');
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: "{{ route('ocr.process') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#ocr_progress_bar').css('width', '100%').addClass('bg-success');
                        $('#ocr_status_text').text('Processing complete!');

                        setTimeout(() => {
                            $('#ocr_processing_state').fadeOut(300, function () {
                                $(this).addClass('d-none');

                                if (res.success && res.data) {
                                    // Store for Gatekeeper
                                    $('#invoice_no_input').data('ocr-data', res.data);
                                    $('#invoice_no_input').data('items-filled', false);

                                    let identifiedCount = parseAndFillOCRResponse(res.data);

                                    if (identifiedCount > 0) {
                                        $('#automation_idle_state').addClass('d-none');
                                        $('#automation_error_state').addClass('d-none');
                                        $('#automation_success_state').removeClass('d-none').hide().fadeIn(400);
                                        $('#extracted_metadata_section').show();
                                        $('#processed_summary_text').text(`${identifiedCount} items auto-filled from Invoice.`);
                                        if (typeof window.checkDistributorApprovalReadiness === 'function') {
                                            window.checkDistributorApprovalReadiness();
                                        }
                                    } else {
                                        // Gatekeeper or No Products found
                                        let extracted = $('#invoice_no_input').data('extracted');
                                        let entered = $('#invoice_no_input').val().trim().toLowerCase();

                                        $('#automation_success_state').addClass('d-none');
                                        $('#automation_error_state').removeClass('d-none').hide().fadeIn(400);

                                        if (entered && extracted && !isInvoiceMatch(entered, extracted)) {
                                            $('#automation_error_state h5').text('Invoice Number Mismatch');
                                            $('#automation_error_state p').text('Warning: The entered number differs from the document scan.');
                                            $('#extracted_metadata_section').show();
                                            parseAndFillOCRResponse(res.data);
                                        } else {
                                            $('#automation_error_state h5').text('No Products Identified');
                                            $('#automation_error_state p').text('The AI could not identify any ordered products in the uploaded invoice.');
                                            $('#extracted_metadata_section').hide();
                                            if (typeof window.checkDistributorApprovalReadiness === 'function') {
                                                window.checkDistributorApprovalReadiness();
                                            }
                                        }
                                    }
                                } else {
                                    $('#automation_idle_state').removeClass('d-none').hide().fadeIn(400);
                                    showToast('error', 'OCR Failed: Invalid response from server.');
                                    if (typeof window.checkDistributorApprovalReadiness === 'function') {
                                        window.checkDistributorApprovalReadiness();
                                    }
                                }
                            });
                        }, 500);
                    },
                    error: function (xhr) {
                        console.error('OCR Error:', xhr);
                        let errMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error during OCR processing';
                        showToast('error', 'OCR Failed: ' + errMsg);
                        $('#ocr_processing_state').addClass('d-none');
                        $('#automation_idle_state').removeClass('d-none');
                        $('#scan_file_input').val(''); // Clear failed file
                        $('#ocr_progress_bar').css('width', '0%');
                        $('#ocr_status_text').text('');
                        $('#btn_approve_order').prop('disabled', false); // Re-enable on error
                    }
                });
            });


            function isInvoiceMatch(entered, extracted) {
                if (!entered || !extracted) return false;
                entered = entered.trim().toLowerCase();
                extracted = extracted.trim().toLowerCase();

                if (entered === extracted) return true;

                // Normalized match (ignore symbols like / - . and spaces)
                let normEntered = entered.replace(/[^a-z0-9]/g, '');
                let normExtracted = extracted.replace(/[^a-z0-9]/g, '');

                return normEntered !== '' && normEntered === normExtracted;
            }

            function parseAndFillOCRResponse(data) {
                const meta = data.invoice_metadata || {};
                const invNo = (meta.invoice_no || meta.invoice_number || meta.inv_no || meta.bill_no || meta.invoice_id || meta.invoice_code || '--').trim();
                const dlNo = (meta.drug_license || meta.dl_no || meta.drug_lic_no || meta.license_no || '--').trim();
                const gstin = (meta.gstin || meta.gst_no || meta.gst_number || '--').trim();
                
                $('#extract_date').text(meta.date || '--');
                $('#extract_invoice_no').text(invNo);
                $('#extract_gstin').text(gstin);
                $('#extract_dl').text(dlNo);

                // Sync editable fields to hidden inputs if changed manually
                $('#extract_invoice_no').attr('contenteditable', 'true').on('input', function() {
                    let val = $(this).text().trim();
                    if (val !== '--') $('#invoice_no_input').val(val).trigger('input');
                });
                $('#extract_gstin').attr('contenteditable', 'true');
                $('#extract_dl').attr('contenteditable', 'true');

                // Validation Logic
                let enteredInv = $('#invoice_no_input').val().trim().toLowerCase();
                let extractedInvRaw = (meta.invoice_no || meta.invoice_number || '').trim();
                let extractedInv = extractedInvRaw.toLowerCase();

                // Auto-fill feature: If field is empty, populate from AI
                if (!enteredInv && extractedInvRaw) {
                    $('#invoice_no_input').val(extractedInvRaw).addClass('is-valid');
                    enteredInv = extractedInv;
                }

                let $valAlert = $('#ai_validation_alert');
                let $valMsg = $('#ai_validation_message');
                let hasError = false;

                $valAlert.addClass('d-none').removeClass('alert-warning alert-danger');

                if (meta.is_duplicate) {
                    $valAlert.removeClass('d-none').addClass('alert-danger');
                    $valMsg.text('DUPLICATE INVOICE: This invoice number has already been used by this distributor.');
                    hasError = true;
                } else if (enteredInv && extractedInv && !isInvoiceMatch(enteredInv, extractedInv)) {
                    $valAlert.removeClass('d-none').addClass('alert-warning');
                    $valMsg.text(`MISMATCH: Entered No. (${$('#invoice_no_input').val()}) does not match Extracted No. (${meta.invoice_no}).`);
                    hasError = true;
                }

                // Store for re-validation on manual input change
                $('#invoice_no_input').data('extracted', extractedInv);
                $('#invoice_no_input').data('extracted-raw', meta.invoice_no);
                $('#invoice_no_input').data('is-duplicate', meta.is_duplicate);

                if (hasError) {
                    // Show warning but no longer block product mapping
                    $('#btn_approve_order').prop('disabled', meta.is_duplicate); // Still block duplicates
                } else {
                    $('#btn_approve_order').prop('disabled', false);
                }

                if (!enteredInv) {
                     // Still need a number to proceed with final approval, but we can try to fill products
                     $('#btn_approve_order').prop('disabled', true);
                }

                return fillDistributorProducts(data);
            }

            function fillDistributorProducts(data) {
                let identifiedCount = 0;

                let totalTaxable = 0;
                let totalCgst = 0;
                let totalSgst = 0;
                let totalIgst = 0;
                let totalNet = 0;

                const items = [];
                $('#batch_entry_body').children('[data-item-id]').each(function () {
                    let name = $(this).find('.product-name-marker').text().trim();
                    items.push({
                        id: $(this).data('item-id'),
                        name: name,
                        orderedQty: parseInt($(this).data('ordered-qty')) || 0
                    });
                });
                
                let missingProducts = [];
                let matchedProducts = [];
                let invoiceProducts = [...(data.line_items || [])];

                items.forEach(item => {
                    const normalize = (str) => {
                        if (!str) return '';
                        return str.toLowerCase()
                            .replace(/\bsyp\b|\bsyrup\b/g, 'syrup')
                            .replace(/\btab\b|\btablet\b/g, 'tablet')
                            .replace(/\bcap\b|\bcapsule\b/g, 'capsule')
                            .replace(/\(\d+\)/g, ' ')
                            .replace(/[^a-z0-9\s]/g, ' ')
                            .replace(/\s+/g, ' ')
                            .trim();
                    };

                    let normItemName = normalize(item.name);

                    // 1. Try exact or full inclusion match
                    let matchedIdx = invoiceProducts.findIndex(p => {
                        if (!p.description) return false;
                        let normDesc = normalize(p.description);
                        // Exact or mutual inclusion
                        return normDesc === normItemName ||
                            normDesc.includes(normItemName) ||
                            normItemName.includes(normDesc);
                    });

                    // 2. Fallback to word-based intersection check
                    if (matchedIdx === -1) {
                        let itemWords = normItemName.split(' ').filter(w => w.length >= 2);
                        matchedIdx = invoiceProducts.findIndex(p => {
                            if (!p.description) return false;
                            let normDesc = normalize(p.description);
                            let matchCount = 0;
                            itemWords.forEach(word => {
                                if (normDesc.includes(word)) matchCount++;
                            });
                            // Threshold: 60% of words or 1 word minimum
                            let threshold = Math.max(1, Math.ceil(itemWords.length * 0.6));
                            return matchCount >= threshold;
                        });
                    }

                    // 3. Last fallback (start-of-string prefix match)
                    if (matchedIdx === -1 && normItemName.length >= 6) {
                        matchedIdx = invoiceProducts.findIndex(p => p.description && normalize(p.description).startsWith(normItemName.substring(0, 6)));
                    }

                    if (matchedIdx !== -1) {
                        let matchedInvoiceItem = invoiceProducts[matchedIdx];
                        invoiceProducts.splice(matchedIdx, 1); // Remove from pool to prevent double matching
                        identifiedCount++;

                        // Helper to parse numeric values safely
                        const safeParse = (val) => {
                            if (typeof val === 'string' && val.toUpperCase() === 'N/A') return 0;
                            let parsed = parseFloat(val);
                            return isNaN(parsed) ? 0 : parsed;
                        };

                        let extBatch = matchedInvoiceItem.batch && matchedInvoiceItem.batch !== 'N/A' ? matchedInvoiceItem.batch : '';
                        let extExpiry = matchedInvoiceItem.expiry && matchedInvoiceItem.expiry !== 'N/A' ? matchedInvoiceItem.expiry : '';
                        let extMrp = safeParse(matchedInvoiceItem.mrp);
                        let extPtr = safeParse(matchedInvoiceItem.ptr);
                        let extPts = safeParse(matchedInvoiceItem.pts);
                        let extTaxable = safeParse(matchedInvoiceItem.taxable_amt) || safeParse(matchedInvoiceItem.amount);
                        let extCgst = safeParse(matchedInvoiceItem.cgst);
                        let extSgst = safeParse(matchedInvoiceItem.sgst);
                        let extIgst = safeParse(matchedInvoiceItem.igst);

                        let extGstAmt = safeParse(matchedInvoiceItem.gst_amt);
                        let gstPercent = safeParse(matchedInvoiceItem.gst);
                        if (extGstAmt === 0 && gstPercent > 0 && extTaxable > 0) {
                            extGstAmt = extTaxable * (gstPercent / 100);
                        }

                        let billedQty = parseInt(matchedInvoiceItem.qty) || 0;
                        let freeQty = parseInt(matchedInvoiceItem.free) || parseInt(matchedInvoiceItem.sch) || parseInt(matchedInvoiceItem.scheme) || parseInt(matchedInvoiceItem.offer) || 0;

                        // Append Pack Size if available
                        let packStr = matchedInvoiceItem.pack && matchedInvoiceItem.pack !== 'N/A' && matchedInvoiceItem.pack.trim() !== '' ? ` [${matchedInvoiceItem.pack.trim()}]` : '';
                        if (packStr) {
                            let nameEl = $(`#v_row_${item.id} .ai-col-product`);
                            nameEl.text(nameEl.text() + packStr);
                        }

                        if (extBatch) {
                            $(`#v_row_${item.id} .v-batch-input`).val(extBatch);
                            $(`#batches_for_${item.id} .hidden-batch-val`).val(extBatch);
                        }
                        if (extExpiry) {
                            let parsedExp = parseExpiryToDate(extExpiry);
                            let cleanDisplay = extExpiry;
                            let match = extExpiry.match(/\b(\d{1,2}[\/\-\.]\d{2,4})\b/);
                            if (match) cleanDisplay = match[1];

                            $(`#v_row_${item.id} .v-expiry-input`).val(cleanDisplay);
                            $(`#batches_for_${item.id} .hidden-expiry-val`).val(parsedExp || cleanDisplay);
                        }

                        if (billedQty > 0) {
                            $(`input[name="batches[${item.id}][0][quantity]"]`).val(billedQty);
                            let unitStr = $(`#v_row_${item.id} .v-qty-display`).data('original-unit') || '';
                            let displayHtml = `<strong>${billedQty}</strong> ${unitStr}`;
                            
                            if (freeQty > 0) displayHtml += ` <small class="text-success ms-1">(+${freeQty} Free)</small>`;
                            
                            // Visual cue if quantity differs from ordered
                            if (billedQty !== item.orderedQty) {
                                let diffClass = billedQty > item.orderedQty ? 'text-primary' : 'text-danger';
                                displayHtml += ` <br><small class="${diffClass} fw-bold" style="font-size: 0.65rem;">(Ord: ${item.orderedQty})</small>`;
                            }
                            
                            $(`#v_row_${item.id} .v-qty-display`).html(displayHtml);
                        }

                        // Update Hidden Values
                        $(`#batches_for_${item.id} .hidden-mrp-val`).val(extMrp);
                        $(`#batches_for_${item.id} .hidden-ptr-val`).val(extPtr);
                        $(`#batches_for_${item.id} .hidden-pts-val`).val(extPts);
                        $(`#batches_for_${item.id} .hidden-taxable-val`).val(extTaxable);
                        $(`#batches_for_${item.id} .hidden-cgst-val`).val(extCgst);
                        $(`#batches_for_${item.id} .hidden-sgst-val`).val(extSgst);
                        $(`#batches_for_${item.id} .hidden-igst-val`).val(extIgst);

                        // If individual GST components are missing but total GST amt exists
                        let extTotalGst = extCgst + extSgst + extIgst;
                        if (extTotalGst === 0 && extGstAmt > 0) extTotalGst = extGstAmt;

                        let netAmt = extTaxable + extTotalGst;
                        $(`#batches_for_${item.id} .hidden-net-val`).val(netAmt.toFixed(2));
                        $(`#v_row_${item.id} .v-taxable-display`).text(`₹${netAmt.toFixed(2)}`);

                        totalTaxable += extTaxable;
                        totalCgst += extCgst;
                        totalSgst += extSgst;
                        totalIgst += extIgst;
                        totalNet += netAmt;
                    } else {
                        missingProducts.push(item.name);
                    }
                });

                if (identifiedCount > 0) {
                    $('#tfoot_taxable').text(totalTaxable.toFixed(2));
                    $('#tfoot_gst_total').text((totalCgst + totalSgst + totalIgst).toFixed(2));

                    // Show AI total as primary Doc Total if available
                    let ocrTotalStr = data.invoice_metadata ? data.invoice_metadata.total_amount : '';
                    let ocrTotal = parseFloat(ocrTotalStr);
                    let finalAmount = isNaN(ocrTotal) ? totalNet : ocrTotal;

                    $('#tfoot_net').text(`₹${finalAmount.toFixed(2)}`);

                    if (!isNaN(ocrTotal)) {
                        $('#tfoot_net_label').text('Doc Total');
                        $('#tfoot_matched_net').html(`Matched Total: ₹${totalNet.toFixed(2)}`).show();
                    } else {
                        $('#tfoot_net_label').text('Invoice Net');
                        $('#tfoot_matched_net').hide();
                    }
                    
                    // Update premium metadata card row 2
                    $('#extract_total_amount').text('₹' + totalTaxable.toFixed(2));
                    $('#extract_net_amount').text('₹' + finalAmount.toFixed(2));

                    // Update hidden inputs for form submission
                    $('#final_amount_input').val(finalAmount.toFixed(2));
                    $('#taxable_amount_input').val(totalTaxable.toFixed(2));
                    
                    // STRICT MATCH BLOCKING: Only enable if no missing AND no extra items
                    if (missingProducts.length === 0 && invoiceProducts.length === 0) {
                        $('#automation_error_state').hide();
                        $('#automation_success_state').fadeIn();
                        $('#btn_approve_order').prop('disabled', false);
                    } else {
                        $('#automation_error_state').removeClass('d-none').show();
                        $('#automation_error_state h5').text('Invoice Content Mismatch');
                        
                        let msg = '';
                        if (missingProducts.length > 0) {
                            msg = `This document is <b>missing ${missingProducts.length} ordered items</b>. `;
                        }
                        if (invoiceProducts.length > 0) {
                            msg += `It also contains <b>${invoiceProducts.length} extra items</b> not in the order. `;
                        }
                        
                        $('#automation_error_state p').html(`${msg} Please upload a perfect match invoice to proceed.`);
                        $('#automation_success_state').hide();
                        $('#btn_approve_order').prop('disabled', true);
                    }
                    
                    $('#verification_table_footer').removeClass('d-none');
                }

                // COMPREHENSIVE SUMMARY ALERT
                if (missingProducts.length > 0 || invoiceProducts.length > 0) {
                    let matchedHtml = matchedProducts.length > 0 
                        ? `<div class="mb-2"><small class="fw-bold text-success">MATCHED (${matchedProducts.length})</small><ul class="text-start text-success small mb-0" style="column-count: 1; list-style-type: none; padding-left: 0;">${matchedProducts.map(p => `<li>✓ ${p}</li>`).join('')}</ul></div>` 
                        : '';
                    
                    let missingHtml = missingProducts.length > 0 
                        ? `<div class="mb-2"><small class="fw-bold text-danger">MISSING IN INVOICE (${missingProducts.length})</small><ul class="text-start text-danger small mb-0" style="column-count: 1; list-style-type: none; padding-left: 0;">${missingProducts.map(p => `<li>✗ ${p}</li>`).join('')}</ul></div>` 
                        : '';
                    
                    let extraHtml = invoiceProducts.length > 0 
                        ? `<div class="mb-2"><small class="fw-bold text-danger">EXTRA ITEMS IN INVOICE (${invoiceProducts.length})</small><ul class="text-start text-danger small mb-0" style="column-count: 1; list-style-type: none; padding-left: 0;">${invoiceProducts.map(p => `<li>! ${p.description || 'Unknown Item'}</li>`).join('')}</ul></div>` 
                        : '';

                    Swal.fire({
                        title: '<h4 class="fw-bold mb-0">Verification Summary</h4>',
                        html: `
                            <div class="text-start p-3 bg-light rounded shadow-inner" style="max-height: 300px; overflow-y: auto;">
                                ${matchedHtml}
                                ${missingHtml}
                                ${extraHtml}
                            </div>
                            <p class="mt-3 mb-0 text-dark fw-bold">Please verify or upload another invoice.</p>`,
                        icon: (missingProducts.length === 0 && invoiceProducts.length > 0) ? 'info' : 'warning',
                        confirmButtonText: 'I understand',
                        customClass: {
                            confirmButton: 'btn btn-primary px-5 py-2 shadow-sm'
                        },
                        buttonsStyling: false
                    });
                }

                $('#invoice_no_input').data('items-filled', true);
                return identifiedCount;
            }

            // Admin Process Form Submit
            $('#processOrderForm').submit(function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                // Append the scanned invoice file if it exists, otherwise block submission
                let scanFileInput = document.getElementById('scan_file_input');
                if (!scanFileInput.files || scanFileInput.files.length === 0) {
                    showToast('error', 'Invoice document is strictly required for approval.');
                    return;
                }

                formData.append('invoice', scanFileInput.files[0]);

                let id = $('#process_order_id').val();
                let url = "{{ route('admin.distributor-orders.accept-by-admin', ':id') }}".replace(':id', id);

                let $btn = $(this).find('button[type="submit"]');
                let oldHtml = $btn.html();
                $btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            $('#processOrderModal').modal('hide');
                            table.ajax.reload(null, false);
                            // // showToast('success', res.success);
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                        } else {
                            $('#distributor_approval_error_message').html(res.error || 'Failed to approve order');
                            $('#distributor_approval_error_alert').removeClass('d-none').show();
                            $('#processOrderModal').animate({ scrollTop: 0 }, 'slow');
                        }
                    },
                    error: function (xhr) {
                        let errMsg = xhr.responseJSON ? xhr.responseJSON.error || xhr.responseJSON.message : 'Failed to approve order';
                        $('#distributor_approval_error_message').html(errMsg);
                        $('#distributor_approval_error_alert').removeClass('d-none').show();
                        $('#processOrderModal').animate({ scrollTop: 0 }, 'slow');
                    },
                    complete: function () {
                        $btn.html(oldHtml).prop('disabled', false);
                    }
                });
            });

            $(document).on('input', '#invoice_no_input', function () {
                let entered = $(this).val().trim().toLowerCase();
                let extracted = $(this).data('extracted');
                let extractedRaw = $(this).data('extracted-raw');
                let isDuplicate = $(this).data('is-duplicate');
                let $valAlert = $('#ai_validation_alert');
                let $valMsg = $('#ai_validation_message');
                let hasError = false;

                // If OCR hasn't run yet, skip live validation
                if (extracted === undefined || extracted === null) return;

                $valAlert.addClass('d-none').removeClass('alert-warning alert-danger');

                if (isDuplicate) {
                    $valAlert.removeClass('d-none').addClass('alert-danger');
                    $valMsg.text('DUPLICATE INVOICE: This invoice number has already been used by this distributor.');
                    hasError = true;
                } else if (entered && extracted && !isInvoiceMatch(entered, extracted)) {
                    $valAlert.removeClass('d-none').addClass('alert-warning');
                    $valMsg.text(`MISMATCH: Entered No. (${$(this).val()}) does not match Extracted No. (${extractedRaw}).`);
                    hasError = true;
                }

                // Gatekeeper: If it matches now and wasn't filled, fill it!
                if (!hasError && entered && !$(this).data('items-filled')) {
                    let ocrData = $(this).data('ocr-data');
                    if (ocrData) {
                        let identifiedCount = fillDistributorProducts(ocrData);
                        if (identifiedCount > 0) {
                            $('#automation_error_state').addClass('d-none');
                            $('#automation_success_state').removeClass('d-none').hide().fadeIn(400);
                            $('#extracted_metadata_section').show();
                            $('#processed_summary_text').text(`${identifiedCount} items auto-filled from Invoice.`);
                        }
                    }
                } else if (!entered || hasError) {
                } else {
                    $('#verification_table_footer').addClass('d-none');
                    $('#automation_success_state').hide();
                    $('#extracted_metadata_section').hide();
                    $('#automation_error_state').removeClass('d-none').show();
                    $('#automation_error_state h5').text('Invoice Number Required');
                    $('#automation_error_state p').text('Please enter the invoice number to unlock and verify data.');
                    $(this).data('items-filled', false);
                }

                $('#btn_approve_order').prop('disabled', hasError || !entered);
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
                formData.append('order_type', 'distributor');

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
                        let err = xhr.responseJSON ? xhr.responseJSON.error : 'Submission failed';
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
