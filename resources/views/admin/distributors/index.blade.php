@extends('layouts.admin')

<style>
    .dataTables_filter {
        text-align: right !important;
    }

    .dataTables_filter input {
        width: 230px !important;
        margin-left: 10px !important;
    }

    .dataTables_length {
        text-align: left !important;
    }

    .dataTables_length select {
        padding: 5px 10px !important;
        padding-right: 30px !important;
        display: inline-block !important;
        width: auto !important;
    }

    /* Flex wrapper for actions */
    .action-buttons {
        display: inline-flex !important;
        align-items: center;
        gap: 4px;
        flex-wrap: nowrap;
    }



    .action-buttons>* {
        display: inline-flex !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .action-buttons .btn {
        padding: 6px 12px !important;
        font-size: 0.75rem !important;
        line-height: 1 !important;
    }

    .pac-container {
        z-index: 10000 !important;
    }
</style>

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fa fa-users me-2"></i>Distributors</h5>
                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('distributors', 'add'))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createDistributorModal">
                            <i class="fa fa-plus me-1"></i>Add Distributor
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
                            <table class="display table table-striped table-hover" id="distributors-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Manager</th>
                                        <th>Email</th>
                                        <th>GST</th>
                                        <th>Drug Lic.</th>
                                        <th>Contact</th>
                                        <th>District</th>
                                        <th>Area</th>
                                        <th>Pincode</th>
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
    <div class="modal fade" id="createDistributorModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Distributor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createDistributorForm" action="{{ route('admin.distributors.store') }}" method="POST">
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
                                <input type="password" name="password_confirmation" id="create_password_confirmation"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST</label>
                                <input type="text" name="gst" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Drug License No</label>
                                <input type="text" name="drug_license_no" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sales Manager</label>
                                <select name="sales_manager_id" class="form-select">
                                    <option value="">Select Sales Manager</option>
                                    @foreach($salesManagers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->user->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
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
                            <div class="col-md-6">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control" required>
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
    <div class="modal fade" id="editDistributorModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Distributor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editDistributorForm" method="POST">
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
                                <input type="password" name="password_confirmation" id="edit_password_confirmation"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST</label>
                                <input type="text" name="gst" id="edit_gst" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Drug License No</label>
                                <input type="text" name="drug_license_no" id="edit_drug_license_no" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" id="edit_contact_no" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sales Manager</label>
                                <select name="sales_manager_id" id="edit_sales_manager_id" class="form-select">
                                    <option value="">Select Sales Manager</option>
                                    @foreach($salesManagers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->user->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
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
    <div class="modal fade" id="showDistributorModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, #1e3a5f, #2e6da4); border-radius: 0.5rem 0.5rem 0 0;">
                    <h5 class="modal-title text-white" style="color: #fff !important;"><i class="fa fa-user-circle me-2"
                            style="color: #fff !important;"></i>Distributor Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Avatar + Name Header --}}
                    <div class="d-flex align-items-center gap-4 p-4"
                        style="background: var(--med-bg-body); border-bottom:1px solid var(--med-border);">
                        <div style="flex-shrink:0;">
                            <img id="dist_avatar_img" src="" alt="" class="rounded-circle shadow"
                                style="width:85px;height:85px;object-fit:cover;display:none;border:3px solid #fff;">
                            <div id="dist_avatar_initials"
                                style="width:85px;height:85px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                                    font-size:1.9rem;font-weight:700;color:#fff;
                                                                    background:linear-gradient(135deg,#1e3a5f,#2e6da4);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            </div>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold" id="dist_view_name"></h4>
                            <div class="mb-1 text-muted small" id="dist_view_manager"></div>
                            <span class="badge" id="dist_view_status"></span>
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
                                        <div class="fw-semibold" id="dist_view_email"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-phone mt-1 text-success"></i>
                                    <div>
                                        <div class="text-muted small">Contact</div>
                                        <div class="fw-semibold" id="dist_view_contact"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-file-alt mt-1 text-warning"></i>
                                    <div>
                                        <div class="text-muted small">GST</div>
                                        <div class="fw-semibold" id="dist_view_gst"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-id-card mt-1 text-info"></i>
                                    <div>
                                        <div class="text-muted small">Drug License</div>
                                        <div class="fw-semibold" id="dist_view_drug"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-map mt-1 text-secondary"></i>
                                    <div>
                                        <div class="text-muted small">District / Area</div>
                                        <div class="fw-semibold" id="dist_view_location"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-hashtag mt-1 text-dark"></i>
                                    <div>
                                        <div class="text-muted small">Pincode</div>
                                        <div class="fw-semibold" id="dist_view_pincode"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-map-marker-alt mt-1 text-danger"></i>
                                    <div>
                                        <div class="text-muted small">Address</div>
                                        <div class="fw-semibold" id="dist_view_address"></div>
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
        $(document).ready(function () {
            const canActivate = @json(Auth::user()->hasRole(['superadmin']));

            var table = $('#distributors-table').DataTable({
                processing: true,
                serverSide: true,
                order: [],
                ajax: "{{ route('admin.distributors.index') }}",
                columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'sales_manager',
                    name: 'salesManager.user.name',
                    orderable: false,
                    render: function (data, type, row) {
                        return data && data.user ? data.user.name : '<span class="text-muted small">Not Assigned</span>';
                    }
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
                    data: 'drug_license_no',
                    name: 'drug_license_no'
                },
                {
                    data: 'contact_no',
                    name: 'contact_no'
                },
                {
                    data: 'district.name',
                    name: 'district.name'
                },
                {
                    data: 'area.name',
                    name: 'area.name'
                },
                {
                    data: 'pincode',
                    name: 'pincode'
                },
                {
                    data: 'user.status',
                    name: 'user.status',
                    render: function (data, type, row) {
                        if (data === 'active') {
                            return `<span class="badge bg-success status-toggle cursor-pointer" style="cursor: pointer;" data-id="${row.id}" data-status="active" title="Click to deactivate">Active</span>`;
                        } else {
                            return `<span class="badge bg-danger status-toggle cursor-pointer" style="cursor: pointer;" data-id="${row.id}" data-status="inactive" title="Click to activate">Inactive</span>`;
                        }
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (id, type, row) {
                        let deleteUrl = "{{ route('admin.distributors.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');

                        /*
                        let activateBtn = '';
                        if (canActivate && row.user.status === 'inactive') {
                            activateBtn = `
                                <button class="btn btn-sm btn-success activate-btn" 
                                        data-id="${id}"
                                        title="Activate">
                                    <i class="fa fa-check"></i>
                                </button>`;
                        }
                        */
                        let activateBtn = '';

                        let btns = `<div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-info view-btn" data-row="${rowData}"><i class="fa fa-eye"></i></button>`;
                        if (row.can_edit) {
                            btns += `<button type="button" class="btn btn-sm btn-primary edit-btn" data-row="${rowData}"><i class="fa fa-edit"></i></button>`;
                        }
                        if (row.can_delete) {
                            btns += `<button type="button" class="btn btn-sm btn-danger delete-btn" data-url="${deleteUrl}"><i class="fa fa-trash"></i></button>`;
                        }
                        btns += `</div>`;
                        return btns;
                    }
                }
                ],
                dom: "<'row mb-3'<'col-sm-12'B>>" +
                    "<'row mb-3'<'col-md-6'l><'col-md-6'f>>" +
                    "<'row '<'col-sm-12'tr>>" +
                    "<'row mt-3 '<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end align-items-center'p>>",
                buttons: {
                    dom: {
                        button: {
                            className: 'btn btn-sm btn-icon'
                        }
                    },
                    buttons: [{
                        extend: 'copy',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fa fa-copy"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-info btn-sm text-white',
                        text: '<i class="fa fa-file-csv"></i> CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fa fa-file-excel"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fa fa-file-pdf"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-dark btn-sm',
                        text: '<i class="fa fa-print"></i> Print'
                    }
                    ]
                }
            });

            // Handle District Change for Create
            $('.district-select').on('change', function () {
                let container = $(this).closest('form');
                let areaSelect = container.find('.area-select');
                if (typeof fetchAreas === 'function') {
                    fetchAreas($(this).val(), areaSelect);
                }
            });

            // Handle Edit
            $('#distributors-table').on('click', '.edit-btn', function () {
                var data = $(this).data('row');

                $('#edit_name').val(data.name);
                $('#edit_email').val(data.user.email);
                $('#edit_gst').val(data.gst);
                $('#edit_drug_license_no').val(data.drug_license_no);
                $('#edit_contact_no').val(data.contact_no);
                $('#edit_pincode').val(data.pincode);
                $('#edit_address').val(data.address);
                $('#edit_latitude').val(data.latitude);
                $('#edit_longitude').val(data.longitude);
                $('#edit_district_id').val(data.district_id);
                $('#edit_sales_manager_id').val(data.sales_manager_id || '');
                if (data.user) {
                    $('#edit_status').val(data.user.status);
                }

                if (typeof fetchAreas === 'function') {
                    fetchAreas(data.district_id, $('#edit_area_id'), data.area_id);
                }

                var url = "{{ route('admin.distributors.update', ':id') }}".replace(':id', data.id);
                $('#editDistributorForm').attr('action', url);

                $('#editDistributorModal').modal('show');
            });

            // Handle View
            $('#distributors-table').on('click', '.view-btn', function () {
                var data = $(this).data('row');
                let districtName = data.district ? data.district.name : 'N/A';
                let areaName = data.area ? data.area.name : 'N/A';
                let managerName = (data.sales_manager && data.sales_manager.user) ? data.sales_manager.user.name : 'N/A';
                let profileImg = data.user && data.user.profile_image ? '/storage/' + data.user.profile_image : null;

                if (profileImg) {
                    $('#dist_avatar_img').attr('src', profileImg).show();
                    $('#dist_avatar_initials').hide();
                } else {
                    let initials = data.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
                    $('#dist_avatar_initials').text(initials).show();
                    $('#dist_avatar_img').hide();
                }

                let status = data.user ? data.user.status : '';
                $('#dist_view_name').text(data.name);
                $('#dist_view_manager').html('<i class="fa fa-user-tie me-1"></i>Manager: ' + managerName);
                $('#dist_view_status').attr('class', 'badge ' + (status === 'active' ? 'bg-success' : 'bg-danger')).text(status === 'active' ? 'Active' : 'Inactive');
                $('#dist_view_email').text(data.user ? data.user.email : 'N/A');
                $('#dist_view_contact').text(data.contact_no || 'N/A');
                $('#dist_view_gst').text(data.gst || 'N/A');
                $('#dist_view_drug').text(data.drug_license_no || 'N/A');
                $('#dist_view_location').text(districtName + ' / ' + areaName);
                $('#dist_view_pincode').text(data.pincode || 'N/A');
                $('#dist_view_address').text(data.address || 'N/A');

                $('#showDistributorModal').data('lat', data.latitude).data('lng', data.longitude);
                $('#showDistributorModal').modal('show');
            });

            // Create Distributor AJAX
            $('#createDistributorForm').on('submit', function (e) {
                e.preventDefault();

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
                    url: "{{ route('admin.distributors.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#createDistributorModal').modal('hide');
                        $('#createDistributorForm')[0].reset();
                        $('#distributors-table').DataTable().ajax.reload();
                        submitBtn.prop('disabled', false).text('Create');
                        showToast('success', 'Distributor created successfully');
                    },
                    error: function (xhr) {
                        submitBtn.prop('disabled', false).text('Create');
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';
                        if (errors) {
                            $.each(errors, function (key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = 'An error occurred. Please try again.';
                        }
                        showToast('error', errorMessage);
                    }
                });
            });

            // Handle Delete via AJAX
            $('#distributors-table').on('click', '.delete-btn', function () {
                let url = $(this).data('url');
                Swal.fire({
                    title: 'Delete Distributor?',
                    text: "Are you sure? This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.success) {
                                    table.ajax.reload(null, false);
                                    Swal.fire('Deleted!', response.message, 'success');
                                } else {
                                    Swal.fire('Error!', response.message || 'Error deleting distributor', 'error');
                                }
                            },
                            error: function (xhr) {
                                let msg = 'Something went wrong.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error!', msg, 'error');
                            }
                        });
                    }
                });
            });

            // Handle Activate
            // Handle Status Toggle (Activate/Deactivate)
            $('#distributors-table').on('click', '.status-toggle', function () {
                if (!canActivate) {
                    showToast('warning', 'You do not have permission to change status.');
                    return;
                }

                let id = $(this).data('id');
                let currentStatus = $(this).data('status');
                let newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                let actionName = newStatus === 'active' ? 'Activate' : 'Deactivate';
                let btnColor = newStatus === 'active' ? '#28a745' : '#dc3545'; // Green for activate, Red for deactivate

                // Determine URL based on action
                let url = "";
                if (newStatus === 'active') {
                    url = "{{ route('admin.distributors.activate', ':id') }}".replace(':id', id);
                } else {
                    url = "{{ route('admin.distributors.deactivate', ':id') }}".replace(':id', id);
                }

                Swal.fire({
                    title: `${actionName} Distributor?`,
                    text: `Are you sure you want to ${actionName.toLowerCase()} this user?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: btnColor,
                    confirmButtonText: `Yes, ${actionName.toLowerCase()}!`
                }).then(result => {
                    if (result.isConfirmed) {
                        $.post(url, {
                            _token: "{{ csrf_token() }}",
                            _method: 'PATCH'
                        }, () => {
                            table.ajax.reload(null, false);
                            let msg = newStatus === 'active' ? 'Distributor activated successfully.' : 'Distributor deactivated successfully.';
                            Swal.fire('Updated!', msg, 'success');
                        }).fail(function (xhr) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        });
                    }
                });
            });

            // Handle Edit Distributor AJAX Submission
            $('#editDistributorForm').on('submit', function (e) {
                e.preventDefault();

                let password = $('#edit_password').val();
                let confirmPassword = $('#edit_password_confirmation').val();

                if (password && password !== confirmPassword) {
                    showToast('danger', 'Passwords do not match!');
                    return false;
                }

                let formData = new FormData(this);
                let submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#editDistributorModal').modal('hide');
                        $('#editDistributorForm')[0].reset();
                        $('#distributors-table').DataTable().ajax.reload();
                        submitBtn.prop('disabled', false).text('Update');
                        showToast('success', response.message);
                    },
                    error: function (xhr) {
                        submitBtn.prop('disabled', false).text('Update');
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';
                        if (errors) {
                            $.each(errors, function (key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = 'An error occurred. Please try again.';
                        }
                        showToast('danger', errorMessage);
                    }
                });
            });
        });

        // Global Map Variables
        let createMap, editMap, showMap;
        let createMarker, editMarker, showMarker;


        function fetchAreas(districtId, areaSelect, selectedAreaId = null) {
            areaSelect.html('<option value="">Loading...</option>');

            if (!districtId) {
                areaSelect.html('<option value="">Select Area</option>');
                return;
            }

            $.ajax({
                url: "{{ route('distributors.getAreas', ':id') }}".replace(':id', districtId),
                type: 'GET',
                success: function (response) {
                    areaSelect.html('<option value="">Select Area</option>');
                    $.each(response, function (key, area) {
                        let selected = (selectedAreaId && selectedAreaId == area.id) ? 'selected' : '';
                        areaSelect.append(`<option value="${area.id}" ${selected}>${area.name}</option>`);
                    });
                },
                error: function () {
                    areaSelect.html('<option value="">Error loading areas</option>');
                }
            });
        }

        function initMap() {
            const defaultLoc = {
                lat: 20.5937,
                lng: 78.9629
            };

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
        }

        function getGeoLocation(latId, longId, mapType) {
            // Geolocation disabled temporarily
        }
    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places,marker&v=weekly&loading=async&callback=initMap"
        async defer></script>
@endpush