@extends('layouts.dashboard')

@section('title', 'Activity Logs - SKY LAINI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Activity Logs</h1>
            <p class="text-gray-400 font-medium">System-wide activity audit</p>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-6">
        <div class="space-y-4">
            @forelse($activities as $activity)
                <div class="flex items-center gap-4 p-4 rounded-xl bg-white/5 hover:bg-white/10 transition border border-white/5">
                    <div class="w-12 h-12 rounded-full {{ $activity->status->value == 'completed' ? 'bg-green-500/20 text-green-500' : 'bg-blue-500/20 text-blue-500' }} flex items-center justify-center shrink-0">
                        @if($activity->status->value == 'completed')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h3 class="text-white font-bold text-lg">
                                {{ ucfirst($activity->line_type->value ?? $activity->line_type) }} Request 
                                <span class="{{ $activity->status->value == 'completed' ? 'text-green-500' : 'text-blue-400' }}">{{ $activity->status->value ?? $activity->status }}</span>
                            </h3>
                            <span class="text-gray-500 text-sm font-bold">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-400 mt-1">
                            Request initiated by <span class="text-white font-medium">{{ $activity->customer->user->name ?? 'Unknown Customer' }}</span>
                            @if($activity->agent)
                                and handled by <span class="text-amber-500 font-medium">{{ $activity->agent->user->name }}</span>
                            @else
                                (Pending Agent Assignment)
                            @endif
                        </p>
                        <div class="mt-2 text-xs text-gray-600 font-mono">ID: {{ $activity->id }} • {{ $activity->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">No activity logs found.</div>
            @endforelse
        </div>
        <div class="mt-6">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
