<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 1. Counts
        $totalUsers = \App\Models\User::count();
        $totalAgents = \App\Models\Agent::count();
        $activeAgents = \App\Models\Agent::where('is_online', true)->count();
        $pendingVerifications = \App\Models\Agent::where('is_verified', false)->count();

        // 2. Revenue (Assuming 1000 TZS per paid request for now, or sum 'amount' if available)
        // We'll count paid requests * 1000 for simplicity as per current flow
        $paidRequestsCount = \App\Models\LineRequest::where('payment_status', 'paid')->count();
        $totalRevenue = $paidRequestsCount * 1000; 

        // 3. Recent Activity
        $recentActivities = \App\Models\LineRequest::with(['customer.user', 'agent.user'])
            ->latest()
            ->take(5)
            ->get();

        // 4. System Health (Mocked for now as we don't have real server stats)
        $systemHealth = [
            'server_load' => rand(10, 40),
            'db_connections' => rand(20, 60),
            'api_latency' => rand(50, 200),
        ];

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalAgents',
            'activeAgents',
            'pendingVerifications',
            'totalRevenue',
            'recentActivities',
            'systemHealth'
        ));
    }
}
