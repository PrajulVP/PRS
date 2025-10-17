<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Riho admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Riho admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="admin/assets/images/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="admin/assets/images/favicon.png" type="image/x-icon">
    <title>Riho - Premium Admin Template</title>
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200;300;400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="admin/assets/css/font-awesome.css">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/icofont.css">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/themify.css">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/flag-icon.css">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/feather-icon.css">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/slick.css">
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/slick-theme.css">
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/scrollbar.css">
    <!-- Range slider css-->
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/rangeslider/rSlider.min.css">
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/animate.css">
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/prism.css">
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/fullcalender.css">
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="admin/assets/css/vendors/bootstrap.css">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="admin/assets/css/style.css">
    <link id="color" rel="stylesheet" href="admin/assets/css/color-1.css" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="admin/assets/css/responsive.css">
</head>
<body>
    <!-- loader starts-->
    <div class="loader-wrapper">
      <div class="loader"> 
        <div class="loader4"></div>
      </div>
    </div>
    <!-- loader ends-->
    @yield('content')

    <!-- footer start-->
    @if(!request()->is('adminlogin'))
    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 footer-copyright text-center">
                    <p class="mb-0">Copyright 2025 ©</p>
                </div>
            </div>
        </div>
    </footer>
    @endif


    <!-- latest jquery-->
    <script src="admin/assets/js/jquery.min.js"></script>
    <!-- Bootstrap js-->
    <script src="admin/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
    <!-- feather icon js-->
    <script src="admin/assets/js/icons/feather-icon/feather.min.js"></script>
    <script src="admin/assets/js/icons/feather-icon/feather-icon.js"></script>
    <!-- scrollbar js-->
    <script src="admin/assets/js/scrollbar/simplebar.js"></script>
    <script src="admin/assets/js/scrollbar/custom.js"></script>
    <!-- Sidebar jquery-->
    <script src="admin/assets/js/config.js"></script>
    <!-- Plugins JS start-->
    <script src="admin/assets/js/sidebar-menu.js"></script>
    <script src="admin/assets/js/sidebar-pin.js"></script>
    <script src="admin/assets/js/slick/slick.min.js"></script>
    <script src="admin/assets/js/slick/slick.js"></script>
    <script src="admin/assets/js/header-slick.js"></script>
    <script src="admin/assets/js/chart/apex-chart/apex-chart.js"></script>
    <script src="admin/assets/js/chart/apex-chart/stock-prices.js"></script>
    <!-- Range Slider js-->
    <script src="admin/assets/js/range-slider/rSlider.min.js"></script>
    <script src="admin/assets/js/rangeslider/rangeslider.js"></script>
    <script src="admin/assets/js/prism/prism.min.js"></script>
    <script src="admin/assets/js/clipboard/clipboard.min.js"></script>
    <script src="admin/assets/js/counter/jquery.waypoints.min.js"></script>
    <script src="admin/assets/js/counter/jquery.counterup.min.js"></script>
    <script src="admin/assets/js/counter/counter-custom.js"></script>
    <script src="admin/assets/js/custom-card/custom-card.js"></script>
    <!-- calendar js-->
    <script src="admin/assets/js/calendar/fullcalender.js"></script>
    <script src="admin/assets/js/calendar/custom-calendar.js"></script>
    <script src="admin/assets/js/dashboard/dashboard_2.js"></script>
    <script src="admin/assets/js/animation/wow/wow.min.js"></script>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="admin/assets/js/script.js"></script>
    <script>new WOW().init();</script>
</body>
</html>