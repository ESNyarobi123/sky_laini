@extends('layouts.dashboard')

@section('title', 'Weka Booking - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .network-option {
        background: rgba(255, 255, 255, 0.03);
        border: 2px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .network-option:hover {
        border-color: rgba(255, 255, 255, 0.2);
    }
    .network-option.selected {
        border-color: #f59e0b;
        background: rgba(245, 158, 11, 0.1);
    }
    .network-option input:checked + .network-content {
        border-color: #f59e0b;
    }
    .time-slot {
        background: rgba(255, 255, 255, 0.03);
        border: 2px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .time-slot:hover {
        border-color: rgba(255, 255, 255, 0.2);
    }
    .time-slot.selected {
        border-color: #f59e0b;
        background: rgba(245, 158, 11, 0.1);
    }
    .form-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .form-input:focus {
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
        <a href="{{ route('customer.bookings.index') }}" class="p-2 bg-white/5 rounded-xl hover:bg-white/10 transition">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-black text-white mb-1">📅 Weka Booking Mpya</h1>
            <p class="text-gray-400">Chagua tarehe, muda na aina ya laini</p>
        </div>
    </div>

    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-4 flex items-center gap-3">
        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-red-500 font-bold">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-4">
        <ul class="text-red-500 font-medium list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('customer.bookings.store') }}" method="POST">
        @csrf

        <!-- Network Selection -->
        <div class="glass-card rounded-3xl p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">📱</span> Chagua Mtandao
            </h2>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                @foreach($networks as $network)
                <label class="network-option rounded-2xl p-4 text-center" id="network-{{ $network }}">
                    <input type="radio" name="line_type" value="{{ $network }}" class="hidden" 
                           {{ old('line_type') === $network ? 'checked' : '' }}
                           onchange="selectNetwork('{{ $network }}')">
                    <div class="network-content">
                        <div class="text-3xl mb-2">
                            @switch($network)
                                @case('vodacom') 🔴 @break
                                @case('airtel') 🔴 @break
                                @case('tigo') 🔵 @break
                                @case('halotel') 🟢 @break
                                @case('zantel') 🟠 @break
                            @endswitch
                        </div>
                        <div class="text-white font-bold text-sm capitalize">{{ $network }}</div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Date Selection -->
        <div class="glass-card rounded-3xl p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">📆</span> Chagua Tarehe
            </h2>
            
            <input type="date" name="scheduled_date" 
                   value="{{ old('scheduled_date') }}"
                   min="{{ $minDate }}" max="{{ $maxDate }}"
                   class="form-input w-full rounded-xl px-5 py-4 text-lg font-bold" required>
            <p class="text-gray-500 text-sm mt-2">* Unaweza ku-book kuanzia kesho hadi miezi 2 mbele</p>
        </div>

        <!-- Time Slot Selection -->
        <div class="glass-card rounded-3xl p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">⏰</span> Chagua Muda
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach($timeSlots as $value => $label)
                <label class="time-slot rounded-2xl p-5 text-center cursor-pointer" id="slot-{{ $value }}">
                    <input type="radio" name="time_slot" value="{{ $value }}" class="hidden"
                           {{ old('time_slot') === $value ? 'checked' : '' }}
                           onchange="selectTimeSlot('{{ $value }}')">
                    <div class="text-3xl mb-2">
                        @switch($value)
                            @case('morning') ☀️ @break
                            @case('afternoon') 🌤️ @break
                            @case('evening') 🌙 @break
                        @endswitch
                    </div>
                    <div class="text-white font-bold">{{ $label }}</div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Contact Info -->
        <div class="glass-card rounded-3xl p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">📞</span> Mawasiliano
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="text-gray-400 text-sm font-bold mb-2 block">Nambari ya Simu *</label>
                    <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" 
                           class="form-input w-full rounded-xl px-5 py-4"
                           placeholder="0712345678" required>
                </div>
                <div>
                    <label class="text-gray-400 text-sm font-bold mb-2 block">Mahali (Address)</label>
                    <input type="text" name="address" value="{{ old('address') }}" 
                           class="form-input w-full rounded-xl px-5 py-4"
                           placeholder="Mfano: Sinza, Dar es Salaam">
                </div>
                <div>
                    <label class="text-gray-400 text-sm font-bold mb-2 block">Maelezo Zaidi</label>
                    <textarea name="notes" rows="3" 
                              class="form-input w-full rounded-xl px-5 py-4"
                              placeholder="Maelezo yoyote ya ziada...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full py-5 bg-gradient-to-r from-amber-500 to-orange-500 text-black font-black text-xl rounded-2xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 transition transform hover:-translate-y-1">
            Weka Booking 📅
        </button>
    </form>
</div>

@push('scripts')
<script>
    function selectNetwork(network) {
        document.querySelectorAll('.network-option').forEach(el => el.classList.remove('selected'));
        document.getElementById('network-' + network).classList.add('selected');
    }

    function selectTimeSlot(slot) {
        document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
        document.getElementById('slot-' + slot).classList.add('selected');
    }

    // Initialize selected states on page load
    document.addEventListener('DOMContentLoaded', function() {
        const checkedNetwork = document.querySelector('input[name="line_type"]:checked');
        if (checkedNetwork) selectNetwork(checkedNetwork.value);

        const checkedSlot = document.querySelector('input[name="time_slot"]:checked');
        if (checkedSlot) selectTimeSlot(checkedSlot.value);
    });
</script>
@endpush
@endsection
