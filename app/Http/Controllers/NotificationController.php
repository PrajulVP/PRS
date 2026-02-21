<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Auto-resolve or delete obsolete unread notifications
        $notificationsList = auth()->user()->notifications; // checking all notifications to clean up database
        foreach ($notificationsList as $notification) {
            $data = $notification->data;
            if (isset($data['order_code'])) {
                $code = $data['order_code'];
                $msg = $data['message'] ?? '';
                $needsAction = true;
                $orderExists = false;

                if (str_starts_with($code, 'RO-')) {
                    $order = \App\Models\RetailerOrder::where('order_code', $code)->first();
                    if ($order) {
                        $orderExists = true;
                        if (str_contains(strtolower($msg), 'assigned to you') && $order->status !== 'pending') $needsAction = false;
                        elseif (str_contains(strtolower($msg), 'ready for your approval') && $order->status !== 'accepted_by_fieldstaff') $needsAction = false;
                        elseif (str_contains(strtolower($msg), 'confirm order upon delivery') && in_array($order->status, ['delivered', 'confirmed'])) $needsAction = false;
                    }
                } elseif (str_starts_with($code, 'DO-')) {
                    $order = \App\Models\DistributorOrder::where('order_code', $code)->first();
                    if ($order) {
                        $orderExists = true;
                        if (str_contains(strtolower($msg), 'ready for your approval') && !in_array($order->status, ['pending', 'accepted_by_sales_manager'])) $needsAction = false;
                    }
                }

                if (!$orderExists) {
                    $notification->delete(); // Automatically clean up deleted orders
                } elseif (!$needsAction && $notification->unread()) {
                    $notification->markAsRead(); // Mark as read if action is done
                }
            }
        }

        $notifications = auth()->user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }
}
