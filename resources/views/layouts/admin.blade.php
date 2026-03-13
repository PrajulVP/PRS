<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.head')
    <style>
        :root {
            /* === Deep Navy Corporate Palette === */
            --med-bg-body: rgba(0, 53, 114, 0.02);
            /* Soft pastel navy-tinted light background */
            --med-bg-card: #ffffff;
            --med-bg-sidebar: linear-gradient(165deg, rgb(44, 44, 44) 0%, rgb(0, 80, 133) 40%, #002b5cf5 100%);
            /* Dark slate into Deep Corporate Navy */
            --med-primary: #00497a;
            /* Corporate Blue */
            --med-secondary: #0067ab;
            /* Lighter Blue */
            --med-accent: #002b5c;
            --med-text-main: #1f2937;
            /* Deep Slate */
            --med-text-muted: #6b7280;
            --med-text-sidebar: #f8fafc;
            --med-border: rgba(0, 73, 122, 0.15);
            --med-shadow-soft: 0 10px 40px -10px rgba(0, 73, 122, 0.08);
            /* Organic floating shadow */
            --med-shadow-glow: 0 0 20px rgba(0, 73, 122, 0.1);
            --med-glass: rgba(255, 255, 255, 0.95);
        }

        body.dark-only {
            /* === Deep Navy Premium Dark Theme === */
            --med-bg-body: #0a0f18;
            /* Absolute Deep Slate Blue */
            --med-bg-card: #121b2a;
            /* Dark Slate Panel */
            --med-bg-sidebar: linear-gradient(165deg, #121b2a 0%, #001f3e 100%);
            /* Dark mode sidebar gradient */
            --med-primary: #38bdf8;
            /* Keep lighter accessible blue for dark mode primary elements */
            --med-secondary: #7dd3fc;
            --med-text-main: #f8fafc;
            --med-text-muted: #94a3b8;
            --med-text-sidebar: #f8fafc;
            --med-border: rgba(56, 189, 248, 0.1);
            --med-shadow-soft: 0 20px 50px -12px rgba(0, 0, 0, 0.5);
            --med-shadow-glow: 0 0 30px rgba(56, 189, 248, 0.05);
            --med-glass: rgba(18, 27, 42, 0.9);
        }

        /* === Premium Global Foundations === */
        body {
            background-color: var(--med-bg-body) !important;
            color: var(--med-text-main) !important;
            font-family: 'Montserrat', sans-serif;
            background-image:
                radial-gradient(at 0% 0%, rgba(0, 73, 122, 0.03) 0, transparent 50%),
                radial-gradient(at 50% 0%, rgba(0, 103, 171, 0.02) 0, transparent 50%);
            background-attachment: fixed;
            transition: background-color 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .page-body,
        .page-wrapper,
        .page-body-wrapper {
            background-color: var(--med-bg-body) !important;
            transition: background-color 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* === The Floating Card Concept === */
        .card {
            background-color: var(--med-bg-card) !important;
            border: 1px solid var(--med-border) !important;
            box-shadow: var(--med-shadow-soft) !important;
            border-radius: 24px !important;
            /* Extra smooth corners */
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            position: relative;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--med-primary), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card:hover {
            transform: none;
            /* Removed translateY to stop jumping */
            box-shadow: 0 20px 60px -15px rgba(0, 73, 122, 0.12) !important;
            border-color: rgba(0, 73, 122, 0.3) !important;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-header,
        .card-footer {
            background-color: transparent !important;
            border-bottom: 1px solid var(--med-border) !important;
            color: var(--med-text-main) !important;
            padding: 1.5rem 2rem !important;
        }

        /* === The Biotech Sidebar === */
        .sidebar-wrapper {
            background: var(--med-bg-sidebar) !important;
            border-right: none !important;
            box-shadow: 10px 0 50px rgba(0, 0, 0, 0.05);
            transition: all 0.5s ease;
        }

        .sidebar-wrapper .logo-wrapper {
            background: transparent !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list .sidebar-link,
        .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list .sidebar-title {
            color: var(--med-text-sidebar) !important;
            opacity: 0.8;
            border-radius: 16px;
            padding: 8px 20px !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list .sidebar-link:hover,
        .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list .sidebar-link.active {
            background: rgba(255, 255, 255, 0.062) !important;
            opacity: 1;
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list .sidebar-link svg {
            stroke: var(--med-text-sidebar) !important;
        }

        /* === Transparent Header & Footer === */
        .page-header,
        .footer {
            background-color: transparent !important;
            backdrop-filter: none !important;
        }

        .page-header .header-wrapper h4 {
            color: var(--med-text-main) !important;
        }

        /* === Utilities === */
        .text-muted {
            color: var(--med-text-muted) !important;
        }

        /* === Premium Modal Concepts === */
        .modal-content {
            background-color: var(--med-bg-card) !important;
            color: var(--med-text-main) !important;
            border: 1px solid var(--med-border) !important;
            border-radius: 24px !important;
            box-shadow: var(--med-shadow-soft) !important;
        }

        .modal-header {
            border-bottom: 1px solid var(--med-border) !important;
            padding: 1.5rem 2rem !important;
        }

        .modal-footer {
            border-top: 1px solid var(--med-border) !important;
            padding: 1.25rem 2rem !important;
        }

        .modal-title {
            font-weight: 700 !important;
            color: var(--med-text-main) !important;
        }

        .btn-close {
            filter: var(--med-close-filter, none);
        }

        body.dark-only .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .pt-6 {
            padding-top: 5rem !important;
        }

        /* === Form Elements === */
        .form-control,
        .form-select,
        select,
        .select2-container * {
            font-family: 'Montserrat', sans-serif !important;
        }

        .form-control,
        .form-select {
            background-color: var(--med-bg-card) !important;
            border: 1px solid var(--med-border) !important;
            color: var(--med-text-main) !important;
            border-radius: 10px !important;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 3px rgba(0, 73, 122, 0.1) !important;
            border-color: var(--med-primary) !important;
            transform: translateY(-1px);
        }

        /* === Buttons & Interactive Elements === */
        .btn-primary {
            background: linear-gradient(135deg, #0067ab 0%, #00497a 100%) !important;
            border: none !important;
            box-shadow: 0 4px 6px -1px rgba(0, 73, 122, 0.3) !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(0, 73, 122, 0.4) !important;
            filter: brightness(1.1);
        }

        .btn-secondary {
            background: #ffffff !important;
            color: var(--med-primary) !important;
            border: 2px solid var(--med-primary) !important;
            border-radius: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.75rem !important;
            letter-spacing: 0.05em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 2px 4px rgba(0, 73, 122, 0.05) !important;
        }

        .btn-secondary:hover {
            background: var(--med-primary) !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 73, 122, 0.2) !important;
        }

        .btn-success {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%) !important;
            border: none !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
        }

        /* DT Export Buttons specific refinement */
        .dt-buttons .btn {
            margin-right: 5px !important;
            margin-bottom: 10px !important;
        }

        /* DataTables Premium Corporate Styling */
        .dataTables_length select {
            padding: 6px 36px 6px 12px !important;
            min-width: 80px !important;
            display: inline-block !important;
            border-radius: 8px !important;
            background-position: right 10px center !important;
        }

        .dataTables_filter input {
            padding: 6px 16px !important;
            border-radius: 8px !important;
            margin-left: 10px !important;
            width: auto !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 12px !important;
            margin: 0 4px !important;
            border: 1px solid var(--med-border) !important;
            background: var(--med-bg-card) !important;
            color: var(--med-text-main) !important;
            transition: all 0.3s ease !important;
            padding: 6px 14px !important;
            display: inline-block !important;
            /* Fix inline layout for arrows */
            vertical-align: middle !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--med-primary) !important;
            color: white !important;
            border-color: var(--med-primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 73, 122, 0.2);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, #0067ab 0%, #00497a 100%) !important;
            color: white !important;
            border: none !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 12px rgba(0, 73, 122, 0.3) !important;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background-color: var(--med-bg-card) !important;
            border: 1px solid var(--med-border) !important;
            color: var(--med-text-main) !important;
            border-radius: 12px !important;
            padding: 8px 15px !important;
            transition: all 0.3s ease;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            outline: none !important;
            border-color: var(--med-primary) !important;
            box-shadow: 0 0 0 4px rgba(0, 73, 122, 0.1) !important;
        }

        /* Table Corporate Refinements */
        .table {
            font-family: 'Montserrat', sans-serif !important;
            color: var(--med-text-main) !important;
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            /* Visual spacing for rows */
        }

        .table thead th {
            background-color: rgba(0, 73, 122, 0.04) !important;
            border: none !important;
            color: var(--med-text-main) !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.8rem !important;
            letter-spacing: 0.08em;
            padding: 15px !important;
            border-radius: 10px;
        }

        .table tbody tr {
            background-color: var(--med-bg-card) !important;
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #ffffff !important;
            transform: none;
            /* Stopped scale jump */
            box-shadow: 0 5px 15px rgba(0, 73, 122, 0.06);
            z-index: 10;
            position: relative;
        }

        .table td {
            border: none !important;
            padding: 15px !important;
            border-bottom: 1px solid var(--med-border) !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            vertical-align: middle !important;
        }

        .table tr td:first-child {
            border-radius: 12px 0 0 12px;
        }

        .table tr td:last-child {
            border-radius: 0 12px 12px 0;
        }

        /* Datatable Info Text */
        .dataTables_info {
            color: var(--med-text-muted) !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
        }

        /* Dark Mode Adjustments */
        body.dark-only .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        body.dark-only .table thead th {
            background-color: rgba(52, 211, 153, 0.05) !important;
        }

        body.dark-only .btn-secondary,
        body.dark-only .dt-buttons .btn {
            background-color: var(--med-bg-card) !important;
            color: var(--med-text-main) !important;
            border-color: var(--med-border) !important;
        }

        body.dark-only .btn-secondary:hover,
        body.dark-only .dt-buttons .btn:hover {
            background-color: var(--med-primary) !important;
            color: #ffffff !important;
        }

        /* Specialized padding for action buttons in tables */
        .table .btn-sm:not(.confirm-receipt-btn):not(.confirm-btn) {
            padding: 5px 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            font-size: 0.78rem !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .table .btn-sm i {
            font-size: 0.9rem !important;
        }

        /* Exempting the confirm button to keep it distinct */
        .table .btn-sm.confirm-receipt-btn,
        .table .btn-sm.confirm-btn {
            padding: 8px 20px !important;
            border-radius: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Datatable Export Buttons Global Resizing */
        .dt-buttons .btn {
            padding: 4px 10px !important;
            font-size: 0.75rem !important;
            height: auto !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            line-height: 1.2 !important;
            gap: 4px;
        }

        .dt-buttons .btn i {
            font-size: 0.75rem !important;
        }

        /* Targeted Row Highlight - Subtle One-time Shine */
        @keyframes rowShine {
            0% {
                background-color: transparent;
            }

            15% {
                background-color: rgba(56, 189, 248, 0.15);
            }

            100% {
                background-color: transparent;
            }
        }

        .highlighted-row td {
            animation: rowShine 2.5s ease-out forwards !important;
            border: none !important;
        }

        @keyframes rowShineDark {
            0% {
                background-color: transparent;
            }

            15% {
                background-color: rgba(56, 189, 248, 0.25);
            }

            100% {
                background-color: transparent;
            }
        }

        body.dark-only .highlighted-row td {
            animation: rowShineDark 2.5s ease-out forwards !important;
            border: none !important;
        }

        /* Sidebar Toggle Mechanism & Header Fixes */
        @media (min-width: 992px) {
            .sidebar-wrapper.close_icon {
                transform: translateX(-100%);
                visibility: hidden;
                transition: all 0.5s ease;
            }

            .page-header.close_icon {
                margin-left: 0 !important;
                width: 100% !important;
                transition: all 0.5s ease;
                height: 80px !important; 
            }

            .page-header.close_icon .header-wrapper {
                height: 100% !important;
                display: flex !important;
                align-items: center !important;
                margin: 0 !important;
            }

            .page-wrapper.compact-wrapper .page-body-wrapper .page-body.close_icon {
                margin-left: 0 !important;
                padding-left: 0 !important;
                transition: all 0.5s ease;
                width: 100% !important;
            }

            /* Clean Toggle at start of header when sidebar is closed */
            .page-header.close_icon .header-logo-wrapper {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                width: 70px !important;
                align-items: center !important;
                justify-content: center !important;
                height: 100% !important; 
                padding: 0 !important;
                margin: 0 !important;
                flex: 0 0 auto !important;
            }

            .page-header.close_icon .header-logo-wrapper .logo-wrapper {
                display: none !important;
            }

            .page-header.close_icon .header-logo-wrapper .toggle-sidebar {
                display: flex !important;
                visibility: visible !important;
                cursor: pointer !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 10px !important;
                background: rgba(0, 73, 122, 0.05) !important; /* Light tint of sidebar color */
                border-radius: 12px !important;
                transition: all 0.3s ease !important;
                margin-right: 5px !important;
                transform: translateY(-22px) !important;
            }

            .page-header.close_icon .header-logo-wrapper .toggle-sidebar:hover {
                background: rgba(0, 73, 122, 0.1) !important;
            }

            .page-header.close_icon .header-logo-wrapper .toggle-sidebar i {
                color: #00497a !important; /* Sidebar primary color */
                stroke: #00497a !important;
                stroke-width: 3px !important; /* Bolder lines */
                width: 26px !important;
                height: 26px !important;
            }

            .page-header.close_icon .left-header {
                padding-left: 30px !important;
                display: flex !important;
                align-items: center !important;
                height: 100% !important;
                flex: 1 !important;
            }
            .page-header.close_icon .left-header > div {
                display: flex !important;
                align-items: center !important;
                gap: 15px !important;
                margin: 0 !important;
                padding-top: 2px !important; /* Subtle nudge down for optical centering */
            }
            .page-header.close_icon .left-header h4 {
                margin: 0 !important;
                line-height: normal !important;
                font-weight: 600 !important;
            }
        }

        /* Mobile Fix */
        @media (max-width: 991px) {
            .sidebar-wrapper.close_icon {
                transform: translateX(-100%);
            }
        }
    </style>
</head>

<body class="{{ $_COOKIE['mode'] ?? 'light' }}">
    <script>
        (function () {
            var mode = localStorage.getItem('mode');
            if (mode) {
                document.body.classList.add(mode);
                document.body.classList.remove(mode === 'dark-only' ? 'light' : 'dark-only');
            }
        })();
    </script>
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;" id="toastContainer"></div>

    <!-- Loader -->
    <div id="global-loader"
        style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: #ffffff; z-index: 99999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s ease-out;">
        <img src="{{ asset('admin/assets/images/logo/favicon.ico') }}" width="60" alt="Loading..."
            style="width: 60px; height: auto; animation: pulse 1.5s infinite ease-in-out;">
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

        /* Apply dark background to loader immediately when body has dark-only class */
        body.dark-only #global-loader {
            background-color: var(--med-bg-body) !important;
        }
    </style>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                var loader = document.getElementById('global-loader');
                if (loader) {
                    loader.style.opacity = '0';
                    setTimeout(function () {
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
                <div class="page-body pt-6">
                    {{-- @include('layouts.partials.breadcrumbs') --}}
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
        document.addEventListener('DOMContentLoaded', function () {
            var currentUrl = window.location.href;
            var sidebarLinks = document.querySelectorAll('.sidebar-link, .sidebar-submenu a');

            sidebarLinks.forEach(function (link) {
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

            // Dynamic Notification Badge Helper
            window.updateNotificationBadge = function (decrement = 1) {
                const badge = document.querySelector('.notification-box .badge');
                if (!badge) return;

                let count = parseInt(badge.innerText.replace(/,/g, ''));
                if (isNaN(count)) return;

                count = Math.max(0, count - decrement);

                if (count > 0) {
                    badge.innerText = count;
                } else {
                    badge.remove();
                }
            };

            // Notification Click Handler
            document.querySelectorAll('.notification-dropdown a').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    var li = link.closest('li');
                    var notificationId = li ? li.dataset.id : null;
                    if (notificationId) {
                        e.preventDefault();
                        var targetUrl = link.getAttribute('href');

                        // Decrease badge immediately for dynamic feedback
                        // Only if the notification looks unread (has a pending action indicator)
                        if (li.classList.contains('b-l-primary')) {
                            window.updateNotificationBadge(1);
                        }

                        fetch("{{ route('notifications.read', ':id') }}".replace(':id', notificationId), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        }).finally(function () {
                            if (targetUrl && targetUrl !== '#') {
                                window.location.href = targetUrl;
                            }
                        });
                    }
                });
            });

            // Datatables Global Highlight Logic
            var urlParams = new URLSearchParams(window.location.search);
            var highlightCode = urlParams.get('highlight');
            if (highlightCode && window.jQuery) {
                // Clear the highlight parameter from URL immediately so it doesn't persist on refresh
                urlParams.delete('highlight');
                let newSearch = urlParams.toString();
                let cleanUrl = window.location.pathname + (newSearch ? '?' + newSearch : '');
                window.history.replaceState({}, document.title, cleanUrl);

                var attemptHighlight = function () {
                    if ($.fn.DataTable) {
                        var tables = $.fn.dataTable.tables(true);
                        if (tables.length > 0) {
                            var api = $(tables[0]).DataTable();

                            // Wait for table to draw/load data
                            api.on('draw.dt', function () {
                                setTimeout(function () {
                                    $(tables[0]).find('tbody tr').each(function () {
                                        if ($(this).text().includes(highlightCode)) {
                                            var $row = $(this);
                                            $row.addClass('highlighted-row');

                                            // Optional: Scroll to the row so it's visible
                                            this.scrollIntoView({ behavior: 'smooth', block: 'center' });

                                            setTimeout(function () {
                                                $row.removeClass('highlighted-row');
                                            }, 3000);
                                        } else {
                                            $(this).removeClass('highlighted-row');
                                        }
                                    });
                                }, 100);
                            });

                            // Trigger initial check
                            setTimeout(function () {
                                $(tables[0]).find('tbody tr').each(function () {
                                    if ($(this).text().includes(highlightCode)) {
                                        var $row = $(this);
                                        $row.addClass('highlighted-row');
                                        this.scrollIntoView({ behavior: 'smooth', block: 'center' });

                                        // Remove highlight class after animation finishes (once)
                                        setTimeout(function () {
                                            $row.removeClass('highlighted-row');
                                        }, 3000);
                                    }
                                });
                            }, 500);
                        }
                    }
                };

                var maxAttempts = 20; // 10 seconds total
                var dtInterval = setInterval(function () {
                    maxAttempts--;
                    if ($.fn.DataTable && $.fn.dataTable.tables(true).length > 0) {
                        clearInterval(dtInterval);
                        attemptHighlight();
                    }
                    if (maxAttempts <= 0) clearInterval(dtInterval);
                }, 500);
            }
        });

        // --- Live Notification Polling ---
        let lastNotificationId = null;

        function fetchLiveNotifications() {
            fetch("{{ route('notifications.fetch') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 1. Detect and show Toast for NEW notifications
                        if (data.notifications.length > 0) {
                            const latestId = data.notifications[0].id;
                            if (lastNotificationId && lastNotificationId !== latestId) {
                                // Find notifications that are newer than lastNotificationId
                                const newNotifications = [];
                                for (let n of data.notifications) {
                                    if (n.id === lastNotificationId) break;
                                    newNotifications.push(n);
                                }
                                
                                // Show toasts in order (oldest of the new ones first)
                                newNotifications.reverse().forEach(n => {
                                    if (typeof showToast === 'function') {
                                        showToast('info', n.message);
                                    }
                                });
                            }
                            lastNotificationId = latestId;
                        } else {
                            // Reset if no unread notifications
                            lastNotificationId = null; 
                        }

                        // 2. Update Badge Count
                        const badgeContainer = document.querySelector('.notification-box');
                        let badge = badgeContainer.querySelector('.badge');
                        if (data.unread_count > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.className = 'badge rounded-pill badge-primary text-white pulse-badge';
                                badgeContainer.appendChild(badge);
                            }
                            badge.innerText = data.unread_count;
                        } else if (badge) {
                            badge.remove();
                        }

                        // 3. Update Dropdown List
                        const dropdownUl = document.querySelector('.notification-dropdown ul');
                        if (dropdownUl) {
                            // Find the "View all" li (last element)
                            const viewAllLi = dropdownUl.querySelector('li.p-2.text-center');
                            
                            // Clear existing notification items (except "View all")
                            const currentLis = dropdownUl.querySelectorAll('li:not(.p-2):not(.text-center.text-muted)');
                            currentLis.forEach(li => {
                                if (li !== viewAllLi) li.remove();
                            });

                            if (data.notifications.length > 0) {
                                // Remove "No notifications found" if it exists
                                const emptyMsg = dropdownUl.querySelector('li .text-center.text-muted');
                                if (emptyMsg) emptyMsg.closest('li').remove();

                                // Prepend new items
                                data.notifications.forEach(n => {
                                    const li = document.createElement('li');
                                    li.className = n.is_pending ? 'b-l-primary border-4' : 'b-l-secondary border-4';
                                    li.dataset.id = n.id;
                                    
                                    li.innerHTML = `
                                        <a href="${n.action_url}" style="display: block; width: 100%; color: inherit; cursor: pointer; text-decoration: none;">
                                            <p class="mb-1 fw-bold text-dark" style="font-size: 0.8rem;">
                                                ${n.message}
                                            </p>
                                            <span class="${n.is_pending ? 'font-danger' : 'text-primary'}" style="font-size: 0.70rem;">
                                                <i class="fa fa-clock-o"></i> ${n.created_at_human}
                                            </span>
                                        </a>
                                    `;
                                    
                                    // Re-attach click handler for the new link
                                    li.querySelector('a').addEventListener('click', function(e) {
                                        e.preventDefault();
                                        handleNotificationClick(n.id, n.action_url, li);
                                    });

                                    dropdownUl.insertBefore(li, viewAllLi);
                                });
                            } else if (dropdownUl.querySelectorAll('li').length === 1) { // Only View All exists
                                const li = document.createElement('li');
                                li.innerHTML = '<p class="text-center text-muted my-2">No notifications found</p>';
                                dropdownUl.insertBefore(li, viewAllLi);
                            }
                        }
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        // Shared Click Handler for better DRY
        function handleNotificationClick(notificationId, targetUrl, li) {
            // Decrease badge immediately for dynamic feedback
            if (li && li.classList.contains('b-l-primary')) {
                window.updateNotificationBadge(1);
            }

            fetch("{{ route('notifications.read', ':id') }}".replace(':id', notificationId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).finally(function () {
                if (targetUrl && targetUrl !== '#') {
                    window.location.href = targetUrl;
                }
            });
        }

        // Initialize first ID and start polling
        const initialBadge = document.querySelector('.notification-box .badge');
        if (initialBadge) {
            // Seed with first notification ID from existing UI if possible, or just wait for first fetch
            const firstLi = document.querySelector('.notification-dropdown ul li[data-id]');
            if (firstLi) lastNotificationId = firstLi.dataset.id;
        }

        setInterval(fetchLiveNotifications, 15000); // 15 seconds
    </script>
</body>

</html>