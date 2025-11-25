@extends('layouts.admin')
@section('page-body')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Create Field Staff</h5>
                </div>

                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.fieldstaffs.store') }}" method="POST">
                        @csrf

                        {{-- Row 1 --}}
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

                        {{-- Row 2 --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Contact No</label>
                                <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no') }}">
                            </div>
                        </div>

                        {{-- Row 3 --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Pincode</label>
                                <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}" required>
                            </div>
                        </div>

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

                        <div class="mt-3">
                            <button class="btn btn-success">Create Field Staff</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection