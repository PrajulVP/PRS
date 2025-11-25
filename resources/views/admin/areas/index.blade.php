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
        
        <!-- LEFT SIDE TABLE -->
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
                                <th>ID</th>
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

        <!-- RIGHT SIDE CREATE FORM -->
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


<!-- EDIT MODAL -->
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

<script>
$(function () {

        let table = $('#areas-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('areas.index') }}",
            dom: 'Bfrtip', // Add this line for buttons
            buttons: [
                { extend: 'csv', className: 'btn btn-sm' },
                { extend: 'excel', className: 'btn btn-sm' },
                { extend: 'pdf', className: 'btn btn-sm' },
                { extend: 'print', className: 'btn btn-sm' },
            ],
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'district_name' },
                {
                    data: null,
                    className: "text-center",
                    render: function(row){
                        return `
                            <button class="btn btn-sm editAreaBtn"
                                data-id="${row.id}"
                                data-name="${row.name}"
                                data-district="${row.district_id}">
                                <i data-feather="edit"></i>
                            </button>
    
                            <form method="POST" action="/admin/areas/${row.id}"
                                  style="display:inline-block;">
                                  @csrf @method('DELETE')
                                <button class="btn btn-sm" onclick="return confirm('Delete this area?')">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </form>
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
            $("#editAreaForm").attr("action", "{{ url('admin/areas') }}/" + id);
    
            $("#editAreaModal").modal("show");
        });
});
</script>
@endpush
