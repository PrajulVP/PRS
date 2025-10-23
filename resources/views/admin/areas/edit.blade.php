@extends('layouts.admin')
@section('content')
<div class="container mt-4">
<h2>Edit Area</h2>
<form action="{{ route('areas.update', $area->id) }}" method="POST"> @csrf @method('PUT')
<div class="mb-3">
<label>Select District</label>
<select name="district_id" class="form-control" required>
@foreach($districts as $district)
<option value="{{ $district->id }}" {{ $area->district_id == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
@endforeach
</select>
</div>
<div class="mb-3">
<label>Area Name</label>
<input type="text" name="name" class="form-control" value="{{ $area->name }}" required>
</div>
<button class="btn btn-success">Update</button>
</form>
</div>
@endsection