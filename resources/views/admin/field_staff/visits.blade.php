@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                    <h4 class="mb-0 fw-bold text-dark"><i class="fa fa-map-marker-alt text-primary me-2"></i> Staff Visits Tracking</h4>
                    <p class="text-muted small mt-1 mb-0">Monitor and filter field staff visits, locations, and purposes.</p>
                </div>
                
                <div class="card-body p-0">
                    <!-- Filters Section -->
                    <div class="border-bottom">
                        <form method="GET" action="{{ route('admin.field-staff.visits') }}" class="px-4 py-4 mb-0 bg-white">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Manager</label>
                                    <select name="manager_id" id="manager_id" class="form-select form-select-sm">
                                        <option value="">All Managers</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ request('manager_id') == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Staff Member</label>
                                    <select name="user_id" id="user_id" class="form-select form-select-sm">
                                        <option value="">All Staff</option>
                                        @foreach($staffUsers as $staff)
                                            <option value="{{ $staff->id }}" data-manager-id="{{ optional($staff->fieldStaff)->sales_manager_id }}" {{ request('user_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Party Type</label>
                                    <select name="party_type" class="form-select form-select-sm">
                                        <option value="">All Types</option>
                                        <option value="retailer" {{ request('party_type') == 'retailer' ? 'selected' : '' }}>Retailer</option>
                                        <option value="distributor" {{ request('party_type') == 'distributor' ? 'selected' : '' }}>Distributor</option>
                                        <option value="other" {{ request('party_type') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Purpose</label>
                                    <select name="purpose_id" class="form-select form-select-sm">
                                        <option value="">All Purposes</option>
                                        @foreach($purposes as $purpose)
                                            <option value="{{ $purpose->id }}" {{ request('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="">All Statuses</option>
                                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-1 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Date Range</label>
                                    <div class="input-group input-group-sm">
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                        <span class="input-group-text">to</span>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-8 text-end">
                                    <a href="{{ route('admin.field-staff.visits') }}" class="btn btn-sm btn-reset-premium me-2"><i class="fa fa-undo"></i> Reset</a>
                                    <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fa fa-filter"></i> Apply Filters</button>
                                    <button type="submit" name="export" value="csv" class="btn btn-sm btn-success"><i class="fa fa-file-excel"></i> Export CSV</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Results Table -->
                    <div class="table-responsive bg-white">
                        <table class="table table-hover table-borderless align-middle mb-0">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="py-3 ps-4 text-secondary fw-bold">Date & Time</th>
                                    <th class="py-3 text-secondary fw-bold">Staff & Manager</th>
                                    <th class="py-3 text-secondary fw-bold">Party Details</th>
                                    <th class="py-3 text-secondary fw-bold">Purpose</th>
                                    <th class="py-3 text-secondary fw-bold">Duration</th>
                                    <th class="py-3 text-secondary fw-bold">Remarks</th>
                                    <th class="py-3 pe-4 text-secondary fw-bold text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visits as $visit)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $visit->start_at ? $visit->start_at->format('d M, Y') : 'N/A' }}</div>
                                        <div class="small text-muted">
                                            {{ $visit->start_at ? $visit->start_at->format('h:i A') : 'N/A' }} 
                                            @if($visit->end_at)
                                                - {{ $visit->end_at->format('h:i A') }}
                                            @elseif($visit->status == 'ongoing')
                                                <span class="badge bg-warning text-dark ms-1">Ongoing</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-size:12px;">
                                                {{ strtoupper(substr(optional($visit->user)->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ optional($visit->user)->name ?? 'Unknown Staff' }}</div>
                                                <div class="small text-muted"><i class="fa fa-user-tie" style="font-size: 10px;"></i> {{ optional(optional(optional($visit->user)->fieldStaff)->salesManager)->user->name ?? 'No Manager' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($visit->party_type == 'retailer' || $visit->party_type == 'distributor')
                                            <span class="badge bg-info text-white mb-1">{{ ucfirst($visit->party_type) }}</span>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 200px;" title="{{ optional($visit->party)->name ?? 'Unknown' }}">
                                                {{ optional($visit->party)->name ?? 'Unknown' }}
                                            </div>
                                        @else
                                            <span class="badge bg-secondary mb-1">Other</span>
                                            <div class="text-muted small">Party ID: {{ $visit->party_id ?? 'N/A' }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border p-2" style="font-size: 0.95rem;">{{ optional($visit->purpose)->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if($visit->start_at && $visit->end_at)
                                            @php
                                                $duration = $visit->start_at->diffInMinutes($visit->end_at);
                                                $hours = floor($duration / 60);
                                                $mins = $duration % 60;
                                            @endphp
                                            <span class="text-dark fw-bold">
                                                {{ $hours > 0 ? $hours . 'h ' : '' }}{{ $mins }}m
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-truncate text-muted" style="max-width: 150px;" title="{{ $visit->remarks }}">
                                            {{ $visit->remarks ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        @php
                                            // Prepare data for modal
                                            $visitData = [
                                                'id' => $visit->id,
                                                'staff_name' => optional($visit->user)->name,
                                                'manager_name' => optional(optional(optional($visit->user)->fieldStaff)->salesManager)->user->name ?? 'N/A',
                                                'date' => $visit->start_at ? $visit->start_at->format('d M, Y') : 'N/A',
                                                'start_time' => $visit->start_at ? $visit->start_at->format('h:i A') : 'N/A',
                                                'end_time' => $visit->end_at ? $visit->end_at->format('h:i A') : 'N/A',
                                                'duration' => (isset($hours) && isset($mins)) ? ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm' : 'N/A',
                                                'party_type' => ucfirst($visit->party_type),
                                                'party_name' => ($visit->party_type == 'retailer' || $visit->party_type == 'distributor') ? (optional($visit->party)->name ?? 'Unknown') : ('Party ID: ' . ($visit->party_id ?? 'N/A')),
                                                'purpose' => optional($visit->purpose)->name ?? 'N/A',
                                                'status' => ucfirst($visit->status),
                                                'remarks' => $visit->remarks ?? 'No remarks provided.',
                                                'location_lat' => $visit->location_lat,
                                                'location_lng' => $visit->location_lng,
                                            ];
                                        @endphp
                                        <button type="button" class="btn btn-sm btn-light border rounded-circle view-visit-btn" data-visit="{{ json_encode($visitData) }}" title="View Details">
                                            <i class="fa fa-eye text-primary"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fa fa-map-marker-alt fs-2 mb-3 text-light"></i>
                                            <h5>No Visits Found</h5>
                                            <p>Try adjusting your filters or date range.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-4 pe-4">
                        {{ $visits->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Visit Details Modal -->
<div class="modal fade" id="visitDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold text-dark"><i class="fa fa-info-circle text-primary me-2"></i> Visit Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-3 pb-4 px-4">
        <div class="row g-3 mb-3">
            <div class="col-6">
                <p class="text-muted small mb-1">Staff Member</p>
                <div class="fw-bold" id="modal_staff_name"></div>
                <div class="small text-muted" id="modal_manager_name"></div>
            </div>
            <div class="col-6">
                <p class="text-muted small mb-1">Date & Duration</p>
                <div class="fw-bold" id="modal_date"></div>
                <div class="small text-muted"><span id="modal_start_time"></span> - <span id="modal_end_time"></span> (<span id="modal_duration"></span>)</div>
            </div>
        </div>
        
        <div class="row g-3 mb-3 p-3 bg-light rounded-3">
            <div class="col-6">
                <p class="text-muted small mb-1">Party</p>
                <div class="fw-bold" id="modal_party_name"></div>
                <span class="badge bg-info mt-1" id="modal_party_type"></span>
            </div>
            <div class="col-6">
                <p class="text-muted small mb-1">Purpose & Status</p>
                <div class="fw-bold" id="modal_purpose"></div>
                <span class="badge bg-secondary mt-1" id="modal_status"></span>
            </div>
        </div>

        <div class="mb-3">
            <p class="text-muted small mb-1">Remarks</p>
            <div class="p-3 border rounded bg-white text-dark" id="modal_remarks" style="min-height: 80px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
    .btn-reset-premium {
        background-color: #334155 !important; /* Bold Dark Slate */
        border: none !important;
        color: #ffffff !important;
        font-weight: bold !important;
        box-shadow: 0 2px 4px rgba(51, 65, 85, 0.3) !important;
        transition: all 0.2s ease;
    }
    .btn-reset-premium:hover {
        background-color: #1e293b !important; /* Even darker on hover */
        color: #ffffff !important;
        box-shadow: 0 4px 8px rgba(51, 65, 85, 0.4) !important;
        transform: translateY(-1px);
    }
    body.dark-only .btn-reset-premium {
        background-color: #475569 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    }
    body.dark-only .btn-reset-premium:hover {
        background-color: #334155 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Dynamic Staff Filtering based on Manager
        $('#manager_id').on('change', function() {
            let managerId = $(this).val();
            
            $('#user_id option').each(function() {
                if ($(this).val() === '') {
                    // Always show "All Staff"
                    $(this).show();
                    return;
                }
                
                let staffManagerId = $(this).data('manager-id');
                
                if (!managerId || staffManagerId == managerId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            
            // Reset staff selection if it's now hidden
            if (managerId) {
                let selectedStaffManager = $('#user_id option:selected').data('manager-id');
                if (selectedStaffManager != managerId && $('#user_id').val() !== '') {
                    $('#user_id').val('');
                }
            }
        });

        // Trigger on load in case a manager is already selected
        if ($('#manager_id').val()) {
            $('#manager_id').trigger('change');
        }

        // View Visit Details Modal
        $('.view-visit-btn').on('click', function() {
            let data = $(this).data('visit');
            
            $('#modal_staff_name').text(data.staff_name);
            $('#modal_manager_name').html('<i class="fa fa-user-tie small"></i> ' + data.manager_name);
            $('#modal_date').text(data.date);
            $('#modal_start_time').text(data.start_time);
            $('#modal_end_time').text(data.end_time);
            $('#modal_duration').text(data.duration);
            
            $('#modal_party_name').text(data.party_name);
            $('#modal_party_type').text(data.party_type);
            $('#modal_purpose').text(data.purpose);
            
            $('#modal_status').text(data.status);
            if(data.status.toLowerCase() === 'ongoing') {
                $('#modal_status').removeClass('bg-secondary bg-success').addClass('bg-warning text-dark');
            } else {
                $('#modal_status').removeClass('bg-secondary bg-warning text-dark').addClass('bg-success');
            }

            $('#modal_remarks').text(data.remarks);

            let modal = new bootstrap.Modal(document.getElementById('visitDetailsModal'));
            modal.show();
        });
    });
</script>
@endpush
