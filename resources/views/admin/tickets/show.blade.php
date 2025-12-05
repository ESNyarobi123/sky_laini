@extends('layouts.dashboard')

@section('title', 'Ticket #' . $ticket->id . ' - SKY LAINI')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 h-[calc(100vh-140px)]">
    
    <!-- Ticket Details (Left Sidebar) -->
    <div class="lg:col-span-1 space-y-6 overflow-y-auto pr-2">
        <a href="{{ route('admin.tickets.index') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Tickets
        </a>

        <div class="glass-card rounded-3xl p-6 border border-white/10">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-black text-white mb-2">Ticket #{{ $ticket->id }}</h1>
                    <p class="text-gray-400 text-sm">Created {{ $ticket->created_at->diffForHumans() }}</p>
                </div>
                @php
                    $statusColors = [
                        'open' => 'bg-green-500/20 text-green-500 border-green-500/20',
                        'in_progress' => 'bg-blue-500/20 text-blue-500 border-blue-500/20',
                        'closed' => 'bg-gray-500/20 text-gray-500 border-gray-500/20',
                    ];
                    $statusClass = $statusColors[$ticket->status] ?? 'bg-gray-500/20 text-gray-500';
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                </span>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase block mb-1">Subject</label>
                    <p class="text-white font-bold text-lg">{{ $ticket->subject }}</p>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase block mb-1">Category</label>
                    <span class="inline-block px-3 py-1 rounded-lg bg-white/5 text-gray-300 text-sm border border-white/10">
                        {{ ucfirst($ticket->category) }}
                    </span>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase block mb-1">User</label>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-600 flex items-center justify-center text-white font-bold">
                            {{ substr($ticket->user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-white text-sm">{{ $ticket->user->name }}</div>
                            <div class="text-xs text-gray-400">{{ $ticket->user->email }}</div>
                        </div>
                    </div>
                </div>

                @if($ticket->relatedRequest)
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase block mb-1">Related Request</label>
                        <a href="#" class="block p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 hover:bg-amber-500/20 transition group">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-amber-500 font-bold text-sm">{{ ucfirst($ticket->relatedRequest->line_type->value) }} Request</span>
                                <svg class="w-4 h-4 text-amber-500 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </div>
                            <div class="text-xs text-gray-400">ID: #{{ $ticket->relatedRequest->id }} • {{ $ticket->relatedRequest->created_at->format('d M, Y') }}</div>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Conversation (Right Main Area) -->
    <div class="lg:col-span-2 flex flex-col glass-card rounded-3xl border border-white/10 overflow-hidden">
        <!-- Messages List -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-black/20">
            <!-- Original Ticket Message -->
            <div class="flex justify-start">
                <div class="max-w-[85%]">
                    <div class="bg-white/10 text-white rounded-2xl rounded-tl-none px-6 py-5 shadow-md border border-white/5">
                        <div class="flex items-center justify-between gap-4 mb-3 border-b border-white/10 pb-2">
                            <span class="text-xs font-bold text-amber-500 uppercase">Original Message</span>
                            <span class="text-[10px] text-gray-400">{{ $ticket->created_at->format('d M, Y H:i') }}</span>
                        </div>
                        <p class="text-base text-gray-200 leading-relaxed whitespace-pre-wrap">{{ $ticket->message }}</p>
                    </div>
                </div>
            </div>

            <!-- Thread -->
            @foreach($ticket->messages as $message)
                <div class="flex w-full {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%]">
                        <div class="px-6 py-4 shadow-md border border-white/5 {{ $message->user_id === auth()->id() ? 'bg-amber-500 text-black rounded-2xl rounded-tr-none' : 'bg-white/10 text-white rounded-2xl rounded-tl-none' }}">
                            <div class="flex items-center justify-between gap-4 mb-2 border-b {{ $message->user_id === auth()->id() ? 'border-black/10' : 'border-white/10' }} pb-2">
                                <span class="text-xs font-bold uppercase {{ $message->user_id === auth()->id() ? 'text-black/70' : 'text-gray-400' }}">
                                    {{ $message->user->name }} {{ $message->user_id === auth()->id() ? '(You)' : '' }}
                                </span>
                                <span class="text-[10px] {{ $message->user_id === auth()->id() ? 'text-black/60' : 'text-gray-400' }}">
                                    {{ $message->created_at->format('d M, H:i') }}
                                </span>
                            </div>
                            <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $message->message }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Reply Box -->
        <div class="p-6 bg-gray-900/90 backdrop-blur-md border-t border-white/10">
            <form action="{{ route('admin.tickets.reply', $ticket) }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <textarea name="message" rows="3" placeholder="Type your reply here..." class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition resize-none"></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <label class="text-sm text-gray-400 font-bold">Update Status:</label>
                        <select name="status" class="bg-black/50 border border-white/10 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500">
                            <option value="">-- Keep Current --</option>
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <button type="submit" class="px-6 py-3 rounded-xl bg-amber-500 text-black font-bold hover:bg-amber-400 transition shadow-lg shadow-amber-500/20 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
