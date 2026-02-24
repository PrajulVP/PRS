@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Manage Permissions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="roles-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Role Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $index => $role)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ ucfirst($role->name) }}</td>
                            <td>
                                <a href="{{ route('admin.permissions.edit', $role->id) }}" class="btn btn-primary btn-sm">Manage</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection