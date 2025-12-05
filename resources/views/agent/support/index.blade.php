@extends('layouts.dashboard')

@section('title', 'Agent Support - SKY LAINI')

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    .chat-bg {
        background-color: #0f172a;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231e293b' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
</style>
@endpush

@section('content')
<div x-data="{ activeTab: 'home' }" class="h-[calc(100vh-140px)] flex flex-col">
    
    <!-- Navigation Tabs -->
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
    </div>

    <!-- HOME VIEW (Cards) -->
    <div x-show="activeTab === 'home'" class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in">
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
                <p class="text-gray-400 font-medium">Ripoti tatizo la kazi, malipo, au akaunti.</p>
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
                <p class="text-gray-400 font-medium">Mawasiliano ya haraka na uongozi.</p>
            </div>
        </button>
    </div>

    <!-- TICKETS VIEW -->
    <div x-show="activeTab === 'tickets'" class="grid grid-cols-1 lg:grid-cols-2 gap-8 h-full overflow-hidden">
        <!-- Create Ticket Form -->
        <div class="glass-card rounded-3xl p-6 border border-white/10 bg-white/5 backdrop-blur-xl overflow-y-auto">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </span>
                Tengeneza Ticket
            </h2>
            
            <form action="{{ route('agent.support.store') }}" method="POST" class="space-y-5">
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
                            <input type="radio" name="category" value="complaint" class="peer sr-only">
                            <div class="p-3 rounded-xl bg-black/50 border border-white/10 text-center text-gray-400 peer-checked:bg-amber-500 peer-checked:text-black peer-checked:border-amber-500 transition text-sm font-bold">
                                Malalamiko
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="refund" class="peer sr-only">
                            <div class="p-3 rounded-xl bg-black/50 border border-white/10 text-center text-gray-400 peer-checked:bg-amber-500 peer-checked:text-black peer-checked:border-amber-500 transition text-sm font-bold">
                                Malipo
                            </div>
                        </label>
                    </div>
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
        </div>

        <!-- Chat Messages Area -->
        <div class="flex-1 chat-bg overflow-y-auto p-4 space-y-2" id="agentChatContainer">
            <!-- Welcome Message -->
            <div class="flex justify-start group">
                <div class="max-w-[80%] relative">
                    <div class="bg-[#202c33] text-white rounded-lg rounded-tl-none px-3 py-2 shadow-sm text-sm">
                        <p class="leading-relaxed">Habari! Karibu Sky Laini Support. Nikusaidie nini leo?</p>
                        <span class="text-[10px] text-white/60 block text-right mt-1">10:00 AM</span>
                    </div>
                    <!-- Tail -->
                    <div class="absolute top-0 -left-2 w-0 h-0 border-t-[10px] border-t-[#202c33] border-l-[10px] border-l-transparent transform rotate-90"></div>
                </div>
            </div>

            <!-- Chat History -->
            @foreach($tickets as $ticket)
                <!-- Ticket Bubble (User) -->
                <div class="flex justify-end group">
                    <div class="max-w-[80%] relative">
                        <div class="bg-[#005c4b] text-white rounded-lg rounded-tr-none px-3 py-2 shadow-sm text-sm">
                            @if($ticket->subject !== 'Live Chat Message')
                                <div class="text-[10px] font-bold text-amber-500 mb-1 uppercase">{{ $ticket->subject }}</div>
                            @endif
                            <p class="leading-relaxed whitespace-pre-wrap">{{ $ticket->message }}</p>
                            <div class="flex justify-end items-center gap-1 mt-1">
                                <span class="text-[10px] text-white/60">{{ $ticket->created_at->format('H:i') }}</span>
                                <!-- Double Tick -->
                                <svg class="w-3 h-3 text-blue-400" viewBox="0 0 16 15" width="16" height="15" fill="currentColor"><path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-7.674a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-7.674a.366.366 0 0 0-.064-.512z"></path></svg>
                            </div>
                        </div>
                        <!-- Tail -->
                        <div class="absolute top-0 -right-2 w-0 h-0 border-t-[10px] border-t-[#005c4b] border-r-[10px] border-r-transparent transform -rotate-90"></div>
                    </div>
                </div>

                <!-- Admin Replies -->
                @foreach($ticket->messages as $message)
                    <div class="flex justify-start group">
                        <div class="max-w-[80%] relative">
                            <div class="bg-[#202c33] text-white rounded-lg rounded-tl-none px-3 py-2 shadow-sm text-sm">
                                <p class="leading-relaxed whitespace-pre-wrap">{{ $message->message }}</p>
                                <span class="text-[10px] text-white/60 block text-right mt-1">{{ $message->created_at->format('H:i') }}</span>
                            </div>
                            <!-- Tail -->
                            <div class="absolute top-0 -left-2 w-0 h-0 border-t-[10px] border-t-[#202c33] border-l-[10px] border-l-transparent transform rotate-90"></div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>

        <!-- Chat Input -->
        <div class="bg-[#202c33] p-3 border-t border-white/5 z-10 flex items-center gap-4">
            <button class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </button>
            <button class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
            </button>
            <form action="{{ route('agent.support.store') }}" method="POST" class="flex-1 flex gap-2">
                @csrf
                <input type="hidden" name="subject" value="Live Chat Message">
                <input type="hidden" name="category" value="general">
                
                <input type="text" name="message" placeholder="Type a message" class="flex-1 bg-[#2a3942] border-none rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:ring-0 text-sm" autocomplete="off">
                
                <button type="submit" class="p-2 bg-[#005c4b] rounded-full text-white hover:bg-[#005c4b]/80 transition shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('agentChatContainer');
        if(container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endsection
