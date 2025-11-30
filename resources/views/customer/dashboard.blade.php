@extends('layouts.dashboard')

@section('title', 'Customer Dashboard - SKY LAINI')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(245, 158, 11, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .btn-gold {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
        transition: all 0.3s ease;
        color: black;
        font-weight: bold;
    }
</style>
@endpush



@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Data from Controller ---
        const initialAgents = @json($agents ?? []);
        const activeRequest = @json($activeRequest ?? null);
        const hasActiveRequests = {{ (($stats['pending_requests'] ?? 0) > 0 || ($stats['in_progress_requests'] ?? 0) > 0) ? 'true' : 'false' }};

        // --- Map Init ---
        const defaultLat = -6.7924;
        const defaultLng = 39.2083;
        const map = L.map('customer-map').setView([defaultLat, defaultLng], 13);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        // --- Professional Icons ---
        
        // Customer Icon (User/Person)
        const customerIcon = L.divIcon({
            className: 'customer-icon',
            html: `
                <div class="relative flex items-center justify-center w-10 h-10">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-30"></span>
                    <div class="relative w-10 h-10 bg-blue-600 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                </div>`,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        // Agent Icon SVG Builder
        function getAgentIcon(isOnline) {
            const color = isOnline ? '#22c55e' : '#ef4444'; // Green or Red
            const glowColor = isOnline ? 'rgba(34, 197, 94, 0.5)' : 'rgba(239, 68, 68, 0.5)';
            
            return L.divIcon({
                className: 'agent-icon',
                html: `
                    <div class="relative flex items-center justify-center w-12 h-12 transition-all duration-500">
                        <div class="absolute inset-0 rounded-full opacity-20" style="background-color: ${color}; box-shadow: 0 0 15px ${glowColor};"></div>
                        <div class="relative w-10 h-10 bg-gray-900 rounded-full border-2 flex items-center justify-center text-white shadow-xl" style="border-color: ${color};">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                        </div>
                        <div class="absolute -bottom-1 w-3 h-3 rounded-full border-2 border-gray-900" style="background-color: ${color};"></div>
                    </div>`,
                iconSize: [48, 48],
                iconAnchor: [24, 24]
            });
        }

        // --- User Location ---
        let userMarker = null;

        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    if (!userMarker) {
                        userMarker = L.marker([lat, lng], {icon: customerIcon}).addTo(map);
                        map.setView([lat, lng], 14);
                    } else {
                        userMarker.setLatLng([lat, lng]);
                    }

                    // Background Update for Active Requests
                    if (hasActiveRequests) {
                        updateServerLocation(lat, lng);
                    }
                },
                (error) => console.error("GPS Error:", error),
                { enableHighAccuracy: true }
            );
        }

        function updateServerLocation(lat, lng) {
            fetch('{{ route("customer.location.update") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            }).catch(err => console.error('Loc Update Error', err));
        }

        // --- Agent Display Logic ---
        let agentMarkers = {};

        function updateAgentsOnMap(agentsList) {
            // Remove markers for agents not in list
            const currentIds = agentsList.map(a => a.id);
            for (let id in agentMarkers) {
                if (!currentIds.includes(parseInt(id))) {
                    map.removeLayer(agentMarkers[id]);
                    delete agentMarkers[id];
                }
            }

            // Add or Update markers
            agentsList.forEach(agent => {
                if (agent.current_latitude && agent.current_longitude) {
                    const latLng = [agent.current_latitude, agent.current_longitude];
                    const icon = getAgentIcon(agent.is_online);
                    const statusText = agent.is_online 
                        ? '<span class="text-green-500 font-bold">Online</span>' 
                        : '<span class="text-red-500 font-bold">Offline</span>';

                    if (agentMarkers[agent.id]) {
                        agentMarkers[agent.id].setLatLng(latLng);
                        agentMarkers[agent.id].setIcon(icon);
                        agentMarkers[agent.id].setPopupContent(`
                            <div class="text-center min-w-[150px]">
                                <div class="font-bold text-gray-800 text-lg">${agent.user.name}</div>
                                <div class="text-xs mb-2">${statusText}</div>
                                <div class="text-xs text-gray-500">${agent.phone}</div>
                            </div>
                        `);
                    } else {
                        const marker = L.marker(latLng, {icon: icon})
                            .addTo(map)
                            .bindPopup(`
                                <div class="text-center min-w-[150px]">
                                    <div class="font-bold text-gray-800 text-lg">${agent.user.name}</div>
                                    <div class="text-xs mb-2">${statusText}</div>
                                    <div class="text-xs text-gray-500">${agent.phone}</div>
                                </div>
                            `);
                        agentMarkers[agent.id] = marker;
                    }
                }
            });
        }

        // Initial Load
        updateAgentsOnMap(initialAgents);

        // Polling for All Agents
        if (!activeRequest) {
            setInterval(() => {
                fetch('{{ route("customer.dashboard.agents") }}')
                    .then(res => res.json())
                    .then(data => updateAgentsOnMap(data))
                    .catch(err => console.error('Error fetching agents:', err));
            }, 5000);
        } else if (activeRequest && activeRequest.agent) {
             // If there is an active request, we might want to focus only on that agent
             // But for now, let's keep the logic simple or just track the specific agent
             // For this task, the user specifically asked about seeing online/offline status generally.
             // Let's stick to the tracking logic for active requests if it exists, or just poll all.
             
             // Existing tracking logic for single agent
             const agent = activeRequest.agent;
             function trackAssignedAgent() {
                fetch(`/customer/tracking/${activeRequest.id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.latitude && data.longitude) {
                            // We construct a single-item list to reuse the update function
                            // Note: We need to know the online status. The tracking endpoint might need to return it.
                            // If tracking endpoint doesn't return is_online, we might default to true or fetch it.
                            // For now, let's assume the tracking endpoint returns basic location.
                            // Let's just use the general poll if we want status updates.
                        }
                    });
             }
             // Actually, let's just use the general poll for now as it includes status
             setInterval(() => {
                fetch('{{ route("customer.dashboard.agents") }}')
                    .then(res => res.json())
                    .then(data => updateAgentsOnMap(data))
                    .catch(err => console.error('Error fetching agents:', err));
            }, 5000);
        }
    });
</script>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Karibu, {{ Auth::user()->name }} 👋</h1>
            <p class="text-gray-400 font-medium">Dhibiti maombi yako ya laini za simu hapa.</p>
        </div>
        
        <a href="{{ route('customer.line-requests.create') }}" class="px-6 py-3 rounded-xl btn-gold text-black font-bold shadow-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Omba Laini Mpya
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Requests -->
        <div class="stat-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-2 text-sm uppercase tracking-wider">Jumla ya Maombi</div>
            <div class="text-4xl font-black text-white mb-1">{{ $stats['total_requests'] ?? 0 }}</div>
            <div class="text-gray-500 text-sm font-bold">Tangu uanze</div>
        </div>

        <!-- Pending -->
        <div class="stat-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-2 text-sm uppercase tracking-wider">Zinasubiri</div>
            <div class="text-4xl font-black text-white mb-1">{{ $stats['pending_requests'] ?? 0 }}</div>
            <div class="text-yellow-500 text-sm font-bold">Inashughulikiwa</div>
        </div>

        <!-- In Progress -->
        <div class="stat-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-2 text-sm uppercase tracking-wider">Zinaendelea</div>
            <div class="text-4xl font-black text-white mb-1">{{ $stats['in_progress_requests'] ?? 0 }}</div>
            <div class="text-blue-500 text-sm font-bold">Wakala yuko njiani</div>
        </div>

        <!-- Completed -->
        <div class="stat-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-2 text-sm uppercase tracking-wider">Zimekamilika</div>
            <div class="text-4xl font-black text-white mb-1">{{ $stats['completed_requests'] ?? 0 }}</div>
            <div class="text-green-500 text-sm font-bold">Umepata laini</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="#" class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition flex flex-col items-center justify-center gap-2 group">
            <div class="w-12 h-12 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-500 group-hover:scale-110 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <span class="text-white font-bold text-sm">Omba Laini</span>
        </a>
        <a href="#" class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition flex flex-col items-center justify-center gap-2 group">
            <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-500 group-hover:scale-110 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <span class="text-white font-bold text-sm">Historia</span>
        </a>
        <a href="#" class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition flex flex-col items-center justify-center gap-2 group">
            <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center text-green-500 group-hover:scale-110 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <span class="text-white font-bold text-sm">Malipo</span>
        </a>
        <a href="#" class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition flex flex-col items-center justify-center gap-2 group">
            <div class="w-12 h-12 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-500 group-hover:scale-110 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <span class="text-white font-bold text-sm">Msaada</span>
        </a>
    </div>

    <!-- Map Section -->
    <div class="glass-card rounded-3xl p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">
                @if($activeRequest)
                    Tracking Agent: {{ $activeRequest->agent?->user?->name ?? 'Unknown Agent' }}
                @else
                    Available Agents Nearby
                @endif
            </h2>
            <div class="flex items-center gap-2">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-sm text-gray-400">Live Map</span>
            </div>
        </div>
        
        <div id="customer-map" class="h-[400px] w-full rounded-2xl border border-white/10 z-0"></div>
    </div>

    <!-- Recent Requests -->
    <div class="glass-card rounded-3xl p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h2 class="text-xl font-bold text-white">Maombi ya Hivi Karibuni</h2>
            
            <div class="flex items-center gap-2 bg-white/5 p-1 rounded-xl border border-white/10">
                <button onclick="filterRequests('all')" class="filter-btn active px-4 py-2 rounded-lg text-xs font-bold transition-all hover:bg-white/10 text-white bg-white/10">Zote</button>
                <button onclick="filterRequests('active')" class="filter-btn px-4 py-2 rounded-lg text-xs font-bold transition-all hover:bg-white/10 text-gray-400">Active</button>
                <button onclick="filterRequests('cancelled')" class="filter-btn px-4 py-2 rounded-lg text-xs font-bold transition-all hover:bg-white/10 text-gray-400">Cancelled</button>
            </div>

            <a href="{{ route('customer.line-requests.index') }}" class="hidden md:block text-amber-500 font-bold hover:text-amber-400 text-sm">Ona Yote →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-white/10">
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider">Aina ya Laini</th>
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider">Wakala</th>
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider">Tarehe</th>
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider">Hali</th>
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider text-right">Kitendo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="requests-table-body">
                    @forelse($recentRequests ?? [] as $request)
                        <tr class="group hover:bg-white/5 transition request-row" data-status="{{ $request->status->value ?? 'pending' }}">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center text-white font-bold">
                                        {{ substr(ucfirst($request->line_type->value ?? 'N'), 0, 1) }}
                                    </div>
                                    <span class="font-bold text-white">{{ ucfirst($request->line_type->value ?? 'Unknown') }}</span>
                                </div>
                            </td>
                            <td class="py-4 text-gray-300">{{ $request->agent?->user?->name ?? 'Inatafuta...' }}</td>
                            <td class="py-4 text-gray-400 text-sm">{{ $request->created_at->format('d M, Y') }}</td>
                            <td class="py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-500/20 text-yellow-500',
                                        'accepted' => 'bg-blue-500/20 text-blue-500',
                                        'in_progress' => 'bg-blue-500/20 text-blue-500',
                                        'completed' => 'bg-green-500/20 text-green-500',
                                        'cancelled' => 'bg-red-500/20 text-red-500',
                                    ];
                                    $statusClass = $statusColors[$request->status->value ?? 'pending'] ?? 'bg-gray-500/20 text-gray-500';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $request->status->value ?? 'Pending')) }}
                                </span>
                            </td>
                            <td class="py-4 text-right flex items-center justify-end gap-2">
                                <a href="{{ route('customer.line-requests.show', $request->id) }}" class="text-amber-500 font-bold hover:text-amber-400 text-sm">Angalia</a>
                                @if(in_array($request->status, [\App\RequestStatus::Pending, \App\RequestStatus::Accepted]))
                                    <form action="{{ route('customer.line-requests.cancel', $request->id) }}" method="POST" onsubmit="return confirm('Una uhakika unataka kufuta ombi hili?');">
                                        @csrf
                                        <button type="submit" class="text-red-500 font-bold hover:text-red-400 text-sm">Futa</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                Hujafanya maombi yoyote bado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 md:hidden text-center">
            <a href="{{ route('customer.line-requests.index') }}" class="text-amber-500 font-bold hover:text-amber-400 text-sm">Ona Yote →</a>
        </div>
    </div>

    <script>
        function filterRequests(filter) {
            // Update buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('bg-white/10', 'text-white');
                btn.classList.add('text-gray-400');
            });
            event.target.classList.remove('text-gray-400');
            event.target.classList.add('bg-white/10', 'text-white');

            // Filter rows
            const rows = document.querySelectorAll('.request-row');
            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'active') {
                    if (['pending', 'accepted', 'in_progress'].includes(status)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                } else if (filter === 'cancelled') {
                    if (status === 'cancelled') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        }
    </script>
</div>
@endsection


