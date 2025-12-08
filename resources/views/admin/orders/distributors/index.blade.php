@extends('layouts.admin')

<style>
    /* Compact the table */
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

    .dataTables_length select {
        margin: 0 5px !important;
        width: 70px !important;
        display: inline-block;
    }

    #distributor-orders-table tbody td {
        font-size: 0.85em;
    }

    #distributor-orders-table td:last-child {
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

    /* Modal sizing */
    .modal-xl {
        max-width: 1140px;
    }
</style>

@section('page-body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Distributor Orders</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal" id="btnOpenCreate">
                <i class="fa fa-plus me-1"></i>Create Order
            </button>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="display table table-striped table-hover" id="distributor-orders-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Order Code</th>
                            <th>Distributor</th>
                            <th>Sales Manager</th>
                            <th>Products</th>
                            <th>Items</th>
                            <th>Qty</th>
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

{{-- Create Modal --}}
<div class="modal fade" id="createOrderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Distributor Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.distributor-orders.store') }}" method="POST" id="createOrderForm">
                @csrf
                <div class="modal-body">
                    {{-- Distributor Select (Admin only) --}}
                    <div class="mb-3">
                        <label class="form-label">Select Distributor</label>
                        <select class="form-select" name="distributor_id" required>
                            <option value="">Select Distributor</option>
                            @foreach($distributors as $distributor)
                            <option value="{{ $distributor->id }}">{{ $distributor->user->name }} ({{ $distributor->company_name }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Product Selection --}}
                    <div class="mb-3 p-3 bg-light rounded">
                        <label class="form-label fw-bold">Add Products</label>
                        <div class="input-group">
                            <select id="create_product_select" class="form-control">
                                <option value="">Select a product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->mrp }}" data-stock="{{ $product->stock }}">
                                    {{ $product->product_name }} - Stock: {{ $product->stock }}
                                </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="btn_add_product_create">Add</button>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered text-center" id="create_items_table">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="empty-row">
                                    <td colspan="6">No products added.</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Grand Total:</th>
                                    <th id="create_grand_total">0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Delivery Notes</label>
                        <textarea name="delivery_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Place Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Order <span id="edit_order_code"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editOrderForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    {{-- Hidden Fields --}}
                    <input type="hidden" name="status" id="edit_status">
                    <input type="hidden" name="distributor_id" id="edit_distributor_id_hidden">

                    <div class="mb-3">
                        <label class="form-label">Distributor: <strong id="edit_distributor_name"></strong></label>
                    </div>

                    {{-- Product Selection --}}
                    <div class="mb-3 p-3 bg-light rounded">
                        <label class="form-label fw-bold">Add Products</label>
                        <div class="input-group">
                            <select id="edit_product_select" class="form-control">
                                <option value="">Select a product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->mrp }}" data-stock="{{ $product->stock }}">
                                    {{ $product->product_name }} - Stock: {{ $product->stock }}
                                </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="btn_add_product_edit">Add</button>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered text-center" id="edit_items_table">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Grand Total:</th>
                                    <th id="edit_grand_total">0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Delivery Notes</label>
                        <textarea name="delivery_notes" id="edit_delivery_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Show Modal --}}
<div class="modal fade" id="showOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody id="showOrderBody"></tbody>
                </table>
                <h6 class="mt-3">Items</h6>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="showOrderItemsBody"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // --- Data Variables ---
        var createItems = {}; // { productId: { id, name, price, stock, quantity } }
        var editItems = {}; // { productId: { id, name, price, stock, quantity, orderItemId } }

        var table = $('#distributor-orders-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.distributor-orders.index') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'order_code',
                    name: 'order_code'
                },
                {
                    data: 'name',
                    name: 'distributor.user.name'
                }, // Distributor Name
                {
                    data: 'sales_manager_name',
                    name: 'salesManager.user.name'
                },
                {
                    data: 'product_summary',
                    name: 'items.product.product_name'
                }, // Searchable
                {
                    data: 'total_items',
                    name: 'total_items'
                },
                {
                    data: 'total_quantity',
                    name: 'total_quantity'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'placed_at',
                    name: 'placed_at'
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let btns = `<div class="action-buttons">`;
                        btns += `<button class="btn btn-info btn-sm view-btn" data-row='${JSON.stringify(row).replace(/'/g, "&apos;")}'><i class="fa fa-eye"></i></button>`;

                        // Edit/Actions based on status/role - Simplified for now or check roles in JS
                        // For simplicity, showing Edit for everyone (Controller handles permission) or check JS variables if passed.
                        // Assuming basic edit button for now.
                        btns += `<button class="btn btn-primary btn-sm edit-btn" data-row='${JSON.stringify(row).replace(/'/g, "&apos;")}'><i class="fa fa-edit"></i></button>`;

                        if (row.status.toLowerCase().includes('pending')) {
                            btns += `<button class="btn btn-danger btn-sm cancel-order-btn" data-id="${row.id}"><i class="fa fa-times"></i></button>`;
                        }

                        btns += `</div>`;
                        return btns;
                    }
                }
            ],
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
            }
        });

        // --- Create Modal Logic ---
        $('#btnOpenCreate').click(function() {
            createItems = {};
            renderCreateItems();
            $('#createOrderForm')[0].reset();
        });

        $('#btn_add_product_create').click(function() {
            let select = $('#create_product_select option:selected');
            let id = select.val();
            if (!id) {
                Swal.fire('Warning', 'Select a product', 'warning');
                return;
            }
            if (createItems[id]) {
                Swal.fire('Warning', 'Already added', 'warning');
                return;
            }

            createItems[id] = {
                id: id,
                name: select.text(),
                price: parseFloat(select.data('price')),
                stock: parseInt(select.data('stock')),
                quantity: 1
            };
            renderCreateItems();
        });

        $('#createOrderForm').submit(function(e) {
            e.preventDefault();
            let form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.success || res.message) {
                        $('#createOrderModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Created!',
                            text: res.success || res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', res.error, 'error');
                    }
                },
                error: function(xhr) {
                    let err = 'An error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                    Swal.fire('Error', err, 'error');
                }
            });
        });

        function renderCreateItems() {
            let tbody = $('#create_items_table tbody');
            tbody.empty();
            let total = 0;
            if (Object.keys(createItems).length === 0) {
                tbody.html('<tr><td colspan="6">No Items</td></tr>');
            } else {
                $.each(createItems, function(id, item) {
                    let sub = item.quantity * item.price;
                    total += sub;
                    tbody.append(`
                    <tr>
                        <td>${item.name}<input type="hidden" name="items[${id}][product_id]" value="${id}"></td>
                        <td>${item.stock}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm qty-input-create" 
                            data-id="${id}" value="${item.quantity}" min="1" max="${item.stock}" style="width:80px">
                            <input type="hidden" name="items[${id}][quantity]" value="${item.quantity}">
                        </td>
                        <td>${item.price}</td>
                        <td>${sub.toFixed(2)}</td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-create" data-id="${id}">X</button></td>
                    </tr>
                `);
                });
            }
            $('#create_grand_total').text(total.toFixed(2));
        }

        $(document).on('change', '.qty-input-create', function() {
            let id = $(this).data('id');
            let val = parseInt($(this).val());
            if (val < 1) val = 1;
            if (val > createItems[id].stock) {
                Swal.fire('Warning', 'Exceeds stock', 'warning');
                val = createItems[id].stock;
            }
            createItems[id].quantity = val;
            renderCreateItems();
        });

        $(document).on('click', '.remove-create', function() {
            delete createItems[$(this).data('id')];
            renderCreateItems();
        });

        // --- Edit Modal Logic ---
        $('#distributor-orders-table').on('click', '.edit-btn', function() {
            let row = $(this).data('row');
            $('#edit_order_code').text(row.order_code);
            $('#edit_distributor_name').text(row.name);
            $('#edit_distributor_id_hidden').val(row.distributor_id);

            let st = row.status.toLowerCase().replace(/ /g, '_');
            if (st.includes('pending')) st = 'pending';
            else if (st.includes('accepted_by_sales_manager')) st = 'accepted_by_sales_manager';
            else if (st.includes('delivered')) st = 'delivered';
            else if (st.includes('cancelled')) st = 'cancelled';
            $('#edit_status').val(st);

            $('#edit_delivery_notes').val(row.delivery_notes);

            editItems = {};
            row.items.forEach(function(item) {
                editItems[item.product_id] = {
                    id: item.product_id,
                    name: item.product_name,
                    price: parseFloat(item.unit_price),
                    stock: 9999,
                    quantity: item.quantity,
                    orderItemId: item.order_item_id
                };
            });
            renderEditItems();

            let url = "{{ route('admin.distributor-orders.update', ':id') }}".replace(':id', row.id);
            $('#editOrderForm').attr('action', url);
            $('#editOrderModal').modal('show');
        });

        $('#btn_add_product_edit').click(function() {
            let select = $('#edit_product_select option:selected');
            let id = select.val();
            if (!id) {
                Swal.fire('Warning', 'Select a product', 'warning');
                return;
            }
            if (editItems[id]) {
                Swal.fire('Warning', 'Already added', 'warning');
                return;
            }

            editItems[id] = {
                id: id,
                name: select.text(),
                price: parseFloat(select.data('price')),
                stock: 9999,
                quantity: 1,
                orderItemId: null // New item
            };
            renderEditItems();
        });

        $('#editOrderForm').submit(function(e) {
            e.preventDefault();
            let form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.success || res.message) {
                        $('#editOrderModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: res.success || res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', res.error, 'error');
                    }
                },
                error: function(xhr) {
                    let err = 'An error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                    Swal.fire('Error', err, 'error');
                }
            });
        });

        function renderEditItems() {
            let tbody = $('#edit_items_table tbody');
            tbody.empty();
            let total = 0;
            if (Object.keys(editItems).length === 0) {
                tbody.html('<tr><td colspan="5">No Items</td></tr>');
            } else {
                $.each(editItems, function(id, item) {
                    let sub = item.quantity * item.price;
                    total += sub;
                    tbody.append(`
                    <tr>
                        <td>${item.name}
                            <input type="hidden" name="items[${id}][product_id]" value="${id}">
                            ${item.orderItemId ? `<input type="hidden" name="items[${id}][order_item_id]" value="${item.orderItemId}">` : ''}
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm qty-input-edit" 
                            data-id="${id}" value="${item.quantity}" min="1" style="width:80px">
                            <input type="hidden" name="items[${id}][quantity]" value="${item.quantity}">
                        </td>
                        <td>${item.price}</td>
                        <td>${sub.toFixed(2)}</td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-edit" data-id="${id}">X</button></td>
                    </tr>
                `);
                });
            }
            $('#edit_grand_total').text(total.toFixed(2));
        }

        $(document).on('change', '.qty-input-edit', function() {
            let id = $(this).data('id');
            let val = parseInt($(this).val());
            if (val < 1) val = 1;
            editItems[id].quantity = val;
            renderEditItems();
        });

        $(document).on('click', '.remove-edit', function() {
            delete editItems[$(this).data('id')];
            renderEditItems();
        });

        // --- Show Modal ---
        $('#distributor-orders-table').on('click', '.view-btn', function() {
            let row = $(this).data('row');
            let html = `
            <tr><th>Order Code</th><td>${row.order_code}</td></tr>
            <tr><th>Distributor</th><td>${row.name}</td></tr>
            <tr><th>Sales Manager</th><td>${row.sales_manager_name}</td></tr>
            <tr><th>Status</th><td>${row.status}</td></tr>
            <tr><th>Placed At</th><td>${row.placed_at}</td></tr>
            <tr><th>Delivery Notes</th><td>${row.delivery_notes || '-'}</td></tr>
         `;
            $('#showOrderBody').html(html);

            let itemsHtml = '';
            row.items.forEach(function(item) {
                itemsHtml += `<tr>
                <td>${item.product_name}</td>
                <td>${item.quantity}</td>
                <td>${item.unit_price}</td>
                <td>${item.total_amount}</td>
             </tr>`;
            });
            $('#showOrderItemsBody').html(itemsHtml);
            $('#showOrderModal').modal('show');
        });

        // --- Actions ---
        $(document).on('click', '.accept-btn', function() {
            let id = $(this).data('id');
            let action = $(this).data('action'); // 'sm' or 'admin'
            let url = action === 'sm' ? `/distributor-orders/${id}/accept-by-sales-manager` : `/distributor-orders/${id}/accept-by-admin`;

            Swal.fire({
                title: 'Accept this order?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Accept'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(url, {
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if (res.success) {
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Accepted!',
                                text: res.success,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else Swal.fire('Error', res.error, 'error');
                    }).fail(function() {
                        Swal.fire('Error', 'Request failed', 'error');
                    });
                }
            });
        });

        $(document).on('click', '.cancel-order-btn', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Cancel this pending order?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`/distributor-orders/${id}/cancel-order`, {
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if (res.success) {
                            table.ajax.reload();
                            Swal.fire('Cancelled!', res.success, 'success');
                        } else Swal.fire('Error', res.error, 'error');
                    }).fail(function() {
                        Swal.fire('Error', 'Request failed', 'error');
                    });
                }
            });
        });

        $(document).on('click', '.request-cancel-btn', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Request Cancellation',
                input: 'text',
                inputLabel: 'Reason',
                inputPlaceholder: 'Enter cancellation reason',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to write something!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`/distributor-orders/${id}/request-cancellation`, {
                        _token: '{{ csrf_token() }}',
                        cancellation_reason: result.value
                    }, function(res) {
                        if (res.success) {
                            table.ajax.reload();
                            Swal.fire('Requested!', res.success, 'success');
                        } else Swal.fire('Error', res.error, 'error');
                    });
                }
            });
        });

        $(document).on('click', '.approve-cancel-btn', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Approve cancellation and restore stock?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`/distributor-orders/${id}/approve-cancellation`, {
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if (res.success) {
                            table.ajax.reload();
                            Swal.fire('Approved!', res.success, 'success');
                        } else Swal.fire('Error', res.error, 'error');
                    });
                }
            });
        });
    });
</script>
@endpush