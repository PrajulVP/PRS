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
                            <a href="{{ route('admin.permissions.edit', $role) }}" class="list-group-item list-group-item-action">
                                {{ ucfirst($role->name) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection