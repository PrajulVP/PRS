@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Field Staff</h5>
                    <a href="{{ route('fieldstaffs.create') }}" class="btn btn-primary">Add Field Staff</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="row">
                        @foreach($fieldstaffs as $staff)
                        <div class="col-md-4 mb-4">
                            <div class="card p-4">
                                <h5 class="card-title">{{ $staff->user->name }}</h5>
                                <p class="card-text"><strong>Distributor:</strong> {{ $staff->distributor->company_name ?? '-' }}</p>
                                <p class="card-text"><strong>District:</strong> {{ $staff->user->district->name ?? '' }}</p>
                                <p class="card-text"><strong>Area:</strong> {{ $staff->user->area->name ?? '' }}</p>
                                <p class="card-text"><strong>Contact:</strong> {{ $staff->user->contact_no }}</p>
                                <p class="card-text"><strong>Email:</strong> {{ $staff->user->email }}</p>
                                <p class="card-text"><strong>Address:</strong> {{ $staff->user->address }}</p>
                                <p class="card-text"><strong>Status:</strong> {{ ucfirst($staff->status) }}</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('fieldstaffs.edit', $staff->id) }}" class="btn btn-primary">Edit</a>
                                    <form action="{{ route('fieldstaffs.destroy', $staff->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection