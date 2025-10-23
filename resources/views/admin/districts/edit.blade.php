@extends('layouts.admin')
@section('content')
<div class="container p-4">
<h2>Edit District</h2>
<form action="{{ route('districts.update',$district->id) }}" method="POST">@csrf @method('PUT')
<div class="mb-3">
<label>Name</label>
<input type="text" name="name" value="{{ $district->name }}" class="form-control" required>
</div>
<button type="submit" class="btn btn-primary">Update</button>
</form>
</div>
@endsection