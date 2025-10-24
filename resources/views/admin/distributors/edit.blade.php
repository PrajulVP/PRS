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
            <label>Company Name</label>
            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $distributor->company_name) }}" required>
        </div>

        <div class="mb-3">
            <label>GST</label>
            <input type="text" name="gst" class="form-control" value="{{ old('gst', $distributor->gst) }}" required>
        </div>

        <div class="mb-3">
            <label>Contact No</label>
            <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $distributor->contact_no) }}" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $distributor->email) }}" required>
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
            <input type="text" name="route" class="form-control" value="{{ old('route', $distributor->route ?? '') }}">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#district_id').change(function(){
    let districtId = $(this).val();
    $('#area_id').html('<option value="">Loading...</option>');

    if(districtId){
        $.get('/get-areas/'+districtId, function(data){
            let options = '<option value="">Select Area</option>';
            $.each(data, function(i, area){
                options += `<option value="${area.id}">${area.name}</option>`;
            });
            $('#area_id').html(options);
        });
    } else {
        $('#area_id').html('<option value="">Select Area</option>');
    }
});
</script>
@endsection
