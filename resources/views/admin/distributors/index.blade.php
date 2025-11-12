@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 p-4">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Distributors</h5>
                    <a href="{{ route('distributors.create') }}" class="btn btn-primary fw-bold">
                        <i class="fa fa-plus me-1"></i> Add Distributor
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($distributors->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-box-open fa-2x mb-3"></i>
                            <p>No distributors found.</p>
                        </div>
                    @else
                        <div class="row g-4">
                            @foreach($distributors as $distributor)
                            <div class="col-lg-6 col-md-12">
                                <div class="card border rounded-3 shadow-sm h-100">
                                    <div class="card-body">

                                        {{-- Header Section --}}
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="fw-bold mb-0">{{ $distributor->company_name }}</h5>
                                            <span class="badge bg-success">{{ $distributor->district->name ?? 'N/A' }}</span>
                                        </div>

                                        {{-- Info Grid --}}
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="mb-1"><strong>GST:</strong> {{ $distributor->gst }}</p>
                                                <p class="mb-1"><strong>Drug License Number:</strong> {{ $distributor->truck_license_number ?? '-' }}</p>
                                                <p class="mb-1"><strong>Area:</strong> {{ $distributor->area->name ?? '-' }}</p>
                                                <p class="mb-1"><strong>Route:</strong> {{ $distributor->route ?? '-' }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="mb-1"><strong>Contact:</strong> {{ $distributor->contact_no }}</p>
                                                <p class="mb-1"><strong>Email:</strong> {{ $distributor->user->email ?? '-' }}</p>
                                                <p class="mb-1"><strong>Pincode:</strong> {{ $distributor->pincode }}</p>
                                            </div>
                                        </div>

                                        {{-- Address Section --}}
                                        <div class="mt-2">
                                            <p class="mb-0"><strong>Address:</strong> {{ $distributor->address }}</p>
                                        </div>

                                    </div>

                                    {{-- Footer Buttons --}}
                                    <div class="card-footer bg-light border-0 d-flex justify-content-end gap-2">
                                        <a href="{{ route('distributors.edit', $distributor->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('distributors.destroy', $distributor->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
