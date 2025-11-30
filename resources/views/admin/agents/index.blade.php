@extends('layouts.dashboard')

@section('title', 'Manage Agents - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .table-row:hover {
        background: rgba(255, 255, 255, 0.05);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Manage Agents</h1>
            <p class="text-gray-400 font-medium">View and manage all registered agents</p>
        </div>
        <a href="{{ route('admin.agents.verification') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black font-bold rounded-xl transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Pending Verifications
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-500 p-4 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-white/10 text-gray-400 text-sm uppercase tracking-wider">
                        <th class="p-6 font-bold">Agent</th>
                        <th class="p-6 font-bold">Contact</th>
                        <th class="p-6 font-bold">Status</th>
                        <th class="p-6 font-bold">Performance</th>
                        <th class="p-6 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($agents as $agent)
                    <tr class="table-row transition">
                        <td class="p-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-500 font-bold">
                                    {{ substr($agent->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-white font-bold">{{ $agent->user->name }}</div>
                                    <div class="text-xs text-gray-500">ID: #{{ $agent->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-6">
                            <div class="text-gray-300">{{ $agent->phone }}</div>
                            <div class="text-xs text-gray-500">{{ $agent->user->email }}</div>
                        </td>
                        <td class="p-6">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $agent->is_verified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $agent->is_verified ? 'Verified' : 'Unverified' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $agent->is_online ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $agent->is_online ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                        </td>
                        <td class="p-6">
                            <div class="flex items-center gap-1 text-amber-500 font-bold">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $agent->rating }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $agent->total_completed_requests }} Jobs Done</div>
                        </td>
                        <td class="p-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.agents.show', $agent) }}" class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-blue-400 transition" title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <form action="{{ route('admin.agents.toggle', $agent) }}" method="POST" onsubmit="return confirm('Are you sure you want to change this agent\'s status?');">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg {{ $agent->is_verified ? 'bg-red-500/10 hover:bg-red-500/20 text-red-500' : 'bg-green-500/10 hover:bg-green-500/20 text-green-500' }} transition" title="{{ $agent->is_verified ? 'Deactivate' : 'Activate' }}">
                                        @if($agent->is_verified)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-white/10">
            {{ $agents->links() }}
        </div>
    </div>
</div>
@endsection
