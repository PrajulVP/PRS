@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Distributor</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('distributors.update', $distributor->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $distributor->user->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>GST</label>
                                <input type="text" name="gst" class="form-control" value="{{ old('gst', $distributor->gst) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Drug License Number</label>
                                <input type="text" name="drug_license_number" class="form-control" value="{{ old('drug_license_number', $distributor->drug_license_number) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Contact No</label>
                                <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $distributor->contact_no) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $distributor->user->email) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Password (Leave blank to keep unchanged)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>District</label>
                                <select name="district_id" id="district_id" class="form-select" required>
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                    <option value="{{ $district->id }}" {{ $district->id == old('district_id', $distributor->district_id) ? 'selected' : '' }}>{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Area</label>
                                <select name="area_id" id="area_id" class="form-select" required>
                                    <option value="">Select Area</option>
                                    @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ $area->id == old('area_id', $distributor->area_id) ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Route</label>
                                <input type="text" name="route" class="form-control" value="{{ old('route', $distributor->route) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control" required>{{ old('address', $distributor->address) }}</textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $distributor->pincode) }}" required>
                        </div>

                        <button class="btn btn-success">Update Distributor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const districtSelect = document.getElementById('district_id');
        const areaSelect = document.getElementById('area_id');
        const initialAreaId = "{{ old('area_id', $distributor->area_id) }}"; // Get the currently selected area

        function fetchAreas(districtId, selectedAreaId) {
            // Clear previous areas more robustly
            while (areaSelect.firstChild) {
                areaSelect.removeChild(areaSelect.firstChild);
            }
            // Add the default "Select Area" option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select Area';
            areaSelect.appendChild(defaultOption);

            if (districtId) {
                console.log('Fetching areas for district:', districtId);
                fetch(`/distributors/get-areas/${districtId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Received data:', data);
                        // Clear previous areas more robustly
                        while (areaSelect.firstChild) {
                            areaSelect.removeChild(areaSelect.firstChild);
                        }
                        // Add the default "Select Area" option
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = 'Select Area';
                        areaSelect.appendChild(defaultOption);

                        data.forEach(area => {
                            const option = document.createElement('option');
                            option.value = area.id;
                            option.textContent = area.name;
                            if (selectedAreaId && area.id == selectedAreaId) {
                                option.selected = true;
                            }
                            areaSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching areas:', error));
            }
        }

        districtSelect.addEventListener('change', function () {
            fetchAreas(this.value, null); // When district changes, don't pre-select area
        });

        // Trigger on page load if a district is already selected
        if (districtSelect.value) {
            fetchAreas(districtSelect.value, initialAreaId);
        }
    });
</script>
@endpush

@endsection
