@extends('layouts.admin')

<style>
    /* Flex wrapper for actions */
    .action-buttons {
        display: inline-flex !important;
        align-items: center;
        gap: 4px;
        flex-wrap: nowrap;
    }

    /* Make every child inline-flex (buttons + forms) */
    .action-buttons>* {
        display: inline-flex !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Normalize button sizes */
    .action-buttons .btn {
        padding: 6px 12px !important;
        font-size: 0.75rem !important;
        line-height: 1 !important;
    }
</style>

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fa fa-users me-2"></i>Field Staff</h5>
                    <button type="button" class="-btn -btn-primary button-64" data-bs-toggle="modal" data-bs-target="#createFieldStaffModal">
                        <span class="text"><i class="fa fa-plus me-1"></i>Add Field Staff</span>
                    </button>
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
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact No</th>
                                    <th>Sales Manager</th>
                                    <th>Pincode</th>
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
                            <input type="password" name="password_confirmation" id="create_password_confirmation" class="form-control" required>
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
                            <textarea name="address" class="form-control"></textarea>
                        </div>
                    </div>
                    <!-- Map Section -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Location</label>
                            <div class="input-group">
                                <input id="create_pac-input" class="form-control" type="text" placeholder="Search for a location">
                                <button type="button" class="btn btn-info" onclick="getGeoLocation('create_lat', 'create_long', 'create')"><i class="fa fa-map-marker"></i> Get Current Location</button>
                            </div>
                            <div id="create_map" style="height: 300px; width: 100%; margin-top: 10px; border-radius: 8px;"></div>
                        </div>
                    </div>
                    <input type="hidden" name="latitude" id="create_lat">
                    <input type="hidden" name="longitude" id="create_long">
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
                            <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact No</label>
                            <input type="text" name="contact_no" id="edit_contact_no" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" id="edit_pincode" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="edit_address" class="form-control"></textarea>
                        </div>
                    </div>
                    <!-- Map Section -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Location</label>
                            <div class="input-group">
                                <input id="edit_pac-input" class="form-control" type="text" placeholder="Search for a location">
                                <button type="button" class="btn btn-info" onclick="getGeoLocation('edit_latitude', 'edit_longitude', 'edit')"><i class="fa fa-map-marker"></i> Get Current Location</button>
                            </div>
                            <div id="edit_map" style="height: 300px; width: 100%; margin-top: 10px; border-radius: 8px;"></div>
                        </div>
                    </div>
                    <input type="hidden" name="latitude" id="edit_latitude">
                    <input type="hidden" name="longitude" id="edit_longitude">
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Field Staff Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="showFieldStaffDetails">
                    <!-- Dynamic Details -->
                </div>
                <hr class="my-4">
                <h6 class="mb-3"><i class="fa fa-map-marker-alt me-2"></i>Location on Map</h6>
                <div id="show_map" style="height: 350px; width: 100%; border-radius: 12px; border: 1px solid #eee;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    let createMap, editMap, showMap;
    let createMarker, editMarker, showMarker;

    function initMap() {
        const defaultLoc = {
            lat: 20.5937,
            lng: 78.9629
        }; // India Center

        // Create Map
        createMap = new google.maps.Map(document.getElementById("create_map"), {
            zoom: 5,
            center: defaultLoc,
            mapId: "DEMO_MAP_ID",
        });
        createMarker = new google.maps.marker.AdvancedMarkerElement({
            position: defaultLoc,
            map: createMap,
            gmpDraggable: true,
        });
        createMarker.addListener("dragend", () => {
            const pos = createMarker.position;
            let lat = (typeof pos.lat === 'function') ? pos.lat() : pos.lat;
            let lng = (typeof pos.lng === 'function') ? pos.lng() : pos.lng;
            document.getElementById("create_lat").value = lat;
            document.getElementById("create_long").value = lng;
        });
        createMap.addListener("click", (e) => {
            createMarker.position = e.latLng;
            document.getElementById("create_lat").value = e.latLng.lat();
            document.getElementById("create_long").value = e.latLng.lng();
        });

        // Create Autocomplete
        const createInput = document.getElementById("create_pac-input");
        const createAutocomplete = new google.maps.places.Autocomplete(createInput);
        createAutocomplete.bindTo("bounds", createMap);
        createAutocomplete.addListener("place_changed", () => {
            const place = createAutocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;
            if (place.geometry.viewport) createMap.fitBounds(place.geometry.viewport);
            else {
                createMap.setCenter(place.geometry.location);
                createMap.setZoom(17);
            }
            createMarker.position = place.geometry.location;
            document.getElementById("create_lat").value = place.geometry.location.lat();
            document.getElementById("create_long").value = place.geometry.location.lng();
        });

        // Edit Map
        editMap = new google.maps.Map(document.getElementById("edit_map"), {
            zoom: 5,
            center: defaultLoc,
            mapId: "DEMO_MAP_ID",
        });
        editMarker = new google.maps.marker.AdvancedMarkerElement({
            position: defaultLoc,
            map: editMap,
            gmpDraggable: true,
        });
        editMarker.addListener("dragend", (event) => {
            const pos = editMarker.position;
            let lat = (typeof pos.lat === 'function') ? pos.lat() : pos.lat;
            let lng = (typeof pos.lng === 'function') ? pos.lng() : pos.lng;
            document.getElementById("edit_latitude").value = lat;
            document.getElementById("edit_longitude").value = lng;
        });
        editMap.addListener("click", (e) => {
            editMarker.position = e.latLng;
            document.getElementById("edit_latitude").value = e.latLng.lat();
            document.getElementById("edit_longitude").value = e.latLng.lng();
        });

        // Edit Autocomplete
        const editInput = document.getElementById("edit_pac-input");
        const editAutocomplete = new google.maps.places.Autocomplete(editInput);
        editAutocomplete.bindTo("bounds", editMap);
        editAutocomplete.addListener("place_changed", () => {
            const place = editAutocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;
            if (place.geometry.viewport) editMap.fitBounds(place.geometry.viewport);
            else {
                editMap.setCenter(place.geometry.location);
                editMap.setZoom(17);
            }
            editMarker.position = place.geometry.location;
            document.getElementById("edit_latitude").value = place.geometry.location.lat();
            document.getElementById("edit_longitude").value = place.geometry.location.lng();
        });

        // Show Map
        showMap = new google.maps.Map(document.getElementById("show_map"), {
            zoom: 5,
            center: defaultLoc,
            mapId: "DEMO_MAP_ID",
        });
        showMarker = new google.maps.marker.AdvancedMarkerElement({
            position: defaultLoc,
            map: showMap,
        });
    }

    // Expose initMap
    window.initMap = initMap;

    function getGeoLocation(latId, longId, mapType) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                let lat = position.coords.latitude;
                let lng = position.coords.longitude;
                document.getElementById(latId).value = lat;
                document.getElementById(longId).value = lng;
                let pos = {
                    lat: lat,
                    lng: lng
                };

                if (mapType === 'create' && createMap) {
                    createMarker.position = pos;
                    createMap.setCenter(pos);
                    createMap.setZoom(15);
                } else if (mapType === 'edit' && editMap) {
                    editMarker.position = pos;
                    editMap.setCenter(pos);
                    editMap.setZoom(15);
                }
            }, function(error) {
                alert("Error getting location: " + error.message);
            });
        } else {
            alert("Geolocation is not supported.");
        }
    }

    $(document).ready(function() {
        var table = $('#fieldstaffs-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.field-staffs.index') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'user.name',
                    name: 'user.name'
                },
                {
                    data: 'user.email',
                    name: 'user.email'
                },
                {
                    data: 'user.contact_no',
                    name: 'user.contact_no',
                    defaultContent: 'N/A'
                },
                {
                    data: 'sales_manager.user.name',
                    name: 'salesManager.user.name',
                    defaultContent: 'N/A'
                },
                {
                    data: 'pincode',
                    name: 'pincode'
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(id, type, row) {
                        let deleteUrl = "{{ route('admin.field-staffs.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');

                        return `
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm btn-info view-btn" data-row="${rowData}"><i class="fa fa-eye"></i></button>
                            <button type="button" class="btn btn-sm btn-primary edit-btn" data-row="${rowData}"><i class="fa fa-edit"></i></button>
                            <form action="${deleteUrl}" method="POST" class="delete-form" onsubmit="return false;">
                                <input type="hidden" name="_token" value="${csrf}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </div>
                    `;
                    }
                }
            ],
            dom: "<'row mb-3'<'col-sm-12'B>>" +
                "<'row mb-3 d-flex align-items-center'<'col-md-6'f><'col-md-6'l>>" +
                "rtip",
            buttons: {
                dom: {
                    button: {
                        className: ''
                    }
                },
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-sm btn-primary'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-secondary'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm btn-success'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm btn-danger'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-info'
                    }
                ]
            }
        });

        // Handle Edit Button
        $('#fieldstaffs-table').on('click', '.edit-btn', function() {
            var data = $(this).data('row');

            $('#edit_name').val(data.user.name);
            $('#edit_email').val(data.user.email);
            $('#edit_contact_no').val(data.user.contact_no);
            $('#edit_address').val(data.user.address);
            $('#edit_pincode').val(data.pincode);
            $('#edit_latitude').val(data.latitude);
            $('#edit_longitude').val(data.longitude);

            var url = "{{ route('admin.field-staffs.update', ':id') }}".replace(':id', data.id);
            $('#editFieldStaffForm').attr('action', url);

            $('#editFieldStaffModal').modal('show');
        });

        // Handle View Button
        $('#fieldstaffs-table').on('click', '.view-btn', function() {
            var data = $(this).data('row');
            let smName = data.sales_manager && data.sales_manager.user ? data.sales_manager.user.name : 'N/A';
            let html = `
                <div class="col-md-6"><label class="fw-bold text-muted small text-uppercase">Name</label><p class="fw-bold mb-0">${data.user.name}</p></div>
                <div class="col-md-6"><label class="fw-bold text-muted small text-uppercase">Email</label><p class="mb-0">${data.user.email}</p></div>
                <div class="col-md-6"><label class="fw-bold text-muted small text-uppercase">Contact</label><p class="mb-0">${data.user.contact_no || 'N/A'}</p></div>
                <div class="col-md-6"><label class="fw-bold text-muted small text-uppercase">Sales Manager</label><p class="mb-0">${smName}</p></div>
                <div class="col-md-6"><label class="fw-bold text-muted small text-uppercase">Pincode</label><p class="mb-0">${data.pincode}</p></div>
                <div class="col-12"><label class="fw-bold text-muted small text-uppercase">Address</label><p class="mb-0">${data.user.address || 'N/A'}</p></div>
            `;
            $('#showFieldStaffDetails').html(html);
            $('#showFieldStaffModal').data('lat', data.latitude).data('lng', data.longitude);
            $('#showFieldStaffModal').modal('show');
        });

        // Handle Delete
        $('#fieldstaffs-table').on('click', '.delete-form button[type="submit"]', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Field Staff?',
                text: "Are you sure?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) form.off('submit').submit();
            });
        });

        // Handle Create Field Staff AJAX Submission
        $('#createFieldStaffForm').on('submit', function(e) {
            e.preventDefault();

            // JS Password Validation
            let password = $('#create_password').val();
            let confirmPassword = $('#create_password_confirmation').val();

            if (password !== confirmPassword) {
                showToast('danger', 'Passwords do not match!');
                return false;
            }

            let formData = new FormData(this);
            let submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Creating...');

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#createFieldStaffModal').modal('hide');
                    $('#createFieldStaffForm')[0].reset();
                    $('#fieldstaffs-table').DataTable().ajax.reload();
                    submitBtn.prop('disabled', false).text('Create');
                    showToast('success', response.message);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).text('Create');
                    let errors = xhr.responseJSON.errors;
                    let errorMessage = '';
                    if (errors) {
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '\n';
                        });
                    } else {
                        errorMessage = 'An error occurred. Please try again.';
                    }
                    showToast('danger', errorMessage);
                }
            });
        });

        // Handle Edit Field Staff Validation
        $('#editFieldStaffForm').on('submit', function(e) {
            let password = $('#edit_password').val();
            let confirmPassword = $('#edit_password_confirmation').val();

            if (password && password !== confirmPassword) {
                e.preventDefault();
                showToast('danger', 'Passwords do not match!');
                return false;
            }
        });

        // Modal Show Events for Map Resize
        $('#createFieldStaffModal').on('shown.bs.modal', function() {
            if (createMap) {
                google.maps.event.trigger(createMap, 'resize');
                createMap.setCenter(createMarker.position);
            }
        });
        $('#editFieldStaffModal').on('shown.bs.modal', function() {
            if (editMap) {
                google.maps.event.trigger(editMap, 'resize');
                let lat = parseFloat($('#edit_latitude').val());
                let lng = parseFloat($('#edit_longitude').val());
                if (lat && lng) {
                    let pos = {
                        lat: lat,
                        lng: lng
                    };
                    editMarker.position = pos;
                    editMap.setCenter(pos);
                    editMap.setZoom(15);
                } else {
                    editMap.setCenter(editMarker.position);
                }
            }
        });
        $('#showFieldStaffModal').on('shown.bs.modal', function() {
            if (showMap) {
                google.maps.event.trigger(showMap, 'resize');
                let lat = parseFloat($(this).data('lat'));
                let lng = parseFloat($(this).data('lng'));
                if (!isNaN(lat) && !isNaN(lng)) {
                    let pos = {
                        lat: lat,
                        lng: lng
                    };
                    showMarker.position = pos;
                    showMap.setCenter(pos);
                    showMap.setZoom(15);
                } else {
                    const defaultLoc = {
                        lat: 20.5937,
                        lng: 78.9629
                    };
                    showMap.setCenter(defaultLoc);
                    showMap.setZoom(5);
                    showMarker.position = defaultLoc;
                }
            }
        });
    });
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places,marker&v=weekly&loading=async&callback=initMap" async defer></script>
@endpush