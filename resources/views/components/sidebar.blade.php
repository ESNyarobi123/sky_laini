@php
    $user = Auth::user();
    $role = $user->role;
    $currentRoute = request()->route()->getName() ?? '';
@endphp

<aside id="sidebar" class="sidebar glass-sidebar h-screen fixed top-0 left-0 overflow-y-auto z-50 flex flex-col transition-all duration-300 w-[280px]">
    <!-- Logo Area -->
    <div class="p-8 flex items-center justify-between">
        <a href="/" class="text-3xl font-black text-white font-display tracking-tight flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-black text-lg font-bold shadow-lg shadow-amber-500/20">S</div>
            <span class="sidebar-text">SKY LAINI</span>
        </a>
        <!-- Close Button (Mobile) -->
        <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 space-y-2">
        @if($user->isCustomer())
            <a href="{{ route('customer.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'customer.dashboard') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="sidebar-text">Dashboard</span>
            </a>
            <a href="{{ route('customer.line-requests.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'customer.line-requests') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="sidebar-text">My Requests</span>
            </a>
        @elseif($user->isAgent())
            <a href="{{ route('agent.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.dashboard') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span class="sidebar-text">Dashboard</span>
            </a>
            <a href="{{ route('agent.gigs.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.gigs') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 7m0 13V7"></path></svg>
                <span class="sidebar-text">Available Gigs</span>
            </a>
            <a href="{{ route('agent.earnings.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.earnings') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-text">Earnings</span>
            </a>
            <a href="{{ route('agent.support.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.support') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                <span class="sidebar-text">Support</span>
            </a>
        @elseif($user->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <a href="{{ route('admin.orders.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.orders') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="sidebar-text">All Orders</span>
            </a>

            <a href="{{ route('admin.payments.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.payments') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-text">Payments</span>
            </a>

            <a href="{{ route('admin.withdrawals.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.withdrawals') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="sidebar-text">Withdrawals</span>
            </a>

            <a href="{{ route('admin.activity.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.activity') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span class="sidebar-text">Activity Logs</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="sidebar-text">Users & Agents</span>
            </a>

            <a href="{{ route('admin.tickets.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.tickets') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                <span class="sidebar-text">Support Tickets</span>
            </a>

            <a href="{{ route('admin.support.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.support') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="sidebar-text">Live Chat</span>
            </a>
        @endif
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t border-white/10">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 px-4 py-3 text-red-500 font-bold rounded-xl hover:bg-red-500/10 transition-all w-full">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="sidebar-text">Logout</span>
            </button>
        </form>
    </div>
</aside>
