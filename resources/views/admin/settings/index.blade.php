@extends('layouts.dashboard')

@section('title', 'System Settings - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .form-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .form-input:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: #f59e0b;
        outline: none;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">System Settings</h1>
            <p class="text-gray-400 font-medium">Manage global configurations</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-500 p-4 rounded-xl flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Pricing Settings -->
            <div class="glass-card rounded-3xl p-6">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-1 h-6 bg-amber-500 rounded-full"></span>
                    Pricing & Payments
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-400 text-sm font-bold mb-2">Price per Laini (TZS)</label>
                        <input type="number" name="price_per_laini" value="{{ $settings['price_per_laini']->value ?? '1000' }}" class="form-input w-full rounded-xl px-4 py-3 font-bold text-lg">
                        <p class="text-gray-500 text-xs mt-2">The amount a customer pays for a line registration request.</p>
                    </div>

                    <div>
                        <label class="block text-gray-400 text-sm font-bold mb-2">Agent Commission (%)</label>
                        <input type="number" name="agent_commission_percent" value="{{ $settings['agent_commission_percent']->value ?? '80' }}" class="form-input w-full rounded-xl px-4 py-3 font-bold text-lg">
                        <p class="text-gray-500 text-xs mt-2">Percentage of the fee that goes to the agent.</p>
                    </div>
                </div>
            </div>

            <!-- General Settings -->
            <div class="glass-card rounded-3xl p-6">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                    General Configuration
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-400 text-sm font-bold mb-2">Support Phone Number</label>
                        <input type="text" name="support_phone" value="{{ $settings['support_phone']->value ?? '+255 700 000 000' }}" class="form-input w-full rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-gray-400 text-sm font-bold mb-2">Support Email</label>
                        <input type="email" name="support_email" value="{{ $settings['support_email']->value ?? 'support@skylaini.com' }}" class="form-input w-full rounded-xl px-4 py-3">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-black text-lg rounded-xl shadow-lg shadow-amber-500/20 transition transform hover:scale-105">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
