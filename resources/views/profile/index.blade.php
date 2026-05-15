@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mx-auto">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mt-3 border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-exclamation-circle me-3 fs-4"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1">Please correct the following errors:</h6>
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif


                <div class="card shadow-sm border-0 mt-3 mb-3">
                    <div class="card-body p-4">
                        <div class="row g-4 h-100">
                            <!-- Left Column: Avatar & Identity - Compact size -->
                            <div
                                class="col-xl-3 col-lg-4 text-center border-end pe-lg-4 d-flex flex-column align-items-center">
                                <div class="mb-3 position-relative d-inline-block mx-auto">
                                    <img src="{{ $user->avatar_url }}" alt="Profile Picture" class="rounded-circle shadow zoomable-avatar"
                                        style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #f8f9fa;"
                                        onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=FFFFFF&background={{ $user->avatar_background ?? '374151' }}';">
                                </div>
                                <h3 class="fw-bold text-main-theme mb-1">{{ $user->name }}</h3>
                                <div>
                                    <span
                                        class="badge bg-light-theme text-primary border border-primary-subtle px-3 py-1 rounded-pill mb-3 fs-6">
                                        {{ $user->getRoleNames()->first() ?? 'User' }}
                                    </span>
                                </div>

                                <div class="text-center mt-3 px-2 flex-grow-1 w-100">
                                    <div class="mb-2">
                                        <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1 d-block"
                                            style="font-size: 0.75rem;">Member
                                            Since</label>
                                        <div class="d-flex align-items-center justify-content-center bg-body-theme p-2 rounded mx-auto"
                                            style="max-width: 200px;">
                                            <i class="fa fa-calendar text-info me-2 fs-6"></i>
                                            <span
                                                class="fs-6 fw-medium text-main-theme">{{ $user->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1 d-block"
                                            style="font-size: 0.75rem;">Status</label>
                                        <div class="d-flex align-items-center justify-content-center bg-body-theme p-2 rounded mx-auto"
                                            style="max-width: 200px;">
                                            <i class="fa fa-check-circle text-success me-2 fs-6"></i>
                                            <span
                                                class="fs-6 fw-medium text-main-theme">{{ ucfirst($user->status ?? 'Active') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Details -->
                            <div class="col-xl-9 col-lg-8 ps-lg-4">
                                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                                    <div>
                                        <h5 class="text-dark fw-bold mb-0">Profile Information</h5>
                                        <p class="text-muted-theme mb-0 small">Manage your personal settings.</p>
                                    </div>
                                    <button class="btn btn-primary btn-sm px-3 py-2" data-bs-toggle="modal"
                                        data-bs-target="#editProfileModal">
                                        <i class="fa fa-edit me-1"></i> Edit
                                    </button>
                                </div>

                                <div class="row g-3">
                                    <!-- Contact Information Section -->
                                    <div class="col-12 mb-1">
                                        <h6 class="text-uppercase text-muted-theme-theme fw-bold mb-3"
                                            style="font-size: 0.75rem; letter-spacing: 1px; border-left: 3px solid var(--med-primary); padding-left: 10px;">
                                            Contact Information</h6>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-2 bg-body-theme rounded h-100 border border-theme">
                                            <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                style="font-size: 0.6rem;">Email</label>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-card-theme rounded-circle shadow-sm border border-theme me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fa fa-envelope text-primary fs-6"></i>
                                                </div>
                                                <span class="fs-6 text-main-theme fw-medium small">{{ $user->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-2 bg-body-theme rounded h-100 border border-theme">
                                            <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                style="font-size: 0.6rem;">Phone</label>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-card-theme rounded-circle shadow-sm border border-theme me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fa fa-phone text-success fs-6"></i>
                                                </div>
                                                <span
                                                    class="fs-6 text-main-theme fw-medium small">{{ $user->contact_no ?? $user->phone_number ?? 'Not updated' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Location Section -->
                                    <div class="col-12 mt-3 mb-1">
                                        <h6 class="text-uppercase text-muted-theme-theme fw-bold mb-3"
                                            style="font-size: 0.75rem; letter-spacing: 1px; border-left: 3px solid #ef4444; padding-left: 10px;">
                                            Location Details</h6>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-2 border border-theme rounded bg-body-theme h-100">
                                            <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                style="font-size: 0.6rem;">City</label>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-map-marker text-danger me-2 fs-5"></i>
                                                <span class="fs-6 text-main-theme small">{{ $user->city ?? 'Not updated' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-2 border border-theme rounded bg-body-theme h-100">
                                            <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                style="font-size: 0.6rem;">Pincode</label>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-map-pin text-danger me-2 fs-5"></i>
                                                <span
                                                    class="fs-6 text-main-theme small">{{ $user->pincode ?? 'Not updated' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 border border-theme rounded bg-body-theme">
                                            <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                style="font-size: 0.6rem;">Full Address</label>
                                            <div class="d-flex align-items-start">
                                                <i class="fa fa-home text-muted-theme-theme me-2 mt-1 fs-5"></i>
                                                <span
                                                    class="fs-6 text-main-theme lh-base small">{{ $user->address ?? 'No detailed address provided.' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Personal Details Section -->
                                    @if($user->hasRole('fieldstaff'))
                                        @if($user->fathers_name || $user->mothers_name)
                                            <div class="col-12 mt-3 mb-1">
                                                <h6 class="text-uppercase text-muted-theme-theme fw-bold mb-3"
                                                    style="font-size: 0.75rem; letter-spacing: 1px; border-left: 3px solid #f59e0b; padding-left: 10px;">
                                                    Personal Details</h6>
                                            </div>
                                            @if($user->fathers_name)
                                                <div class="col-md-6">
                                                    <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                        <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                            style="font-size: 0.6rem;">Father's Name</label>
                                                        <span
                                                            class="fs-6 text-main-theme fw-bold d-block small">{{ $user->fathers_name }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($user->mothers_name)
                                                <div class="col-md-6">
                                                    <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                        <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                            style="font-size: 0.6rem;">Mother's Name</label>
                                                        <span
                                                            class="fs-6 text-main-theme fw-bold d-block small">{{ $user->mothers_name }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    @endif

                                    <!-- Business Details Section -->
                                    @if($user->hasRole('retailer') || $user->hasRole('distributor'))
                                        <div class="col-12 mt-3 mb-1">
                                            <h6 class="text-uppercase text-muted-theme-theme fw-bold mb-3"
                                                style="font-size: 0.75rem; letter-spacing: 1px; border-left: 3px solid #10b981; padding-left: 10px;">
                                                Business Information</h6>
                                        </div>

                                        @if($user->hasRole('retailer') && $user->retailer)
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                        style="font-size: 0.6rem;">Shop Name</label>
                                                    <span
                                                        class="fs-6 text-main-theme fw-bold d-block small">{{ $user->retailer->shop_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                        style="font-size: 0.6rem;">Drug License No.</label>
                                                    <span
                                                        class="fs-6 text-main-theme fw-bold d-block small">{{ $user->retailer->drug_license_no ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                        style="font-size: 0.6rem;">GST Number</label>
                                                    <span
                                                        class="fs-6 text-main-theme fw-bold d-block small">{{ $user->retailer->gst ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        @if($user->hasRole('distributor') && $user->distributor)
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                        style="font-size: 0.6rem;">Drug License No.</label>
                                                    <span
                                                        class="fs-6 text-main-theme fw-bold d-block small">{{ $user->distributor->drug_license_no ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme-theme small text-uppercase fw-bold mb-1"
                                                        style="font-size: 0.6rem;">GST Number</label>
                                                    <span
                                                        class="fs-6 text-main-theme fw-bold d-block small">{{ $user->distributor->gst ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
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

    <!-- Backdrop Blur Style -->
    <style>
        .modal-backdrop.show {
            backdrop-filter: blur(5px);
            background-color: rgba(0, 0, 0, 0.5);
        }
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.08); }
        .bg-soft-success { background-color: rgba(25, 135, 84, 0.08); }
        .bg-soft-warning { background-color: rgba(255, 193, 7, 0.08); }
        .bg-soft-danger { background-color: rgba(220, 53, 69, 0.08); }
        .bg-soft-info { background-color: rgba(13, 202, 240, 0.08); }
        .cursor-pointer { cursor: pointer; }
    </style>

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="width: 95%; max-width: 950px;">
            <div class="modal-content border-0 shadow-lg overflow-hidden position-relative" style="height: 560px; max-height: 90vh;">
                <!-- Absolute Top Right Close Button -->
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Close"></button>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="editProfileForm" class="h-100 d-flex flex-column">
                    @csrf
                    @method('PUT')
                    <div class="d-flex flex-column flex-md-row flex-grow-1 overflow-hidden">
                        <!-- Sidebar: Profile Overview (Left) -->
                        <div class="profile-modal-sidebar border-end p-4 d-flex flex-column align-items-center text-center flex-shrink-0 h-100" style="width: 260px;">
                            <div class="w-100 mb-4">
                                <div class="position-relative d-inline-block mt-2">
                                    <img src="{{ $user->avatar_url }}" alt="Avatar"
                                        class="rounded-circle shadow-lg border border-4 border-white zoomable-avatar"
                                        style="width: 150px; height: 150px; object-fit: cover;"
                                        onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=FFFFFF&background={{ $user->avatar_background ?? '374151' }}';">
                                    
                                    <label for="profile_pic" class="position-absolute bottom-0 end-0 bg-white shadow-sm rounded-circle d-flex align-items-center justify-content-center cursor-pointer border hover-scale" 
                                        style="width: 32px; height: 32px; margin-right: 4px; margin-bottom: 4px;" title="Change Photo">
                                        <i class="fa fa-camera text-primary" style="font-size: 0.7rem;"></i>
                                        <input type="file" name="profile_pic" id="profile_pic" class="d-none" accept="image/*" onchange="previewProfilePic(this)">
                                    </label>
                                </div>
                                
                                <h5 class="mt-3 mb-1 fw-bold text-dark">{{ $user->name }}</h5>
                                <div class="d-flex justify-content-center">
                                    <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-1 mb-3" style="font-size: 0.7rem;">
                                        {{ ucfirst($user->getRoleNames()->first() ?? 'User') }}
                                    </span>
                                </div>

                                <div id="remove_pic_container" class="mt-2" style="{{ $user->profile_pic ? '' : 'display:none;' }}">
                                    <button type="button" class="btn btn-soft-danger px-3 py-1 rounded-pill fw-bold text-uppercase d-inline-flex align-items-center" style="font-size: 0.6rem; letter-spacing: 0.5px;" onclick="removeProfilePic()">
                                        <i class="fa fa-trash-alt me-1"></i> Remove Photo
                                    </button>
                                </div>
                                <input type="hidden" name="remove_profile_pic" id="remove_profile_pic" value="0">
                            </div>

                            @if(!$user->hasAnyRole(['superadmin', 'admin']))
                                <div class="mt-auto text-center w-100 border-top pt-3">
                                    <div class="text-muted-theme mb-1 fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">ACCOUNT STATUS</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="badge bg-success rounded-pill px-3 py-2 mb-3 text-white shadow-sm" style="font-size: 0.75rem;">
                                            <i class="fa fa-check-circle me-1"></i> Active
                                        </div>
                                    </div>
                                    <div class="text-muted-theme border-top pt-2" style="font-size: 0.75rem;">
                                        Member Since {{ $user->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Main Content: Tabs & Form (Right) -->
                        <div class="flex-grow-1 bg-card-theme d-flex flex-column overflow-hidden">
                            <div class="px-4 pt-4 pb-0">
                                <h5 class="fw-bold text-main-theme fs-5 mb-0">Edit Profile</h5>
                            </div>

                            <!-- Horizontal Navigation (Single Line) -->
                            <div class="px-4 mb-2 mt-3">
                                <ul class="nav nav-pills custom-profile-tabs-top bg-light-theme-theme p-1 rounded-3 flex-nowrap overflow-auto hide-scrollbar" id="pills-tab" role="tablist">
                                    <li class="nav-item flex-grow-1 text-center" role="presentation">
                                        <button class="nav-link active w-100 py-2 border-0 text-nowrap d-inline-flex align-items-center justify-content-center" id="pills-general-tab" data-bs-toggle="pill" data-bs-target="#pills-general" type="button" role="tab" style="font-size: 0.8rem; line-height: 1;">
                                            <i class="fa fa-user-circle me-2"></i> <span>General</span>
                                        </button>
                                    </li>
                                    @if($user->hasRole('retailer') || $user->hasRole('distributor'))
                                        <li class="nav-item flex-grow-1 text-center" role="presentation">
                                            <button class="nav-link w-100 py-2 border-0 text-nowrap d-inline-flex align-items-center justify-content-center" id="pills-business-tab" data-bs-toggle="pill" data-bs-target="#pills-business" type="button" role="tab" style="font-size: 0.8rem; line-height: 1;">
                                                <i class="fa fa-briefcase me-2"></i> <span>Business</span>
                                            </button>
                                        </li>
                                    @endif
                                    <li class="nav-item flex-grow-1 text-center" role="presentation">
                                        <button class="nav-link w-100 py-2 border-0 text-nowrap d-inline-flex align-items-center justify-content-center" id="pills-location-tab" data-bs-toggle="pill" data-bs-target="#pills-location" type="button" role="tab" style="font-size: 0.8rem; line-height: 1;">
                                            <i class="fa fa-map-marker me-2"></i> <span>Location</span>
                                        </button>
                                    </li>
                                    @if($user->hasAnyRole(['superadmin', 'admin']))
                                        <li class="nav-item flex-grow-1 text-center" role="presentation">
                                            <button class="nav-link w-100 py-2 border-0 text-nowrap d-inline-flex align-items-center justify-content-center" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab" style="font-size: 0.8rem; line-height: 1;">
                                                <i class="fa fa-shield me-2"></i> <span>Security</span>
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            <div class="tab-content flex-grow-1 p-4 overflow-y-auto hide-scrollbar" id="pills-tabContent">
                                <!-- Tab content remains same -->
                                <div class="tab-pane fade show active" id="pills-general" role="tabpanel" aria-labelledby="pills-general-tab">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light-theme border-end-0"><i class="fa fa-user text-muted-theme"></i></span>
                                                <input type="text" name="name" class="form-control bg-light-theme border-start-0" value="{{ $user->name }}" required>
                                            </div>
                                            <div class="invalid-feedback d-block" id="error_name"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Email Address</label>
                                            <input type="email" name="email" class="form-control {{ $user->hasAnyRole(['superadmin', 'admin']) ? 'bg-light-theme' : 'bg-body-secondary' }}"
                                                value="{{ $user->email }}" {{ $user->hasAnyRole(['superadmin', 'admin']) ? '' : 'readonly' }}>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Phone Number</label>
                                            <input type="text" name="contact_no" class="form-control {{ $user->hasAnyRole(['superadmin', 'admin']) ? 'bg-light-theme' : 'bg-body-secondary' }}"
                                                value="{{ $user->contact_no ?? $user->phone_number ?? '' }}" {{ $user->hasAnyRole(['superadmin', 'admin']) ? '' : 'readonly' }}>
                                        </div>
                                        @unless($user->hasAnyRole(['superadmin', 'admin']))
                                            <div class="col-12">
                                                <div class="alert alert-info border-0 py-2 small mb-0 d-flex align-items-center">
                                                    <i class="fa fa-info-circle me-2"></i> Contact your administrator to update email or phone.
                                                </div>
                                            </div>
                                        @endunless
                                        @if($user->hasRole('fieldstaff'))
                                            <div class="col-md-6 mt-3">
                                                <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Father's Name</label>
                                                <input type="text" name="fathers_name" class="form-control bg-light-theme" value="{{ $user->fathers_name }}">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Mother's Name</label>
                                                <input type="text" name="mothers_name" class="form-control bg-light-theme" value="{{ $user->mothers_name }}">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Tab: Business Details -->
                                @if($user->hasRole('retailer') || $user->hasRole('distributor'))
                                    <div class="tab-pane fade" id="pills-business" role="tabpanel" aria-labelledby="pills-business-tab">
                                        <div class="row g-3">
                                            @if($user->hasRole('retailer'))
                                                <div class="col-12">
                                                    <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Shop Name</label>
                                                    <input type="text" name="shop_name" class="form-control bg-light-theme" value="{{ $user->retailer->shop_name ?? '' }}">
                                                </div>
                                            @endif
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Drug License No.</label>
                                                <input type="text" name="drug_license_no" id="drug_license_no" class="form-control bg-light-theme" 
                                                    value="{{ $user->hasRole('retailer') ? ($user->retailer->drug_license_no ?? '') : ($user->distributor->drug_license_no ?? '') }}">
                                                <div class="text-danger small mt-1" id="error_drug_license" style="display:none;">The drug license number can only contain letters, numbers, slashes (/), and hyphens (-).</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">GST Number</label>
                                                <input type="text" name="gst" class="form-control bg-light-theme" 
                                                    value="{{ $user->hasRole('retailer') ? ($user->retailer->gst ?? '') : ($user->distributor->gst ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="tab-pane fade" id="pills-location" role="tabpanel" aria-labelledby="pills-location-tab">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Street Address</label>
                                            <textarea name="address" class="form-control bg-light-theme" rows="3">{{ $user->address }}</textarea>
                                        </div>
                                        @if(!$user->hasAnyRole(['admin', 'superadmin']))
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Assigned District</label>
                                                <div class="form-control bg-body-secondary text-muted-theme border-0 fw-bold">
                                                    @php
                                                        $districtName = 'N/A';
                                                        if ($user->hasRole('retailer') && $user->retailer && $user->retailer->district) {
                                                            $districtName = $user->retailer->district->name;
                                                        } elseif ($user->hasRole('distributor') && $user->distributor && $user->distributor->district) {
                                                            $districtName = $user->distributor->district->name;
                                                        }
                                                    @endphp
                                                    <i class="fa fa-lock me-2 small"></i> {{ $districtName }}
                                                </div>
                                            </div>
                                        @endif
                                        <div class="{{ $user->hasAnyRole(['admin', 'superadmin']) ? 'col-12' : 'col-md-6' }}">
                                            <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Pincode</label>
                                            <input type="text" name="pincode" class="form-control bg-light-theme" value="{{ $user->pincode ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Security -->
                                @if($user->hasAnyRole(['superadmin', 'admin']))
                                    <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab">
                                        <div class="mb-4">
                                            <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Current Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light-theme border-end-0"><i class="fa fa-lock text-muted-theme"></i></span>
                                                <input type="password" name="current_password" id="current_password" class="form-control bg-light-theme border-start-0" 
                                                    placeholder="Type current password" autocomplete="new-password">
                                                <span class="input-group-text bg-light-theme cursor-pointer toggle-password border-start-0" data-target="current_password"><i class="fa fa-eye-slash"></i></span>
                                            </div>
                                            <div class="text-danger small mt-1" id="error_current_password" style="display:none;"></div>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">New Password</label>
                                                <div class="input-group">
                                                    <input type="password" name="new_password" id="new_password" class="form-control bg-light-theme" autocomplete="new-password">
                                                    <span class="input-group-text bg-light-theme cursor-pointer toggle-password" data-target="new_password"><i class="fa fa-eye-slash"></i></span>
                                                </div>
                                                <div class="text-danger small mt-1" id="error_new_password" style="display:none;"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted-theme text-uppercase" style="font-size: 0.65rem;">Confirm Password</label>
                                                <div class="input-group">
                                                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control bg-light-theme" autocomplete="new-password">
                                                    <span class="input-group-text bg-light-theme cursor-pointer toggle-password" data-target="new_password_confirmation"><i class="fa fa-eye-slash"></i></span>
                                                </div>
                                                <div class="text-danger small mt-1" id="error_confirm_password" style="display:none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="modal-footer border-0 p-4 pt-0 bg-card-theme">
                                <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary btn-sm px-5 fw-bold shadow-sm rounded-pill">
                                    Update Profile
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .custom-profile-tabs-top .nav-link {
            color: #6c757d;
            font-weight: 600;
            font-size: 0.8rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
        .custom-profile-tabs-top .nav-link:hover {
            background-color: rgba(115, 102, 255, 0.1);
            color: var(--med-primary, #7366ff);
        }
        body.dark-only .custom-profile-tabs-top .nav-link:hover {
            background-color: #334155;
            color: #f1f5f9;
        }
        /* Theme Variables */
        :root {
            --bg-card: #ffffff;
            --bg-light: #f8f9fa;
            --text-main: #2d3748;
            --border-light: #edf2f7;
        }

        body.dark-only {
            --bg-card: #1e293b;
            --bg-light: #0f172a;
            --text-main: #f8fafc;
            --border-light: #334155;
        }

        .bg-card-theme { background-color: var(--bg-card) !important; }
        .bg-light-theme { background-color: var(--bg-light) !important; }
        .text-main-theme { color: var(--text-main) !important; }
        .border-theme { border-color: var(--border-light) !important; }

        .custom-profile-tabs-top .nav-link.active {
            background-color: #7366ff !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(115, 102, 255, 0.3);
        }
        .profile-modal-sidebar { background-color: #f1f5f9; }
        body.dark-only .profile-modal-sidebar { background-color: #0f172a; }
        .bg-soft-primary { background-color: rgba(115, 102, 255, 0.1); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .btn-soft-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            transition: all 0.2s ease;
        }
        .btn-soft-danger:hover {
            background-color: #ef4444;
            color: #ffffff !important;
            border-color: #ef4444;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        body.dark-only .btn-soft-danger {
            background-color: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.3);
        }
    </style>
@endsection

@push('scripts')
    <script>
        function previewProfilePic(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#modal_avatar_preview').attr('src', e.target.result);
                    $('#sidebar_avatar_preview').attr('src', e.target.result);
                    $('#remove_pic_container').show();
                    $('#remove_profile_pic').val('0');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeProfilePic() {
            if (confirm('Are you sure you want to remove your profile picture?')) {
                $('#remove_profile_pic').val('1');
                $('#profile_pic').val('');
                $('#modal_avatar_preview').attr('src', 'https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=FFFFFF&background={{ $user->avatar_background ?? '374151' }}');
                $('#remove_pic_container').hide();
            }
        }
        // AJAX Form Submission
        $('#editProfileForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');
            
            // Clear previous errors
            form.find('.invalid-feedback').text('');
            form.find('.form-control').removeClass('is-invalid');
            
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');
            
            $.ajax({
                url: form.attr('action'),
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#editProfileModal').modal('hide');
                    showToast('success', 'Profile updated successfully.');
                    setTimeout(() => window.location.reload(), 1000);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, messages) {
                            let input = form.find(`[name="${key}"]`);
                            input.addClass('is-invalid');
                            let errorDiv = form.find(`#error_${key}`);
                            if (errorDiv.length) {
                                errorDiv.text(messages[0]);
                            } else {
                                input.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                            }
                        });
                        showToast('danger', 'Please fix the errors below.');
                    } else {
                        let message = xhr.responseJSON?.message || 'An error occurred';
                        showToast('danger', message);
                    }
                }
            });
        });

        // Live Validation & UI Logic
        // Name Validation (No numbers/symbols)
        $('input[name="name"]').on('input', function() {
            let val = $(this).val();
            let regex = /^[a-zA-Z\s]*$/;
            let errorDiv = $('#error_name');
            
            if (!regex.test(val)) {
                errorDiv.text('Name should only contain letters and spaces.');
                $(this).addClass('is-invalid');
            } else {
                errorDiv.text('');
                $(this).removeClass('is-invalid');
            }
        });

        // Phone Validation (if editable/present)
        $('input[name="contact_no"]').on('input', function() {
            let val = $(this).val().replace(/\D/g, ''); // Remove non-digits
            let errorDiv = $('#error_contact_no');
            if (errorDiv.length === 0) {
                $(this).after('<div class="invalid-feedback d-block" id="error_contact_no"></div>');
                errorDiv = $('#error_contact_no');
            }

            if (val.startsWith('0')) {
                val = val.substring(1);
                errorDiv.text('Phone number cannot start with 0.');
            } else if (val.length > 10) {
                val = val.substring(0, 10);
                errorDiv.text('Phone number cannot exceed 10 digits.');
            } else {
                errorDiv.text('');
            }
            
            $(this).val(val);
            if (errorDiv.text() !== '') {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Pincode Validation (6 digits)
        $('input[name="pincode"]').on('input', function() {
            let val = $(this).val().replace(/\D/g, '').substring(0, 6);
            $(this).val(val);
            let errorDiv = $('#error_pincode');

            if (val.length > 0 && val.length < 6) {
                errorDiv.text('Pincode must be exactly 6 digits.');
                $(this).addClass('is-invalid');
            } else {
                errorDiv.text('');
                $(this).removeClass('is-invalid');
            }
        });

        // Password Visibility Toggle
        $(document).on('click', '.toggle-password', function() {
            let targetId = $(this).data('target');
            let input = targetId ? $('#' + targetId) : $(this).parent().find('input');
            let icon = $(this).find('i');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
            
            // Live Drug License Validation
            const drugLicenseInput = document.getElementById('drug_license_no');
            const drugLicenseError = document.getElementById('error_drug_license');
            if (drugLicenseInput) {
                drugLicenseInput.addEventListener('input', function() {
                    const regex = /^[a-zA-Z0-9\/\-]*$/;
                    if (!regex.test(this.value)) {
                        drugLicenseError.style.display = 'block';
                        this.classList.add('is-invalid');
                    } else {
                        drugLicenseError.style.display = 'none';
                        this.classList.remove('is-invalid');
                    }
                });
            }

            // Real-time sequential password validation
        $('#current_password').on('blur', function() {
            let val = $(this).val();
            if (val.length > 0) {
                $.post('{{ route("profile.check-password") }}', {
                    _token: '{{ csrf_token() }}',
                    current_password: val
                }, function(response) {
                    if (!response.valid) {
                        $('#error_current_password').text(response.message).show();
                        $('#current_password').addClass('is-invalid');
                    } else {
                        $('#error_current_password').text('').hide();
                        $('#current_password').removeClass('is-invalid');
                    }
                });
            } else {
                $('#error_current_password').hide();
                $('#current_password').removeClass('is-invalid');
            }
        });

        $('#new_password').on('focus input', function() {
            let curVal = $('#current_password').val();
            if (curVal.length === 0) {
                $('#error_current_password').text('Please enter your current password first.').show();
                $('#current_password').addClass('is-invalid');
            }
        });

        $('#new_password').on('blur', function() {
            let val = $(this).val();
            if (val.length > 0 && val.length < 6) {
                $('#error_new_password').text('Password must be at least 6 characters.').show();
                $(this).addClass('is-invalid');
            } else {
                $('#error_new_password').hide();
                $(this).removeClass('is-invalid');
            }
        });

        $('#new_password_confirmation').on('input blur', function() {
            let val = $(this).val();
            let target = $('#new_password').val();
            if (val.length > 0 && val !== target) {
                $('#error_confirm_password').text('Passwords do not match.').show();
                $(this).addClass('is-invalid');
            } else {
                $('#error_confirm_password').hide();
                $(this).removeClass('is-invalid');
            }
        });
    </script>
@endpush
