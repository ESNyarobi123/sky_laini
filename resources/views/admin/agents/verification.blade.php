@extends('layouts.dashboard')

@section('title', 'Verify Agents - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Pending Verifications</h1>
            <p class="text-gray-400 font-medium">Review and approve new agents</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-500 p-4 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6">
        @forelse($pendingAgents as $agent)
        <div class="glass-card rounded-3xl p-6 border border-white/10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Agent Info (4 cols) -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-black font-black text-2xl shadow-lg shadow-amber-500/20">
                            {{ substr($agent->user->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white leading-tight">{{ $agent->user->name }}</h3>
                            <p class="text-gray-400 text-sm">{{ $agent->user->email }}</p>
                            <p class="text-amber-500 text-sm font-bold mt-1">{{ $agent->phone }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
                            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-1">NIDA Number</p>
                            <p class="text-white font-mono text-sm tracking-wide break-all">{{ $agent->nida_number }}</p>
                        </div>
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
                            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-1">Joined Date</p>
                            <p class="text-white text-sm">{{ $agent->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Documents (5 cols) -->
                <div class="lg:col-span-5 lg:border-l lg:border-white/10 lg:pl-6">
                    <h4 class="text-white font-bold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Submitted Documents
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($agent->documents as $doc)
                        <div class="group">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="block aspect-[4/3] bg-black/40 rounded-xl overflow-hidden border border-white/10 hover:border-amber-500/50 transition relative flex items-center justify-center">
                                @if(Str::endsWith(strtolower($doc->file_path), '.pdf'))
                                    <div class="text-center p-4">
                                        <svg class="w-10 h-10 text-red-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <span class="text-white font-bold text-xs">PDF</span>
                                    </div>
                                @else
                                    <img src="{{ asset('storage/' . $doc->file_path) }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition" alt="Document">
                                @endif
                                
                                <!-- Overlay Label -->
                                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 to-transparent p-3 pt-6">
                                    <p class="text-white text-[10px] font-bold uppercase tracking-wider">{{ str_replace('_', ' ', $doc->document_type) }}</p>
                                </div>
                            </a>
                            <div class="mt-2 flex justify-end">
                                <a href="{{ asset('storage/' . $doc->file_path) }}" download class="text-xs text-amber-500 hover:text-amber-400 font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($agent->documents->isEmpty())
                        <div class="bg-white/5 rounded-xl p-4 text-center border border-white/5 border-dashed">
                            <p class="text-gray-500 text-sm italic">No documents uploaded yet.</p>
                        </div>
                    @endif
                </div>

                <!-- Actions (3 cols) -->
                <div class="lg:col-span-3 lg:border-l lg:border-white/10 lg:pl-6 flex flex-col justify-center h-full gap-3">
                    <div class="bg-white/5 rounded-2xl p-4 mb-2">
                        <p class="text-gray-400 text-xs mb-1">Status</p>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></span>
                            <span class="text-yellow-500 font-bold text-sm">Pending Review</span>
                        </div>
                    </div>
                    
                    <form action="{{ route('admin.agents.verify', $agent) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-green-500 hover:bg-green-400 text-black font-bold shadow-lg shadow-green-500/20 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Approve
                        </button>
                    </form>
                    <form action="{{ route('admin.agents.reject', $agent) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-500 font-bold border border-red-500/20 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Reject
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <div class="w-20 h-20 mx-auto bg-white/5 rounded-full flex items-center justify-center text-gray-500 mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-white font-bold text-xl">All Caught Up!</h3>
            <p class="text-gray-400">No pending agent verifications.</p>
        </div>
        @endforelse

        {{ $pendingAgents->links() }}
    </div>
</div>
@endsection
