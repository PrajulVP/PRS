<?php

namespace App\Observers;

use App\Models\RetailerOrder;
use App\Traits\HandlesNotifications;
use App\Traits\CalculatesPrices;
use Illuminate\Support\Facades\Log;

class RetailerOrderObserver
{
    use HandlesNotifications, CalculatesPrices;

    /**
     * Handle the RetailerOrder "updated" event.
     */
    public function updated(RetailerOrder $retailerOrder): void
    {
        // Whenever status changes, clear relevant unread notifications
        if ($retailerOrder->isDirty('status')) {
            $this->clearOrderNotifications($retailerOrder->id, 'retailer_order');
            
            $oldStatus = $retailerOrder->getOriginal('status');
            $newStatus = $retailerOrder->status;
            
            // Statuses where loyalty points are considered "earned"
            $eligibleStatuses = [RetailerOrder::STATUS_APPROVED, RetailerOrder::STATUS_DELIVERED];

            // 1. Award points when moving to an eligible status from an ineligible one
            if (in_array($newStatus, $eligibleStatuses) && !in_array($oldStatus, $eligibleStatuses)) {
                $this->awardPoints($retailerOrder);
            }
            
            // 2. Deduct points when moving AWAY from an eligible status to an ineligible one (e.g. cancelled, rejected)
            if (in_array($oldStatus, $eligibleStatuses) && !in_array($newStatus, $eligibleStatuses)) {
                $this->deductPoints($retailerOrder);
            }
        }
    }

    /**
     * Handle the RetailerOrder "deleted" event.
     */
    public function deleted(RetailerOrder $retailerOrder): void
    {
        $this->clearOrderNotifications($retailerOrder->id, 'retailer_order');
        $this->deductPoints($retailerOrder);
    }

    /**
     * Award loyalty points to the retailer based on order items.
     */
    protected function awardPoints(RetailerOrder $retailerOrder)
    {
        $totalPoints = 0;
        $retailerOrder->loadMissing('items.product');

        foreach ($retailerOrder->items as $item) {
            if ($item->product) {
                $ptr = (float) ($item->product->ptr ?? 0);
                $percentage = (float) $item->product->loyalty_point_percentage;
                
                // Use trait helper for correct base quantity (strips)
                $totalQtyStrips = $this->convertQuantityToStrips($item->product, $item->quantity, $item->unit);

                if ($percentage > 0 && $ptr > 0) {
                    $totalPoints += ($totalQtyStrips * $ptr) * ($percentage / 100);
                }
            }
        }

        if ($totalPoints > 0) {
            // Store the points on the order for history and future deduction
            $retailerOrder->updateQuietly(['loyalty_points_earned' => $totalPoints]);
            
            $retailer = $retailerOrder->retailer;
            if ($retailer) {
                $retailer->increment('loyalty_points', $totalPoints);
                Log::info("Loyalty: Awarded {$totalPoints} points to Retailer ID {$retailer->id} for Order #{$retailerOrder->order_code}");
            }
        }
    }

    /**
     * Deduct loyalty points from the retailer if they were previously awarded.
     */
    protected function deductPoints(RetailerOrder $retailerOrder)
    {
        $pointsToDeduct = (float)($retailerOrder->loyalty_points_earned ?? 0);
        
        if ($pointsToDeduct > 0) {
            $retailer = $retailerOrder->retailer;
            if ($retailer) {
                $retailer->decrement('loyalty_points', $pointsToDeduct);
                // Reset the points on the order so they aren't deducted again
                $retailerOrder->updateQuietly(['loyalty_points_earned' => 0]);
                Log::info("Loyalty: Deducted {$pointsToDeduct} points from Retailer ID {$retailer->id} for Order #{$retailerOrder->order_code} (Status: {$retailerOrder->status})");
            }
        }
    }
}
