@extends('layouts.admin')

@push('styles')
    <style>
        .table-hover tbody tr {
            transition: transform 0.3s ease, background-color 0.3s ease !important;
        }

        .table-hover tbody tr:hover {
            transform: translateY(-1px) !important;
        }

        .table-responsive table th:first-child,
        .table-responsive table td:first-child {
            padding-left: 1.5rem !important;
        }

        .table-responsive table th:last-child,
        .table-responsive table td:last-child {
            padding-right: 1.5rem !important;
        }

        .table thead th {
            border-radius: 0 !important;
        }
    </style>
@endpush
@section('page-body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="mb-0"><i class="fa fa-bell me-2"></i>All Notifications</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="15%">Status</th>
                                        <th>Message</th>
                                        <th width="20%">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notifications as $notification)
                                        <tr class="{{ $notification->unread() ? 'fw-bold bg-light' : 'text-muted' }}"
                                            style="cursor: pointer;"
                                            onclick="window.location.href='{{ $notification->data['action_url'] ?? '#' }}'">
                                            <td>
                                                @if($notification->unread())
                                                    <span class="badge bg-primary rounded-pill"><i class="fa fa-circle me-1"
                                                            style="font-size: 8px;"></i> Unread</span>
                                                @else
                                                    <span class="badge bg-secondary rounded-pill"><i class="fa fa-check me-1"></i>
                                                        Read</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $notification->data['message'] ?? 'Notification' }}
                                            </td>
                                            <td class="small">
                                                {{ $notification->created_at->format('d M Y, h:i A') }}
                                                <br>
                                                <span class="text-muted"
                                                    style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No notifications found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-top">
                        {{ $notifications->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection