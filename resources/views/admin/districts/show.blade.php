@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <h1>District Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Name: {{ $district->name }}</h5
                <p class="card-text">District: {{ $district->district->name ?? 'N/A' }}</p>
                <a href="{{ route('district.edit', $area->id) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('district.destroy', $area->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
                <a href="{{ route('district.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
@endsection