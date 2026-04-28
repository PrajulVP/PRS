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
                                    <img src="{{ $user->avatar_url }}" alt="Profile Picture" class="rounded-circle shadow"
                                        style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #f8f9fa;"
                                        onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=FFFFFF&background={{ $user->avatar_background ?? '374151' }}';">
                                </div>
                                <h3 class="fw-bold text-main-theme mb-1">{{ $user->name }}</h3>
                                <div>
                                    <span
                                        class="badge bg-light text-primary border border-primary-subtle px-3 py-1 rounded-pill mb-3 fs-6">
                                        {{ $user->getRoleNames()->first() ?? 'User' }}
                                    </span>
                                </div>

                                <div class="text-center mt-3 px-2 flex-grow-1 w-100">
                                    <div class="mb-2">
                                        <label class="text-muted-theme small text-uppercase fw-bold mb-1 d-block"
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
                                        <label class="text-muted-theme small text-uppercase fw-bold mb-1 d-block"
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
                                        <p class="text-muted mb-0 small">Manage your personal settings.</p>
                                    </div>
                                    <button class="btn btn-primary btn-sm px-3 py-2" data-bs-toggle="modal"
                                        data-bs-target="#editProfileModal">
                                        <i class="fa fa-edit me-1"></i> Edit
                                    </button>
                                </div>

                                <div class="row g-3">
                                    <!-- Contact Information Section -->
                                    <div class="col-12 mb-1">
                                        <h6 class="text-uppercase text-muted-theme fw-bold mb-3"
                                            style="font-size: 0.75rem; letter-spacing: 1px; border-left: 3px solid var(--med-primary); padding-left: 10px;">
                                            Contact Information</h6>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-2 bg-body-theme rounded h-100 border border-theme">
                                            <label class="text-muted-theme small text-uppercase fw-bold mb-1"
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
                                            <label class="text-muted-theme small text-uppercase fw-bold mb-1"
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
                                        <h6 class="text-uppercase text-muted-theme fw-bold mb-3"
                                            style="font-size: 0.75rem; letter-spacing: 1px; border-left: 3px solid #ef4444; padding-left: 10px;">
                                            Location Details</h6>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-2 border border-theme rounded bg-body-theme h-100">
                                            <label class="text-muted-theme small text-uppercase fw-bold mb-1"
                                                style="font-size: 0.6rem;">City</label>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-map-marker text-danger me-2 fs-5"></i>
                                                <span class="fs-6 text-main-theme small">{{ $user->city ?? 'Not updated' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-2 border border-theme rounded bg-body-theme h-100">
                                            <label class="text-muted-theme small text-uppercase fw-bold mb-1"
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
                                            <label class="text-muted-theme small text-uppercase fw-bold mb-1"
                                                style="font-size: 0.6rem;">Full Address</label>
                                            <div class="d-flex align-items-start">
                                                <i class="fa fa-home text-muted-theme me-2 mt-1 fs-5"></i>
                                                <span
                                                    class="fs-6 text-main-theme lh-base small">{{ $user->address ?? 'No detailed address provided.' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Personal Details Section -->
                                    @if($user->hasRole('fieldstaff'))
                                        @if($user->fathers_name || $user->mothers_name)
                                            <div class="col-12 mt-3 mb-1">
                                                <h6 class="text-uppercase text-muted-theme fw-bold mb-3"
                                                    style="font-size: 0.75rem; letter-spacing: 1px; border-left: 3px solid #f59e0b; padding-left: 10px;">
                                                    Personal Details</h6>
                                            </div>
                                            @if($user->fathers_name)
                                                <div class="col-md-6">
                                                    <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                        <label class="text-muted-theme small text-uppercase fw-bold mb-1"
                                                            style="font-size: 0.6rem;">Father's Name</label>
                                                        <span
                                                            class="fs-6 text-main-theme fw-bold d-block small">{{ $user->fathers_name }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($user->mothers_name)
                                                <div class="col-md-6">
                                                    <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                        <label class="text-muted-theme small text-uppercase fw-bold mb-1"
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
                                            <h6 class="text-uppercase text-muted-theme fw-bold mb-3"
                                                style="font-size: 0.75rem; letter-spacing: 1px; border-left: 3px solid #10b981; padding-left: 10px;">
                                                Business Information</h6>
                                        </div>

                                        @if($user->hasRole('retailer') && $user->retailer)
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme small text-uppercase fw-bold mb-1"
                                                        style="font-size: 0.6rem;">Shop Name</label>
                                                    <span
                                                        class="fs-6 text-main-theme fw-bold d-block small">{{ $user->retailer->shop_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme small text-uppercase fw-bold mb-1"
                                                        style="font-size: 0.6rem;">Drug License No.</label>
                                                    <span
                                                        class="fs-6 text-main-theme fw-bold d-block small">{{ $user->retailer->drug_license_no ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme small text-uppercase fw-bold mb-1"
                                                        style="font-size: 0.6rem;">GST Number</label>
                                                    <span
                                                        class="fs-6 text-main-theme fw-bold d-block small">{{ $user->retailer->gst ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        @if($user->hasRole('distributor') && $user->distributor)
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme small text-uppercase fw-bold mb-1"
                                                        style="font-size: 0.6rem;">Drug License No.</label>
                                                    <span
                                                        class="fs-6 text-main-theme fw-bold d-block small">{{ $user->distributor->drug_license_no ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 border border-theme rounded h-100 bg-body-theme">
                                                    <label class="text-muted-theme small text-uppercase fw-bold mb-1"
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
            /* Semi-transparent black */
        }
    </style>

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1000px;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-1py-2 px-3">
                    <h5 class="modal-title text-white fw-bold fs-6"><i class="fa fa-user-edit me-2"></i> Edit Profile
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-3">
                        <div class="row g-3">
                            <!-- Left Column: Editable Information -->
                            <div class="col-lg-7 border-end">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-1" style="font-size: 0.8rem;">
                                    Personal Information</h6>

                                <div class="mb-2">
                                    <label class="form-label fw-bold small mb-1">Full Name</label>
                                    <input type="text" name="name" class="form-control form-control-sm"
                                        value="{{ $user->name }}" required>
                                </div>

                                @if($user->hasRole('fieldstaff'))
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1">Father's Name</label>
                                            <input type="text" name="fathers_name" class="form-control form-control-sm"
                                                value="{{ $user->fathers_name }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1">Mother's Name</label>
                                            <input type="text" name="mothers_name" class="form-control form-control-sm"
                                                value="{{ $user->mothers_name }}">
                                        </div>
                                    </div>
                                @endif

                                {{-- Role Specific Editable Fields --}}
                                @if($user->hasRole('retailer'))
                                    <h6 class="text-success fw-bold mt-3 mb-2 border-bottom pb-1" style="font-size: 0.8rem;">
                                        Business Details (Retailer)</h6>
                                    <div class="mb-2">
                                        <label class="form-label fw-bold small mb-1">Shop Name</label>
                                        <input type="text" name="shop_name" class="form-control form-control-sm"
                                            value="{{ $user->retailer->shop_name ?? '' }}">
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1">Drug License No.</label>
                                            <input type="text" name="drug_license_no" class="form-control form-control-sm"
                                                value="{{ $user->retailer->drug_license_no ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1">GST Number</label>
                                            <input type="text" name="gst" class="form-control form-control-sm"
                                                value="{{ $user->retailer->gst ?? '' }}">
                                        </div>
                                    </div>
                                @endif

                                @if($user->hasRole('distributor'))
                                    <h6 class="text-info fw-bold mt-3 mb-2 border-bottom pb-1" style="font-size: 0.8rem;">
                                        Business Details (Distributor)</h6>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1">Drug License No.</label>
                                            <input type="text" name="drug_license_no" class="form-control form-control-sm"
                                                value="{{ $user->distributor->drug_license_no ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1">GST Number</label>
                                            <input type="text" name="gst" class="form-control form-control-sm"
                                                value="{{ $user->distributor->gst ?? '' }}">
                                        </div>
                                    </div>
                                @endif

                                <h6 class="text-primary fw-bold mt-3 mb-2 border-bottom pb-1" style="font-size: 0.8rem;">
                                    Address Details</h6>
                                <div class="mb-2">
                                    <label class="form-label fw-bold small mb-1">Address</label>
                                    <textarea name="address" class="form-control form-control-sm"
                                        rows="2">{{ $user->address }}</textarea>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small mb-1">City</label>
                                        <input type="text" name="city" class="form-control form-control-sm"
                                            value="{{ $user->city }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small mb-1">Pincode</label>
                                        <input type="text" name="pincode" class="form-control form-control-sm"
                                            value="{{ $user->pincode ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Account Details & Avatar -->
                            <div class="col-lg-5 ps-lg-4">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-1" style="font-size: 0.8rem;">
                                    Profile Picture</h6>
                                <div class="text-center mb-3">
                                    <div class="position-relative d-inline-block">
                                        <img src="{{ $user->avatar_url }}" alt="Avatar"
                                            class="rounded-circle shadow-sm border border-3 border-white"
                                            style="width: 100px; height: 100px; object-fit: cover;"
                                            onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=FFFFFF&background={{ $user->avatar_background ?? '374151' }}';">
                                        <label for="profile_pic"
                                            class="position-absolute bottom-0 end-0 bg-white text-primary shadow-sm rounded-circle p-1 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; cursor: pointer; border: 2px solid #e9ecef;">
                                            <i class="fa fa-camera fs-6"></i>
                                            <input type="file" name="profile_pic" id="profile_pic" class="d-none"
                                                accept="image/*" onchange="previewProfilePic(this)">
                                        </label>
                                    </div>
                                    <p class="text-muted small mt-1 fst-italic" style="font-size: 0.6rem;">Click camera
                                        icon to change</p>
                                </div>

                                <h6 class="text-muted fw-bold mb-2 small"><i class="fa fa-lock me-1"></i> Account
                                    Details</h6>

                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label text-muted small text-uppercase mb-1"
                                            style="font-size: 0.6rem;">Email</label>
                                        <input type="text" name="email" class="form-control form-control-sm {{ $user->hasAnyRole(['superadmin', 'admin']) ? '' : 'bg-light text-dark' }}"
                                            value="{{ $user->email }}" {{ $user->hasAnyRole(['superadmin', 'admin']) ? '' : 'readonly' }}>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase mb-1"
                                            style="font-size: 0.6rem;">Phone</label>
                                        <input type="text" name="contact_no" class="form-control form-control-sm {{ $user->hasAnyRole(['superadmin', 'admin']) ? '' : 'bg-light text-dark' }}"
                                            value="{{ $user->contact_no ?? $user->phone_number ?? '' }}" {{ $user->hasAnyRole(['superadmin', 'admin']) ? '' : 'readonly' }}>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase mb-1"
                                            style="font-size: 0.6rem;">Role</label>
                                        <input type="text" class="form-control form-control-sm bg-light text-dark"
                                            value="{{ ucfirst($user->getRoleNames()->first() ?? 'User') }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase mb-1"
                                            style="font-size: 0.6rem;">Member Since</label>
                                        <input type="text" class="form-control form-control-sm bg-light text-dark"
                                            value="{{ $user->created_at->format('M d, Y') }}" readonly>
                                    </div>
                                </div>

                                @unless($user->hasAnyRole(['superadmin', 'admin']))
                                    <div class="alert alert-info d-flex align-items-center mt-3 mb-0 py-2 small" role="alert">
                                        <i class="fa fa-info-circle me-2"></i>
                                        <div>
                                            To update details, please contact Admin.
                                        </div>
                                    </div>
                                @endunless
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-card-theme border-top border-theme py-2 px-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4"><i class="fa fa-save me-1"></i>
                            Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function previewProfilePic(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.querySelector('#editProfileModal img.rounded-circle');
                    if (img) {
                        img.src = e.target.result;
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush