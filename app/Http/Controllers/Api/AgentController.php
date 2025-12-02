<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use App\RequestStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
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

        return response()->json($lineRequest->load(['customer.user', 'payment']));
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
            $lineRequest->update([
                'status' => RequestStatus::Accepted,
                'accepted_at' => now(),
            ]);
        } else {
            $lineRequest->update([
                'status' => RequestStatus::Cancelled, // Or Rejected if enum exists
                // 'agent_id' => null // Maybe release it back to pool?
            ]);
        }

        return response()->json($lineRequest);
    }
}
