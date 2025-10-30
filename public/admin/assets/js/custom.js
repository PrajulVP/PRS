$('#district_id').change(function(){
    let districtId = $(this).val();
    $('#area_id').html('<option value="">Loading...</option>');

    if(districtId){
        $.get('/distributors/get-areas/'+districtId, function(data){
            let options = '<option value="">Select Area</option>';
            $.each(data, function(i, area){
                options += `<option value="${area.id}">${area.name}</option>`;
            });
            $('#area_id').html(options);
        });
        $.get('/retailers/get-distributors/'+districtId, function(data){
            let distributorOptions = '<option value="">Select Distributor</option>';
            $.each(data, function(i, distributor){
                distributorOptions += `<option value="${distributor.id}">${distributor.user.name}</option>`;
            });
            $('#assigned_distributor_id').html(distributorOptions);
        });
    } else {
        $('#area_id').html('<option value="">Select Area</option>');
    }
});