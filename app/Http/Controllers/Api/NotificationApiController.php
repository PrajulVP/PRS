<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Get notifications for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer"), description="Page number for pagination"),
     *     @OA\Response(
     *         response=200,
     *         description="List of notifications",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="unread_count", type="integer"),
     *             @OA\Property(property="notifications", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $unreadCount = $user->unreadNotifications()->count();
        $notifications = $user->notifications()->latest()->paginate(20);

        $formatted = collect($notifications->items())->map(function ($notification) {
            $is_pending = NotificationController::checkActionStatus($notification);
            return [
                'id' => $notification->id,
                'message' => $notification->data['message'] ?? 'Notification',
                'action_url' => $notification->data['action_url'] ?? '#',
                'order_code' => $notification->data['order_code'] ?? null,
                'is_pending_action' => $is_pending,
                'read_at' => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : null,
                'created_at_human' => $notification->created_at->diffForHumans(),
                'created_at' => $notification->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $formatted,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/{id}/read",
     *     summary="Mark a notification as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Marked as read")
     * )
     */
    public function markAsRead($id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        $user->unreadNotifications()->where('id', $id)->each(function($n) { $n->markAsRead(); });
        return response()->json(['success' => true]);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/read-all",
     *     summary="Mark all notifications as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="All marked as read")
     * )
     */
    public function markAllRead()
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        $user->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}
