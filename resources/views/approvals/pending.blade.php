@extends('layouts.admin')


@section('page-body')
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Pending Approvals for {{ $user_type }}</h5>
                </div>
                <div class="card-body">
                    @if($users_to_approve->isEmpty())
                        <p>No users are currently pending approval.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="pending-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users_to_approve as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->user->name }}</td>
                                            <td>{{ $item->user->email }}</td>
                                            <td>{{ $item->user->role }}</td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $item->user->id) }}" class="btn btn-sm btn-primary">View</a>
                                                <form action="{{ route('admin.users.activate', $item->user) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        Approve
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
@endpush

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<script>
$(document).ready(function() {
    $('#pending-table').DataTable({
        responsive: true,
        pageLength: 10,
        autoWidth: false,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search users..."
        },
        dom: "<'row mb-3'<'col-md-6'l><'col-md-6 d-flex justify-content-end'f>>" +
             "rt" +
             "<'row mt-3'<'col-md-6'i><'col-md-6 d-flex justify-content-end'p>>"
    });
});
</script>

@endpush
