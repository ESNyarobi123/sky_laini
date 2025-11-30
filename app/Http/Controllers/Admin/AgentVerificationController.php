<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;

class AgentVerificationController extends Controller
{
    public function index()
    {
        $pendingAgents = Agent::with('user', 'documents')
            ->where('is_verified', false)
            ->latest()
            ->paginate(10);
            
        return view('admin.agents.verification', compact('pendingAgents'));
    }

    public function verify(Agent $agent)
    {
        $agent->update(['is_verified' => true]);
        
        // Notify agent (TODO: Add notification logic)
        
        return back()->with('success', 'Agent verified successfully.');
    }

    public function reject(Agent $agent)
    {
        // In a real app, we might want to provide a reason or delete the agent
        // For now, we'll just leave them unverified or delete
        // $agent->delete(); 
        
        return back()->with('success', 'Agent verification rejected.');
    }
}
