<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InAppNotification;
use App\Services\FirebasePushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    public function index()
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

        // Get recent broadcasts
        $history = InAppNotification::where('type', 'admin_broadcast')
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
            'history' => $history,
        ]);
    }

    /**
     * Send broadcast notification
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
