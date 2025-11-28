@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
    .dataTables_filter { text-align: left !important; }
    .dataTables_length { text-align: right !important; }
    .dataTables_filter input { width: 230px; margin-left: 8px; }

    .side-form {
        background: #ffffff;
        border-left: 3px solid #0d6efd;
        padding: 20px;
        border-radius: 6px;
    }
</style>
@endpush

@section('page-body')
<div class="container-fluid">

    <div class="row">
        
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Areas</h5>
                </div>

                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <table id="areas-table" class="table table-striped table-hover display">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Area Name</th>
                                <th>District</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="side-form shadow-sm">
                <h5>Add New Area</h5>
                <hr>

                <form action="{{ route('areas.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">District</label>
                        <select name="district_id" class="form-control" required>
                            <option value="">Select District</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Area Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <button class="btn btn-primary w-100">Add Area</button>
                </form>

            </div>
        </div>

    </div>

</div>


<div class="modal fade" id="editAreaModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" id="editAreaForm">
        @csrf
        @method('PUT')

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">District</label>
                    <select name="district_id" id="edit_district_id" class="form-control" required>
                        <option value="">Select District</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Area Name</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Update Area</button>
            </div>

        </div>

    </form>
  </div>
</div>
@endsection


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


<script>
$(function () {

    let table = $('#areas-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('areas.index') }}",
        dom: 'Bfrtip',
        buttons: [
            { extend: 'csv', className: 'btn btn-sm btn-outline-secondary' },
            { extend: 'excel', className: 'btn btn-sm btn-outline-success' },
            { extend: 'pdf', className: 'btn btn-sm btn-outline-danger' },
            { extend: 'print', className: 'btn btn-sm btn-outline-info' },
        ],
        columns: [
            { data: 'id',
              orderable: false,
              searchable: false,
              render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'name' },
            { data: 'district_name' },
            {
                data: null,
                className: "text-center",
                render: function(row) {
                    return `
                        <div class="d-flex justify-content-center align-items-center">

                            <!-- Small Edit Button -->
                            <button 
                                class="btn btn-primary btn-sm px-2 py-1 me-1 editAreaBtn"
                                data-id="${row.id}"
                                data-name="${row.name}"
                                data-district="${row.district_id}">
                                Edit
                            </button>

                            <!-- Small Red Delete Button -->
                            <form method="POST" action="/areas/${row.id}" class="d-inline deleteAreaForm" data-name="${row.name}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">

                                <button class="btn btn-danger btn-sm px-2 py-1">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    `;
                }
            }
        ],
        drawCallback: function(){ feather.replace(); }
    });


    /** OPEN EDIT MODAL **/
    $(document).on("click", ".editAreaBtn", function() {
        let id = $(this).data("id");
        let name = $(this).data("name");
        let district = $(this).data("district");

        $("#edit_name").val(name);
        $("#edit_district_id").val(district);
        $("#editAreaForm").attr("action", "{{ url('areas') }}/" + id);

        $("#editAreaModal").modal("show");
    });

    // SweetAlert Delete Confirmation
    $(document).on("submit", ".deleteAreaForm", function(e){
        e.preventDefault();  // Stop default form submission

        let form = this;
        let areaName = $(this).data("name");

        Swal.fire({
            title: "Delete Area?",
            text: "Are you sure you want to delete: " + areaName + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, Delete",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Submit the form if confirmed
            }
        });
    });

});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush