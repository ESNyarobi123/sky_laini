<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\InAppNotification;
use App\Models\LineRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class InAppNotificationService
{
    protected ?FirebasePushNotificationService $pushService = null;

    public function __construct()
    {
        // Initialize push service if available
        try {
            $this->pushService = app(FirebasePushNotificationService::class);
        } catch (\Exception $e) {
            Log::warning('Firebase Push Service not available', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send notification to a user (In-App + Push)
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?LineRequest $lineRequest = null,
        ?array $extraData = null
    ): InAppNotification {
        $config = InAppNotification::getTypeConfig($type);

        $data = $extraData ?? [];
        if ($lineRequest) {
            $data['line_request_id'] = $lineRequest->id;
            $data['request_number'] = $lineRequest->request_number;
        }

        // Create in-app notification
        $notification = InAppNotification::create([
            'user_id' => $user->id,
            'line_request_id' => $lineRequest?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $config['icon'],
            'color' => $config['color'],
            'data' => $data,
        ]);

        Log::info('In-app notification sent', [
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
        ]);

        // Also send push notification if user has FCM token
        if ($this->pushService && $user->fcm_token) {
            try {
                $pushData = array_merge($data, [
                    'type' => $type,
                    'notification_id' => (string) $notification->id,
                ]);
                
                // Convert any non-string values to strings for FCM
                $pushData = array_map(function ($value) {
                    return is_array($value) ? json_encode($value) : (string) $value;
                }, $pushData);

                $this->pushService->sendToUser($user, $title, $message, $pushData);
            } catch (\Exception $e) {
                Log::warning('Failed to send push notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Send notification for booking events (without LineRequest)
     */
    public function sendBookingNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?array $extraData = null
    ): InAppNotification {
        $config = InAppNotification::getTypeConfig($type);

        $data = $extraData ?? [];

        // Create in-app notification
        $notification = InAppNotification::create([
            'user_id' => $user->id,
            'line_request_id' => null,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $config['icon'],
            'color' => $config['color'],
            'data' => $data,
        ]);

        Log::info('Booking notification sent', [
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
        ]);

        // Also send push notification if user has FCM token
        if ($this->pushService && $user->fcm_token) {
            try {
                $pushData = array_merge($data, [
                    'type' => $type,
                    'notification_id' => (string) $notification->id,
                ]);
                
                // Convert any non-string values to strings for FCM
                $pushData = array_map(function ($value) {
                    return is_array($value) ? json_encode($value) : (string) $value;
                }, $pushData);

                $this->pushService->sendToUser($user, $title, $message, $pushData);
            } catch (\Exception $e) {
                Log::warning('Failed to send push notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Notify all verified agents about a new line request
     */
    public function notifyAllAgentsNewRequest(LineRequest $lineRequest): void
    {
        $lineType = $lineRequest->line_type->value ?? $lineRequest->line_type;
        $networkName = ucfirst($lineType);

        // Get all verified agents
        $agents = Agent::where('is_verified', true)
            ->with('user')
            ->get();

        foreach ($agents as $agent) {
            if ($agent->user) {
                $this->send(
                    $agent->user,
                    InAppNotification::TYPE_NEW_REQUEST,
                    'Ombi Jipya la Laini! 📱',
                    "Mteja anahitaji laini ya {$networkName}. Ombi #{$lineRequest->request_number}",
                    $lineRequest,
                    [
                        'line_type' => $lineType,
                        'customer_address' => $lineRequest->customer_address,
                    ]
                );
            }
        }

        Log::info('All agents notified about new request', [
            'line_request_id' => $lineRequest->id,
            'agents_count' => $agents->count(),
        ]);
    }

    /**
     * Notify customer that their order has been created
     */
    public function notifyCustomerOrderCreated(LineRequest $lineRequest): void
    {
        $customer = $lineRequest->customer;
        if (!$customer || !$customer->user) {
            return;
        }

        $lineType = $lineRequest->line_type->value ?? $lineRequest->line_type;
        $networkName = ucfirst($lineType);

        $this->send(
            $customer->user,
            InAppNotification::TYPE_ORDER_CREATED,
            'Ombi Limesajiliwa! ✅',
            "Ombi lako la laini ya {$networkName} #{$lineRequest->request_number} limesajiliwa. Subiri agent akubali.",
            $lineRequest
        );
    }

    /**
     * Notify customer that an agent has accepted their request
     */
    public function notifyCustomerAgentAccepted(LineRequest $lineRequest): void
    {
        $customer = $lineRequest->customer;
        $agent = $lineRequest->agent;

        if (!$customer || !$customer->user || !$agent) {
            return;
        }

        $agentName = $agent->user?->name ?? 'Agent';

        $this->send(
            $customer->user,
            InAppNotification::TYPE_AGENT_ACCEPTED,
            'Agent Amekubali Ombi! 🎉',
            "{$agentName} amekubali ombi lako #{$lineRequest->request_number}. Tafadhali kamilisha malipo.",
            $lineRequest,
            [
                'agent_id' => $agent->id,
                'agent_name' => $agentName,
            ]
        );
    }

    /**
     * Notify customer about payment pending (USSD sent)
     */
    public function notifyCustomerPaymentPending(LineRequest $lineRequest): void
    {
        $customer = $lineRequest->customer;
        if (!$customer || !$customer->user) {
            return;
        }

        $amount = $lineRequest->service_fee ?? 1000;

        $this->send(
            $customer->user,
            InAppNotification::TYPE_PAYMENT_PENDING,
            'Kamilisha Malipo 💳',
            "Tafadhali kamilisha malipo ya TZS " . number_format($amount) . " kwa ombi #{$lineRequest->request_number}. Angalia simu yako.",
            $lineRequest,
            [
                'amount' => $amount,
            ]
        );
    }

    /**
     * Notify customer that payment was received
     */
    public function notifyCustomerPaymentReceived(LineRequest $lineRequest): void
    {
        $customer = $lineRequest->customer;
        if (!$customer || !$customer->user) {
            return;
        }

        $amount = $lineRequest->service_fee ?? 1000;

        $this->send(
            $customer->user,
            InAppNotification::TYPE_PAYMENT_RECEIVED,
            'Malipo Yamepokelewa! 💰',
            "Asante! Malipo yako ya TZS " . number_format($amount) . " yamekamilika kwa ombi #{$lineRequest->request_number}. Agent anakuja!",
            $lineRequest,
            [
                'amount' => $amount,
                'confirmation_code' => $lineRequest->confirmation_code,
            ]
        );
    }

    /**
     * Notify agent that customer has paid
     */
    public function notifyAgentPaymentReceived(LineRequest $lineRequest): void
    {
        $agent = $lineRequest->agent;
        if (!$agent || !$agent->user) {
            return;
        }

        $amount = $lineRequest->service_fee ?? 1000;
        $customerName = $lineRequest->customer?->user?->name ?? 'Mteja';

        $this->send(
            $agent->user,
            InAppNotification::TYPE_PAYMENT_RECEIVED,
            'Mteja Amelipa! 💰',
            "{$customerName} amelipa TZS " . number_format($amount) . " kwa ombi #{$lineRequest->request_number}. Nenda kumhudumia!",
            $lineRequest,
            [
                'amount' => $amount,
                'customer_name' => $customerName,
                'customer_address' => $lineRequest->customer_address,
                'customer_latitude' => $lineRequest->customer_latitude,
                'customer_longitude' => $lineRequest->customer_longitude,
            ]
        );
    }

    /**
     * Notify both customer and agent that job is completed
     */
    public function notifyJobCompleted(LineRequest $lineRequest): void
    {
        $customer = $lineRequest->customer;
        $agent = $lineRequest->agent;

        $lineType = $lineRequest->line_type->value ?? $lineRequest->line_type;
        $networkName = ucfirst($lineType);

        // Notify customer
        if ($customer && $customer->user) {
            $agentName = $agent?->user?->name ?? 'Agent';

            $this->send(
                $customer->user,
                InAppNotification::TYPE_JOB_COMPLETED,
                'Kazi Imekamilika! 🎊',
                "Laini yako ya {$networkName} imesajiliwa kikamilifu na {$agentName}. Ombi #{$lineRequest->request_number}.",
                $lineRequest,
                [
                    'agent_name' => $agentName,
                ]
            );
        }

        // Notify agent
        if ($agent && $agent->user) {
            $customerName = $customer?->user?->name ?? 'Mteja';
            $earnings = $lineRequest->commission ?? 800;

            $this->send(
                $agent->user,
                InAppNotification::TYPE_JOB_COMPLETED,
                'Kazi Imekamilika! 🎊',
                "Umekamilisha kazi kwa {$customerName}. Umepata TZS " . number_format($earnings) . ". Ombi #{$lineRequest->request_number}.",
                $lineRequest,
                [
                    'customer_name' => $customerName,
                    'earnings' => $earnings,
                ]
            );
        }
    }

    /**
     * Notify customer that their request was cancelled
     */
    public function notifyCustomerRequestCancelled(LineRequest $lineRequest, ?string $reason = null): void
    {
        $customer = $lineRequest->customer;
        if (!$customer || !$customer->user) {
            return;
        }

        $message = "Ombi lako #{$lineRequest->request_number} limefutwa.";
        if ($reason) {
            $message .= " Sababu: {$reason}";
        }

        $this->send(
            $customer->user,
            InAppNotification::TYPE_JOB_CANCELLED,
            'Ombi Limefutwa ❌',
            $message,
            $lineRequest,
            [
                'reason' => $reason,
            ]
        );
    }

    /**
     * Notify agents that a request has been released back to pool
     */
    public function notifyAgentsRequestReleased(LineRequest $lineRequest): void
    {
        $lineType = $lineRequest->line_type->value ?? $lineRequest->line_type;
        $networkName = ucfirst($lineType);

        // Notify all verified agents
        $agents = Agent::where('is_verified', true)
            ->where('is_online', true)
            ->with('user')
            ->get();

        foreach ($agents as $agent) {
            if ($agent->user) {
                $this->send(
                    $agent->user,
                    InAppNotification::TYPE_REQUEST_RELEASED,
                    'Ombi Linapatikana Tena! 🔄',
                    "Ombi la laini ya {$networkName} #{$lineRequest->request_number} linapatikana tena. Haraka ukubali!",
                    $lineRequest
                );
            }
        }
    }

    /**
     * Notify agent that they received a rating
     */
    public function notifyAgentRatingReceived(LineRequest $lineRequest, int $rating): void
    {
        $agent = $lineRequest->agent;
        if (!$agent || !$agent->user) {
            return;
        }

        $customerName = $lineRequest->customer?->user?->name ?? 'Mteja';
        $stars = str_repeat('⭐', $rating);

        $this->send(
            $agent->user,
            InAppNotification::TYPE_RATING_RECEIVED,
            'Umepata Tathmini! ⭐',
            "{$customerName} amekupa nyota {$rating}/5 {$stars} kwa ombi #{$lineRequest->request_number}.",
            $lineRequest,
            [
                'rating' => $rating,
                'customer_name' => $customerName,
            ]
        );
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount(User $user): int
    {
        return InAppNotification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Get notifications for a user (paginated)
     */
    public function getNotifications(User $user, int $perPage = 20)
    {
        return InAppNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): int
    {
        return InAppNotification::where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete old notifications (older than 30 days)
     */
    public function cleanupOldNotifications(int $days = 30): int
    {
        return InAppNotification::where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
