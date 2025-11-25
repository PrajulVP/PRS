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
                <div class="col-md-12">
                    <div class="row mb-2">
                        <div class="col-sm-5 fw-bold">Name:</div>
                        <div class="col-sm-7">{{ $distributor->name }}</div>
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
                        <div class="col-sm-5 fw-bold">Drug License No:</div>
                        <div class="col-sm-7">{{ $distributor->drug_license_no ?? 'N/A' }}</div>
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
                </div>

            </div>

            <div class="mt-4">
                <a href="{{ route('admin.distributors.edit', $distributor->id) }}" class="btn btn-primary">Edit</a>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            </div>

        </div>
    </div>

</div>
@endsection
