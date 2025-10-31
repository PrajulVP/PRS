@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Retailers</h5>
                    <a href="{{ route('retailers.create') }}" class="btn btn-primary">Add Retailer</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="row">
                        @foreach($retailers as $retailer)
                        <div class="col-md-4 mb-4">
                            <div class="card p-4">
                                <h5 class="card-title">{{ $retailer->user->name }}</h5>
                                <p class="card-text"><strong>GST:</strong> {{ $retailer->gst }}</p>
                                <p class="card-text"><strong>Distributor:</strong> {{ $retailer->distributor->company_name ?? '-' }}</p>
                                <p class="card-text"><strong>District:</strong> {{ $retailer->user->district->name ?? '' }}</p>
                                <p class="card-text"><strong>Area:</strong> {{ $retailer->user->area->name ?? '' }}</p>
                                <p class="card-text"><strong>Route:</strong> {{ $retailer->route ?? '-' }}</p>
                                <p class="card-text"><strong>Contact:</strong> {{ $retailer->user->contact_no }}</p>
                                <p class="card-text"><strong>Email:</strong> {{ $retailer->user->email }}</p>
                                <p class="card-text"><strong>Address:</strong> {{ $retailer->user->address }}</p>
                                <p class="card-text"><strong>Pincode:</strong> {{ $retailer->pincode }}</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('retailers.edit', $retailer->id) }}" class="btn btn-primary">Edit</a>
                                    <form action="{{ route('retailers.destroy', $retailer->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
