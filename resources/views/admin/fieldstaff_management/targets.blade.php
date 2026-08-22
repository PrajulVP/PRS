@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-3 pt-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-bullseye me-2"></i>Field Staff Monthly Targets</h5>
                    @if($fieldStaffs->count() > 0)
                        <button type="button" id="save-targets-btn" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 8px;">
                            <i class="fa fa-save me-2"></i> Save All Targets
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.field-staff.targets') }}" method="GET" class="row g-3 mb-4 align-items-end" id="filter-form">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Month</label>
                            <input type="month" name="month" class="form-control" value="{{ $month }}" style="border-radius: 8px;">
                        </div>
                        @if(Auth::user()->hasAnyRole(['admin', 'superadmin']))
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Sales Manager</label>
                            <select name="sales_manager_id" class="form-select" style="border-radius: 8px;">
                                <option value="">All Sales Managers</option>
                                @foreach($salesManagers as $manager)
                                    <option value="{{ $manager->id }}" {{ request('sales_manager_id') == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->user->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100" style="height: 38px; border-radius: 8px;"><i class="fa fa-filter me-1"></i> Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.field-staff.targets') }}" class="btn btn-secondary w-100" style="height: 38px; border-radius: 8px;"><i class="fa fa-refresh me-1"></i> Reset</a>
                        </div>
                    </form>

                    <form id="targets-form">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <div class="table-responsive mb-4">
                            <table class="table table-striped table-hover" id="targets-table">
                                <thead>
                                    <tr>
                                        <th>Field Staff</th>
                                        <th>Sales Manager</th>
                                        @foreach($brands as $brand)
                                            <th>{{ $brand }} Target <br><small class="text-muted">Achieved / Target</small></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fieldStaffs as $fs)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $fs->user->name ?? 'N/A' }}</div>
                                                <div class="small text-muted">{{ $fs->user->email ?? '' }}</div>
                                            </td>
                                            <td>{{ $fs->salesManager->user->name ?? 'N/A' }}</td>
                                            @foreach($brands as $brand)
                                                @php
                                                    $targetRecord = $fs->salesTargets->where('brand', $brand)->first();
                                                    $targetAmount = $targetRecord ? $targetRecord->amount : 0;
                                                    // Get dynamic achieved amount from the query result
                                                    $achievedAmount = 0;
                                                    if (isset($achievedData[$fs->id])) {
                                                        $brandAchieved = collect($achievedData[$fs->id])->where('brand', $brand)->first();
                                                        if ($brandAchieved) {
                                                            $achievedAmount = $brandAchieved->total_achieved;
                                                        }
                                                    }
                                                @endphp
                                                <td>
                                                    <div class="d-flex flex-column gap-2">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="badge bg-{{ $achievedAmount >= $targetAmount && $targetAmount > 0 ? 'success' : 'secondary' }}">
                                                                Achieved: ₹{{ number_format($achievedAmount, 2) }}
                                                            </span>
                                                        </div>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">₹</span>
                                                            <input type="number" step="0.01" min="0" name="targets[{{ $fs->id }}][{{ $brand }}]" class="form-control target-input" value="{{ $targetAmount }}">
                                                        </div>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Save button moved to header -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    #targets-table td {
        vertical-align: middle;
    }
    
    /* Fix for SweetAlert2 Icon Box Sizing Conflict */
    .swal2-icon {
        box-sizing: content-box !important;
    }
    .swal2-icon-content {
        box-sizing: content-box !important;
    }
    
    /* Premium Dirty State for Modified Inputs */
    .input-dirty {
        border-color: #f39c12 !important;
        background-color: #fffdf7 !important;
        box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.15) !important;
    }
    .badge-dirty {
        background-color: #f39c12 !important;
        color: white !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var table = $('#targets-table').DataTable({
            "pageLength": 50,
            "order": [[0, "asc"]],
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search..."
            }
        });

        // Track dirty state
        $('#targets-table').on('input', '.target-input', function() {
            var input = $(this);
            var defaultValue = input.prop('defaultValue');
            var currentValue = input.val();
            
            if (currentValue !== defaultValue) {
                input.addClass('input-dirty');
                $('#save-targets-btn').removeClass('btn-primary').addClass('btn-warning text-dark').html('<i class="fa fa-save me-2"></i> Save Changes');
            } else {
                input.removeClass('input-dirty');
                if ($('.input-dirty').length === 0) {
                    $('#save-targets-btn').removeClass('btn-warning text-dark').addClass('btn-primary').html('<i class="fa fa-save me-2"></i> Save All Targets');
                }
            }
        });

        $('#save-targets-btn').click(function() {
            var btn = $(this);
            var originalHtml = btn.html();
            
            btn.html('<i class="fa fa-spinner fa-spin me-2"></i> Saving...').prop('disabled', true);
            
            var data = {
                _token: "{{ csrf_token() }}",
                month: "{{ $month }}",
                targets: {}
            };
            
            // Get all inputs from datatable (including paginated pages)
            table.$('input.target-input').each(function() {
                var name = $(this).attr('name');
                var val = $(this).val();
                
                // name format is targets[ID][BRAND]
                var matches = name.match(/targets\[(\d+)\]\[([^\]]+)\]/);
                if (matches) {
                    var fsId = matches[1];
                    var brand = matches[2];
                    if (!data.targets[fsId]) {
                        data.targets[fsId] = {};
                    }
                    data.targets[fsId][brand] = val;
                }
            });

            $.ajax({
                url: "{{ route('admin.field-staff.targets.save') }}",
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: 'All targets have been updated successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to save targets.', 'error');
                        btn.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'An error occurred while saving targets.', 'error');
                    btn.html(originalHtml).prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
