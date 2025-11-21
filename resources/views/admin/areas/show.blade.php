@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <h1>Area: {{ $area->name }}</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Name: {{ $area->name }}</h5>
                <p class="card-text">District: {{ $area->district->name ?? 'N/A' }}</p>
                <a href="{{ route('areas.edit', $area->id) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('areas.destroy', $area->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
                <a href="{{ route('areas.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
@endsection