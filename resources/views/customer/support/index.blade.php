@extends('layouts.dashboard')

@section('title', 'Msaada - SKY LAINI')

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    .chat-bg {
        background-color: #0f172a;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231e293b' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@section('content')
<div x-data="{ activeTab: 'home' }" class="h-[calc(100vh-140px)] flex flex-col">
    
    <!-- Navigation Tabs (Only visible on Home or Desktop) -->
    <div class="flex gap-4 mb-6 overflow-x-auto pb-2" x-show="activeTab === 'home' || window.innerWidth >= 1024">
        <button @click="activeTab = 'tickets'" 
            :class="activeTab === 'tickets' ? 'bg-amber-500 text-black' : 'bg-white/5 text-gray-400 hover:bg-white/10'"
            class="px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Tickets & History
        </button>
        <button @click="activeTab = 'chat'" 
            :class="activeTab === 'chat' ? 'bg-green-500 text-black' : 'bg-white/5 text-gray-400 hover:bg-white/10'"
            class="px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            Live Chat Admin
        </button>
        <a href="https://wa.me/255700000000" target="_blank"
            class="px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 whitespace-nowrap bg-white/5 text-green-400 hover:bg-green-500/20 border border-green-500/30">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            WhatsApp
        </a>
    </div>

    <!-- HOME VIEW (Cards) -->
    <div x-show="activeTab === 'home'" class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
        <!-- Ticket Card -->
        <button @click="activeTab = 'tickets'" class="group relative overflow-hidden rounded-3xl p-8 bg-gradient-to-br from-gray-900 to-black border border-white/10 text-left hover:border-amber-500/50 transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-32 h-32 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div class="relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-500 mb-6 group-hover:scale-110 transition">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-white mb-2">Fungua Ticket</h3>
                <p class="text-gray-400 font-medium">Tuma malalamiko, maombi ya refund, au maswali mengine.</p>
            </div>
        </button>

        <!-- Chat Card -->
        <button @click="activeTab = 'chat'" class="group relative overflow-hidden rounded-3xl p-8 bg-gradient-to-br from-gray-900 to-black border border-white/10 text-left hover:border-green-500/50 transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-32 h-32 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            </div>
            <div class="relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-green-500/20 flex items-center justify-center text-green-500 mb-6 group-hover:scale-110 transition">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-white mb-2">Chat na Admin</h3>
                <p class="text-gray-400 font-medium">Ongea na mhudumu wetu moja kwa moja kwa msaada wa haraka.</p>
            </div>
        </button>

        <!-- WhatsApp Card -->
        <a href="https://wa.me/255700000000" target="_blank" class="group relative overflow-hidden rounded-3xl p-8 bg-green-600 border border-transparent text-left hover:bg-green-500 transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-20 group-hover:opacity-30 transition-opacity">
                <svg class="w-32 h-32 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            </div>
            <div class="relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-white mb-6 group-hover:scale-110 transition">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                </div>
                <h3 class="text-2xl font-black text-white mb-2">WhatsApp</h3>
                <p class="text-green-100 font-medium">Tuma ujumbe WhatsApp moja kwa moja.</p>
            </div>
        </a>
    </div>

    <!-- TICKETS VIEW -->
    <div x-show="activeTab === 'tickets'" class="grid grid-cols-1 lg:grid-cols-2 gap-8 h-full overflow-hidden">
        <!-- Create Ticket -->
        <div class="glass-card rounded-3xl p-6 border border-white/10 bg-white/5 backdrop-blur-xl overflow-y-auto">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </span>
                Tengeneza Ticket
            </h2>
            
            <form action="{{ route('customer.support.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase">Kichwa cha Habari</label>
                    <input type="text" name="subject" required class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition">
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase">Aina ya Tatizo</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="general" class="peer sr-only" checked>
                            <div class="p-3 rounded-xl bg-black/50 border border-white/10 text-center text-gray-400 peer-checked:bg-amber-500 peer-checked:text-black peer-checked:border-amber-500 transition text-sm font-bold">
                                Kawaida
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="refund" class="peer sr-only">
                            <div class="p-3 rounded-xl bg-black/50 border border-white/10 text-center text-gray-400 peer-checked:bg-amber-500 peer-checked:text-black peer-checked:border-amber-500 transition text-sm font-bold">
                                Refund
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="complaint" class="peer sr-only">
                            <div class="p-3 rounded-xl bg-black/50 border border-white/10 text-center text-gray-400 peer-checked:bg-amber-500 peer-checked:text-black peer-checked:border-amber-500 transition text-sm font-bold">
                                Malalamiko
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase">Chagua Request (Optional)</label>
                    <select name="related_request_id" class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition">
                        <option value="">-- Hakuna --</option>
                        @foreach($recentRequests as $request)
                            <option value="{{ $request->id }}">
                                {{ ucfirst($request->line_type->value) }} - {{ $request->created_at->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase">Maelezo</label>
                    <textarea name="message" rows="4" required class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition"></textarea>
                </div>

                <button type="submit" class="w-full py-4 rounded-xl bg-amber-500 text-black font-bold text-lg shadow-lg hover:bg-amber-400 transition">
                    Tuma Ticket
                </button>
            </form>
        </div>

        <!-- History -->
        <div class="glass-card rounded-3xl p-6 border border-white/10 bg-white/5 backdrop-blur-xl overflow-y-auto">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
                Historia ya Tickets
            </h2>
            
            <div class="space-y-4">
                @forelse($tickets as $ticket)
                    <div class="p-4 rounded-xl bg-black/40 border border-white/5 hover:border-white/20 transition group">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-white group-hover:text-amber-500 transition">{{ $ticket->subject }}</h3>
                                <p class="text-xs text-gray-400">{{ $ticket->created_at->format('d M, H:i') }} • {{ ucfirst($ticket->category) }}</p>
                            </div>
                            @php
                                $statusColors = [
                                    'open' => 'bg-green-500/20 text-green-500',
                                    'closed' => 'bg-gray-500/20 text-gray-500',
                                    'in_progress' => 'bg-blue-500/20 text-blue-500',
                                ];
                                $statusClass = $statusColors[$ticket->status] ?? 'bg-gray-500/20 text-gray-500';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-300 line-clamp-2">{{ $ticket->message }}</p>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center text-gray-500 mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <p class="text-gray-500 font-medium">Hakuna tickets zozote.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>

    <!-- LIVE CHAT VIEW -->
    <div x-show="activeTab === 'chat'" class="flex-1 flex flex-col glass-card rounded-3xl border border-white/10 overflow-hidden relative">
        <!-- Chat Header -->
        <div class="bg-gray-900/90 backdrop-blur-md p-4 border-b border-white/10 flex items-center justify-between z-10">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white font-bold">
                        A
                    </div>
                    <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-green-500 border-2 border-gray-900"></div>
                </div>
                <div>
                    <h3 class="font-bold text-white">Admin Support</h3>
                    <p class="text-xs text-green-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        Online
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <button class="p-2 hover:bg-white/10 rounded-full text-gray-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </button>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div class="flex-1 chat-bg overflow-y-auto p-4 space-y-4">
            <!-- Welcome Message -->
            <div class="flex justify-start">
                <div class="bg-gray-800 text-white rounded-2xl rounded-tl-none px-4 py-3 max-w-[80%] shadow-md border border-white/5">
                    <p class="text-sm">Habari! Karibu Sky Laini Support. Nikusaidie nini leo?</p>
                    <span class="text-[10px] text-gray-400 block text-right mt-1">10:00 AM</span>
                </div>
            </div>

            <!-- User Messages (Simulated from Tickets for now) -->
            @foreach($tickets->where('category', 'general')->take(5) as $msg)
                <div class="flex justify-end">
                    <div class="bg-green-600 text-white rounded-2xl rounded-tr-none px-4 py-3 max-w-[80%] shadow-md">
                        <p class="text-sm">{{ $msg->message }}</p>
                        <span class="text-[10px] text-green-200 block text-right mt-1">{{ $msg->created_at->format('H:i') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Chat Input -->
        <div class="bg-gray-900/90 backdrop-blur-md p-4 border-t border-white/10 z-10">
            <form action="{{ route('customer.support.store') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="hidden" name="subject" value="Live Chat Message">
                <input type="hidden" name="category" value="general">
                
                <button type="button" class="p-3 text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                </button>
                
                <input type="text" name="message" placeholder="Andika ujumbe..." class="flex-1 bg-black/50 border border-white/10 rounded-full px-6 py-3 text-white focus:outline-none focus:border-green-500 transition" autocomplete="off">
                
                <button type="submit" class="p-3 bg-green-500 rounded-full text-white hover:bg-green-400 transition shadow-lg shadow-green-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
