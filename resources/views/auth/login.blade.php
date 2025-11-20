@extends('layouts.admin')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            confirmButtonColor: '#3085d6'
        });
    </script>
@endi

<div class="login-card">

<form id="login-form" method="POST" action="{{ route('login.post') }}" novalidate>
    @csrf
    @method('POST')
    <div class="mb-3">
        <label for="email" class="form-label">Email or Username</label>
        <input id="email" type="text"
               class="form-control @error('email') is-invalid @enderror"
               name="email"
               value="{{ old('email') }}"
               required>
        <div class="invalid-feedback d-block" style="color: red; font-weight: bold; min-height: 1.2em;">
            @error('email')
                {{ $message }}
            @enderror
        </div>
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
            <div class="invalid-feedback d-block">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
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

    <style>
        .form-control.is-invalid {
            width: 100% !important;
            box-sizing: border-box;
        }
        .invalid-feedback.d-block {
            width: 100%;
            box-sizing: border-box;
            height: 1.2em; /* Fixed height to prevent layout shift */
            margin-top: 0.25rem; /* Standard Bootstrap spacing */
            margin-bottom: 0;
            padding: 0;
            line-height: 1.2; /* Ensure text fits within the height */
            overflow: hidden; /* Hide overflow if message is too long */
        }
    </style>
<script src="{{ asset('admin/assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
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
})();
</script>
@endsection
