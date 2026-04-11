<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi - Geoportal Provinsi Bengkulu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up { animation: fadeInUp 0.5s ease forwards; }

        .password-strength-bar { height: 4px; border-radius: 2px; transition: all 0.3s ease; }
    </style>
</head>

<body class="min-h-screen bg-cover bg-center relative"
      style="background-image: url('/bg 2.png');">

<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-5xl bg-white shadow-xl rounded-2xl flex overflow-hidden">

        {{-- KIRI (FORM RESET PASSWORD) --}}
        <div class="w-full lg:w-1/2 p-6 sm:p-10 fade-in-up">

            <div class="text-center">
                <img src="/Logo Provinsi Bengkulu.png" class="w-20 mx-auto" alt="Logo Bengkulu" />
            </div>

            <div class="mt-6 text-center">
                <h1 class="text-2xl font-extrabold text-red-700">Buat Kata Sandi Baru</h1>
                <p class="text-gray-500 mt-1 text-sm">Masukkan kata sandi baru untuk akun <strong>{{ $email }}</strong></p>
            </div>

            {{-- ERROR --}}
            @if(session('error'))
                <div class="mt-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- FORM --}}
            <form method="POST" action="{{ route('password.reset.process') }}" class="mt-6 space-y-4" id="resetForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- Kata Sandi Baru --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-2.5 pr-10 rounded-lg bg-gray-100 border border-gray-200 text-sm focus:outline-none focus:border-red-400 focus:bg-white transition"
                        />
                        <button type="button" onclick="togglePassword('password', 'eyeIcon1')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eyeIcon1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Strength Bar --}}
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-1">
                            <div id="strengthBar" class="password-strength-bar bg-gray-300 w-0"></div>
                        </div>
                        <p id="strengthText" class="text-xs text-gray-400 mt-1"></p>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Kata Sandi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="Ulangi kata sandi baru"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-2.5 pr-10 rounded-lg bg-gray-100 border border-gray-200 text-sm focus:outline-none focus:border-red-400 focus:bg-white transition"
                        />
                        <button type="button" onclick="togglePassword('password_confirmation', 'eyeIcon2')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eyeIcon2" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p id="matchText" class="text-xs mt-1"></p>
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Simpan Kata Sandi Baru
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-red-600 hover:text-red-800 font-medium transition">
                        ← Kembali ke halaman Login
                    </a>
                </div>
            </form>
        </div>

        {{-- KANAN --}}
        <div class="hidden lg:flex lg:w-1/2 bg-red-600 items-center justify-center">
            <div class="p-8 text-center text-white">
                <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold mb-3">Keamanan Akun</h2>
                <p class="opacity-90 text-base leading-relaxed">
                    Buat kata sandi yang kuat untuk melindungi akun Geoportal Anda.
                </p>
                <div class="mt-6 bg-white/10 rounded-xl p-4 text-left text-sm space-y-2">
                    <p class="font-semibold mb-2">Tips Kata Sandi Kuat:</p>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Minimal 8 karakter</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Gabungkan huruf besar & kecil</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Gunakan angka & simbol</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    if (field.type === 'password') {
        field.type = 'text';
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
    } else {
        field.type = 'password';
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
    }
}

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    const bar = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let strength = 0;

    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const levels = [
        { width: '0%', color: 'bg-gray-300', label: '' },
        { width: '25%', color: 'bg-red-400', label: 'Sangat Lemah' },
        { width: '50%', color: 'bg-orange-400', label: 'Lemah' },
        { width: '75%', color: 'bg-yellow-400', label: 'Cukup Kuat' },
        { width: '100%', color: 'bg-green-500', label: 'Sangat Kuat' },
    ];

    const level = val.length === 0 ? 0 : strength;
    bar.className = `password-strength-bar ${levels[level].color}`;
    bar.style.width = levels[level].width;
    text.textContent = levels[level].label;
    text.className = `text-xs mt-1 ${level >= 3 ? 'text-green-600' : 'text-orange-500'}`;
});

// Password match checker
document.getElementById('password_confirmation').addEventListener('input', function() {
    const pass = document.getElementById('password').value;
    const matchText = document.getElementById('matchText');
    if (this.value === '') {
        matchText.textContent = '';
        return;
    }
    if (this.value === pass) {
        matchText.textContent = '✓ Kata sandi cocok';
        matchText.className = 'text-xs mt-1 text-green-600';
    } else {
        matchText.textContent = '✗ Kata sandi tidak cocok';
        matchText.className = 'text-xs mt-1 text-red-500';
    }
});
</script>

</body>
</html>
