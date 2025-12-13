<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentLocation;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\LineRequest;
use App\RequestStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveMonitoringController extends Controller
{
    /**
     * Show the live monitoring dashboard
     */
    public function index()
    {
        // Get initial stats
        $stats = $this->getStats();

        return view('admin.monitoring.index', compact('stats'));
    }

    /**
     * Get real-time data for the map
     */
    public function liveData(Request $request): JsonResponse
    {
        $data = [
            'agents' => $this->getAgentsData(),
            'customers' => $this->getCustomersWithActiveRequests(),
            'all_customers' => $this->getAllCustomersWithLocation(),
            'active_requests' => $this->getActiveRequestsData(),
            'bookings_today' => $this->getTodayBookings(),
            'stats' => $this->getStats(),
            'timestamp' => now()->toIso8601String(),
        ];

        return response()->json($data);
    }

    /**
     * Get all agents with their current locations
     */
    protected function getAgentsData(): array
    {
        return Agent::with(['user:id,name,profile_picture,phone'])
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get()
            ->map(function ($agent) {
                $activeRequest = LineRequest::where('agent_id', $agent->id)
                    ->whereIn('status', [RequestStatus::Accepted, RequestStatus::InProgress])
                    ->with(['customer.user:id,name'])
                    ->first();

                return [
                    'id' => $agent->id,
                    'name' => $agent->user?->name ?? 'Unknown',
                    'phone' => $agent->phone ?? $agent->user?->phone,
                    'profile_picture' => $agent->user?->profile_picture,
                    'latitude' => (float) $agent->current_latitude,
                    'longitude' => (float) $agent->current_longitude,
                    'is_online' => $agent->is_online,
                    'is_verified' => $agent->is_verified,
                    'rating' => $agent->rating,
                    'total_completed' => $agent->completed_requests ?? 0,
                    'last_location_update' => $agent->updated_at?->diffForHumans(),
                    'has_active_request' => $activeRequest !== null,
                    'active_request' => $activeRequest ? [
                        'id' => $activeRequest->id,
                        'request_number' => $activeRequest->request_number,
                        'customer_name' => $activeRequest->customer?->user?->name,
                        'status' => $activeRequest->status->value ?? $activeRequest->status,
                        'destination' => [
                            'latitude' => (float) $activeRequest->customer_latitude,
                            'longitude' => (float) $activeRequest->customer_longitude,
                            'address' => $activeRequest->customer_address,
                        ],
                    ] : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get customers with active requests
     */
    protected function getCustomersWithActiveRequests(): array
    {
        return LineRequest::whereIn('status', [
            RequestStatus::Pending,
            RequestStatus::Accepted,
            RequestStatus::InProgress,
        ])
            ->with(['customer.user:id,name,profile_picture,phone', 'agent.user:id,name'])
            ->whereNotNull('customer_latitude')
            ->whereNotNull('customer_longitude')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->customer?->id,
                    'name' => $request->customer?->user?->name ?? 'Unknown Customer',
                    'phone' => $request->customer_phone,
                    'latitude' => (float) $request->customer_latitude,
                    'longitude' => (float) $request->customer_longitude,
                    'address' => $request->customer_address,
                    'request_id' => $request->id,
                    'request_number' => $request->request_number,
                    'line_type' => $request->line_type->value ?? $request->line_type,
                    'status' => $request->status->value ?? $request->status,
                    'has_agent' => $request->agent_id !== null,
                    'agent_name' => $request->agent?->user?->name,
                    'agent_id' => $request->agent_id,
                    'created_at' => $request->created_at->diffForHumans(),
                    'payment_status' => $request->payment_status ?? 'pending',
                ];
            })
            ->toArray();
    }

    /**
     * Get ALL customers with location (for showing on admin map)
     */
    protected function getAllCustomersWithLocation(): array
    {
        return Customer::with(['user:id,name,phone,profile_picture'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($customer) {
                // Check if customer has active request
                $activeRequest = LineRequest::where('customer_id', $customer->id)
                    ->whereIn('status', [
                        RequestStatus::Pending,
                        RequestStatus::Accepted,
                        RequestStatus::InProgress,
                    ])
                    ->first();

                // Get last completed request date
                $lastRequest = LineRequest::where('customer_id', $customer->id)
                    ->where('status', RequestStatus::Completed)
                    ->orderByDesc('completed_at')
                    ->first();

                return [
                    'id' => $customer->id,
                    'name' => $customer->user?->name ?? 'Customer',
                    'phone' => $customer->phone ?? $customer->user?->phone,
                    'profile_picture' => $customer->user?->profile_picture,
                    'latitude' => (float) $customer->latitude,
                    'longitude' => (float) $customer->longitude,
                    'has_active_request' => $activeRequest !== null,
                    'active_request_id' => $activeRequest?->id,
                    'active_request_status' => $activeRequest?->status->value ?? $activeRequest?->status,
                    'last_request_at' => $lastRequest?->completed_at?->diffForHumans(),
                    'total_requests' => LineRequest::where('customer_id', $customer->id)->count(),
                    'location_updated_at' => $customer->updated_at?->diffForHumans(),
                ];
            })
            ->toArray();
    }

    /**
     * Get active requests with route data
     */
    protected function getActiveRequestsData(): array
    {
        return LineRequest::whereIn('status', [RequestStatus::Accepted, RequestStatus::InProgress])
            ->with(['customer.user', 'agent.user'])
            ->whereNotNull('agent_id')
            ->get()
            ->map(function ($request) {
                $agent = $request->agent;

                return [
                    'id' => $request->id,
                    'request_number' => $request->request_number,
                    'status' => $request->status->value ?? $request->status,
                    'line_type' => $request->line_type->value ?? $request->line_type,
                    'payment_status' => $request->payment_status ?? 'pending',
                    
                    // Agent location (start point)
                    'agent' => [
                        'id' => $agent->id,
                        'name' => $agent->user?->name,
                        'phone' => $agent->phone,
                        'latitude' => (float) $agent->current_latitude,
                        'longitude' => (float) $agent->current_longitude,
                    ],
                    
                    // Customer location (end point)
                    'customer' => [
                        'id' => $request->customer?->id,
                        'name' => $request->customer?->user?->name,
                        'phone' => $request->customer_phone,
                        'latitude' => (float) $request->customer_latitude,
                        'longitude' => (float) $request->customer_longitude,
                        'address' => $request->customer_address,
                    ],
                    
                    // Route for drawing line on map
                    'route' => [
                        'start' => [
                            'latitude' => (float) $agent->current_latitude,
                            'longitude' => (float) $agent->current_longitude,
                        ],
                        'end' => [
                            'latitude' => (float) $request->customer_latitude,
                            'longitude' => (float) $request->customer_longitude,
                        ],
                    ],
                    
                    'distance_km' => $this->calculateDistance(
                        $agent->current_latitude,
                        $agent->current_longitude,
                        $request->customer_latitude,
                        $request->customer_longitude
                    ),
                    
                    'accepted_at' => $request->accepted_at?->diffForHumans(),
                    'created_at' => $request->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    /**
     * Get today's bookings with locations
     */
    protected function getTodayBookings(): array
    {
        return Booking::whereDate('scheduled_date', today())
            ->whereIn('status', ['confirmed', 'pending', 'in_progress'])
            ->with(['customer.user', 'agent.user'])
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'scheduled_time' => $booking->scheduled_time?->format('H:i'),
                    'time_slot' => $booking->getTimeSlotLabel(),
                    'line_type' => $booking->line_type,
                    'customer' => [
                        'name' => $booking->customer?->user?->name,
                        'phone' => $booking->phone,
                    ],
                    'agent' => $booking->agent ? [
                        'name' => $booking->agent->user?->name,
                        'phone' => $booking->agent->phone,
                    ] : null,
                    'location' => [
                        'latitude' => (float) $booking->latitude,
                        'longitude' => (float) $booking->longitude,
                        'address' => $booking->address,
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Get overall stats
     */
    protected function getStats(): array
    {
        $now = now();
        $today = today();

        return [
            'total_agents' => Agent::count(),
            'online_agents' => Agent::where('is_online', true)->count(),
            'verified_agents' => Agent::where('is_verified', true)->count(),
            
            'pending_requests' => LineRequest::where('status', RequestStatus::Pending)->count(),
            'active_requests' => LineRequest::whereIn('status', [RequestStatus::Accepted, RequestStatus::InProgress])->count(),
            'completed_today' => LineRequest::where('status', RequestStatus::Completed)
                ->whereDate('completed_at', $today)
                ->count(),
            
            'total_requests_today' => LineRequest::whereDate('created_at', $today)->count(),
            
            'bookings_today' => Booking::whereDate('scheduled_date', $today)->count(),
            'confirmed_bookings' => Booking::whereDate('scheduled_date', $today)
                ->where('status', 'confirmed')
                ->count(),
            
            'revenue_today' => LineRequest::where('payment_status', 'paid')
                ->whereDate('created_at', $today)
                ->sum('service_fee'),
        ];
    }

    /**
     * Get agent location history (trail on map)
     */
    public function agentLocationHistory(Request $request, Agent $agent): JsonResponse
    {
        $hours = $request->get('hours', 2);

        $locations = AgentLocation::where('agent_id', $agent->id)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($loc) {
                return [
                    'latitude' => (float) $loc->latitude,
                    'longitude' => (float) $loc->longitude,
                    'timestamp' => $loc->created_at->toIso8601String(),
                    'time' => $loc->created_at->format('H:i'),
                ];
            });

        return response()->json([
            'agent_id' => $agent->id,
            'agent_name' => $agent->user?->name,
            'locations' => $locations,
            'count' => $locations->count(),
        ]);
    }

    /**
     * Get specific request details with full route info
     */
    public function requestDetails(Request $request, LineRequest $lineRequest): JsonResponse
    {
        $lineRequest->load(['customer.user', 'agent.user', 'payment', 'rating', 'chatMessages']);

        $data = [
            'id' => $lineRequest->id,
            'request_number' => $lineRequest->request_number,
            'status' => $lineRequest->status->value ?? $lineRequest->status,
            'line_type' => $lineRequest->line_type->value ?? $lineRequest->line_type,
            'payment_status' => $lineRequest->payment_status ?? 'pending',
            'service_fee' => $lineRequest->service_fee,
            'commission' => $lineRequest->commission,
            
            'customer' => [
                'id' => $lineRequest->customer?->id,
                'name' => $lineRequest->customer?->user?->name,
                'phone' => $lineRequest->customer_phone,
                'address' => $lineRequest->customer_address,
                'location' => [
                    'latitude' => (float) $lineRequest->customer_latitude,
                    'longitude' => (float) $lineRequest->customer_longitude,
                ],
            ],
            
            'agent' => $lineRequest->agent ? [
                'id' => $lineRequest->agent->id,
                'name' => $lineRequest->agent->user?->name,
                'phone' => $lineRequest->agent->phone,
                'rating' => $lineRequest->agent->rating,
                'location' => [
                    'latitude' => (float) $lineRequest->agent->current_latitude,
                    'longitude' => (float) $lineRequest->agent->current_longitude,
                ],
            ] : null,
            
            'timeline' => [
                'created_at' => $lineRequest->created_at->format('Y-m-d H:i:s'),
                'accepted_at' => $lineRequest->accepted_at?->format('Y-m-d H:i:s'),
                'completed_at' => $lineRequest->completed_at?->format('Y-m-d H:i:s'),
                'cancelled_at' => $lineRequest->cancelled_at?->format('Y-m-d H:i:s'),
            ],
            
            'messages_count' => $lineRequest->chatMessages->count(),
            'has_rating' => $lineRequest->rating !== null,
            'rating_value' => $lineRequest->rating?->rating,
        ];

        return response()->json($data);
    }

    /**
     * Calculate distance between two points
     */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2): ?float
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            return null;
        }

        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Get heatmap data (areas with high demand)
     */
    public function heatmapData(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);

        $requests = LineRequest::where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('customer_latitude')
            ->whereNotNull('customer_longitude')
            ->select('customer_latitude', 'customer_longitude')
            ->get()
            ->map(function ($r) {
                return [
                    'lat' => (float) $r->customer_latitude,
                    'lng' => (float) $r->customer_longitude,
                    'weight' => 1,
                ];
            });

        return response()->json([
            'heatmap_data' => $requests,
            'count' => $requests->count(),
            'period_days' => $days,
        ]);
    }
}
