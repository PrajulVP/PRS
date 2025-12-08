@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Manage Permissions: {{ ucfirst($role->name) }}</h5>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-sm">Back to Roles</a>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.permissions.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>View</th>
                                <th>Add</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedPermissions as $groupName => $groupData)
                            <tr class="table-secondary">
                                <td colspan="5"><strong>{{ $groupName }}</strong></td>
                            </tr>
                            @foreach($groupData['categories'] as $catName => $cat)
                            <tr>
                                <td>{{ $catName }}</td>
                                <td>
                                    <input type="checkbox" name="permissions[{{ $cat['id'] }}][can_view]"
                                        {{ $cat['can_view'] ? 'checked' : '' }}
                                        {{ $cat['is_disabled'] ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="checkbox" name="permissions[{{ $cat['id'] }}][can_add]"
                                        {{ $cat['can_add'] ? 'checked' : '' }}
                                        {{ $cat['is_disabled'] ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="checkbox" name="permissions[{{ $cat['id'] }}][can_edit]"
                                        {{ $cat['can_edit'] ? 'checked' : '' }}
                                        {{ $cat['is_disabled'] ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="checkbox" name="permissions[{{ $cat['id'] }}][can_delete]"
                                        {{ $cat['can_delete'] ? 'checked' : '' }}
                                        {{ $cat['is_disabled'] ? 'disabled' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection