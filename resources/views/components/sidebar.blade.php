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
                <span class="sidebar-text">{{ __('messages.nav.dashboard') }}</span>
            </a>
            <a href="{{ route('customer.line-requests.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'customer.line-requests') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.requests') }}</span>
            </a>
            <a href="{{ route('customer.bookings.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'customer.bookings') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="sidebar-text">📅 Booking</span>
            </a>
            <a href="{{ route('customer.chat.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'customer.chat') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.chat') }}</span>
            </a>
            <a href="{{ route('customer.invoices.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'customer.invoices') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="sidebar-text">{{ __('messages.payments.invoice') }}</span>
            </a>
            <a href="{{ route('customer.profile.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'customer.profile') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="sidebar-text">Wasifu Wangu</span>
            </a>
            <a href="{{ route('customer.referrals.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'customer.referrals') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-text">🎁 Referral</span>
            </a>
            <a href="{{ route('customer.support.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'customer.support') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.support') }}</span>
            </a>
        @elseif($user->isAgent())
            <a href="{{ route('agent.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.dashboard') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.dashboard') }}</span>
            </a>
            <a href="{{ route('agent.gigs.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.gigs') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 7m0 13V7"></path></svg>
                <span class="sidebar-text">{{ __('messages.dashboard_page.available_gigs') }}</span>
            </a>
            <a href="{{ route('agent.bookings.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.bookings') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="sidebar-text">📅 Booking</span>
            </a>
            <a href="{{ route('agent.earnings.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.earnings') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-text">{{ __('messages.dashboard_page.my_earnings') }}</span>
            </a>
            <a href="{{ route('agent.chat.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.chat') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.chat') }}</span>
            </a>
            <a href="{{ route('agent.working-hours.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.working-hours') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-text">{{ __('messages.agent.working_hours') }}</span>
            </a>
            <a href="{{ route('agent.profile.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.profile') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="sidebar-text">Wasifu Wangu</span>
            </a>
            <a href="{{ route('agent.referrals.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.referrals') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-text">🎁 Referral</span>
            </a>
            <a href="{{ route('agent.support.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'agent.support') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.support') }}</span>
            </a>
        @elseif($user->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.dashboard') }}</span>
            </a>

            <a href="{{ route('admin.analytics.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.analytics') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.analytics') }}</span>
            </a>

            <a href="{{ route('admin.orders.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.orders') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="sidebar-text">All Orders</span>
            </a>

            <a href="{{ route('admin.payments.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.payments') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.payments') }}</span>
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
                <span class="sidebar-text">{{ __('messages.nav.users') }}</span>
            </a>

            <a href="{{ route('admin.tickets.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.tickets') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                <span class="sidebar-text">Support Tickets</span>
            </a>

            <a href="{{ route('admin.support.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.support') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="sidebar-text">Live Chat</span>
            </a>

            <div class="pt-3 mt-3 border-t border-white/5">
                <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Features</p>
            </div>

            <a href="{{ route('admin.monitoring.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.monitoring') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                <span class="sidebar-text">🛰️ Live Monitoring</span>
            </a>

            <a href="{{ route('admin.bookings.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.bookings') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="sidebar-text">📅 Bookings</span>
            </a>

            <a href="{{ route('admin.referrals.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.referrals') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-text">🎁 Referrals</span>
            </a>

            @php
                $pendingFaceVerifications = \App\Models\AgentFaceVerification::where('status', 'pending')
                    ->whereNotNull('face_center')
                    ->whereNotNull('face_left')
                    ->whereNotNull('face_right')
                    ->whereNotNull('face_up')
                    ->whereNotNull('face_down')
                    ->count();
            @endphp
            <a href="{{ route('admin.face-verification.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.face-verification') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <span class="sidebar-text">🔐 Face Verify</span>
                @if($pendingFaceVerifications > 0)
                <span class="ml-auto bg-amber-500 text-black text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingFaceVerifications }}</span>
                @endif
            </a>

            <a href="{{ route('admin.agents.verification') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ str_starts_with($currentRoute, 'admin.agents.verification') ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <span class="sidebar-text">📄 Doc Verify</span>
            </a>
        @endif

        <!-- Leaderboard (visible to all) -->
        <div class="pt-4 mt-4 border-t border-white/10">
            <a href="{{ route('leaderboard') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-400 font-bold rounded-xl transition-all {{ $currentRoute === 'leaderboard' ? 'active' : '' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                <span class="sidebar-text">{{ __('messages.nav.leaderboard') }}</span>
            </a>
        </div>
    </nav>

    <!-- Language Switcher & Logout -->
    <div class="p-4 border-t border-white/10 space-y-3">
        <!-- Language Switcher -->
        <div class="flex items-center gap-2 px-4 py-2 bg-white/5 rounded-xl">
            <span class="text-gray-400 text-sm">🌐</span>
            <form action="{{ route('language.switch') }}" method="POST" class="flex-1 flex gap-1">
                @csrf
                <button type="submit" name="locale" value="sw" class="flex-1 py-1 px-2 text-xs font-bold rounded-lg transition {{ app()->getLocale() === 'sw' ? 'bg-amber-500 text-black' : 'text-gray-400 hover:text-white' }}">
                    Swahili
                </button>
                <button type="submit" name="locale" value="en" class="flex-1 py-1 px-2 text-xs font-bold rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-amber-500 text-black' : 'text-gray-400 hover:text-white' }}">
                    English
                </button>
            </form>
        </div>
        
        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 px-4 py-3 text-red-500 font-bold rounded-xl hover:bg-red-500/10 transition-all w-full">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="sidebar-text">{{ __('messages.logout') }}</span>
            </button>
        </form>
    </div>
</aside>

