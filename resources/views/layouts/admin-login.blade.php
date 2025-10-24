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
    <div class="login-page">
        @yield('content')
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
