<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InAppNotification;
use App\Services\InAppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InAppNotificationController extends Controller
{
    public function __construct(
        private InAppNotificationService $notificationService
    ) {}

    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 20);
        $type = $request->query('type');
        $unreadOnly = $request->boolean('unread_only', false);

        $query = InAppNotification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        if ($unreadOnly) {
            $query->unread();
        }

        $notifications = $query->paginate($perPage);

        // Transform data
        $notifications->getCollection()->transform(function ($notification) {
            return $this->transformNotification($notification);
        });

        return response()->json([
            'notifications' => $notifications->items(),
            'unread_count' => $this->notificationService->getUnreadCount($request->user()),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user());

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    /**
     * Get a single notification
     */
    public function show(Request $request, $id): JsonResponse
    {
        $notification = InAppNotification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'notification' => $this->transformNotification($notification),
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $notification = InAppNotification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $this->transformNotification($notification->fresh()),
            'unread_count' => $this->notificationService->getUnreadCount($request->user()),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user());

        return response()->json([
            'message' => 'All notifications marked as read',
            'marked_count' => $count,
            'unread_count' => 0,
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $notification = InAppNotification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted',
            'unread_count' => $this->notificationService->getUnreadCount($request->user()),
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function clearRead(Request $request): JsonResponse
    {
        $count = InAppNotification::where('user_id', $request->user()->id)
            ->read()
            ->delete();

        return response()->json([
            'message' => 'Read notifications cleared',
            'deleted_count' => $count,
        ]);
    }

    /**
     * Get notification types/categories summary
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $summary = InAppNotification::where('user_id', $userId)
            ->selectRaw('type, COUNT(*) as total, SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread')
            ->groupBy('type')
            ->get();

        $totalUnread = $this->notificationService->getUnreadCount($request->user());
        $totalNotifications = InAppNotification::where('user_id', $userId)->count();

        return response()->json([
            'summary' => $summary,
            'total_notifications' => $totalNotifications,
            'total_unread' => $totalUnread,
        ]);
    }

    /**
     * Transform notification to API response format
     */
    private function transformNotification(InAppNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'icon' => $notification->icon,
            'color' => $notification->color,
            'data' => $notification->data,
            'line_request_id' => $notification->line_request_id,
            'is_read' => $notification->isRead(),
            'read_at' => $notification->read_at?->toIso8601String(),
            'time_ago' => $notification->time_ago,
            'created_at' => $notification->created_at->toIso8601String(),
        ];
    }
}
