<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $agent = $user->agent;

        if (!$agent) {
            // Auto-create missing agent profile
            $agent = \App\Models\Agent::create([
                'user_id' => $user->id,
                'phone' => $user->phone,
                'nida_number' => 'TEMP-' . $user->id . '-' . time(),
                'is_verified' => false,
                'is_online' => false,
            ]);
        }

        // Check Verification Status
        if (!$agent->is_verified) {
            $agent->load('documents');
            return view('agent.verification_pending', compact('agent'));
        }

        $stats = [
            'total_requests' => $agent->lineRequests()->count(),
            'pending_requests' => $agent->lineRequests()->where('status', \App\RequestStatus::Pending)->count(),
            'completed_requests' => $agent->lineRequests()->where('status', \App\RequestStatus::Completed)->count(),
            'in_progress_requests' => $agent->lineRequests()->where('status', \App\RequestStatus::InProgress)->count(),
            'wallet_balance' => $agent->wallet?->balance ?? 0,
            'total_earnings' => $agent->total_earnings ?? 0,
            'rating' => $agent->rating ?? 0,
        ];

        // Fetch Available Gigs (Pending & Unassigned)
        $recentRequests = LineRequest::with(['customer.user'])
            ->where('status', \App\RequestStatus::Pending)
            ->whereNull('agent_id')
            ->latest()
            ->limit(10)
            ->get();

        // Fetch Active Jobs (Accepted/In Progress by this agent)
        $activeJobs = $agent->lineRequests()
            ->with(['customer.user'])
            ->whereIn('status', [\App\RequestStatus::Accepted, \App\RequestStatus::InProgress])
            ->latest()
            ->get();

        return view('agent.dashboard', compact('stats', 'recentRequests', 'agent', 'activeJobs'));
    }

    public function updates(Request $request)
    {
        $user = $request->user();
        $agent = $user->agent;

        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        // Fetch Available Gigs (Pending & Unassigned)
        $recentRequests = LineRequest::with(['customer.user'])
            ->where('status', \App\RequestStatus::Pending)
            ->whereNull('agent_id')
            ->latest()
            ->limit(10)
            ->get();

        // Fetch Active Jobs (Accepted/In Progress by this agent)
        $activeJobs = $agent->lineRequests()
            ->with(['customer.user'])
            ->whereIn('status', [\App\RequestStatus::Accepted, \App\RequestStatus::InProgress])
            ->latest()
            ->get();

        return response()->json([
            'active_jobs_html' => view('agent.partials.active_jobs', compact('activeJobs'))->render(),
            'available_gigs_html' => view('agent.partials.available_gigs', compact('recentRequests'))->render(),
            'stats' => [
                'pending_count' => $recentRequests->count(),
                'active_count' => $activeJobs->count()
            ]
        ]);
    }
}
