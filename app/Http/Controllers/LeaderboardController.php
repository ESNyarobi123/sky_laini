<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    /**
     * Display the leaderboard page.
     */
    public function index(Request $request): View
    {
        $period = $request->get('period', 'all');
        $agents = $this->getLeaderboardData($period, 20);
        $topThree = array_slice($agents, 0, 3);
        $others = array_slice($agents, 3);

        return view('leaderboard.index', compact('agents', 'topThree', 'others', 'period'));
    }

    /**
     * Get leaderboard data as JSON.
     */
    public function data(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all');
        $limit = $request->get('limit', 20);

        return response()->json([
            'leaderboard' => $this->getLeaderboardData($period, $limit),
            'period' => $period,
        ]);
    }

    /**
     * Get leaderboard data.
     */
    private function getLeaderboardData(string $period, int $limit): array
    {
        $query = Agent::with('user:id,name')
            ->where('is_verified', true);

        // Apply period filter for earnings calculation
        $periodStart = $this->getPeriodStart($period);

        if ($periodStart) {
            // For period-based ranking, we need to calculate period-specific stats
            $agents = $query->get()->map(function ($agent) use ($periodStart) {
                $periodRequests = $agent->lineRequests()
                    ->where('status', 'completed')
                    ->where('completed_at', '>=', $periodStart);

                $periodEarnings = (clone $periodRequests)->sum('commission');
                $periodCompletedJobs = $periodRequests->count();

                return [
                    'id' => $agent->id,
                    'name' => $agent->user?->name ?? 'Unknown',
                    'avatar' => $this->getAvatarUrl($agent),
                    'rating' => round($agent->rating ?? 0, 2),
                    'total_ratings' => $agent->total_ratings ?? 0,
                    'completed_jobs' => $periodCompletedJobs,
                    'total_completed_jobs' => $agent->total_completed_requests ?? 0,
                    'earnings' => $periodEarnings,
                    'total_earnings' => $agent->total_earnings ?? 0,
                    'is_online' => $agent->is_online,
                    'tier' => $agent->tier?->value ?? 'bronze',
                    'score' => $this->calculateScore($agent->rating ?? 0, $periodCompletedJobs, $periodEarnings),
                ];
            });
        } else {
            $agents = $query->get()->map(function ($agent) {
                return [
                    'id' => $agent->id,
                    'name' => $agent->user?->name ?? 'Unknown',
                    'avatar' => $this->getAvatarUrl($agent),
                    'rating' => round($agent->rating ?? 0, 2),
                    'total_ratings' => $agent->total_ratings ?? 0,
                    'completed_jobs' => $agent->total_completed_requests ?? 0,
                    'total_completed_jobs' => $agent->total_completed_requests ?? 0,
                    'earnings' => $agent->total_earnings ?? 0,
                    'total_earnings' => $agent->total_earnings ?? 0,
                    'is_online' => $agent->is_online,
                    'tier' => $agent->tier?->value ?? 'bronze',
                    'score' => $this->calculateScore(
                        $agent->rating ?? 0, 
                        $agent->total_completed_requests ?? 0, 
                        $agent->total_earnings ?? 0
                    ),
                ];
            });
        }

        // Sort by score and add rank
        $sorted = $agents->sortByDesc('score')->values();
        
        return $sorted->take($limit)->map(function ($agent, $index) {
            $agent['rank'] = $index + 1;
            $agent['badge'] = $this->getRankBadge($index + 1);
            return $agent;
        })->toArray();
    }

    /**
     * Calculate agent score for ranking.
     */
    private function calculateScore(float $rating, int $completedJobs, float $earnings): float
    {
        // Weighted scoring: 40% rating, 35% completed jobs, 25% earnings (normalized)
        $normalizedRating = ($rating / 5) * 100;
        $normalizedJobs = min($completedJobs / 100, 1) * 100; // Cap at 100 jobs
        $normalizedEarnings = min($earnings / 1000000, 1) * 100; // Cap at 1M TZS

        return ($normalizedRating * 0.4) + ($normalizedJobs * 0.35) + ($normalizedEarnings * 0.25);
    }

    /**
     * Get period start date.
     */
    private function getPeriodStart(string $period): ?Carbon
    {
        return match ($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => null,
        };
    }

    /**
     * Get avatar URL or generate initials.
     */
    private function getAvatarUrl(Agent $agent): string
    {
        // Return a placeholder URL with initials
        $name = $agent->user?->name ?? 'A';
        $initials = strtoupper(substr($name, 0, 2));
        return "https://ui-avatars.com/api/?name={$initials}&background=f59e0b&color=000&bold=true&size=128";
    }

    /**
     * Get rank badge info.
     */
    private function getRankBadge(int $rank): array
    {
        return match ($rank) {
            1 => ['icon' => '🥇', 'color' => '#ffd700', 'label' => 'Gold'],
            2 => ['icon' => '🥈', 'color' => '#c0c0c0', 'label' => 'Silver'],
            3 => ['icon' => '🥉', 'color' => '#cd7f32', 'label' => 'Bronze'],
            default => ['icon' => "#{$rank}", 'color' => '#6b7280', 'label' => 'Participant'],
        };
    }
}
