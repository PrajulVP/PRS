@extends('layouts.admin')

@section('content')
    <!-- Background Image Container -->
    <div class="container-fluid p-0"
        style="background-image: url('{{ asset('admin/assets/images/login/login_bg.jpg') }}'); background-size: cover; background-position: center; transition: all 0.4s ease;">

        <!-- Dynamic Overlay -->
        <div class="row m-0 vh-100 justify-content-center align-items-center" 
             style="background: var(--login-overlay); transition: background 0.4s ease;">

            <div class="col-12 w-100" style="max-width: 400px;">
                <!-- Theme-aware Card -->
                <div class="card border-0 shadow-lg rounded-4">

                    <div class="card-body p-4 p-sm-5">

                        <div class="text-center mb-4">
                            <!-- Logo Badge -->
                            <div class="d-inline-block bg-white p-2 rounded-3 shadow-sm mb-3">
                                <img src="{{ asset('admin/assets/images/logo/atom-logo.webp') }}" class="img-fluid"
                                    alt="PRS Logo" style="max-height: 50px;">
                            </div>
                            <h3 class="fw-bold mb-1" style="color: var(--login-text);">Set New Password</h3>
                            <p class="small" style="color: var(--login-muted);">Create a new secure password</p>
                        </div>

                        <form method="POST" action="{{ route('password.update') }}" novalidate>
                            @csrf
                            <input type="hidden" name="token" value="{{ $token ?? '' }}">

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold small mb-2" style="color: var(--login-muted);">Email Address</label>
                                <input id="email" type="email"
                                    class="form-control form-control-lg border-opacity-10 @error('email') is-invalid @enderror" name="email"
                                    value="{{ $email ?? old('email') }}" required autofocus readonly>
                                
                                @error('email')
                                    <span class="invalid-feedback d-block fw-bold small mt-2" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold small mb-2" style="color: var(--login-muted);">New Password</label>
                                <input id="password" type="password"
                                    class="form-control form-control-lg border-opacity-10 @error('password') is-invalid @enderror" name="password"
                                    required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback d-block fw-bold small mt-2" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password-confirm" class="form-label fw-bold small mb-2" style="color: var(--login-muted);">Confirm Password</label>
                                <input id="password-confirm" type="password"
                                    class="form-control form-control-lg border-opacity-10" name="password_confirmation"
                                    required autocomplete="new-password">
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary shadow-sm fw-bold py-2">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
