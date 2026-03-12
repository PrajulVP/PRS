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
    .segmented-control input:checked + label {
        color: #0f172a;
    }
    #pay_paid:checked + label {
        color: #15803d;
    }
    #pay_pending:checked + label {
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
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    #pay_all:checked ~ .selection-indicator {
        transform: translateX(0);
        background: #ffffff;
    }
    #pay_paid:checked ~ .selection-indicator {
        transform: translateX(100%);
        background: #dcfce7;
    }
    #pay_pending:checked ~ .selection-indicator {
        transform: translateX(200%);
        background: #fef9c3;
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

    .modal-xl {
        max-width: 1140px;
    }

    /* Premium Dropzone */
    .premium-dropzone {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        padding: 30px 20px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .premium-dropzone:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .premium-dropzone i {
        font-size: 2.5rem;
        color: #3b82f6;
        margin-bottom: 15px;
        display: block;
    }

    .premium-dropzone.has-file {
        border-style: solid;
        border-color: #10b981;
        background: #ecfdf5;
    }
</style>

@section('page-body')

    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom pb-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-primary fw-bold">Distributor Orders</h5>
                    @if(Auth::user()->hasRole('distributor'))
                        <a href="{{ route('admin.distributor-orders.create') }}" class="btn btn-primary btn-sm"><i
                                class="fa fa-plus"></i> Create Order</a>
                    @endif
                </div>
                
                <ul class="nav nav-tabs border-bottom-0" id="orderStatusTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-4 fw-bold text-primary border-bottom-0" id="tab-all" data-bs-toggle="tab" data-status="" type="button" role="tab">All</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-pending" data-bs-toggle="tab" data-status="pending" type="button" role="tab">Pending</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-processing" data-bs-toggle="tab" data-status="processing" type="button" role="tab">Processing</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-approved" data-bs-toggle="tab" data-status="approved" type="button" role="tab">Approved</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-delivered" data-bs-toggle="tab" data-status="delivered" type="button" role="tab">Delivered</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-cancelled" data-bs-toggle="tab" data-status="cancelled" type="button" role="tab">Cancelled</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 fw-bold text-muted" id="tab-rejected" data-bs-toggle="tab" data-status="rejected" type="button" role="tab">Rejected</button>
                    </li>
                </ul>
            </div>
            <div class="card-body pt-4">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <!-- Hidden filter element to be moved into datatable wrapper -->
                <div class="d-none" id="payment-filter-wrapper">
                    <div class="d-flex align-items-center mb-0 ms-2">
                        <span class="text-muted fw-bold me-3 small text-uppercase">Payment:</span>
                        <div class="segmented-control" id="payment_status_filter_group">
                            <input type="radio" name="payment_status" id="pay_all" value="" checked>
                            <label for="pay_all">All</label>
                            
                            <input type="radio" name="payment_status" id="pay_paid" value="paid">
                            <label for="pay_paid">Paid</label>
                            
                            <input type="radio" name="payment_status" id="pay_pending" value="pending">
                            <label for="pay_pending">Pending</label>
                            
                            <div class="selection-indicator"></div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="display table table-striped table-hover" id="distributor-orders-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Order Code</th>
                                @if(!Auth::user()->hasRole('distributor'))
                                    <th>Distributor</th>
                                @endif
                                {{-- <th>Sales Manager</th> Removed --}}
                                <th>Products</th>
                                {{-- <th>Items</th> --}}
                                {{-- <th>Qty</th> --}}
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Placed At</th>
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
                                    <option value="{{ $distributor->id }}">{{ $distributor->user->name }}
                                        ({{ $distributor->company_name }})</option>
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
                                        <option value="{{ $product->id }}" data-price="{{ $product->mrp }}"
                                            data-stock="{{ $product->stock }}">
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
                    <h5 class="modal-title">Edit Order - <span id="edit_order_code" class="fw-bold"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOrderForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        {{-- Hidden Fields --}}
                        <input type="hidden" name="status" id="edit_status">
                        <input type="hidden" name="distributor_id" id="edit_distributor_id_hidden">

                        <div class="mb-4">
                            <label class="form-label text-muted small text-uppercase fw-bold">Distributor</label>
                            <h5 class="mb-0 text-dark" id="edit_distributor_name"></h5>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Add Products</label>
                            <div class="input-group">
                                <select id="edit_product_select" class="form-control select2-modal">
                                    <option value="">Select a product to add...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->mrp }}"
                                            data-stock="{{ $product->stock }}">
                                            {{ $product->product_name }} (Stock: {{ $product->stock }}) - ₹{{ $product->mrp }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary" id="btn_add_product_edit">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Order Items</label>
                            <div class="table-responsive border rounded">
                                <table class="table table-hover align-middle mb-0" id="edit_items_table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Product</th>
                                            <th class="text-center" width="120">Unit</th>
                                            <th class="text-center" width="100">Qty</th>
                                            <th class="text-end" width="120">Price</th>
                                            <th class="text-end" width="120">Total</th>
                                            <th class="text-center" width="60"></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                            <td class="text-end fw-bold"><i class="fa fa-rupee"></i> <span
                                                    id="edit_grand_total">0.00</span></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Show Modal --}}
    <div class="modal fade" id="showOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="fw-bold mb-0">Order Details <span id="modalOrderCode" class="text-primary ms-2"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3" id="showOrderContent">
                    <!-- Dynamic content will be injected here via JS -->
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
        {{-- Upload Invoice Modal --}}
        <div class="modal fade" id="uploadInvoiceModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title text-white">Upload / Change Invoice</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="uploadInvoiceForm">
                        @csrf
                        <input type="hidden" id="upload_invoice_order_id">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Invoice Document</label>
                                <div class="premium-dropzone" id="invoice_dropzone">
                                    <i class="fa fa-file-invoice"></i>
                                    <h5 class="fw-bold mb-1">Upload Invoice File</h5>
                                    <p class="text-muted small mb-0">PDF, JPG, PNG (Max 5MB)</p>
                                    <div id="dropzone_filename" class="mt-2 fw-bold text-success d-none"></div>
                                </div>
                                <input type="file" name="invoice" id="invoice_file_input" class="d-none"
                                    accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary px-4">Upload Invoice</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const canDelete = {{ Auth::user()->hasAnyRole(['admin', 'superadmin']) ? 'true' : 'false' }};
            const isDistributor = {{ Auth::user()->hasRole('distributor') ? 'true' : 'false' }};
            const isSalesManager = {{ Auth::user()->hasRole('salesmanager') ? 'true' : 'false' }};
            const isAdmin = {{ Auth::user()->hasRole('admin') ? 'true' : 'false' }};
            // --- Data Variables ---
            var createItems = {}; // { productId: { id, name, price, stock, quantity } }
            var editItems = {}; // { productId: { id, name, price, stock, quantity, orderItemId } }

            var table = $('#distributor-orders-table').DataTable({
                processing: true,
                serverSide: true,
                order: [
                    [{{ Auth::user()->hasRole('distributor') ? 6 : 7 }}, 'desc']
                ],
                ajax: {
                    url: "{{ route('admin.distributor-orders.index') }}",
                    data: function (d) {
                        d.payment_status = $('input[name="payment_status"]:checked').val();
                        d.status = $('#orderStatusTabs .nav-link.active').data('status');
                    }
                },
                columns: [{
                    data: null,
                    name: 'id',
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
                    @if(!Auth::user()->hasRole('distributor')) {
                            data: 'name',
                            name: 'distributor.user.name'
                        }, // Distributor Name
                    @endif
                {
                    data: 'product_summary',
                    name: 'items.product.product_name',
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
                    render: function (data, type, row) {
                        return `<span class="fw-bold text-success"><i class="fa fa-rupee"></i> ${data}</span>`;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data, type, row) {
                        let status = data.toLowerCase();
                        let badgeClass = 'bg-secondary';
                        if (status === 'pending') badgeClass = 'bg-warning text-dark';
                        else if (status === 'processing') badgeClass = 'bg-info';
                        else if (status === 'approved') badgeClass = 'bg-primary';
                        else if (status === 'delivered') badgeClass = 'bg-success';
                        else if (status === 'cancelled') badgeClass = 'bg-danger';

                        return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    render: function (data, type, row) {
                        let status = (data || 'pending').toLowerCase();
                        let badgeClass = 'bg-secondary';
                        if (status === 'paid') badgeClass = 'bg-success';
                        else if (status === 'failed') badgeClass = 'bg-danger';
                        else badgeClass = 'bg-warning text-dark';

                        return `<span class="badge ${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
                    }
                },
                {
                    data: 'placed_at',
                    name: 'placed_at'
                },
                {
                    data: 'invoice_url',
                    name: 'invoice_url',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (data) {
                            let ext = data.split('.').pop().toLowerCase();
                            let icon = ext === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-image-o';

                            // Safely construct filename
                            let name = (row.name || 'Distributor').replace(/[^a-zA-Z0-9_\-]/g, '_');
                            let dateStr = row.placed_at || new Date().toISOString();
                            let date = dateStr.split(' ')[0] || new Date().toISOString().split('T')[0];
                            let code = row.order_code || 'Order';

                            let filename = `Invoice_${code}_${name}_${date}.${ext}`;
                            return `<a href="${data}" download="${filename}" target="_blank" class="btn btn-sm btn-success"><i class="fa ${icon}"></i> Download</a>`;
                        }
                        return '<span class="text-muted small">No Invoice</span>';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let btns = `<div class="action-buttons">`;
                        // View Button (Always visible)
                        btns += `<button class="btn btn-info btn-sm view-btn" data-row='${JSON.stringify(row).replace(/'/g, "&apos;")}'><i class="fa fa-eye"></i></button>`;

                        // System Invoice Button
                        let invoiceUrl = "{{ route('admin.distributor-orders.invoice', ':id') }}".replace(':id', row.id);
                        btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="System Invoice"><i class="fa fa-print"></i></a>`;

                        let st = row.status.toLowerCase();

                        // Interactions simplified for history page
                        // Approval buttons moved to separate Approvals page

                        if (!isDistributor && (st === 'processing' || st === 'approved')) {
                            btns += `<button class="btn btn-warning btn-sm upload-invoice-btn" data-id="${row.id}" title="Upload/Change Invoice"><i class="fa fa-upload"></i></button>`;
                        }

                        if (st === 'approved') {
                            if (isDistributor) {
                                btns += `<button class="btn btn-primary btn-sm confirm-receipt-btn" data-id="${row.id}" title="Confirm Receipt"> Confirm</button>`;
                            }
                        }

                        btns += `</div>`;
                        return btns;
                    }
                }
                ],
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-3 d-flex align-items-center'<'col-md-4'l><'col-md-4 payment-filter-container'><'col-md-4'f>>" +
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
                }
            });

            // Move Custom Filter
            $('#payment-filter-wrapper').children().appendTo('.payment-filter-container');

            // Filter Change
            $(document).on('change', 'input[name="payment_status"]', function () {
                table.ajax.reload();
            });

            // Tab Click
            $('#orderStatusTabs .nav-link').on('click', function(e) {
                e.preventDefault();
                $('#orderStatusTabs .nav-link').removeClass('active text-primary border-bottom-0').addClass('text-muted');
                $(this).removeClass('text-muted').addClass('active text-primary border-bottom-0');
                table.ajax.reload();
            });

            // --- Create Modal Logic ---
            $('#btnOpenCreate').click(function () {
                createItems = {};
                renderCreateItems();
                $('#createOrderForm')[0].reset();
            });

            // ... (existing code) ...

            // --- Upload Invoice Modal ---
            $(document).on('click', '.upload-invoice-btn', function () {
                let id = $(this).data('id');
                $('#upload_invoice_order_id').val(id);
                $('#uploadInvoiceForm')[0].reset();
                $('#invoice_dropzone').removeClass('has-file');
                $('#dropzone_filename').addClass('d-none').text('');
                $('#uploadInvoiceModal').modal('show');
            });

            $(document).on('click', '#invoice_dropzone', function () {
                $('#invoice_file_input').click();
            });

            $(document).on('change', '#invoice_file_input', function () {
                let file = this.files[0];
                if (file) {
                    $('#invoice_dropzone').addClass('has-file');
                    $('#dropzone_filename').removeClass('d-none').text(file.name);
                }
            });

            // Drag and Drop
            const dropzone = document.getElementById('invoice_dropzone');
            if (dropzone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, e => {
                        e.preventDefault();
                        e.stopPropagation();
                    }, false);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, () => dropzone.classList.add('bg-primary-subtle', 'border-primary'), false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, () => dropzone.classList.remove('bg-primary-subtle', 'border-primary'), false);
                });

                dropzone.addEventListener('drop', e => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    if (files.length) {
                        document.getElementById('invoice_file_input').files = files;
                        $('#invoice_file_input').trigger('change');
                    }
                }, false);
            }

            $('#uploadInvoiceForm').submit(function (e) {
                e.preventDefault();
                let id = $('#upload_invoice_order_id').val();
                let formData = new FormData(this);
                let $btn = $(this).find('button[type="submit"]');

                $btn.prop('disabled', true).text('Uploading...');

                $.ajax({
                    url: `/admin/distributor-orders/${id}/upload-invoice`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#uploadInvoiceModal').modal('hide');
                        table.ajax.reload(null, false);
                        showToast('success', res.success || 'Invoice uploaded successfully');
                    },
                    error: function (xhr) {
                        showToast('error', xhr.responseJSON ? xhr.responseJSON.error : 'Upload failed');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Upload');
                    }
                });
            });

            // Delete Order Logic
            let deleteOrderId = null;
            $(document).on('click', '.delete-order-btn', function () {
                deleteOrderId = $(this).data('id');
                $('#deleteConfirmModal').modal('show');
            });

            $('#confirmDeleteBtn').click(function () {
                if (!deleteOrderId) return;

                $.ajax({
                    url: `/distributor-orders/${deleteOrderId}`, // Assuming resource route uses destroy
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        $('#deleteConfirmModal').modal('hide');
                        if (res.success) {
                            table.ajax.reload();
                            showToast('success', res.success || 'Order deleted successfully');
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

            // Upload Invoice Logic
            $(document).on('click', '.upload-invoice-btn', function () {
                $('#upload_invoice_order_id').val($(this).data('id'));
                $('#uploadInvoiceForm')[0].reset();
                $('#uploadInvoiceModal').modal('show');
            });

            $('#uploadInvoiceForm').submit(function (e) {
                e.preventDefault();
                let form = $(this)[0];
                let orderId = $('#upload_invoice_order_id').val();
                let formData = new FormData(form);
                let url = "{{ route('admin.distributor-orders.upload-invoice', ':id') }}".replace(':id', orderId);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            $('#uploadInvoiceModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', res.success);
                        } else {
                            showToast('error', res.error || 'Failed to upload invoice');
                        }
                    },
                    error: function (xhr) {
                        let err = 'An error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                        showToast('error', err);
                    }
                });
            });

            // --- Create Modal Logic ---
            $('#btnOpenCreate').click(function () {
                createItems = {};
                renderCreateItems();
                $('#createOrderForm')[0].reset();
            });

            $('#btn_add_product_create').click(function () {
                let select = $('#create_product_select option:selected');
                let id = select.val();
                if (!id) {
                    showToast('error', 'Select a product');
                    return;
                }
                if (createItems[id]) {
                    showToast('error', 'Already added');
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

            $('#createOrderForm').submit(function (e) {
                e.preventDefault();
                let form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function (res) {
                        if (res.success || res.message) {
                            $('#createOrderModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', res.success || res.message);
                        } else {
                            showToast('error', res.error);
                        }
                    },
                    error: function (xhr) {
                        let err = 'An error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                        showToast('error', err);
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
                    $.each(createItems, function (id, item) {
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

            $(document).on('change', '.qty-input-create', function () {
                let id = $(this).data('id');
                let val = parseInt($(this).val());
                if (val < 1) val = 1;
                if (val > createItems[id].stock) {
                    showToast('error', 'Exceeds stock');
                    val = createItems[id].stock;
                }
                createItems[id].quantity = val;
                renderCreateItems();
            });

            $(document).on('click', '.remove-create', function () {
                delete createItems[$(this).data('id')];
                renderCreateItems();
            });

            // --- Edit Modal Logic ---
            $('#distributor-orders-table').on('click', '.edit-btn', function () {
                let row = $(this).data('row');
                $('#edit_order_code').text(row.order_code);
                $('#edit_distributor_name').text(row.name);
                $('#edit_distributor_id_hidden').val(row.distributor_id);

                let st = row.status.toLowerCase().replace(/ /g, '_');
                if (['pending', 'processing', 'approved', 'delivered', 'cancelled'].includes(st)) {
                    $('#edit_status').val(st);
                }

                // Removed delivery notes population

                editItems = {};
                row.items.forEach(function (item) {
                    editItems[item.product_id] = {
                        id: item.product_id,
                        name: item.product_name,
                        price: parseFloat(item.unit_price),
                        stock: 9999,
                        quantity: item.quantity,
                        unit: item.unit || 'Box',
                        orderItemId: item.order_item_id
                    };
                });
                renderEditItems();

                let url = "{{ route('admin.distributor-orders.update', ':id') }}".replace(':id', row.id);
                $('#editOrderForm').attr('action', url);
                $('#editOrderModal').modal('show');
            });

            $('#btn_add_product_edit').click(function () {
                let selectEl = $('#edit_product_select');
                let id = selectEl.val(); // Get value directly from select
                let selectedOption = selectEl.find('option:selected'); // Get selected option

                if (!id) {
                    showToast('error', 'Select a product');
                    return;
                }
                if (editItems[id]) {
                    editItems[id].quantity += 1;
                    renderEditItems();
                    return;
                }

                editItems[id] = {
                    id: id,
                    name: selectedOption.text(),
                    price: parseFloat(selectedOption.data('price')) || 0,
                    stock: parseInt(selectedOption.data('stock')) || 0,
                    quantity: 1,
                    unit: 'Box',
                    orderItemId: null
                };
                renderEditItems();
            });

            $('#editOrderForm').submit(function (e) {
                e.preventDefault();
                let form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function (res) {
                        if (res.success || res.message) {
                            $('#editOrderModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', res.success || res.message);
                        } else {
                            showToast('error', res.error);
                        }
                    },
                    error: function (xhr) {
                        let err = 'An error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                        showToast('error', err);
                    }
                });
            });

            function renderEditItems() {
                let tbody = $('#edit_items_table tbody');
                tbody.empty();
                let total = 0;
                if (Object.keys(editItems).length === 0) {
                    tbody.html('<tr><td colspan="5" class="text-center text-muted">No Items Added</td></tr>');
                } else {
                    $.each(editItems, function (key, item) {
                        let price = parseFloat(item.price) || 0;
                        let qty = parseInt(item.quantity) || 1;
                        let sub = qty * price;
                        total += sub;

                        // Ensure ID is passed as string to avoid type confusion
                        let rowId = item.id;
                        let unit = item.unit || 'Box';
                        let options = '';
                        ['Box', 'Carton', 'Strips'].forEach(function (u) {
                            options += `<option value="${u}" ${unit === u ? 'selected' : ''}>${u}</option>`;
                        });

                        tbody.append(`
                                                                                                                                                                                                                                                        <tr>
                                                                                                                                                                                                                                                            <td>${item.name}
                                                                                                                                                                                                                                                                <input type="hidden" name="items[${rowId}][product_id]" value="${rowId}">
                                                                                                                                                                                                                                                                ${item.orderItemId ? `<input type="hidden" name="items[${rowId}][order_item_id]" value="${item.orderItemId}">` : ''}
                                                                                                                                                                                                                                                            </td>
                                                                                                                                                                                                                                                            <td>
                                                                                                                                                                                                                                                                <select class="form-select form-select-sm unit-select-edit" data-id="${rowId}" style="width: 90px; margin: 0 auto;">
                                                                                                                                                                                                                                                                    ${options}
                                                                                                                                                                                                                                                                </select>
                                                                                                                                                                                                                                                                <input type="hidden" name="items[${rowId}][unit]" value="${unit}">
                                                                                                                                                                                                                                                            </td>
                                                                                                                                                                                                                                                            <td>
                                                                                                                                                                                                                                                                <input type="number" class="form-control form-control-sm qty-input-edit" 
                                                                                                                                                                                                                                                                data-id="${rowId}" value="${qty}" min="1" style="width:80px; margin: 0 auto;">
                                                                                                                                                                                                                                                                <input type="hidden" name="items[${rowId}][quantity]" value="${qty}">
                                                                                                                                                                                                                                                            </td>
                                                                                                                                                                                                                                                            <td class="text-end">${price.toFixed(2)}</td>
                                                                                                                                                                                                                                                            <td class="text-end">${sub.toFixed(2)}</td>
                                                                                                                                                                                                                                                            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-edit" data-id="${rowId}"><i class="fa fa-times"></i></button></td>
                                                                                                                                                                                                                                                        </tr>
                                                                                                                                                                                                                                                    `);
                    });
                }
                $('#edit_grand_total').text(total.toFixed(2));
            }

            $(document).on('change', '.unit-select-edit', function () {
                let id = $(this).data('id');
                let val = $(this).val();
                if (editItems[id]) {
                    editItems[id].unit = val;
                    // Update hidden input directly to avoid full table re-render
                    $(`input[name="items[${id}][unit]"]`).val(val);
                }
            });

            $(document).on('change', '.qty-input-edit', function () {
                let id = $(this).data('id');
                let val = parseInt($(this).val());
                if (val < 1) val = 1;
                if (editItems[id]) {
                    editItems[id].quantity = val;
                    renderEditItems();
                }
            });

            $(document).on('click', '.remove-edit', function () {
                let id = $(this).data('id');
                if (editItems[id]) {
                    delete editItems[id];
                    renderEditItems();
                }
            });

            // --- Show Modal ---
            $('#distributor-orders-table').on('click', '.view-btn', function () {
                let row = $(this).data('row');
                $('#modalOrderCode').text('#' + row.order_code);

                let detailsHtml = `
                                                        <div class="row mb-4">
                                                            <div class="col-md-6 mb-3 mb-md-0">
                                                                <div class="card h-100 border-0 shadow-sm">
                                                                    <div class="card-body">
                                                                        <h6 class="text-uppercase text-muted fw-bold mb-3"><i class="fa fa-building me-2"></i>Distributor Info</h6>
                                                                        <h5 class="fw-bold text-dark mb-1">${row.name || 'N/A'}</h5>
                                                                        <div class="d-flex align-items-center mb-1"><i class="fa fa-envelope text-muted me-2" style="width: 16px;"></i> <span>${row.distributor_email || 'N/A'}</span></div>
                                                                        <div class="d-flex align-items-center mb-1"><i class="fa fa-phone text-muted me-2" style="width: 16px;"></i> <span>${row.distributor_phone || 'N/A'}</span></div>
                                                                        <div class="d-flex align-items-start mb-1"><i class="fa fa-map-marker text-muted me-2 mt-1" style="width: 16px;"></i> <span class="text-wrap">${row.distributor_address || 'N/A'}</span></div>
                                                                        <div class="d-flex align-items-center mb-1"><i class="fa fa-id-card text-muted me-2" style="width: 16px;"></i> <span class="text-muted small me-1">GST:</span> <span>${row.distributor_gst || 'N/A'}</span></div>
                                                                        <div class="d-flex align-items-center"><i class="fa fa-file-alt text-muted me-2" style="width: 16px;"></i> <span class="text-muted small me-1">DL No:</span> <span>${row.distributor_dl || 'N/A'}</span></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="card h-100 border-0 shadow-sm">
                                                                    <div class="card-body">
                                                                        <h6 class="text-uppercase text-muted fw-bold mb-3"><i class="fa fa-industry me-2"></i>Company Info</h6>
                                                                        <h5 class="fw-bold text-dark mb-2">PRS Company</h5>
                                                                        <div class="text-muted small mb-2"><i class="fa fa-user-tie me-2"></i>Sales Manager: ${row.sales_manager_name || 'N/A'}</div>
                                                                        <div class="d-flex align-items-center mb-1"><i class="fa fa-envelope text-muted me-2" style="width: 16px;"></i> <span>info@prs.com</span></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card border-0 shadow-sm mb-4">
                                                            <div class="card-body p-0">
                                                                <div class="table-responsive">
                                                                    <table class="table table-striped table-hover mb-0">
                                                                        <thead class="bg-light">
                                                                            <tr>
                                                                                <th class="py-3 px-4">Product</th>
                                                                                <th class="py-3 px-4 text-center">Batch/Exp</th>
                                                                                <th class="py-3 px-4 text-center">Qty</th>
                                                                                <th class="py-3 px-4 text-end">Price</th>
                                                                                <th class="py-3 px-4 text-end">Total</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                    `;

                row.items.forEach(function (i) {
                    let name = i.product_name || i.name || '-';
                    let qty = i.quantity || i.qty || 0;
                    let unitPrice = parseFloat(i.unit_price || 0);
                    let totalAmt = parseFloat(i.total_amount || i.total || (i.unit_price ? (i.unit_price * qty) : 0));

                    let batchHtml = '-';
                    if (i.batches && i.batches.length > 0) {
                        batchHtml = i.batches.map(b => `<div class="small"><span class="badge bg-soft-primary text-primary px-1 py-0 me-1">${b.batch_no}</span><span class="text-muted small">${b.expiry_date}</span></div>`).join('');
                    }

                    detailsHtml += `
                                                                <tr>
                                                                    <td class="py-3 px-4">
                                                                        <div class="fw-bold text-dark">${name}</div>
                                                                    </td>
                                                                    <td class="py-3 px-4 text-center">${batchHtml}</td>
                                                                    <td class="py-3 px-4 text-center"><span class="badge bg-soft-primary text-primary px-2 py-1">${qty} ${i.unit || ''}</span></td>
                                                                    <td class="py-3 px-4 text-end">₹${unitPrice.toFixed(2)}</td>
                                                                    <td class="py-3 px-4 text-end fw-bold text-primary">₹${totalAmt.toFixed(2)}</td>
                                                                </tr>
                                                            `;
                });

                detailsHtml += `
                                                                            </tbody>
                                                                            <tfoot class="bg-light">
                                                                                <tr>
                                                                                    <td colspan="4" class="text-end py-3 px-4 text-uppercase fw-bold text-muted">Grand Total:</td>
                                                                                    <td class="py-3 px-4 text-end fw-bold text-success fs-5">₹${parseFloat(row.total_amount).toFixed(2)}</td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-4 mb-3">
                                                                    <div class="bg-light rounded p-3 h-100">
                                                                        <h6 class="text-muted fw-bold text-uppercase mb-2">Payment Status</h6>
                                                                        <p class="mb-0 fs-5"><span class="badge ${row.payment_status === 'paid' ? 'bg-success' : 'bg-warning'}">${(row.payment_status || 'Pending').toUpperCase()}</span></p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 mb-3">
                                                                    <div class="bg-light rounded p-3 h-100">
                                                                        <h6 class="text-muted fw-bold text-uppercase mb-2">Order Timeline</h6>
                                                                        <div class="d-flex align-items-center"><i class="fa fa-calendar-alt text-muted me-2"></i> <strong>${row.placed_at || 'N/A'}</strong></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        `;

                $('#showOrderContent').html(detailsHtml);
                $('#showOrderModal').modal('show');
            });

            // --- Actions ---
            $(document).on('click', '.accept-btn', function () {
                let id = $(this).data('id');
                let action = $(this).data('action'); // 'sm' or 'admin'
                let url = action === 'sm' ? "{{ route('admin.distributor-orders.accept-by-sales-manager', ':id') }}".replace(':id', id) : "{{ route('admin.distributor-orders.accept-by-admin', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Accept this order?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Accept'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(url, {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                showToast('success', res.success);
                            } else showToast('error', res.error);
                        }).fail(function () {
                            showToast('error', 'Request failed');
                        });
                    }
                });
            });

            // Cancel Order Logic (Pending Orders)
            let cancelOrderId = null;
            $(document).on('click', '.cancel-order-btn', function () {
                cancelOrderId = $(this).data('id');
                $('#cancel_reason_input').val(''); // Reset input
                $('#cancelConfirmModal').modal('show');
            });

            $('#confirmCancelBtn').click(function () {
                if (!cancelOrderId) return;

                let reason = $('#cancel_reason_input').val().trim();
                if (!reason) {
                    showToast('error', 'Please provide a cancellation reason.');
                    return;
                }

                let url = "{{ route('admin.distributor-orders.cancel-order', ':id') }}".replace(':id', cancelOrderId);
                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    cancellation_reason: reason
                }, function (res) {
                    $('#cancelConfirmModal').modal('hide');
                    if (res.success) {
                        table.ajax.reload();
                        showToast('success', res.success);
                    } else showToast('error', res.error);
                }).fail(function (xhr) {
                    $('#cancelConfirmModal').modal('hide');
                    let err = 'Request failed';
                    if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                    showToast('error', err);
                });
            });

            $(document).on('click', '.request-cancel-btn', function () {
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
                        let url = "{{ route('admin.distributor-orders.request-cancellation', ':id') }}".replace(':id', id);
                        $.post(url, {
                            _token: '{{ csrf_token() }}',
                            cancellation_reason: result.value
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                showToast('success', res.success);
                            } else showToast('error', res.error);
                        });
                    }
                });
            });

            $(document).on('click', '.approve-cancel-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Approve cancellation and restore stock?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ route('admin.distributor-orders.approve-cancellation', ':id') }}".replace(':id', id);
                        $.post(url, {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                showToast('success', res.success);
                            } else showToast('error', res.error);
                        });
                    }
                });
            });

            // Sales Manager Approve Click
            $(document).on('click', '.approve-order-btn', function () {
                let id = $(this).data('id');
                $('#approve_order_id').val(id);
                $('#approveOrderForm')[0].reset();
                $('#approveOrderModal').modal('show');
            });

            // Handle Approval Submission
            $('#approveOrderForm').submit(function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#approve_order_id').val();

                $.ajax({
                    url: "{{ route('admin.distributor-orders.approve', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#approveOrderModal').modal('hide');
                        if (res.success) {
                            table.ajax.reload();
                            showToast('success', res.success);
                        } else showToast('error', res.error);
                    },
                    error: function (xhr) {
                        let err = 'An error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                        showToast('error', err);
                    }
                });
            });

            // Admin Process/Accept
            $(document).on('click', '.accept-admin-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Approve Order',
                    text: "Select payment status for this order:",
                    icon: 'warning',
                    input: 'select',
                    inputOptions: {
                        'pending': 'Pending',
                        'paid': 'Paid'
                    },
                    inputValue: 'pending',
                    showCancelButton: true,
                    confirmButtonText: 'Approve & Process',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'You need to choose a payment status!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ route('admin.distributor-orders.accept-by-admin', ':id') }}".replace(':id', id);
                        $.post(url, {
                            _token: '{{ csrf_token() }}',
                            payment_status: result.value
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                showToast('success', res.success);
                            } else showToast('error', res.error);
                        });
                    }
                });
            });

            // Confirm Receipt Logic
            $(document).on('click', '.confirm-receipt-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Confirm Order?',
                    text: "Have you received the order? This will mark it as Delivered.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Confirm'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ route('admin.distributor-orders.confirm-receipt', ':id') }}".replace(':id', id);
                        $.post(url, {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                showToast('success', res.success);
                            } else showToast('error', res.error);
                        });
                    }
                });
            });

        });
    </script>
@endpush