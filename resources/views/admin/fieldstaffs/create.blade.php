@extends('layouts.admin')
@section('page-body')

<div class="container p-4">
    <h2>Create Field Staff</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('fieldstaffs.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
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
            <label>Contact No</label>
            <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no') }}">
        </div>

        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control">{{ old('address') }}</textarea>
        </div>

        <div class="mb-3">
            <label>District</label>
            <select name="district_id" id="district_id" class="form-select" required>
                <option value="">Select District</option>
                @foreach($districts as $district)
                    <option value="{{ $district->id }}">{{ $district->name }}</option>
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
            <label>Distributor</label>
            <select name="assigned_distributor_id" id="assigned_distributor_id" class="form-select" required>
                <option value="">Select Distributor</option>
                @foreach($distributors as $distributor)
                    <option value="{{ $distributor->id }}" {{ old('assigned_distributor_id') == $distributor->id ? 'selected' : '' }}>
                        {{ $distributor->company_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <button class="btn btn-success">Create Field Staff</button>
    </form>
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
