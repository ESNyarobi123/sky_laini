@extends('layouts.dashboard')

@section('title', 'Manage Users - SKY LAINI')

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
            <h1 class="text-3xl font-black text-white mb-2">Manage Users</h1>
            <p class="text-gray-400 font-medium">View and manage all system users</p>
        </div>
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
                        <th class="p-6 font-bold">Name</th>
                        <th class="p-6 font-bold">Email</th>
                        <th class="p-6 font-bold">Role</th>
                        <th class="p-6 font-bold">Joined</th>
                        <th class="p-6 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($users as $user)
                    <tr class="table-row transition">
                        <td class="p-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-500 font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <span class="text-white font-bold">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="p-6 text-gray-300">{{ $user->email }}</td>
                        <td class="p-6">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase 
                                {{ $user->role === 'admin' ? 'bg-red-500/20 text-red-500' : 
                                   ($user->role === 'agent' ? 'bg-blue-500/20 text-blue-500' : 'bg-green-500/20 text-green-500') }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="p-6 text-gray-400 text-sm">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="p-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-blue-400 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-500 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
