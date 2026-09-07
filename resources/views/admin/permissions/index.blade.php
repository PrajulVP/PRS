@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Permissions</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info d-flex align-items-center mb-3 py-2 px-3 border-0 rounded-3" style="background-color: rgba(var(--med-primary-rgb), 0.08); color: var(--med-primary);">
                <i class="fa fa-info-circle me-2 fs-6"></i>
                <small class="fw-semibold"><strong>Note:</strong> Field Staff members do not have web application access and operate strictly through the mobile app.</small>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0" id="roles-table">
                    <thead class="table-light">
                        <tr>
                            <th width="80">No.</th>
                            <th>Role Name</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $index => $role)
                        @php
                            $isSuperAdminUser = Auth::user()->hasRole('superadmin');
                            $isDisabled = false;
                            $disabledTooltip = '';
                            
                            // Superadmin role is always disabled as Superadmin has all permissions by default
                            if ($role->name === 'superadmin') {
                                $isDisabled = true;
                                $disabledTooltip = 'Superadmin has full access by default';
                            }
                            
                            // Admin role can only be managed by Superadmin
                            if ($role->name === 'admin' && !$isSuperAdminUser) {
                                $isDisabled = true;
                                $disabledTooltip = 'Only Superadmin can modify Admin permissions';
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ ucfirst($role->name) }}</td>
                            <td class="text-center">
                                @if($isDisabled)
                                    <button class="btn btn-primary btn-sm" disabled style="pointer-events: none;" title="{{ $disabledTooltip }}">Manage</button>
                                @else
                                    <a href="{{ route('admin.permissions.edit', $role->id) }}" class="btn btn-primary btn-sm">Manage</a>
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
@endsection