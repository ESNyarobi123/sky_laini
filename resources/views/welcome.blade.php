<!DOCTYPE html>
<html lang="sw" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SKY LAINI • Usajili wa Laini za Simu kwa Haraka na Uhakika</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900|clash-display:400,500,600,700" rel="stylesheet" />
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
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'shine': 'shine 4s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'gradient': 'gradient 15s ease infinite',
                    },
                    keyframes: {
                        float: {
                            '0%,100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' }
                        },
                        shine: {
                            '0%,100%': { backgroundPosition: '-200% center' },
                            '50%': { backgroundPosition: '200% center' }
                        },
                        gradient: {
                            '0%,100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass { 
            background: rgba(20, 20, 20, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-strong {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(245, 158, 11, 0.2);
        }
        .btn-gold {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.3);
            transition: all 0.4s ease;
        }
        .btn-gold:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 0 50px rgba(245, 158, 11, 0.5);
        }
        .text-gradient {
            background: linear-gradient(90deg, #fbbf24, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen overflow-x-hidden relative selection:bg-amber-500 selection:text-black">

    <!-- Background Elements -->
    <div class="fixed inset-0 -z-10 bg-black">
        <div class="absolute inset-0 bg-grid-pattern opacity-20"></div>
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-amber-900/10 via-black to-black"></div>
        <div class="absolute top-20 left-1/4 w-96 h-96 bg-amber-600/20 rounded-full blur-[128px] animate-pulse-slow"></div>
        <div class="absolute bottom-20 right-1/4 w-96 h-96 bg-orange-600/10 rounded-full blur-[128px] animate-pulse-slow" style="animation-delay: 2s"></div>
    </div>

    <!-- Navbar -->
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 glass border-b border-white/5 transition-all duration-500">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="#" class="text-3xl font-black text-white font-display tracking-tight flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-black shadow-lg shadow-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                SKY LAINI
            </a>
            
            <div class="hidden lg:flex items-center gap-8">
                <a href="#home" class="text-sm font-bold text-gray-400 hover:text-amber-400 transition tracking-wide uppercase">Nyumbani</a>
                <a href="#features" class="text-sm font-bold text-gray-400 hover:text-amber-400 transition tracking-wide uppercase">Vipengee</a>
                <a href="#networks" class="text-sm font-bold text-gray-400 hover:text-amber-400 transition tracking-wide uppercase">Mitandao</a>
                
                <div class="flex items-center gap-4 ml-4">
                    <a href="{{ route('login') }}" class="px-6 py-2.5 text-white font-bold hover:text-amber-400 transition border border-white/10 rounded-xl hover:bg-white/5">Ingia</a>
                    <a href="{{ route('register') }}" class="px-8 py-2.5 rounded-xl btn-gold text-black font-bold relative overflow-hidden group">
                        <span class="relative z-10">Jisajili Bure</span>
                        <div class="absolute inset-0 bg-white/30 scale-0 group-hover:scale-150 transition-transform duration-700 rounded-full"></div>
                    </a>
                </div>
            </div>

            <button id="menuBtn" class="lg:hidden text-white">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="fixed inset-0 bg-black/95 backdrop-blur-2xl z-40 transform translate-x-full transition-transform duration-500 lg:hidden border-l border-white/10">
        <div class="flex flex-col items-center justify-center h-full gap-8 text-2xl font-bold text-white">
            <a href="#home" class="hover:text-amber-400 transition">Nyumbani</a>
            <a href="#features" class="hover:text-amber-400 transition">Vipengee</a>
            <a href="#networks" class="hover:text-amber-400 transition">Mitandao</a>
            <div class="flex flex-col gap-4 w-64 mt-8">
                <a href="{{ route('login') }}" class="text-center py-4 border border-white/20 rounded-xl hover:bg-white/5 transition">Ingia</a>
                <a href="{{ route('register') }}" class="text-center py-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-black font-bold">Jisajili Bure</a>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section id="home" class="min-h-screen flex items-center justify-center relative px-6 pt-20">
        <div class="text-center max-w-5xl mx-auto relative z-10">
            <div class="mb-6 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-amber-400 text-sm font-bold uppercase tracking-wider animate-float">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Mfumo Mpya wa Usajili
            </div>

            <h1 class="text-6xl md:text-8xl lg:text-9xl font-black font-display leading-tight mb-6">
                <span class="text-white">SKY</span>
                <span class="text-gradient">LAINI</span>
            </h1>
            
            <p class="text-2xl md:text-4xl font-bold text-gray-300 mb-8 max-w-3xl mx-auto">
                Usajili wa Laini za Simu • <span class="text-amber-500">Popote Upo</span> • Ndani ya Dakika 15
            </p>

            <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto mb-12 leading-relaxed font-medium">
                Tuma maombi • Chagua wakala wa karibu • Lipa kwa M-Pesa/Tigo Pesa • Pokea laini mpya mlangoni kwako!
            </p>

            <div class="flex flex-col md:flex-row gap-6 justify-center items-center">
                <a href="{{ route('register') }}" class="px-12 py-5 rounded-2xl btn-gold text-black text-xl font-bold shadow-2xl shadow-amber-500/20 transform hover:scale-105 transition">
                    Anza Usajili Sasa
                    <span class="ml-2">→</span>
                </a>
                <a href="#features" class="px-12 py-5 rounded-2xl bg-white/5 border border-white/10 text-white text-xl font-bold hover:bg-white/10 transition backdrop-blur-sm">
                    Jifunze Zaidi
                </a>
            </div>

            <div class="mt-24 grid grid-cols-3 gap-8 max-w-3xl mx-auto border-t border-white/10 pt-12">
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-black text-white mb-2">15<span class="text-amber-500 text-2xl">+</span></div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">Dakika Tu</div>
                </div>
                <div class="text-center border-l border-white/10">
                    <div class="text-4xl md:text-5xl font-black text-white mb-2">5<span class="text-amber-500 text-2xl">★</span></div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">Ukadiriaji</div>
                </div>
                <div class="text-center border-l border-white/10">
                    <div class="text-4xl md:text-5xl font-black text-white mb-2">50<span class="text-amber-500 text-2xl">K</span></div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">Wateja</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/255..." class="fixed bottom-8 right-8 z-50 bg-[#25D366] hover:bg-[#20bd5a] text-white p-4 rounded-full shadow-2xl hover:scale-110 transition duration-300 group">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.952.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436 9.878 9.88 9.88a9.87 9.87 0 005.412-1.615l.383-.231"></path></svg>
        <span class="absolute right-full mr-4 top-1/2 -translate-y-1/2 bg-white text-black px-3 py-1 rounded-lg text-sm font-bold opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Chat Nasi</span>
    </a>

    <script>
        const navbar = document.getElementById('navbar');
        const mobileMenu = document.getElementById('mobileMenu');
        const menuBtn = document.getElementById('menuBtn');

        window.addEventListener('scroll', () => {
            navbar.classList.toggle('glass-strong', window.scrollY > 50);
        });

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('translate-x-full');
        });

        document.querySelectorAll('#mobileMenu a').forEach(link => {
            link.addEventListener('click', () => mobileMenu.classList.add('translate-x-full'));
        });
    </script>
</body>
</html>