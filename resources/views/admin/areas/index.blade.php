@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <h1>Area List</h1>
        <a href="{{ route('areas.create') }}" class="btn btn-primary">Add New Area</a>
        <table class="table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>District</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($areas as $area)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $area->name }}</td>
                        <td>{{ $area->district->name ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('areas.edit', $area->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('areas.destroy', $area->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection