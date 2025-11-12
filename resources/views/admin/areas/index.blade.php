@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Areas</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Areas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i data-feather="map-pin" class="me-2"></i>Areas</h5>
            <a href="{{ route('areas.create') }}" class="btn btn-primary"><i data-feather="plus" class="me-1"></i>Add Area</a>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="display table table-striped table-hover" id="areas-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>District</th>
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
        $(document).ready(function() {
            $('#areas-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('areas.index') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'district_name', name: 'district_name' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var editUrl = "{{ route('areas.edit', ':id') }}".replace(':id', row.id);
                            var deleteUrl = "{{ route('areas.destroy', ':id') }}".replace(':id', row.id);
                            var csrfToken = "{{ csrf_token() }}";

                            return `
                                <a href="${editUrl}" class="btn btn-primary btn-sm"><i data-feather="edit"></i></a>
                                <form action="${deleteUrl}" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i data-feather="trash-2"></i></button>
                                </form>
                            `;
                        }
                    }
                ],
                dom: 'Blfrtip',
                buttons: [
                    { extend: 'copy', className: 'btn btn-primary btn-sm' },
                    { extend: 'csv', className: 'btn btn-primary btn-sm' },
                    { extend: 'excel', className: 'btn btn-primary btn-sm' },
                    { extend: 'pdf', className: 'btn btn-primary btn-sm' },
                    { extend: 'print', className: 'btn btn-primary btn-sm' },
                ],
                drawCallback: function() {
                    feather.replace();
                }
            });
        });
    </script>
@endpush
