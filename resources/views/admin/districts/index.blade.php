@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
    /* Smaller table rows */
    table.table-sm > :not(caption) > * > * {
        padding: 6px 10px !important;
        font-size: 13px !important;
    }

    /* Side form UI */
    .side-form {
        background: #ffffff;
        border-left: 3px solid #0d6efd;
        border-radius: 6px;
        padding: 15px;
    }

    .dataTables_filter input {
        width: 180px !important;
        padding: 3px 6px !important;
        font-size: 13px !important;
        border-radius: 4px;
    }
    .dataTables_length {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
    }

    .dataTables_length label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
    }


    .dataTables_length select {
        padding: 3px;
        font-size: 13px;
    }

    .dt-button.btn {
        padding: 3px 8px !important;
        font-size: 12px !important;
        border-radius: 4px !important;
    }
</style>
@endpush


@section('page-body')
<div class="container-fluid py-3">

    <div class="row">

        <!-- LEFT: ADD DISTRICT FORM -->
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm">

                <div class="card-header text-white py-4">
                    <h6 class="mb-0">➕ Add District</h6>
                </div>

                <div class="card-body">

                    <form action="{{ route('districts.store') }}" method="POST">
                        @csrf

                        <div class="mb-2">
                            <label class="fw-bold small">District Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
                            Add District
                        </button>
                    </form>

                </div>

            </div>
        </div>


        <!-- RIGHT: TABLE -->
        <div class="col-lg-8">
            <div class="card shadow-sm">

                <div class="card-header">
                    <h4 class="mb-0">Districts</h4>
                </div>

                <div class="card-body">

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <table id="districtTable" class="table table-striped table-hover table-sm display w-100">
                        <thead>
                            <tr>
                                <th>no.</th>
                                <th>Name</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>

    </div>
</div>


<!-- ✏️ EDIT MODAL -->
<div class="modal fade" id="editDistrictModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" id="editDistrictForm" class="modal-content">
            @csrf @method('PUT')

            <div class="modal-header bg-primary py-2">
                <h6 class="modal-title text-white">Edit District</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="fw-bold small">District Name</label>
                <input type="text" name="name" id="edit_name" class="form-control form-control-sm" required>
            </div>

            <div class="modal-footer py-1">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success btn-sm">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let table = $('#districtTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('districts.index') }}",
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    // Serial number across pagination
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'name' },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(row) {
                    return `
                        <button class="btn btn-primary btn-sm editBtn px-2 py-1" 
                                data-id="${row.id}" 
                                data-name="${row.name}">
                            Edit
                        </button>
                        <form method="POST" action="/districts/${row.id}" class="d-inline deleteDistrictForm">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="btn btn-danger btn-sm px-2 py-1" type="submit"><i class="fa fa-trash"></i></button>
                        </form>
                    `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'csv', className: 'btn btn-sm btn-outline-secondary' },
            { extend: 'excel', className: 'btn btn-sm btn-outline-success' },
            { extend: 'pdf', className: 'btn btn-sm btn-outline-danger' },
            { extend: 'print', className: 'btn btn-sm btn-outline-info' },
        ]
    });

    // Open edit modal (works on all pages)
    $(document).on('click', '.editBtn', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');

        $('#edit_name').val(name);
        $('#editDistrictForm').attr('action', `/districts/${id}`);

        new bootstrap.Modal('#editDistrictModal').show();
    }); 

    // Delete confirmation
    $(document).on('submit', '.deleteDistrictForm', function(e) {
        e.preventDefault();
        let form = this;
        Swal.fire({
            title: 'Delete District?',
            text: 'Are you sure you want to delete this district?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush