<?php

namespace App\Observers;

use App\Models\RetailerOrder;
use App\Traits\HandlesNotifications;

class RetailerOrderObserver
{
    use HandlesNotifications;

    /**
     * Handle the RetailerOrder "updated" event.
     */
    public function updated(RetailerOrder $retailerOrder): void
    {
        // Whenever status changes, clear relevant unread notifications
        if ($retailerOrder->isDirty('status')) {
            $this->clearOrderNotifications($retailerOrder->id, 'retailer_order');
        }
    }

    /**
     * Handle the RetailerOrder "deleted" event.
     */
    public function deleted(RetailerOrder $retailerOrder): void
    {
        $this->clearOrderNotifications($retailerOrder->id, 'retailer_order');
    }
}
