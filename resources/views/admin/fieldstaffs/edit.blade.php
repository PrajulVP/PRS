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

                    <form action="{{ route('fieldstaffs.update', $fieldstaff->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $fieldstaff->user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $fieldstaff->user->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label>Password (Leave blank to keep unchanged)</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Contact No</label>
                            <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $fieldstaff->user->contact_no) }}">
                        </div>

                        <div class="mb-3">
                            <label>Address</label>
                            <textarea name="address" class="form-control">{{ old('address', $fieldstaff->user->address) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label>District</label>
                            <select name="district_id" id="district_id" class="form-select" required>
                                <option value="">Select District</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}" {{ $district->id == $fieldstaff->user->district_id ? 'selected' : '' }}>
                                        {{ $district->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Area</label>
                            <select name="area_id" id="area_id" class="form-select" required>
                                <option value="">Select Area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ $area->id == $fieldstaff->user->area_id ? 'selected' : '' }}>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Distributor</label>
                            <select name="assigned_distributor_id" id="assigned_distributor_id" class="form-select" required>
                                <option value="">Select Distributor</option>
                                @foreach($distributors as $distributor)
                                    <option value="{{ $distributor->id }}" {{ old('assigned_distributor_id', $fieldstaff->assigned_distributor_id) == $distributor->id ? 'selected' : '' }}>
                                        {{ $distributor->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ $fieldstaff->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $fieldstaff->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <button class="btn btn-success">Update Field Staff</button>
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
    const distributorSelect = document.getElementById('assigned_distributor_id');

    function fetchAreas(districtId) {
        areaSelect.innerHTML = '<option value="">Select Area</option>';

                    if (districtId) {
                        fetch(`{{ route('fieldstaffs.getAreas', ['district' => '__districtId__']) }}`.replace('__districtId__', districtId))
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(area => {
                                    const option = document.createElement('option');
                                    option.value = area.id;
                                    option.textContent = area.name;
                                    areaSelect.appendChild(option);
                                });
                            })
                            .catch(error => console.error('Error fetching areas:', error));
                    }    }

    function fetchDistributors(districtId) {
        distributorSelect.innerHTML = '<option value="">Select Distributor</option>';

                    if (districtId) {
                        fetch(`{{ route('fieldstaffs.getDistributors', ['district' => '__districtId__']) }}`.replace('__districtId__', districtId))
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
                                    distributorSelect.appendChild(option);
                                });
                            })
                            .catch(error => console.error('Error fetching distributors:', error));
                    }    }

    districtSelect.addEventListener('change', function () {
        const districtId = this.value;
        console.log('Selected District ID:', districtId);
        fetchAreas(districtId);
        fetchDistributors(districtId);
    });
});
</script>
@endpush

@endsection
