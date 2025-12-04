<?php

namespace App\Http\Controllers;

use App\Models\LineRequest;
use App\RequestStatus;
use App\Services\ZenoPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $zenoPay;

    public function __construct(ZenoPayService $zenoPay)
    {
        $this->zenoPay = $zenoPay;
    }

    public function initiate(Request $request, LineRequest $lineRequest)
    {
        // Ensure user is the customer
        if ($request->user()->id !== $lineRequest->customer->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get amount from settings or default to 1000
        $amount = \App\Models\SystemSetting::where('key', 'price_per_laini')->value('value') ?? 1000; 

        $result = $this->zenoPay->createOrder(
            $request->user()->name,
            $request->user()->email,
            $lineRequest->customer_phone, // Use the phone from the request
            $amount
        );

        if ($result['success']) {
            $lineRequest->update([
                'payment_order_id' => $result['order_id'],
                'payment_status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Payment initiated. Please check your phone.',
                'order_id' => $result['order_id']
            ]);
        }

        return response()->json(['message' => 'Failed to initiate payment: ' . $result['message']], 500);
    }

    public function checkStatus(Request $request, LineRequest $lineRequest)
    {
        if (!$lineRequest->payment_order_id) {
            return response()->json(['message' => 'No payment found'], 404);
        }

        $result = $this->zenoPay->checkStatus($lineRequest->payment_order_id);

        if ($result['success']) {
            $paymentData = $result['data'];
            
            if ($paymentData['payment_status'] === 'COMPLETED') { // Verify exact status string from ZenoPay docs/response
                
                if ($lineRequest->payment_status !== 'paid') {
                    // Generate Completion Code
                    $code = strtoupper(Str::random(6));
                    
                    $lineRequest->update([
                        'payment_status' => 'paid',
                        'confirmation_code' => $code
                    ]);
                }

                return response()->json([
                    'status' => 'paid',
                    'confirmation_code' => $lineRequest->confirmation_code
                ]);
            }
        }

        return response()->json(['status' => 'pending']);
    }

    public function completeJob(Request $request, LineRequest $lineRequest)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        // Ensure agent is assigned
        if ($request->user()->agent->id !== $lineRequest->agent_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($lineRequest->confirmation_code === $request->code) {
            $lineRequest->update([
                'status' => RequestStatus::Completed,
                'completed_at' => now()
            ]);

            // TODO: Add logic to credit agent wallet here

            return response()->json(['message' => 'Job completed successfully!']);
        }

        return response()->json(['message' => 'Invalid code'], 400);
    }

    public function cancelJobPayment(Request $request, LineRequest $lineRequest)
    {
        // Ensure user is the customer
        if ($request->user()->id !== $lineRequest->customer->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($lineRequest->payment_status === 'paid') {
            return response()->json(['message' => 'Cannot cancel payment for a paid job'], 400);
        }

        // Logic to cancel payment with ZenoPay if needed, or just update local status
        // For now, we just reset the payment status
        $lineRequest->update([
            'payment_status' => 'cancelled',
            'payment_order_id' => null
        ]);

        return response()->json(['message' => 'Payment cancelled successfully']);
    }
}
