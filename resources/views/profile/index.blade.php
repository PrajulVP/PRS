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
        <div class="row">
            <!-- User Profile Card -->
            <div class="col-xl-4">
                <div class="card hovercard text-center">
                    <div class="cardheader"></div>
                    <div class="user-image">
                        <div class="avatar">
                            <img alt="" src="{{ $user->profile_pic ? asset('storage/' . $user->profile_pic) : asset('admin/assets/images/user/7.jpg') }}">
                        </div>
                        <div class="icon-wrapper"><i class="icofont icofont-pencil-alt-5"></i></div>
                    </div>
                    <div class="info">
                        <div class="row">
                            <div class="col-sm-6 col-lg-4 order-sm-1 order-xl-0">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ttl-info text-start">
                                            <h6><i class="fa fa-envelope"></i> Email</h6><span>{{ $user->email }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ttl-info text-start">
                                            <h6><i class="fa fa-calendar"></i> Joined</h6><span>{{ $user->created_at->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-lg-4 order-sm-0 order-xl-1">
                                <div class="user-designation">
                                    <div class="title"><a target="_blank" href="">{{ $user->name }}</a></div>
                                    <div class="desc">{{ $user->getRoleNames()->first() ?? 'User' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 order-sm-2 order-xl-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ttl-info text-start">
                                            <h6><i class="fa fa-phone"></i> Contact Us</h6><span>{{ $user->phone_number ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ttl-info text-start">
                                            <h6><i class="fa fa-location-arrow"></i> Location</h6><span>{{ $user->city ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <!-- Social/Follow Section (Optional) -->
                        <div class="social-media">
                            <ul class="list-inline">
                                <li class="list-inline-item"><a href="#"><i class="fa fa-facebook"></i></a></li>
                                <li class="list-inline-item"><a href="#"><i class="fa fa-google-plus"></i></a></li>
                                <li class="list-inline-item"><a href="#"><i class="fa fa-twitter"></i></a></li>
                                <li class="list-inline-item"><a href="#"><i class="fa fa-instagram"></i></a></li>
                                <li class="list-inline-item"><a href="#"><i class="fa fa-rss"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="col-xl-8">
                <form class="card" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h4 class="card-title mb-0">Edit Profile</h4>
                        <div class="card-options"><a class="card-options-collapse" href="#" data-bs-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a><a class="card-options-remove" href="#" data-bs-toggle="card-remove"><i class="fe fe-x"></i></a></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label class="form-label">Company</label>
                                    <input class="form-control" type="text" placeholder="Company" value="PRS" disabled>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input class="form-control" type="text" placeholder="Username" value="{{ $user->name }}" name="name">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Email address</label>
                                    <input class="form-control" type="email" placeholder="Email" value="{{ $user->email }}" disabled>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input class="form-control" type="text" placeholder="First Name" value="{{ explode(' ', $user->name)[0] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input class="form-control" type="text" placeholder="Last Name" value="{{ explode(' ', $user->name, 2)[1] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <input class="form-control" type="text" placeholder="Home Address" value="{{ $user->address ?? '' }}" name="address">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <input class="form-control" type="text" placeholder="City" value="{{ $user->city ?? '' }}" name="city">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Postal Code</label>
                                    <input class="form-control" type="number" placeholder="ZIP Code" value="{{ $user->pincode ?? '' }}" name="pincode">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label class="form-label">Country</label>
                                    <select class="form-control btn-square">
                                        <option value="0">India</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Profile Image</label>
                                    <input class="form-control" type="file" name="profile_pic">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary" type="submit">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection