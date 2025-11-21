@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Retailers</h5>
                    <a href="{{ route('retailers.create') }}" class="btn btn-primary">Add Retailer</a>
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
                                    <th>GST</th>
                                    <th>Distributor</th>
                                    <th>District</th>
                                    <th>Area</th>
                                    <th>Contact No</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($retailers as $retailer)
                                <tr>
                                    <td>{{ $retailer->user->name }}</td>
                                    <td>{{ $retailer->gst }}</td>
                                    <td>{{ $retailer->distributor->company_name ?? '-' }}</td>
                                    <td>{{ $retailer->user->district->name ?? '' }}</td>
                                    <td>{{ $retailer->user->area->name ?? '' }}</td>
                                    <td>{{ $retailer->user->contact_no }}</td>
                                    <td>
                                        @if($retailer->user->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('retailers.show', $retailer->id) }}" class="btn btn-sm btn-secondary me-1">
                                            <i class="fa fa-eye me-1"></i> View
                                        </a>
                                        <a href="{{ route('retailers.edit', $retailer->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('retailers.destroy', $retailer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                                        <p>No retailers found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $retailers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
