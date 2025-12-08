@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pending Approvals</h5>
        </div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

            <table class="table table-striped table-hover" id="pending-table">
                <thead>
                    @if($type == 'superadmin')
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                    @elseif($type == 'admin')
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Sales Manager</th>
                        <th>Actions</th>
                    </tr>
                    @elseif($type == 'salesmanager')
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Added By (FS)</th>
                        <th>Actions</th>
                    </tr>
                    @endif
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
@push('scripts')
<script>
    $(document).ready(function() {
        var type = "{{ $type }}";

        // Define columns based on type
        var cols = [{
                data: 'name',
                name: 'name'
            },
            {
                data: 'email',
                name: 'email'
            }
        ];

        if (type === 'superadmin') {
            cols.push({
                data: 'role',
                name: 'role'
            });
        } else if (type === 'admin') {
            cols.push({
                data: 'linked_manager',
                name: 'linked_manager'
            });
        } else if (type === 'salesmanager') {
            cols.push({
                data: 'added_by',
                name: 'added_by'
            });
        }

        // Actions Column
        cols.push({
            data: null,
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
                let csrf = "{{ csrf_token() }}";
                let methodField = (type === 'superadmin') ? '' : '<input type="hidden" name="_method" value="PATCH">';
                // Note: User activation is POST usually, others PATCH as per previous code.
                // Checking logic:
                // SuperAdmin activating User: route admin.users.activate (POST)
                // Admin/SM activating Resource: route admin.fieldstaffs.activate (PATCH)

                // Wait, previous file line 55: POST admin.users.activate
                // Line 65: PATCH admin.fieldstaffs.activate
                // Line 76: PATCH admin.retailers.activate

                let method = 'POST';
                if (data.role_type !== 'superadmin') {
                    method = 'POST'; // We will use _method field to simulate PATCH
                }

                let html = `<form action="${data.activate_url}" method="POST" class="d-inline">
                <input type="hidden" name="_token" value="${csrf}">`;

                if (data.role_type !== 'superadmin') {
                    html += `<input type="hidden" name="_method" value="PATCH">`;
                }

                html += `<button type="submit" class="btn btn-sm btn-success">Activate</button>
            </form>`;
                return html;
            }
        });

        $('#pending-table').DataTable({
            dom: 'Bfrtip',
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            ajax: "{{ route('pending-approvals') }}", // Default index route serves AJAX now
            columns: cols
        });
    });
</script>
@endpush