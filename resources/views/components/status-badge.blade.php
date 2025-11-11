@props(['status'])

@php
    $badgeClass = 'badge-primary'; // Default
    switch (strtolower($status)) {
        case 'pending':
            $badgeClass = 'badge-warning';
            break;
        case 'assigned_to_distributor':
            $badgeClass = 'badge-info';
            break;
        case 'assigned_to_fieldstaff':
            $badgeClass = 'badge-info';
            break;
        case 'out_for_delivery':
            $badgeClass = 'badge-secondary';
            break;
        case 'delivered':
            $badgeClass = 'badge-success';
            break;
        case 'cancelled':
            $badgeClass = 'badge-danger';
            break;
    }
@endphp

<span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
