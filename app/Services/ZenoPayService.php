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
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get($this->baseUrl . '/api/payments/order-status', [
                'order_id' => $orderId
            ]);

            if ($response->successful()) {
                $data = $response->json();
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
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Payment not found or pending'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }
}
