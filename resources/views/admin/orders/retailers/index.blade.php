@extends('layouts.admin')

@section('page-body')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
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

        /* Spacing Fix for DataTable Footer */
        .dataTables_info {
            padding-left: 20px !important;
            padding-bottom: 20px !important;
            padding-top: 20px !important;
            color: var(--med-text-muted, #64748b) !important;
            font-size: 0.85rem !important;
        }
        .dataTables_paginate {
            padding-right: 20px !important;
            padding-bottom: 20px !important;
            padding-top: 20px !important;
        }

        /* Minimal Filter Bar Styles */
        .filter-bar {
            background: #fff;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .filter-bar:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05) !important;
        }

        .select2-minimal + .select2-container .select2-selection--single {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            height: 38px;
            padding-left: 5px;
            background-color: #f8f9fa;
            transition: all 0.2s;
        }

        .select2-minimal + .select2-container .select2-selection--single:hover {
            border-color: #3b82f6;
            background-color: #fff;
        }

        .select2-minimal + .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #475569;
        }

        .select2-minimal + .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        #btn-clear-all {
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
            background: #fff;
            color: #64748b;
            transition: all 0.2s;
            font-size: 0.8rem;
        }

        #btn-clear-all:hover {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fca5a5;
        }

        /* --- New Order View UI Styles --- */
        .order-timeline {
            position: relative;
            padding-left: 30px;
        }
        .order-timeline::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 5px;
            bottom: 5px;
            width: 2px;
            background: #e2e8f0;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-marker {
            position: absolute;
            left: -24px;
            top: 4px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #cbd5e1;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e2e8f0;
            z-index: 1;
        }
        .timeline-item.active .timeline-marker {
            background: #00497a;
            box-shadow: 0 0 0 2px #00497a44;
        }
        .timeline-content h6 {
            font-size: 0.85rem;
            margin-bottom: 2px;
            color: #1e293b;
        }
        .timeline-content span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .payment-status-card {
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            height: 100%;
        }
        .payment-status-card.paid {
            background: #f0fdf4;
            border: 1px solid #dcfce7;
        }
        .payment-status-card.pending {
            background: #fffbeb;
            border: 1px solid #fef3c7;
        }
        .payment-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .paid .payment-icon {
            background: #dcfce7;
            color: #15803d;
        }
        .pending .payment-icon {
            background: #fef3c7;
            color: #b45309;
        }
        .payment-info h6 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
            color: #64748b;
        }
        .payment-info p {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        .paid .payment-info p { color: #15803d; }
        .pending .payment-info p { color: #b45309; }
        /* --- End New Order View UI Styles --- */
    </style>
    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom pb-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-primary fw-bold">Retailer Orders</h5>
                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'retailer']) || Auth::user()->hasPermissionToCategory('retailer_orders', 'add'))
                    <a href="{{ route('admin.retailer.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>
                        Create Order</a>
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
                <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div> @endif


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
                    <table class="table table-striped table-hover" id="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Order Code</th>
                                <th>Retailer</th>
                                <th>Distributor</th>
                                <th>Products</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Placed At</th>
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
                                <select name="status" id="edit_status" class="form-select" required>
                                    @foreach(['pending', 'processing', 'approved', 'delivered', 'cancelled'] as $st)
                                        <option value="{{ $st }}">{{ ucfirst($st) }}</option>
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
                            @foreach($fieldstaffs as $fs) <option value="{{ $fs->id }}">{{ $fs->user->name }}</option>
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
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="fw-bold mb-0">Order Details <span id="modalOrderCode" class="text-primary ms-2"></span></h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3" id="showOrderContent">
                    <!-- Dynamic content will be injected here -->
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

    {{-- Distributor Batch Selection Modal --}}
    <div class="modal fade" id="distributorApproveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Retailer Order & Allocate Batches</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="distributorApproveForm">
                    <div class="modal-body">
                        <input type="hidden" id="approve_order_id" name="order_id">
                        <div class="alert alert-info py-2">
                            <small><i class="fa fa-info-circle"></i> Please allocate specific batches for each product to
                                fulfill this order. Total allocated quantity must match ordered quantity.</small>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Upload Invoice (PDF, JPG, PNG)</label>
                                <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Status</label>
                                <select name="payment_status" class="form-select">
                                    <option value="pending">Unpaid</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="batch_allocation_table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Product</th>
                                        <th style="width: 10%;" class="text-center">Ordered</th>
                                        <th style="width: 60%;">Batch Allocation</th>
                                    </tr>
                                </thead>
                                <tbody id="batch_allocation_body">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="btnSubmitDistributorApprove">Approve & Allocate
                            Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const canAcceptOrder = {{ Auth::user()->hasAnyRole(['distributor', 'admin', 'superadmin', 'manager']) ? 'true' : 'false' }};
        const canAssignFieldStaff = {{ Auth::user()->hasAnyRole(['admin', 'superadmin', 'manager', 'distributor']) ? 'true' : 'false' }};
        const isRetailer = {{ Auth::user()->hasRole('retailer') ? 'true' : 'false' }};
        const isAdmin = {{ Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']) ? 'true' : 'false' }};

        $(document).ready(function () {
            var editItems = {};

            var ajaxUrl = "{{ route('admin.retailer.index') }}";
            const urlParams = new URLSearchParams(window.location.search);
            // We'll pass retailer_id via data object in ajax call, so no need to append to Url if we want to be clean, 
            // but let's just make sure we don't double count it.

            var table = $('#orders-table').DataTable({
                order: [],
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
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: ajaxUrl,
                    data: function(d) {
                        d.payment_status = $('input[name="payment_status"]:checked').val();
                        d.status = $('#orderStatusTabs .nav-link.active').data('status');
                        d.retailer_id = urlParams.get('retailer_id');
                    }
                },
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
                    visible: !{{ Auth::user()->hasRole('retailer') ? 'true' : 'false' }},
                    render: function(data, type, row) {
                        return `<div class="d-flex flex-column">
                                    <span class="fw-bold text-primary entity-info-popover" 
                                          style="cursor: pointer;"
                                          data-bs-toggle="popover" 
                                          data-bs-trigger="hover" 
                                          data-bs-html="true"
                                          title="Retailer Details"
                                          data-bs-content="<b>Shop:</b> ${row.retailer_shop}<br><b>SM:</b> ${row.retailer_sm_name}<br><b>FS:</b> ${row.retailer_fs_name}<br><b>Phone:</b> ${row.retailer_phone}<br><b>GST:</b> ${row.retailer_gst}<br><b>DL:</b> ${row.retailer_dl}">
                                        ${data}
                                    </span>
                                    <span class="small text-muted">${row.retailer_shop}</span>
                                </div>`;
                    }
                },
                {
                    data: 'distributor_name',
                    name: 'distributor_name',
                    render: function (data, type, row) {
                        if (!data || data === 'N/A') return '-';
                        return `<span class="fw-bold text-dark entity-info-popover" 
                                      style="cursor: pointer;"
                                      data-bs-toggle="popover" 
                                      data-bs-trigger="hover" 
                                      data-bs-html="true"
                                      title="Distributor Details"
                                      data-bs-content="<b>Phone:</b> ${row.distributor_phone || 'N/A'}<br><b>GST:</b> ${row.distributor_gst || 'N/A'}<br><b>DL:</b> ${row.distributor_dl || 'N/A'}">
                                    ${data}
                                </span>`;
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
                        if (status === 'pending') badgeClass = 'bg-warning text-dark';
                        else if (status === 'processing') badgeClass = 'bg-info text-white';
                        else if (status === 'approved') badgeClass = 'bg-primary text-white';
                        else if (status === 'delivered') badgeClass = 'bg-success text-white';
                        else if (status === 'cancelled') badgeClass = 'bg-danger text-white';

                        return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                {
                    data: 'placed_at',
                    name: 'placed_at'
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
                    data: 'invoice_url',
                    name: 'invoice_url',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let st = (row.status || '').toLowerCase().replace(/ /g, '_');
                        if (data && st === 'delivered') {
                            let ext = data.split('.').pop().toLowerCase();
                            let icon = ext === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-image-o';
                            let code = row.order_code || 'Order';
                            let filename = `Invoice_${code}.${ext}`;
                            return `<a href="${data}" download="${filename}" target="_blank" class="btn btn-sm btn-success"><i class="fa ${icon}"></i> Download</a>`;
                        }
                        return '<span class="text-muted small">No Invoice</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function (d, t, row) {
                        let btns = `<div class="action-buttons">`;
                        btns += `<button class="btn btn-info btn-sm view-btn" title="View Details"><i class="fa fa-eye"></i></button>`;

                        let st = (row.status || '').toLowerCase().replace(/ /g, '_');

                        // Print Invoice
                        if (st === 'delivered') {
                            let invoiceUrl = "{{ route('admin.retailer.invoice', ':id') }}".replace(':id', row.id);
                            btns += `<a href="${invoiceUrl}" target="_blank" class="btn btn-dark btn-sm" title="Print Invoice"><i class="fa fa-print"></i></a>`;
                        }

                        // Retailer Cancel Pending Order
                        if (st === 'pending' && isRetailer) {
                            btns += `<button class="btn btn-danger btn-sm cancel-order-btn" data-id="${row.id}" title="Cancel Order"><i class="fa fa-times-circle"></i></button>`;
                        }

                        // Retailer Confirmation
                        if (st === 'approved' && isRetailer) {
                            btns += `<button class="btn btn-success btn-sm confirm-receipt-btn" data-id="${row.id}" title="Confirm Receipt">Confirm</button>`;
                        }

                        // Fallback for Admin/SalesManager to see confirm button if needed
                        if (isAdmin) {
                            if (st === 'approved') {
                                btns += `<button class="btn btn-success btn-sm confirm-receipt-btn" data-id="${row.id}" title="Confirm (Admin override)"><i class="fa fa-handshake-o"></i> Confirm</button>`;
                            }
                        }

                        btns += `</div>`;
                        return btns;

                    }
                }
                ],
                drawCallback: function() {
                    $('[data-bs-toggle="popover"]').popover();
                }
            });

            // Move Custom Filter
            $('#payment-filter-wrapper').children().appendTo('.payment-filter-container');

            // Initialize Minimal Select2
            $('.select2-minimal').select2({
                width: '100%',
                allowClear: true
            });


            // Payment Status listener
            $('input[name="payment_status"]').on('change', function() {
                table.ajax.reload();
            });


            // Tab Click
            $('#orderStatusTabs .nav-link').on('click', function(e) {
                e.preventDefault();
                $('#orderStatusTabs .nav-link').removeClass('active text-primary border-bottom-0').addClass('text-muted');
                $(this).removeClass('text-muted').addClass('active text-primary border-bottom-0');
                table.ajax.reload();
            });

            // --- Admin Edit Logic ---
            $(document).on('click', '.edit-btn', function () {
                let tr = $(this).closest('tr');
                if ($(tr).hasClass('child')) {
                    tr = $(tr).prev();
                }
                let row = $('#orders-table').DataTable().row(tr).data();
                $('#edit_retailer_id').val(row.retailer_id);
                $('#edit_distributor_id').val(row.distributor_id);
                $('#edit_status').val(row.status.toLowerCase().replace(/ /g, '_'));
                $('#edit_notes').val(row.notes);
                $('#edit_delivery_notes').val(row.delivery_notes);

                editItems = {};
                row.items.forEach(function (i) {
                    editItems[i.product_id] = {
                        id: i.product_id,
                        name: i.product_name || i.name,
                        qty: i.quantity || i.qty,
                        price: parseFloat(i.unit_price || i.price),
                        order_item_id: i.order_item_id
                    };
                });
                renderEditItems();
                $('#editOrderForm').attr('action', `/admin/retailer/${row.id}`);
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
                    url: `/admin/retailer/${deleteOrderId}`,
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

                $.post(`/admin/retailer/${cancelOrderId}/cancel-order`, {
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
                        $.post(`/admin/retailer/${id}/request-cancellation`, {
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
                        $.post(`/admin/retailer/${id}/approve-cancellation`, {
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
                        let url = "{{ route('admin.retailer.confirm-receipt', ':id') }}".replace(':id', id);
                        $.post(url, { _token: '{{ csrf_token() }}' }, function (res) {
                            if (res.success) {
                                table.ajax.reload(null, false);
                                showToast('success', res.success);
                                if (res.new_points) {
                                    $('.notification-box .badge').text(parseFloat(res.new_points).toFixed(2));
                                }
                            } else {
                                showToast('error', res.error || 'Failed to confirm order');
                            }
                        }).fail(function (xhr) {
                            showToast('error', 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Request failed'));
                        });
                    }
                });
            });


            // --- Distributor Approve & Batch Allocation Logic ---


            $(document).on('click', '.distributor-approve-btn', function () {
                let row = $(this).data('row');
                $('#approve_order_id').val(row.id);
                let tbody = $('#batch_allocation_body');
                tbody.empty().html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading items...</td></tr>');

                $('#distributorApproveModal').modal('show');

                let itemsHtml = '';
                let itemsProcessed = 0;

                if (!row.items || row.items.length === 0) {
                    tbody.html('<tr><td colspan="3" class="text-center text-danger">No items found in this order.</td></tr>');
                    return;
                }

                row.items.forEach(function (item, index) {
                    let productId = item.product_id;
                    let orderItemId = item.order_item_id;
                    let orderedQty = item.quantity;
                    let distributorId = row.distributor_id;

                    // Fetch batches for this product and distributor
                    $.get("{{ route('inventories.index') }}", {
                        product_id: productId,
                        distributor_id: distributorId,
                        length: -1 // Fetch all batches
                    }, function (res) {
                        let batches = res.data || [];
                        let batchOptions = '<option value="">-- Select Batch --</option>';
                        batches.forEach(b => {
                            if (b.stock > 0) {
                                batchOptions += `<option value="${b.id}" data-stock="${b.stock}">Batch: ${b.batch_no} | Exp: ${b.expiry_date} | Stock: ${b.stock}</option>`;
                            }
                        });

                        let itemRow = `
                                                                                                        <tr class="product-row" data-item-id="${orderItemId}" data-ordered-qty="${orderedQty}">
                                                                                                            <td>
                                                                                                                <strong>${item.product_name}</strong>
                                                                                                                <div class="small text-muted">
                                                                                                                    Pack: ${item.pack || 'N/A'} | 
                                                                                                                    Strip Size: ${item.strip_size || 1} | 
                                                                                                                    Box Size: ${item.box_size || 1} | 
                                                                                                                    Carton Size: ${item.carton_size || 1}
                                                                                                                </div>
                                                                                                                <input type="hidden" name="items[${orderItemId}][product_id]" value="${productId}">
                                                                                                            </td>
                                                                                                            <td class="text-center font-weight-bold">
                                                                                                                <span class="fs-5">${orderedQty}</span><br>
                                                                                                                <span class="badge bg-light text-dark border">${item.unit || 'Strips'}</span>
                                                                                                            </td>
                                                                                                            <td>
                                                                                                                <div class="allocation-container" id="allocation_for_${orderItemId}">
                                                                                                                    <input type="hidden" name="items_batches[${orderItemId}][order_item_id]" value="${orderItemId}">
                                                                                                                    <div class="allocation-item mb-2 d-flex gap-2">
                                                                                                                        <select name="items_batches[${orderItemId}][batches][0][inventory_id]" class="form-select form-select-sm batch-select" required style="flex: 2;">
                                                                                                                            ${batchOptions}
                                                                                                                        </select>
                                                                                                                        <input type="number" name="items_batches[${orderItemId}][batches][0][quantity]" class="form-control form-control-sm qty-input" value="${orderedQty}" min="1" required style="flex: 1;" placeholder="Qty">
                                                                                                                        <button type="button" class="btn btn-sm btn-outline-primary add-more-batch" data-item-id="${orderItemId}" data-options='${JSON.stringify(batchOptions).replace(/'/g, "&apos;")}' title="Split into multiple batches"><i class="fa fa-plus"></i></button>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                                <div class="small text-danger validation-msg" style="display:none;">Total must match ${orderedQty}</div>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    `;

                        if (itemsProcessed === 0) tbody.empty();
                        tbody.append(itemRow);
                        itemsProcessed++;
                    }).fail(function () {
                        if (itemsProcessed === 0) tbody.empty();
                        tbody.append(`<tr><td>${item.product_name}</td><td colspan="2" class="text-center text-danger">Failed to load batches</td></tr>`);
                        itemsProcessed++;
                    });
                });
            });

            // Add more batch rows for an item
            $(document).on('click', '.add-more-batch', function () {
                let itemId = $(this).data('item-id');
                let options = $(this).data('options');
                let container = $(`#allocation_for_${itemId}`);
                let idx = container.find('.allocation-item').length;

                let html = `
                                                                                                <div class="allocation-item mb-2 d-flex gap-2">
                                                                                                    <select name="items_batches[${itemId}][batches][${idx}][inventory_id]" class="form-select form-select-sm batch-select" required style="flex: 2;">
                                                                                                        ${options}
                                                                                                    </select>
                                                                                                    <input type="number" name="items_batches[${itemId}][batches][${idx}][quantity]" class="form-control form-control-sm qty-input" value="0" min="1" required style="flex: 1;" placeholder="Qty">
                                                                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-allocation-row"><i class="fa fa-times"></i></button>
                                                                                                </div>
                                                                                            `;
                container.append(html);
            });

            $(document).on('click', '.remove-allocation-row', function () {
                $(this).closest('.allocation-item').remove();
            });

            // Form Validation and Submission
            $('#distributorApproveForm').submit(function (e) {
                e.preventDefault();
                let isValid = true;

                // Validate each product row
                $('.product-row').each(function () {
                    let ordered = parseInt($(this).data('ordered-qty'));
                    let allocated = 0;
                    $(this).find('.qty-input').each(function () {
                        allocated += parseInt($(this).val() || 0);
                    });

                    if (allocated !== ordered) {
                        isValid = false;
                        $(this).find('.validation-msg').show().text(`Quantity mismatch: Allocated ${allocated} of ${ordered}`);
                        $(this).find('.allocation-container').addClass('border border-danger rounded p-1');
                    } else {
                        $(this).find('.validation-msg').hide();
                        $(this).find('.allocation-container').removeClass('border border-danger rounded p-1');
                    }

                    // Also check if batches are selected
                    $(this).find('.batch-select').each(function () {
                        if (!$(this).val()) {
                            isValid = false;
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });
                });

                if (!isValid) {
                    Swal.fire('Validation Error', 'Please ensure all items have correctly allocated batches matching the ordered quantity.', 'error');
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
                        $.post(`/admin/retailer/${id}/accept`, {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            if (res.success) {
                                table.ajax.reload();
                                showToast('success', res.success || 'Order approved successfully.');
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

                $.post(`/admin/retailer/${$('#modalOrderId').val()}/assign-fieldstaff`, form, function (res) {
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
                let tr = $(this).closest('tr');
                if ($(tr).hasClass('child')) {
                    tr = $(tr).prev();
                }
                let row = $('#orders-table').DataTable().row(tr).data();
                if (!row) return;

                $('#modalOrderCode').text('#' + row.order_code);

                let detailsHtml = `
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px !important;">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.7rem; letter-spacing: 0.05em;"><i class="fa fa-shop me-2"></i>Retailer Info</h6>
                                    <h5 class="fw-bold text-dark mb-1">${row.retailer_shop || row.retailer_name}</h5>
                                    ${row.retailer_shop ? `<div class="text-muted small mb-2"><i class="fa fa-user me-2"></i>${row.retailer_name}</div>` : ''}
                                    <div class="d-flex align-items-center mb-1"><i class="fa fa-phone text-muted me-2" style="width: 16px;"></i> <span class="small">${row.retailer_phone || 'N/A'}</span></div>
                                    <div class="d-flex align-items-start"><i class="fa fa-map-marker text-muted me-2 mt-1" style="width: 16px;"></i> <span class="text-wrap small">${row.retailer_address || 'N/A'}</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px !important;">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.7rem; letter-spacing: 0.05em;"><i class="fa fa-building me-2"></i>Distributor Info</h6>
                                    <h5 class="fw-bold text-dark mb-2">${row.distributor_name || 'N/A'}</h5>
                                    <div class="d-flex align-items-center mb-1"><i class="fa fa-phone text-muted me-2" style="width: 16px;"></i> <span class="small">${row.distributor_phone || 'N/A'}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px !important;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="py-3 px-4" style="background: #f8fafc !important; border-bottom: 1px solid #e2e8f0 !important;">Product</th>
                                            <th class="py-3 px-4 text-center" style="background: #f8fafc !important; border-bottom: 1px solid #e2e8f0 !important;">Batch/Exp</th>
                                            <th class="py-3 px-4 text-center" style="background: #f8fafc !important; border-bottom: 1px solid #e2e8f0 !important;">Qty</th>
                                            <th class="py-3 px-4 text-end" style="background: #f8fafc !important; border-bottom: 1px solid #e2e8f0 !important;">Price</th>
                                            <th class="py-3 px-4 text-end" style="background: #f8fafc !important; border-bottom: 1px solid #e2e8f0 !important;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                `;

                (row.items || []).forEach(function (i) {
                    let name = i.product_name || i.name || '-';
                    let qty = i.quantity || i.qty || 0;
                    let unitPrice = parseFloat(i.unit_price || 0);
                    let totalAmt = parseFloat(i.total_amount || i.total || (i.unit_price ? (i.unit_price * qty) : 0));

                    let batchHtml = '-';
                    if (i.batches && i.batches.length > 0) {
                        batchHtml = i.batches.map(b => `<div class="small"><span class="badge bg-soft-primary text-primary px-1 py-0 me-1" style="font-size: 0.65rem;">${b.batch_no}</span><span class="text-muted" style="font-size: 0.65rem;">${b.expiry_date}</span></div>`).join('');
                    }

                    detailsHtml += `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td class="py-3 px-4">
                                <div class="fw-bold text-dark">${name}</div>
                            </td>
                            <td class="py-3 px-4 text-center">${batchHtml}</td>
                            <td class="py-3 px-4 text-center"><span class="badge bg-soft-primary text-primary px-2 py-1" style="font-size: 0.75rem;">${qty} ${i.unit || ''}</span></td>
                            <td class="py-3 px-4 text-end small">₹${unitPrice.toFixed(2)}</td>
                            <td class="py-3 px-4 text-end fw-bold text-primary">₹${totalAmt.toFixed(2)}</td>
                        </tr>
                    `;
                });

                detailsHtml += `
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="4" class="text-end py-3 px-4 text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Grand Total:</td>
                                            <td class="py-3 px-4 text-end fw-bold text-success fs-5">₹${parseFloat(row.total_amount).toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="payment-status-card ${row.payment_status === 'paid' ? 'paid' : 'pending'} shadow-sm">
                                <div class="payment-icon">
                                    <i class="fa ${row.payment_status === 'paid' ? 'fa-check-circle' : 'fa-clock'}"></i>
                                </div>
                                <div class="payment-info">
                                    <h6>Payment Status</h6>
                                    <p>${(row.payment_status || 'Pending').toUpperCase()}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px !important;">
                                <div class="card-body py-2">
                                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Order Activity</h6>
                                    <div class="order-timeline">
                                        <div class="timeline-item active">
                                            <div class="timeline-marker"></div>
                                            <div class="timeline-content">
                                                <h6>Order Placed</h6>
                                                <span>${row.placed_at || 'N/A'}</span>
                                            </div>
                                        </div>
                                        ${row.status === 'Delivered' ? `
                                            <div class="timeline-item active">
                                                <div class="timeline-marker"></div>
                                                <div class="timeline-content">
                                                    <h6>Order Delivered</h6>
                                                    <span>Completed</span>
                                                </div>
                                            </div>
                                        ` : `
                                            <div class="timeline-item">
                                                <div class="timeline-marker"></div>
                                                <div class="timeline-content">
                                                    <h6>Current Status</h6>
                                                    <span>${row.status}</span>
                                                </div>
                                            </div>
                                        `}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#showOrderContent').html(detailsHtml);
                $('#showOrderModal').modal('show');
            });


        });
    </script>
@endpush