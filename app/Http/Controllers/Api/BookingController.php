<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Get customer's bookings
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        $bookings = $this->bookingService->getCustomerBookings($customer);

        return response()->json([
            'success' => true,
            'bookings' => $bookings->map(fn($b) => $this->formatBooking($b)),
        ]);
    }

    /**
     * Get upcoming bookings
     */
    public function upcoming(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isCustomer()) {
            $bookings = Booking::where('customer_id', $user->customer->id)
                ->upcoming()
                ->with(['agent.user'])
                ->orderBy('scheduled_date')
                ->get();
        } elseif ($user->isAgent()) {
            $bookings = $this->bookingService->getAgentUpcomingBookings($user->agent);
        } else {
            return response()->json(['message' => 'Invalid user type'], 400);
        }

        return response()->json([
            'success' => true,
            'bookings' => $bookings->map(fn($b) => $this->formatBooking($b)),
        ]);
    }

    /**
     * Create a new booking
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'line_type' => ['required', Rule::in(['vodacom', 'airtel', 'tigo', 'halotel', 'zantel'])],
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'time_slot' => ['nullable', Rule::in(['morning', 'afternoon', 'evening'])],
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
            'preferred_agent_id' => 'nullable|exists:agents,id',
            'is_recurring' => 'nullable|boolean',
            'recurrence_type' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        $customer = $request->user()->customer;

        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        $booking = $this->bookingService->createBooking($validated, $customer);

        return response()->json([
            'success' => true,
            'message' => 'Booking imeundwa kikamilifu!',
            'booking' => $this->formatBooking($booking),
        ], 201);
    }

    /**
     * Get booking details
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        // Check authorization
        if ($user->isCustomer() && $booking->customer_id !== $user->customer?->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->isAgent() && $booking->agent_id !== $user->agent?->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking->load(['customer.user', 'agent.user', 'lineRequest']);

        return response()->json([
            'success' => true,
            'booking' => $this->formatBooking($booking, true),
        ]);
    }

    /**
     * Agent confirms a booking
     */
    public function confirm(Request $request, Booking $booking): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        if (!$agent->is_verified) {
            return response()->json(['message' => 'Agent hajaverifyiwa'], 403);
        }

        $result = $this->bookingService->confirmBooking($booking, $agent);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Haiwezekani kukubali booking hii. Inaweza kuwa imekubaliwa au umeshika kazi nyingine wakati huohuo.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Umekubali booking kikamilifu!',
            'booking' => $this->formatBooking($booking->fresh()),
        ]);
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $cancelledBy = $user->isAgent() ? 'agent' : 'customer';

        // Check authorization
        if ($user->isCustomer() && $booking->customer_id !== $user->customer?->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->isAgent() && $booking->agent_id !== $user->agent?->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = $this->bookingService->cancelBooking($booking, $request->reason, $cancelledBy);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Haiwezekani kughairi booking hii.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking imeghairiwa.',
        ]);
    }

    /**
     * Get available agents for a date/time
     */
    public function availableAgents(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
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
            'success' => true,
            'agents' => $agents->map(function ($agent) {
                return [
                    'id' => $agent->id,
                    'name' => $agent->user?->name ?? 'Unknown',
                    'profile_picture' => $agent->user?->profile_picture,
                    'rating' => $agent->rating,
                    'total_completed' => $agent->completed_requests ?? 0,
                    'distance_km' => $agent->distance ? round($agent->distance, 1) : null,
                    'specialization' => $agent->specialization,
                ];
            }),
        ]);
    }

    /**
     * Agent's pending booking requests
     */
    public function pending(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        // Get pending bookings (either assigned to agent or unassigned)
        $bookings = Booking::where(function ($q) use ($agent) {
            $q->whereNull('agent_id')
              ->orWhere('agent_id', $agent->id);
        })
            ->pending()
            ->upcoming()
            ->with(['customer.user'])
            ->orderBy('scheduled_date')
            ->get();

        return response()->json([
            'success' => true,
            'bookings' => $bookings->map(fn($b) => $this->formatBooking($b)),
        ]);
    }

    /**
     * Get today's bookings for agent
     */
    public function today(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $bookings = Booking::where('agent_id', $agent->id)
            ->today()
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with(['customer.user'])
            ->orderBy('scheduled_time')
            ->get();

        return response()->json([
            'success' => true,
            'bookings' => $bookings->map(fn($b) => $this->formatBooking($b)),
            'count' => $bookings->count(),
        ]);
    }

    /**
     * Format booking for API response
     */
    protected function formatBooking(Booking $booking, bool $detailed = false): array
    {
        $data = [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'line_type' => $booking->line_type,
            'scheduled_date' => $booking->scheduled_date->format('Y-m-d'),
            'scheduled_date_formatted' => $booking->scheduled_date->format('d M Y'),
            'scheduled_time' => $booking->scheduled_time?->format('H:i'),
            'time_slot' => $booking->time_slot,
            'time_slot_label' => $booking->getTimeSlotLabel(),
            'status' => $booking->status,
            'status_color' => $booking->getStatusColor(),
            'is_today' => $booking->isToday(),
            'can_cancel' => $booking->canBeCancelled(),
            'created_at' => $booking->created_at->diffForHumans(),
        ];

        if ($booking->agent) {
            $data['agent'] = [
                'id' => $booking->agent->id,
                'name' => $booking->agent->user?->name,
                'phone' => $booking->agent->phone,
                'profile_picture' => $booking->agent->user?->profile_picture,
                'rating' => $booking->agent->rating,
            ];
        }

        if ($booking->customer && $detailed) {
            $data['customer'] = [
                'id' => $booking->customer->id,
                'name' => $booking->customer->user?->name,
                'phone' => $booking->phone,
            ];
        }

        if ($detailed) {
            $data['address'] = $booking->address;
            $data['latitude'] = $booking->latitude;
            $data['longitude'] = $booking->longitude;
            $data['notes'] = $booking->notes;
            $data['is_recurring'] = $booking->is_recurring;
            $data['recurrence_type'] = $booking->recurrence_type;
            $data['confirmed_at'] = $booking->agent_confirmed_at?->diffForHumans();

            if ($booking->lineRequest) {
                $data['line_request_id'] = $booking->lineRequest->id;
                $data['line_request_status'] = $booking->lineRequest->status->value ?? $booking->lineRequest->status;
            }
        }

        return $data;
    }
}
