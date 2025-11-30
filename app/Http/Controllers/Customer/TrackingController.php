<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function getAgentLocation(Request $request, LineRequest $lineRequest): JsonResponse
    {
        // Ensure the user owns this request
        if ($lineRequest->customer_id !== $request->user()->customer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $agent = $lineRequest->agent;

        if (!$agent) {
            return response()->json(['message' => 'No agent assigned'], 404);
        }

        return response()->json([
            'latitude' => $agent->current_latitude,
            'longitude' => $agent->current_longitude,
            'is_online' => $agent->is_online,
            'last_update' => $agent->last_location_update,
        ]);
    }
    public function updateCustomerLocation(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $customer = $request->user()->customer;

        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        // Update location for all active requests (pending or in_progress)
        $customer->lineRequests()
            ->whereIn('status', ['pending', 'in_progress'])
            ->update([
                'customer_latitude' => $request->latitude,
                'customer_longitude' => $request->longitude,
            ]);

        return response()->json(['message' => 'Location updated successfully']);
    }
}
