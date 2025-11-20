@extends('layouts.admin')

@section('page-body')


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
                                @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
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