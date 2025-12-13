@extends('layouts.dashboard')

@section('title', 'Referral Settings - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .setting-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .setting-input:focus {
        border-color: #f59e0b;
        outline: none;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.referrals.index') }}" class="p-2 bg-white/5 rounded-xl hover:bg-white/10 transition">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-black text-white mb-1">⚙️ Referral Settings</h1>
            <p class="text-gray-400">Configure bonus amounts and referral rules</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Settings Form -->
    <form action="{{ route('admin.referrals.settings.update') }}" method="POST">
        @csrf

        <div class="glass-card rounded-3xl p-6 space-y-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-2xl">👥</span> Customer Referrals
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-400 text-sm font-bold mb-2">Referrer Bonus (TSh)</label>
                    <p class="text-gray-500 text-xs mb-2">Amount paid to customer who refers another customer</p>
                    <input type="number" name="customer_referral_bonus" 
                           value="{{ old('customer_referral_bonus', $settings->firstWhere('key', 'customer_referral_bonus')?->value ?? 500) }}"
                           class="setting-input w-full px-4 py-3 rounded-xl font-bold text-lg" min="0">
                    @error('customer_referral_bonus')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-gray-400 text-sm font-bold mb-2">Referred Discount (TSh)</label>
                    <p class="text-gray-500 text-xs mb-2">Discount on first request for referred customer</p>
                    <input type="number" name="customer_referred_discount" 
                           value="{{ old('customer_referred_discount', $settings->firstWhere('key', 'customer_referred_discount')?->value ?? 300) }}"
                           class="setting-input w-full px-4 py-3 rounded-xl font-bold text-lg" min="0">
                    @error('customer_referred_discount')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="glass-card rounded-3xl p-6 space-y-6 mt-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-2xl">🏍️</span> Agent Referrals
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-400 text-sm font-bold mb-2">Referrer Bonus (TSh)</label>
                    <p class="text-gray-500 text-xs mb-2">Amount paid to agent who refers another agent</p>
                    <input type="number" name="agent_referral_bonus" 
                           value="{{ old('agent_referral_bonus', $settings->firstWhere('key', 'agent_referral_bonus')?->value ?? 1000) }}"
                           class="setting-input w-full px-4 py-3 rounded-xl font-bold text-lg" min="0">
                    @error('agent_referral_bonus')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-gray-400 text-sm font-bold mb-2">Welcome Bonus (TSh)</label>
                    <p class="text-gray-500 text-xs mb-2">Bonus for new agent after first completed job</p>
                    <input type="number" name="agent_referred_bonus" 
                           value="{{ old('agent_referred_bonus', $settings->firstWhere('key', 'agent_referred_bonus')?->value ?? 500) }}"
                           class="setting-input w-full px-4 py-3 rounded-xl font-bold text-lg" min="0">
                    @error('agent_referred_bonus')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="glass-card rounded-3xl p-6 space-y-6 mt-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-2xl">📋</span> Reward Rules
            </h2>
            
            <div>
                <label class="block text-gray-400 text-sm font-bold mb-2">Minimum Jobs for Reward</label>
                <p class="text-gray-500 text-xs mb-2">Number of completed jobs before referral bonus is paid</p>
                <input type="number" name="min_jobs_for_reward" 
                       value="{{ old('min_jobs_for_reward', $settings->firstWhere('key', 'min_jobs_for_reward')?->value ?? 1) }}"
                       class="setting-input w-full md:w-1/4 px-4 py-3 rounded-xl font-bold text-lg" min="1">
                @error('min_jobs_for_reward')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-black font-bold rounded-xl hover:opacity-90 transition">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
