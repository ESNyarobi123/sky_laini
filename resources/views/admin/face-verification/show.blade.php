@extends('layouts.dashboard')

@section('title', 'Face Verification Details - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .face-image-large {
        transition: transform 0.3s ease;
    }
    .face-image-large:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.face-verification.index') }}" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-white/10 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-black text-white mb-1">Uthibitishaji wa Uso</h1>
            <p class="text-gray-400">{{ $verification->agent->user->name ?? 'Unknown Agent' }}</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            @if($verification->status === 'pending')
            <span class="px-4 py-2 rounded-full bg-amber-500/20 text-amber-500 font-bold text-sm">Inasubiri</span>
            @elseif($verification->status === 'approved')
            <span class="px-4 py-2 rounded-full bg-green-500/20 text-green-500 font-bold text-sm">Imekubaliwa</span>
            @else
            <span class="px-4 py-2 rounded-full bg-red-500/20 text-red-500 font-bold text-sm">Imekataliwa</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Agent Info -->
        <div class="space-y-6">
            <div class="glass-card rounded-3xl p-6 text-center">
                <div class="w-32 h-32 mx-auto rounded-full bg-amber-500 flex items-center justify-center text-black font-bold text-4xl mb-4">
                    {{ substr($verification->agent->user->name ?? 'A', 0, 1) }}
                </div>
                <h2 class="text-xl font-bold text-white mb-1">{{ $verification->agent->user->name ?? 'Unknown' }}</h2>
                <p class="text-gray-400 text-sm mb-4">{{ $verification->agent->user->email ?? '-' }}</p>
                
                <div class="grid grid-cols-2 gap-4 border-t border-white/10 pt-4">
                    <div>
                        <div class="text-2xl font-black text-white">{{ $verification->agent->rating ?? 0 }}</div>
                        <div class="text-xs text-gray-500 uppercase font-bold">Rating</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white">{{ $verification->agent->total_completed_requests ?? 0 }}</div>
                        <div class="text-xs text-gray-500 uppercase font-bold">Jobs</div>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Maelezo ya Agent</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">Simu</label>
                        <p class="text-white">{{ $verification->agent->phone }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">NIDA</label>
                        <p class="text-white font-mono">{{ $verification->agent->nida_number }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">Tarehe ya Kujisajili</label>
                        <p class="text-white">{{ $verification->agent->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">Tarehe ya Kutuma Picha</label>
                        <p class="text-white">{{ $verification->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    @if($verification->device_info)
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">Kifaa Kilichotumika</label>
                        <p class="text-gray-400 text-sm break-words">{{ Str::limit($verification->device_info, 100) }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Verification Actions -->
            @if($verification->status === 'pending')
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Hatua</h3>
                <div class="space-y-3">
                    <form action="{{ route('admin.face-verification.approve', $verification) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Una uhakika unataka kukubali uthibitishaji huu?')" class="w-full px-4 py-3 bg-green-500 rounded-xl text-black font-bold hover:bg-green-400 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kubali Uthibitishaji
                        </button>
                    </form>
                    <button onclick="openRejectModal()" class="w-full px-4 py-3 bg-red-500/20 rounded-xl text-red-500 font-bold hover:bg-red-500/30 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Kataa Uthibitishaji
                    </button>
                </div>
            </div>
            @elseif($verification->status === 'rejected')
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Sababu ya Kukataliwa</h3>
                <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                    <p class="text-red-400">{{ $verification->rejection_reason }}</p>
                </div>
                @if($verification->verifiedBy)
                <p class="text-gray-500 text-sm mt-4">Imekataliwa na: {{ $verification->verifiedBy->name }}</p>
                <p class="text-gray-500 text-sm">Tarehe: {{ $verification->verified_at->format('d M Y, h:i A') }}</p>
                @endif
            </div>
            @else
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Imekubaliwa</h3>
                <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4">
                    <p class="text-green-400">Uthibitishaji huu umekubaliwa.</p>
                </div>
                @if($verification->verifiedBy)
                <p class="text-gray-500 text-sm mt-4">Imekubaliwa na: {{ $verification->verifiedBy->name }}</p>
                <p class="text-gray-500 text-sm">Tarehe: {{ $verification->verified_at->format('d M Y, h:i A') }}</p>
                @endif
            </div>
            @endif
        </div>

        <!-- Face Images (Large View) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Picha za Uso - Liveness Detection</h3>
                <p class="text-gray-400 text-sm mb-6">Picha zilizopigwa kutoka mwelekeo tofauti kwa ajili ya uthibitishaji</p>
                
                <!-- Main Grid -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <!-- Top Row - Up -->
                    <div></div>
                    <div class="text-center">
                        <a href="{{ route('admin.face-verification.image', [$verification, 'up']) }}" target="_blank" class="block">
                            <div class="aspect-square rounded-2xl overflow-hidden border-2 border-white/10 hover:border-amber-500/50 transition face-image-large">
                                @if($verification->face_up)
                                <img src="{{ route('admin.face-verification.image', [$verification, 'up']) }}" alt="Juu" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                    <span class="text-gray-500">Hakuna</span>
                                </div>
                                @endif
                            </div>
                        </a>
                        <p class="text-amber-500 font-bold text-sm mt-2">⬆ JUU</p>
                    </div>
                    <div></div>

                    <!-- Middle Row - Left, Center, Right -->
                    <div class="text-center">
                        <a href="{{ route('admin.face-verification.image', [$verification, 'left']) }}" target="_blank" class="block">
                            <div class="aspect-square rounded-2xl overflow-hidden border-2 border-white/10 hover:border-amber-500/50 transition face-image-large">
                                @if($verification->face_left)
                                <img src="{{ route('admin.face-verification.image', [$verification, 'left']) }}" alt="Kushoto" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                    <span class="text-gray-500">Hakuna</span>
                                </div>
                                @endif
                            </div>
                        </a>
                        <p class="text-amber-500 font-bold text-sm mt-2">⬅ KUSHOTO</p>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('admin.face-verification.image', [$verification, 'center']) }}" target="_blank" class="block">
                            <div class="aspect-square rounded-2xl overflow-hidden border-4 border-amber-500 hover:border-amber-400 transition face-image-large">
                                @if($verification->face_center)
                                <img src="{{ route('admin.face-verification.image', [$verification, 'center']) }}" alt="Katikati" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                    <span class="text-gray-500">Hakuna</span>
                                </div>
                                @endif
                            </div>
                        </a>
                        <p class="text-amber-500 font-bold text-sm mt-2">⬤ KATIKATI</p>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('admin.face-verification.image', [$verification, 'right']) }}" target="_blank" class="block">
                            <div class="aspect-square rounded-2xl overflow-hidden border-2 border-white/10 hover:border-amber-500/50 transition face-image-large">
                                @if($verification->face_right)
                                <img src="{{ route('admin.face-verification.image', [$verification, 'right']) }}" alt="Kulia" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                    <span class="text-gray-500">Hakuna</span>
                                </div>
                                @endif
                            </div>
                        </a>
                        <p class="text-amber-500 font-bold text-sm mt-2">KULIA ➡</p>
                    </div>

                    <!-- Bottom Row - Down -->
                    <div></div>
                    <div class="text-center">
                        <a href="{{ route('admin.face-verification.image', [$verification, 'down']) }}" target="_blank" class="block">
                            <div class="aspect-square rounded-2xl overflow-hidden border-2 border-white/10 hover:border-amber-500/50 transition face-image-large">
                                @if($verification->face_down)
                                <img src="{{ route('admin.face-verification.image', [$verification, 'down']) }}" alt="Chini" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                    <span class="text-gray-500">Hakuna</span>
                                </div>
                                @endif
                            </div>
                        </a>
                        <p class="text-amber-500 font-bold text-sm mt-2">⬇ CHINI</p>
                    </div>
                    <div></div>
                </div>

                <!-- Instructions -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4">
                    <h4 class="text-blue-400 font-bold mb-2">Maelekezo ya Uhakiki</h4>
                    <ul class="text-gray-400 text-sm space-y-1">
                        <li>✓ Hakikisha picha zote 5 zinaonyesha uso uleule</li>
                        <li>✓ Angalia kama uso unaonekana wazi na sio picha ya mtu mwingine</li>
                        <li>✓ Linganisha na picha ya passport kwenye documents</li>
                        <li>✓ Hakikisha mwanga ni mzuri na uso unaonekana vizuri</li>
                    </ul>
                </div>
            </div>

            <!-- Documents Comparison -->
            @if($verification->agent->documents->count() > 0)
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Nyaraka za Uthibitisho (Kwa Kulinganisha)</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($verification->agent->documents as $doc)
                    <a href="{{ route('admin.documents.view', $doc) }}" target="_blank" class="group relative aspect-video bg-black rounded-xl overflow-hidden border border-white/10 hover:border-amber-500/50 transition">
                        @if(Str::endsWith(strtolower($doc->file_path), '.pdf'))
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        @else
                        <img src="{{ route('admin.documents.view', $doc) }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition">
                        @endif
                        <div class="absolute bottom-0 left-0 right-0 bg-black/70 px-2 py-1">
                            <span class="text-white text-xs font-bold uppercase">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-3xl p-8 max-w-md w-full border border-white/10">
        <h3 class="text-xl font-bold text-white mb-4">Kataa Uthibitishaji</h3>
        <form action="{{ route('admin.face-verification.reject', $verification) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-400 text-sm mb-2">Sababu ya Kukataa</label>
                <textarea name="rejection_reason" rows="4" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-amber-500 transition" placeholder="Eleza sababu ya kukataa..." required></textarea>
            </div>
            <div class="mb-4">
                <p class="text-gray-500 text-sm">Sababu za kawaida:</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    <button type="button" onclick="setReason('Picha hazionekani vizuri')" class="px-3 py-1 bg-white/10 rounded-lg text-gray-400 text-sm hover:bg-white/20 transition">Picha hazionekani vizuri</button>
                    <button type="button" onclick="setReason('Uso tofauti na passport')" class="px-3 py-1 bg-white/10 rounded-lg text-gray-400 text-sm hover:bg-white/20 transition">Uso tofauti</button>
                    <button type="button" onclick="setReason('Picha ya mtu mwingine')" class="px-3 py-1 bg-white/10 rounded-lg text-gray-400 text-sm hover:bg-white/20 transition">Picha ya mtu mwingine</button>
                </div>
            </div>
            <div class="flex gap-4">
                <button type="button" onclick="closeRejectModal()" class="flex-1 px-4 py-3 bg-white/10 rounded-xl text-white font-bold hover:bg-white/20 transition">
                    Ghairi
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-red-500 rounded-xl text-white font-bold hover:bg-red-400 transition">
                    Kataa
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    function setReason(reason) {
        document.querySelector('textarea[name="rejection_reason"]').value = reason;
    }

    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
</script>
@endpush
@endsection
