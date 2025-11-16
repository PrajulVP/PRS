@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Manage Permissions</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">User Management</li>
                    <li class="breadcrumb-item active">Manage Permissions</li>
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
                    <h5>Manage Permissions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($roles as $role)
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                {{ ucfirst($role->name) }}
                                @if (Auth::user()->hasPermissionToCategory('permissions', 'edit'))
                                <a href="{{ route('admin.permissions.edit', $role) }}" class="btn btn-primary btn-sm">
                                    Manage Permissions
                                </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection