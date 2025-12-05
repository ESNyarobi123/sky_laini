<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use App\RequestStatus;
use Illuminate\Http\Request;

class AvailableGigsController extends Controller
{
    public function index()
    {
        // For now, show pending requests. 
        // In a real system, you might filter by location or other criteria.
        $gigs = LineRequest::with(['customer.user'])
            ->where('status', RequestStatus::Pending)
            ->latest()
            ->paginate(15);

        return view('agent.gigs.index', compact('gigs'));
    }
}
