@extends('layouts.admin')

@section('title', 'Leave Requests Approval')

@section('page-body')
<div class="container-fluid">
    <div class="page-title text-start mb-4">
        <div class="row m-0">
          <div class="col-sm-6 p-0">
            <h4 class="mb-0 fw-bold">Leave & Permission Management</h4>
            <p class="text-muted mb-0 small">Manage sick leaves, casual leaves, and short permissions for field personnel.</p>
          </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary">Leave Requests Timeline</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover w-100">
                            <thead>
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Type</th>
                                    <th>Period (Start - End)</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                    <tr>
                                        <td><span class="fw-bold">{{ $leave->user->name }}</span></td>
                                        <td>
                                            @if($leave->type == 'Sick Leave')
                                                <span class="badge badge-light-danger">Sick</span>
                                            @elseif($leave->type == 'Casual Leave')
                                                <span class="badge badge-light-info">Casual</span>
                                            @else
                                                <span class="badge badge-light-warning">Permission</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small fw-bold">{{ $leave->start_date->format('M d, Y') }}</div>
                                            @if($leave->end_date && $leave->end_date != $leave->start_date)
                                                <div class="small text-muted">to {{ $leave->end_date->format('M d, Y') }}</div>
                                            @endif
                                        </td>
                                        <td><div class="small text-truncate" style="max-width: 200px;" title="{{ $leave->reason }}">{{ $leave->reason }}</div></td>
                                        <td>
                                            @if($leave->status == 'pending')
                                                <span class="badge badge-light-warning pulsate">Pending (FS)</span>
                                            @elseif($leave->status == 'manager_approved')
                                                <span class="badge badge-light-primary">Approved by Manager</span>
                                            @elseif($leave->status == 'approved')
                                                <span class="badge badge-light-success">Fully Approved</span>
                                            @else
                                                <span class="badge badge-light-dark">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $user = auth()->user();
                                                $canApprove = false;
                                                if ($user->hasRole('salesmanager') && $leave->status == 'pending') {
                                                    $canApprove = true;
                                                } elseif ($user->hasAnyRole(['admin', 'superadmin']) && ($leave->status == 'manager_approved' || $leave->status == 'pending')) {
                                                    $canApprove = true;
                                                }
                                            @endphp

                                            @if($canApprove)
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <form action="{{ route('admin.field-staff.leaves.status', $leave->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                                             {{ $user->hasRole('salesmanager') ? 'Verify' : 'Final Approve' }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.field-staff.leaves.status', $leave->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="small text-muted">
                                                    @if($leave->status == 'approved')
                                                        Approved by {{ $leave->admin->name ?? 'Admin' }}
                                                    @elseif($leave->status == 'manager_approved')
                                                        Verified by {{ $leave->manager->name ?? 'SM' }}
                                                    @elseif($leave->status == 'rejected')
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
                                        <td colspan="6" class="text-center py-5">No leave requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $leaves->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pulsate { animation: pulsate 2s infinite; }
    @keyframes pulsate { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }
    .modal-content {
        border-radius: 20px !important;
        overflow: hidden !important;
    }
</style>
@endpush
