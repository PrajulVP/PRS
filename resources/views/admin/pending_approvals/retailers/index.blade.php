@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Retailer Order Approvals</h5>
        </div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

            <div id="filter_container" class="d-none">
                <div class="d-inline-flex align-items-center ms-2">
                    <label for="status_filter" class="form-label me-2 mb-0 fw-bold">Status:</label>
                    <select id="status_filter" class="form-select form-select-sm" style="width: 150px;">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="accepted_by_distributor">Approve</option>
                        <option value="assigned_to_field_staff">Assigned</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <table class="table table-striped table-hover" id="retailer-approval-table">
                <thead>
                    <tr>
                        <th>Sl No</th>
                        <th>Order Code</th>
                        <th>Retailer</th>
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
                <div class="mt-3">
                    <strong>Delivery Notes:</strong>
                    <p id="view_notes" class="text-muted small"></p>
                </div>
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

@endsection



@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<style>
    .action-buttons .btn {
        padding: 4px 8px;
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#retailer-approval-table').DataTable({
            dom: "<'row mb-3'<'col-sm-12'B>>" +
                "<'row mb-3 d-flex align-items-center'<'col-md-6'f><'col-md-6'l>>" +
                "rtip",
            buttons: {
                dom: {
                    button: {
                        className: ''
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
            initComplete: function() {
                var $filter = $('#filter_container').children().first();
                $('.dt-buttons').append($filter);
                $('#filter_container').remove();
            },
            ajax: {
                url: window.location.href,
                data: function(d) {
                    d.status = $('#status_filter').val();
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'order_code',
                    name: 'order_code',
                    render: function(data, type, row) {
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
                    render: function(d) {
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
                    render: function(d, type, row) {
                        let statusRaw = row.status ? row.status.toLowerCase().replace(/ /g, '_') : '';
                        let bgClass = 'bg-secondary text-white';

                        if (statusRaw.includes('pending')) bgClass = 'bg-warning text-dark';
                        else if (statusRaw.includes('accepted')) bgClass = 'bg-info text-white';
                        else if (statusRaw.includes('delivered')) bgClass = 'bg-success text-white';
                        else if (statusRaw.includes('rejected') || statusRaw.includes('cancelled')) bgClass = 'bg-danger text-white';

                        return `
                        <select class="form-select form-select-sm status-select ${bgClass}" data-id="${row.id}" data-original="${statusRaw}" style="width: 130px; font-weight: 500; border: 1px solid rgba(0,0,0,0.1);">
                            <option value="pending" ${statusRaw.includes('pending') ? 'selected' : ''} class="bg-white text-dark">Pending</option>
                            <option value="accepted_by_distributor" ${statusRaw.includes('accepted') ? 'selected' : ''} class="bg-white text-dark">Approve</option>
                            <option value="delivered" ${statusRaw.includes('delivered') ? 'selected' : ''} class="bg-white text-dark">Delivered</option>
                            <option value="rejected" ${statusRaw.includes('rejected') ? 'selected' : ''} class="bg-white text-dark">Rejected</option>
                            <option value="cancelled" ${statusRaw.includes('cancelled') ? 'selected' : ''} class="bg-white text-dark">Cancelled</option>
                        </select>
                        `;
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    render: function(d, type, row) {
                        let payStatus = row.payment_status ? row.payment_status.toLowerCase() : 'pending';
                        let bgClass = 'bg-secondary text-white';

                        if (payStatus === 'pending') bgClass = 'bg-warning text-dark';
                        else if (payStatus === 'paid') bgClass = 'bg-success text-white';
                        else if (payStatus === 'failed') bgClass = 'bg-danger text-white';

                        return `
                        <select class="form-select form-select-sm payment-status-select ${bgClass}" data-id="${row.id}" data-original="${payStatus}" style="width: 120px; font-weight: 500; border: 1px solid rgba(0,0,0,0.1);">
                            <option value="pending" ${payStatus === 'pending' ? 'selected' : ''} class="bg-white text-dark">Pending</option>
                            <option value="paid" ${payStatus === 'paid' ? 'selected' : ''} class="bg-white text-dark">Paid</option>
                            <option value="failed" ${payStatus === 'failed' ? 'selected' : ''} class="bg-white text-dark">Failed</option>
                        </select>
                        `;
                    }
                },
                {
                    data: null,
                    name: 'invoice',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (row.invoice_url) {
                            let ext = row.invoice_url.split('.').pop().toLowerCase();
                            let icon = ext === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-image-o';
                            return `
                                <div class="d-flex align-items-center gap-1">
                                    <a href="${row.invoice_url}" target="_blank" class="btn btn-sm btn-success" title="View Invoice">
                                        <i class="fa ${icon}"></i> View
                                    </a>
                                    <button class="btn btn-xs btn-warning upload-invoice-btn" data-id="${row.id}" title="Re-upload Invoice">
                                        <i class="fa fa-refresh"></i>
                                    </button>
                                    <button class="btn btn-xs btn-danger remove-invoice-btn" data-id="${row.id}" title="Remove Invoice">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            `;
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
                    render: function(data, type, row) {
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');
                        let btns = `<div class="action-buttons d-flex gap-1">`;
                        btns += `<button class="btn btn-info btn-sm view-details-btn" data-row="${rowData}" title="View Details"><i class="fa fa-eye"></i></button>`;
                        btns += `<a href="/retailer-orders/${row.id}/invoice" target="_blank" class="btn btn-dark btn-sm" title="Print Invoice"><i class="fa fa-print"></i></a>`;
                        btns += `</div>`;
                        return btns;
                    }
                }
            ]
        });

        $('#status_filter').change(function() {
            table.ajax.reload();
        });

        // Status Update Logic
        $(document).on('change', '.status-select', function(e) {
            let $select = $(this);
            let id = $select.data('id');
            let newStatus = $select.val();
            let originalStatus = $select.data('original');

            if (!confirm('Are you sure you want to change the status?')) {
                $select.val(originalStatus);
                return;
            }

            $select.prop('disabled', true);

            $.post(`/retailer-orders/${id}/update-status`, {
                _token: '{{ csrf_token() }}',
                status: newStatus
            }, function(res) {
                if (res.success) {
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
            }).fail(function(xhr) {
                showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                $select.val(originalStatus);
            }).always(function() {
                $select.prop('disabled', false);
            });
        });

        // Payment Status Update Logic
        $(document).on('change', '.payment-status-select', function(e) {
            let $select = $(this);
            let id = $select.data('id');
            let newStatus = $select.val();
            let originalStatus = $select.data('original');

            $select.prop('disabled', true);

            $.post(`/retailer-orders/${id}/update-payment-status`, {
                _token: '{{ csrf_token() }}',
                payment_status: newStatus
            }, function(res) {
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
            }).fail(function(xhr) {
                showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                $select.val(originalStatus);
            }).always(function() {
                $select.prop('disabled', false);
            });
        });

        let currentOrderIdForInvoice = null;
        $(document).on('click', '.upload-invoice-btn', function() {
            currentOrderIdForInvoice = $(this).data('id');
            $('#invoice_upload_input').click();
        });

        $('#invoice_upload_input').change(function() {
            if (!this.files.length) return;
            let file = this.files[0];
            let formData = new FormData();
            formData.append('invoice', file);
            formData.append('_token', '{{ csrf_token() }}');

            let $btn = $(`.upload-invoice-btn[data-id="${currentOrderIdForInvoice}"]`);
            let oldHtml = $btn.html();
            $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: `/retailer-orders/${currentOrderIdForInvoice}/upload-invoice`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    showToast('success', res.success);
                    table.ajax.reload(null, false);
                },
                error: function(xhr) {
                    showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Upload failed');
                    $btn.html(oldHtml).prop('disabled', false);
                }
            });
            $(this).val('');
        });

        // Remove Invoice Logic
        let removeInvoiceId = null;
        $(document).on('click', '.remove-invoice-btn', function() {
            removeInvoiceId = $(this).data('id');
            $('#removeInvoiceConfirmModal').modal('show');
        });

        $('#confirmRemoveInvoiceBtn').click(function() {
            if (!removeInvoiceId) return;

            let $modalBtn = $(this);
            let oldModalBtnHtml = $modalBtn.html();
            $modalBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

            $.post(`/retailer-orders/${removeInvoiceId}/remove-invoice`, {
                _token: '{{ csrf_token() }}'
            }, function(res) {
                $('#removeInvoiceConfirmModal').modal('hide');
                if (res.success) {
                    showToast('success', res.success);
                    table.ajax.reload(null, false);
                } else {
                    showToast('error', res.error || 'Failed to remove invoice');
                }
            }).fail(function(xhr) {
                $('#removeInvoiceConfirmModal').modal('hide');
                showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
            }).always(function() {
                $modalBtn.html(oldModalBtnHtml).prop('disabled', false);
            });
        });

        // View Modal Logic
        $(document).on('click', '.view-details-btn', function() {
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
    });
</script>
@endpush