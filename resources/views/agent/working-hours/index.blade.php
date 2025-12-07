@extends('layouts.dashboard')

@section('title', __('messages.agent.working_hours') . ' - SKY LAINI')

@push('styles')
<style>
    .schedule-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 24px;
        padding: 32px;
    }
    
    .day-row {
        display: flex;
        align-items: center;
        padding: 20px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.02);
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }
    
    .day-row:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .day-toggle {
        position: relative;
        width: 56px;
        height: 28px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .day-toggle.active {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    
    .day-toggle::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 22px;
        height: 22px;
        background: white;
        border-radius: 50%;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }
    
    .day-toggle.active::after {
        left: 31px;
    }
    
    .time-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 10px 16px;
        color: white;
        font-size: 14px;
        width: 100px;
        text-align: center;
        outline: none;
        transition: all 0.3s ease;
    }
    
    .time-input:focus {
        border-color: rgba(245, 158, 11, 0.5);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }
    
    .time-input:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    
    .day-label {
        width: 120px;
        font-weight: bold;
        color: white;
    }
    
    .preview-card {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05));
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: 20px;
        padding: 24px;
    }
    
    .current-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: bold;
        font-size: 14px;
    }
    
    .status-within {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
    }
    
    .status-outside {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
    }
    
    .timezone-select {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 12px 16px;
        color: white;
        font-size: 14px;
        outline: none;
        appearance: none;
        cursor: pointer;
    }
    
    .save-btn {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: black;
        font-weight: bold;
        padding: 14px 32px;
        border-radius: 16px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 16px;
    }
    
    .save-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
    }
    
    .auto-offline-toggle {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 16px;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">🕐 {{ __('messages.agent.working_hours') }}</h1>
            <p class="text-gray-400 font-medium">Weka ratiba yako ya kazi ili wateja wajue unapatikana lini</p>
        </div>
    </div>

    <form id="workingHoursForm" method="POST" action="{{ route('agent.working-hours.update') }}">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Schedule Settings -->
            <div class="lg:col-span-2">
                <div class="schedule-card">
                    <h2 class="text-xl font-bold text-white mb-6">📅 Ratiba ya Kila Siku</h2>
                    
                    @php
                        $days = [
                            'monday' => 'Jumatatu',
                            'tuesday' => 'Jumanne',
                            'wednesday' => 'Jumatano',
                            'thursday' => 'Alhamisi',
                            'friday' => 'Ijumaa',
                            'saturday' => 'Jumamosi',
                            'sunday' => 'Jumapili',
                        ];
                    @endphp
                    
                    @foreach($days as $dayKey => $dayName)
                        @php
                            $dayData = $workingHours[$dayKey] ?? ['enabled' => false, 'start' => '08:00', 'end' => '18:00'];
                            $isEnabled = $dayData['enabled'] ?? false;
                        @endphp
                        <div class="day-row">
                            <div class="day-label">{{ $dayName }}</div>
                            
                            <div class="day-toggle {{ $isEnabled ? 'active' : '' }}" 
                                 onclick="toggleDay('{{ $dayKey }}')"
                                 id="toggle-{{ $dayKey }}">
                            </div>
                            <input type="hidden" name="working_hours[{{ $dayKey }}][enabled]" 
                                   value="{{ $isEnabled ? '1' : '0' }}" 
                                   id="enabled-{{ $dayKey }}">
                            
                            <div class="flex-1 flex items-center justify-center gap-4">
                                <input type="time" 
                                       name="working_hours[{{ $dayKey }}][start]" 
                                       value="{{ $dayData['start'] ?? '08:00' }}"
                                       class="time-input"
                                       id="start-{{ $dayKey }}"
                                       {{ !$isEnabled ? 'disabled' : '' }}>
                                <span class="text-gray-500">hadi</span>
                                <input type="time" 
                                       name="working_hours[{{ $dayKey }}][end]" 
                                       value="{{ $dayData['end'] ?? '18:00' }}"
                                       class="time-input"
                                       id="end-{{ $dayKey }}"
                                       {{ !$isEnabled ? 'disabled' : '' }}>
                            </div>
                            
                            <div class="text-sm text-gray-500 w-24 text-right" id="hours-{{ $dayKey }}">
                                @if($isEnabled)
                                    @php
                                        $start = \Carbon\Carbon::createFromFormat('H:i', $dayData['start']);
                                        $end = \Carbon\Carbon::createFromFormat('H:i', $dayData['end']);
                                        $hours = $end->diffInHours($start);
                                    @endphp
                                    {{ $hours }} saa
                                @else
                                    Imefungwa
                                @endif
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Quick Actions -->
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="setAllDays(true)" class="px-4 py-2 bg-white/5 text-white rounded-xl text-sm font-bold hover:bg-white/10 transition">
                            ✓ Weka Zote Zimefunguliwa
                        </button>
                        <button type="button" onclick="setAllDays(false)" class="px-4 py-2 bg-white/5 text-white rounded-xl text-sm font-bold hover:bg-white/10 transition">
                            ✗ Weka Zote Zimefungwa
                        </button>
                        <button type="button" onclick="setWeekdays()" class="px-4 py-2 bg-white/5 text-white rounded-xl text-sm font-bold hover:bg-white/10 transition">
                            📆 Siku za Kazi tu (Mon-Fri)
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Preview & Settings -->
            <div class="space-y-6">
                <!-- Current Status -->
                <div class="preview-card">
                    <h3 class="text-lg font-bold text-white mb-4">📍 Hali ya Sasa</h3>
                    
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-2">🕐</div>
                        <div class="text-2xl font-bold text-white" id="currentTime">--:--</div>
                        <div class="text-gray-400 text-sm">{{ $agent->timezone ?? 'Africa/Dar_es_Salaam' }}</div>
                    </div>
                    
                    <div class="text-center">
                        @php
                            $isWithinHours = app(\App\Http\Controllers\Agent\WorkingHoursController::class)->isWithinWorkingHours($agent);
                        @endphp
                        <div class="current-status {{ $isWithinHours ? 'status-within' : 'status-outside' }}">
                            @if($isWithinHours)
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                Uko ndani ya saa za kazi
                            @else
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                Uko nje ya saa za kazi
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Settings -->
                <div class="schedule-card">
                    <h3 class="text-lg font-bold text-white mb-4">⚙️ Mipangilio</h3>
                    
                    <!-- Timezone -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-400 mb-2">Eneo la Saa</label>
                        <select name="timezone" class="timezone-select w-full">
                            <option value="Africa/Dar_es_Salaam" {{ ($agent->timezone ?? '') === 'Africa/Dar_es_Salaam' ? 'selected' : '' }}>Africa/Dar_es_Salaam (EAT)</option>
                            <option value="Africa/Nairobi" {{ ($agent->timezone ?? '') === 'Africa/Nairobi' ? 'selected' : '' }}>Africa/Nairobi (EAT)</option>
                            <option value="UTC" {{ ($agent->timezone ?? '') === 'UTC' ? 'selected' : '' }}>UTC</option>
                        </select>
                    </div>
                    
                    <!-- Auto Offline -->
                    <div class="auto-offline-toggle">
                        <div class="day-toggle {{ ($agent->auto_offline ?? true) ? 'active' : '' }}" 
                             onclick="toggleAutoOffline()"
                             id="toggle-auto-offline">
                        </div>
                        <input type="hidden" name="auto_offline" value="{{ ($agent->auto_offline ?? true) ? '1' : '0' }}" id="auto-offline-input">
                        <div>
                            <div class="font-bold text-white">Auto Offline</div>
                            <div class="text-sm text-gray-500">Jiondoe kiatomati nje ya saa za kazi</div>
                        </div>
                    </div>
                </div>
                
                <!-- Save Button -->
                <button type="submit" class="save-btn w-full">
                    💾 Hifadhi Mabadiliko
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Update current time
    function updateCurrentTime() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
        document.getElementById('currentTime').textContent = timeStr;
    }
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    
    // Toggle day
    function toggleDay(day) {
        const toggle = document.getElementById(`toggle-${day}`);
        const input = document.getElementById(`enabled-${day}`);
        const startInput = document.getElementById(`start-${day}`);
        const endInput = document.getElementById(`end-${day}`);
        const hoursLabel = document.getElementById(`hours-${day}`);
        
        const isEnabled = input.value === '1';
        
        if (isEnabled) {
            toggle.classList.remove('active');
            input.value = '0';
            startInput.disabled = true;
            endInput.disabled = true;
            hoursLabel.textContent = 'Imefungwa';
        } else {
            toggle.classList.add('active');
            input.value = '1';
            startInput.disabled = false;
            endInput.disabled = false;
            updateHoursLabel(day);
        }
    }
    
    // Update hours label
    function updateHoursLabel(day) {
        const startInput = document.getElementById(`start-${day}`);
        const endInput = document.getElementById(`end-${day}`);
        const hoursLabel = document.getElementById(`hours-${day}`);
        
        const start = startInput.value.split(':');
        const end = endInput.value.split(':');
        
        const startMinutes = parseInt(start[0]) * 60 + parseInt(start[1]);
        const endMinutes = parseInt(end[0]) * 60 + parseInt(end[1]);
        
        let diff = endMinutes - startMinutes;
        if (diff < 0) diff += 24 * 60; // Handle overnight
        
        const hours = Math.floor(diff / 60);
        hoursLabel.textContent = `${hours} saa`;
    }
    
    // Add event listeners for time inputs
    document.querySelectorAll('.time-input').forEach(input => {
        input.addEventListener('change', (e) => {
            const day = e.target.id.split('-')[1];
            updateHoursLabel(day);
        });
    });
    
    // Set all days
    function setAllDays(enabled) {
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        days.forEach(day => {
            const toggle = document.getElementById(`toggle-${day}`);
            const input = document.getElementById(`enabled-${day}`);
            const startInput = document.getElementById(`start-${day}`);
            const endInput = document.getElementById(`end-${day}`);
            const hoursLabel = document.getElementById(`hours-${day}`);
            
            if (enabled) {
                toggle.classList.add('active');
                input.value = '1';
                startInput.disabled = false;
                endInput.disabled = false;
                updateHoursLabel(day);
            } else {
                toggle.classList.remove('active');
                input.value = '0';
                startInput.disabled = true;
                endInput.disabled = true;
                hoursLabel.textContent = 'Imefungwa';
            }
        });
    }
    
    // Set weekdays only
    function setWeekdays() {
        const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        const weekends = ['saturday', 'sunday'];
        
        weekdays.forEach(day => {
            const toggle = document.getElementById(`toggle-${day}`);
            const input = document.getElementById(`enabled-${day}`);
            const startInput = document.getElementById(`start-${day}`);
            const endInput = document.getElementById(`end-${day}`);
            
            toggle.classList.add('active');
            input.value = '1';
            startInput.disabled = false;
            endInput.disabled = false;
            updateHoursLabel(day);
        });
        
        weekends.forEach(day => {
            const toggle = document.getElementById(`toggle-${day}`);
            const input = document.getElementById(`enabled-${day}`);
            const startInput = document.getElementById(`start-${day}`);
            const endInput = document.getElementById(`end-${day}`);
            const hoursLabel = document.getElementById(`hours-${day}`);
            
            toggle.classList.remove('active');
            input.value = '0';
            startInput.disabled = true;
            endInput.disabled = true;
            hoursLabel.textContent = 'Imefungwa';
        });
    }
    
    // Toggle auto offline
    function toggleAutoOffline() {
        const toggle = document.getElementById('toggle-auto-offline');
        const input = document.getElementById('auto-offline-input');
        
        const isEnabled = input.value === '1';
        
        if (isEnabled) {
            toggle.classList.remove('active');
            input.value = '0';
        } else {
            toggle.classList.add('active');
            input.value = '1';
        }
    }
    
    // Form submission
    document.getElementById('workingHoursForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {};
        
        // Build working_hours object
        data.working_hours = {};
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        days.forEach(day => {
            data.working_hours[day] = {
                enabled: document.getElementById(`enabled-${day}`).value === '1',
                start: document.getElementById(`start-${day}`).value,
                end: document.getElementById(`end-${day}`).value,
            };
        });
        
        data.timezone = formData.get('timezone');
        data.auto_offline = document.getElementById('auto-offline-input').value === '1';
        
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });
            
            if (response.ok) {
                // Show success message
                alert('Mabadiliko yamehifadhiwa!');
            } else {
                throw new Error('Failed to save');
            }
        } catch (error) {
            alert('Hitilafu! Tafadhali jaribu tena.');
            console.error(error);
        }
    });
</script>
@endpush
