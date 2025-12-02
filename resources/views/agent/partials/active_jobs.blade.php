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
