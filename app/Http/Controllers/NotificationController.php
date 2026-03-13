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
                        elseif (str_contains(strtolower($msg), 'ready for your approval') && $order->status !== 'processing') $needsAction = false;
                        elseif (str_contains(strtolower($msg), 'confirm order upon delivery') && $order->status === 'delivered') $needsAction = false;
                    }
                } elseif (str_starts_with($code, 'DO-')) {
                    $order = \App\Models\DistributorOrder::where('order_code', $code)->first();
                    if ($order) {
                        $orderExists = true;
                        if (str_contains(strtolower($msg), 'ready for your approval') && !in_array($order->status, ['pending', 'processing'])) $needsAction = false;
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
        $notifications->getCollection()->transform(function ($notification) {
            $notification->is_pending_action = self::checkActionStatus($notification);
            return $notification;
        });
        return view('notifications.index', compact('notifications'));
    }

    public static function checkActionStatus($notification)
    {
        $data = $notification->data;
        if (!isset($data['order_code'])) {
            return false;
        }

        $code = $data['order_code'];
        $msg = $data['message'] ?? '';

        if (str_starts_with($code, 'RO-')) {
            $order = \App\Models\RetailerOrder::where('order_code', $code)->first();
            if ($order) {
                if (str_contains(strtolower($msg), 'assigned to you') && $order->status !== 'pending') return false;
                if (str_contains(strtolower($msg), 'ready for your approval') && $order->status !== 'processing') return false;
                if (str_contains(strtolower($msg), 'confirm order upon delivery') && $order->status === 'delivered') return false;
                return true;
            }
        } elseif (str_starts_with($code, 'DO-')) {
            $order = \App\Models\DistributorOrder::where('order_code', $code)->first();
            if ($order) {
                if (str_contains(strtolower($msg), 'ready for your approval') && !in_array($order->status, ['pending', 'processing'])) return false;
                return true;
            }
        }

        return false;
    }
    public function markAsRead($id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        $user->unreadNotifications()->where('id', $id)->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        $user->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function fetchLatest()
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $unreadNotifications = $user->unreadNotifications()->latest()->take(5)->get();
        $unreadCount = $user->unreadNotifications()->count();

        $formatted = $unreadNotifications->map(function ($notification) {
            $is_pending = self::checkActionStatus($notification);
            $actionUrl = $notification->data['action_url'] ?? '#';
            $orderCode = $notification->data['order_code'] ?? '';
            
            if ($actionUrl !== '#' && !empty($orderCode)) {
                $separator = parse_url($actionUrl, PHP_URL_QUERY) ? '&' : '?';
                $actionUrl .= $separator . 'highlight=' . urlencode($orderCode);
            }

            return [
                'id' => $notification->id,
                'message' => $notification->data['message'] ?? 'Notification',
                'action_url' => $actionUrl,
                'created_at_human' => $notification->created_at->diffForHumans(),
                'is_pending' => $is_pending,
            ];
        });

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $formatted
        ]);
    }
}
