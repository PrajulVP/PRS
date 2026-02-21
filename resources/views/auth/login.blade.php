@extends('layouts.admin')

@section('content')
    <!-- Background Image Container -->
    <div class="container-fluid p-0"
        style="background-image: url('{{ asset('admin/assets/images/login/login_bg.jpg') }}'); background-size: cover; background-position: center;">

        <!-- Light Overlay for White Theme -->
        <div class="row m-0 bg-white bg-opacity-50 vh-100 justify-content-center align-items-center">

            <div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-3">
                <!-- White Card -->
                <div class="card border-0 shadow-lg rounded-4 bg-white">

                    <div class="card-body p-4 p-sm-5">

                        <div class="text-center mb-4">
                            <!-- Use Dark Logo for White Background -->
                            <img src="{{ asset('admin/assets/images/logo/atom-logo.webp') }}" class="img-fluid mb-3"
                                alt="PRS Logo" style="max-height: 60px;">
                            <h3 class="fw-bold text-dark mb-1">Welcome Back</h3>
                            <p class="text-muted small">Sign in to continue</p>
                        </div>

                        <form id="login-form" method="POST" action="{{ route('login') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold text-muted small">Email Address</label>
                                <input id="email" type="email"
                                    class="form-control form-control-lg bg-light border-light-subtle text-dark" name="email"
                                    value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold text-muted small">Password</label>
                                <div class="input-group">
                                    <input id="password" type="password"
                                        class="form-control form-control-lg bg-light border-light-subtle text-dark border-end-0"
                                        name="password" required autocomplete="current-password"
                                        placeholder="Enter password">
                                    <button type="button"
                                        class="btn btn-lg bg-light border-light-subtle border-start-0 text-muted show-pass"
                                        tabindex="-1" title="Show/Hide">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted small" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#" class="text-primary text-decoration-none small hover-underline">Forgot
                                    Password?</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm">LOG IN</button>
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
            const showPassBtns = document.querySelectorAll('.show-pass');
            showPassBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const input = this.parentElement.querySelector('input');
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
    </style>

@endsection