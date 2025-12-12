<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\InAppNotification;
use App\Models\LineRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebasePushNotificationService
{
    protected $messaging;
    protected array $defaults;

    public function __construct()
    {
        try {
            // Use kreait/laravel-firebase facade
            $this->messaging = Firebase::messaging();
            Log::info('FCM: Firebase Messaging initialized successfully');
        } catch (\Exception $e) {
            Log::error('FCM: Failed to initialize Firebase Messaging', [
                'error' => $e->getMessage()
            ]);
            $this->messaging = null;
        }

        $this->defaults = [
            'android' => [
                'channel_id' => 'sky_laini_channel',
                'priority' => 'high',
            ],
            'sound' => 'default',
            'badge' => 1,
        ];
    }

    /**
     * Check if messaging is available
     */
    protected function isAvailable(): bool
    {
        if (!$this->messaging) {
            Log::warning('FCM: Messaging service not available');
            return false;
        }
        return true;
    }

    /**
     * Send push notification to a single user
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if (!$user->fcm_token) {
            Log::warning('FCM: User has no FCM token', ['user_id' => $user->id]);
            return false;
        }

        return $this->sendToToken($user->fcm_token, $title, $body, $data);
    }

    /**
     * Send notification to a specific FCM token
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            // Ensure all data values are strings (FCM requirement)
            $stringData = [];
            foreach ($data as $key => $value) {
                $stringData[$key] = is_array($value) ? json_encode($value) : (string) $value;
            }
            $stringData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

            // Build the message
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($stringData)
                ->withAndroidConfig(
                    AndroidConfig::fromArray([
                        'priority' => 'high',
                        'notification' => [
                            'channel_id' => $this->defaults['android']['channel_id'],
                            'sound' => 'default',
                            'default_vibrate_timings' => true,
                            'default_light_settings' => true,
                        ],
                    ])
                )
                ->withApnsConfig(
                    ApnsConfig::fromArray([
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ])
                );

            // Send the message
            $response = $this->messaging->send($message);

            Log::info('FCM: Notification sent successfully', [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title,
                'response' => $response,
            ]);

            return true;

        } catch (MessagingException $e) {
            Log::error('FCM: MessagingException', [
                'token' => substr($token, 0, 20) . '...',
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            // Check if token is invalid and remove it
            if ($this->isInvalidTokenError($e)) {
                $this->handleInvalidToken($token);
            }

            return false;

        } catch (\Exception $e) {
            Log::error('FCM: Exception during send', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            return false;
        }
    }

    /**
     * Check if the error indicates an invalid token
     */
    protected function isInvalidTokenError(MessagingException $e): bool
    {
        $invalidCodes = ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'];
        $message = $e->getMessage();
        
        foreach ($invalidCodes as $code) {
            if (stripos($message, $code) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Handle invalid FCM token - remove from database
     */
    protected function handleInvalidToken(?string $token): void
    {
        if ($token) {
            User::where('fcm_token', $token)->update([
                'fcm_token' => null,
                'fcm_token_updated_at' => null,
            ]);
            Log::info('FCM: Removed invalid token', ['token' => substr($token, 0, 20) . '...']);
        }
    }

    /**
     * Send to all users (admin broadcast)
     */
    public function sendToAllUsers(string $title, string $body, array $data = []): array
    {
        $users = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get();

        return $this->sendToUserCollection($users, $title, $body, $data);
    }

    /**
     * Send to all agents
     */
    public function sendToAllAgents(string $title, string $body, array $data = []): array
    {
        $users = User::whereHas('agent', function ($query) {
                $query->where('is_verified', true);
            })
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get();

        Log::info('FCM: Sending to agents', [
            'count' => $users->count(),
            'users' => $users->pluck('name', 'id'),
        ]);

        return $this->sendToUserCollection($users, $title, $body, $data);
    }

    /**
     * Send to all customers
     */
    public function sendToAllCustomers(string $title, string $body, array $data = []): array
    {
        $users = User::whereHas('customer')
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get();

        return $this->sendToUserCollection($users, $title, $body, $data);
    }

    /**
     * Send to a collection of users
     */
    protected function sendToUserCollection($users, string $title, string $body, array $data = []): array
    {
        $success = 0;
        $failure = 0;

        foreach ($users as $user) {
            if ($this->sendToUser($user, $title, $body, $data)) {
                $success++;
            } else {
                $failure++;
            }
        }

        Log::info('FCM: Batch send completed', [
            'success' => $success,
            'failure' => $failure,
            'total' => $users->count(),
        ]);

        return ['success' => $success, 'failure' => $failure];
    }

    /**
     * Send to multiple tokens at once (batch)
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        if (!$this->isAvailable() || empty($tokens)) {
            return ['success' => 0, 'failure' => count($tokens)];
        }

        try {
            $stringData = [];
            foreach ($data as $key => $value) {
                $stringData[$key] = is_array($value) ? json_encode($value) : (string) $value;
            }
            $stringData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($stringData)
                ->withAndroidConfig(
                    AndroidConfig::fromArray([
                        'priority' => 'high',
                        'notification' => [
                            'channel_id' => $this->defaults['android']['channel_id'],
                            'sound' => 'default',
                        ],
                    ])
                );

            $report = $this->messaging->sendMulticast($message, $tokens);

            $success = $report->successes()->count();
            $failure = $report->failures()->count();

            // Handle invalid tokens
            foreach ($report->failures()->getItems() as $failedItem) {
                $token = $tokens[$failedItem->index()] ?? null;
                if ($token && $this->isInvalidTokenFromReport($failedItem)) {
                    $this->handleInvalidToken($token);
                }
            }

            Log::info('FCM: Multicast send completed', [
                'success' => $success,
                'failure' => $failure,
            ]);

            return ['success' => $success, 'failure' => $failure];

        } catch (\Exception $e) {
            Log::error('FCM: Multicast exception', ['error' => $e->getMessage()]);
            return ['success' => 0, 'failure' => count($tokens)];
        }
    }

    /**
     * Check if a failure report indicates an invalid token
     */
    protected function isInvalidTokenFromReport($failedItem): bool
    {
        $error = $failedItem->error();
        if (!$error) {
            return false;
        }
        
        $message = $error->getMessage();
        $invalidCodes = ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'];
        
        foreach ($invalidCodes as $code) {
            if (stripos($message, $code) !== false) {
                return true;
            }
        }
        
        return false;
    }

    // ========================================
    // INTEGRATED NOTIFICATION METHODS
    // ========================================

    /**
     * Notify all agents about new line request (Push + In-App)
     */
    public function notifyAllAgentsNewRequest(LineRequest $lineRequest): void
    {
        $lineType = $lineRequest->line_type->value ?? $lineRequest->line_type;
        $networkName = ucfirst($lineType);

        $title = 'Ombi Jipya la Laini! 📱';
        $body = "Mteja anahitaji laini ya {$networkName}. Ombi #{$lineRequest->request_number}";

        $data = [
            'type' => 'new_request',
            'line_request_id' => (string) $lineRequest->id,
            'request_number' => $lineRequest->request_number,
            'line_type' => $lineType,
        ];

        // Get all verified agents with FCM tokens
        $agents = Agent::where('is_verified', true)
            ->with('user')
            ->get();

        foreach ($agents as $agent) {
            if ($agent->user && $agent->user->fcm_token) {
                $this->sendToUser($agent->user, $title, $body, $data);
            }
        }
    }

    /**
     * Notify customer about order created (Push + In-App)
     */
    public function notifyCustomerOrderCreated(LineRequest $lineRequest): void
    {
        $customer = $lineRequest->customer;
        if (!$customer || !$customer->user) {
            return;
        }

        $lineType = $lineRequest->line_type->value ?? $lineRequest->line_type;
        $networkName = ucfirst($lineType);

        $title = 'Ombi Limesajiliwa! ✅';
        $body = "Ombi lako la laini ya {$networkName} #{$lineRequest->request_number} limesajiliwa. Subiri agent akubali.";

        $data = [
            'type' => 'order_created',
            'line_request_id' => (string) $lineRequest->id,
            'request_number' => $lineRequest->request_number,
        ];

        $this->sendToUser($customer->user, $title, $body, $data);
    }

    /**
     * Notify customer about agent acceptance (Push + In-App)
     */
    public function notifyCustomerAgentAccepted(LineRequest $lineRequest): void
    {
        $customer = $lineRequest->customer;
        $agent = $lineRequest->agent;

        if (!$customer || !$customer->user) {
            return;
        }

        $agentName = $agent?->user?->name ?? 'Agent';

        $title = 'Agent Amekubali Ombi! 🎉';
        $body = "{$agentName} amekubali ombi lako #{$lineRequest->request_number}. Tafadhali kamilisha malipo.";

        $data = [
            'type' => 'agent_accepted',
            'line_request_id' => (string) $lineRequest->id,
            'request_number' => $lineRequest->request_number,
            'agent_name' => $agentName,
        ];

        $this->sendToUser($customer->user, $title, $body, $data);
    }

    /**
     * Notify customer about payment received (Push + In-App)
     */
    public function notifyCustomerPaymentReceived(LineRequest $lineRequest): void
    {
        $customer = $lineRequest->customer;
        if (!$customer || !$customer->user) {
            return;
        }

        $amount = $lineRequest->service_fee ?? 1000;

        $title = 'Malipo Yamepokelewa! 💰';
        $body = "Asante! Malipo yako ya TZS " . number_format($amount) . " yamekamilika. Agent anakuja!";

        $data = [
            'type' => 'payment_received',
            'line_request_id' => (string) $lineRequest->id,
            'request_number' => $lineRequest->request_number,
            'amount' => (string) $amount,
        ];

        $this->sendToUser($customer->user, $title, $body, $data);
    }

    /**
     * Notify agent about customer payment (Push + In-App)
     */
    public function notifyAgentPaymentReceived(LineRequest $lineRequest): void
    {
        $agent = $lineRequest->agent;
        if (!$agent || !$agent->user) {
            return;
        }

        $customerName = $lineRequest->customer?->user?->name ?? 'Mteja';
        $amount = $lineRequest->service_fee ?? 1000;

        $title = 'Mteja Amelipa! 💰';
        $body = "{$customerName} amelipa TZS " . number_format($amount) . ". Nenda kumhudumia!";

        $data = [
            'type' => 'payment_received',
            'line_request_id' => (string) $lineRequest->id,
            'request_number' => $lineRequest->request_number,
            'customer_name' => $customerName,
            'customer_address' => $lineRequest->customer_address ?? '',
            'amount' => (string) $amount,
        ];

        $this->sendToUser($agent->user, $title, $body, $data);
    }

    /**
     * Notify both customer and agent about job completion (Push + In-App)
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

            $title = 'Kazi Imekamilika! 🎊';
            $body = "Laini yako ya {$networkName} imesajiliwa kikamilifu na {$agentName}.";

            $data = [
                'type' => 'job_completed',
                'line_request_id' => (string) $lineRequest->id,
                'request_number' => $lineRequest->request_number,
            ];

            $this->sendToUser($customer->user, $title, $body, $data);
        }

        // Notify agent
        if ($agent && $agent->user) {
            $customerName = $customer?->user?->name ?? 'Mteja';
            $earnings = $lineRequest->commission ?? 800;

            $title = 'Kazi Imekamilika! 🎊';
            $body = "Umekamilisha kazi kwa {$customerName}. Umepata TZS " . number_format($earnings) . "!";

            $data = [
                'type' => 'job_completed',
                'line_request_id' => (string) $lineRequest->id,
                'request_number' => $lineRequest->request_number,
                'earnings' => (string) $earnings,
            ];

            $this->sendToUser($agent->user, $title, $body, $data);
        }
    }

    /**
     * Send to topic (for future use)
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            $stringData = array_map('strval', $data);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create($title, $body))
                ->withData($stringData);

            $this->messaging->send($message);

            return true;
        } catch (\Exception $e) {
            Log::error('FCM: Topic send failed', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Subscribe tokens to a topic
     */
    public function subscribeToTopic(array $tokens, string $topic): bool
    {
        if (!$this->isAvailable() || empty($tokens)) {
            return false;
        }

        try {
            $this->messaging->subscribeToTopic($topic, $tokens);
            return true;
        } catch (\Exception $e) {
            Log::error('FCM: Failed to subscribe to topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Unsubscribe tokens from a topic
     */
    public function unsubscribeFromTopic(array $tokens, string $topic): bool
    {
        if (!$this->isAvailable() || empty($tokens)) {
            return false;
        }

        try {
            $this->messaging->unsubscribeFromTopic($topic, $tokens);
            return true;
        } catch (\Exception $e) {
            Log::error('FCM: Failed to unsubscribe from topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Validate a single FCM token
     */
    public function validateToken(string $token): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            // Send a dry run message to validate the token
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create('Test', 'Token validation'));

            $this->messaging->send($message, true); // validateOnly = true

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
