@extends('layouts.dashboard')

@section('title', __('messages.leaderboard.title') . ' - SKY LAINI')

@push('styles')
<style>
    .leaderboard-header {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.1));
        border-radius: 32px;
        padding: 40px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .leaderboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.1) 0%, transparent 70%);
        animation: pulse 4s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.3; }
    }
    
    .podium-container {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 20px;
        margin-top: 40px;
        padding: 0 20px;
    }
    
    .podium-item {
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .podium-item:hover {
        transform: translateY(-10px);
    }
    
    .podium-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 16px;
        position: relative;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: bold;
        color: black;
        box-shadow: 0 10px 40px rgba(245, 158, 11, 0.3);
    }
    
    .podium-avatar.gold {
        width: 100px;
        height: 100px;
        font-size: 36px;
        border: 4px solid #ffd700;
        box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
    }
    
    .podium-avatar.silver {
        border: 4px solid #c0c0c0;
        box-shadow: 0 0 20px rgba(192, 192, 192, 0.4);
    }
    
    .podium-avatar.bronze {
        border: 4px solid #cd7f32;
        box-shadow: 0 0 20px rgba(205, 127, 50, 0.4);
    }
    
    .podium-base {
        width: 120px;
        border-radius: 12px 12px 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: bold;
    }
    
    .podium-base.gold {
        background: linear-gradient(180deg, #ffd700, #ffb700);
        height: 120px;
        color: #000;
    }
    
    .podium-base.silver {
        background: linear-gradient(180deg, #c0c0c0, #a0a0a0);
        height: 90px;
        color: #333;
    }
    
    .podium-base.bronze {
        background: linear-gradient(180deg, #cd7f32, #a56429);
        height: 70px;
        color: #fff;
    }
    
    .rank-badge {
        position: absolute;
        bottom: -5px;
        right: -5px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        border: 2px solid #0a0a0a;
    }
    
    .rank-badge.gold { background: #ffd700; }
    .rank-badge.silver { background: #c0c0c0; }
    .rank-badge.bronze { background: #cd7f32; color: white; }
    
    .leaderboard-table {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 24px;
        overflow: hidden;
    }
    
    .leaderboard-row {
        display: flex;
        align-items: center;
        padding: 16px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    
    .leaderboard-row:hover {
        background: rgba(245, 158, 11, 0.05);
    }
    
    .rank-number {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    
    .agent-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: black;
        font-size: 18px;
    }
    
    .filter-tabs {
        display: flex;
        gap: 8px;
        background: rgba(255, 255, 255, 0.05);
        padding: 6px;
        border-radius: 16px;
    }
    
    .filter-tab {
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        color: #9ca3af;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .filter-tab.active {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: black;
    }
    
    .filter-tab:hover:not(.active) {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    
    .star-rating {
        display: flex;
        align-items: center;
        gap: 2px;
    }
    
    .online-badge {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 8px #22c55e;
    }
    
    .offline-badge {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #6b7280;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header Section -->
    <div class="leaderboard-header">
        <div class="relative z-10">
            <h1 class="text-4xl font-black text-white mb-2">🏆 {{ __('messages.leaderboard.title') }}</h1>
            <p class="text-gray-300 text-lg">Mawakala walioongoza kwa utendaji bora</p>
            
            <!-- Podium for Top 3 -->
            @if(count($topThree ?? []) >= 3)
                <div class="podium-container">
                    <!-- Second Place -->
                    <div class="podium-item">
                        <div class="podium-avatar silver">
                            {{ substr($topThree[1]['name'] ?? 'A', 0, 1) }}
                            <div class="rank-badge silver">🥈</div>
                        </div>
                        <div class="text-white font-bold mb-2">{{ $topThree[1]['name'] ?? 'N/A' }}</div>
                        <div class="text-gray-400 text-sm mb-4">⭐ {{ $topThree[1]['rating'] ?? 0 }}</div>
                        <div class="podium-base silver">2</div>
                    </div>
                    
                    <!-- First Place -->
                    <div class="podium-item">
                        <div class="podium-avatar gold">
                            {{ substr($topThree[0]['name'] ?? 'A', 0, 1) }}
                            <div class="rank-badge gold">🥇</div>
                        </div>
                        <div class="text-white font-bold text-lg mb-2">{{ $topThree[0]['name'] ?? 'N/A' }}</div>
                        <div class="text-gray-400 text-sm mb-4">⭐ {{ $topThree[0]['rating'] ?? 0 }}</div>
                        <div class="podium-base gold">1</div>
                    </div>
                    
                    <!-- Third Place -->
                    <div class="podium-item">
                        <div class="podium-avatar bronze">
                            {{ substr($topThree[2]['name'] ?? 'A', 0, 1) }}
                            <div class="rank-badge bronze">🥉</div>
                        </div>
                        <div class="text-white font-bold mb-2">{{ $topThree[2]['name'] ?? 'N/A' }}</div>
                        <div class="text-gray-400 text-sm mb-4">⭐ {{ $topThree[2]['rating'] ?? 0 }}</div>
                        <div class="podium-base bronze">3</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex justify-center">
        <div class="filter-tabs">
            <a href="?period=all" class="filter-tab {{ $period === 'all' ? 'active' : '' }}">{{ __('messages.leaderboard.all_time') }}</a>
            <a href="?period=month" class="filter-tab {{ $period === 'month' ? 'active' : '' }}">{{ __('messages.leaderboard.this_month') }}</a>
            <a href="?period=week" class="filter-tab {{ $period === 'week' ? 'active' : '' }}">{{ __('messages.leaderboard.this_week') }}</a>
            <a href="?period=today" class="filter-tab {{ $period === 'today' ? 'active' : '' }}">Leo</a>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="leaderboard-table">
        <div class="p-6 border-b border-white/5">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">Mawakala Wote</h2>
                <span class="text-gray-400 text-sm">{{ count($agents ?? []) }} mawakala</span>
            </div>
        </div>
        
        <!-- Table Header -->
        <div class="leaderboard-row bg-white/5">
            <div class="w-16 text-gray-400 text-sm font-bold">{{ __('messages.leaderboard.rank') }}</div>
            <div class="flex-1 text-gray-400 text-sm font-bold">{{ __('messages.leaderboard.agent') }}</div>
            <div class="w-32 text-center text-gray-400 text-sm font-bold">{{ __('messages.leaderboard.rating') }}</div>
            <div class="w-32 text-center text-gray-400 text-sm font-bold">{{ __('messages.leaderboard.jobs') }}</div>
            <div class="w-40 text-right text-gray-400 text-sm font-bold">{{ __('messages.leaderboard.earnings') }}</div>
        </div>
        
        @forelse($others ?? [] as $agent)
            <div class="leaderboard-row">
                <div class="w-16">
                    <div class="rank-number">{{ $agent['rank'] ?? '-' }}</div>
                </div>
                <div class="flex-1 flex items-center gap-4">
                    <div class="relative">
                        <div class="agent-avatar">{{ substr($agent['name'] ?? 'A', 0, 1) }}</div>
                        <div class="absolute -bottom-1 -right-1 {{ $agent['is_online'] ? 'online-badge' : 'offline-badge' }}"></div>
                    </div>
                    <div>
                        <div class="font-bold text-white">{{ $agent['name'] ?? 'Unknown' }}</div>
                        <div class="text-sm text-gray-500">{{ ucfirst($agent['tier'] ?? 'bronze') }} Tier</div>
                    </div>
                </div>
                <div class="w-32 text-center">
                    <div class="star-rating justify-center">
                        <span class="text-amber-500 font-bold">⭐ {{ $agent['rating'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="w-32 text-center">
                    <span class="font-bold text-white">{{ $agent['completed_jobs'] ?? 0 }}</span>
                </div>
                <div class="w-40 text-right">
                    <span class="font-bold text-amber-500">TZS {{ number_format($agent['earnings'] ?? 0) }}</span>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-white/5 flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <p class="text-gray-500">Hakuna mawakala walioorodheshwa bado</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
