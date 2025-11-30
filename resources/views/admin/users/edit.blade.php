@extends('layouts.dashboard')

@section('title', 'Edit User - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .form-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .form-input:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: #f59e0b;
        outline: none;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
    }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto space-y-8">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-white/10 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-black text-white mb-1">Edit User</h1>
            <p class="text-gray-400 font-medium">Update user details</p>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-8">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-gray-400 text-sm font-bold mb-2">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input w-full rounded-xl px-4 py-3" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-400 text-sm font-bold mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input w-full rounded-xl px-4 py-3" required>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-400 text-sm font-bold mb-2">Role</label>
                <select name="role" class="form-input w-full rounded-xl px-4 py-3 appearance-none">
                    <option value="customer" {{ $user->role === 'customer' ? 'selected' : '' }} class="bg-gray-900">Customer</option>
                    <option value="agent" {{ $user->role === 'agent' ? 'selected' : '' }} class="bg-gray-900">Agent</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }} class="bg-gray-900">Admin</option>
                </select>
                @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-amber-500 hover:bg-amber-400 text-black font-bold rounded-xl shadow-lg shadow-amber-500/20 transition transform hover:scale-105">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
