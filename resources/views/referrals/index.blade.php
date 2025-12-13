@extends('layouts.dashboard')

@section('title', 'Referral Program - SKY LAINI')

@push('styles')
<style>
    /* Glass Cards */
    .glass-card {
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* Referral Code Box */
    .referral-code-box {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(168, 85, 247, 0.1));
        border: 2px dashed rgba(245, 158, 11, 0.4);
        position: relative;
        overflow: hidden;
    }
    .referral-code-box::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(245, 158, 11, 0.1), transparent);
        animation: shimmer 3s infinite;
    }
    @keyframes shimmer {
        0% { transform: translateX(-100%) rotate(45deg); }
        100% { transform: translateX(100%) rotate(45deg); }
    }

    /* Stats Cards */
    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(245, 158, 11, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    /* Share Buttons */
    .share-btn {
        transition: all 0.3s ease;
    }
    .share-btn:hover {
        transform: scale(1.05);
    }
    .share-btn.whatsapp { background: linear-gradient(135deg, #25D366, #128C7E); }
    .share-btn.sms { background: linear-gradient(135deg, #3B82F6, #1D4ED8); }
    .share-btn.copy { background: linear-gradient(135deg, #6B7280, #4B5563); }

    /* Referral Row */
    .referral-row {
        transition: all 0.3s ease;
    }
    .referral-row:hover {
        background: rgba(255, 255, 255, 0.05);
        transform: translateX(5px);
    }

    /* Leaderboard */
    .leaderboard-item {
        transition: all 0.3s ease;
    }
    .leaderboard-item:hover {
        background: rgba(245, 158, 11, 0.1);
    }

    /* Confetti Animation for Discount */
    .discount-badge {
        animation: bounce 2s infinite;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    /* Copy Success Animation */
    .copy-success {
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black text-white mb-2 tracking-tight flex items-center gap-3">
                <span class="text-5xl">🎁</span> Referral Program
            </h1>
            <p class="text-gray-400 font-medium text-lg">Waleta marafiki, pata zawadi!</p>
        </div>
        @if($pendingDiscount)
        <div class="discount-badge bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-3 rounded-2xl shadow-lg shadow-green-500/30">
            <div class="flex items-center gap-3">
                <span class="text-3xl">💰</span>
                <div>
                    <p class="text-green-100 text-sm font-medium">Discount Yako!</p>
                    <p class="text-white font-black text-xl">TSh {{ number_format($pendingDiscount) }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Your Referral Code -->
    <div class="referral-code-box rounded-3xl p-8 text-center relative">
        <div class="absolute top-4 right-4">
            <span class="px-4 py-2 bg-amber-500/20 text-amber-400 text-xs font-bold rounded-full uppercase">
                {{ $user->role->value ?? 'User' }}
            </span>
        </div>
        
        <h2 class="text-gray-400 font-bold text-lg mb-2">Code Yako ya Referral</h2>
        <div class="relative inline-block">
            <div id="referral-code" class="text-5xl md:text-6xl font-black text-white tracking-widest mb-4 select-all cursor-pointer" onclick="copyCode()">
                {{ $referralCode }}
            </div>
            <button onclick="copyCode()" id="copyBtn" class="absolute -right-12 top-1/2 -translate-y-1/2 p-3 bg-white/10 rounded-xl hover:bg-white/20 transition">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </button>
        </div>
        <div id="copySuccess" class="hidden copy-success text-green-400 font-bold mb-4">
            ✓ Code imecopy!
        </div>
        
        <p class="text-gray-400 max-w-lg mx-auto mb-6">
            @if($user->isCustomer())
                Shiriki code hii na marafiki wako. Wao watapata <span class="text-green-400 font-bold">TSh {{ number_format($bonusSettings['customer_referred_discount']) }}</span> discount, 
                na wewe utapata <span class="text-amber-400 font-bold">TSh {{ number_format($bonusSettings['customer_referral_bonus']) }}</span> bonus!
            @else
                Shiriki code hii na agents wengine. Wao watapata <span class="text-green-400 font-bold">TSh {{ number_format($bonusSettings['agent_referred_bonus']) }}</span> bonus, 
                na wewe utapata <span class="text-amber-400 font-bold">TSh {{ number_format($bonusSettings['agent_referral_bonus']) }}</span> kwenye wallet yako!
            @endif
        </p>

        <!-- Share Buttons -->
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ $shareMessage['whatsapp_url'] }}" target="_blank" class="share-btn whatsapp flex items-center gap-3 px-6 py-4 rounded-2xl text-white font-bold shadow-lg">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Share via WhatsApp
            </a>
            <a href="sms:?body={{ urlencode($shareMessage['text']) }}" class="share-btn sms flex items-center gap-3 px-6 py-4 rounded-2xl text-white font-bold shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                Share via SMS
            </a>
            <button onclick="copyCode()" class="share-btn copy flex items-center gap-3 px-6 py-4 rounded-2xl text-white font-bold shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Copy Code
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card rounded-2xl p-6 text-center">
            <div class="text-4xl mb-2">👥</div>
            <div class="text-3xl font-black text-white">{{ $stats['total_referrals'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Referral Zote</div>
        </div>
        <div class="stat-card rounded-2xl p-6 text-center">
            <div class="text-4xl mb-2">⏳</div>
            <div class="text-3xl font-black text-yellow-400">{{ $stats['pending_referrals'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Zinasubiri</div>
        </div>
        <div class="stat-card rounded-2xl p-6 text-center">
            <div class="text-4xl mb-2">✅</div>
            <div class="text-3xl font-black text-green-400">{{ $stats['completed_referrals'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Zimekamilika</div>
        </div>
        <div class="stat-card rounded-2xl p-6 text-center">
            <div class="text-4xl mb-2">💰</div>
            <div class="text-2xl font-black text-amber-400">TSh {{ number_format($stats['total_earnings']) }}</div>
            <div class="text-gray-400 text-sm font-medium">Mapato Yote</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Referral History -->
        <div class="lg:col-span-2 glass-card rounded-3xl p-6">
            <h3 class="text-xl font-black text-white mb-6 flex items-center gap-3">
                <span class="text-2xl">📋</span> Historia ya Referral
            </h3>
            
            @if($referrals->count() > 0)
            <div class="space-y-3">
                @foreach($referrals as $referral)
                <div class="referral-row flex items-center gap-4 p-4 rounded-xl bg-white/5">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-{{ $referral->referred_type === 'agent' ? 'amber' : 'blue' }}-500 to-{{ $referral->referred_type === 'agent' ? 'orange' : 'indigo' }}-600 flex items-center justify-center text-{{ $referral->referred_type === 'agent' ? 'black' : 'white' }} font-bold text-lg overflow-hidden">
                        @if($referral->referred?->profile_picture)
                            <img src="{{ route('profile.picture.view', $referral->referred->profile_picture) }}" alt="" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($referral->referred?->name ?? 'U', 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="text-white font-bold">{{ $referral->referred?->name ?? 'Unknown' }}</div>
                        <div class="text-gray-500 text-sm">
                            {{ ucfirst($referral->referred_type) }} • {{ $referral->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-green-400 font-bold">+TSh {{ number_format($referral->bonus_amount) }}</div>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-500/20 text-yellow-400',
                                'completed' => 'bg-blue-500/20 text-blue-400',
                                'rewarded' => 'bg-green-500/20 text-green-400',
                            ];
                            $statusLabels = [
                                'pending' => 'Inasubiri',
                                'completed' => 'Imekamilika',
                                'rewarded' => 'Umelipwa',
                            ];
                        @endphp
                        <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold {{ $statusColors[$referral->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                            {{ $statusLabels[$referral->status] ?? ucfirst($referral->status) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🔗</div>
                <h4 class="text-white font-bold text-lg mb-2">Hakuna Referral Bado</h4>
                <p class="text-gray-400 max-w-sm mx-auto">Shiriki code yako na marafiki wako ili kuanza kupata zawadi!</p>
            </div>
            @endif
        </div>

        <!-- Leaderboard & How It Works -->
        <div class="space-y-6">
            <!-- Mini Leaderboard -->
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-black text-white mb-4 flex items-center gap-2">
                    <span class="text-xl">🏆</span> Top Referrers
                </h3>
                
                <div class="space-y-3">
                    @forelse($leaderboard as $index => $referrer)
                    <div class="leaderboard-item flex items-center gap-3 p-3 rounded-xl">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                            {{ $index === 0 ? 'bg-yellow-500 text-black' : ($index === 1 ? 'bg-gray-400 text-black' : ($index === 2 ? 'bg-amber-600 text-white' : 'bg-gray-700 text-white')) }}">
                            @if($index === 0) 🥇 @elseif($index === 1) 🥈 @elseif($index === 2) 🥉 @else {{ $index + 1 }} @endif
                        </div>
                        <div class="flex-1">
                            <div class="text-white font-medium text-sm">{{ $referrer['name'] }}</div>
                            <div class="text-gray-500 text-xs">{{ $referrer['referral_count'] }} referrals</div>
                        </div>
                        <div class="text-green-400 font-bold text-sm">
                            TSh {{ number_format($referrer['total_earnings']) }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-gray-500">
                        Kuwa wa kwanza!
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- How It Works -->
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-black text-white mb-4 flex items-center gap-2">
                    <span class="text-xl">📖</span> Jinsi Inavyofanya Kazi
                </h3>
                
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-500 font-bold shrink-0">1</div>
                        <div>
                            <p class="text-white font-medium">Shiriki Code Yako</p>
                            <p class="text-gray-400 text-sm">Tuma code yako kwa marafiki kupitia WhatsApp au SMS</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-500 font-bold shrink-0">2</div>
                        <div>
                            <p class="text-white font-medium">Wanajisajili</p>
                            <p class="text-gray-400 text-sm">Rafiki yako anajisajili na kuingiza code yako</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-500 font-bold shrink-0">3</div>
                        <div>
                            <p class="text-white font-medium">Wanakamilisha Order</p>
                            <p class="text-gray-400 text-sm">Rafiki anafanya order yake ya kwanza</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center text-green-500 font-bold shrink-0">✓</div>
                        <div>
                            <p class="text-white font-medium">Unapata Bonus!</p>
                            <p class="text-gray-400 text-sm">
                                @if($user->isAgent())
                                    TSh {{ number_format($bonusSettings['agent_referral_bonus']) }} inakwenda wallet yako
                                @else
                                    TSh {{ number_format($bonusSettings['customer_referral_bonus']) }} discount kwenye order yako ijayo
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bonus Info -->
            <div class="glass-card rounded-3xl p-6 bg-gradient-to-br from-amber-500/10 to-purple-500/5">
                <h3 class="text-lg font-black text-white mb-4 flex items-center gap-2">
                    <span class="text-xl">💎</span> Bonuses
                </h3>
                
                <div class="space-y-3">
                    @if($user->isCustomer())
                    <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl">
                        <span class="text-gray-400 text-sm">Unapata:</span>
                        <span class="text-amber-400 font-bold">TSh {{ number_format($bonusSettings['customer_referral_bonus']) }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl">
                        <span class="text-gray-400 text-sm">Rafiki anapata:</span>
                        <span class="text-green-400 font-bold">TSh {{ number_format($bonusSettings['customer_referred_discount']) }} discount</span>
                    </div>
                    @else
                    <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl">
                        <span class="text-gray-400 text-sm">Unapata:</span>
                        <span class="text-amber-400 font-bold">TSh {{ number_format($bonusSettings['agent_referral_bonus']) }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl">
                        <span class="text-gray-400 text-sm">Agent mpya anapata:</span>
                        <span class="text-green-400 font-bold">TSh {{ number_format($bonusSettings['agent_referred_bonus']) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyCode() {
        const code = document.getElementById('referral-code').textContent.trim();
        navigator.clipboard.writeText(code).then(() => {
            const successEl = document.getElementById('copySuccess');
            successEl.classList.remove('hidden');
            
            // Hide after 2 seconds
            setTimeout(() => {
                successEl.classList.add('hidden');
            }, 2000);
        }).catch(err => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            const successEl = document.getElementById('copySuccess');
            successEl.classList.remove('hidden');
            setTimeout(() => {
                successEl.classList.add('hidden');
            }, 2000);
        });
    }
</script>
@endpush
@endsection
