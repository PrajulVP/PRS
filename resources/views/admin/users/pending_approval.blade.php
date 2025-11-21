
@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 p-4">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Users Pending Approval</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if($users->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-user-check fa-2x mb-3"></i>
                            <p>No users currently pending approval.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($user->role) }}</span></td>
                                        <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-success activate-user-btn" data-id="{{ $user->id }}">
                                                <i class="fa fa-check me-1"></i> Activate
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.activate-user-btn').forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.dataset.id;
                if (confirm('Are you sure you want to activate this user? This will allow them to log in.')) {
                    fetch(`{{ route('admin.users.activate', ['user' => '__userId__']) }}`.replace('__userId__', userId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.success);
                            location.reload(); // Reload page to update list
                        } else {
                            alert(data.error || 'Failed to activate user.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred during activation.');
                    });
                }
            });
        });
    });
</script>
@endpush
