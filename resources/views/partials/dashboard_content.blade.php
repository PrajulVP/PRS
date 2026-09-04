@php
if (!function_exists('format_inr')) {
    function format_inr($num, $decimals = 0) {
        $num = number_format($num, $decimals, '.', '');
        return preg_replace('/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i', '$1,', $num);
    }
}
@endphp
<div class="row size-column">
            <div class="col-xxl-12 box-col-12">

                <style>
                    :root {
                        --med-primary-rgb: 0, 73, 122;
                        --med-accent-rgb: 0, 43, 92;
                    }

                    /* Premium Executive Cards (Centered Icon) */
                    .executive-metric-card {
                        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
                        border: 1px solid rgba(0, 0, 0, 0.05) !important;
                        border-radius: 24px !important;
                        background: #fff;
                    }
                    .executive-metric-card:hover {
                        transform: translateY(-10px);
                        box-shadow: 0 30px 60px rgba(0, 73, 122, 0.1) !important;
                        border-color: var(--med-primary) !important;
                    }
                    .icon-circle-lg {
                        width: 85px;
                        height: 85px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto;
                        transition: all 0.3s ease;
                    }
                    .icon-circle-md {
                        width: 60px;
                        height: 60px;
                        border-radius: 18px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.3s ease;
                        flex-shrink: 0;
                    }
                    .executive-metric-card:hover .icon-circle-lg,
                    .executive-metric-card:hover .icon-circle-md {
                        transform: scale(1.05);
                    }
                    
                    .action-card-compact .card-body {
                        padding: 1.25rem !important;
                    }
                    .action-card-compact h5 {
                        font-size: 1.05rem !important;
                    }
                    .action-card-compact p {
                        font-size: 0.8rem !important;
                    }

                    .btn-outline-pill {
                        border-radius: 50px !important;
                        padding: 8px 30px !important;
                        font-weight: 600 !important;
                        text-transform: capitalize;
                        border-width: 1.5px !important;
                        transition: all 0.3s ease !important;
                    }

                    .btn-pill-compact {
                        border-radius: 50px !important;
                        padding: 8px 18px !important;
                        font-weight: 800 !important;
                        font-size: 10px !important;
                        text-transform: uppercase;
                        letter-spacing: 0.8px;
                        border-width: 1.5px !important;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        white-space: nowrap;
                    }

                    .btn-pill-compact i {
                        font-size: 12px;
                    }

                    .btn-pill-compact:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    }

                    .btn-pill-compact.btn-outline-primary {
                        background: rgba(0, 73, 122, 0.05);
                        border-color: rgba(0, 73, 122, 0.2);
                        color: #00497a;
                    }
                    .btn-pill-compact.btn-outline-primary:hover {
                        background: #00497a;
                        color: white !important;
                        border-color: #00497a;
                    }

                    .btn-pill-compact.btn-outline-secondary {
                        background: rgba(139, 92, 246, 0.08) !important;
                        border-color: rgba(139, 92, 246, 0.3) !important;
                        color: #8b5cf6 !important;
                    }
                    .btn-pill-compact.btn-outline-secondary:hover {
                        background: #8b5cf6 !important;
                        color: #ffffff !important;
                        border-color: #8b5cf6 !important;
                        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3) !important;
                    }

                    .btn-pill-compact.btn-outline-warning {
                        background: rgba(245, 158, 11, 0.08) !important;
                        border-color: rgba(245, 158, 11, 0.3) !important;
                        color: #d97706 !important;
                    }
                    .btn-pill-compact.btn-outline-warning:hover {
                        background: #f59e0b !important;
                        color: #ffffff !important;
                        border-color: #f59e0b !important;
                        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3) !important;
                    }

                    .btn-pill-compact.btn-outline-info {
                        background: rgba(14, 165, 233, 0.08) !important;
                        border-color: rgba(14, 165, 233, 0.3) !important;
                        color: #0ea5e9 !important;
                    }
                    .btn-pill-compact.btn-outline-info:hover {
                        background: #0ea5e9 !important;
                        color: #ffffff !important;
                        border-color: #0ea5e9 !important;
                        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3) !important;
                    }

                    .btn-pill-compact.btn-outline-success {
                        background: rgba(16, 185, 129, 0.08) !important;
                        border-color: rgba(16, 185, 129, 0.3) !important;
                        color: #10b981 !important;
                    }
                    .btn-pill-compact.btn-outline-success:hover {
                        background: #10b981 !important;
                        color: #ffffff !important;
                        border-color: #10b981 !important;
                        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3) !important;
                    }

                    .btn-pill-compact.btn-primary {
                        background: var(--med-primary);
                        color: white !important;
                        border: none !important;
                    }
                    .btn-pill-compact.btn-primary:hover {
                        background: var(--med-accent);
                        transform: translateY(-2px);
                    }

                    .med-widget-card {
                        background: linear-gradient(135deg, var(--med-primary) 0%, var(--med-accent) 100%) !important;
                        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
                        border-radius: 16px;
                        overflow: hidden;
                        position: relative;
                        border: none !important;
                    }

                    .premium-stats-card {
                        background: var(--med-bg-card) !important;
                        border-radius: 20px;
                        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
                        border: 1px solid var(--med-border) !important;
                        position: relative;
                        overflow: hidden;
                    }

                    .premium-stats-card:hover {
                        transform: translateY(-8px);
                        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05) !important;
                        border-color: var(--med-primary) !important;
                    }

                    .bg-soft-primary { background-color: rgba(0, 73, 122, 0.08); }
                    .bg-soft-success { background-color: rgba(16, 185, 129, 0.08); }
                    .bg-soft-info { background-color: rgba(14, 165, 233, 0.08); }
                    .bg-soft-secondary { background-color: rgba(139, 92, 246, 0.08); }
                    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.08); }
                    
                    .fs-1 { font-size: 2rem !important; }
                    
                    /* Period Toggle Stylings */
                    .period-toggle {
                        background: rgba(0, 73, 122, 0.05);
                        padding: 5px;
                        border-radius: 14px;
                        display: inline-flex;
                        gap: 4px;
                        border: 1px solid rgba(0, 73, 122, 0.1);
                    }
                    .period-btn {
                        border: none;
                        background: transparent;
                        padding: 8px 18px;
                        border-radius: 10px;
                        font-size: 12px;
                        font-weight: 700;
                        color: rgba(0, 73, 122, 0.7);
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        letter-spacing: 0.3px;
                    }
                    .period-btn:hover {
                        background: rgba(0, 73, 122, 0.08);
                        color: var(--med-primary);
                    }
                    .period-btn.active {
                        background: var(--med-primary);
                        color: #fff !important;
                        box-shadow: 0 4px 12px rgba(0, 73, 122, 0.25);
                    }

                    /* Enhanced Dark Mode Support */
                    .dark-only .executive-metric-card,
                    [data-theme="dark"] .executive-metric-card { 
                        background: #111827 !important; 
                        border-color: rgba(255, 255, 255, 0.1) !important; 
                    }
                    .dark-only .bg-soft-primary,
                    [data-theme="dark"] .bg-soft-primary { background-color: rgba(0, 73, 122, 0.25); }
                    
                    .dark-only .text-dark, [data-theme="dark"] .text-dark { color: #f3f4f6 !important; }
                    .dark-only .text-muted, [data-theme="dark"] .text-muted { color: #9ca3af !important; }
                    .dark-only h1, .dark-only h2, .dark-only h3, .dark-only h4, .dark-only h5, .dark-only h6,
                    [data-theme="dark"] h5, [data-theme="dark"] h6 { color: #ffffff !important; }
                    
                    .dark-only .bg-light, [data-theme="dark"] .bg-light { 
                        background-color: rgba(255, 255, 255, 0.05) !important; 
                        color: #ffffff;
                    }
                    
                    .dark-only .period-toggle, [data-theme="dark"] .period-toggle {
                        background: rgba(255, 255, 255, 0.05);
                        border-color: rgba(255, 255, 255, 0.1);
                    }
                    .dark-only .period-btn, [data-theme="dark"] .period-btn {
                        color: #9ca3af;
                    }

                    /* Dark Mode Fixes for Pill Buttons */
                    .dark-only .btn-pill-compact.btn-outline-primary,
                    [data-theme="dark"] .btn-pill-compact.btn-outline-primary {
                        background: rgba(56, 189, 248, 0.15);
                        border-color: rgba(56, 189, 248, 0.4);
                        color: #7dd3fc !important;
                    }
                    .dark-only .btn-pill-compact.btn-outline-info,
                    [data-theme="dark"] .btn-pill-compact.btn-outline-info {
                        background: rgba(14, 165, 233, 0.15);
                        border-color: rgba(14, 165, 233, 0.4);
                        color: #38bdf8 !important;
                    }
                    .dark-only .btn-pill-compact.btn-outline-secondary,
                    [data-theme="dark"] .btn-pill-compact.btn-outline-secondary {
                        background: rgba(139, 92, 246, 0.15);
                        border-color: rgba(139, 92, 246, 0.4);
                        color: #a78bfa !important;
                    }
                    .dark-only .btn-pill-compact.btn-outline-warning,
                    [data-theme="dark"] .btn-pill-compact.btn-outline-warning {
                        background: rgba(245, 158, 11, 0.15);
                        border-color: rgba(245, 158, 11, 0.4);
                        color: #fbbf24 !important;
                    }
                    .dark-only .btn-pill-compact.btn-outline-success,
                    [data-theme="dark"] .btn-pill-compact.btn-outline-success {
                        background: rgba(16, 185, 129, 0.15);
                        border-color: rgba(16, 185, 129, 0.4);
                        color: #34d399 !important;
                    }

                    /* Retailer Hero Section */
                    .retailer-hero-glass {
                        background: rgba(255, 255, 255, 0.7);
                        backdrop-filter: blur(15px);
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        border-radius: 30px;
                        padding: 30px 40px;
                        margin-bottom: 35px;
                        box-shadow: 0 20px 40px rgba(0, 73, 122, 0.08);
                    }
                    .profile-avatar {
                        width: 70px;
                        height: 70px;
                        background: linear-gradient(135deg, var(--med-primary) 0%, var(--med-accent) 100%);
                        border-radius: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #fff;
                        font-size: 28px;
                        font-weight: 800;
                        box-shadow: 0 10px 20px rgba(0, 73, 122, 0.15);
                    }
                    .profile-info-item {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        background: rgba(0, 73, 122, 0.05);
                        padding: 8px 16px;
                        border-radius: 12px;
                        font-size: 13px;
                        font-weight: 600;
                        color: var(--med-primary);
                        border: 1px solid rgba(0, 73, 122, 0.05);
                    }
                    .profile-info-item i {
                        width: 14px;
                        height: 14px;
                        stroke-width: 2.5px;
                    }

                    /* Section Headers & Spacing */
                    .section-header-modern {
                        display: flex;
                        align-items: center;
                        gap: 15px;
                        margin-bottom: 25px; /* Added spacing after heading as requested */
                    }
                    .section-header-modern .dash {
                        width: 4px;
                        height: 24px;
                        background: var(--med-primary);
                        border-radius: 10px;
                    }

                    /* Action Buttons Premium */
                    .action-btn-premium {
                        border-radius: 18px !important;
                        padding: 18px 35px !important;
                        font-weight: 700 !important;
                        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
                    }
                    .action-btn-premium:hover {
                        transform: translateY(-5px) scale(1.02);
                    }

                    /* Enhanced Dark Mode for Retailer Hero */
                    .dark-only .retailer-hero-glass,
                    [data-theme="dark"] .retailer-hero-glass {
                        background: rgba(18, 27, 42, 0.7);
                        border-color: rgba(255, 255, 255, 0.05);
                    }
                    .dark-only .profile-info-item,
                    [data-theme="dark"] .profile-info-item {
                        background: rgba(255, 255, 255, 0.05);
                        color: #e5e7eb;
                        border-color: rgba(255, 255, 255, 0.08);
                    }

                    .retailer-info-card {
                        background: #fff;
                    }
                    .dark-only .retailer-info-card,
                    [data-theme="dark"] .retailer-info-card {
                        background: rgba(255, 255, 255, 0.05) !important;
                        border-color: rgba(255, 255, 255, 0.1) !important;
                    }
                    .dark-only .retailer-rep-name {
                        color: #fff !important;
                    }
                    /* Force white font for chart tooltips as they use dark backgrounds */
                    .apexcharts-tooltip, 
                    .apexcharts-tooltip *,
                    .apexcharts-tooltip-title, 
                    .apexcharts-tooltip-text, 
                    .apexcharts-tooltip-y-group {
                        color: #ffffff !important;
                    }
                </style>

                @php 
                    $user = auth()->user(); 
                    
                    $monthParamForView = $monthParam ?? date('Y-m');
                    $displayMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthParamForView)->format('F Y');
                    $prevMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthParamForView)->subMonth()->format('Y-m');
                    $nextMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthParamForView)->addMonth()->format('Y-m');
                    
                    $timeSelectHtml = '<div class="d-flex align-items-center justify-content-between bg-white p-1 rounded-pill" style="min-width: 230px; border: 1px solid #f1f5f9; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);">';
                    $timeSelectHtml .= '<button class="btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center border-0 transition-all" style="width: 34px; height: 34px; background-color: #f4f7f9; color: #1e293b;" onclick="fetchDashboardMonth(\'' . $prevMonth . '\')" onmouseover="this.style.backgroundColor=\'#e2e8f0\'" onmouseout="this.style.backgroundColor=\'#f4f7f9\'"><i data-feather="chevron-left" style="width: 16px; height: 16px; stroke-width: 2.5px;"></i></button>';
                    $timeSelectHtml .= '<span class="fw-800" style="color: #002b5c; font-size: 1.05rem;">' . $displayMonth . '</span>';
                    $timeSelectHtml .= '<button class="btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center border-0 transition-all" style="width: 34px; height: 34px; background-color: #f4f7f9; color: #1e293b;" onclick="fetchDashboardMonth(\'' . $nextMonth . '\')" onmouseover="this.style.backgroundColor=\'#e2e8f0\'" onmouseout="this.style.backgroundColor=\'#f4f7f9\'"><i data-feather="chevron-right" style="width: 16px; height: 16px; stroke-width: 2.5px;"></i></button>';
                    $timeSelectHtml .= '</div>';
                @endphp

                @if(Auth::user()->hasRole('retailer'))
                    <div class="retailer-hero-glass d-flex align-items-center justify-content-between flex-wrap gap-4">
                        <div class="d-flex align-items-center gap-4">
                            <div class="profile-avatar">
                                {{ strtoupper(substr($user->retailer->shop_name ?? $user->name, 0, 1)) }}
                            </div>
                            <div>
                                <span class="badge bg-soft-primary text-primary px-3 py-2 mb-2 fw-700" style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; border-radius: 8px;">Retailer</span>
                                <h5 class="fw-800 mb-2" style="color: var(--med-primary); letter-spacing: -0.3px; margin-top: 2px;">{{ $user->retailer->shop_name ?? 'Medical Store' }}</h5>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <div class="profile-info-item">
                                        <i data-feather="user"></i>
                                        <span>{{ $user->name }}</span>
                                    </div>
                                    <div class="profile-info-item text-success" style="background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2);">
                                        <i data-feather="award"></i>
                                        <span>Locality Rank: #{{ $myRank ?? 'N/A' }} / {{ $totalInLocality ?? '1' }}</span>
                                    </div>
                                    {{-- <div class="profile-info-item text-warning" style="background: rgba(251, 191, 36, 0.1); border-color: rgba(251, 191, 36, 0.2);">
                                        <i data-feather="database"></i>
                                        <span>Loyalty Points: {{ format_inr($totalLoyaltyPoints) }}</span>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        {{-- Sales Representative Card --}}
                        @if($user->retailer->fieldStaff)
                        <div class="d-flex align-items-center gap-3 p-3 rounded-4 retailer-info-card shadow-sm border border-light" style="min-width: 280px;">
                            <div class="rounded-circle bg-soft-info d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i data-feather="user" class="text-info" style="width: 20px;"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1 fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Assigned Sales Rep</p>
                                <h6 class="fw-800 mb-1 retailer-rep-name">{{ $user->retailer->fieldStaff->user->name }}</h6>
                                <p class="mb-0 small text-primary fw-bold"><i class="fa fa-phone me-1"></i>{{ $user->retailer->fieldStaff->user->contact_no }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- BRAND LOYALTY PROGRESS --}}
                    @if(isset($upcomingRewards) && count($upcomingRewards) > 0)
                        <div class="section-header-modern mt-3 mb-3">
                            <div class="dash"></div>
                            <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Brand Rewards Progress</h5>
                            <a href="{{ route('retailer.loyalty-points.index') }}" class="btn btn-sm btn-primary fw-bold ms-auto d-flex align-items-center gap-1 shadow-sm" style="border-radius: 8px;">
                                <i data-feather="gift" style="width: 14px; height: 14px;"></i> Redeem Rewards
                            </a>
                        </div>
                        <div class="row mb-4">
                            @foreach($upcomingRewards as $reward)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-left: 4px solid var(--med-primary) !important; background: #fff;">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-800 mb-0 text-primary text-uppercase">{{ $reward['brand'] }}</h6>
                                                @if($reward['next_reward'])
                                                    <span class="badge bg-soft-success text-success fw-bold" style="font-size: 11px;"><i class="fa fa-gift me-1"></i>{{ $reward['next_reward'] }}</span>
                                                @else
                                                    <span class="badge bg-soft-success text-success fw-bold" style="font-size: 11px;"><i class="fa fa-star me-1"></i>Max Level</span>
                                                @endif
                                            </div>
                                            
                                            <div class="d-flex justify-content-between text-muted mb-1 fw-600" style="font-size: 12px;">
                                                <span>Current: ₹{{ format_inr($reward['current_total'], 2) }}</span>
                                                @if($reward['next_target'])
                                                    <span>Target: ₹{{ format_inr($reward['next_target'], 2) }}</span>
                                                @endif
                                            </div>
                                            
                                            @if($reward['next_target'])
                                                @php 
                                                    $progress = min(100, ($reward['current_total'] / $reward['next_target']) * 100); 
                                                @endphp
                                                <div class="progress" style="height: 8px; border-radius: 10px; background-color: rgba(0, 73, 122, 0.1);">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: {{ $progress }}%"></div>
                                                </div>
                                                <div class="mt-2 text-end text-muted fw-bold" style="font-size: 11px;">
                                                    ₹{{ format_inr($reward['next_target'] - $reward['current_total'], 2) }} more for next reward!
                                                </div>
                                            @else
                                                <div class="progress" style="height: 8px; border-radius: 10px; background-color: rgba(16, 185, 129, 0.1);">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                                </div>
                                                <div class="mt-2 text-end text-success fw-bold" style="font-size: 11px;">
                                                    All rewards unlocked!
                                                </div>
                                            @endif
                                            
                                            @if(isset($reward['achieved_rewards']) && count($reward['achieved_rewards']) > 0)
                                                <div class="mt-3 pt-2 border-top border-light">
                                                    <p class="mb-2 text-muted fw-bold" style="font-size: 11px;">Unlocked Reward Levels:</p>
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach($reward['achieved_rewards'] as $achieved)
                                                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background: rgba(var(--med-primary-rgb), 0.05); border-left: 3px solid var(--med-primary);">
                                                                <div class="text-primary fw-bold d-flex align-items-center" style="font-size: 11px; min-width: 75px;">
                                                                    <i class="fa fa-check-circle me-1 text-success"></i> ₹{{ format_inr($achieved['threshold'], 0) }}
                                                                </div>
                                                                <div class="text-dark fw-bold" style="font-size: 11px; word-break: break-word;">
                                                                    {{ implode(', ', $achieved['reward_options'] ?? [$achieved['reward']]) }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Direct Action Section (Replacing Offers) --}}
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="section-header-modern">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Direct Ordering Actions</h5>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden executive-metric-card action-card-compact cursor-pointer" 
                                        style="background: linear-gradient(135deg, rgba(var(--med-primary-rgb), 0.05) 0%, #fff 100%);"
                                        onclick="window.location.href='{{ route('admin.retailer.create') }}'">
                                        <div class="card-body d-flex align-items-center gap-3">
                                            <div class="icon-circle-md" style="background: rgba(var(--med-primary-rgb), 0.1)">
                                                <i data-feather="plus-circle" class="text-primary" style="width: 24px; height: 24px;"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-800 mb-0" style="color: var(--med-primary);">Create Manual Order</h5>
                                                <p class="text-muted mb-0">Pick products and build order</p>
                                            </div>
                                            <div class="ms-auto">
                                                <i data-feather="arrow-right" class="text-muted" style="width: 18px;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden executive-metric-card action-card-compact cursor-pointer" 
                                        style="background: linear-gradient(135deg, rgba(var(--med-primary-rgb), 0.9) 0%, var(--med-accent) 100%);"
                                        onclick="window.location.href='{{ route('admin.retailer.create', ['action' => 'upload_prescription']) }}'">
                                        <div class="card-body d-flex align-items-center gap-3">
                                            <div class="icon-circle-md" style="background: rgba(255, 255, 255, 0.2)">
                                                <i data-feather="cpu" class="text-white" style="width: 24px; height: 24px; stroke-width: 2.5px;"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-800 mb-0 text-white">AI Prescription Order</h5>
                                                <p class="text-white text-opacity-75 mb-0">AI product extraction</p>
                                            </div>
                                            <div class="ms-auto text-white">
                                                <i data-feather="arrow-right" style="width: 18px;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden executive-metric-card action-card-compact cursor-pointer" 
                                        style="background: linear-gradient(135deg, #00497a 0%, #002b5c 100%);"
                                        onclick="window.location.href='{{ url('admin/returns') }}'">
                                        <div class="card-body d-flex align-items-center gap-3">
                                            <div class="icon-circle-md" style="background: rgba(255, 255, 255, 0.2)">
                                                <i data-feather="rotate-ccw" class="text-white" style="width: 24px; height: 24px; stroke-width: 2.5px;"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-800 mb-0 text-white">Return Products</h5>
                                                <p class="text-white text-opacity-75 mb-0">Initiate a return request</p>
                                            </div>
                                            <div class="ms-auto text-white">
                                                <i data-feather="arrow-right" style="width: 18px;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Restored Stats Section --}}
                    <div class="row">
                        @php
                            $stats = [
                                ['label' => 'Total Orders', 'value' => $retailerOrderStats['total'], 'icon' => 'shopping-cart', 'color' => 'var(--med-primary)', 'bg' => 'rgba(var(--med-primary-rgb), 0.1)', 'route' => route('retailer.orders.index')],
                                ['label' => 'Pending', 'value' => $retailerOrderStats['pending'], 'icon' => 'clock', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)', 'route' => route('retailer.orders.index', ['status' => 'pending'])],
                                ['label' => 'Delivered', 'value' => $retailerOrderStats['delivered'], 'icon' => 'check-circle', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)', 'route' => route('retailer.orders.index', ['status' => 'delivered'])],
                            ];
                        @endphp

                        @foreach($stats as $stat)
                            <div class="col-xl-4 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm executive-metric-card cursor-pointer h-100" onclick="window.location.href='{{ $stat['route'] }}'">
                                    <div class="card-body p-4 text-center">
                                        <div class="icon-circle-lg bg-soft-primary mb-3" style="width: 60px; height: 60px; background: {{ $stat['bg'] }}">
                                            <i data-feather="{{ $stat['icon'] }}" style="color: {{ $stat['color'] }}; width: 22px; height: 22px;"></i>
                                        </div>
                                        @php 
                                            $slug = strtolower(str_replace(' ', '-', $stat['label'])); 
                                        @endphp
                                        <h6 class="text-muted fw-700 text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1px;">{{ $stat['label'] }}</h6>
                                        <h3 class="mb-0 fw-800" id="stat-{{ $slug }}" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="section-header-modern">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">My Orders Over Time</h5>
                                <div class="ms-auto">{!! $timeSelectHtml !!}</div>
                            </div>
                            <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
                                <div id="monthlyOrdersChart"></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="section-header-modern">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0 status-period-label" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">Status Overview (<span class="current-period-text">{{ isset($monthParam) ? \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->format('F Y') : date('F Y') }}</span>)</h5>
                            </div>
                            <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
                                <div id="orderStatusChart"></div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ========================================== --}}
                {{-- DISTRIBUTOR DASHBOARD --}}
                {{-- ========================================== --}}
                @if(Auth::user()->hasRole('distributor'))
                    <div class="section-header-modern">
                        <div class="dash"></div>
                        <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Distributor Control Panel</h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden executive-metric-card action-card-compact cursor-pointer" 
                                style="background: linear-gradient(135deg, rgba(var(--med-primary-rgb), 0.05) 0%, #fff 100%);"
                                onclick="window.location.href='{{ route('admin.distributor-orders.create') }}'">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="icon-circle-md" style="background: rgba(var(--med-primary-rgb), 0.1)">
                                        <i data-feather="plus-circle" class="text-primary" style="width: 24px; height: 24px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-800 mb-0" style="color: var(--med-primary);">Stock Order</h5>
                                        <p class="text-muted mb-0">Order stock from company</p>
                                    </div>
                                    <div class="ms-auto">
                                        <i data-feather="arrow-right" class="text-muted" style="width: 18px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden executive-metric-card action-card-compact cursor-pointer" 
                                style="background: linear-gradient(135deg, #00497a 0%, #002b5c 100%);"
                                onclick="window.location.href='{{ url('admin/returns') }}'">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="icon-circle-md" style="background: rgba(255, 255, 255, 0.2)">
                                        <i data-feather="rotate-ccw" class="text-white" style="width: 24px; height: 24px; stroke-width: 2.5px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-800 mb-0 text-white">Manage Returns</h5>
                                        <p class="text-white text-opacity-75 mb-0">Handle customer returns</p>
                                    </div>
                                    <div class="ms-auto text-white">
                                        <i data-feather="arrow-right" style="width: 18px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden executive-metric-card action-card-compact cursor-pointer" 
                                style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.02) 100%);"
                                onclick="openMyRetailersModal(event)">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="icon-circle-md" style="background: rgba(245, 158, 11, 0.15)">
                                        <i data-feather="users" class="text-warning" style="width: 24px; height: 24px; stroke-width: 2.5px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-800 mb-0 text-warning" style="color: #d97706 !important;">Retailers</h5>
                                        <p class="text-muted text-opacity-75 mb-0">Retailers who ordered from you</p>
                                    </div>
                                    <div class="ms-auto text-warning">
                                        <i data-feather="external-link" style="width: 18px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        @php
                            $stats = [
                                ['label' => 'Order Volume', 'value' => $retailerOrderStats['total'], 'icon' => 'truck', 'color' => 'var(--med-primary)', 'bg' => 'rgba(var(--med-primary-rgb), 0.1)', 'route' => route('distributor.orders.index')],
                                ['label' => 'Delivered', 'value' => $retailerOrderStats['delivered'], 'icon' => 'check-square', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)', 'route' => route('distributor.orders.index', ['status' => 'delivered'])],
                                ['label' => 'Pending', 'value' => $retailerOrderStats['pending'], 'icon' => 'clock', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)', 'route' => route('distributor.orders.index', ['status' => 'pending'])],
                                ['label' => 'My Orders', 'value' => $distributorOrderStats['total'], 'icon' => 'shopping-bag', 'color' => '#0ea5e9', 'bg' => 'rgba(14, 165, 233, 0.1)', 'route' => route('admin.distributor-orders.index')]
                            ];
                        @endphp
                        @foreach($stats as $stat)
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm executive-metric-card cursor-pointer h-100" onclick="window.location.href='{{ $stat['route'] }}'">
                                    <div class="card-body p-4 text-center">
                                        <div class="icon-circle-lg bg-soft-primary mb-3" style="width: 60px; height: 60px; background: {{ $stat['bg'] }}">
                                            <i data-feather="{{ $stat['icon'] }}" style="color: {{ $stat['color'] }}; width: 22px; height: 22px;"></i>
                                        </div>
                                        @php 
                                            $slug = strtolower(str_replace(' ', '-', $stat['label'])); 
                                        @endphp
                                        <h6 class="text-muted fw-700 text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1px;">{{ $stat['label'] }}</h6>
                                        <h3 class="mb-0 fw-800" id="stat-{{ $slug }}" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- [NEW] Distributor Performance Index --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-header-modern">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px; color: var(--med-primary);">Performance Index & Benefits</h5>
                            </div>
                        </div>
                        
                        {{-- Target vs Achievement --}}
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm premium-stats-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="p-2 bg-soft-primary rounded-circle">
                                            <i class="fa fa-crosshairs text-primary" style="width: 16px; height: 16px;"></i>
                                        </div>
                                        @php 
                                            $target = $data_extra['target'] ?? 0;
                                            $achieved = $data_extra['achieved'] ?? 0;
                                            $percent = $target > 0 ? round(($achieved / $target) * 100, 1) : 0;
                                            $pColor = $percent >= 100 ? 'success' : ($percent >= 50 ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge bg-soft-{{ $pColor }} text-{{ $pColor }} rounded-pill">{{ $percent }}%</span>
                                    </div>
                                    <h6 class="text-muted small fw-700 text-uppercase mb-1">Target Achievement</h6>
                                    <h4 class="fw-800 mb-0">₹{{ format_inr($achieved, 0) }}</h4>
                                    <p class="text-muted small mb-0 mt-1">Goal: ₹{{ format_inr($target, 0) }}</p>
                                    <div class="progress mt-3" style="height: 6px; border-radius: 10px;">
                                        <div class="progress-bar bg-{{ $pColor }}" role="progressbar" style="width: {{ min($percent, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Average Turnaround Time --}}
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm premium-stats-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="p-2 bg-soft-info rounded-circle">
                                            <i class="fa fa-bolt text-info" style="width: 16px; height: 16px;"></i>
                                        </div>
                                    </div>
                                    <h6 class="text-muted small fw-700 text-uppercase mb-1">Turnaround Time</h6>
                                    <h4 class="fw-800 mb-0">{{ $data_extra['avg_turnaround'] ?? 'N/A' }}</h4>
                                    <p class="text-muted small mb-0 mt-1">Average delivery speed</p>
                                </div>
                            </div>
                        </div>

                        {{-- Outstanding Balance --}}
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm premium-stats-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="p-2 bg-soft-danger rounded-circle">
                                            <i class="fa fa-credit-card text-danger" style="width: 16px; height: 16px;"></i>
                                        </div>
                                        <span class="badge bg-soft-secondary text-secondary rounded-pill">{{ $data_extra['credit_days'] ?? 0 }} Days Credit</span>
                                    </div>
                                    <h6 class="text-muted small fw-700 text-uppercase mb-1">Outstanding</h6>
                                    <h4 class="fw-800 mb-0 text-danger">₹{{ format_inr($data_extra['outstanding'] ?? 0, 0) }}</h4>
                                    <p class="text-muted small mb-0 mt-1">Pending with Company</p>
                                </div>
                            </div>
                        </div>

                        {{-- Available Credits --}}
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm premium-stats-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="p-2 rounded-circle" style="background: rgba(14, 165, 233, 0.1);">
                                            <i class="fa fa-wallet" style="color: #0ea5e9; width: 16px; height: 16px;"></i>
                                        </div>
                                        <span class="badge rounded-pill" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9;">Refunds</span>
                                    </div>
                                    <h6 class="text-muted small fw-700 text-uppercase mb-1">Credit Balance</h6>
                                    <h4 class="fw-800 mb-0" style="color: #0ea5e9;">₹{{ format_inr(Auth::user()->distributor->credit_balance ?? 0, 2) }}</h4>
                                    <p class="text-muted small mb-0 mt-1">Available for next orders</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0" style="border-bottom: none;">Orders Received (Trend)</h5>
                                    {!! $timeSelectHtml !!}
                                </div>
                                <div id="monthlyOrdersChart"></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="section-title mb-0" style="border-bottom: none;">Fulfillment Rate (<span class="current-period-text">{{ isset($monthParam) ? \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->format('F Y') : date('F Y') }}</span>)</h5>
                                </div>
                                <div id="orderStatusChart"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Top Retailers -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                                <div class="p-4">
                                    <div class="d-flex align-items-center gap-2 mb-4">
                                        <div class="p-2 bg-soft-warning rounded-circle">
                                            <i class="fa fa-award text-warning" style="width: 16px; height: 16px;"></i>
                                        </div>
                                        <h6 class="fw-800 text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px; color: var(--med-primary);">Top Producing Retailers</h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light" style="font-size: 10px; text-transform: uppercase;">
                                                <tr>
                                                    <th class="px-4 py-3">Retailer</th>
                                                    <th class="text-end px-4">Performance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($topRetailers as $tr)
                                                    <tr>
                                                        <td class="px-4 py-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 text-white shadow-sm"
                                                                    style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--med-primary) 0%, var(--med-accent) 100%); font-weight: 700; font-size: 12px;">
                                                                    {{ strtoupper(substr($tr->retailer->user->name ?? 'U', 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0 fw-800 text-dark" style="font-size: 13px;">{{ $tr->retailer->user->name ?? 'Unknown' }}</h6>
                                                                    <small class="text-muted" style="font-size: 11px;">{{ $tr->total_orders ?? '0' }} Orders</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-end px-4">
                                                            <span class="text-success fw-800">₹{{ format_inr($tr->total_revenue, 0) }}</span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="2" class="text-center py-5 text-muted">No retailer data found.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
 
                        <!-- Top Products -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white border-0 pt-4 pb-2">
                                    <h6 class="m-0 font-weight-bold text-success text-uppercase d-flex align-items-center"
                                        style="letter-spacing: 0.05em; font-size: 0.85rem;">
                                        <i data-feather="trending-up" class="me-2"
                                            style="width: 18px; height: 18px;"></i> Top Products Ordered
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless align-middle mb-0">
                                            <thead class="bg-light text-muted"
                                                style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                <tr>
                                                    <th class="px-4 py-3 font-weight-bold" style="width: 45%;">Product</th>
                                                    <th class="px-3 py-3 font-weight-bold text-center">Units Sold</th>
                                                    <th class="text-right px-4 py-3 font-weight-bold">Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($topProducts as $tp)
                                                    <tr style="border-bottom: 1px solid #f8f9fc;">
                                                        <td class="px-4 py-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="rounded d-flex align-items-center justify-content-center me-3 text-success shadow-sm"
                                                                    style="width: 38px; height: 38px; background: #e8f5e9; flex-shrink: 0;">
                                                                    <i data-feather="package" style="width: 18px; height: 18px;"></i>
                                                                </div>
                                                                <h6 class="mb-0 font-weight-bold text-dark"
                                                                    style="font-size: 0.9rem; line-height: 1.4;">
                                                                    {{ \Illuminate\Support\Str::limit($tp->product_name, 40) }}
                                                                </h6>
                                                            </div>
                                                        </td>
                                                        <td class="px-3 py-3 text-center align-middle">
                                                            <div class="d-inline-flex align-items-baseline">
                                                                <span class="font-weight-bold text-dark me-1"
                                                                    style="font-size: 1rem;">{{ $tp->total_quantity_ordered }}</span>
                                                                <small class="text-muted font-weight-600" style="font-size: 0.7rem;">Units</small>
                                                            </div>
                                                        </td>
                                                        <td class="text-right px-4 py-3 align-middle">
                                                            <h6 class="mb-0 font-weight-bold text-success" style="font-size: 0.95rem;">
                                                                ₹{{ format_inr($tp->total_revenue, 0) }}</h6>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center py-5">
                                                            <div class="text-muted opacity-50">
                                                                <i data-feather="box" style="width: 40px; height: 40px;" class="mb-2"></i>
                                                                <p class="mb-0 font-weight-500">No product data available yet.</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    </div>
                @endif

                {{-- ========================================== --}}
                {{-- FIELD STAFF DASHBOARD --}}
                {{-- ========================================== --}}
                @if(Auth::user()->hasRole('fieldstaff'))
                    <div class="section-header-modern">
                        <div class="dash"></div>
                        <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Field Force Overview</h5>
                    </div>
                    <div class="row">
                        @php
                            $stats = [
                                ['label' => 'My Retailers', 'value' => $counts['retailers'], 'icon' => 'users', 'color' => 'var(--med-primary)', 'bg' => 'rgba(var(--med-primary-rgb), 0.1)', 'route' => '#'],
                                ['label' => 'Orders Generated', 'value' => $retailerOrderStats['total'], 'icon' => 'shopping-bag', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)', 'route' => '#'],
                                ['label' => 'Total Products', 'value' => $counts['products'], 'icon' => 'grid', 'color' => 'var(--med-accent)', 'bg' => 'rgba(var(--med-accent-rgb), 0.1)', 'route' => '#']
                            ];
                        @endphp
                        @foreach($stats as $stat)
                            <div class="col-xl-4 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm executive-metric-card cursor-pointer h-100" onclick="window.location.href='{{ $stat['route'] }}'">
                                    <div class="card-body p-4 text-center">
                                        <div class="icon-circle-lg bg-soft-primary mb-3" style="width: 60px; height: 60px; background: {{ $stat['bg'] }}">
                                            <i data-feather="{{ $stat['icon'] }}" style="color: {{ $stat['color'] }}; width: 22px; height: 22px;"></i>
                                        </div>
                                        @php 
                                            $slug = strtolower(str_replace(' ', '-', $stat['label'])); 
                                        @endphp
                                        <h6 class="text-muted fw-700 text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1px;">{{ $stat['label'] }}</h6>
                                        <h3 class="mb-0 fw-800" id="stat-{{ $slug }}" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="section-header-modern">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">My Performance (Trend)</h5>
                                <div class="ms-auto">{!! $timeSelectHtml !!}</div>
                            </div>
                            <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
                                <div id="monthlyOrdersChart"></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="section-header-modern">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0 status-period-label" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">Client Status (<span class="current-period-text">{{ isset($monthParam) ? \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->format('F Y') : date('F Y') }}</span>)</h5>
                            </div>
                            <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 15px;">
                                <div id="orderStatusChart"></div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ========================================== --}}
                {{-- ADMIN & SUPERADMIN & SALES MANAGER DASHBOARD --}}
                {{-- ========================================== --}}
                @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))

                    <div class="row g-3 mb-4">
                        @php
                            $adminStats = [
                                ['label' => 'Distributors', 'value' => $counts['distributors'], 'icon' => 'briefcase', 'color' => 'var(--med-primary)', 'bg' => 'rgba(var(--med-primary-rgb), 0.1)', 'route' => route('admin.distributors.index')],
                                ['label' => 'Retailers', 'value' => $counts['retailers'], 'icon' => 'shopping-bag', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)', 'route' => route('admin.retailers.index')],
                                ['label' => 'Field Staff', 'value' => $counts['field_staff'], 'icon' => 'users', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)', 'route' => route('admin.field-staffs.index')],
                                ['label' => 'Products', 'value' => $counts['products'], 'icon' => 'box', 'color' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.1)', 'route' => route('products.index')]
                            ];

                            if (Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                                $adminStats[] = ['label' => 'Sales Managers', 'value' => $counts['sales_managers'], 'icon' => 'user-check', 'color' => '#0ea5e9', 'bg' => 'rgba(14, 165, 233, 0.1)', 'route' => route('admin.sales-managers.index')];
                            }
                        @endphp
                        @foreach($adminStats as $stat)
                            <div class="col-xl col-md-6">
                                <div class="card border-0 shadow-sm executive-metric-card cursor-pointer h-100" onclick="window.location.href='{{ $stat['route'] }}'">
                                    <div class="card-body py-3 px-4 text-center">
                                        <div class="icon-circle-md bg-soft-primary mb-2 mx-auto" style="width: 45px; height: 45px; background: {{ $stat['bg'] }}">
                                            <i data-feather="{{ $stat['icon'] }}" style="color: {{ $stat['color'] }}; width: 18px; height: 18px;"></i>
                                        </div>
                                        @php 
                                            $slug = strtolower(str_replace(' ', '-', $stat['label'])); 
                                        @endphp
                                        <h6 class="text-muted fw-700 text-uppercase mb-1" style="font-size: 9px; letter-spacing: 1px;">{{ $stat['label'] }}</h6>
                                        <h4 class="mb-0 fw-800" id="stat-{{ $slug }}" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Executive Reports Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="section-header-modern mb-4">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Executive Dashboard Hub</h5>
                                <div class="ms-auto">
                                    {!! $timeSelectHtml !!}
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->hasAnyRole(['superadmin', 'admin', 'salesmanager']))
                            <!-- GLOBAL ADMIN TARGETS WIDGET -->
                            <div class="col-lg-12 mb-4">
                                <div class="card p-4 border-0 shadow-sm h-100" style="border-radius: 20px;">
                                    <div class="mb-4 d-flex align-items-center justify-content-between">
                                        <h6 class="fw-800 text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px; color: var(--med-primary);">
                                            {{ Auth::user()->hasRole('salesmanager') ? 'My Sales Performance' : 'Global Company Performance' }}
                                        </h6>
                                    </div>
                                    <div class="row text-center mb-4">
                                        <div class="col-md-3">
                                            <div class="text-muted small fw-bold text-uppercase">Total Target</div>
                                            <h4 class="fw-800 text-dark">₹{{ format_inr($data_extra['global_target'] ?? $data_extra['target'] ?? 0) }}</h4>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-muted small fw-bold text-uppercase">Total Achieved</div>
                                            <h4 class="fw-800 text-success">₹{{ format_inr($data_extra['global_achieved'] ?? $data_extra['achieved'] ?? 0) }}</h4>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-muted small fw-bold text-uppercase">Remaining</div>
                                            <h4 class="fw-800 text-warning">₹{{ format_inr($data_extra['global_remaining'] ?? $data_extra['remaining'] ?? 0) }}</h4>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-muted small fw-bold text-uppercase">Completion</div>
                                            <h4 class="fw-800 text-primary">{{ $data_extra['global_percent'] ?? $data_extra['achievement_percent'] ?? 0 }}%</h4>
                                        </div>
                                    </div>

                                    @if(!Auth::user()->hasRole('salesmanager'))

                                    <h6 class="fw-800 text-uppercase mt-4 mb-3" style="font-size: 0.75rem; letter-spacing: 1px; color: var(--med-primary);">Top Sales Managers Performance</h6>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="px-4 py-2 border-0 small fw-800 text-uppercase text-muted" style="font-size: 9px;">Sales Manager</th>
                                                    <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Team Target</th>
                                                    <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Team Achieved</th>
                                                    <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Remaining</th>
                                                    <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($data_extra['top_managers'] ?? [] as $sm)
                                                <tr>
                                                    <td class="px-4 py-2 fw-700 small">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="icon-circle-sm bg-soft-primary d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; border-radius: 8px;">
                                                                <i data-feather="user-check" class="text-primary" style="width: 14px;"></i>
                                                            </div>
                                                            <div class="text-dark">{{ $sm['name'] }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-2 text-end text-dark fw-bold small">₹{{ format_inr($sm['target']) }}</td>
                                                    <td class="px-3 py-2 text-end text-success fw-bold small">₹{{ format_inr($sm['achieved']) }}</td>
                                                    <td class="px-3 py-2 text-end text-warning fw-bold small">₹{{ format_inr($sm['remaining']) }}</td>
                                                    <td class="px-3 py-2 text-end" style="width: 20%;">
                                                        <div class="progress mb-1" style="height: 6px; border-radius: 3px;">
                                                            <div class="progress-bar {{ $sm['achievement_percent'] >= 100 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ min(100, $sm['achievement_percent']) }}%"></div>
                                                        </div>
                                                        <div class="text-muted" style="font-size: 9px;">{{ $sm['achievement_percent'] }}%</div>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted small">No active manager targets found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                        <!-- Field Staff Performance Table for Sales Manager -->
                                        <h6 class="fw-800 text-uppercase mt-4 mb-3" style="font-size: 0.75rem; letter-spacing: 1px; color: var(--med-primary);">Field Staff Performance</h6>
                                        <div class="table-responsive mb-4">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="px-4 py-2 border-0 small fw-800 text-uppercase text-muted" style="font-size: 9px;">Field Staff</th>
                                                        <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Target</th>
                                                        <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Achieved</th>
                                                        <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Orders</th>
                                                        <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Remaining</th>
                                                        <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($data_extra['field_staff_performance'] ?? [] as $fs)
                                                    <tr>
                                                        <td class="px-4 py-2 fw-700 small">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="icon-circle-sm bg-soft-primary d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; border-radius: 8px;">
                                                                    <i data-feather="user" class="text-primary" style="width: 14px;"></i>
                                                                </div>
                                                                <div class="text-dark">{{ $fs['name'] }}</div>
                                                            </div>
                                                        </td>
                                                        <td class="px-3 py-2 text-end text-dark fw-bold small">₹{{ format_inr($fs['target']) }}</td>
                                                        <td class="px-3 py-2 text-end text-success fw-bold small">₹{{ format_inr($fs['achieved']) }}</td>
                                                        <td class="px-3 py-2 text-end text-primary fw-bold small">{{ $fs['orders'] ?? 0 }}</td>
                                                        <td class="px-3 py-2 text-end text-warning fw-bold small">₹{{ format_inr($fs['remaining']) }}</td>
                                                        <td class="px-3 py-2 text-end" style="width: 20%;">
                                                            <div class="progress mb-1" style="height: 6px; border-radius: 3px;">
                                                                <div class="progress-bar {{ $fs['achievement_percent'] >= 100 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ min(100, $fs['achievement_percent']) }}%"></div>
                                                            </div>
                                                            <div class="text-muted" style="font-size: 9px;">{{ $fs['achievement_percent'] }}%</div>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4 text-muted small">No active field staff targets found.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="col-lg-6 mb-4">
                            <div class="card p-4 border-0 shadow-sm h-100" style="border-radius: 20px;">
                                <div class="mb-4 d-flex align-items-center justify-content-between">
                                    <h6 class="fw-800 text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px; color: var(--med-primary);">Market Share by Brand</h6>
                                </div>
                                <div id="brandDistributionChart"></div>
                            </div>
                        </div>

                        @if($topAreas->count() > 0 && Auth::user()->hasAnyRole(['admin', 'superadmin']))
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-radius: 20px;">
                                <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="fw-800 text-uppercase mb-0" style="font-size: 0.75rem; letter-spacing: 1px; color: var(--med-primary);">Area Leaderboard</h6>
                                    </div>
                                    <a href="{{ route('admin.reports.areas') }}" class="btn btn-pill-compact btn-outline-primary" style="font-size: 10px;">Analyze</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="px-4 py-2 border-0 small fw-800 text-uppercase text-muted" style="font-size: 9px;">Area</th>
                                                    <th class="px-3 py-2 border-0 small fw-800 text-uppercase text-muted text-center" style="font-size: 9px;">Retailers</th>
                                                    <th class="px-4 py-2 border-0 small fw-800 text-uppercase text-muted text-end" style="font-size: 9px;">Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topAreas as $area)
                                                <tr>
                                                    <td class="px-4 py-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="icon-circle-sm bg-soft-primary" style="width: 30px; height: 30px; border-radius: 8px;">
                                                                <i data-feather="map-pin" class="text-primary" style="width: 14px;"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-800 text-dark small">{{ $area->name }}</div>
                                                                <div class="text-muted" style="font-size: 9px;">{{ $area->district->name ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <span class="badge bg-soft-info text-info px-2 py-1 rounded-pill fw-700" style="font-size: 9px;">{{ $area->retailers_count }}</span>
                                                    </td>
                                                    <td class="px-4 py-2 text-end">
                                                        <div class="fw-800 text-primary small">₹{{ format_inr($area->total_revenue, 0) }}</div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>

                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin']))
                    <!-- Master Order Watch & Ops Center -->
                    <div class="row">
                        <div class="col-12">
                            <div class="section-header-modern">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Command Center: Live Pulse</h5>
                            </div>
                        </div>

                        <!-- Live Field Monitoring -->
                        <div class="col-xl-6 col-md-12 mb-4">
                            <div class="card h-100 executive-metric-card border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="icon-circle-lg bg-soft-info" style="width: 50px; height: 50px;">
                                                <i class="fa fa-map-marker text-info"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-800 mb-0">Field Staff Tracking</h6>
                                                <small class="text-muted">Historical GPS routing & daily timelines</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-4">
                                        <div class="col-4">
                                            <div class="p-3 bg-light rounded-4 text-center">
                                                <h6 class="text-muted small mb-1">Active</h6>
                                                <h5 class="fw-800 mb-0">--</h5>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-3 bg-light rounded-4 text-center">
                                                <h6 class="text-muted small mb-1">Visits</h6>
                                                <h5 class="fw-800 mb-0">--</h5>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-3 bg-light rounded-4 text-center">
                                                <h6 class="text-muted small mb-1">Alerts</h6>
                                                <h5 class="fw-800 mb-0 text-danger">0</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.field-staff.tracking') }}" class="btn btn-primary w-100 btn-outline-pill py-3" style="background: var(--med-primary); border: none;">View Staff Tracking</a>
                                </div>
                            </div>
                        </div>

                        <!-- Master Order Watch -->
                        <div class="col-xl-3 col-md-12 mb-4">
                            <div class="card h-100 executive-metric-card border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="icon-circle-lg bg-soft-primary" style="width: 50px; height: 50px;">
                                                <i class="fa fa-eye text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-800 mb-0">Master Order Watch</h6>
                                                <small class="text-muted">Minute-by-minute tracking</small>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-4">Staff attribution & fulfillment velocity tracking.</p>
                                    <div class="d-flex flex-column gap-2 mt-auto">
                                        <a href="{{ route('admin.reports.orders') }}" class="btn btn-pill-compact btn-outline-primary w-100">
                                            <i class="fa fa-shopping-basket me-2"></i>Retailer
                                        </a>
                                        <a href="{{ route('admin.reports.orders', ['order_type' => 'distributor']) }}" class="btn btn-pill-compact btn-outline-secondary w-100">
                                            <i class="fa fa-building-o me-2"></i>Distributor
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Growth & Performance -->
                        <div class="col-xl-3 col-md-12 mb-4">
                            <div class="card h-100 executive-metric-card border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="icon-circle-lg bg-soft-warning" style="width: 50px; height: 50px;">
                                                <i class="fa fa-line-chart text-warning"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-800 mb-0">Network Growth</h6>
                                                <small class="text-muted">Targets & Visit Velocity</small>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-4">Monitor target achievements and field force productivity metrics.</p>
                                    <div class="d-flex flex-column gap-2 mt-auto">
                                        <a href="{{ route('admin.reports.targets') }}" class="btn btn-pill-compact btn-outline-warning w-100">
                                            <i class="fa fa-bullseye me-2"></i>Targets
                                        </a>
                                        <a href="{{ route('admin.reports.visits') }}" class="btn btn-pill-compact btn-outline-info w-100">
                                            <i class="fa fa-map-marker me-2"></i>Visits
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    </div>


                    </div>



                    <div class="row">
                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']))
                            <div class="col-lg-12">
                                <div class="card p-4 h-100 mb-4 border-0 shadow-sm" style="border-radius: 20px;">
                                    <div class="d-flex align-items-center gap-2 mb-4">
                                        <div class="p-2 bg-soft-success rounded-circle">
                                            <i class="fa fa-handshake-o text-success" style="width: 16px; height: 16px;"></i>
                                        </div>
                                        <h6 class="fw-800 text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px; color: var(--med-primary);">Global Partnership Leaderboard</h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 border-right">
                                            <div class="px-2">
                                                <h6 class="text-muted fw-700 mb-3 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Top Distributors</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover align-middle">
                                                        <tbody>
                                                            @forelse($topDistributors as $td)
                                                                <tr>
                                                                    <td class="fw-700 py-2">{{ $td->distributor->user->name ?? 'Unknown' }}</td>
                                                                    <td class="text-end">
                                                                        <span class="text-success fw-800" style="font-size: 13px;">₹{{ format_inr($td->total_revenue, 0) }}</span>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="px-2">
                                                <h6 class="text-muted fw-700 mb-3 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Top Retailers</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover align-middle">
                                                        <tbody>
                                                            @forelse($topRetailers as $tr)
                                                                <tr>
                                                                    <td class="fw-700 py-2">{{ $tr->retailer->user->name ?? 'Unknown' }}</td>
                                                                    <td class="text-end">
                                                                        <span class="text-success fw-800" style="font-size: 13px;">₹{{ format_inr($tr->total_revenue, 0) }}</span>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                    </div>

                @endif

                @endif

                {{-- ========================================== --}}
                {{-- RECENT ORDERS (Visible to Most) --}}
                {{-- ========================================== --}}
                <div class="row mt-3">
                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager', 'distributor', 'fieldstaff', 'retailer']))
                        <div class="col-lg-{{ Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']) ? '6' : '12' }} mb-4">
                            <div class="section-header-modern">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">
                                    {{ Auth::user()->hasRole('retailer') ? 'My Recent Orders' : 'Recent Retailer Orders' }}
                                </h5>
                            </div>
                            <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-radius: 20px;">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <tr>
                                                <th class="px-4 py-3">Reference</th>
                                                @if(!Auth::user()->hasRole('retailer'))
                                                    <th>Stakeholder</th>
                                                @endif
                                                <th>Timeline</th>
                                                <th>Valuation</th>
                                                <th class="text-end px-4">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentRetailerOrders as $order)
                                                <tr>
                                                    <td class="px-4 py-3 fw-800 text-primary" style="font-size: 13px;">
                                                        {{ $order->order_code ?? '#' . $order->id }}
                                                    </td>
                                                    @if(!Auth::user()->hasRole('retailer'))
                                                        <td class="fw-700 text-dark" style="font-size: 12px;">{{ $order->retailer->user->name ?? 'N/A' }}</td>
                                                    @endif
                                                    <td class="text-muted" style="font-size: 12px;">
                                                        <div>{{ $order->created_at->format('d M, Y') }}</div>
                                                        @if(Auth::user()->hasRole('distributor') && isset($order->supply_chain_track))
                                                           <div class="mt-1">
                                                               <span class="badge bg-soft-{{ $order->supply_chain_track['color'] }} text-{{ $order->supply_chain_track['color'] }}" style="font-size: 9px; padding: 2px 6px;">
                                                                   <i class="fa fa-truck me-1"></i>{{ $order->supply_chain_track['label'] }}
                                                               </span>
                                                           </div>
                                                        @endif
                                                    </td>
                                                    <td class="fw-800 text-dark" style="font-size: 13px;">₹{{ format_inr($order->total_amount, 0) }}</td>
                                                    <td class="text-end px-4">
                                                        <span class="badge rounded-pill px-3 py-2 
                                                            {{ $order->status == 'delivered' ? 'bg-soft-success text-success' : ($order->status == 'cancelled' ? 'bg-soft-danger text-danger' : ($order->status == 'approved' ? 'bg-soft-info text-info' : 'bg-soft-primary text-primary')) }}"
                                                            style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                                            {{ $order->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted py-5">No recent activity detected.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                        <div class="col-lg-6 mb-4">
                            <div class="section-header-modern mb-3 mt-2">
                                <div class="dash"></div>
                                <h5 class="fw-800 text-uppercase mb-0" style="font-size: 0.9rem; letter-spacing: 1.5px; color: var(--med-primary);">Recent Distributor Orders</h5>
                            </div>
                            <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-radius: 20px;">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light" style="font-size: 11px; text-transform: uppercase;">
                                            <tr>
                                                <th class="px-4 py-3">Reference</th>
                                                <th>Partner</th>
                                                <th>Timeline</th>
                                                <th>Valuation</th>
                                                <th class="text-end px-4">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentDistributorOrders as $order)
                                                <tr>
                                                    <td class="px-4 py-3 fw-800 text-primary" style="font-size: 13px;">
                                                        {{ $order->order_code ?? '#' . $order->id }}
                                                    </td>
                                                    <td class="fw-700 text-dark" style="font-size: 12px;">{{ $order->distributor->user->name ?? 'N/A' }}</td>
                                                    <td class="text-muted" style="font-size: 12px;">{{ $order->created_at->format('d M, Y') }}</td>
                                                    <td class="fw-800 text-dark" style="font-size: 13px;">₹{{ format_inr($order->total_amount, 0) }}</td>
                                                    <td class="text-end px-4">
                                                        <span class="badge rounded-pill px-3 py-2 
                                                            {{ $order->status == 'delivered' ? 'bg-soft-success text-success' : ($order->status == 'cancelled' ? 'bg-soft-danger text-danger' : ($order->status == 'approved' ? 'bg-soft-info text-info' : 'bg-soft-primary text-primary')) }}"
                                                            style="font-size: 10px; font-weight: 700; text-transform: uppercase;">
                                                            {{ $order->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted py-5">No recent activity detected.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Scripts for Charts -->
                    <script>
function initDashboardCharts() {
                            // Theme Detection for charts
                            var isDark = document.body.classList.contains('dark-only') || document.documentElement.getAttribute('data-theme') === 'dark';
                            var chartTheme = isDark ? 'dark' : 'light';
                            var tooltipTheme = 'dark'; // Force dark tooltips for white font visibility

                            // Initialize Charts
                            window.charts = {};

                            // Chart Initialization Helper
                            function initPulseChart(selector, name, data, categories, color, isCurrency = false) {
                                if (document.querySelector(selector)) {
                                    var options = {
                                        series: [{ name: name, data: data }],
                                        chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false } },
                                        dataLabels: { enabled: false },
                                        stroke: { curve: 'smooth', width: 3 },
                                        xaxis: { categories: categories },
                                        colors: [color],
                                        tooltip: { 
                                            theme: tooltipTheme,
                                            y: {
                                                formatter: function (val) {
                                                    return isCurrency ? '₹' + Number(val).toLocaleString() : val;
                                                }
                                            }
                                        },
                                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1, stops: [0, 90, 100] } }
                                    };
                                    var chart = new ApexCharts(document.querySelector(selector), options);
                                    chart.render();
                                    return chart;
                                }
                                return null;
                            }

                            // Initialize Retailer/Order Trend Charts
                            window.charts.valuationTrendsChart = initPulseChart("#valuationTrendsChart", "Order Valuation", @json($chartData['valuations']), @json($chartData['labels']), '#00497a', true);
                            window.charts.retailerOrderFlowChart = initPulseChart("#retailerOrderFlowChart", "Order Activity", @json($chartData['counts']), @json($chartData['labels']), '#00497a');
                            
                            // Compatibility for other roles using the old ID
                            window.charts.monthlyOrdersChart = initPulseChart("#monthlyOrdersChart", "Order Activity", @json($chartData['counts']), @json($chartData['labels']), '#00497a');

                            // Order Status Donut Chart
                            if (document.querySelector("#orderStatusChart")) {
                                var orderTotal = {{ $retailerOrderStats['total'] }};
                                var statusSeries = orderTotal > 0 ? [
                                        {{ $retailerOrderStats['pending'] }},
                                        {{ $retailerOrderStats['processing'] }},
                                        {{ $retailerOrderStats['approved'] }},
                                        {{ $retailerOrderStats['delivered'] }},
                                        {{ $retailerOrderStats['cancelled'] }}
                                ] : [1];
                                var statusLabels = orderTotal > 0 ? ['Pending', 'Processing', 'Accepted', 'Delivered', 'Cancelled'] : ['No Orders Yet'];
                                var statusColors = orderTotal > 0 ? ['#f59e0b', '#3b82f6', '#0ea5e9', '#10b981', '#ef4444'] : ['#e2e8f0'];

                                var statusOptions = {
                                    series: statusSeries,
                                    labels: statusLabels,
                                    chart: { type: 'donut', height: 320 },
                                    colors: statusColors,
                                    stroke: { width: 0 },
                                    plotOptions: { pie: { donut: { size: '65%' } } },
                                    dataLabels: { enabled: false },
                                    tooltip: { enabled: orderTotal > 0, theme: tooltipTheme },
                                    legend: { position: 'bottom' },
                                    responsive: [{
                                        breakpoint: 480,
                                        options: {
                                            chart: { height: 350 },
                                            legend: { position: 'bottom' }
                                        }
                                    }]
                                };
                                window.charts.orderStatusChart = new ApexCharts(document.querySelector("#orderStatusChart"), statusOptions);
                                window.charts.orderStatusChart.render();
                            }

                            // Monthly Dist Orders Chart (Admin/SM)
                            @if(isset($monthlyDistributorOrdersChart))
                                if (document.querySelector("#monthlyDistOrdersChart")) {
                                    var monthlyDistOptions = {
                                        series: [{
                                            name: "Distributor Orders",
                                            data: @json($monthlyDistributorOrdersChart['counts'])
                                        }],
                                        chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false } },
                                        dataLabels: { enabled: false },
                                        stroke: { curve: 'smooth', width: 3 },
                                        xaxis: { categories: @json($monthlyDistributorOrdersChart['labels']) },
                                        colors: ['#8b5cf6'],
                                        tooltip: { theme: tooltipTheme },
                                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1, stops: [0, 90, 100] } }
                                    };
                                    window.charts.monthlyDistOrdersChart = new ApexCharts(document.querySelector("#monthlyDistOrdersChart"), monthlyDistOptions);
                                    window.charts.monthlyDistOrdersChart.render();
                                }
                            @endif

                            // Brand Sales Distribution Chart
                            @if(isset($brandSalesDistribution))
                                if (document.querySelector("#brandDistributionChart")) {
                                    var brandValues = @json($brandSalesDistribution['values']);
                                    var brandLabels = @json($brandSalesDistribution['labels']);
                                    
                                    // Sort brands alphabetically to maintain consistent order and color mapping
                                    let brandZip = brandLabels.map((l, i) => ({ label: l, value: brandValues[i] }));
                                    brandZip.sort((a, b) => a.label.localeCompare(b.label));
                                    brandLabels = brandZip.map(z => z.label);
                                    brandValues = brandZip.map(z => Number(z.value));
                                    var brandTotal = brandValues.reduce((a, b) => a + b, 0);
                                    var brandColors = ['#00497a', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#0ea5e9'];
                                    var hasBrandData = brandValues.length > 0;
                                    var isBrandZero = hasBrandData && brandTotal === 0;

                                    var brandOptions = {
                                        series: isBrandZero ? brandValues.map(() => 1) : (hasBrandData ? brandValues : [1]),
                                        labels: hasBrandData ? brandLabels : ['No Sales Data'],
                                        chart: { type: 'donut', height: 320 },
                                        colors: isBrandZero ? brandLabels.map(() => '#e2e8f0') : (hasBrandData ? brandColors : ['#e2e8f0']),
                                        stroke: { width: 0 },
                                        dataLabels: { enabled: false },
                                        plotOptions: { 
                                            pie: { 
                                                donut: { 
                                                    size: '70%',
                                                    labels: {
                                                        show: true,
                                                        total: {
                                                            show: true,
                                                            showAlways: true,
                                                            label: 'Total Revenue',
                                                            fontSize: '12px',
                                                            fontWeight: 600,
                                                            color: '#64748b',
                                                            formatter: function (w) {
                                                                 if (!hasBrandData || isBrandZero) return '₹0';
                                                                return '₹' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString(undefined, {maximumFractionDigits: 0});
                                                            }
                                                        },
                                                        value: {
                                                            show: true,
                                                            fontSize: '20px',
                                                            fontWeight: 800,
                                                            color: '#1e293b'
                                                        }
                                                    }
                                                } 
                                            } 
                                        },
                                        legend: { 
                                            position: 'right',
                                            markers: { fillColors: isBrandZero ? brandColors : undefined }
                                        },
                                        tooltip: { 
                                            enabled: hasBrandData && !isBrandZero,
                                            theme: tooltipTheme,
                                            y: { formatter: (val) => '₹' + val.toLocaleString() }
                                        },
                                        responsive: [{
                                            breakpoint: 480,
                                            options: {
                                                chart: { height: 380 },
                                                legend: { position: 'bottom' }
                                            }
                                        }]
                                    };
                                    window.charts.brandSalesChart = new ApexCharts(document.querySelector("#brandDistributionChart"), brandOptions);
                                    window.charts.brandSalesChart.render();
                                }
                            @endif
                        }

                        function updateDashboardPeriod(btn, period) {
                            // Update active button state immediately for responsiveness
                            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                            btn.classList.add('active');

                            // Show loading state (optional but premium)
                            const containers = ['#monthlyOrdersChart', '#orderStatusChart', '#monthlyDistOrdersChart', '#brandDistributionChart'];
                            containers.forEach(selector => {
                                const el = document.querySelector(selector);
                                if (el) el.style.opacity = '0.5';
                            });

                            // Fetch new data
                            fetch(`{{ route('dashboard.stats') }}?period=${period}`)
                                .then(response => response.json())
                                .then(data => {
                                    // Update Valuation Chart
                                    if (window.charts.valuationTrendsChart && data.chartData) {
                                        window.charts.valuationTrendsChart.updateOptions({ xaxis: { categories: data.chartData.labels } });
                                        window.charts.valuationTrendsChart.updateSeries([{ data: data.chartData.valuations }]);
                                    }

                                    // Update Retailer Flow Chart
                                    if (window.charts.retailerOrderFlowChart && data.chartData) {
                                        window.charts.retailerOrderFlowChart.updateOptions({ xaxis: { categories: data.chartData.labels } });
                                        window.charts.retailerOrderFlowChart.updateSeries([{ data: data.chartData.counts }]);
                                    }

                                    // Update fallback Monthly chart
                                    if (window.charts.monthlyOrdersChart && data.chartData) {
                                        window.charts.monthlyOrdersChart.updateOptions({
                                            xaxis: { categories: data.chartData.labels }
                                        });
                                        window.charts.monthlyOrdersChart.updateSeries([{
                                            data: data.chartData.counts
                                        }]);
                                    }

                                    if (window.charts.orderStatusChart && data.retailerOrderStats) {
                                        const stats = data.retailerOrderStats;
                                        const hasOrders = stats.total > 0;
                                        window.charts.orderStatusChart.updateOptions({
                                            labels: hasOrders ? ['Pending', 'Processing', 'Accepted', 'Delivered', 'Cancelled'] : ['No Orders Yet'],
                                            colors: hasOrders ? ['#f59e0b', '#3b82f6', '#0ea5e9', '#10b981', '#ef4444'] : ['#e2e8f0'],
                                            tooltip: { enabled: hasOrders }
                                        });
                                        window.charts.orderStatusChart.updateSeries(hasOrders ? [
                                            stats.pending, stats.processing, stats.approved, stats.delivered, stats.cancelled
                                        ] : [1]);
                                    }

                                    if (window.charts.monthlyDistOrdersChart && data.monthlyDistributorOrdersChart) {
                                        window.charts.monthlyDistOrdersChart.updateOptions({
                                            xaxis: { categories: data.monthlyDistributorOrdersChart.labels }
                                        });
                                        window.charts.monthlyDistOrdersChart.updateSeries([{
                                            data: data.monthlyDistributorOrdersChart.counts
                                        }]);
                                    }

                                    if (window.charts.brandSalesChart && data.brandSalesDistribution) {
                                        const brandData = data.brandSalesDistribution;
                                         
                                         // Sort brands alphabetically to maintain consistent order and color mapping
                                         let brandZip = brandData.labels.map((l, i) => ({ label: l, value: brandData.values[i] }));
                                         brandZip.sort((a, b) => a.label.localeCompare(b.label));
                                         const brandLabels = brandZip.map(z => z.label);
                                         const brandValues = brandZip.map(z => Number(z.value));
                                        const brandTotal = brandValues.reduce((a, b) => a + b, 0);
                                         const brandColors = ['#00497a', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#0ea5e9'];
                                         const hasBrandData = brandData.values.length > 0;
                                         const isBrandZero = hasBrandData && brandTotal === 0;
                                        window.charts.brandSalesChart.updateOptions({
                                            labels: hasBrandData ? brandLabels : ['No Sales Data'],
                                             colors: isBrandZero ? brandLabels.map(() => '#e2e8f0') : (hasBrandData ? brandColors : ['#e2e8f0']),
                                             tooltip: { enabled: hasBrandData && !isBrandZero },
                                             legend: {
                                                 markers: {
                                                     fillColors: isBrandZero ? brandColors : undefined
                                                 }
                                             },
                                            plotOptions: { 
                                                pie: { 
                                                    donut: { 
                                                        size: '70%',
                                                        labels: {
                                                            show: true,
                                                            total: {
                                                                show: true,
                                                                showAlways: true,
                                                                label: 'Total Revenue',
                                                                fontSize: '12px',
                                                                fontWeight: 600,
                                                                color: '#64748b',
                                                                formatter: function (w) {
                                                                     if (!hasBrandData || isBrandZero) return '₹0';
                                                                    return '₹' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString(undefined, {maximumFractionDigits: 0});
                                                                }
                                                            },
                                                            value: {
                                                                show: true,
                                                                fontSize: '20px',
                                                                fontWeight: 800,
                                                                color: '#1e293b'
                                                            }
                                                        }
                                                    } 
                                                } 
                                            }
                                        });
                                         window.charts.brandSalesChart.updateSeries(isBrandZero ? brandLabels.map(() => 1) : (hasBrandData ? brandValues : [1]));
                                    }

                                    // 2. Update Stat Cards
                                    // Update retailer-related stats
                                    if (data.retailerOrderStats) {
                                        updateStatValue('total-orders', data.retailerOrderStats.total);
                                        updateStatValue('pending', data.retailerOrderStats.pending);
                                        updateStatValue('delivered', data.retailerOrderStats.delivered);
                                        // Specific for distributor view labels
                                        updateStatValue('order-volume', data.retailerOrderStats.total);
                                        updateStatValue('field-force-orders-generated', data.retailerOrderStats.total); // Field staff view label
                                    }

                                    // Update distributor orders stats (Admin/SM view)
                                    if (data.distributorOrderStats) {
                                        updateStatValue('total-request', data.distributorOrderStats.total);
                                        updateStatValue('dist-pending', data.distributorOrderStats.pending);
                                        updateStatValue('dist-delivered', data.distributorOrderStats.delivered);
                                    }

                                    // 3. Update Period Labels in Titles
                                    const periodLabel = period.charAt(0).toUpperCase() + period.slice(1);
                                    document.querySelectorAll('.current-period-text').forEach(el => {
                                        el.innerText = periodLabel;
                                    });

                                    // Restore opacity
                                    containers.forEach(selector => {
                                        const el = document.querySelector(selector);
                                        if (el) el.style.opacity = '1';
                                    });
                                    
                                    // Update URL without refresh (clean experience)
                                    const newUrl = new URL(window.location.href);
                                    newUrl.searchParams.set('period', period);
                                    window.history.pushState({period: period}, '', newUrl);

                                })
                                .catch(error => {
                                    console.error('Error fetching dashboard stats:', error);
                                    containers.forEach(selector => {
                                        const el = document.querySelector(selector);
                                        if (el) el.style.opacity = '1';
                                    });
                                });
                        }

                        function updateStatValue(slug, value) {
                            const el = document.getElementById(`stat-${slug}`);
                            if (el) {
                                // Animate the number change for premium feel
                                const startValue = parseInt(el.innerText.replace(/,/g, '')) || 0;
                                animateNumber(el, startValue, value, 800);
                            }
                        }

                        function animateNumber(obj, start, end, duration) {
                            let startTimestamp = null;
                            const step = (timestamp) => {
                                if (!startTimestamp) startTimestamp = timestamp;
                                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                                const current = Math.floor(progress * (end - start) + start);
                                obj.innerHTML = current.toLocaleString();
                                if (progress < 1) {
                                    window.requestAnimationFrame(step);
                                }
                            };
                            window.requestAnimationFrame(step);
                        }
                        
                        function openMyRetailersModal(event) {
                            if (event) {
                                event.preventDefault();
                            }
                            const modalEl = document.getElementById('myRetailersModal');
                            let bsModal = bootstrap.Modal.getInstance(modalEl);
                            if (!bsModal) {
                                bsModal = new bootstrap.Modal(modalEl, { focus: false });
                            }
                            bsModal.show();
                            
                            const container = document.getElementById('myRetailersList');
                            container.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading your retailers...</p></div>';
                            
                            fetch('{{ route("dashboard.retailers") }}')
                                .then(response => response.json())
                                .then(data => {
                                    if(data.data && data.data.length > 0) {
                                        let html = '<div class="row g-3">';
                                        data.data.forEach(r => {
                                            const hasAction = r.orders_needing_action > 0;
                                            html += `
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: 12px; background: rgba(120,120,120,0.05);">
                                                        ${hasAction ? '<div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #f59e0b;"></div>' : ''}
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <div>
                                                                    <h6 class="fw-800 mb-1" style="color: var(--med-primary); font-size: 0.95rem;">${r.shop_name}</h6>
                                                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;"><i class="fa fa-user me-1"></i>${r.name}</p>
                                                                </div>
                                                                ${hasAction ? `<span class="badge bg-warning text-dark rounded-pill fw-bold" style="font-size: 0.65rem; padding: 0.35em 0.65em;"><i class="fa fa-exclamation-circle me-1"></i>${r.orders_needing_action} Action</span>` : ''}
                                                            </div>
                                                            <div class="mb-2">
                                                                <p class="mb-0 text-muted" style="font-size: 0.75rem;"><i class="fa fa-phone me-1"></i>${r.phone !== 'N/A' ? r.phone : 'No Phone'} <span class="mx-1">|</span> <i class="fa fa-envelope me-1"></i>${r.email !== 'N/A' ? r.email : 'No Email'}</p>
                                                                <p class="mb-0 text-muted text-truncate" style="font-size: 0.75rem;" title="${r.address}"><i class="fa fa-map-marker-alt me-1"></i>${r.address}</p>
                                                            </div>
                                                            <div class="row g-2 mt-1">
                                                                <div class="col-6">
                                                                    <div class="p-2 rounded h-100" style="background: rgba(120, 120, 120, 0.08);">
                                                                        <p class="text-muted mb-0" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Revenue</p>
                                                                        <p class="fw-800 mb-0 text-success" style="font-size: 0.9rem;">₹${r.total_revenue}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 text-end">
                                                                    <div class="p-2 rounded h-100 text-start" style="background: rgba(120, 120, 120, 0.08);">
                                                                        <p class="text-muted mb-0" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Orders</p>
                                                                        <p class="fw-bold mb-0" style="font-size: 0.9rem;">${r.total_orders}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 mt-1">
                                                                    <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: rgba(0, 73, 122, 0.04);">
                                                                        <div class="text-center px-1">
                                                                            <p class="mb-0 text-muted" style="font-size: 0.6rem; text-transform: uppercase;">Pending</p>
                                                                            <p class="mb-0 fw-bold" style="font-size: 0.8rem;">${r.status_counts?.pending || 0}</p>
                                                                        </div>
                                                                        <div class="text-center px-1">
                                                                            <p class="mb-0 text-muted" style="font-size: 0.6rem; text-transform: uppercase;">Processing</p>
                                                                            <p class="mb-0 fw-bold" style="font-size: 0.8rem;">${r.status_counts?.processing || 0}</p>
                                                                        </div>
                                                                        <div class="text-center px-1">
                                                                            <p class="mb-0 text-muted" style="font-size: 0.6rem; text-transform: uppercase;">Approved</p>
                                                                            <p class="mb-0 fw-bold" style="font-size: 0.8rem;">${r.status_counts?.approved || 0}</p>
                                                                        </div>
                                                                        <div class="text-center px-1">
                                                                            <p class="mb-0 text-muted" style="font-size: 0.6rem; text-transform: uppercase;">Delivered</p>
                                                                            <p class="mb-0 fw-bold text-success" style="font-size: 0.8rem;">${r.status_counts?.delivered || 0}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                                        });
                                        html += '</div>';
                                        container.innerHTML = html;
                                    } else {
                                        container.innerHTML = '<div class="text-center p-5 text-muted"><i data-feather="users" style="width: 40px; height: 40px; opacity: 0.5; margin-bottom: 15px;"></i><p>No retailers found.</p></div>';
                                        feather.replace();
                                    }
                                })
                                .catch(error => {
                                    container.innerHTML = '<div class="alert alert-danger">Error loading retailers.</div>';
                                });
                            }
                            // Works for both initial page load and AJAX reinjects
function tryInitDashboard() {
    if (typeof ApexCharts !== 'undefined') {
        initDashboardCharts();
    } else {
        setTimeout(tryInitDashboard, 50);
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryInitDashboard);
} else {
    tryInitDashboard();
}
                    
                    </script>

            </div>
        </div>
    </div>

    {{-- Modal for Retailers --}}
    <div class="modal fade" id="myRetailersModal" tabindex="-1" aria-labelledby="myRetailersModalLabel" aria-hidden="true" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-bottom-0 pb-3 shadow-sm" style="z-index: 10;">
                    <h5 class="modal-title fw-800 d-flex align-items-center gap-2" id="myRetailersModalLabel" style="color: var(--med-primary);">
                        <div class="p-2 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i data-feather="users" class="text-primary" style="width: 18px; height: 18px;"></i>
                        </div>
                        My Retailers Network
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="myRetailersList">
                        <!-- Populated via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>


