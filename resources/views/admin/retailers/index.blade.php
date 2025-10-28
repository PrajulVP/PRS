@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Retailer</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('retailers.create') }}" class="btn btn-primary mb-3">Add retailer</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>GST</th>
                <th>Distributor</th>
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
            @foreach($retailers as $key => $retailer)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $retailer->name }}</td>
                <td>{{ $retailer->gst }}</td>
                <td>{{ $retailer->distributor->company_name ?? '-' }}</td>
                <td>{{ $retailer->district->name ?? '' }}</td>
                <td>{{ $retailer->area->name ?? '' }}</td>
                <td>{{ $retailer->route ?? '-' }}</td>
                <td>{{ $retailer->contact_no }}</td>
                <td>{{ $retailer->email }}</td>
                <td>{{ $retailer->address }}</td>
                <td>{{ $retailer->pincode }}</td>
                <td>
                    <a href="{{ route('retailers.edit', $retailer->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('retailers.destroy', $retailer->id) }}" method="POST" style="display:inline-block;">
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
