@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Managers</h5>
                    <a href="{{ route('managers.create') }}" class="btn btn-primary">Add Manager</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="row">
                        @foreach($managers as $manager)
                        <div class="col-md-4 mb-4">
                            <div class="card p-4">
                                <h5 class="card-title">{{ $manager->name }}</h5>
                                <p class="card-text"><strong>Email:</strong> {{ $manager->email }}</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('managers.edit', $manager->id) }}" class="btn btn-primary">Edit</a>
                                    <form action="{{ route('managers.destroy', $manager->id) }}" method="POST" style="display: inline-block;">
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
