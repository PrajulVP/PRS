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
                                <div class="col-md-6 mb-4">
                                    <div class="border p-4 h-100 position-relative bg-white" style="border-radius: 12px; border-color: #e2e8f0 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                        <!-- Brand Banner -->
                                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                            <h6 class="mb-0 fw-bold text-primary text-uppercase" style="letter-spacing: 1px;">{{ $reward['brand'] }}</h6>
                                            <span class="badge bg-soft-primary text-primary rounded-pill px-2 py-1" style="font-size: 0.65rem; letter-spacing: 0.5px; border: 1px solid rgba(13, 110, 253, 0.2);">BRAND</span>
                                        </div>
                                        
                                        <!-- Points Info -->
                                        <div class="d-flex justify-content-between align-items-end mb-4">
                                            <div>
                                                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Points</span>
                                                <h3 class="mb-0 fw-bold text-dark">{{ number_format($reward['current_total'], 2) }}</h3>
                                            </div>
                                            @if($reward['next_target'])
                                                <div class="text-end">
                                                    <span class="text-muted small fw-semibold" style="font-size: 0.7rem;">Target: <span class="text-dark">{{ number_format($reward['next_target'], 2) }}</span></span><br>
                                                    <span class="text-dark mt-1 d-inline-block fw-bold" style="font-size: 0.8rem;"><i class="fa fa-gift me-1 text-muted"></i>{{ $reward['next_reward'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        @if($reward['next_target'])
                                            @php
                                                $progress = min(100, ($reward['current_total'] / $reward['next_target']) * 100);
                                            @endphp
                                            <div class="progress mb-3 bg-light" style="height: 6px; border-radius: 3px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%;"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                @if($progress < 100)
                                                    <div class="text-muted w-100 text-end">
                                                        <p class="small mb-0" style="font-size: 0.75rem;">Requires <strong>{{ number_format($reward['next_target'] - $reward['current_total'], 2) }}</strong> more</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        @if(count($reward['achieved_rewards']) > 0)
                                            <hr>
                                            <h6 class="fw-bold mb-3">Claimable Rewards:</h6>
                                            @foreach($reward['achieved_rewards'] as $achieved)
                                                <div class="d-flex justify-content-between align-items-center bg-white rounded-3 p-3 mb-3 shadow-sm border border-light position-relative overflow-hidden" style="transition: all 0.2s;">
                                                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(45deg, rgba(237, 143, 3, 0.05) 0%, transparent 100%); z-index: 0; pointer-events: none;"></div>
                                                    
                                                    <div class="d-flex flex-column" style="z-index: 1;">
                                                        <span class="text-muted small fw-bold text-uppercase">Target Reached</span>
                                                        <div class="fw-bold text-dark fs-5 mt-1">
                                                            <i class="fa fa-star text-warning me-1"></i> {{ number_format($achieved['threshold'], 2) }}
                                                        </div>
                                                    </div>
                                                    
                                                    <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold shadow-sm" style="background: linear-gradient(135deg, #FFB75E 0%, #ED8F03 100%); color: white; border: none; z-index: 1;" data-bs-toggle="modal" data-bs-target="#claimModal{{ $achieved['slab_id'] }}">
                                                        Claim Reward
                                                    </button>
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
                                                                    <strong>Note:</strong> Claiming this reward will deduct <strong>{{ number_format($achieved['threshold'], 2) }}</strong> from your progress. You will need to earn points again to unlock future rewards.
                                                                </p>
                                                                
                                                                <form action="{{ route('retailer.loyalty-points.claim') }}" method="POST" id="claimForm{{ $achieved['slab_id'] }}">
                                                                    @csrf
                                                                    <input type="hidden" name="slab_id" value="{{ $achieved['slab_id'] }}">
                                                                    
                                                                    @if(isset($achieved['reward_options']) && count($achieved['reward_options']) > 0)
                                                                        <h6 class="fw-bold mb-3 text-white">Select your preferred reward:</h6>
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
                                            @endforeach
                                        @endif
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
                                                {{ $item->details }}
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
@endpush