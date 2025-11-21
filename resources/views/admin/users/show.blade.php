@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ ucfirst($user->role) }} - {{ $user->name }} Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $user->name }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Role:</strong> <span class="badge badge-primary">{{ $user->role }}</span></p>
                            <p><strong>Status:</strong> <span class="badge {{ $user->status === 'active' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($user->status) }}</span></p>
                        </div>
                        <div class="col-md-6">
                            @if($user->profile_pic)
                                <img src="{{ asset('storage/' . $user->profile_pic) }}" alt="Profile Picture" class="img-fluid rounded" style="max-width: 200px;">
                            @else
                                <p>No profile picture uploaded.</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    @if($user->distributor)
                        <h5>Distributor Details</h5>
                        <p><strong>GST:</strong> {{ $user->distributor->gst }}</p>
                        <p><strong>Drug License Number:</strong> {{ $user->distributor->drug_license_number }}</p>
                        <p><strong>Contact No:</strong> {{ $user->distributor->contact_no }}</p>
                        <p><strong>Address:</strong> {{ $user->distributor->address }}</p>
                        <p><strong>Pincode:</strong> {{ $user->distributor->pincode }}</p>
                    @elseif($user->manager)
                        <h5>Manager Details</h5>
                        {{-- Add manager-specific details here if any --}}
                        <p>No additional details available for this manager.</p>
                    @elseif($user->fieldStaff)
                        <h5>Field Staff Details</h5>
                        <p><strong>Distributor:</strong> {{ $user->fieldStaff->distributor->user->name ?? 'N/A' }}</p>
                    @elseif($user->retailer)
                        <h5>Retailer Details</h5>
                        <p><strong>GST:</strong> {{ $user->retailer->gst }}</p>
                        <p><strong>Distributor:</strong> {{ $user->retailer->distributor->user->name ?? 'N/A' }}</p>
                    @endif

                    <div class="mt-3">
                        <!-- <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">Edit</a> -->
                        <a href="{{ url()->previous() }}" class="btn btn-dark">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
