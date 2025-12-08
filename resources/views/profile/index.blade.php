@extends('layouts.admin')

@section('page-body')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <!-- Left Column: Avatar & Identity -->
                        <div class="col-md-4 text-center border-end">
                            <div class="mb-3 position-relative d-inline-block">
                                <img src="{{ $user->profile_pic ? asset('storage/' . $user->profile_pic) : asset('admin/assets/images/user/7.jpg') }}"
                                    alt="Profile Picture"
                                    class="rounded-circle shadow-sm"
                                    style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #f8f9fa;">
                            </div>
                            <h3 class="fw-bold text-dark mb-1">{{ $user->name }}</h3>
                            <span class="badge bg-light text-primary border border-primary-subtle px-3 py-2 rounded-pill mb-3">
                                {{ $user->getRoleNames()->first() ?? 'User' }}
                            </span>
                        </div>

                        <!-- Right Column: Details -->
                        <div class="col-md-8 ps-md-5">
                            <h6 class="text-secondary text-uppercase fw-bold mb-4 border-bottom pb-2">Account Details</h6>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Email Address</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-envelope text-primary me-2"></i>
                                        <span class="fs-6 fw-medium text-dark">{{ $user->email }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Phone Number</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-phone text-success me-2"></i>
                                        <span class="fs-6 fw-medium text-dark">{{ $user->phone_number ?? 'Not updated' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Location</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-map-marker text-danger me-2"></i>
                                        <span class="fs-6 fw-medium text-dark">{{ $user->city ?? 'Not updated' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Member Since</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-calendar text-info me-2"></i>
                                        <span class="fs-6 fw-medium text-dark">{{ $user->created_at->format('F d, Y') }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Address</label>
                                    <div class="d-flex align-items-center bg-light p-3 rounded">
                                        <i class="fa fa-home text-secondary me-3"></i>
                                        <span class="fs-6 text-dark">{{ $user->address ?? 'No detailed address provided.' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection