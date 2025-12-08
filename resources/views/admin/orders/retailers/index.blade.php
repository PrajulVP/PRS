@extends('layouts.admin')

<style>
    .dataTables_filter {
        text-align: left !important;
    }

    .dataTables_filter input {
        width: 230px !important;
        margin-left: 10px !important;
    }

    .dataTables_length {
        text-align: right !important;
    }

    .action-buttons {
        display: inline-flex !important;
        gap: 4px;
    }

    .action-buttons .btn {
        padding: 4px 8px !important;
        font-size: 0.75rem !important;
    }
</style>

@section('page-body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Retailer Orders</h5>
        </div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover" id="orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Order Code</th>
                            <th>Retailer</th>
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
                                @foreach($retailers as $r) <option value="{{ $r->id }}">{{ $r->user->name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Distributor</label>
                            <select name="distributor_id" id="edit_distributor_id" class="form-select">
                                <option value="">-- None --</option>
                                @foreach($distributors as $d) <option value="{{ $d->id }}">{{ $d->user->name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                @foreach(['pending', 'accepted_by_distributor', 'assigned_to_fieldstaff', 'out_for_delivery', 'delivered', 'rejected'] as $st)
                                <option value="{{ $st }}">{{ ucfirst(str_replace('_',' ',$st)) }}</option>
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
                                @foreach($products as $p) <option value="{{ $p->id }}" data-price="{{ $p->mrp }}">{{ $p->product_name }}</option> @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="btn_add_prod_edit">Add</button>
                        </div>
                        <table class="table table-bordered table-sm" id="edit_items_table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
                        @foreach($fieldstaffs as $fs) <option value="{{ $fs['id'] }}">{{ $fs['name'] }}</option> @endforeach
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
                    <tbody id="showOrderItemsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var editItems = {};

        var ajaxUrl = "{{ route('admin.retailer-orders.index') }}";

        var table = $('#orders-table').DataTable({
            dom: 'Bfrtip',
            buttons: {
                dom: {
                    button: {
                        className: ''
                    }
                },
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-primary btn-sm'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-secondary'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm'
                    }
                ]
            },
            processing: true,
            serverSide: true,
            ajax: ajaxUrl,
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'order_code',
                    name: 'order_code'
                },
                {
                    data: 'retailer_name',
                    name: 'retailer_name'
                },
                {
                    data: 'product_summary',
                    name: 'product_summary',
                    orderable: false
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(d) {
                        return `<span class="badge bg-secondary">${d}</span>`;
                    }
                },
                {
                    data: 'placed_at',
                    name: 'placed_at'
                },
                {
                    data: null,
                    orderable: false,
                    render: function(d, t, row) {
                        let rowJson = JSON.stringify(row).replace(/"/g, '&quot;');
                        let btns = `<div class="action-buttons">
                    <button class="btn btn-info btn-sm view-btn" data-row="${rowJson}"><i class="fa fa-eye"></i></button>`;

                        btns += `<button class="btn btn-primary btn-sm edit-btn" data-row="${rowJson}"><i class="fa fa-edit"></i></button>`;
                        btns += `<form action="/retailer-orders/${row.id}" method="POST" onsubmit="return confirm('Delete?')" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>`;

                        let st = row.status.toLowerCase();
                        if (st.includes('pending')) {
                            btns += `<button class="btn btn-success btn-sm accept-btn" data-id="${row.id}">Accept</button>`;
                        }
                        if (st.includes('accepted_by_distributor')) {
                            btns += `<button class="btn btn-primary btn-sm assign-fs-btn" data-id="${row.id}">Assign FS</button>`;
                        }
                        return btns + `</div>`;
                    }
                }
            ]
        });

        // --- Admin Edit Logic ---
        $(document).on('click', '.edit-btn', function() {
            let row = $(this).data('row');
            $('#edit_retailer_id').val(row.retailer_id);
            $('#edit_distributor_id').val(row.distributor_id);
            $('#edit_status').val(row.status.toLowerCase().replace(/ /g, '_'));
            $('#edit_notes').val(row.notes);
            $('#edit_delivery_notes').val(row.delivery_notes);

            editItems = {};
            row.items.forEach(function(i) {
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

        $('#btn_add_prod_edit').click(function() {
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
            $.each(editItems, function(id, item) {
                tbody.append(`<tr>
                <td>${item.name}<input type="hidden" name="items[${id}][product_id]" value="${id}">
                   ${item.order_item_id ? `<input type="hidden" name="items[${id}][order_item_id]" value="${item.order_item_id}">` : ''}
                </td>
                <td><input type="number" class="form-control form-control-sm edit-qty" data-id="${id}" value="${item.qty}" name="items[${id}][quantity]"></td>
                <td>${(item.qty*item.price).toFixed(2)}</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-edit" data-id="${id}">X</button></td>
            </tr>`);
            });
        }

        $(document).on('change', '.edit-qty', function() {
            let id = $(this).data('id');
            editItems[id].qty = parseInt($(this).val());
            renderEditItems();
        });
        $(document).on('click', '.remove-edit', function() {
            delete editItems[$(this).data('id')];
            renderEditItems();
        });


        // --- Admin Edit Logic ---
        $('#editOrderForm').submit(function(e) {
            e.preventDefault();
            let form = $(this);
            let url = form.attr('action');
            let data = form.serialize();

            $.ajax({
                url: url,
                type: 'POST', // Method spoofing is handled by _method input
                data: data,
                success: function(response) {
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
                error: function(xhr) {
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

        // --- Delete Logic (Form interception) ---
        $(document).on('submit', 'form', function(e) {
            // Intercept delete forms
            if ($(this).find('input[name="_method"][value="DELETE"]').length > 0) {
                e.preventDefault();
                let form = $(this);
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire('Deleted!', 'Record has been deleted.', 'success');
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Could not delete record.', 'error');
                            }
                        });
                    }
                });
            }
        });


        // --- Accept & Assign Logic ---
        $(document).on('click', '.accept-btn', function() {
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
                    }, function(res) {
                        if (res.success) {
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Accepted!',
                                text: 'Order accepted successfully.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', res.error, 'error');
                        }
                    }).fail(function() {
                        Swal.fire('Error', 'Request failed.', 'error');
                    });
                }
            });
        });

        $(document).on('click', '.assign-fs-btn', function() {
            $('#modalOrderId').val($(this).data('id'));
            $('#assignFieldStaffModal').modal('show');
        });

        $('#confirmAssignFieldStaffBtn').click(function() {
            let form = {
                _token: '{{ csrf_token() }}',
                fieldstaff_id: $('#modalFieldStaffSelect').val()
            };

            if (!form.fieldstaff_id) {
                Swal.fire('Warning', 'Please select a field staff.', 'warning');
                return;
            }

            $.post(`/retailer-orders/${$('#modalOrderId').val()}/assign-fieldstaff`, form, function(res) {
                if (res.success) {
                    $('#assignFieldStaffModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Assigned!',
                        text: 'Field Staff assigned successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Request failed.', 'error');
            });
        });

        // --- Show Logic ---
        $(document).on('click', '.view-btn', function() {
            let row = $(this).data('row');
            $('#showOrderBody').html(`
            <tr><th>Order Code</th><td>${row.order_code}</td></tr>
            <tr><th>Status</th><td>${row.status}</td></tr>
            <tr><th>Notes</th><td>${row.notes||'-'}</td></tr>
         `);
            let h = '';
            row.items.forEach(i => h += `<tr><td>${i.product_name||i.name}</td><td>${i.quantity||i.qty}</td><td>${i.total_amount||i.total}</td></tr>`);
            $('#showOrderItemsBody').html(h);
            $('#showOrderModal').modal('show');
        });
    });
</script>
@endpush