@extends('layouts.dashboard')

@section('title', 'Face Verification History - SKY LAINI')

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
        <a href="{{ route('admin.face-verification.index') }}" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-white/10 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Historia ya Uthibitishaji</h1>
            <p class="text-gray-400">Rekodi za uthibitishaji wa uso zilizokwishahakikiwa</p>
        </div>
    </div>

    <!-- History Table -->
    <div class="glass-card rounded-3xl p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="text-left text-gray-400 text-sm font-bold uppercase pb-4">Agent</th>
                        <th class="text-left text-gray-400 text-sm font-bold uppercase pb-4">Simu</th>
                        <th class="text-left text-gray-400 text-sm font-bold uppercase pb-4">Hali</th>
                        <th class="text-left text-gray-400 text-sm font-bold uppercase pb-4">Aliyehakiki</th>
                        <th class="text-left text-gray-400 text-sm font-bold uppercase pb-4">Tarehe</th>
                        <th class="text-right text-gray-400 text-sm font-bold uppercase pb-4">Hatua</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($verifications as $verification)
                    <tr class="hover:bg-white/5 transition">
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-amber-500 flex items-center justify-center text-black font-bold">
                                    {{ substr($verification->agent->user->name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-white font-bold">{{ $verification->agent->user->name ?? 'Unknown' }}</p>
                                    <p class="text-gray-500 text-xs">ID: {{ $verification->agent->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 text-gray-400">{{ $verification->agent->phone }}</td>
                        <td class="py-4">
                            @if($verification->status === 'approved')
                            <span class="px-3 py-1 rounded-full bg-green-500/20 text-green-500 text-sm font-bold">Imekubaliwa</span>
                            @else
                            <span class="px-3 py-1 rounded-full bg-red-500/20 text-red-500 text-sm font-bold">Imekataliwa</span>
                            @endif
                        </td>
                        <td class="py-4 text-gray-400">{{ $verification->verifiedBy->name ?? '-' }}</td>
                        <td class="py-4 text-gray-400">{{ $verification->verified_at?->format('d M Y, H:i') ?? '-' }}</td>
                        <td class="py-4 text-right">
                            <a href="{{ route('admin.face-verification.show', $verification) }}" class="px-4 py-2 bg-white/10 rounded-xl text-white hover:bg-white/20 transition inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Angalia
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p>Hakuna rekodi za historia</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($verifications->hasPages())
        <div class="mt-6">
            {{ $verifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
