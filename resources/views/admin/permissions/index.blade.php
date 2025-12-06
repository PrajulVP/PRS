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
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col">Role Name</th>
                                    <th scope="col" class="text-center" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $index => $role)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-light text-dark border border-dark border-1 fs-6">{{ ucfirst($role->name) }}</span></td>
                                    <td class="text-center">
                                        @if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
                                        <a href="{{ route('admin.permissions.edit', $role) }}" class="btn btn-primary btn-sm btn-pill px-4">
                                            <i class="fa fa-key me-1"></i> Manage Permissions
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection