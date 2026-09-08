    @if(isset($upcomingRewards) && count($upcomingRewards) > 0)
        @foreach($upcomingRewards as $reward)
            @php $slug = Str::slug($reward['brand'] ?? 'brand-' . $loop->index); @endphp
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 d-flex flex-column summary-card" style="border-radius: 18px; border: 1px solid var(--med-border, #e2e8f0) !important; transition: all 0.3s ease;">
                    <div class="card-body p-4 d-flex flex-column flex-grow-1">
                        
                        <!-- Header: Brand Name -->
                        <div class="mb-3 pb-2 border-bottom" style="border-color: var(--med-border, #f1f5f9) !important;">
                            <span class="text-uppercase fw-bold d-block text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">Brand</span>
                            <h6 class="fw-bold mb-0 text-primary text-uppercase" style="letter-spacing: 0.5px; font-size: 1.1rem; color: #0d6efd !important;">{{ $reward['brand'] ?? 'Loyalty Rewards' }}</h6>
                        </div>

                        <!-- Points Stats Grid (Current vs Target) -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2.5 px-3 rounded-3 border text-start" style="background-color: var(--med-bg-subtle, rgba(255, 255, 255, 0.05)) !important; border-color: var(--med-border, #e2e8f0) !important;">
                                    <span class="d-block fw-bold text-uppercase text-muted text-nowrap" style="font-size: 0.65rem; letter-spacing: 0.5px; white-space: nowrap;">Current Pts</span>
                                    <span class="fw-bold fs-6 text-main-theme" style="font-weight: 800;">{{ number_format($reward['current_total'], 2) }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2.5 px-3 rounded-3 border text-end" style="background-color: var(--med-bg-subtle, rgba(255, 255, 255, 0.05)) !important; border-color: var(--med-border, #e2e8f0) !important;">
                                    <span class="d-block fw-bold text-uppercase text-muted text-nowrap" style="font-size: 0.65rem; letter-spacing: 0.5px; white-space: nowrap;">Target Pts</span>
                                    @if($reward['next_target'])
                                        <span class="fw-bold fs-6" style="color: #0d6efd !important; font-weight: 800;">{{ number_format($reward['next_target'], 2) }}</span>
                                    @else
                                        <span class="fw-bold fs-6 text-success" style="font-weight: 800;">Achieved</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar & Remaining Counter -->
                        @if($reward['next_target'])
                            @php 
                                $progress = min(100, max(0, ($reward['current_total'] / $reward['next_target']) * 100)); 
                                $remaining = max(0, $reward['next_target'] - $reward['current_total']);
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                    <span class="text-muted" style="font-weight: 600;">Progress</span>
                                    <span class="fw-bold text-primary" style="color: #0d6efd !important;">{{ number_format($progress, 1) }}%</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 10px; background-color: var(--med-bg-subtle, #e2e8f0); overflow: hidden;">
                                    <div class="progress-bar rounded-pill" role="progressbar" style="width: {{ $progress }}%; background-color: #0d6efd !important;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 0.75rem;">
                                    <span class="text-muted" style="font-weight: 600;"><i class="fa fa-flag-checkered me-1"></i>Remaining : &nbsp;</span>
                                    <span class="fw-bold text-main-theme">{{ number_format($remaining, 2) }} pts needed</span>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                    <span class="text-muted" style="font-weight: 600;">Progress</span>
                                    <span class="fw-bold text-success">100% Completed</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 10px; background-color: var(--med-bg-subtle, #e2e8f0); overflow: hidden;">
                                    <div class="progress-bar rounded-pill" role="progressbar" style="width: 100%; background-color: #198754 !important;"></div>
                                </div>
                                <div class="text-end text-success fw-semibold mt-1" style="font-size: 0.75rem;">
                                    <i class="fa fa-check-circle me-1"></i>All slabs unlocked!
                                </div>
                            </div>
                        @endif

                        <!-- Unclaimed Rewards Available -->
                        @if(isset($reward['achieved_rewards']) && count($reward['achieved_rewards']) > 0)
                            <div class="mt-2 pt-2 border-top" style="border-color: var(--med-border, #f1f5f9) !important;">
                                <div class="mb-2">
                                    <span class="fw-bold" style="font-size: 0.75rem;"><i class="fa fa-gift me-1"></i>Unclaimed Rewards</span>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    @foreach($reward['achieved_rewards'] as $achieved)
                                        @php
                                            $opts = isset($achieved['reward_options']) && is_array($achieved['reward_options']) && count($achieved['reward_options']) > 0 
                                                ? $achieved['reward_options'] 
                                                : [$achieved['reward']];
                                        @endphp
                                        <div class="p-2 px-3 rounded-3 border" style="background-color: rgba(245, 158, 11, 0.1) !important; border-color: rgba(245, 158, 11, 0.25) !important;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold" style="font-size: 0.8rem; color: #f59e0b !important;">
                                                    <i class="fa fa-check-circle me-1 text-success"></i>Unlocked ({{ number_format($achieved['threshold'], 0) }} Pts)
                                                </span>
                                                <span class="badge border rounded-pill px-2 py-1 text-nowrap" style="font-size: 0.68rem; font-weight: 600; background-color: #c07b05 !important; color: #ffffff !important; border-color: #d97706 !important;">Unclaimed</span>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1.5 align-items-center mt-1">
                                                <span class="fw-bold text-muted" style="font-size: 0.7rem;">Options: &nbsp;</span>
                                                @foreach($opts as $opt)
                                                    <span class="badge border px-2.5 py-1 rounded-pill d-inline-flex align-items-center shadow-2xs text-main-theme" style="font-size: 0.72rem; font-weight: 600; background-color: var(--med-bg-card, #ffffff) !important; border-color: var(--med-border, #cbd5e1) !important;">
                                                        <i class="fa fa-gift me-1 text-primary" style="font-size: 10px;"></i>{{ $opt }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Modal Trigger Button at Card Bottom -->
                        <div class="mt-auto pt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#brandRoadmapModal-{{ $slug }}" style="font-size: 0.8rem; padding: 8px 16px; border-width: 1.5px;">
                                <i class="fa fa-map-o"></i> <span>View Brand Roadmap</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brand Roadmap Modal -->
            @push('modals')
            <div class="modal fade" id="brandRoadmapModal-{{ $slug }}" tabindex="-1" aria-labelledby="brandRoadmapModalLabel-{{ $slug }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 760px;">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: var(--med-bg-card, #ffffff) !important;">
                        <div class="modal-header text-white px-4 py-3 border-0" style="background: linear-gradient(135deg, #00497a 0%, #002b5c 100%) !important;">
                            <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2" id="brandRoadmapModalLabel-{{ $slug }}">
                                <i class="fa fa-map text-warning"></i><span class="text-white">{{ $reward['brand'] }} Reward Roadmap</span>
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1); opacity: 0.9;"></button>
                        </div>
                        <div class="modal-body p-4" style="background-color: var(--med-bg-card, #ffffff) !important;">
                            <div class="p-3.5 px-4 rounded-3 border modal-inner-box" style="border-color: var(--med-border, #e2e8f0) !important; background-color: var(--med-bg-subtle, rgba(255,255,255,0.03)) !important;">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2.5" style="border-color: var(--med-border, rgba(255,255,255,0.1)) !important;">
                                    <div>
                                        <span class="fw-bold small d-block text-main-theme" style="font-size: 0.9rem;">Milestone Progression</span>
                                        <div class="text-muted" style="font-size: 0.78rem;">Reward options for each points threshold</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="d-block text-muted" style="font-size: 0.75rem;">Total Earned</span>
                                        <span class="fw-bold text-primary" style="font-size: 1.1rem; color: #0d6efd !important;">{{ number_format($reward['current_total'], 2) }} Pts</span>
                                    </div>
                                </div>
                                @if(isset($reward['all_targets']) && count($reward['all_targets']) > 0)
                                    <div class="d-flex flex-column gap-2.5">
                                        @foreach($reward['all_targets'] as $target)
                                            @php
                                                $isAchieved = $reward['current_total'] >= $target['target'];
                                                $isNext = !$isAchieved && ($reward['next_target'] == $target['target']);
                                                $optionsList = isset($target['options']) && is_array($target['options']) ? $target['options'] : [ $target['reward'] ?? 'Reward' ];
                                            @endphp
                                            <div class="p-3 px-4 rounded-3 border d-flex align-items-center justify-content-between gap-3 shadow-xs w-100 milestone-card-item" style="border-color: {{ $isNext ? '#0d6efd' : 'var(--med-border, rgba(255,255,255,0.1))' }} !important; border-left: 5px solid {{ $isAchieved ? '#0d6efd' : ($isNext ? '#00497a' : 'var(--med-border, #cbd5e1)') }} !important; background-color: var(--med-bg-card, #ffffff) !important;">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <span class="fw-bold {{ $isNext ? '' : 'text-main-theme' }}" style="font-size: 1rem; color: {{ $isNext ? '#0d6efd !important' : 'inherit' }};">{{ number_format($target['target'], 0) }} Points</span>
                                                        @if($isAchieved)
                                                            <span class="badge border px-2.5 py-1 rounded-pill" style="font-size: 10px; background-color: rgba(13, 110, 253, 0.1) !important; color: #0d6efd !important; border-color: rgba(13, 110, 253, 0.25) !important;"><i class="fa fa-check-circle me-1"></i>Unlocked</span>
                                                        @elseif($isNext)
                                                            <span class="badge bg-primary text-white px-2.5 py-1 rounded-pill" style="font-size: 10px; background-color: #0d6efd !important;"><i class="fa fa-arrow-right me-1"></i>Next Goal</span>
                                                        @else
                                                            <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill" style="font-size: 10px; border-color: var(--med-border, #cbd5e1) !important;"><i class="fa fa-lock me-1"></i>Locked</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex flex-wrap align-items-center gap-1.5 mt-1.5">
                                                        <span class="fw-bold me-1 text-muted" style="font-size: 11px;">Rewards:</span>
                                                        @foreach($optionsList as $opt)
                                                            <span class="badge border px-2.5 py-1 rounded-pill d-inline-flex align-items-center shadow-2xs text-main-theme reward-opt-badge" style="font-size: 11px; font-weight: 600; border-color: var(--med-border, #cbd5e1) !important;">
                                                                <i class="fa fa-gift text-primary me-1" style="font-size: 10px;"></i>{{ $opt }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="text-end flex-shrink-0">
                                                    @if($isAchieved)
                                                        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background-color: rgba(13, 110, 253, 0.1);">
                                                            <i class="fa fa-check-circle text-primary" style="font-size: 1.15rem;"></i>
                                                        </div>
                                                    @elseif($isNext)
                                                        <div class="d-flex align-items-center justify-content-center rounded-circle border border-primary" style="width: 36px; height: 36px; background-color: var(--med-bg-subtle, rgba(255,255,255,0.05));">
                                                            <i class="fa fa-unlock-alt text-primary" style="font-size: 1.15rem;"></i>
                                                        </div>
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center rounded-circle border" style="width: 36px; height: 36px; background-color: var(--med-bg-subtle, rgba(255,255,255,0.05));">
                                                            <i class="fa fa-lock text-muted" style="font-size: 1rem;"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted small mb-0">No roadmap slabs configured for this brand.</p>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer px-4 py-2.5 border-0 justify-content-end" style="background-color: var(--med-bg-card, #ffffff) !important;">
                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal" style="font-size: 0.85rem;">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endpush
        @endforeach
    @else
        <div class="col-12">
            <div class="text-center p-4">
                <p class="text-muted mb-0">No loyalty slabs defined.</p>
            </div>
        </div>
    @endif

