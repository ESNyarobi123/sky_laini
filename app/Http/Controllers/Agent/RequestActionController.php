<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use App\RequestStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestActionController extends Controller
{
    public function accept(Request $request, LineRequest $lineRequest): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        if ($lineRequest->status !== RequestStatus::Pending) {
            return response()->json(['message' => 'This request is no longer available'], 400);
        }

        $lineRequest->update([
            'agent_id' => $agent->id,
            'status' => RequestStatus::Accepted,
            'accepted_at' => now(),
        ]);

        return response()->json(['message' => 'Request accepted successfully']);
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
}
