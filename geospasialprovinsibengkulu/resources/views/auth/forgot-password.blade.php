<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - Geoportal Provinsi Bengkulu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up { animation: fadeInUp 0.5s ease forwards; }
    </style>
</head>

<body class="min-h-screen bg-cover bg-center relative"
      style="background-image: url('/bg 2.png');">

<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-5xl bg-white shadow-xl rounded-2xl flex overflow-hidden">

        {{-- KIRI (FORM LUPA PASSWORD) --}}
        <div class="w-full lg:w-1/2 p-6 sm:p-10 fade-in-up">

            <div class="text-center">
                <img src="/Logo Provinsi Bengkulu.png" class="w-20 mx-auto" alt="Logo Bengkulu" />
            </div>

            <div class="mt-6 text-center">
                <h1 class="text-2xl font-extrabold text-red-700">Lupa Kata Sandi</h1>
                <p class="text-gray-500 mt-1 text-sm">Masukkan email terdaftar Anda untuk menerima link reset kata sandi.</p>
            </div>

            {{-- STATUS / ERROR --}}
            @if(session('status'))
                <div class="mt-4 flex items-start gap-2 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mt-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- FORM --}}
            <form method="POST" action="{{ route('password.forgot.send') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                    <input
                        name="email"
                        type="email"
                        placeholder="Masukkan email Anda"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        class="w-full px-4 py-2.5 rounded-lg bg-gray-100 border border-gray-200 text-sm focus:outline-none focus:border-red-400 focus:bg-white transition"
                    />
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Kirim Link Reset
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-red-600 hover:text-red-800 font-medium transition">
                        ← Kembali ke halaman Login
                    </a>
                </div>
            </form>
        </div>

        {{-- KANAN (ILUSTRASI) --}}
        <div class="hidden lg:flex lg:w-1/2 bg-red-600 items-center justify-center">
            <div class="p-8 text-center text-white">
                <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold mb-3">Reset Kata Sandi</h2>
                <p class="opacity-90 text-base leading-relaxed">
                    Kami akan mengirimkan link reset kata sandi ke email Anda. Link berlaku selama <strong>60 menit</strong>.
                </p>
                <div class="mt-6 bg-white/10 rounded-xl p-4 text-left text-sm space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
                        <span>Masukkan email terdaftar Anda</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
                        <span>Buka email & klik link reset</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
                        <span>Buat kata sandi baru Anda</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
