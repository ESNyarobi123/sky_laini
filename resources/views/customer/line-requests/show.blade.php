@extends('layouts.dashboard')

@section('title', 'Tracking - SKY LAINI')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #map {
        height: 600px;
        width: 100%;
        border-radius: 1.5rem;
        z-index: 1;
    }
    /* Custom Map Icons */
    .agent-div-icon {
        background: transparent;
        border: none;
    }
    .customer-div-icon {
        background: transparent;
        border: none;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('customer.dashboard') }}" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-white/10 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h1 class="text-3xl font-black text-white">Tracking #{{ $lineRequest->request_number }}</h1>
            </div>
            <p class="text-gray-400 font-medium ml-13">Fuatilia ombi lako la laini hapa.</p>
        </div>

        @php
            $statusColors = match($lineRequest->status->value) {
                'pending' => 'bg-yellow-500/20 text-yellow-500 border-yellow-500/20',
                'in_progress' => 'bg-blue-500/20 text-blue-500 border-blue-500/20',
                'accepted' => 'bg-amber-500/20 text-amber-500 border-amber-500/20',
                'completed' => 'bg-green-500/20 text-green-500 border-green-500/20',
                'cancelled' => 'bg-red-500/20 text-red-500 border-red-500/20',
                default => 'bg-gray-500/20 text-gray-500 border-gray-500/20',
            };
        @endphp
        <div class="px-4 py-2 rounded-full border {{ $statusColors }} backdrop-blur-md flex items-center gap-2">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-current"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-current"></span>
            </span>
            <span class="font-bold text-sm uppercase tracking-wider">{{ str_replace('_', ' ', $lineRequest->status->value) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Map Section -->
        <div class="lg:col-span-2 glass-card rounded-3xl p-1 border border-white/10 relative overflow-hidden">
            <div id="map"></div>
            
            <!-- Floating Status Card -->
            <div class="absolute top-4 left-4 right-4 md:left-auto md:right-4 md:w-80 bg-black/80 backdrop-blur-xl border border-white/10 p-4 rounded-2xl z-[400]">
                <div class="flex items-center gap-4">
                    @if($lineRequest->agent)
                        <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center text-black font-bold text-xl">
                            {{ substr($lineRequest->agent->user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-bold uppercase">Wakala Wako</p>
                            <h3 class="text-white font-bold">{{ $lineRequest->agent->user->name }}</h3>
                            <div class="flex items-center gap-1 text-amber-500 text-xs font-bold">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $lineRequest->agent->rating }} ({{ $lineRequest->agent->total_ratings }})
                            </div>
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-500 animate-pulse">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-bold uppercase">Hali</p>
                            <h3 class="text-white font-bold animate-pulse">Inatafuta Wakala...</h3>
                        </div>
                    @endif
                </div>
                
                @if($lineRequest->agent)
                <div class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-2">
                    <a href="tel:{{ $lineRequest->agent->user->phone }}" class="flex items-center justify-center gap-2 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        Piga Simu
                    </a>
                    <button class="flex items-center justify-center gap-2 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-bold text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        Chat
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <!-- Request Details -->
            <div class="glass-card rounded-3xl p-6 border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Maelezo ya Ombi</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Aina ya Laini</span>
                        <span class="text-white font-bold">{{ ucfirst($lineRequest->line_type->value) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Gharama</span>
                        <span class="text-amber-500 font-bold text-lg">TSh {{ number_format($lineRequest->service_fee) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Namba ya Simu</span>
                        <span class="text-white font-bold">{{ $lineRequest->customer_phone }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Tarehe</span>
                        <span class="text-white font-bold">{{ $lineRequest->created_at->format('d M, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment & Completion -->
            <div class="glass-card rounded-3xl p-1 border border-white/10 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <span class="w-1 h-6 bg-amber-500 rounded-full"></span>
                        Malipo & Kukamilisha
                    </h3>
                    
                    <div id="payment-section">
                        @if($lineRequest->payment_status === 'paid')
                            <!-- Success State -->
                            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-500/10 to-emerald-600/10 border border-green-500/20 p-6 md:p-8 text-center group">
                                <!-- Animated Background Glow -->
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-green-500/5 blur-3xl -z-10 animate-pulse"></div>
                                
                                <div class="mb-8">
                                    <div class="w-20 h-20 mx-auto bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-lg shadow-green-500/30 mb-4 animate-bounce">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <h3 class="text-2xl md:text-3xl font-black text-white mb-2">Malipo Yamekamilika!</h3>
                                    <p class="text-green-200/80 text-sm font-medium">Hii hapa kodi yako ya siri ya kumalizia kazi.</p>
                                </div>

                                <!-- Code Card -->
                                <div class="relative group/code cursor-pointer max-w-sm mx-auto" onclick="copyCode('{{ $lineRequest->confirmation_code }}')">
                                    <div class="absolute inset-0 bg-green-400/20 blur-xl rounded-2xl opacity-0 group-hover/code:opacity-100 transition duration-500"></div>
                                    <div class="relative bg-black/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 flex flex-col items-center gap-3 transform transition group-hover/code:scale-[1.02] group-hover/code:border-green-500/50">
                                        <span class="text-[10px] text-green-400 font-bold tracking-[0.2em] uppercase bg-green-400/10 px-3 py-1 rounded-full">Kodi ya Wakala</span>
                                        
                                        <div class="flex items-center justify-center gap-4 w-full">
                                            <span class="text-4xl md:text-5xl font-black text-white tracking-[0.15em] font-mono drop-shadow-[0_0_15px_rgba(74,222,128,0.5)]" id="code-text">{{ $lineRequest->confirmation_code }}</span>
                                        </div>
                                        
                                        <div class="flex items-center gap-2 text-xs text-gray-400 group-hover/code:text-white transition-colors mt-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            <span id="copy-hint">Gusa kucopy kodi</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instructions -->
                                <div class="mt-8 bg-white/5 rounded-xl p-4 border border-white/5 text-left flex items-start gap-4">
                                    <div class="shrink-0 w-8 h-8 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-500 border border-amber-500/20">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-white font-bold text-sm mb-1">Maelekezo Muhimu</p>
                                        <p class="text-gray-400 text-xs leading-relaxed">
                                            Mpe wakala kodi hii <strong>BAADA</strong> ya yeye kukamilisha usajili wa laini yako. Hii ndio itathibitisha kuwa kazi imekamilika na wakala atalipwa.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Pending Payment State -->
                            <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-6 mb-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-500 shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-amber-500 font-bold text-base mb-1">Malipo Yanahitajika</p>
                                        <p class="text-gray-400 text-sm">Lipia <span class="text-white font-bold">TSh {{ number_format($lineRequest->service_fee) }}</span> ili kupata kodi ya kukamilisha usajili.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <button onclick="initiatePayment()" id="pay-btn" class="group relative w-full py-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-black text-lg shadow-lg shadow-amber-500/20 transition-all transform hover:scale-[1.02] active:scale-[0.98] overflow-hidden">
                                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                <span class="relative flex items-center justify-center gap-3">
                                    <span>Lipia Sasa (ZenoPay)</span>
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </span>
                            </button>
                            <p id="payment-msg" class="text-center text-xs text-gray-400 mt-4 hidden font-medium"></p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div class="glass-card rounded-3xl p-6 border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Eneo Lako</h3>
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">{{ $lineRequest->customer_address ?? 'Coordinates Only' }}</p>
                        <p class="text-gray-500 text-xs mt-1">Lat: {{ number_format($lineRequest->customer_latitude, 6) }}, Lng: {{ number_format($lineRequest->customer_longitude, 6) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Config ---
        const customerLat = {{ $lineRequest->customer_latitude }};
        const customerLng = {{ $lineRequest->customer_longitude }};
        const hasAgent = {{ $lineRequest->agent ? 'true' : 'false' }};
        let agentLat = {{ $lineRequest->agent?->current_latitude ?? 'null' }};
        let agentLng = {{ $lineRequest->agent?->current_longitude ?? 'null' }};

        // --- Icons ---
        const customerIcon = L.divIcon({
            className: 'customer-div-icon',
            html: `
                <div class="relative flex h-6 w-6">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-6 w-6 bg-blue-500 border-2 border-white shadow-lg"></span>
                </div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const agentIconContent = `
            <div id="agent-marker-icon" style="
                transition: transform 0.5s linear;
                width: 40px; height: 40px;
                display: flex; align-items: center; justify-content: center;
            ">
                <div style="
                    width: 0; height: 0; 
                    border-left: 10px solid transparent;
                    border-right: 10px solid transparent;
                    border-bottom: 20px solid #f59e0b;
                    filter: drop-shadow(0 0 5px rgba(0,0,0,0.5));
                "></div>
            </div>
        `;
        
        const agentIcon = L.divIcon({
            className: 'agent-div-icon',
            html: agentIconContent,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        // --- Map Init ---
        const map = L.map('map', { zoomControl: false }).setView([customerLat, customerLng], 15);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        // Customer Marker
        L.marker([customerLat, customerLng], {icon: customerIcon}).addTo(map);

        // Agent Marker & Routing
        let agentMarker = null;
        let routingControl = null;

        // --- Plot Nearby Agents (Visual Trust) ---
        const nearbyAgents = @json($nearbyAgents);
        
        const availableAgentIcon = L.divIcon({
            className: 'available-agent-icon',
            html: `
                <div class="w-8 h-8 bg-gray-700/80 rounded-full border border-white/20 flex items-center justify-center shadow-lg backdrop-blur-sm transition hover:scale-110">
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        nearbyAgents.forEach(agent => {
            // Don't show the assigned agent again if they are in this list
            if (hasAgent && agent.id === {{ $lineRequest->agent_id ?? 'null' }}) return;

            L.marker([agent.current_latitude, agent.current_longitude], {icon: availableAgentIcon})
                .addTo(map)
                .bindPopup(`<div class="text-xs font-bold text-black p-1">${agent.user.name}</div>`);
        });

        if (hasAgent && agentLat && agentLng) {
            agentMarker = L.marker([agentLat, agentLng], {icon: agentIcon}).addTo(map);
            drawRoute(agentLat, agentLng);
            startTracking();
        }

        function drawRoute(aLat, aLng) {
            if (routingControl) map.removeControl(routingControl);
            
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(aLat, aLng),
                    L.latLng(customerLat, customerLng)
                ],
                lineOptions: {
                    styles: [{color: '#f59e0b', opacity: 0.8, weight: 6}]
                },
                createMarker: function() { return null; },
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                show: false
            }).addTo(map);
        }

        // --- Real-time Tracking ---
        function startTracking() {
            setInterval(() => {
                fetch('{{ route("customer.tracking.agent", $lineRequest->id) }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data.latitude && data.longitude) {
                            const newLatLng = new L.LatLng(data.latitude, data.longitude);
                            
                            // Update Marker
                            if (agentMarker) {
                                // Calculate bearing for rotation
                                const oldLatLng = agentMarker.getLatLng();
                                if (oldLatLng.lat !== newLatLng.lat || oldLatLng.lng !== newLatLng.lng) {
                                    const bearing = getBearing(oldLatLng.lat, oldLatLng.lng, newLatLng.lat, newLatLng.lng);
                                    const iconEl = document.getElementById('agent-marker-icon');
                                    if(iconEl) iconEl.style.transform = `rotate(${bearing}deg)`;
                                }
                                agentMarker.setLatLng(newLatLng);
                            } else {
                                agentMarker = L.marker(newLatLng, {icon: agentIcon}).addTo(map);
                            }

                            // Update Route (less frequently ideally, but okay for now)
                            // We can optimize to only update route if distance changes significantly
                        }
                    })
                    .catch(err => console.error('Tracking error:', err));
            }, 5000); // Poll every 5 seconds
        }

        // Helper: Calculate Bearing
        function getBearing(startLat, startLng, destLat, destLng) {
            startLat = toRadians(startLat);
            startLng = toRadians(startLng);
            destLat = toRadians(destLat);
            destLng = toRadians(destLng);

            y = Math.sin(destLng - startLng) * Math.cos(destLat);
            x = Math.cos(startLat) * Math.sin(destLat) -
                Math.sin(startLat) * Math.cos(destLat) * Math.cos(destLng - startLng);
            brng = Math.atan2(y, x);
            brng = toDegrees(brng);
            return (brng + 360) % 360;
        }
        function toRadians(degrees) { return degrees * Math.PI / 180; }
        function toDegrees(radians) { return radians * 180 / Math.PI; }
    });

    // Payment Logic
    function initiatePayment() {
        const btn = document.getElementById('pay-btn');
        const msg = document.getElementById('payment-msg');
        
        if(!confirm('Unataka kulipia TSh {{ number_format($lineRequest->service_fee) }} kupitia ZenoPay?')) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="animate-pulse">Inaanzisha Malipo...</span>';
        msg.classList.remove('hidden');
        msg.textContent = 'Tafadhali subiri USSD push kwenye simu yako...';

        fetch('{{ route("customer.requests.pay", $lineRequest->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.message) {
                msg.textContent = data.message;
                msg.className = 'text-center text-xs text-green-400 mt-3';
                // Start polling for status
                pollPaymentStatus();
            } else {
                throw new Error('Payment initiation failed');
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = '<span>Jaribu Tena</span>';
            msg.textContent = 'Imeshindikana kuanzisha malipo. Tafadhali jaribu tena.';
            msg.className = 'text-center text-xs text-red-400 mt-3';
        });
    }

    function pollPaymentStatus() {
        const interval = setInterval(() => {
            fetch('{{ route("customer.requests.payment-status", $lineRequest->id) }}')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'paid') {
                        clearInterval(interval);
                        location.reload(); // Reload to show code
                    }
                })
                .catch(err => console.error('Polling error:', err));
        }, 5000); // Check every 5 seconds
    }

    function copyCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            const codeText = document.getElementById('code-text');
            const copyHint = document.getElementById('copy-hint');
            
            // Visual Feedback
            codeText.classList.add('text-green-400');
            copyHint.textContent = 'Imenakiliwa!';
            copyHint.classList.add('text-green-400', 'font-bold');
            
            setTimeout(() => {
                codeText.classList.remove('text-green-400');
                copyHint.textContent = 'Gusa kucopy kodi';
                copyHint.classList.remove('text-green-400', 'font-bold');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
</script>
@endpush

