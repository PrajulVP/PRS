@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid p-4">
        <h1>Create New Area</h1>
        <form action="{{ route('areas.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="district_id" class="form-label">District</label>
                <select class="form-control" id="district_id" name="district_id" required>
                    <option value="">Select District</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Area Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
@endsection