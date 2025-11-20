@extends('layouts.admin')
@section('page-body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 p-4">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Retailer</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('retailers.update', $retailer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $retailer->user->name) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $retailer->user->email) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Password (Leave blank to keep unchanged)</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Contact No</label>
                                <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $retailer->user->contact_no) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control" required>{{ old('address', $retailer->user->address) }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Pincode</label>
                                <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $retailer->user->pincode) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Route</label>
                                <input type="text" name="route" class="form-control" value="{{ old('route', $retailer->user->route) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>GST</label>
                                <input type="text" name="gst" class="form-control" value="{{ old('gst', $retailer->gst) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>District</label>
                                <select name="district_id" id="district_id" class="form-select" required>
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                    <option value="{{ $district->id }}" {{ $district->id == old('district_id', $retailer->district_id) ? 'selected' : '' }}>
                                        {{ $district->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Area</label>
                                <select name="area_id" id="area_id" class="form-select" required>
                                    <option value="">Select Area</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Distributor</label>
                            <select name="distributor_id" id="distributor_id" class="form-select" required>
                                <option value="">Select Distributor</option>
                            </select>
                        </div>

                        <button class="btn btn-success">Update Retailer</button>
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
    const distributorSelect = document.getElementById('distributor_id');

    // Store initial values for pre-selection
    const initialDistrictId = "{{ old('district_id', $retailer->district_id) }}";
    const initialAreaId = "{{ old('area_id', $retailer->area_id) }}";
    const initialDistributorId = "{{ old('distributor_id', $retailer->distributor_id) }}";

    // Function to fetch and populate areas
    function fetchAreas(districtId, selectedAreaId = null) {
        console.log('fetchAreas called for districtId:', districtId);
        areaSelect.innerHTML = '<option value="">Select Area</option>'; // Ensure only one default option

        if (districtId) {
            fetch(`{{ route('retailers.getAreas', ['district' => '__districtId__']) }}`.replace('__districtId__', districtId))
                .then(response => response.json())
                .then(data => {
                    console.log('Areas data received:', data);
                    data.forEach(area => {
                        const option = document.createElement('option');
                        option.value = area.id;
                        option.textContent = area.name;
                        if (selectedAreaId && area.id == selectedAreaId) {
                            option.selected = true;
                        }
                        areaSelect.appendChild(option);
                        console.log('Appended area:', area.name);
                    });
                })
                .catch(error => console.error('Error fetching areas:', error));
        }
    }

    // Function to fetch and populate distributors
    function fetchDistributors(districtId, areaId, selectedDistributorId = null) {
        distributorSelect.innerHTML = '<option value="">Select Distributor</option>';

        if (districtId && areaId) {
            fetch(`{{ route('retailers.getDistributorsByDistrictAndArea', ['district' => '__districtId__', 'area' => '__areaId__']) }}`
                .replace('__districtId__', districtId)
                .replace('__areaId__', areaId))
                .then(response => response.json())
                .then(data => {
                    if (!data.length) {
                        const option = document.createElement('option');
                        option.textContent = 'No distributors found';
                        distributorSelect.appendChild(option);
                        return;
                    }
                    data.forEach(distributor => {
                        const option = document.createElement('option');
                        option.value = distributor.id;
                        option.textContent = distributor.company_name || `Distributor #${distributor.id}`;
                        if (selectedDistributorId && distributor.id == selectedDistributorId) {
                            option.selected = true;
                        }
                        distributorSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching distributors:', error));
        }
    }

    // Event listener for district change
    districtSelect.addEventListener('change', function () {
        const districtId = this.value;
        console.log('Selected District ID:', districtId);
        fetchAreas(districtId);
        // Clear distributors when district changes, as areas will change
        distributorSelect.innerHTML = '<option value="">Select Distributor</option>';
    });

    // Event listener for area change
    areaSelect.addEventListener('change', function () {
        const districtId = districtSelect.value;
        const areaId = this.value;
        console.log('Selected Area ID:', areaId);
        fetchDistributors(districtId, areaId);
    });

    // Initial population on page load if district and area are already selected
    if (initialDistrictId) {
        fetchAreas(initialDistrictId, initialAreaId);
        if (initialAreaId) {
            fetchDistributors(initialDistrictId, initialAreaId, initialDistributorId);
        }
    }
});
</script>
@endpush

@endsection
