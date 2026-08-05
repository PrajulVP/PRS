<?php

namespace App\Observers;

use App\Models\FieldStaff;
use App\Models\SalesTarget;

class FieldStaffObserver
{
    /**
     * Handle the FieldStaff "updated" event.
     */
    public function updated(FieldStaff $fieldStaff): void
    {
        if ($fieldStaff->isDirty('monthly_target')) {
            $currentMonth = date('F');
            $currentYear = date('Y');

            // Find or create the sales target for the current month
            $salesTarget = $fieldStaff->salesTargets()->firstOrCreate(
                ['month' => $currentMonth, 'year' => $currentYear],
                ['amount' => $fieldStaff->monthly_target ?? 0, 'achieved_amount' => 0]
            );

            // Update it to the new amount
            $salesTarget->update([
                'amount' => $fieldStaff->monthly_target ?? 0
            ]);
        }
    }
}
