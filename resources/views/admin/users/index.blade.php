@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>User Management</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal" id="btnCreate">Add
                    User</button>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul>
                </div> @endif

                <table class="table table-striped" id="users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
                        <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control"
                                required></div>
                        <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control"
                                required></div>
                        <div class="mb-3"><label>Confirm Password</label><input type="password" name="password_confirmation"
                                class="form-control" required></div>
                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" class="form-select role-select" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $r) <option value="{{ $r }}">{{ ucfirst($r) }}</option> @endforeach
                            </select>
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
                            <label>GST (Retailer)</label>
                            <input type="text" name="gst" class="form-control">
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
                        <div class="mb-3"><label>Name</label><input type="text" name="name" id="edit_name"
                                class="form-control" required></div>
                        <div class="mb-3"><label>Email</label><input type="email" name="email" id="edit_email"
                                class="form-control" required></div>
                        <div class="mb-3"><label>Password (blank to keep)</label><input type="password" name="password"
                                class="form-control"></div>
                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" id="edit_role" class="form-select role-select" required>
                                @foreach($roles as $r) <option value="{{ $r }}">{{ ucfirst($r) }}</option> @endforeach
                            </select>
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
                        style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
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
                                <div class="d-flex align-items-start gap-2 p-3 rounded" style="background:#f1f5f9;">
                                    <i class="fa fa-envelope mt-1 text-primary"></i>
                                    <div>
                                        <div class="text-muted small">Email</div>
                                        <div class="fw-semibold" id="view_email"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded" style="background:#f1f5f9;">
                                    <i class="fa fa-phone mt-1 text-success"></i>
                                    <div>
                                        <div class="text-muted small">Contact</div>
                                        <div class="fw-semibold" id="view_contact"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-2 p-3 rounded" style="background:#f1f5f9;">
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
                <div class="modal-footer border-0 bg-light">
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
                    "<'row mb-3 d-flex align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
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
                ajax: "{{ route('admin.users') }}",
                columns: [{
                    data: 'name'
                }, {
                    data: 'email'
                }, {
                    data: 'roles_display'
                },
                {
                    data: 'status',
                    render: d => `<span class="badge bg-${d === 'active' ? 'success' : 'secondary'}">${d}</span>`
                },
                {
                    data: null,
                    render: function (d, t, row) {
                        let json = JSON.stringify(row).replace(/"/g, '&quot;');
                        let deleteUrl = "{{ route('admin.users.destroy', ':id') }}".replace(':id', row.id);
                        let activateUrl = "{{ route('admin.users.activate', ':id') }}".replace(':id', row.id);

                        let btns = `<div class="d-flex gap-1 flex-wrap">`;
                        btns += `<button class="btn btn-info btn-sm view-btn" data-row="${json}"><i class="fa fa-eye"></i> View</button>`;
                        btns += `<button class="btn btn-primary btn-sm edit-btn" data-row="${json}">Edit</button>`;

                        if (row.status !== 'active') {
                            btns += `
                                    <form action="${activateUrl}" method="POST">
                                        @csrf
                                        <button class="btn btn-success btn-sm">Activate</button>
                                    </form>`;
                        }

                        // Delete Button
                        btns += `
                                    <button type="button" class="btn btn-danger btn-sm delete-btn" data-url="${deleteUrl}">Delete</button>
                                `;

                        return btns + `</div>`;
                    }
                }
                ]
            });

            // Handle Delete via AJAX
            $('#users-table').on('click', '.delete-btn', function () {
                let url = $(this).data('url');
                if (!confirm('Are you sure you want to delete this user?')) return;

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            table.ajax.reload(null, false);
                            alert(response.message);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function (xhr) {
                        alert('Error deleting user');
                    }
                });
            });

            $('.role-select').change(function () {
                let val = $(this).val();
                let form = $(this).closest('form');
                form.find('.field-distributor, .field-gst').addClass('d-none');
                if (val === 'retailer') {
                    form.find('.field-distributor, .field-gst').removeClass('d-none');
                } else if (val === 'fieldstaff') {
                    form.find('.field-distributor').removeClass('d-none');
                }
            });

            $(document).on('click', '.edit-btn', function () {
                let row = $(this).data('row');
                $('#edit_name').val(row.name);
                $('#edit_email').val(row.email);
                $('#edit_role').val(row.role);
                $('#editForm').attr('action', `/users/${row.id}`);
                $('#editUserModal').modal('show');
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
                    .attr('class', 'badge ' + (row.status === 'active' ? 'bg-success' : 'bg-secondary'))
                    .text(row.status ? row.status.charAt(0).toUpperCase() + row.status.slice(1) : '—');

                $('#viewUserModal').modal('show');
            });
        });
    </script>
@endpush