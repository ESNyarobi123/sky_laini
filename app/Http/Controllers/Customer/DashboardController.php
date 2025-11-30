<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            abort(404, 'Customer profile not found');
        }

        $stats = [
            'total_requests' => $customer->lineRequests()->count(),
            'pending_requests' => $customer->lineRequests()->where('status', \App\RequestStatus::Pending)->count(),
            'completed_requests' => $customer->lineRequests()->where('status', \App\RequestStatus::Completed)->count(),
            'in_progress_requests' => $customer->lineRequests()->where('status', \App\RequestStatus::InProgress)->count(),
        ];

        $recentRequests = $customer->lineRequests()
            ->with(['agent.user'])
            ->latest()
            ->limit(10)
            ->get();

        $activeRequest = $customer->lineRequests()
            ->whereIn('status', [\App\RequestStatus::Accepted, \App\RequestStatus::InProgress])
            ->with(['agent.user'])
            ->latest()
            ->first();

        $agents = \App\Models\Agent::where('is_verified', true)
            ->with('user')
            ->get(['id', 'user_id', 'current_latitude', 'current_longitude', 'is_online', 'phone']);

        return view('customer.dashboard', compact('stats', 'recentRequests', 'activeRequest', 'agents'));
    }
    public function getAgents(): \Illuminate\Http\JsonResponse
    {
        $agents = \App\Models\Agent::where('is_verified', true)
            ->with('user')
            ->get(['id', 'user_id', 'current_latitude', 'current_longitude', 'is_online', 'phone']);

        return response()->json($agents);
    }
}
