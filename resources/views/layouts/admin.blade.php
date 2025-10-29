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
    @if(Auth::guard('superadmin')->check() || Auth::guard('admin')->check() || Auth::guard('manager')->check() || Auth::guard('distributor')->check() || Auth::guard('fieldstaff')->check() || Auth::guard('retailer')->check())
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
    @else
        {{-- If NOT authenticated as any role → show only login content --}}
        @yield('content')
    @endif
</body>
</html>
