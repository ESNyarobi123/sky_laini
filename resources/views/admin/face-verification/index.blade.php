@extends('layouts.dashboard')

@section('title', 'Face Verification - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .face-image {
        transition: transform 0.3s ease;
    }
    .face-image:hover {
        transform: scale(1.05);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Uthibitishaji wa Uso</h1>
            <p class="text-gray-400">Hakiki picha za uso za mawakala (Face Verification / Liveness Detection)</p>
        </div>
        <a href="{{ route('admin.face-verification.history') }}" class="px-4 py-2 bg-white/10 rounded-xl text-white hover:bg-white/20 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Historia
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-3xl font-black text-white">{{ $pendingVerifications->total() }}</p>
                    <p class="text-gray-400 text-sm">Wanasubiri</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-green-500/20 flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-3xl font-black text-white">{{ \App\Models\AgentFaceVerification::where('status', 'approved')->count() }}</p>
                    <p class="text-gray-400 text-sm">Wamehakikiwa</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-red-500/20 flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-3xl font-black text-white">{{ \App\Models\AgentFaceVerification::where('status', 'rejected')->count() }}</p>
                    <p class="text-gray-400 text-sm">Wamekataliwa</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Verifications -->
    <div class="glass-card rounded-3xl p-6">
        <h2 class="text-xl font-bold text-white mb-6">Wanasubiri Uhakiki</h2>
        
        @if($pendingVerifications->count() > 0)
        <div class="space-y-6">
            @foreach($pendingVerifications as $verification)
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10 hover:border-amber-500/30 transition">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Agent Info -->
                    <div class="lg:w-1/4">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-16 h-16 rounded-full bg-amber-500 flex items-center justify-center text-black font-bold text-2xl">
                                {{ substr($verification->agent->user->name ?? 'A', 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-white font-bold">{{ $verification->agent->user->name ?? 'Unknown' }}</h3>
                                <p class="text-gray-400 text-sm">{{ $verification->agent->phone }}</p>
                                <p class="text-gray-500 text-xs">{{ $verification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">NIDA:</span>
                                <span class="text-white font-mono">{{ $verification->agent->nida_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Email:</span>
                                <span class="text-white">{{ $verification->agent->user->email ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Face Images Grid -->
                    <div class="lg:w-2/4">
                        <p class="text-gray-400 text-sm mb-3">Picha za Uso (Liveness Detection)</p>
                        <div class="grid grid-cols-5 gap-2">
                            @foreach(['center' => 'Katikati', 'left' => 'Kushoto', 'right' => 'Kulia', 'up' => 'Juu', 'down' => 'Chini'] as $direction => $label)
                            @php $field = 'face_' . $direction; @endphp
                            <div class="text-center">
                                <a href="{{ route('admin.face-verification.image', [$verification, $direction]) }}" target="_blank" class="block">
                                    <div class="aspect-square rounded-xl overflow-hidden border border-white/10 hover:border-amber-500/50 transition face-image">
                                        @if($verification->$field)
                                        <img src="{{ route('admin.face-verification.image', [$verification, $direction]) }}" 
                                             alt="{{ $label }}" 
                                             class="w-full h-full object-cover">
                                        @else
                                        <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        @endif
                                    </div>
                                </a>
                                <p class="text-gray-500 text-xs mt-1">{{ $label }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="lg:w-1/4 flex flex-col justify-center gap-3">
                        <a href="{{ route('admin.face-verification.show', $verification) }}" class="w-full px-4 py-3 bg-white/10 rounded-xl text-white text-center hover:bg-white/20 transition font-bold">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Angalia Zaidi
                        </a>
                        <form action="{{ route('admin.face-verification.approve', $verification) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Una uhakika unataka kukubali uthibitishaji huu?')" class="w-full px-4 py-3 bg-green-500 rounded-xl text-black font-bold hover:bg-green-400 transition">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Kubali
                            </button>
                        </form>
                        <button onclick="openRejectModal({{ $verification->id }})" class="w-full px-4 py-3 bg-red-500/20 rounded-xl text-red-500 font-bold hover:bg-red-500/30 transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Kataa
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $pendingVerifications->links() }}
        </div>
        @else
        <div class="text-center py-16">
            <div class="w-20 h-20 mx-auto bg-green-500/20 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Hakuna Wanasubiri</h3>
            <p class="text-gray-400">Uthibitishaji wote wa uso umeshakamilika.</p>
        </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-3xl p-8 max-w-md w-full border border-white/10">
        <h3 class="text-xl font-bold text-white mb-4">Kataa Uthibitishaji</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-400 text-sm mb-2">Sababu ya Kukataa</label>
                <textarea name="rejection_reason" rows="4" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-amber-500 transition" placeholder="Eleza sababu ya kukataa..." required></textarea>
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
    function openRejectModal(verificationId) {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectForm').action = '/admin/face-verification/' + verificationId + '/reject';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    // Close modal on outside click
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
</script>
@endpush
@endsection
