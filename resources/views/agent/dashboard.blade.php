@extends('layouts.dashboard')

@section('title', 'Agent Dashboard - SKY LAINI')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
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
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }
    .toggle-checkbox:checked {
        right: 0;
        border-color: #f59e0b;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #f59e0b;
    }
    #map {
        height: 100%;
        width: 100%;
        border-radius: 1rem;
        z-index: 1;
    }
</style>
@endpush

@section('content')
<div class="space-y-8 pb-20">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black text-white mb-2 tracking-tight">Karibu, {{ Auth::user()->name }} 👋</h1>
            <p class="text-gray-400 font-medium text-lg">Hapa kuna muhtasari wa kazi zako leo.</p>
        </div>
        
        <div class="flex items-center gap-4 bg-white/5 p-3 rounded-2xl border border-white/10 backdrop-blur-md shadow-xl">
            <span class="text-sm font-bold text-gray-300 px-2 uppercase tracking-wider">Niko Hewani</span>
            <div class="relative inline-block w-14 mr-2 align-middle select-none transition duration-200 ease-in">
                <input type="checkbox" name="toggle" id="toggle" {{ $agent->is_online ? 'checked' : '' }} class="toggle-checkbox absolute block w-7 h-7 rounded-full bg-white border-4 appearance-none cursor-pointer transition-all duration-300 left-0 checked:left-7 checked:bg-black checked:border-amber-500 shadow-sm"/>
                <label for="toggle" class="toggle-label block overflow-hidden h-7 rounded-full bg-gray-700 cursor-pointer transition-colors duration-300"></label>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Earnings -->
        <div class="stat-card rounded-[2rem] p-8 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 p-4 opacity-10 group-hover:opacity-20 transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                <svg class="w-32 h-32 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="relative z-10">
                <div class="text-gray-400 font-bold mb-3 text-xs uppercase tracking-[0.2em]">Mapato ya Leo</div>
                <div class="text-4xl lg:text-5xl font-black text-white mb-2 tracking-tight">TSh {{ number_format($stats['total_earnings']) }}</div>
                <div class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-green-500/10 text-green-500 text-xs font-bold">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    +0% vs jana
                </div>
            </div>
        </div>

        <!-- Completed Jobs -->
        <div class="stat-card rounded-[2rem] p-8 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 p-4 opacity-10 group-hover:opacity-20 transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                <svg class="w-32 h-32 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="relative z-10">
                <div class="text-gray-400 font-bold mb-3 text-xs uppercase tracking-[0.2em]">Kazi Zilizokamilika</div>
                <div class="text-4xl lg:text-5xl font-black text-white mb-2 tracking-tight">{{ $stats['completed_requests'] }}</div>
                <div class="text-amber-500 text-xs font-bold">{{ $stats['pending_requests'] }} zinasubiri</div>
            </div>
        </div>

        <!-- Rating -->
        <div class="stat-card rounded-[2rem] p-8 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 p-4 opacity-10 group-hover:opacity-20 transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                <svg class="w-32 h-32 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
            </div>
            <div class="relative z-10">
                <div class="text-gray-400 font-bold mb-3 text-xs uppercase tracking-[0.2em]">Ukadiriaji</div>
                <div class="text-4xl lg:text-5xl font-black text-white mb-2 tracking-tight">{{ number_format($stats['rating'], 1) }}</div>
                <div class="text-gray-500 text-xs font-bold">Nyota</div>
            </div>
        </div>

        <!-- Wallet -->
        <div class="stat-card rounded-[2rem] p-8 relative overflow-hidden group bg-gradient-to-br from-amber-900/40 to-black border-amber-500/30 shadow-2xl shadow-amber-900/20">
            <div class="absolute -right-6 -top-6 p-4 opacity-20 group-hover:opacity-30 transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">
                <svg class="w-32 h-32 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div>
                    <div class="text-amber-500 font-bold mb-3 text-xs uppercase tracking-[0.2em]">Salio la Wallet</div>
                    <div class="text-4xl lg:text-5xl font-black text-white mb-4 tracking-tight">TSh {{ number_format($stats['wallet_balance']) }}</div>
                </div>
                <button class="w-full py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm transition border border-white/10 backdrop-blur-sm flex items-center justify-center gap-2 group-hover:border-amber-500/50">
                    <span>Toa Pesa</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 space-y-8">
            <!-- Active Jobs Section -->
            <div id="active-jobs-container">
            @if($activeJobs->count() > 0)
            <div>
                <h2 class="text-2xl font-black text-white mb-6 flex items-center gap-3">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    Kazi Zangu ({{ $activeJobs->count() }})
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($activeJobs as $job)
                    <div class="glass-card p-1 rounded-[2rem] border border-green-500/30 relative overflow-hidden group hover:border-green-500/50 transition-all duration-300">
                        <div class="bg-black/40 backdrop-blur-xl p-6 rounded-[1.8rem] h-full flex flex-col">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <svg class="w-40 h-40 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            
                            <div class="relative z-10 flex-1">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-700 flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-green-500/20">
                                            {{ substr($job->customer->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-white text-xl tracking-tight">{{ $job->customer->user->name }}</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="px-2 py-0.5 rounded bg-green-500/20 text-green-400 text-[10px] font-black uppercase tracking-wider border border-green-500/20">
                                                    Inaendelea
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-black text-white tracking-tight">TSh {{ number_format($job->service_fee) }}</div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Malipo</div>
                                    </div>
                                </div>

                                <div class="space-y-3 mb-8">
                                    <div class="flex items-center gap-3 text-gray-300 bg-white/5 p-3 rounded-xl border border-white/5">
                                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </div>
                                        <span class="text-sm font-medium truncate">{{ $job->customer_address ?? 'Eneo la Mteja' }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-300 bg-white/5 p-3 rounded-xl border border-white/5">
                                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        </div>
                                        <span class="text-sm font-medium">{{ $job->customer_phone }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mt-auto">
                                <a href="{{ route('agent.requests.show', $job->id) }}" class="flex items-center justify-center gap-2 py-3.5 rounded-xl bg-green-500 hover:bg-green-400 text-black font-bold shadow-lg shadow-green-500/20 transition transform hover:-translate-y-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                    Ramani
                                </a>
                                <button onclick="releaseRequest({{ $job->id }})" class="flex items-center justify-center gap-2 py-3.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-500 font-bold border border-red-500/20 transition hover:border-red-500/40">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Ghairi
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            </div>

            <!-- Available Gigs -->
            <div class="glass-card rounded-[2.5rem] p-8 border border-white/5">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-black text-white flex items-center gap-3">
                        <span class="w-3 h-8 bg-amber-500 rounded-full shadow-[0_0_15px_rgba(245,158,11,0.5)]"></span>
                        Maombi Mapya
                    </h2>
                    <div class="flex gap-3">
                        <button onclick="toggleMapMode()" class="flex items-center gap-2 bg-white/5 hover:bg-white/10 px-5 py-2.5 rounded-xl text-amber-500 font-bold transition border border-white/5 hover:border-amber-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                            Ramani Kubwa
                        </button>
                    </div>
                </div>
                        
                <div id="requests-container" class="space-y-4">
                    @forelse($recentRequests as $request)
                    <!-- Gig Card -->
                    <div id="request-{{ $request->id }}" 
                         class="request-card group relative overflow-hidden rounded-2xl bg-white/5 border border-white/5 hover:bg-white/[0.07] transition-all duration-300"
                         data-lat="{{ $request->customer_latitude }}" 
                         data-lng="{{ $request->customer_longitude }}"
                         data-id="{{ $request->id }}">
                        
                        <!-- Distance Color Strip -->
                        <div class="distance-indicator absolute left-0 top-0 bottom-0 w-1.5 bg-gray-600 transition-colors duration-500"></div>

                        <div class="flex flex-col md:flex-row items-center gap-6 p-6 pl-8">
                            <div class="w-16 h-16 rounded-2xl bg-gray-800 flex items-center justify-center text-gray-400 font-bold text-xl border border-white/10 shadow-lg shrink-0">
                                {{ substr($request->customer->user->name, 0, 2) }}
                            </div>
                            
                            <div class="flex-1 text-center md:text-left min-w-0 w-full">
                                <div class="flex flex-col md:flex-row md:items-center gap-2 mb-1">
                                    <h3 class="text-white font-bold text-xl truncate">{{ $request->customer->user->name }}</h3>
                                    <span class="distance-badge inline-flex self-center md:self-auto items-center text-[10px] font-black px-2 py-1 rounded-md bg-gray-700 text-gray-300 uppercase tracking-wider">
                                        Calculating...
                                    </span>
                                </div>
                                <p class="text-gray-400 text-sm flex items-center justify-center md:justify-start gap-2 truncate">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="truncate">{{ $request->location ?? 'Unknown Location' }}</span>
                                </p>
                            </div>

                            <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 border-white/5 pt-4 md:pt-0 mt-2 md:mt-0">
                                <div class="text-left md:text-right">
                                    <div class="text-amber-500 font-black text-2xl">TSh {{ number_format($request->service_fee) }}</div>
                                    <div class="text-gray-500 text-[10px] font-bold uppercase tracking-wider">Malipo</div>
                                </div>
                                
                                <div class="flex gap-2">
                                    @if($request->customer_latitude && $request->customer_longitude)
                                    <button onclick="viewRequestOnMap({{ $request->id }}, {{ $request->customer_latitude }}, {{ $request->customer_longitude }})" class="p-3 rounded-xl bg-white/5 text-amber-500 hover:bg-white/10 transition border border-white/5 hover:border-amber-500/30" title="Ona kwenye Ramani">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                    </button>
                                    @endif
                                    <button onclick="rejectRequest({{ $request->id }})" class="px-5 py-3 rounded-xl bg-white/5 text-gray-400 font-bold hover:bg-white/10 transition border border-white/5 hover:text-white">Kataa</button>
                                    <button onclick="acceptRequest({{ $request->id }})" class="px-8 py-3 rounded-xl btn-gold text-black font-bold shadow-lg hover:shadow-amber-500/20">Kubali</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <p class="text-gray-500 font-medium text-lg">Hakuna maombi mapya kwa sasa.</p>
                        <p class="text-gray-600 text-sm mt-1">Subiri kidogo, kazi zitaingia...</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Live Map Preview (Sidebar) -->
        <div class="lg:col-span-1">
            <div id="map-card" class="glass-card rounded-[2.5rem] p-2 flex flex-col h-[600px] sticky top-24 transition-all duration-500 ease-in-out border border-white/10 shadow-2xl">
                <div class="p-6 pb-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-black text-white">Eneo Lako</h2>
                        <p class="text-xs text-green-500 font-bold flex items-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            Live Location
                        </p>
                    </div>
                    <button onclick="toggleMapMode()" class="p-2 rounded-xl hover:bg-white/10 text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                    </button>
                </div>
                
                <div class="flex-1 bg-gray-900 rounded-[2rem] relative overflow-hidden group border border-white/5 mx-2 mb-2">
                    <div id="map" class="w-full h-full"></div>
                    
                    <!-- Radar Effect Overlay -->
                    <div class="absolute inset-0 pointer-events-none z-[400] flex items-center justify-center">
                       <div class="w-4 h-4 bg-amber-500 rounded-full shadow-[0_0_20px_rgba(245,158,11,0.8)] animate-pulse"></div>
                       <div class="absolute w-32 h-32 border border-amber-500/20 rounded-full animate-ping"></div>
                    </div>

                    <!-- Map Controls -->
                    <div class="absolute bottom-4 right-4 z-[400] flex flex-col gap-2">
                        <button onclick="recenterMap()" class="p-3 rounded-xl bg-black/80 backdrop-blur-md text-amber-500 border border-white/10 shadow-lg hover:bg-black transition transform hover:scale-105 active:scale-95">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-white/5">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-[10px] uppercase font-bold tracking-wider">Usahihi wa GPS</span>
                                <span id="gps-accuracy" class="text-white font-bold">Inatafuta...</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-gray-500 block text-[10px] uppercase font-bold tracking-wider">Hali ya Hewani</span>
                            <span id="last-sync" class="text-gray-400 font-bold">Inasubiri...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Configuration ---
        const mapConfig = {
            defaultLat: -6.7924,
            defaultLng: 39.2083,
            zoom: 15
        };

        // --- Icons ---
        // --- Icons ---
        // Agent Icon SVG Builder
        function getAgentIcon(isOnline) {
            const color = isOnline ? '#22c55e' : '#ef4444'; // Green or Red
            const glowColor = isOnline ? 'rgba(34, 197, 94, 0.5)' : 'rgba(239, 68, 68, 0.5)';
            
            return L.divIcon({
                className: 'agent-icon',
                html: `
                    <div id="agent-marker-icon" class="relative flex items-center justify-center w-12 h-12 transition-all duration-500">
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

        const agentOnlineIcon = getAgentIcon(true);
        const agentOfflineIcon = getAgentIcon(false);

        let isOnline = {{ $agent->is_online ? 'true' : 'false' }};
        const currentAgentIcon = isOnline ? agentOnlineIcon : agentOfflineIcon;

        // Customer Icon (User/Person)
        const customerIcon = L.divIcon({
            className: 'customer-div-icon',
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

        // --- Map Initialization ---
        const map = L.map('map', { zoomControl: false }).setView([mapConfig.defaultLat, mapConfig.defaultLng], mapConfig.zoom);
        
        // Dark Mode Tiles (Premium Look)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        let agentMarker = L.marker([mapConfig.defaultLat, mapConfig.defaultLng], {icon: currentAgentIcon}).addTo(map);
        let routingControl = null;
        let lastLat = mapConfig.defaultLat;
        let lastLng = mapConfig.defaultLng;

        // Toggle Listener


        // --- Helper Functions ---
        
        // Calculate Bearing (Direction)
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

        // Haversine Distance Calculation (in km)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the earth in km
            const dLat = toRadians(lat2 - lat1);
            const dLon = toRadians(lon2 - lon1);
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) * 
                Math.sin(dLon/2) * Math.sin(dLon/2); 
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
            const d = R * c; // Distance in km
            return d;
        }

        // Draw Route
        window.drawRouteToCustomer = function(custLat, custLng) {
            if (routingControl) {
                map.removeControl(routingControl);
            }

            const agentPos = agentMarker.getLatLng();

            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(agentPos.lat, agentPos.lng),
                    L.latLng(custLat, custLng)
                ],
                lineOptions: {
                    styles: [{color: '#f59e0b', opacity: 0.8, weight: 6}]
                },
                createMarker: function() { return null; }, // Don't create default markers
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                show: false // Hide instructions panel
            }).addTo(map);
        };

        // --- Load Data ---
        const requests = @json($recentRequests);
        const requestMarkers = {};

        requests.forEach(req => {
            if (req.customer_latitude && req.customer_longitude) {
                const marker = L.marker([req.customer_latitude, req.customer_longitude], {icon: customerIcon})
                    .addTo(map)
                    .bindPopup(`
                        <div class="p-3 bg-gray-900 text-white rounded-xl min-w-[220px]">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-bold text-lg">
                                    ${req.customer.user.name.charAt(0)}
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm">${req.customer.user.name}</h3>
                                    <p class="text-xs text-gray-400">${req.location ?? 'Unknown'}</p>
                                    <p class="text-amber-500 font-bold text-sm mt-1">TSh ${new Intl.NumberFormat().format(req.service_fee)}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="rejectRequest(${req.id})" 
                                    class="bg-white/10 hover:bg-white/20 text-gray-300 px-3 py-2 rounded-lg text-xs font-bold transition">
                                    Kataa
                                </button>
                                <button onclick="acceptRequest(${req.id})" 
                                    class="bg-amber-500 hover:bg-amber-400 text-black px-3 py-2 rounded-lg text-xs font-bold transition">
                                    Kubali
                                </button>
                            </div>
                        </div>
                    `, {
                        className: 'custom-popup'
                    });
                
                requestMarkers[req.id] = marker;
            }
        });

        // --- Map Toggle Logic ---
        let isMapExpanded = false;
        window.toggleMapMode = function() {
            const mapCard = document.getElementById('map-card');
            const mapDiv = document.getElementById('map');
            
            if (!isMapExpanded) {
                mapCard.classList.remove('h-[400px]');
                mapCard.classList.add('fixed', 'inset-0', 'z-50', 'h-screen', 'rounded-none', 'm-0');
                map.invalidateSize();
                isMapExpanded = true;
            } else {
                mapCard.classList.add('h-[400px]');
                mapCard.classList.remove('fixed', 'inset-0', 'z-50', 'h-screen', 'rounded-none', 'm-0');
                map.invalidateSize();
                isMapExpanded = false;
            }
        };

        // Function to view specific request on map
        window.viewRequestOnMap = function(id, lat, lng) {
            if(!isMapExpanded) {
                document.getElementById('map-card').scrollIntoView({ behavior: 'smooth' });
            }
            map.setView([lat, lng], 16);
            
            // Open popup
            if (requestMarkers[id]) {
                setTimeout(() => {
                    requestMarkers[id].openPopup();
                }, 500);
            }
        };

        // --- Real-time Tracking & List Sorting ---
        let isFirstLocation = true;
        let currentAgentPos = null;

        window.recenterMap = function() {
            if (currentAgentPos) {
                map.setView(currentAgentPos, 17);
            }
        };

        function updateRequestList(agentLat, agentLng) {
            const container = document.getElementById('requests-container');
            const cards = Array.from(container.getElementsByClassName('request-card'));

            // Calculate distances and update UI
            cards.forEach(card => {
                const reqLat = parseFloat(card.dataset.lat);
                const reqLng = parseFloat(card.dataset.lng);
                
                if (reqLat && reqLng) {
                    const dist = calculateDistance(agentLat, agentLng, reqLat, reqLng);
                    card.dataset.distance = dist; // Store for sorting

                    // Update Badge Text
                    const badge = card.querySelector('.distance-badge');
                    if (dist < 1) {
                        badge.textContent = `${(dist * 1000).toFixed(0)} m`;
                    } else {
                        badge.textContent = `${dist.toFixed(1)} km`;
                    }

                    // Update Colors
                    const indicator = card.querySelector('.distance-indicator');
                    const badgeEl = card.querySelector('.distance-badge');
                    
                    // Reset classes
                    indicator.className = 'distance-indicator absolute left-0 top-0 bottom-0 w-1 transition-colors duration-500';
                    badgeEl.className = 'distance-badge text-[10px] font-black px-2 py-0.5 rounded transition-colors duration-500';

                    if (dist < 2) { // Near (Green)
                        indicator.classList.add('bg-green-500');
                        badgeEl.classList.add('bg-green-500/20', 'text-green-500');
                        card.classList.add('border-green-500/30');
                        card.classList.remove('border-white/5', 'border-amber-500/30', 'border-red-500/30');
                    } else if (dist < 5) { // Medium (Orange)
                        indicator.classList.add('bg-amber-500');
                        badgeEl.classList.add('bg-amber-500/20', 'text-amber-500');
                        card.classList.add('border-amber-500/30');
                        card.classList.remove('border-white/5', 'border-green-500/30', 'border-red-500/30');
                    } else { // Far (Red)
                        indicator.classList.add('bg-red-500');
                        badgeEl.classList.add('bg-red-500/20', 'text-red-500');
                        card.classList.add('border-red-500/30');
                        card.classList.remove('border-white/5', 'border-green-500/30', 'border-amber-500/30');
                    }
                } else {
                    card.dataset.distance = 999999; // Unknown location goes last
                }
            });

            // Sort Cards
            cards.sort((a, b) => {
                return parseFloat(a.dataset.distance) - parseFloat(b.dataset.distance);
            });

            // Re-append in new order
            cards.forEach(card => container.appendChild(card));
        }

        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    // Update List Sorting based on new location
                    updateRequestList(lat, lng);

                    // Calculate Bearing for Rotation
                    if (lastLat !== lat || lastLng !== lng) {
                        const bearing = getBearing(lastLat, lastLng, lat, lng);
                        const iconElement = document.getElementById('agent-marker-icon');
                        if (iconElement) {
                            iconElement.style.transform = `rotate(${bearing}deg)`;
                        }
                        lastLat = lat;
                        lastLng = lng;
                    }

                    // Smooth Move
                    const newLatLng = new L.LatLng(lat, lng);
                    currentAgentPos = newLatLng;
                    agentMarker.setLatLng(newLatLng);
                    
                    // Auto-center on first load or if requested
                    if (isFirstLocation) {
                        map.setView(newLatLng, 17);
                        isFirstLocation = false;
                    }
                    
                    // Update Route if active
                    if (routingControl) {
                        const waypoints = routingControl.getWaypoints();
                        if (waypoints.length > 1) {
                            waypoints[0].latLng = newLatLng;
                            routingControl.setWaypoints(waypoints);
                        }
                    }

                    // Update UI
                    const accuracyElement = document.getElementById('gps-accuracy');
                    if (accuracyElement) {
                        if (accuracy < 10) {
                            accuracyElement.textContent = `Bora (${Math.round(accuracy)}m)`;
                            accuracyElement.className = 'text-green-500 font-bold';
                        } else {
                            accuracyElement.textContent = `Wastani (${Math.round(accuracy)}m)`;
                            accuracyElement.className = 'text-yellow-500 font-bold';
                        }
                    }

                    // Send to Backend
                    // Send to Backend
                    fetch('{{ route("agent.location.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ 
                            latitude: lat, 
                            longitude: lng,
                            is_online: isOnline // Send current status
                        })
                    })
                    .then(response => {
                        if(response.ok) {
                            const syncEl = document.getElementById('last-sync');
                            if(syncEl) {
                                syncEl.textContent = 'Imesasishwa';
                                syncEl.className = 'text-green-500 font-bold';
                                setTimeout(() => {
                                    syncEl.className = 'text-gray-500 font-bold';
                                }, 2000);
                            }
                        }
                    })
                    .catch(err => console.error('Location update failed', err));
                },
                (error) => {
                    console.error("GPS Error:", error);
                    const syncEl = document.getElementById('last-sync');
                    if(syncEl) {
                        syncEl.textContent = 'Hitilafu ya GPS';
                        syncEl.className = 'text-red-500 font-bold';
                    }
                },
            );
        }

        // Toggle Listener
        const toggleCheckbox = document.getElementById('toggle');
        if (toggleCheckbox) {
            toggleCheckbox.addEventListener('change', function() {
                isOnline = this.checked;
                
                // Update marker definition
                const newIcon = getAgentIcon(isOnline);
                agentMarker.setIcon(newIcon);

                // 2. Backend Sync
                fetch('{{ route("agent.status.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ is_online: isOnline })
                }).catch(err => console.error('Status toggle failed', err));
            });
        }

        // --- Real-Time Job Listener ---
        const agentId = {{ $agent->id }};
        
        // Wait for window.listenForJobs to be available (it comes from app.js)
        const initRealTimeListener = setInterval(() => {
            if (window.listenForJobs) {
                clearInterval(initRealTimeListener);
                
                window.listenForJobs(agentId, (e) => {
                    console.log('Job received via WebSocket:', e);
                    
                    // Play Notification Sound
                    try {
                        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                        audio.play();
                    } catch (err) {
                        console.log('Audio play failed', err);
                    }

                    Swal.fire({
                        title: '🔔 Kazi Mpya!',
                        html: `
                            <div class="text-left">
                                <p class="font-bold text-lg">${e.customer.name}</p>
                                <p class="text-gray-400">Anahitaji laini ya <b class="uppercase text-amber-500">${e.line_type}</b></p>
                                <div class="mt-4 p-3 bg-white/5 rounded-lg border border-white/10">
                                    <p class="text-sm text-gray-300">📍 ${e.customer.address ?? 'Eneo la Mteja'}</p>
                                </div>
                            </div>
                        `,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Angalia Sasa',
                        cancelButtonText: 'Baadaye',
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#374151',
                        background: 'rgba(20, 20, 20, 0.95)',
                        color: '#fff',
                        backdrop: `
                            rgba(0,0,0,0.4)
                            url("https://media.giphy.com/media/3o7aD2saalBwwftBIY/giphy.gif")
                            left top
                            no-repeat
                        `
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Highlight the new request on map if possible, or just reload
                            window.location.reload();
                        }
                    });
                    
                    // Refresh data immediately
                    checkForUpdates();
                });
            }
        }, 100);

        // --- Polling for Updates ---
        setInterval(checkForUpdates, 5000);

        function checkForUpdates() {
            fetch('{{ route("agent.dashboard.updates") }}')
                .then(response => response.json())
                .then(data => {
                    const activeContainer = document.getElementById('active-jobs-container');
                    if (activeContainer) {
                        // Check if content is different to avoid unnecessary DOM updates (optional but good)
                        if(activeContainer.innerHTML.trim() !== data.active_jobs_html.trim()) {
                             activeContainer.innerHTML = data.active_jobs_html;
                        }
                    }

                    const requestsContainer = document.getElementById('requests-container');
                    if (requestsContainer) {
                        // Always update available gigs to ensure fresh list
                         requestsContainer.innerHTML = data.available_gigs_html;
                        
                        // Re-sort and calculate distances
                        if (currentAgentPos) {
                            updateRequestList(currentAgentPos.lat, currentAgentPos.lng);
                        }
                    }
                })
                .catch(error => console.error('Error fetching updates:', error));
        }
    });

    // --- Actions ---
    function acceptRequest(id) {
        if(!confirm('Una uhakika unataka kukubali ombi hili?')) return;
        fetch(`/agent/requests/${id}/accept`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(r => r.json()).then(d => { alert(d.message); location.reload(); });
    }

    function rejectRequest(id) {
        if(!confirm('Una uhakika unataka kukataa ombi hili?')) return;
        fetch(`/agent/requests/${id}/reject`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(r => r.json()).then(d => { alert(d.message); location.reload(); });
    }

    function releaseRequest(id) {
        if(!confirm('Una uhakika unataka kuacha kazi hii? Itarudi kwenye orodha ya maombi mapya.')) return;
        fetch(`/agent/requests/${id}/release`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(r => r.json()).then(d => { alert(d.message); location.reload(); });
    }
</script>
@endpush
