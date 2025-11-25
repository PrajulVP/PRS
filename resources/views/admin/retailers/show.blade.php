@extends('layouts.admin')

@section('page-body')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Retailer Details</h5>
                </div>

                <div class="card-body">

                    <div class="row g-4">

                        <!-- DETAILS -->
                        <div class="col-md-12">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Name</p>
                                    <p class="fw-semibold">{{ $retailer->user->name }}</p>
                                    <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Email</p>
                                    <p class="fw-semibold">{{ $retailer->user->email }}</p>
                                    <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Contact No</p>
                                    <p class="fw-semibold">{{ $retailer->contact_no ?? 'N/A' }}</p>
                                    <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">GST</p>
                                    <p class="fw-semibold">{{ $retailer->gst }}</p>
                                    <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Address</p>
                                    <p class="fw-semibold">{{ $retailer->address ?? 'N/A' }}</p>
                                    <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Pincode</p>
                                    <p class="fw-semibold">{{ $retailer->pincode ?? 'N/A' }}</p>
                                    <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Distributor</p>
                                    <p class="fw-semibold">{{ $retailer->distributor->name ?? 'N/A' }}</p>
                                    <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Sales Manager</p>
                                    <p class="fw-semibold">{{ $retailer->salesManager->name ?? 'N/A' }}</p>
                                    <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Field Staff</p>
                                    <p class="fw-semibold">{{ $retailer->fieldStaff->user->name ?? 'N/A' }}</p>
                                    <hr class="my-1">
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Status</p>
                                    <span class="badge {{ $retailer->user->status === 'active' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($retailer->user->status) }}
                                    </span>
                                    <hr class="my-1">
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('retailers.edit', $retailer->id) }}" class="btn btn-primary">Edit</a>
                        <a href="{{ url()->previous() }}" class="btn btn-primary text-white">Back</a>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
@endsection
