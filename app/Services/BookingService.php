<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\LineRequest;
use App\RequestStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    protected InAppNotificationService $notificationService;
    protected AgentMatchingService $agentMatchingService;

    public function __construct(
        InAppNotificationService $notificationService,
        AgentMatchingService $agentMatchingService
    ) {
        $this->notificationService = $notificationService;
        $this->agentMatchingService = $agentMatchingService;
    }

    /**
     * Create a new booking
     */
    public function createBooking(array $data, Customer $customer): Booking
    {
        // Determine scheduled_time: use provided time, or generate from time_slot
        $scheduledTime = $data['scheduled_time'] ?? null;
        
        if (!$scheduledTime && isset($data['time_slot'])) {
            $scheduledTime = $this->getDefaultTimeFromSlot($data['time_slot']);
        }
        
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'line_type' => $data['line_type'],
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $scheduledTime,
            'time_slot' => $data['time_slot'] ?? null,
            'latitude' => $data['latitude'] ?? $customer->current_latitude,
            'longitude' => $data['longitude'] ?? $customer->current_longitude,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? $customer->user->phone,
            'notes' => $data['notes'] ?? null,
            'is_recurring' => $data['is_recurring'] ?? false,
            'recurrence_type' => $data['recurrence_type'] ?? null,
            'agent_id' => $data['preferred_agent_id'] ?? null,
        ]);

        // Send notifications to available agents
        $this->notifyAgentsAboutBooking($booking);

        // Notify customer
        $this->notificationService->send(
            $customer->user,
            'booking_created',
            'Booking Imepokelewa! 📅',
            "Booking yako #{$booking->booking_number} imesajiliwa kwa tarehe {$booking->scheduled_date->format('d M Y')}. Utaarifiwa agent atakapokubali.",
            ['booking_id' => $booking->id]
        );

        Log::info('Booking created', ['booking_id' => $booking->id, 'customer_id' => $customer->id]);

        return $booking;
    }

    /**
     * Notify available agents about a new booking
     */
    protected function notifyAgentsAboutBooking(Booking $booking): void
    {
        // If preferred agent is set, notify only them
        if ($booking->agent_id) {
            $agent = Agent::find($booking->agent_id);
            if ($agent) {
                $this->notificationService->send(
                    $agent->user,
                    'new_booking',
                    'Booking Mpya! 📅',
                    "Umepokea booking kwa tarehe {$booking->scheduled_date->format('d M Y')}. Bonyeza kukubali.",
                    ['booking_id' => $booking->id]
                );
            }
            return;
        }

        // Find nearby agents based on booking location
        $agents = Agent::where('is_verified', true)
            ->where('is_online', true)
            ->get();

        foreach ($agents as $agent) {
            $this->notificationService->send(
                $agent->user,
                'new_booking_available',
                'Booking Inapatikana! 📅',
                "Kuna booking mpya kwa tarehe {$booking->scheduled_date->format('d M Y')} eneo la {$booking->address}. Haraka kukubali!",
                ['booking_id' => $booking->id]
            );
        }
    }

    /**
     * Agent confirms/accepts a booking
     */
    public function confirmBooking(Booking $booking, Agent $agent): bool
    {
        if (!$booking->isPending()) {
            return false;
        }

        // Check if agent is available on that date/time
        if (!$this->isAgentAvailable($agent, $booking->scheduled_date, $booking->scheduled_time)) {
            return false;
        }

        DB::transaction(function () use ($booking, $agent) {
            $booking->confirm($agent->id);

            // Notify customer
            $this->notificationService->send(
                $booking->customer->user,
                'booking_confirmed',
                'Booking Imekubaliwa! ✅',
                "Agent {$agent->user->name} amekubali booking yako kwa tarehe {$booking->scheduled_date->format('d M Y')}.",
                ['booking_id' => $booking->id, 'agent_id' => $agent->id]
            );

            // Notify agent
            $this->notificationService->send(
                $agent->user,
                'booking_accepted',
                'Umekubali Booking! 📋',
                "Umekubali booking #{$booking->booking_number} kwa tarehe {$booking->scheduled_date->format('d M Y')}.",
                ['booking_id' => $booking->id]
            );
        });

        Log::info('Booking confirmed', ['booking_id' => $booking->id, 'agent_id' => $agent->id]);

        return true;
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(Booking $booking, string $reason, string $cancelledBy): bool
    {
        if (!$booking->canBeCancelled()) {
            return false;
        }

        $booking->cancel($reason, $cancelledBy);

        // Notify relevant parties
        if ($cancelledBy === 'customer' && $booking->agent_id) {
            $this->notificationService->send(
                $booking->agent->user,
                'booking_cancelled',
                'Booking Imeghairiwa 😔',
                "Customer ameghairi booking #{$booking->booking_number}. Sababu: {$reason}",
                ['booking_id' => $booking->id]
            );
        } elseif ($cancelledBy === 'agent') {
            $this->notificationService->send(
                $booking->customer->user,
                'booking_cancelled',
                'Booking Imeghairiwa 😔',
                "Agent ameghairi booking #{$booking->booking_number}. Sababu: {$reason}",
                ['booking_id' => $booking->id]
            );
        }

        Log::info('Booking cancelled', [
            'booking_id' => $booking->id,
            'cancelled_by' => $cancelledBy,
            'reason' => $reason,
        ]);

        return true;
    }

    /**
     * Convert booking to line request when the day comes
     */
    public function convertToLineRequest(Booking $booking): ?LineRequest
    {
        if ($booking->status !== 'confirmed' || !$booking->agent_id) {
            return null;
        }

        try {
            $lineRequest = DB::transaction(function () use ($booking) {
                $lineRequest = LineRequest::create([
                    'customer_id' => $booking->customer_id,
                    'agent_id' => $booking->agent_id,
                    'request_number' => 'LR' . now()->format('ymdHis') . rand(100, 999),
                    'line_type' => $booking->line_type,
                    'status' => RequestStatus::Accepted,
                    'customer_latitude' => $booking->latitude,
                    'customer_longitude' => $booking->longitude,
                    'customer_address' => $booking->address,
                    'customer_phone' => $booking->phone,
                    'accepted_at' => now(),
                ]);

                // Link booking to line request
                $booking->update([
                    'line_request_id' => $lineRequest->id,
                    'status' => 'in_progress',
                ]);

                return $lineRequest;
            });

            // Notify both parties
            $this->notificationService->send(
                $booking->customer->user,
                'booking_started',
                'Booking Yako Imeanza! 🚀',
                "Agent {$booking->agent->user->name} anaelekea kwako sasa. Jiandae!",
                ['booking_id' => $booking->id, 'line_request_id' => $lineRequest->id]
            );

            $this->notificationService->send(
                $booking->agent->user,
                'booking_start_reminder',
                'Booking ya Leo! ⏰',
                "Una booking sasa kwa customer {$booking->customer->user->name}. Elekea sasa!",
                ['booking_id' => $booking->id, 'line_request_id' => $lineRequest->id]
            );

            return $lineRequest;
        } catch (\Exception $e) {
            Log::error('Failed to convert booking to line request', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if agent is available on a specific date/time
     */
    public function isAgentAvailable(Agent $agent, $date, $time = null): bool
    {
        $query = Booking::where('agent_id', $agent->id)
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['confirmed', 'in_progress']);

        if ($time) {
            // Check if time overlaps (assuming 1 hour per booking)
            $startTime = Carbon::parse($time);
            $endTime = $startTime->copy()->addHour();

            $query->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('scheduled_time', [$startTime->format('H:i'), $endTime->format('H:i')]);
            });
        }

        return $query->count() === 0;
    }

    /**
     * Get upcoming bookings for an agent
     */
    public function getAgentUpcomingBookings(Agent $agent, int $days = 7): Collection
    {
        return Booking::where('agent_id', $agent->id)
            ->whereBetween('scheduled_date', [today(), today()->addDays($days)])
            ->whereIn('status', ['confirmed', 'pending'])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->with(['customer.user'])
            ->get();
    }

    /**
     * Get customer's bookings
     */
    public function getCustomerBookings(Customer $customer): Collection
    {
        return Booking::where('customer_id', $customer->id)
            ->orderByDesc('scheduled_date')
            ->with(['agent.user'])
            ->get();
    }

    /**
     * Get available agents for a specific date/time
     */
    public function getAvailableAgents($date, $time = null, $latitude = null, $longitude = null): Collection
    {
        $agents = Agent::where('is_verified', true)
            ->with('user:id,name,profile_picture')
            ->get();

        $available = $agents->filter(function ($agent) use ($date, $time) {
            return $this->isAgentAvailable($agent, $date, $time);
        });

        // If location is provided, calculate distances
        if ($latitude && $longitude) {
            $available = $available->map(function ($agent) use ($latitude, $longitude) {
                if ($agent->current_latitude && $agent->current_longitude) {
                    $agent->distance = LocationService::calculateDistanceStatic(
                        $latitude,
                        $longitude,
                        $agent->current_latitude,
                        $agent->current_longitude
                    );
                } else {
                    $agent->distance = null;
                }
                return $agent;
            })->sortBy('distance');
        }

        return $available->values();
    }

    /**
     * Send reminders for tomorrow's bookings
     */
    public function sendBookingReminders(): int
    {
        $bookings = Booking::where('scheduled_date', today()->addDay())
            ->where('status', 'confirmed')
            ->where('reminder_sent', false)
            ->with(['customer.user', 'agent.user'])
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            // Remind customer
            $this->notificationService->send(
                $booking->customer->user,
                'booking_reminder',
                'Kumbuka Booking Yako Kesho! ⏰',
                "Booking yako #{$booking->booking_number} itakuwa kesho saa {$booking->getTimeSlotLabel()}. Jiandae!",
                ['booking_id' => $booking->id]
            );

            // Remind agent
            $this->notificationService->send(
                $booking->agent->user,
                'booking_reminder',
                'Una Booking Kesho! ⏰',
                "Una booking #{$booking->booking_number} kesho saa {$booking->getTimeSlotLabel()}. Usisahau!",
                ['booking_id' => $booking->id]
            );

            $booking->update([
                'reminder_sent' => true,
                'reminder_sent_at' => now(),
            ]);

            $count++;
        }

        Log::info("Sent {$count} booking reminders");

        return $count;
    }

    /**
     * Expire pending bookings that weren't confirmed
     */
    public function expirePendingBookings(): int
    {
        $expired = Booking::where('status', 'pending')
            ->where('scheduled_date', '<', today())
            ->update(['status' => 'expired']);

        Log::info("Expired {$expired} pending bookings");

        return $expired;
    }

    /**
     * Get default time based on time slot
     */
    protected function getDefaultTimeFromSlot(string $timeSlot): ?string
    {
        return match ($timeSlot) {
            'morning' => '08:00',
            'afternoon' => '12:00',
            'evening' => '16:00',
            default => null,
        };
    }
}

