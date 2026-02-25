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
            padding: 2px 6px !important;
            font-size: 0.75rem !important;
            height: 28px !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            line-height: 1 !important;
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
                                <option value="accepted_by_fieldstaff">Accepted by Sales Rep</option>
                                <option value="accepted_by_distributor">Approved</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
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

    {{-- Approve Retailer Order Modal --}}
    <div class="modal fade" id="approveRetailerOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Retailer Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveRetailerOrderForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="approve_retailer_order_id" name="order_id">
                    <div class="modal-body">
                        <p class="mb-3" id="approveModalText">Are you sure you want to approve this order? You may
                            optionally upload an invoice.</p>
                        <div class="mb-3" id="invoiceUploadGroup">
                            <label class="form-label">Upload Invoice (PDF, JPG, PNG)</label>
                            <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        @if(Auth::user()->hasRole('distributor'))
                            <div class="mb-3" id="paymentStatusGroup">
                                <label class="form-label fw-bold">Payment Status</label>
                                <select name="payment_status" class="form-select">
                                    <option value="pending">Unpaid</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Retailer Payment Status Modal --}}
    <div class="modal fade" id="retailerPaymentStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
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
    <style>
        .action-buttons .btn,
        .table td .btn {
            padding: 2px 6px !important;
            font-size: 0.75rem !important;
            height: 28px !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            line-height: 1 !important;
        }
    </style>
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
                        else if (statusRaw === 'accepted_by_fieldstaff') {
                            bgClass = 'bg-info text-white';
                            displayStatus = 'Accepted by Sales Rep';
                        }
                        else if (statusRaw === 'accepted_by_distributor') {
                            bgClass = 'bg-primary text-white';
                            displayStatus = 'Approved';
                        }
                        else if (statusRaw === 'delivered') bgClass = 'bg-success text-white';
                        else if (statusRaw.includes('rejected') || statusRaw.includes('cancelled')) bgClass = 'bg-danger text-white';

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
                            if (!isFieldStaff) {
                                btnsHtml += `
                                                                                                                                            <button class="btn btn-xs btn-warning upload-invoice-btn" data-id="${row.id}" title="Re-upload Invoice">
                                                                                                                                                <i class="fa fa-refresh"></i>
                                                                                                                                            </button>
                                                                                                                                            <button class="btn btn-xs btn-danger remove-invoice-btn" data-id="${row.id}" title="Remove Invoice">
                                                                                                                                                <i class="fa fa-trash"></i>
                                                                                                                                            </button>`;
                            }
                            btnsHtml += `</div>`;
                            return btnsHtml;
                        }
                        if (isFieldStaff) return '<span class="text-muted small">No Invoice</span>';

                        let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';
                        if (statusRaw === 'pending') {
                            return '<span class="text-muted small">Waiting for Sales Rep Approval</span>';
                        }

                        return `
                                                                                                                                    <button class="btn btn-xs btn-warning upload-invoice-btn" data-id="${row.id}">
                                                                                                                                        <i class="fa fa-upload"></i> Upload
                                                                                                                                    </button>
                                                                                                                                `;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');
                        let btns = `<div class="action-buttons d-flex gap-1">`;
                        let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';

                        // Tiered Approval Buttons
                        let canApprove = false;
                        if (isFieldStaff && statusRaw === 'pending') canApprove = true;
                        if (isDistributor && statusRaw === 'accepted_by_fieldstaff') canApprove = true;
                        if ((isAdmin || isSalesManager) && (statusRaw === 'pending' || statusRaw === 'accepted_by_fieldstaff')) canApprove = true;

                        if (canApprove) {
                            btns += `<button class="btn btn-success btn-sm approve-retailer-btn" data-id="${row.id}" title="Approve"><i class="fa fa-check"></i></button>`;
                            btns += `<button class="btn btn-danger btn-sm reject-retailer-btn" data-id="${row.id}" title="Reject"><i class="fa fa-times"></i></button>`;
                        }

                        btns += `<button class="btn btn-info btn-sm view-details-btn" data-row="${rowData}" title="View Details"><i class="fa fa-eye"></i></button>`;
                        let invoiceUrl = "{{ route('admin.retailer-orders.invoice', ':id') }}".replace(':id', row.id);
                        btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="Print Invoice"><i class="fa fa-print"></i></a>`;

                        // Retailer Confirmation
                        if (statusRaw === 'accepted_by_distributor') {
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
                    url: "{{ route('admin.retailer-orders.update-payment-status', ':id') }}".replace(':id', id),
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

                let url = "{{ route('admin.retailer-orders.update-status', ':id') }}".replace(':id', id);
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
                        else if (newStatus.includes('rejected') || newStatus.includes('cancelled')) newClass = 'bg-danger text-white';

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

                let url = "{{ route('admin.retailer-orders.update-payment-status', ':id') }}".replace(':id', id);
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
                    url: "{{ route('admin.retailer-orders.upload-invoice', ':id') }}".replace(':id', currentOrderIdForInvoice),
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

                let url = "{{ route('admin.retailer-orders.remove-invoice', ':id') }}".replace(':id', removeInvoiceId);
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
                    $('#approveModalText').text('Are you sure you want to approve this order?');
                } else {
                    $('#invoiceUploadGroup').show();
                    $('#approveModalText').text('Are you sure you want to approve this order? You may optionally upload an invoice.');
                }

                $('#approveRetailerOrderModal').modal('show');
            });

            $('#approveRetailerOrderForm').submit(function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#approve_retailer_order_id').val();
                let url = "{{ route('admin.retailer-orders.acceptOrder', ':id') }}".replace(':id', id);

                let $btn = $(this).find('button[type="submit"]');
                let oldHtml = $btn.html();
                $btn.html('<i class="fa fa-spinner fa-spin"></i> Approving...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#approveRetailerOrderModal').modal('hide');
                        if (res.success) {
                            table.ajax.reload(null, false);
                            showToast('success', res.success);
                        } else {
                            showToast('error', res.error || 'Failed to approve');
                        }
                    },
                    error: function (xhr) {
                        $('#approveRetailerOrderModal').modal('hide');
                        showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
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
                        let url = "{{ route('admin.retailer-orders.confirm-receipt', ':id') }}".replace(':id', id);
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
                        let url = "{{ route('admin.retailer-orders.update-status', ':id') }}".replace(':id', id);
                        $.post(url, { _token: '{{ csrf_token() }}', status: 'rejected' }, function (res) {
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
                        let url = "{{ route('admin.retailer-orders.approve-cancellation', ':id') }}".replace(':id', id);
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