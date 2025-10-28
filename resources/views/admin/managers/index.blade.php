@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Managers</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('managers.create') }}" class="btn btn-primary mb-3 mt-2">Add Manager</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($managers as $key => $manager)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $manager->name }}</td>
                <td>{{ $manager->email }}</td>
                <td>
                    <a href="{{ route('managers.edit', $manager->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('managers.destroy', $manager->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
