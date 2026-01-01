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
                    <h5><i class="fa fa-users me-2"></i>Retailers</h5>
                    <button type="button" class="-btn -btn-primary button-64" data-bs-toggle="modal" data-bs-target="#createRetailerModal">
                        <span class="text"><i class="fa fa-plus me-1"></i>Add Retailer</span>
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
                        <table class="display table table-striped table-hover" id="retailers-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>GST</th>
                                    <th>Distributor</th>
                                    <th>Sales Manager</th>
                                    <th>Field Staff</th>
                                    <th>Contact No</th>
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
<div class="modal fade" id="createRetailerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Retailer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.retailers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
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
                            <input type="password" name="password_confirmation" id="create_password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact No</label>
                            <input type="text" name="contact_no" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GST</label>
                            <input type="text" name="gst" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label">Location</label>
                            <div class="input-group">
                                <input id="create_pac-input" class="form-control" type="text" placeholder="Search for a location" autocomplete="off">
                                <button type="button" class="btn btn-info" onclick="getGeoLocation('create_lat', 'create_long', 'create')"><i class="fa fa-map-marker"></i> Get Current Location</button>
                            </div>
                            <div id="create_map" style="height: 300px; width: 100%; margin-top: 10px; border-radius: 8px;"></div>
                            <input type="hidden" name="latitude" id="create_lat">
                            <input type="hidden" name="longitude" id="create_long">
                        </div>
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
<div class="modal fade" id="editRetailerModal" tabindex="-1" aria-hidden="true">
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
                            <label class="form-label">Name</label>
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
                            <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact No</label>
                            <input type="text" name="contact_no" id="edit_contact_no" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GST</label>
                            <input type="text" name="gst" id="edit_gst" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" id="edit_pincode" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Distributor</label>
                            <select name="distributor_id" id="edit_distributor_id" class="form-select">
                                <option value="">Select Distributor</option>
                                @foreach($distributors as $distributor)
                                <option value="{{ $distributor->id }}">{{ $distributor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sales Manager</label>
                            <select name="sales_manager_id" id="edit_sales_manager_id" class="form-select">
                                <option value="">Select Sales Manager</option>
                                @foreach($salesManagers as $salesManager)
                                <option value="{{ $salesManager->id }}">{{ $salesManager->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Field Staff</label>
                            <select name="field_staff_id" id="edit_field_staff_id" class="form-select">
                                <option value="">Select Field Staff</option>
                                @foreach($fieldStaffs as $fieldStaff)
                                <option value="{{ $fieldStaff->id }}">{{ $fieldStaff->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label">Location</label>
                            <div class="input-group">
                                <input id="edit_pac-input" class="form-control" type="text" placeholder="Search for a location" autocomplete="off">
                                <button type="button" class="btn btn-info" onclick="getGeoLocation('edit_latitude', 'edit_longitude', 'edit')"><i class="fa fa-map-marker"></i> Get Current Location</button>
                            </div>
                            <div id="edit_map" style="height: 300px; width: 100%; margin-top: 10px; border-radius: 8px;"></div>
                            <input type="hidden" name="latitude" id="edit_latitude">
                            <input type="hidden" name="longitude" id="edit_longitude">
                        </div>
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
<div class="modal fade" id="showRetailerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Retailer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody id="showRetailerBody">
                    </tbody>
                </table>
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
        z-index: 2000 !important;
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
            document.getElementById("create_lat").value = pos.lat;
            document.getElementById("create_long").value = pos.lng;
        });
        createMap.addListener("click", (e) => {
            createMarker.position = e.latLng;
            document.getElementById("create_lat").value = e.latLng.lat();
            document.getElementById("create_long").value = e.latLng.lng();
        });

        // Create Autocomplete
        const createInput = document.getElementById("create_pac-input");
        const createAutocomplete = new google.maps.places.Autocomplete(createInput, {
            fields: ["geometry", "name", "formatted_address"],
            types: ["geocode", "establishment"]
        });
        createAutocomplete.bindTo("bounds", createMap);
        createAutocomplete.addListener("place_changed", () => {
            const place = createAutocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;
            if (place.geometry.viewport) {
                createMap.fitBounds(place.geometry.viewport);
            } else {
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
        editMarker.addListener("dragend", () => {
            const pos = editMarker.position;
            document.getElementById("edit_latitude").value = pos.lat;
            document.getElementById("edit_longitude").value = pos.lng;
        });
        editMap.addListener("click", (e) => {
            editMarker.position = e.latLng;
            document.getElementById("edit_latitude").value = e.latLng.lat();
            document.getElementById("edit_longitude").value = e.latLng.lng();
        });

        // Edit Autocomplete
        const editInput = document.getElementById("edit_pac-input");
        const editAutocomplete = new google.maps.places.Autocomplete(editInput, {
            fields: ["geometry", "name", "formatted_address"],
            types: ["geocode", "establishment"]
        });
        editAutocomplete.bindTo("bounds", editMap);
        editAutocomplete.addListener("place_changed", () => {
            const place = editAutocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;
            if (place.geometry.viewport) {
                editMap.fitBounds(place.geometry.viewport);
            } else {
                editMap.setCenter(place.geometry.location);
                editMap.setZoom(17);
            }
            editMarker.position = place.geometry.location;
            document.getElementById("edit_latitude").value = place.geometry.location.lat();
            document.getElementById("edit_longitude").value = place.geometry.location.lng();
        });
    }
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
                showToast('danger', "Error getting location: " + error.message);
            });
        } else {
            showToast('danger', "Geolocation is not supported by this browser.");
        }
    }

    $(document).ready(function() {
        var table = $('#retailers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.retailers.index') }}",
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
                    data: 'gst',
                    name: 'gst'
                },
                {
                    data: 'distributor.user.name',
                    name: 'distributor.user.name',
                    defaultContent: 'Direct'
                },
                {
                    data: 'sales_manager.user.name',
                    name: 'sales_manager.user.name',
                    defaultContent: 'N/A'
                },
                {
                    data: 'field_staff.user.name',
                    name: 'field_staff.user.name',
                    defaultContent: 'N/A'
                },
                {
                    data: 'contact_no',
                    name: 'contact_no',
                    defaultContent: 'N/A'
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(id, type, row) {
                        let deleteUrl = "{{ route('admin.retailers.destroy', ':id') }}".replace(':id', id);
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
            dom: "<'row mb-3'<'col-sm-12'B>>" + "<'row mb-3 d-flex align-items-center'<'col-md-6'f><'col-md-6'l>>" + "rtip",
            buttons: {
                dom: {
                    button: {
                        className: ''
                    }
                },
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-primary btn-sm'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-secondary'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm'
                    }
                ]
            }
        });

        // Handle Create Retailer AJAX Submission
        $('#createRetailerForm').on('submit', function(e) {
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
                    $('#createRetailerModal').modal('hide');
                    $('#createRetailerForm')[0].reset();
                    $('#retailers-table').DataTable().ajax.reload();
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

        // Handle Edit Retailer Validation
        $('#editRetailerForm').on('submit', function(e) {
            let password = $('#edit_password').val();
            let confirmPassword = $('#edit_password_confirmation').val();
            if (password && password !== confirmPassword) {
                e.preventDefault();
                showToast('danger', 'Passwords do not match!');
                return false;
            }
        });

        // Handle Edit
        $('#retailers-table').on('click', '.edit-btn', function() {
            var data = $(this).data('row');
            $('#edit_name').val(data.user.name);
            $('#edit_email').val(data.user.email);
            $('#edit_gst').val(data.gst);
            $('#edit_contact_no').val(data.contact_no);
            $('#edit_pincode').val(data.pincode);
            $('#edit_address').val(data.address);
            $('#edit_latitude').val(data.latitude);
            $('#edit_longitude').val(data.longitude);
            $('#edit_distributor_id').val(data.distributor_id);
            $('#edit_sales_manager_id').val(data.sales_manager_id);
            $('#edit_field_staff_id').val(data.field_staff_id);
            var url = "{{ route('admin.retailers.update', ':id') }}".replace(':id', data.id);
            $('#editRetailerForm').attr('action', url);
            $('#editRetailerModal').modal('show');
        });

        // Handle View
        $('#retailers-table').on('click', '.view-btn', function() {
            var data = $(this).data('row');
            let distName = data.distributor && data.distributor.user ? data.distributor.user.name : 'N/A';
            let smName = data.sales_manager && data.sales_manager.user ? data.sales_manager.user.name : 'N/A';
            let fsName = data.field_staff && data.field_staff.user ? data.field_staff.user.name : 'N/A';
            let html = `
            <tr>
                <th>Name</th>
                <td>${data.user.name}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>${data.user.email}</td>
            </tr>
            <tr>
                <th>GST</th>
                <td>${data.gst}</td>
            </tr>
            <tr>
                <th>Contact No</th>
                <td>${data.contact_no || 'N/A'}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>${data.address || 'N/A'}</td>
            </tr>
            <tr>
                <th>Pincode</th>
                <td>${data.pincode}</td>
            </tr>
            <tr>
                <th>Distributor</th>
                <td>${distName}</td>
            </tr>
            <tr>
                <th>Sales Manager</th>
                <td>${smName}</td>
            </tr>
            <tr>
                <th>Field Staff</th>
                <td>${fsName}</td>
            </tr>
            `;
            $('#showRetailerBody').html(html);
            $('#showRetailerModal').modal('show');
        });

        // Handle Delete
        $('#retailers-table').on('click', '.delete-form button[type="submit"]', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Retailer?',
                text: "Are you sure?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) form.off('submit').submit();
            });
        });

        // Modal Show Events for Map Resize
        $('#createRetailerModal').on('shown.bs.modal', function() {
            if (createMap) {
                google.maps.event.trigger(createMap, 'resize');
                createMap.setCenter(createMarker.position);
            }
        });
        $('#editRetailerModal').on('shown.bs.modal', function() {
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
    });
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places,marker&v=weekly&loading=async&callback=initMap" async defer></script>
@endpush