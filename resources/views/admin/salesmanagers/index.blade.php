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
</style>

@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fa fa-users me-2"></i>Sales Managers</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createSalesManagerModal">
                            <i class="fa fa-plus me-1"></i>Add Sales Manager
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
                            <table class="display table table-striped table-hover" id="sales-managers-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact No</th>
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

    <!-- Create Sales Manager Modal -->
    <div class="modal fade" id="createSalesManagerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Sales Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createSalesManagerForm" action="{{ route('admin.sales-managers.store') }}" method="POST">
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
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact No</label>
                                <input type="text" name="contact_no" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control"></textarea>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editSalesManagerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sales Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editSalesManagerForm" method="POST">
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
                                <input type="text" name="contact_no" id="edit_contact_no" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" id="edit_address" class="form-control"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
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
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Show Modal -->
    <div class="modal fade" id="showSalesManagerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, #1e3a5f, #2e6da4); border-radius: 0.5rem 0.5rem 0 0;">
                    <h5 class="modal-title text-white" style="color: #fff !important;"><i class="fa fa-user-circle me-2"
                            style="color: #fff !important;"></i>Sales Manager Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Avatar + Name Header --}}
                    <div class="d-flex align-items-center gap-4 p-4"
                        style="background: var(--med-bg-body); border-bottom:1px solid var(--med-border);">
                        <div style="flex-shrink:0;">
                            <img id="sm_avatar_img" src="" alt="" class="rounded-circle shadow"
                                style="width:85px;height:85px;object-fit:cover;display:none;border:3px solid #fff;">
                            <div id="sm_avatar_initials"
                                style="width:85px;height:85px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                                font-size:1.9rem;font-weight:700;color:#fff;
                                                                background:linear-gradient(135deg,#1e3a5f,#2e6da4);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            </div>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold" id="sm_view_name"></h4>
                            <div class="mb-1 text-muted small"><i class="fa fa-user-tie me-1"></i>Sales Manager</div>
                            <span class="badge" id="sm_view_status"></span>
                        </div>
                    </div>

                    {{-- Basic Info Cards --}}
                    <div class="p-4 pb-0">
                        <h6 class="fw-bold mb-3"><i class="fa fa-info-circle me-2"></i>Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-envelope mt-1 text-primary"></i>
                                    <div>
                                        <div class="text-muted small">Email</div>
                                        <div class="fw-semibold" id="sm_view_email"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-phone mt-1 text-success"></i>
                                    <div>
                                        <div class="text-muted small">Contact</div>
                                        <div class="fw-semibold" id="sm_view_contact"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                    style="background: var(--med-bg-body);">
                                    <i class="fa fa-map-marker-alt mt-1 text-danger"></i>
                                    <div>
                                        <div class="text-muted small">Address</div>
                                        <div class="fw-semibold" id="sm_view_address"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Assignments Section --}}
                    <div class="p-4 pt-4">
                        <ul class="nav nav-tabs" id="smModalTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="fieldstaff-tab" data-bs-toggle="tab"
                                    data-bs-target="#fieldstaff-panel" type="button" role="tab">
                                    <i class="fa fa-users me-1"></i>Field Staff (<span id="fieldStaffCount">0</span>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="retailer-tab" data-bs-toggle="tab"
                                    data-bs-target="#retailer-panel" type="button" role="tab">
                                    <i class="fa fa-store me-1"></i>Retailers (<span id="retailerCount">0</span>)
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content border border-top-0 p-3" id="smModalTabsContent"
                            style="border-radius: 0 0 0.5rem 0.5rem; background: var(--med-bg-body);">
                            <div class="tab-pane fade show active" id="fieldstaff-panel" role="tabpanel">
                                <div class="table-responsive" style="max-height: 300px;">
                                    <table class="table table-sm table-striped table-hover mb-0">
                                        <thead class="sticky-top" style="background: var(--med-bg-card);">
                                            <tr>
                                                <th style="color: var(--med-text-main) !important;">Name</th>
                                                <th style="color: var(--med-text-main) !important;">Email</th>
                                                <th style="color: var(--med-text-main) !important;">Contact</th>
                                                <th style="color: var(--med-text-main) !important;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="showFieldStaffBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="retailer-panel" role="tabpanel">
                                <div class="table-responsive" style="max-height: 300px;">
                                    <table class="table table-sm table-striped table-hover mb-0">
                                        <thead class="sticky-top" style="background: var(--med-bg-card);">
                                            <tr>
                                                <th style="color: var(--med-text-main) !important;">Shop Name</th>
                                                <th style="color: var(--med-text-main) !important;">Owner</th>
                                                <th style="color: var(--med-text-main) !important;">Email</th>
                                                <th style="color: var(--med-text-main) !important;">Contact</th>
                                                <th style="color: var(--med-text-main) !important;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="showRetailerBody"></tbody>
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
            const canActivate = @json(Auth::user()->hasRole('superadmin'));

            // Handle Create Form AJAX
            $('#createSalesManagerForm').on('submit', function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                let submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).text('Creating...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#createSalesManagerModal').modal('hide');
                        $('#createSalesManagerForm')[0].reset();
                        $('#sales-managers-table').DataTable().ajax.reload();
                        submitBtn.prop('disabled', false).text('Create');
                        showToast('success', response.message);
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

            // Handle Edit Form AJAX
            $('#editSalesManagerForm').on('submit', function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                let submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: "POST", // Method spoofing will handle PUT
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#editSalesManagerModal').modal('hide');
                        $('#editSalesManagerForm')[0].reset();
                        $('#sales-managers-table').DataTable().ajax.reload();
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
                        showToast('error', errorMessage);
                    }
                });
            });

            var table = $('#sales-managers-table').DataTable({
                processing: true,
                serverSide: true,
                order: [],
                ajax: "{{ route('admin.sales-managers.index') }}",
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
                    data: 'user.email',
                    name: 'user.email'
                },
                {
                    data: 'contact_no',
                    name: 'contact_no'
                },
                {
                    data: 'address',
                    name: 'address'
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
                        let deleteUrl = "{{ route('admin.sales-managers.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');

                        /*
                        // Removed separate Activate button as requested
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

                        return `
                                                    <div class="action-buttons">
                                                        ${activateBtn}
                                                        <button type="button" class="btn btn-sm btn-info view-btn" data-row="${rowData}"><i class="fa fa-eye"></i></button>
                                                        <button type="button" class="btn btn-sm btn-primary edit-btn" data-row="${rowData}"><i class="fa fa-edit"></i></button>
                                                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-url="${deleteUrl}"><i class="fa fa-trash"></i></button>
                                                    </div>
                                                `;
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

            // Handle Edit Button
            $('#sales-managers-table').on('click', '.edit-btn', function () {
                var data = $(this).data('row');

                $('#edit_name').val(data.name);
                var email = data.user ? data.user.email : (data.email || '');
                var address = data.address || '';

                $('#edit_email').val(email);
                $('#edit_contact_no').val(data.contact_no);
                $('#edit_address').val(address);
                if (data.user) {
                    $('#edit_status').val(data.user.status);
                }

                var url = "{{ route('admin.sales-managers.update', ':id') }}".replace(':id', data.id);
                $('#editSalesManagerForm').attr('action', url);

                $('#editSalesManagerModal').modal('show');
            });

            // Handle View Button
            $('#sales-managers-table').on('click', '.view-btn', function () {
                var data = $(this).data('row');
                var id = data.id;
                var url = "{{ route('admin.sales-managers.show', ':id') }}".replace(':id', id);

                // Clear previous data
                $('#sm_avatar_img').hide();
                $('#sm_avatar_initials').text('').show();
                $('#sm_view_name').text('Loading...');
                $('#sm_view_email').text('Loading...');
                $('#sm_view_contact').text('Loading...');
                $('#sm_view_address').text('Loading...');
                $('#showFieldStaffBody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
                $('#showRetailerBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
                $('#fieldStaffCount').text('0');
                $('#retailerCount').text('0');

                $('#showSalesManagerModal').modal('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        if (response.success) {
                            let sm = response.data;
                            let email = sm.user ? sm.user.email : (sm.email || 'N/A');
                            let profileImg = sm.user && sm.user.profile_image ? '/storage/' + sm.user.profile_image : null;

                            // Avatar
                            if (profileImg) {
                                $('#sm_avatar_img').attr('src', profileImg).show();
                                $('#sm_avatar_initials').hide();
                            } else {
                                let initials = sm.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
                                $('#sm_avatar_initials').text(initials).show();
                                $('#sm_avatar_img').hide();
                            }

                            let status = sm.user ? sm.user.status : '';
                            $('#sm_view_name').text(sm.name);
                            $('#sm_view_email').text(email);
                            $('#sm_view_contact').text(sm.contact_no || 'N/A');
                            $('#sm_view_address').text(sm.address || 'N/A');
                            $('#sm_view_status').attr('class', 'badge ' + (status === 'active' ? 'bg-success' : 'bg-danger')).text(status === 'active' ? 'Active' : 'Inactive');

                            // Populate Field Staff
                            let fieldStaff = sm.field_staffs || [];
                            $('#fieldStaffCount').text(fieldStaff.length);
                            let fsHtml = '';
                            if (fieldStaff.length > 0) {
                                fieldStaff.forEach(fs => {
                                    let fsStatus = fs.user ? fs.user.status : 'inactive';
                                    let fsBadgeClass = fsStatus === 'active' ? 'bg-success' : 'bg-danger';
                                    fsHtml += `
                                            <tr>
                                                <td style="color: var(--med-text-main) !important;">${fs.user.name}</td>
                                                <td style="color: var(--med-text-main) !important;">${fs.user.email}</td>
                                                <td style="color: var(--med-text-main) !important;">${fs.contact_no || 'N/A'}</td>
                                                <td style="color: var(--med-text-main) !important;"><span class="badge ${fsBadgeClass}">${fsStatus}</span></td>
                                            </tr>
                                        `;
                                });
                            } else {
                                fsHtml = '<tr><td colspan="4" class="text-center text-muted">No Field Staff assigned.</td></tr>';
                            }
                            $('#showFieldStaffBody').html(fsHtml);

                            // Retailers
                            let retailers = sm.retailers || [];
                            $('#retailerCount').text(retailers.length);
                            let retHtml = '';
                            if (retailers.length > 0) {
                                retailers.forEach(ret => {
                                    let rStatus = ret.user ? ret.user.status : 'inactive';
                                    let rBadgeClass = rStatus === 'active' ? 'bg-success' : 'bg-danger';
                                    let owner = ret.user ? ret.user.name : 'N/A';
                                    let email = ret.user ? ret.user.email : 'N/A';
                                    retHtml += `
                                            <tr>
                                                <td style="color: var(--med-text-main) !important;">${ret.shop_name}</td>
                                                <td style="color: var(--med-text-main) !important;">${owner}</td>
                                                <td style="color: var(--med-text-main) !important;">${email}</td>
                                                <td style="color: var(--med-text-main) !important;">${ret.contact_no || 'N/A'}</td>
                                                <td style="color: var(--med-text-main) !important;"><span class="badge ${rBadgeClass}">${rStatus}</span></td>
                                            </tr>
                                        `;
                                });
                            } else {
                                retHtml = '<tr><td colspan="5" class="text-center text-muted">No Retailers assigned.</td></tr>';
                            }
                            $('#showRetailerBody').html(retHtml);

                        } else {
                            Swal.fire('Error!', 'Failed to fetch details.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'Something went wrong fetching details.', 'error');
                    }
                });
            });

            // Handle Delete via AJAX
            $('#sales-managers-table').on('click', '.delete-btn', function () {
                let url = $(this).data('url');
                Swal.fire({
                    title: 'Delete Sales Manager?',
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
                                    Swal.fire('Error!', response.message, 'error');
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
            $('#sales-managers-table').on('click', '.status-toggle', function () {
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
                    url = "{{ route('admin.sales-managers.activate', ':id') }}".replace(':id', id);
                } else {
                    url = "{{ route('admin.sales-managers.deactivate', ':id') }}".replace(':id', id);
                }

                Swal.fire({
                    title: `${actionName} Sales Manager?`,
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
                            let msg = newStatus === 'active' ? 'Sales Manager activated successfully.' : 'Sales Manager deactivated successfully.';
                            Swal.fire('Updated!', msg, 'success');
                        }).fail(function (xhr) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        });
                    }
                });
            });

        });
    </script>
@endpush