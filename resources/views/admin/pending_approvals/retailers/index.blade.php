@extends('layouts.admin')

@section('page-body')
    <style>
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

        #retailer-approval-table td:last-child {
            white-space: nowrap !important;
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
    </style>
    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom pb-0">
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
                        $defaultStatus = $isSMorFS ? 'pending' : 'processing';
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
                                <th>No.</th>
                                <th>Order Code</th>
                                <th>Retailer</th>
                                <th>Products</th>
                                <th>Total</th>
                                <th>Placed At</th>
                                <th>Distributor</th>
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

    {{-- Improved View Details Modal --}}
    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 p-0">
                    <div class="w-100 p-4"
                        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary px-3 py-2">ORDER DETAILS</span>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <h3 class="mb-1 fw-bold" id="view_order_code">--</h3>
                        <div class="d-flex gap-3 small text-white-50">
                            <span><i class="fa fa-calendar me-1"></i> <span id="view_placed_at">--</span></span>
                            <span id="view_status"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3"
                                        style="font-size: 0.75rem; letter-spacing: 1px;">Retailer Information</h6>
                                    <h5 class="fw-bold mb-2 text-dark" id="view_retailer_name">--</h5>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="fa fa-phone me-2 text-primary" style="width: 15px;"></i>
                                            <span id="view_retailer_phone">--</span>
                                        </div>
                                        <div class="d-flex align-items-start text-muted small">
                                            <i class="fa fa-map-marker-alt me-2 text-primary mt-1" style="width: 15px;"></i>
                                            <span id="view_retailer_address">--</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3"
                                        style="font-size: 0.75rem; letter-spacing: 1px;">Distributor Information</h6>
                                    <h5 class="fw-bold mb-2 text-dark" id="view_distributor_name">--</h5>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="fa fa-phone me-2 text-success" style="width: 15px;"></i>
                                        <span id="view_distributor_phone">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm overflow-hidden mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h6 class="mb-0 fw-bold"><i class="fa fa-box-open text-primary me-2"></i>Order Items</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 small text-muted text-uppercase fw-bold py-3 ps-4">Product</th>
                                        <th class="border-0 small text-muted text-uppercase fw-bold py-3">Batch/Exp</th>
                                        <th class="border-0 small text-muted text-uppercase fw-bold py-3 text-center">Qty
                                        </th>
                                        <th class="border-0 small text-muted text-uppercase fw-bold py-3 text-end">Price
                                        </th>
                                        <th class="border-0 small text-muted text-uppercase fw-bold py-3 text-end pe-4">
                                            Total</th>
                                    </tr>
                                </thead>
                                <tbody id="view_items_body">
                                    <!-- Items added via JS -->
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold py-3">Grand Total:</td>
                                        <td class="text-end fw-bold text-primary fs-5 py-3 pe-4" id="view_grand_total">₹0.00
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">Close</button>
                </div>
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                {{-- <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-check-circle me-2"></i> Approve Retailer Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div> --}}
                <form id="approveRetailerOrderForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="approve_retailer_order_id" name="order_id">
                    <div class="modal-body p-0">
                        <!-- Order Summary Header -->
                        <div class="order-summary-header">
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                            <div class="row align-items-center">
                                <div class="col-sm-7">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary me-2">ORDER</span>
                                        <h4 class="mb-0 fw-bold text-white" id="retailer_approve_order_code_display">--</h4>
                                    </div>
                                    <div class="retailer-detail-item">
                                        <i class="fa fa-calendar"></i>
                                        <span id="retailer_approve_order_date_display">--</span>
                                    </div>
                                </div>
                                <div class="col-sm-5 text-sm-end">
                                    <div class="text-white-50 small text-uppercase fw-bold mb-1">Retailer Details</div>
                                    <h5 class="fw-bold text-white mb-1" id="retailer_approve_retailer_display">--</h5>
                                    <div class="retailer-detail-item justify-content-sm-end">
                                        <i class="fa fa-phone"></i>
                                        <span id="retailer_approve_phone_display">--</span>
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
                            <h6 class="fw-bold mb-3 d-flex align-items-center">
                                <span class="bg-light p-2 rounded me-2"><i
                                        class="fa fa-shopping-basket text-primary"></i></span>
                                Order Items Summary
                            </h6>
                            <div class="invoice-list mb-4">
                                <div class="invoice-list-header">
                                    <div style="flex: 2;">Product Name</div>
                                    <div style="flex: 1;" class="text-center">Quantity</div>
                                    <div style="flex: 1;" class="text-end">Value (PTR)</div>
                                </div>
                                <div id="retailer_approve_items_list">
                                    <!-- Items will be populated here -->
                                </div>
                                <div class="invoice-list-footer border-0 shadow-sm bg-light">
                                    <div class="me-3 text-muted">Estimated Total Amount:</div>
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
                    <div class="modal-footer bg-light">
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
                <div class="modal-header bg-primary text-white border-0 py-3 px-4 position-relative">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fa fa-cubes fs-4 text-white"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0">Approve & Allocate Batches</h5>
                            <p class="small text-white text-opacity-75 mb-0" id="approve_order_code_display"></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="distributorApproveForm">
                    <div class="modal-body p-0">
                        <!-- Order Summary Header -->
                        <div class="order-summary-header"
                            style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                            <div class="row align-items-center">
                                <div class="col-sm-7">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">BATCH ALLOCATION</span>
                                        <h4 class="mb-0 fw-bold text-white" id="dist_approve_order_code_display">--</h4>
                                    </div>
                                    <div class="retailer-detail-item">
                                        <i class="fa fa-calendar"></i>
                                        <span id="dist_approve_order_date_display">--</span>
                                    </div>
                                </div>
                                <div class="col-sm-5 text-sm-end">
                                    <div class="text-white-50 small text-uppercase fw-bold mb-1">Retailer / Site</div>
                                    <h5 class="fw-bold text-white mb-1" id="dist_approve_retailer_display">--</h5>
                                    <div class="retailer-detail-item justify-content-sm-end">
                                        <i class="fa fa-map-marker-alt"></i>
                                        <span id="dist_approve_location_display">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-white">
                            <input type="hidden" id="approve_order_id" name="order_id">

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
                                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                        <div>
                                            <h5 class="fw-bold mb-1 text-success"><i
                                                    class="fa fa-check-circle me-2"></i>Scan Complete</h5>
                                            <span class="text-muted small" id="processed_summary_text">Extracted
                                                metadata</span>
                                        </div>
                                        <div class="text-end" id="extracted_metadata_section">
                                            <div class="d-flex gap-3">
                                                <div>
                                                    <small class="text-muted d-block text-uppercase fw-bold"
                                                        style="font-size: 0.7rem;">Date</small>
                                                    <span class="fw-bold" id="meta_date">--</span>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block text-uppercase fw-bold"
                                                        style="font-size: 0.7rem;">GSTIN</small>
                                                    <span class="fw-bold" id="meta_gstin">--</span>
                                                </div>
                                            </div>
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
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-outline-danger fw-bold border-2 px-4 py-2 me-auto reject-retailer-btn" style="border-radius: 12px;">
                            <i class="fa fa-times me-2"></i>Reject Order
                        </button>
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none px-4" data-bs-dismiss="modal">Go Back</button>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm" id="btnSubmitDistributorApprove" 
                            style="border-radius: 12px; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                            Finalize & Approve
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

            var table = $('#retailer-approval-table').DataTable({
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
                    "<'row mb-3 d-flex align-items-center'<'col-md-4'l><'col-md-4 d-flex justify-content-center payment-filter-container'><'col-md-4'f>>" +
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
                    var $filter = $('#filter_container').children().first();
                    $('.payment-filter-container').append($filter);
                    $('#filter_container').remove();
                    
                    // Definitive Tab Activation using Bootstrap API
                    // This ensures the correct tab is marked active and internal state is synced
                    const $targetTab = $('#tab-' + INITIAL_STATUS);
                    if ($targetTab.length) {
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
                    data: null,
                    defaultContent: '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'order_code',
                    name: 'order_code',
                    render: function (data, type, row) {
                        return data;
                    }
                },
                {
                    data: 'retailer_name',
                    name: 'retailer.user.name',
                    render: function(data, type, row) {
                        return `<span class="fw-bold text-primary" 
                                      style="cursor: pointer;"
                                      data-bs-toggle="popover" 
                                      data-bs-trigger="hover" 
                                      data-bs-html="true"
                                      title="Retailer Details"
                                      data-bs-content="<b>Shop:</b> ${row.retailer_name}<br><b>SM:</b> ${row.retailer_sm_name}<br><b>FS:</b> ${row.retailer_fs_name}<br><b>Phone:</b> ${row.retailer_phone}<br><b>GST:</b> ${row.retailer_gst}<br><b>DL:</b> ${row.retailer_dl}">
                                    ${data}
                                </span>`;
                    }
                },
                {
                    data: 'product_summary',
                    name: 'product_summary',
                    render: function (d) {
                        return d.length > 50 ? d.substring(0, 50) + '...' : d;
                    }
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    render: function(data) {
                        return `<span class="fw-bold text-success">₹${data}</span>`;
                    }
                },
                {
                    data: 'placed_at',
                    name: 'placed_at'
                },
                {
                    data: 'distributor_name',
                    name: 'distributor.user.name',
                    visible: isAdmin || isSalesManager,
                    render: function(data, type, row) {
                        return `<span class="fw-bold text-primary" 
                                      style="cursor: pointer;"
                                      data-bs-toggle="popover" 
                                      data-bs-trigger="hover" 
                                      data-bs-html="true"
                                      title="Distributor Details"
                                      data-bs-content="<b>Phone:</b> ${row.distributor_phone || 'N/A'}<br><b>GST:</b> ${row.distributor_gst || 'N/A'}<br><b>DL:</b> ${row.distributor_dl || 'N/A'}">
                                    ${data}
                                </span>`;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data, type, row) {
                        let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';
                        let bgClass = 'bg-secondary text-white';
                        let displayStatus = row.status;

                        if (statusRaw === 'pending') bgClass = 'bg-warning text-dark';
                        else if (statusRaw === 'processing') {
                            bgClass = 'bg-primary text-white';
                            displayStatus = 'Processing';
                        }
                        else if (statusRaw === 'approved') {
                            bgClass = 'bg-info text-white';
                            displayStatus = 'Approved';
                        }
                        else if (statusRaw === 'delivered') bgClass = 'bg-success text-white';
                        else if (statusRaw === 'cancelled') bgClass = 'bg-danger text-white';
                        else if (statusRaw === 'rejected') bgClass = 'bg-danger text-white';

                        return `<span class="badge ${bgClass}">${displayStatus}</span>`;
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    render: function (data, type, row) {
                        let payStatus = row.payment_status ? row.payment_status.toLowerCase() : 'pending';
                        let bgClass, displayLabel;

                        if (payStatus === 'paid') {
                            bgClass = 'bg-success text-white';
                            displayLabel = 'Paid';
                        } else {
                            bgClass = 'bg-warning text-dark';
                            displayLabel = 'Pending';
                        }

                        let canChangePayment = isAdmin || isDistributor;
                        let cursorStyle = canChangePayment ? 'cursor: pointer;' : '';
                        let clickableClass = canChangePayment ? 'change-payment-status' : '';

                        return `<span class="badge ${bgClass} ${clickableClass}" data-id="${row.id}" data-status="${payStatus}" style="font-size: 0.85rem; ${cursorStyle}">${displayLabel}</span>`;
                    }
                },
                {
                    data: null,
                    name: 'invoice',
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
                            showToast('error', res.error || 'Failed to approve order');
                        }
                    },
                    error: function (xhr) {
                        let errMsg = xhr.responseJSON ? xhr.responseJSON.error || xhr.responseJSON.message : 'An error occurred during approval.';
                        showToast('error', errMsg);
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
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Update failed');
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
                        if (newStatus.includes('pending')) newClass = 'bg-warning text-dark';
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
                        if (newStatus == 'pending') newClass = 'bg-warning text-dark';
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
                if (isAdmin) {
                    Swal.fire({
                        title: 'Confirm Admin Action',
                        text: 'This invoice should ideally be uploaded by the respective distributor. Do you still want to proceed as an Admin?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, proceed',
                        cancelButtonText: 'No, cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentOrderIdForInvoice = id;
                            $('#invoice_upload_input').click();
                        }
                    });
                } else {
                    currentOrderIdForInvoice = id;
                    $('#invoice_upload_input').click();
                }
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
                $('#view_status').html(`<span class="badge bg-soft-primary text-primary px-3" style="font-size: 0.9rem;">${row.status || 'Pending'}</span>`);

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
                            item.batches.map(b => `<div class="small text-muted">B: ${b.batch_no} | E: ${b.expiry_date}</div>`).join('') :
                            '<span class="text-muted small">Not Allocated</span>';

                        tbody.append(`
                                                        <tr class="align-middle">
                                                            <td class="py-3">
                                                                <div class="fw-bold text-dark">${item.product_name}</div>
                                                                <div class="small text-muted">${item.product_code || ''}</div>
                                                            </td>
                                                            <td>${batchInfo}</td>
                                                            <td class="text-center">${item.quantity} ${item.unit || 'Strips'}</td>
                                                            <td class="text-end">₹${parseFloat(item.unit_price).toFixed(2)}</td>
                                                            <td class="text-end fw-bold text-primary">₹${parseFloat(item.total_amount).toFixed(2)}</td>
                                                        </tr>
                                                    `);
                    });
                } else {
                    tbody.html('<tr><td colspan="5" class="text-center py-4 text-muted italic">No items found in this order</td></tr>');
                }

                $('#view_grand_total').text(`₹${parseFloat(row.total_amount).toFixed(2)}`);
                $('#view_notes').text(row.delivery_notes || 'No notes provided.');

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
                            list.append(`
                                <div class="invoice-list-row">
                                    <div style="flex: 2;" class="fw-bold text-dark">${item.product_name}</div>
                                    <div style="flex: 1;" class="text-center text-muted small">${item.quantity} ${item.unit || 'Box'}</div>
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

                    $('#approveRetailerOrderModal').modal('show');
                };

                if (isAdmin) {
                    Swal.fire({
                        title: 'Confirm Admin Approval',
                        text: 'This order should ideally be approved by the respective distributor. Do you still want to proceed as an Admin?',
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
                            showToast('success', res.success);
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                        } else {
                            showToast('error', res.error || 'Failed to approve order');
                        }
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Approval failed');
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

                    // Reset payment status to pending
                    $('input[name="payment_status"][value="pending"]').prop('checked', true);

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
                                                                                                                                                                                        <div class="fw-bold product-name-marker">${item.product_name}</div>
                                                                                                                                                                                        <input type="number" name="items[${orderItemId}][quantity]" value="${orderedQty}">
                                                                                                                                                                                        <input type="hidden" name="items[${orderItemId}][product_id]" value="${productId}">
                                                                                                                                                                                        <input type="hidden" name="items_batches[${orderItemId}][order_item_id]" value="${orderItemId}">
                                                                                                                                                                                    </div>
                                                                                                                                                                                    <div class="d-none" id="batches_for_${orderItemId}">
                                                                                                                                                                                        <input type="text" name="items_batches[${orderItemId}][batches][0][batch_no]" class="hidden-batch-val" required>
                                                                                                                                                                                        <input type="date" name="items_batches[${orderItemId}][batches][0][expiry_date]" class="hidden-expiry-val" required>
                                                                                                                                                                                        <input type="number" name="items_batches[${orderItemId}][batches][0][quantity]" class="hidden-qty-val" value="${orderedQty}" required>
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
                                                                                                                                                                                    <div class="ai-col-product fw-bold text-dark small">${item.product_name}</div>
                                                                                                                                                                                    <div class="ai-col-batch v-batch-display text-muted small" data-id="${orderItemId}">--</div>
                                                                                                                                                                                    <div class="ai-col-expiry v-expiry-display text-muted small" data-id="${orderItemId}">--</div>
                                                                                                                                                                                    <div class="ai-col-qty fw-bold text-primary v-qty-display" data-original-unit="${item.unit || ''}">${orderedQty} ${item.unit || ''}</div>
                                                                                                                                                                                    <div class="ai-col-value text-end small text-dark fw-bold v-taxable-display">--</div>
                                                                                                                                                                                    <div class="ai-col-value text-end small text-muted v-gst-display">--</div>
                                                                                                                                                                                    <div class="ai-col-value text-end fw-bold v-net-display">--</div>
                                                                                                                                                                                </div>
                                                                                                                                                                            `;
                        vbody.append(vRowHtml);
                    });
                };

                if (isAdmin) {
                    Swal.fire({
                        title: 'Confirm Admin Approval',
                        text: 'This order should ideally be approved by the respective distributor. Do you still want to proceed as an Admin?',
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

            // File upload logic for AI Processing
            $(document).on('change', '#scan_retailer_file_input', function () {
                const file = this.files[0];
                if (!file) return;

                $('#ocr_dropzone').addClass('d-none'); // Hide the dropzone
                $('#results_loading_spinner').removeClass('d-none'); // Show loading spinner
                $('#automation_success_state').hide();
                $('#automation_success_footer').addClass('d-none');
                $('#automation_error_state').addClass('d-none');
                $('#batch_allocation_table_container').addClass('d-none');

                let formData = new FormData();
                formData.append('invoice', file);
                formData.append('type', 'retailer');
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
                            let identifiedCount = parseRetailerOCRResponse(res.data);

                            if (identifiedCount > 0) {
                                $('#automation_idle_state').hide();
                                $('#results_loading_spinner').addClass('d-none');
                                $('#automation_success_state').fadeIn();
                                $('#automation_success_footer').removeClass('d-none');
                                $('#batch_allocation_table_container').removeClass('d-none'); // Show grid
                                $('#processed_summary_text').text(`${identifiedCount} products mapped from Invoice.`);
                            } else {
                                $('#automation_idle_state').hide();
                                $('#results_loading_spinner').addClass('d-none');
                                $('#automation_error_state').removeClass('d-none').fadeIn();
                                $('#batch_allocation_table_container').addClass('d-none');
                                showToast('warning', 'Mismatched Invoice: No products identified.');
                            }
                        } else {
                            $('#automation_idle_state').show();
                            $('#ocr_idle_state').show();
                            showToast('error', 'OCR Failed: Invalid response from server.');
                        }
                    },
                    error: function (xhr) {
                        console.error('OCR Error:', xhr);
                        // $('#ocr_processing_state').addClass('d-none');
                        $('#results_loading_spinner').addClass('d-none');
                        $('#ocr_dropzone').removeClass('d-none');
                        showToast('error', 'OCR Failed: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Processing error'));
                    }
                });
            });


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
                let identifiedCount = 0;
                let missingProducts = [];
                let invoiceProducts = data.line_items || [];
                let totalInvoiceNet = 0;

                console.log('Retailer AI Invoice Items:', invoiceProducts);

                // Update Metadata visually
                if (data.invoice_metadata) {
                    $('#meta_date').text(data.invoice_metadata.date || '--');
                    $('#meta_gstin').text(data.invoice_metadata.gstin || '--');
                }

                $('.product-row').each(function () {
                    let container = $(this);
                    let orderItemId = container.data('item-id');
                    let productName = container.find('.product-name-marker').text().trim().toLowerCase();
                    let orderedQty = parseInt(container.data('ordered-qty'));

                    // Helper to normalize strings for better matching
                    const normalize = (str) => {
                        if (!str) return '';
                        return str.toLowerCase()
                            .replace(/\bsyp\b|\bsyrup\b/g, 'syrup')
                            .replace(/\btab\b|\btablet\b/g, 'tablet')
                            .replace(/\bcap\b|\bcapsule\b/g, 'capsule')
                            .replace(/\(\d+\)/g, '') // Remove pack sizes like (10), (5)
                            .replace(/\([^)]*\)/g, ' ') // Remove anything in parentheses
                            .replace(/[^a-z0-9\s]/g, ' ') // Replace non-alphanumeric with space
                            .replace(/\s+/g, ' ')
                            .trim();
                    };

                    let normProductName = normalize(productName);

                    // Try to extract a product code (e.g., first part of the name before spaces/hyphens)
                    let pCodeMatch = container.data('p-code') || productName.split(/[\s\-]/)[0].toLowerCase();
                    // 1. Try exact or full inclusion match
                    let matchedIdx = invoiceProducts.findIndex(p => {
                        if (!p.description) return false;
                        let normDesc = normalize(p.description);
                        let normAiCode = p.p_code ? String(p.p_code).toLowerCase() : '';

                        // If p_code matches exactly, it's a very strong indicator
                        if (pCodeMatch && normAiCode && normAiCode.includes(pCodeMatch)) {
                            return true;
                        }

                        return normDesc === normProductName || normDesc.includes(normProductName) || normProductName.includes(normDesc);
                    });

                    // 2. Fallback to word-based intersection check
                    if (matchedIdx === -1) {
                        let productWords = normProductName.split(' ').filter(w => w.length > 2);
                        matchedIdx = invoiceProducts.findIndex(p => {
                            if (!p.description) return false;
                            let normDesc = normalize(p.description);
                            let matchCount = 0;
                            productWords.forEach(word => {
                                if (normDesc.includes(word)) matchCount++;
                            });
                            // Match if at least 60% of words found (minimum 1 word if possible)
                            let threshold = Math.max(1, Math.ceil(productWords.length * 0.6));
                            return matchCount >= threshold;
                        });
                    }

                    // 3. Last fallback to substring match
                    if (matchedIdx === -1) {
                        matchedIdx = invoiceProducts.findIndex(p => p.description && p.description.toLowerCase().includes(normProductName.substring(0, 7)));
                    }

                    if (matchedIdx !== -1) {
                        let matchedInvoiceItem = invoiceProducts[matchedIdx];
                        invoiceProducts.splice(matchedIdx, 1); // Remove from pool
                        identifiedCount++;
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
                                vRow.find('.v-expiry-display').text(extExpiry).removeClass('text-muted').addClass('text-success fw-bold');
                            }

                            let origUnit = vRow.find('.v-qty-display').data('original-unit') || '';
                            let displayQty = `${billedQty} ${origUnit}`;
                            if (freeQty > 0) {
                                displayQty += ` <span class="text-success small">(+${freeQty} Free)</span>`;
                            }
                            vRow.find('.v-qty-display').html(displayQty);

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
                if (data.invoice_metadata && data.invoice_metadata.total_amount) {
                    let metaTotal = parseFloat(data.invoice_metadata.total_amount) || 0;
                    $('#v_total_meta').text(`₹${metaTotal.toFixed(2)}`);
                }

                if (missingProducts.length > 0) {
                    let missingList = missingProducts.map(p => `<li>${p.charAt(0).toUpperCase() + p.slice(1)}</li>`).join('');
                    Swal.fire({
                        title: 'Missing Products!',
                        html: `<p>The AI could not find the following ordered products in the invoice:</p><ul class="text-start text-danger">${missingList}</ul><p>Please enter their batch and expiry manually.</p>`,
                        icon: 'warning',
                        confirmButtonText: 'I understand'
                    });
                }

                return identifiedCount;
            }
        });
    </script>
@endpush