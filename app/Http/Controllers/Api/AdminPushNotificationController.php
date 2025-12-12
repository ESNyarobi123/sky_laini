<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InAppNotification;
use App\Services\FirebasePushNotificationService;
use App\Services\InAppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminPushNotificationController extends Controller
{
    protected FirebasePushNotificationService $pushService;
    protected InAppNotificationService $inAppService;

    public function __construct(
        FirebasePushNotificationService $pushService,
        InAppNotificationService $inAppService
    ) {
        $this->pushService = $pushService;
        $this->inAppService = $inAppService;
    }

    /**
     * Send push notification to all users (Admin only)
     *
     * POST /api/admin/push/broadcast
     * {
     *     "title": "Important Announcement",
     *     "body": "Your message here...",
     *     "target": "all" // or "agents" or "customers"
     * }
     */
    public function broadcast(Request $request): JsonResponse
    {
        // Check if user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'target' => 'nullable|string|in:all,agents,customers',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $title = $request->input('title');
        $body = $request->input('body');
        $target = $request->input('target', 'all');
        $extraData = $request->input('data', []);

        // Add admin broadcast marker
        $data = array_merge($extraData, [
            'type' => 'admin_broadcast',
            'sent_at' => now()->toIso8601String(),
        ]);

        // Send push notifications
        $result = match ($target) {
            'agents' => $this->pushService->sendToAllAgents($title, $body, $data),
            'customers' => $this->pushService->sendToAllCustomers($title, $body, $data),
            default => $this->pushService->sendToAllUsers($title, $body, $data),
        };

        // Also create in-app notifications
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
                'color' => '#6366F1', // Indigo
                'data' => $data,
            ]);
        }

        Log::info('Admin broadcast sent', [
            'admin_id' => $request->user()->id,
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

    /**
     * Send push notification to specific user (Admin only)
     *
     * POST /api/admin/push/user/{userId}
     */
    public function sendToUser(Request $request, int $userId): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $title = $request->input('title');
        $body = $request->input('body');

        // Send push notification
        $sent = $this->pushService->sendToUser($user, $title, $body, [
            'type' => 'admin_message',
            'sent_at' => now()->toIso8601String(),
        ]);

        // Create in-app notification
        InAppNotification::create([
            'user_id' => $user->id,
            'type' => 'admin_message',
            'title' => $title,
            'message' => $body,
            'icon' => 'admin_panel_settings',
            'color' => '#8B5CF6', // Purple
            'data' => [
                'type' => 'admin_message',
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent to user',
            'result' => [
                'user_id' => $userId,
                'user_name' => $user->name,
                'push_sent' => $sent,
                'in_app_created' => true,
            ],
        ]);
    }

    /**
     * Get statistics about FCM tokens (Admin only)
     *
     * GET /api/admin/push/stats
     */
    public function stats(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

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

        // Recent token registrations (last 7 days)
        $recentTokens = User::whereNotNull('fcm_token')
            ->where('fcm_token_updated_at', '>=', now()->subDays(7))
            ->count();

        return response()->json([
            'success' => true,
            'stats' => [
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
            ],
        ]);
    }

    /**
     * Get list of users with FCM tokens (Admin only)
     *
     * GET /api/admin/push/users
     */
    public function users(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $users = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->select(['id', 'name', 'email', 'phone', 'role', 'device_type', 'fcm_token_updated_at'])
            ->orderBy('fcm_token_updated_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get broadcast history (Admin only)
     * 
     * GET /api/admin/push/history
     */
    public function history(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        // Get admin broadcast notifications (group by title and message)
        $broadcasts = InAppNotification::where('type', 'admin_broadcast')
            ->selectRaw('title, message, MIN(created_at) as sent_at, COUNT(*) as recipients_count')
            ->groupBy('title', 'message')
            ->orderBy('sent_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $broadcasts->map(fn($b) => [
                'title' => $b->title,
                'message' => $b->message,
                'sent_at' => $b->sent_at,
                'recipients' => $b->recipients_count,
            ]),
        ]);
    }
}
