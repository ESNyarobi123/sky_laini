@extends('layouts.dashboard')

@section('title', 'All Tickets - SKY LAINI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Support Tickets</h1>
            <p class="text-gray-400 font-medium">Manage and reply to customer inquiries.</p>
        </div>
    </div>

    <div class="glass-card rounded-3xl border border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-white/5 border-b border-white/10 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="p-6 font-bold">Subject</th>
                        <th class="p-6 font-bold">User</th>
                        <th class="p-6 font-bold">Category</th>
                        <th class="p-6 font-bold">Status</th>
                        <th class="p-6 font-bold">Date</th>
                        <th class="p-6 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-white/5 transition group">
                            <td class="p-6">
                                <div class="font-bold text-white group-hover:text-amber-500 transition">{{ $ticket->subject }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($ticket->message, 50) }}</div>
                            </td>
                            <td class="p-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-gray-700 to-gray-600 flex items-center justify-center text-white font-bold text-xs">
                                        {{ substr($ticket->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm">{{ $ticket->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ ucfirst($ticket->user->role->value) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-gray-300 border border-white/5">
                                    {{ ucfirst($ticket->category) }}
                                </span>
                            </td>
                            <td class="p-6">
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
                            </td>
                            <td class="p-6 text-sm text-gray-400">
                                {{ $ticket->created_at->format('d M, Y H:i') }}
                            </td>
                            <td class="p-6 text-right">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-black font-bold text-sm hover:bg-amber-400 transition shadow-lg shadow-amber-500/20">
                                    View
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-gray-500">
                                No tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-white/10">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection
