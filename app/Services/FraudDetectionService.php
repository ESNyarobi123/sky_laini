<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\FraudAlert;
use App\Models\LineRequest;
use Illuminate\Support\Collection;

class FraudDetectionService
{
    private const MAX_SPEED_KMH = 120; // Maximum reasonable speed in km/h
    private const LOCATION_MISMATCH_THRESHOLD = 0.5; // 500 meters

    public function __construct(
        private LocationService $locationService
    ) {
    }

    /**
     * Check for speed anomalies in agent location updates.
     */
    public function checkSpeedAnomaly(Agent $agent, float $newLatitude, float $newLongitude): ?FraudAlert
    {
        $lastLocation = $agent->locations()->latest('recorded_at')->first();

        if (!$lastLocation) {
            return null;
        }

        $distance = $this->locationService->calculateDistance(
            $lastLocation->latitude,
            $lastLocation->longitude,
            $newLatitude,
            $newLongitude
        );

        $timeDiff = now()->diffInSeconds($lastLocation->recorded_at);

        if ($timeDiff === 0) {
            return null;
        }

        $speedKmh = ($distance / $timeDiff) * 3600; // Convert to km/h

        if ($speedKmh > self::MAX_SPEED_KMH) {
            return FraudAlert::create([
                'agent_id' => $agent->id,
                'alert_type' => 'speed_anomaly',
                'description' => "Agent moving at {$speedKmh} km/h, exceeding maximum of ".self::MAX_SPEED_KMH.' km/h',
                'metadata' => [
                    'speed_kmh' => $speedKmh,
                    'distance_km' => $distance,
                    'time_seconds' => $timeDiff,
                ],
                'severity' => 'high',
                'status' => 'open',
            ]);
        }

        return null;
    }

    /**
     * Check for location mismatch between agent and request location.
     */
    public function checkLocationMismatch(LineRequest $request, Agent $agent): ?FraudAlert
    {
        if (!$agent->current_latitude || !$agent->current_longitude) {
            return null;
        }

        $distance = $this->locationService->calculateDistance(
            $request->customer_latitude,
            $request->customer_longitude,
            $agent->current_latitude,
            $agent->current_longitude
        );

        if ($distance > self::LOCATION_MISMATCH_THRESHOLD) {
            return FraudAlert::create([
                'agent_id' => $agent->id,
                'line_request_id' => $request->id,
                'alert_type' => 'location_mismatch',
                'description' => "Agent location is {$distance} km away from customer location",
                'metadata' => [
                    'distance_km' => $distance,
                    'customer_lat' => $request->customer_latitude,
                    'customer_lon' => $request->customer_longitude,
                    'agent_lat' => $agent->current_latitude,
                    'agent_lon' => $agent->current_longitude,
                ],
                'severity' => 'medium',
                'status' => 'open',
            ]);
        }

        return null;
    }

    /**
     * Get active fraud alerts for an agent.
     */
    public function getActiveAlerts(Agent $agent): Collection
    {
        return FraudAlert::where('agent_id', $agent->id)
            ->where('status', 'open')
            ->get();
    }
}
