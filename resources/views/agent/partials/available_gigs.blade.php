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
