@extends('layouts.dashboard')

@section('title', 'Booking Details - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .status-badge {
        font-size: 12px;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 10px;
        text-transform: uppercase;
    }
    .status-pending { background: rgba(234, 179, 8, 0.2); color: #eab308; }
    .status-confirmed { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
    .status-in_progress { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
    .status-completed { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    .status-cancelled { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .info-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.bookings.index') }}" class="p-2 bg-white/5 rounded-xl hover:bg-white/10 transition">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-3xl font-black text-white mb-1">Booking #{{ $booking->booking_number }}</h1>
            <p class="text-gray-400">Created {{ $booking->created_at->format('d M Y, H:i') }}</p>
        </div>
        <span class="status-badge status-{{ $booking->status }}">
            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Booking Info -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">📅</span> Booking Information
            </h2>
            
            <div>
                <div class="info-row">
                    <span class="text-gray-400">Scheduled Date</span>
                    <span class="text-white font-bold">{{ $booking->scheduled_date->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="text-gray-400">Time Slot</span>
                    <span class="text-white font-bold">{{ $booking->getTimeSlotLabel() }}</span>
                </div>
                <div class="info-row">
                    <span class="text-gray-400">Line Type</span>
                    <span class="text-white font-bold capitalize">{{ $booking->line_type }}</span>
                </div>
                <div class="info-row">
                    <span class="text-gray-400">Address</span>
                    <span class="text-white font-medium text-right max-w-[60%]">{{ $booking->address ?? 'Not specified' }}</span>
                </div>
                @if($booking->notes)
                <div class="info-row">
                    <span class="text-gray-400">Notes</span>
                    <span class="text-gray-300 text-sm text-right max-w-[60%]">{{ $booking->notes }}</span>
                </div>
                @endif
                @if($booking->is_recurring)
                <div class="info-row">
                    <span class="text-gray-400">Recurrence</span>
                    <span class="text-purple-400 font-bold capitalize">{{ $booking->recurrence_type }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Customer Info -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">👤</span> Customer
            </h2>
            
            @if($booking->customer)
                <div class="flex items-center gap-4 mb-4 p-4 bg-white/5 rounded-xl">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-2xl">
                        {{ strtoupper(substr($booking->customer->user?->name ?? 'C', 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-white font-bold text-lg">{{ $booking->customer->user?->name ?? 'Unknown' }}</div>
                        <div class="text-gray-400">{{ $booking->phone }}</div>
                    </div>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">Customer data unavailable</div>
            @endif
        </div>

        <!-- Agent Info -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">🏍️</span> Agent
            </h2>
            
            @if($booking->agent)
                <div class="flex items-center gap-4 mb-4 p-4 bg-white/5 rounded-xl">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-black font-black text-2xl">
                        {{ strtoupper(substr($booking->agent->user?->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="text-white font-bold text-lg">{{ $booking->agent->user?->name ?? 'Unknown' }}</div>
                        <div class="text-gray-400">{{ $booking->agent->phone }}</div>
                        <div class="text-amber-400 text-sm mt-1">⭐ {{ $booking->agent->rating ?? 0 }} rating</div>
                    </div>
                </div>
                @if($booking->agent_confirmed_at)
                    <div class="text-center p-3 bg-green-500/10 rounded-xl">
                        <span class="text-green-400 text-sm font-bold">✓ Confirmed {{ $booking->agent_confirmed_at->diffForHumans() }}</span>
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <div class="text-4xl mb-3">⏳</div>
                    <div class="text-gray-400">Waiting for agent to accept</div>
                </div>
            @endif
        </div>

        <!-- Timeline -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">⏱️</span> Timeline
            </h2>
            
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <div>
                        <div class="text-white font-medium">Booking Created</div>
                        <div class="text-gray-500 text-sm">{{ $booking->created_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
                
                @if($booking->agent_confirmed_at)
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <div>
                        <div class="text-white font-medium">Agent Confirmed</div>
                        <div class="text-gray-500 text-sm">{{ $booking->agent_confirmed_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
                @endif
                
                @if($booking->completed_at)
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <div>
                        <div class="text-white font-medium">Completed</div>
                        <div class="text-gray-500 text-sm">{{ $booking->completed_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
                @endif
                
                @if($booking->cancelled_at)
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div>
                        <div class="text-white font-medium">Cancelled by {{ ucfirst($booking->cancelled_by) }}</div>
                        <div class="text-gray-500 text-sm">{{ $booking->cancelled_at->format('d M Y, H:i') }}</div>
                        @if($booking->cancellation_reason)
                            <div class="text-red-400 text-sm mt-1">Reason: {{ $booking->cancellation_reason }}</div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Linked Line Request -->
    @if($booking->lineRequest)
    <div class="glass-card rounded-3xl p-6">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span class="text-2xl">🔗</span> Linked Line Request
        </h2>
        
        <div class="p-4 bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-xl border border-amber-500/20 flex items-center justify-between">
            <div>
                <div class="text-white font-bold">#{{ $booking->lineRequest->request_number }}</div>
                <div class="text-gray-400 text-sm">Status: {{ ucfirst($booking->lineRequest->status->value ?? $booking->lineRequest->status) }}</div>
            </div>
            <a href="{{ route('admin.orders.show', $booking->lineRequest) }}" class="px-4 py-2 bg-amber-500 text-black font-bold rounded-xl hover:bg-amber-400 transition">
                View Request
            </a>
        </div>
    </div>
    @endif

    <!-- Actions -->
    @if($booking->canBeCancelled())
    <div class="flex justify-end">
        <button onclick="document.getElementById('cancelModal').classList.remove('hidden')" 
                class="px-6 py-3 bg-red-500/20 text-red-400 font-bold rounded-xl hover:bg-red-500/30 transition border border-red-500/30">
            Cancel Booking
        </button>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-gray-900 rounded-3xl p-6 w-full max-w-md border border-white/10">
            <h3 class="text-xl font-bold text-white mb-4">Cancel Booking</h3>
            <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-400 text-sm font-bold mb-2">Reason for cancellation</label>
                    <textarea name="reason" rows="3" required
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white"
                              placeholder="Enter reason..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')" 
                            class="flex-1 py-3 bg-white/5 text-white font-bold rounded-xl hover:bg-white/10 transition">
                        Back
                    </button>
                    <button type="submit" class="flex-1 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 transition">
                        Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
