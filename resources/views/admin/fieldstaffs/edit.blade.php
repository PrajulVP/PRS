@extends('layouts.admin')
</div>


<div class="mb-3">
<label>District</label>
<select name="district_id" id="district_id" class="form-select" required>
<option value="">Select District</option>
@foreach($districts as $district)
<option value="{{ $district->id }}" {{ $district->id == $fieldstaff->district_id ? 'selected' : '' }}>{{ $district->name }}</option>
@endforeach
</select>
</div>


<div class="mb-3">
<label>Area</label>
<select name="area_id" id="area_id" class="form-select" required>
<option value="">Select Area</option>
@foreach($areas as $area)
<option value="{{ $area->id }}" {{ $area->id == $fieldstaff->area_id ? 'selected' : '' }}>{{ $area->name }}</option>
@endforeach
</select>
</div>


<div class="mb-3">
<label>Address</label>
<textarea name="address" class="form-control">{{ old('address', $fieldstaff->address) }}</textarea>
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