@extends('layouts.dashboard')

@section('title', 'Referral Program - SKY LAINI')

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
    .referral-row {
        transition: all 0.2s ease;
    }
    .referral-row:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    .top-referrer-card {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(168, 85, 247, 0.05));
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white mb-1">🎁 Referral Program</h1>
            <p class="text-gray-400">Manage referral bonuses, discounts, and track performance</p>
        </div>
        <a href="{{ route('admin.referrals.settings') }}" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-black font-bold rounded-xl hover:opacity-90 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Settings
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="stat-card rounded-2xl p-5">
            <div class="text-gray-400 text-xs font-bold uppercase mb-2">Total Referrals</div>
            <div class="text-3xl font-black text-white">{{ number_format($stats['total_referrals']) }}</div>
        </div>
        <div class="stat-card rounded-2xl p-5">
            <div class="text-gray-400 text-xs font-bold uppercase mb-2">Pending</div>
            <div class="text-3xl font-black text-yellow-400">{{ number_format($stats['pending_referrals']) }}</div>
        </div>
        <div class="stat-card rounded-2xl p-5">
            <div class="text-gray-400 text-xs font-bold uppercase mb-2">Completed</div>
            <div class="text-3xl font-black text-blue-400">{{ number_format($stats['completed_referrals']) }}</div>
        </div>
        <div class="stat-card rounded-2xl p-5">
            <div class="text-gray-400 text-xs font-bold uppercase mb-2">Rewarded</div>
            <div class="text-3xl font-black text-green-400">{{ number_format($stats['rewarded_referrals']) }}</div>
        </div>
        <div class="stat-card rounded-2xl p-5">
            <div class="text-gray-400 text-xs font-bold uppercase mb-2">Total Paid</div>
            <div class="text-2xl font-black text-amber-400">TSh {{ number_format($stats['total_bonuses_paid']) }}</div>
        </div>
    </div>

    <!-- Current Settings -->
    <div class="glass-card rounded-3xl p-6">
        <h2 class="text-lg font-bold text-white mb-4">Current Settings</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 bg-white/5 rounded-xl">
                <div class="text-gray-400 text-xs mb-1">Customer Referral Bonus</div>
                <div class="text-xl font-bold text-green-400">TSh {{ number_format($settings['customer_referral_bonus'] ?? 500) }}</div>
            </div>
            <div class="p-4 bg-white/5 rounded-xl">
                <div class="text-gray-400 text-xs mb-1">Customer Discount</div>
                <div class="text-xl font-bold text-blue-400">TSh {{ number_format($settings['customer_referred_discount'] ?? 300) }}</div>
            </div>
            <div class="p-4 bg-white/5 rounded-xl">
                <div class="text-gray-400 text-xs mb-1">Agent Referral Bonus</div>
                <div class="text-xl font-bold text-amber-400">TSh {{ number_format($settings['agent_referral_bonus'] ?? 1000) }}</div>
            </div>
            <div class="p-4 bg-white/5 rounded-xl">
                <div class="text-gray-400 text-xs mb-1">Agent Welcome Bonus</div>
                <div class="text-xl font-bold text-purple-400">TSh {{ number_format($settings['agent_referred_bonus'] ?? 500) }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Referrals -->
        <div class="lg:col-span-2 glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">Recent Referrals</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-400 text-sm border-b border-white/10">
                            <th class="pb-3 font-bold">Referrer</th>
                            <th class="pb-3 font-bold">Referred</th>
                            <th class="pb-3 font-bold">Bonus</th>
                            <th class="pb-3 font-bold">Status</th>
                            <th class="pb-3 font-bold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($recentReferrals as $referral)
                            <tr class="referral-row">
                                <td class="py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-black font-bold text-sm">
                                            {{ strtoupper(substr($referral->referrer?->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-white font-medium">{{ $referral->referrer?->name ?? 'Unknown' }}</div>
                                            <div class="text-gray-500 text-xs">{{ ucfirst($referral->referrer_type) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <div class="text-white">{{ $referral->referred?->name ?? 'Unknown' }}</div>
                                    <div class="text-gray-500 text-xs">{{ ucfirst($referral->referred_type) }}</div>
                                </td>
                                <td class="py-4">
                                    <div class="text-green-400 font-bold">TSh {{ number_format($referral->bonus_amount) }}</div>
                                </td>
                                <td class="py-4">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-500/20 text-yellow-400',
                                            'completed' => 'bg-blue-500/20 text-blue-400',
                                            'rewarded' => 'bg-green-500/20 text-green-400',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusColors[$referral->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                        {{ ucfirst($referral->status) }}
                                    </span>
                                </td>
                                <td class="py-4 text-gray-400 text-sm">
                                    {{ $referral->created_at->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">
                                    No referrals yet
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Referrers -->
        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">🏆 Top Referrers</h2>
                <a href="{{ route('admin.referrals.leaderboard') }}" class="text-amber-500 text-sm font-bold hover:text-amber-400">View All →</a>
            </div>
            
            <div class="space-y-4">
                @forelse($topReferrers as $index => $referrer)
                    <div class="top-referrer-card rounded-xl p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full {{ $index < 3 ? 'bg-gradient-to-br from-amber-500 to-orange-600' : 'bg-gray-700' }} flex items-center justify-center text-{{ $index < 3 ? 'black' : 'white' }} font-bold">
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
                        <div class="flex-1">
                            <div class="text-white font-bold">{{ $referrer->name }}</div>
                            <div class="text-gray-400 text-sm">{{ $referrer->referral_count }} referrals</div>
                        </div>
                        <div class="text-right">
                            <div class="text-green-400 font-bold">TSh {{ number_format($referrer->referral_earnings) }}</div>
                            <div class="text-gray-500 text-xs">earned</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        No top referrers yet
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
