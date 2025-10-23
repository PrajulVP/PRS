@extends('layouts.admin')
@section('content')
<div class="container mt-4">
<h2>Add New Area</h2>
<form action="{{ route('areas.store') }}" method="POST"> @csrf
<div class="mb-3">
<label>Select District</label>
<select name="district_id" class="form-control" required>
@foreach($districts as $district)
<option value="{{ $district->id }}">{{ $district->name }}</option>
@endforeach
</select>
</div>
<div class="mb-3">
<label>Area Name</label>
<input type="text" name="name" class="form-control" required>
</div>
<button class="btn btn-primary">Save</button>
</form>
</div>
@endsection