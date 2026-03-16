@extends('layouts.admin')

@section('page-body')
    <style>
        /* Compact the table */
        .dataTables_filter {
            text-align: right !important;
        }

        .dataTables_filter input {
            width: 230px !important;
            margin-left: 10px !important;
        }

        /* Segmented Control for Payment Filter */
        .segmented-control {
            display: flex;
            background-color: #e2e8f0;
            border-radius: 50px;
            padding: 4px;
            position: relative;
            width: 260px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .segmented-control input {
            display: none;
        }

        .segmented-control label {
            flex: 1;
            text-align: center;
            padding: 6px 0;
            margin: 0;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            position: relative;
            z-index: 2;
            transition: color 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .segmented-control input:checked+label {
            color: #0f172a;
        }

        #pay_paid:checked+label {
            color: #15803d;
        }

        #pay_pending:checked+label {
            color: #b45309;
        }

        .selection-indicator {
            position: absolute;
            top: 4px;
            bottom: 4px;
            left: 4px;
            width: calc(33.333% - 2.66px);
            background: #ffffff;
            border-radius: 50px;
            z-index: 1;
            transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
        }

        #pay_all:checked~.selection-indicator {
            transform: translateX(0);
        }

        #pay_paid:checked~.selection-indicator {
            transform: translateX(100%);
        }

        #pay_unpaid:checked~.selection-indicator {
            transform: translateX(200%);
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

        .action-buttons {
            display: inline-flex !important;
            align-items: center;
            gap: 4px;
        }

        .action-buttons .btn {
            margin: 0 !important;
        }

        /* Invoice-style Item List */
        .invoice-list {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 10px;
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
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        /* Order Summary Card */
        .order-summary-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            position: relative;
        }

        .retailer-detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: #cbd5e1;
            margin-bottom: 4px;
        }

        .retailer-detail-item i {
            width: 16px;
            text-align: center;
            color: #94a3b8;
        }

        /* Payment Card */
        .payment-status-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
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
            background: #f1f5f9;
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .status-radio-option input:checked+.status-radio-box {
            background: #dcfce7;
            color: #15803d;
            border-color: #22c55e;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.1);
        }

        .status-radio-option input[value="pending"]:checked+.status-radio-box {
            background: #fef9c3;
            color: #854d0e;
            border-color: #eab308;
        }

        /* Custom Modal Width */
        .modal-custom-width {
            max-width: 800px !important;
        }
    </style>

    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom pb-0">
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
                        $defaultPending = $user->hasRole(['salesmanager', 'fieldstaff']);
                    @endphp
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $defaultPending ? 'active px-4 fw-bold text-primary border-bottom-0' : 'px-4 fw-bold text-muted' }}" id="tab-pending" data-bs-toggle="tab"
                            data-status="pending" type="button" role="tab">Pending</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !$defaultPending ? 'active px-4 fw-bold text-primary border-bottom-0' : 'px-4 fw-bold text-muted' }}" id="tab-processing"
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

                <table class="table table-striped table-hover" id="distributor-approval-table">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Order ID</th>
                            <th>Distributor</th>
                            <th>Summary</th>
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

    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-custom-width modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 overflow-hidden">
                <div class="modal-body p-0">
                    <!-- Order Summary Header -->
                    <div class="order-summary-header">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="row align-items-center">
                            <div class="col-sm-7">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-soft-primary text-white me-2"
                                        style="background: rgba(255,255,255,0.2) !important;">DETAILS</span>
                                    <h4 class="mb-0 fw-bold text-white" id="view_order_code">--</h4>
                                </div>
                                <div class="retailer-detail-item">
                                    <i class="fa fa-calendar"></i>
                                    <span id="view_placed_at">--</span>
                                </div>
                            </div>
                            <div class="col-sm-5 text-sm-end">
                                <div class="text-white-50 small text-uppercase fw-bold mb-1">Distributor Information</div>
                                <h5 class="fw-bold text-white mb-1" id="view_distributor">--</h5>
                                <div class="badge bg-soft-info" id="view_status_badge">--</div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-white">
                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <span class="bg-light p-2 rounded me-2"><i class="fa fa-list-ul text-primary"></i></span>
                            Order Items
                        </h6>
                        <div class="invoice-list mb-3">
                            <div class="invoice-list-header bg-dark text-white border-0 py-2">
                                <div style="flex: 2;" class="ps-3 text-white">Product Name</div>
                                <div style="flex: 1;" class="text-center text-white">Quantity</div>
                                <div style="flex: 1;" class="text-end pe-3 text-white">Total Price</div>
                            </div>
                            <div id="view_items_list">
                                <!-- Items will be populated here -->
                            </div>
                            <div class="invoice-list-footer bg-light">
                                <div class="me-3 text-muted">Grand Total:</div>
                                <div class="text-primary fs-5" id="view_total">₹0</div>
                            </div>
                        </div>

                        <div id="view_notes_container" class="mt-3 p-3 bg-light rounded-3 d-none">
                            <div class="text-muted small text-uppercase fw-bold mb-1">Notes</div>
                            <p id="view_notes" class="text-dark mb-0 small"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Approve Order Modal for Sales Managers --}}
    <div class="modal fade" id="approveOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-custom-width modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 overflow-hidden">
                <form id="approveOrderForm">
                    <div class="modal-body p-0">
                        <!-- Order Summary Header -->
                        <div class="order-summary-header">
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                            <div class="row align-items-center">
                                <div class="col-sm-7">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary me-2">ORDER</span>
                                        <h4 class="mb-0 fw-bold text-white" id="approve_order_code_display">--</h4>
                                    </div>
                                    <div class="retailer-detail-item">
                                        <i class="fa fa-calendar"></i>
                                        <span id="approve_order_date_display">--</span>
                                    </div>
                                </div>
                                <div class="col-sm-5 text-sm-end">
                                    <div class="text-white-50 small text-uppercase fw-bold mb-1">Distributor Information
                                    </div>
                                    <h5 class="fw-bold text-white mb-1" id="approve_distributor_display">--</h5>
                                    <div class="retailer-detail-item justify-content-sm-end">
                                        <i class="fa fa-map-marker-alt"></i>
                                        <span id="approve_location_display">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-white">
                            <input type="hidden" id="approve_order_id" name="order_id">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 d-flex align-items-center">
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
                                <div class="invoice-list-footer bg-light">
                                    <div class="me-3 text-muted">Total Estimated Value:</div>
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
                    <div class="modal-footer bg-light border-top-0 px-4 py-3">
                        <button type="button" class="btn btn-link link-secondary fw-bold text-decoration-none"
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
    <div class="modal fade" id="rejectOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger py-3">
                    <h5 class="modal-title fw-bold text-white">Reject Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectOrderForm">
                    <div class="modal-body">
                        <input type="hidden" id="reject_order_id" name="order_id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason for Rejection</label>
                            <textarea class="form-control" name="reason" rows="3" required
                                placeholder="Enter rejection reason..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
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
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                            <div class="row align-items-center">
                                <div class="col-sm-7">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary me-2">DISTRIBUTION</span>
                                        <h4 class="mb-0 fw-bold text-white" id="process_order_code_display">--</h4>
                                    </div>
                                    <div class="retailer-detail-item">
                                        <i class="fa fa-calendar"></i>
                                        <span id="process_order_date_display">--</span>
                                    </div>
                                </div>
                                <div class="col-sm-5 text-sm-end">
                                    <div class="text-white-50 small text-uppercase fw-bold mb-1">Distributor Information
                                    </div>
                                    <h5 class="fw-bold text-white mb-1" id="process_distributor_display">--</h5>
                                    <div class="retailer-detail-item justify-content-sm-end">
                                        <i class="fa fa-map-marker-alt"></i>
                                        <span id="process_location_display">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-white">
                            <input type="hidden" id="process_order_id" name="order_id">

                            <!-- Payment Configuration Section -->
                            <div class="payment-status-card mb-4">
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark d-flex align-items-center">
                                        <i class="fa fa-money-check-alt text-primary me-2"></i>
                                        Settlement & Payment Status
                                    </h6>
                                    <div class="text-muted small">Update the current payment state for this order.</div>
                                </div>
                                <div class="status-badge-group">
                                    <label class="status-radio-option">
                                        <input type="radio" name="payment_status" value="pending" checked>
                                        <span class="status-radio-box">PENDING</span>
                                    </label>
                                    <label class="status-radio-option">
                                        <input type="radio" name="payment_status" value="paid">
                                        <span class="status-radio-box">PAID</span>
                                    </label>
                                </div>
                            </div>

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
                                    <div class="bg-light p-3 rounded-3 border mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="text-muted small">Inv Date</div>
                                                <div class="fw-bold text-dark" id="extract_date">--</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-muted small">GSTIN (Extracted)</div>
                                                <div class="fw-bold text-dark" id="extract_gstin">--</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-muted small">Drug License</div>
                                                <div class="fw-bold text-dark" id="extract_dl">--</div>
                                            </div>
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
                                            style="font-size: 0.75rem;">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#scan_file_input').val('').click()">
                                                    <i class="fa fa-sync me-1"></i> Re-scan / Different File
                                                </button>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="text-muted">Extracted Totals:</div>
                                                    <div>Txl: <span class="fw-bold" id="tfoot_taxable">0</span></div>
                                                    <div>GST: <span class="fw-bold" id="tfoot_gst_total">0</span></div>
                                                    <div class="ms-2 border-start ps-3 py-1">
                                                        <div class="small text-muted mb-0">Invoice Net</div>
                                                        <div class="fs-6 fw-bold text-primary" id="tfoot_net">₹0.00</div>
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

                            <div class="alert alert-info py-2 small mb-0 d-flex align-items-center">
                                <i class="fa fa-info-circle me-2 fs-5"></i>
                                <span>Verify extracted batch, expiry and pricing details before confirming. You can edit
                                    them directly if needed.</span>
                            </div>

                            <!-- Hidden inputs for Form Submission -->
                            <div id="hidden_batch_inputs" class="d-none">
                                <div id="batch_entry_body"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0 px-4 py-3">
                        <button type="button" class="btn btn-link link-secondary fw-bold text-decoration-none"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn_approve_order" class="btn btn-primary px-4 py-2 fw-bold shadow">
                            <i class="fa fa-check-circle me-1"></i> Confirm & Approve Order
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
            font-size: 0.75rem;
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

            var table = $('#distributor-approval-table').DataTable({
                order: [
                    [5, 'desc']
                ],
                drawCallback: function (settings) {
                    var api = this.api();
                    api.column(0, {
                        search: 'applied',
                        order: 'applied'
                    }).nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1 + settings._iDisplayStart;
                    });

                    // Initialize Popovers
                    $('[data-bs-toggle="popover"]').popover();
                },
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-3 d-flex align-items-center'<'col-md-6'l><'col-md-6'f>>" +
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
                        text: '<i class="fa fa-copy"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-info btn-sm text-white',
                        text: '<i class="fa fa-file-csv"></i> CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fa fa-file-excel"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fa fa-file-pdf"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-dark btn-sm',
                        text: '<i class="fa fa-print"></i> Print'
                    }
                    ]
                },
                initComplete: function () {
                    // Move custom filters to the DataTables filter area
                    var $filterContainer = $('#filter_container').removeClass('d-none');
                    $('.dt-buttons').parent().append($filterContainer);

                    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                        $('#orderStatusTabs .nav-link').removeClass('text-primary border-bottom-0').addClass('text-muted');
                        $(this).removeClass('text-muted').addClass('text-primary border-bottom-0');
                        table.ajax.reload();
                    });

                    $('input[name="payment_status"]').on('change', function () {
                        table.ajax.reload();
                    });
                },
                ajax: {
                    url: "{{ route('admin.approvals.distributor') }}",
                    data: function (d) {
                        d.status = $('#orderStatusTabs .nav-link.active').attr('data-status');
                        d.payment_status = $('input[name="payment_status"]:checked').val() || '';
                    }
                },
                columns: [
                    {
                        data: null,
                        defaultContent: '',
                        orderable: false,
                        searchable: false
                    },
                    { data: 'order_code', render: (d, t, r) => r.invoice_url ? d + ' <i class="fa fa-check-circle text-success" title="Invoice Uploaded"></i>' : d },
                    { 
                        data: 'distributor_name',
                        render: function(data, type, row) {
                            return `<span class="fw-bold text-primary" 
                                          style="cursor: pointer;"
                                          data-bs-toggle="popover" 
                                          data-bs-trigger="hover" 
                                          data-bs-html="true"
                                          title="Distributor Details"
                                          data-bs-content="<b>Phone:</b> ${row.distributor_phone || 'N/A'}<br><b>Email:</b> ${row.distributor_email || 'N/A'}<br><b>Address:</b> ${row.distributor_address || 'N/A'}<br><b>GST:</b> ${row.distributor_gst || 'N/A'}<br><b>DL:</b> ${row.distributor_dl || 'N/A'}">
                                        ${data}
                                    </span>`;
                        }
                    },
                    { data: 'product_summary', render: d => d.length > 50 ? d.substring(0, 50) + '...' : d },
                    { 
                        data: 'total_amount',
                        render: function(data) {
                            return `<span class="fw-bold text-success">₹${data}</span>`;
                        }
                    },
                    { data: 'placed_at' },
                    {
                        data: 'status',
                        render: function (d, type, row) {
                            let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';
                            let bgClass = 'bg-secondary';
                            let displayStatus = row.status;

                            if (statusRaw.includes('pending')) bgClass = 'bg-warning text-dark';
                            else if (statusRaw === 'processing') {
                                bgClass = 'bg-primary';
                                displayStatus = 'Processing';
                            } else if (statusRaw === 'approved') {
                                bgClass = 'bg-info';
                                displayStatus = 'Accepted';
                            } else if (statusRaw.includes('delivered')) bgClass = 'bg-success';
                            else if (statusRaw.includes('cancelled')) bgClass = 'bg-danger';
                            else if (statusRaw.includes('rejected')) bgClass = 'bg-danger';

                            return `<span class="badge ${bgClass}" style="font-size: 0.85rem;">${displayStatus}</span>`;
                        }
                    },
                    {
                        data: 'payment_status',
                        render: function (d, type, row) {
                            let payStatus = row.payment_status ? row.payment_status.toLowerCase() : 'pending';
                            let bgClass = 'bg-secondary';
                            let displayLabel = payStatus.charAt(0).toUpperCase() + payStatus.slice(1);

                            if (payStatus === 'paid') bgClass = 'bg-success';
                            else {
                                bgClass = 'bg-warning text-dark';
                                displayLabel = 'Pending';
                            }

                            let cursorStyle = isAdmin ? 'cursor: pointer;' : '';
                            let clickableClass = isAdmin ? 'change-payment-status' : '';

                            return `<span class="badge ${bgClass} ${clickableClass}" data-id="${row.id}" data-status="${payStatus}" style="font-size: 0.85rem; ${cursorStyle}">${displayLabel}</span>`;
                        }
                    },
                    {
                        data: null,
                        visible: isAdmin, // Only visible to Admins
                        orderable: false,
                        render: function (data, type, row) {
                            // Logic for Admins ONLY (since visible: isAdmin)
                            if (row.invoice_url) {
                                let ext = row.invoice_url.split('.').pop().toLowerCase();
                                let icon = ext === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-image-o';
                                let html = `<div class="d-flex align-items-center gap-2">`;
                                html += `<a href="${row.invoice_url}" target="_blank" class="btn btn-sm btn-info text-white" title="View Invoice"><i class="fa ${icon}"></i> View</a>`;
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
                        orderable: false,
                        render: function (data, type, row) {
                            let rowData = JSON.stringify(row).replace(/"/g, '&quot;');
                            let invoiceUrl = "{{ route('admin.distributor-orders.invoice', ':id') }}".replace(':id', row.id);
                            let btns = `<div class="action-buttons">`;

                            // View Details
                            btns += `<button class="btn btn-info btn-sm view-details-btn" data-row="${rowData}" title="View Details"><i class="fa fa-eye"></i></button>`;
                            // System Invoice
                            btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="System Invoice"><i class="fa fa-print"></i></a>`;

                            // Sales Manager Actions
                            if (isSalesManager && row.status.toLowerCase().includes('pending')) {
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
                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');
                let id = $('#reject_order_id').val();
                let url = "{{ route('admin.distributor-orders.reject', ':id') }}".replace(':id', id);

                let $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('Rejecting...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#rejectOrderModal').modal('hide');
                        table.ajax.reload(null, false);
                        showToast('success', res.success);
                        $('#rejectOrderForm')[0].reset();
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Failed to reject');
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
                    success: function (res) { showToast('success', res.success); table.ajax.reload(null, false); },
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
                    if (res.success) { showToast('success', res.success); table.ajax.reload(null, false); }
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
                        showToast('success', res.success || 'Updated');
                        table.ajax.reload(null, false);
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
                $('#view_distributor').text(row.distributor_name || '--');
                $('#view_total').text('₹' + (row.total_amount || '0'));

                // Status Badge logic
                let status = (row.status || 'pending').toLowerCase();
                let badgeClass = 'bg-soft-secondary';
                if (status === 'pending') badgeClass = 'bg-warning text-dark';
                else if (status === 'processing') badgeClass = 'bg-info text-white';
                else if (status === 'approved' || status === 'delivered') badgeClass = 'bg-success text-white';
                else if (status === 'cancelled' || status === 'rejected') badgeClass = 'bg-danger text-white';

                $('#view_status_badge').text(row.status || 'Pending').removeClass().addClass('badge ' + badgeClass);

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
                        list.append(`
                        <div class="invoice-list-row p-2">
                            <div style="flex: 2;" class="fw-bold text-dark small ps-2">${item.product_name}</div>
                            <div style="flex: 1;" class="fw-bold text-primary text-center">${item.quantity} ${item.unit || 'Nos'}</div>
                            <div style="flex: 1;" class="fw-bold text-dark text-end pe-3">₹${item.total_amount}</div>
                        </div>
                                                `);
                    });
                } else {
                    list.append('<div class="invoice-list-row justify-content-center text-muted">No items</div>');
                }
                $('#viewOrderModal').modal('show');
            });

            // Sales Manager Approve Click
            $(document).on('click', '.approve-order-btn', function () {
                let id = $(this).data('id');
                let row = $(this).data('row');

                $('#approve_order_id').val(id);
                $('#approve_order_code_display').text(row.order_code);
                $('#approve_order_date_display').text(row.placed_at ? row.placed_at.split(' ')[0] : '--');
                $('#approve_distributor_display').text(row.distributor_name);
                $('#approve_location_display').text(row.distributor_location || '--');
                $('#approve_total_display').text('₹' + row.total_amount);

                let list = $('#approve_items_list');
                list.empty();

                if (row.items && row.items.length) {
                    row.items.forEach(item => {
                        list.append(`
                        <div class="invoice-list-row p-2">
                            <div style="flex: 2;" class="fw-bold text-dark small ps-2">${item.product_name}</div>
                            <div style="flex: 1;" class="fw-bold text-primary text-center">${item.quantity} ${item.unit || 'Nos'}</div>
                            <div style="flex: 1;" class="fw-bold text-dark text-end pe-3">₹${item.total_amount}</div>
                        </div>
                                                `);
                    });
                } else {
                    list.append('<div class="invoice-list-row justify-content-center text-muted">No items found</div>');
                }

                $('#approveOrderForm')[0].reset();
                $('#approveOrderModal').modal('show');
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
                        showToast('success', res.success || 'Order approved successfully');
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

                $('#process_order_id').val(id);
                $('#processOrderForm')[0].reset();

                // Populate Premium Header
                $('#process_order_code_display').text(row.order_code || '--');
                $('#process_order_date_display').text(row.placed_at ? row.placed_at.split(' ')[0] : '--');
                $('#process_distributor_display').text(row.distributor_name || '--');
                $('#process_location_display').text(row.distributor_location || '--');

                // Reset Payment Status toggle
                $('input[name="payment_status"][value="pending"]').prop('checked', true);

                // Reset OCR & Automation UI States
                $('#automation_idle_state').removeClass('d-none');
                $('#ocr_processing_state').addClass('d-none');
                $('#verification_table_footer').addClass('d-none');
                $('#scan_file_input').val('');
                $('#automation_success_state').addClass('d-none');
                $('#automation_error_state').addClass('d-none');
                $('#btn_approve_order').prop('disabled', true);
                $('#ocr_dropzone').removeClass('has-file');

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
                                                                <div class="ai-col-product fw-bold text-dark small ps-2">${item.product_name}</div>
                                                                <div class="ai-col-batch">
                                                                    <input type="text" class="form-control form-control-sm v-batch-input border-0 bg-light p-1" 
                                                                           data-id="${item.order_item_id}" placeholder="Wait AI...">
                                                                </div>
                                                                <div class="ai-col-expiry">
                                                                    <input type="text" class="form-control form-control-sm v-expiry-input border-0 bg-light p-1" 
                                                                           data-id="${item.order_item_id}" placeholder="MM/YY">
                                                                </div>
                                                                <div class="ai-col-qty fw-bold text-primary text-center v-qty-display" data-original-unit="${item.unit || 'Nos'}">${item.quantity} ${item.unit || 'Nos'}</div>
                                                                <div class="ai-col-value fw-bold text-dark text-end pe-3 v-taxable-display">--</div>
                                                            </div>
                                                        `;
                        vbody.append(vRowHtml);
                    });
                }

                $('#processOrderModal').modal('show');
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

                // Switch UI to processing
                $('#ocr_processing_state').removeClass('d-none');
                $('#automation_idle_state').addClass('d-none');
                $('#automation_success_state').addClass('d-none');
                $('#automation_error_state').addClass('d-none');
                $('#ocr_dropzone').removeClass('has-file');
                $('#btn_approve_order').prop('disabled', true);

                $('#ocr_progress_bar').css('width', '50%');
                $('#ocr_status_text').text('AI is analyzing your invoice...');

                let formData = new FormData();
                formData.append('invoice', file);
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
                                    let identifiedCount = parseAndFillOCRResponse(res.data);

                                    if (identifiedCount > 0) {
                                        $('#automation_idle_state').addClass('d-none');
                                        $('#automation_error_state').addClass('d-none');
                                        $('#automation_success_state').removeClass('d-none').hide().fadeIn(400);
                                        $('#extracted_metadata_section').show();
                                        $('#processed_summary_text').text(`${identifiedCount} items auto-filled from Invoice.`);
                                        $('#btn_approve_order').prop('disabled', false);
                                    } else {
                                        $('#automation_success_state').addClass('d-none');
                                        $('#automation_error_state').removeClass('d-none').hide().fadeIn(400);
                                        $('#extracted_metadata_section').hide();
                                        $('#btn_approve_order').prop('disabled', true);
                                        showToast('warning', 'Mismatched Invoice: No products identified.');
                                    }
                                } else {
                                    $('#automation_idle_state').removeClass('d-none').hide().fadeIn(400);
                                    showToast('error', 'OCR Failed: Invalid response from server.');
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
                    }
                });
            });

            function parseAndFillOCRResponse(data) {
                let identifiedCount = 0;

                // Extract Overall Metadata
                $('#extract_date').text(data.invoice_metadata ? data.invoice_metadata.date : '--');
                $('#extract_gstin').text(data.invoice_metadata ? data.invoice_metadata.gstin : '--');
                $('#extract_dl').text(data.invoice_metadata && data.invoice_metadata.drug_license ? data.invoice_metadata.drug_license : '--');

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
                        name: name
                    });
                });
                console.log('Order Items to match:', items);

                let missingProducts = [];
                let invoiceProducts = data.line_items || [];
                console.log('AI Invoice Items:', invoiceProducts);

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
                            let displayHtml = `${billedQty} ${unitStr}`;
                            if (freeQty > 0) displayHtml += ` <small class="text-success ms-1">(+${freeQty} Free)</small>`;
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

                    // If OCR provided an exact total, prefer that, else sum of parts
                    let ocrTotalStr = data.invoice_metadata ? data.invoice_metadata.total_amount : '';
                    let ocrTotal = parseFloat(ocrTotalStr);
                    if (!isNaN(ocrTotal) && ocrTotal > 0) {
                        $('#tfoot_net').text(`₹${ocrTotal.toFixed(2)}`);
                    } else {
                        $('#tfoot_net').text(`₹${totalNet.toFixed(2)}`);
                    }
                    $('#verification_table_footer').removeClass('d-none');
                }

                if (missingProducts.length > 0) {
                    let missingList = missingProducts.map(p => `<li>${p.charAt(0).toUpperCase() + p.slice(1)}</li>`).join('');
                    Swal.fire({
                        title: 'Missing Products!',
                        html: `<p>The AI could not find the following ordered products in the invoice:</p><ul class="text-start text-danger">${missingList}</ul><p>Please review the invoice manually.</p>`,
                        icon: 'warning',
                        confirmButtonText: 'I understand'
                    });
                }

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
                        $('#processOrderModal').modal('hide');
                        table.ajax.reload(null, false);
                        showToast('success', res.success);
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Failed to approve order');
                    },
                    complete: function () {
                        $btn.html(oldHtml).prop('disabled', false);
                    }
                });
            });

        });
    </script>
@endpush