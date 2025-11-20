@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 p-4">

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Managers</h5>
                    <a href="{{ route('managers.create') }}" class="btn btn-primary fw-bold">
                        <i class="fa fa-plus me-1"></i> Add Manager
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($managers->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-user-tie fa-2x mb-3"></i>
                            <p>No managers found.</p>
                        </div>
                    @else
                        <div class="row g-4">
                            @foreach($managers as $manager)
                            <div class="col-lg-6 col-md-12">
                                <div class="card border rounded-3 shadow-sm h-100">
                                    <div class="card-body">

                                        {{-- Header Section --}}
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="fw-bold mb-0">{{ $manager->name }}</h5>
                                            <div>
                                                @if($manager->user->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-warning">Inactive</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Info Grid --}}
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="mb-1"><strong>Email:</strong> {{ $manager->email }}</p>
                                                <p class="mb-1"><strong>Contact:</strong> {{ $manager->contact_no ?? '-' }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="mb-1"><strong>Distributor:</strong> {{ $manager->distributor->user->name ?? 'N/A' }}</p>
                                                <p class="mb-1"><strong>Address:</strong> {{ $manager->address ?? '-' }}</p>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- Footer Buttons --}}
                                    <div class="card-footer bg-light border-0 d-flex justify-content-end gap-2">
                                        <a href="{{ route('managers.edit', $manager->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('managers.destroy', $manager->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection