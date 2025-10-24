@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Chemists</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('chemists.create') }}" class="btn btn-primary mb-3">Add Chemist</a>

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
            @foreach($chemists as $key => $chemist)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $chemist->name }}</td>
                <td>{{ $chemist->gst }}</td>
                <td>{{ $chemist->distributor->company_name ?? '-' }}</td>
                <td>{{ $chemist->district->name ?? '' }}</td>
                <td>{{ $chemist->area->name ?? '' }}</td>
                <td>{{ $chemist->route ?? '-' }}</td>
                <td>{{ $chemist->contact_no }}</td>
                <td>{{ $chemist->email }}</td>
                <td>{{ $chemist->address }}</td>
                <td>{{ $chemist->pincode }}</td>
                <td>
                    <a href="{{ route('chemists.edit', $chemist->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('chemists.destroy', $chemist->id) }}" method="POST" style="display:inline-block;">
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
