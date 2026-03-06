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
    </style>
    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Retailer Order Approvals</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div> @endif

                <div id="filter_container" class="d-none">
                    <div class="d-inline-flex align-items-center gap-3 ms-2">
                        <div class="d-flex align-items-center">
                            <label for="status_filter" class="form-label me-2 mb-0 fw-bold">Status:</label>
                            <select id="status_filter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="accepted">Accepted</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center">
                            <label for="payment_status_filter" class="form-label me-2 mb-0 fw-bold">Payment:</label>
                            <select id="payment_status_filter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">All Payments</option>
                                <option value="paid">Paid</option>
                                <option value="pending">Unpaid</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="retailer-approval-table">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Order Code</th>
                                <th>Retailer</th>
                                <th>Products</th>
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

    {{-- View Details Modal --}}
    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6"><strong>Order Code:</strong> <span id="view_order_code"></span></div>
                        <div class="col-6"><strong>Placed At:</strong> <span id="view_placed_at"></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>Retailer:</strong> <span id="view_retailer"></span></div>
                        <div class="col-6"><strong>Status:</strong> <span id="view_status"></span></div>
                    </div>
                    <h6 class="mt-4">Items</h6>
                    <div class="table-responsive bg-light p-2 rounded">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="view_items_body"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Grand Total:</th>
                                    <th id="view_total"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    {{-- <div class="mt-3">
                        <strong>Delivery Notes:</strong>
                        <p id="view_notes" class="text-muted small"></p>
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Retailer Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveRetailerOrderForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="approve_retailer_order_id" name="order_id">
                    <div class="modal-body">
                        <p class="mb-3" id="approveModalText">Are you sure you want to approve this order? Please upload the
                            finalized invoice.</p>
                        <div class="mb-3" id="invoiceUploadGroup">
                            <label class="form-label">Upload Invoice (PDF, JPG, PNG) <span
                                    class="text-danger">*</span></label>
                            <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Distributor Batch Selection & OCR Modal --}}
    <div class="modal fade" id="distributorApproveModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 overflow-hidden">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="fa fa-file-invoice-dollar me-2"></i> Approve Retailer Order &
                        Allocate Batches</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="distributorApproveForm">
                    <div class="modal-body p-0">
                        <input type="hidden" id="approve_order_id" name="order_id">

                        <div class="row g-0">
                            <!-- Left Sidebar: Configuration & Scanning -->
                            <div class="col-md-4 bg-light p-4">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-uppercase small text-muted">1. Payment
                                        Setup</label>
                                    <div class="p-3 bg-white rounded shadow-sm">
                                        <select class="form-select border-0 fs-6 fw-bold" name="payment_status" required>
                                            <option value="pending">⏳ Pending Payment</option>
                                            <option value="paid">✅ Already Paid</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-uppercase small text-muted">2. Smart Invoice
                                        Processing</label>
                                    <div class="ocr-dropzone p-4 text-center border-2 border-dashed rounded-3 bg-white shadow-sm"
                                        id="ocr_dropzone" style="cursor: pointer; transition: 0.3s; position: relative;">
                                        <div id="ocr_idle_state">
                                            <i class="fa fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <h6 class="fw-bold">Upload Store Invoice</h6>
                                            <p class="text-muted small mb-0">Drag & drop or click to scan (PDF/Image)</p>
                                        </div>
                                        <div id="ocr_processing_state"
                                            class="position-absolute top-50 start-50 translate-middle w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white rounded-3 d-none"
                                            style="z-index: 2; opacity: 0.9;">
                                            <div class="spinner-border text-primary mb-2" role="status"></div>
                                            <h6 class="fw-bold mb-0 small" id="ocr_status_text">Processing...</h6>
                                        </div>
                                    </div>
                                    <input type="file" class="d-none" name="invoice" id="scan_retailer_file_input"
                                        accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text mt-2"><i class="fa fa-info-circle text-info"></i> Batch & Expiries
                                        will be auto-extracted locally. The invoice document is required.</div>
                                </div>
                            </div>

                            <!-- Right Content: Automation Summary & Batch Allocation -->
                            <div class="col-md-8 p-4 bg-white">
                                <div id="automation_idle_state" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fa fa-robot fa-4x text-light"></i>
                                    </div>
                                    <h5 class="text-muted">Waiting for AI Scan...</h5>
                                    <p class="text-muted small">Upload an invoice to automatically process batch details.
                                    </p>

                                    <div id="results_loading_spinner" class="mt-4 d-none">
                                        <div class="spinner-border text-primary mb-3" role="status"
                                            style="width: 3rem; height: 3rem;"></div>
                                        <h5 class="fw-bold text-primary">AI is analyzing your invoice...</h5>
                                        <p class="text-muted">Extracting products, batches, and expiries.</p>
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

                                <div class="table-responsive mt-3 d-none" id="batch_allocation_table_container">
                                    <table class="table table-bordered table-sm align-middle mb-0" id="verification_table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 25%;">Product</th>
                                                <th style="width: 15%;">Batch</th>
                                                <th style="width: 12%;">Expiry</th>
                                                <th style="width: 10%;" class="text-center">Qty</th>
                                                <th style="width: 12%;" class="text-end pe-3">Taxable</th>
                                                <th style="width: 12%;" class="text-end pe-3">GST</th>
                                                <th style="width: 14%;" class="text-end pe-3">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="verification_table_body">
                                            <!-- AI Verification Rows -->
                                        </tbody>
                                        <tfoot id="verification_table_footer" class="table-light d-none">
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold py-1 border-0">Calculated Net Total:
                                                </td>
                                                <td colspan="3" class="text-end pe-3 fw-bold text-muted py-1 border-0"
                                                    id="v_total_net">₹0.00</td>
                                            </tr>
                                            <tr class="table-success">
                                                <td colspan="4" class="text-end fw-bold py-2">Invoiced Total (AI Scan):</td>
                                                <td colspan="3" class="text-end pe-3 fw-bold text-success fs-5 py-2"
                                                    id="v_total_meta">₹0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <!-- Hidden Inputs for Submission -->
                                    <div id="batch_entry_body" class="d-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0 py-3">
                        <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm"
                            id="btnSubmitDistributorApprove">Approve & Allocate Stock</button>
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
                                <option value="pending">Unpaid</option>
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
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            const isFieldStaff = {{ Auth::user()->hasRole('fieldstaff') ? 'true' : 'false' }};
            const isDistributor = {{ Auth::user()->hasRole('distributor') ? 'true' : 'false' }};
            const isRetailer = {{ Auth::user()->hasRole('retailer') ? 'true' : 'false' }};
            const isSalesManager = {{ Auth::user()->hasRole('salesmanager') ? 'true' : 'false' }};
            const isAdmin = {{ Auth::user()->hasAnyRole(['admin', 'superadmin']) ? 'true' : 'false' }};

            var table = $('#retailer-approval-table').DataTable({
                dom: "<'row mb-3'<'col-sm-12'B>>" + // Buttons on top
                    "<'row mb-3 d-flex align-items-center'<'col-md-6'l><'col-md-6'f>>" + // 'l' (length) on left, 'f' (filter/search) on right
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
                    var $filter = $('#filter_container').children().first();
                    $('.dt-buttons').append($filter);
                    $('#filter_container').remove();
                },
                ajax: {
                    url: window.location.href,
                    data: function (d) {
                        d.status = $('#status_filter').val();
                        d.payment_status = $('#payment_status_filter').val();
                    }
                },
                columns: [{
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'order_code',
                    name: 'order_code',
                    render: function (data, type, row) {
                        let code = data;
                        if (row.invoice_url) {
                            code += ' <i class="fa fa-check-circle text-success" title="Invoice Uploaded"></i>';
                        }
                        return code;
                    }
                },
                {
                    data: 'retailer_name',
                    name: 'retailer_name'
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
                    name: 'total_amount'
                },
                {
                    data: 'placed_at',
                    name: 'placed_at'
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
                            bgClass = 'bg-info text-white';
                            displayStatus = 'Processing';
                        }
                        else if (statusRaw === 'accepted') {
                            bgClass = 'bg-primary text-white';
                            displayStatus = 'Accepted';
                        }
                        else if (statusRaw === 'delivered') bgClass = 'bg-success text-white';
                        else if (statusRaw === 'cancelled') bgClass = 'bg-danger text-white';

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
                            displayLabel = 'Unpaid';
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
                                                                                                                                                                                                                                                                                                                                                    <i class="fa ${icon}"></i> &nbsp;View
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
                        if (isFieldStaff && statusRaw === 'pending') canApprove = true;
                        if (isDistributor && statusRaw === 'processing') canApprove = true;
                        if ((isAdmin || isSalesManager) && (statusRaw === 'pending' || statusRaw === 'processing')) canApprove = true;

                        if (canApprove) {
                            if ((isDistributor || isAdmin || isSalesManager) && (statusRaw === 'processing' || statusRaw === 'pending')) {
                                btns += `<button class="btn btn-success btn-sm distributor-approve-btn" data-row="${rowData}" title="Approve & Allocate Batches"><i class="fa fa-check-circle"></i></button>`;
                            } else {
                                btns += `<button class="btn btn-success btn-sm approve-retailer-btn" data-id="${row.id}" title="Approve"><i class="fa fa-check"></i></button>`;
                            }
                        }

                        btns += `<button class="btn btn-info btn-sm view-details-btn" data-row="${rowData}" title="View Details"><i class="fa fa-eye"></i></button>`;
                        let invoiceUrl = "{{ route('admin.retailer.invoice', ':id') }}".replace(':id', row.id);
                        btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="Print Invoice"><i class="fa fa-print"></i></a>`;

                        // Retailer Confirmation
                        if (statusRaw === 'accepted') {
                            if (isRetailer) {
                                btns += `<button class="btn btn-primary btn-sm confirm-receipt-btn" data-id="${row.id}" title="Confirm Order"> Confirm</button>`;
                            }
                        }
                        btns += `</div>`;
                        return btns;
                    }
                }
                ]
            });

            $('#status_filter').change(function () {
                table.ajax.reload();
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
                        else if (newStatus.includes('accepted')) newClass = 'bg-info text-white';
                        else if (newStatus.includes('delivered')) newClass = 'bg-success text-white';
                        else if (newStatus.includes('cancelled')) newClass = 'bg-danger text-white';

                        $select.addClass(newClass);
                        $select.data('original', newStatus);
                        showToast('success', res.success);
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
                currentOrderIdForInvoice = $(this).data('id');
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
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Upload failed');
                        $btn.html(oldHtml).prop('disabled', false);
                    }
                });
                $(this).val('');
            });

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

            // View Modal Logic
            $(document).on('click', '.view-details-btn', function () {
                let row = $(this).data('row');
                $('#view_order_code').text(row.order_code);
                $('#view_placed_at').text(row.placed_at);
                $('#view_retailer').text(row.retailer_name);
                $('#view_status').text(row.status);
                $('#view_total').text(row.total_amount);
                $('#view_notes').text(row.delivery_notes || '-');

                let tbody = $('#view_items_body');
                tbody.empty();

                if (row.items && row.items.length) {
                    row.items.forEach(item => {
                        tbody.append(`<tr>
                                                                                                                                                                                                                                                                                                                                <td>${item.product_name}</td>
                                                                                                                                                                                                                                                                                                                                <td>${item.quantity}</td>
                                                                                                                                                                                                                                                                                                                                <td>${item.unit_price}</td>
                                                                                                                                                                                                                                                                                                                                <td>${item.total_amount}</td>
                                                                                                                                                                                                                                                                                                                            </tr>`);
                    });
                } else {
                    tbody.html('<tr><td colspan="4" class="text-center">No items</td></tr>');
                }
                $('#viewOrderModal').modal('show');
            });

            // Approve Retailer Order (Field Staff / Manager / Distributor)
            $(document).on('click', '.approve-retailer-btn', function () {
                let id = $(this).data('id');
                $('#approve_retailer_order_id').val(id);
                $('#approveRetailerOrderForm')[0].reset();

                if (isFieldStaff) {
                    $('#invoiceUploadGroup').hide();
                    $('#invoiceUploadGroup input').prop('required', false);
                    $('#approveModalText').text('Are you sure you want to approve this order?');
                } else {
                    $('#invoiceUploadGroup').show();
                    $('#invoiceUploadGroup input').prop('required', true);
                    $('#approveModalText').text('Are you sure you want to approve this order? Please upload the finalized invoice.');
                }

                $('#approveRetailerOrderModal').modal('show');
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
                $('#approve_order_id').val(row.id);
                let tbody = $('#batch_entry_body');
                let vbody = $('#verification_table_body');

                // Reset UI visibility statuses when modal is opened anew
                $('#automation_idle_state').show();
                $('#automation_success_state').hide();
                $('#ocr_processing_state').hide();
                $('#ocr_idle_state').show();
                $('#batch_allocation_table_container').addClass('d-none');
                $('#scan_retailer_file_input').val(''); // Clear old file inputs

                $('#distributorApproveModal').modal('show');

                let itemsHtml = '';
                let itemsProcessed = 0;

                if (!row.items || row.items.length === 0) {
                    vbody.html('<tr><td colspan="7" class="text-center text-danger">No items found in this order.</td></tr>');
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
                                                                                                                                    <tr data-item-id="${orderItemId}" class="product-row" data-ordered-qty="${orderedQty}">
                                                                                                                                        <td class="d-none">
                                                                                                                                            <div class="fw-bold product-name-marker">${item.product_name}</div>
                                                                                                                                            <input type="number" name="items[${orderItemId}][quantity]" value="${orderedQty}">
                                                                                                                                            <input type="hidden" name="items[${orderItemId}][product_id]" value="${productId}">
                                                                                                                                            <input type="hidden" name="items_batches[${orderItemId}][order_item_id]" value="${orderItemId}">
                                                                                                                                        </td>
                                                                                                                                        <td class="d-none" id="batches_for_${orderItemId}">
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
                                                                                                                                        </td>
                                                                                                                                    </tr>
                                                                                                                                `;
                    tbody.append(rowHtml);

                    // 2. Visible Verification Row
                    let vRowHtml = `
                                                                                                                <tr id="v_row_${orderItemId}">
                                                                                                                    <td class="ps-3 py-2">
                                                                                                                        <div class="fw-bold text-dark small">${item.product_name}</div>
                                                                                                                    </td>
                                                                                                                    <td class="py-2">
                                                                                                                        <span class="v-batch-display text-muted small" data-id="${orderItemId}">--</span>
                                                                                                                    </td>
                                                                                                                    <td class="py-2">
                                                                                                                        <span class="v-expiry-display text-muted small" data-id="${orderItemId}">--</span>
                                                                                                                    </td>
                                                                                                                    <td class="text-center pe-3 fw-bold text-primary v-qty-display" data-original-unit="${item.unit || ''}">${orderedQty} ${item.unit || ''}</td>
                                                                                                                    <td class="text-end pe-3 small text-dark fw-bold v-taxable-display">--</td>
                                                                                                                    <td class="text-end pe-3 small text-muted v-gst-display">--</td>
                                                                                                                    <td class="text-end pe-3 small text-dark fw-bold v-net-display">--</td>
                                                                                                                </tr>
                                                                                                            `;
                    vbody.append(vRowHtml);
                });
            });

            // OCR Dropzone Click
            $(document).on('click', '#ocr_dropzone', function () {
                $('#scan_retailer_file_input').click();
            });

            // File upload logic for AI Processing
            $(document).on('change', '#scan_retailer_file_input', function () {
                const file = this.files[0];
                if (!file) return;

                // $('#ocr_idle_state').hide(); // Keep it visible as per user request
                // $('#ocr_processing_state').removeClass('d-none'); // Disable overlay as per user request
                $('#automation_idle_state').find('i, h5, p:not(.text-muted)').hide(); // Hide idle robot/text
                $('#results_loading_spinner').removeClass('d-none'); // Show loading spinner in results area

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
                            // $('#ocr_processing_state').addClass('d-none');
                            $('#results_loading_spinner').addClass('d-none');
                            $('#automation_idle_state').find('i, h5, p').show(); // Reset idle state
                        }, 500);

                        if (res.success && res.data) {
                            let identifiedCount = parseRetailerOCRResponse(res.data);

                            if (identifiedCount > 0) {
                                $('#automation_idle_state').hide();
                                $('#automation_success_state').show();
                                $('#batch_allocation_table_container').removeClass('d-none'); // Show grid
                                $('#processed_summary_text').text(`${identifiedCount} products mapped from Invoice.`);
                            } else {
                                $('#automation_idle_state').show();
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
                        $('#automation_idle_state').find('i, h5, p').show();
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

                    // Try to find the item in the JSON response
                    let matchedInvoiceItem = invoiceProducts.find(p => p.description && p.description.toLowerCase().includes(productName.substring(0, 5)));

                    if (matchedInvoiceItem) {
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
                        let freeQty = safeParse(matchedInvoiceItem.free);

                        let extTaxable = safeParse(matchedInvoiceItem.taxable_amt) || safeParse(matchedInvoiceItem.amount);
                        if (extTaxable === 0 && billedQty > 0 && extRate > 0) {
                            extTaxable = billedQty * extRate;
                        }

                        let extCgst = safeParse(matchedInvoiceItem.cgst);
                        let extSgst = safeParse(matchedInvoiceItem.sgst);
                        let extIgst = safeParse(matchedInvoiceItem.igst);
                        let extGstAmt = safeParse(matchedInvoiceItem.gst_amt);

                        // If individual GST components are missing but total GST amt exists
                        let extGst = extCgst + extSgst + extIgst;
                        if (extGst === 0 && extGstAmt > 0) extGst = extGstAmt;

                        let itemNet = extTaxable + extGst;
                        let totalQty = billedQty + freeQty > 0 ? (billedQty + freeQty) : orderedQty;

                        // 1. Update Visible Row Fields
                        let vRow = $(`#v_row_${orderItemId}`);
                        if (vRow.length) {
                            if (extBatch) {
                                vRow.find('.v-batch-display').text(extBatch).removeClass('text-muted').addClass('text-success fw-bold');
                            }
                            if (extExpiry) {
                                vRow.find('.v-expiry-display').text(extExpiry).removeClass('text-muted').addClass('text-success fw-bold');
                            }
                            let origUnit = vRow.find('.v-qty-display').data('original-unit') || '';
                            vRow.find('.v-qty-display').text(`${totalQty} ${origUnit}`);

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
                        }

                        totalInvoiceNet += itemNet;

                    } else {
                        missingProducts.push(productName);
                        // Mark missing visually
                        let vRow = $(`#v_row_${orderItemId}`);
                        if (vRow.length) {
                            vRow.addClass('table-danger');
                            vRow.find('.v-batch-display').text('Manual Entry Required').addClass('text-danger fw-bold');
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

            // Batch Form Validation and Submission
            $('#distributorApproveForm').submit(function (e) {
                e.preventDefault();
                let isValid = true;

                // Validate each product row
                $('.product-row').each(function () {
                    let orderItemId = $(this).data('item-id');
                    let ordered = parseInt($(this).data('ordered-qty'));

                    let hContainer = $(`#batches_for_${orderItemId}`);
                    let allocated = parseInt(hContainer.find('.hidden-qty-val').val() || 0);

                    let vRow = $(`#v_row_${orderItemId}`);

                    if (allocated !== ordered) {
                        isValid = false;
                        vRow.addClass('table-danger');
                    } else {
                        vRow.removeClass('table-danger');
                    }

                    // Also check if batches and expiry are filled
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
                let fileInput = document.getElementById('scan_retailer_file_input');
                if (!fileInput.files.length) {
                    showToast('error', 'Invoice document is strictly required for approval.');
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
                            showToast('success', res.success);
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
                            } else {
                                showToast('error', res.error || 'Failed to confirm order');
                            }
                        }).fail(function (xhr) {
                            showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                        });
                    }
                });
            });

            // Reject Retailer Order
            $(document).on('click', '.reject-retailer-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Reject Order?',
                    text: "Are you sure you want to reject this order?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Reject',
                    confirmButtonColor: '#dc3545'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ route('admin.retailer.update-status', ':id') }}".replace(':id', id);
                        $.post(url, { _token: '{{ csrf_token() }}', status: 'cancelled' }, function (res) {
                            if (res.success) {
                                table.ajax.reload(null, false);
                                showToast('success', res.success);
                            } else {
                                showToast('error', res.error || 'Failed to reject');
                            }
                        }).fail(function (xhr) {
                            showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                        });
                    }
                });
            });

            // Approve Cancellation
            $(document).on('click', '.approve-cancel-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Approve Cancellation?',
                    text: "Are you sure you want to cancel this order and restore stock?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve Cancellation',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ route('admin.retailer.approve-cancellation', ':id') }}".replace(':id', id);
                        $.post(url, { _token: '{{ csrf_token() }}' }, function (res) {
                            if (res.success) {
                                table.ajax.reload(null, false);
                                showToast('success', res.success);
                            } else {
                                showToast('error', res.error || 'Failed to approve cancellation');
                            }
                        }).fail(function (xhr) {
                            showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                        });
                    }
                });
            });
        });
    </script>
@endpush