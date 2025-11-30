<?php

namespace App\Services;

use App\LineType;
use App\Models\Agent;
use App\Models\LineRequest;
use Illuminate\Support\Collection;

class AgentMatchingService
{
    public function __construct(
        private LocationService $locationService
    ) {
    }

    /**
     * Find the best matching agent for a line request.
     */
    public function findBestAgent(LineRequest $request): ?Agent
    {
        $availableAgents = $this->getAvailableAgents($request);

        if ($availableAgents->isEmpty()) {
            return null;
        }

        // Score each agent based on multiple factors
        $scoredAgents = $availableAgents->map(function (Agent $agent) use ($request) {
            return [
                'agent' => $agent,
                'score' => $this->calculateAgentScore($agent, $request),
            ];
        })->sortByDesc('score');

        return $scoredAgents->first()['agent'] ?? null;
    }

    /**
     * Get available agents within radius for the request.
     */
    private function getAvailableAgents(LineRequest $request): Collection
    {
        $agents = Agent::query()
            ->where('is_verified', true)
            ->where('is_available', true)
            ->where('is_online', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get();

        return $agents->filter(function (Agent $agent) use ($request) {
            // Check if agent specializes in this line type
            if ($agent->specialization) {
                $specializations = explode(',', $agent->specialization);
                if (!in_array($request->line_type->value, $specializations)) {
                    return false;
                }
            }

            // Check if agent is within service radius
            $distance = $this->locationService->calculateDistance(
                $request->customer_latitude,
                $request->customer_longitude,
                $agent->current_latitude,
                $agent->current_longitude
            );

            return $distance <= $agent->service_radius_km;
        });
    }

    /**
     * Calculate agent score based on distance, rating, and availability.
     */
    private function calculateAgentScore(Agent $agent, LineRequest $request): float
    {
        $distance = $this->locationService->calculateDistance(
            $request->customer_latitude,
            $request->customer_longitude,
            $agent->current_latitude,
            $agent->current_longitude
        );

        // Distance score (closer = higher score, max 50 points)
        $distanceScore = max(0, 50 - ($distance * 10));

        // Rating score (max 30 points)
        $ratingScore = $agent->rating * 6;

        // Completion count score (max 20 points)
        $completionScore = min(20, $agent->total_completed_requests / 10);

        return $distanceScore + $ratingScore + $completionScore;
    }
}
