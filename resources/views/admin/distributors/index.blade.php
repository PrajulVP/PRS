@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 p-4">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Distributors</h5>
                    <a href="{{ route('admin.distributors.create') }}" class="btn btn-primary fw-bold">
                        <i class="fa fa-plus me-1"></i> Add Distributor
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
                                    <th>GST</th>
                                    <th>Drug License No</th>
                                    <th>Contact No</th>
                                    <th>Address</th>
                                    <th>Pincode</th>
                                    <th>District</th>
                                    <th>Area</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $distributor)
                                <tr>
                                    <td>{{ $distributor->name }}</td>
                                    <td>{{ $distributor->gst }}</td>
                                    <td>{{ $distributor->drug_license_no }}</td>
                                    <td>{{ $distributor->contact_no }}</td>
                                    <td>{{ $distributor->address }}</td>
                                    <td>{{ $distributor->pincode }}</td>
                                    <td>{{ $distributor->district->name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->area->name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.distributors.show', $distributor->id) }}" class="btn btn-sm btn-secondary me-1">
                                            <i class="fa fa-eye me-1"></i> View
                                        </a>
                                        <a href="{{ route('admin.distributors.edit', $distributor->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.distributors.destroy', $distributor->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fa fa-box-open fa-2x mb-3"></i>
                                        <p>No distributors found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $distributors->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
