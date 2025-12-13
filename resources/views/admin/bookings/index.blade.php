@extends('layouts.dashboard')

@section('title', 'Bookings Management - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(245, 158, 11, 0.3);
    }
    .booking-row {
        transition: all 0.2s ease;
    }
    .booking-row:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    .status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 8px;
        text-transform: uppercase;
    }
    .status-pending { background: rgba(234, 179, 8, 0.2); color: #eab308; }
    .status-confirmed { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
    .status-in_progress { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
    .status-completed { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    .status-cancelled { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    .status-expired { background: rgba(107, 114, 128, 0.2); color: #6b7280; }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white mb-1">📅 Advance Bookings</h1>
            <p class="text-gray-400">Manage scheduled line registration appointments</p>
        </div>
        <a href="{{ route('admin.bookings.calendar') }}" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-bold rounded-xl hover:opacity-90 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Calendar View
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="stat-card rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Today</div>
            <div class="text-2xl font-black text-white">{{ $stats['today'] }}</div>
        </div>
        <div class="stat-card rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Pending</div>
            <div class="text-2xl font-black text-yellow-400">{{ $stats['pending'] }}</div>
        </div>
        <div class="stat-card rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Confirmed</div>
            <div class="text-2xl font-black text-blue-400">{{ $stats['confirmed'] }}</div>
        </div>
        <div class="stat-card rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Completed</div>
            <div class="text-2xl font-black text-green-400">{{ $stats['completed'] }}</div>
        </div>
        <div class="stat-card rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Cancelled</div>
            <div class="text-2xl font-black text-red-400">{{ $stats['cancelled'] }}</div>
        </div>
        <div class="stat-card rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">This Week</div>
            <div class="text-2xl font-black text-purple-400">{{ $stats['upcoming_week'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap items-center gap-4">
            <div>
                <select name="status" class="bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <input type="date" name="date" value="{{ request('date') }}" 
                       class="bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-amber-500 text-black font-bold rounded-xl hover:bg-amber-400 transition">
                Filter
            </button>
            @if(request()->hasAny(['status', 'date']))
                <a href="{{ route('admin.bookings.index') }}" class="text-gray-400 hover:text-white text-sm">Clear filters</a>
            @endif
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-400 text-sm bg-white/5">
                        <th class="px-6 py-4 font-bold">Booking #</th>
                        <th class="px-6 py-4 font-bold">Customer</th>
                        <th class="px-6 py-4 font-bold">Date & Time</th>
                        <th class="px-6 py-4 font-bold">Line Type</th>
                        <th class="px-6 py-4 font-bold">Agent</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($bookings as $booking)
                        <tr class="booking-row">
                            <td class="px-6 py-4">
                                <span class="font-bold text-white">#{{ $booking->booking_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($booking->customer?->user?->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-white font-medium">{{ $booking->customer?->user?->name ?? 'Unknown' }}</div>
                                        <div class="text-gray-500 text-sm">{{ $booking->phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-white font-medium">{{ $booking->scheduled_date->format('d M Y') }}</div>
                                <div class="text-gray-400 text-sm">
                                    {{ $booking->scheduled_time ? $booking->scheduled_time->format('H:i') : $booking->getTimeSlotLabel() }}
                                </div>
                                @if($booking->isToday())
                                    <span class="text-xs px-2 py-0.5 bg-green-500/20 text-green-400 rounded">TODAY</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-white capitalize">{{ $booking->line_type }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($booking->agent)
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-black font-bold text-sm">
                                            {{ strtoupper(substr($booking->agent->user?->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-white text-sm font-medium">{{ $booking->agent->user?->name }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500 text-sm italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="status-badge status-{{ $booking->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" 
                                       class="p-2 bg-white/5 rounded-lg hover:bg-white/10 transition" title="View Details">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @if($booking->canBeCancelled())
                                        <button onclick="cancelBooking({{ $booking->id }})" 
                                                class="p-2 bg-red-500/20 rounded-lg hover:bg-red-500/30 transition" title="Cancel">
                                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No bookings found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $bookings->links() }}
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-gray-900 rounded-3xl p-6 w-full max-w-md border border-white/10">
        <h3 class="text-xl font-bold text-white mb-4">Cancel Booking</h3>
        <form id="cancelForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-400 text-sm font-bold mb-2">Reason for cancellation</label>
                <textarea name="reason" rows="3" required
                          class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white"
                          placeholder="Enter reason..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeCancelModal()" 
                        class="flex-1 py-3 bg-white/5 text-white font-bold rounded-xl hover:bg-white/10 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 transition">
                    Confirm Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function cancelBooking(bookingId) {
        document.getElementById('cancelForm').action = `/admin/bookings/${bookingId}/cancel`;
        document.getElementById('cancelModal').classList.remove('hidden');
    }
    
    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
