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
