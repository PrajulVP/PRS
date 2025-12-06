@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title row">
        <div class="col-6">
            <h3>My Profile</h3>
        </div>
        <div class="col-6">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                        </svg></a></li>
                <li class="breadcrumb-item">Users</li>
                <li class="breadcrumb-item active">My Profile</li>
            </ol>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="user-profile">
        <div class="row justify-content-center">
            <!-- User Profile Card -->
            <div class="col-xl-6 col-lg-8 col-md-10">
                <div class="card hovercard text-center profile-card">
                    <div class="cardheader" style="background-image: url('{{ asset('admin/assets/images/other-images/bg-profile.png') }}'); background-size: cover; height: 155px;"></div>
                    <div class="user-image">
                        <div class="avatar">
                            <img alt="" src="{{ $user->profile_pic ? asset('storage/' . $user->profile_pic) : asset('admin/assets/images/user/7.jpg') }}" class="img-fluid rounded-circle shadow-lg border border-3 border-white">
                        </div>
                    </div>
                    <div class="info mt-3 p-4">
                        <div class="row">
                            <div class="col-12">
                                <div class="user-designation mb-3">
                                    <h3 class="fw-bold text-dark">{{ $user->name }}</h3>
                                    <h5 class="text-primary text-uppercase f-12 fw-bold tracking-wide mt-1">{{ $user->getRoleNames()->first() ?? 'User' }}</h5>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <p class="mb-1 text-muted small text-uppercase fw-bold">Email Address</p>
                                <h6 class="text-dark"><i class="fa fa-envelope me-2 text-primary"></i>{{ $user->email }}</h6>
                            </div>
                            <div class="col-md-6 text-start">
                                <p class="mb-1 text-muted small text-uppercase fw-bold">Joined On</p>
                                <h6 class="text-dark"><i class="fa fa-calendar me-2 text-success"></i>{{ $user->created_at->format('d M Y') }}</h6>
                            </div>
                            <div class="col-md-6 text-start mt-4">
                                <p class="mb-1 text-muted small text-uppercase fw-bold">Contact Number</p>
                                <h6 class="text-dark"><i class="fa fa-phone me-2 text-info"></i>{{ $user->phone_number ?? 'Not Available' }}</h6>
                            </div>
                            <div class="col-md-6 text-start mt-4">
                                <p class="mb-1 text-muted small text-uppercase fw-bold">Location</p>
                                <h6 class="text-dark"><i class="fa fa-map-marker me-2 text-danger"></i>{{ $user->city ?? 'Not Available' }}</h6>
                            </div>
                            <div class="col-md-12 text-start mt-4">
                                <p class="mb-1 text-muted small text-uppercase fw-bold">Detailed Address</p>
                                <h6 class="text-dark mb-0"><i class="fa fa-home me-2 text-warning"></i>{{ $user->address ?? 'Address not updated' }}</h6>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="social-media">
                            <p class="text-muted small mb-2">Connect with me</p>
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item"><a href="#" class="btn btn-primary btn-sm rounded-circle p-2 "><i class="fa fa-facebook"></i></a></li>
                                <li class="list-inline-item"><a href="#" class="btn btn-danger btn-sm rounded-circle p-2"><i class="fa fa-google-plus"></i></a></li>
                                <li class="list-inline-item"><a href="#" class="btn btn-info btn-sm rounded-circle p-2"><i class="fa fa-twitter"></i></a></li>
                                <li class="list-inline-item"><a href="#" class="btn btn-warning btn-sm rounded-circle p-2 text-white"><i class="fa fa-instagram"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection