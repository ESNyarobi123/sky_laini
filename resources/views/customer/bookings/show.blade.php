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
    .timeline-item {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 24px;
        bottom: -14px;
        width: 2px;
        background: rgba(255, 255, 255, 0.1);
    }
    .timeline-item:last-child::before {
        display: none;
    }
    .timeline-dot {
        position: absolute;
        left: 0;
        top: 4px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('customer.bookings.index') }}" class="p-2 bg-white/5 rounded-xl hover:bg-white/10 transition">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-3xl font-black text-white mb-1">Booking #{{ $booking->booking_number }}</h1>
            <p class="text-gray-400">Imeundwa {{ $booking->created_at->diffForHumans() }}</p>
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

    <!-- Main Date Display -->
    <div class="glass-card rounded-3xl p-8 text-center {{ $booking->isToday() ? 'bg-gradient-to-br from-amber-500/10 to-orange-500/5 border-amber-500/30' : '' }}">
        @if($booking->isToday())
            <span class="inline-block px-4 py-2 bg-green-500/20 text-green-400 font-bold rounded-xl mb-4 animate-pulse">🎯 LEO!</span>
        @endif
        <div class="text-6xl font-black text-white mb-2">{{ $booking->scheduled_date->format('d') }}</div>
        <div class="text-2xl font-bold text-gray-400 mb-1">{{ $booking->scheduled_date->translatedFormat('F Y') }}</div>
        <div class="text-amber-500 font-bold text-lg">{{ $booking->getTimeSlotLabel() }}</div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Booking Details -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">📋</span> Maelezo ya Booking
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
                <div class="info-row">
                    <span class="text-gray-400">Simu</span>
                    <span class="text-white font-bold">{{ $booking->phone }}</span>
                </div>
                @if($booking->address)
                <div class="info-row">
                    <span class="text-gray-400">Mahali</span>
                    <span class="text-white font-medium text-right max-w-[60%]">{{ $booking->address }}</span>
                </div>
                @endif
                @if($booking->notes)
                <div class="info-row flex-col gap-2">
                    <span class="text-gray-400">Maelezo</span>
                    <span class="text-gray-300 text-sm">{{ $booking->notes }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Agent Info -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">🏍️</span> Agent
            </h2>
            
            @if($booking->agent)
                <div class="flex items-center gap-4 mb-4 p-4 bg-white/5 rounded-xl">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-black font-black text-2xl overflow-hidden">
                        @if($booking->agent->user?->profile_picture)
                            <img src="{{ route('profile.picture.view', $booking->agent->user->profile_picture) }}" alt="" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($booking->agent->user?->name ?? 'A', 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="text-white font-bold text-lg">{{ $booking->agent->user?->name ?? 'Unknown' }}</div>
                        <div class="text-amber-400 text-sm font-medium">⭐ {{ $booking->agent->rating ?? 0 }} rating</div>
                    </div>
                </div>

                @if($booking->agent_confirmed_at)
                    <div class="p-4 bg-green-500/10 rounded-xl border border-green-500/20">
                        <span class="text-green-400 font-bold">✓ Amekubali {{ $booking->agent_confirmed_at->diffForHumans() }}</span>
                    </div>
                @endif

                @if($booking->status === 'confirmed' || $booking->status === 'in_progress')
                    <div class="mt-4 p-4 bg-blue-500/10 rounded-xl border border-blue-500/20">
                        <div class="flex items-center gap-2 text-blue-400 font-bold mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            Wasiliana
                        </div>
                        <a href="tel:{{ $booking->agent->phone }}" class="text-white font-bold text-lg">{{ $booking->agent->phone }}</a>
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <div class="text-5xl mb-4">⏳</div>
                    <h4 class="text-white font-bold mb-2">Inasubiri Agent</h4>
                    <p class="text-gray-400 text-sm">Agents wetu wanaangalia booking yako. Utapata notification akikubali.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Timeline -->
    <div class="glass-card rounded-3xl p-6">
        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
            <span class="text-2xl">⏱️</span> Historia
        </h2>
        
        <div class="space-y-4">
            <div class="timeline-item">
                <div class="timeline-dot bg-green-500"></div>
                <div class="text-white font-medium">Booking Imeundwa</div>
                <div class="text-gray-500 text-sm">{{ $booking->created_at->format('d M Y, H:i') }}</div>
            </div>

            @if($booking->agent_confirmed_at)
            <div class="timeline-item">
                <div class="timeline-dot bg-blue-500"></div>
                <div class="text-white font-medium">Agent Amekubali</div>
                <div class="text-gray-500 text-sm">{{ $booking->agent_confirmed_at->format('d M Y, H:i') }}</div>
            </div>
            @endif

            @if($booking->completed_at)
            <div class="timeline-item">
                <div class="timeline-dot bg-green-500"></div>
                <div class="text-white font-medium">Imekamilika</div>
                <div class="text-gray-500 text-sm">{{ $booking->completed_at->format('d M Y, H:i') }}</div>
            </div>
            @endif

            @if($booking->cancelled_at)
            <div class="timeline-item">
                <div class="timeline-dot bg-red-500"></div>
                <div class="text-white font-medium">Imeghairiwa na {{ ucfirst($booking->cancelled_by) }}</div>
                <div class="text-gray-500 text-sm">{{ $booking->cancelled_at->format('d M Y, H:i') }}</div>
                @if($booking->cancellation_reason)
                    <div class="text-red-400 text-sm mt-1">Sababu: {{ $booking->cancellation_reason }}</div>
                @endif
            </div>
            @endif
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
            <a href="{{ route('customer.line-requests.show', $booking->lineRequest) }}" class="px-4 py-2 bg-amber-500 text-black font-bold rounded-xl hover:bg-amber-400 transition">
                Angalia
            </a>
        </div>
    </div>
    @endif

    <!-- Cancel Button -->
    @if($booking->canBeCancelled())
    <div class="text-center">
        <button onclick="document.getElementById('cancelModal').classList.remove('hidden')" 
                class="px-6 py-3 bg-red-500/20 text-red-400 font-bold rounded-xl hover:bg-red-500/30 transition border border-red-500/30">
            Ghairi Booking
        </button>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-gray-900 rounded-3xl p-6 w-full max-w-md border border-white/10 m-4">
            <h3 class="text-xl font-bold text-white mb-4">Ghairi Booking?</h3>
            <p class="text-gray-400 mb-4">Je, una uhakika unataka kughairi booking hii?</p>
            <form action="{{ route('customer.bookings.cancel', $booking) }}" method="POST">
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
</div>
@endsection
