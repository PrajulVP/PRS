@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Distributor Order Approvals</h5>
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
                        <option value="hold">Hold</option>
                        <option value="accepted_by_sales_manager">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
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

        .badge-dropdown-btn {
            padding: 5px 10px;
            font-size: 0.85em;
            border-radius: 4px;
            /* More standard button look */
            border: 1px solid rgba(0, 0, 0, 0.1);
            font-weight: 600;
            /* opacity: 1 !important; */
            color: #fff !important;
            /* Ensure text is white */
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
            /* Lift it up */
        }

        .badge-dropdown-btn:hover {
            opacity: 0.9 !important;
        }

        .dropdown-menu {
            z-index: 9999 !important;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid #ddd;
        }

        .badge-dropdown-btn.btn-warning {
            color: #212529 !important;
        }

        /* Dark text for yellow */

        .table-responsive {
            overflow: visible !important;
        }

        table.dataTable {
            border-collapse: separate !important;
            /* Might help with overlap */
            border-spacing: 0;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#distributor-approval-table').DataTable({
                // ... (DataTable config remains same, skipping lines for brevity if not editing)
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
                        name: 'sl_no',
                        orderable: false,
                        searchable: false,
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
                        data: 'distributor_name',
                        name: 'distributor_name'
                    },
                    {
                        data: 'product_summary',
                        name: 'product_summary',
                        orderable: false,
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
                            else if (statusRaw.includes('hold')) bgClass = 'bg-secondary text-white';
                            else if (statusRaw.includes('accepted') || statusRaw.includes('delivered')) bgClass = 'bg-success text-white';
                            else if (statusRaw.includes('rejected') || statusRaw.includes('cancelled')) bgClass = 'bg-danger text-white';

                            return `
                        <select class="form-select form-select-sm status-select ${bgClass}" data-id="${row.id}" data-original="${statusRaw}" style="width: 130px; font-weight: 500; border: 1px solid rgba(0,0,0,0.1);">
                            <option value="pending" ${statusRaw.includes('pending') ? 'selected' : ''} class="bg-white text-dark">Pending</option>
                            <option value="hold" ${statusRaw.includes('hold') ? 'selected' : ''} class="bg-white text-dark">Hold</option>
                            <option value="accepted_by_sales_manager" ${statusRaw.includes('accepted') ? 'selected' : ''} class="bg-white text-dark">Approved</option>
                            <option value="rejected" ${statusRaw.includes('rejected') ? 'selected' : ''} class="bg-white text-dark">Rejected</option>
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
                            let invoiceUrl = "{{ route('admin.distributor-orders.invoice', ':id') }}".replace(':id', row.id);
                            return `<div class="action-buttons">
                                        <button class="btn btn-info btn-sm view-details-btn" data-row="${rowData}" title="View Details"><i class="fa fa-eye"></i></button>
                                        <a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="Print Invoice"><i class="fa fa-print"></i></a>
                                     </div>`;
                        }
                    }
                ]
            });

            $('#status_filter').change(function() {
                table.ajax.reload();
            });

            // Status Update Logic (Native Select)
            $(document).on('change', '.status-select', function(e) {
                let $select = $(this);
                let id = $select.data('id');
                let newStatus = $select.val();
                let originalStatus = $select.data('original');

                $select.prop('disabled', true);

                let url = "{{ route('admin.distributor-orders.update-status', ':id') }}".replace(':id', id);
                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    status: newStatus
                }, function(res) {
                    if (res.success) {
                        // Update coloring
                        $select.removeClass('bg-warning bg-secondary bg-success bg-danger text-dark text-white');
                        let newClass = 'bg-secondary text-white';
                        if (newStatus == 'pending') newClass = 'bg-warning text-dark';
                        else if (newStatus == 'hold') newClass = 'bg-secondary text-white';
                        else if (newStatus == 'accepted_by_sales_manager') newClass = 'bg-success text-white';
                        else if (newStatus == 'rejected') newClass = 'bg-danger text-white';

                        $select.addClass(newClass);
                        $select.data('original', newStatus); // Update original

                        showToast('success', res.success); // SUCCESS TOAST
                    } else {
                        showToast('error', res.error || 'Failed to update status'); // ERROR TOAST
                        $select.val(originalStatus); // Revert
                    }
                }).fail(function(xhr) {
                    showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed')); // FAIL TOAST
                    $select.val(originalStatus); // Revert
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

                let url = "{{ route('admin.distributor-orders.update-payment-status', ':id') }}".replace(':id', id);
                $.post(url, {
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
                    url: "{{ route('admin.distributor-orders.upload-invoice', ':id') }}".replace(':id', currentOrderIdForInvoice),
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

                let $btn = $(`.remove-invoice-btn[data-id="${removeInvoiceId}"]`);
                let oldHtml = $btn.html();
                // $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true); // Optional: add spinner to button behind modal or to modal button
                let $modalBtn = $(this);
                let oldModalBtnHtml = $modalBtn.html();
                $modalBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                let url = "{{ route('admin.distributor-orders.remove-invoice', ':id') }}".replace(':id', removeInvoiceId);
                $.post(url, {
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
                $('#view_distributor').text(row.distributor_name);
                $('#view_status').text(row.status);
                $('#view_total').text(row.total_amount);
                $('#view_notes').text(row.delivery_notes || row.notes || 'None');

                let tbody = $('#view_items_body');
                tbody.empty();

                // Note: row.items might not have all details if controller optimized (but it has everything currently)
                // But 'items' key needed in step 188 response logic. 
                // In step 188 I included `items` in the returned generic collection or order resource.
                // Let's verify Step 188. Yes, `items` => map(...) returns product_name, quantity, unit_price, total_amount...

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