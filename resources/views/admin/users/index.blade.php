@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>User Management</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">User Management</li>
                    <li class="breadcrumb-item active">All Users</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="accordion" id="roleAccordion">

        @foreach(Spatie\Permission\Models\Role::all() as $key => $role)
            <div class="accordion-item mb-2">
                <h2 class="accordion-header" id="heading{{ $key }}">
                    <button class="accordion-button {{ $key != 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $key }}">
                        <strong>{{ ucfirst($role->name) }}s</strong>
                    </button>
                </h2>

                <div id="collapse{{ $key }}" class="accordion-collapse collapse {{ $key == 0 ? 'show' : '' }}" data-bs-parent="#roleAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            @php
                                $roleUsers = $users->filter(fn($user) => $user->hasRole($role->name));
                            @endphp

                            @forelse ($roleUsers as $user)
                            <div class="col-md-4 mb-4">
                                <div class="card p-4">
                                    <h5 class="card-title">{{ $user->name }}</h5>
                                    <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
                                    <div class="d-flex justify-content-between">
                                        @role('superadmin|admin|manager|distributor')

                                        {{-- Edit button --}}
                                        @if (
                                            auth()->id() === $user->id || 
                                            (auth()->user()->hasRole('superadmin') && !$user->hasRole('superadmin')) || 
                                            (auth()->user()->hasRole('admin') && $user->hasAnyRole(['manager', 'distributor', 'fieldstaff', 'retailer'])) ||
                                            (auth()->user()->hasRole('manager') && $user->hasAnyRole(['distributor', 'fieldstaff', 'retailer'])) ||
                                            (auth()->user()->hasRole('distributor') && $user->hasAnyRole(['fieldstaff', 'retailer']))
                                        )
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        @endif

                                        {{-- Delete button --}}
                                        @if (
                                            (auth()->user()->hasRole('superadmin') && !$user->hasRole('superadmin')) ||
                                            (auth()->user()->hasRole('admin') && in_array($user->role, ['manager', 'distributor', 'fieldstaff', 'retailer'])) ||
                                            (auth()->user()->hasRole('manager') && in_array($user->role, ['distributor', 'fieldstaff', 'retailer'])) ||
                                            (auth()->user()->hasRole('distributor') && in_array($user->role, ['fieldstaff', 'retailer']))

                                        )
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete user?')">Delete</button>
                                            </form>
                                        @endif

                                        @endrole
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-center">No users found</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        @endforeach

    </div>
</div>
@endsection
