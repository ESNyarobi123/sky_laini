<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InAppNotification;
use App\Services\FirebasePushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PushNotificationController extends Controller
{
    protected FirebasePushNotificationService $pushService;

    public function __construct(FirebasePushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Show the push notifications page
     */
    public function index(Request $request)
    {
        // Get FCM stats
        $totalUsers = User::count();
        $usersWithToken = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->count();

        $androidUsers = User::where('device_type', 'android')
            ->whereNotNull('fcm_token')
            ->count();

        $iosUsers = User::where('device_type', 'ios')
            ->whereNotNull('fcm_token')
            ->count();

        $agentsWithToken = User::whereHas('agent', fn($q) => $q->where('is_verified', true))
            ->whereNotNull('fcm_token')
            ->count();

        $customersWithToken = User::whereHas('customer')
            ->whereNotNull('fcm_token')
            ->count();

        $recentTokens = User::whereNotNull('fcm_token')
            ->where('fcm_token_updated_at', '>=', now()->subDays(7))
            ->count();

        $stats = [
            'total_users' => $totalUsers,
            'users_with_token' => $usersWithToken,
            'token_coverage' => $totalUsers > 0 
                ? round(($usersWithToken / $totalUsers) * 100, 1) . '%' 
                : '0%',
            'by_device' => [
                'android' => $androidUsers,
                'ios' => $iosUsers,
                'unknown' => $usersWithToken - $androidUsers - $iosUsers,
            ],
            'by_role' => [
                'agents' => $agentsWithToken,
                'customers' => $customersWithToken,
            ],
            'recent_registrations_7d' => $recentTokens,
        ];

        // Get all notification types for filters
        $notificationTypes = InAppNotification::select('type')
            ->distinct()
            ->pluck('type')
            ->toArray();

        // Build the history query with filters
        $typeFilter = $request->input('type');
        $search = $request->input('search');

        // Get grouped broadcasts (admin_broadcast type)
        $recentBroadcasts = InAppNotification::where('type', 'admin_broadcast')
            ->selectRaw('title, message, MIN(created_at) as sent_at, COUNT(*) as recipients')
            ->groupBy('title', 'message')
            ->orderBy('sent_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($b) => [
                'title' => $b->title,
                'message' => $b->message,
                'sent_at' => $b->sent_at,
                'recipients' => $b->recipients,
            ])
            ->toArray();

        return view('admin.notifications.push', [
            'stats' => $stats,
            'history' => $recentBroadcasts,
            'notificationTypes' => $notificationTypes,
        ]);
    }

    /**
     * Show all notifications history with full management
     */
    public function history(Request $request)
    {
        $query = InAppNotification::with('user:id,name,email,role')
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by source (admin vs system)
        if ($request->filled('source')) {
            if ($request->source === 'admin') {
                $query->whereIn('type', ['admin_broadcast', 'admin_message']);
            } else {
                $query->whereNotIn('type', ['admin_broadcast', 'admin_message']);
            }
        }

        // Search by title or message
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get distinct notification types for filter dropdown
        $notificationTypes = InAppNotification::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        // Get summary stats
        $totalNotifications = InAppNotification::count();
        $adminNotifications = InAppNotification::whereIn('type', ['admin_broadcast', 'admin_message'])->count();
        $systemNotifications = InAppNotification::whereNotIn('type', ['admin_broadcast', 'admin_message'])->count();
        $todayNotifications = InAppNotification::whereDate('created_at', today())->count();

        // Paginate results
        $notifications = $query->paginate(20)->withQueryString();

        return view('admin.notifications.history', [
            'notifications' => $notifications,
            'notificationTypes' => $notificationTypes,
            'stats' => [
                'total' => $totalNotifications,
                'admin' => $adminNotifications,
                'system' => $systemNotifications,
                'today' => $todayNotifications,
            ],
            'filters' => [
                'type' => $request->type,
                'source' => $request->source,
                'search' => $request->search,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
        ]);
    }

    /**
     * Show single notification details
     */
    public function show(InAppNotification $notification)
    {
        $notification->load('user:id,name,email,role', 'lineRequest');
        
        return response()->json([
            'success' => true,
            'notification' => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'icon' => $notification->icon,
                'color' => $notification->color,
                'data' => $notification->data,
                'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                'user' => $notification->user ? [
                    'id' => $notification->user->id,
                    'name' => $notification->user->name,
                    'email' => $notification->user->email,
                    'role' => $notification->user->role,
                ] : null,
                'line_request_id' => $notification->line_request_id,
            ],
        ]);
    }

    /**
     * Update a notification
     */
    public function update(Request $request, InAppNotification $notification)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        $notification->update([
            'title' => $request->title,
            'message' => $request->message,
        ]);

        Log::info('Notification updated by admin', [
            'admin_id' => auth()->id(),
            'notification_id' => $notification->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification updated successfully',
            'notification' => $notification,
        ]);
    }

    /**
     * Delete a single notification
     */
    public function destroy(InAppNotification $notification)
    {
        $notificationId = $notification->id;
        $notification->delete();

        Log::info('Notification deleted by admin', [
            'admin_id' => auth()->id(),
            'notification_id' => $notificationId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
        ]);
    }

    /**
     * Delete multiple notifications
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:in_app_notifications,id',
        ]);

        $count = InAppNotification::whereIn('id', $request->ids)->delete();

        Log::info('Bulk notifications deleted by admin', [
            'admin_id' => auth()->id(),
            'count' => $count,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications deleted successfully",
            'deleted_count' => $count,
        ]);
    }

    /**
     * Delete all notifications by type
     */
    public function deleteByType(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
        ]);

        $count = InAppNotification::where('type', $request->type)->delete();

        Log::info('Notifications deleted by type', [
            'admin_id' => auth()->id(),
            'type' => $request->type,
            'count' => $count,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} {$request->type} notifications deleted",
            'deleted_count' => $count,
        ]);
    }

    /**
     * Resend a notification to user
     */
    public function resend(InAppNotification $notification)
    {
        // Create a new notification with same content
        $newNotification = InAppNotification::create([
            'user_id' => $notification->user_id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'icon' => $notification->icon,
            'color' => $notification->color,
            'data' => array_merge($notification->data ?? [], [
                'resent_at' => now()->toIso8601String(),
                'resent_by' => auth()->id(),
            ]),
        ]);

        // Try to send push notification if user has FCM token
        $user = $notification->user;
        if ($user && $user->fcm_token) {
            $this->pushService->sendToUser(
                $user,
                $notification->title,
                $notification->message,
                $notification->data ?? []
            );
        }

        Log::info('Notification resent by admin', [
            'admin_id' => auth()->id(),
            'original_notification_id' => $notification->id,
            'new_notification_id' => $newNotification->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification resent successfully',
        ]);
    }

    /**
     * Send broadcast notification (Web Session Auth)
     */
    public function broadcast(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'target' => 'nullable|string|in:all,agents,customers',
        ]);

        $title = $request->input('title');
        $body = $request->input('body');
        $target = $request->input('target', 'all');

        $data = [
            'type' => 'admin_broadcast',
            'sent_at' => now()->toIso8601String(),
            'sent_by' => auth()->id(),
        ];

        // Send push notifications
        $result = match ($target) {
            'agents' => $this->pushService->sendToAllAgents($title, $body, $data),
            'customers' => $this->pushService->sendToAllCustomers($title, $body, $data),
            default => $this->pushService->sendToAllUsers($title, $body, $data),
        };

        // Create in-app notifications
        $users = match ($target) {
            'agents' => User::whereHas('agent', fn($q) => $q->where('is_verified', true))->get(),
            'customers' => User::whereHas('customer')->get(),
            default => User::all(),
        };

        foreach ($users as $user) {
            InAppNotification::create([
                'user_id' => $user->id,
                'type' => 'admin_broadcast',
                'title' => $title,
                'message' => $body,
                'icon' => 'campaign',
                'color' => '#6366F1',
                'data' => $data,
            ]);
        }

        Log::info('Admin broadcast sent from web', [
            'admin_id' => auth()->id(),
            'target' => $target,
            'success' => $result['success'] ?? 0,
            'failure' => $result['failure'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Broadcast notification sent',
            'result' => [
                'target' => $target,
                'push_success' => $result['success'] ?? 0,
                'push_failure' => $result['failure'] ?? 0,
                'in_app_created' => $users->count(),
            ],
        ]);
    }
}
