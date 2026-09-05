@extends('layouts.admin')

@section('page-body')
    <style>
        /* PREMIUM 3D GOLD COIN (Synced with Header big-gold-coin) */
        .coin-wrapper {
            perspective: 1000px;
            padding: 5px;
            display: inline-block;
        }
        
        .big-gold-coin-dashboard {
            width: 70px;
            height: 70px;
            background: radial-gradient(ellipse at center, #ffd700 0%, #daa520 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 
                inset 0 0 0 4px #d4af37,
                0 4px 10px rgba(0,0,0,0.1);
            animation: coin-shine-flip-dashboard 7s infinite ease-in-out;
            position: relative;
            transform-style: preserve-3d;
            flex-shrink: 0;
            margin: 0 auto 20px auto;
        }

        .big-gold-coin-dashboard::before {
            content: '';
            position: absolute;
            inset: 8px;
            border: 2px dashed rgba(184, 134, 11, 0.5);
            border-radius: 50%;
        }

        .coin-inner-dashboard {
            font-size: 35px;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(184, 134, 11, 0.8);
            transform: translateZ(1px);
        }

        @keyframes coin-flip-dashboard {
            0%, 90% { transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); }
        }

        @keyframes coin-shine-flip-dashboard {
            0%, 75% { transform: rotateY(0deg); filter: brightness(1) drop-shadow(0 0 3px rgba(184, 134, 11, 0.3)); }
            85% { filter: brightness(1.8) drop-shadow(0 0 15px rgba(212, 175, 55, 0.8)); transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); filter: brightness(1) drop-shadow(0 0 3px rgba(184, 134, 11, 0.3)); }
        }

        /* Entrance Animations */
        .entrance-animate {
            animation: slideUpFade 0.6s ease-out forwards;
            opacity: 0;
        }
        @keyframes slideUpFade {
            from { transform: translateY(25px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }

        /* DataTable Polished Padding & Alignment */
        #retailer-points-table thead th {
            padding: 20px 25px !important;
            background: var(--med-bg-card, #fff);
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--med-border, #f1f5f9) !important;
        }
        #retailer-points-table tbody td {
            padding: 22px 25px !important;
            border-bottom: 1px solid var(--med-border, #f1f5f9) !important;
            vertical-align: middle !important;
        }
        
        .table-controls-row {
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--med-border, #f1f5f9);
            background: rgba(0, 0, 0, 0.01);
        }
        .dt-buttons {
            display: flex !important;
            gap: 8px !important;
            margin-bottom: 0 !important;
        }
        .dt-buttons .btn {
            margin: 0 !important;
            border-radius: 8px !important;
            padding: 5px 12px !important;
        }
        .dataTables_filter {
            margin: 0 !important;
        }
        .dataTables_filter input {
            border-radius: 10px !important;
            padding: 8px 15px !important;
            border: 1px solid var(--med-border, #dee2e6) !important;
            outline: none !important;
            width: 250px !important;
        }
        
        /* Updated Points Card Style */
        .loyalty-widget-card {
            background: linear-gradient(135deg, #00497a 0%, #002b5c 100%) !important;
            border-radius: 20px;
            color: #fff !important;
        }
        .loyalty-widget-card .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        .loyalty-widget-card h1 {
            color: #fff !important;
        }

        /* Spacing Fix for DataTable Footer */
        .dataTables_info {
            padding-left: 30px !important;
            padding-bottom: 25px !important;
            padding-top: 25px !important;
            color: var(--med-text-muted, #64748b) !important;
            font-size: 0.85rem !important;
        }
        .dataTables_paginate {
            padding-right: 30px !important;
            padding-bottom: 25px !important;
            padding-top: 25px !important;
        }
        .dataTables_paginate .paginate_button {
            border: none !important;
            border-radius: 8px !important;
            margin: 0 2px !important;
            background: transparent !important;
            padding: 5px 12px !important;
        }
        .dataTables_paginate .paginate_button.current {
            background: var(--med-primary, #00497a) !important;
            color: white !important;
            border: none !important;
        }
        .dataTables_paginate .paginate_button:hover {
            background: rgba(0, 73, 122, 0.1) !important;
            color: var(--med-primary, #00497a) !important;
            border: none !important;
        }
    </style>

    <div class="container-fluid">
        <div class="page-title">
            <div class="row align-items-center">
                <div class="col-6">
                    <h3 class="fw-bold m-0" style="color: var(--med-text-main, #1e293b);">Loyalty & Credits</h3>
                    <p class="text-muted small m-0">Dynamic tracking of your earned rewards</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">


            <!-- Upcoming Rewards -->
            <div class="col-xl-12 entrance-animate delay-2 mb-4">
                <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden; background: var(--med-bg-card);">
                    <div class="card-header bg-white py-3 px-4 border-bottom" style="background: var(--med-bg-card) !important;">
                        <h5 class="card-title mb-0 fw-bold" style="color: var(--med-text-main);"><i class="fa fa-gift me-2 text-warning"></i>Available Rewards</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @forelse($upcomingRewards as $reward)
                                <div class="col-12 mb-4">
                                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; background: #fff;">
                                        <div class="card-body p-4">
                                            <div class="row align-items-center">
                                                <!-- Brand Banner & Progress -->
                                                <div class="col-lg-4 mb-4 mb-lg-0 border-end-lg" style="border-right: 1px solid #e2e8f0;">
                                                    @php
                                                        $brandColors = [
                                                            'ATOMEDS' => ['bg' => 'rgba(13, 110, 253, 0.08)', 'text' => '#0d6efd', 'border' => 'rgba(13, 110, 253, 0.2)', 'pill_bg' => 'linear-gradient(45deg, #ff416c, #ff4b2b)'],
                                                            'ATOMSHIELD' => ['bg' => 'rgba(13, 110, 253, 0.12)', 'text' => '#084298', 'border' => 'rgba(13, 110, 253, 0.3)', 'pill_bg' => 'linear-gradient(45deg, #f03e3e, #d9480f)'],
                                                            'SUDHNEELGIRI' => ['bg' => 'rgba(13, 110, 253, 0.05)', 'text' => '#3d8bfd', 'border' => 'rgba(13, 110, 253, 0.15)', 'pill_bg' => 'linear-gradient(45deg, #e64980, #f76707)'],
                                                        ];
                                                        $bColor = $brandColors[strtoupper($reward['brand'])] ?? ['bg' => 'rgba(13, 110, 253, 0.1)', 'text' => '#0d6efd', 'border' => 'rgba(13, 110, 253, 0.2)', 'pill_bg' => 'linear-gradient(45deg, #ff416c, #ff4b2b)'];
                                                    @endphp
                                                    <div class="mb-3">
                                                        <h6 class="fw-bold mb-2 text-uppercase" style="color: {{ $bColor['text'] }};">{{ $reward['brand'] }}</h6>
                                                        @if(isset($reward['next_reward_options']) && is_array($reward['next_reward_options']) && count($reward['next_reward_options']) > 0)
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @php 
                                                                    $displayOptions = array_slice($reward['next_reward_options'], 0, 2); 
                                                                    $hiddenCount = count($reward['next_reward_options']) - 2; 
                                                                @endphp
                                                                @foreach($displayOptions as $opt)
                                                                    <span class="badge rounded-pill shadow-sm d-inline-flex align-items-center" style="background: {{ $bColor['text'] }}; color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 10px; padding: 4px 8px; font-weight: 600; line-height: 1;">
                                                                        <i class="fa fa-gift me-1" style="color: white; opacity: 0.9; font-size: 10px;"></i> <span>{{ $opt }}</span>
                                                                    </span>
                                                                @endforeach
                                                                @if($hiddenCount > 0)
                                                                    <span class="badge rounded-pill shadow-sm d-inline-flex align-items-center justify-content-center" style="background: {{ $bColor['pill_bg'] }}; border: 1px solid rgba(255,255,255,0.3); color: white; font-size: 10px; cursor: pointer; transition: all 0.2s; padding: 4px 8px; line-height: 1;" data-bs-toggle="modal" data-bs-target="#roadmapModal-{{ Str::slug($reward['brand']) }}">
                                                                        +{{ $hiddenCount }} more (View Roadmap)
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @elseif($reward['next_reward'])
                                                            <span class="badge rounded-pill shadow-sm d-inline-flex align-items-center" style="background: {{ $bColor['text'] }}; color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 10px; padding: 4px 8px; font-weight: 600; line-height: 1;">
                                                                <i class="fa fa-gift me-1" style="color: white; opacity: 0.9; font-size: 10px;"></i> <span>{{ $reward['next_reward'] }}</span>
                                                            </span>
                                                        @else
                                                            <span class="badge bg-light text-muted border rounded-pill shadow-sm" style="font-size: 11px;"><i class="fa fa-star me-1"></i>Max Level</span>
                                                        @endif
                                                    </div>
                                            
                                            <!-- Points Info -->
                                            <div class="d-flex justify-content-between text-muted mb-1 fw-semibold" style="font-size: 12px;">
                                                <span>Current Points: <span class="text-dark">{{ number_format($reward['current_total'], 2) }}</span></span>
                                                @if($reward['next_target'])
                                                    <span>Target: <span class="badge rounded-pill shadow-sm ms-1" style="background: {{ $bColor['pill_bg'] }}; color: white; padding: 3px 8px; font-size: 11px;">{{ number_format($reward['next_target'], 0) }}</span></span>
                                                @endif
                                            </div>
                                            
                                                        @if($reward['next_target'])
                                                            @php
                                                                $progress = min(100, ($reward['current_total'] / $reward['next_target']) * 100);
                                                            @endphp
                                                            <div class="progress mb-2" style="height: 6px; border-radius: 3px; background-color: rgba(13, 110, 253, 0.1);">
                                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%;"></div>
                                                            </div>
                                                            <div class="mt-3 d-flex flex-wrap justify-content-end align-items-center gap-2">
                                                                <button type="button" class="btn btn-sm shadow-sm text-nowrap d-flex align-items-center justify-content-center" style="background: white; color: {{ $bColor['text'] }}; border: 1px solid {{ $bColor['border'] }}; border-radius: 20px; font-weight: 700; height: 30px; padding: 0 12px;" data-bs-toggle="modal" data-bs-target="#roadmapModal-{{ Str::slug($reward['brand']) }}">
                                                                    <i class="fa fa-map me-1 d-flex align-items-center" style="font-size: 12px; height: 100%;"></i> <span style="line-height: 1;">View Roadmap</span>
                                                                </button>
                                                                <div class="d-inline-flex align-items-center justify-content-center rounded-pill px-3 text-nowrap shadow-sm" style="background: {{ $bColor['pill_bg'] }}; color: white; font-size: 11px; font-weight: 700; border: 1px solid rgba(255,255,255,0.3); height: 30px;">
                                                                    <i class="fa fa-lock me-1 d-flex align-items-center" style="font-size: 10px; height: 100%;"></i>
                                                                    <span style="line-height: 1;">{{ number_format($reward['next_target'] - $reward['current_total'], 0) }} points to go!</span>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="progress mb-2" style="height: 6px; border-radius: 3px; background-color: rgba(13, 110, 253, 0.1);">
                                                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
                                                            </div>
                                                            <div class="mt-3 d-flex justify-content-end align-items-center gap-2">
                                                                <button type="button" class="btn btn-sm shadow-sm text-nowrap d-flex align-items-center justify-content-center" style="background: white; color: {{ $bColor['text'] }}; border: 1px solid {{ $bColor['border'] }}; border-radius: 20px; font-weight: 600; height: 32px; padding: 0 12px;" data-bs-toggle="modal" data-bs-target="#roadmapModal-{{ Str::slug($reward['brand']) }}">
                                                                    <i class="fa fa-list me-1"></i> View All Rewards
                                                                </button>
                                                                <div class="d-inline-flex align-items-center justify-content-center rounded-pill px-3 shadow-sm text-nowrap bg-light text-muted border" style="font-size: 11px; font-weight: 700; height: 32px;">
                                                                    <i class="fa fa-star text-warning me-1" style="font-size: 12px;"></i>
                                                                    <span>All rewards unlocked</span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Roadmap Modal -->
                                                        <div class="modal fade" id="roadmapModal-{{ Str::slug($reward['brand']) }}" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                                                <div class="modal-content border-0 rounded-4 shadow">
                                                                    <div class="modal-header border-0 rounded-top-4" style="background: {{ $bColor['bg'] }};">
                                                                        <h5 class="modal-title fw-bold" style="color: {{ $bColor['text'] }};"><i class="fa fa-map text-muted me-2"></i>{{ $reward['brand'] }} Reward Roadmap</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body p-4">
                                                                        <div class="position-relative">
                                                                            <div class="position-absolute h-100" style="left: 15px; top: 0; width: 2px; background: #e2e8f0; z-index: 0;"></div>
                                                                            @if(isset($reward['all_targets']) && count($reward['all_targets']) > 0)
                                                                                @foreach($reward['all_targets'] as $targetIndex => $target)
                                                                                    @php
                                                                                        $isAchieved = $reward['current_total'] >= $target['target'];
                                                                                        $isNext = !$isAchieved && $reward['next_target'] == $target['target'];
                                                                                    @endphp
                                                                                    <div class="d-flex align-items-center mb-4 position-relative" style="z-index: 1;">
                                                                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0; background: {{ $isAchieved ? $bColor['pill_bg'] : ($isNext ? '#fff' : '#f1f5f9') }}; border: 2px solid {{ $isAchieved ? 'transparent' : ($isNext ? $bColor['text'] : '#cbd5e1') }}; color: {{ $isAchieved ? '#fff' : ($isNext ? $bColor['text'] : '#94a3b8') }}; box-shadow: {{ $isNext ? '0 0 0 4px ' . $bColor['bg'] : 'none' }};">
                                                                                            <i class="fa {{ $isAchieved ? 'fa-check' : ($isNext ? 'fa-unlock' : 'fa-lock') }}" style="font-size: 12px;"></i>
                                                                                        </div>
                                                                                        <div class="flex-grow-1 p-3 rounded-3 shadow-sm border" style="background: {{ $isNext ? $bColor['bg'] : '#fff' }}; border-color: {{ $isNext ? $bColor['border'] : '#e2e8f0' }} !important;">
                                                                                            <div class="d-flex justify-content-between mb-1">
                                                                                                <span class="fw-bold" style="color: {{ $isAchieved ? $bColor['text'] : '#475569' }};">{{ number_format($target['target'], 0) }} Pts</span>
                                                                                                @if($isAchieved)
                                                                                                    <span class="badge bg-success" style="font-size: 9px; padding: 3px 6px;">Unlocked</span>
                                                                                                @elseif($isNext)
                                                                                                    <span class="badge" style="background: {{ $bColor['text'] }}; font-size: 9px; padding: 3px 6px;">Next Goal</span>
                                                                                                @endif
                                                                                            </div>
                                                                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                                                                @foreach($target['options'] as $opt)
                                                                                                    @if($isAchieved)
                                                                                                        <span class="badge rounded-pill shadow-sm d-inline-flex align-items-center" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 10px; padding: 5px 10px; font-weight: 600;">
                                                                                                            <i class="fa fa-check-circle me-1" style="font-size: 11px;"></i>{{ $opt }}
                                                                                                        </span>
                                                                                                    @elseif($isNext)
                                                                                                        <span class="badge rounded-pill shadow-sm d-inline-flex align-items-center" style="background: {{ $bColor['text'] }}; color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 10px; padding: 5px 10px; font-weight: 600;">
                                                                                                            <i class="fa fa-gift me-1" style="color: white; font-size: 11px;"></i>{{ $opt }}
                                                                                                        </span>
                                                                                                    @else
                                                                                                        <span class="badge rounded-pill shadow-sm d-inline-flex align-items-center" style="background: white; color: #64748b; border: 1px dashed #cbd5e1; font-size: 10px; padding: 5px 10px; font-weight: 500;">
                                                                                                            <i class="fa fa-gift me-1" style="color: #94a3b8; font-size: 11px;"></i>{{ $opt }}
                                                                                                        </span>
                                                                                                    @endif
                                                                                                @endforeach
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            @else
                                                                                <div class="text-center text-muted py-3">No targets available.</div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Claimable Rewards -->
                                                    <div class="col-lg-8 ps-lg-4">
                                                        @if(count($reward['achieved_rewards']) > 0)
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h6 class="mb-0 text-dark fw-bold"><i class="fa fa-gift me-2" style="color: {{ $bColor['text'] }};"></i>Claimable Rewards</h6>
                                                                <span class="badge bg-success rounded-pill">{{ count($reward['achieved_rewards']) }} Available</span>
                                                            </div>
                                                            <div class="row row-cols-1 row-cols-md-2 g-3">
                                                                @foreach($reward['achieved_rewards'] as $achieved)
                                                                    <div class="col">
                                                                        <div class="position-relative overflow-hidden w-100 bg-white rounded-3 p-3 border shadow-sm h-100 d-flex flex-column justify-content-between" style="border-top: 4px solid var(--med-primary) !important;">
                                                                            <i class="fa fa-gift position-absolute text-light" style="font-size: 4rem; right: -10px; bottom: -10px; opacity: 0.3; transform: rotate(-15deg);"></i>
                                                                            
                                                                            <div class="mb-3 position-relative" style="z-index: 1;">
                                                                                <div class="text-muted fw-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1px;">
                                                                                    <i class="fa fa-unlock-alt me-1 text-success"></i> Unlocked at {{ number_format($achieved['threshold'], 0) }} Pts
                                                                                </div>
                                                                                <div class="d-flex flex-wrap gap-1">
                                                                                    @php $options = $achieved['reward_options'] ?? [$achieved['reward']]; @endphp
                                                                                    @foreach($options as $opt)
                                                                                        <div class="d-inline-flex align-items-center bg-soft-primary text-primary px-2 py-1 rounded-pill fw-bold border border-primary-subtle" style="font-size: 11px;">
                                                                                            <i class="fa fa-star me-1" style="font-size: 9px; color: #f59e0b;"></i>{{ $opt }}
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="position-relative mt-auto pt-2" style="z-index: 1;">
                                                                                <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm w-100 rounded-pill d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#claimModal{{ $achieved['slab_id'] }}" style="transition: all 0.2s ease;">
                                                                                    <span>Claim Reward</span> <i class="fa fa-arrow-right"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>

                                                            <!-- Modal for claiming reward -->
                                                            <div class="modal fade" id="claimModal{{ $achieved['slab_id'] }}" tabindex="-1" aria-labelledby="claimModalLabel{{ $achieved['slab_id'] }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content border-0 rounded-4 shadow">
                                                                        <div class="modal-header bg-light border-0 rounded-top-4">
                                                                            <h5 class="modal-title fw-bold" id="claimModalLabel{{ $achieved['slab_id'] }}"><i class="fa fa-gift text-primary me-2"></i>Claim Reward</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body p-4 pb-2">
                                                                            <p class="text-muted small mb-4" style="line-height: 1.5;">
                                                                                <strong>Note:</strong> Claiming this reward will deduct <strong>{{ number_format($achieved['threshold'], 2) }}</strong> points from your <strong>{{ $reward['brand'] }}</strong> balance. You will need to earn points again to unlock future rewards in this brand.
                                                                            </p>
                                                                            
                                                                            <form action="{{ route('retailer.loyalty-points.claim') }}" method="POST" id="claimForm{{ $achieved['slab_id'] }}">
                                                                                @csrf
                                                                                <input type="hidden" name="slab_id" value="{{ $achieved['slab_id'] }}">
                                                                                
                                                                                @if(isset($achieved['reward_options']) && count($achieved['reward_options']) > 0)
                                                                                    <h6 class="fw-bold mb-3 text-dark">Select your preferred reward:</h6>
                                                                                    <div class="reward-options-container">
                                                                                        @foreach($achieved['reward_options'] as $index => $option)
                                                                                            <label class="d-block m-0 p-0 w-100">
                                                                                                <input type="radio" name="selected_reward" value="{{ $option }}" class="d-none premium-reward-input" required>
                                                                                                <div class="premium-reward-option">
                                                                                                    <div class="reward-icon">
                                                                                                        <i class="fa fa-gift"></i>
                                                                                                    </div>
                                                                                                    <span class="reward-text" style="color: #333 !important;">{{ $option }}</span>
                                                                                                    <div class="check-icon">
                                                                                                        <i class="fa fa-check-circle"></i>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </label>
                                                                                        @endforeach
                                                                                    </div>
                                                                                @else
                                                                                    <input type="hidden" name="selected_reward" value="{{ $achieved['reward'] }}">
                                                                                    <div class="p-3 bg-light rounded-3 text-center mb-3 border">
                                                                                        <span class="fw-bold fs-5" style="color: #333 !important;">{{ $achieved['reward'] }}</span>
                                                                                    </div>
                                                                                @endif
                                                                            </form>
                                                                        </div>
                                                                        <div class="modal-footer border-0 p-4 pt-2">
                                                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                                            <button type="button" class="btn rounded-pill px-4 fw-bold shadow-sm" style="background: linear-gradient(135deg, #FFB75E 0%, #ED8F03 100%); color: white;" onclick="var form = document.getElementById('claimForm{{ $achieved['slab_id'] }}'); var selected = form.querySelector('input[name=\'selected_reward\']:checked, input[type=\'hidden\'][name=\'selected_reward\']'); if(!selected) { alert('Please choose a reward option.'); return false; } form.submit();">
                                                                                Confirm & Claim
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                                </div>
                                                            @endforeach
                                                            </div>
                                                        @else
                                                            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 bg-light rounded-3" style="min-height: 150px; border: 1px dashed #ced4da;">
                                                                <i class="fa fa-gift mb-2 text-secondary" style="font-size: 24px;"></i>
                                                                <span style="font-size: 12px; font-weight: 500;">No rewards available to claim yet.</span>
                                                                <span style="font-size: 11px;">Keep earning points to unlock rewards!</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            @empty
                                <div class="col-12 text-center text-muted py-4">No reward slabs configured yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="col-xl-12 entrance-animate delay-2">
                <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden; background: var(--med-bg-card);">
                    <div class="card-header bg-white py-3 px-4 border-bottom" style="background: var(--med-bg-card) !important;">
                        <h5 class="card-title mb-0 fw-bold" style="color: var(--med-text-main);"><i class="fa fa-history me-2"></i>Points Transaction History</h5>
                    </div>
                    <div id="retailer-table-controls" class="table-controls-row">
                        <!-- DT Buttons and Search will be moved here -->
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="retailer-points-table" class="display table table-hover align-middle mb-0" style="width:100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-muted small text-uppercase">Finalized Date</th>
                                        <th class="text-muted small text-uppercase">Type</th>
                                        <th class="text-muted small text-uppercase">Reference #</th>
                                        <th class="text-muted small text-uppercase">Items Summary</th>
                                        <th class="text-center text-muted small text-uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody style="color: var(--med-text-main);">
                                    @foreach($unifiedHistory as $item)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($item->date)->format('d M Y, h:i A') }}</td>
                                            <td>
                                                @if($item->type === 'CR')
                                                    <span class="badge bg-info text-dark px-3 py-2 fs-6 shadow-sm"><i class="fa fa-undo me-1"></i>Return / Credit</span>
                                                @elseif($item->type === 'REWARD')
                                                    <span class="badge bg-warning text-dark px-3 py-2 fs-6 shadow-sm"><i class="fa fa-gift me-1"></i>Reward Claim</span>
                                                @endif
                                            </td>
                                            <td><span class="fw-bold {{ $item->type === 'CR' ? 'text-info' : 'text-primary' }}">{{ $item->reference }}</span></td>
                                            <td class="small">
                                                {!! $item->details !!}
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $badgeClass = 'bg-primary';
                                                    if ($item->status === 'delivered') $badgeClass = 'bg-success';
                                                    if ($item->status === 'CREDIT') $badgeClass = 'bg-info';
                                                @endphp
                                                <span class="badge {{ $badgeClass }} text-uppercase" style="font-size: 10px;">
                                                    {{ str_replace('_', ' ', $item->status) }}
                                                </span>
                                                @if($item->type === 'REWARD' && $item->status === 'pending' && auth()->user()->hasAnyRole(['admin', 'superadmin', 'salesmanager']))
                                                    <form action="{{ route('admin.loyalty-points.mark-reward-given', $retailer->id) }}" method="POST" class="mt-1">
                                                        @csrf
                                                        <input type="hidden" name="redemption_id" value="{{ $item->id }}">
                                                        <button type="submit" class="btn btn-xs btn-success rounded-pill px-2" style="font-size: 10px;" onclick="return confirm('Mark this reward as fulfilled?');">
                                                            <i class="fa fa-check me-1"></i> Fulfill
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



@push('scripts')
    <script>
        $(document).ready(function () {
            // Fix Bootstrap modal rendering when inside containers with overflow/relative position
            $('.modal').appendTo('body');
            
            $('#retailer-points-table').DataTable({
                dom: "Bfrtip", 
                initComplete: function() {
                    let container = $('#retailer-table-controls');
                    $('.dt-buttons').appendTo(container);
                    $('.dataTables_filter').appendTo(container);
                },
                buttons: [
                    { extend: 'copy', className: 'btn btn-xs btn-outline-secondary' },
                    { extend: 'csv', className: 'btn btn-xs btn-outline-secondary' },
                    { extend: 'excel', className: 'btn btn-xs btn-outline-secondary' }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Filter transactions..."
                }
            });
        });
    </script>

    <style>
        /* Premium Reward Options */
        .premium-reward-option {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            margin-bottom: 10px;
            position: relative;
            overflow: hidden;
        }
        .premium-reward-option:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .premium-reward-option .reward-text {
            color: #334155 !important;
        }
        .premium-reward-input:checked + .premium-reward-option {
            border-color: #0ea5e9;
            background: #f0f9ff;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.15);
        }
        .premium-reward-input:checked + .premium-reward-option .reward-text {
            font-weight: 700;
            color: #0369a1 !important;
        }
        .premium-reward-input:checked + .premium-reward-option .check-icon {
            opacity: 1;
            transform: scale(1);
            color: #0ea5e9;
        }
        .premium-reward-input:checked + .premium-reward-option::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: #0ea5e9;
        }
        .check-icon {
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            margin-left: auto;
            font-size: 1.1rem;
        }
        .reward-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            margin-right: 12px;
            transition: all 0.25s;
        }
        .premium-reward-input:checked + .premium-reward-option .reward-icon {
            background: #e0f2fe;
            color: #0ea5e9;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @keyframes pulse-attention {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 65, 108, 0.7); }
            50% { transform: scale(1.03); box-shadow: 0 0 0 10px rgba(255, 65, 108, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 65, 108, 0); }
        }
        @keyframes bounce-icon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                // Fire confetti
                var duration = 3000;
                var animationEnd = Date.now() + duration;
                var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10000 };

                function randomInRange(min, max) {
                    return Math.random() * (max - min) + min;
                }

                var interval = setInterval(function() {
                    var timeLeft = animationEnd - Date.now();
                    if (timeLeft <= 0) {
                        return clearInterval(interval);
                    }
                    var particleCount = 50 * (timeLeft / duration);
                    confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
                    confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
                }, 250);

                // Premium SweetAlert
                Swal.fire({
                    html: `
                        <style>.swal2-icon { display: none !important; }</style>
                        <div style="padding: 20px;">
                            <div style="font-size: 60px; margin-bottom: 10px; animation: bounce-icon 2s infinite;">🏆</div>
                            <h2 style="font-weight: 800; font-size: 28px; background: linear-gradient(135deg, #FFB75E 0%, #ED8F03 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 15px;">Reward Claimed!</h2>
                            <p style="font-size: 16px; color: #475569; font-weight: 500;">{{ session('success') }}</p>
                        </div>
                    `,
                    background: '#ffffff',
                    backdrop: `rgba(15, 23, 42, 0.85)`,
                    showConfirmButton: true,
                    confirmButtonText: 'Awesome!',
                    confirmButtonColor: '#0ea5e9',
                    icon: undefined,
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0',
                        confirmButton: 'rounded-pill px-4 py-2 fw-bold shadow-sm',
                        icon: 'd-none border-0'
                    },
                    showClass: {
                        popup: 'animate__animated animate__zoomIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut animate__faster'
                    }
                });
            @endif
        });
    </script>
@endpush