@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 p-4">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Managers</h5>
                    <a href="{{ route('managers.create') }}" class="btn btn-primary fw-bold">
                        <i class="fa fa-plus me-1"></i> Add Manager
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact No</th>
                                    <th>Distributor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($managers as $manager)
                                <tr>
                                    <td>{{ $manager->name }}</td>
                                    <td>{{ $manager->email }}</td>
                                    <td>{{ $manager->contact_no ?? '-' }}</td>
                                    <td>{{ $manager->distributor->user->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($manager->user->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('managers.show', $manager->id) }}" class="btn btn-sm btn-secondary me-1">
                                            <i class="fa fa-eye me-1"></i> View
                                        </a>
                                        <a href="{{ route('managers.edit', $manager->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('managers.destroy', $manager->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fa fa-user-tie fa-2x mb-3"></i>
                                        <p>No managers found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-3">
                        {{ $managers->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection