@extends('layouts.admin')
@section('content')
<div class="container p-4">
<h2>Areas</h2>
<div class="mb-3">
<select id="districtFilter" class="form-select">
<option value="">-- All Districts --</option>
@foreach($districts as $d)
<option value="{{ $d->id }}">{{ $d->name }}</option>
@endforeach
</select>
</div>
<button id="addAreaBtn" class="btn btn-primary mb-3">Add Area</button>


<div id="areaFormContainer" style="display:none;">
<form id="areaForm">
@csrf
<div class="mb-3">
<label>Select District</label>
<select name="district_id" id="districtSelect" class="form-control" required>
@foreach($districts as $d)
<option value="{{ $d->id }}">{{ $d->name }}</option>
@endforeach
</select>
</div>
<div class="mb-3">
<label>Area Name</label>
<input type="text" name="name" class="form-control" required>
</div>
<button type="submit" class="btn btn-success">Save</button>
<button type="button" id="cancelAreaBtn" class="btn btn-secondary">Cancel</button>
</form>
</div>


<table class="table table-bordered" id="areaTable">
<thead><tr><th>ID</th><th>District</th><th>Name</th><th>Actions</th></tr></thead>
<tbody></tbody>
</table>
</div>


@section('scripts')
<script>
function fetchAreas(district_id=''){
let url = '/api/v1/areas';
if(district_id) url += `?district_id=${district_id}`;
fetch(url)
.then(res => res.json())
.then(res => {
let tbody = document.querySelector('#areaTable tbody');
tbody.innerHTML = '';
res.data.forEach(a => {
tbody.innerHTML += `<tr><td>${a.id}</td><td>${a.district.name}</td><td>${a.name}</td><td><button class='btn btn-sm btn-danger' onclick='deleteArea(${a.id})'>Delete</button></td></tr>`;
});
});
}


function deleteArea(id){
if(confirm('Are you sure?')){
fetch(`/api/v1/areas/${id}`,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}})
.then(res=>res.json()).then(res=>{ alert(res.message); fetchAreas(document.getElementById('districtFilter').value); });
}
}


document.getElementById('districtFilter').addEventListener('change', function(){
fetchAreas(this.value);
document.getElementById('districtSelect').value = this.value;
});


document.getElementById('addAreaBtn').addEventListener('click',()=>{
document.getElementById('areaFormContainer').style.display='block';
});


document.getElementById('cancelAreaBtn').addEventListener('click',()=>{
document.getElementById('areaFormContainer').style.display='none';
});


document.getElementById('areaForm').addEventListener('submit',function(e){
e.preventDefault();
let formData = new FormData(this);
fetch('/api/v1/areas',{method:'POST',body:formData,headers:{'Accept':'application/json'}})
.then(res=>res.json())
.then(res=>{
alert(res.message);
this.reset();
document.getElementById('areaFormContainer').style.display='none';
fetchAreas(document.getElementById('districtFilter').value);
});
});


fetchAreas();
</script>
@endsection
@endsection