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
            --med-primary-rgb: 0, 73, 122;
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
            
            /* Payment Status Palette - Light */
            --med-paid-bg: #f0fdf4;
            --med-paid-text: #15803d;
            --med-paid-border: #dcfce7;
            --med-pending-bg: #fffbeb;
            --med-pending-text: #d97706;
            --med-pending-border: #fef3c7;
            --med-failed-bg: #fef2f2;
            --med-failed-text: #b91c1c;
            --med-failed-border: #fee2e2;

            /* Login Specific */
            --login-overlay: rgba(255, 255, 255, 0.4);
            --login-text: #1f2937;
            --login-muted: #6b7280;
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

            /* Payment Status Palette - Dark */
            --med-paid-bg: rgba(21, 128, 61, 0.15);
            --med-paid-text: #4ade80;
            --med-paid-border: rgba(74, 222, 128, 0.2);
            --med-pending-bg: rgba(180, 83, 9, 0.15);
            --med-pending-text: #fbbf24;
            --med-pending-border: rgba(251, 191, 36, 0.2);
            --med-failed-bg: rgba(185, 28, 28, 0.15);
            --med-failed-text: #f87171;
            --med-failed-border: rgba(248, 113, 113, 0.2);

            /* Login Specific */
            --login-overlay: rgba(10, 15, 24, 0.7);
            --login-text: #f8fafc;
            --login-muted: #94a3b8;
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

        /* Password Eye Icon Integration */
        .password-field-container {
            position: relative;
        }
        .password-field-container .toggle-password {
            position: absolute;
            right: 14px;
            top: 19px; /* Fixed center point for standard 38px input */
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
            color: #94a3b8;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            background: none !important;
            border: none !important;
            padding: 0 !important;
            opacity: 0.7;
        }
        .password-field-container .toggle-password:hover {
            color: var(--med-primary, #00497a);
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
        }
        .password-field-container .form-control {
            padding-right: 42px !important;
        }
        .dark-only .password-field-container .toggle-password:hover {
            color: var(--med-primary, #38bdf8);
        }
        /* Disable browser-native password reveal button */
        input::-ms-reveal,
        input::-ms-clear {
            display: none !important;
        }
        input::-webkit-contacts-auto-fill-button,
        input::-webkit-credentials-auto-fill-button {
            visibility: hidden !important;
            display: none !important;
            pointer-events: none !important;
        }
        /* Hide native Bootstrap validation icons globally */
        .form-control.is-invalid, 
        .form-select.is-invalid,
        .password-field-container .form-control.is-invalid {
            background-image: none !important;
            padding-right: 0.75rem !important; /* Reset padding to standard if icon is gone */
        }

        .page-body {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* === The Floating Card Concept === */
        .card {
            background-color: var(--med-bg-card) !important;
            border: 1px solid var(--med-border);
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

        /* === Premium Status Badges === */
        @keyframes status-pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.6); }
            70% { box-shadow: 0 0 0 6px rgba(74, 222, 128, 0); }
            100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
        }

        @keyframes status-pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
            70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Corporate Utility Classes */
        .bg-body-theme { background-color: var(--med-bg-body) !important; }
        .bg-card-theme { background-color: var(--med-bg-card) !important; }
        .text-main-theme { color: var(--med-text-main) !important; }
        .text-muted-theme { color: var(--med-text-muted) !important; }
        .border-theme { border-color: var(--med-border) !important; }
        .bg-dark-red { background-color: #8b0000 !important; color: #ffffff !important; }

        /* Toast Color Fallbacks */
        .bg-error { background-color: #ef4444 !important; }
        .bg-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; }
        .bg-info { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important; }
        .bg-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; }

        body.dark-only .text-dark {
            color: var(--med-text-main) !important;
        }

        body.dark-only .modal-body td,
        body.dark-only .modal-body .text-main-theme {
            color: #f8fafc !important;
        }

        /* Improved Dark Mode Contrast */
        body.dark-only .btn-primary {
            background-color: #38bdf8 !important;
            border-color: #38bdf8 !important;
            color: #000 !important;
            font-weight: 800 !important;
        }
        body.dark-only .btn-info {
            background-color: #0ea5e9 !important;
            border-color: #0ea5e9 !important;
            color: #fff !important;
            font-weight: 800 !important;
        }
        body.dark-only .btn-success {
            background-color: #22c55e !important;
            border-color: #22c55e !important;
            color: #fff !important;
            font-weight: 800 !important;
        }
        body.dark-only .card-header h5 {
            color: #fff !important;
            font-weight: 800 !important;
        }
        body.dark-only label {
            color: #f8fafc !important;
            font-weight: 700 !important;
        }
        body.dark-only .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Dark Mode Form Controls */
        body.dark-only .form-control,
        body.dark-only .form-select,
        body.dark-only .input-group-text {
            background-color: #1a2234 !important;
            border-color: rgba(56, 189, 248, 0.2) !important;
            color: #f8fafc !important;
        }

        body.dark-only .form-control:focus,
        body.dark-only .form-select:focus {
            background-color: #1a2234 !important;
            color: #fff !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25) !important;
        }

        /* Autofill Fix for Dark Mode */
        body.dark-only input:-webkit-autofill,
        body.dark-only input:-webkit-autofill:hover,
        body.dark-only input:-webkit-autofill:focus,
        body.dark-only textarea:-webkit-autofill,
        body.dark-only textarea:-webkit-autofill:hover,
        body.dark-only textarea:-webkit-autofill:focus,
        body.dark-only select:-webkit-autofill,
        body.dark-only select:-webkit-autofill:hover,
        body.dark-only select:-webkit-autofill:focus {
            -webkit-text-fill-color: #f8fafc !important;
            -webkit-box-shadow: 0 0 0px 1000px #1a2234 inset !important;
            transition: background-color 5000s ease-in-out 0s !important;
        }

        /* Explicitly fix password fields in dark mode */
        body.dark-only input[type="password"] {
            background-color: #1a2234 !important;
            color: #f8fafc !important;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            cursor: pointer;
            user-select: none;
            line-height: 1.2;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(4px);
        }

        .status-badge-active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: #ffffff !important;
        }

        .status-badge-active::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ffffff !important;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
            display: none !important; /* Hidden by default for datatables */
            animation: status-pulse-green 2s infinite;
        }

        .status-badge-active:hover {
            transform: translateY(-2px) scale(1.05);
            filter: brightness(1.1);
            box-shadow: 0 8px 15px rgba(16, 185, 129, 0.35);
        }

        .status-badge-inactive {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
            color: #ffffff !important;
        }

        .status-badge-inactive::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ffffff !important;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
            display: none !important; /* Hidden by default for datatables */
            animation: status-pulse-red 2s infinite;
        }

        .status-badge-inactive:hover {
            transform: translateY(-2px) scale(1.05);
            filter: brightness(1.1);
            box-shadow: 0 8px 15px rgba(239, 68, 68, 0.35);
        }

        .status-badge:active {
            transform: scale(0.95);
        }

        /* === Modal Specific Soft Badges (With Dots) === */
        .modal-body .status-badge {
            padding: 5px 12px !important;
            font-size: 0.68rem !important;
            gap: 7px !important;
            backdrop-filter: none !important;
            box-shadow: none !important;
            border-radius: 6px !important;
        }

        .modal-body .status-badge::before {
            display: inline-block !important; /* Show dots in modals */
        }

        .modal-body .status-badge-active {
            background: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
            border: 1px solid rgba(16, 185, 129, 0.2) !important;
        }

        .modal-body .status-badge-active::before {
            background: #10b981 !important; /* Colored dot for modal */
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
        }

        .modal-body .status-badge-inactive {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
            border: 1px solid rgba(239, 68, 68, 0.2) !important;
        }

        .modal-body .status-badge-inactive::before {
            background: #ef4444 !important; /* Colored dot for modal */
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.5);
        }

        /* Fix for absolute positioned badges on avatars in modals */
        .modal-body .position-relative .status-badge.position-absolute {
            z-index: 5 !important;
            bottom: 2px !important;
            right: 2px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
        }

        /* === Premium Payment Status Cards === */
        .payment-status-card {
            border-radius: 20px !important;
            padding: 1.25rem !important;
            display: flex !important;
            align-items: center !important;
            gap: 1rem !important;
            height: 100% !important;
            transition: all 0.3s ease !important;
            border: 1px solid transparent !important;
            backdrop-filter: blur(8px) !important;
        }

        .payment-status-card.paid {
            background: var(--med-paid-bg) !important;
            border-color: var(--med-paid-border) !important;
            color: var(--med-paid-text) !important;
        }

        .payment-status-card.pending {
            background: var(--med-pending-bg) !important;
            border-color: var(--med-pending-border) !important;
            color: var(--med-pending-text) !important;
        }

        .payment-status-card.failed {
            background: var(--med-failed-bg) !important;
            border-color: var(--med-failed-border) !important;
            color: var(--med-failed-text) !important;
        }

        .payment-icon {
            width: 48px !important;
            height: 48px !important;
            border-radius: 14px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1.4rem !important;
            background: rgba(255, 255, 255, 0.5) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        }

        body.dark-only .payment-icon {
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .payment-info h6 {
            font-size: 0.7rem !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            margin-bottom: 4px !important;
            opacity: 0.8 !important;
            font-weight: 700 !important;
            color: inherit !important;
        }

        .payment-info p {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            margin-bottom: 0 !important;
            color: inherit !important;
        }

        /* Support for Modal Info Cards in Dark Mode */
        body.dark-only .modal-content .card.border-0.shadow-sm {
            background-color: rgba(255, 255, 255, 0.03) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        body.dark-only .modal-content .bg-light {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        body.dark-only .modal-content .table-responsive table thead.bg-light th {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #fff !important;
            border-bottom-color: rgba(255, 255, 255, 0.1) !important;
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
        .page-header {
            position: sticky !important;
            top: 0;
            z-index: 1000 !important;
            background-color: var(--med-bg-card) !important;
            backdrop-filter: blur(10px) !important;
            border-bottom: 1px solid var(--med-border) !important;
        }

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

        .premium-variant-badge {
            display: inline-flex;
            align-items: center;
            padding: 1px 6px;
            background: rgba(var(--med-primary-rgb, 0, 73, 122), 0.08);
            color: var(--med-primary);
            border: 1px solid rgba(var(--med-primary-rgb, 0, 73, 122), 0.15);
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
            vertical-align: middle;
            white-space: nowrap;
            margin-left: 6px;
        }
        body.dark-only .premium-variant-badge {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.3);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
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
            height: 38px !important;
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
            font-size: 0.9rem !important;
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

        /* === Force Red Delete & Danger Buttons === */
        .btn-danger, .btn-outline-danger {
            background-color: #ef4444 !important;
            border-color: #ef4444 !important;
            color: #ffffff !important;
        }

        .btn-danger:hover, .btn-outline-danger:hover {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
            filter: brightness(1.1);
        }

        /* Specifically target icons inside danger buttons */
        .btn-danger i, .btn-outline-danger i {
            color: #ffffff !important;
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
            border-radius: 8px !important;
            margin: 0 2px !important;
            border: 1px solid var(--med-border) !important;
            background: var(--med-bg-card) !important;
            color: var(--med-text-main) !important;
            transition: all 0.3s ease !important;
            padding: 4px 10px !important;
            display: inline-block !important;
            vertical-align: middle !important;
            font-size: 0.8rem !important;
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

        /* Refined DataTables Footer Wrapping & Compact Pagination (Bootstrap 5) */
        .dataTables_wrapper .pagination .page-link {
            padding: 0.15rem 0.4rem !important;
            font-size: 0.75rem !important;
            min-width: 28px !important;
            text-align: center !important;
            border-radius: 4px !important;
            margin: 0 1px !important;
        }

        /* Specific styling for Previous/Next to keep them readable */
        .dataTables_wrapper .pagination .page-item:first-child .page-link,
        .dataTables_wrapper .pagination .page-item:last-child .page-link {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .dataTables_wrapper .pagination {
            display: flex !important;
            flex-wrap: nowrap !important;
            justify-content: flex-end !important;
            margin-bottom: 0 !important;
        }

        .dataTables_wrapper .row:last-child {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            margin-top: 1.5rem !important;
            gap: 0 !important;
        }

        /* Force stacking only on mobile/small tablets, or if container is narrow */
        @media (max-width: 991px) {
            .dataTables_wrapper .row:last-child > div {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
                justify-content: center !important;
                text-align: center !important;
                display: flex !important;
            }
            
            .dataTables_wrapper .dataTables_info {
                margin-bottom: 0.5rem !important;
                justify-content: center !important;
                text-align: center !important;
                width: 100% !important;
            }

            .dataTables_wrapper .dataTables_paginate {
                width: 100% !important;
                display: flex !important;
                justify-content: center !important;
            }
        }



        /* Dark Mode Adjustments */
        body.dark-only .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        body.dark-only .table thead th {
            background-color: rgba(52, 211, 153, 0.05) !important;
        }

        body.dark-only .btn-secondary {
            background-color: var(--med-bg-card) !important;
            color: var(--med-text-main) !important;
            border-color: var(--med-border) !important;
        }

        /* Visible DataTables Export Buttons in Dark Mode */
        body.dark-only .dt-buttons .btn.btn-secondary {
            background: #4b5563 !important;
            border: none !important;
            color: #ffffff !important;
        }

        body.dark-only .dt-buttons .btn.btn-info {
            background: #0ea5e9 !important;
            border: none !important;
            color: #ffffff !important;
        }

        body.dark-only .dt-buttons .btn.btn-danger {
            background: #ef4444 !important;
            border: none !important;
            color: #ffffff !important;
        }

        body.dark-only .dt-buttons .btn.btn-dark {
            background: #1f2937 !important;
            border: 1px solid #374151 !important;
            color: #ffffff !important;
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

        /* === High Visibility Utility Classes (Theme Aware) === */
        .text-main-theme { 
            color: var(--med-text-main) !important; 
        }
        .text-muted-theme { 
            color: var(--med-text-muted) !important; 
        }
        .bg-card-theme { 
            background-color: var(--med-bg-card) !important; 
        }
        .bg-body-theme { 
            background-color: var(--med-bg-body) !important; 
        }
        /* === Professional Print Optimization === */
        @media print {
            @page {
                size: landscape;
                margin: 1cm;
            }

            /* Hide Non-Essential UI for lean reports */
            .sidebar-wrapper,
            .page-header,
            .footer,
            .page-title nav,
            .preset-filters,
            .card-header form,
            /* Broadest possible selectors to kill pagination and info rows */
            .dataTables_paginate,
            .paging_simple_numbers,
            .pagination,
            .dataTables_info,
            .dataTables_length,
            .dataTables_filter,
            .dataTables_wrapper .row:last-child,
            .dt-buttons,
            .btn:not(.btn-print-only) {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                background: white !important;
                color: black !important;
                font-size: 10pt;
            }

            .page-body {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            /* Force standard black text for everything */
            * {
                color: black !important;
                background-color: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            /* No symbols/icons as requested */
            i, .fa, .icofont, .themify {
                display: none !important;
            }

            /* Status Badges - Remove colors, keep text */
            .badge, .status-badge {
                border: 1px solid #ccc !important;
                background: transparent !important;
                color: black !important;
                padding: 2px 5px !important;
            }

            .table thead th {
                border-bottom: 2px solid black !important;
                background-color: #f0f0f0 !important;
                color: black !important;
            }

            .table td {
                border-bottom: 1px solid #eee !important;
            }
        }
        /* Image Zoom Feature */
        .zoomable-avatar { cursor: zoom-in; transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .zoomable-avatar:hover { transform: scale(1.05); filter: brightness(0.9); }
        #imageZoomModal { backdrop-filter: blur(10px); background-color: rgba(0,0,0,0.85) !important; }
        #imageZoomModal .modal-dialog { background: transparent !important; border: none !important; box-shadow: none !important; }
        #imageZoomModal .zoomed-img { 
            max-width: 85vw; 
            max-height: 85vh; 
            object-fit: contain; 
            border-radius: 4px; 
            box-shadow: 0 0 100px rgba(0,0,0,0.8);
            border: none;
            animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
        }
        @keyframes zoomIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
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

    {{-- Removed Global Loader to prevent visual glitches --}}

    {{-- If authenticated as any role → show full dashboard layout --}}
    @if(Auth::guard('web')->check())
        <div class="page-wrapper compact-wrapper" id="pageWrapper">
            @include('layouts.partials.header')
            <div class="page-body-wrapper">
                @include('layouts.partials.sidebar')
                <div class="page-body">
                    {{-- @include('layouts.partials.breadcrumbs') --}}
                    @yield('page-body')
                </div>
                @include('layouts.partials.footer')
            </div>
        </div>
        @include('layouts.partials.scripts')

        @stack('scripts')

        {{-- Global Image Zoom Modal --}}
        <div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true" data-bs-dismiss="modal">
            <div class="modal-dialog modal-dialog-centered" style="max-width: fit-content !important; background: transparent !important; border: none !important; box-shadow: none !important;">
                <img src="" id="zoomPreviewImg" class="zoomed-img" alt="Zoomed Preview" style="display: block; margin: auto;">
            </div>
        </div>

        <script>
            $(document).on('click', '.zoomable-avatar', function() {
                let src = $(this).attr('src');
                // Skip zooming if it's a UI-Avatar (initials)
                if (src && !src.includes('ui-avatars.com')) {
                    $('#zoomPreviewImg').attr('src', src);
                    $('#imageZoomModal').modal('show');
                }
            });

            // Close modal when clicking anywhere inside (image or backdrop)
            $('#imageZoomModal').on('click', function() {
                $(this).modal('hide');
            });
        </script>
        <script>
            // Global Password Toggle Handler
            $(document).on('click', '.toggle-password', function() {
                const container = $(this).closest('.password-field-container');
                const input = container.find('input');
                const icon = $(this).find('i');
                
                if (input.length && input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else if (input.length) {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        </script>
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
            
            // Sidebar Counts Update Helper
            window.updateSidebarCounts = function () {
                fetch("{{ route('admin.sidebar-counts') }}", {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const counts = data.counts;
                        const totalApprovals = counts.retailer_approvals + counts.distributor_approvals;
                        
                        const badgeMap = {
                            'badge-total-approvals': totalApprovals,
                            'badge-retailer-approvals': counts.retailer_approvals,
                            'badge-distributor-approvals': counts.distributor_approvals,
                            'badge-staff-approvals': counts.staff_expenses + counts.staff_leaves,
                            'badge-staff-expenses': counts.staff_expenses,
                            'badge-staff-leaves': counts.staff_leaves,
                            'badge-inactive-sales-managers': counts.inactive_sales_managers,
                            'badge-inactive-distributors': counts.inactive_distributors,
                            'badge-inactive-field-staff': counts.inactive_field_staff,
                            'badge-inactive-retailers': counts.inactive_retailers
                        };

                        for (const [id, count] of Object.entries(badgeMap)) {
                            const el = document.getElementById(id);
                            if (el) {
                                el.innerText = count;
                                if (count > 0) {
                                    el.style.setProperty('display', 'inline-flex', 'important');
                                } else {
                                    el.style.setProperty('display', 'none', 'important');
                                }
                            }
                        }
                    }
                })
                .catch(error => console.error('Error updating sidebar counts:', error));
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

            // Table Pagination Logic Globally (1 2 3 ... Last)
            if (window.jQuery && $.fn.DataTable) {
                $.fn.dataTable.ext.pager.numbers_length = 5;
            }

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

        // Global Product Detail Helpers
        window.cleanProductName = function(name, side, size) {
            if (!name) return '';
            let cleaned = name;
            const variants = [side, size].filter(v => v && v !== 'NONE' && v !== 'N/A' && v !== '---');
            variants.forEach(v => {
                const escapedV = v.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`\\s*[\\[\\(]${escapedV}[\\]\\)]`, 'gi');
                cleaned = cleaned.replace(regex, '');
            });
            // Also handle combined formats like [LEFT/L]
            if (side && size) {
                const combined = `${side}/${size}`;
                const escapedC = combined.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regexC = new RegExp(`\\s*[\\[\\(]${escapedC}[\\]\\)]`, 'gi');
                cleaned = cleaned.replace(regexC, '');
            }
            return cleaned.trim();
        };

        window.renderProductVariantBadge = function(item) {
            let side = (item.side && item.side !== 'NONE' && item.side !== 'N/A' && item.side !== '---') ? item.side : '';
            let size = (item.size && item.size !== 'NONE' && item.size !== 'N/A' && item.size !== '---') ? item.size : '';
            let pack = (item.pack && item.pack !== 'NONE' && item.pack !== 'N/A' && item.pack !== '---') ? item.pack : '';
            
            let variants = [];
            if (side || size) {
                variants.push([side, size].filter(v => v).join(' / '));
            }
            if (pack) {
                variants.push(pack);
            }
            
            if (variants.length > 0) {
                return `<span class="premium-variant-badge">${variants.join(' | ')}</span>`;
            }
            return '';
        };

        window.showToast = function(type, message) {
            if (!message || message === 'undefined') return;
            
            const toastContainer = document.querySelector('.toast-container') || (function() {
                const container = document.createElement('div');
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                container.style.zIndex = '10000';
                document.body.appendChild(container);
                return container;
            })();

            const toast = document.createElement('div');
            // Support both 'error' and 'danger'
            const bgClass = type === 'error' ? 'bg-error' : `bg-${type}`;
            
            toast.className = `toast align-items-center text-white ${bgClass} border-0 show mb-3 shadow-lg`;
            toast.style.borderRadius = '12px';
            toast.style.minWidth = '280px';
            toast.style.backdropFilter = 'blur(10px)';
            toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
            toast.role = 'alert';
            
            const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle');
            
            toast.innerHTML = `
                <div class="d-flex p-3 align-items-center">
                    <div class="me-3 fs-5"><i class="fa fa-${icon}"></i></div>
                    <div class="toast-body p-0 fw-bold" style="font-size: 0.85rem; letter-spacing: 0.3px;">${message}</div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            toastContainer.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, 5000);
        };

        // Ensure icons are initialized for dynamic content
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    </script>
</body>

</html>
