@extends('layouts.dashboard')

@section('title', 'Job Details - SKY LAINI')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    #map {
        height: 600px;
        width: 100%;
        border-radius: 1.5rem;
        z-index: 1;
    }
    /* Custom Map Icons */
    .agent-div-icon { background: transparent; border: none; }
    .customer-div-icon { background: transparent; border: none; }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('agent.dashboard') }}" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-white/10 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h1 class="text-3xl font-black text-white">Job #{{ $lineRequest->request_number }}</h1>
            </div>
            <p class="text-gray-400 font-medium ml-13">Maelezo ya kazi na ramani ya kuelekea kwa mteja.</p>
        </div>

        <div class="flex gap-3">
            <button class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold transition">
                Sitisha Kazi
            </button>
            <button onclick="completeJob({{ $lineRequest->id }})" class="px-6 py-3 rounded-xl bg-green-500 hover:bg-green-400 text-black font-bold shadow-lg shadow-green-500/20 transition">
                Kamilisha Kazi
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Map Section -->
        <div class="lg:col-span-2 glass-card rounded-3xl p-1 border border-white/10 relative overflow-hidden">
            <div id="map"></div>
            
            <!-- Floating Customer Card -->
            <div class="absolute top-4 left-4 right-4 md:left-auto md:right-4 md:w-80 bg-black/80 backdrop-blur-xl border border-white/10 p-4 rounded-2xl z-[400]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xl">
                        {{ substr($lineRequest->customer->user->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase">Mteja</p>
                        <h3 class="text-white font-bold">{{ $lineRequest->customer->user->name }}</h3>
                        <div class="flex items-center gap-1 text-blue-400 text-xs font-bold">
                            {{ $lineRequest->line_type }}
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-2">
                    <a href="tel:{{ $lineRequest->customer_phone }}" class="flex items-center justify-center gap-2 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        Piga Simu
                    </a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $lineRequest->customer_latitude }},{{ $lineRequest->customer_longitude }}" target="_blank" class="flex items-center justify-center gap-2 py-2 rounded-xl bg-blue-500 hover:bg-blue-400 text-white font-bold text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Google Maps
                    </a>
                </div>
            </div>
        </div>

        <!-- Details Column -->
        <div class="space-y-6">
            <!-- Request Details -->
            <div class="glass-card rounded-3xl p-6 border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Maelezo ya Kazi</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Aina ya Laini</span>
                        <span class="text-white font-bold">{{ ucfirst($lineRequest->line_type->value) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Malipo Yako</span>
                        <span class="text-green-500 font-bold text-lg">TSh {{ number_format($lineRequest->service_fee) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Namba ya Mteja</span>
                        <span class="text-white font-bold">{{ $lineRequest->customer_phone }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Tarehe</span>
                        <span class="text-white font-bold">{{ $lineRequest->created_at->format('d M, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div class="glass-card rounded-3xl p-6 border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Eneo la Mteja</h3>
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
        let lastLat = null;
        let lastLng = null;

        // --- Real-time Tracking ---
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // Calculate Bearing for Rotation
                    if (lastLat && (lat !== lastLat || lng !== lastLng)) {
                        const bearing = getBearing(lastLat, lastLng, lat, lng);
                        const iconElement = document.getElementById('agent-marker-icon');
                        if (iconElement) {
                            iconElement.style.transform = `rotate(${bearing}deg)`;
                        }
                    }
                    lastLat = lat;
                    lastLng = lng;

                    const newLatLng = new L.LatLng(lat, lng);

                    if (!agentMarker) {
                        agentMarker = L.marker(newLatLng, {icon: agentIcon}).addTo(map);
                        // Draw initial route
                        drawRoute(lat, lng);
                    } else {
                        agentMarker.setLatLng(newLatLng);
                        // Update route occasionally or if needed
                        // For now, we can redraw it if the distance is significant, or just let it be
                    }

                    // Send to Backend
                    fetch('{{ route("agent.location.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ latitude: lat, longitude: lng })
                    }).catch(err => console.error('Location update failed', err));
                },
                (error) => console.error("GPS Error:", error),
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
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

    function completeJob(id) {
        const code = prompt("Tafadhali ingiza kodi ya kukamilisha kutoka kwa mteja:");
        if (!code) return;

        fetch(`/agent/requests/${id}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ code: code })
        })
        .then(res => res.json())
        .then(data => {
            if (data.message === 'Job completed successfully!') {
                alert(data.message);
                window.location.href = '{{ route("agent.dashboard") }}';
            } else {
                alert('Imeshindikana: ' + (data.message || 'Kodi siyo sahihi'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Kuna tatizo la kiufundi.');
        });
    }
</script>
@endpush
