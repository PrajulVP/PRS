<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <!-- Loader -->
    <!-- <div class="loader-wrapper">
        <div class="loader">
            <div class="loader4"></div>
        </div>
    </div> -->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>

    {{-- If NOT authenticated as admin → show only login content --}}
    @guest('admin')
        @yield('content')
    @endguest

    {{-- If authenticated as admin → show full dashboard layout --}}
    @auth('admin')
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        @include('layouts.partials.header')
        <div class="page-body-wrapper">
            @include('layouts.partials.sidebar')
            <div class="page-body">
                @yield('page-body')
            </div>
            @include('layouts.partials.footer')
        </div>
    </div>
    @include('layouts.partials.scripts')
    @endauth
</body>
</html>
