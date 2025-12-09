<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use App\RequestStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestActionController extends Controller
{
    protected $zenoPay;

    public function __construct(\App\Services\ZenoPayService $zenoPay)
    {
        $this->zenoPay = $zenoPay;
    }

    public function accept(Request $request, LineRequest $lineRequest): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        if ($lineRequest->status !== RequestStatus::Pending) {
            return response()->json(['message' => 'This request is no longer available'], 400);
        }

        // 1. Assign Agent
        $lineRequest->update([
            'agent_id' => $agent->id,
            'status' => RequestStatus::Accepted,
            'accepted_at' => now(),
        ]);

        // 2. Initiate Payment (ZenoPay)
        // Get amount from settings or default to 1000
        $amount = \App\Models\SystemSetting::where('key', 'price_per_laini')->value('value') ?? 1000; 
        
        // Format phone number to 255xxxxxxxxx
        $phone = $lineRequest->customer_phone;
        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+255')) {
            $phone = substr($phone, 1);
        }

        // We use the customer's phone number for the payment request
        $result = $this->zenoPay->createOrder(
            $lineRequest->customer->user->name,
            $lineRequest->customer->user->email,
            $phone, 
            $amount
        );

        if ($result['success']) {
            $lineRequest->update([
                'payment_order_id' => $result['order_id'],
                'payment_status' => 'pending',
                'service_fee' => $amount, // Tambua bei ya huduma
            ]);
            
            return response()->json([
                'message' => 'Request accepted. Payment request sent to customer.',
                'payment_initiated' => true
            ]);
        } else {
            // If payment fails to initiate, we might want to warn the agent but still keep the acceptance?
            // Or maybe rollback? For now, let's keep it accepted but warn.
            return response()->json([
                'message' => 'Request accepted, but payment initiation failed: ' . $result['message'],
                'payment_initiated' => false
            ]);
        }
    }

    public function reject(Request $request, LineRequest $lineRequest): JsonResponse
    {
        // For now, rejecting just hides it from the UI or does nothing if it's a pool request
        // In a more complex system, we might log this rejection to avoid showing it again
        
        return response()->json(['message' => 'Request rejected']);
    }

    public function release(Request $request, LineRequest $lineRequest): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        if ($lineRequest->agent_id !== $agent->id) {
            return response()->json(['message' => 'You are not authorized to release this request'], 403);
        }

        if ($lineRequest->status !== RequestStatus::Accepted) {
            return response()->json(['message' => 'Only accepted requests can be released'], 400);
        }

        $lineRequest->update([
            'agent_id' => null,
            'status' => RequestStatus::Pending,
            'accepted_at' => null,
        ]);

        return response()->json(['message' => 'Request released successfully']);
    }

    public function retryPayment(Request $request, LineRequest $lineRequest): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent || $lineRequest->agent_id !== $agent->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($lineRequest->payment_status === 'paid') {
            return response()->json(['message' => 'Payment already completed'], 400);
        }

        // Increment attempts
        $lineRequest->increment('payment_attempts');
        $attempts = $lineRequest->payment_attempts;

        // Check if max attempts reached (3)
        if ($attempts >= 3) {
            // Release the job
            $lineRequest->update([
                'agent_id' => null,
                'status' => RequestStatus::Pending,
                'accepted_at' => null,
                'payment_status' => 'pending', // Reset payment status too
                'payment_attempts' => 0, // Reset attempts for next agent
                'payment_order_id' => null
            ]);

            return response()->json([
                'message' => 'Maximum payment attempts reached. Job has been released back to the pool.',
                'released' => true
            ]);
        }

        // Retry Payment
        $amount = \App\Models\SystemSetting::where('key', 'price_per_laini')->value('value') ?? 1000; 
        
        // Format phone number
        $phone = $lineRequest->customer_phone;
        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+255')) {
            $phone = substr($phone, 1);
        }

        $result = $this->zenoPay->createOrder(
            $lineRequest->customer->user->name,
            $lineRequest->customer->user->email,
            $phone, 
            $amount
        );

        if ($result['success']) {
            $lineRequest->update([
                'payment_order_id' => $result['order_id'],
                'payment_status' => 'pending',
                'service_fee' => $amount, // Tambua bei ya huduma
            ]);
            
            return response()->json([
                'message' => 'Payment request resent successfully. Attempt ' . $attempts . '/3',
                'released' => false
            ]);
        }

        return response()->json(['message' => 'Failed to resend payment request'], 500);
    }
}
