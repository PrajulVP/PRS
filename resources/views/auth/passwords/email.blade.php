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
                            <!-- Logo Badge (Ensures visibility regardless of theme) -->
                            <div class="d-inline-block bg-white p-2 rounded-3 shadow-sm mb-3">
                                <img src="{{ asset('admin/assets/images/logo/atom-logo.webp') }}" class="img-fluid"
                                    alt="PRS Logo" style="max-height: 50px;">
                            </div>
                            <h3 class="fw-bold mb-1" style="color: var(--login-text);">Reset Password</h3>
                            <p class="small" style="color: var(--login-muted);">Enter your email to receive a reset link</p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success border-0 shadow-sm rounded-3 small fw-bold mb-3" role="alert">
                                {{ session('status') }}
                            </div>
                            <div class="d-grid mb-4">
                                <a href="{{ route('login') }}" class="btn btn-outline-primary fw-bold py-2">
                                    <i class="fa fa-arrow-left me-2"></i> Go to Login
                                </a>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" novalidate>
                            @csrf

                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold small mb-2" style="color: var(--login-muted);">Email Address</label>
                                <input id="email" type="email"
                                    class="form-control form-control-lg border-opacity-10 @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                                
                                @error('email')
                                    <span class="invalid-feedback d-block fw-bold small mt-2" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="d-grid gap-3 mt-4">
                                <button type="submit" class="btn btn-primary shadow-sm fw-bold py-2">Send Password Reset Link</button>
                                <div class="text-center">
                                    <a href="{{ route('login') }}" class="text-decoration-none small fw-bold text-muted" style="transition: color 0.2s ease;" onmouseover="this.className='text-decoration-none small fw-bold text-primary'" onmouseout="this.className='text-decoration-none small fw-bold text-muted'">
                                        <i class="fa fa-arrow-left me-1"></i> Back to Login
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
