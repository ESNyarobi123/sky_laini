@extends('layouts.dashboard')

@section('title', 'Referral Leaderboard - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .leaderboard-card {
        transition: all 0.3s ease;
    }
    .leaderboard-card:hover {
        transform: translateX(10px);
    }
    .rank-1 { background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(255, 215, 0, 0.05)); border-color: rgba(255, 215, 0, 0.3); }
    .rank-2 { background: linear-gradient(135deg, rgba(192, 192, 192, 0.2), rgba(192, 192, 192, 0.05)); border-color: rgba(192, 192, 192, 0.3); }
    .rank-3 { background: linear-gradient(135deg, rgba(205, 127, 50, 0.2), rgba(205, 127, 50, 0.05)); border-color: rgba(205, 127, 50, 0.3); }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.referrals.index') }}" class="p-2 bg-white/5 rounded-xl hover:bg-white/10 transition">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-black text-white mb-1">🏆 Referral Leaderboard</h1>
            <p class="text-gray-400">Top performers in our referral program</p>
        </div>
    </div>

    <!-- Top 3 Podium -->
    <div class="glass-card rounded-3xl p-8">
        <div class="grid grid-cols-3 gap-4 items-end mb-8">
            @if(isset($leaderboard[1]))
                <!-- 2nd Place -->
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center text-4xl shadow-lg">
                        🥈
                    </div>
                    <div class="bg-gray-600/30 rounded-t-xl pt-4 pb-12 px-4">
                        <div class="text-white font-bold text-lg">{{ $leaderboard[1]['name'] }}</div>
                        <div class="text-gray-400 text-sm">{{ $leaderboard[1]['referral_count'] }} referrals</div>
                        <div class="text-green-400 font-bold mt-2">TSh {{ number_format($leaderboard[1]['total_earnings']) }}</div>
                    </div>
                </div>
            @else
                <div></div>
            @endif

            @if(isset($leaderboard[0]))
                <!-- 1st Place -->
                <div class="text-center -mt-8">
                    <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-5xl shadow-2xl ring-4 ring-yellow-500/30">
                        🥇
                    </div>
                    <div class="bg-gradient-to-b from-yellow-500/30 to-yellow-500/10 rounded-t-xl pt-6 pb-16 px-4 border-2 border-yellow-500/30">
                        <div class="text-white font-black text-xl">{{ $leaderboard[0]['name'] }}</div>
                        <div class="text-gray-300 text-sm">{{ $leaderboard[0]['referral_count'] }} referrals</div>
                        <div class="text-green-400 font-bold text-lg mt-2">TSh {{ number_format($leaderboard[0]['total_earnings']) }}</div>
                        <span class="inline-block mt-2 px-3 py-1 bg-yellow-500/20 text-yellow-400 text-xs font-bold rounded-full">👑 CHAMPION</span>
                    </div>
                </div>
            @else
                <div class="text-center text-gray-500 py-12">No data yet</div>
            @endif

            @if(isset($leaderboard[2]))
                <!-- 3rd Place -->
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-amber-600 to-amber-700 flex items-center justify-center text-4xl shadow-lg">
                        🥉
                    </div>
                    <div class="bg-amber-700/20 rounded-t-xl pt-4 pb-8 px-4">
                        <div class="text-white font-bold text-lg">{{ $leaderboard[2]['name'] }}</div>
                        <div class="text-gray-400 text-sm">{{ $leaderboard[2]['referral_count'] }} referrals</div>
                        <div class="text-green-400 font-bold mt-2">TSh {{ number_format($leaderboard[2]['total_earnings']) }}</div>
                    </div>
                </div>
            @else
                <div></div>
            @endif
        </div>
    </div>

    <!-- Full Leaderboard -->
    <div class="glass-card rounded-3xl p-6">
        <h2 class="text-xl font-bold text-white mb-6">Complete Rankings</h2>
        
        <div class="space-y-3">
            @forelse($leaderboard as $index => $referrer)
                <div class="leaderboard-card rounded-xl p-4 flex items-center gap-4 border {{ $index < 3 ? 'rank-' . ($index + 1) : 'bg-white/5 border-white/5' }}">
                    <!-- Rank -->
                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-lg {{ $index < 3 ? 'text-2xl' : 'bg-gray-800 text-white' }}">
                        @if($index === 0)
                            🥇
                        @elseif($index === 1)
                            🥈
                        @elseif($index === 2)
                            🥉
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>

                    <!-- Avatar -->
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-black font-bold text-lg overflow-hidden">
                        @if($referrer['profile_picture'])
                            <img src="{{ $referrer['profile_picture'] }}" alt="" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($referrer['name'], 0, 1)) }}
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1">
                        <div class="text-white font-bold">{{ $referrer['name'] }}</div>
                        <div class="text-gray-400 text-sm flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-{{ $referrer['role'] === 'agent' ? 'amber' : 'blue' }}-500/20 text-{{ $referrer['role'] === 'agent' ? 'amber' : 'blue' }}-400 rounded text-xs font-bold">
                                {{ ucfirst($referrer['role']) }}
                            </span>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="text-right">
                        <div class="text-2xl font-black text-white">{{ $referrer['referral_count'] }}</div>
                        <div class="text-gray-500 text-xs">referrals</div>
                    </div>

                    <!-- Earnings -->
                    <div class="text-right min-w-[100px]">
                        <div class="text-green-400 font-bold">TSh {{ number_format($referrer['total_earnings']) }}</div>
                        <div class="text-gray-500 text-xs">earned</div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    No referrers yet. Share your referral codes!
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
