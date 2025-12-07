<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\LineRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Display analytics dashboard.
     */
    public function index(): View
    {
        $stats = $this->getOverviewStats();
        $requestsTrend = $this->getRequestsTrend(30);
        $popularLineTypes = $this->getPopularLineTypes();
        $topAgents = $this->getTopAgents(10);
        $revenueData = $this->getRevenueData(12);
        $hourlyDistribution = $this->getHourlyDistribution();

        return view('admin.analytics.index', compact(
            'stats',
            'requestsTrend',
            'popularLineTypes',
            'topAgents',
            'revenueData',
            'hourlyDistribution'
        ));
    }

    /**
     * Get analytics data as JSON (for API/AJAX).
     */
    public function data(Request $request): JsonResponse
    {
        $period = $request->get('period', 30);

        return response()->json([
            'overview' => $this->getOverviewStats(),
            'requests_trend' => $this->getRequestsTrend($period),
            'popular_line_types' => $this->getPopularLineTypes(),
            'top_agents' => $this->getTopAgents(10),
            'revenue_data' => $this->getRevenueData(12),
            'hourly_distribution' => $this->getHourlyDistribution(),
            'agent_performance' => $this->getAgentPerformance(),
            'customer_stats' => $this->getCustomerStats(),
        ]);
    }

    /**
     * Get overview statistics.
     */
    private function getOverviewStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Today's stats
        $todayRequests = LineRequest::whereDate('created_at', $today)->count();
        $todayCompleted = LineRequest::whereDate('completed_at', $today)->count();
        $todayRevenue = LineRequest::whereDate('completed_at', $today)
            ->where('payment_status', 'paid')
            ->sum('service_fee');

        // This month's stats
        $monthRequests = LineRequest::where('created_at', '>=', $thisMonth)->count();
        $monthCompleted = LineRequest::where('completed_at', '>=', $thisMonth)->count();
        $monthRevenue = LineRequest::where('completed_at', '>=', $thisMonth)
            ->where('payment_status', 'paid')
            ->sum('service_fee');

        // Last month's stats for comparison
        $lastMonthRequests = LineRequest::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $lastMonthRevenue = LineRequest::whereBetween('completed_at', [$lastMonth, $lastMonthEnd])
            ->where('payment_status', 'paid')
            ->sum('service_fee');

        // Growth percentages
        $requestsGrowth = $lastMonthRequests > 0 
            ? round((($monthRequests - $lastMonthRequests) / $lastMonthRequests) * 100, 1)
            : 100;
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 100;

        // Active stats
        $activeAgents = Agent::where('is_online', true)->count();
        $totalAgents = Agent::count();
        $pendingRequests = LineRequest::where('status', 'pending')->count();

        // Completion rate
        $totalCompleted = LineRequest::where('status', 'completed')->count();
        $totalRequests = LineRequest::count();
        $completionRate = $totalRequests > 0 ? round(($totalCompleted / $totalRequests) * 100, 1) : 0;

        return [
            'today' => [
                'requests' => $todayRequests,
                'completed' => $todayCompleted,
                'revenue' => $todayRevenue,
            ],
            'month' => [
                'requests' => $monthRequests,
                'completed' => $monthCompleted,
                'revenue' => $monthRevenue,
                'requests_growth' => $requestsGrowth,
                'revenue_growth' => $revenueGrowth,
            ],
            'agents' => [
                'total' => $totalAgents,
                'online' => $activeAgents,
                'offline' => $totalAgents - $activeAgents,
            ],
            'pending_requests' => $pendingRequests,
            'completion_rate' => $completionRate,
            'total_users' => User::count(),
        ];
    }

    /**
     * Get requests trend for chart.
     */
    private function getRequestsTrend(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);

        $data = LineRequest::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $labels = [];
        $totals = [];
        $completed = [];
        $cancelled = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayData = $data->firstWhere('date', $date);

            $labels[] = Carbon::parse($date)->format('M d');
            $totals[] = $dayData?->total ?? 0;
            $completed[] = $dayData?->completed ?? 0;
            $cancelled[] = $dayData?->cancelled ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Total Requests', 'data' => $totals, 'color' => '#f59e0b'],
                ['label' => 'Completed', 'data' => $completed, 'color' => '#22c55e'],
                ['label' => 'Cancelled', 'data' => $cancelled, 'color' => '#ef4444'],
            ],
        ];
    }

    /**
     * Get popular line types distribution.
     */
    private function getPopularLineTypes(): array
    {
        $data = LineRequest::select('line_type', DB::raw('COUNT(*) as count'))
            ->groupBy('line_type')
            ->orderByDesc('count')
            ->get();

        $labels = [];
        $values = [];
        $colors = [
            'vodacom' => '#e60000',
            'airtel' => '#ff0000',
            'tigo' => '#0066cc',
            'halotel' => '#00aa00',
            'zantel' => '#ff6600',
        ];

        foreach ($data as $item) {
            $type = $item->line_type->value ?? $item->line_type;
            $labels[] = ucfirst($type);
            $values[] = $item->count;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => array_values($colors),
        ];
    }

    /**
     * Get top performing agents.
     */
    private function getTopAgents(int $limit): array
    {
        return Agent::with('user:id,name')
            ->select('agents.*')
            ->where('total_completed_requests', '>', 0)
            ->orderByDesc('total_completed_requests')
            ->orderByDesc('rating')
            ->limit($limit)
            ->get()
            ->map(fn($agent) => [
                'id' => $agent->id,
                'name' => $agent->user?->name ?? 'Unknown',
                'rating' => round($agent->rating, 2),
                'completed_jobs' => $agent->total_completed_requests,
                'earnings' => $agent->total_earnings,
                'is_online' => $agent->is_online,
                'is_verified' => $agent->is_verified,
            ])
            ->toArray();
    }

    /**
     * Get monthly revenue data.
     */
    private function getRevenueData(int $months): array
    {
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();

        $data = LineRequest::select(
                DB::raw('YEAR(completed_at) as year'),
                DB::raw('MONTH(completed_at) as month'),
                DB::raw('SUM(service_fee) as revenue'),
                DB::raw('SUM(commission) as agent_commission'),
                DB::raw('COUNT(*) as transactions')
            )
            ->where('payment_status', 'paid')
            ->where('completed_at', '>=', $startDate)
            ->groupBy(DB::raw('YEAR(completed_at)'), DB::raw('MONTH(completed_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $revenue = [];
        $commission = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            
            $monthData = $data->first(fn($d) => $d->year == $year && $d->month == $month);

            $labels[] = $date->format('M Y');
            $revenue[] = $monthData?->revenue ?? 0;
            $commission[] = $monthData?->agent_commission ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Total Revenue', 'data' => $revenue, 'color' => '#f59e0b'],
                ['label' => 'Agent Commissions', 'data' => $commission, 'color' => '#22c55e'],
            ],
        ];
    }

    /**
     * Get hourly distribution of requests.
     */
    private function getHourlyDistribution(): array
    {
        $data = LineRequest::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        $hours = [];
        $counts = [];

        for ($i = 0; $i < 24; $i++) {
            $hours[] = sprintf('%02d:00', $i);
            $counts[] = $data[$i] ?? 0;
        }

        return [
            'labels' => $hours,
            'values' => $counts,
        ];
    }

    /**
     * Get agent performance metrics.
     */
    private function getAgentPerformance(): array
    {
        $avgResponseTime = LineRequest::whereNotNull('accepted_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, accepted_at)) as avg_time')
            ->value('avg_time');

        $avgCompletionTime = LineRequest::whereNotNull('completed_at')
            ->whereNotNull('accepted_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, accepted_at, completed_at)) as avg_time')
            ->value('avg_time');

        $avgRating = Agent::avg('rating');

        return [
            'avg_response_time' => round($avgResponseTime ?? 0, 1),
            'avg_completion_time' => round($avgCompletionTime ?? 0, 1),
            'avg_rating' => round($avgRating ?? 0, 2),
            'verified_agents' => Agent::where('is_verified', true)->count(),
            'unverified_agents' => Agent::where('is_verified', false)->count(),
        ];
    }

    /**
     * Get customer statistics.
     */
    private function getCustomerStats(): array
    {
        $activeCustomers = LineRequest::distinct('customer_id')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count('customer_id');

        $repeatCustomers = LineRequest::select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $avgRequestsPerCustomer = LineRequest::count() / max(User::where('role', 'customer')->count(), 1);

        return [
            'total_customers' => User::where('role', 'customer')->count(),
            'active_customers' => $activeCustomers,
            'repeat_customers' => $repeatCustomers,
            'avg_requests_per_customer' => round($avgRequestsPerCustomer, 2),
        ];
    }
}
