@extends('layouts.admin')

@section('title', 'Expense Claims Approval')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Expense Claims Management</h4>
            <p class="text-muted mb-0 small">Review and approve field staff TA/DA and miscellaneous expenses.</p>
          </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">Pending Approvals</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100" id="expenseTable">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Distance (KM)</th>
                                    <th class="text-end">Amount</th>
                                    <th>Bill</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $expense->user->name }}</div>
                                            <div class="small text-muted">{{ $expense->is_outstation ? 'Outstation' : 'HQ' }}</div>
                                        </td>
                                        <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                        <td><span class="badge badge-light-secondary">{{ $expense->type }}</span></td>
                                        <td>{{ $expense->distance_km ?? 'N/A' }}</td>
                                        <td class="text-end fw-bold text-primary">₹{{ number_format($expense->amount, 2) }}</td>
                                        <td>
                                            @if($expense->bill_path)
                                                <a href="{{ asset('storage/' . $expense->bill_path) }}" target="_blank" class="btn btn-xs btn-outline-info">View Bill</a>
                                            @else
                                                <span class="text-muted small">No Bill</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($expense->status == 'pending')
                                                <span class="badge badge-light-warning">Pending (FS)</span>
                                            @elseif($expense->status == 'manager_approved')
                                                <span class="badge badge-light-primary">Approved by Manager</span>
                                            @elseif($expense->status == 'approved')
                                                <span class="badge badge-light-success">Fully Approved</span>
                                            @else
                                                <span class="badge badge-light-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $user = auth()->user();
                                                $canApprove = false;
                                                if ($user->hasRole('salesmanager') && $expense->status == 'pending') {
                                                    $canApprove = true;
                                                } elseif ($user->hasAnyRole(['admin', 'superadmin']) && ($expense->status == 'manager_approved' || $expense->status == 'pending')) {
                                                    $canApprove = true;
                                                }
                                            @endphp

                                            @if($canApprove)
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <form action="{{ route('admin.field-staff.expenses.status', $expense->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            {{ $user->hasRole('salesmanager') ? 'Verify' : 'Final Approve' }}
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="rejectExpense({{ $expense->id }})">Reject</button>
                                                </div>
                                            @else
                                                <span class="small text-muted">
                                                    @if($expense->status == 'approved')
                                                        Approved by {{ $expense->admin->name ?? 'Admin' }}
                                                    @elseif($expense->status == 'manager_approved')
                                                        Verified by {{ $expense->manager->name ?? 'SM' }}
                                                    @elseif($expense->status == 'rejected')
                                                        Rejected
                                                    @else
                                                        -
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">No expense claims found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $expenses->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="rejectForm" method="POST">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Expense Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function rejectExpense(id) {
        let url = "{{ route('admin.field-staff.expenses.status', ':id') }}";
        $('#rejectForm').attr('action', url.replace(':id', id));
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }
</script>
@endpush
