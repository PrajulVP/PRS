<?php

namespace App\View\Components;

use Illuminate\View\Component;

class StatusBadge extends Component
{
    public $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    public function render()
    {
        $badgeClass = 'badge-primary'; // Default
        switch (strtolower($this->status)) {
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

        return view('components.status-badge-view', compact('badgeClass'));
    }
}
