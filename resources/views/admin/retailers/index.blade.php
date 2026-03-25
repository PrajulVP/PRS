@extends('layouts.admin')

@push('styles')
<style>
    .nav-tabs.custom-tabs {
        border-bottom: none;
        gap: 0.5rem;
        padding: 0.5rem;
        background: #f8fafc;
        border-radius: 12px;
        display: inline-flex;
    }
    .nav-tabs.custom-tabs .nav-link {
        border: 1px solid transparent !important;
        color: #64748b;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        border-radius: 8px !important;
        background: none;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .nav-tabs.custom-tabs .nav-link.active {
        color: #00497a !important;
        background: #ffffff !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    }
    .nav-tabs.custom-tabs .nav-link:hover:not(.active) {
        color: #475569;
        background: #f1f5f9;
        border-color: transparent;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
        white-space: nowrap;
    }
</style>
@endpush

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-3"><i class="fa fa-users me-2"></i>Retailers</h5>
                        <ul class="nav nav-tabs custom-tabs" id="userStatusTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-status="all" type="button">All Retailers</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-status="active" type="button">Active</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-status="inactive" type="button">Inactive</button>
                            </li>
                        </ul>
                    </div>
                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('retailers', 'add'))
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#createRetailerModal">
                                <i class="fa fa-plus me-1"></i>Add Retailer
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="table-responsive">
                            <table class="display table table-striped table-hover" id="retailers-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Shop Name</th>
                                        <th>Owner Name</th>
                                        <th>Email</th>
                                        <th>Contact No</th>
                                        <th>GST</th>
                                        <th>Drug Lic.</th>
                                        <th>District</th>
                                        <th>Area</th>
                                        <th>Pincode</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createRetailerModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Retailer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createRetailerForm" action="{{ route('admin.retailers.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Shop Name</label>
                                <input type="text" name="shop_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Owner Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" id="create_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="create_password_confirmation"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST</label>
                                <input type="text" name="gst" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Drug License No</label>
                                <input type="text" name="drug_license_no" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">District</label>
                                <select name="district_id" class="form-select district-select" required>
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Area</label>
                                <select name="area_id" class="form-select area-select" required>
                                    <option value="">Select Area</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control" required>
                            </div>

                            {{-- Dynamic Assignment Row --}}
                            <div class="col-12">
                                <div class="row g-3">
                                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin']))
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Sales Manager</label>
                                        <select name="sales_manager_id" id="create_sales_manager_id" class="form-select">
                                            <option value="">Select Sales Manager</option>
                                            @foreach ($salesManagers as $sm)
                                                <option value="{{ $sm->id }}">{{ $sm->user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif

                                    @if(!Auth::user()->hasRole('fieldstaff'))
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Field Staff</label>
                                        <select name="field_staff_id" id="create_field_staff_id" class="form-select">
                                            <option value="">Select Field Staff</option>
                                            @foreach ($fieldStaffs as $fs)
                                                <option value="{{ $fs->id }}" data-sales-manager-id="{{ $fs->sales_manager_id }}">{{ $fs->user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2" required></textarea>
                            </div>
                            {{-- 
                            <input type="hidden" name="latitude" id="create_lat">
                            <input type="hidden" name="longitude" id="create_long">
                            <div class="col-12 mt-3">
                                <div class="input-group">
                                    <input id="create_pac-input" class="form-control" type="text"
                                        placeholder="Search for a location">
                                    <button type="button" class="btn btn-info"
                                        onclick="getGeoLocation('create_lat', 'create_long', 'create')"><i
                                            class="fa fa-map-marker"></i> Get Current Location</button>
                                </div>
                                <div id="create_map" style="height: 300px; width: 100%; margin-top: 10px;"></div>
                            </div>
                            --}}

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editRetailerModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Retailer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editRetailerForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Shop Name</label>
                                <input type="text" name="shop_name" id="edit_shop_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Owner Name</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password (Leave blank to keep unchanged)</label>
                                <input type="password" name="password" id="edit_password" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="edit_password_confirmation"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" id="edit_contact_no" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST</label>
                                <input type="text" name="gst" id="edit_gst" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Drug License No</label>
                                <input type="text" name="drug_license_no" id="edit_drug_license_no" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">District</label>
                                <select name="district_id" id="edit_district_id" class="form-select district-select"
                                    required>
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Area</label>
                                <select name="area_id" id="edit_area_id" class="form-select area-select" required>
                                    <option value="">Select Area</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" id="edit_pincode" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" id="edit_address" class="form-control" rows="2"
                                    required></textarea>
                            </div>
                            {{-- 
                            <input type="hidden" name="latitude" id="edit_latitude">
                            <input type="hidden" name="longitude" id="edit_longitude">
                            <div class="col-12 mt-3">
                                <div class="input-group">
                                    <input id="edit_pac-input" class="form-control" type="text"
                                        placeholder="Search for a location">
                                    <button type="button" class="btn btn-info"
                                        onclick="getGeoLocation('edit_latitude', 'edit_longitude', 'edit')"><i
                                            class="fa fa-map-marker"></i> Get Current Location</button>
                                </div>
                                <div id="edit_map" style="height: 300px; width: 100%; margin-top: 10px;"></div>
                            </div>
                            --}}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Show Modal --}}
    <div class="modal fade" id="showRetailerModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, #1e3a5f, #2e6da4); border-radius: 0.5rem 0.5rem 0 0;">
                    <h5 class="modal-title text-white" style="color: #fff !important;"><i class="fa fa-store me-2"
                            style="color: #fff !important;"></i>Retailer Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Avatar + Name Header --}}
                    <div class="d-flex align-items-center gap-4 p-4"
                        style="background: var(--med-bg-body); border-bottom:1px solid var(--med-border);">
                        <div style="flex-shrink:0;">
                            <img id="ret_avatar_img" src="" alt="" class="rounded-circle shadow"
                                style="width:85px;height:85px;object-fit:cover;display:none;border:3px solid #fff;">
                            <div id="ret_avatar_initials"
                                style="width:85px;height:85px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                                                    font-size:1.9rem;font-weight:700;color:#fff;
                                                                                    background:linear-gradient(135deg,#1e3a5f,#2e6da4);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="mb-0 fw-bold" id="ret_view_shop"></h4>
                                <span class="badge" id="ret_view_status"></span>
                            </div>
                            <div class="mt-1 text-muted small" id="ret_view_owner"></div>
                        </div>
                        <div class="text-end">
                            <div class="loyalty-badge shadow-sm rounded-3 py-2 px-3 d-inline-flex align-items-center gap-2" 
                                 style="background: linear-gradient(135deg, #ffd700, #ffa500); border: 1px solid rgba(255,255,255,0.4); font-family: 'Montserrat', sans-serif;">
                                <i class="fa fa-award text-white fs-4"></i>
                                <div class="text-start">
                                    <div class="text-white small fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px; line-height: 1;">Loyalty Points</div>
                                    <div class="text-white fw-bold fs-5" id="ret_header_points" style="line-height: 1.1; font-family: 'Montserrat', sans-serif;">0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Info Cards --}}
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-envelope mt-1 text-primary"></i>
                                    <div>
                                        <div class="text-muted small">Email</div>
                                        <div class="fw-semibold" id="ret_view_email"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-phone mt-1 text-success"></i>
                                    <div>
                                        <div class="text-muted small">Contact</div>
                                        <div class="fw-semibold" id="ret_view_contact"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-file-alt mt-1 text-warning"></i>
                                    <div>
                                        <div class="text-muted small">GST</div>
                                        <div class="fw-semibold" id="ret_view_gst"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-id-card mt-1 text-info"></i>
                                    <div>
                                        <div class="text-muted small">Drug License</div>
                                        <div class="fw-semibold" id="ret_view_drug"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-map mt-1 text-secondary"></i>
                                    <div>
                                        <div class="text-muted small">District / Area</div>
                                        <div class="fw-semibold" id="ret_view_location"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-hashtag mt-1 text-dark"></i>
                                    <div>
                                        <div class="text-muted small">Pincode</div>
                                        <div class="fw-semibold" id="ret_view_pincode"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-dark">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-map-marker-alt mt-1 text-danger"></i>
                                    <div>
                                        <div class="text-muted small">Address</div>
                                        <div class="fw-semibold" id="ret_view_address"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-user-tie mt-1 text-success"></i>
                                    <div>
                                        <div class="text-muted small">Field Staff</div>
                                        <div class="fw-semibold" id="ret_view_fieldstaff"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-user-shield mt-1 text-primary"></i>
                                    <div>
                                        <div class="text-muted small">Sales Manager</div>
                                        <div class="fw-semibold" id="ret_view_salesmanager"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        {{-- 
                        <hr class="my-4">
                        <h6 class="mb-3"><i class="fa fa-map-marker-alt me-2"></i>Location on Map</h6>
                        <div id="show_map" style="height:300px;width:100%;border-radius:12px;border:1px solid #eee;"></div>
                        --}}
                    </div>
                </div>
                <div class="modal-footer border-0" style="background: var(--med-bg-body);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Global Map Variables
        var createMap, editMap, showMap;
        var createMarker, editMarker, showMarker;

        function initMap() {
            const createMapDiv = document.getElementById("create_map");
            if (!createMapDiv) return;

            const defaultLoc = { lat: 20.5937, lng: 78.9629 };

            // Create Map
            createMap = new google.maps.Map(createMapDiv, {
                zoom: 5, center: defaultLoc, mapId: "DEMO_MAP_ID",
            });
            createMarker = new google.maps.marker.AdvancedMarkerElement({
                position: defaultLoc, map: createMap, gmpDraggable: true,
            });
            createMarker.addListener("dragend", () => {
                const pos = createMarker.position;
                let lat = (typeof pos.lat === 'function') ? pos.lat() : pos.lat;
                let lng = (typeof pos.lng === 'function') ? pos.lng() : pos.lng;
                document.getElementById("create_lat").value = lat;
                document.getElementById("create_long").value = lng;
            });

            // Edit Map
            const editMapDiv = document.getElementById("edit_map");
            if (editMapDiv) {
                editMap = new google.maps.Map(editMapDiv, {
                    zoom: 5, center: defaultLoc, mapId: "DEMO_MAP_ID_EDIT",
                });
                editMarker = new google.maps.marker.AdvancedMarkerElement({
                    position: defaultLoc, map: editMap, gmpDraggable: true,
                });
                editMarker.addListener("dragend", () => {
                    const pos = editMarker.position;
                    let lat = (typeof pos.lat === 'function') ? pos.lat() : pos.lat;
                    let lng = (typeof pos.lng === 'function') ? pos.lng() : pos.lng;
                    document.getElementById("edit_latitude").value = lat;
                    document.getElementById("edit_longitude").value = lng;
                });
            }

            // Show Map
            const showMapDiv = document.getElementById("show_map");
            if (showMapDiv) {
                showMap = new google.maps.Map(showMapDiv, {
                    zoom: 5, center: defaultLoc, mapId: "DEMO_MAP_ID_SHOW",
                });
                showMarker = new google.maps.marker.AdvancedMarkerElement({
                    position: defaultLoc, map: showMap,
                });
            }

            // Autocomplete for Create
            const createInput = document.getElementById("create_pac-input");
            if (createInput) {
                const createAutocomplete = new google.maps.places.Autocomplete(createInput);
                createAutocomplete.bindTo("bounds", createMap);
                createAutocomplete.addListener("place_changed", () => {
                    const place = createAutocomplete.getPlace();
                    if (!place.geometry || !place.geometry.location) return;
                    if (place.geometry.viewport) createMap.fitBounds(place.geometry.viewport);
                    else { createMap.setCenter(place.geometry.location); createMap.setZoom(17); }
                    createMarker.position = place.geometry.location;
                    document.getElementById("create_lat").value = place.geometry.location.lat();
                    document.getElementById("create_long").value = place.geometry.location.lng();
                });
            }
        }
        window.initMap = initMap;

        function fetchAreas(districtId, areaSelect, selectedAreaId = null) {
            areaSelect.html('<option value="">Loading...</option>');
            if (!districtId) { areaSelect.html('<option value="">Select Area</option>'); return; }
            $.get("{{ route('retailers.getAreas', ':district') }}".replace(':district', districtId), (data) => {
                areaSelect.html('<option value="">Select Area</option>');
                $.each(data, (k, v) => areaSelect.append(`<option value="${v.id}">${v.name}</option>`));
                if (selectedAreaId) areaSelect.val(selectedAreaId);
            }).fail(() => areaSelect.html('<option value="">Error</option>'));
        }

        function getGeoLocation(latId, longId, mapType) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    let lat = pos.coords.latitude, lng = pos.coords.longitude;
                    document.getElementById(latId).value = lat;
                    document.getElementById(longId).value = lng;
                    let p = { lat, lng };
                    if (mapType === 'create' && createMap) { createMarker.position = p; createMap.setCenter(p); createMap.setZoom(15); }
                    else if (mapType === 'edit' && editMap) { editMarker.position = p; editMap.setCenter(p); editMap.setZoom(15); }
                }, (err) => alert("Error: " + err.message));
            }
        }

        $(document).ready(function () {
            const isDistributor = @json(Auth::user()->hasRole('distributor'));
            var table = $('#retailers-table').DataTable({
                processing: true, serverSide: true, order: [],
                ajax: {
                    url: "{{ route('admin.retailers.index') }}",
                    data: (d) => { d.status = $('#userStatusTabs button.active').data('status'); }
                },
                columns: [
                    { data: null, orderable: false, searchable: false, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
                    { data: 'shop_name', name: 'shop_name' },
                    { data: 'user.name', name: 'user.name' },
                    { data: 'user.email', name: 'user.email' },
                    { data: 'contact_no', name: 'contact_no' },
                    { data: 'gst', name: 'gst' },
                    { data: 'drug_license_no', name: 'drug_license_no' },
                    { data: 'district_name', name: 'district_name' },
                    { data: 'area_name', name: 'area_name' },
                    { data: 'pincode', name: 'pincode' },
                    { data: 'address', name: 'address' },
                    { 
                        data: 'user.status', name: 'user.status',
                        render: (data, type, row) => `<span class="status-badge ${data === 'active' ? 'status-badge-active' : 'status-badge-inactive'} status-toggle" data-id="${row.id}" data-status="${data}">${data === 'active' ? 'Active' : 'Inactive'}</span>`
                    },
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (id, type, row) {
                            let rowData = JSON.stringify(row).replace(/"/g, '&quot;');
                            let deleteUrl = "{{ route('admin.retailers.destroy', ':id') }}".replace(':id', id);
                            let btns = `<div class="action-buttons">
                                ${isDistributor ? `<a href="{{ route('admin.retailer.index') }}?retailer_id=${id}" class="btn btn-sm btn-warning" title="View Orders"><i class="fa fa-shopping-cart"></i></a>` : ''}
                                <button type="button" class="btn btn-sm btn-info view-btn" data-row="${rowData}"><i class="fa fa-eye"></i></button>`;
                            if (row.can_edit) btns += `<button type="button" class="btn btn-sm btn-primary edit-btn" data-row="${rowData}"><i class="fa fa-edit"></i></button>`;
                            if (row.can_delete) btns += `<button type="button" class="btn btn-sm btn-danger delete-btn" data-url="${deleteUrl}"><i class="fa fa-trash"></i></button>`;
                            return btns + `</div>`;
                        }
                    }
                ],
                dom: "<'row mb-3'<'col-sm-12'B>><'row mb-3'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex justify-content-center justify-content-md-start align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end align-items-center'p>>",
                buttons: {
                    dom: { button: { className: 'btn btn-sm btn-icon' } },
                    buttons: [
                        { extend: 'copy', className: 'btn btn-secondary btn-sm', text: '<i class="fa fa-copy"></i> Copy' },
                        { extend: 'csv', className: 'btn btn-info btn-sm text-white', text: '<i class="fa fa-file-csv"></i> CSV' },
                        { extend: 'excel', className: 'btn btn-success btn-sm', text: '<i class="fa fa-file-excel"></i> Excel' },
                        { 
                            extend: 'pdf', className: 'btn btn-danger btn-sm', text: '<i class="fa fa-file-pdf"></i> PDF',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            customize: function(doc) {
                                doc.defaultStyle.fontSize = 7;
                                doc.styles.tableHeader.fontSize = 8;
                            }
                        },
                        { extend: 'print', className: 'btn btn-dark btn-sm', text: '<i class="fa fa-print"></i> Print', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] } }
                    ]
                }
            });

            // Handlers
            $('.district-select').on('change', function () {
                fetchAreas($(this).val(), $(this).closest('form').find('.area-select'));
            });

            $('#retailers-table').on('click', '.edit-btn', function () {
                var data = $(this).data('row');
                $('#edit_shop_name').val(data.shop_name);
                $('#edit_name').val(data.user.name);
                $('#edit_email').val(data.user.email);
                $('#edit_gst').val(data.gst);
                $('#edit_drug_license_no').val(data.drug_license_no);
                $('#edit_contact_no').val(data.contact_no);
                $('#edit_pincode').val(data.pincode);
                $('#edit_address').val(data.address);
                $('#edit_latitude').val(data.latitude);
                $('#edit_longitude').val(data.longitude);
                $('#edit_district_id').val(data.district_id);
                $('#edit_status').val(data.user?.status || '');
                fetchAreas(data.district_id, $('#edit_area_id'), data.area_id);
                $('#editRetailerForm').attr('action', "{{ route('admin.retailers.update', ':id') }}".replace(':id', data.id));
                $('#editRetailerModal').modal('show');
            });

            $('#retailers-table').on('click', '.view-btn', function () {
                var data = $(this).data('row');
                // Avatar logic
                if (data.user?.avatar) {
                    $('#ret_avatar_img').attr('src', data.user.avatar).show();
                    $('#ret_avatar_initials').hide();
                } else {
                    $('#ret_avatar_img').hide();
                    let initials = data.user?.name ? data.user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '??';
                    $('#ret_avatar_initials').text(initials).show();
                }

                $('#ret_view_shop').text(data.shop_name);
                $('#ret_view_owner').html('<i class="fa fa-user me-1"></i>Owner: ' + data.user.name);
                $('#ret_view_status').attr('class', 'status-badge ' + (data.user?.status === 'active' ? 'status-badge-active' : 'status-badge-inactive')).text(data.user?.status);
                $('#ret_view_email').text(data.user.email);
                $('#ret_view_contact').text(data.contact_no || 'N/A');
                $('#ret_view_gst').text(data.gst || 'N/A');
                $('#ret_view_drug').text(data.drug_license_no || 'N/A');
                $('#ret_view_location').text((data.district?.name || 'N/A') + ' / ' + (data.area?.name || 'N/A'));
                $('#ret_view_pincode').text(data.pincode || 'N/A');
                $('#ret_view_address').text(data.address || 'N/A');
                let points = parseFloat(data.loyalty_points || 0).toFixed(2);
                $('#ret_header_points').text(points);
                $('#ret_view_fieldstaff').text(data.field_staff_name || 'N/A');
                $('#ret_view_salesmanager').text(data.sales_manager_name || 'N/A');
                $('#showRetailerModal').modal('show');
            });

            $('#createRetailerForm, #editRetailerForm').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                btn.prop('disabled', true);
                $.ajax({
                    url: form.attr('action') || "{{ route('admin.retailers.store') }}",
                    type: "POST", data: new FormData(this), processData: false, contentType: false,
                    success: (res) => {
                        $('.modal').modal('hide');
                        form[0].reset();
                        table.ajax.reload();
                        btn.prop('disabled', false);
                        showToast('success', 'Saved successfully');
                    },
                    error: (xhr) => {
                        btn.prop('disabled', false);
                        let message = 'Error';
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).map(e => e[0]).join('<br>');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showToast('danger', message);
                    }
                });
            });

            $('#retailers-table').on('click', '.delete-btn', function () {
                let url = $(this).data('url');
                Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true }).then((r) => {
                    if (r.isConfirmed) $.ajax({ url, type: 'DELETE', data: { _token: "{{ csrf_token() }}" }, success: () => table.ajax.reload(null, false) });
                });
            });

            $('#retailers-table').on('click', '.status-toggle', function () {
                let id = $(this).data('id'), status = $(this).data('status'), next = status === 'active' ? 'inactive' : 'active';
                
                // Frontend Permission Check
                const canManage = @json(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']));
                if (!canManage) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Permission Denied',
                        text: 'You do not have permission to change the status of retailers.',
                        confirmButtonColor: '#00497a'
                    });
                    return;
                }

                Swal.fire({
                    title: `Change Status to ${next.toUpperCase()}?`,
                    text: `Are you sure you want to ${next} this retailer?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#00497a',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, change it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = (next === 'active' ? "{{ route('admin.retailers.activate', ':id') }}" : "{{ route('admin.retailers.deactivate', ':id') }}").replace(':id', id);
                        $.post(url, { _token: "{{ csrf_token() }}", _method: 'PATCH' }, () => {
                            table.ajax.reload(null, false);
                            showToast('success', 'Status updated successfully');
                        }).fail((xhr) => {
                            console.error('Status Toggle Error:', xhr);
                            let msg = 'Error changing user status';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                try {
                                    let err = JSON.parse(xhr.responseText);
                                    if (err.message) msg = err.message;
                                } catch (e) {
                                    console.error('Error parsing responseText:', e);
                                }
                            }

                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Permission Denied',
                                    text: msg,
                                    confirmButtonColor: '#00497a'
                                });
                            } else {
                                alert('Permission Denied: ' + msg);
                            }
                        });
                    }
                });
            });

            // Dynamic Filtering for Sales Manager -> Field Staff (Create Modal)
            $('#create_sales_manager_id').on('change', function() {
                var managerId = $(this).val();
                var fieldStaffSelect = $('#create_field_staff_id');
                var options = fieldStaffSelect.find('option');

                if (!managerId) {
                    // If no manager selected, show all or none? 
                    // User said "after selecting the manager the fieldstaffs under him should fetch"
                    // So let's hide all except the default "Select Field Staff"
                    options.each(function() {
                        if ($(this).val() === "") $(this).show();
                        else $(this).hide();
                    });
                    fieldStaffSelect.val("");
                } else {
                    var firstVisible = null;
                    options.each(function() {
                        var optionManagerId = $(this).data('sales-manager-id');
                        if ($(this).val() === "") {
                            $(this).show();
                        } else if (optionManagerId == managerId) {
                            $(this).show();
                            if (!firstVisible) firstVisible = $(this).val();
                        } else {
                            $(this).hide();
                        }
                    });
                    fieldStaffSelect.val(""); // Reset selection
                }
            });

            // Initial trigger if needed
            $('#create_sales_manager_id').trigger('change');

            $('#userStatusTabs button').on('click', () => setTimeout(() => table.ajax.reload(), 50));

            // Modal events for Map resize
            $('.modal').on('shown.bs.modal', function() {
                if (createMap) google.maps.event.trigger(createMap, 'resize');
                if (editMap) google.maps.event.trigger(editMap, 'resize');
                if (showMap) google.maps.event.trigger(showMap, 'resize');
            });
        });
    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places,marker&v=weekly&loading=async&callback=initMap"
        async defer></script>
@endpush