<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    // ==================== CUSTOMER ENDPOINTS ====================

    /**
     * Get customer's bookings
     */
    public function customerIndex(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        
        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        $bookings = Booking::where('customer_id', $customer->id)
            ->with(['agent.user:id,name,profile_picture'])
            ->orderByDesc('scheduled_date')
            ->get()
            ->map(fn($b) => $this->formatBooking($b));

        return response()->json([
            'bookings' => $bookings,
            'stats' => [
                'total' => $bookings->count(),
                'pending' => $bookings->where('status', 'pending')->count(),
                'confirmed' => $bookings->where('status', 'confirmed')->count(),
                'completed' => $bookings->where('status', 'completed')->count(),
            ]
        ]);
    }

    /**
     * Create a new booking
     */
    public function customerStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'line_type' => 'required|string',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'time_slot' => 'nullable|in:morning,afternoon,evening',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'preferred_agent_id' => 'nullable|exists:agents,id',
        ]);

        $customer = $request->user()->customer;
        
        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        // Use customer's saved location if not provided in request
        if (empty($validated['latitude']) || empty($validated['longitude'])) {
            $validated['latitude'] = $customer->current_latitude ?? $customer->default_latitude;
            $validated['longitude'] = $customer->current_longitude ?? $customer->default_longitude;
        }

        // Use customer's default address if not provided
        if (empty($validated['address'])) {
            $validated['address'] = $customer->default_address ?? $request->user()->address ?? null;
        }

        try {
            $booking = $this->bookingService->createBooking($validated, $customer);

            return response()->json([
                'message' => 'Booking imewekwa kikamilifu! Utaarifiwa agent atakapokubali.',
                'booking' => $this->formatBooking($booking),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Imeshindikana kuunda booking: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get single booking details for customer
     */
    public function customerShow(Request $request, Booking $booking): JsonResponse
    {
        $customer = $request->user()->customer;
        
        if (!$customer || $booking->customer_id !== $customer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'booking' => $this->formatBooking($booking->load(['agent.user', 'lineRequest'])),
        ]);
    }

    /**
     * Cancel a booking
     */
    public function customerCancel(Request $request, Booking $booking): JsonResponse
    {
        $customer = $request->user()->customer;
        
        if (!$customer || $booking->customer_id !== $customer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $success = $this->bookingService->cancelBooking($booking, $validated['reason'], 'customer');

        if ($success) {
            return response()->json(['message' => 'Booking imeghairiwa']);
        }

        return response()->json(['message' => 'Haiwezekani kughairi booking hii'], 400);
    }

    /**
     * Get available dates for booking
     */
    public function availableDates(Request $request): JsonResponse
    {
        // Return next 30 days
        $dates = collect();
        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i);
            $dates->push([
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->translatedFormat('l'),
                'display' => $date->format('d M Y'),
                'is_weekend' => $date->isWeekend(),
            ]);
        }

        return response()->json(['dates' => $dates]);
    }

    /**
     * Get available agents for a specific date
     */
    public function availableAgents(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $agents = $this->bookingService->getAvailableAgents(
            $request->date,
            $request->time,
            $request->latitude,
            $request->longitude
        );

        return response()->json([
            'agents' => $agents->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->user?->name,
                'profile_picture' => $a->user?->profile_picture,
                'rating' => $a->rating,
                'distance' => $a->distance ?? null,
            ]),
        ]);
    }

    // ==================== AGENT ENDPOINTS ====================

    /**
     * Get agent's bookings overview
     */
    public function agentIndex(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;
        
        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $pendingBookings = Booking::where('status', 'pending')
            ->whereNull('agent_id')
            ->with(['customer.user:id,name'])
            ->orderBy('scheduled_date')
            ->limit(20)
            ->get();

        $myBookings = Booking::where('agent_id', $agent->id)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with(['customer.user:id,name'])
            ->orderBy('scheduled_date')
            ->get();

        return response()->json([
            'pending' => $pendingBookings->map(fn($b) => $this->formatBooking($b)),
            'my_bookings' => $myBookings->map(fn($b) => $this->formatBooking($b)),
            'stats' => [
                'pending_count' => $pendingBookings->count(),
                'my_count' => $myBookings->count(),
                'today_count' => $myBookings->where('scheduled_date', today())->count(),
            ],
        ]);
    }

    /**
     * Get pending bookings for agent to accept
     */
    public function agentPending(Request $request): JsonResponse
    {
        $bookings = Booking::where('status', 'pending')
            ->whereNull('agent_id')
            ->with(['customer.user:id,name,profile_picture'])
            ->orderBy('scheduled_date')
            ->get()
            ->map(fn($b) => $this->formatBooking($b));

        return response()->json(['bookings' => $bookings]);
    }

    /**
     * Get upcoming confirmed bookings for agent
     */
    public function agentUpcoming(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;
        
        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $bookings = Booking::where('agent_id', $agent->id)
            ->where('scheduled_date', '>=', today())
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with(['customer.user:id,name,profile_picture'])
            ->orderBy('scheduled_date')
            ->get()
            ->map(fn($b) => $this->formatBooking($b));

        return response()->json(['bookings' => $bookings]);
    }

    /**
     * Get today's bookings for agent
     */
    public function agentToday(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;
        
        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $bookings = Booking::where('agent_id', $agent->id)
            ->whereDate('scheduled_date', today())
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with(['customer.user:id,name,profile_picture,phone'])
            ->orderBy('scheduled_time')
            ->get()
            ->map(fn($b) => $this->formatBooking($b, true));

        return response()->json([
            'bookings' => $bookings,
            'count' => $bookings->count(),
        ]);
    }

    /**
     * Get single booking details for agent
     */
    public function agentShow(Request $request, Booking $booking): JsonResponse
    {
        $agent = $request->user()->agent;
        
        // Agent can view if assigned OR if booking is pending (to accept)
        $canView = ($agent && $booking->agent_id === $agent->id) || 
                   ($booking->status === 'pending' && $booking->agent_id === null);

        if (!$canView) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'booking' => $this->formatBooking($booking->load(['customer.user', 'lineRequest']), true),
        ]);
    }

    /**
     * Confirm/accept a booking
     */
    public function agentConfirm(Request $request, Booking $booking): JsonResponse
    {
        $agent = $request->user()->agent;
        
        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        if (!$booking->isPending()) {
            return response()->json(['message' => 'Booking hii haiwezi kukubaliwa'], 400);
        }

        $success = $this->bookingService->confirmBooking($booking, $agent);

        if ($success) {
            return response()->json([
                'message' => 'Umekubali booking! Customer amearibiwa.',
                'booking' => $this->formatBooking($booking->fresh(['customer.user'])),
            ]);
        }

        return response()->json(['message' => 'Huna uwezo wa kukubali booking hii (labda una booking nyingine wakati huo)'], 400);
    }

    /**
     * Cancel a booking as agent
     */
    public function agentCancel(Request $request, Booking $booking): JsonResponse
    {
        $agent = $request->user()->agent;
        
        if (!$agent || $booking->agent_id !== $agent->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $success = $this->bookingService->cancelBooking($booking, $validated['reason'], 'agent');

        if ($success) {
            return response()->json(['message' => 'Booking imeghairiwa. Customer amearibiwa.']);
        }

        return response()->json(['message' => 'Haiwezekani kughairi booking hii'], 400);
    }

    // ==================== HELPERS ====================

    /**
     * Format booking for API response
     */
    private function formatBooking(Booking $booking, bool $includeContact = false): array
    {
        $data = [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'line_type' => $booking->line_type,
            'status' => $booking->status,
            'scheduled_date' => $booking->scheduled_date->format('Y-m-d'),
            'scheduled_date_display' => $booking->scheduled_date->format('d M Y'),
            'scheduled_time' => $booking->scheduled_time?->format('H:i'),
            'time_slot' => $booking->time_slot,
            'time_slot_label' => $booking->getTimeSlotLabel(),
            'address' => $booking->address,
            'notes' => $booking->notes,
            'is_today' => $booking->isToday(),
            'can_cancel' => $booking->canBeCancelled(),
            'created_at' => $booking->created_at->diffForHumans(),
        ];

        // Customer info
        if ($booking->customer) {
            $data['customer'] = [
                'id' => $booking->customer->id,
                'name' => $booking->customer->user?->name ?? 'Customer',
                'profile_picture' => $booking->customer->user?->profile_picture,
            ];
            if ($includeContact) {
                $data['customer']['phone'] = $booking->phone;
            }
        }

        // Agent info
        if ($booking->agent) {
            $data['agent'] = [
                'id' => $booking->agent->id,
                'name' => $booking->agent->user?->name ?? 'Agent',
                'profile_picture' => $booking->agent->user?->profile_picture,
                'rating' => $booking->agent->rating,
            ];
            if ($includeContact) {
                $data['agent']['phone'] = $booking->agent->phone;
            }
        }

        // Location
        if ($booking->latitude && $booking->longitude) {
            $data['location'] = [
                'latitude' => (float) $booking->latitude,
                'longitude' => (float) $booking->longitude,
            ];
        }

        // Linked line request
        if ($booking->lineRequest) {
            $data['line_request'] = [
                'id' => $booking->lineRequest->id,
                'request_number' => $booking->lineRequest->request_number,
                'status' => $booking->lineRequest->status->value ?? $booking->lineRequest->status,
            ];
        }

        return $data;
    }
}
