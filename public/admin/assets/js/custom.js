$('#district_id').change(function(){
    let districtId = $(this).val();
    $('#area_id').html('<option value="">Loading...</option>');

    if(districtId){
        $.get('/api/get-areas/'+districtId, function(data){
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