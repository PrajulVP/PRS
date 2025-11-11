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
                                        <th class="text-center">Assign Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissionGroups as $group)
                                        @php $firstCategoryInGroup = true; @endphp
                                        @foreach($group->permissionCategories as $category)
                                            <tr>
                                                @if($firstCategoryInGroup)
                                                    <td rowspan="{{ count($group->permissionCategories) }}" class="align-middle font-weight-bold">
                                                        {{ ucfirst($group->name) }}
                                                    </td>
                                                    @php $firstCategoryInGroup = false; @endphp
                                                @endif
                                                <td>{{ ucfirst($category->name) }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $isChecked = in_array($category->id, $assignedCategoryIds);
                                                        $isDisabled = ($role->name === 'superadmin' || $role->name === 'admin');
                                                    @endphp
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="permission_categories[]" 
                                                               value="{{ $category->id }}" 
                                                               style="border: 1px solid #727272ff;"
                                                               {{ $isChecked ? 'checked' : '' }}
                                                               {{ $isDisabled ? 'disabled' : '' }}>
                                                    </div>
                                                </td>
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
