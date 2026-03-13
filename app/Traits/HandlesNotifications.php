<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HandlesNotifications
{
    /**
     * Mark unread order-related notifications as read for specific order and type.
     */
    protected function clearOrderNotifications($orderId, $orderType)
    {
        DB::table('notifications')
            ->whereNull('read_at')
            ->where('data->order_id', (int)$orderId)
            ->where('data->order_type', $orderType)
            ->update(['read_at' => now()]);
    }

    /**
     * Mark unread user-approval notifications as read for specific user.
     */
    protected function clearUserNotifications($userId)
    {
        DB::table('notifications')
            ->whereNull('read_at')
            ->where('type', \App\Notifications\UserApprovalRequired::class)
            ->where('data->user_id', (int)$userId)
            ->update(['read_at' => now()]);
    }

    /**
     * Delete ALL notifications (read or unread) for a specific order.
     */
    protected function deleteOrderNotifications($orderId, $orderType)
    {
        DB::table('notifications')
            ->where('data->order_id', $orderId)
            ->where('data->order_type', $orderType)
            ->delete();
    }

    /**
     * Check if an unread notification already exists for this order and user.
     */
    protected function hasUnreadNotification($user, $orderId, $orderType, $message = null)
    {
        $query = $user->unreadNotifications()
            ->where('data->order_id', (int)$orderId)
            ->where('data->order_type', $orderType);

        if ($message) {
            $query->where('data->message', $message);
        }

        return $query->exists();
    }

    /**
     * Send notification and delete ANY previous unread notifications for this order.
     * This ensures the user only sees the LATEST action required for the order.
     */
    protected function notifyUnique($user, $notification)
    {
        // Extract data for duplicate check
        $data = $notification->toArray($user);
        $orderId = $data['order_id'] ?? null;
        $orderType = $data['order_type'] ?? null;
        $message = $data['message'] ?? null;

        if ($orderId && $orderType) {
            // User requested that earlier notifications of the same order should disappear
            // So we delete existing unread notifications for this order before sending the new one
            DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->whereNull('read_at')
                ->where('data->order_id', (int)$orderId)
                ->where('data->order_type', $orderType)
                ->delete();
        }

        $user->notify($notification);
    }
}
