@extends('layouts.dashboard')

@section('title', 'Admin Support - SKY LAINI')

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    .chat-bg {
        background-color: #0f172a;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231e293b' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
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
<div x-data="supportChat()" class="h-[calc(100vh-140px)] flex flex-col gap-6">

    <!-- Navigation Tabs -->
    <div class="flex gap-4 mb-2 overflow-x-auto pb-2">
        <button @click="viewMode = 'home'" 
            :class="viewMode === 'home' ? 'bg-amber-500 text-black' : 'bg-white/5 text-gray-400 hover:bg-white/10'"
            class="px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Overview
        </button>
        <a href="{{ route('admin.tickets.index') }}" 
            class="bg-white/5 text-gray-400 hover:bg-white/10 px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            All Tickets
        </a>
        <button @click="viewMode = 'chat'" 
            :class="viewMode === 'chat' ? 'bg-green-500 text-black' : 'bg-white/5 text-gray-400 hover:bg-white/10'"
            class="px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            Live Chat
        </button>
    </div>
    
    <!-- Stats Row (Visible in Home/Overview) -->
    <div x-show="viewMode === 'home'" class="grid grid-cols-1 md:grid-cols-4 gap-4 animate-fade-in">
        <div class="glass-card p-4 rounded-2xl border border-white/10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase">Total Tickets</p>
                <p class="text-2xl font-black text-white">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="glass-card p-4 rounded-2xl border border-white/10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase">Open Tickets</p>
                <p class="text-2xl font-black text-white">{{ number_format($stats['open']) }}</p>
            </div>
        </div>
        <div class="glass-card p-4 rounded-2xl border border-white/10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center text-green-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase">Resolved</p>
                <p class="text-2xl font-black text-white">{{ number_format($stats['closed']) }}</p>
            </div>
        </div>
        <div class="glass-card p-4 rounded-2xl border border-white/10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase">New Today</p>
                <p class="text-2xl font-black text-white">{{ number_format($stats['today']) }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div x-show="viewMode === 'home' || viewMode === 'chat'" class="flex-1 flex gap-6 overflow-hidden animate-fade-in">
        <!-- Sidebar (User List) -->
        <div class="w-full md:w-1/3 lg:w-1/4 flex flex-col glass-card rounded-3xl border border-white/10 overflow-hidden">
            <!-- Header & Tabs -->
            <div class="p-4 bg-gray-900/90 backdrop-blur-md border-b border-white/10 z-10">
                <h2 class="text-xl font-bold text-white mb-4">Inbox</h2>
                
                <div class="flex p-1 bg-white/5 rounded-xl border border-white/10">
                    <button @click="activeTab = 'customers'" 
                        :class="activeTab === 'customers' ? 'bg-amber-500 text-black shadow-lg' : 'text-gray-400 hover:text-white'"
                        class="flex-1 py-2 rounded-lg text-sm font-bold transition text-center">
                        Customers
                    </button>
                    <button @click="activeTab = 'agents'" 
                        :class="activeTab === 'agents' ? 'bg-amber-500 text-black shadow-lg' : 'text-gray-400 hover:text-white'"
                        class="flex-1 py-2 rounded-lg text-sm font-bold transition text-center">
                        Agents
                    </button>
                </div>
            </div>

            <!-- User List -->
            <div class="flex-1 overflow-y-auto p-2 space-y-2 scrollbar-hide">
                <!-- Customers List -->
                <div x-show="activeTab === 'customers'" class="space-y-2">
                    @forelse($customers as $user)
                        <button @click="selectUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}')" 
                            :class="selectedUserId === {{ $user->id }} ? 'bg-white/10 border-amber-500/50' : 'bg-transparent border-transparent hover:bg-white/5'"
                            class="w-full p-3 rounded-xl border flex items-center gap-3 transition text-left group">
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                @if($user->last_ticket && $user->last_ticket->status === 'open')
                                    <div class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-red-500 border-2 border-gray-900"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline mb-0.5">
                                    <h3 class="font-bold text-white text-sm truncate group-hover:text-amber-500 transition">{{ $user->name }}</h3>
                                    <span class="text-[10px] text-gray-500">{{ $user->last_ticket ? $user->last_ticket->created_at->format('H:i') : '' }}</span>
                                </div>
                                <p class="text-xs text-gray-400 truncate">{{ $user->last_ticket ? $user->last_ticket->subject : 'No tickets' }}</p>
                            </div>
                        </button>
                    @empty
                        <div class="text-center py-8 text-gray-500 text-xs">No customers found</div>
                    @endforelse
                </div>

                <!-- Agents List -->
                <div x-show="activeTab === 'agents'" class="space-y-2" style="display: none;">
                    @forelse($agents as $user)
                        <button @click="selectUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}')" 
                            :class="selectedUserId === {{ $user->id }} ? 'bg-white/10 border-amber-500/50' : 'bg-transparent border-transparent hover:bg-white/5'"
                            class="w-full p-3 rounded-xl border flex items-center gap-3 transition text-left group">
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white font-bold text-sm">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                @if($user->last_ticket && $user->last_ticket->status === 'open')
                                    <div class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-red-500 border-2 border-gray-900"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline mb-0.5">
                                    <h3 class="font-bold text-white text-sm truncate group-hover:text-amber-500 transition">{{ $user->name }}</h3>
                                    <span class="text-[10px] text-gray-500">{{ $user->last_ticket ? $user->last_ticket->created_at->format('H:i') : '' }}</span>
                                </div>
                                <p class="text-xs text-gray-400 truncate">{{ $user->last_ticket ? $user->last_ticket->subject : 'No tickets' }}</p>
                            </div>
                        </button>
                    @empty
                        <div class="text-center py-8 text-gray-500 text-xs">No agents found</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col glass-card rounded-3xl border border-white/10 overflow-hidden relative">
            <template x-if="selectedUserId">
                <div class="flex flex-col h-full">
                    <!-- Chat Header -->
                    <div class="bg-gray-900/90 backdrop-blur-md p-4 border-b border-white/10 flex items-center justify-between z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-600 flex items-center justify-center text-white font-bold text-lg">
                                <span x-text="selectedUserName.charAt(0)"></span>
                            </div>
                            <div>
                                <h3 class="font-bold text-white" x-text="selectedUserName"></h3>
                                <p class="text-xs text-gray-400" x-text="selectedUserEmail"></p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="p-2 hover:bg-white/10 rounded-full text-gray-400 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div class="flex-1 chat-bg overflow-y-auto p-4 space-y-2" id="chatContainer">
                        <template x-for="ticket in tickets" :key="ticket.id">
                            <div class="flex flex-col gap-2">
                                <!-- Ticket Start (User Message) -->
                                <div class="flex justify-start group">
                                    <div class="max-w-[80%] relative">
                                        <div class="bg-[#202c33] text-white rounded-lg rounded-tl-none px-3 py-2 shadow-sm text-sm">
                                            <div class="flex items-center justify-between gap-4 mb-1 border-b border-white/10 pb-1">
                                                <span class="text-[10px] font-bold text-amber-500 uppercase" x-text="ticket.category"></span>
                                            </div>
                                            <h4 class="font-bold text-xs mb-1 text-white/90" x-text="ticket.subject"></h4>
                                            <p class="text-sm text-gray-200 leading-relaxed whitespace-pre-wrap" x-text="ticket.message"></p>
                                            
                                            <div class="mt-2 pt-1 border-t border-white/10 flex justify-between items-center">
                                                <a :href="'/admin/tickets/' + ticket.id" class="text-[10px] font-bold text-blue-400 hover:text-blue-300 transition flex items-center gap-1">
                                                    View Ticket
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                </a>
                                                <span class="text-[10px] text-gray-400" x-text="formatDate(ticket.created_at)"></span>
                                            </div>
                                        </div>
                                        <!-- Tail -->
                                        <div class="absolute top-0 -left-2 w-0 h-0 border-t-[10px] border-t-[#202c33] border-l-[10px] border-l-transparent transform rotate-90"></div>
                                    </div>
                                </div>

                                <!-- Subsequent Messages -->
                                <template x-for="msg in ticket.messages" :key="msg.id">
                                    <div class="flex w-full" :class="msg.user_id == selectedUserId ? 'justify-start' : 'justify-end'">
                                        <div class="max-w-[80%] relative">
                                            <div class="px-3 py-2 shadow-sm rounded-lg text-sm" 
                                                :class="msg.user_id == selectedUserId ? 'bg-[#202c33] text-white rounded-tl-none' : 'bg-[#005c4b] text-white rounded-tr-none'">
                                                <p class="leading-relaxed whitespace-pre-wrap" x-text="msg.message"></p>
                                                <div class="flex justify-end items-center gap-1 mt-1">
                                                    <span class="text-[10px] text-white/60" x-text="formatDate(msg.created_at)"></span>
                                                    <!-- Double Tick for Admin Messages -->
                                                    <template x-if="msg.user_id != selectedUserId">
                                                        <svg class="w-3 h-3 text-blue-400" viewBox="0 0 16 15" width="16" height="15" fill="currentColor"><path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-7.674a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-7.674a.366.366 0 0 0-.064-.512z"></path></svg>
                                                    </template>
                                                </div>
                                            </div>
                                            <!-- Tails -->
                                            <template x-if="msg.user_id == selectedUserId">
                                                <div class="absolute top-0 -left-2 w-0 h-0 border-t-[10px] border-t-[#202c33] border-l-[10px] border-l-transparent transform rotate-90"></div>
                                            </template>
                                            <template x-if="msg.user_id != selectedUserId">
                                                <div class="absolute top-0 -right-2 w-0 h-0 border-t-[10px] border-t-[#005c4b] border-r-[10px] border-r-transparent transform -rotate-90"></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <div x-show="loading" class="flex justify-center py-4">
                            <svg class="animate-spin h-8 w-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="bg-[#202c33] p-3 border-t border-white/5 z-10 flex items-center gap-4">
                        <button class="text-gray-400 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                        <button class="text-gray-400 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        </button>
                        <form @submit.prevent="sendMessage" class="flex-1 flex gap-2">
                            <input type="text" x-model="newMessage" placeholder="Type a message" class="flex-1 bg-[#2a3942] border-none rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:ring-0 text-sm">
                            <button type="submit" class="p-2 bg-[#005c4b] rounded-full text-white hover:bg-[#005c4b]/80 transition shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!selectedUserId">
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
                    <div class="w-24 h-24 rounded-full bg-white/5 flex items-center justify-center text-gray-600 mb-6 animate-pulse">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Select a Conversation</h3>
                    <p class="text-gray-400 max-w-sm">Choose a customer or agent from the sidebar to view their support tickets and history.</p>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function supportChat() {
        return {
            viewMode: 'home',
            activeTab: 'customers',
            selectedUserId: null,
            selectedUserName: '',
            selectedUserEmail: '',
            tickets: [],
            loading: false,
            newMessage: '',

            selectUser(id, name, email) {
                this.selectedUserId = id;
                this.selectedUserName = name;
                this.selectedUserEmail = email;
                this.loading = true;
                this.tickets = [];

                fetch(`/admin/support/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        this.tickets = data;
                        this.loading = false;
                        this.$nextTick(() => {
                            const container = document.getElementById('chatContainer');
                            if(container) container.scrollTop = container.scrollHeight;
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                    });
            },

            sendMessage() {
                if (!this.newMessage.trim()) return;
                
                const message = this.newMessage;
                this.newMessage = ''; // Clear immediately

                fetch('{{ route("admin.support.reply") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_id: this.selectedUserId,
                        message: message
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        this.selectUser(this.selectedUserId, this.selectedUserName, this.selectedUserEmail);
                    }
                });
            },

            formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }
    }
</script>
@endsection
