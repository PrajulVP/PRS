@extends('layout.admin')

@section('content')

<div class="login-card">

<div id="error-messages" class="alert alert-danger" style="display: none;">
    <ul class="mb-0">
    </ul>
</div>

<form id="login-form" method="POST" action="{{ route('api.adminlogin') }}" novalidate>
    @csrf
    
    <div class="mb-3">
        <label for="email" class="form-label">Email or Username</label>
        <input id="email" type="text"
               class="form-control @error('email') is-invalid @enderror"
               name="email"
               value="{{ old('email') }}"
               required autofocus>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <input id="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   name="password" required autocomplete="current-password">
            <button type="button" class="btn btn-outline-secondary show-pass" tabindex="-1" title="Show/Hide">
                <i class="fa fa-eye"></i>
            </button>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox"
                   name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
        </div>
        <div>
            <a href="">Forgot password?</a>
        </div>
    </div>

    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary">Login</button>
    </div>
</form>

<hr>

</div> <!-- close .login-card -->

<script src="{{ asset('admin/assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/bootstrap.bundle.min.js') }}"></script>
<script>
(function(){
    document.querySelectorAll('.show-pass').forEach(function(btn){
        btn.addEventListener('click', function(){
            var input = this.parentElement.querySelector('input');
            if(input.type === 'password'){
                input.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });
    });

    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function(data)
            {
                if(data.success) {
                    localStorage.setItem('authToken', data.token);
                    window.location.href = "{{ route('api.admindashboard') }}";
                } 
            },
            error: function(data) {
                var errors = data.responseJSON.errors;
                var errorMessages = $('#error-messages ul');
                errorMessages.empty();
                if (errors) {
                    $.each(errors, function(key, value){
                        errorMessages.append('<li>'+value+'</li>');
                    });
                } else {
                    errorMessages.append('<li>'+data.responseJSON.message+'</li>');
                }
                $('#error-messages').show();
            }
        });
    });
})();
</script>
@endsection
