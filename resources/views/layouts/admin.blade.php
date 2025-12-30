<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.head')
</head>

<body>
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;" id="toastContainer"></div>

    <!-- Loader -->
    <div id="global-loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: #ffffff; z-index: 99999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s ease-out;">
        <img src="{{ asset('admin/assets/images/logo/favicon.ico') }}" alt="Loading..." style="width: 150px; height: auto; animation: pulse 1.5s infinite ease-in-out;">
    </div>

    <style>
        @keyframes pulse {
            0% {
                transform: scale(0.95);
                opacity: 0.8;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
            }

            100% {
                transform: scale(0.95);
                opacity: 0.8;
            }
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
        </div>
    </div>
    @include('layouts.partials.scripts')

    @stack('scripts')
    @else
    {{-- If NOT authenticated as any role → show only login content --}}
    @yield('content')
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var currentUrl = window.location.href;
            var sidebarLinks = document.querySelectorAll('.sidebar-link, .sidebar-submenu a');

            sidebarLinks.forEach(function(link) {
                if (link.href === currentUrl || currentUrl.startsWith(link.href)) {
                    link.classList.add('active');

                    // If sub-menu item, open parent
                    var parentLi = link.closest('li');
                    var grandParentUl = parentLi.closest('ul.sidebar-submenu');
                    if (grandParentUl) {
                        var grandParentLi = grandParentUl.closest('li.sidebar-list');
                        if (grandParentLi) {
                            var parentLink = grandParentLi.querySelector('.sidebar-title');
                            if (parentLink) parentLink.classList.add('active');
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>