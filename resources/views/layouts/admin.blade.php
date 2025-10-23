<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <!-- Loader -->
    <div class="loader-wrapper">
        <div class="loader"> 
            <div class="loader4"></div>
        </div>
    </div>

    <!-- Main Content -->
    @yield('content')

    @if(!request()->is('admin.login'))
        <div class="page-wrapper compact-wrapper" id="pageWrapper">
            @include('layouts.partials.header')
            <div class="page-body-wrapper">
                @if(request()->is('admin.login'))
                    @include('layouts.partials.sidebar')
                @endif
                <div class="page-body">
                    @yield('page-body')
                </div>
            </div>
        </div>
        @include('layouts.partials.footer')
        @include('layouts.partials.scripts')
    @endif
</body>
</html>
