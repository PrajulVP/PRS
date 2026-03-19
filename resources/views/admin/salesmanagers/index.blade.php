@extends('layouts.admin')

@push('styles')
<style>
    .nav-tabs.custom-tabs {
        border-bottom: none;
        gap: 0.5rem;
        padding: 0.5rem;
        background: #f8fafc;
        border-radius: 12px;
        display: inline-flex;
    }
    .nav-tabs.custom-tabs .nav-link {
        border: 1px solid transparent !important;
        color: #64748b;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        border-radius: 8px !important;
        background: none;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .nav-tabs.custom-tabs .nav-link.active {
        color: #00497a !important;
        background: #ffffff !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    }
    .nav-tabs.custom-tabs .nav-link:hover:not(.active) {
        color: #475569;
        background: #f1f5f9;
        border-color: transparent;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
        white-space: nowrap;
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
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control"></textarea>
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
                                <input type="password" name="password" id="edit_password" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="edit_password_confirmation"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" id="edit_contact_no" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" id="edit_address" class="form-control"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
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
                        <div>
                            <h4 class="mb-1 fw-bold" id="sm_view_name"></h4>
                            <div class="mb-1 text-muted small"><i class="fa fa-user-tie me-1"></i>Sales Manager</div>
                            <span class="badge" id="sm_view_status"></span>
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
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-map-marker-alt mt-1 text-danger"></i>
                                    <div>
                                        <div class="text-muted small">Address</div>
                                        <div class="fw-semibold" id="sm_view_address"></div>
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
                    {
                        data: 'user.status', name: 'user.status',
                        render: (data, type, row) => `<span class="badge ${data === 'active' ? 'bg-success' : 'bg-danger'} status-toggle cursor-pointer" data-id="${row.id}" data-status="${data}">${data === 'active' ? 'Active' : 'Inactive'}</span>`
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
                dom: "<'row mb-3'<'col-sm-12'B>><'row mb-3'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end align-items-center'p>>",
                buttons: {
                    dom: { button: { className: 'btn btn-sm btn-icon' } },
                    buttons: [
                        { extend: 'copy', className: 'btn btn-secondary btn-sm', text: '<i class="fa fa-copy"></i> Copy' },
                        { extend: 'csv', className: 'btn btn-info btn-sm text-white', text: '<i class="fa fa-file-csv"></i> CSV' },
                        { extend: 'excel', className: 'btn btn-success btn-sm', text: '<i class="fa fa-file-excel"></i> Excel' },
                        { extend: 'pdf', className: 'btn btn-danger btn-sm', text: '<i class="fa fa-file-pdf"></i> PDF' },
                        { extend: 'print', className: 'btn btn-dark btn-sm', text: '<i class="fa fa-print"></i> Print' }
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
                        let sm = res.data;
                        $('#sm_view_name').text(sm.name);
                        $('#sm_view_email').text(sm.user?.email || 'N/A');
                        $('#sm_view_contact').text(sm.contact_no || 'N/A');
                        $('#sm_view_address').text(sm.address || 'N/A');
                        $('#sm_view_status').attr('class', 'badge ' + (sm.user?.status === 'active' ? 'bg-success' : 'bg-danger')).text(sm.user?.status);
                        
                        let fsHtml = sm.field_staffs?.map(fs => `<tr><td>${fs.user.name}</td><td>${fs.user.email}</td><td>${fs.contact_no || 'N/A'}</td><td><span class="badge ${fs.user.status === 'active' ? 'bg-success' : 'bg-danger'}">${fs.user.status}</span></td></tr>`).join('') || '<tr><td colspan="4">None</td></tr>';
                        $('#showFieldStaffBody').html(fsHtml);
                        $('#fieldStaffCount').text(sm.field_staffs?.length || 0);

                        let retHtml = sm.retailers?.map(ret => `<tr><td>${ret.shop_name}</td><td>${ret.user.name}</td><td>${ret.user.email}</td><td>${ret.contact_no || 'N/A'}</td><td><span class="badge ${ret.user.status === 'active' ? 'bg-success' : 'bg-danger'}">${ret.user.status}</span></td></tr>`).join('') || '<tr><td colspan="5">None</td></tr>';
                        $('#showRetailerBody').html(retHtml);
                        $('#retailerCount').text(sm.retailers?.length || 0);
                    }
                });
            });

            $('#createSalesManagerForm, #editSalesManagerForm').on('submit', function (e) {
                e.preventDefault();
                let form = $(this), btn = form.find('button[type="submit"]');
                btn.prop('disabled', true);
                $.ajax({
                    url: form.attr('action'), type: "POST", data: new FormData(this), processData: false, contentType: false,
                    success: (res) => {
                        $('.modal').modal('hide');
                        form[0].reset();
                        table.ajax.reload();
                        btn.prop('disabled', false);
                        showToast('success', 'Saved successfully');
                    },
                    error: () => { btn.prop('disabled', false); showToast('danger', 'Error'); }
                });
            });

            $('#sales-managers-table').on('click', '.delete-btn', function () {
                let url = $(this).data('url');
                Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true }).then((r) => {
                    if (r.isConfirmed) $.ajax({ url, type: 'DELETE', data: { _token: "{{ csrf_token() }}" }, success: () => table.ajax.reload(null, false) });
                });
            });

            $('#sales-managers-table').on('click', '.status-toggle', function () {
                let id = $(this).data('id'), status = $(this).data('status'), next = status === 'active' ? 'inactive' : 'active';
                let url = (next === 'active' ? "{{ route('admin.sales-managers.activate', ':id') }}" : "{{ route('admin.sales-managers.deactivate', ':id') }}").replace(':id', id);
                $.post(url, { _token: "{{ csrf_token() }}", _method: 'PATCH' }, () => table.ajax.reload(null, false));
            });

            $('#userStatusTabs button').on('click', () => setTimeout(() => table.ajax.reload(), 50));
        });
    </script>
@endpush