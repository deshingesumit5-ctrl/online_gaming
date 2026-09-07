<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    /**
     * Get recent notifications and unread count via AJAX.
     */
    public function getRecent(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $unreadCount = $this->notificationService->getUnreadCount($user);
        $notifications = $this->notificationService->getRecentNotifications($user, 20);

        $data = $notifications->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'title' => $item->title,
                'message' => $item->message,
                'link' => $item->link ?: '#',
                'icon' => $item->icon ?: '🔔',
                'is_read' => $item->isRead(),
                'time_ago' => $item->created_at ? $item->created_at->diffForHumans() : 'Recently',
            ];
        });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $data,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $updated = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'marked_count' => $updated,
            'unread_count' => 0,
        ]);
    }

    /**
     * Mark single notification as read.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $success = $this->notificationService->markAsRead($id, $user);

        return response()->json([
            'success' => $success,
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }
}
