<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
     * Display all bookings
     */
    public function index(Request $request)
    {
        $query = Booking::with(['customer.user', 'agent.user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        // Filter by date range
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('scheduled_date', [$request->from, $request->to]);
        }

        $bookings = $query->orderByDesc('scheduled_date')
            ->orderBy('scheduled_time')
            ->paginate(20);

        $stats = [
            'today' => Booking::whereDate('scheduled_date', today())->count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'upcoming_week' => Booking::whereBetween('scheduled_date', [today(), today()->addWeek()])
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * Calendar view of bookings
     */
    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $bookings = Booking::whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->with(['customer.user', 'agent.user'])
            ->get()
            ->groupBy(function ($booking) {
                return $booking->scheduled_date->format('Y-m-d');
            });

        return view('admin.bookings.calendar', compact('bookings', 'month', 'year', 'startOfMonth', 'endOfMonth'));
    }

    /**
     * Show booking details
     */
    public function show(Booking $booking)
    {
        $booking->load(['customer.user', 'agent.user', 'lineRequest']);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Cancel a booking (Admin action)
     */
    public function cancel(Request $request, Booking $booking)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $result = $this->bookingService->cancelBooking($booking, $request->reason, 'system');

        if (!$result) {
            return back()->with('error', 'Unable to cancel this booking.');
        }

        return back()->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Get bookings data for AJAX calendar
     */
    public function calendarData(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth());
        $end = $request->get('end', now()->endOfMonth());

        $bookings = Booking::whereBetween('scheduled_date', [$start, $end])
            ->with(['customer.user', 'agent.user'])
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'title' => "#{$booking->booking_number} - {$booking->customer?->user?->name}",
                    'start' => $booking->scheduled_date->format('Y-m-d') . 
                        ($booking->scheduled_time ? 'T' . $booking->scheduled_time->format('H:i') : ''),
                    'color' => match ($booking->status) {
                        'pending' => '#eab308',
                        'confirmed' => '#3b82f6',
                        'in_progress' => '#a855f7',
                        'completed' => '#22c55e',
                        'cancelled' => '#ef4444',
                        default => '#6b7280',
                    },
                    'extendedProps' => [
                        'status' => $booking->status,
                        'agent' => $booking->agent?->user?->name,
                        'line_type' => $booking->line_type,
                    ],
                ];
            });

        return response()->json($bookings);
    }
}
