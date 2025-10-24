@extends('layouts.admin')

@section('page-body')
    <div class="container-fluid">
        <h1>Create New District</h1>
        <form action="{{ route('districts.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">District Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
@endsection