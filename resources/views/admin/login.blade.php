@extends('layouts.admin')

@section('content')

<div class="login-card">



<form id="login-form" method="POST" action="{{ route('login.post') }}" novalidate>
    @csrf
    @method('POST')
    <div class="mb-3">
        <label for="email" class="form-label">Email or Username</label>
        <input id="email" type="text"
               class="form-control"
               name="email"
               value="{{ old('email') }}"
               required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <input id="password" type="password"
                   class="form-control"
                   name="password" required autocomplete="current-password">
            <button type="button" class="btn btn-outline-secondary show-pass" tabindex="-1" title="Show/Hide">
                <i class="fa fa-eye"></i>
            </button>
            <div class="invalid-feedback d-block"></div>
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
