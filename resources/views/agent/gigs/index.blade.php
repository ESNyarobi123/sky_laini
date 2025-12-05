@extends('layouts.dashboard')

@section('title', 'Available Gigs - SKY LAINI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Available Gigs</h1>
            <p class="text-gray-400 font-medium">Browse and accept new job requests</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($gigs as $gig)
            <div class="glass-card rounded-3xl p-6 border border-white/10 hover:border-amber-500/50 transition group relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-24 h-24 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-500 border border-amber-500/20">
                            {{ ucfirst($gig->line_type->value ?? $gig->line_type) }}
                        </span>
                        <span class="text-gray-400 text-xs font-bold">{{ $gig->created_at->diffForHumans() }}</span>
                    </div>

                    <h3 class="text-xl font-bold text-white mb-1">Request #{{ $gig->request_number }}</h3>
                    <p class="text-gray-400 text-sm mb-4">Customer Location: {{ $gig->customer_address ?? 'View on Map' }}</p>

                    <div class="flex items-center justify-between mt-6 pt-6 border-t border-white/10">
                        <div>
                            <p class="text-gray-500 text-xs font-bold uppercase">Est. Earnings</p>
                            <p class="text-green-500 font-bold text-lg">TSh {{ number_format($gig->service_fee ?? 1000) }}</p>
                        </div>
                        <form action="{{ route('agent.requests.accept', $gig) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm transition flex items-center gap-2">
                                Accept Job
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="w-24 h-24 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No Gigs Available</h3>
                <p class="text-gray-400">Check back later for new requests.</p>
            </div>
        @endforelse
    </div>
    
    <div class="mt-6">
        {{ $gigs->links() }}
    </div>
</div>
@endsection
