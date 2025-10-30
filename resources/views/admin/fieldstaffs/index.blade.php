@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
<h2>Field Staff</h2>


@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif


<a href="{{ route('fieldstaffs.create') }}" class="btn btn-primary mb-3 mt-2">Add Field Staff</a>


<table class="table table-bordered table-striped">
<thead>
<tr>
<th>#</th>
<th>Name</th>
<th>Distributor</th>
<th>District</th>
<th>Area</th>
<th>Contact</th>
<th>Email</th>
<th>Address</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($fieldstaffs as $key => $staff)
<tr>
<td>{{ $key + 1 }}</td>
<td>{{ $staff->user->name }}</td>
<td>{{ $staff->distributor->company_name ?? '-' }}</td>
<td>{{ $staff->user->district->name ?? '' }}</td>
<td>{{ $staff->user->area->name ?? '' }}</td>
<td>{{ $staff->user->contact_no }}</td>
<td>{{ $staff->user->email }}</td>
<td>{{ $staff->user->address }}</td>
<td>{{ ucfirst($staff->status) }}</td>
<td>
<a href="{{ route('fieldstaffs.edit', $staff->id) }}" class="btn btn-sm btn-primary">Edit</a>
<form action="{{ route('fieldstaffs.destroy', $staff->id) }}" method="POST" style="display:inline-block;">
@csrf
@method('DELETE')
<button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
@endsection