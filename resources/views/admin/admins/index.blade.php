
@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Admins</h5>
                    <a href="{{ route('admins.create') }}" class="btn btn-primary">Create Admin</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($admins as $admin)
                        <div class="col-md-4 mb-4">
                            <div class="card p-4">
                                <h5 class="card-title">{{ $admin->name }}</h5>
                                <p class="card-text"><strong>ID:</strong> {{ $admin->id }}</p>
                                <p class="card-text"><strong>Email:</strong> {{ $admin->email }}</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admins.edit', $admin->id) }}" class="btn btn-primary">Edit</a>
                                    <form action="{{ route('admins.destroy', $admin->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
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
