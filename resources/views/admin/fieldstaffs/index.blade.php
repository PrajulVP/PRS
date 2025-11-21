@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Field Staff</h5>
                    <a href="{{ route('fieldstaffs.create') }}" class="btn btn-primary">Add Field Staff</a>
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
                                    <th>District</th>
                                    <th>Area</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fieldstaffs as $staff)
                                <tr>
                                    <td>{{ $staff->user->name }}</td>
                                    <td>{{ $staff->user->email }}</td>
                                    <td>{{ $staff->user->contact_no }}</td>
                                    <td>{{ $staff->distributor->company_name ?? '-' }}</td>
                                    <td>{{ $staff->user->district->name ?? '' }}</td>
                                    <td>{{ $staff->user->area->name ?? '' }}</td>
                                    <td>
                                        @if($staff->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('fieldstaffs.show', $staff->id) }}" class="btn btn-sm btn-secondary me-1">
                                            <i class="fa fa-eye me-1"></i> View
                                        </a>
                                        <a href="{{ route('fieldstaffs.edit', $staff->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('fieldstaffs.destroy', $staff->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <p>No field staff found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $fieldstaffs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection