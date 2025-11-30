<nav class="bg-white/80 backdrop-blur-lg border-b border-sky-200 shadow-sm sticky top-0 z-40">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Button -->
                <button id="sidebar-toggle" class="md:hidden p-2 rounded-lg text-sky-700 hover:bg-sky-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-xl font-bold text-sky-900">@yield('page-title', 'Dashboard')</h2>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Notifications -->
                <button class="p-2 rounded-lg text-sky-700 hover:bg-sky-50 relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                
                <!-- User Menu -->
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <div class="font-semibold text-sky-900">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-sky-600">{{ Auth::user()->role->value }}</div>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });
</script>
