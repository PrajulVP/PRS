@extends('layouts.admin')

@push('styles')
<!-- DataTables Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
    /* Search Bar Left */
    .dataTables_filter {
        text-align: left !important;
    }
    .dataTables_filter input {
        width: 230px !important;
        margin-left: 10px !important;
    }

    /* Show Entries Right */
    .dataTables_length {
        text-align: right !important;
    }
    .dataTables_length select {
        margin: 0 5px !important;
        width: 70px !important;
    }
</style>
@endpush


@section('page-body')
<div class="container-fluid">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">
                <i data-feather="map-pin" class="me-2"></i>
                <h5 class="mb-0">Areas</h5>
            </div>

            <a href="{{ route('areas.create') }}" class="btn btn-primary d-flex align-items-center">
                <i data-feather="plus" class="me-1"></i>
                <span>Add Area</span>
            </a>

        </div>


        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table id="areas-table" class="table table-striped table-hover display">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
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
@endsection



@push('scripts')
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#areas-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('areas.index') }}",
            type: "GET",
        },
        columns: [
            { data: "id", name: "id" },
            { data: "name", name: "name" },
            { data: "district_name", name: "district_name" },
            {
                data: "actions",
                name: "actions",
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let editUrl = "{{ route('areas.edit', ':id') }}".replace(':id', row.id);
                    let deleteUrl = "{{ route('areas.destroy', ':id') }}".replace(':id', row.id);
                    let token = "{{ csrf_token() }}";

                    return `
                        <a href="${editUrl}" class="btn btn-primary btn-sm">
                            <i data-feather="edit"></i>
                        </a>

                        <form action="${deleteUrl}" method="POST" style="display:inline-block;">
                            <input type="hidden" name="_token" value="${token}">
                            <input type="hidden" name="_method" value="DELETE">

                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure?')">
                                <i data-feather="trash-2"></i>
                            </button>
                        </form>
                    `;
                }
            }
        ],

        /** Layout: Buttons -> Search Left & Show Entries Right */
        dom: "<'row mb-3'<'col-sm-12'B>>" +
             "<'row mb-3 d-flex align-items-center'<'col-md-6'f><'col-md-6 text-end'l>>" +
             "rtip",

        buttons: [
            { extend: "copy", className: "btn btn-primary btn-sm" },
            { extend: "csv", className: "btn btn-primary btn-sm" },
            { extend: "excel", className: "btn btn-primary btn-sm" },
            { extend: "pdf", className: "btn btn-primary btn-sm" },
            { extend: "print", className: "btn btn-primary btn-sm" },
        ],

        drawCallback: function() {
            feather.replace();
        }
    });
});
</script>
@endpush
