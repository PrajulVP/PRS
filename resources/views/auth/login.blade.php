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
                            <h3 class="fw-bold mb-1" style="color: var(--login-text);">Welcome Back</h3>
                            <p class="small" style="color: var(--login-muted);">Sign in to continue</p>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success border-0 shadow-sm rounded-3 small fw-bold mb-4" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form id="login-form" method="POST" action="{{ route('login') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold small mb-2" style="color: var(--login-muted);">Email Address</label>
                                <input id="email" type="email"
                                    class="form-control form-control-lg border-opacity-10" name="email"
                                    value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold small mb-2" style="color: var(--login-muted);">Password</label>
                                <div class="password-field-container">
                                    <input id="password" type="password"
                                        class="form-control form-control-lg border-opacity-10"
                                        name="password" required autocomplete="current-password"
                                        placeholder="Enter password">
                                    <span class="toggle-password"><i class="fa fa-eye"></i></span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="remember" style="color: var(--login-muted); white-space: nowrap;">
                                        Remember me
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a class="small fw-bold text-decoration-none text-end" href="{{ route('password.request') }}" style="color: #0d6efd; white-space: nowrap;">
                                        Forgot Password?
                                    </a>
                                @endif
                            </div>

                            <div class="d-grid">
                                <button type="submit" id="login-btn" class="btn btn-primary btn-lg shadow-sm py-3 fw-bold">LOG IN</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#7366ff'
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtns = document.querySelectorAll('.toggle-password');
            toggleBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const container = this.closest('.password-field-container');
                    const input = container.querySelector('input');
                    const icon = this.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            const loginForm = document.getElementById('login-form');
            if (loginForm) {
                loginForm.addEventListener('submit', function (e) {
                    const submitBtn = document.getElementById('login-btn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging in...';
                    }
                });
            }
        });
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        .card {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Decrease font sizes */
        .card h3 {
            font-size: 1.4rem !important;
        }

        .card p.text-muted {
            font-size: 0.8rem !important;
        }

        .card label.form-label {
            font-size: 0.75rem !important;
        }

        .card input.form-control {
            font-size: 0.95rem !important;
            padding: 0.5rem 1rem !important;
        }

        .card .form-check-label {
            font-size: 0.75rem !important;
        }

        .card .hover-underline {
            font-size: 0.75rem !important;
        }

        .card .btn-lg {
            font-size: 0.9rem !important;
            padding: 0.5rem 1rem !important;
        }

        .hover-underline:hover {
            text-decoration: underline !important;
        }

        /* Hide browser-default password visibility toggles (Edge/IE) */
        input::-ms-reveal,
        input::-ms-clear {
            display: none !important;
        }
    </style>

@endsection