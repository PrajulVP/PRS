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
        padding: 6px 10px !important;
        font-size: 0.75rem !important;
        line-height: 1 !important;
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
                                <option value="accepted_by_sales_manager">Approved by Manager</option>
                                <option value="delivered">Delivered</option>
                                <option value="rejected">Rejected</option>
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

    {{-- Process Order Modal for Admins --}}
    <div class="modal fade" id="processOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Order (Admin)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="processOrderForm">
                    <div class="modal-body">
                        <input type="hidden" id="process_order_id" name="order_id">
                        <div class="alert alert-primary">
                            <i class="fa fa-exclamation-triangle"></i> This action will mark the order as
                            <strong>Approved</strong> and send it to the distributor for final confirmation.
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Set Payment Status</label>
                                    <select class="form-select" name="payment_status" required>
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Upload Invoice (Optional)</label>
                                    <input type="file" class="form-control" name="invoice" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">You can upload an invoice now or later.</div>
                                </div>
                            </div>
                            <div class="col-md-8 border-start">
                                <h6 class="fw-bold mb-3">Item Wise Batch Details</h6>
                                <div class="table-responsive bg-light p-2 rounded"
                                    style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-bordered table-sm mb-0 bg-white" id="batch_entry_table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product</th>
                                                <th>Ordered Qty</th>
                                                <th>Batch Details</th>
                                            </tr>
                                        </thead>
                                        <tbody id="batch_entry_body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Process & Approve</button>
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

        /* ... existing styles ... */
        .table-responsive {
            overflow: visible !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
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
                    url: window.location.href,
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
                            else if (statusRaw.includes('accepted')) {
                                bgClass = 'bg-primary';
                                displayStatus = 'Accepted By Manager';
                            } else if (statusRaw.includes('rejected')) bgClass = 'bg-danger';
                            else if (statusRaw.includes('delivered')) bgClass = 'bg-success';
                            else if (statusRaw.includes('cancelled')) bgClass = 'bg-dark';

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
                                html += `<a href="${row.invoice_url}" target="_blank" class="btn btn-sm btn-info text-white" style="width: 40px; padding: 6px 0; text-align: center;" title="View"><i class="fa ${icon}"></i></a>`;
                                html += `<button class="btn btn-sm btn-warning upload-invoice-btn" style="width: 40px; padding: 6px 0;" data-id="${row.id}" title="Re-upload"><i class="fa fa-refresh"></i></button>`;
                                html += `<button class="btn btn-sm btn-danger remove-invoice-btn" style="width: 40px; padding: 6px 0;" data-id="${row.id}" title="Remove"><i class="fa fa-trash"></i></button>`;
                                html += `</div>`;
                                return html;
                            }

                            let canUpload = false;
                            let statusCheck = row.raw_status || (row.status ? row.status.toLowerCase().replace(/ /g, '_') : '');

                            if (statusCheck === 'delivered' || statusCheck.includes('delivered') || statusCheck.includes('approved') || statusCheck.includes('accepted')) {
                                canUpload = true;
                            }

                            if (canUpload) {
                                return `<button class="btn btn-sm btn-primary upload-invoice-btn" data-id="${row.id}" title="Upload Invoice"><i class="fa fa-upload"></i> Upload</button>`;
                            }
                            return '<span class="text-muted small">Wait for Approval</span>';
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
                            btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" style="padding: 8px 12px !important;" title="System Invoice"><i class="fa fa-print"></i></a>`;

                            // Sales Manager Actions
                            if (isSalesManager && row.status.toLowerCase().includes('pending')) {
                                btns += `<button class="btn btn-success btn-sm approve-order-btn" data-id="${row.id}" title="Approve"><i class="fa fa-check"></i></button>`;
                                btns += `<button class="btn btn-danger btn-sm reject-order-btn" data-id="${row.id}" title="Reject"><i class="fa fa-times"></i></button>`;
                            }

                            // Admin Actions
                            // Console log for debugging
                            if (isAdmin && (row.raw_status === 'accepted_by_sales_manager' || (row.status && row.status.toLowerCase().replace(/ /g, '_').includes('accepted')))) {
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
            // Reject Order Logic
            $(document).on('click', '.reject-order-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Reject Order?',
                    text: "Please provide a reason for rejection:",
                    input: 'text',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    confirmButtonColor: '#d33',
                    preConfirm: (reason) => {
                        if (!reason) Swal.showValidationMessage('Reason is required');
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ route('admin.distributor-orders.update-status', ':id') }}".replace(':id', id);
                        $.post(url, { _token: '{{ csrf_token() }}', status: 'rejected', reason: result.value }, function (res) {
                            if (res.success) {
                                table.ajax.reload(null, false);
                                showToast('success', 'Order rejected successfully');
                            } else showToast('error', res.error || 'Failed');
                        }).fail(() => showToast('error', 'Request failed'));
                    }
                });
            });

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
                        tbody.append(`<tr><td>${item.product_name}${batchHtml}</td><td>${item.quantity}</td><td>${item.unit_price}</td><td>${item.total_amount}</td></tr>`);
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

                let tbody = $('#batch_entry_body');
                tbody.empty();

                if (row && row.items) {
                    row.items.forEach(item => {
                        let rowHtml = `
                                                            <tr data-item-id="${item.order_item_id}">
                                                                <td>${item.product_name}</td>
                                                                <td class="text-center">${item.quantity}</td>
                                                                <td>
                                                                    <div class="batch-list" id="batches_for_${item.order_item_id}">
                                                                        <div class="batch-item border-bottom pb-2 mb-2">
                                                                            <div class="row g-1">
                                                                                <div class="col-md-5">
                                                                                    <input type="text" name="batches[${item.order_item_id}][0][batch_no]" class="form-control form-control-sm" placeholder="Batch No" required>
                                                                                </div>
                                                                                <div class="col-md-4">
                                                                                    <input type="date" name="batches[${item.order_item_id}][0][expiry_date]" class="form-control form-control-sm" required>
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <input type="number" name="batches[${item.order_item_id}][0][quantity]" class="form-control form-control-sm" value="${item.quantity}" min="1" required>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <button type="button" class="btn btn-link btn-sm p-0 add-batch-btn" data-item-id="${item.order_item_id}" data-item-qty="${item.quantity}">+ Add Multiple Batch</button>
                                                                </td>
                                                            </tr>
                                                        `;
                        tbody.append(rowHtml);
                    });
                }

                $('#processOrderModal').modal('show');
            });

            // Add more batch rows for an item
            $(document).on('click', '.add-batch-btn', function () {
                let itemId = $(this).data('item-id');
                let container = $(`#batches_for_${itemId}`);
                let index = container.find('.batch-item').length;

                let html = `
                                                    <div class="batch-item border-bottom pb-2 mb-2">
                                                        <div class="row g-1">
                                                            <div class="col-md-5">
                                                                <input type="text" name="batches[${itemId}][${index}][batch_no]" class="form-control form-control-sm" placeholder="Batch No" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <input type="date" name="batches[${itemId}][${index}][expiry_date]" class="form-control form-control-sm" required>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="number" name="batches[${itemId}][${index}][quantity]" class="form-control form-control-sm" value="0" min="1" required>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-batch-row"><i class="fa fa-times"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                container.append(html);
            });

            $(document).on('click', '.remove-batch-row', function () {
                $(this).closest('.batch-item').remove();
            });

            // Admin Process Form Submit
            $('#processOrderForm').submit(function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');
                let id = $('#process_order_id').val();
                let url = "{{ route('admin.distributor-orders.accept-by-admin', ':id') }}".replace(':id', id);

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
                        $('#processOrderModal').modal('hide');
                        table.ajax.reload(null, false);
                        showToast('success', res.success);
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Failed');
                    },
                    complete: function () {
                        $btn.text(oldText).prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush