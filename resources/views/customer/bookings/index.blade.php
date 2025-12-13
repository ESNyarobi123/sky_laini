@extends('layouts.dashboard')

@section('title', 'Booking Zangu - SKY LAINI')

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
        transform: translateY(-3px);
        border-color: rgba(245, 158, 11, 0.3);
    }
    .booking-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .booking-card:hover {
        border-color: rgba(245, 158, 11, 0.3);
        transform: translateX(5px);
    }
    .status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 12px;
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
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black text-white mb-2 tracking-tight flex items-center gap-3">
                <span class="text-5xl">📅</span> Booking Zangu
            </h1>
            <p class="text-gray-400 font-medium text-lg">Weka miadi ya usajili wa laini mapema</p>
        </div>
        <a href="{{ route('customer.bookings.create') }}" class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-black font-bold rounded-2xl hover:shadow-lg hover:shadow-amber-500/30 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Weka Booking Mpya
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-500/10 border border-green-500/30 rounded-2xl p-4 flex items-center gap-3">
        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-green-500 font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card rounded-2xl p-6 text-center">
            <div class="text-4xl mb-2">📋</div>
            <div class="text-3xl font-black text-white">{{ $stats['total'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Jumla</div>
        </div>
        <div class="stat-card rounded-2xl p-6 text-center">
            <div class="text-4xl mb-2">⏰</div>
            <div class="text-3xl font-black text-blue-400">{{ $stats['upcoming'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Zijazo</div>
        </div>
        <div class="stat-card rounded-2xl p-6 text-center">
            <div class="text-4xl mb-2">✅</div>
            <div class="text-3xl font-black text-green-400">{{ $stats['completed'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Zimekamilika</div>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="glass-card rounded-3xl p-6">
        <h2 class="text-xl font-bold text-white mb-6">Booking Zako</h2>

        @if($bookings->count() > 0)
        <div class="space-y-4">
            @foreach($bookings as $booking)
            <a href="{{ route('customer.bookings.show', $booking) }}" class="booking-card block rounded-2xl p-5 hover:bg-white/5">
                <div class="flex items-center gap-4">
                    <!-- Date Badge -->
                    <div class="w-16 h-16 rounded-2xl {{ $booking->isToday() ? 'bg-gradient-to-br from-amber-500 to-orange-500 text-black' : 'bg-white/10 text-white' }} flex flex-col items-center justify-center">
                        <span class="text-xs font-bold uppercase">{{ $booking->scheduled_date->format('M') }}</span>
                        <span class="text-2xl font-black">{{ $booking->scheduled_date->format('d') }}</span>
                    </div>

                    <!-- Details -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-1">
                            <span class="font-bold text-white">#{{ $booking->booking_number }}</span>
                            <span class="status-badge status-{{ $booking->status }}">
                                {{ $booking->status === 'in_progress' ? 'Inaendelea' : ($booking->status === 'pending' ? 'Inasubiri' : ($booking->status === 'confirmed' ? 'Imekubaliwa' : ($booking->status === 'completed' ? 'Imekamilika' : ($booking->status === 'cancelled' ? 'Imeghairiwa' : ucfirst($booking->status))))) }}
                            </span>
                            @if($booking->isToday())
                                <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs font-bold rounded">LEO!</span>
                            @endif
                        </div>
                        <div class="text-gray-400 text-sm flex flex-wrap items-center gap-3">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $booking->getTimeSlotLabel() }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                {{ ucfirst($booking->line_type) }}
                            </span>
                        </div>
                        @if($booking->agent)
                            <div class="mt-2 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-black text-xs font-bold">
                                    {{ strtoupper(substr($booking->agent->user?->name ?? 'A', 0, 1)) }}
                                </div>
                                <span class="text-green-400 text-sm font-medium">{{ $booking->agent->user?->name }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Arrow -->
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📅</div>
            <h4 class="text-white font-bold text-lg mb-2">Hakuna Booking Bado</h4>
            <p class="text-gray-400 max-w-sm mx-auto mb-6">Weka miadi ya usajili wa laini mapema ili kupata huduma haraka na rahisi!</p>
            <a href="{{ route('customer.bookings.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-black font-bold rounded-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Weka Booking ya Kwanza
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
