@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 p-4">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Managers</h5>
                    <a href="{{ route('admin.salesmanagers.create') }}" class="btn btn-primary fw-bold">
                        <i class="fa fa-plus me-1"></i> Add Sales Manager
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
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salesManagers as $salesManager)
                                <tr>
                                    <td>{{ $salesManager->name }}</td>
                                    <td>{{ $salesManager->email }}</td>
                                    <td>{{ $salesManager->contact_no ?? '-' }}</td>
                                    <td>
                                        @if($salesManager->user->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.salesmanagers.show', $salesManager->id) }}" class="btn btn-sm btn-secondary me-1">
                                            <i class="fa fa-eye me-1"></i> View
                                        </a>
                                        <a href="{{ route('admin.salesmanagers.edit', $salesManager->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.salesmanagers.destroy', $salesManager->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fa fa-user-tie fa-2x mb-3"></i>
                                        <p>No sales managers found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-3">
                        {{ $salesManagers->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection