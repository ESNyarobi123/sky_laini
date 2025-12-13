@extends('layouts.dashboard')

@section('title', 'Bookings Calendar - SKY LAINI')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/main.min.css' rel='stylesheet' />
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    #calendar {
        --fc-border-color: rgba(255, 255, 255, 0.1);
        --fc-bg-event-opacity: 0.9;
        --fc-today-bg-color: rgba(245, 158, 11, 0.1);
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .fc-col-header-cell-cushion,
    .fc-daygrid-day-number {
        color: #9ca3af;
        text-decoration: none;
    }
    .fc-daygrid-day-number:hover {
        color: #f59e0b;
    }
    .fc-event {
        border: none;
        border-radius: 6px;
        padding: 2px 4px;
        font-size: 11px;
        font-weight: 600;
    }
    .fc-toolbar-title {
        color: white !important;
        font-weight: 800;
    }
    .fc-button-primary {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
    }
    .fc-button-primary:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }
    .fc-button-primary:disabled {
        opacity: 0.5;
    }
    .fc-day-today {
        background: rgba(245, 158, 11, 0.1) !important;
    }
    .fc-day-today .fc-daygrid-day-number {
        color: #f59e0b;
        font-weight: bold;
    }
    .fc-daygrid-event-harness {
        margin-bottom: 2px;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.bookings.index') }}" class="p-2 bg-white/5 rounded-xl hover:bg-white/10 transition">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-white mb-1">📅 Bookings Calendar</h1>
                <p class="text-gray-400">Visual overview of all scheduled bookings</p>
            </div>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-white/10 text-white font-bold rounded-xl hover:bg-white/20 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
            </svg>
            List View
        </a>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap gap-4 glass-card rounded-xl p-4">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-yellow-500"></div>
            <span class="text-gray-300 text-sm">Pending</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-blue-500"></div>
            <span class="text-gray-300 text-sm">Confirmed</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-purple-500"></div>
            <span class="text-gray-300 text-sm">In Progress</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-green-500"></div>
            <span class="text-gray-300 text-sm">Completed</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-red-500"></div>
            <span class="text-gray-300 text-sm">Cancelled</span>
        </div>
    </div>

    <!-- Calendar -->
    <div class="glass-card rounded-3xl p-6">
        <div id="calendar"></div>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="bookingModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-gray-900 rounded-3xl p-6 w-full max-w-md border border-white/10">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-white">Booking Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="bookingContent" class="space-y-4">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            events: @json($bookings->map(function($date, $bookingsList) {
                return collect($bookingsList)->map(function($booking) {
                    return [
                        'id' => $booking->id,
                        'title' => "#{$booking->booking_number} - " . ($booking->customer?->user?->name ?? 'Customer'),
                        'start' => $booking->scheduled_date->format('Y-m-d') . ($booking->scheduled_time ? 'T' . $booking->scheduled_time->format('H:i') : ''),
                        'color' => match ($booking->status) {
                            'pending' => '#eab308',
                            'confirmed' => '#3b82f6',
                            'in_progress' => '#a855f7',
                            'completed' => '#22c55e',
                            'cancelled' => '#ef4444',
                            default => '#6b7280',
                        },
                        'extendedProps' => [
                            'booking_number' => $booking->booking_number,
                            'customer' => $booking->customer?->user?->name ?? 'Unknown',
                            'agent' => $booking->agent?->user?->name ?? 'Unassigned',
                            'phone' => $booking->phone,
                            'line_type' => $booking->line_type,
                            'status' => $booking->status,
                            'time_slot' => $booking->getTimeSlotLabel(),
                            'address' => $booking->address,
                        ],
                    ];
                });
            })->flatten(1)),
            eventClick: function(info) {
                showBookingModal(info.event);
            },
            eventMouseEnter: function(info) {
                info.el.style.transform = 'scale(1.02)';
                info.el.style.cursor = 'pointer';
            },
            eventMouseLeave: function(info) {
                info.el.style.transform = 'scale(1)';
            },
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            height: 'auto',
        });
        
        calendar.render();
    });

    function showBookingModal(event) {
        const props = event.extendedProps;
        const statusColors = {
            'pending': 'bg-yellow-500/20 text-yellow-400',
            'confirmed': 'bg-blue-500/20 text-blue-400',
            'in_progress': 'bg-purple-500/20 text-purple-400',
            'completed': 'bg-green-500/20 text-green-400',
            'cancelled': 'bg-red-500/20 text-red-400',
        };

        document.getElementById('bookingContent').innerHTML = `
            <div class="p-4 bg-white/5 rounded-xl">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-2xl font-black text-white">#${props.booking_number}</span>
                    <span class="px-3 py-1 rounded-lg text-xs font-bold ${statusColors[props.status] || 'bg-gray-500/20 text-gray-400'}">
                        ${props.status.toUpperCase()}
                    </span>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Customer:</span>
                        <span class="text-white font-medium">${props.customer}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Phone:</span>
                        <span class="text-white font-medium">${props.phone || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Agent:</span>
                        <span class="text-white font-medium">${props.agent}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Line Type:</span>
                        <span class="text-white font-medium capitalize">${props.line_type}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Time:</span>
                        <span class="text-white font-medium">${props.time_slot}</span>
                    </div>
                    ${props.address ? `
                    <div class="flex justify-between">
                        <span class="text-gray-400">Address:</span>
                        <span class="text-white font-medium">${props.address}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
            <a href="/admin/bookings/${event.id}" class="block w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-black font-bold rounded-xl text-center hover:opacity-90 transition">
                View Full Details
            </a>
        `;

        document.getElementById('bookingModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('bookingModal').classList.add('hidden');
    }
</script>
@endpush
