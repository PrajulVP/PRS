@extends('layouts.admin')

@push('styles')
<style>
    .nav-tabs.custom-tabs {
        border-bottom: none;
        gap: 0.5rem;
        padding: 0.5rem;
        background: var(--med-bg-body, #f8fafc);
        border-radius: 12px;
        display: inline-flex;
    }
    .nav-tabs.custom-tabs .nav-link {
        border: 1px solid transparent !important;
        color: var(--med-text-muted, #64748b);
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        border-radius: 8px !important;
        background: none;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .nav-tabs.custom-tabs .nav-link.active {
        color: var(--med-primary, #00497a) !important;
        background: var(--med-bg-card, #ffffff) !important;
        border-color: var(--med-border, #e2e8f0) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    }
    .nav-tabs.custom-tabs .nav-link:hover:not(.active) {
        color: var(--med-text-main, #475569);
        background: var(--med-bg-body);
        opacity: 0.8;
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
                        <h5 class="mb-3"><i class="fa fa-users me-2"></i>Distributors</h5>
                        <ul class="nav nav-tabs custom-tabs" id="userStatusTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-status="all" type="button">
                                    All <span class="ms-1 fw-bold">({{ $stats['total'] }})</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-status="active" type="button">
                                    Active <span class="ms-1 text-success">({{ $stats['active'] }})</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-status="inactive" type="button">
                                    Inactive <span class="ms-1 text-danger">({{ $stats['inactive'] }})</span>
                                </button>
                            </li>
                        </ul>
                    </div>
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
    <div class="modal fade" id="createDistributorModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Distributor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createDistributorForm" action="{{ route('admin.distributors.store') }}" method="POST" novalidate autocomplete="off">
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
                                <div class="password-field-container">
                                    <input type="password" name="password" id="create_password" class="form-control" required autocomplete="new-password">
                                    <span class="toggle-password"><i class="fa fa-eye"></i></span>
                                </div>
                                <span class="text-danger small error-text" id="create_password_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <div class="password-field-container">
                                    <input type="password" name="password_confirmation" id="create_password_confirmation" class="form-control" required autocomplete="new-password">
                                    <span class="toggle-password"><i class="fa fa-eye"></i></span>
                                </div>
                                <span class="text-danger small error-text" id="create_password_confirmation_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST</label>
                                <input type="text" name="gst" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Drug License No</label>
                                <input type="text" name="drug_license_no" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" class="form-control" required>
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
                                <label class="form-label">Sales Manager</label>
                                <select name="sales_manager_id" class="form-select">
                                    <option value="">Select Sales Manager</option>
                                    @foreach($salesManagers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->user->name ?? 'N/A' }}</option>
                                    @endforeach
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
                <form id="editDistributorForm" method="POST" novalidate>
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
                                <div class="password-field-container">
                                    <input type="password" name="password" id="edit_password" class="form-control">
                                    <span class="toggle-password"><i class="fa fa-eye"></i></span>
                                </div>
                                <span class="text-danger small error-text" id="edit_password_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <div class="password-field-container">
                                    <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
                                    <span class="toggle-password"><i class="fa fa-eye"></i></span>
                                </div>
                                <span class="text-danger small error-text" id="edit_password_confirmation_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST</label>
                                <input type="text" name="gst" id="edit_gst" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Drug License No</label>
                                <input type="text" name="drug_license_no" id="edit_drug_license_no" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" id="edit_contact_no" class="form-control" required>
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
                                <label class="form-label">Sales Manager</label>
                                <select name="sales_manager_id" id="edit_sales_manager_id" class="form-select">
                                    <option value="">Select Sales Manager</option>
                                    @foreach($salesManagers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->user->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" id="edit_pincode" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select" {{ Auth::user()->hasRole('superadmin') ? '' : 'disabled' }}>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" id="edit_address" class="form-control" rows="2"
                                    required></textarea>
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
                    <div class="d-flex align-items-center gap-4 p-4"
                        style="background: var(--med-bg-body); border-bottom:1px solid var(--med-border);">
                        <div style="flex-shrink:0;">
                            <img id="dist_avatar_img" src="" alt="" class="rounded-circle shadow zoomable-avatar"
                                style="width:85px;height:85px;object-fit:cover;display:none;border:3px solid #fff;">
                            <div id="dist_avatar_initials"
                                style="width:85px;height:85px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                                    font-size:1.9rem;font-weight:700;color:#fff;
                                                                    background:linear-gradient(135deg,#1e3a5f,#2e6da4);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="mb-0 fw-bold" id="dist_view_name"></h4>
                                <span class="badge" id="dist_view_status"></span>
                            </div>
                            <div class="mt-1 text-muted small" id="dist_view_manager"></div>
                        </div>
                    </div>
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
                                        <div class="text-muted small">District</div>
                                        <div class="fw-semibold" id="dist_view_location"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-hashtag mt-1"></i>
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
        function fetchAreas(districtId, areaSelect, selectedAreaId = null) {
            areaSelect.html('<option value="">Loading...</option>');
            if (!districtId) {
                areaSelect.html('<option value="">Select Area</option>');
                return;
            }
            $.get("{{ route('distributors.getAreas', ':id') }}".replace(':id', districtId), (response) => {
                areaSelect.html('<option value="">Select Area</option>');
                $.each(response, function (key, area) {
                    let selected = (selectedAreaId && selectedAreaId == area.id) ? 'selected' : '';
                    areaSelect.append(`<option value="${area.id}" ${selected}>${area.name}</option>`);
                });
            }).fail(() => areaSelect.html('<option value="">Error loading areas</option>'));
        }

        $(document).ready(function () {
            var table = $('#distributors-table').DataTable({
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: "{{ route('admin.distributors.index') }}",
                    data: function(d) {
                        d.status = $('#userStatusTabs button.active').data('status');
                    }
                },
                columns: [
                    { data: 'id', name: 'id', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'name', name: 'name' },
                    { 
                        data: 'sales_manager', name: 'salesManager.user.name', orderable: false,
                        render: (data) => data?.user?.name || '<span class="text-muted small">Not Assigned</span>'
                    },
                    { data: 'user.email', name: 'user.email' },
                    { data: 'gst', name: 'gst' },
                    { data: 'drug_license_no', name: 'drug_license_no' },
                    { data: 'contact_no', name: 'contact_no' },
                    { data: 'district_name', name: 'district_name' },
                    { data: 'pincode', name: 'pincode' },
                    { data: 'address', name: 'address' },
                    { 
                        data: 'user.status', name: 'user.status',
                        render: (data, type, row) => {
                            let canToggle = row.can_activate ? 'status-toggle cursor-pointer' : '';
                            return `<span class="status-badge ${data === 'active' ? 'status-badge-active' : 'status-badge-inactive'} ${canToggle}" data-id="${row.id}" data-status="${data}">${data === 'active' ? 'Active' : 'Inactive'}</span>`;
                        }
                    },
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (id, type, row) {
                            let rowData = JSON.stringify(row).replace(/"/g, '&quot;');
                            let deleteUrl = "{{ route('admin.distributors.destroy', ':id') }}".replace(':id', id);
                            let btns = `<div class="action-buttons">
                                <button type="button" class="btn btn-sm btn-info view-btn" data-row="${rowData}"><i class="fa fa-eye"></i></button>`;
                            if (row.can_edit) btns += `<button type="button" class="btn btn-sm btn-primary edit-btn" data-row="${rowData}"><i class="fa fa-edit"></i></button>`;
                            if (row.can_delete) btns += `<button type="button" class="btn btn-sm btn-danger delete-btn" data-url="${deleteUrl}"><i class="fa fa-trash"></i></button>`;
                            btns += `</div>`;
                            return btns;
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
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10] },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            customize: function(doc) {
                                doc.defaultStyle.fontSize = 7;
                                doc.styles.tableHeader.fontSize = 8;
                            }
                        },
                        { extend: 'print', className: 'btn btn-dark btn-sm', text: '<i class="fa fa-print"></i> Print', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10] } }
                    ]
                }
            });

            // Handlers
            $('.district-select').on('change', function () {
                let container = $(this).closest('form');
                fetchAreas($(this).val(), container.find('.area-select'));
            });

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
                $('#edit_status').val(data.user?.status || '');
                $('#editDistributorForm').attr('action', "{{ route('admin.distributors.update', ':id') }}".replace(':id', data.id));
                $('#editDistributorModal').modal('show');
            });

            $('#distributors-table').on('click', '.view-btn', function () {
                var data = $(this).data('row');
                $('#dist_view_name').text(data.name);
                $('#dist_view_manager').html('<i class="fa fa-user-tie me-1"></i>Manager: ' + (data.sales_manager?.user?.name || 'N/A'));
                $('#dist_view_status').attr('class', 'status-badge ' + (data.user?.status === 'active' ? 'status-badge-active' : 'status-badge-inactive')).text(data.user?.status);
                $('#dist_view_email').text(data.user?.email || 'N/A');
                $('#dist_view_contact').text(data.contact_no || 'N/A');
                $('#dist_view_gst').text(data.gst || 'N/A');
                $('#dist_view_drug').text(data.drug_license_no || 'N/A');
                // Avatar logic
                if (data.user?.avatar) {
                    $('#dist_avatar_img').attr('src', data.user.avatar).show();
                    $('#dist_avatar_initials').hide();
                } else {
                    $('#dist_avatar_img').hide();
                    let initials = data.user?.name ? data.user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '??';
                    $('#dist_avatar_initials').text(initials).show();
                }

                $('#dist_view_name').text(data.name);
                $('#dist_view_manager').html('<i class="fa fa-user-tie me-1"></i>Manager: ' + (data.sales_manager?.user?.name || 'Not Assigned'));
                $('#dist_view_status').attr('class', 'status-badge ' + (data.user?.status === 'active' ? 'status-badge-active' : 'status-badge-inactive')).text(data.user?.status);
                $('#dist_view_email').text(data.user.email);
                $('#dist_view_contact').text(data.contact_no || 'N/A');
                $('#dist_view_gst').text(data.gst || 'N/A');
                $('#dist_view_drug').text(data.drug_license_no || 'N/A');
                $('#dist_view_location').text(data.district?.name || 'N/A');
                $('#dist_view_pincode').text(data.pincode || 'N/A');
                $('#dist_view_address').text(data.address || 'N/A');
                $('#showDistributorModal').modal('show');
            });

            $('#createDistributorForm, #editDistributorForm').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                
                // Clear previous errors
                form.find('.invalid-feedback').text('');
                form.find('.form-control').removeClass('is-invalid');

                btn.prop('disabled', true);
                $.ajax({
                    url: form.attr('action') || "{{ route('admin.distributors.store') }}",
                    type: "POST", data: new FormData(this), processData: false, contentType: false,
                    beforeSend: function() {
                        let isValid = true;
                        // Clear previous errors
                        form.find('.is-invalid').removeClass('is-invalid');
                        form.find('.error-text').text('').removeClass('d-block');
                        form.find('.invalid-feedback').text('').removeClass('d-block');

                        form.find('[required]').each(function() {
                            if (!$(this).val()) {
                                let name = $(this).attr('name');
                                $(this).addClass('is-invalid');
                                // Target specific error div
                                let errorDiv = $(this).closest('div').find('.invalid-feedback, .error-text').first();
                                if (errorDiv.length) {
                                    errorDiv.text('This field is required.').addClass('d-block');
                                } else {
                                    $(this).after('<div class="invalid-feedback d-block">This field is required.</div>');
                                }
                                isValid = false;
                            }
                        });
                        
                        if (!isValid) {
                            btn.prop('disabled', false).text(oldText);
                            showToast('danger', 'Please fill in all required fields.');
                            return false;
                        }
                    },
                    success: (res) => {
                        $('.modal').modal('hide');
                        form[0].reset();
                        table.ajax.reload();
                        btn.prop('disabled', false);
                        showToast('success', 'Saved successfully');
                    },
                    error: (xhr) => {
                        btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(key, messages) {
                                let input = form.find(`[name="${key}"]`);
                                input.addClass('is-invalid');
                                // Find or create error div
                                let errorDiv = form.find(`#${form.attr('id').startsWith('create') ? 'create' : 'edit'}_${key}_error`);
                                if (errorDiv.length === 0) {
                                    // Fallback: search for generic error div or append one
                                    input.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                                } else {
                                    errorDiv.text(messages[0]);
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
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block name-error"></div>');
                    errorDiv = $(this).closest('div').find('.name-error');
                }
                
                if (!regex.test(val)) {
                    errorDiv.text('Name should only contain letters and spaces.');
                    $(this).addClass('is-invalid');
                } else {
                    errorDiv.text('');
                    $(this).removeClass('is-invalid');
                }
            });

            // Phone Validation (No 0 at start, max 10 digits)
            $('input[name="contact_no"]').on('input', function() {
                let val = $(this).val().replace(/\D/g, ''); // Remove non-digits
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block phone-error"></div>');
                    errorDiv = $(this).closest('div').find('.phone-error');
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

            // Contact 10 digits check on blur
            $('input[name="contact_no"]').on('blur', function() {
                let val = $(this).val();
                let errorDiv = $(this).closest('div').find('.phone-error');
                if (val.length > 0 && val.length < 10) {
                    errorDiv.text('The contact no should be of 10 digits.');
                    $(this).addClass('is-invalid');
                }
            });

            // GST Validation (No symbols)
            $('input[name="gst"]').on('input', function() {
                let val = $(this).val();
                let regex = /^[a-zA-Z0-9]*$/;
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block gst-error"></div>');
                    errorDiv = $(this).closest('div').find('.gst-error');
                }
                
                if (!regex.test(val)) {
                    errorDiv.text('GST should only contain letters and numbers.');
                    $(this).addClass('is-invalid');
                } else {
                    errorDiv.text('');
                    $(this).removeClass('is-invalid');
                }
            });

            // Drug License Validation (No symbols except / and -)
            $('input[name="drug_license_no"]').on('input', function() {
                let val = $(this).val();
                let regex = /^[a-zA-Z0-9\/\-]*$/;
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block drug-error"></div>');
                    errorDiv = $(this).closest('div').find('.drug-error');
                }
                
                if (!regex.test(val)) {
                    errorDiv.text('The drug license number can only contain letters, numbers, slashes (/), and hyphens (-).');
                    $(this).addClass('is-invalid');
                } else {
                    errorDiv.text('');
                    $(this).removeClass('is-invalid');
                }
            });

            // Pincode Validation (6 digits)
            $('input[name="pincode"]').on('input blur', function() {
                let val = $(this).val().replace(/\D/g, '').substring(0, 6);
                $(this).val(val);
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block pin-error"></div>');
                    errorDiv = $(this).closest('div').find('.pin-error');
                }

                if (val.length > 0 && val.length < 6) {
                    errorDiv.text('Pincode must be exactly 6 digits.');
                    $(this).addClass('is-invalid');
                } else {
                    errorDiv.text('');
                    $(this).removeClass('is-invalid');
                }
            });

            // Email Validation (Live & Blur)
            $('input[name="email"]').on('input blur', function(e) {
                let val = $(this).val();
                let regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block email-error"></div>');
                    errorDiv = $(this).closest('div').find('.email-error');
                }
                
                if (e.type === 'blur' || $(this).hasClass('is-invalid')) {
                    if (val && !regex.test(val)) {
                        errorDiv.text('Please enter a valid email address.').addClass('d-block');
                        $(this).addClass('is-invalid');
                    } else {
                        errorDiv.text('').removeClass('d-block');
                        $(this).removeClass('is-invalid');
                    }
                } else if (val && regex.test(val)) {
                    errorDiv.text('').removeClass('d-block');
                    $(this).removeClass('is-invalid');
                }
            });

            // Password Validation (Live & Blur)
            $('input[name="password"]').on('input blur', function(e) {
                let val = $(this).val();
                let form = $(this).closest('form');
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block pass-error"></div>');
                    errorDiv = $(this).closest('div').find('.pass-error');
                }
                
                if (e.type === 'blur' || $(this).hasClass('is-invalid')) {
                    if (val && val.length < 6) {
                        errorDiv.text('Password must be at least 6 characters.').addClass('d-block');
                        $(this).addClass('is-invalid');
                    } else {
                        errorDiv.text('').removeClass('d-block');
                        $(this).removeClass('is-invalid');
                    }
                } else if (!val || val.length >= 6) {
                    errorDiv.text('').removeClass('d-block');
                    $(this).removeClass('is-invalid');
                }
                form.find('input[name="password_confirmation"]').trigger('input');
            });

            // Confirm Password Validation (Live)
            $('input[name="password_confirmation"]').on('input blur', function() {
                let val = $(this).val();
                let form = $(this).closest('form');
                let password = form.find('input[name="password"]').val();
                let errorDiv = $(this).closest('div').find('.invalid-feedback');
                if (errorDiv.length === 0) {
                    $(this).after('<div class="invalid-feedback d-block confirm-error"></div>');
                    errorDiv = $(this).closest('div').find('.confirm-error');
                }

                if (val && password && val !== password) {
                    errorDiv.text('Passwords do not match.').addClass('d-block');
                    $(this).addClass('is-invalid');
                } else if (!val && !password) {
                    errorDiv.text('').removeClass('d-block');
                    $(this).removeClass('is-invalid');
                } else if (val === password) {
                    errorDiv.text('').removeClass('d-block');
                    $(this).removeClass('is-invalid');
                } else if (val && !password) {
                    errorDiv.text('Passwords do not match.').addClass('d-block');
                    $(this).addClass('is-invalid');
                }
            });

            $('#distributors-table').on('click', '.delete-btn', function () {
                let url = $(this).data('url');
                Swal.fire({ title: 'Delete?', text: "Are you sure?", icon: 'warning', showCancelButton: true }).then((r) => {
                    if (r.isConfirmed) $.ajax({ url: url, type: 'DELETE', data: { _token: "{{ csrf_token() }}" }, success: (res) => {
                        table.ajax.reload(null, false);
                        Swal.fire('Deleted!', 'Success', 'success');
                    }});
                });
            });

            $('#distributors-table').on('click', '.status-toggle', function () {
                let id = $(this).data('id'), status = $(this).data('status'), next = status === 'active' ? 'inactive' : 'active';
                
                // Frontend Permission Check (Allow ONLY Superadmin)
                const isSuperAdmin = {{ Auth::user()->hasRole('superadmin') ? 'true' : 'false' }};
                if (!isSuperAdmin) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Superadmin Required',
                        text: 'Only a Superadmin can activate or deactivate distributors.',
                        confirmButtonColor: '#00497a'
                    });
                    return;
                }

                Swal.fire({
                    title: `Change Status to ${next.toUpperCase()}?`,
                    text: `Are you sure you want to ${next} this distributor?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, change it!'
                }).then((r) => {
                    if (r.isConfirmed) {
                        let url = (next === 'active' ? "{{ route('admin.distributors.activate', ':id') }}" : "{{ route('admin.distributors.deactivate', ':id') }}").replace(':id', id);
                        $.post(url, { _token: "{{ csrf_token() }}", _method: 'PATCH' }, () => {
                            table.ajax.reload(null, false);
                            showToast('success', 'Status updated successfully');
                        }).fail((xhr) => {
                            console.error('Status Toggle Error:', xhr);
                            let msg = 'Error changing user status';
                            let title = 'Action Failed';
                            if (xhr.status === 403) title = 'Permission Denied';

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
                                    title: title,
                                    text: msg,
                                    confirmButtonColor: '#00497a'
                                });
                            } else {
                                alert(title + ': ' + msg);
                            }
                        });
                    }
                });
            });

            $('#userStatusTabs button').on('click', function() {
                setTimeout(() => table.ajax.reload(), 50);
            });
        });
    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places,marker&v=weekly&loading=async&callback=initMap"
        async defer></script>
@endpush