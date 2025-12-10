@extends('layouts.dashboard')

@section('title', 'Wasifu Wangu - SKY LAINI')

@push('styles')
<style>
    .profile-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: all 0.3s ease;
    }
    .profile-card:hover {
        border-color: rgba(245, 158, 11, 0.3);
    }
    .avatar-upload {
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .avatar-upload:hover .avatar-overlay {
        opacity: 1;
    }
    .avatar-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        border-radius: 50%;
    }
    .star-rating {
        display: flex;
        gap: 2px;
    }
    .star-rating .star {
        color: #374151;
    }
    .star-rating .star.active {
        color: #f59e0b;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black text-white mb-2 tracking-tight">Wasifu Wangu</h1>
            <p class="text-gray-400 font-medium text-lg">Simamia wasifu wako na mipangilio</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-500/10 border border-green-500/30 rounded-2xl p-4 flex items-center gap-3">
        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-green-500 font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-4 flex items-center gap-3">
        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-red-500 font-bold">{{ $errors->first() }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Profile Card -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Profile Picture Card -->
            <div class="profile-card rounded-[2rem] p-8 text-center">
                <form action="{{ route(request()->routeIs('customer.*') ? 'customer.profile.picture.upload' : 'agent.profile.picture.upload') }}" method="POST" enctype="multipart/form-data" id="pictureForm">
                    @csrf
                    <div class="relative inline-block mb-6">
                        <label for="profile_picture" class="avatar-upload block">
                            <div class="w-36 h-36 rounded-full bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-black text-5xl font-black shadow-2xl shadow-amber-500/30 mx-auto overflow-hidden">
                                @if($user->profile_picture)
                                    <img src="{{ url('storage/profile_pictures/' . $user->profile_picture) }}" alt="Profile" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                @endif
                            </div>
                            <div class="avatar-overlay">
                                <div class="text-center">
                                    <svg class="w-8 h-8 text-white mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="text-white text-xs font-bold">Badilisha</span>
                                </div>
                            </div>
                        </label>
                        <input type="file" id="profile_picture" name="profile_picture" class="hidden" accept="image/*" onchange="document.getElementById('pictureForm').submit()">
                    </div>
                </form>

                <h2 class="text-2xl font-black text-white mb-1">{{ $user->name }}</h2>
                <p class="text-amber-500 font-bold uppercase tracking-wider text-sm mb-4">{{ $user->role->value ?? 'User' }}</p>
                
                <div class="space-y-3 text-left">
                    <div class="flex items-center gap-3 text-gray-400 bg-white/5 p-3 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span class="text-sm">{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-400 bg-white/5 p-3 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <span class="text-sm">{{ $user->phone ?? 'Hakuna nambari' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-400 bg-white/5 p-3 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="text-sm">Umejiunga {{ $user->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                @if($user->profile_picture)
                <form action="{{ route(request()->routeIs('customer.*') ? 'customer.profile.picture.delete' : 'agent.profile.picture.delete') }}" method="POST" class="mt-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 text-sm font-bold hover:text-red-400 transition">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Futa Picha
                    </button>
                </form>
                @endif
            </div>

            <!-- Agent Stats (if agent) -->
            @if($user->isAgent() && $user->agent)
            <div class="profile-card rounded-[2rem] p-6">
                <h3 class="text-lg font-black text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Takwimu Zangu
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Kazi Zilizokamilika</span>
                        <span class="text-white font-bold text-xl">{{ $user->agent->total_completed_requests ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Mapato Yote</span>
                        <span class="text-amber-500 font-bold text-xl">TSh {{ number_format($user->agent->total_earnings ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Rating</span>
                        <div class="flex items-center gap-2">
                            <div class="star-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 star {{ $i <= ($user->agent->rating ?? 0) ? 'active' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endfor
                            </div>
                            <span class="text-white font-bold">{{ number_format($user->agent->rating ?? 0, 1) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Salio la Wallet</span>
                        <span class="text-green-500 font-bold text-xl">TSh {{ number_format($user->agent->wallet->balance ?? 0) }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Settings & Actions -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Update Name -->
            <div class="profile-card rounded-[2rem] p-8">
                <h3 class="text-xl font-black text-white mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    Badilisha Jina
                </h3>
                <form action="{{ route(request()->routeIs('customer.*') ? 'customer.profile.name.update' : 'agent.profile.name.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-col md:flex-row gap-4">
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                               class="flex-1 bg-white/5 border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition"
                               placeholder="Jina lako kamili" required>
                        <button type="submit" class="px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-black font-bold rounded-xl shadow-lg shadow-amber-500/20 hover:shadow-amber-500/40 transition transform hover:-translate-y-1">
                            Hifadhi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Update Password -->
            <div class="profile-card rounded-[2rem] p-8">
                <h3 class="text-xl font-black text-white mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    Badilisha Nenosiri
                </h3>
                <form action="{{ route(request()->routeIs('customer.*') ? 'customer.profile.password.update' : 'agent.profile.password.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="text-gray-400 text-sm font-bold mb-2 block">Nenosiri la Sasa</label>
                        <input type="password" name="current_password" 
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition"
                               placeholder="••••••••" required>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-400 text-sm font-bold mb-2 block">Nenosiri Jipya</label>
                            <input type="password" name="password" 
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition"
                                   placeholder="••••••••" required minlength="8">
                        </div>
                        <div>
                            <label class="text-gray-400 text-sm font-bold mb-2 block">Thibitisha Nenosiri</label>
                            <input type="password" name="password_confirmation" 
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition"
                                   placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="px-8 py-4 bg-red-500/20 text-red-500 border border-red-500/30 font-bold rounded-xl hover:bg-red-500 hover:text-white transition transform hover:-translate-y-1">
                        Badilisha Nenosiri
                    </button>
                </form>
            </div>

            <!-- Withdrawal Section (For Agents) -->
            @if($user->isAgent() && $user->agent && $user->agent->wallet)
            <div class="profile-card rounded-[2rem] p-8">
                <h3 class="text-xl font-black text-white mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    Toa Pesa
                    <span class="ml-auto text-green-500 font-bold">Salio: TSh {{ number_format($user->agent->wallet->balance ?? 0) }}</span>
                </h3>
                <form action="{{ route('agent.profile.withdrawal.request') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-400 text-sm font-bold mb-2 block">Kiasi (TSh)</label>
                            <input type="number" name="amount" min="1000" step="100"
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition"
                                   placeholder="1000" required>
                            <p class="text-gray-500 text-xs mt-1">Kiwango cha chini: TSh 1,000</p>
                        </div>
                        <div>
                            <label class="text-gray-400 text-sm font-bold mb-2 block">Njia ya Malipo</label>
                            <select name="payment_method" 
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition" required>
                                <option value="mpesa" class="bg-gray-900">M-Pesa</option>
                                <option value="tigopesa" class="bg-gray-900">Tigo Pesa</option>
                                <option value="airtelmoney" class="bg-gray-900">Airtel Money</option>
                                <option value="bank" class="bg-gray-900">Benki</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-400 text-sm font-bold mb-2 block">Nambari ya Akaunti/Simu</label>
                            <input type="text" name="account_number" 
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition"
                                   placeholder="0712345678" required>
                        </div>
                        <div>
                            <label class="text-gray-400 text-sm font-bold mb-2 block">Jina la Akaunti</label>
                            <input type="text" name="account_name" 
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition"
                                   placeholder="John Doe" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-green-500/20 hover:shadow-green-500/40 transition transform hover:-translate-y-1">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Toa Pesa
                    </button>
                </form>

                <!-- Withdrawal History -->
                @if($withdrawals->count() > 0)
                <div class="mt-8 pt-6 border-t border-white/10">
                    <h4 class="text-lg font-bold text-white mb-4">Historia ya Withdrawal</h4>
                    <div class="space-y-3">
                        @foreach($withdrawals as $withdrawal)
                        <div class="flex items-center justify-between bg-white/5 p-4 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    {{ $withdrawal->status === 'completed' ? 'bg-green-500/20 text-green-500' : 
                                       ($withdrawal->status === 'rejected' ? 'bg-red-500/20 text-red-500' : 'bg-amber-500/20 text-amber-500') }}">
                                    @if($withdrawal->status === 'completed')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @elseif($withdrawal->status === 'rejected')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-white font-bold">TSh {{ number_format($withdrawal->amount) }}</p>
                                    <p class="text-gray-500 text-xs">{{ strtoupper($withdrawal->payment_method) }} - {{ $withdrawal->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                                {{ $withdrawal->status === 'completed' ? 'bg-green-500/20 text-green-500' : 
                                   ($withdrawal->status === 'rejected' ? 'bg-red-500/20 text-red-500' : 'bg-amber-500/20 text-amber-500') }}">
                                {{ $withdrawal->status === 'completed' ? 'Imekamilika' : ($withdrawal->status === 'rejected' ? 'Imekataliwa' : 'Inasubiri') }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Ratings Section (For Agents) -->
            @if($user->isAgent() && $ratings->count() > 0)
            <div class="profile-card rounded-[2rem] p-8">
                <h3 class="text-xl font-black text-white mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    </div>
                    Ukadiriaji Wangu
                </h3>
                <div class="space-y-4">
                    @foreach($ratings as $rating)
                    <div class="bg-white/5 p-5 rounded-xl border border-white/5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($rating->customer->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-white font-bold">{{ $rating->customer->user->name ?? 'Unknown' }}</p>
                                    <p class="text-gray-500 text-xs">{{ $rating->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="star-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 star {{ $i <= $rating->rating ? 'active' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endfor
                            </div>
                        </div>
                        @if($rating->review)
                        <p class="text-gray-300 text-sm italic">"{{ $rating->review }}"</p>
                        @else
                        <p class="text-gray-500 text-sm italic">Hakuna maoni</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
