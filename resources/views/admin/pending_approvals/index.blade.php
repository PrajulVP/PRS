@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 p-4">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending Approvals</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
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
                                        <th>Added By (Field Staff)</th>
                                        <th>Actions</th>
                                    </tr>
                                @endif
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    @if($type == 'superadmin')
                                        <td>{{ $item->user->name }}</td>
                                        <td>{{ $item->user->email }}</td>
                                        <td>{{ $item->user->role }}</td>
                                        <td>
                                            <form action="{{ route('admin.users.activate', $item->user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                            </form>
                                        </td>
                                    @elseif($type == 'admin')
                                        <td>{{ $item->user->name }}</td>
                                        <td>{{ $item->user->email }}</td>
                                        <td>{{ $item->salesmanager->user->name ?? 'N/A' }}</td>
                                        <td>
                                            <form action="{{ route('admin.fieldstaffs.activate', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                            </form>
                                        </td>
                                    @elseif($type == 'salesmanager')
                                        <td>{{ $item->user->name }}</td>
                                        <td>{{ $item->user->email }}</td>
                                        <td>{{ $item->fieldstaff->user->name ?? 'N/A' }}</td>
                                        <td>
                                            <form action="{{ route('admin.retailers.activate', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="fa fa-box-open fa-2x mb-3"></i>
                                        <p>No pending approvals found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
