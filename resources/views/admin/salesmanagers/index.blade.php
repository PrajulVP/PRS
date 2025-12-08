@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 p-4">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Managers</h5>
                    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#createSalesManagerModal">
                        <i class="fa fa-plus me-1"></i> Add Sales Manager
                    </button>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="sales-managers-table">
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
                            <tbody>
                               
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createSalesManagerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Sales Manager</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.salesmanagers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Contact No</label>
                            <input type="text" name="contact_no" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Address</label>
                            <textarea name="address" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Create</button>
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
                            <label>Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Password (Leave blank to keep unchanged)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Contact No</label>
                            <input type="text" name="contact_no" id="edit_contact_no" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Address</label>
                            <textarea name="address" id="edit_address" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
    <!-- DataTables with Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <!-- DataTables Bootstrap 5 -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        var table = $('#sales-managers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.salesmanagers.index') }}",
            type: 'GET'
        },
        columns: [
            { data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + 1; // Generates serial number based on row index
                }
            },
            { data: 'name', name: 'name' },
            { data: 'user.email', name: 'user.email' },
            { data: 'contact_no', name: 'contact_no' },
            { data: 'user.address', name: 'user.address' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        dom: "<'row mb-3'<'col-sm-12'B>>" + 
                            "<'row mb-3 d-flex align-items-center'<'col-md-6 d-flex justify-content-start'f><'col-md-6 d-flex justify-content-end text-end'l>>" +
                            "rtip",
        buttons: [
            { extend: 'copy', className: 'btn btn-primary btn-sm' },
            { extend: 'csv', className: 'btn btn-sm' },
            { extend: 'excel', className: 'btn btn-sm' },
            { extend: 'pdf', className: 'btn btn-sm' },
            { extend: 'print', className: 'btn btn-sm' },
        ]
    });

    // Intercept Edit Button Click
    $('#sales-managers-table').on('click', 'a.edit-btn, a[href*="/edit"]', function(e) {
        e.preventDefault();
        var data = table.row($(this).parents('tr')).data();
        
        // Populate form
        $('#edit_name').val(data.name);
        // Access nested user properties safely
        var email = data.user ? data.user.email : (data.email || '');
        var address = data.user ? data.user.address : (data.address || '');
        
        $('#edit_email').val(email);
        $('#edit_contact_no').val(data.contact_no);
        $('#edit_address').val(address);
        
        var url = "{{ route('admin.salesmanagers.update', ':id') }}";
        url = url.replace(':id', data.id);
        $('#editSalesManagerForm').attr('action', url);
        
        $('#editSalesManagerModal').modal('show');
    });

    </script>

@endpush