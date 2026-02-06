@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>User Management</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal" id="btnCreate">Add User</button>
        </div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if($errors->any()) <div class="alert alert-danger">
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
                    <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="mb-3"><label>Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
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
                            @foreach($distributors as $d) <option value="{{ $d->id }}">{{ $d->user->name }}</option> @endforeach
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
                    <div class="mb-3"><label>Name</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
                    <div class="mb-3"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                    <div class="mb-3"><label>Password (blank to keep)</label><input type="password" name="password" class="form-control"></div>
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
    $(document).ready(function() {
        var table = $('#users-table').DataTable({
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
                    render: d => `<span class="badge bg-${d==='active'?'success':'secondary'}">${d}</span>`
                },
                {
                    data: null,
                    render: function(d, t, row) {
                        let json = JSON.stringify(row).replace(/"/g, '&quot;');
                        let deleteUrl = "{{ route('admin.users.destroy', ':id') }}".replace(':id', row.id);
                        let activateUrl = "{{ route('admin.users.activate', ':id') }}".replace(':id', row.id);

                        let btns = `<div class="d-flex gap-1">`;
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
        $('#users-table').on('click', '.delete-btn', function() {
            let url = $(this).data('url');
            if (!confirm('Are you sure you want to delete this user?')) return;

            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload(null, false);
                        alert(response.message);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error deleting user');
                }
            });
        });

        $('.role-select').change(function() {
            let val = $(this).val();
            let form = $(this).closest('form');
            form.find('.field-distributor, .field-gst').addClass('d-none');
            if (val === 'retailer') {
                form.find('.field-distributor, .field-gst').removeClass('d-none');
            } else if (val === 'fieldstaff') {
                form.find('.field-distributor').removeClass('d-none');
            }
        });

        $(document).on('click', '.edit-btn', function() {
            let row = $(this).data('row');
            $('#edit_name').val(row.name);
            $('#edit_email').val(row.email);
            $('#edit_role').val(row.role);
            $('#editForm').attr('action', `/users/${row.id}`);
            $('#editUserModal').modal('show');
        });
    });
</script>
@endpush