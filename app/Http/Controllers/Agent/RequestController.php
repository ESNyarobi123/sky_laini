<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestController extends Controller
{
    public function show(Request $request, LineRequest $lineRequest): View
    {
        // Ensure the agent owns this request or it's available (though 'show' usually implies ownership or specific interest)
        // For now, we'll allow agents to view details of requests they've accepted.
        
        $agent = $request->user()->agent;
        
        if ($lineRequest->agent_id !== $agent->id) {
             abort(403, 'Unauthorized access to this request.');
        }

        return view('agent.requests.show', compact('lineRequest'));
    }
}
