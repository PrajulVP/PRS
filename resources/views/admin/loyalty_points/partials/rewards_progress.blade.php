    @if(isset($upcomingRewards) && count($upcomingRewards) > 0)
        @foreach($upcomingRewards as $reward)
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-top: 4px solid var(--med-primary) !important; background: #fff;">
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
                            <span>Current: ₹{{ number_format($reward['current_total'], 2) }}</span>
                            @if($reward['next_target'])
                                <span>Target: ₹{{ number_format($reward['next_target'], 2) }}</span>
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
                                ₹{{ number_format($reward['next_target'] - $reward['current_total'], 2) }} more for next reward!
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
                                <p class="mb-1 text-muted fw-bold" style="font-size: 11px;">Rewards Earned:</p>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($reward['achieved_rewards'] as $achieved)
                                        <div class="d-flex justify-content-between align-items-center w-100 bg-light rounded px-2 py-1 mb-1">
                                            <span class="badge bg-soft-primary text-primary" style="font-size: 10px;"><i class="fa fa-check-circle me-1"></i>{{ $achieved['reward'] }}</span>
                                            @if($achieved['is_redeemed'])
                                                <span class="badge bg-success text-white" style="font-size: 10px;">Given</span>
                                            @else
                                                <form action="{{ route('admin.loyalty-points.mark-reward-given', $selectedRetailer->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="slab_id" value="{{ $achieved['slab_id'] }}">
                                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-2 py-0" style="font-size: 10px;">Mark Given</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="text-center p-4">
                <p class="text-muted mb-0">No loyalty slabs defined.</p>
            </div>
        </div>
    @endif
