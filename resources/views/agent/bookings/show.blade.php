@extends('layouts.dashboard')

@section('title', 'Booking #' . $booking->booking_number . ' - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .status-badge {
        font-size: 14px;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 12px;
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
        padding: 14px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .info-row:last-child { border-bottom: none; }
    .action-btn {
        padding: 16px 32px;
        font-weight: 700;
        font-size: 16px;
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .action-btn:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('agent.bookings.index') }}" class="p-2 bg-white/5 rounded-xl hover:bg-white/10 transition">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-3xl font-black text-white mb-1">Booking #{{ $booking->booking_number }}</h1>
            <p class="text-gray-400">{{ $booking->created_at->diffForHumans() }}</p>
        </div>
        <span class="status-badge status-{{ $booking->status }}">
            @switch($booking->status)
                @case('pending') Inasubiri @break
                @case('confirmed') Imekubaliwa @break
                @case('in_progress') Inaendelea @break
                @case('completed') Imekamilika @break
                @case('cancelled') Imeghairiwa @break
                @default {{ ucfirst($booking->status) }}
            @endswitch
        </span>
    </div>

    @if(session('success'))
    <div class="bg-green-500/10 border border-green-500/30 rounded-2xl p-4 flex items-center gap-3">
        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-green-500 font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-4 flex items-center gap-3">
        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-red-500 font-bold">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Main Date Display -->
    <div class="glass-card rounded-3xl p-8 text-center {{ $booking->isToday() ? 'bg-gradient-to-br from-amber-500/10 to-orange-500/5 border-amber-500/30' : '' }}">
        @if($booking->isToday())
            <span class="inline-block px-4 py-2 bg-green-500/20 text-green-400 font-bold rounded-xl mb-4 animate-pulse">🎯 LEO NI LEO!</span>
        @endif
        <div class="text-6xl font-black text-white mb-2">{{ $booking->scheduled_date->format('d') }}</div>
        <div class="text-2xl font-bold text-gray-400 mb-1">{{ $booking->scheduled_date->translatedFormat('F Y') }}</div>
        <div class="text-amber-500 font-bold text-lg">{{ $booking->getTimeSlotLabel() }}</div>
    </div>

    <!-- Accept Button (for pending unassigned bookings) -->
    @if($booking->status === 'pending' && !$booking->agent_id)
    <div class="glass-card rounded-3xl p-6 bg-gradient-to-r from-green-500/10 to-emerald-500/5 border-green-500/30">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-white font-bold text-lg mb-1">Kubali Booking Hii?</h3>
                <p class="text-gray-400 text-sm">Unapoikubali, customer ataaribiwa na utaipewa kazi hii.</p>
            </div>
            <form action="{{ route('agent.bookings.confirm', $booking) }}" method="POST">
                @csrf
                <button type="submit" class="action-btn bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg shadow-green-500/30 hover:shadow-green-500/50">
                    ✓ Kubali Booking
                </button>
            </form>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Customer Details -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">👤</span> Customer
            </h2>
            
            @if($booking->customer)
                <div class="flex items-center gap-4 mb-4 p-4 bg-white/5 rounded-xl">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-2xl overflow-hidden">
                        @if($booking->customer->user?->profile_picture)
                            <img src="{{ route('profile.picture.view', $booking->customer->user->profile_picture) }}" alt="" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($booking->customer->user?->name ?? 'C', 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="text-white font-bold text-lg">{{ $booking->customer->user?->name ?? 'Unknown' }}</div>
                        <div class="text-gray-400 text-sm">Customer</div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="space-y-3">
                    <a href="tel:{{ $booking->phone }}" class="flex items-center gap-3 p-4 bg-green-500/10 rounded-xl border border-green-500/20 hover:bg-green-500/20 transition">
                        <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <div>
                            <div class="text-green-400 font-bold text-sm">Piga Simu</div>
                            <div class="text-white font-bold">{{ $booking->phone }}</div>
                        </div>
                    </a>

                    @if($booking->address)
                    <div class="flex items-start gap-3 p-4 bg-blue-500/10 rounded-xl border border-blue-500/20">
                        <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <div class="text-blue-400 font-bold text-sm">Mahali</div>
                            <div class="text-white">{{ $booking->address }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8 text-gray-500">Customer data unavailable</div>
            @endif
        </div>

        <!-- Booking Details -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">📋</span> Maelezo
            </h2>
            
            <div>
                <div class="info-row">
                    <span class="text-gray-400">Mtandao</span>
                    <span class="text-white font-bold capitalize">{{ $booking->line_type }}</span>
                </div>
                <div class="info-row">
                    <span class="text-gray-400">Tarehe</span>
                    <span class="text-white font-bold">{{ $booking->scheduled_date->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="text-gray-400">Muda</span>
                    <span class="text-white font-bold">{{ $booking->getTimeSlotLabel() }}</span>
                </div>
                @if($booking->notes)
                <div class="pt-4 border-t border-white/5 mt-4">
                    <span class="text-gray-400 block mb-2">Maelezo:</span>
                    <p class="text-gray-300 text-sm bg-white/5 rounded-xl p-3">{{ $booking->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Linked Line Request -->
    @if($booking->lineRequest)
    <div class="glass-card rounded-3xl p-6">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span class="text-2xl">🔗</span> Line Request
        </h2>
        
        <div class="p-4 bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-xl border border-amber-500/20 flex items-center justify-between">
            <div>
                <div class="text-white font-bold">#{{ $booking->lineRequest->request_number }}</div>
                <div class="text-gray-400 text-sm">Status: {{ ucfirst($booking->lineRequest->status->value ?? $booking->lineRequest->status) }}</div>
            </div>
            <a href="{{ route('agent.requests.show', $booking->lineRequest) }}" class="px-4 py-2 bg-amber-500 text-black font-bold rounded-xl hover:bg-amber-400 transition">
                Angalia Request
            </a>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    @if($booking->agent_id === auth()->user()->agent?->id)
        @if($booking->status === 'confirmed')
        <div class="flex gap-4">
            <button onclick="document.getElementById('cancelModal').classList.remove('hidden')" 
                    class="flex-1 action-btn bg-red-500/20 text-red-400 border border-red-500/30 hover:bg-red-500 hover:text-white">
                Ghairi Booking
            </button>
        </div>
        @endif
    @endif
</div>

<!-- Cancel Modal -->
@if($booking->agent_id === auth()->user()->agent?->id && $booking->canBeCancelled())
<div id="cancelModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-gray-900 rounded-3xl p-6 w-full max-w-md border border-white/10 m-4">
        <h3 class="text-xl font-bold text-white mb-4">Ghairi Booking?</h3>
        <p class="text-gray-400 mb-4">Je, una uhakika unataka kughairi booking hii? Customer ataaribiwa.</p>
        <form action="{{ route('agent.bookings.cancel', $booking) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-400 text-sm font-bold mb-2">Sababu</label>
                <textarea name="reason" rows="3" required
                          class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white"
                          placeholder="Eleza sababu ya kughairi..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')" 
                        class="flex-1 py-3 bg-white/5 text-white font-bold rounded-xl hover:bg-white/10 transition">
                    Hapana
                </button>
                <button type="submit" class="flex-1 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 transition">
                    Ndio, Ghairi
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
