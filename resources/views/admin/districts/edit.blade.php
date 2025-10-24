@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <h1>Edit District</h1>
        <form action="{{ route('districts.update', $district->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">District Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $district->name }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection