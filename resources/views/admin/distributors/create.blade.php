@extends('layouts.admin')
@section('page-body')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12 p-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header text-white">
                    <h5 class="mb-0">Create Distributor</h5>
                </div>
                <div class="card-body p-4">
                    
                    {{-- Validation Errors --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('distributors.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            {{-- Left Column --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">GST</label>
                                    <input type="text" name="gst" class="form-control" value="{{ old('gst') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Drug License Number</label>
                                    <input type="text" name="drug_license_number" class="form-control" value="{{ old('drug_license_number') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Contact No</label>
                                    <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">District</label>
                                    <select name="district_id" id="district_id" class="form-select" required>
                                        <option value="">Select District</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Area</label>
                                    <select name="area_id" id="area_id" class="form-select" required>
                                        <option value="">Select Area</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Route</label>
                                    <input type="text" name="route" class="form-control" value="{{ old('route') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="3" required>{{ old('address') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Pincode</label>
                                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button class="btn btn-success px-4">Create Distributor</button>
                        </div>
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

        districtSelect.addEventListener('change', function () {
            const districtId = this.value;
            areaSelect.innerHTML = '<option value="">Select Area</option>'; // Clear previous areas

            if (districtId) {
                fetch(`/distributors/get-areas/${districtId}`)
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
            }
        });
    });
</script>
@endpush

@endsection
