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
                        <h5 class="mb-3"><i class="fa fa-users me-2"></i>Field Staff</h5>
                        <ul class="nav nav-tabs custom-tabs" id="userStatusTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-status="all" type="button">All Field Staff</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-status="active" type="button">Active</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-status="inactive" type="button">Inactive</button>
                            </li>
                        </ul>
                    </div>
                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('field_staff', 'add'))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createFieldStaffModal">
                            <i class="fa fa-plus me-1"></i>Add Field Staff
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
                            <table class="display table table-striped table-hover" id="fieldstaffs-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact No</th>
                                        <th>Sales Manager</th>
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
    <div class="modal fade" id="createFieldStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Field Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createFieldStaffForm" action="{{ route('admin.field-staffs.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" id="create_password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="create_password_confirmation"
                                    class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']))
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Sales Manager</label>
                                <select name="sales_manager_id" class="form-select" required>
                                    <option value="">Select Sales Manager</option>
                                    @foreach ($salesManagers as $sm)
                                        <option value="{{ $sm->id }}">{{ $sm->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <!-- Map Section -->
                        {{-- 
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Location</label>
                                <div class="input-group">
                                    <input id="create_pac-input" class="form-control" type="text"
                                        placeholder="Search for a location">
                                    <button type="button" class="btn btn-info"
                                        onclick="getGeoLocation('create_lat', 'create_long', 'create')"><i
                                            class="fa fa-map-marker"></i> Get Current Location</button>
                                </div>
                                <div id="create_map"
                                    style="height: 300px; width: 100%; margin-top: 10px; border-radius: 8px;"></div>
                            </div>
                        </div>
                        <input type="hidden" name="latitude" id="create_lat">
                        <input type="hidden" name="longitude" id="create_long">
                        --}}
                        <div class="row">
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
    <div class="modal fade" id="editFieldStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Field Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editFieldStaffForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password (Leave blank to keep unchanged)</label>
                                <input type="password" name="password" id="edit_password" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="edit_password_confirmation"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" id="edit_contact_no" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" id="edit_pincode" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <!-- Map Section -->
                        {{-- 
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Location</label>
                                <div class="input-group">
                                    <input id="edit_pac-input" class="form-control" type="text"
                                        placeholder="Search for a location">
                                    <button type="button" class="btn btn-info"
                                        onclick="getGeoLocation('edit_latitude', 'edit_longitude', 'edit')"><i
                                            class="fa fa-map-marker"></i> Get Current Location</button>
                                </div>
                                <div id="edit_map"
                                    style="height: 300px; width: 100%; margin-top: 10px; border-radius: 8px;"></div>
                            </div>
                        </div>
                        <input type="hidden" name="latitude" id="edit_latitude">
                        <input type="hidden" name="longitude" id="edit_longitude">
                        --}}
                        <div class="row">
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
    <div class="modal fade" id="showFieldStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, #1e3a5f, #2e6da4); border-radius: 0.5rem 0.5rem 0 0;">
                    <h5 class="modal-title text-white" style="color: #fff !important;"><i class="fa fa-user-circle me-2"
                            style="color: #fff !important;"></i>Field Staff Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Avatar + Name Header --}}
                    <div class="d-flex align-items-center gap-4 p-4"
                        style="background: var(--med-bg-body); border-bottom:1px solid var(--med-border);">
                        <div style="flex-shrink:0;">
                            <img id="fs_avatar_img" src="" alt="" class="rounded-circle shadow"
                                style="width:85px;height:85px;object-fit:cover;display:none;border:3px solid #fff;">
                            <div id="fs_avatar_initials"
                                style="width:85px;height:85px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                                font-size:1.9rem;font-weight:700;color:#fff;
                                                                background:linear-gradient(135deg,#1e3a5f,#2e6da4);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="mb-0 fw-bold" id="fs_view_name"></h4>
                                <span class="badge" id="fs_view_status"></span>
                            </div>
                            <div class="mt-1 text-muted small" id="fs_view_manager"></div>
                        </div>
                        <div class="text-end">
                            {{-- Badge moved to name part --}}
                        </div>
                    </div>
                    {{-- Assignments Section --}}
                    <div class="px-4">
                        <ul class="nav nav-tabs" id="fsModalTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold" id="fs-info-tab" data-bs-toggle="tab"
                                    data-bs-target="#fs-info-panel" type="button" role="tab">
                                    <i class="fa fa-info-circle me-1"></i>Basic Information
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold" id="fs-retailer-tab" data-bs-toggle="tab"
                                    data-bs-target="#fs-retailer-panel" type="button" role="tab">
                                    <i class="fa fa-store me-1"></i>Retailers (<span id="fsRetailerCount">0</span>)
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content border border-top-0 p-3" id="fsModalTabsContent"
                            style="border-radius: 0 0 0.5rem 0.5rem; background: var(--med-bg-body);">
                            <div class="tab-pane fade show active" id="fs-info-panel" role="tabpanel">
                                {{-- Info Cards --}}
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start gap-2 p-3 rounded"
                                            style="background: var(--med-bg-body);">
                                            <i class="fa fa-envelope mt-1 text-primary"></i>
                                            <div>
                                                <div class="text-muted small">Email</div>
                                                <div class="fw-semibold" id="fs_view_email"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start gap-2 p-3 rounded"
                                            style="background: var(--med-bg-body);">
                                            <i class="fa fa-phone mt-1 text-success"></i>
                                            <div>
                                                <div class="text-muted small">Contact</div>
                                                <div class="fw-semibold" id="fs_view_contact"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start gap-2 p-3 rounded"
                                            style="background: var(--med-bg-body);">
                                            <i class="fa fa-hashtag mt-1 text-dark"></i>
                                            <div>
                                                <div class="text-muted small">Pincode</div>
                                                <div class="fw-semibold" id="fs_view_pincode"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 text-dark">
                                        <div class="d-flex align-items-start gap-2 p-3 rounded"
                                            style="background: var(--med-bg-body);">
                                            <i class="fa fa-map-marker-alt mt-1 text-danger"></i>
                                            <div>
                                                <div class="text-muted small">Address</div>
                                                <div class="fw-semibold" id="fs_view_address"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- 
                                <hr class="my-4">
                                <h6 class="mb-3"><i class="fa fa-map-marker-alt me-2"></i>Location on Map</h6>
                                <div id="show_map"
                                    style="height:300px;width:100%;border-radius:12px;border:1px solid #eee;"></div>
                                --}}
                            </div>
                            <div class="tab-pane fade" id="fs-retailer-panel" role="tabpanel">
                                <div class="table-responsive" style="max-height: 400px;">
                                    <table class="table table-sm table-striped table-hover mb-0">
                                        <thead class="sticky-top" style="background: var(--med-bg-card);">
                                            <tr>
                                                <th style="color: var(--med-text-main) !important;">Shop Name</th>
                                                <th style="color: var(--med-text-main) !important;">Owner</th>
                                                <th style="color: var(--med-text-main) !important;">Contact</th>
                                                <th style="color: var(--med-text-main) !important;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="fs_view_retailers_body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0" style="background: var(--med-bg-body);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick View Modal for nested details --}}
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0">
                    <h6 class="modal-title fw-bold"><i class="fa fa-eye me-2 text-primary"></i>Quick View</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="quickViewContent">
                    {{-- Populated by JS --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        .pac-container {
            z-index: 10000 !important;
        }
        .modal-content {
        font-family: 'Montserrat', sans-serif !important;
    }
    .quick-card {
        background: var(--med-bg-body);
        border: 1px solid var(--med-border);
        border-radius: 16px;
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .quick-card:hover {
        border-color: var(--med-primary);
        box-shadow: var(--med-shadow-glow);
        transform: translateY(-2px);
    }
    .quick-card i {
        font-size: 1.1rem;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }
    .quick-card .label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--med-text-muted);
        margin-bottom: 2px;
    }
    .quick-card .value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--med-text-main);
        word-break: break-all;
    }
</style>
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
            const editMapDiv = document.getElementById("edit_map");
            const showMapDiv = document.getElementById("show_map");

            if (!createMapDiv && !editMapDiv && !showMapDiv) return;

            const defaultLoc = { lat: 20.5937, lng: 78.9629 };

            if (createMapDiv) {
                createMap = new google.maps.Map(createMapDiv, { center: defaultLoc, zoom: 12 });
                createMarker = new google.maps.Marker({ position: defaultLoc, map: createMap, draggable: true });
                google.maps.event.addListener(createMarker, 'dragend', function () {
                    $('#create_latitude').val(createMarker.getPosition().lat());
                    $('#create_longitude').val(createMarker.getPosition().lng());
                });
            }

            if (editMapDiv) {
                editMap = new google.maps.Map(editMapDiv, { center: defaultLoc, zoom: 12 });
                editMarker = new google.maps.Marker({ position: defaultLoc, map: editMap, draggable: true });
                google.maps.event.addListener(editMarker, 'dragend', function () {
                    $('#edit_latitude').val(editMarker.getPosition().lat());
                    $('#edit_longitude').val(editMarker.getPosition().lng());
                });
            }

            if (showMapDiv) {
                showMap = new google.maps.Map(showMapDiv, { center: defaultLoc, zoom: 12 });
                showMarker = new google.maps.Marker({ position: defaultLoc, map: showMap, draggable: false });
            }
        }
        window.initMap = initMap;

        function getGeoLocation(latId, longId, mapType) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    let lat = position.coords.latitude;
                    let lng = position.coords.longitude;
                    document.getElementById(latId).value = lat;
                    document.getElementById(longId).value = lng;
                    let pos = { lat: lat, lng: lng };

                    if (mapType === 'create' && createMap) {
                        createMarker.setPosition(pos);
                        createMap.setCenter(pos);
                        createMap.setZoom(15);
                    } else if (mapType === 'edit' && editMap) {
                        editMarker.setPosition(pos);
                        editMap.setCenter(pos);
                        editMap.setZoom(15);
                    }
                }, function (error) {
                    alert("Error getting location: " + error.message);
                });
            }
        }

        $(document).ready(function () {
            var table = $('#fieldstaffs-table').DataTable({
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: "{{ route('admin.field-staffs.index') }}",
                    data: function(d) {
                        d.status = $('#userStatusTabs button.active').data('status');
                    }
                },
                columns: [
                    { data: null, orderable: false, searchable: false, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'user.name', name: 'user.name' },
                    { data: 'user.email', name: 'user.email' },
                    { data: 'contact_no', name: 'contact_no', defaultContent: 'N/A' },
                    { data: 'sales_manager.user.name', name: 'salesManager.user.name', defaultContent: 'N/A' },
                    { data: 'pincode', name: 'pincode' },
                    { data: 'address', name: 'address' },
                    { 
                        data: 'user.status', name: 'user.status',
                        render: function (data, type, row) {
                            return `<span class="status-badge ${data === 'active' ? 'status-badge-active' : 'status-badge-inactive'} status-toggle" data-id="${row.id}" data-status="${data}">${data === 'active' ? 'Active' : 'Inactive'}</span>`;
                        }
                    },
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (id, type, row) {
                            let rowData = JSON.stringify(row).replace(/"/g, '&quot;');
                            let deleteUrl = "{{ route('admin.field-staffs.destroy', ':id') }}".replace(':id', id);
                            let btns = `<div class="action-buttons">
                                <button type="button" class="btn btn-sm btn-info view-btn" data-row="${rowData}"><i class="fa fa-eye"></i></button>`;
                            if (row.can_edit) btns += `<button type="button" class="btn btn-sm btn-primary edit-btn" data-row="${rowData}"><i class="fa fa-edit"></i></button>`;
                            if (row.can_delete) btns += `<button type="button" class="btn btn-sm btn-danger delete-btn" data-url="${deleteUrl}"><i class="fa fa-trash"></i></button>`;
                            btns += `</div>`;
                            return btns;
                        }
                    }
                ],
                dom: "<'row mb-3'<'col-sm-12'B>><'row mb-3'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end align-items-center'p>>",
                buttons: {
                    dom: { button: { className: 'btn btn-sm btn-icon' } },
                    buttons: [
                        { extend: 'copy', className: 'btn btn-secondary btn-sm', text: '<i class="fa fa-copy"></i> Copy' },
                        { extend: 'csv', className: 'btn btn-info btn-sm text-white', text: '<i class="fa fa-file-csv"></i> CSV' },
                        { extend: 'excel', className: 'btn btn-success btn-sm', text: '<i class="fa fa-file-excel"></i> Excel' },
                        { 
                            extend: 'pdf', className: 'btn btn-danger btn-sm', text: '<i class="fa fa-file-pdf"></i> PDF',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            customize: function (doc) {
                                doc.defaultStyle.fontSize = 8;
                                doc.styles.tableHeader.fontSize = 9;
                            }
                        },
                        { extend: 'print', className: 'btn btn-dark btn-sm', text: '<i class="fa fa-print"></i> Print', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] } }
                    ]
                }
            });

            $('#userStatusTabs button').on('click', function() {
                setTimeout(() => table.ajax.reload(), 50);
            });

            // Handle View Button
            $('#fieldstaffs-table').on('click', '.view-btn', function () {
                var data = $(this).data('row');
                var url = "{{ route('admin.field-staffs.show', ':id') }}".replace(':id', data.id);
                $('#showFieldStaffModal').modal('show');
                $.get(url, (response) => {
                    if (response.success) {
                        let fs = response.data;
                        // Avatar logic
                        if (fs.user?.avatar) {
                            $('#fs_avatar_img').attr('src', fs.user.avatar).show();
                            $('#fs_avatar_initials').hide();
                        } else {
                            $('#fs_avatar_img').hide();
                            let initials = fs.user?.name ? fs.user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '??';
                            $('#fs_avatar_initials').text(initials).show();
                        }

                        $('#fs_view_name').text(fs.user.name);
                        $('#fs_view_manager').html('<i class="fa fa-user-tie me-1"></i>Manager: ' + (fs.sales_manager?.user?.name || 'Not Assigned'));
                        $('#fs_view_status').attr('class', 'status-badge ' + (fs.user?.status === 'active' ? 'status-badge-active' : 'status-badge-inactive')).text(fs.user?.status);
                        $('#fs_view_email').text(fs.user.email);
                        $('#fs_view_contact').text(fs.contact_no || 'N/A');
                        $('#fs_view_pincode').text(fs.pincode || 'N/A');
                        $('#fs_view_address').text(fs.address || 'N/A');
                        
                        let retHtml = fs.retailers?.map(ret => {
                            let retData = JSON.stringify(ret).replace(/"/g, '&quot;');
                            let title = `Email: ${ret.user.email}&#10;Contact: ${ret.contact_no || 'N/A'}&#10;Points: ${ret.loyalty_points}`;
                            return `<tr>
                                <td>
                                    <a href="javascript:void(0)" class="fw-bold text-primary show-retailer-detail" data-retailer='${retData}' title="${title}">
                                        ${ret.shop_name}
                                    </a>
                                </td>
                                <td>${ret.user.name}</td>
                                <td>${ret.contact_no || 'N/A'}</td>
                                <td><span class="status-badge ${ret.user.status === 'active' ? 'status-badge-active' : 'status-badge-inactive'}">${ret.user.status}</span></td>
                            </tr>`;
                        }).join('') || '<tr><td colspan="4">None</td></tr>';
                        $('#fs_view_retailers_body').html(retHtml);
                        $('#fsRetailerCount').text(fs.retailers?.length || 0);

                        $('#showFieldStaffModal').data('lat', fs.latitude).data('lng', fs.longitude);
                    }
                });
            });

            // Handle clicking retailer name from within Field Staff modal
            $(document).on('click', '.show-retailer-detail', function() {
                let data = $(this).data('retailer');
                let initials = data.user?.name ? data.user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '??';
                let avatarHtml = data.user?.avatar ? `<img src="${data.user.avatar}" class="rounded-circle shadow-sm" style="width:60px;height:60px;object-fit:cover;">` : 
                                 `<div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width:60px;height:60px;background:linear-gradient(135deg,#1e3a5f,#2e6da4);font-size:1.4rem;">${initials}</div>`;

                let html = `
                    <div class="text-center mb-4">
                        <div class="d-inline-block position-relative mb-3">
                            ${avatarHtml}
                            <span class="position-absolute bottom-0 end-0 status-badge ${data.user?.status === 'active' ? 'status-badge-active' : 'status-badge-inactive'}" style="border: 2px solid var(--med-bg-card); transform: scale(0.8); transform-origin: bottom right;">
                                ${data.user?.status}
                            </span>
                        </div>
                        <h5 class="fw-bold mb-0 text-main" style="font-family: 'Montserrat', sans-serif;">${data.shop_name}</h5>
                        <div class="text-muted small">Owner: ${data.user?.name}</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="quick-card">
                                <div class="label"><i class="fa fa-envelope bg-primary-light text-primary me-1"></i>Email Address</div>
                                <div class="value">${data.user?.email}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-card">
                                <div class="label"><i class="fa fa-phone bg-success-light text-success me-1"></i>Contact No</div>
                                <div class="value">${data.contact_no || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-card">
                                <div class="label"><i class="fa fa-award bg-warning-light text-warning me-1"></i>Loyalty Points</div>
                                <div class="value text-warning">${parseFloat(data.loyalty_points || 0).toFixed(2)}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="quick-card">
                                <div class="label"><i class="fa fa-map-marker-alt bg-info-light text-info me-1"></i>Business Address</div>
                                <div class="value">${data.address || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                `;
                $('#quickViewContent').html(html);
                $('#quickViewModal').modal('show');
            });

            // Handle Edit Button
            $('#fieldstaffs-table').on('click', '.edit-btn', function () {
                var data = $(this).data('row');
                $('#edit_name').val(data.user.name);
                $('#edit_email').val(data.user.email);
                $('#edit_contact_no').val(data.contact_no || '');
                $('#edit_pincode').val(data.pincode || '');
                $('#edit_address').val(data.address || ''); // Populate address
                $('#edit_status').val(data.user.status);
                $('#edit_latitude').val(data.latitude);
                $('#edit_longitude').val(data.longitude);
                $('#editFieldStaffForm').attr('action', "{{ route('admin.field-staffs.update', ':id') }}".replace(':id', data.id));
                $('#editFieldStaffModal').modal('show');
            });

            // Handle Delete
            $('#fieldstaffs-table').on('click', '.delete-btn', function () {
                let url = $(this).data('url');
                Swal.fire({ title: 'Delete?', text: "Are you sure?", icon: 'warning', showCancelButton: true }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({ url: url, type: 'DELETE', data: { _token: "{{ csrf_token() }}" }, success: (res) => {
                            table.ajax.reload(null, false);
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
                            Swal.fire('Deleted!', res.message, 'success');
                        }});
                    }
                });
            });

            // Handle AJAX Forms
            $('#createFieldStaffForm, #editFieldStaffForm').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                btn.prop('disabled', true);
                $.ajax({
                    url: form.attr('action'), type: "POST", data: new FormData(this), processData: false, contentType: false,
                    success: (res) => {
                        $('.modal').modal('hide');
                        form[0].reset();
                        table.ajax.reload();
                        btn.prop('disabled', false);
                        if (window.updateSidebarCounts) window.updateSidebarCounts();
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

            // Map Resize on Modal Show
            $('#createFieldStaffModal, #editFieldStaffModal, #showFieldStaffModal').on('shown.bs.modal', function () {
                let m = (this.id === 'createFieldStaffModal') ? createMap : (this.id === 'editFieldStaffModal' ? editMap : showMap);
                if (m) {
                    google.maps.event.trigger(m, 'resize');
                    let lat = parseFloat($('#' + (this.id === 'showFieldStaffModal' ? 'showFieldStaffModal' : (this.id === 'editFieldStaffModal' ? 'edit_latitude' : 'create_latitude'))).val() || $(this).data('lat'));
                    let lng = parseFloat($('#' + (this.id === 'showFieldStaffModal' ? 'showFieldStaffModal' : (this.id === 'editFieldStaffModal' ? 'edit_longitude' : 'create_longitude'))).val() || $(this).data('lng'));
                    if (lat && lng) m.setCenter({lat, lng});
                }
            });

            $('#fieldstaffs-table').on('click', '.status-toggle', function () {
                let id = $(this).data('id'), status = $(this).data('status'), next = status === 'active' ? 'inactive' : 'active';
                
                // Frontend Permission Check
                const canManage = @json(Auth::user()->hasAnyRole(['admin', 'superadmin']));
                if (!canManage) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Permission Denied',
                        text: 'You do not have permission to change the status of field staffs.',
                        confirmButtonColor: '#00497a'
                    });
                    return;
                }

                Swal.fire({
                    title: `Change Status to ${next.toUpperCase()}?`,
                    text: `Are you sure you want to ${next} this field staff?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#00497a',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, change it!'
                }).then((r) => {
                    if (r.isConfirmed) {
                        let url = (next === 'active' ? "{{ route('admin.field-staffs.activate', ':id') }}" : "{{ route('admin.field-staffs.deactivate', ':id') }}").replace(':id', id);
                        $.post(url, { _token: "{{ csrf_token() }}", _method: 'PATCH' }, () => {
                            table.ajax.reload(null, false);
                            if (window.updateSidebarCounts) window.updateSidebarCounts();
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
        });
    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places,marker&v=weekly&loading=async&callback=initMap"
        async defer></script>
@endpush