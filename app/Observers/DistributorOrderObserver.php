<?php

namespace App\Observers;

use App\Models\DistributorOrder;
use App\Traits\HandlesNotifications;

class DistributorOrderObserver
{
    use HandlesNotifications;

    /**
     * Handle the DistributorOrder "updated" event.
     */
    public function updated(DistributorOrder $distributorOrder): void
    {
        if ($distributorOrder->isDirty('status')) {
            $this->clearOrderNotifications($distributorOrder->id, 'distributor_order');
        }
    }

    /**
     * Handle the DistributorOrder "deleted" event.
     */
    public function deleted(DistributorOrder $distributorOrder): void
    {
        $this->clearOrderNotifications($distributorOrder->id, 'distributor_order');
    }
}
