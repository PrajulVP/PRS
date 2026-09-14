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
                        <h5 class="mb-3"><i class="fa fa-hospital-o me-2"></i>Hospitals / Clinics</h5>
                        <ul class="nav nav-tabs custom-tabs" id="hospitalStatusTabs" role="tablist">
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
                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']) || Auth::user()->hasPermissionToCategory('hospitals_clinics', 'add'))
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#createHospitalModal">
                                <i class="fa fa-plus me-1"></i>Add Hospital / Clinic
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

                        <!-- Advanced Filters -->
                        <div class="row g-3 mb-4 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small text-uppercase"><i class="fa fa-map me-1"></i>District</label>
                                <select id="filter_district" class="form-select" style="border-radius: 8px;">
                                    <option value="">All Districts</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small text-uppercase"><i class="fa fa-map-pin me-1"></i>Area</label>
                                <select id="filter_area" class="form-select" style="border-radius: 8px;">
                                    <option value="">Select District First</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="button" id="btn_filter_reset" class="btn btn-secondary w-100" style="height: 38px; border-radius: 8px;"><i class="fa fa-refresh me-1"></i>Reset</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="display table table-striped table-hover" id="hospitals-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>District</th>
                                        <th>Area</th>
                                        <th>Address</th>
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
    <div class="modal fade" id="createHospitalModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Hospital / Clinic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createHospitalForm" action="{{ route('admin.hospitals-clinics.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">District</label>
                                <select name="district_id" class="form-select district-select">
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Area</label>
                                <select name="area_id" class="form-select area-select">
                                    <option value="">Select Area</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Hospital / Clinic</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editHospitalModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Hospital / Clinic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editHospitalForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">District</label>
                                <select name="district_id" id="edit_district_id" class="form-select district-select">
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Area</label>
                                <select name="area_id" id="edit_area_id" class="form-select area-select">
                                    <option value="">Select Area</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Hospital / Clinic</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentStatus = 'all';

    let table = $('#hospitals-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.hospitals-clinics.index') }}",
            data: function (d) {
                d.status = currentStatus;
                d.district_id = $('#filter_district').val();
                d.area_id = $('#filter_area').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'district_name', name: 'district_name'},
            {data: 'area_name', name: 'area_name'},
            {data: 'address', name: 'address'},
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let hasLocation = row.latitude && row.longitude && parseFloat(row.latitude) !== 0 && parseFloat(row.longitude) !== 0;
                    let resetUrl = "{{ url('admin/hospitals-clinics') }}/" + row.id + "/reset-location";

                    let btns = '<div class="action-buttons">';
                    
                    if (row.can_edit) {
                        btns += `<button type="button" class="btn btn-sm btn-primary btn-edit" data-id="${row.id}" title="Edit"><i class="fa fa-edit"></i></button>`;
                    }

                    if (hasLocation) {
                        btns += `<button type="button" class="btn btn-sm btn-warning text-white btn-reset-location" data-id="${row.id}" data-url="${resetUrl}" data-name="${row.name}" title="Reset Saved Location"><span style="position: relative; display: inline-flex; align-items: center; justify-content: center; line-height: 1; overflow: visible;"><i class="fa fa-map-marker" style="font-size: 1.5rem; color: #ffffff;"></i><span style="position: absolute; top: 50%; left: 50%; width: 210%; height: 2px; background-color: #ffffff; transform: translate(-50%, -50%) rotate(-45deg); transform-origin: center; box-shadow: 0 0 1px rgba(0,0,0,0.5);"></span></span></button>`;
                    } else {
                        btns += `<button type="button" class="btn btn-sm btn-warning text-white no-location-btn" data-name="${row.name}" title="No location set for this hospital/clinic" style="opacity: 0.55; cursor: not-allowed;"><span style="position: relative; display: inline-flex; align-items: center; justify-content: center; line-height: 1; overflow: visible;"><i class="fa fa-map-marker" style="font-size: 1.5rem; color: #ffffff;"></i><span style="position: absolute; top: 50%; left: 50%; width: 210%; height: 2px; background-color: #ffffff; transform: translate(-50%, -50%) rotate(-45deg); transform-origin: center; box-shadow: 0 0 1px rgba(0,0,0,0.5);"></span></span></button>`;
                    }

                    if (row.can_delete) {
                        btns += `<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" title="Delete"><i class="fa fa-trash"></i></button>`;
                    }

                    btns += '</div>';
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
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                { extend: 'print', className: 'btn btn-dark btn-sm', text: '<i class="fa fa-print"></i> Print', exportOptions: { columns: [0, 1, 2, 3, 4] } }
            ]
        }
    });

    // Tab Filter
    $('#hospitalStatusTabs button').on('click', function() {
        currentStatus = $(this).data('status');
        table.ajax.reload();
    });

    // Filter changes
    $('#filter_district, #filter_area').on('change', function() {
        table.ajax.reload();
    });

    $('#btn_filter_reset').on('click', function() {
        $('#filter_district').val('');
        $('#filter_area').html('<option value="">Select District First</option>');
        table.ajax.reload();
    });

    // District -> Area cascading dropdown
    $(document).on('change', '.district-select, #filter_district', function() {
        let districtId = $(this).val();
        let targetAreaSelect = $(this).attr('id') === 'filter_district' 
            ? $('#filter_area') 
            : $(this).closest('.modal-body').find('.area-select');

        if (districtId) {
            targetAreaSelect.html('<option value="">Loading...</option>');
            $.get("{{ url('admin/hospitals-clinics/areas') }}/" + districtId, function(data) {
                let options = '<option value="">Select Area</option>';
                $.each(data, function(key, value) {
                    options += `<option value="${value.id}">${value.name}</option>`;
                });
                targetAreaSelect.html(options);
            }).fail(function() {
                targetAreaSelect.html('<option value="">Error loading areas</option>');
            });
        } else {
            targetAreaSelect.html('<option value="">Select District First</option>');
        }
    });

    // Edit Hospital Modal Populate
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        $.get("{{ url('admin/hospitals-clinics') }}/" + id, function(res) {
            if (res.success) {
                let item = res.data;
                $('#editHospitalForm').attr('action', "{{ url('admin/hospitals-clinics') }}/" + id);
                $('#edit_name').val(item.name);
                $('#edit_address').val(item.address);
                $('#edit_status').val(item.status);
                
                if (item.district_id) {
                    $('#edit_district_id').val(item.district_id);
                    $.get("{{ url('admin/hospitals-clinics/areas') }}/" + item.district_id, function(data) {
                        let options = '<option value="">Select Area</option>';
                        $.each(data, function(key, value) {
                            let selected = (item.area_id && item.area_id == value.id) ? 'selected' : '';
                            options += `<option value="${value.id}" ${selected}>${value.name}</option>`;
                        });
                        $('#edit_area_id').html(options);
                    });
                } else {
                    $('#edit_district_id').val('');
                    $('#edit_area_id').html('<option value="">Select District First</option>');
                }

                $('#editHospitalModal').modal('show');
            }
        });
    });

    // Reset Location Ajax
    $(document).on('click', '.btn-reset-location', function() {
        let id = $(this).data('id');
        if (confirm('Are you sure you want to reset the location coordinates for this Hospital/Clinic? Field staff can then capture it fresh.')) {
            $.post("{{ url('admin/hospitals-clinics') }}/" + id + "/reset-location", {_token: "{{ csrf_token() }}"}, function(res) {
                if (res.success) {
                    alert(res.message);
                    table.ajax.reload();
                } else {
                    alert(res.message);
                }
            }).fail(function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error resetting location.');
            });
        }
    });

    // Delete Ajax
    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id');
        if (confirm('Are you sure you want to delete this Hospital/Clinic record?')) {
            $.ajax({
                url: "{{ url('admin/hospitals-clinics') }}/" + id,
                type: 'DELETE',
                data: {_token: "{{ csrf_token() }}"},
                success: function(res) {
                    if (res.success) {
                        alert(res.message);
                        table.ajax.reload();
                    }
                }
            });
        }
    });
});
</script>
@endpush

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
@endpush
