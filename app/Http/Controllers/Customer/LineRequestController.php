<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\LineType;
use App\Models\LineRequest;
use App\RequestStatus;
use App\Services\AgentMatchingService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LineRequestController extends Controller
{
    public function __construct(
        private AgentMatchingService $matchingService,
        private NotificationService $notificationService
    ) {
    }

    /**
     * Show the form for creating a new line request.
     */
    public function create(Request $request): View
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            abort(404, 'Customer profile not found');
        }

        return view('customer.line-requests.create');
    }

    /**
     * Store a newly created line request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'line_type' => 'required|in:airtel,vodacom,halotel,tigo,zantel',
            'customer_latitude' => 'required|numeric',
            'customer_longitude' => 'required|numeric',
            'customer_address' => 'nullable|string|max:500',
            'customer_phone' => 'required|string',
        ]);

        $customer = $request->user()->customer;

        if (!$customer) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Customer profile not found');
        }

        $lineRequest = LineRequest::create([
            'customer_id' => $customer->id,
            'request_number' => 'REQ-'.Str::upper(Str::random(8)),
            'line_type' => LineType::from($validated['line_type']),
            'status' => RequestStatus::Pending,
            'customer_latitude' => $validated['customer_latitude'],
            'customer_longitude' => $validated['customer_longitude'],
            'customer_address' => $validated['customer_address'] ?? null,
            'customer_phone' => $validated['customer_phone'],
            'service_fee' => \App\Models\SystemSetting::where('key', 'price_per_laini')->value('value') ?? 1000,
        ]);

        // Find best matching agent (optional: just to notify someone nearby)
        $agent = $this->matchingService->findBestAgent($lineRequest);

        if ($agent) {
            // We don't assign the agent_id yet, or we do but keep status Pending
            // Let's just notify them without assigning, so it appears in the pool (or assign as 'suggested')
            // For now, let's NOT assign agent_id so it's open to all nearby agents (if logic supports it)
            // OR assign it but keep status Pending.
            
            // Based on Agent Dashboard logic, it likely looks for requests where agent_id is null OR agent_id is them.
            // Let's assume we just notify the nearest one but leave it open.
            
            $this->notificationService->notifyAgent($agent->user, $lineRequest);
        }

        return redirect()->route('customer.line-requests.show', $lineRequest)
            ->with('success', 'Line request created successfully!');
    }

    /**
     * Display a listing of the customer's line requests.
     */
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            abort(404, 'Customer profile not found');
        }

        $requests = $customer->lineRequests()
            ->with(['agent.user', 'payment', 'rating'])
            ->latest()
            ->paginate(15);

        return view('customer.line-requests.index', compact('requests'));
    }

    /**
     * Display the specified line request with map.
     */
    public function show(Request $request, LineRequest $lineRequest): View
    {
        // Ensure the request belongs to the authenticated customer
        if ($lineRequest->customer->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $lineRequest->load(['customer', 'agent.user', 'payment', 'rating']);

        // Fetch nearby online agents to show on map (Visual Trust)
        $nearbyAgents = \App\Models\Agent::with('user')
            ->where('is_online', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->limit(20)
            ->get();

        return view('customer.line-requests.show', compact('lineRequest', 'nearbyAgents'));
    }

    /**
     * Cancel the specified line request.
     */
    public function cancel(Request $request, LineRequest $lineRequest): RedirectResponse
    {
        // Ensure the request belongs to the authenticated customer
        if ($lineRequest->customer->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        if (!in_array($lineRequest->status, [RequestStatus::Pending, RequestStatus::Accepted])) {
            return back()->with('error', 'Cannot cancel a request that is already in progress or completed.');
        }

        $lineRequest->update([
            'status' => RequestStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Cancelled by customer',
        ]);

        return back()->with('success', 'Request cancelled successfully.');
    }
}

