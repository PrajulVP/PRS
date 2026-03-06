@extends('layouts.admin')

<style>
    /* Compact the table */
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
</style>
@section('page-body')
    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Distributor Order Approvals</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div> @endif

                <div id="filter_container" class="d-none">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <label for="status_filter" class="form-label me-2 mb- fw-bold">Status:</label>
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

    {{-- View Details Modal (Bootstrap) --}}
    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
                        <div class="col-6"><strong>Distributor:</strong> <span id="view_distributor"></span></div>
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
                    <div class="mt-3">
                        <strong>Notes:</strong>
                        <p id="view_notes" class="text-muted small"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>

        {{-- Approve Order Modal for Sales Managers --}}
        <div class="modal fade" id="approveOrderModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="approveOrderForm">
                        <div class="modal-body">
                            <input type="hidden" id="approve_order_id" name="order_id">

                            <!-- Invoice Upload Removed for Sales Manager Approval as per request -->
                            <div class="alert alert-info py-2">
                                <small><i class="fa fa-info-circle"></i> Approving this order moves it to the Admin for
                                    processing and invoice generation.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Approve & Process</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    </div>
    </div>

    </div>

    {{-- Process Order Modal for Admins --}}
    <div class="modal fade" id="processOrderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1000px;">
            <div class="modal-content shadow-lg border-0 overflow-hidden">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold text-white"><i class="fa fa-file-invoice-dollar me-2"></i> Professional
                        Order
                        Approval & Batch Entry</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="processOrderForm">
                    <div class="modal-body p-0">
                        <input type="hidden" id="process_order_id" name="order_id">

                        <div class="row g-0">
                            <!-- Left Sidebar: Configuration & Scanning -->
                            <div class="col-md-3 bg-light p-4">
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
                                            <h6 class="fw-bold">Upload Invoice</h6>
                                            <p class="text-muted small mb-0">Drag & drop or click</p>
                                        </div>
                                        <div id="ocr_processing_overlay"
                                            class="position-absolute top-50 start-50 translate-middle w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white rounded-3 d-none"
                                            style="z-index: 2; opacity: 0.9;">
                                            <div class="spinner-border text-primary mb-2" role="status"></div>
                                            <h6 class="fw-bold mb-0 small">Processing...</h6>
                                        </div>
                                    </div>
                                    <input type="file" class="d-none" id="scan_file_input" accept=".pdf,.jpg,.jpeg,.png">
                                    {{-- <div class="form-text mt-2"><i class="fa fa-info-circle text-info"></i> Batch &
                                        Expiries will be auto-extracted locally. The invoice document is required.</div>
                                    --}}
                                </div>

                                <div class="alert alert-soft-warning border-0 small shadow-sm">
                                    <i class="fa fa-shield-alt me-1"></i>Data is processed locally in your browser for
                                    maximum privacy.
                                </div>
                            </div>

                            <!-- Right Content: Automation Summary (Simplified) -->
                            <div class="col-md-9 p-4 bg-white">
                                <div id="automation_idle_state" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fa fa-robot fa-4x text-light"></i>
                                    </div>
                                    <h5 class="text-muted">Waiting for AI Scan...</h5>
                                    <p class="text-muted small">Upload an invoice to automatically process batch details.
                                    </p>
                                </div>
                                <div id="ocr_processing_state" class="d-none text-center py-5">
                                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"
                                        role="status"></div>
                                    <h5 class="fw-bold text-primary" id="ocr_status_text">Reading Invoice via AI...</h5>
                                    <p class="text-muted small">Extracting line items, taxes, and batches. Please wait.</p>
                                    <div class="progress mt-3 mx-auto" style="height: 10px; max-width: 400px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                            id="ocr_progress_bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>

                                <div id="automation_success_state" class="d-none">
                                    <!-- Extracted Metadata Section -->
                                    <h6 class="fw-bold text-uppercase small text-muted mb-2"><i
                                            class="fa fa-file-invoice me-1"></i> Invoice Details</h6>

                                    <div class="row g-2 mb-3" id="extracted_metadata_section" style="display: none;">
                                        <div class="col-12">
                                            <div class="bg-light p-3 rounded-3 border">
                                                <div class="row">
                                                    <div class="col-md-6 mb-2 mb-md-0">
                                                        <div class="text-muted small">GSTIN (Extracted)</div>
                                                        <div class="fw-bold text-dark" id="extract_gstin">--</div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 mb-md-0">
                                                        <div class="text-muted small">Drug License No.</div>
                                                        <div class="fw-bold text-dark" id="extract_dl">--</div>
                                                    </div>
                                                    <div class="col-md-6 mt-md-2">
                                                        <div class="text-muted small">Invoice Date</div>
                                                        <div class="fw-bold text-dark" id="extract_date">--</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-uppercase small text-muted mb-3"><i
                                            class="fa fa-search me-1"></i> Verify Extracted Details</h6>
                                    <div class="table-responsive rounded border shadow-sm mb-3">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light sticky-top">
                                                <tr class="small text-uppercase fw-bold text-nowrap">
                                                    <th class="ps-3 py-1">Product</th>
                                                    <th class="py-1">Batch</th>
                                                    <th class="py-1">Exp</th>
                                                    <th class="py-1 text-center pe-3">Qty</th>
                                                    <th class="py-1 text-end pe-3">Taxable</th>
                                                    <th class="py-1 text-end pe-3">CGST</th>
                                                    <th class="py-1 text-end pe-3">SGST</th>
                                                </tr>
                                            </thead>
                                            <tbody id="verification_table_body">
                                                <!-- Dynamic Rows after scan -->
                                            </tbody>
                                            <tfoot id="verification_table_footer" class="bg-light border-top d-none">
                                                <tr>
                                                    <td colspan="6" class="text-end fw-bold text-muted small py-1">Total
                                                        Taxable:</td>
                                                    <td class="text-end fw-bold text-dark py-1 pe-3" id="tfoot_taxable">--
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-end fw-bold text-muted small py-1 border-0">
                                                        Total CGST:</td>
                                                    <td class="text-end fw-bold text-dark py-1 pe-3 border-0"
                                                        id="tfoot_cgst">--
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-end fw-bold text-muted small py-1 border-0">
                                                        Total SGST:</td>
                                                    <td class="text-end fw-bold text-dark py-1 pe-3 border-0"
                                                        id="tfoot_sgst">--
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-end fw-bold text-muted small py-1 border-0">
                                                        Total IGST:</td>
                                                    <td class="text-end fw-bold text-dark py-1 pe-3 border-0"
                                                        id="tfoot_igst">--
                                                    </td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td colspan="6" class="text-end fw-bold text-dark py-2">Net Invoice
                                                        Amount:</td>
                                                    <td class="text-end fw-bold text-success py-2 pe-3" id="tfoot_net"
                                                        style="font-size: 1.1rem;">--</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="alert alert-info py-2 small mb-0">
                                        <i class="fa fa-info-circle me-1"></i> You can review and edit extracted details
                                        above.
                                    </div>
                                </div>

                                <!-- Automation Error State (Mismatched Invoice) -->
                                <div id="automation_error_state" class="d-none">
                                    <div
                                        class="text-center py-5 bg-soft-danger rounded-3 border-start border-4 border-danger">
                                        <div class="mb-3">
                                            <i class="fa fa-exclamation-triangle fa-4x text-danger opacity-75"></i>
                                        </div>
                                        <h5 class="fw-bold text-danger">Mismatched Invoice!</h5>
                                        <p class="text-muted small px-4">
                                            The AI could not find any products belonging to this order in the document.<br>
                                            <strong>Please ensure you uploaded the correct invoice.</strong>
                                        </p>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2"
                                            onclick="$('#scan_file_input').click()">
                                            <i class="fa fa-refresh me-1"></i> Try Another File
                                        </button>
                                    </div>
                                </div>

                                <!-- Hidden Table for Form Submission (Maintains functionality without cluttering UI) -->
                                <div id="hidden_batch_inputs" class="d-none">
                                    <table class="table" id="batch_entry_table">
                                        <tbody id="batch_entry_body">
                                            <!-- Dynamic Rows populated by OCR logic -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0 px-4 py-3">
                        <button type="button" class="btn btn-link link-secondary fw-bold text-decoration-none"
                            data-bs-dismiss="modal">Cancel Approval</button>
                        <button type="submit" id="btn_approve_order" class="btn btn-primary px-4 py-2 fw-bold shadow">
                            <i class="fa fa-check-circle me-1"></i> Confirm & Approve Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Hidden file input for AI Scanning --}}
    <input type="file" id="scan_file_input" class="d-none" accept=".pdf,.jpg,.jpeg,.png">

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
                <div class="modal-header">
                    <h5 class="modal-title">Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="paymentStatusForm">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="payment_order_id">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="payment_status" id="payment_status_select">
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

                    // Re-bind events since moving elements might detach them in some browsers/versions
                    $('#status_filter').off('change').on('change', function () {
                        table.ajax.reload();
                    });
                    $('#payment_status_filter').off('change').on('change', function () {
                        table.ajax.reload();
                    });
                },
                ajax: {
                    url: "{{ route('admin.approvals.distributor') }}",
                    data: function (d) {
                        d.status = $('#status_filter').val();
                        d.payment_status = $('#payment_status_filter').val();
                    }
                },
                columns: [
                    { data: null, render: (d, t, r, m) => m.row + 1 },
                    { data: 'order_code', render: (d, t, r) => r.invoice_url ? d + ' <i class="fa fa-check-circle text-success" title="Invoice Uploaded"></i>' : d },
                    { data: 'distributor_name' },
                    { data: 'product_summary', render: d => d.length > 50 ? d.substring(0, 50) + '...' : d },
                    { data: 'total_amount' },
                    { data: 'placed_at' },
                    {
                        data: 'status',
                        render: function (d, type, row) {
                            let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';
                            let bgClass = 'bg-secondary';
                            let displayStatus = row.status;

                            if (statusRaw.includes('pending')) bgClass = 'bg-warning text-dark';
                            else if (statusRaw === 'processing') {
                                bgClass = 'bg-info';
                                displayStatus = 'Processing';
                            } else if (statusRaw === 'accepted') {
                                bgClass = 'bg-primary';
                                displayStatus = 'Accepted';
                            } else if (statusRaw.includes('delivered')) bgClass = 'bg-success';
                            else if (statusRaw.includes('cancelled')) bgClass = 'bg-danger';

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
                                displayLabel = 'Unpaid';
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

                            if (statusCheck === 'delivered' || statusCheck.includes('delivered') || statusCheck.includes('approved') || statusCheck.includes('accepted')) {
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
                                btns += `<button class="btn btn-success btn-sm approve-order-btn" data-id="${row.id}" title="Approve"><i class="fa fa-check"></i></button>`;
                            }

                            // Admin Actions
                            if (isAdmin && (row.raw_status === 'processing' || (row.status && row.status.toLowerCase().includes('processing')))) {
                                btns += `<button class="btn btn-success btn-sm accept-admin-btn" data-id="${row.id}" title="Process Order"><i class="fa fa-check"></i></button>`;
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

            // Approve Form Submit
            $('#approveOrderForm').submit(function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');
                let id = $('#approve_order_id').val();
                let url = "{{ route('admin.distributor-orders.approve', ':id') }}".replace(':id', id);

                let $btn = $(this).find('button[type="submit"]');
                let oldText = $btn.text();
                $btn.text('Processing...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#approveOrderModal').modal('hide');
                        table.ajax.reload(null, false);
                        showToast('success', res.success);
                        $('#approveOrderForm')[0].reset();
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Failed');
                    },
                    complete: function () {
                        $btn.text(oldText).prop('disabled', false);
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
                $('#view_order_code').text(row.order_code);
                $('#view_placed_at').text(row.placed_at);
                $('#view_distributor').text(row.distributor_name);
                $('#view_status').text(row.status);
                $('#view_total').text(row.total_amount);
                $('#view_notes').text(row.delivery_notes || row.notes || 'None');
                let tbody = $('#view_items_body'); tbody.empty();
                if (row.items && row.items.length) {
                    row.items.forEach(item => {
                        let batchHtml = '';
                        if (item.batches && item.batches.length) {
                            batchHtml = '<div class="small text-muted mt-1">';
                            item.batches.forEach(b => {
                                batchHtml += `Batch: ${b.batch_no} | Exp: ${b.expiry_date} | Qty: ${b.quantity}<br>`;
                            });
                            batchHtml += '</div>';
                        }
                        tbody.append(`<tr><td>${item.product_name}${batchHtml}</td><td>${item.quantity} ${item.unit || ''}</td><td>${item.unit_price}</td><td>${item.total_amount}</td></tr>`);
                    });
                } else { tbody.html('<tr><td colspan="4" class="text-center">No items</td></tr>'); }
                $('#view_total').text(row.total_amount); // Grand total again
                $('#viewOrderModal').modal('show');
            });

            // Sales Manager Approve Click
            $(document).on('click', '.approve-order-btn', function () {
                let id = $(this).data('id');
                $('#approve_order_id').val(id);
                $('#approveOrderForm')[0].reset();
                $('#approveOrderModal').modal('show');
            });

            // Admin Process/Accept Click
            $(document).on('click', '.accept-admin-btn', function () {
                let id = $(this).data('id');
                let row = $(this).closest('tr').find('.view-details-btn').data('row');

                $('#process_order_id').val(id);
                $('#processOrderForm')[0].reset();

                // Reset OCR & Automation UI States
                $('#ocr_idle_state').removeClass('d-none');
                $('#ocr_processing_state').addClass('d-none');
                $('#ocr_progress_bar').css('width', '0%');
                $('#scan_file_input').val(''); // Clear any previously scanned file
                $('#automation_idle_state').removeClass('d-none');
                $('#automation_success_state').addClass('d-none');
                $('#automation_error_state').addClass('d-none');
                $('#extracted_metadata_section').hide(); // Hide if error
                $('#btn_approve_order').prop('disabled', true); // Disable until scan

                // Populate Expected Details
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
                                                                                                                                                                                                                <tr data-item-id="${item.order_item_id}">
                                                                                                                                                                                                                    <td class="d-none">
                                                                                                                                                                                                                        <div class="fw-bold product-name-marker">${item.product_name}</div>
                                                                                                                                                                                                                        <input type="number" name="batches[${item.order_item_id}][0][quantity]" value="${item.quantity}">
                                                                                                                                                                                                                    </td>
                                                                                                                                                                                                                    <td class="d-none" id="batches_for_${item.order_item_id}">
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
                                                                                                                                                                                                                    </td>
                                                                                                                                                                                                                </tr>
                                                                                                                                                                                                            `;
                        tbody.append(rowHtml);

                        let vRowHtml = `
                                                                                                                                                                                                                <tr id="v_row_${item.order_item_id}">
                                                                                                                                                                                                                    <td class="ps-3 py-2">
                                                                                                                                                                                                                        <div class="fw-bold text-dark small">${item.product_name}</div>
                                                                                                                                                                                                                    </td>
                                                                                                                                                                                                                    <td class="py-1">
                                                                                                                                                                                                                        <input type="text" class="form-control form-control-sm v-batch-input border-0 bg-light" 
                                                                                                                                                                                                                               data-id="${item.order_item_id}" placeholder="Wait for AI...">
                                                                                                                                                                                                                    </td>
                                                                                                                                                                                                                    <td class="py-1">
                                                                                                                                                                                                                        <input type="text" class="form-control form-control-sm v-expiry-input border-0 bg-light" 
                                                                                                                                                                                                                               data-id="${item.order_item_id}" placeholder="MM/YY">
                                                                                                                                                                                                                    </td>
                                                                                                                                                                                                                    <td class="text-center pe-3 fw-bold text-primary v-qty-display" data-original-unit="${item.unit || ''}">${item.quantity} ${item.unit || ''}</td>
                                                                                                                                                                                                                    <td class="text-end pe-3 small text-dark fw-bold v-taxable-display">--</td>
                                                                                                                                                                                                                    <td class="text-end pe-3 small text-muted v-cgst-display">--</td>
                                                                                                                                                                                                                    <td class="text-end pe-3 small text-muted v-sgst-display">--</td>
                                                                                                                                                                                                                </tr>
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

            // OCR Processing Logic
            $(document).on('change', '#scan_file_input', async function () {
                const file = this.files[0];
                if (!file) return;

                // Switch UI to processing
                // $('#ocr_idle_state').addClass('d-none'); // Keep it visible
                $('#ocr_processing_overlay').removeClass('d-none');
                $('#ocr_processing_state').removeClass('d-none');
                $('#automation_idle_state').addClass('d-none');
                $('#automation_success_state').addClass('d-none');
                $('#automation_error_state').addClass('d-none');

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
                        $('#ocr_progress_bar').css('width', '100%');
                        setTimeout(() => {
                            $('#ocr_processing_overlay').addClass('d-none');
                            $('#ocr_processing_state').addClass('d-none');
                            // No need to remove d-none from ocr_idle_state as it was never added
                        }, 500);

                        if (res.success && res.data) {
                            let identifiedCount = parseAndFillOCRResponse(res.data);

                            if (identifiedCount > 0) {
                                $('#automation_idle_state').addClass('d-none');
                                $('#automation_error_state').addClass('d-none');
                                $('#automation_success_state').removeClass('d-none');
                                $('#extracted_metadata_section').show();
                                $('#processed_summary_text').text(`${identifiedCount} items auto-filled from Invoice.`);
                                $('#btn_approve_order').prop('disabled', false);
                            } else {
                                $('#automation_idle_state').addClass('d-none');
                                $('#automation_success_state').addClass('d-none');
                                $('#automation_error_state').removeClass('d-none');
                                $('#extracted_metadata_section').hide();
                                $('#btn_approve_order').prop('disabled', true);
                                showToast('warning', 'Mismatched Invoice: No products identified.');
                            }
                        } else {
                            showToast('error', 'OCR Failed: Invalid response from server.');
                        }
                    },
                    error: function (xhr) {
                        console.error('OCR Error:', xhr);
                        let errMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error during OCR processing';
                        showToast('error', 'OCR Failed: ' + errMsg);
                        $('#ocr_processing_overlay').addClass('d-none');
                        $('#ocr_processing_state').addClass('d-none');
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
                $('#batch_entry_body tr').each(function () {
                    items.push({
                        id: $(this).data('item-id'),
                        name: $(this).find('.product-name-marker').text().trim().toLowerCase()
                    });
                });

                let missingProducts = [];
                let invoiceProducts = data.line_items || [];

                items.forEach(item => {
                    // Try to find the item in the JSON response
                    let matchedInvoiceItem = invoiceProducts.find(p => p.description && p.description.toLowerCase().includes(item.name.substring(0, 5)));

                    if (matchedInvoiceItem) {
                        identifiedCount++;
                        if (matchedInvoiceItem.batch) {
                            $(`#v_row_${item.id} .v-batch-input`).val(matchedInvoiceItem.batch);
                            $(`#batches_for_${item.id} .hidden-batch-val`).val(matchedInvoiceItem.batch);
                        }
                        if (matchedInvoiceItem.expiry) {
                            let rawExpiry = matchedInvoiceItem.expiry;
                            let parsedExp = parseExpiryToDate(rawExpiry);

                            // Extract just the MM/YY if there's noise (e.g., "61 1/31" -> "1/31")
                            let cleanDisplay = rawExpiry;
                            let match = rawExpiry.match(/\b(\d{1,2}[\/\-\.]\d{2,4})\b/);
                            if (match) cleanDisplay = match[1];

                            $(`#v_row_${item.id} .v-expiry-input`).val(cleanDisplay);
                            $(`#batches_for_${item.id} .hidden-expiry-val`).val(parsedExp || cleanDisplay);
                        }
                        // Extracting quantities 
                        let billedQty = parseInt(matchedInvoiceItem.qty) || 0;
                        let freeQty = parseInt(matchedInvoiceItem.free) || 0;

                        if (billedQty > 0) {
                            $(`input[name="batches[${item.id}][0][quantity]"]`).val(billedQty);
                            let unitStr = $(`#v_row_${item.id} .v-qty-display`).data('original-unit') || '';
                            let displayHtml = `${billedQty} ${unitStr}`;
                            if (freeQty > 0) displayHtml += ` <small class="text-success ms-1">(+${freeQty} Free)</small>`;
                            $(`#v_row_${item.id} .v-qty-display`).html(displayHtml);
                        }

                        // Extracting pricing & tax details
                        $(`#batches_for_${item.id} .hidden-mrp-val`).val(matchedInvoiceItem.mrp || 0);
                        $(`#batches_for_${item.id} .hidden-ptr-val`).val(matchedInvoiceItem.ptr || 0);
                        $(`#batches_for_${item.id} .hidden-pts-val`).val(matchedInvoiceItem.pts || 0);
                        $(`#batches_for_${item.id} .hidden-taxable-val`).val(matchedInvoiceItem.taxable_amt || 0);
                        $(`#batches_for_${item.id} .hidden-cgst-val`).val(matchedInvoiceItem.cgst || 0);
                        $(`#batches_for_${item.id} .hidden-sgst-val`).val(matchedInvoiceItem.sgst || 0);
                        $(`#batches_for_${item.id} .hidden-igst-val`).val(matchedInvoiceItem.igst || 0);

                        let itemTaxable = parseFloat(matchedInvoiceItem.taxable_amt || 0);
                        let itemCgst = parseFloat(matchedInvoiceItem.cgst || 0);
                        let itemSgst = parseFloat(matchedInvoiceItem.sgst || 0);
                        let itemIgst = parseFloat(matchedInvoiceItem.igst || 0);

                        $(`#v_row_${item.id} .v-taxable-display`).text(itemTaxable > 0 ? itemTaxable.toFixed(2) : '--');
                        $(`#v_row_${item.id} .v-cgst-display`).text(itemCgst > 0 ? itemCgst.toFixed(2) : '--');
                        $(`#v_row_${item.id} .v-sgst-display`).text(itemSgst > 0 ? itemSgst.toFixed(2) : '--');

                        let netAmt = itemTaxable + itemCgst + itemSgst + itemIgst;
                        $(`#batches_for_${item.id} .hidden-net-val`).val(netAmt.toFixed(2));

                        totalTaxable += itemTaxable;
                        totalCgst += itemCgst;
                        totalSgst += itemSgst;
                        totalIgst += itemIgst;
                        totalNet += netAmt;
                    } else {
                        missingProducts.push(item.name);
                    }
                });

                if (identifiedCount > 0) {
                    $('#tfoot_taxable').text(totalTaxable.toFixed(2));
                    $('#tfoot_cgst').text(totalCgst.toFixed(2));
                    $('#tfoot_sgst').text(totalSgst.toFixed(2));
                    $('#tfoot_igst').text(totalIgst.toFixed(2));
                    // If OCR provided an exact total, prefer that, else sum of parts
                    let ocrTotalStr = data.invoice_metadata ? data.invoice_metadata.total_amount : '';
                    let ocrTotal = parseFloat(ocrTotalStr);
                    if (!isNaN(ocrTotal) && ocrTotal > 0) {
                        $('#tfoot_net').text(ocrTotal.toFixed(2));
                    } else {
                        $('#tfoot_net').text(totalNet.toFixed(2));
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