@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Create Retailer</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.retailers.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Contact No</label>
                                <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control" required>{{ old('address') }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Pincode</label>
                                <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>GST</label>
                                <input type="text" name="gst" class="form-control" value="{{ old('gst') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Distributor</label>
                                <select name="distributor_id" id="distributor_id" class="form-select" required>
                                    <option value="">Select Distributor</option>
                                    @foreach($distributors as $distributor)
                                    <option value="{{ $distributor->id }}" {{ old('distributor_id') == $distributor->id ? 'selected' : '' }}>
                                        {{ $distributor->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Sales Manager</label>
                                <select name="sales_manager_id" id="sales_manager_id" class="form-select" required>
                                    <option value="">Select Sales Manager</option>
                                    @foreach($salesManagers as $salesManager)
                                    <option value="{{ $salesManager->id }}" {{ old('sales_manager_id') == $salesManager->id ? 'selected' : '' }}>
                                        {{ $salesManager->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Field Staff</label>
                                <select name="field_staff_id" id="field_staff_id" class="form-select" required>
                                    <option value="">Select Field Staff</option>
                                    @foreach($fieldStaffs as $fieldStaff)
                                    <option value="{{ $fieldStaff->id }}" {{ old('field_staff_id') == $fieldStaff->id ? 'selected' : '' }}>
                                        {{ $fieldStaff->user->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <button class="btn btn-success">Create Retailer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection