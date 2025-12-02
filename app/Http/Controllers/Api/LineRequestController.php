<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\LineType;
use App\Models\LineRequest;
use App\RequestStatus;
use App\Services\AgentMatchingService;
use App\Services\NotificationService;
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

        // Find best matching agent
        $agent = $this->matchingService->findBestAgent($lineRequest);

        if ($agent) {
            $lineRequest->update([
                'agent_id' => $agent->id,
                'status' => RequestStatus::Accepted,
                'accepted_at' => now(),
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

        return response()->json($lineRequest->load(['customer', 'agent.user', 'payment', 'rating']));
    }
}
