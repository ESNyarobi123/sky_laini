<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebasePushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FcmTokenController extends Controller
{
    protected FirebasePushNotificationService $pushService;

    public function __construct(FirebasePushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Register/Update FCM token for the authenticated user
     *
     * POST /api/fcm/token
     * {
     *     "token": "fcm_device_token_here",
     *     "device_type": "android" // or "ios"
     * }
     */
    public function registerToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|min:20',
            'device_type' => 'nullable|string|in:android,ios',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $token = $request->input('token');
        $deviceType = $request->input('device_type', 'android');

        // Check if token is already used by another user
        $existingUser = User::where('fcm_token', $token)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            // Remove token from other user (device changed hands)
            $existingUser->update([
                'fcm_token' => null,
                'device_type' => null,
                'fcm_token_updated_at' => null,
            ]);
        }

        // Update current user's token
        $user->update([
            'fcm_token' => $token,
            'device_type' => $deviceType,
            'fcm_token_updated_at' => now(),
        ]);

        Log::info('FCM token registered', [
            'user_id' => $user->id,
            'device_type' => $deviceType,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token registered successfully',
            'data' => [
                'device_type' => $deviceType,
                'registered_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Remove FCM token for the authenticated user (on logout)
     *
     * DELETE /api/fcm/token
     */
    public function removeToken(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'fcm_token' => null,
            'device_type' => null,
            'fcm_token_updated_at' => null,
        ]);

        Log::info('FCM token removed', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token removed successfully',
        ]);
    }

    /**
     * Check if user has FCM token registered
     *
     * GET /api/fcm/status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'has_token' => !empty($user->fcm_token),
            'device_type' => $user->device_type,
            'registered_at' => $user->fcm_token_updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Test push notification (for debugging)
     *
     * POST /api/fcm/test
     */
    public function testNotification(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->fcm_token) {
            return response()->json([
                'success' => false,
                'message' => 'No FCM token registered for this user',
            ], 400);
        }

        $sent = $this->pushService->sendToUser(
            $user,
            'Test Notification 🔔',
            'Hii ni notification ya majaribio kutoka Sky Laini!',
            [
                'type' => 'test',
                'timestamp' => now()->toIso8601String(),
            ]
        );

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Test notification sent successfully' : 'Failed to send test notification',
        ]);
    }
}
