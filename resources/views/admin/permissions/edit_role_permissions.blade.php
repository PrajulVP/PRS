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
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="permissions[{{ $categoryData['id'] }}][can_{{ $action }}]" 
                                                               value="1" 
                                                               style="border: 1px solid #727272ff;"
                                                               {{ $categoryData['can_' . $action] ? 'checked' : '' }}
                                                               {{ ($role->name === 'superadmin' || ($role->name === 'admin' && !Auth::user()->hasRole('superadmin')) || ($categoryData['short_code'] === 'permissions' && !Auth::user()->hasRole('superadmin'))) ? 'disabled' : '' }}>
                                                    </div>
                                                </td>                                                
                                                @endforeach
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