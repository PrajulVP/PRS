@extends('layouts.admin')

@section('page-body')
<div class="container p-4">
    <h2>Districts</h2>

    <!-- Add District button -->
    <a href="{{ route('districts.create') }}" class="btn btn-primary mb-3">Add District</a>

    <table class="table table-bordered" id="districtTable">
        <thead>
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($districts as $district)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $district->name }}</td>
                <td>
                    <a href="{{ route('districts.edit', $district->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('districts.destroy', $district->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
