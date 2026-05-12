@extends('layouts.admin')

@push('styles')
<style>
    .nav-tabs.custom-tabs {
        border-bottom: none;
        gap: 0.5rem;
        padding: 0.5rem;
        background: var(--med-bg-body, #f8fafc);
        border-radius: 12px;
        display: inline-flex;
    }
    .nav-tabs.custom-tabs .nav-link {
        border: 1px solid transparent !important;
        color: var(--med-text-muted, #64748b);
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        border-radius: 8px !important;
        background: none;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .nav-tabs.custom-tabs .nav-link.active {
        color: var(--med-primary, #00497a) !important;
        background: var(--med-bg-card, #ffffff) !important;
        border-color: var(--med-border, #e2e8f0) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    }
    .nav-tabs.custom-tabs .nav-link:hover:not(.active) {
        color: var(--med-text-main, #475569);
        background: var(--med-bg-body);
        opacity: 0.8;
        border-color: transparent;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
        white-space: nowrap;
    }
    .modal-content {
        font-family: 'Montserrat', sans-serif !important;
    }
    .quick-card {
        background: var(--med-bg-body);
        border: 1px solid var(--med-border);
        border-radius: 16px;
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .quick-card:hover {
        border-color: var(--med-primary);
        box-shadow: var(--med-shadow-glow);
        transform: translateY(-2px);
    }
    .quick-card i {
        font-size: 1.1rem;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }
    .quick-card .label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--med-text-muted);
        margin-bottom: 2px;
    }
    .quick-card .value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--med-text-main);
        word-break: break-all;
    }
</style>
@endpush

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-3"><i class="fa fa-users me-2"></i>Sales Managers</h5>
                        <ul class="nav nav-tabs custom-tabs" id="userStatusTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-status="all" type="button">All Sales Managers</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-status="active" type="button">Active</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-status="inactive" type="button">Inactive</button>
                            </li>
                        </ul>
                    </div>
                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('sales_managers', 'add'))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createSalesManagerModal">
                            <i class="fa fa-plus me-1"></i>Add Sales Manager
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="table-responsive">
                            <table class="display table table-striped table-hover" id="sales-managers-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact No</th>
                                        <th>Address</th>
                                        <th>Pincode</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Sales Manager Modal -->
    <div class="modal fade" id="createSalesManagerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Sales Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createSalesManagerForm" action="{{ route('admin.sales-managers.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <div class="password-field-container">
                                    <input type="password" name="password" id="create_password" class="form-control" required>
                                    <span class="toggle-password"><i class="fa fa-eye"></i></span>
                                </div>
                                <div class="invalid-feedback d-block" id="create_password_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="password-field-container">
                                    <input type="password" name="password_confirmation" id="create_password_confirmation" class="form-control" required>
                                    <span class="toggle-password"><i class="fa fa-eye"></i></span>
                                </div>
                                <div class="invalid-feedback d-block" id="create_password_confirmation_error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editSalesManagerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sales Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editSalesManagerForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password (Leave blank to keep unchanged)</label>
                                <div class="password-field-container">
                                    <input type="password" name="password" id="edit_password" class="form-control">
                                    <span class="toggle-password"><i class="fa fa-eye"></i></span>
                                </div>
                                <div class="invalid-feedback d-block" id="edit_password_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="password-field-container">
                                    <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
                                    <span class="toggle-password"><i class="fa fa-eye"></i></span>
                                </div>
                                <div class="invalid-feedback d-block" id="edit_password_confirmation_error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" id="edit_contact_no" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" id="edit_pincode" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" id="edit_address" class="form-control" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select" {{ Auth::user()->hasRole('superadmin') ? '' : 'disabled' }}>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Show Modal -->
    <div class="modal fade" id="showSalesManagerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, #1e3a5f, #2e6da4); border-radius: 0.5rem 0.5rem 0 0;">
                    <h5 class="modal-title text-white" style="color: #fff !important;"><i class="fa fa-user-circle me-2"
                            style="color: #fff !important;"></i>Sales Manager Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Avatar + Name Header --}}
                    <div class="d-flex align-items-center gap-4 p-4"
                        style="background: var(--med-bg-body); border-bottom:1px solid var(--med-border);">
                        <div style="flex-shrink:0;">
                            <img id="sm_avatar_img" src="" alt="" class="rounded-circle shadow"
                                style="width:85px;height:85px;object-fit:cover;display:none;border:3px solid #fff;">
                            <div id="sm_avatar_initials"
                                style="width:85px;height:85px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                                font-size:1.9rem;font-weight:700;color:#fff;
                                                                background:linear-gradient(135deg,#1e3a5f,#2e6da4);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h4 class="mb-0 fw-bold" id="sm_view_name"></h4>
                                <span class="badge" id="sm_view_status"></span>
                            </div>
                            <div class="mb-1 text-muted small"><i class="fa fa-user-tie me-1"></i>Sales Manager</div>
                        </div>
                        <div class="text-end">
                            {{-- Badge moved to name part --}}
                        </div>
                    </div>

                    {{-- Basic Info Cards --}}
                    <div class="p-4 pb-0">
                        <h6 class="fw-bold mb-3"><i class="fa fa-info-circle me-2"></i>Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-envelope mt-1 text-primary"></i>
                                    <div>
                                        <div class="text-muted small">Email</div>
                                        <div class="fw-semibold" id="sm_view_email"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-phone mt-1 text-success"></i>
                                    <div>
                                        <div class="text-muted small">Contact</div>
                                        <div class="fw-semibold" id="sm_view_contact"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-map-marker-alt mt-1 text-danger"></i>
                                    <div>
                                        <div class="text-muted small">Address</div>
                                        <div class="fw-semibold" id="sm_view_address"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-hashtag mt-1 text-secondary"></i>
                                    <div>
                                        <div class="text-muted small">Pincode</div>
                                        <div class="fw-semibold" id="sm_view_pincode"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Assignments Section --}}
                    <div class="p-4 pt-4">
                        <ul class="nav nav-tabs" id="smModalTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="fieldstaff-tab" data-bs-toggle="tab"
                                    data-bs-target="#fieldstaff-panel" type="button" role="tab">
                                    <i class="fa fa-users me-1"></i>Field Staff (<span id="fieldStaffCount">0</span>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="retailer-tab" data-bs-toggle="tab"
                                    data-bs-target="#retailer-panel" type="button" role="tab">
                                    <i class="fa fa-store me-1"></i>Retailers (<span id="retailerCount">0</span>)
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content border border-top-0 p-3" id="smModalTabsContent"
                            style="border-radius: 0 0 0.5rem 0.5rem; background: var(--med-bg-body);">
                            <div class="tab-pane fade show active" id="fieldstaff-panel" role="tabpanel">
                                <div class="table-responsive" style="max-height: 300px;">
                                    <table class="table table-sm table-striped table-hover mb-0">
                                        <thead class="sticky-top" style="background: var(--med-bg-card);">
                                            <tr>
                                                <th style="color: var(--med-text-main) !important;">Name</th>
                                                <th style="color: var(--med-text-main) !important;">Email</th>
                                                <th style="color: var(--med-text-main) !important;">Contact</th>
                                                <th style="color: var(--med-text-main) !important;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="showFieldStaffBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="retailer-panel" role="tabpanel">
                                <div class="table-responsive" style="max-height: 300px;">
                                    <table class="table table-sm table-striped table-hover mb-0">
                                        <thead class="sticky-top" style="background: var(--med-bg-card);">
                                            <tr>
                                                <th style="color: var(--med-text-main) !important;">Shop Name</th>
                                                <th style="color: var(--med-text-main) !important;">Owner</th>
                                                <th style="color: var(--med-text-main) !important;">Email</th>
                                                <th style="color: var(--med-text-main) !important;">Contact</th>
                                                <th style="color: var(--med-text-main) !important;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="showRetailerBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0" style="background: var(--med-bg-body);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick View Modal --}}
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-card-theme border-0">
                    <h6 class="modal-title fw-bold"><i class="fa fa-eye me-2 text-primary"></i>Quick View</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="quickViewContent"></div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            const canActivate = @json(Auth::user()->hasRole('superadmin'));

            var table = $('#sales-managers-table').DataTable({
                processing: true, serverSide: true, order: [],
                ajax: {
                    url: "{{ route('admin.sales-managers.index') }}",
                    data: (d) => { d.status = $('#userStatusTabs button.active').data('status'); }
                },
                columns: [
                    { data: null, orderable: false, searchable: false, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
                    { data: 'name', name: 'name' },
                    { data: 'user.email', name: 'user.email' },
                    { data: 'contact_no', name: 'contact_no' },
                    { data: 'address', name: 'address' },
                    { data: 'pincode', name: 'pincode' },
                    {
                        data: 'user.status', name: 'user.status',
                        render: (data, type, row) => {
                            let canToggle = row.can_activate ? 'status-toggle cursor-pointer' : '';
                            return `<span class="status-badge ${data === 'active' ? 'status-badge-active' : 'status-badge-inactive'} ${canToggle}" data-id="${row.id}" data-status="${data}">${data === 'active' ? 'Active' : 'Inactive'}</span>`;
                        }
                    },
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (id, type, row) {
                            let rowData = JSON.stringify(row).replace(/"/g, '&quot;');
                            let deleteUrl = "{{ route('admin.sales-managers.destroy', ':id') }}".replace(':id', id);
                            let btns = `<div class="action-buttons">
                                <button type="button" class="btn btn-sm btn-info view-btn" data-row="${rowData}"><i class="fa fa-eye"></i></button>`;
                            if (row.can_edit) btns += `<button type="button" class="btn btn-sm btn-primary edit-btn" data-row="${rowData}"><i class="fa fa-edit"></i></button>`;
                            if (row.can_delete) btns += `<button type="button" class="btn btn-sm btn-danger delete-btn" data-url="${deleteUrl}"><i class="fa fa-trash"></i></button>`;
                            return btns + `</div>`;
                        }
                    }
                ],
                dom: "<'row mb-3'<'col-sm-12'B>><'row mb-3'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex justify-content-center justify-content-md-start align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end align-items-center'p>>",
                buttons: {
                    dom: { button: { className: 'btn btn-sm btn-icon' } },
                    buttons: [
                        { extend: 'copy', className: 'btn btn-secondary btn-sm', text: '<i class="fa fa-copy"></i> Copy' },
                        { extend: 'csv', className: 'btn btn-info btn-sm text-white', text: '<i class="fa fa-file-csv"></i> CSV' },
                        { extend: 'excel', className: 'btn btn-success btn-sm', text: '<i class="fa fa-file-excel"></i> Excel' },
                        { extend: 'pdf', className: 'btn btn-danger btn-sm', text: '<i class="fa fa-file-pdf"></i> PDF', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                        { extend: 'print', className: 'btn btn-dark btn-sm', text: '<i class="fa fa-print"></i> Print', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } }
                    ]
                }
            });

            // Handlers
            $('#sales-managers-table').on('click', '.edit-btn', function () {
                var data = $(this).data('row');
                $('#edit_name').val(data.name);
                $('#edit_email').val(data.user?.email || '');
                $('#edit_contact_no').val(data.contact_no);
                $('#edit_address').val(data.address);
                $('#edit_pincode').val(data.pincode);
                $('#edit_status').val(data.user?.status || '');
                $('#editSalesManagerForm').attr('action', "{{ route('admin.sales-managers.update', ':id') }}".replace(':id', data.id));
                $('#editSalesManagerModal').modal('show');
            });

            $('#sales-managers-table').on('click', '.view-btn', function () {
                var data = $(this).data('row');
                var url = "{{ route('admin.sales-managers.show', ':id') }}".replace(':id', data.id);
                $('#showSalesManagerModal').modal('show');
                $.get(url, (res) => {
                    if (res.success) {
                        let smData = res.data;
                        // Avatar logic
                        if (smData.user?.avatar) {
                            $('#sm_avatar_img').attr('src', smData.user.avatar).show();
                            $('#sm_avatar_initials').hide();
                        } else {
                            $('#sm_avatar_img').hide();
                            let initials = smData.user?.name ? smData.user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '??';
                            $('#sm_avatar_initials').text(initials).show();
                        }

                        $('#sm_view_name').text(smData.user?.name || 'N/A');
                        $('#sm_view_email').text(smData.user?.email || 'N/A');
                        $('#sm_view_contact').text(smData.contact_no || 'N/A');
                        $('#sm_view_address').text(smData.address || 'N/A');
                        $('#sm_view_pincode').text(smData.pincode || 'N/A');
                        $('#sm_view_status').attr('class', 'status-badge ' + (smData.user?.status === 'active' ? 'status-badge-active' : 'status-badge-inactive')).text(smData.user?.status);
                        
                        let fsHtml = smData.field_staffs?.map(fs => {
                            let fsData = JSON.stringify(fs).replace(/"/g, '&quot;');
                            let title = `Email: ${fs.user.email}&#10;Contact: ${fs.contact_no || 'N/A'}`;
                            return `<tr>
                                <td class="text-main-theme"><a href="javascript:void(0)" class="fw-bold text-primary quick-view-trigger" data-type="fieldstaff" data-item='${fsData}' title="${title}">${fs.user.name}</a></td>
                                <td class="text-main-theme">${fs.user.email}</td>
                                <td class="text-main-theme">${fs.contact_no || 'N/A'}</td>
                                <td><span class="status-badge ${fs.user.status === 'active' ? 'status-badge-active' : 'status-badge-inactive'}">${fs.user.status}</span></td>
                            </tr>`;
                        }).join('') || '<tr><td colspan="4">None</td></tr>';
                        $('#showFieldStaffBody').html(fsHtml);
                        $('#fieldStaffCount').text(smData.field_staffs?.length || 0);

                        let retHtml = smData.retailers?.map(ret => {
                            let retData = JSON.stringify(ret).replace(/"/g, '&quot;');
                            let title = `Email: ${ret.user.email}&#10;Contact: ${ret.contact_no || 'N/A'}&#10;Points: ${ret.loyalty_points}`;
                            return `<tr>
                                <td class="text-main-theme"><a href="javascript:void(0)" class="fw-bold text-primary quick-view-trigger" data-type="retailer" data-item='${retData}' title="${title}">${ret.shop_name}</a></td>
                                <td class="text-main-theme">${ret.user.name}</td>
                                <td class="text-main-theme">${ret.user.email}</td>
                                <td class="text-main-theme">${ret.contact_no || 'N/A'}</td>
                                <td><span class="status-badge ${ret.user.status === 'active' ? 'status-badge-active' : 'status-badge-inactive'}">${ret.user.status}</span></td>
                            </tr>`;
                        }).join('') || '<tr><td colspan="5">None</td></tr>';
                        $('#showRetailerBody').html(retHtml);
                        $('#retailerCount').text(smData.retailers?.length || 0);
                    }
                });
            });

            // Quick View logic
            $(document).on('click', '.quick-view-trigger', function() {
                let data = $(this).data('item'), type = $(this).data('type');
                let name = type === 'retailer' ? data.shop_name : data.user.name;
                let owner = type === 'retailer' ? `<div class="text-muted small">Owner: ${data.user.name}</div>` : '';
                let initials = (type === 'retailer' ? data.shop_name : data.user.name).split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                
                let avatarHtml = data.user?.avatar ? `<img src="${data.user.avatar}" class="rounded-circle shadow-sm" style="width:60px;height:60px;object-fit:cover;">` : 
                                 `<div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width:60px;height:60px;background:linear-gradient(135deg,#1e3a5f,#2e6da4);font-size:1.4rem;">${initials}</div>`;

                let bodyHtml = `
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            ${avatarHtml}
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                            <h5 class="fw-bold mb-0 text-main" style="font-family: 'Montserrat', sans-serif;">${name}</h5>
                            <span class="status-badge ${data.user?.status === 'active' ? 'status-badge-active' : 'status-badge-inactive'}" style="font-size: 0.65rem; padding: 2px 8px;">
                                ${data.user?.status}
                            </span>
                        </div>
                        ${owner}
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="quick-card">
                                <div class="label"><i class="fa fa-envelope bg-primary-light text-primary me-1"></i>Email Address</div>
                                <div class="value">${data.user?.email}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-card">
                                <div class="label"><i class="fa fa-phone bg-success-light text-success me-1"></i>Contact No</div>
                                <div class="value">${data.contact_no || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-card">
                                <div class="label"><i class="fa ${type==='retailer'?'fa-award':'fa-hashtag'} ${type==='retailer'?'bg-warning-light text-warning':'bg-info-light text-info'} me-1"></i>${type==='retailer'?'Loyalty Points':'Pincode'}</div>
                                <div class="value ${type==='retailer'?'text-warning':'text-main'}">${type==='retailer'?parseFloat(data.loyalty_points || 0).toFixed(2):data.pincode}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="quick-card">
                                <div class="label"><i class="fa ${type==='retailer'?'fa-map-marker-alt':'fa-home'} bg-secondary-light text-secondary me-1"></i>${type==='retailer'?'Business Address':'Address'}</div>
                                <div class="value">${data.address || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                `;
                $('#quickViewContent').html(bodyHtml);
                $('#quickViewModal').modal('show');
            });

             $('#createSalesManagerForm, #editSalesManagerForm').on('submit', function (e) {
                e.preventDefault();
                let form = $(this), btn = form.find('button[type="submit"]');

                // Clear previous errors
                form.find('.invalid-feedback').text('');
                form.find('.form-control').removeClass('is-invalid');

                btn.prop('disabled', true);
                $.ajax({
                    url: form.attr('action') || (form.attr('id') === 'createSalesManagerForm' ? "{{ route('admin.sales-managers.store') }}" : null),
                    type: "POST", data: new FormData(this), processData: false, contentType: false,
                    success: (res) => {
                        $('.modal').modal('hide');
                        form[0].reset();
                        table.ajax.reload();
                        btn.prop('disabled', false);
                        showToast('success', 'Saved successfully');
                    },
                    error: (xhr) => {
                        btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(key, messages) {
                                let input = form.find(`[name="${key}"]`);
                                input.addClass('is-invalid');
                                let errorDiv = form.find(`#${form.attr('id').startsWith('create') ? 'create' : 'edit'}_${key}_error`);
                                if (errorDiv.length === 0) {
                                    input.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                                } else {
                                    errorDiv.text(messages[0]);
                                }
                            });
                            showToast('danger', 'Please fix the errors below.');
                        } else {
                            let message = xhr.responseJSON?.message || 'An error occurred';
                            showToast('danger', message);
                        }
                    }
                });
            });

            // Live Validation & UI Logic

            // Name Validation (No numbers/symbols)
            $('input[name="name"]').on('input', function() {
                let val = $(this).val();
                let regex = /^[a-zA-Z\s]*$/;
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block name-error"></div>');
                    errorDiv = $(this).closest('div').find('.name-error');
                }
                
                if (!regex.test(val)) {
                    errorDiv.text('Name should only contain letters and spaces.');
                    $(this).addClass('is-invalid');
                } else {
                    errorDiv.text('');
                    $(this).removeClass('is-invalid');
                }
            });

            // Phone Validation (No 0 at start, max 10 digits)
            $('input[name="contact_no"]').on('input', function() {
                let val = $(this).val().replace(/\D/g, ''); // Remove non-digits
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block phone-error"></div>');
                    errorDiv = $(this).closest('div').find('.phone-error');
                }

                if (val.startsWith('0')) {
                    val = val.substring(1);
                    errorDiv.text('Phone number cannot start with 0.');
                } else if (val.length > 10) {
                    val = val.substring(0, 10);
                    errorDiv.text('Phone number cannot exceed 10 digits.');
                } else {
                    errorDiv.text('');
                }
                
                $(this).val(val);
                if (errorDiv.text() !== '') {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Contact 10 digits check on blur
            $('input[name="contact_no"]').on('blur', function() {
                let val = $(this).val();
                let errorDiv = $(this).closest('div').find('.phone-error');
                if (val.length > 0 && val.length < 10) {
                    errorDiv.text('The contact no should be of 10 digits.');
                    $(this).addClass('is-invalid');
                }
            });

            // Pincode Validation (6 digits)
            $('input[name="pincode"]').on('input', function() {
                let val = $(this).val().replace(/\D/g, '').substring(0, 6);
                $(this).val(val);
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block pin-error"></div>');
                    errorDiv = $(this).closest('div').find('.pin-error');
                }

                if (val.length > 0 && val.length < 6) {
                    errorDiv.text('Pincode must be exactly 6 digits.');
                    $(this).addClass('is-invalid');
                } else {
                    errorDiv.text('');
                    $(this).removeClass('is-invalid');
                }
            });

            $('#sales-managers-table').on('click', '.delete-btn', function () {
                let url = $(this).data('url');
                Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true }).then((r) => {
                    if (r.isConfirmed) $.ajax({ url, type: 'DELETE', data: { _token: "{{ csrf_token() }}" }, success: () => table.ajax.reload(null, false) });
                });
            });

            $('#sales-managers-table').on('click', '.status-toggle', function () {
                let id = $(this).data('id'), status = $(this).data('status'), next = status === 'active' ? 'inactive' : 'active';
                
                // Frontend Permission Check (Allow ONLY Superadmin)
                const isSuperAdmin = {{ Auth::user()->hasRole('superadmin') ? 'true' : 'false' }};
                if (!isSuperAdmin) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Superadmin Required',
                        text: 'Only a Superadmin can activate or deactivate sales managers.',
                        confirmButtonColor: '#00497a'
                    });
                    return;
                }

                Swal.fire({
                    title: `Change Status to ${next.toUpperCase()}?`,
                    text: `Are you sure you want to ${next} this sales manager?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#00497a',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, change it!'
                }).then((r) => {
                    if (r.isConfirmed) {
                        let url = (next === 'active' ? "{{ route('admin.sales-managers.activate', ':id') }}" : "{{ route('admin.sales-managers.deactivate', ':id') }}").replace(':id', id);
                        $.post(url, { _token: "{{ csrf_token() }}", _method: 'PATCH' }, () => {
                            table.ajax.reload(null, false);
                            showToast('success', 'Status updated successfully');
                        }).fail((xhr) => {
                            console.error('Status Toggle Error:', xhr);
                            let msg = 'Error changing user status';
                            let title = 'Action Failed';
                            if (xhr.status === 403) title = 'Permission Denied';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                try {
                                    let err = JSON.parse(xhr.responseText);
                                    if (err.message) msg = err.message;
                                } catch (e) {
                                    console.error('Error parsing responseText:', e);
                                }
                            }

                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: title,
                                    text: msg,
                                    confirmButtonColor: '#00497a'
                                });
                            } else {
                                alert(title + ': ' + msg);
                            }
                        });
                    }
                });
            });

            $('#userStatusTabs button').on('click', () => setTimeout(() => table.ajax.reload(), 50));
        });
    </script>
@endpush