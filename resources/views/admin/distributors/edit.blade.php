@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Edit Distributor</h2>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('distributors.update', $distributor->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $distributor->user->name) }}" required>
        </div>
        <div class="mb-3">
            <label>GST</label>
            <input type="text" name="gst" class="form-control" value="{{ old('gst', $distributor->gst) }}" required>
        </div>

        <div class="mb-3">
            <label>Truck License Number</label>
            <input type="text" name="truck_license_number" class="form-control" value="{{ old('truck_license_number', $distributor->truck_license_number) }}">
        </div>

        <div class="mb-3">
            <label>Contact No</label>
            <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $distributor->contact_no) }}" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $distributor->user->email) }}" required>
        </div>

        <div class="mb-3">
            <label>Password (Leave blank to keep unchanged)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>District</label>
            <select name="district_id" id="district_id" class="form-select" required>
                <option value="">Select District</option>
                @foreach($districts as $district)
                <option value="{{ $district->id }}" {{ $district->id == $distributor->district_id ? 'selected' : '' }}>{{ $district->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Area</label>
            <select name="area_id" id="area_id" class="form-select" required>
                <option value="">Select Area</option>
                @foreach($areas as $area)
                <option value="{{ $area->id }}" {{ $area->id == $distributor->area_id ? 'selected' : '' }}>{{ $area->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Route</label>
            <input type="text" name="route" class="form-control" value="{{ old('route', $distributor->route) }}">
        </div>


        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control" required>{{ old('address', $distributor->address) }}</textarea>
        </div>

        <div class="mb-3">
            <label>Pincode</label>
            <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $distributor->pincode) }}" required>
        </div>

        <button class="btn btn-success">Update Distributor</button>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const districtSelect = document.getElementById('district_id');
        const areaSelect = document.getElementById('area_id');
        const initialAreaId = "{{ old('area_id', $distributor->area_id) }}"; // Get the currently selected area

        function fetchAreas(districtId, selectedAreaId) {
            areaSelect.innerHTML = '<option value="">Select Area</option>'; // Clear previous areas

            if (districtId) {
                fetch(`/distributors/get-areas/${districtId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(area => {
                            const option = document.createElement('option');
                            option.value = area.id;
                            option.textContent = area.name;
                            if (area.id == selectedAreaId) { // Select the existing area
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
