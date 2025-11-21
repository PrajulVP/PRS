@extends('layouts.admin')

@section('page-body')
<div class="container-fluid mt-4">

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0 text-white">Distributor Details</h5>
        </div>

        <div class="card-body">

            <div class="row g-4">

                {{-- LEFT SIDE DETAILS --}}
                <div class="col-md-8">
                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">Company Name:</div>
                        <div class="col-sm-7">{{ $distributor->user->name }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">Email:</div>
                        <div class="col-sm-7">{{ $distributor->user->email }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">Contact No:</div>
                        <div class="col-sm-7">{{ $distributor->contact_no }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">GST:</div>
                        <div class="col-sm-7">{{ $distributor->gst }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">Truck License Number:</div>
                        <div class="col-sm-7">{{ $distributor->truck_license_number ?? 'N/A' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">Address:</div>
                        <div class="col-sm-7">{{ $distributor->address }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">Pincode:</div>
                        <div class="col-sm-7">{{ $distributor->pincode }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">District:</div>
                        <div class="col-sm-7">{{ $distributor->district->name ?? 'N/A' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">Area:</div>
                        <div class="col-sm-7">{{ $distributor->area->name ?? 'N/A' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">Route:</div>
                        <div class="col-sm-7">{{ $distributor->route }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-5 fw-bold">Status:</div>
                        <div class="col-sm-7">
                            <span class="badge {{ $distributor->user->status === 'active' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($distributor->user->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- RIGHT SIDE PROFILE PIC --}}
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            @if($distributor->user->profile_pic)
                                <img src="{{ asset('storage/' . $distributor->user->profile_pic) }}"
                                     class="img-fluid rounded mb-2"
                                     style="max-width: 230px;">
                            @else
                                <p class="text-muted">No Profile Picture</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-4">
                <a href="{{ route('distributors.edit', $distributor->id) }}" class="btn btn-primary">Edit</a>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            </div>

        </div>
    </div>

</div>
@endsection
