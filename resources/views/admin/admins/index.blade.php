
@extends('layouts.admin')

@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Admins</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                        Create Admin
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($admins as $admin)
                        <div class="col-md-4 mb-4">
                            <div class="card p-4">
                                <h5 class="card-title">{{ $admin->name }}</h5>
                                <p class="card-text"><strong>ID:</strong> {{ $admin->id }}</p>
                                <p class="card-text"><strong>Email:</strong> {{ $admin->email }}</p>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="button" class="btn btn-info btn-sm show-btn" 
                                            data-id="{{ $admin->id }}" 
                                            data-name="{{ $admin->name }}" 
                                            data-email="{{ $admin->email }}">
                                            Show
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm edit-btn" 
                                            data-id="{{ $admin->id }}" 
                                            data-name="{{ $admin->name }}" 
                                            data-email="{{ $admin->email }}">
                                            Edit
                                        </button>
                                    </div>
                                    <form action="{{ route('admins.destroy', $admin->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1" aria-labelledby="createAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAdminModalLabel">Create Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admins.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="password">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
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
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAdminModalLabel">Edit Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAdminForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="edit_name">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_email">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_password">Password (leave blank to keep current)</label>
                        <input type="password" name="password" id="edit_password" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_password_confirmation">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
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
<div class="modal fade" id="showAdminModal" tabindex="-1" aria-labelledby="showAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showAdminModalLabel">Admin Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="show_id"></span></p>
                <p><strong>Name:</strong> <span id="show_name"></span></p>
                <p><strong>Email:</strong> <span id="show_email"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var email = $(this).data('email');
            var url = "{{ route('admins.update', ':id') }}";
            url = url.replace(':id', id);

            $('#editAdminForm').attr('action', url);
            $('#edit_name').val(name);
            $('#edit_email').val(email);
            $('#editAdminModal').modal('show');
        });

        $('.show-btn').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var email = $(this).data('email');

            $('#show_id').text(id);
            $('#show_name').text(name);
            $('#show_email').text(email);
            $('#showAdminModal').modal('show');
        });
    });
</script>
@endpush
