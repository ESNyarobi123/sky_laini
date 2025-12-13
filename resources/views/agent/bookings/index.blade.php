@extends('layouts.dashboard')

@section('title', 'Booking - SKY LAINI')

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
    .booking-card.today {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(168, 85, 247, 0.05));
        border-color: rgba(245, 158, 11, 0.3);
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
    .tab-btn {
        padding: 12px 24px;
        font-weight: 700;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .tab-btn.active {
        background: rgba(245, 158, 11, 0.2);
        color: #f59e0b;
    }
    .tab-btn:not(.active) {
        color: #9ca3af;
    }
    .tab-btn:not(.active):hover {
        background: rgba(255, 255, 255, 0.05);
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black text-white mb-2 tracking-tight flex items-center gap-3">
                <span class="text-5xl">📅</span> Booking
            </h1>
            <p class="text-gray-400 font-medium text-lg">Manage advance booking requests</p>
        </div>
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

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4">
        <div class="stat-card rounded-2xl p-5 text-center">
            <div class="text-3xl mb-2">⏳</div>
            <div class="text-3xl font-black text-yellow-400">{{ $stats['pending'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Zinasubiri</div>
        </div>
        <div class="stat-card rounded-2xl p-5 text-center {{ $stats['today'] > 0 ? 'ring-2 ring-amber-500/50' : '' }}">
            <div class="text-3xl mb-2">📍</div>
            <div class="text-3xl font-black text-amber-400">{{ $stats['today'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Leo</div>
        </div>
        <div class="stat-card rounded-2xl p-5 text-center">
            <div class="text-3xl mb-2">📆</div>
            <div class="text-3xl font-black text-blue-400">{{ $stats['upcoming'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Zijazo</div>
        </div>
        <div class="stat-card rounded-2xl p-5 text-center">
            <div class="text-3xl mb-2">✅</div>
            <div class="text-3xl font-black text-green-400">{{ $stats['completed'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Zimekamilika</div>
        </div>
    </div>

    <!-- Today's Bookings Alert -->
    @if($todayBookings->count() > 0)
    <div class="glass-card rounded-3xl p-6 bg-gradient-to-r from-amber-500/10 to-orange-500/5 border-amber-500/30">
        <h3 class="text-xl font-bold text-amber-400 mb-4 flex items-center gap-2">
            <span class="text-2xl animate-pulse">🔔</span> Booking za Leo ({{ $todayBookings->count() }})
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($todayBookings as $booking)
            <a href="{{ route('agent.bookings.show', $booking) }}" class="flex items-center gap-4 p-4 bg-black/30 rounded-xl hover:bg-black/40 transition">
                <div class="w-12 h-12 rounded-xl bg-amber-500 text-black flex items-center justify-center font-bold">
                    {{ $booking->scheduled_time ? $booking->scheduled_time->format('H') : '?' }}:{{ $booking->scheduled_time ? $booking->scheduled_time->format('i') : '??' }}
                </div>
                <div class="flex-1">
                    <div class="text-white font-bold">{{ $booking->customer?->user?->name ?? 'Customer' }}</div>
                    <div class="text-gray-400 text-sm">{{ ucfirst($booking->line_type) }} • {{ $booking->getTimeSlotLabel() }}</div>
                </div>
                <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Tabs -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="flex border-b border-white/10 px-4 pt-4">
            <button class="tab-btn active" onclick="showTab('pending')">
                ⏳ Zinasubiri
                @if($pendingBookings->count() > 0)
                    <span class="ml-2 px-2 py-0.5 bg-yellow-500 text-black text-xs font-bold rounded">{{ $pendingBookings->count() }}</span>
                @endif
            </button>
            <button class="tab-btn" onclick="showTab('upcoming')">
                📆 Zijazo
                @if($upcomingBookings->count() > 0)
                    <span class="ml-2 px-2 py-0.5 bg-blue-500 text-white text-xs font-bold rounded">{{ $upcomingBookings->count() }}</span>
                @endif
            </button>
        </div>

        <!-- Pending Bookings Tab -->
        <div id="tab-pending" class="tab-content active p-6">
            @if($pendingBookings->count() > 0)
            <div class="space-y-4">
                @foreach($pendingBookings as $booking)
                <div class="booking-card {{ $booking->isToday() ? 'today' : '' }} rounded-2xl p-5">
                    <div class="flex items-center gap-4">
                        <!-- Date Badge -->
                        <div class="w-16 h-16 rounded-2xl {{ $booking->isToday() ? 'bg-gradient-to-br from-amber-500 to-orange-500 text-black' : 'bg-white/10 text-white' }} flex flex-col items-center justify-center">
                            <span class="text-xs font-bold uppercase">{{ $booking->scheduled_date->format('M') }}</span>
                            <span class="text-2xl font-black">{{ $booking->scheduled_date->format('d') }}</span>
                        </div>

                        <!-- Details -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <span class="font-bold text-white">{{ $booking->customer?->user?->name ?? 'Customer' }}</span>
                                @if($booking->isToday())
                                    <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs font-bold rounded">LEO!</span>
                                @endif
                            </div>
                            <div class="text-gray-400 text-sm flex flex-wrap items-center gap-3">
                                <span>📱 {{ ucfirst($booking->line_type) }}</span>
                                <span>⏰ {{ $booking->getTimeSlotLabel() }}</span>
                                @if($booking->address)
                                    <span>📍 {{ Str::limit($booking->address, 20) }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('agent.bookings.show', $booking) }}" class="px-4 py-2 bg-white/10 text-white font-bold rounded-xl hover:bg-white/20 transition">
                                Angalia
                            </a>
                            <form action="{{ route('agent.bookings.confirm', $booking) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-green-500/30 transition">
                                    ✓ Kubali
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📭</div>
                <h4 class="text-white font-bold text-lg mb-2">Hakuna Booking Zinasubiri</h4>
                <p class="text-gray-400">Booking mpya zitatokea hapa wakati customers watakapoweka.</p>
            </div>
            @endif
        </div>

        <!-- Upcoming Bookings Tab -->
        <div id="tab-upcoming" class="tab-content p-6">
            @if($upcomingBookings->count() > 0)
            <div class="space-y-4">
                @foreach($upcomingBookings as $booking)
                <a href="{{ route('agent.bookings.show', $booking) }}" class="booking-card {{ $booking->isToday() ? 'today' : '' }} block rounded-2xl p-5 hover:bg-white/5">
                    <div class="flex items-center gap-4">
                        <!-- Date Badge -->
                        <div class="w-16 h-16 rounded-2xl {{ $booking->isToday() ? 'bg-gradient-to-br from-amber-500 to-orange-500 text-black' : 'bg-white/10 text-white' }} flex flex-col items-center justify-center">
                            <span class="text-xs font-bold uppercase">{{ $booking->scheduled_date->format('M') }}</span>
                            <span class="text-2xl font-black">{{ $booking->scheduled_date->format('d') }}</span>
                        </div>

                        <!-- Details -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <span class="font-bold text-white">{{ $booking->customer?->user?->name ?? 'Customer' }}</span>
                                <span class="status-badge status-{{ $booking->status }}">
                                    {{ $booking->status === 'confirmed' ? 'Imekubaliwa' : ($booking->status === 'in_progress' ? 'Inaendelea' : ucfirst($booking->status)) }}
                                </span>
                                @if($booking->isToday())
                                    <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs font-bold rounded">LEO!</span>
                                @endif
                            </div>
                            <div class="text-gray-400 text-sm flex flex-wrap items-center gap-3">
                                <span>📱 {{ ucfirst($booking->line_type) }}</span>
                                <span>⏰ {{ $booking->getTimeSlotLabel() }}</span>
                                <span>📞 {{ $booking->phone }}</span>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📆</div>
                <h4 class="text-white font-bold text-lg mb-2">Hakuna Booking Zijazo</h4>
                <p class="text-gray-400">Ukikubali booking, zitaonekana hapa.</p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

        // Show selected tab
        document.getElementById('tab-' + tabName).classList.add('active');
        event.target.classList.add('active');
    }
</script>
@endpush
@endsection
