@extends('layouts.admin')

@section('page-body')
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
    </style>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-card-theme border-bottom-0 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-3">User Management</h5>
                    <ul class="nav nav-tabs custom-tabs" id="userStatusTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-status="all" type="button">All Users</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-status="active" type="button">Active</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-status="inactive" type="button">Inactive</button>
                        </li>
                    </ul>
                </div>
                @if(Auth::user()->hasAnyRole(['admin', 'superadmin']) || 
                    Auth::user()->hasPermissionToCategory('sales_managers', 'add') || 
                    Auth::user()->hasPermissionToCategory('distributors', 'add') || 
                    Auth::user()->hasPermissionToCategory('field_staff', 'add') || 
                    Auth::user()->hasPermissionToCategory('retailers', 'add'))
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal" id="btnCreate">
                    <i class="fa fa-plus me-1"></i> Add User
                </button>
                @endif
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul>
                </div> @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="users-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Orders</th>
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
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Create User</h5><button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST" id="createForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required pattern="^[a-zA-Z\s]+$" title="Name should only contain letters and spaces.">
                            <span class="text-danger small error-text" id="error-name"></span>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Please enter a valid email address with a valid domain (e.g. .com, .in)">
                            <span class="text-danger small error-text" id="error-email"></span>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                            <span class="text-danger small error-text" id="error-password"></span>
                        </div>
                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" class="form-select role-select" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $r) <option value="{{ $r }}">{{ ucfirst($r) }}</option> @endforeach
                            </select>
                            <span class="text-danger small error-text" id="error-role"></span>
                        </div>

                        {{-- Conditional Fields --}}
                        <div class="mb-3 d-none field-distributor">
                            <label>Distributor (Required for Retailer/FieldStaff)</label>
                            <select name="distributor_id" class="form-select">
                                <option value="">Select Distributor</option>
                                @foreach($distributors as $d) <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 d-none field-gst">
                            <label>GST (Retailer/Distributor)</label>
                            <input type="text" name="gst" class="form-control" pattern="^[a-zA-Z0-9]+$" title="GST must only contain letters and numbers.">
                        </div>
                        <div class="mb-3 d-none field-drug-license">
                            <label>Drug License No (Retailer/Distributor)</label>
                            <input type="text" name="drug_license_no" class="form-control" pattern="^[a-zA-Z0-9\/\-]+$" title="Only letters, numbers, / and - are allowed.">
                        </div>
                        <div class="mb-3">
                            <label>Contact No</label>
                            <input type="text" name="contact_no" class="form-control" required>
                            <span class="text-danger small error-text" id="error-contact_no"></span>
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="2" required></textarea>
                            <span class="text-danger small error-text" id="error-address"></span>
                        </div>
                        <div class="mb-3">
                            <label>Pincode</label>
                            <input type="text" name="pincode" class="form-control" required>
                            <span class="text-danger small error-text" id="error-pincode"></span>
                        </div>
                        <div class="mb-3">
                            <label>Profile Picture</label>
                            <input type="file" name="profile_pic" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Create</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit User</h5><button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required pattern="^[a-zA-Z\s]+$" title="Name should only contain letters and spaces.">
                            <span class="text-danger small error-text" id="edit-error-name"></span>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Please enter a valid email address with a valid domain (e.g. .com, .in)">
                            <span class="text-danger small error-text" id="edit-error-email"></span>
                        </div>
                        <div class="mb-3">
                            <label>Password (blank to keep)</label>
                            <input type="password" name="password" class="form-control">
                            <span class="text-danger small error-text" id="edit-error-password"></span>
                        </div>
                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" id="edit_role" class="form-select role-select" required>
                                @foreach($roles as $r) <option value="{{ $r }}">{{ ucfirst($r) }}</option> @endforeach
                            </select>
                        </div>
                        <div class="mb-3 d-none field-gst">
                            <label>GST (Retailer/Distributor)</label>
                            <input type="text" name="gst" id="edit_gst" class="form-control" pattern="^[a-zA-Z0-9]+$" title="GST must only contain letters and numbers.">
                        </div>
                        <div class="mb-3 d-none field-drug-license">
                            <label>Drug License No (Retailer/Distributor)</label>
                            <input type="text" name="drug_license_no" id="edit_drug_license_no" class="form-control" pattern="^[a-zA-Z0-9\/\-]+$" title="Only letters, numbers, / and - are allowed.">
                        </div>
                        <div class="mb-3">
                            <label>Contact No</label>
                            <input type="text" name="contact_no" id="edit_contact_no" class="form-control" required>
                            <span class="text-danger small error-text" id="edit-error-contact_no"></span>
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2" required></textarea>
                            <span class="text-danger small error-text" id="edit-error-address"></span>
                        </div>
                        <div class="mb-3">
                            <label>Pincode</label>
                            <input type="text" name="pincode" id="edit_pincode" class="form-control" required>
                            <span class="text-danger small error-text" id="edit-error-pincode"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-2">Profile Picture</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="position-relative">
                                    <img id="edit_avatar_preview" src="" alt="" class="rounded-circle shadow-sm border border-2 border-white" style="width:60px;height:60px;object-fit:cover;display:none;">
                                    <div id="edit_avatar_initials_preview" class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width:60px;height:60px;display:none;background:#374151; font-size: 1.2rem;"></div>
                                    <button type="button" id="btn_remove_edit_pic" class="position-absolute top-0 end-0 bg-white text-danger shadow-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width:22px;height:22px; border: 1px solid #dee2e6; transform: translate(5px, -5px); display:none;" onclick="removeEditUserPic()">
                                        <i class="fa fa-times" style="font-size: 11px;"></i>
                                    </button>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="profile_pic" class="form-control form-control-sm" accept="image/*" onchange="previewEditUserPic(this)">
                                    <input type="hidden" name="remove_profile_pic" id="remove_edit_profile_pic" value="0">
                                    <small class="text-muted" style="font-size: 0.65rem;">Recommended: Square image, max 5MB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- View User Modal --}}
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, var(--theme-primary, #1e3a5f), #2e6da4); border-radius: 0.5rem 0.5rem 0 0;">
                    <h5 class="modal-title text-white"><i class="fa fa-user-circle me-2"></i>User Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Profile Header --}}
                    <div class="d-flex align-items-center gap-4 p-4"
                        style="background: var(--med-bg-body); border-bottom: 1px solid var(--med-border);">
                        <div id="view_avatar_wrap" style="flex-shrink:0;">
                            <img id="view_avatar_img" src="" alt="" class="rounded-circle shadow"
                                style="width:90px;height:90px;object-fit:cover;display:none;border:3px solid #fff;">
                            <div id="view_avatar_initials"
                                style="width:90px;height:90px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                                            font-size:2rem;font-weight:700;color:#fff;
                                            background:linear-gradient(135deg,#11998e,#38ef7d);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            </div>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold" id="view_name"></h4>
                            <div class="mb-1"><span class="badge fs-6" id="view_role_badge"></span></div>
                            <span class="badge" id="view_status_badge"></span>
                        </div>
                    </div>
                    {{-- Info Grid --}}
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded" style="background: var(--med-bg-body);">
                                    <i class="fa fa-envelope mt-1 text-primary"></i>
                                    <div>
                                        <div class="text-muted small">Email</div>
                                        <div class="fw-semibold" id="view_email"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded" style="background: var(--med-bg-body);">
                                    <i class="fa fa-phone mt-1 text-success"></i>
                                    <div>
                                        <div class="text-muted small">Contact</div>
                                        <div class="fw-semibold" id="view_contact"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-2 p-3 rounded" style="background: var(--med-bg-body);">
                                    <i class="fa fa-map-marker-alt mt-1 text-danger"></i>
                                    <div>
                                        <div class="text-muted small">Address</div>
                                        <div class="fw-semibold" id="view_address"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-body-theme">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#users-table').DataTable({
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-3'<'col-md-6'l><'col-md-6'f>>" +
                    "<'row '<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-sm-12 col-md-5 d-flex justify-content-center justify-content-md-start align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end align-items-center'p>>",
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
                ajax: {
                    url: "{{ route('admin.users') }}",
                    data: function(d) {
                        d.status = $('#userStatusTabs button.active').data('status');
                    }
                },
                columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'name'
                }, {
                    data: 'email'
                }, {
                    data: 'roles_display'
                },
                {
                    data: 'status',
                    render: d => `<span class="status-badge ${d === 'active' ? 'status-badge-active' : 'status-badge-inactive'}">${d}</span>`
                },
                {
                    data: 'order_count',
                    render: function(d, t, row) {
                        if (row.order_link && row.order_link !== '#') {
                            return `<a href="${row.order_link}" class="btn btn-light btn-sm"><i class="fa fa-shopping-cart text-primary me-1"></i>${d} Orders</a>`;
                        }
                        return `—`;
                    }
                },
                {
                    data: null,
                    render: function (d, t, row) {
                        let json = JSON.stringify(row).replace(/"/g, '&quot;');
                        let deleteUrl = "{{ route('admin.users.destroy', ':id') }}".replace(':id', row.id);
                        let activateUrl = "{{ route('admin.users.activate', ':id') }}".replace(':id', row.id);

                        let btns = `<div class="d-flex gap-1 flex-wrap">`;
                        btns += `<button class="btn btn-info btn-sm view-btn" data-row="${json}"><i class="fa fa-eye"></i> View</button>`;
                        
                        // Permission-based Edit
                        if (row.can_edit) {
                            btns += `<button class="btn btn-primary btn-sm edit-btn" data-row="${json}">Edit</button>`;
                        }

                        if (row.can_activate) {
                            if (row.status !== 'active') {
                                btns += `<button class="btn btn-success btn-sm status-toggle-btn" data-url="${activateUrl}" data-action="activate">Activate</button>`;
                            } else {
                                let deactivateUrl = "{{ route('admin.users.deactivate', ':id') }}".replace(':id', row.id);
                                btns += `<button class="btn btn-warning btn-sm status-toggle-btn" data-url="${deactivateUrl}" data-action="deactivate">Deactivate</button>`;
                            }
                        }

                        // Permission-based Delete
                        if (row.can_delete) {
                            btns += `
                                    <button type="button" class="btn btn-danger btn-sm delete-btn" data-url="${deleteUrl}">Delete</button>
                                `;
                        }

                        return btns + `</div>`;
                    }
                }
                ]
            });

            // Tab Click Handler
            $('#userStatusTabs button').on('click', function() {
                // The delay is needed to let Bootstrap set the 'active' class
                setTimeout(() => {
                    table.ajax.reload();
                }, 50);
            });

            // Handle Delete via AJAX
            $('#users-table').on('click', '.delete-btn', function () {
                let url = $(this).data('url');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This user will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#00497a',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.success) {
                                    table.ajax.reload(null, false);
                                    if (window.updateSidebarCounts) window.updateSidebarCounts();
                                    showToast('success', response.message || 'User deleted');
                                } else {
                                    showToast('error', response.message || 'Failed to delete');
                                }
                            },
                            error: function (xhr) {
                                let err = 'Error deleting user';
                                if (xhr.responseJSON) {
                                    err = xhr.responseJSON.message || xhr.responseJSON.error || err;
                                }
                                showToast('error', err);
                            }
                        });
                    }
                });
            });

            // Handle Status Toggle via AJAX
            $('#users-table').on('click', '.status-toggle-btn', function () {
                let url = $(this).data('url');
                let action = $(this).data('action');
                let btn = $(this);
                let oldText = btn.text();

                btn.prop('disabled', true).text('Working...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            table.ajax.reload(null, false);
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                            if (typeof showToast === 'function') {
                                showToast('success', response.message);
                            } else {
                                Swal.fire('Success', response.message, 'success');
                            }
                        } else {
                            Swal.fire('Error', response.message || 'Error occurred', 'error');
                        }
                    },
                    error: function (xhr) {
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
                    },
                    complete: function () {
                        btn.prop('disabled', false).text(oldText);
                    }
                });
            });

            $('.role-select').change(function () {
                let val = $(this).val();
                let form = $(this).closest('form');
                form.find('.field-distributor, .field-gst, .field-drug-license').addClass('d-none');
                form.find('.field-gst input, .field-drug-license input').prop('required', false);

                if (val === 'retailer') {
                    form.find('.field-distributor, .field-gst, .field-drug-license').removeClass('d-none');
                    form.find('.field-gst input, .field-drug-license input').prop('required', true);
                } else if (val === 'distributor') {
                    form.find('.field-gst, .field-drug-license').removeClass('d-none');
                    form.find('.field-gst input, .field-drug-license input').prop('required', true);
                } else if (val === 'fieldstaff') {
                    form.find('.field-distributor').removeClass('d-none');
                }
            });

            $(document).on('click', '.edit-btn', function () {
                let row = $(this).data('row');
                $('#edit_name').val(row.name);
                $('#edit_email').val(row.email);
                $('#edit_contact_no').val(row.contact_no || '');
                $('#edit_address').val(row.address || '');
                $('#edit_pincode').val(row.pincode || '');
                $('#edit_role').val(row.role);
                // Trigger change to update visibility of conditional fields
                $('#edit_role').trigger('change');

                // Reset removal flag
                $('#remove_edit_profile_pic').val('0');
                
                // Update Avatar Preview
                if (row.profile_image_url) {
                    $('#edit_avatar_preview').attr('src', row.profile_image_url).show();
                    $('#edit_avatar_initials_preview').hide();
                    $('#btn_remove_edit_pic').show();
                } else {
                    let initials = row.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
                    $('#edit_avatar_initials_preview').text(initials).show();
                    $('#edit_avatar_preview').hide();
                    $('#btn_remove_edit_pic').hide();
                }

                // GST and Drug License for Edit Modal
                $('#edit_gst').val(row.gst || '');
                $('#edit_drug_license_no').val(row.drug_license_no || '');

                $('#editForm').attr('action', `/users/${row.id}`);
                $('#editUserModal').modal('show');
            });

            $('#createForm, #editForm').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                let oldText = btn.text();
                
                // Clear previous errors
                form.find('.error-text').text('');
                
                btn.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: form.attr('action'),
                    type: "POST",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: (res) => {
                        $('.modal').modal('hide');
                        form[0].reset();
                        table.ajax.reload();
                        btn.prop('disabled', false).text(oldText);
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
                        showToast('success', 'Saved successfully');
                    },
                    error: (xhr) => {
                        btn.prop('disabled', false).text(oldText);
                        let isEdit = form.attr('id') === 'editForm';
                        let prefix = isEdit ? 'edit-error-' : 'error-';

                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(key => {
                                let errorSpan = form.find(`#${prefix}${key}`);
                                if (errorSpan.length) {
                                    errorSpan.text(errors[key][0]);
                                }
                            });
                        } else {
                            let message = 'Error';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            showToast('danger', message);
                        }
                    }
                });
            });

            // View Modal handler
            const roleBadgeColors = {
                superadmin: 'bg-dark', admin: 'bg-danger', salesmanager: 'bg-warning text-dark',
                distributor: 'bg-primary', fieldstaff: 'bg-info text-dark', retailer: 'bg-success'
            };
            $(document).on('click', '.view-btn', function () {
                let row = $(this).data('row');

                // Avatar: show image if available, else initials
                if (row.profile_image_url) {
                    $('#view_avatar_img').attr('src', row.profile_image_url).show();
                    $('#view_avatar_initials').hide();
                } else {
                    let initials = row.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
                    $('#view_avatar_initials').text(initials).show();
                    $('#view_avatar_img').hide();
                }

                $('#view_name').text(row.name);
                $('#view_email').text(row.email);
                $('#view_contact').text(row.contact_no || '—');
                $('#view_address').text(row.address || '—');

                // Role badge
                let roleKey = (row.role || '').toLowerCase();
                let roleClass = roleBadgeColors[roleKey] || 'bg-secondary';
                $('#view_role_badge').attr('class', 'badge fs-6 ' + roleClass).text(row.roles_display || row.role || '—');

                // Status badge
                $('#view_status_badge')
                    .attr('class', 'status-badge ' + (row.status === 'active' ? 'status-badge-active' : 'status-badge-inactive'))
                    .text(row.status || '—');

                $('#viewUserModal').modal('show');
            });
        });

        function previewEditUserPic(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#edit_avatar_preview').attr('src', e.target.result).show();
                    $('#edit_avatar_initials_preview').hide();
                    $('#btn_remove_edit_pic').show();
                    $('#remove_edit_profile_pic').val('0');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeEditUserPic() {
            const hasNoImage = $('#edit_avatar_preview').is(':hidden') && $('#editUserModal input[type="file"]').val() === '';

            if (hasNoImage) {
                alert('No profile picture to remove.');
                return;
            }

            if (confirm('Are you sure you want to remove this user\'s profile picture?')) {
                $('#remove_edit_profile_pic').val('1');
                $('#edit_avatar_preview').hide();
                // Show initials as fallback
                let name = $('#edit_name').val();
                let initials = name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
                $('#edit_avatar_initials_preview').text(initials).show();
                $('#btn_remove_edit_pic').hide();
                // Clear file input
                $('#editUserModal input[type="file"]').val('');
            }
        }
    </script>
@endpush