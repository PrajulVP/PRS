@extends('layouts.admin')
@section('page-body')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Field Staff</h5>
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

                    <form action="{{ route('admin.fieldstaffs.update', $fieldstaff->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $fieldstaff->user->name) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $fieldstaff->user->email) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Password (Leave blank to keep unchanged)</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Contact No</label>
                                <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $fieldstaff->user->contact_no) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control">{{ old('address', $fieldstaff->user->address) }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Pincode</label>
                                <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $fieldstaff->pincode) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Sales Manager</label>
                                <select name="sales_manager_id" id="sales_manager_id" class="form-select" required>
                                    <option value="">Select Sales Manager</option>
                                    @foreach($salesManagers as $salesManager)
                                        <option value="{{ $salesManager->id }}" {{ old('sales_manager_id', $fieldstaff->sales_manager_id) == $salesManager->id ? 'selected' : '' }}>
                                            {{ $salesManager->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <button class="btn btn-success">Update Field Staff</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
