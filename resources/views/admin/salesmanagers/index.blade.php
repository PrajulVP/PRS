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
                    <h5><i class="fa fa-users me-2"></i>Sales Managers</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSalesManagerModal">
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
<!-- Create Sales Manager Modal -->
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
                            <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact No</label>
                            <input type="text" name="contact_no" id="edit_contact_no" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="edit_address" class="form-control"></textarea>
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sales Manager Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody id="showSalesManagerBody">
                        <!-- Filled by JS -->
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
        // Handle Create Form AJAX
        $('#createSalesManagerForm').on('submit', function(e) {
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
                success: function(response) {
                    $('#createSalesManagerModal').modal('hide');
                    $('#createSalesManagerForm')[0].reset();
                    $('#sales-managers-table').DataTable().ajax.reload();
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
                    showToast('error', errorMessage);
                }
            });
        });

        // Handle Edit Form AJAX
        $('#editSalesManagerForm').on('submit', function(e) {
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
                success: function(response) {
                    $('#editSalesManagerModal').modal('hide');
                    $('#editSalesManagerForm')[0].reset();
                    $('#sales-managers-table').DataTable().ajax.reload();
                    submitBtn.prop('disabled', false).text('Update');
                    showToast('success', response.message);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).text('Update');
                    let errors = xhr.responseJSON.errors;
                    let errorMessage = '';
                    if (errors) {
                        $.each(errors, function(key, value) {
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
            ajax: "{{ route('admin.sales-managers.index') }}",
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
                    data: 'contact_no',
                    name: 'contact_no'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(id, type, row) {
                        let deleteUrl = "{{ route('admin.sales-managers.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        // JSON stringify the row for data usage
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
        $('#sales-managers-table').on('click', '.edit-btn', function() {
            var data = $(this).data('row');

            $('#edit_name').val(data.name);
            var email = data.user ? data.user.email : (data.email || '');
            var address = data.user ? data.user.address : (data.address || ''); // Assuming address is on user or sales manager? Table col says 'user.address' but create form says 'address'. Controller: 'address' => $request->address. Wait, in create(), address is stored in SM?
            // Controller store(): $user->address is not set? invalid code in store(): 'role'=>'salesmanager', 'status'=>'inactive' on User::create. But address is in SalesManager::create.
            // However, table column 'user.address' implies it is on User?
            // Looking at Controller: 'address' is stored in SalesManager.
            // But 'user.address' in DataTable columns?
            // Wait, previous file had: { data: 'user.address', name: 'user.address' } ?
            // Let's check previous file content.
            // Line 189: { data: 'user.address', name: 'user.address' }
            // Line 89 (Store): 'address' => $request->address maps to SalesManager::create(... 'address' => ...)
            // Line 121 (Update): 'address' => ... maps to SalesManager->update
            // So address is on SalesManager, NOT User directly?
            // BUT, line 24: SalesManager::with('user')->select('sales_managers.*');
            // If address is on SM, it should be data: 'address'.
            // Why was it 'user.address'? Maybe incorrect previous code?
            // I will assume it's on SalesManager as per Controller store/update.
            // I will fix the column to 'address' in my rewrite.

            $('#edit_email').val(email);
            $('#edit_contact_no').val(data.contact_no);
            $('#edit_address').val(data.address);

            var url = "{{ route('admin.sales-managers.update', ':id') }}".replace(':id', data.id);
            $('#editSalesManagerForm').attr('action', url);

            $('#editSalesManagerModal').modal('show');
        });

        // Handle View Button
        $('#sales-managers-table').on('click', '.view-btn', function() {
            var data = $(this).data('row');
            var email = data.user ? data.user.email : (data.email || 'N/A');

            let html = `
                <tr><th>Name</th><td>${data.name}</td></tr>
                <tr><th>Email</th><td>${email}</td></tr>
                <tr><th>Contact No</th><td>${data.contact_no || 'N/A'}</td></tr>
                <tr><th>Address</th><td>${data.address || 'N/A'}</td></tr>
            `;
            $('#showSalesManagerBody').html(html);
            $('#showSalesManagerModal').modal('show');
        });

        // Handle Delete
        $('#sales-managers-table').on('click', '.delete-form button[type="submit"]', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Sales Manager?',
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