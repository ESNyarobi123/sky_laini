<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\InAppNotification;
use App\Models\LineRequest;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FirebasePushNotificationService
{
    protected string $projectId;
    protected string $fcmV1Url;
    protected string $serviceAccountPath;
    protected ?string $serverKey;
    protected array $defaults;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id', 'skyline-c84aa');
        $this->fcmV1Url = config('firebase.fcm_v1_url');
        $this->serviceAccountPath = config('firebase.service_account_path');
        $this->serverKey = config('firebase.server_key');
        $this->defaults = config('firebase.defaults', []);
    }

    /**
     * Get OAuth2 access token for FCM v1 API
     */
    protected function getAccessToken(): ?string
    {
        // Cache the token for 50 minutes (it expires after 60)
        return Cache::remember('fcm_access_token', 50 * 60, function () {
            if (!file_exists($this->serviceAccountPath)) {
                Log::error('FCM: Service account file not found', ['path' => $this->serviceAccountPath]);
                return null;
            }

            try {
                $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);

                if (!isset($serviceAccount['private_key']) || !isset($serviceAccount['client_email'])) {
                    Log::error('FCM: Invalid service account file');
                    return null;
                }

                // Create JWT
                $now = time();
                $header = [
                    'alg' => 'RS256',
                    'typ' => 'JWT',
                ];
                $payload = [
                    'iss' => $serviceAccount['client_email'],
                    'sub' => $serviceAccount['client_email'],
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'iat' => $now,
                    'exp' => $now + 3600,
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                ];

                $headerEncoded = $this->base64UrlEncode(json_encode($header));
                $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
                $signatureInput = $headerEncoded . '.' . $payloadEncoded;

                // Sign with private key
                $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
                if (!$privateKey) {
                    Log::error('FCM: Failed to load private key');
                    return null;
                }

                openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
                $signatureEncoded = $this->base64UrlEncode($signature);

                $jwt = $signatureInput . '.' . $signatureEncoded;

                // Exchange JWT for access token
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info('FCM: Access token obtained successfully');
                    return $data['access_token'] ?? null;
                }

                Log::error('FCM: Failed to get access token', ['response' => $response->body()]);
                return null;

            } catch (\Exception $e) {
                Log::error('FCM: Exception getting access token', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Base64 URL encode
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send push notification to a single user using FCM v1 API
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        if (!$user->fcm_token) {
            Log::warning('FCM: User has no FCM token', ['user_id' => $user->id]);
            return false;
        }

        return $this->sendV1($user->fcm_token, $title, $body, $data);
    }

    /**
     * Send notification using FCM v1 API (OAuth2)
     */
    public function sendV1(string $token, string $title, string $body, array $data = []): bool
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            // Fallback to legacy API if v1 fails
            return $this->sendLegacy($token, $title, $body, $data);
        }

        // Ensure all data values are strings (FCM requirement)
        $stringData = [];
        foreach ($data as $key => $value) {
            $stringData[$key] = is_array($value) ? json_encode($value) : (string) $value;
        }
        $stringData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $stringData,
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => $this->defaults['android']['channel_id'] ?? 'sky_laini_channel',
                        'sound' => 'default',
                        'default_vibrate_timings' => true,
                        'default_light_settings' => true,
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->fcmV1Url, $payload);

            if ($response->successful()) {
                Log::info('FCM v1: Notification sent successfully', [
                    'token' => substr($token, 0, 20) . '...',
                    'title' => $title,
                ]);
                return true;
            }

            $error = $response->json();
            
            // Handle invalid token
            if (isset($error['error']['details'])) {
                foreach ($error['error']['details'] as $detail) {
                    if (isset($detail['errorCode']) && 
                        in_array($detail['errorCode'], ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                        $this->handleInvalidToken($token);
                    }
                }
            }

            Log::error('FCM v1: Send failed', [
                'status' => $response->status(),
                'error' => $error,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('FCM v1: Exception during send', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send notification using Legacy FCM API (fallback)
     */
    public function sendLegacy(string $token, string $title, string $body, array $data = []): bool
    {
        if (!$this->serverKey) {
            Log::error('FCM Legacy: Server key not configured');
            return false;
        }

        $payload = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => array_merge($data, [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'title' => $title,
                'body' => $body,
            ]),
            'android' => [
                'priority' => 'high',
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['success']) && $result['success'] > 0) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('FCM Legacy: Exception during send', ['message' => $e->getMessage()]);
            return false;
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
            
            // Clear the access token cache if there's an issue
            Cache::forget('fcm_access_token');
        }
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
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return false;
        }

        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $data),
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->fcmV1Url, $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM: Topic send failed', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
