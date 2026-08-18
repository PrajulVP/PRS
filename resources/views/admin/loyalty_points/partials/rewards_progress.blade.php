    @if(isset($upcomingRewards) && count($upcomingRewards) > 0)
        @foreach($upcomingRewards as $reward)
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; background: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-primary text-uppercase">{{ $reward['brand'] ?? 'Loyalty Rewards' }}</h6>
                            @if($reward['next_reward'])
                                <span class="badge bg-light text-dark border" style="font-size: 11px;"><i class="fa fa-gift me-1 text-muted"></i>{{ $reward['next_reward'] }}</span>
                            @else
                                <span class="badge bg-light text-muted border" style="font-size: 11px;"><i class="fa fa-star me-1"></i>Max Level</span>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between text-muted mb-1 fw-semibold" style="font-size: 12px;">
                            <span>Current: {{ number_format($reward['current_total'], 2) }}</span>
                            @if($reward['next_target'])
                                <span>Target: {{ number_format($reward['next_target'], 2) }}</span>
                            @endif
                        </div>
                        
                        @if($reward['next_target'])
                            @php 
                                $progress = min(100, ($reward['current_total'] / $reward['next_target']) * 100); 
                            @endphp
                            <div class="progress" style="height: 6px; border-radius: 3px; background-color: rgba(13, 110, 253, 0.1);">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="mt-2 text-end text-muted fw-semibold" style="font-size: 11px;">
                                {{ number_format($reward['next_target'] - $reward['current_total'], 2) }} more for next reward
                            </div>
                        @else
                            <div class="progress" style="height: 6px; border-radius: 3px; background-color: rgba(13, 110, 253, 0.1);">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                            </div>
                            <div class="mt-2 text-end text-muted fw-semibold" style="font-size: 11px;">
                                All rewards unlocked
                            </div>
                        @endif
                        
                        @if(isset($reward['achieved_rewards']) && count($reward['achieved_rewards']) > 0)
                            <div class="mt-3 pt-2 border-top border-light">
                                <p class="mb-1 text-muted fw-bold" style="font-size: 11px;">Available to Claim (Unclaimed):</p>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($reward['achieved_rewards'] as $achieved)
                                        <div class="d-flex justify-content-between align-items-center w-100 bg-light rounded px-2 py-1 mb-1">
                                            <div>
                                                <span class="badge bg-soft-primary text-primary" style="font-size: 10px;"><i class="fa fa-check-circle me-1"></i>{{ $achieved['reward'] }}</span>
                                                <span class="text-muted small ms-1" style="font-size: 10px;">Cost: {{ number_format($achieved['threshold'], 2) }}</span>
                                            </div>
                                            <span class="badge bg-secondary" style="font-size: 9px;">Unclaimed</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
        
        @if(isset($retailerPendingRedemptions) && $retailerPendingRedemptions->count() > 0)
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm" style="border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                    <div class="card-body p-4">
                        <p class="mb-3 text-danger fw-bold" style="font-size: 14px;"><i class="fa fa-clock-o me-2 text-danger"></i>Pending Claims (Action Required)</p>
                        <div class="d-flex flex-column gap-2">
                            @foreach($retailerPendingRedemptions as $pending)
                                <div class="d-flex justify-content-between align-items-center w-100 bg-light border rounded px-3 py-2">
                                    <div>
                                        <span class="badge bg-dark text-white" style="font-size: 12px;">{{ $pending->gift_name }} <span class="badge bg-white text-dark ms-1">{{ $pending->brand }}</span></span>
                                        <div class="text-muted small mt-1" style="font-size: 11px;">Cost: {{ number_format($pending->threshold, 2) }} Points | Claimed on: {{ \Carbon\Carbon::parse($pending->created_at)->format('d M, Y') }}</div>
                                    </div>
                                    <form action="{{ route('admin.loyalty-points.mark-reward-given', $selectedRetailer->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="redemption_id" value="{{ $pending->redemption_id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-semibold" onclick="return confirm('Mark this reward as given?');">Mark Given</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
        

    @else
        <div class="col-12">
            <div class="text-center p-4">
                <p class="text-muted mb-0">No loyalty slabs defined.</p>
            </div>
        </div>
    @endif
