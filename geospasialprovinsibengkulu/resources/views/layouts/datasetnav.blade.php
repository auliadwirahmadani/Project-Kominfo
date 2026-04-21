<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Geoportal')) — Geoportal Provinsi Bengkulu</title>
    <meta name="description" content="@yield('meta_description', 'Platform Informasi Geospasial Resmi Provinsi Bengkulu')">

    {{-- Fonts (sama seperti geonav) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet" />

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ==========================================
           BASE STYLES — sama persis dengan geonav
        ========================================== */
        html, body {
            font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        body {
            background-color: #f9fafb;
        }

        /* ==========================================
           NAVBAR — normal-mode (sticky seperti catalog/about)
        ========================================== */
        #dataset-navbar {
            position: sticky;
            top: 0;
            width: 100%;
            height: 72px;
            z-index: 9999;
            background: #8d1919;
            border: 1.5px solid rgba(255,255,255,0.12);
            border-bottom: 2.5px solid rgba(251,191,36,0.55);
            box-shadow: 0 4px 24px rgba(153,27,27,0.3), 0 1px 0 rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            backdrop-filter: blur(12px);
        }
        @media (min-width: 640px) {
            #dataset-navbar { padding: 0 2rem; }
        }

        /* Logo */
        #dataset-logo-img {
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.35));
            transition: transform 0.3s ease, filter 0.3s ease;
        }
        #dataset-logo-img:hover {
            transform: scale(1.08) rotate(-2deg);
            filter: drop-shadow(0 4px 14px rgba(0,0,0,0.45));
        }

        /* Brand text */
        .dataset-brand-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1rem;
            color: #fff;
            letter-spacing: 0.01em;
            line-height: 1.2;
        }
        .dataset-brand-sub {
            font-weight: 500;
            font-size: 0.7rem;
            color: rgba(254,202,202,0.9);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        /* ✕ Tombol Kembali */
        .dataset-close-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background: rgba(255,255,255,0.92);
            color: #991b1b;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            transition: all 0.22s ease;
            box-shadow: 0 2px 12px rgba(0,0,0,0.18);
            border: 1.5px solid rgba(255,255,255,0.8);
            text-decoration: none;
            cursor: pointer;
        }
        .dataset-close-btn:hover {
            background: #fff;
            color: #7f1d1d;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.22);
        }
        .dataset-close-btn svg {
            width: 1rem;
            height: 1rem;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 3px; }
        ::-webkit-scrollbar-thumb { background: #dc2626; border-radius: 3px; }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased min-h-screen flex flex-col">

{{-- ============================================================
     NAVBAR — desain sama dengan geonav, hanya ada tombol ✕
============================================================ --}}
<nav id="dataset-navbar" role="navigation" aria-label="Dataset Navigation">

    {{-- ===== KIRI: Logo ===== --}}
    <div class="flex items-center gap-3 shrink-0">
        <a href="{{ route('geo') }}" class="flex items-center gap-3" aria-label="Beranda Geoportal">
            {{-- Logo --}}
            <div class="relative">
                <div class="absolute inset-0 rounded-full bg-white/15 blur-sm scale-110"></div>
                <img src="{{ asset('Logo Provinsi Bengkulu.png') }}"
                     alt="Logo Provinsi Bengkulu"
                     id="dataset-logo-img"
                     class="relative w-11 h-11 object-contain"
                     onerror="this.src='https://placehold.co/44x44/b91c1c/ffffff?text=G'">
            </div>
            <div class="hidden sm:flex flex-col">
                <span class="dataset-brand-title">Geoportal</span>
                <span class="dataset-brand-sub">Provinsi Bengkulu</span>
            </div>
        </a>
    </div>

    {{-- ===== KANAN: Hanya tombol ✕ Kembali ===== --}}
    <div class="flex items-center">
        <button onclick="if(window.history.length <= 1 || document.referrer === '') { window.close(); } else { window.history.back(); }"
           class="dataset-close-btn"
           title="Kembali ke halaman sebelumnya"
           aria-label="Tutup dan kembali">
            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="hidden sm:inline">Kembali</span>
        </button>
    </div>

</nav>

{{-- ============================================================
     MAIN CONTENT
============================================================ --}}
<main class="flex-grow" id="main-content">
    @yield('content')
</main>

{{-- ============================================================
     FOOTER — sama seperti geonav normal mode
============================================================ --}}
<footer class="bg-white border-t border-gray-100 py-6 mt-auto">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <div class="flex flex-col items-center space-y-2">
            <img src="{{ asset('Logo Provinsi Bengkulu.png') }}" class="h-8 mb-2 grayscale opacity-50" alt="Logo">
            <p class="text-gray-500 text-sm">
                &copy; {{ date('Y') }} <strong>Geoportal Provinsi Bengkulu</strong>. All rights reserved.
            </p>
            <p class="text-gray-400 text-[10px] uppercase tracking-widest">
                Pemerintah Provinsi Bengkulu
            </p>
        </div>
    </div>
</footer>

{{-- AlpineJS --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

{{-- Scripts dari child view --}}
@stack('scripts')

</body>
</html>