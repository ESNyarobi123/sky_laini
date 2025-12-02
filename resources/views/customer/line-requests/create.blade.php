@extends('layouts.dashboard')

@section('title', 'Ombi Jipya - SKY LAINI')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .glass-form {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .form-input {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .form-input:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
    }
    .map-container {
        border-radius: 1.5rem;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }
    .network-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .network-card:hover {
        transform: translateY(-2px);
    }
    .network-radio:checked + .network-card {
        background: rgba(245, 158, 11, 0.1);
        border-color: #f59e0b;
    }
    .network-radio:checked + .network-card .check-icon {
        opacity: 1;
        transform: scale(1);
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight mb-2">Ombi Jipya la Laini</h1>
            <p class="text-gray-400">Jaza fomu hii ili kuletewa laini yako mpaka ulipo.</p>
        </div>
        <a href="{{ route('customer.dashboard') }}" class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition border border-white/5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </a>
    </div>

    <form action="{{ route('customer.line-requests.store') }}" method="POST" id="lineRequestForm" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Details -->
            <div class="space-y-6">
                <!-- Network Selection -->
                <div class="glass-form rounded-3xl p-6">
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Chagua Mtandao</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['vodacom', 'airtel', 'tigo', 'halotel', 'zantel'] as $network)
                        <label class="relative">
                            <input type="radio" name="line_type" value="{{ $network }}" class="network-radio peer sr-only" required>
                            <div class="network-card p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-white font-bold capitalize">
                                    {{ substr($network, 0, 1) }}
                                </div>
                                <span class="text-white font-bold capitalize">{{ $network }}</span>
                                <div class="check-icon absolute top-2 right-2 w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center text-black opacity-0 transform scale-0 transition-all duration-300">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('line_type')
                        <p class="text-red-500 text-sm mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Details -->
                <div class="glass-form rounded-3xl p-6 space-y-4">
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Mawasiliano</label>
                    
                    <div>
                        <label class="text-xs text-gray-500 font-bold mb-1 block">Namba ya Simu</label>
                        <input type="tel" name="customer_phone" value="{{ old('customer_phone', Auth::user()->phone) }}" required 
                               class="form-input w-full px-4 py-3 rounded-xl outline-none font-bold"
                               placeholder="Mfano: 0712345678">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 font-bold mb-1 block">Maelezo ya Ziada ya Eneo (Hiari)</label>
                        <textarea name="customer_address" rows="3" 
                                  class="form-input w-full px-4 py-3 rounded-xl outline-none font-medium text-sm"
                                  placeholder="Mfano: Karibu na shule ya msingi, nyumba ya rangi ya blue...">{{ old('customer_address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Map -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider">Eneo Lako (GPS)</label>
                    <div id="gps-status" class="flex items-center gap-2 text-xs font-bold text-amber-500">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Inatafuta...
                    </div>
                </div>

                <div class="map-container relative h-[500px] w-full bg-gray-900 group">
                    <div id="map" class="w-full h-full z-0"></div>
                    
                    <!-- Center Marker Overlay (Visual Guide) -->
                    <div class="absolute inset-0 pointer-events-none z-10 flex items-center justify-center">
                        <div class="relative -mt-8">
                            <div class="w-4 h-4 bg-amber-500 rounded-full shadow-[0_0_20px_rgba(245,158,11,0.8)] animate-pulse mx-auto"></div>
                            <div class="w-0.5 h-8 bg-amber-500 mx-auto"></div>
                            <div class="absolute top-full left-1/2 -translate-x-1/2 mt-1 px-3 py-1 bg-black/80 backdrop-blur text-amber-500 text-xs font-bold rounded-full whitespace-nowrap border border-amber-500/30">
                                Weka hapa
                            </div>
                        </div>
                    </div>

                    <!-- Map Controls -->
                    <div class="absolute bottom-6 right-6 z-20 flex flex-col gap-3">
                        <button type="button" onclick="locateUser()" class="p-4 rounded-2xl bg-amber-500 text-black shadow-lg shadow-amber-500/20 hover:bg-amber-400 transition transform hover:scale-105 active:scale-95">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="customer_latitude" id="customer_latitude" required>
                <input type="hidden" name="customer_longitude" id="customer_longitude" required>
                
                <p class="text-xs text-gray-500 text-center">
                    Sogeza ramani ili kuweka alama mahali sahihi ulipo.
                </p>
            </div>
        </div>

        <!-- Submit Action -->
        <div class="pt-6 border-t border-white/10 flex justify-end">
            <button type="submit" id="submitBtn" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 text-black font-black text-lg shadow-lg shadow-amber-500/20 hover:shadow-amber-500/40 transition transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-3">
                <span>Tuma Ombi</span>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let map;
    let userMarker;
    let isLocationLocked = false;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map
        map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([-6.7924, 39.2083], 13);

        // Dark Mode Tiles
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 20
        }).addTo(map);

        // Locate User immediately
        locateUser();

        // Update coordinates when map moves
        map.on('move', function() {
            const center = map.getCenter();
            updateCoordinates(center.lat, center.lng);
        });

        // Form Validation
        document.getElementById('lineRequestForm').addEventListener('submit', function(e) {
            const lat = document.getElementById('customer_latitude').value;
            const lng = document.getElementById('customer_longitude').value;
            
            if (!lat || !lng) {
                e.preventDefault();
                alert('Tafadhali subiri GPS ipatikane au chagua eneo kwenye ramani.');
            }
        });
    });

    function locateUser() {
        const statusEl = document.getElementById('gps-status');
        statusEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Inatafuta...';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    map.setView([lat, lng], 18, { animate: true });
                    updateCoordinates(lat, lng);
                    
                    statusEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500"></span> GPS Imepatikana';
                    statusEl.className = 'flex items-center gap-2 text-xs font-bold text-green-500';
                },
                (error) => {
                    console.error(error);
                    statusEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-red-500"></span> GPS Imeshindikana';
                    statusEl.className = 'flex items-center gap-2 text-xs font-bold text-red-500';
                    alert('Imeshindikana kupata eneo lako. Tafadhali washa GPS au sogeza ramani mwenyewe.');
                },
                { enableHighAccuracy: true }
            );
        } else {
            alert('Browser yako haisupport GPS.');
        }
    }

    function updateCoordinates(lat, lng) {
        document.getElementById('customer_latitude').value = lat;
        document.getElementById('customer_longitude').value = lng;
    }
</script>
@endpush
