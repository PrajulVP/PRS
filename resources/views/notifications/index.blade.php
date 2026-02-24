@extends('layouts.admin')

@push('styles')
    <style>
        .notification-row {
            cursor: pointer;
            transition: all 0.3s ease !important;
        }

        .notification-row:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
        }

        .notification-read {
            opacity: 0.6;
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
                <div class="card shadow-sm border-0">
                    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-bell me-2 text-primary"></i>All Notifications</h5>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="mark-all-read-checkbox">
                                <label class="form-check-label small fw-bold text-primary cursor-pointer"
                                    for="mark-all-read-checkbox">
                                    Mark all as read
                                </label>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light text-muted small text-uppercase fw-bold">
                                    <tr>
                                        <th width="75%">Message</th>
                                        <th width="25%">Received</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notifications as $notification)
                                        @php
                                            $actionUrl = $notification->data['action_url'] ?? '#';
                                            $orderCode = $notification->data['order_code'] ?? '';
                                            if ($actionUrl !== '#' && !empty($orderCode)) {
                                                $separator = parse_url($actionUrl, PHP_URL_QUERY) ? '&' : '?';
                                                $actionUrl .= $separator . 'highlight=' . urlencode($orderCode);
                                            }
                                            $isUnread = $notification->unread();
                                        @endphp
                                        <tr class="notification-row {{ $isUnread ? 'fw-bold border-start border-primary border-4' : 'notification-read' }}"
                                            onclick="window.handleNotificationClick('{{ $notification->id }}', '{{ $actionUrl }}', this)">
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <div class="mb-0 text-dark">
                                                            {{ $notification->data['message'] ?? 'Notification' }}</div>
                                                        @if($orderCode)
                                                            <span
                                                                class="badge bg-light text-primary border border-primary-subtle small mt-1">
                                                                {{ $orderCode }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="small py-3">
                                                <div class="text-dark">{{ $notification->created_at->format('d M Y, h:i A') }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fa fa-bell-slash fa-3x mb-3 opacity-25"></i>
                                                    <p class="mb-0">No notifications found.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($notifications->hasPages())
                        <div class="card-footer border-top bg-white py-3">
                            {{ $notifications->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.handleNotificationClick = function (id, url, rowElement) {
            // First, check if it's already read by checking the class
            if (rowElement.classList.contains('notification-read')) {
                // Already read, just navigate
                if (url !== '#') window.location.href = url;
                return;
            }

            // Mark as read via AJAX
            let readUrl = "{{ route('notifications.read', ':id') }}".replace(':id', id);
            fetch(readUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).finally(function () {
                if (url !== '#') window.location.href = url;
                else {
                    // Update UI if no URL
                    rowElement.classList.add('notification-read');
                    rowElement.classList.remove('fw-bold', 'border-start', 'border-primary', 'border-4');
                    
                    // Update header badge count
                    const headerBadge = document.querySelector('.notification-box .badge');
                    if(headerBadge) {
                        let count = parseInt(headerBadge.innerText);
                        if(count > 1) {
                            headerBadge.innerText = count - 1;
                        } else {
                            headerBadge.remove();
                            // Also hide mark all as read since this was the last one
                            document.getElementById('mark-all-read-checkbox')?.closest('.form-check')?.parentElement?.remove();
                        }
                    }
                }
            });
        };

        document.getElementById('mark-all-read-checkbox')?.addEventListener('change', function() {
            if (!this.checked) return;
            
            let checkbox = this;
            checkbox.disabled = true;
            
            fetch("{{ route('notifications.markAllRead') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update all rows dynamically
                    document.querySelectorAll('.notification-row').forEach(row => {
                        row.classList.add('notification-read');
                        row.classList.remove('fw-bold', 'border-start', 'border-primary', 'border-4');
                    });
                    
                    // Hide the "Mark all read" section
                    checkbox.closest('.form-check').parentElement.remove();
                    
                    // Update header badge if exists
                    const headerBadge = document.querySelector('.notification-box .badge');
                    if(headerBadge) headerBadge.remove();

                    showToast('success', 'All notifications marked as read');
                } else {
                    showToast('error', 'Failed to mark notifications as read');
                    checkbox.disabled = false;
                    checkbox.checked = false;
                }
            }).catch(error => {
                console.error('Error:', error);
                showToast('error', 'An error occurred');
                checkbox.disabled = false;
                checkbox.checked = false;
            });
        });
    </script>
@endpush