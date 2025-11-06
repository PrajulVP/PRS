@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Manage Permissions for {{ ucfirst($role->name) }}</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('admin.permissions.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th class="text-center">Permissions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissions as $group => $permissionGroup)
                                        <tr>
                                            <td>{{ ucfirst($group) }}</td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    @foreach($permissionGroup as $permission)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ ucwords(implode(' ', array_slice(explode(' ', $permission->name), 1))) }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Permissions</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
