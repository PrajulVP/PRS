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
                    <h5><i class="fa fa-users me-2"></i>Distributors</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDistributorModal">
                        <i class="fa fa-plus me-1"></i>Add Distributor
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
                        <table class="display table table-striped table-hover" id="distributors-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>GST</th>
                                    <th>Drug Lic.</th>
                                    <th>Contact</th>
                                    <th>District</th>
                                    <th>Area</th>
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
<div class="modal fade" id="createDistributorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Distributor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.distributors.store') }}" method="POST">
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
                            <input type="password" name="password" class="form-control" required>
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
                            <label class="form-label">Sales Manager</label>
                            <select name="sales_manager_id" class="form-select" required>
                                <option value="">Select Sales Manager</option>
                                @foreach($salesManagers as $salesManager)
                                <option value="{{ $salesManager->id }}">{{ $salesManager->user->name }}</option>
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
<div class="modal fade" id="editDistributorModal" tabindex="-1" aria-hidden="true">
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
                            <input type="password" name="password" class="form-control">
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
                            <label class="form-label">District</label>
                            <select name="district_id" id="edit_district_id" class="form-select district-select" required>
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
                            <label class="form-label">Sales Manager</label>
                            <select name="sales_manager_id" id="edit_sales_manager_id" class="form-select" required>
                                <option value="">Select Sales Manager</option>
                                @foreach($salesManagers as $salesManager)
                                <option value="{{ $salesManager->id }}">{{ $salesManager->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" id="edit_pincode" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2" required></textarea>
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
<div class="modal fade" id="showDistributorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Distributor Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody id="showDistributorBody">
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
    $(document).ready(function() {
        var table = $('#distributors-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.distributors.index') }}",
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'name',
                    name: 'name'
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
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(id, type, row) {
                        let deleteUrl = "{{ route('admin.distributors.destroy', ':id') }}".replace(':id', id);
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

        // Dynamic Areas fetching
        function fetchAreas(districtId, areaSelect, selectedAreaId = null) {
            areaSelect.empty().append('<option value="">Select Area</option>');
            if (districtId) {
                $.ajax({
                    url: "/distributors/get-areas/" + districtId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $.each(data, function(key, value) {
                            let selected = (selectedAreaId && selectedAreaId == value.id) ? 'selected' : '';
                            areaSelect.append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                        });
                    }
                });
            }
        }

        // Handle District Change for Create
        $('.district-select').on('change', function() {
            let container = $(this).closest('form');
            let areaSelect = container.find('.area-select');
            fetchAreas($(this).val(), areaSelect);
        });

        // Handle Edit
        $('#distributors-table').on('click', '.edit-btn', function() {
            var data = $(this).data('row');

            $('#edit_name').val(data.name);
            $('#edit_email').val(data.user.email);
            $('#edit_gst').val(data.gst);
            $('#edit_drug_license_no').val(data.drug_license_no);
            $('#edit_contact_no').val(data.contact_no);
            $('#edit_pincode').val(data.pincode);
            $('#edit_address').val(data.address);
            $('#edit_district_id').val(data.district_id);
            $('#edit_sales_manager_id').val(data.sales_manager_id);

            // Fetch areas for the selected district and select the correct area
            fetchAreas(data.district_id, $('#edit_area_id'), data.area_id);

            var url = "{{ route('admin.distributors.update', ':id') }}".replace(':id', data.id);
            $('#editDistributorForm').attr('action', url);

            $('#editDistributorModal').modal('show');
        });

        // Handle View
        $('#distributors-table').on('click', '.view-btn', function() {
            var data = $(this).data('row');
            let districtName = data.district ? data.district.name : 'N/A';
            let areaName = data.area ? data.area.name : 'N/A';
            let smName = data.sales_manager && data.sales_manager.user ? data.sales_manager.user.name : 'N/A';

            let html = `
                <tr><th>Name</th><td>${data.name}</td></tr>
                <tr><th>Email</th><td>${data.user.email}</td></tr>
                <tr><th>GST</th><td>${data.gst}</td></tr>
                <tr><th>Drug License No</th><td>${data.drug_license_no || 'N/A'}</td></tr>
                <tr><th>Contact</th><td>${data.contact_no}</td></tr>
                <tr><th>Address</th><td>${data.address}</td></tr>
                <tr><th>District</th><td>${districtName}</td></tr>
                <tr><th>Area</th><td>${areaName}</td></tr>
                <tr><th>Pincode</th><td>${data.pincode}</td></tr>
                <tr><th>Sales Manager</th><td>${smName}</td></tr>
            `;
            $('#showDistributorBody').html(html);
            $('#showDistributorModal').modal('show');
        });

        // Handle Delete
        $('#distributors-table').on('click', '.delete-form button[type="submit"]', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Distributor?',
                text: "Are you sure?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) form.off('submit').submit();
            });
        });
    });
</script>
@endpush