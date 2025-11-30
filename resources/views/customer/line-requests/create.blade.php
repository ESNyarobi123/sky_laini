@extends('layouts.dashboard')

@section('title', 'Create Line Request - SKY LAINI')
@section('page-title', 'Create New Line Request')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 500px;
        width: 100%;
        border-radius: 1rem;
        z-index: 1;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 10px 30px rgba(14, 165, 233, 0.1);
    }
    
    .btn-sky {
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-sky:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(14, 165, 233, 0.4);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <form action="{{ route('customer.line-requests.store') }}" method="POST" id="lineRequestForm">
        @csrf
        
        <div class="glass-card rounded-2xl p-6 space-y-6">
            <h2 class="text-2xl font-bold text-sky-900 mb-4">Line Request Details</h2>
            
            <!-- Line Type -->
            <div>
                <label class="block text-sm font-semibold text-sky-900 mb-2">Select Network</label>
                <select name="line_type" id="line_type" required class="w-full px-4 py-3 rounded-xl border-2 border-sky-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-200 outline-none transition">
                    <option value="">Choose a network...</option>
                    <option value="airtel">Airtel</option>
                    <option value="vodacom">Vodacom</option>
                    <option value="halotel">Halotel</option>
                    <option value="tigo">Tigo</option>
                    <option value="zantel">Zantel</option>
                </select>
                @error('line_type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone Number -->
            <div>
                <label class="block text-sm font-semibold text-sky-900 mb-2">Phone Number</label>
                <input type="tel" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', Auth::user()->phone) }}" required 
                       class="w-full px-4 py-3 rounded-xl border-2 border-sky-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-200 outline-none transition"
                       placeholder="Enter phone number">
                @error('customer_phone')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Address -->
            <div>
                <label class="block text-sm font-semibold text-sky-900 mb-2">Address (Optional)</label>
                <textarea name="customer_address" id="customer_address" rows="3" 
                          class="w-full px-4 py-3 rounded-xl border-2 border-sky-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-200 outline-none transition"
                          placeholder="Enter your address">{{ old('customer_address') }}</textarea>
                @error('customer_address')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Map Section -->
            <div>
                <label class="block text-sm font-semibold text-sky-900 mb-2">Select Your Location on Map</label>
                <div class="relative">
                    <div id="map"></div>
                    <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg p-3 shadow-lg z-10">
                        <p class="text-xs text-sky-700 font-medium">Click on map to set location</p>
                        <p class="text-xs text-sky-600 mt-1" id="locationInfo">Getting location...</p>
                    </div>
                </div>
                <input type="hidden" name="customer_latitude" id="customer_latitude" required>
                <input type="hidden" name="customer_longitude" id="customer_longitude" required>
                @error('customer_latitude')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('customer_longitude')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4 pt-4">
                <button type="submit" class="btn-sky px-8 py-3 rounded-xl text-white font-bold transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Submit Request
                </button>
                <a href="{{ route('customer.dashboard') }}" class="px-8 py-3 bg-white/60 backdrop-blur-sm border-2 border-sky-300 rounded-xl text-sky-900 font-bold hover:bg-white/80 transition-all">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map;
    let marker;
    let latitude = null;
    let longitude = null;

    // Initialize map
    function initMap() {
        // Default location (Dar es Salaam, Tanzania)
        const defaultLat = -6.7924;
        const defaultLng = 39.2083;
        
        map = L.map('map', {
            zoomControl: false,
            dragging: false,
            touchZoom: false,
            doubleClickZoom: false,
            scrollWheelZoom: false,
            boxZoom: false,
            keyboard: false
        }).setView([defaultLat, defaultLng], 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{s}/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Try to get user's current location
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                function(position) {
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;
                    
                    map.setView([latitude, longitude], 16);
                    placeMarker(latitude, longitude);
                    updateLocationInfo(latitude, longitude);
                },
                function(error) {
                    console.log('Geolocation error:', error);
                    document.getElementById('locationInfo').textContent = 'Error fetching location. Please enable GPS.';
                    document.getElementById('locationInfo').classList.add('text-red-500');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        } else {
            document.getElementById('locationInfo').textContent = 'Geolocation is not supported by this browser.';
        }
    }

    function placeMarker(lat, lng) {
        if (marker) {
            map.removeLayer(marker);
        }
        
        // Pulse Icon
        const pulseIcon = L.divIcon({
            className: 'css-icon',
            html: '<div class="relative flex h-6 w-6"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span><span class="relative inline-flex rounded-full h-6 w-6 bg-sky-500 border-2 border-white shadow-lg"></span></div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        marker = L.marker([lat, lng], {
            icon: pulseIcon,
            draggable: false // Disable dragging
        }).addTo(map);
    }

    function updateLocationInfo(lat, lng) {
        document.getElementById('customer_latitude').value = lat;
        document.getElementById('customer_longitude').value = lng;
        document.getElementById('locationInfo').innerHTML = `<span class="flex items-center gap-1 text-green-600 font-bold"><span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Live GPS: ${lat.toFixed(6)}, ${lng.toFixed(6)}</span>`;
    }

    // Form validation
    document.getElementById('lineRequestForm').addEventListener('submit', function(e) {
        if (!latitude || !longitude) {
            e.preventDefault();
            alert('Waiting for location... Please ensure GPS is enabled.');
            return false;
        }
    });

    // Initialize map when page loads
    window.addEventListener('DOMContentLoaded', initMap);
</script>
@endpush
@endsection

