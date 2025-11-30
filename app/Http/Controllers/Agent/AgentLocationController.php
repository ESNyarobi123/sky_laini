<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AgentLocationController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $data = [
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
            'last_location_update' => now(),
        ];

        if ($request->has('is_online')) {
            $data['is_online'] = $request->boolean('is_online');
        }

        $agent->update($data);

        return response()->json(['message' => 'Location updated successfully']);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'is_online' => 'required|boolean',
        ]);

        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $agent->update([
            'is_online' => $request->is_online
        ]);

        return response()->json(['message' => 'Status updated successfully']);
    }
}
