@extends('layouts.dashboard')

@section('title', 'Agent Details - SKY LAINI')

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
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.agents.index') }}" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-white/10 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-black text-white mb-1">{{ $agent->user->name }}</h1>
            <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-400">Agent ID: #{{ $agent->id }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-600"></span>
                <span class="{{ $agent->is_verified ? 'text-green-500' : 'text-red-500' }} font-bold">{{ $agent->is_verified ? 'Verified' : 'Unverified' }}</span>
            </div>
        </div>
        <div class="ml-auto">
            <form action="{{ route('admin.agents.toggle', $agent) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                @csrf
                <button type="submit" class="px-6 py-3 rounded-xl font-bold transition {{ $agent->is_verified ? 'bg-red-500/10 text-red-500 hover:bg-red-500/20' : 'bg-green-500 text-black hover:bg-green-400' }}">
                    {{ $agent->is_verified ? 'Deactivate Agent' : 'Activate Agent' }}
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-card rounded-3xl p-6 text-center">
                <div class="w-32 h-32 mx-auto rounded-full bg-amber-500 flex items-center justify-center text-black font-bold text-4xl mb-4">
                    {{ substr($agent->user->name, 0, 1) }}
                </div>
                <h2 class="text-xl font-bold text-white mb-1">{{ $agent->user->name }}</h2>
                <p class="text-gray-400 text-sm mb-4">{{ $agent->user->email }}</p>
                
                <div class="grid grid-cols-2 gap-4 border-t border-white/10 pt-4">
                    <div>
                        <div class="text-2xl font-black text-white">{{ $agent->rating }}</div>
                        <div class="text-xs text-gray-500 uppercase font-bold">Rating</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white">{{ $agent->total_completed_requests }}</div>
                        <div class="text-xs text-gray-500 uppercase font-bold">Jobs</div>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Contact Info</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">Phone</label>
                        <p class="text-white">{{ $agent->phone }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">NIDA Number</label>
                        <p class="text-white font-mono">{{ $agent->nida_number }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">Joined</label>
                        <p class="text-white">{{ $agent->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents & History -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Documents -->
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Documents</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($agent->documents as $doc)
                    <a href="{{ route('admin.documents.view', $doc) }}" target="_blank" class="group relative aspect-video bg-black rounded-xl overflow-hidden border border-white/10 hover:border-amber-500/50 transition flex items-center justify-center">
                        @if(Str::endsWith(strtolower($doc->file_path), '.pdf'))
                            <div class="text-center">
                                <svg class="w-12 h-12 text-red-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <span class="text-white font-bold text-sm">PDF Document</span>
                            </div>
                        @else
                            <img src="{{ route('admin.documents.view', $doc) }}" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition">
                        @endif
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="bg-black/50 px-2 py-1 rounded text-xs text-white font-bold uppercase">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                        </div>
                    </a>
                    @empty
                    <div class="col-span-2 text-center py-8 text-gray-500 italic">
                        No documents uploaded.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Face Verification (Liveness Detection) -->
            @php
                $faceVerification = $agent->faceVerification;
            @endphp
            <div class="glass-card rounded-3xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white">🔐 Face Verification (Liveness)</h3>
                    @if($faceVerification)
                        @if($faceVerification->status === 'approved')
                            <span class="px-3 py-1 rounded-full bg-green-500/20 text-green-500 text-xs font-bold">✓ Imehakikiwa</span>
                        @elseif($faceVerification->status === 'rejected')
                            <span class="px-3 py-1 rounded-full bg-red-500/20 text-red-500 text-xs font-bold">✗ Imekataliwa</span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-500 text-xs font-bold">⏳ Inasubiri</span>
                        @endif
                    @endif
                </div>
                
                @if($faceVerification && $faceVerification->isComplete())
                    <!-- Face Images Grid (Cross Pattern) -->
                    <div class="grid grid-cols-3 gap-3 max-w-md mx-auto">
                        <!-- Top Row - Up -->
                        <div></div>
                        <div class="text-center">
                            <a href="{{ route('admin.face-verification.image', [$faceVerification, 'up']) }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border border-white/10 hover:border-amber-500/50 transition">
                                <img src="{{ route('admin.face-verification.image', [$faceVerification, 'up']) }}" alt="Juu" class="w-full h-full object-cover">
                            </a>
                            <p class="text-gray-500 text-xs mt-1">⬆ Juu</p>
                        </div>
                        <div></div>

                        <!-- Middle Row - Left, Center, Right -->
                        <div class="text-center">
                            <a href="{{ route('admin.face-verification.image', [$faceVerification, 'left']) }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border border-white/10 hover:border-amber-500/50 transition">
                                <img src="{{ route('admin.face-verification.image', [$faceVerification, 'left']) }}" alt="Kushoto" class="w-full h-full object-cover">
                            </a>
                            <p class="text-gray-500 text-xs mt-1">⬅ Kushoto</p>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('admin.face-verification.image', [$faceVerification, 'center']) }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border-2 border-amber-500 hover:border-amber-400 transition">
                                <img src="{{ route('admin.face-verification.image', [$faceVerification, 'center']) }}" alt="Katikati" class="w-full h-full object-cover">
                            </a>
                            <p class="text-amber-500 text-xs mt-1 font-bold">⬤ Katikati</p>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('admin.face-verification.image', [$faceVerification, 'right']) }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border border-white/10 hover:border-amber-500/50 transition">
                                <img src="{{ route('admin.face-verification.image', [$faceVerification, 'right']) }}" alt="Kulia" class="w-full h-full object-cover">
                            </a>
                            <p class="text-gray-500 text-xs mt-1">Kulia ➡</p>
                        </div>

                        <!-- Bottom Row - Down -->
                        <div></div>
                        <div class="text-center">
                            <a href="{{ route('admin.face-verification.image', [$faceVerification, 'down']) }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border border-white/10 hover:border-amber-500/50 transition">
                                <img src="{{ route('admin.face-verification.image', [$faceVerification, 'down']) }}" alt="Chini" class="w-full h-full object-cover">
                            </a>
                            <p class="text-gray-500 text-xs mt-1">⬇ Chini</p>
                        </div>
                        <div></div>
                    </div>

                    <!-- Actions -->
                    @if($faceVerification->status === 'pending')
                    <div class="flex gap-3 mt-6 justify-center">
                        <a href="{{ route('admin.face-verification.show', $faceVerification) }}" class="px-4 py-2 bg-white/10 rounded-xl text-white hover:bg-white/20 transition font-bold text-sm">
                            Angalia Zaidi
                        </a>
                    </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto bg-gray-800 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </div>
                        <p class="text-gray-500">Hakuna uthibitishaji wa uso bado.</p>
                        <p class="text-gray-600 text-sm mt-1">Agent hajapakia picha za uso wake.</p>
                    </div>
                @endif
            </div>

            <!-- Recent Jobs -->
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Recent Jobs</h3>
                <div class="space-y-4">
                    @forelse($agent->lineRequests()->latest()->take(5)->get() as $job)
                    <div class="flex items-center justify-between p-4 rounded-xl bg-white/5 border border-white/5">
                        <div>
                            <p class="text-white font-bold">{{ ucfirst($job->line_type->value) }} Registration</p>
                            <p class="text-gray-400 text-sm">Customer: {{ $job->customer->user->name ?? 'Unknown' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase 
                                {{ $job->status->value == 'completed' ? 'bg-green-500/20 text-green-500' : 'bg-blue-500/20 text-blue-500' }}">
                                {{ $job->status->value }}
                            </span>
                            <p class="text-gray-500 text-xs mt-1">{{ $job->created_at->format('d M') }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500 italic">
                        No jobs history yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
