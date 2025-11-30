<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard - SKY LAINI')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900|clash-display:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'sans-serif'],
                        display: ['Clash Display', 'sans-serif']
                    },
                    colors: {
                        gold: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 88px;
        }
        .glass-sidebar {
            background: rgba(15, 15, 15, 0.9);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-nav {
            background: rgba(15, 15, 15, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .sidebar {
            width: var(--sidebar-width);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .logo-text {
            display: none;
            opacity: 0;
        }
        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                height: 100vh;
                z-index: 50;
                width: var(--sidebar-width); /* Always full width on mobile */
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }
            .main-content.expanded {
                margin-left: 0;
            }
        }
        .nav-item.active {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0) 100%);
            border-left: 4px solid #f59e0b;
            color: #fbbf24;
        }
        .nav-item:hover:not(.active) {
            background: rgba(255, 255, 255, 0.03);
            color: #fbbf24;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-black text-white overflow-x-hidden selection:bg-amber-500 selection:text-black">
    
    <!-- Background Elements -->
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden bg-black">
        <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-amber-900/20 via-black to-black"></div>
    </div>

    <div class="flex">
        <!-- Sidebar Component -->
        <x-sidebar />
        
        <!-- Main Content -->
        <div id="mainContent" class="main-content flex-1 min-h-screen flex flex-col">
            <!-- Navbar -->
            <header class="glass-nav sticky top-0 z-40 px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <!-- Mobile Toggle -->
                    <button id="sidebarToggle" class="lg:hidden text-white p-2 hover:bg-white/10 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    <!-- Desktop Toggle -->
                    <button id="desktopSidebarToggle" class="hidden lg:block text-gray-400 hover:text-white p-2 hover:bg-white/10 rounded-lg transition" title="Toggle Sidebar">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    </button>
                </div>

                <div class="flex items-center gap-4 ml-auto">
                    <div class="hidden md:flex items-center gap-2 px-4 py-2 bg-white/5 rounded-full border border-white/10">
                        <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                        <span class="text-sm font-bold text-gray-300">System Operational</span>
                    </div>
                    
                    <div class="flex items-center gap-3 pl-4 border-l border-white/10">
                        <div class="text-right hidden md:block">
                            <div class="text-sm font-bold text-white">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-amber-500 font-medium capitalize">{{ Auth::user()->role }}</div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-black font-bold text-lg shadow-lg">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-6 lg:p-10 flex-1">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity opacity-0"></div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const desktopSidebarToggle = document.getElementById('desktopSidebarToggle');
        const mobileOverlay = document.getElementById('mobileOverlay');

        // Mobile Toggle
        function toggleMobileSidebar() {
            sidebar.classList.toggle('open');
            if (sidebar.classList.contains('open')) {
                mobileOverlay.classList.remove('hidden');
                setTimeout(() => mobileOverlay.classList.remove('opacity-0'), 10);
            } else {
                mobileOverlay.classList.add('opacity-0');
                setTimeout(() => mobileOverlay.classList.add('hidden'), 300);
            }
        }

        // Desktop Toggle (Collapse/Expand)
        function toggleDesktopSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Save preference
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }

        // Restore State
        document.addEventListener('DOMContentLoaded', () => {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed && window.innerWidth >= 1024) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }
        });

        sidebarToggle.addEventListener('click', toggleMobileSidebar);
        mobileOverlay.addEventListener('click', toggleMobileSidebar);
        desktopSidebarToggle.addEventListener('click', toggleDesktopSidebar);
    </script>
    @stack('scripts')
</body>
</html>
