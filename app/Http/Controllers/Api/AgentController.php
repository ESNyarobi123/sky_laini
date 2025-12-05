<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use App\RequestStatus;
use App\Services\ZenoPayService;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(
        private ZenoPayService $zenoPayService
    ) {}
    /**
     * Get agent profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        return response()->json($agent->load('user'));
    }

    /**
     * Update agent profile/status.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'is_online' => 'boolean',
            'is_available' => 'boolean',
        ]);

        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $agent->update($validated);

        return response()->json($agent->load('user'));
    }

    /**
     * Update agent location.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $agent->update([
            'current_latitude' => $validated['latitude'],
            'current_longitude' => $validated['longitude'],
            'last_location_update' => now(),
        ]);

        return response()->json($agent);
    }

    /**
     * Get agent's assigned requests.
     */
    public function requests(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $requests = $agent->lineRequests()
            ->with(['customer.user', 'payment'])
            ->latest()
            ->paginate(15);

        return response()->json($requests);
    }

    /**
     * Get a specific request.
     */
    public function showRequest(Request $request, LineRequest $lineRequest): JsonResponse
    {
        // Ensure the request belongs to the authenticated agent
        if ($lineRequest->agent_id !== $request->user()->agent->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lineRequest->load(['customer.user', 'payment']);

        if ($lineRequest->payment_status !== 'paid') {
            $lineRequest->customer_latitude = null;
            $lineRequest->customer_longitude = null;
            $lineRequest->customer_address = null;
        }

        return response()->json($lineRequest);
    }

    /**
     * Accept or Reject a request.
     */
    public function respondToRequest(Request $request, LineRequest $lineRequest): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:accept,reject',
        ]);

        // Ensure the request is assigned to this agent (or pending if open pool logic exists, but here we assume assignment)
        // For now, assuming direct assignment or pre-check.
        // Actually, usually agents accept pending requests.
        // If the request is already assigned to this agent:
        if ($lineRequest->agent_id !== $request->user()->agent->id) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($validated['action'] === 'accept') {
            // Initiate Payment
            $amount = SystemSetting::where('key', 'price_per_laini')->value('value') ?? 1000;
            
            $result = $this->zenoPayService->createOrder(
                $lineRequest->customer->user->name,
                $lineRequest->customer->user->email,
                $lineRequest->customer_phone,
                $amount
            );

            $updateData = [
                'status' => RequestStatus::Accepted,
                'accepted_at' => now(),
            ];

            if ($result['success']) {
                $updateData['payment_order_id'] = $result['order_id'];
                $updateData['payment_status'] = 'pending';
                $updateData['service_fee'] = $amount;
            }

            $lineRequest->update($updateData);
        } else {
            $lineRequest->update([
                'status' => RequestStatus::Cancelled, // Or Rejected if enum exists
                // 'agent_id' => null // Maybe release it back to pool?
            ]);
        }

        return response()->json($lineRequest);
    }

    /**
     * Get available gigs (pending requests).
     */
    public function gigs(Request $request): JsonResponse
    {
        // In a real system, filter by location radius
        $gigs = LineRequest::with(['customer.user'])
            ->where('status', RequestStatus::Pending)
            ->whereNull('agent_id') // Only unassigned requests
            ->latest()
            ->paginate(15);

        return response()->json($gigs);
    }
}
