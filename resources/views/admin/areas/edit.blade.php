@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Area</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('areas.update', $area->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="district_id" class="form-label">District</label>
                            <select class="form-control" id="district_id" name="district_id" required>
                                <option value="">Select District</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}" {{ $area->district_id == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Area Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $area->name }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection