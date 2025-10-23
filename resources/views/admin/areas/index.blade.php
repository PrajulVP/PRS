@extends('layouts.admin')
@section('content')
<div class="container p-4">
<h2>Areas</h2>
<form method="GET" class="mb-3">
<select name="district_id" class="form-select" onchange="this.form.submit()">
<option value="">-- All Districts --</option>
@foreach($districts as $d)
<option value="{{ $d->id }}" {{ request('district_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
@endforeach
</select>
</form>
<a href="{{ route('areas.create') }}" class="btn btn-primary mb-3">Add Area</a>
<table class="table table-bordered">
<thead><tr><th>ID</th><th>District</th><th>Name</th><th>Actions</th></tr></thead>
<tbody>
@foreach($areas as $area)
<tr>
<td>{{ $area->id }}</td>
<td>{{ $area->district->name }}</td>
<td>{{ $area->name }}</td>
<td>
<a href="{{ route('areas.edit',$area->id) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('areas.destroy',$area->id) }}" method="POST" style="display:inline-block;">@csrf @method('DELETE')
<button class="btn btn-sm btn-danger">Delete</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
@endsection