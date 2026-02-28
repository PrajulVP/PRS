<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HandlesNotifications
{
    /**
     * Mark unread order-related notifications as read for specific order and type.
     *
     * @param int|string $orderId
     * @param string $orderType
     * @return void
     */
    protected function clearOrderNotifications($orderId, $orderType)
    {
        DB::table('notifications')
            ->whereNull('read_at')
            ->where('data->order_id', $orderId)
            ->where('data->order_type', $orderType)
            ->update(['read_at' => now()]);
    }

    /**
     * Check if an unread notification already exists for this order and user.
     */
    protected function hasUnreadNotification($user, $orderId, $orderType, $message = null)
    {
        $query = $user->unreadNotifications()
            ->where('data->order_id', $orderId)
            ->where('data->order_type', $orderType);

        if ($message) {
            $query->where('data->message', $message);
        }

        return $query->exists();
    }

    /**
     * Send notification only if a similar unread one doesn't exist.
     */
    protected function notifyUnique($user, $notification)
    {
        // We need to extract data from the notification instance to check
        $data = $notification->toArray($user);
        $orderId = $data['order_id'] ?? null;
        $orderType = $data['order_type'] ?? null;
        $message = $data['message'] ?? null;

        if ($orderId && $orderType) {
            if ($this->hasUnreadNotification($user, $orderId, $orderType, $message)) {
                return; // Duplicate unread notification already exists
            }
        }

        $user->notify($notification);
    }
}
