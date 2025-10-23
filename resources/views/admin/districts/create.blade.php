@extends('layouts.admin')
@section('content')
<div class="container p-4">
<h2>Add District</h2>
<form action="{{ route('districts.store') }}" method="POST">@csrf
<div class="mb-3">
<label>Name</label>
<input type="text" name="name" class="form-control" required>
</div>
<button type="submit" class="btn btn-success">Save</button>
</form>
</div>
@endsection