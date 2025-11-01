@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>User Management</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">User Management</li>
                    <li class="breadcrumb-item active">All Users</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <ul class="nav nav-tabs" id="userRolesTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="unique-roles-tab" data-bs-toggle="tab" data-bs-target="#unique-roles" type="button" role="tab" aria-controls="unique-roles" aria-selected="true">Unique Roles</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="other-roles-tab" data-bs-toggle="tab" data-bs-target="#other-roles" type="button" role="tab" aria-controls="other-roles" aria-selected="false">Other Roles</button>
        </li>
    </ul>
    <div class="tab-content" id="userRolesTabContent">
        <div class="tab-pane fade show active" id="unique-roles" role="tabpanel" aria-labelledby="unique-roles-tab">
            <div class="row mt-3">
                @foreach($users['superadmin'] ?? [] as $user)
                <div class="col-md-4 mb-4">
                    <div class="card p-4">
                        <h5 class="card-title">{{ $user->name }} (Superadmin)</h5>
                        <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    </div>
                </div>
                @endforeach
                @foreach($users['admin'] ?? [] as $user)
                <div class="col-md-4 mb-4">
                    <div class="card p-4">
                        <h5 class="card-title">{{ $user->name }} (Admin)</h5>
                        <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    </div>
                </div>
                @endforeach
                @foreach($users['manager'] ?? [] as $user)
                <div class="col-md-4 mb-4">
                    <div class="card p-4">
                        <h5 class="card-title">{{ $user->name }} (Manager)</h5>
                        <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="other-roles" role="tabpanel" aria-labelledby="other-roles-tab">
            <div class="row mt-3">
                <div class="col-md-4">
                    <select class="form-select" id="role-filter">
                        <option value="">Select Role</option>
                        <option value="distributor">Distributors</option>
                        <option value="fieldstaff">Field Staff</option>
                        <option value="retailer">Retailers</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleFilter = document.getElementById('role-filter');
    const usersTableBody = document.getElementById('users-table-body');

    roleFilter.addEventListener('change', function () {
        const selectedRole = this.value;
        usersTableBody.innerHTML = '';

        if (selectedRole) {
            const url = `/admin/users/get-by-role?role=${selectedRole}`;
            console.log("Fetching:", url);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(users => {
                console.log("✅ Users response:", users);
                console.log("Type of response:", Array.isArray(users) ? "Array" : typeof users);
                console.log("Number of users:", users.length ?? 0);

                // Check if array and has data
                if (!Array.isArray(users) || users.length === 0) {
                    console.warn("⚠️ No users found for this role.");
                    usersTableBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No users found for this role.
                            </td>
                        </tr>`;
                    return;
                }

                users.forEach(user => {
                    const row = `
                        <tr>
                            <td>${user.name ?? '—'}</td>
                            <td>${user.email ?? '—'}</td>
                            <td>
                                <a href="/admin/users/${user.id}/edit" class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>`;
                    usersTableBody.insertAdjacentHTML('beforeend', row);
                });
            })
            .catch(error => {
                console.error('❌ Error fetching users:', error);
                usersTableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-danger text-center">
                            Failed to load users.
                        </td>
                    </tr>`;
            });
        }
    });
});
</script>
@endpush

