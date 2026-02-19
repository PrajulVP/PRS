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

        #orders-table td:last-child {
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Retailer Orders</h5>
                <a href="{{ route('admin.retailer-orders.create') }}" class="btn btn-primary btn-sm"><i
                        class="fa fa-plus"></i> Create Order</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div> @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Order Code</th>
                                <th>Retailer</th>
                                <th>Distributor</th>
                                <th>Summary</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Placed At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    {{-- Admin Edit Modal --}}
    <div class="modal fade" id="editOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Order</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editOrderForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Retailer</label>
                                <select name="retailer_id" id="edit_retailer_id" class="form-select" required>
                                    @foreach($retailers as $r) <option value="{{ $r->id }}">{{ $r->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Distributor</label>
                                <select name="distributor_id" id="edit_distributor_id" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach($distributors as $d) <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Status</label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    @foreach(['pending', 'accepted_by_distributor', 'assigned_to_fieldstaff', 'out_for_delivery', 'delivered', 'rejected'] as $st)
                                        <option value="{{ $st }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Items Section --}}
                        <div class="mb-3 p-3 bg-light">
                            <h6>Items</h6>
                            <div class="input-group mb-2">
                                <select id="edit_product_select" class="form-control">
                                    <option value="">Select Product</option>
                                    @foreach($products as $p) <option value="{{ $p->id }}" data-price="{{ $p->mrp }}">
                                        {{ $p->product_name }}
                                    </option> @endforeach
                                </select>
                                <button type="button" class="btn btn-primary" id="btn_add_prod_edit">Add</button>
                            </div>
                            <table class="table table-bordered table-sm" id="edit_items_table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                        <td id="edit_grand_total">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea name="notes" id="edit_notes" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Delivery Notes</label>
                            <textarea name="delivery_notes" id="edit_delivery_notes" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Assign Field Staff Modal --}}
    <div class="modal fade" id="assignFieldStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Field Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalOrderId">
                    <div class="mb-3">
                        <label class="form-label">Select Field Staff</label>
                        <select id="modalFieldStaffSelect" class="form-select">
                            <option value="">-- Select --</option>
                            @foreach($fieldstaffs as $fs) <option value="{{ $fs['id'] }}">{{ $fs['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirmAssignFieldStaffBtn">Assign</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Show Modal --}}
    <div class="modal fade" id="showOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Order Details</h5><button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tbody id="showOrderBody"></tbody>
                    </table>
                    <h6 class="mt-2">Items</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="showOrderItemsBody"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end"><strong>Total Amount:</strong></td>
                                <td id="showOrderTotal">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this order? This process cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Order</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Confirmation Modal --}}
    <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to cancel this order? This action cannot be undone.</p>
                    <div class="mb-3">
                        <label class="form-label required">Cancellation Reason</label>
                        <textarea id="cancel_reason_input" class="form-control" rows="3"
                            placeholder="Please provide a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep It</button>
                    <button type="button" class="btn btn-warning" id="confirmCancelBtn">Yes, Cancel Order</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const canAcceptOrder = {{ Auth::user()->hasAnyRole(['distributor', 'admin', 'superadmin', 'manager']) ? 'true' : 'false' }};
        const canAssignFieldStaff = {{ Auth::user()->hasAnyRole(['admin', 'superadmin', 'manager', 'distributor']) ? 'true' : 'false' }};
        const isRetailer = {{ Auth::user()->hasRole('retailer') ? 'true' : 'false' }};
        const isAdmin = {{ Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']) ? 'true' : 'false' }};

        $(document).ready(function () {
            var editItems = {};

            var ajaxUrl = "{{ route('admin.retailer-orders.index') }}";

            var table = $('#orders-table').DataTable({
                order: [],
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-3 d-flex align-items-center'<'col-md-6'l><'col-md-6'f>>" +
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
                processing: true,
                serverSide: true,
                ajax: ajaxUrl,
                columns: [{
                    data: null,
                    name: 'sl_no',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'order_code',
                    name: 'order_code'
                },
                {
                    data: 'retailer_name',
                    name: 'retailer_name',
                    visible: !{{ Auth::user()->hasRole('retailer') ? 'true' : 'false' }} 
                            },
                {
                    data: 'distributor_name',
                    name: 'distributor_name',
                    render: function (data, type, row) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'product_summary',
                    name: 'product_summary',
                    orderable: false,
                    render: function (data, type, row) {
                        if (!data) return '-';
                        let items = data.split('<br>');
                        if (items.length > 2) {
                            let visible = items.slice(0, 2).join('<br>');
                            return `<div>
                                                                                                <span class="preview-content">${visible}</span>
                                                                                                <span class="full-content d-none">${data}</span>
                                                                                                <br>
                                                                                                <a href="#" class="small text-primary toggle-more-btn" onclick="event.preventDefault(); let p = $(this).parent(); if(p.find('.full-content').hasClass('d-none')){ p.find('.full-content').removeClass('d-none'); p.find('.preview-content').addClass('d-none'); $(this).text('Show Less'); } else { p.find('.full-content').addClass('d-none'); p.find('.preview-content').removeClass('d-none'); $(this).text('Read More'); }">Read More</a>
                                                                                            </div>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    render: function (data) {
                        return `<span class="fw-bold text-success"><i class="fa fa-rupee"></i> ${data}</span>`;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data, type, row) {
                        let status = (data || '').toLowerCase();
                        let badgeClass = 'bg-secondary';
                        if (status.includes('pending')) badgeClass = 'bg-warning text-dark';
                        else if (status.includes('accepted')) badgeClass = 'bg-primary';
                        else if (status.includes('delivered')) badgeClass = 'bg-success';
                        else if (status.includes('cancelled') || status.includes('rejected')) badgeClass = 'bg-danger';

                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'placed_at',
                    name: 'placed_at'
                },
                {
                    data: null,
                    orderable: false,
                    render: function (d, t, row) {
                        let rowData = JSON.stringify(row).replace(/'/g, "&apos;");
                        let btns = `<div class="action-buttons">`;
                        btns += `<button class="btn btn-info btn-sm view-btn" data-row='${rowData}' title="View Details"><i class="fa fa-eye"></i></button>`;

                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                            btns += `<button class="btn btn-primary btn-sm edit-btn" data-row='${rowData}' title="Edit"><i class="fa fa-edit"></i></button>`;
                        @endif

                        let st = (row.status || '').toLowerCase().replace(/ /g, '_');

                        // Print Invoice
                        let invoiceUrl = "{{ route('admin.retailer-orders.invoice', ':id') }}".replace(':id', row.id);
                        btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="Print Invoice"><i class="fa fa-print"></i></a>`;

                        // Retailer Confirmation
                        if (st === 'accepted_by_distributor') {
                            if (isRetailer || isAdmin) {
                                btns += `<button class="btn btn-success btn-sm confirm-receipt-btn" data-id="${row.id}" title="Confirm Order"> Confirm</button>`;
                            }
                        }

                        if (st.includes('cancellation_requested')) {
                            btns += `<button class="btn btn-success btn-sm approve-cancel-btn" data-id="${row.id}" title="Approve Cancellation"><i class="fa fa-check-circle"></i></button>`;
                        }

                        // Delete
                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']))
                            btns += `<button class="btn btn-danger btn-sm delete-order-btn" data-id="${row.id}" title="Delete"><i class="fa fa-trash"></i></button>`;
                        @endif

                        btns += `</div>`;
                        return btns;
                    }
                }
                ]
            });



            // --- Admin Edit Logic ---
            $(document).on('click', '.edit-btn', function () {
                let row = $(this).data('row');
                $('#edit_retailer_id').val(row.retailer_id);
                $('#edit_distributor_id').val(row.distributor_id);
                $('#edit_status').val(row.status.toLowerCase().replace(/ /g, '_'));
                $('#edit_notes').val(row.notes);
                $('#edit_delivery_notes').val(row.delivery_notes);

                editItems = {};
                row.items.forEach(function (i) {
                    // Item from JSON might have different structure based on Controller
                    // Admin index returns items with product_id, product_name, quantity, unit_price
                    editItems[i.product_id] = {
                        id: i.product_id,
                        name: i.product_name || i.name,
                        qty: i.quantity || i.qty,
                        price: parseFloat(i.unit_price || i.price),
                        order_item_id: i.order_item_id
                    };
                });
                renderEditItems();
                $('#editOrderForm').attr('action', `/retailer-orders/${row.id}`);
                $('#editOrderModal').modal('show');
            });

            $('#btn_add_prod_edit').click(function () {
                let sel = $('#edit_product_select option:selected');
                let id = sel.val();
                if (!id) return;
                if (editItems[id]) return alert('Already added');
                editItems[id] = {
                    id: id,
                    name: sel.text(),
                    qty: 1,
                    price: parseFloat(sel.data('price'))
                };
                renderEditItems();
            });

            function renderEditItems() {
                let tbody = $('#edit_items_table tbody');
                tbody.empty();
                let total = 0;
                if (Object.keys(editItems).length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center text-muted">No Items Added</td></tr>');
                } else {
                    $.each(editItems, function (id, item) {
                        let price = parseFloat(item.price) || 0;
                        let qty = parseInt(item.qty) || 1;
                        let sub = price * qty;
                        total += sub;
                        let unit = item.unit || 'Box';
                        let options = '';
                        ['Box', 'Carton', 'Strips'].forEach(function (u) {
                            options += `<option value="${u}" ${unit === u ? 'selected' : ''}>${u}</option>`;
                        });

                        tbody.append(`
                                                                            <tr>
                                                                                <td>${item.name}
                                                                                    <input type="hidden" name="items[${id}][product_id]" value="${id}">
                                                                                    ${item.order_item_id ? `<input type="hidden" name="items[${id}][order_item_id]" value="${item.order_item_id}">` : ''}
                                                                                </td>
                                                                                <td>
                                                                                    <select class="form-select form-select-sm unit-select-edit" data-id="${id}" name="items[${id}][unit]" style="width:90px; margin: 0 auto;">
                                                                                        ${options}
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="number" class="form-control form-control-sm edit-qty" data-id="${id}" value="${qty}" name="items[${id}][quantity]" min="1" style="width:80px; margin: 0 auto;">
                                                                                </td>
                                                                                <td class="text-end">${price.toFixed(2)}<input type="hidden" name="items[${id}][unit_price]" value="${price}"></td>
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
                editItems[id].qty = parseInt($(this).val());
                renderEditItems();
            });

            $(document).on('change', '.unit-select-edit', function () {
                let id = $(this).data('id');
                let val = $(this).val();
                if (editItems[id]) {
                    editItems[id].unit = val;
                }
            });
            $(document).on('click', '.remove-edit', function () {
                delete editItems[$(this).data('id')];
                renderEditItems();
            });


            // --- Admin Edit Logic ---
            $('#editOrderForm').submit(function (e) {
                e.preventDefault();
                let form = $(this);
                let url = form.attr('action');
                let data = form.serialize();

                $.ajax({
                    url: url,
                    type: 'POST', // Method spoofing is handled by _method input
                    data: data,
                    success: function (response) {
                        if (response.success || response.message) {
                            $('#editOrderModal').modal('hide');
                            table.ajax.reload();
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
                        if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err
                        });
                    }
                });
            });

            // --- Delete & Cancel Logic (modals) ---
            let deleteOrderId = null;
            $(document).on('click', '.delete-order-btn', function () {
                deleteOrderId = $(this).data('id');
                $('#deleteConfirmModal').modal('show');
            });

            $('#confirmDeleteBtn').click(function () {
                if (!deleteOrderId) return;
                $.ajax({
                    url: `/retailer-orders/${deleteOrderId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        $('#deleteConfirmModal').modal('hide');
                        if (res.success) {
                            table.ajax.reload();
                            showToast('success', res.success || 'Order deleted');
                        } else {
                            showToast('error', res.error || 'Failed to delete order');
                        }
                    },
                    error: function (xhr) {
                        $('#deleteConfirmModal').modal('hide');
                        let err = 'An error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                        showToast('error', err);
                    }
                });
            });

            // Cancel Order (Admin direct cancel of pending)
            let cancelOrderId = null;
            $(document).on('click', '.cancel-order-btn', function () {
                cancelOrderId = $(this).data('id');
                $('#cancel_reason_input').val('');
                $('#cancelConfirmModal').modal('show');
            });

            $('#confirmCancelBtn').click(function () {
                if (!cancelOrderId) return;
                let reason = $('#cancel_reason_input').val().trim();
                if (!reason) return Swal.fire('Error', 'Please provide a cancellation reason', 'error');

                $.post(`/retailer-orders/${cancelOrderId}/cancel-order`, {
                    _token: '{{ csrf_token() }}',
                    cancellation_reason: reason
                }, function (res) {
                    $('#cancelConfirmModal').modal('hide');
                    if (res.success) {
                        table.ajax.reload();
                        showToast('success', res.success || 'Order cancelled');
                    } else {
                        showToast('error', res.error || 'Failed to cancel order');
                    }
                }).fail(function (xhr) {
                    $('#cancelConfirmModal').modal('hide');
                    let err = 'Request failed';
                    if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                    showToast('error', err);
                });
            });

            // Request cancellation (Distributor requests)
            $(document).on('click', '.request-cancel-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Request Cancellation',
                    input: 'text',
                    inputLabel: 'Reason',
                    inputPlaceholder: 'Enter cancellation reason',
                    showCancelButton: true,
                    inputValidator: (value) => {
                        if (!value) return 'You need to write something!';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`/retailer-orders/${id}/request-cancellation`, {
                            _token: '{{ csrf_token() }}',
                            cancellation_reason: result.value
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                showToast('success', res.success || 'Cancellation requested');
                            } else showToast('error', res.error || 'Failed to request cancellation');
                        }).fail(function () {
                            showToast('error', 'Request failed');
                        });
                    }
                });
            });

            // Approve cancellation (Sales Manager)
            $(document).on('click', '.approve-cancel-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Approve cancellation and restore stock?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`/retailer-orders/${id}/approve-cancellation`, {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                showToast('success', res.success || 'Cancellation approved');
                            } else showToast('error', res.error || 'Failed to approve cancellation');
                        }).fail(function () {
                            showToast('error', 'Request failed');
                        });
                    }
                });
            });


            // Confirm Receipt (Retailer)
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


            // --- Accept & Assign Logic ---
            $(document).on('click', '.accept-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Accept this order?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Accept'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`/retailer-orders/${id}/accept`, {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                showToast('success', res.success || 'Order accepted successfully.');
                            } else {
                                showToast('error', res.error || 'Error accepting order');
                            }
                        }).fail(function () {
                            showToast('error', 'Request failed.');
                        });
                    }
                });
            });

            $(document).on('click', '.assign-fs-btn', function () {
                $('#modalOrderId').val($(this).data('id'));
                $('#assignFieldStaffModal').modal('show');
            });

            $('#confirmAssignFieldStaffBtn').click(function () {
                let form = {
                    _token: '{{ csrf_token() }}',
                    fieldstaff_id: $('#modalFieldStaffSelect').val()
                };

                if (!form.fieldstaff_id) {
                    Swal.fire('Warning', 'Please select a field staff.', 'warning');
                    return;
                }

                $.post(`/retailer-orders/${$('#modalOrderId').val()}/assign-fieldstaff`, form, function (res) {
                    if (res.success) {
                        $('#assignFieldStaffModal').modal('hide');
                        table.ajax.reload();
                        showToast('success', 'Field Staff assigned successfully');
                    } else {
                        showToast('error', res.error || 'Failed to assign field staff');
                    }
                }).fail(function () {
                    showToast('error', 'Request failed.');
                });
            });

            // --- Show Logic ---
            $(document).on('click', '.view-btn', function () {
                let row = $(this).data('row');
                $('#showOrderBody').html(`
                                                                    <tr><th>Order Code</th><td>${row.order_code}</td></tr>
                                                                    <tr><th>Retailer</th><td>${row.retailer_name || '-'}</td></tr>
                                                                    <tr><th>Distributor</th><td>${row.distributor_name || row.distributor || '-'}</td></tr>
                                                                    <tr><th>Status</th><td>${row.status}</td></tr>
                                                                    <tr><th>Notes</th><td>${row.notes || '-'}</td></tr>
                                                                    <tr><th>Placed At</th><td>${row.placed_at || '-'}</td></tr>
                                                                 `);
                let h = '';
                let total = 0;
                (row.items || []).forEach(function (i) {
                    let name = i.product_name || i.name || '-';
                    let qty = i.quantity || i.qty || 0;
                    let totalAmt = parseFloat(i.total_amount || i.total || (i.unit_price ? (i.unit_price * qty) : 0));
                    total += totalAmt;
                    h += `<tr><td>${name}</td><td>${qty}</td><td class="text-end"><i class="fa fa-rupee"></i> ${totalAmt.toFixed(2)}</td></tr>`;
                });
                $('#showOrderItemsBody').html(h);
                $('#showOrderTotal').text(total.toFixed(2));
                $('#showOrderModal').modal('show');
            });


        });
    </script>
@endpush