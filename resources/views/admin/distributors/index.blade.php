@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Distributors</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('distributors.create') }}" class="btn btn-primary mb-3 mt-2">Add Distributor</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Company Name</th>
                <th>GST</th>
                <th>District</th>
                <th>Area</th>
                <th>Route</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Address</th>
                <th>Pincode</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($distributors as $key => $distributor)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $distributor->company_name }}</td>
                <td>{{ $distributor->gst }}</td>
                <td>{{ $distributor->district->name ?? '' }}</td>
                <td>{{ $distributor->area->name ?? '' }}</td>
                <td>{{ $distributor->route ?? '-' }}</td>
                <td>{{ $distributor->contact_no }}</td>
                <td>{{ $distributor->email }}</td>
                <td>{{ $distributor->address }}</td>
                <td>{{ $distributor->pincode }}</td>
                <td>
                    <a href="{{ route('distributors.edit', $distributor->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('distributors.destroy', $distributor->id) }}" method="POST" style="display:inline-block;">
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
