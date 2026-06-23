@extends('layouts.admin')

@section('page-body')
    <style>
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

        .action-buttons {
            display: inline-flex !important;
            gap: 4px;
            align-items: center;
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

        /* Modal sizing and table compacting */
        .modal-xl {
            max-width: 1140px;
        }

        .modal-content {
            border-radius: 20px !important;
            overflow: hidden !important;
        }

        #retailer-approval-table td:last-child {
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
            padding: 12px 20px;
            font-weight: bold;
            color: #1e293b;
            background: #fff;
            border-radius: 8px;
            margin-top: 4px;
            border: 1px solid #e2e8f0;
        }

        /* AI Scan Specific Flex Column Layout */
        .ai-col-product {
            flex: 2.5;
            max-width: 200px;
            white-space: normal;
            word-break: break-word;
        }

        .ai-col-batch {
            flex: 1.5;
        }

        .ai-col-expiry {
            flex: 1.5;
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
                    <h5 class="mb-0 text-primary fw-bold">Retailer Order Approvals</h5>
                </div>

                <ul class="nav nav-tabs border-bottom-0" id="orderStatusTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-all" data-bs-toggle="tab" data-status=""
                            type="button" role="tab">All</button>
                    </li>
                    @php
                        $user = Auth::user();
                        $isSMorFS = $user->hasRole(['salesmanager', 'fieldstaff']);
                        $isDistributor = $user->hasRole('distributor');
                        // Default to 'processing' for distributors, 'all' for others
                        $defaultStatus = $isDistributor ? 'processing' : 'all'; 
                    @endphp
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-pending" data-bs-toggle="tab"
                            data-status="pending" type="button" role="tab">Pending</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-processing"
                            data-bs-toggle="tab" data-status="processing" type="button" role="tab">Processing</button>
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
                    <table class="table table-striped table-hover" id="retailer-approval-table">
                        <thead>
                            <tr>
                                <th style="display:none;">ID</th>
                                <th>No.</th>
                                <th>Order Code</th>
                                <th>Retailer</th>
                                <th style="width: 200px;">Products</th>
                                <th style="width: 120px;">Brand</th>
                                <th>Total</th>
                                <th>Placed At</th>
                                <th>Distributor</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Invoice</th>
                                <th>Actions</th>
                                <th class="d-none">Shop Name</th>
                                <th class="d-none">Area</th>
                                <th class="d-none">District</th>
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

    {{-- Improved View Details Modal --}}
    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 p-0">
                    <div class="w-100 p-3 bg-card-theme"
                        style="border-top: 3px solid var(--med-primary); border-bottom: 1px solid var(--med-border-light);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-primary-subtle text-primary px-2 py-1 fw-bold" style="font-size: 0.6rem; letter-spacing: 0.5px;">ORDER DETAILS</span>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <h4 class="mb-0 fw-bold text-main-theme" id="view_order_code">--</h4>
                        <div class="d-flex align-items-center gap-3 mt-2">
                            <span class="small text-muted-theme" style="font-size: 0.75rem;"><i class="fa fa-calendar me-1 text-primary"></i> <span id="view_placed_at">--</span></span>
                            <span class="small text-muted-theme" style="font-size: 0.75rem;"><i class="fa fa-hashtag me-1 text-primary"></i> Inv: <span id="view_invoice_no">--</span></span>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="badge" id="view_status_badge" style="font-size: 0.65rem; padding: 0.3em 0.6em; letter-spacing: 0.5px;">--</div>
                                <div class="badge" id="view_payment_status_badge" style="font-size: 0.65rem; padding: 0.3em 0.6em; letter-spacing: 0.5px; border: 1px solid currentColor; background: transparent;">--</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-3 bg-body-theme">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm h-100 bg-card-theme" style="border-radius: 10px !important;">
                                <div class="card-body py-2 px-3">
                                    <h6 class="text-uppercase text-muted-theme fw-bold mb-1"
                                        style="font-size: 0.6rem; letter-spacing: 1px;">Retailer Information</h6>
                                    <h5 class="fw-bold mb-1 text-main-theme" id="view_retailer_name" style="font-size: 1rem;">--</h5>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center text-muted-theme small" style="font-size: 0.75rem;">
                                            <i class="fa fa-phone me-2 text-primary" style="width: 12px;"></i>
                                            <span id="view_retailer_phone">--</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm h-100 bg-card-theme" style="border-radius: 10px !important;">
                                <div class="card-body py-2 px-3">
                                    <h6 class="text-uppercase text-muted-theme fw-bold mb-1"
                                        style="font-size: 0.6rem; letter-spacing: 1px;">Distributor Information</h6>
                                    <h5 class="fw-bold mb-1 text-main-theme" id="view_distributor_name" style="font-size: 1rem;">--</h5>
                                    <div class="d-flex align-items-center text-muted-theme small" style="font-size: 0.75rem;">
                                        <i class="fa fa-phone me-2 text-primary" style="width: 12px;"></i>
                                        <span id="view_distributor_phone">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm overflow-hidden mb-4 bg-card-theme">
                        <div class="card-header bg-transparent py-3 border-0">
                            <h6 class="mb-0 fw-bold text-main-theme"><i class="fa fa-box-open text-primary me-2"></i>Order Items</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-body-theme">
                                    <tr>
                                        <th class="border-0 small text-muted-theme text-uppercase fw-bold py-3 ps-4" style="width: 240px;">Product</th>
                                        <th class="border-0 small text-muted-theme text-uppercase fw-bold py-3">Batch/Exp</th>
                                        <th class="border-0 small text-muted-theme text-uppercase fw-bold py-3 text-center">Qty</th>
                                        <th class="border-0 small text-muted-theme text-uppercase fw-bold py-3 text-center">Free</th>
                                        <th class="border-0 small text-muted-theme text-uppercase fw-bold py-3 text-end">Price</th>
                                        <th class="border-0 small text-muted-theme text-uppercase fw-bold py-3 text-end pe-4">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="view_items_body">
                                    <!-- Items added via JS -->
                                </tbody>
                                <tfoot class="bg-body-theme">
                                    <tr class="text-main-theme">
                                        <td colspan="4" class="text-end fw-bold py-3">Grand Total:</td>
                                        <td class="text-end fw-bold text-primary fs-5 py-3 pe-4" id="view_grand_total">₹0.00
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-card-theme border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">Close</button>
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

    {{-- Hidden file input for invoice --}}
    <input type="file" id="invoice_upload_input" class="d-none" accept=".pdf,.jpg,.jpeg,.png">


    {{-- Remove Invoice Confirmation Modal --}}
    <div class="modal fade" id="removeInvoiceConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Invoice Removal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to remove this invoice? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveInvoiceBtn">Remove</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Status Change Confirmation Modal --}}
    <div class="modal fade" id="statusChangeConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Status Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="fa fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p class="mb-0">Are you sure you want to change the status of this order?</p>
                    <div class="mt-3 p-2 bg-light rounded d-flex justify-content-between">
                        <span class="text-muted small">New Status:</span>
                        <strong id="new-status-label" class="text-primary">-</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusChangeBtn">Confirm Change</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Approve Retailer Order Modal (Simple) --}}
    <div class="modal fade" id="approveRetailerOrderModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                {{-- <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-check-circle me-2"></i> Approve Retailer Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div> --}}
                <form id="approveRetailerOrderForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="approve_retailer_order_id" name="order_id">
                    <div class="modal-body p-0 bg-card-theme">
                        <!-- Order Summary Header -->
                        <div class="order-summary-header">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                            <div class="row align-items-center">
                                <div class="col-sm-7">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary-subtle text-primary me-2 fw-bold">ORDER</span>
                                        <h4 class="mb-0 fw-bold text-main-theme" id="retailer_approve_order_code_display">--</h4>
                                    </div>
                                    <div class="retailer-detail-item">
                                        <i class="fa fa-calendar"></i>
                                        <span id="retailer_approve_order_date_display">--</span>
                                    </div>
                                </div>
                                <div class="col-sm-5 text-sm-end">
                                    <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Retailer Details</div>
                                    <h5 class="fw-bold text-main-theme mb-1" id="retailer_approve_retailer_display">--</h5>
                                    <div class="retailer-detail-item justify-content-sm-end">
                                        <i class="fa fa-phone"></i>
                                        <span id="retailer_approve_phone_display">--</span>
                                    </div>
                                    <div class="d-flex justify-content-sm-end gap-2 mt-2 align-items-center">
                                        <div class="badge" id="approve_retailer_status_badge" style="font-size: 0.75rem; padding: 0.4em 0.8em; letter-spacing: 0.5px;">--</div>
                                        <div class="badge" id="approve_retailer_payment_status_badge" style="font-size: 0.75rem; padding: 0.4em 0.8em; letter-spacing: 0.5px; border: 1px solid currentColor; background: transparent;">--</div>
                                    </div>
                                    <div class="retailer-detail-item justify-content-sm-end d-none"
                                        id="retailer_approve_gstin_container">
                                        <i class="fa fa-id-card"></i>
                                        <span>GST: </span><span id="retailer_approve_gstin_display">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <!-- Action Required / Validation Errors -->
                            <div id="approval_validation_errors" class="mb-4">
                                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 d-flex align-items-center" style="background: #fff5f5;">
                                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-danger">Action Required</h6>
                                        <p class="mb-0 text-muted-theme small" id="approval_validation_msg">Please select payment status and upload the finalized invoice to proceed.</p>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-3 d-flex align-items-center">
                                <span class="bg-light p-2 rounded me-2"><i
                                        class="fa fa-shopping-basket text-primary"></i></span>
                                Order Items Summary
                            </h6>
                            <div class="invoice-list mb-4">
                                <div class="invoice-list-header">
                                    <div style="flex: 2; max-width: 240px;">Product Name</div>
                                    <div style="flex: 1;" class="text-center">Quantity</div>
                                    <div style="flex: 1;" class="text-center">Free</div>
                                    <div style="flex: 1;" class="text-end">Value (PTR)</div>
                                </div>
                                <div id="retailer_approve_items_list">
                                    <!-- Items will be populated here -->
                                </div>
                                <div class="invoice-list-footer border-0 shadow-sm bg-card-theme text-main-theme">
                                    <div class="me-3 text-muted-theme">Estimated Total Amount:</div>
                                    <div class="text-primary fw-bold fs-4" id="retailer_approve_total_display">₹0</div>
                                </div>
                            </div>

                            <div id="invoiceUploadGroup">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 d-flex align-items-center">
                                        <span class="bg-light p-2 rounded me-2"><i
                                                class="fa fa-file-invoice text-success"></i></span>
                                        Finalized Invoice Document
                                    </h6>
                                    <span class="badge bg-soft-danger text-danger">MANDATORY</span>
                                </div>

                                <div class="premium-dropzone" id="retailer_invoice_dropzone">
                                    <i class="fa fa-cloud-upload-alt"></i>
                                    <h5 class="fw-bold mb-1" id="dropzone_title">Click or Drag & Drop to Upload Invoice</h5>
                                    <p class="text-muted small mb-0" id="dropzone_subtitle">Supported formats: PDF, JPG, PNG
                                        (Max 5MB)</p>
                                    <div id="file_preview_name" class="mt-2 fw-bold text-success d-none"></div>
                                </div>

                                <input type="file" name="invoice" id="retailer_invoice_file_input" class="d-none"
                                    accept=".pdf,.jpg,.jpeg,.png">

                                <div class="alert alert-info mt-3 py-2 px-3 border-0 small">
                                    <i class="fa fa-info-circle me-2"></i>
                                    Approval requires the official invoice generated from your local billing software.
                                </div>
                            </div>

                            {{-- <div id="approveModalTextContainer" class="text-center mt-3 d-none">
                                <p class="mb-0 text-muted" id="approveModalText">Ready to confirm approval for this order?
                                </p>
                            </div> --}}
                        </div>
                    </div>
                    <div class="modal-footer bg-card-theme border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Distributor Batch Selection Modal --}}
    <div class="modal fade" id="distributorApproveModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
                
                <form id="distributorApproveForm">
                    <div class="modal-body p-0">
                        <!-- Order Summary Header -->
                        <div class="order-summary-header"
                             style="border-top-color: #22c55e;"> {{-- Green accent for batch allocation --}}
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>
                            <div class="row align-items-center">
                                <div class="col-sm-7">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success-subtle text-success me-2 fw-bold">BATCH ALLOCATION</span>
                                        <h4 class="mb-0 fw-bold text-main-theme" id="dist_approve_order_code_display">--</h4>
                                    </div>
                                    <div class="retailer-detail-item">
                                        <i class="fa fa-calendar"></i>
                                        <span id="dist_approve_order_date_display">--</span>
                                    </div>
                                </div>
                                <div class="col-sm-5 text-sm-end">
                                    <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Retailer / Site</div>
                                    <h5 class="fw-bold text-main-theme mb-1" id="dist_approve_retailer_display">--</h5>
                                    <div class="retailer-detail-item justify-content-sm-end">
                                        <i class="fa fa-map-marker-alt"></i>
                                        <span id="dist_approve_location_display">--</span>
                                    </div>
                                    {{-- Badges removed as requested --}}
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-body-theme">
                            <input type="hidden" id="approve_order_id" name="order_id">

                            <!-- Action Error Alert -->
                            <div id="retailer_approval_error_alert" class="alert alert-danger d-none mb-4 shadow-sm border-0 animate__animated animate__shakeX approval-error-alert">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-1 fw-bold text-danger">Action Required</h6>
                                        <div id="retailer_approval_error_message" class="small fw-bold text-dark"></div>
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
                                        <div class="text-muted small">Update the payment status for this order if required.</div>
                                    </div>
                                    <div class="status-badge-group">
                                        <label class="status-radio-option">
                                            <input type="radio" name="payment_status" value="paid" id="retailer_modal_pay_paid">
                                            <span class="status-radio-box">Mark as Paid</span>
                                        </label>
                                        <label class="status-radio-option">
                                            <input type="radio" name="payment_status" value="pending" id="retailer_modal_pay_pending">
                                            <span class="status-radio-box">Still Pending</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Number (Hidden, populated by AI) -->
                            <input type="hidden" name="invoice_no" id="retailer_invoice_no_input" required>

                            <!-- Smart Invoice Processing Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 d-flex align-items-center">
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
                                            batches & expiries)</p>
                                    </div>
                                    <input type="file" class="d-none" name="invoice" id="scan_retailer_file_input"
                                        accept=".pdf,.jpg,.jpeg,.png">

                                    <div id="results_loading_spinner" class="text-center mt-4 d-none">
                                        <div class="spinner-border text-primary mb-3" role="status"
                                            style="width: 2.5rem; height: 2.5rem;"></div>
                                        <h5 class="fw-bold text-dark">AI is scanning your invoice...</h5>
                                        <p class="text-muted small">This may take a few seconds.</p>
                                    </div>
                                </div>

                                <div id="automation_success_state" class="d-none">
                                    <div class="premium-metadata-card p-3 rounded-4 border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                                        <div class="row g-4 mb-3">
                                            <div class="col-md-3 border-end border-2 border-white">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Invoice Date</div>
                                                <div class="fw-bold text-dark fs-6" id="meta_date" contenteditable="true" title="Click to edit">--</div>
                                            </div>
                                            <div class="col-md-3 border-end border-2 border-white">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Invoice Number</div>
                                                <div class="fw-bold text-primary fs-6" id="meta_invoice_no" contenteditable="true" title="Click to edit">--</div>
                                            </div>
                                            <div class="col-md-3 border-end border-2 border-white">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">GSTIN Extracted</div>
                                                <div class="fw-bold text-dark fs-6" id="meta_gstin" contenteditable="true" title="Click to edit">--</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Drug License</div>
                                                <div class="fw-bold text-dark fs-6" id="meta_dl" contenteditable="true" title="Click to edit">--</div>
                                            </div>
                                        </div>
                                        <hr class="my-3 border-white border-2">
                                        <div class="row g-4">
                                            <div class="col-md-6 border-end border-2 border-white">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Total Amount (Taxable)</div>
                                                <div class="fw-bold text-secondary fs-5" id="meta_taxable_amount">₹0.00</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Net Amount (Payable)</div>
                                                <div class="fw-bold text-success fs-5" id="meta_net_amount">₹0.00</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- AI Mismatch / Duplicate Warnings -->
                                    <div id="ai_retailer_validation_alert" class="alert alert-warning py-2 mb-3 d-none">
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-exclamation-triangle me-2"></i>
                                            <div id="ai_retailer_validation_message" class="small fw-bold"></div>
                                        </div>
                                    </div>
                                </div> <!-- Close success state here -->

                                <div id="automation_success_footer" class="d-none mt-3 pb-3 border-bottom">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#scan_retailer_file_input').val('').click()">
                                        <i class="fa fa-sync me-1"></i> Scan Another File
                                    </button>
                                </div>

                                <div id="automation_error_state" class="d-none">
                                    <div
                                        class="text-center py-5 bg-soft-danger rounded-3 border-start border-4 border-danger">
                                        <i class="fa fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                        <h5 class="fw-bold text-danger">Mismatched Invoice!</h5>
                                        <p class="text-muted small">AI could not find matching products in this document.
                                        </p>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="$('#scan_retailer_file_input').val('').click()">Try Another
                                            File</button>
                                    </div>
                                </div>

                                <div class="invoice-list mt-3 d-none border rounded-3 overflow-hidden shadow-sm"
                                    id="batch_allocation_table_container">
                                    <div class="invoice-list-header px-3" style="background: #f8fafc;">
                                        <div style="flex: 2.5;">Product</div>
                                        <div style="flex: 1.5;">Batch</div>
                                        <div style="flex: 1.5;">Expiry</div>
                                        <div style="flex: 1;" class="text-center">Qty</div>
                                        <div style="flex: 1.2;" class="text-end">Taxable</div>
                                        <div style="flex: 1;" class="text-end">GST</div>
                                        <div style="flex: 1.3;" class="text-end">Total</div>
                                    </div>
                                    <div id="verification_table_body" class="px-3">
                                        <!-- AI Verification Rows -->
                                    </div>
                                    <div id="verification_table_footer"
                                        class="invoice-list-footer d-block d-none bg-light p-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted small">Calculated Net Total:</span>
                                            <span class="fw-bold" id="v_total_net">₹0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between pt-2 border-top">
                                            <span class="text-dark fw-bold">Invoiced Total (AI Scan):</span>
                                            <span class="text-primary fs-5 fw-bold" id="v_total_meta">₹0.00</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden inputs for Form Submission -->
                                <div id="hidden_batch_inputs" class="d-none">
                                    <div id="batch_entry_body"></div>
                                    <input type="hidden" name="final_amount" id="final_amount_input">
                                    <input type="hidden" name="taxable_amount" id="taxable_amount_input">
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none px-4" data-bs-dismiss="modal">Go Back</button>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm" id="btnSubmitDistributorApprove" 
                            disabled
                            style="border-radius: 12px; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                            Confirm & Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Retailer Payment Status Modal --}}
    <div class="modal fade" id="retailerPaymentStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="retailerPaymentStatusForm">
                    <div class="modal-body">
                        <input type="hidden" id="retailer_payment_order_id">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="retailer_payment_status_select" name="payment_status">
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

    {{-- Reject Retailer Order Modal --}}
    <div class="modal fade" id="rejectRetailerOrderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
                <div class="modal-header border-0 py-3 px-4 position-relative" style="background-color: #b91c1c;">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fa fa-times-circle fs-4 text-white"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" style="color: #ffffff !important;">Reject Order</h5>
                            <p class="small text-white text-opacity-85 mb-0" id="reject_order_code_display" style="color: rgba(255,255,255,0.85) !important;"></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectRetailerOrderForm">
                    @csrf
                    <div class="modal-body p-4">
                        <input type="hidden" id="reject_retailer_order_id" name="order_id">
                        
                        <div class="p-3 rounded-3 mb-4 d-flex align-items-start bg-danger-subtle border border-danger border-opacity-25">
                            <i class="fa fa-exclamation-triangle text-danger mt-1 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1 text-danger-emphasis">Confirm Rejection</h6>
                                <p class="text-body-secondary small mb-0">Rejected orders cannot be processed further. Please provide a clear reason for the retailer and staff.</p>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold text-dark small text-uppercase">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control border-0 bg-light shadow-none" name="rejection_reason" rows="4" required
                                placeholder="E.g., Out of stock, incorrect pricing, invalid retailer document..." 
                                style="border-radius: 12px; resize: none;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none px-4" data-bs-dismiss="modal">Go Back</button>
                        <button type="submit" class="btn px-4 py-2 fw-bold shadow-sm" style="border-radius: 10px; background-color: #b91c1c; color: #fff;">
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $retailers = \App\Models\Retailer::with('user')->get();
        $distributors = \App\Models\Distributor::with('user')->get();
        $products = \App\Models\Product::all();
    @endphp

    {{-- Edit History Modal --}}
    <div class="modal fade" id="editHistoryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow bg-card-theme" style="border-radius: 15px;">
                <div class="modal-header border-0 py-3" style="background: linear-gradient(135deg, #1e293b, #0f172a); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <h5 class="modal-title fw-bold text-white mb-0" style="color: #ffffff !important;"><i class="fa fa-history me-2"></i>Edit History - <span id="history_order_code" class="fw-bold text-warning"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-body-theme text-main-theme">
                    <div id="edit_history_content"></div>
                </div>
                <div class="modal-footer border-0 bg-body-theme" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Admin Edit Modal --}}
    <div class="modal fade" id="editOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow bg-card-theme" style="border-radius: 15px;">
                <div class="modal-header border-0 py-3" style="background: linear-gradient(135deg, #1e293b, #0f172a); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <h5 class="modal-title fw-bold text-white mb-0" style="color: #ffffff !important;"><i class="fa fa-edit me-2"></i>Edit Order</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editOrderForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4 bg-body-theme text-main-theme">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold text-muted-theme small">Distributor</label>
                                <select name="distributor_id" id="edit_distributor_id" class="form-select bg-card-theme text-main-theme" style="border-radius: 8px; border-color: var(--med-border-light);">
                                    <option value="">-- None --</option>
                                    @foreach($distributors as $d) <option value="{{ $d->id }}">{{ $d->shop_name }} ({{ $d->user->name ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="retailer_id" id="edit_retailer_id">
                            <input type="hidden" name="status" id="edit_status">
                        </div>

                        {{-- Items Section --}}
                        <div class="mb-4 p-4 rounded-3 border-0 bg-card-theme shadow-sm animate__animated animate__fadeIn" style="border: 1px solid var(--med-border-light, #e2e8f0) !important;">
                            <h6 class="fw-bold mb-3 text-main-theme"><i class="fa fa-shopping-cart me-2"></i>Order Items</h6>
                            <div class="mb-3">
                                <div class="d-flex gap-2 align-items-stretch">
                                    <select id="edit_product_select" class="form-select bg-card-theme text-main-theme flex-grow-1" style="border-radius: 8px; border-color: var(--med-border-light);">
                                        <option value="">Select Product to Add</option>
                                        @foreach($products as $p) <option
                                            value="{{ $p->id }}"
                                            data-price="{{ $p->ptr }}"
                                            data-gst="{{ $p->gst ?? 0 }}"
                                            data-pack="{{ $p->pack ?? '' }}"
                                            data-code="{{ $p->product_code ?? '' }}"
                                            data-boxsize="{{ $p->box_size ?? '' }}"
                                            data-stripsperbox="{{ $p->strips_per_box ?? 10 }}"
                                            data-variants="{{ $p->has_variants ? json_encode($p->variant_options) : '' }}">
                                            {{ $p->product_name }}
                                        </option> @endforeach
                                    </select>
                                    <button type="button" class="btn btn-primary fw-bold px-4" id="btn_add_prod_edit" style="border-radius: 8px; background: var(--med-primary, #2563eb); border: none; white-space: nowrap;">
                                        <i class="fa fa-plus me-1"></i>Add
                                    </button>
                                </div>
                                {{-- Variant picker shown dynamically --}}
                                <div id="edit_variant_picker" class="mt-2" style="display:none;">
                                    <div id="edit_variant_levels"></div>
                                    <input type="hidden" id="edit_selected_variant" value="">
                                    <small class="text-danger d-none" id="edit_variant_warn">Please select a variant first.</small>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle table-hover text-main-theme" id="edit_items_table">
                                    <thead style="background-color: #1e293b; color: #ffffff !important;">
                                        <tr>
                                            <th style="color: #ffffff !important;">Product</th>
                                            <th style="color: #ffffff !important; width: 100px;" class="text-center">Unit</th>
                                            <th style="color: #ffffff !important; width: 90px;" class="text-center">Qty</th>
                                            <th style="color: #ffffff !important; width: 110px;" class="text-end">Price (PTR)</th>
                                            <th style="color: #ffffff !important; width: 80px;" class="text-end">GST %</th>
                                            <th style="color: #ffffff !important; width: 130px;" class="text-end">Total (With GST)</th>
                                            <th style="color: #ffffff !important; width: 60px;" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <td colspan="5" class="text-end fw-bold text-main-theme">Grand Total:</td>
                                            <td id="edit_grand_total" class="text-end fw-bold text-primary" style="color: var(--med-primary, #2563eb) !important;">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted-theme small">Delivery Notes</label>
                            <textarea name="delivery_notes" id="edit_delivery_notes" class="form-control bg-card-theme text-main-theme" rows="3" style="border-radius: 8px; border-color: var(--med-border-light);"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-card-theme" style="border-top: 1px solid var(--med-border-light, #e2e8f0) !important; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                        <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal" style="border-radius: 8px; border: 1px solid var(--med-border-light); background: transparent; color: var(--med-text-main);">CANCEL</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius: 8px; background: var(--med-primary, #2563eb); border: none;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            const INITIAL_STATUS = "{{ $defaultStatus }}";
            window.currentStatus = INITIAL_STATUS;
            window.initialTabSelected = false;

            const isFieldStaff = {{ Auth::user()->hasRole('fieldstaff') ? 'true' : 'false' }};
            const isDistributor = {{ Auth::user()->hasRole('distributor') ? 'true' : 'false' }};
            const isRetailer = {{ Auth::user()->hasRole('retailer') ? 'true' : 'false' }};
            const isSalesManager = {{ Auth::user()->hasRole('salesmanager') ? 'true' : 'false' }};
            const isAdmin = {{ Auth::user()->hasAnyRole(['admin', 'superadmin']) ? 'true' : 'false' }};

            let exportCols = [1, 2, 3, 13, 14, 15, 16, 17, 18, 19, 20, 8, 4, 5, 6, 7, 9, 10];
            let exportOptions = {
                columns: exportCols,
                format: {
                    body: function(data, row, column, node) {
                        let originalColIdx = exportCols[column];
                        let tableApi = $('#retailer-approval-table').DataTable();
                        let rowData = null;
                        try {
                            rowData = tableApi.row(row).data();
                        } catch(e) {}
                        
                        if (originalColIdx === 3) {
                            if (rowData) return (rowData.retailer_name || '').trim();
                            let temp = document.createElement('div');
                            temp.innerHTML = data;
                            let span = temp.querySelector('span.text-primary');
                            return span ? span.innerText.trim() : temp.innerText.trim();
                        }
                        if (originalColIdx === 4) {
                            if (rowData && rowData.product_summary) {
                                return rowData.product_summary.split('|||').map(it => it.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim()).join('\n');
                            }
                            return data ? data.toString().replace(/<\/div>/gi, '\n').replace(/<[^>]*>?/gm, '').trim() : '';
                        }
                        if (originalColIdx === 5) {
                            if (rowData && rowData.brand_summary) {
                                return rowData.brand_summary.split('|||').join('\n');
                            }
                            return data ? data.toString().replace(/<\/div>/gi, '\n').replace(/<[^>]*>?/gm, '').trim() : '';
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

            var table = $('#retailer-approval-table').DataTable({
                order: [
                    [0, 'desc']
                ],
                autoWidth: false,
                columnDefs: [
                    {
                        targets: 4,
                        className: 'product-col',
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
                initComplete: function () {
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
                columns: [{
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
                {
                    data: 'order_code',
                    name: 'order_code',
                    render: function (data, type, row) {
                        if (type !== 'display') return data;
                        let html = `<span class="fw-bold">${data}</span>`;
                        if (row.metadata && row.metadata.is_edited) {
                            let lastEditor = row.metadata.last_edited_by || 'Staff';
                            let lastTime = row.metadata.last_edited_at || '';
                            html += ` <span class="badge bg-warning text-dark ms-1 view-edit-history" style="font-size: 0.65rem; padding: 0.25em 0.5em; vertical-align: middle; cursor: pointer; border-radius: 4px;" title="Click to view edit history" data-id="${row.id}"><i class="fa fa-pencil"></i> EDITED</span>`;
                        }
                        return html;
                    }
                },
                {
                    data: 'retailer_name',
                    name: 'retailer.user.name',
                    render: function(data, type, row) {
                        if (type !== 'display') return data;
                        let html = `<div class="d-flex flex-column">
                                        <span class="fw-bold text-primary">${data}</span>
                                        <span class="small text-muted" style="font-size: 0.7rem;"><i class="fa fa-map-marker-alt"></i> ${row.retailer_area || 'N/A'}, ${row.retailer_district || 'N/A'}</span>
                                    </div>`;
                        return html;
                    }
                },
                {
                    data: 'product_summary',
                    name: 'product_summary',
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
                        } else if (status === 'cancelled' || status === 'rejected') {
                            let estAmt = (row.metadata && row.metadata.estimated_amount !== undefined) ? parseFloat(row.metadata.estimated_amount).toFixed(2) : parseFloat(data).toFixed(2);
                            return `<div class="d-flex flex-column">
                                <span class="fw-bold text-muted" style="text-decoration: line-through;">₹${parseFloat(data).toFixed(2)}</span>
                                <span class="text-muted small" style="font-size: 0.65rem; font-weight: 500; opacity: 0.85; margin-top: 2px;">Est: ₹${estAmt}</span>
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
                    data: 'placed_at',
                    name: 'placed_at'
                },
                {
                    data: 'distributor_name',
                    name: 'distributor.user.name',
                    visible: isAdmin || isSalesManager || isFieldStaff,
                    render: function(data, type, row) {
                        if (type !== 'display') return data;
                        return `<span class="fw-bold text-primary">${data}</span>`;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data, type, row) {
                        if (type !== 'display') return row.status;
                        let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';
                        let bgClass = 'bg-secondary text-white';
                        let displayStatus = row.status;

                        if (statusRaw === 'pending') {
                            bgClass = 'bg-secondary text-white';
                            displayStatus = 'Pending';
                        }
                        else if (statusRaw === 'processing') {
                            bgClass = 'bg-warning text-white';
                            displayStatus = 'Processing';
                        }
                        else if (statusRaw === 'approved') {
                            bgClass = 'bg-info text-white';
                            displayStatus = 'Approved';
                        }
                        else if (statusRaw === 'delivered') bgClass = 'bg-success text-white';
                        else if (statusRaw === 'cancelled') bgClass = 'bg-danger text-white';
                        else if (statusRaw === 'rejected') bgClass = 'bg-dark-red text-white';

                        return `<span class="badge ${bgClass}" style="font-size: 0.8rem; padding: 0.5em 0.9em; font-weight: 600;">${displayStatus}</span>`;
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    render: function (data, type, row) {
                        if (type !== 'display') return row.payment_status;
                        let payStatus = row.payment_status ? row.payment_status.toLowerCase() : 'pending';
                        let bgClass, displayLabel;

                        if (payStatus === 'paid') {
                            bgClass = 'bg-success text-white';
                            displayLabel = 'Paid';
                        } else {
                            bgClass = 'bg-secondary text-white';
                            displayLabel = 'Pending';
                        }

                        let canChangePayment = isAdmin || isDistributor;
                        let cursorStyle = canChangePayment ? 'cursor: pointer;' : '';
                        let clickableClass = canChangePayment ? 'change-payment-status' : '';

                        return `<span class="badge ${bgClass} ${clickableClass}" data-id="${row.id}" data-status="${payStatus}" style="font-size: 0.8rem; padding: 0.5em 0.9em; font-weight: 600; ${cursorStyle}">${displayLabel}</span>`;
                    }
                },
                {
                    data: null,
                    name: 'invoice',
                    className: 'no-export',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (row.invoice_url) {
                            let ext = row.invoice_url.split('.').pop().toLowerCase();
                            let icon = ext === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-image-o';
                            let btnsHtml = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="d-flex align-items-center gap-1 p-2">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <a href="${row.invoice_url}" target="_blank" class="btn btn-sm btn-success" title="View Invoice">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fa ${icon}"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </a>`;
                            btnsHtml += `</div>`;
                            return btnsHtml;
                        }
                        if (isFieldStaff) return '<span class="text-muted small">No Invoice</span>';

                        let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';
                        if (statusRaw === 'pending') {
                            return '<span class="text-muted small">Waiting for Sales Rep Approval</span>';
                        }

                        return `<span class="text-muted small">Pending Approval</span>`;
                    }
                },
                {
                    data: null,
                    className: 'no-export',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');
                        let btns = `<div class="action-buttons">`;

                        // Tiered Approval Buttons
                        let canApprove = false;
                        if ((isFieldStaff || isSalesManager || isAdmin) && statusRaw === 'pending') canApprove = true;
                        if ((isDistributor || isSalesManager || isAdmin) && statusRaw === 'processing') canApprove = true;

                        if (canApprove) {
                            if ((isDistributor || isAdmin || (isSalesManager && statusRaw === 'processing')) && statusRaw === 'processing') {
                                btns += `<button class="btn btn-success btn-sm distributor-approve-btn" data-row="${rowData}" title="Approve & Allocate Batches"><i class="fa fa-check-circle"></i></button>`;
                            } else {
                                btns += `<button class="btn btn-success btn-sm approve-retailer-btn" data-id="${row.id}" data-row="${rowData}" title="Approve"><i class="fa fa-check"></i></button>`;
                            }
                            btns += `<button class="btn btn-danger btn-sm reject-retailer-btn" data-id="${row.id}" title="Reject"><i class="fa fa-times-circle"></i></button>`;
                        }

                        // Edit Order Button (Only inside approvals)
                        let canEdit = false;
                        if (isAdmin) {
                            canEdit = true;
                        } else if (isFieldStaff && (statusRaw === 'pending' || statusRaw === 'processing')) {
                            if (row.fieldstaff_id == authFieldStaffId || row.retailer_fs_id == authFieldStaffId) {
                                canEdit = true;
                            }
                        } else if (isSalesManager && (statusRaw === 'pending' || statusRaw === 'processing')) {
                            if (row.sales_manager_id == authSalesManagerId || row.retailer_sm_id == authSalesManagerId) {
                                canEdit = true;
                            }
                        }

                        if (canEdit && statusRaw !== 'delivered' && statusRaw !== 'cancelled' && statusRaw !== 'rejected' && statusRaw !== 'approved') {
                            btns += `<button class="btn btn-primary btn-sm edit-btn" data-row="${rowData}" title="Edit Order"><i class="fa fa-edit"></i></button>`;
                        }

                        btns += `<button class="btn btn-info btn-sm view-details-btn" data-row="${rowData}" title="View Details"><i class="fa fa-eye"></i></button>`;
                        let invoiceUrl = "{{ route('admin.retailer.invoice', ':id') }}".replace(':id', row.id);
                        btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="Print Invoice"><i class="fa fa-print"></i></a>`;

                        // Retailer Confirmation
                        if (statusRaw === 'approved') {
                            if (isRetailer) {
                                btns += `<button class="btn btn-primary btn-sm confirm-receipt-btn" data-id="${row.id}" title="Confirm Order"><i class="fa fa-check-square"></i></button>`;
                            }
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
                    data: 'retailer_area',
                    visible: false,
                    render: function(d) { return d || '-'; }
                },
                {
                    data: 'retailer_district',
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
                ]
            });

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $('#orderStatusTabs .nav-link').removeClass('text-primary border-bottom-0').addClass('text-muted');
                $(this).removeClass('text-muted').addClass('text-primary border-bottom-0');
                table.ajax.reload();
            });

            $('input[name="payment_status"]').on('change', function () {
                table.ajax.reload();
            });
            // Consolidated Batch Form Validation and Submission
            $(document).on('submit', '#distributorApproveForm', function (e) {
                e.preventDefault();
                let $form = $(this);
                let isValid = true;

                // Validate each product row
                $('.product-row').each(function () {
                    let orderItemId = $(this).data('item-id');
                    let ordered = parseInt($(this).data('ordered-qty'));
                    let hContainer = $(`#batches_for_${orderItemId}`);
                    let allocated = parseInt(hContainer.find('.hidden-qty-val').val() || 0);
                    let vRow = $(`#v_row_${orderItemId}`);

                    if (allocated < ordered) {
                        isValid = false;
                        vRow.addClass('bg-danger-subtle border-danger');
                    } else {
                        vRow.removeClass('bg-danger-subtle border-danger');
                    }

                    let batchVal = hContainer.find('.hidden-batch-val').val();
                    if (!batchVal) {
                        isValid = false;
                        vRow.find('.v-batch-display').addClass('text-danger fw-bold').text('Missing');
                    } else {
                        vRow.find('.v-batch-display').removeClass('text-danger');
                    }

                    let expVal = hContainer.find('.hidden-expiry-val').val();
                    if (!expVal) {
                        isValid = false;
                        vRow.find('.v-expiry-display').addClass('text-danger fw-bold').text('Missing');
                    } else {
                        vRow.find('.v-expiry-display').removeClass('text-danger');
                    }
                });

                if (!isValid) {
                    showToast('error', 'Please ensure all items have filled out batches, expiries, and quantities matching the ordered amounts.');
                    return;
                }

                // Check if invoice is uploaded
                let scanFileInput = document.getElementById('scan_retailer_file_input');
                if (!scanFileInput.files || scanFileInput.files.length === 0) {
                    showToast('error', 'Invoice document is strictly required for approval.');
                    return;
                }

                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('invoice', scanFileInput.files[0]);

                let orderId = $('#approve_order_id').val();
                let url = "{{ route('admin.retailer.accept', ':id') }}".replace(':id', orderId);

                let $btn = $form.find('button[type="submit"]');
                let oldHtml = $btn.html();
                $btn.html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

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
                            showToast('success', res.success);
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                            Swal.fire({
                                icon: 'success',
                                title: 'Order Approved!',
                                text: 'Stock has been allocated and status updated successfully.',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else {
                            $('#retailer_approval_error_message').html(res.error || 'Failed to approve order');
                            $('#retailer_approval_error_alert').removeClass('d-none').show();
                            $('#distributorApproveModal').animate({ scrollTop: 0 }, 'slow');
                        }
                    },
                    error: function (xhr) {
                        let errMsg = xhr.responseJSON ? xhr.responseJSON.error || xhr.responseJSON.message : 'An error occurred during approval.';
                        $('#retailer_approval_error_message').html(errMsg);
                        $('#retailer_approval_error_alert').removeClass('d-none').show();
                        $('#distributorApproveModal').animate({ scrollTop: 0 }, 'slow');
                    },
                    complete: function () {
                        $btn.html(oldHtml).prop('disabled', false);
                    }
                });
            });

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
                                showToast('success', res.success);
                                if (window.updateSidebarCounts) window.updateSidebarCounts();
                            } else {
                                showToast('error', res.error || 'Failed to confirm order');
                            }
                        }).fail(function (xhr) {
                            showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                        });
                    }
                });
            });

            // Reject Retailer Order handler
            $(document).on('click', '.reject-retailer-btn', function () {
                let id = $(this).data('id');
                let tr = $(this).closest('tr');
                if ($(tr).hasClass('child')) tr = $(tr).prev();
                let row = table.row(tr).data();

                if (!id) {
                    // Fallback to hidden input if clicked from inside a modal that sets #approve_order_id
                    id = $('#approve_order_id').val();
                }
                if (!id) {
                    id = $('#distributor_approve_order_id').val();
                }
                
                if (!id) {
                    showToast('error', 'Order ID not found for rejection.');
                    return;
                }

                $('#reject_retailer_order_id').val(id);
                $('#reject_order_code_display').text(row ? '#' + row.order_code : '');
                $('#rejectRetailerOrderForm').find('textarea[name="rejection_reason"]').val('');
                
                // Hide any open approval modals if they exist
                $('#approveRetailerOrderModal').modal('hide');
                $('#distributorApproveModal').modal('hide');
                $('#rejectRetailerOrderModal').modal('show');
            });

            $(document).on('submit', '#rejectRetailerOrderForm', function (e) {
                e.preventDefault();
                let $form = $(this);
                let id = $('#reject_retailer_order_id').val();
                let reason = $form.find('textarea[name="rejection_reason"]').val().trim();

                if (!reason) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Reason Required',
                        text: 'Please provide a valid reason for rejecting this order.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                let $btn = $form.find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rejecting...');

                $.ajax({
                    url: "{{ route('admin.retailer.reject', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: $form.serialize(),
                    success: function (res) {
                        $('#rejectRetailerOrderModal').modal('hide');
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
            $('#payment_status_filter').change(function () {
                table.ajax.reload();
            });

            // Update Payment Status (Admin only - click badge)
            $(document).on('click', '.change-payment-status', function () {
                let id = $(this).data('id');
                let status = $(this).data('status') || 'pending';
                $('#retailer_payment_order_id').val(id);
                $('#retailer_payment_status_select').val(status.toLowerCase());
                $('#retailerPaymentStatusModal').modal('show');
            });

            $('#retailerPaymentStatusForm').submit(function (e) {
                e.preventDefault();
                let id = $('#retailer_payment_order_id').val();
                let $btn = $(this).find('button[type="submit"]');
                let oldText = $btn.text();
                $btn.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: "{{ route('admin.retailer.update-payment-status', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        payment_status: $('#retailer_payment_status_select').val()
                    },
                    success: function (res) {
                        $('#retailerPaymentStatusModal').modal('hide');
                        showToast('success', res.success || 'Updated');
                        table.ajax.reload(null, false);
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                    },
                    error: function (xhr) {
                        let errMsg = 'Update failed';
                        if (xhr.responseJSON) {
                            errMsg = xhr.responseJSON.error || xhr.responseJSON.message || errMsg;
                        }
                        showToast('error', errMsg);
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text(oldText);
                    }
                });
            });

            // Status Update Logic
            let statusChangeData = null;
            $(document).on('change', '.status-select', function (e) {
                let $select = $(this);
                let id = $select.data('id');
                let newStatus = $select.val();
                let originalStatus = $select.data('original');
                let statusText = $select.find('option:selected').text();

                // Store info for confirmation
                statusChangeData = {
                    id: id,
                    newStatus: newStatus,
                    originalStatus: originalStatus,
                    $select: $select
                };

                $('#new-status-label').text(statusText);
                $('#statusChangeConfirmModal').modal('show');

                // Revert temporarily until confirmed
                $select.val(originalStatus);
            });

            $('#confirmStatusChangeBtn').click(function () {
                if (!statusChangeData) return;

                let {
                    id,
                    newStatus,
                    originalStatus,
                    $select
                } = statusChangeData;
                let $modalBtn = $(this);
                let oldBtnHtml = $modalBtn.html();

                $modalBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
                $select.prop('disabled', true);

                let url = "{{ route('admin.retailer.update-status', ':id') }}".replace(':id', id);
                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    status: newStatus
                }, function (res) {
                    $('#statusChangeConfirmModal').modal('hide');
                    if (res.success) {
                        $select.val(newStatus);
                        $select.removeClass('bg-warning bg-secondary bg-success bg-danger bg-info text-dark text-white');
                        let newClass = 'bg-secondary text-white';
                        if (newStatus.includes('pending')) newClass = 'bg-secondary text-white';
                        else if (newStatus.includes('processing')) newClass = 'bg-custom-yellow text-white';
                        else if (newStatus.includes('approved')) newClass = 'bg-info text-white';
                        else if (newStatus.includes('delivered')) newClass = 'bg-success text-white';
                        else if (newStatus.includes('cancelled')) newClass = 'bg-danger text-white';

                        $select.addClass(newClass);
                        $select.data('original', newStatus);
                        showToast('success', res.success);
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                    } else {
                        showToast('error', res.error || 'Failed to update status');
                        $select.val(originalStatus);
                    }
                }).fail(function (xhr) {
                    $('#statusChangeConfirmModal').modal('hide');
                    showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                    $select.val(originalStatus);
                }).always(function () {
                    $modalBtn.html(oldBtnHtml).prop('disabled', false);
                    $select.prop('disabled', false);
                    statusChangeData = null;
                });
            });

            // Payment Status Update Logic
            $(document).on('change', '.payment-status-select', function (e) {
                let $select = $(this);
                let id = $select.data('id');
                let newStatus = $select.val();
                let originalStatus = $select.data('original');

                $select.prop('disabled', true);

                let url = "{{ route('admin.retailer.update-payment-status', ':id') }}".replace(':id', id);
                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    payment_status: newStatus
                }, function (res) {
                    if (res.success) {
                        $select.removeClass('bg-warning bg-secondary bg-success bg-danger text-dark text-white');
                        let newClass = 'bg-secondary text-white';
                        if (newStatus == 'pending') newClass = 'bg-secondary text-white';
                        else if (newStatus == 'paid') newClass = 'bg-success text-white';
                        else if (newStatus == 'failed') newClass = 'bg-danger text-white';

                        $select.addClass(newClass);
                        $select.data('original', newStatus);

                        showToast('success', res.success);
                    } else {
                        showToast('error', res.error || 'Failed to update payment status');
                        $select.val(originalStatus);
                    }
                }).fail(function (xhr) {
                    showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                    $select.val(originalStatus);
                }).always(function () {
                    $select.prop('disabled', false);
                });
            });

            let currentOrderIdForInvoice = null;
            $(document).on('click', '.upload-invoice-btn', function () {
                let id = $(this).data('id');
                currentOrderIdForInvoice = id;
                $('#invoice_upload_input').click();
            });

            $('#invoice_upload_input').change(function () {
                if (!this.files.length) return;
                let file = this.files[0];
                let formData = new FormData();
                formData.append('invoice', file);
                formData.append('_token', '{{ csrf_token() }}');

                let $btn = $(`.upload-invoice-btn[data-id="${currentOrderIdForInvoice}"]`);
                let oldHtml = $btn.html();
                $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.retailer.upload-invoice', ':id') }}".replace(':id', currentOrderIdForInvoice),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        showToast('success', res.success);
                        table.ajax.reload(null, false);
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Upload failed');
                        $btn.html(oldHtml).prop('disabled', false);
                    }
                });
                $(this).val('');
            });

            // Dropzone & File Input Logic for Retailer Approval
            $(document).on('click', '#retailer_invoice_dropzone', function () {
                $('#retailer_invoice_file_input').click();
            });

            $(document).on('change', '#retailer_invoice_file_input', function () {
                let file = this.files[0];
                if (file) {
                    $('#file_preview_name').text('Selected: ' + file.name).removeClass('d-none');
                    $('#retailer_invoice_dropzone').addClass('has-file');
                    $('#dropzone_title').text('File Selected');
                    $('#dropzone_subtitle').text('Click or drag to replace: ' + file.name);
                } else {
                    $('#file_preview_name').addClass('d-none');
                    $('#retailer_invoice_dropzone').removeClass('has-file');
                    $('#dropzone_title').text('Click or Drag & Drop to Upload Invoice');
                    $('#dropzone_subtitle').text('Supported formats: PDF, JPG, PNG (Max 5MB)');
                }
            });

            // Handle Drag & Drop
            const dropzone = document.getElementById('retailer_invoice_dropzone');
            if (dropzone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                    }, false);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, () => dropzone.classList.add('bg-primary-subtle'), false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, () => dropzone.classList.remove('bg-primary-subtle'), false);
                });

                dropzone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    document.getElementById('retailer_invoice_file_input').files = files;
                    $('#retailer_invoice_file_input').trigger('change');
                }, false);
            }

            // Remove Invoice Logic
            let removeInvoiceId = null;
            $(document).on('click', '.remove-invoice-btn', function () {
                removeInvoiceId = $(this).data('id');
                $('#removeInvoiceConfirmModal').modal('show');
            });

            $('#confirmRemoveInvoiceBtn').click(function () {
                if (!removeInvoiceId) return;

                let $modalBtn = $(this);
                let oldModalBtnHtml = $modalBtn.html();
                $modalBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                let url = "{{ route('admin.retailer.remove-invoice', ':id') }}".replace(':id', removeInvoiceId);
                $.post(url, {
                    _token: '{{ csrf_token() }}'
                }, function (res) {
                    $('#removeInvoiceConfirmModal').modal('hide');
                    if (res.success) {
                        showToast('success', res.success);
                        table.ajax.reload(null, false);
                    } else {
                        showToast('error', res.error || 'Failed to remove invoice');
                    }
                }).fail(function (xhr) {
                    $('#removeInvoiceConfirmModal').modal('hide');
                    showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                }).always(function () {
                    $modalBtn.html(oldModalBtnHtml).prop('disabled', false);
                });
            });

            // Improved View Modal Logic
            $(document).on('click', '.view-details-btn', function () {
                let row = $(this).data('row');

                // Header Info
                $('#view_order_code').text(row.order_code);
                $('#view_placed_at').text(row.placed_at || '--');
                $('#view_invoice_no').text(row.invoice_no || '--');
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
                $('#view_payment_status_badge').text(payStatus === 'paid' ? 'PAID' : 'UNPAID').removeClass().addClass('badge ' + payBadgeClass).css({'border': '1px solid currentColor', 'background': 'transparent', 'font-size': '0.7rem'});

                // Retailer Info
                $('#view_retailer_name').text(row.retailer_name || '--');
                $('#view_retailer_phone').text(row.retailer_phone || '--');
                $('#view_retailer_address').text(row.retailer_address || '--');

                // Distributor Info
                $('#view_distributor_name').text(row.distributor_name || '--');
                $('#view_distributor_phone').text(row.distributor_phone || '--');

                // Items Table
                let tbody = $('#view_items_body');
                tbody.empty();

                if (row.items && row.items.length) {
                    row.items.forEach(item => {
                        let batchInfo = item.batches && item.batches.length ?
                            item.batches.map(b => `<div class="mb-1 last-child-mb-0"><span class="badge bg-soft-info text-info border-0 px-2 py-1" style="font-size: 0.85rem; font-weight: 800; letter-spacing: 0.5px;">${b.batch_no}</span><div class="text-muted d-block" style="font-size: 0.75rem; margin-top: 1px;">Exp: ${b.expiry_date}</div></div>`).join('') :
                            '<span class="text-muted small">Not Allocated</span>';

                        let cleanedName = window.cleanProductName(item.product_name, item.side, item.size);
                        let variantBadge = window.renderProductVariantBadge(item);

                        tbody.append(`
                                                        <tr class="align-middle" style="border-bottom: 1px solid var(--med-border-light, #f1f5f9);">
                                                            <td class="py-2 ps-4">
                                                                <div class="fw-bold text-main-theme mb-0" style="font-size: 0.9rem; white-space: normal; line-height: 1.2;">
                                                                    ${cleanedName} ${variantBadge}
                                                                </div>
                                                                <div class="small text-muted-theme" style="font-size: 0.7rem;">
                                                                    ${item.brand ? `<span class="fw-bold">(${item.brand})</span> •` : ''} 
                                                                </div>
                                                                <div class="d-flex gap-2 flex-wrap mt-0 opacity-75" style="font-size: 0.6rem;">
                                                                    ${item.generic_name ? `<span>${item.generic_name}</span>` : ''}
                                                                    ${item.pack && item.pack.trim() ? `<span>• ${item.pack}</span>` : ''}
                                                                    ${item.product_code && item.product_code !== '---' && item.product_code !== 'N/A' ? `<span>Code: ${item.product_code}</span>` : ''}
                                                                </div>
                                                            </td>
                                                            <td class="text-main-theme" style="font-size: 0.75rem;">${batchInfo}</td>
                                                            <td class="text-center fw-bold text-primary" style="font-size: 0.85rem;">
                                                                ${item.quantity} ${item.unit || 'Nos'}
                                                            </td>
                                                            <td class="text-center fw-bold text-success" style="font-size: 0.85rem;">
                                                                ${item.free_quantity > 0 ? item.free_quantity : '-'}
                                                            </td>
                                                            <td class="text-end fw-bold text-main-theme" style="font-size: 0.85rem;">₹${parseFloat(item.unit_price).toFixed(2)}</td>
                                                            <td class="text-end pe-4 fw-bold text-main-theme" style="font-size: 0.85rem;">₹${parseFloat(item.total_amount).toFixed(2)}</td>
                                                        </tr>
                                                    `);
                    });
                } else {
                    tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted-theme italic">No items found in this order</td></tr>');
                }

                $('#view_grand_total').text(`₹${parseFloat(row.total_amount).toFixed(2)}`);
                $('#view_notes').text(row.delivery_notes || 'No notes provided.').addClass('text-main-theme');

                $('#viewOrderModal').modal('show');
            });

            // Approve Retailer Order (Field Staff / Manager / Distributor)
            $(document).on('click', '.approve-retailer-btn', function () {
                let id = $(this).data('id');
                let row = $(this).data('row');

                const proceed = () => {
                    $('#approve_retailer_order_id').val(id);
                    $('#retailer_approve_order_code_display').text(row.order_code);
                    $('#retailer_approve_order_date_display').text(row.placed_at);
                    $('#retailer_approve_retailer_display').text(row.retailer_name || '--');
                    $('#retailer_approve_total_display').text(row.total_amount || '₹0');

                    // Populate more retailer details
                    $('#retailer_approve_phone_display').text(row.retailer_phone || '--');

                    let status = (row.status || 'pending').toLowerCase();
                    let badgeClass = 'bg-secondary text-white';
                    if (status === 'pending') badgeClass = 'bg-secondary text-white';
                    else if (status === 'processing') badgeClass = 'bg-warning text-white';
                    else if (status === 'approved') badgeClass = 'bg-info text-white';
                    else if (status === 'delivered') badgeClass = 'bg-success text-white';
                    else if (status === 'cancelled') badgeClass = 'bg-danger text-white';
                    else if (status === 'rejected') badgeClass = 'bg-dark-red text-white';
                    $('#approve_retailer_status_badge').text(row.status || 'Pending').removeClass().addClass('badge ' + badgeClass).css('font-size', '0.7rem');

                    let payStatus = (row.payment_status || 'pending').toLowerCase();
                    let payBadgeClass = payStatus === 'paid' ? 'text-success' : 'text-warning';
                    $('#approve_retailer_payment_status_badge').text(payStatus === 'paid' ? 'PAID' : 'UNPAID').removeClass().addClass('badge ' + payBadgeClass).css({'border': '1px solid currentColor', 'background': 'transparent', 'font-size': '0.7rem'});

                    if (row.retailer_gstin) {
                        $('#retailer_approve_gstin_display').text(row.retailer_gstin);
                        $('#retailer_approve_gstin_container').removeClass('d-none');
                    } else {
                        $('#retailer_approve_gstin_container').addClass('d-none');
                    }

                    let list = $('#retailer_approve_items_list');
                    list.empty();

                    if (row.items && row.items.length) {
                        row.items.forEach(item => {
                            let batchesHtml = '';
                            if (item.batches && item.batches.length > 0) {
                                batchesHtml = `<div class="mt-1 d-flex flex-wrap gap-2">` + item.batches.map(b => `
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-25" style="font-size: 0.65rem;">
                                        <i class="fa fa-barcode me-1"></i>Batch: ${b.batch_no} | Exp: ${b.expiry_date}
                                    </span>
                                `).join('') + `</div>`;
                            }

                            list.append(`
                                <div class="invoice-list-row">
                                    <div style="flex: 2; max-width: 200px;" class="fw-bold text-main-theme">
                                        <div style="white-space: normal; line-height: 1.3;">
                                            ${window.cleanProductName(item.product_name, item.side, item.size)} ${window.renderProductVariantBadge(item)}
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap mt-1">
                                            ${item.generic_name ? `<span class="badge bg-light text-dark border-0 fw-normal" style="font-size: 0.6rem;">${item.generic_name}</span>` : ''}
                                            ${item.product_code && item.product_code !== '---' && item.product_code !== 'N/A' ? `<div class="small text-muted-theme opacity-75" style="font-size: 0.65rem;">C: ${item.product_code}</div>` : ''}
                                        </div>
                                        ${batchesHtml}
                                    </div>
                                    <div style="flex: 1;" class="text-center text-muted-theme small">
                                        ${item.quantity} ${item.unit || 'Box'}
                                    </div>
                                    <div style="flex: 1;" class="text-center fw-bold text-success small">
                                        ${item.free_quantity > 0 ? item.free_quantity : '-'}
                                    </div>
                                    <div style="flex: 1;" class="text-end fw-bold text-success">₹${item.total_amount}</div>
                                </div>
                            `);
                        });
                    } else {
                        list.append('<div class="invoice-list-row justify-content-center text-muted">No items found</div>');
                    }

                    // Reset dropzone state
                    $('#file_preview_name').addClass('d-none');
                    $('#retailer_invoice_dropzone').removeClass('has-file');
                    $('#dropzone_title').text('Click or Drag & Drop to Upload Invoice');
                    $('#dropzone_subtitle').text('Supported formats: PDF, JPG, PNG (Max 5MB)');

                    $('#approveRetailerOrderForm')[0].reset();
                    $('#modal_reject_retailer_btn').data('id', id);

                    if (isFieldStaff) {
                        $('#invoiceUploadGroup').hide();
                        $('#retailer_invoice_file_input').prop('required', false);
                        $('#approveModalTextContainer').removeClass('d-none');
                        $('#approveModalText').text('Review the order items above. Are you sure you want to approve this order?');
                    } else {
                        $('#invoiceUploadGroup').show();
                        $('#retailer_invoice_file_input').prop('required', true);
                        $('#approveModalTextContainer').addClass('d-none');
                    }

                    if (status === 'pending') {
                        $('#approval_validation_errors').addClass('d-none');
                    } else {
                        $('#approval_validation_errors').removeClass('d-none');
                    }

                    $('#approveRetailerOrderModal').modal('show');
                };

                proceed();
                if (false) {
                    let targetRole = (row.status || '').toLowerCase() === 'pending' ? 'field staff' : 'distributor';
                    Swal.fire({
                        title: 'Confirm Admin Approval',
                        text: `This order should ideally be approved by the respective ${targetRole}. Do you still want to proceed as an Admin?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, proceed',
                        cancelButtonText: 'No, cancel',
                        confirmButtonColor: '#28a745'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            proceed();
                        }
                    });
                } else {
                    proceed();
                }
            });

            $('#approveRetailerOrderForm').submit(function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#approve_retailer_order_id').val();
                let url = "{{ route('admin.retailer.accept', ':id') }}".replace(':id', id);

                let $btn = $(this).find('button[type="submit"]');
                let oldHtml = $btn.html();

                // Manual check for invoice if required
                let invoiceInput = $(this).find('input[name="invoice"]')[0];
                if (invoiceInput && invoiceInput.required && !invoiceInput.files.length) {
                    showToast('error', 'Invoice document is strictly required.');
                    return;
                }

                $btn.html('<i class="fa fa-spinner fa-spin"></i> Approving...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            $('#approveRetailerOrderModal').modal('hide');
                            table.ajax.reload(null, false);
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.success || 'Order approved successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            showToast('error', res.error || 'Failed to approve order');
                        }
                    },
                    error: function (xhr) {
                        let errMsg = 'Approval failed';
                        if (xhr.responseJSON) {
                            errMsg = xhr.responseJSON.error || xhr.responseJSON.message || errMsg;
                        }
                        showToast('error', errMsg);
                    },
                    complete: function () {
                        $btn.html(oldHtml).prop('disabled', false);
                    }
                });
            });

            // --- Distributor Approve & Batch Allocation Logic ---
            $(document).on('click', '.distributor-approve-btn', function () {
                let row = $(this).data('row');

                const proceed = () => {
                    $('#approve_order_id').val(row.id);

                    // Populate new premium header fields
                    $('#dist_approve_order_code_display').text(row.order_code || '--');
                    $('#dist_approve_order_date_display').text(row.placed_at || '--');
                    $('#dist_approve_retailer_display').text(row.retailer_name || '--');
                    $('#dist_approve_location_display').text(row.retailer_location || row.retailer_address || '--');

                    let status = (row.status || 'pending').toLowerCase();
                    let badgeClass = 'bg-secondary text-white';
                    if (status === 'pending') badgeClass = 'bg-secondary text-white';
                    else if (status === 'processing') badgeClass = 'bg-warning text-white';
                    else if (status === 'approved') badgeClass = 'bg-info text-white';
                    else if (status === 'delivered') badgeClass = 'bg-success text-white';
                    else if (status === 'cancelled') badgeClass = 'bg-danger text-white';
                    else if (status === 'rejected') badgeClass = 'bg-dark-red text-white';
                    $('#dist_approve_status_badge').text(row.status || 'Pending').removeClass().addClass('badge ' + badgeClass).css('font-size', '0.7rem');

                    let payStatus = (row.payment_status || 'pending').toLowerCase();
                    
                    // Radio buttons are unchecked by default to force manual selection



                    let tbody = $('#batch_entry_body');
                    let vbody = $('#verification_table_body');

                    // Reset UI visibility statuses when modal is opened anew
                    $('#automation_idle_state').show();
                    $('#automation_success_state').hide();
                    $('#automation_error_state').addClass('d-none');
                    $('#ocr_processing_state').hide();
                    $('#ocr_idle_state').show();
                    $('#batch_allocation_table_container').addClass('d-none');
                    $('#scan_retailer_file_input').val(''); // Clear old file inputs
                    $('#retailer_approval_error_alert').removeClass('d-none');
                    $('#retailer_approval_error_message').text('Please select payment status and upload the finalized invoice to proceed.');
                    $('#btnSubmitDistributorApprove').prop('disabled', true);
                    
                    // Helper to check readiness
                    window.checkRetailerApprovalReadiness = function() {
                        let paySelected = $('input[name="payment_status"]:checked').length > 0;
                        let fileUploaded = $('#scan_retailer_file_input')[0].files.length > 0 || $('#batch_allocation_table_container').not('.d-none').length > 0;
                        
                        if (paySelected && fileUploaded) {
                            $('#retailer_approval_error_alert').addClass('d-none');
                            $('#btnSubmitDistributorApprove').prop('disabled', false);
                        } else {
                            $('#retailer_approval_error_alert').removeClass('d-none');
                            $('#btnSubmitDistributorApprove').prop('disabled', true);
                            
                            let msg = "";
                            if (!paySelected && !fileUploaded) msg = "Please select payment status and upload the finalized invoice to proceed.";
                            else if (!paySelected) msg = "Please select the payment status manually to continue.";
                            else if (!fileUploaded) msg = "Please upload and scan the invoice to continue.";
                            $('#retailer_approval_error_message').text(msg);
                        }
                    };

                    $('#distributorApproveModal').modal('show');

                    let itemsHtml = '';
                    let itemsProcessed = 0;

                    if (!row.items || row.items.length === 0) {
                        vbody.html('<div class="invoice-list-row justify-content-center text-danger">No items found in this order.</div>');
                        return;
                    }

                    tbody.empty();
                    vbody.empty();

                    row.items.forEach(function (item, index) {
                        let productId = item.product_id;
                        let orderItemId = item.order_item_id;
                        let orderedQty = item.quantity;

                        // 1. Hidden Submission Row
                        let rowHtml = `
                                                                                                                                                                                <div data-item-id="${orderItemId}" class="product-row" data-ordered-qty="${orderedQty}">
                                                                                                                                                                                    <div class="d-none">
                                                                                                                                                                                        <div class="fw-bold product-name-marker">${item.product_name} ${(item.side && item.side !== '-' && item.side !== 'N/A') ? '['+item.side+']' : ''} ${(item.size && item.size !== '-' && item.size !== 'N/A') ? '['+item.size+']' : ''}</div>
                                                                                                                                                                                        <input type="number" name="items[${orderItemId}][quantity]" value="${orderedQty}">
                                                                                                                                                                                        <input type="hidden" name="items[${orderItemId}][product_id]" value="${productId}">
                                                                                                                                                                                        <input type="hidden" name="items_batches[${orderItemId}][order_item_id]" value="${orderItemId}">
                                                                                                                                                                                    </div>
                                                                                                                                                                                    <div class="d-none" id="batches_for_${orderItemId}">
                                                                                                                                                                                        <input type="text" name="items_batches[${orderItemId}][batches][0][batch_no]" class="hidden-batch-val" required>
                                                                                                                                                                                        <input type="date" name="items_batches[${orderItemId}][batches][0][expiry_date]" class="hidden-expiry-val" required>
                                                                                                                                                                                        <input type="number" name="items_batches[${orderItemId}][batches][0][quantity]" class="hidden-qty-val" value="${orderedQty}" required>
                                                                                                                                                                                        <input type="hidden" name="items_batches[${orderItemId}][free_quantity]" class="hidden-free-val" value="0">
                                                                                                                                                                                        <input type="hidden" name="batches[${orderItemId}][0][mrp]" class="hidden-mrp-val">
                                                                                                                                                                                        <input type="hidden" name="batches[${orderItemId}][0][ptr]" class="hidden-ptr-val">
                                                                                                                                                                                        <input type="hidden" name="batches[${orderItemId}][0][pts]" class="hidden-pts-val">
                                                                                                                                                                                        <input type="hidden" name="batches[${orderItemId}][0][taxable_value]" class="hidden-taxable-val">
                                                                                                                                                                                        <input type="hidden" name="batches[${orderItemId}][0][cgst]" class="hidden-cgst-val">
                                                                                                                                                                                        <input type="hidden" name="batches[${orderItemId}][0][sgst]" class="hidden-sgst-val">
                                                                                                                                                                                        <input type="hidden" name="batches[${orderItemId}][0][igst]" class="hidden-igst-val">
                                                                                                                                                                                        <input type="hidden" name="batches[${orderItemId}][0][net_amount]" class="hidden-net-val">
                                                                                                                                                                                    </div>
                                                                                                                                                                                </div>
                                                                                                                                                                            `;
                        tbody.append(rowHtml);


                        // 2. Visible Verification Row (Invoiced Style)
                        let vRowHtml = `
                                                                                                                                                                                <div id="v_row_${orderItemId}" class="invoice-list-row">
                                                                                                                                                                                    <div class="ai-col-product fw-bold text-dark small" style="white-space: normal; line-height: 1.2;">
                                                                                                                                                                                        ${window.cleanProductName(item.product_name, item.side, item.size)} ${window.renderProductVariantBadge(item)}
                                                                                                                                                                                    </div>
                                                                                                                                                                                    <div class="ai-col-batch v-batch-display text-muted small" data-id="${orderItemId}">--</div>
                                                                                                                                                                                    <div class="ai-col-expiry v-expiry-display text-muted small" data-id="${orderItemId}">--</div>
                                                                                                                                                                                    <div class="ai-col-qty fw-bold text-primary v-qty-display" data-original-unit="${item.unit || ''}">
                                                                                                                                                                                        ${orderedQty} ${item.unit || ''}
                                                                                                                                                                                    </div>
                                                                                                                                                                                    <div class="ai-col-free fw-bold text-success v-free-display" data-id="${orderItemId}">-</div>
                                                                                                                                                                                    <div class="ai-col-value text-end small text-dark fw-bold v-taxable-display">--</div>
                                                                                                                                                                                    <div class="ai-col-value text-end small text-muted v-gst-display">--</div>
                                                                                                                                                                                    <div class="ai-col-value text-end fw-bold v-net-display">--</div>
                                                                                                                                                                                </div>
                                                                                                                                                                            `;
                        vbody.append(vRowHtml);
                    });
                };

                proceed();
                if (false) {
                    let targetRole = (row.status || '').toLowerCase() === 'pending' ? 'field staff' : 'distributor';
                    Swal.fire({
                        title: 'Confirm Admin Approval',
                        text: `This order should ideally be approved by the respective ${targetRole}. Do you still want to proceed as an Admin?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, proceed',
                        cancelButtonText: 'No, cancel',
                        confirmButtonColor: '#28a745'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            proceed();
                        }
                    });
                } else {
                    proceed();
                }
            });

            // OCR Dropzone Click
            $(document).on('click', '#ocr_dropzone', function () {
                $('#scan_retailer_file_input').click();
            });

            // Handle Drag & Drop for OCR
            const ocrDropzone = document.getElementById('ocr_dropzone');
            if (ocrDropzone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    ocrDropzone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                    }, false);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    ocrDropzone.addEventListener(eventName, () => ocrDropzone.classList.add('bg-primary-subtle'), false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    ocrDropzone.addEventListener(eventName, () => ocrDropzone.classList.remove('bg-primary-subtle'), false);
                });

                ocrDropzone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    document.getElementById('scan_retailer_file_input').files = files;
                    $('#scan_retailer_file_input').trigger('change');
                }, false);
            }

            // Payment Radio Change
            $(document).on('change', 'input[name="payment_status"]', function() {
                if (typeof window.checkRetailerApprovalReadiness === 'function') {
                    window.checkRetailerApprovalReadiness();
                }
            });

            // File upload logic for AI Processing
            $(document).on('change', '#scan_retailer_file_input', function () {
                const file = this.files[0];
                if (!file) return;

                // Unlock the invoice number field
                $('#retailer_invoice_no_input').prop('readonly', false).attr('placeholder', 'Enter invoice number...');

                $('#ocr_dropzone').addClass('d-none'); // Hide the dropzone
                $('#results_loading_spinner').removeClass('d-none'); // Show loading spinner
                $('#automation_success_state').hide();
                $('#automation_success_footer').addClass('d-none');
                $('#automation_error_state').addClass('d-none');
                $('#batch_allocation_table_container').addClass('d-none');
                $('#btnSubmitDistributorApprove').prop('disabled', true); // Disable submit during OCR

                let formData = new FormData();
                formData.append('invoice', file);
                formData.append('type', 'retailer');
                formData.append('order_id', $('#approve_order_id').val());
                formData.append('order_type', 'retailer');
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: "{{ route('ocr.process') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        setTimeout(() => {
                            $('#results_loading_spinner').addClass('d-none');
                            $('#ocr_dropzone').removeClass('d-none'); // Reset dropzone visibility
                        }, 500);

                        if (res.success && res.data) {
                            // Store data for potential Gatekeeper bypass/retry
                            $('#retailer_invoice_no_input').data('ocr-data', res.data);
                            $('#retailer_invoice_no_input').data('items-filled', false);

                            let identifiedCount = parseRetailerOCRResponse(res.data);

                            if (identifiedCount > 0) {
                                $('#results_loading_spinner').addClass('d-none');
                                $('#batch_allocation_table_container').removeClass('d-none'); // Show grid
                                $('#processed_summary_text').text(`${identifiedCount} products mapped from Invoice.`);
                                window.checkRetailerApprovalReadiness();
                            } else {
                                // If identifiedCount is 0, it might be due to a mismatch Gatekeeper
                                let extracted = $('#retailer_invoice_no_input').data('extracted');
                                let entered = $('#retailer_invoice_no_input').val().trim().toLowerCase();
                                
                                $('#automation_idle_state').hide();
                                $('#results_loading_spinner').addClass('d-none');
                                
                                if (entered && extracted && !isInvoiceMatch(entered, extracted)) {
                                    // Mismatch - Allow but warn
                                    $('#automation_error_state').removeClass('d-none').fadeIn();
                                    $('#automation_error_state h5').text('Invoice Number Mismatch');
                                    $('#automation_error_state p').text('Warning: The entered number differs from the document scan.');
                                    $('#batch_allocation_table_container').removeClass('d-none');
                                    parseRetailerOCRResponse(res.data);
                                } else {
                                    // Actual "No products found" error
                                    $('#automation_error_state').removeClass('d-none').fadeIn();
                                    $('#automation_error_state h5').text('No Products Identified');
                                    $('#automation_error_state p').text('The AI could not identify any ordered products in the uploaded invoice.');
                                    $('#batch_allocation_table_container').addClass('d-none');
                                    $('#btnSubmitDistributorApprove').prop('disabled', true); 
                                }
                            }
                        } else {
                            $('#automation_idle_state').show();
                            $('#ocr_idle_state').show();
                            showToast('error', 'OCR Failed: Invalid response from server.');
                            $('#btnSubmitDistributorApprove').prop('disabled', false); // Re-enable on failure
                        }
                    },
                    error: function (xhr) {
                        console.error('OCR Error:', xhr);
                        // $('#ocr_processing_state').addClass('d-none');
                        $('#results_loading_spinner').addClass('d-none');
                        $('#ocr_dropzone').removeClass('d-none');
                        showToast('error', 'OCR Failed: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Processing error'));
                        $('#btnSubmitDistributorApprove').prop('disabled', false); // Re-enable on error
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


            function parseExpiryToDate(expStr) {
                if (!expStr) return '';
                expStr = expStr.trim();
                // If it's already YYYY-MM-DD
                if (/^\d{ 4} -\d{ 2} -\d{ 2}$ /.test(expStr)) return expStr;

                // Match MM/YY or MM-YY or MM.YY
                let match = expStr.match(/\b(\d{1,2})[\/\-\.](\d{2,4})\b/);
                if (!match) {
                    // Try generic Date parsing
                    let d = new Date(expStr);
                    if (!isNaN(d.getTime())) return d.toISOString().split('T')[0];
                    return ''; // Default fallback
                }

                let month = parseInt(match[1]);
                if (month < 1 || month > 12) return '';

                let year = match[2];
                // Handle 2-digit years
                if (year.length === 2) {
                    year = '20' + year;
                }

                // Get the last day of the parsed month/year
                let lastDay = new Date(year, month, 0).getDate();
                // Format nicely: YYYY-MM-DD
                return `${year}-${month.toString().padStart(2, '0')}-${lastDay.toString().padStart(2, '0')}`;
            }

            // Sync Verification Edits to Hidden Inputs for Submission
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

            function parseRetailerOCRResponse(data) {
                // Update Metadata visually
                if (data.invoice_metadata) {
                    const meta = data.invoice_metadata;
                    const invNo = (meta.invoice_no || meta.invoice_number || meta.inv_no || meta.bill_no || meta.invoice_id || meta.invoice_code || '--').trim();
                    const dlNo = (meta.drug_license || meta.dl_no || meta.drug_lic_no || meta.license_no || '--').trim();
                    const gstin = (meta.gstin || meta.gst_no || meta.gst_number || '--').trim();

                    $('#meta_date').text(meta.date || '--');
                    $('#meta_invoice_no').text(invNo);
                    $('#meta_gstin').text(gstin);
                    $('#meta_dl').text(dlNo);

                    // Sync editable fields
                    $('#meta_invoice_no').attr('contenteditable', 'true').on('input', function() {
                        let val = $(this).text().trim();
                        if (val !== '--') $('#retailer_invoice_no_input').val(val).trigger('input');
                    });
                    $('#meta_gstin').attr('contenteditable', 'true');
                    $('#meta_dl').attr('contenteditable', 'true');

                    // Validation Logic
                    let enteredInv = $('#retailer_invoice_no_input').val().trim().toLowerCase();
                    let extractedInvRaw = invNo !== '--' ? invNo : '';
                    let extractedInv = extractedInvRaw.toLowerCase();
                    
                    // Auto-fill feature: If field is empty, populate from AI
                    if (!enteredInv && extractedInvRaw) {
                        $('#retailer_invoice_no_input').val(extractedInvRaw).addClass('is-valid');
                        enteredInv = extractedInv;
                    }
                    
                    let $valAlert = $('#ai_retailer_validation_alert');
                    let $valMsg = $('#ai_retailer_validation_message');
                    let hasError = false;

                    // Store extracted data on the input element for real-time re-validation
                    $('#retailer_invoice_no_input').data('extracted', extractedInv);
                    $('#retailer_invoice_no_input').data('extracted-raw', extractedInvRaw);
                    $('#retailer_invoice_no_input').data('is-duplicate', meta.is_duplicate);

                    $valAlert.addClass('d-none').removeClass('alert-warning alert-danger');

                    if (meta.is_duplicate) {
                        $valAlert.removeClass('d-none').addClass('alert-danger');
                        $valMsg.text('DUPLICATE INVOICE: This invoice number has already been used by this distributor.');
                        hasError = true;
                    } else if (enteredInv && extractedInv && !isInvoiceMatch(enteredInv, extractedInv)) {
                        $valAlert.removeClass('d-none').addClass('alert-warning');
                        $valMsg.text(`MISMATCH: Entered No. (${$('#retailer_invoice_no_input').val()}) does not match Extracted No. (${extractedInvRaw}).`);
                        hasError = true;
                    }

                    if (hasError) {
                        // Show warning but no longer block product mapping
                        $('#btnSubmitDistributorApprove').prop('disabled', meta.is_duplicate); 
                    } else {
                        $('#btnSubmitDistributorApprove').prop('disabled', false);
                    }

                    if (!enteredInv) {
                        // Gatekeeper: Still block if number is missing, but show products
                        $('#btnSubmitDistributorApprove').prop('disabled', true);
                        $('#automation_error_state').removeClass('d-none').show();
                        $('#automation_error_state h5').text('Invoice Number Required');
                        $('#automation_error_state p').text('Please enter the invoice number from the document to proceed.');
                        return fillRetailerProducts(data);
                    } else {
                        return fillRetailerProducts(data);
                    }
                }
                return 0;
            }

            function fillRetailerProducts(data) {
                let identifiedCount = 0;
                let matchedProducts = [];
                let missingProducts = [];
                window.qtyMismatchProducts = [];
                let invoiceProducts = [...(data.line_items || [])];
                let totalInvoiceNet = 0;
                let totalInvoiceTaxable = 0;

                console.log('Retailer AI Invoice Items:', invoiceProducts);

                $('.product-row').each(function () {
                    let container = $(this);
                    let orderItemId = container.data('item-id');
                    let productName = container.find('.product-name-marker').text().trim().toLowerCase();
                    let orderedQty = parseInt(container.data('ordered-qty'));

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

                    let normProductName = normalize(productName);

                    // 1. Try exact or full inclusion match
                    let matchedIdx = invoiceProducts.findIndex(p => {
                        if (!p.description) return false;
                        let normDesc = normalize(p.description);
                        return normDesc === normProductName || normDesc.includes(normProductName) || normProductName.includes(normDesc);
                    });

                    // 2. Fallback to scoring word-based intersection check (picks BEST match)
                    if (matchedIdx === -1) {
                        let productWords = normProductName.split(' ').filter(w => w.length > 0);
                        let bestScore = 0;
                        let bestIdx = -1;
                        let threshold = Math.max(1, Math.ceil(productWords.length * 0.6));

                        invoiceProducts.forEach((p, idx) => {
                            if (!p.description) return;
                            let normDesc = normalize(p.description);
                            let matchCount = 0;
                            let descWords = normDesc.split(' ');
                            productWords.forEach(word => {
                                // Match word exactly if it's a short size variant, otherwise includes
                                if (word.length <= 2) {
                                    if (descWords.includes(word)) matchCount++;
                                } else {
                                    if (normDesc.includes(word)) matchCount++;
                                }
                            });
                            
                            if (matchCount >= threshold && matchCount > bestScore) {
                                bestScore = matchCount;
                                bestIdx = idx;
                            }
                        });

                        matchedIdx = bestIdx;
                    }

                    // 3. Last fallback (start-of-string prefix match)
                    if (matchedIdx === -1 && normProductName.length >= 6) {
                        matchedIdx = invoiceProducts.findIndex(p => p.description && normalize(p.description).startsWith(normProductName.substring(0, 6)));
                    }

                    if (matchedIdx !== -1) {
                        let matchedInvoiceItem = invoiceProducts[matchedIdx];
                        invoiceProducts.splice(matchedIdx, 1); // Remove from pool
                        identifiedCount++;
                        matchedProducts.push(productName);
                        // Helper to parse numeric values safely
                        const safeParse = (val) => {
                            if (typeof val === 'string' && val.toUpperCase() === 'N/A') return 0;
                            let parsed = parseFloat(val);
                            return isNaN(parsed) ? 0 : parsed;
                        };

                        let extBatch = matchedInvoiceItem.batch && matchedInvoiceItem.batch !== 'N/A' ? matchedInvoiceItem.batch : '';
                        let extExpiry = matchedInvoiceItem.expiry && matchedInvoiceItem.expiry !== 'N/A' ? matchedInvoiceItem.expiry : '';
                        let extPtr = safeParse(matchedInvoiceItem.ptr);
                        let extMrp = safeParse(matchedInvoiceItem.mrp);
                        let extRate = safeParse(matchedInvoiceItem.rate);
                        let billedQty = safeParse(matchedInvoiceItem.qty);
                        let freeQty = safeParse(matchedInvoiceItem.free) || safeParse(matchedInvoiceItem.sch) || safeParse(matchedInvoiceItem.scheme) || safeParse(matchedInvoiceItem.offer);

                        // Correct AI extracting total (billed + free) as billedQty
                        if (billedQty > orderedQty && freeQty > 0 && billedQty === (orderedQty + freeQty)) {
                            billedQty = billedQty - freeQty;
                        } else if (extRate > 0 && (safeParse(matchedInvoiceItem.taxable_amt) || safeParse(matchedInvoiceItem.amount)) > 0) {
                            // If taxable and rate exist, use math to determine true billed quantity
                            let calcQty = Math.round((safeParse(matchedInvoiceItem.taxable_amt) || safeParse(matchedInvoiceItem.amount)) / extRate);
                            if (calcQty > 0 && calcQty < billedQty && billedQty === (calcQty + freeQty)) {
                                billedQty = calcQty;
                            }
                        }

                        let extTaxable = safeParse(matchedInvoiceItem.taxable_amt) || safeParse(matchedInvoiceItem.amount);
                        if (extTaxable === 0 && billedQty > 0 && extRate > 0) {
                            extTaxable = billedQty * extRate;
                        }

                        let extCgst = safeParse(matchedInvoiceItem.cgst);
                        let extSgst = safeParse(matchedInvoiceItem.sgst);
                        let extIgst = safeParse(matchedInvoiceItem.igst);

                        // Handle GST percentage calculation if only 'gst' is provided
                        let extGstAmt = safeParse(matchedInvoiceItem.gst_amt);
                        let gstPercent = safeParse(matchedInvoiceItem.gst);
                        if (extGstAmt === 0 && gstPercent > 0 && extTaxable > 0) {
                            extGstAmt = extTaxable * (gstPercent / 100);
                        }

                        // If individual GST components are missing but total GST amt exists
                        let extGst = extCgst + extSgst + extIgst;
                        if (extGst === 0 && extGstAmt > 0) extGst = extGstAmt;

                        let itemNet = extTaxable + extGst;
                        let totalQty = billedQty + freeQty > 0 ? (billedQty + freeQty) : orderedQty;

                        // 1. Update Visible Row Fields
                        let vRow = $(`#v_row_${orderItemId}`);
                        if (vRow.length) {
                            // Append Pack Size if available
                            let packStr = matchedInvoiceItem.pack && matchedInvoiceItem.pack !== 'N/A' && matchedInvoiceItem.pack.trim() !== '' ? ` [${matchedInvoiceItem.pack.trim()}]` : '';
                            if (packStr) {
                                let nameEl = vRow.find('.ai-col-product');
                                nameEl.text(nameEl.text() + packStr);
                            }

                            if (extBatch) {
                                vRow.find('.v-batch-display').text(extBatch).removeClass('text-muted').addClass('text-success fw-bold');
                            }
                            if (extExpiry) {
                                let cleanDisplay = extExpiry;
                                let match = extExpiry.match(/\b(\d{1,2}[\/\-\.]\d{2,4})\b/);
                                if (match) cleanDisplay = match[1];
                                vRow.find('.v-expiry-display').text(cleanDisplay).removeClass('text-muted').addClass('text-success fw-bold');
                            }

                            let origUnit = vRow.find('.v-qty-display').data('original-unit') || '';
                            let displayQty = `<strong>${billedQty}</strong> ${origUnit}`;
                            
                            // Visual cue if quantity differs from ordered
                            if (billedQty !== orderedQty) {
                                let diffClass = billedQty > orderedQty ? 'text-primary' : 'text-danger';
                                displayQty += ` <br><small class="${diffClass} fw-bold" style="font-size: 0.65rem;">(Ord: ${orderedQty})</small>`;
                                if (billedQty > orderedQty) {
                                    window.qtyMismatchProducts.push(`You ordered ${orderedQty} but the invoiced quantity is ${billedQty} for item: ${productName}`);
                                }
                            }
                            vRow.find('.v-qty-display').html(displayQty);
                            vRow.find('.v-free-display').text(freeQty > 0 ? freeQty : '-');

                            vRow.find('.v-taxable-display').text(`₹${extTaxable.toFixed(2)}`);
                            vRow.find('.v-gst-display').text(`₹${extGst.toFixed(2)}`);
                            vRow.find('.v-net-display').text(`₹${itemNet.toFixed(2)}`);
                        }

                        // 2. Update Hidden Form Data
                        let hContainer = $(`#batches_for_${orderItemId}`);
                        if (hContainer.length) {
                            if (extBatch) hContainer.find('.hidden-batch-val').val(extBatch);
                            if (extExpiry) hContainer.find('.hidden-expiry-val').val(parseExpiryToDate(extExpiry));

                            hContainer.find('.hidden-qty-val').val(totalQty);
                            hContainer.find('.hidden-free-val').val(freeQty);
                            hContainer.find('.hidden-mrp-val').val(extMrp);
                            hContainer.find('.hidden-ptr-val').val(extPtr);
                            hContainer.find('.hidden-taxable-val').val(extTaxable);
                            hContainer.find('.hidden-cgst-val').val(extCgst);
                            hContainer.find('.hidden-sgst-val').val(extSgst);
                            hContainer.find('.hidden-igst-val').val(extIgst);
                            hContainer.find('.hidden-net-val').val(itemNet);

                            // Propagate quantity update to the main items array visible submission
                            let itemRow = $(`div.product-row[data-item-id="${orderItemId}"]`);
                            if (itemRow.length) {
                                itemRow.find(`input[name="items[${orderItemId}][quantity]"]`).val(totalQty);
                            }
                        }

                        totalInvoiceNet += itemNet;
                        totalInvoiceTaxable += extTaxable;

                    } else {
                        missingProducts.push(productName);
                        // Mark missing visually
                        let vRow = $(`#v_row_${orderItemId}`);
                        if (vRow.length) {
                            vRow.addClass('bg-danger-subtle');
                            vRow.find('.v-batch-display').text('Missing').addClass('text-danger fw-bold');
                        }
                    }
                });

                // Update Footer Totals
                $('#verification_table_footer').removeClass('d-none');
                $('#v_total_net').text(`₹${totalInvoiceNet.toFixed(2)}`);
                
                let ocrTotal = 0;
                if (data.invoice_metadata && data.invoice_metadata.total_amount) {
                    ocrTotal = parseFloat(data.invoice_metadata.total_amount) || 0;
                }
                let finalNet = ocrTotal > 0 ? ocrTotal : totalInvoiceNet;
                $('#v_total_meta').text(`₹${finalNet.toFixed(2)}`);

                // Update premium metadata card row 2
                $('#meta_taxable_amount').text(`₹${totalInvoiceTaxable.toFixed(2)}`);
                $('#meta_net_amount').text(`₹${finalNet.toFixed(2)}`);

                // Update hidden inputs for form submission
                $('#final_amount_input').val(finalNet.toFixed(2));
                $('#taxable_amount_input').val(totalInvoiceTaxable.toFixed(2));

                // STRICT MATCH BLOCKING: Only enable if no missing AND no extra items
                if (missingProducts.length === 0 && invoiceProducts.length === 0 && window.qtyMismatchProducts.length === 0) {
                    
                    // NEW: Validate Batches against Backend Inventory dynamically
                    $('#automation_error_state').hide();
                    $('#automation_success_state').hide();
                    $('#btnSubmitDistributorApprove').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Validating Inventory...');
                    
                    $.ajax({
                        url: "{{ route('admin.retailer.validate-batches') }}",
                        type: "POST",
                        data: $('#distributorApproveForm').serialize() + '&_token={{ csrf_token() }}',
                        success: function(valRes) {
                            if (valRes.success) {
                                $('#automation_success_state').fadeIn();
                                $('#btnSubmitDistributorApprove').prop('disabled', false).html('Accept Order');
                            } else {
                                $('#automation_error_state').removeClass('d-none').show();
                                $('#automation_error_state h5').text('Inventory Mismatch Detected');
                                let msg = valRes.errors.join('<br>');
                                $('#automation_error_state p').html(`${msg} <br>Please resolve inventory shortages before approving.`);
                                $('#btnSubmitDistributorApprove').prop('disabled', true).html('Accept Order');
                            }
                        },
                        error: function() {
                            $('#automation_error_state').removeClass('d-none').show();
                            $('#automation_error_state h5').text('Validation Error');
                            $('#automation_error_state p').html('Failed to validate inventory batches. Please try again.');
                            $('#btnSubmitDistributorApprove').prop('disabled', true).html('Accept Order');
                        }
                    });

                } else {
                    $('#automation_error_state').removeClass('d-none').show();
                    $('#automation_error_state h5').text('Action Required');
                    
                    let msg = '';
                    if (missingProducts.length > 0) {
                        msg = `This document is <b>missing ${missingProducts.length} ordered items</b>. <br>`;
                    }
                    if (invoiceProducts.length > 0) {
                        msg += `It also contains <b>${invoiceProducts.length} extra items</b> not in the order. <br>`;
                    }
                    if (window.qtyMismatchProducts.length > 0) {
                        msg += window.qtyMismatchProducts.join('<br>') + '<br>';
                    }
                    
                    $('#automation_error_state p').html(`${msg} Please upload a perfect match invoice to proceed.`);
                    $('#automation_success_state').hide();
                    $('#btnSubmitDistributorApprove').prop('disabled', true).html('Accept Order');
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

                $('#retailer_invoice_no_input').data('items-filled', true);
                return identifiedCount;
            }
            $(document).on('input', '#retailer_invoice_no_input', function() {
                let entered = $(this).val().trim().toLowerCase();
                let extracted = $(this).data('extracted');
                let extractedRaw = $(this).data('extracted-raw');
                let isDuplicate = $(this).data('is-duplicate');
                let $valAlert = $('#ai_retailer_validation_alert');
                let $valMsg = $('#ai_retailer_validation_message');
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
                        fillRetailerProducts(ocrData);
                    }
                } else if (!entered || hasError) {
                    // Hide products if empty or error
                    $('#batch_allocation_table_container').addClass('d-none');
                    $('#automation_success_state').hide();
                    $('#automation_success_footer').addClass('d-none');
                    
                    if (!entered) {
                        $('#automation_error_state').removeClass('d-none').show();
                        $('#automation_error_state h5').text('Invoice Number Required');
                        $('#automation_error_state p').text('Please enter the invoice number to load and verify data.');
                    } else {
                        $('#automation_error_state').removeClass('d-none').show();
                        $('#automation_error_state h5').text('Invoice Number Mismatch');
                        $('#automation_error_state p').text('The entered number does not match the AI scan. Products hidden for safety.');
                    }
                    $(this).data('items-filled', false);
                    $('#btnSubmitDistributorApprove').prop('disabled', true);
                }
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
                $(this).data('items-filled', true);
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
                        let err = xhr.responseJSON ? xhr.responseJSON.error : 'Submission failed';
                        Swal.fire('Error', err, 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Submit Return');
                    }
                });
            });
            // --- Edit Order Logic ---
            let editItems = {};
            $(document).on('click', '.edit-btn', function () {
                let row = JSON.parse($(this).attr('data-row'));
                $('#editOrderForm').attr('action', `/admin/retailer/${row.id}`);
                $('#edit_retailer_id').val(row.retailer_id);
                $('#edit_distributor_id').val(row.distributor_id || '');
                $('#edit_status').val(row.status ? row.status.toLowerCase().replace(/ /g, '_') : 'pending');
                $('#edit_delivery_notes').val(row.delivery_notes || '');

                editItems = {};
                row.items.forEach(function (i) {
                    let vInfo = [i.side, i.size].filter(v => v).join(' / ');
                    let displayName = i.product_name || i.name;
                    if (vInfo) displayName += ` [${vInfo}]`;
                    
                    let itemKey = i.product_id + (vInfo ? '-' + vInfo.replace(/ /g, '_') : '');
                    
                    editItems[itemKey] = {
                        id: i.product_id,
                        itemKey: itemKey,
                        name: displayName,
                        side: i.side,
                        size: i.size,
                        qty: i.quantity || i.qty,
                        unit: i.unit || 'Strips',
                        price: parseFloat(i.unit_price || i.price),
                        basePrice: parseFloat(i.unit_price || i.price),
                        gst: parseFloat(i.gst) || 0,
                        order_item_id: i.order_item_id,
                        pack: i.pack,
                        box_size: i.box_size,
                        product_code: i.product_code,
                        brand: i.brand,
                        generic_name: i.generic_name
                    };
                });
                renderEditItems();
                $('#editOrderModal').modal('show');
            });

            // Variant picker logic for add-product in edit modal
            function buildEditVariantPicker(variantsJson) {
                let $picker = $('#edit_variant_picker');
                let $levels = $('#edit_variant_levels');
                let $hidden = $('#edit_selected_variant');
                $picker.hide();
                $levels.empty();
                $hidden.val('');
                if (!variantsJson) return;
                let variants;
                try { variants = JSON.parse(variantsJson); } catch(e) { return; }
                if (!variants || Object.keys(variants).length === 0) return;

                let attrNames = Object.keys(variants);
                $picker.show();
                attrNames.forEach(function(attr, idx) {
                    let vals = variants[attr];
                    let levelHtml = `<div class="mb-1 edit-variant-level" data-attr="${attr}" data-level="${idx}" ${idx > 0 ? 'style="display:none;"' : ''}>
                        <span class="text-muted small text-uppercase fw-bold me-2" style="font-size:0.7rem;">${attr}:</span>
                        <div class="d-inline-flex flex-wrap gap-1">
                            ${vals.map(v => `<button type="button" class="btn btn-sm btn-outline-primary edit-var-btn px-2 py-1 fw-bold" style="font-size:0.78rem;border-radius:6px;" data-attr="${attr}" data-value="${v}" data-level="${idx}">${v}</button>`).join('')}
                        </div>
                    </div>`;
                    $levels.append(levelHtml);
                });
            }

            $('#edit_product_select').on('change', function() {
                let sel = $(this).find('option:selected');
                let variantsRaw = sel.attr('data-variants') || '';
                buildEditVariantPicker(variantsRaw || null);
                $('#edit_variant_warn').addClass('d-none');
            });

            $(document).on('click', '.edit-var-btn', function() {
                let $btn = $(this);
                let level = parseInt($btn.data('level'));

                // Mark active in this level
                $btn.closest('.edit-variant-level').find('.edit-var-btn')
                    .removeClass('active btn-primary text-white').addClass('btn-outline-primary');
                $btn.removeClass('btn-outline-primary').addClass('active btn-primary text-white');

                // Hide/reset subsequent levels
                $('.edit-variant-level').each(function() {
                    if (parseInt($(this).data('level')) > level) {
                        $(this).hide().find('.edit-var-btn')
                            .removeClass('active btn-primary text-white').addClass('btn-outline-primary');
                    }
                });

                // Show next level or finalise
                let $nextLevel = $(`.edit-variant-level[data-level="${level + 1}"]`);
                if ($nextLevel.length > 0) {
                    $nextLevel.show();
                    $('#edit_selected_variant').val('');
                } else {
                    let parts = [];
                    $('.edit-variant-level:visible .edit-var-btn.active').each(function() {
                        parts.push($(this).data('value'));
                    });
                    $('#edit_selected_variant').val(parts.join(' - '));
                }
            });

            $('#btn_add_prod_edit').click(function () {
                let sel = $('#edit_product_select option:selected');
                let id = sel.val();
                if (!id) return;

                let pack = (sel.data('pack') || '').toString().toLowerCase();
                let code = (sel.data('code') || '').toString().trim();
                let boxSize = sel.data('boxsize');
                let stripsPerBox = parseInt(sel.data('stripsperbox')) || 10;
                let hasCode = code && code !== '---' && code !== '';
                let isCount = hasCode || boxSize === '' || boxSize === null || pack.includes('nos') || pack.includes('count');

                let variantsRaw = sel.attr('data-variants') || '';
                let hasVariants = variantsRaw && variantsRaw.trim() !== '';
                let selectedVariant = $('#edit_selected_variant').val();

                if (hasVariants && !selectedVariant) {
                    $('#edit_variant_warn').removeClass('d-none');
                    return;
                }
                $('#edit_variant_warn').addClass('d-none');

                // Parse variant into side/size fields
                let side = null, size = null;
                if (selectedVariant && hasVariants) {
                    let variantParts = selectedVariant.split(' - ');
                    let variantDef = {};
                    try { variantDef = JSON.parse(variantsRaw); } catch(e) {}
                    let attrNames = Object.keys(variantDef);
                    attrNames.forEach(function(attr, idx) {
                        let v = variantParts[idx] || null;
                        let aLow = attr.toLowerCase();
                        if (aLow === 'side') side = v;
                        else if (aLow === 'size') size = v;
                    });
                }

                // Use variant-aware unique key so same product with diff variants can coexist
                let itemKey = id + (selectedVariant ? '-' + selectedVariant.replace(/ /g, '_') : '');
                if (editItems[itemKey]) { editItems[itemKey].qty += 1; renderEditItems(); return; }

                editItems[itemKey] = {
                    id: id,
                    itemKey: itemKey,
                    name: sel.text().trim() + (selectedVariant ? ` [${selectedVariant}]` : ''),
                    side: side,
                    size: size,
                    qty: 1,
                    unit: isCount ? 'Nos' : 'Strips',
                    basePrice: parseFloat(sel.data('price')),
                    price: parseFloat(sel.data('price')),
                    stripsPerBox: stripsPerBox,
                    isCount: isCount,
                    gst: parseFloat(sel.data('gst')) || 0
                };

                // Reset picker after adding
                buildEditVariantPicker(null);
                $('#edit_product_select').val('');
                renderEditItems();
            });



            function computeUnitPrice(item, unit) {
                // basePrice is always per-strip PTR. Convert based on selected unit.
                let base = parseFloat(item.basePrice || item.price) || 0;
                let spb = parseInt(item.stripsPerBox) || 10;
                if (unit === 'Box') return base * spb;
                if (unit === 'Nos') return base; // count-based products: price per unit
                return base; // Strips (default)
            }

            function renderEditItems() {
                let tbody = $('#edit_items_table tbody');
                tbody.empty();
                let total = 0;
                if (Object.keys(editItems).length === 0) {
                    tbody.html('<tr><td colspan="7" class="text-center text-muted">No Items Added</td></tr>');
                } else {
                    $.each(editItems, function (id, item) {
                        let pPack = (item.pack || '').toLowerCase();
                        let hasCode = item.product_code && item.product_code !== '---' && item.product_code.trim() !== '';
                        let isCount = item.isCount !== undefined ? item.isCount : (hasCode || item.box_size === '' || item.box_size === null || pPack.includes('nos') || pPack.includes('count'));

                        let allowedUnits = isCount ? ['Nos'] : ['Strips', 'Box'];
                        let unit = item.unit || (isCount ? 'Nos' : 'Strips');
                        // Ensure unit is valid for this product
                        if (!allowedUnits.includes(unit)) unit = allowedUnits[0];

                        let price = computeUnitPrice(item, unit);
                        let qty = parseInt(item.qty) || 1;
                        let gst = parseFloat(item.gst) || 0;
                        let sub = price * qty * (1 + (gst / 100));
                        total += sub;

                        let options = '';
                        allowedUnits.forEach(function (u) {
                            let displayU = u === 'Nos' ? 'No.' : u;
                            options += `<option value="${u}" ${unit === u ? 'selected' : ''}>${displayU}</option>`;
                        });

                        tbody.append(`
                            <tr>
                                <td>
                                    <div class="fw-bold text-main-theme mb-0" style="font-size:0.88rem; line-height:1.2;">${item.name}</div>
                                    <div class="small text-muted-theme" style="font-size:0.7rem;">
                                        ${item.brand ? `<span class="fw-bold">(${item.brand})</span> •` : ''} <span class="fw-bold text-primary">${qty} ${unit === 'Nos' ? 'No.' : unit}</span>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap opacity-75" style="font-size:0.6rem;">
                                        ${item.generic_name ? `<span>${item.generic_name}</span>` : ''}
                                        ${item.pack && item.pack.trim() ? `<span>• ${item.pack}</span>` : ''}
                                        ${item.product_code && item.product_code !== '---' && item.product_code !== 'N/A' ? `<span>Code: ${item.product_code}</span>` : ''}
                                    </div>
                                    <input type="hidden" name="items[${id}][product_id]" value="${item.id}">
                                    <input type="hidden" name="items[${id}][side]" value="${item.side || ''}">
                                    <input type="hidden" name="items[${id}][size]" value="${item.size || ''}">
                                    ${item.order_item_id ? `<input type="hidden" name="items[${id}][order_item_id]" value="${item.order_item_id}">` : ''}
                                </td>
                                <td>
                                    <select class="form-select form-select-sm unit-select-edit bg-card-theme text-main-theme" data-id="${id}" name="items[${id}][unit]" style="width:90px; margin: 0 auto; border-color: var(--med-border-light);">
                                        ${options}
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm edit-qty bg-card-theme text-main-theme text-center" data-id="${id}" value="${qty}" name="items[${id}][quantity]" min="1" style="width:80px; margin: 0 auto; border-color: var(--med-border-light);">
                                </td>
                                <td class="text-end">${price.toFixed(2)}<input type="hidden" name="items[${id}][unit_price]" value="${price.toFixed(2)}"></td>
                                <td class="text-end">${gst}%</td>
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
                editItems[id].qty = parseInt($(this).val()) || 1;
                renderEditItems();
            });

            $(document).on('change', '.unit-select-edit', function () {
                let id = $(this).data('id');
                let val = $(this).val();
                if (editItems[id]) {
                    editItems[id].unit = val;
                    renderEditItems(); // Recalculate price on unit change
                }
            });

            $(document).on('click', '.remove-edit', function () {
                delete editItems[$(this).data('id')];
                renderEditItems();
            });

            $(document).on('click', '.view-edit-history', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let rowId = $(this).data('id');
                let rowData = table.row($(this).closest('tr')).data();
                if (!rowData || !rowData.metadata || !rowData.metadata.edit_history) return;

                $('#history_order_code').text(rowData.order_code);
                let historyHtml = '';
                
                let history = rowData.metadata.edit_history;
                // Reverse to show latest first
                let reversedHistory = [...history].reverse();

                reversedHistory.forEach(function(log, index) {
                    let snapshotHtml = '<div class="text-muted small">No item snapshot available for this edit.</div>';
                    
                    if (log.snapshot && Array.isArray(log.snapshot)) {
                        snapshotHtml = `
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${log.snapshot.map(s => {
                                            let variantInfo = '';
                                            if (s.side || s.size) {
                                                variantInfo = `<br><span class="text-muted" style="font-size:0.7rem;">[${[s.side, s.size].filter(Boolean).join(' - ')}]</span>`;
                                            }
                                            return `
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold">${s.product_name}</span>
                                                        ${variantInfo}
                                                    </td>
                                                    <td class="text-center">${s.quantity} ${s.unit === 'Nos' ? 'No.' : s.unit}</td>
                                                    <td class="text-end">₹${parseFloat(s.subtotal).toFixed(2)}</td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }

                    historyHtml += `
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-card-theme border-bottom-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary me-2">Edit ${history.length - index}</span>
                                    <span class="fw-bold text-main-theme">${log.edited_by}</span> 
                                    <span class="text-muted small">(${log.role})</span>
                                </div>
                                <span class="text-muted small"><i class="fa fa-clock me-1"></i>${log.edited_at}</span>
                            </div>
                            <div class="card-body py-2">
                                <div class="mb-2">
                                    <span class="fw-bold text-muted small">Original Total:</span> 
                                    <span class="text-danger fw-bold">₹${parseFloat(log.original_total).toFixed(2)}</span>
                                </div>
                                <div class="fw-bold text-main-theme small mb-1">State Before Edit:</div>
                                ${snapshotHtml}
                            </div>
                        </div>
                    `;
                });

                $('#edit_history_content').html(historyHtml);
                $('#editHistoryModal').modal('show');
            });

            $('#editOrderForm').submit(function (e) {
                e.preventDefault();
                let form = $(this);
                let url = form.attr('action');
                let data = form.serialize();

                let $btn = form.find('button[type="submit"]');
                let oldText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

                $.ajax({
                    url: url,
                    type: 'POST', // Method spoofing is handled by _method input
                    data: data,
                    success: function (response) {
                        if (response.success || response.message) {
                            $('#editOrderModal').modal('hide');
                            $('#retailer-approval-table').DataTable().ajax.reload();
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
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(oldText);
                    }
                });
            });
        });
    </script>
@endpush
