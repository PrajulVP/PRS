@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5><i class="fa fa-bullseye me-2"></i>Field Staff Monthly Targets</h5>
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
                                        <th>Current Target for {{ $monthName }} {{ $yearStr }}</th>
                                        <th>Achieved for {{ $monthName }} {{ $yearStr }}</th>
                                        <th>Update Target Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fieldStaffs as $fs)
                                        @php
                                            $targetRecord = $fs->salesTargets->first();
                                            $targetAmount = $fs->default_target_amount ?? 0;
                                            $achievedAmount = $targetRecord ? $targetRecord->achieved_amount : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $fs->user->name ?? 'N/A' }}</div>
                                                <div class="small text-muted">{{ $fs->user->email ?? '' }}</div>
                                            </td>
                                            <td>{{ $fs->salesManager->user->name ?? 'N/A' }}</td>
                                            <td>₹{{ number_format($targetAmount, 2) }}</td>
                                            <td>₹{{ number_format($achievedAmount, 2) }}</td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">₹</span>
                                                    <input type="number" step="0.01" min="0" name="targets[{{ $fs->id }}]" class="form-control target-input" value="{{ $targetAmount }}">
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($fieldStaffs->count() > 0)
                            <div class="text-end">
                                <button type="button" id="save-targets-btn" class="btn btn-success px-4" style="border-radius: 8px;">
                                    <i class="fa fa-save me-2"></i> Save All Targets
                                </button>
                            </div>
                        @endif
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

        $('#save-targets-btn').click(function() {
            var btn = $(this);
            var originalHtml = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin me-2"></i> Saving...').prop('disabled', true);

            // Important: if there is pagination in DataTables, a regular serialize() will only get the inputs of the current page.
            // We need to get all inputs across all pages. DataTables provides `table.$('input')`.
            
            var data = {
                _token: "{{ csrf_token() }}",
                month: "{{ $month }}",
                targets: {}
            };
            
            table.$('input.target-input').each(function() {
                var name = $(this).attr('name');
                var val = $(this).val();
                
                // name format is targets[ID]
                var matches = name.match(/targets\[(\d+)\]/);
                if (matches) {
                    data.targets[matches[1]] = val;
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
                            title: 'Success',
                            text: response.message,
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
