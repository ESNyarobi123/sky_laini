<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ZenoPayService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.zenopay.api_key');
        $this->baseUrl = config('services.zenopay.base_url');
    }

    public function createOrder($buyerName, $buyerEmail, $buyerPhone, $amount)
    {
        $orderId = uniqid('', true);

        // Ensure API key is set
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'ZenoPay API Key is missing in configuration'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey,
            ])->post($this->baseUrl . '/api/payments/mobile_money_tanzania', [
                'order_id' => $orderId,
                'buyer_email' => $buyerEmail,
                'buyer_name' => $buyerName,
                'buyer_phone' => $buyerPhone,
                'amount' => $amount,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'success') {
                    return [
                        'success' => true,
                        'order_id' => $data['order_id'] ?? $orderId,
                        'message' => 'Payment initiated. Check your phone.'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Failed to initiate payment'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    public function checkStatus($orderId)
    {
        // Validate inputs
        if (empty($orderId)) {
            \Log::warning('ZenoPay checkStatus: Empty order ID');
            return [
                'success' => false,
                'message' => 'Order ID is required'
            ];
        }

        if (empty($this->apiKey)) {
            \Log::error('ZenoPay checkStatus: API Key is missing');
            return [
                'success' => false,
                'message' => 'ZenoPay API Key is not configured'
            ];
        }

        try {
            \Log::info('ZenoPay API Request', [
                'url' => $this->baseUrl . '/api/payments/order-status',
                'order_id' => $orderId,
            ]);

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get($this->baseUrl . '/api/payments/order-status', [
                'order_id' => $orderId
            ]);

            // Log raw response for debugging
            \Log::info('ZenoPay API Raw Response', [
                'order_id' => $orderId,
                'status_code' => $response->status(),
                'body' => $response->json(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Handle different response formats
                if (isset($data['resultcode']) && $data['resultcode'] === '000') {
                    // The data field is an array of objects
                    $paymentData = $data['data'][0] ?? null;
                    
                    if ($paymentData) {
                        return [
                            'success' => true,
                            'data' => $paymentData
                        ];
                    }
                }
                
                // Alternative response format
                if (isset($data['status']) && isset($data['payment_status'])) {
                    return [
                        'success' => true,
                        'data' => $data
                    ];
                }
                
                // Return the raw data if structure is different
                if (isset($data['data'])) {
                    return [
                        'success' => true,
                        'data' => is_array($data['data']) ? ($data['data'][0] ?? $data['data']) : $data['data']
                    ];
                }
            }

            \Log::warning('ZenoPay API: Payment not found or pending', [
                'order_id' => $orderId,
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Payment not found or pending'
            ];
        } catch (\Exception $e) {
            \Log::error('ZenoPay API Exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }
}
