<?php

namespace App\Services;

class LocationService
{
    /**
     * Calculate distance between two coordinates using Haversine formula.
     * Returns distance in kilometers.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula (Static version).
     * Returns distance in kilometers.
     */
    public static function calculateDistanceStatic(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if a point is within a radius of another point.
     */
    public function isWithinRadius(float $lat1, float $lon1, float $lat2, float $lon2, float $radiusKm): bool
    {
        return $this->calculateDistance($lat1, $lon1, $lat2, $lon2) <= $radiusKm;
    }

    /**
     * Get agents within radius of a location.
     */
    public function getAgentsWithinRadius(float $latitude, float $longitude, float $radiusKm): array
    {
        // This would typically query the database
        // For now, return empty array - will be implemented with spatial queries
        return [];
    }
}
