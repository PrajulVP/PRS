@php
    $hasPending = isset($pendingRedemptions) && count($pendingRedemptions) > 0;
@endphp

@if($hasPending)
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="small text-muted text-uppercase">Date & Time</th>
                <th class="small text-muted text-uppercase">Retailer</th>
                <th class="small text-muted text-uppercase">Brand</th>
                <th class="small text-muted text-uppercase">Reward Claimed</th>
                <th class="small text-muted text-uppercase">Status</th>
                <th class="small text-muted text-uppercase text-end">Action</th>
            </tr>
        </thead>
        <tbody id="activity-table-body">
            @foreach($pendingRedemptions as $pending)
                <tr>
                    <td class="small">{{ \Carbon\Carbon::parse($pending->created_at)->format('d M Y, h:i A') }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $pending->shop_name }}</div>
                        <div class="small text-muted">{{ $pending->owner_name }}</div>
                    </td>
                    <td><span class="badge bg-soft-primary text-primary">{{ $pending->brand }}</span></td>
                    <td>
                        <div class="fw-bold">{{ $pending->selected_reward ?? $pending->gift_name }}</div>
                        <div class="small text-muted">Cost: {{ number_format($pending->threshold, 2) }} Points</div>
                    </td>
                    <td>
                        <span class="fw-bold" style="color: #d97706;"><i class="fa fa-clock-o me-1"></i>Pending</span>
                        <div class="small text-muted" style="font-size:0.7rem;">Action Required</div>
                    </td>
                    <td class="text-end">
                        @if(Auth::user()->hasAnyRole(['superadmin', 'admin']))
                            <form action="{{ route('admin.loyalty-points.mark-reward-given', $pending->retailer_id) }}" method="POST" class="d-inline approve-reward-form">
                                @csrf
                                <input type="hidden" name="redemption_id" value="{{ $pending->redemption_id }}">
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm btn-approve-reward" 
                                        data-shop="{{ $pending->shop_name }}" 
                                        data-owner="{{ $pending->owner_name }}" 
                                        data-reward="{{ $pending->selected_reward ?? $pending->gift_name }}" 
                                        data-brand="{{ $pending->brand }}" 
                                        data-points="{{ number_format($pending->threshold, 2) }}">
                                    <i class="fa fa-check me-1"></i> Approve
                                </button>
                            </form>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 ms-1 shadow-sm btn-view-redemption-modal" data-id="{{ $pending->redemption_id }}">
                            View Details
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
    <div class="text-center py-4 text-muted">
        <i class="fa fa-check-circle fa-2x mb-2 text-success"></i>
        <p class="mb-0">No pending reward claims found.</p>
    </div>
@endif
