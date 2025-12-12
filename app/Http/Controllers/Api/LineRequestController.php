<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\LineType;
use App\Models\LineRequest;
use App\RequestStatus;
use App\Services\AgentMatchingService;
use App\Services\NotificationService;
use App\Services\InAppNotificationService;
use App\Services\ZenoPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Events\LineRequestAssigned;

class LineRequestController extends Controller
{
    public function __construct(
        private AgentMatchingService $matchingService,
        private NotificationService $notificationService,
        private InAppNotificationService $inAppNotificationService,
        private ZenoPayService $zenoPayService
    ) {
    }

    /**
     * Create a new line request.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'line_type' => 'required|in:airtel,vodacom,halotel,tigo,zantel',
            'customer_latitude' => 'required|numeric',
            'customer_longitude' => 'required|numeric',
            'customer_address' => 'nullable|string',
            'customer_phone' => 'required|string',
        ]);

        $customer = $request->user()->customer;

        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        $lineRequest = LineRequest::create([
            'customer_id' => $customer->id,
            'request_number' => 'REQ-'.Str::upper(Str::random(8)),
            'line_type' => LineType::from($validated['line_type']),
            'status' => RequestStatus::Pending,
            'customer_latitude' => $validated['customer_latitude'],
            'customer_longitude' => $validated['customer_longitude'],
            'customer_address' => $validated['customer_address'] ?? null,
            'customer_phone' => $validated['customer_phone'],
        ]);

        // 🔔 Send in-app notification to customer that order is created
        $this->inAppNotificationService->notifyCustomerOrderCreated($lineRequest);

        // 🔔 Notify ALL agents about the new request
        $this->inAppNotificationService->notifyAllAgentsNewRequest($lineRequest);

        // Find best matching agent (optional assignment)
        $agent = $this->matchingService->findBestAgent($lineRequest);

        if ($agent) {
            $lineRequest->update([
                'agent_id' => $agent->id,
            ]);

            $this->notificationService->notifyAgent($agent->user, $lineRequest);
            
            // Broadcast event to the agent in real-time
            LineRequestAssigned::dispatch($lineRequest);
        }

        return response()->json($lineRequest->load(['customer', 'agent']), 201);
    }


    /**
     * Get customer's line requests.
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        $requests = $customer->lineRequests()
            ->with(['agent.user', 'payment', 'rating'])
            ->latest()
            ->paginate(15);

        return response()->json($requests);
    }

    /**
     * Get a specific line request.
     */
    public function show(Request $request, LineRequest $lineRequest): JsonResponse
    {
        // Ensure the request belongs to the authenticated customer
        if ($lineRequest->customer->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lineRequest->load(['customer', 'agent.user', 'payment', 'rating']);

        // Hide details if not paid
        if ($lineRequest->payment_status !== 'paid') {
            $lineRequest->confirmation_code = null;
            if ($lineRequest->agent) {
                $lineRequest->agent->current_latitude = null;
                $lineRequest->agent->current_longitude = null;
            }
        }

        return response()->json($lineRequest);
    }

    /**
     * Cancel a line request.
     */
    public function cancel(Request $request, LineRequest $lineRequest): JsonResponse
    {
        // Ensure the request belongs to the authenticated customer
        if ($lineRequest->customer->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($lineRequest->status === RequestStatus::Completed) {
            return response()->json(['message' => 'Cannot cancel a completed request'], 400);
        }

        $lineRequest->update([
            'status' => RequestStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        // 🔔 Notify customer that their request was cancelled
        $this->inAppNotificationService->notifyCustomerRequestCancelled($lineRequest, 'Umefuta ombi lako mwenyewe.');

        return response()->json(['message' => 'Request cancelled successfully']);
    }

    /**
     * Rate the agent for a completed request.
     */
    public function rate(Request $request, LineRequest $lineRequest): JsonResponse
    {
        // Ensure the request belongs to the authenticated customer
        if ($lineRequest->customer->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($lineRequest->status !== RequestStatus::Completed) {
            return response()->json(['message' => 'Cannot rate an incomplete request'], 400);
        }

        if ($lineRequest->rating) {
            return response()->json(['message' => 'Request already rated'], 400);
        }

        $validated = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        $rating = $lineRequest->rating()->create([
            'customer_id' => $lineRequest->customer_id,
            'agent_id' => $lineRequest->agent_id,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
        ]);

        // Update Agent's average rating
        $agent = $lineRequest->agent;
        if ($agent) {
            $newTotalRatings = $agent->total_ratings + 1;
            // Calculate new average: ((old_avg * old_count) + new_rating) / new_count
            // But if old_avg is null/0, handle it.
            $currentTotalScore = ($agent->rating * $agent->total_ratings);
            $newRating = ($currentTotalScore + $validated['rating']) / $newTotalRatings;

            $agent->update([
                'rating' => $newRating,
                'total_ratings' => $newTotalRatings,
            ]);

            // 🔔 Notify agent that they received a rating
            $this->inAppNotificationService->notifyAgentRatingReceived($lineRequest, $validated['rating']);
        }

        return response()->json($rating, 201);
    }
}
