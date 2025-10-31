@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Edit District</h5>
                </div>
                <div class="card-body">
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
            </div>
        </div>
    </div>
</div>
@endsection