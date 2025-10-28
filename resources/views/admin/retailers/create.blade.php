@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Create Retailer</h2>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('retailers.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label>GST</label>
            <input type="text" name="gst" class="form-control" value="{{ old('gst') }}" required>
        </div>

        <div class="mb-3">
            <label>Contact No</label>
            <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no') }}" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Distributor</label>
            <select name="distributor_id" class="form-select" required>
                <option value="">Select Distributor</option>
                @foreach($distributors as $distributor)
                    <option value="{{ $distributor->id }}" {{ old('distributor_id') == $distributor->id ? 'selected' : '' }}>
                        {{ $distributor->company_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>District</label>
            <select name="district_id" id="district_id" class="form-select" required>
                <option value="">Select District</option>
                @foreach($districts as $district)
                <option value="{{ $district->id }}" {{ old('district_id') == $district->id ? 'selected' : '' }}>
                    {{ $district->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Area</label>
            <select name="area_id" id="area_id" class="form-select" required>
                <option value="">Select Area</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Route</label>
            <input type="text" name="route" class="form-control" value="{{ old('route') }}">
        </div>

        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control" required>{{ old('address') }}</textarea>
        </div>

        <div class="mb-3">
            <label>Pincode</label>
            <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}" required>
        </div>

        <button class="btn btn-success">Create Retailer</button>
    </form>
</div>


@endsection
