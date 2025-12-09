<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Umesahau Nenosiri • SKY LAINI</title>
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
            <p class="text-gray-400 mt-3 font-medium">Rejesha nenosiri lako</p>
        </div>

        <div class="glass-card rounded-3xl p-8 md:p-10" id="emailForm">
            <div class="mb-6 text-center">
                <p class="text-gray-300 text-sm">Ingiza barua pepe yako ili kupokea namba ya siri ya muda (OTP).</p>
            </div>

            <form id="sendOtpForm" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-300 mb-2">Barua Pepe</label>
                    <input id="email" type="email" name="email" required autofocus 
                        class="input-field w-full px-5 py-4 rounded-xl outline-none placeholder-gray-600 font-medium"
                        placeholder="mfano@skylaini.co.tz">
                    <span id="emailError" class="text-red-500 text-xs mt-1 block font-medium hidden"></span>
                </div>

                <button type="submit" id="sendOtpBtn" class="w-full py-4 rounded-xl btn-gold text-black font-bold text-lg shadow-lg relative overflow-hidden group">
                    <span class="relative z-10">Tuma OTP</span>
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                </button>
            </form>
        </div>

        <div class="glass-card rounded-3xl p-8 md:p-10 hidden" id="otpForm">
            <div class="mb-6 text-center">
                <p class="text-gray-300 text-sm">Tumetuma OTP kwenye barua pepe yako. Tafadhali ingiza hapa chini.</p>
            </div>

            <form id="verifyOtpForm" class="space-y-6">
                <div>
                    <label for="otp" class="block text-sm font-bold text-gray-300 mb-2">OTP Code</label>
                    <input id="otp" type="text" name="otp" required 
                        class="input-field w-full px-5 py-4 rounded-xl outline-none placeholder-gray-600 font-medium text-center tracking-widest text-2xl"
                        placeholder="123456" maxlength="6">
                    <span id="otpError" class="text-red-500 text-xs mt-1 block font-medium hidden"></span>
                </div>

                <button type="submit" id="verifyOtpBtn" class="w-full py-4 rounded-xl btn-gold text-black font-bold text-lg shadow-lg relative overflow-hidden group">
                    <span class="relative z-10">Thibitisha OTP</span>
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                </button>
            </form>
        </div>

        <div class="glass-card rounded-3xl p-8 md:p-10 hidden" id="resetPasswordForm">
            <div class="mb-6 text-center">
                <p class="text-gray-300 text-sm">Weka nenosiri jipya.</p>
            </div>

            <form id="newPasswordForm" class="space-y-6">
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-300 mb-2">Nenosiri Jipya</label>
                    <input id="password" type="password" name="password" required 
                        class="input-field w-full px-5 py-4 rounded-xl outline-none placeholder-gray-600 font-medium"
                        placeholder="••••••••">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-300 mb-2">Thibitisha Nenosiri</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required 
                        class="input-field w-full px-5 py-4 rounded-xl outline-none placeholder-gray-600 font-medium"
                        placeholder="••••••••">
                    <span id="passwordError" class="text-red-500 text-xs mt-1 block font-medium hidden"></span>
                </div>

                <button type="submit" id="resetBtn" class="w-full py-4 rounded-xl btn-gold text-black font-bold text-lg shadow-lg relative overflow-hidden group">
                    <span class="relative z-10">Badilisha Nenosiri</span>
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                </button>
            </form>
        </div>
        
        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="text-gray-400 text-sm font-medium hover:text-white transition-colors">
                ← Rudi kwenye kuingia
            </a>
        </div>
    </div>

    <script>
        let userEmail = '';
        let userOtp = '';

        // Send OTP
        document.getElementById('sendOtpForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const btn = document.getElementById('sendOtpBtn');
            const errorSpan = document.getElementById('emailError');
            
            btn.disabled = true;
            btn.innerHTML = '<span class="relative z-10">Inatuma...</span>';
            errorSpan.classList.add('hidden');

            try {
                const response = await fetch('/api/password/email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email })
                });

                const data = await response.json();

                if (response.ok) {
                    userEmail = email;
                    document.getElementById('emailForm').classList.add('hidden');
                    document.getElementById('otpForm').classList.remove('hidden');
                } else {
                    errorSpan.textContent = data.message || 'Tatizo limetokea. Jaribu tena.';
                    errorSpan.classList.remove('hidden');
                }
            } catch (error) {
                errorSpan.textContent = 'Tatizo la mtandao. Jaribu tena.';
                errorSpan.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="relative z-10">Tuma OTP</span><div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>';
            }
        });

        // Verify OTP
        document.getElementById('verifyOtpForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const otp = document.getElementById('otp').value;
            const btn = document.getElementById('verifyOtpBtn');
            const errorSpan = document.getElementById('otpError');
            
            btn.disabled = true;
            btn.innerHTML = '<span class="relative z-10">Inathibitisha...</span>';
            errorSpan.classList.add('hidden');

            try {
                const response = await fetch('/api/password/verify-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: userEmail, otp })
                });

                const data = await response.json();

                if (response.ok) {
                    userOtp = otp;
                    document.getElementById('otpForm').classList.add('hidden');
                    document.getElementById('resetPasswordForm').classList.remove('hidden');
                } else {
                    errorSpan.textContent = data.message || 'OTP siyo sahihi.';
                    errorSpan.classList.remove('hidden');
                }
            } catch (error) {
                errorSpan.textContent = 'Tatizo la mtandao. Jaribu tena.';
                errorSpan.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="relative z-10">Thibitisha OTP</span><div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>';
            }
        });

        // Reset Password
        document.getElementById('newPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;
            const btn = document.getElementById('resetBtn');
            const errorSpan = document.getElementById('passwordError');
            
            if (password !== password_confirmation) {
                errorSpan.textContent = 'Nenosiri halifanani.';
                errorSpan.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="relative z-10">Inabadilisha...</span>';
            errorSpan.classList.add('hidden');

            try {
                const response = await fetch('/api/password/reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        email: userEmail, 
                        otp: userOtp, 
                        password, 
                        password_confirmation 
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Nenosiri limebadilishwa kikamilifu! Sasa unaweza kuingia.');
                    window.location.href = '/login';
                } else {
                    errorSpan.textContent = data.message || 'Tatizo limetokea.';
                    errorSpan.classList.remove('hidden');
                }
            } catch (error) {
                errorSpan.textContent = 'Tatizo la mtandao. Jaribu tena.';
                errorSpan.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="relative z-10">Badilisha Nenosiri</span><div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>';
            }
        });
    </script>

</body>
</html>
