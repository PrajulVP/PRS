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
        {{-- Left Column: Quick Add Form --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h5><i class="fa fa-plus-circle me-2 text-primary"></i>Add Area</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('areas.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="create_district_id" class="form-label fw-bold">District</label>
                            <select name="district_id" id="create_district_id" class="form-control" required>
                                <option value="">Select District</option>
                                @foreach($districts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select the district this area belongs to.</small>
                        </div>
                        <div class="mb-4">
                            <label for="create_name" class="form-label fw-bold">Area Name</label>
                            <input type="text" name="name" id="create_name" class="form-control" placeholder="e.g. Aluva" required>
                            <small class="text-muted">Enter a unique name for the area.</small>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                <i class="fa fa-save me-2"></i>Save Area
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right Column: Areas Table --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 pb-0">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0"><i class="fa fa-map-pin me-2 text-primary"></i>Areas</h5>
                        <div class="ms-3 d-flex gap-2">
                            <span class="badge badge-light-primary px-3 py-2 rounded-pill" style="font-size: 0.85rem;">
                                <i class="fa fa-location-arrow me-1"></i> Total Areas: {{ $totalAreas ?? 0 }}
                            </span>
                            <span class="badge badge-light-info px-3 py-2 rounded-pill" style="font-size: 0.85rem;">
                                <i class="fa fa-map me-1"></i> Districts: {{ $totalDistricts ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="display table table-striped table-hover" id="areas-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Area Name</th>
                                    <th>District</th>
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


{{-- Edit Area Modal --}}
<div class="modal fade" id="editAreaModal" tabindex="-1" aria-labelledby="editAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAreaModalLabel">Edit Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAreaForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_district_id" class="form-label">District</label>
                        <select name="district_id" id="edit_district_id" class="form-control" required>
                            <option value="">Select District</option>
                            @foreach($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Area Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Area</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#areas-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('areas.index') }}",
            order: [
                [1, 'asc']
            ], // Default sort by Area Name to avoid sorting on the index column
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
                    data: 'district_name',
                    name: 'district_name'
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(id, type, row) {
                        let deleteUrl = "{{ route('areas.destroy', ':id') }}".replace(':id', id);
                        let csrf = "{{ csrf_token() }}";
                        let rowData = JSON.stringify(row).replace(/"/g, '&quot;');

                        return `
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm btn-primary edit-btn" data-area="${rowData}"><i class="fa fa-edit"></i></button>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-url="${deleteUrl}"><i class="fa fa-trash"></i></button>
                        </div>
                    `;
                    }
                }
            ],
            dom: "<'row mb-3'<'col-sm-12'B>>" + // Buttons on top
                "<'row mb-3'<'col-md-6'l><'col-md-6'f>>" + // 'l' (length) on left, 'f' (filter/search) on right
                "<'row '<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-12 col-xxl-5 d-flex justify-content-center justify-content-xxl-start align-items-center'i><'col-12 col-xxl-7 d-flex justify-content-center justify-content-xxl-end align-items-center'p>>",
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });

        // --- Create Modal AJAX ---
        $('#createAreaModal form').submit(function(e) {
            e.preventDefault();
            let form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.status) {
                        $('#createAreaModal').modal('hide');
                        form[0].reset();
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Created!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Something went wrong', 'error');
                    }
                },
                error: function(xhr) {
                    let err = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                    Swal.fire('Error', err, 'error');
                }
            });
        });

        // --- Edit Modal Logic ---
        $('#areas-table').on('click', '.edit-btn', function() {
            var area = $(this).data('area');

            $('#edit_name').val(area.name);
            $('#edit_district_id').val(area.district_id);

            let updateUrl = "{{ route('areas.update', ':id') }}".replace(':id', area.id);
            $('#editAreaForm').attr('action', updateUrl);

            $('#editAreaModal').modal('show');
        });

        // --- Edit Modal AJAX ---
        $('#editAreaForm').submit(function(e) {
            e.preventDefault();
            let form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.status) {
                        $('#editAreaModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Update failed', 'error');
                    }
                },
                error: function(xhr) {
                    let err = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                    Swal.fire('Error', err, 'error');
                }
            });
        });

        // --- Delete Logic (SweetAlert + AJAX) ---
        $('#areas-table').on('click', '.delete-btn', function() {
            let url = $(this).data('url');

            Swal.fire({
                title: 'Delete Area?',
                text: "Are you sure you want to delete this area?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            if (res.status) {
                                table.ajax.reload();
                                Swal.fire('Deleted!', res.message, 'success');
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let err = 'Could not delete.';
                            if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                            Swal.fire('Error', err, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush