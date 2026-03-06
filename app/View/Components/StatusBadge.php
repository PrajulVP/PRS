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
            case 'processing':
                $badgeClass = 'badge-info';
                break;
            case 'accepted':
                $badgeClass = 'badge-primary';
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
