<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Booking;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Customer: Show bookings list
     */
    public function customerIndex(Request $request)
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            return redirect()->route('customer.dashboard')->with('error', 'Customer profile not found');
        }

        $bookings = Booking::where('customer_id', $customer->id)
            ->with(['agent.user'])
            ->orderByDesc('scheduled_date')
            ->paginate(10);

        $stats = [
            'total' => Booking::where('customer_id', $customer->id)->count(),
            'upcoming' => Booking::where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('scheduled_date', '>=', today())
                ->count(),
            'completed' => Booking::where('customer_id', $customer->id)
                ->where('status', 'completed')
                ->count(),
        ];

        return view('customer.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * Customer: Show create booking form
     */
    public function customerCreate()
    {
        $networks = ['vodacom', 'airtel', 'tigo', 'halotel', 'zantel'];
        $timeSlots = [
            'morning' => 'Asubuhi (8:00 - 12:00)',
            'afternoon' => 'Mchana (12:00 - 17:00)',
            'evening' => 'Jioni (17:00 - 20:00)',
        ];

        // Get minimum booking date (tomorrow)
        $minDate = now()->addDay()->format('Y-m-d');
        $maxDate = now()->addMonths(2)->format('Y-m-d');

        return view('customer.bookings.create', compact('networks', 'timeSlots', 'minDate', 'maxDate'));
    }

    /**
     * Customer: Store new booking
     */
    public function customerStore(Request $request)
    {
        $validated = $request->validate([
            'line_type' => 'required|in:vodacom,airtel,tigo,halotel,zantel',
            'scheduled_date' => 'required|date|after:today',
            'time_slot' => 'required|in:morning,afternoon,evening',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $customer = $request->user()->customer;

        if (!$customer) {
            return back()->with('error', 'Customer profile not found');
        }

        try {
            $booking = $this->bookingService->createBooking($validated, $customer);

            return redirect()->route('customer.bookings.show', $booking)
                ->with('success', 'Booking imeundwa kikamilifu! Utapata confirmation haraka.');
        } catch (\Exception $e) {
            return back()->with('error', 'Imeshindikana kuunda booking: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Customer: Show booking details
     */
    public function customerShow(Request $request, Booking $booking)
    {
        $customer = $request->user()->customer;

        if (!$customer || $booking->customer_id !== $customer->id) {
            abort(403, 'Unauthorized');
        }

        $booking->load(['agent.user', 'lineRequest']);

        return view('customer.bookings.show', compact('booking'));
    }

    /**
     * Customer: Cancel booking
     */
    public function customerCancel(Request $request, Booking $booking)
    {
        $customer = $request->user()->customer;

        if (!$customer || $booking->customer_id !== $customer->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $result = $this->bookingService->cancelBooking($booking, $request->reason, 'customer');

        if (!$result) {
            return back()->with('error', 'Haiwezekani kughairi booking hii.');
        }

        return redirect()->route('customer.bookings.index')
            ->with('success', 'Booking imeghairiwa.');
    }

    /**
     * Agent: Show pending and assigned bookings
     */
    public function agentIndex(Request $request)
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return redirect()->route('agent.dashboard')->with('error', 'Agent profile not found');
        }

        // Get pending bookings (unassigned or assigned to this agent)
        $pendingBookings = Booking::where(function ($q) use ($agent) {
            $q->whereNull('agent_id')
              ->orWhere('agent_id', $agent->id);
        })
            ->where('status', 'pending')
            ->where('scheduled_date', '>=', today())
            ->with(['customer.user'])
            ->orderBy('scheduled_date')
            ->get();

        // Get upcoming confirmed bookings for this agent
        $upcomingBookings = Booking::where('agent_id', $agent->id)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->where('scheduled_date', '>=', today())
            ->with(['customer.user'])
            ->orderBy('scheduled_date')
            ->get();

        // Get today's bookings
        $todayBookings = Booking::where('agent_id', $agent->id)
            ->whereDate('scheduled_date', today())
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with(['customer.user'])
            ->orderBy('scheduled_time')
            ->get();

        $stats = [
            'pending' => $pendingBookings->count(),
            'today' => $todayBookings->count(),
            'upcoming' => $upcomingBookings->count(),
            'completed' => Booking::where('agent_id', $agent->id)->where('status', 'completed')->count(),
        ];

        return view('agent.bookings.index', compact('pendingBookings', 'upcomingBookings', 'todayBookings', 'stats'));
    }

    /**
     * Agent: Show booking details
     */
    public function agentShow(Request $request, Booking $booking)
    {
        $agent = $request->user()->agent;

        // Agent can view unassigned bookings or their own
        if (!$agent || ($booking->agent_id && $booking->agent_id !== $agent->id)) {
            abort(403, 'Unauthorized');
        }

        $booking->load(['customer.user', 'lineRequest']);

        return view('agent.bookings.show', compact('booking'));
    }

    /**
     * Agent: Accept/Confirm booking
     */
    public function agentConfirm(Request $request, Booking $booking)
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return back()->with('error', 'Agent profile not found');
        }

        if (!$agent->is_verified) {
            return back()->with('error', 'Agent hajaverifyiwa');
        }

        $result = $this->bookingService->confirmBooking($booking, $agent);

        if (!$result) {
            return back()->with('error', 'Haiwezekani kukubali booking hii. Inaweza kuwa imekubaliwa au umeshika kazi nyingine wakati huohuo.');
        }

        return redirect()->route('agent.bookings.show', $booking)
            ->with('success', 'Umekubali booking kikamilifu!');
    }

    /**
     * Agent: Cancel booking
     */
    public function agentCancel(Request $request, Booking $booking)
    {
        $agent = $request->user()->agent;

        if (!$agent || $booking->agent_id !== $agent->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $result = $this->bookingService->cancelBooking($booking, $request->reason, 'agent');

        if (!$result) {
            return back()->with('error', 'Haiwezekani kughairi booking hii.');
        }

        return redirect()->route('agent.bookings.index')
            ->with('success', 'Booking imeghairiwa.');
    }
}
