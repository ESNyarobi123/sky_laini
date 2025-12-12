<?php

namespace App\Http\Controllers;

use App\Models\LineRequest;
use App\RequestStatus;
use App\Services\InvoiceService;
use App\Services\InAppNotificationService;
use App\Services\ZenoPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $zenoPay;
    protected $invoiceService;
    protected $inAppNotificationService;

    public function __construct(
        ZenoPayService $zenoPay, 
        InvoiceService $invoiceService,
        InAppNotificationService $inAppNotificationService
    ) {
        $this->zenoPay = $zenoPay;
        $this->invoiceService = $invoiceService;
        $this->inAppNotificationService = $inAppNotificationService;
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
        // Authorization: Check if user is either the customer or the assigned agent
        $user = $request->user();
        $isCustomer = $user->customer && $lineRequest->customer_id === $user->customer->id;
        $isAgent = $user->agent && $lineRequest->agent_id === $user->agent->id;
        
        if (!$isCustomer && !$isAgent) {
            return response()->json([
                'message' => 'Unauthorized - You are not authorized to check this payment status'
            ], 403);
        }

        // Check if payment_order_id exists
        if (!$lineRequest->payment_order_id) {
            \Log::warning('Payment status check failed: No payment_order_id', [
                'line_request_id' => $lineRequest->id,
                'payment_status' => $lineRequest->payment_status,
                'user_id' => $user->id,
            ]);
            
            return response()->json([
                'status' => $lineRequest->payment_status ?? 'pending',
                'payment_status' => $lineRequest->payment_status ?? 'pending',
                'message' => 'No payment order found'
            ]);
        }

        // Check ZenoPay API
        $result = $this->zenoPay->checkStatus($lineRequest->payment_order_id);

        // Log the API response for debugging
        \Log::info('ZenoPay status check response', [
            'order_id' => $lineRequest->payment_order_id,
            'line_request_id' => $lineRequest->id,
            'result' => $result,
        ]);

        if ($result['success']) {
            $paymentData = $result['data'];
            $paymentStatus = strtoupper($paymentData['payment_status'] ?? '');
            
            // Check for completed status (case-insensitive)
            if ($paymentStatus === 'COMPLETED' || $paymentStatus === 'SUCCESS' || $paymentStatus === 'PAID') {
                
                if ($lineRequest->payment_status !== 'paid') {
                    // Generate Completion Code
                    $code = strtoupper(Str::random(6));
                    
                    $lineRequest->update([
                        'payment_status' => 'paid',
                        'confirmation_code' => $code
                    ]);
                    
                    // Auto-generate Invoice
                    try {
                        $invoice = $this->invoiceService->generateInvoice($lineRequest);
                        \Log::info('Invoice auto-generated', [
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to auto-generate invoice', [
                            'line_request_id' => $lineRequest->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // 🔔 Notify customer that payment was received
                    $this->inAppNotificationService->notifyCustomerPaymentReceived($lineRequest);

                    // 🔔 Notify agent that customer has paid
                    $this->inAppNotificationService->notifyAgentPaymentReceived($lineRequest);
                    
                    \Log::info('Payment marked as paid', [
                        'line_request_id' => $lineRequest->id,
                        'confirmation_code' => $code,
                    ]);
                }

                return response()->json([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'confirmation_code' => $lineRequest->fresh()->confirmation_code,
                    'message' => 'Payment completed successfully'
                ]);
            }
            
            // Return the actual status from ZenoPay
            return response()->json([
                'status' => 'pending',
                'payment_status' => 'pending',
                'zenopay_status' => $paymentData['payment_status'] ?? 'unknown',
                'message' => 'Payment is still being processed'
            ]);
        }

        // API call failed - return current local status
        \Log::warning('ZenoPay API check failed', [
            'order_id' => $lineRequest->payment_order_id,
            'error' => $result['message'] ?? 'Unknown error',
        ]);
        
        return response()->json([
            'status' => $lineRequest->payment_status ?? 'pending',
            'payment_status' => $lineRequest->payment_status ?? 'pending',
            'message' => $result['message'] ?? 'Unable to check payment status',
            'error' => true
        ]);
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
            // Calculate Commission (e.g., 80% to agent)
            $amount = $lineRequest->service_fee ?? 1000;
            $commissionRate = 0.8;
            $earnings = $amount * $commissionRate;

            $lineRequest->update([
                'status' => RequestStatus::Completed,
                'completed_at' => now(),
                'commission' => $earnings
            ]);

            // Credit Agent Wallet
            $agent = $lineRequest->agent;
            if ($agent && $agent->wallet) {
                $wallet = $agent->wallet;
                $balanceBefore = $wallet->balance;
                $wallet->balance += $earnings;
                $wallet->save();
                
                $wallet->transactions()->create([
                    'line_request_id' => $lineRequest->id,
                    'transaction_type' => 'credit',
                    'amount' => $earnings,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'description' => 'Earnings for Request #' . $lineRequest->request_number,
                ]);
                
                $agent->total_earnings += $earnings;
                $agent->total_completed_requests += 1;
                $agent->save();
            }

            // Ensure invoice exists (may have been created on payment)
            $invoice = \App\Models\Invoice::where('line_request_id', $lineRequest->id)->first();
            if (!$invoice) {
                try {
                    $invoice = $this->invoiceService->generateInvoice($lineRequest);
                } catch (\Exception $e) {
                    \Log::error('Failed to generate invoice on job completion', [
                        'line_request_id' => $lineRequest->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            \Log::info('Job completed successfully', [
                'line_request_id' => $lineRequest->id,
                'agent_id' => $agent?->id,
                'earnings' => $earnings,
                'invoice_id' => $invoice?->id,
            ]);

            // 🔔 Notify both customer and agent that job is completed
            $this->inAppNotificationService->notifyJobCompleted($lineRequest);

            return response()->json([
                'message' => 'Job completed successfully!',
                'earnings' => $earnings,
                'invoice' => $invoice ? [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ] : null,
            ]);
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
