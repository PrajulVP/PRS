@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Retailer Approvals</h5>
        </div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

            <table class="table table-striped table-hover" id="retailer-approval-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Added By (FS)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
        $('#retailer-approval-table').DataTable({
            dom: 'Bfrtip',
            buttons: {
                dom: {
                    button: {
                        className: ''
                    } // Overrides default btn-secondary
                },
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-primary btn-sm'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-info btn-sm'
                    }
                ]
            },
            ajax: window.location.href,
            columns: [{
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'added_by',
                    name: 'added_by'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `<form action="${data.activate_url}" method="POST" class="d-inline">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="_method" value="PATCH">
                                    <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                </form>`;
                    }
                }
            ]
        });
    });
</script>
@endpush