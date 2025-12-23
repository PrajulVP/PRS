@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fa fa-cog me-2"></i>Loyalty Settings</h5>
                </div>
                <div class="card-body">
                    <form id="general-settings-form">
                        @csrf
                        <input type="hidden" name="slug" value="loyalty_point_inr">

                        <div class="mb-3 row align-items-center">
                            <label for="loyalty_point_inr" class="col-sm-4 col-form-label fw-medium">1 Loyalty Point = INR ₹</label>
                            <div class="col-sm-4">
                                <input type="number" step="0.01" min="0" name="value" id="loyalty_point_inr" class="form-control" value="{{ old('value', $value) }}">
                            </div>
                            <div class="col-sm-4">
                                <button type="button" id="save-settings" class="btn btn-primary">Save</button>
                            </div>
                        </div>

                        <div id="settings-errors" class="text-danger" style="display:none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
    function showError(msg){
        $('#settings-errors').text(msg).show();
    }

    function showToast(icon, title, text, timer=3000){
        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: icon,
            title: title,
            text: text,
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true
        });
    }

    $('#save-settings').on('click', function(){
        $('#settings-errors').hide();
        var $btn = $(this).prop('disabled', true).text('Saving...');
        var data = $('#general-settings-form').serialize();

        // Show persistent saving toast
        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'info',
            title: 'Saving...',
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: '{{ route('admin.settings.save') }}',
            method: 'POST',
            data: data,
            success: function(res){
                Swal.close();
                showToast('success', 'Saved', 'Setting saved successfully');
            },
            error: function(xhr){
                Swal.close();
                var message = 'An unexpected error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors){
                    var all = [];
                    $.each(xhr.responseJSON.errors, function(k,v){ all.push(v.join(', ')); });
                    message = all.join('\n');
                }
                showToast('error', 'Error', message, 5000);
            },
            complete: function(){
                $btn.prop('disabled', false).text('Save');
            }
        });
    });
});
</script>
@endpush
