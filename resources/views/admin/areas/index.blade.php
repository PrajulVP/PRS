@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Area List</h5>
                    <a href="{{ route('areas.create') }}" class="btn btn-primary">Add New Area</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($areas as $area)
                        <div class="col-md-4 mb-4">
                            <div class="card p-4">
                                <h5 class="card-title">{{ $area->name }}</h5>
                                <p class="card-text"><strong>District:</strong> {{ $area->district->name ?? 'N/A' }}</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('areas.edit', $area->id) }}" class="btn btn-warning">Edit</a>
                                    <form action="{{ route('areas.destroy', $area->id) }}" method="POST" style="display:inline;">
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