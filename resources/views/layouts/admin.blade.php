<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <!-- Loader -->
    <div id="global-loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: #ffffff; z-index: 99999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s ease-out;">
        <img src="{{ asset('admin/assets/images/logo/logo.png') }}" alt="Loading..." style="width: 150px; height: auto; animation: pulse 1.5s infinite ease-in-out;">
    </div>

    <style>
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
    </style>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                var loader = document.getElementById('global-loader');
                if (loader) {
                    loader.style.opacity = '0';
                    setTimeout(function() {
                        loader.style.display = 'none';
                    }, 500);
                }
            }, 500); // Optional small delay to ensure logo is seen
        });
    </script>
 
    {{-- If authenticated as any role → show full dashboard layout --}}
    @if(Auth::guard('web')->check())
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        @include('layouts.partials.header')
                    <div class="page-body-wrapper">
                        @include('layouts.partials.sidebar')
                        <div class="page-body">
                            @include('layouts.partials.breadcrumbs')
                            @yield('page-body')
                        </div>
                        @include('layouts.partials.footer')
                    </div>    </div>
    @include('layouts.partials.scripts')
  
    @stack('scripts')
    @else
        {{-- If NOT authenticated as any role → show only login content --}}
        @yield('content')
    @endif
</body>
</html>
