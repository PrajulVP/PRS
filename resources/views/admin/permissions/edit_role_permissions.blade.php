@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Manage Permissions for {{ ucfirst($role->name) }}</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">User Management</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Manage Permissions</a></li>
                    <li class="breadcrumb-item active">{{ ucfirst($role->name) }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

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
                                        <th>Category</th>
                                        @foreach($actions as $action)
                                            <th class="text-center">{{ ucfirst($action) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupedPermissions as $groupName => $groupData)
                                        @php $firstCategoryInGroup = true; @endphp
                                        @foreach($groupData['categories'] as $categoryName => $categoryData)
                                            <tr>
                                                @if($firstCategoryInGroup)
                                                    <td rowspan="{{ count($groupData['categories']) }}" class="align-middle font-weight-bold">
                                                        {{ ucfirst($groupName) }}
                                                    </td>
                                                    @php $firstCategoryInGroup = false; @endphp
                                                @endif
                                                <td>{{ ucfirst($categoryName) }}</td>
                                                @foreach($actions as $action)
                                                <td class="text-center">
                                                    @php
                                                        $permission = $categoryData['permissions'][$action] ?? null;
                                                        $isChecked = false;
                                                        $isDisabled = false;
                    
                                                        if ($permission) {
                                                            // For superadmin and admin, always check by default for display
                                                            if ($role->name === 'superadmin' || $role->name === 'admin') {
                                                                $isChecked = true;
                                                            } else {
                                                                $isChecked = $role->hasPermissionTo($permission->name);
                                                            }
                                                            $isDisabled = false; // Enabled if permission exists
                                                        } else {
                                                            $isDisabled = true; // Disabled if permission doesn't exist
                                                        }
                                                    @endphp
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="permissions[{{ $permission->id ?? '' }}]" 
                                                               value="1" 
                                                               style="border: 1px solid #727272ff;"
                                                               {{ $isChecked ? 'checked' : '' }}
                                                               {{ $isDisabled ? 'disabled' : '' }}>
                                                    </div>
                                                </td>                                                @endforeach
                                            </tr>
                                        @endforeach
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
