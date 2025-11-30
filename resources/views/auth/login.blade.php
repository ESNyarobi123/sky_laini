<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingia • SKY LAINI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800|clash-display:400,500,600,700" rel="stylesheet" />
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
        .glass-card {
            background: rgba(20, 20, 20, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        .btn-gold {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            transition: all 0.3s ease;
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
        }
        .input-field {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            color: white;
        }
        .input-field:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        }
    </style>
</head>
<body class="bg-black min-h-screen flex flex-col items-center justify-center relative overflow-x-hidden py-12 px-4 sm:px-6 lg:px-8">

    <!-- Background Elements -->
    <div class="fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-black to-black"></div>
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden">
            <div class="absolute top-20 left-10 w-96 h-96 bg-amber-600/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-orange-600/10 rounded-full blur-[100px]"></div>
        </div>
    </div>

    <div class="w-full max-w-md p-6">
        <div class="text-center mb-8">
            <a href="/" class="text-4xl font-black text-white font-display tracking-tight flex items-center justify-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-black shadow-lg shadow-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                SKY LAINI
            </a>
            <p class="text-gray-400 mt-3 font-medium">Karibu tena! Tafadhali ingia.</p>
        </div>

        <div class="glass-card rounded-3xl p-8 md:p-10">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-bold text-gray-300 mb-2">Barua Pepe / Namba ya Simu</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                        class="input-field w-full px-5 py-4 rounded-xl outline-none placeholder-gray-600 font-medium"
                        placeholder="mfano@skylaini.co.tz">
                    @error('email')
                        <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-bold text-gray-300">Nenosiri</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-amber-500 hover:text-amber-400">Umesahau nenosiri?</a>
                        @endif
                    </div>
                    <input id="password" type="password" name="password" required 
                        class="input-field w-full px-5 py-4 rounded-xl outline-none placeholder-gray-600 font-medium"
                        placeholder="••••••••">
                    @error('password')
                        <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-amber-500 border-gray-600 rounded focus:ring-amber-500 bg-gray-800">
                    <label for="remember" class="ml-2 block text-sm text-gray-400 font-medium">Nikumbuke</label>
                </div>

                <button type="submit" class="w-full py-4 rounded-xl btn-gold text-black font-bold text-lg shadow-lg relative overflow-hidden group">
                    <span class="relative z-10">Ingia</span>
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-400 text-sm font-medium">
                    Huna akaunti bado? 
                    <a href="{{ route('register') }}" class="text-amber-500 font-bold hover:text-amber-400 underline decoration-2 decoration-amber-500/30 underline-offset-4">Jisajili hapa</a>
                </p>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-gray-600 text-xs font-medium">© {{ date('Y') }} Sky Laini. Haki zote zimehifadhiwa.</p>
        </div>
    </div>

</body>
</html>
