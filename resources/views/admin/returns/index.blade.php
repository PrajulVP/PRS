@extends('layouts.admin')

@section('title', 'Return Requests & Credit Notes')

@section('page-body')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold text-main-theme mb-1">Returns & Credits</h4>
            <p class="text-muted-theme small mb-0">Track and manage product returns and issued credit notes.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            @if(auth()->user()->hasRole('retailer') || auth()->user()->hasRole('distributor'))
            <button class="btn btn-primary shadow-sm rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#createReturnModal">
                <i class="fa fa-plus-circle me-2"></i>New Return Request
            </button>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-card-theme rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-soft-warning p-2 rounded-3 me-2">
                            <i class="fa fa-clock text-warning"></i>
                        </div>
                        <span class="text-muted-theme small fw-bold">Pending</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-main-theme" id="count-pending">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-card-theme rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-soft-info p-2 rounded-3 me-2">
                            <i class="fa fa-check-circle text-info"></i>
                        </div>
                        <span class="text-muted-theme small fw-bold">Approved</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-main-theme" id="count-approved">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-card-theme rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-soft-success p-2 rounded-3 me-2">
                            <i class="fa fa-file-invoice-dollar text-success"></i>
                        </div>
                        <span class="text-muted-theme small fw-bold">Completed</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-main-theme" id="count-completed">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-card-theme rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-soft-primary p-2 rounded-3 me-2">
                            <i class="fa fa-wallet text-primary"></i>
                        </div>
                        <span class="text-muted-theme small fw-bold">Total Credits</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-main-theme" id="total-refunds">₹0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs (Only for Admin and Distributors who see multiple types) -->
    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('distributor'))
    <div class="card border-0 shadow-sm bg-card-theme mb-3" style="border-radius: 12px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-justified" id="returnTypeTabs">
                <li class="nav-item">
                    <a class="nav-link active rounded-3 fw-bold small py-2" data-type="retailer" href="javascript:void(0)">
                        <i class="fa fa-shopping-basket me-1"></i> Retailer Returns
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-3 fw-bold small py-2" data-type="distributor" href="javascript:void(0)">
                        <i class="fa fa-truck me-1"></i> Distributor Returns
                    </a>
                </li>
            </ul>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm bg-card-theme" style="border-radius: 16px !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="returnsTable">
                    <thead>
                        <tr>
                            <th class="ps-4">Return Code</th>
                            <th>Requester</th>
                            <th>Responsible Staff</th>
                            <th>Product Details</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables populated --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Return Modal -->
<div class="modal fade shadow-lg" id="viewReturnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 bg-card-theme" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="bg-soft-primary p-2 rounded-3 me-3">
                        <i class="fa fa-file-invoice text-primary fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-main-theme" id="viewReturnCodeHeader">RET-XXXXX</h5>
                        <p class="text-muted-theme small mb-0">Return Request Details</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5">
                        <label class="text-muted-theme small text-uppercase fw-bold mb-3 d-block">Evidence Photos</label>
                        <div id="returnGallery" class="row g-2 overflow-auto" style="max-height: 400px;">
                            <!-- Images will be injected here -->
                        </div>
                        <div id="noImagePlaceholder" class="text-center text-muted p-5 bg-light-theme rounded-4 w-100">
                            <i class="fa fa-image fa-3x mb-2"></i>
                            <p class="small mb-0">No image uploaded</p>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card border-0 bg-light-theme mb-3" style="border-radius: 16px;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-main-theme" id="viewProductName">Product Name</h6>
                                        <div id="viewProductVariant"></div>
                                    </div>
                                    <div class="text-end" id="viewStatusBadge">
                                        {{-- Status badge injected here --}}
                                    </div>
                                </div>
                                <div class="row g-3 py-3 border-top border-bottom border-light-theme">
                                    <div class="col-6">
                                        <label class="text-muted-theme small text-uppercase fw-bold mb-1 d-block">Quantity</label>
                                        <span id="viewQuantity" class="fw-bold text-main-theme"></span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <label class="text-muted-theme small text-uppercase fw-bold mb-1 d-block">Est. Credit</label>
                                        <h5 id="viewRefund" class="fw-bold text-success mb-0">₹0.00</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted-theme small text-uppercase fw-bold mb-2 d-block">Management Chain</label>
                            <div class="d-flex flex-wrap gap-2" id="viewTracking"></div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted-theme small text-uppercase fw-bold mb-2 d-block">Reason for Return</label>
                            <div class="bg-light-theme p-3 rounded-4">
                                <p id="viewReason" class="text-main-theme mb-0 small" style="line-height: 1.6;"></p>
                            </div>
                        </div>

                        <div id="rejectionSection" class="mb-0 d-none">
                            <label class="text-danger small text-uppercase fw-bold mb-2 d-block">Rejection Feedback</label>
                            <div class="bg-soft-danger p-3 rounded-4">
                                <p id="viewRejectionReason" class="text-danger mb-0 small"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 pt-0">
                <div id="approvalActions" class="d-flex gap-2 w-100">
                    <button class="btn btn-outline-danger flex-grow-1 py-2 rounded-3 fw-bold" id="btnReject">Reject</button>
                    <button class="btn btn-primary flex-grow-1 py-2 rounded-3 fw-bold" id="btnApprove">Approve</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Return Modal (Search & Initiate) -->
<div class="modal fade" id="createReturnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">New Return Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                {{-- Step 1: Search Order --}}
                <div id="searchStep">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Enter Delivered Order Code</label>
                        <div class="input-group">
                            <input type="text" id="searchOrderCode" class="form-control form-control-lg" placeholder="e.g., ORD-12345678" style="border-radius: 12px 0 0 12px;">
                            <button class="btn btn-primary px-4" id="btnSearchOrder" style="border-radius: 0 12px 12px 0;">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>
                        <div class="form-text mt-2 small text-muted">Search for orders that have already been delivered.</div>
                    </div>

                    @if(isset($deliveredOrders) && count($deliveredOrders) > 0)
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase fw-bold mb-3 d-block">Quick Select (Recent Delivered Orders)</label>
                        <div class="row g-2">
                            @foreach($deliveredOrders as $order)
                            <div class="col-sm-6">
                                <div class="card border shadow-none rounded-3 cursor-pointer recent-order-card" 
                                     data-code="{{ $order->order_code }}" 
                                     style="transition: all 0.2s ease; cursor: pointer;">
                                    <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold text-primary small">{{ $order->order_code }}</div>
                                            <div class="text-muted" style="font-size: 0.65rem;">{{ $order->delivered_at ? $order->delivered_at->format('d M, Y') : ($order->updated_at ? $order->updated_at->format('d M, Y') : 'N/A') }}</div>
                                        </div>
                                        <i class="fa fa-chevron-right text-muted opacity-50 small"></i>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <div id="searchResults" class="d-none">
                        <h6 class="fw-bold mb-3">Order Items</h6>
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0" id="searchItemsTable">
                                <thead class="bg-light">
                                    <tr style="font-size: 0.75rem;">
                                        <th class="ps-3">Product</th>
                                        <th class="text-center">Ordered</th>
                                        <th class="text-center">Already Returned</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Items injected here --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Return Form (Hidden by default) --}}
                <div id="returnFormStep" class="d-none">
                    <button class="btn btn-link p-0 text-decoration-none mb-3" id="btnBackToSearch">
                        <i class="fa fa-arrow-left me-1"></i> Back to items
                    </button>
                    <form id="directReturnForm">
                        <input type="hidden" name="order_id" id="dr_order_id">
                        <input type="hidden" name="order_type" id="dr_order_type">
                        <input type="hidden" name="product_id" id="dr_product_id">
                        <input type="hidden" name="side" id="dr_side">
                        <input type="hidden" name="size" id="dr_size">

                        <div class="p-3 bg-light rounded-4 mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Product</label>
                                    <h6 class="fw-bold mb-0" id="dr_product_name_display"></h6>
                                    <div class="small text-muted" id="dr_variant_display"></div>
                                </div>
                                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Available to Return</label>
                                    <span class="badge bg-primary rounded-pill px-3" id="dr_max_qty_display"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Return Quantity</label>
                                <input type="number" name="quantity" id="dr_quantity" class="form-control" step="0.01" required min="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Reason for Return</label>
                                <select name="reason_preset" id="dr_reason_preset" class="form-select">
                                    <option value="">Select a reason...</option>
                                    <option value="Broken/Damaged">Broken/Damaged</option>
                                    <option value="Expired">Expired</option>
                                    <option value="Wrong Product">Wrong Product</option>
                                    <option value="Quality Issue">Quality Issue</option>
                                    <option value="other">Other (Specify below)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 d-none" id="otherReasonContainer">
                            <label class="form-label fw-bold">Specific Reason</label>
                            <textarea name="reason" id="dr_reason_text" class="form-control" rows="2" placeholder="Describe the issue..."></textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Upload Images (Proof)</label>
                            <input type="file" name="images[]" id="dr_images" class="form-control" accept="image/*" required multiple>
                            <div id="dr_image_preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-3" id="btnSubmitDirectReturn">
                                Submit Return Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let userRoles = {!! json_encode(auth()->user()->getRoleNames()) !!};
    
    let table = $('#returnsTable').DataTable({
        processing: true,
        ajax: "{{ route('admin.returns.index') }}",
        columns: [
            { 
                data: 'return_code',
                render: function(data, type, row) {
                    let date = row.created_at ? row.created_at.split('T')[0] : 'N/A';
                    return `<div>
                        <span class="fw-bold text-primary">${data}</span>
                        <div class="small text-muted" style="font-size: 0.65rem;">${date}</div>
                    </div>`;
                }
            },
            { 
                data: 'user',
                render: function(data, type, row) {
                    let typeBadge = row.order_type === 'retailer' ? 'bg-soft-success text-success' : 'bg-soft-info text-info';
                    return `<div class="d-flex align-items-center">
                        <div class="bg-soft-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                            <i class="fa fa-user text-primary" style="font-size: 0.8rem;"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-main-theme small">${data ? data.name : 'N/A'}</div>
                            <span class="badge ${typeBadge} border-0" style="font-size: 0.55rem; text-transform: uppercase; padding: 2px 4px;">${row.order_type}</span>
                        </div>
                    </div>`;
                }
            },
            {
                data: null,
                title: 'Responsible Staff',
                render: function(data, type, row) {
                    let html = '<div class="d-flex flex-column gap-1">';
                    if (row.field_staff) html += `<div class="small text-muted-theme" style="font-size: 0.7rem;"><i class="fa fa-user-tie me-1 text-success"></i>${row.field_staff.name}</div>`;
                    if (row.sales_manager) html += `<div class="small text-muted-theme" style="font-size: 0.7rem;"><i class="fa fa-user-shield me-1 text-info"></i>${row.sales_manager.name}</div>`;
                    if (row.distributor) html += `<div class="small text-muted-theme" style="font-size: 0.7rem;"><i class="fa fa-truck me-1 text-primary"></i>${row.distributor.name}</div>`;
                    if (html === '<div class="d-flex flex-column gap-1">') html += '<span class="text-muted small">-</span>';
                    html += '</div>';
                    return html;
                }
            },
            { 
                data: 'product_name',
                render: function(data, type, row) {
                    let variant = [row.side, row.size].filter(v => v).join(' / ');
                    return `<div>
                        <div class="fw-bold text-main-theme small" style="max-width: 150px; white-space: normal; line-height: 1.2;">${data}</div>
                        ${variant ? `<span class="badge bg-soft-info text-info border-0 px-2 mt-1" style="font-size: 0.6rem;">${variant}</span>` : ''}
                    </div>`;
                }
            },
            { 
                data: 'refund_amount',
                render: function(data, type, row) {
                    return `<div class="fw-bold text-success">₹${parseFloat(data || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
                    <div class="small text-muted" style="font-size: 0.65rem;">${row.quantity} ${row.unit}</div>`;
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    let badgeClass = 'bg-secondary';
                    let label = data.replace('_', ' ');
                    
                    if(data === 'pending') badgeClass = 'bg-warning';
                    else if(data.startsWith('approved')) badgeClass = 'bg-info';
                    else if(data === 'completed') badgeClass = 'bg-success';
                    else if(data === 'rejected') badgeClass = 'bg-danger';
                    
                    return `<span class="badge ${badgeClass} text-white text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">${label}</span>`;
                }
            },
            { 
                data: null,
                className: 'text-end pe-4',
                render: function(data, type, row) {
                    return `<button class="btn btn-light btn-sm rounded-3 view-btn shadow-sm" data-row='${JSON.stringify(row)}'>
                        <i class="fa fa-eye text-primary"></i>
                    </button>`;
                }
            }
        ],
        order: [[0, 'desc']],
        dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-3"<"d-flex align-items-center"l><"d-flex align-items-center"B><"d-flex align-items-center"f>>t<"d-flex justify-content-between align-items-center p-3"ip>',
        search: {
            search: "retailer" 
        },
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fa fa-file-excel me-1"></i> Excel',
                className: 'btn btn-success btn-sm rounded-3 shadow-sm border-0'
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf me-1"></i> PDF',
                className: 'btn btn-danger btn-sm rounded-3 shadow-sm border-0'
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print me-1"></i> Print',
                className: 'btn btn-dark btn-sm rounded-3 shadow-sm border-0'
            }
        ],
        language: {
            lengthMenu: "Show _MENU_ entries",
            search: "",
            searchPlaceholder: "Search returns...",
            emptyTable: `<div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No return requests found.</p>
            </div>`
        },
        drawCallback: function(settings) {
            let json = settings.json;
            if(json && json.data) {
                let pending = json.data.filter(r => r.status === 'pending').length;
                let approved = json.data.filter(r => r.status.startsWith('approved')).length;
                let completed = json.data.filter(r => r.status === 'completed').length;
                let totalRefund = json.data.filter(r => r.status === 'completed').reduce((sum, r) => sum + parseFloat(r.refund_amount || 0), 0);
                
                $('#count-pending').text(pending);
                $('#count-approved').text(approved);
                $('#count-completed').text(completed);
                $('#total-refunds').text('₹' + totalRefund.toLocaleString('en-IN', {minimumFractionDigits: 0}));
            }
        }
    });

    $('#returnTypeTabs .nav-link').click(function() {
        $('#returnTypeTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        
        let type = $(this).data('type');
        table.search(type === 'retailer' ? 'retailer' : 'distributor').draw();
    });

    $(document).on('click', '.view-btn', function() {
        let row = $(this).data('row');
        $('#viewReturnCodeHeader').text(row.return_code);
        $('#viewProductName').text(row.product_name);
        
        let statusBadge = '';
        switch(row.status) {
            case 'pending': statusBadge = '<span class="badge bg-soft-warning text-warning rounded-pill px-3 py-2">PENDING TIER 1</span>'; break;
            case 'approved_tier1': statusBadge = '<span class="badge bg-soft-primary text-primary rounded-pill px-3 py-2">PENDING FINAL</span>'; break;
            case 'completed': statusBadge = '<span class="badge bg-soft-success text-success rounded-pill px-3 py-2">COMPLETED</span>'; break;
            case 'rejected': statusBadge = '<span class="badge bg-soft-danger text-danger rounded-pill px-3 py-2">REJECTED</span>'; break;
        }
        $('#viewStatusBadge').html(statusBadge);
        
        let variant = [row.side, row.size].filter(v => v).join(' / ');
        $('#viewProductVariant').html(variant ? `<span class="badge bg-soft-info text-info border-0">${variant}</span>` : '');
        
        $('#viewQuantity').text(`${row.quantity} ${row.unit}`);
        $('#viewRefund').text(`₹${row.refund_amount || '0.00'}`);
        $('#viewReason').text(row.reason);

        let tracking = $('#viewTracking').empty();
        if (row.distributor) {
            tracking.append(`<span class="badge bg-soft-primary text-primary border-0 px-2 py-1" style="font-size: 0.7rem;"><i class="fa fa-truck me-1"></i>Distro: ${row.distributor.name}</span>`);
        }
        if (row.field_staff) {
            tracking.append(`<span class="badge bg-soft-success text-success border-0 px-2 py-1" style="font-size: 0.7rem;"><i class="fa fa-user-tie me-1"></i>Staff: ${row.field_staff.name}</span>`);
        }
        if (row.sales_manager) {
            tracking.append(`<span class="badge bg-soft-info text-info border-0 px-2 py-1" style="font-size: 0.7rem;"><i class="fa fa-user-shield me-1"></i>Manager: ${row.sales_manager.name}</span>`);
        }

        let gallery = $('#returnGallery');
        gallery.empty();
        
        let images = row.image_paths || (row.image_path ? [row.image_path] : []);
        
        if (images.length > 0) {
            $('#noImagePlaceholder').addClass('d-none');
            gallery.removeClass('d-none');
            
            images.forEach(path => {
                gallery.append(`
                    <div class="col-6 col-md-4">
                        <div class="gallery-item rounded-3 overflow-hidden border shadow-sm cursor-pointer" onclick="window.open('/storage/${path}', '_blank')">
                            <img src="/storage/${path}" class="img-fluid w-100 h-100 object-fit-cover" style="aspect-ratio: 1/1;" alt="Evidence">
                            <div class="gallery-overlay">
                                <i class="fa fa-search-plus text-white"></i>
                            </div>
                        </div>
                    </div>
                `);
            });
        } else {
            $('#noImagePlaceholder').removeClass('d-none');
            gallery.addClass('d-none');
        }

        if(row.status === 'rejected') {
            $('#rejectionSection').removeClass('d-none');
            $('#viewRejectionReason').text(row.rejection_reason || 'No reason provided.');
        } else {
            $('#rejectionSection').addClass('d-none');
        }

        let showApproval = false;
        
        if(row.order_type === 'retailer') {
            if(userRoles.includes('fieldstaff') && row.status === 'pending') showApproval = true;
            if(userRoles.includes('distributor') && row.status === 'approved_tier1') showApproval = true;
        } else {
            if(userRoles.includes('salesmanager') && row.status === 'pending') showApproval = true;
            if((userRoles.includes('admin') || userRoles.includes('superadmin')) && row.status === 'approved_tier1') showApproval = true;
        }

        if(showApproval) {
            $('#approvalActions').removeClass('d-none');
        } else {
            $('#approvalActions').addClass('d-none');
        }

        $('#btnApprove, #btnReject').data('id', row.id);

        $('#viewReturnModal').modal('show');
    });

    $('#btnApprove').click(function() {
        let id = $(this).data('id');
        let approveUrl = "{{ route('admin.returns.approve', ':id') }}".replace(':id', id);
        
        Swal.fire({
            title: 'Approve Return?',
            text: "This will move the request to the next stage or complete it.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Yes, Approve'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(approveUrl, { _token: '{{ csrf_token() }}' }, function(res) {
                    if(res.success) {
                        $('#viewReturnModal').modal('hide');
                        table.ajax.reload();
                        showToast('success', res.success);
                    } else {
                        showToast('error', res.error);
                    }
                });
            }
        });
    });

    $('#btnReject').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Reject Return?',
            input: 'textarea',
            inputLabel: 'Reason for rejection',
            inputPlaceholder: 'Enter reason here...',
            inputAttributes: {
                'aria-label': 'Type your reason here'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Reject'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                let rejectUrl = "{{ route('admin.returns.reject', ':id') }}".replace(':id', id);
                $.post(rejectUrl, { 
                    _token: '{{ csrf_token() }}',
                    reason: result.value
                }, function(res) {
                    if(res.success) {
                        $('#viewReturnModal').modal('hide');
                        table.ajax.reload();
                        showToast('success', res.success);
                    } else {
                        showToast('error', res.error);
                    }
                });
            }
        });
    });

    // --- Create Return Flow ---
    let currentSearchOrder = null;
    let currentSearchType = null;

    $(document).on('click', '.recent-order-card', function() {
        let code = $(this).data('code');
        $('#searchOrderCode').val(code);
        $('#btnSearchOrder').trigger('click');
    });

    $('#btnSearchOrder').click(function() {
        let code = $('#searchOrderCode').val();
        if(!code) return showToast('error', 'Please enter an order code');

        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.get("{{ route('admin.returns.search-order') }}", { code: code }, function(res) {
            $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Search');
            if(res.success) {
                currentSearchOrder = res.order;
                currentSearchType = res.type;
                renderSearchItems(res.order.items);
                $('#searchResults').removeClass('d-none');
            } else {
                showToast('error', res.message);
                $('#searchResults').addClass('d-none');
            }
        }).fail(function() {
            $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Search');
            showToast('error', 'An error occurred while searching');
        });
    });

    function renderSearchItems(items) {
        let tbody = $('#searchItemsTable tbody');
        tbody.empty();
        
        items.forEach(item => {
            let remaining = item.quantity - item.returned_qty - item.pending_return_qty;
            let statusHtml = '';
            
            if (item.returned_qty > 0) {
                statusHtml += `<div class="badge bg-soft-success text-success small mb-1">${item.returned_qty} Returned</div><br>`;
            }
            if (item.pending_return_qty > 0) {
                statusHtml += `<div class="badge bg-soft-warning text-warning small">${item.pending_return_qty} Pending</div>`;
            }
            if (statusHtml === '') statusHtml = '<span class="text-muted small">No returns</span>';

            let variant = [item.side, item.size].filter(Boolean).join(' / ');

            tbody.append(`
                <tr>
                    <td class="ps-3">
                        <div class="fw-bold">${item.product_name}</div>
                        ${variant ? `<div class="small text-muted">${variant}</div>` : ''}
                    </td>
                    <td class="text-center">${item.quantity} ${item.unit}</td>
                    <td class="text-center">${item.returned_qty} ${item.unit}</td>
                    <td class="text-center">${statusHtml}</td>
                    <td class="text-end pe-3">
                        ${(item.is_returnable && remaining > 0) ? `
                            <button class="btn btn-sm btn-outline-primary btn-init-dr" 
                                data-id="${item.product_id}" 
                                data-name="${item.product_name}"
                                data-side="${item.side || ''}"
                                data-size="${item.size || ''}"
                                data-unit="${item.unit}"
                                data-max="${remaining}">
                                Return
                            </button>
                        ` : `
                            <span class="text-muted small">${item.is_returnable ? 'Fully Returned' : 'Not Returnable'}</span>
                        `}
                    </td>
                </tr>
            `);
        });
    }

    $(document).on('click', '.btn-init-dr', function() {
        let data = $(this).data();
        
        $('#dr_order_id').val(currentSearchOrder.id);
        $('#dr_order_type').val(currentSearchType);
        $('#dr_product_id').val(data.id);
        $('#dr_side').val(data.side);
        $('#dr_size').val(data.size);
        
        $('#dr_product_name_display').text(data.name);
        $('#dr_variant_display').text([data.side, data.size].filter(Boolean).join(' / '));
        $('#dr_max_qty_display').text(`${data.max} ${data.unit} Available`);
        $('#dr_quantity').attr('max', data.max).val(data.max);
        
        $('#searchStep').addClass('d-none');
        $('#returnFormStep').removeClass('d-none');
    });

    $('#btnBackToSearch').click(function() {
        $('#returnFormStep').addClass('d-none');
        $('#searchStep').removeClass('d-none');
    });

    $('#dr_reason_preset').change(function() {
        if($(this).val() === 'other') {
            $('#otherReasonContainer').removeClass('d-none');
            $('#dr_reason_text').prop('required', true);
        } else {
            $('#otherReasonContainer').addClass('d-none');
            $('#dr_reason_text').prop('required', false);
        }
    });

    $('#dr_images').change(function() {
        let container = $('#dr_image_preview');
        container.empty();
        Array.from(this.files).forEach(file => {
            let reader = new FileReader();
            reader.onload = e => {
                container.append(`<img src="${e.target.result}" class="rounded-3 border" style="width: 60px; height: 60px; object-fit: cover;">`);
            };
            reader.readAsDataURL(file);
        });
    });

    $('#directReturnForm').submit(function(e) {
        e.preventDefault();
        
        let preset = $('#dr_reason_preset').val();
        let text = $('#dr_reason_text').val();
        let finalReason = preset === 'other' ? text : (preset + (text ? ': ' + text : ''));
        
        if(!finalReason || finalReason.length < 5) {
            return showToast('error', 'Please provide a reason (min 5 characters)');
        }

        let formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('reason', finalReason);

        let $btn = $('#btnSubmitDirectReturn');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');

        $.ajax({
            url: "{{ route('admin.returns.store') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if(res.success) {
                    $('#createReturnModal').modal('hide');
                    table.ajax.reload();
                    showToast('success', 'Return request submitted successfully');
                    
                    $('#searchOrderCode').val('');
                    $('#searchResults').addClass('d-none');
                    $('#returnFormStep').addClass('d-none');
                    $('#searchStep').removeClass('d-none');
                    $('#directReturnForm')[0].reset();
                    $('#dr_image_preview').empty();
                } else {
                    showToast('error', res.message);
                }
                $btn.prop('disabled', false).text('Submit Return Request');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Submit Return Request');
                let err = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'An error occurred';
                showToast('error', err);
            }
        });
    });

    // Deep Linking
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight');
    if (highlightId) {
        table.on('draw', function() {
            let rowData = table.rows().data().toArray().find(r => r.return_code === highlightId || r.id == highlightId);
            if (rowData) {
                let $btn = $(`.view-btn[data-id="${rowData.id}"]`).first();
                if ($btn.length) {
                    $btn.click();
                    let $tr = $btn.closest('tr');
                    $tr.addClass('highlight-pulse');
                    $('html, body').animate({ scrollTop: $tr.offset().top - 150 }, 500);
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }
            }
        });
    }
});
</script>

<style>
.bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
.bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
.bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
.text-main-theme { color: #2c3e50; }
.bg-card-theme { background-color: #fff; }
.text-muted-theme { color: #6c757d; }
.object-fit-cover { object-fit: cover; }

:root {
    --login-text: #1e293b;
    --login-muted: #64748b;
    --med-border: #e2e8f0;
    --bg-light: #f8fafc;
}

body.dark-only {
    --login-text: #f1f5f9;
    --login-muted: #94a3b8;
    --med-border: #334155;
    --bg-light: #0f172a;
    --bg-card: #1e293b;
    --text-main: #f8fafc;
}

.bg-card-theme { background-color: #ffffff; }
.bg-light-theme { background-color: #f8f9fa; }
.text-main-theme { color: #2d3748; }
.text-muted-theme { color: #718096; }
.border-light-theme { border-color: #edf2f7; }

body.dark-only .bg-card-theme { background-color: #1e293b !important; }
body.dark-only .bg-light-theme { background-color: #0f172a !important; }
body.dark-only .text-main-theme { color: #f8fafc !important; }
body.dark-only .text-muted-theme { color: #94a3b8 !important; }
body.dark-only .border-light-theme { border-color: #334155 !important; }

body.dark-only #returnsTable thead th {
    background-color: #0f172a;
    color: #cbd5e1;
    border-bottom-color: #334155;
}

.badge.bg-soft-primary { background-color: rgba(115, 102, 255, 0.1); color: #7366ff; }
.badge.bg-soft-success { background-color: rgba(81, 187, 37, 0.1); color: #51bb25; }
.badge.bg-soft-info { background-color: rgba(0, 157, 181, 0.1); color: #009db5; }
.badge.bg-soft-warning { background-color: rgba(248, 214, 43, 0.1); color: #f8d62b; }
.badge.bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }

@keyframes pulse-highlight {
    0% { background-color: rgba(115, 102, 255, 0); }
    50% { background-color: rgba(115, 102, 255, 0.15); }
    100% { background-color: rgba(115, 102, 255, 0); }
}
.highlight-pulse {
    animation: pulse-highlight 2s ease-in-out infinite;
    position: relative;
    z-index: 1;
}

#returnsTable thead th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding: 16px 12px;
    border-bottom: 2px solid #edf2f9;
}

.dt-buttons { display: flex; gap: 5px; }
.dt-buttons .btn {
    font-weight: 600;
    font-size: 0.75rem;
    padding: 6px 12px;
}

.dataTables_length label { 
    display: flex !important; 
    align-items: center !important; 
    gap: 8px; 
}

#returnTypeTabs .nav-link.active { 
    background-color: var(--med-primary, #0d6efd) !important; 
    color: #fff !important; 
}

.cursor-pointer { cursor: pointer; }
.recent-order-card:hover { border-color: #7366ff !important; background: rgba(115, 102, 255, 0.05); }
</style>
@endpush
