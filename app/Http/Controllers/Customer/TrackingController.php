<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Get agent location (basic)
     */
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

    /**
     * Get full tracking data with directions (only after payment is complete)
     */
    public function getTrackingWithDirections(Request $request, LineRequest $lineRequest): JsonResponse
    {
        // Ensure the user owns this request
        $customer = $request->user()->customer;
        if (!$customer || $lineRequest->customer_id !== $customer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Load relationships
        $lineRequest->load(['agent.user']);

        $agent = $lineRequest->agent;
        if (!$agent) {
            return response()->json(['message' => 'No agent assigned yet'], 404);
        }

        // Check payment status - only allow tracking after payment
        $isPaid = $lineRequest->payment_status === 'paid';
        $canTrack = $isPaid && in_array($lineRequest->status->value ?? $lineRequest->status, ['accepted', 'in_progress']);

        // Basic response for unpaid requests
        if (!$canTrack) {
            return response()->json([
                'can_track' => false,
                'payment_status' => $lineRequest->payment_status,
                'request_status' => $lineRequest->status->value ?? $lineRequest->status,
                'message' => $isPaid ? 'Agent not yet on the way' : 'Payment required to track agent',
                'agent' => [
                    'name' => $agent->user?->name ?? 'Unknown',
                    'phone' => $agent->phone,
                    'rating' => $agent->rating,
                    'is_online' => $agent->is_online,
                ],
            ]);
        }

        // Get locations
        $agentLat = $agent->current_latitude;
        $agentLng = $agent->current_longitude;
        $customerLat = $lineRequest->customer_latitude;
        $customerLng = $lineRequest->customer_longitude;

        // Calculate distance
        $distance = null;
        $estimatedTime = null;
        if ($agentLat && $agentLng && $customerLat && $customerLng) {
            $distance = LocationService::calculateDistanceStatic(
                $agentLat,
                $agentLng,
                $customerLat,
                $customerLng
            );
            // Estimate time: assume 30 km/h average speed in city
            $estimatedTime = round(($distance / 30) * 60); // minutes
        }

        // Build navigation URLs
        $origin = "{$agentLat},{$agentLng}";
        $destination = "{$customerLat},{$customerLng}";

        return response()->json([
            'can_track' => true,
            'payment_status' => $lineRequest->payment_status,
            'request_status' => $lineRequest->status->value ?? $lineRequest->status,
            
            // Agent info
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->user?->name ?? 'Unknown',
                'phone' => $agent->phone,
                'rating' => $agent->rating,
                'is_online' => $agent->is_online,
                'latitude' => $agentLat,
                'longitude' => $agentLng,
                'last_location_update' => $agent->last_location_update,
            ],
            
            // Customer destination
            'destination' => [
                'latitude' => $customerLat,
                'longitude' => $customerLng,
                'address' => $lineRequest->customer_address,
            ],
            
            // Tracking metrics
            'tracking' => [
                'distance_km' => $distance ? round($distance, 2) : null,
                'estimated_minutes' => $estimatedTime,
                'estimated_arrival' => $estimatedTime ? now()->addMinutes($estimatedTime)->format('H:i') : null,
            ],
            
            // Navigation intents for mobile apps
            'navigation' => [
                'google_maps_url' => "https://www.google.com/maps/dir/{$origin}/{$destination}",
                'directions_api_url' => "https://www.google.com/maps/dir/?api=1&origin={$origin}&destination={$destination}&travelmode=driving",
                'android_intent' => "google.navigation:q={$destination}",
                'ios_intent' => "comgooglemaps://?daddr={$destination}&directionsmode=driving",
                'waze_url' => "https://waze.com/ul?ll={$customerLat},{$customerLng}&navigate=yes",
            ],
            
            // Route polyline coordinates (for drawing on map)
            'route' => [
                'start' => ['latitude' => $agentLat, 'longitude' => $agentLng],
                'end' => ['latitude' => $customerLat, 'longitude' => $customerLng],
            ],
        ]);
    }

    /**
     * Update customer location
     */
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

        // Update customer's current location
        $customer->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
        ]);

        // Update location for all active requests (pending or in_progress)
        $customer->lineRequests()
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->update([
                'customer_latitude' => $request->latitude,
                'customer_longitude' => $request->longitude,
            ]);

        return response()->json(['message' => 'Location updated successfully']);
    }
}
