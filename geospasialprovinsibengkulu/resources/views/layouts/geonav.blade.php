<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Geoportal')) — Geoportal Provinsi Bengkulu</title>
    <meta name="description" content="@yield('meta_description', 'Platform Informasi Geospasial Resmi Provinsi Bengkulu')">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet" />

    {{-- Leaflet (only loaded when needed) --}}
    @if($navMode ?? 'map' === 'map')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endif

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ==========================================
           BASE STYLES
        ========================================== */
        html, body {
            font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        /* ==========================================
           MAP MODE — navbar mengambang di atas peta
        ========================================== */
        body.map-mode {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body.map-mode #map {
            position: fixed;
            inset: 0;
            z-index: 0;
        }
        body.map-mode main {
            height: 100%;
            padding: 0;
        }
        /* Navbar floating */
        body.map-mode #main-navbar {
            position: fixed;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            width: 95vw;
            max-width: 1440px;
            border-radius: 9999px;
            height: 72px;
            box-shadow: 0 12px 40px -6px rgba(153,27,27,0.5), 0 4px 16px rgba(0,0,0,0.2);
        }

        /* ==========================================
           NORMAL MODE — navbar sticky standar
        ========================================== */
        body.normal-mode #main-navbar {
            position: sticky;
            top: 0;
            width: 100%;
            height: 72px;
            box-shadow: 0 4px 24px rgba(153,27,27,0.3), 0 1px 0 rgba(255,255,255,0.08);
        }
        body.normal-mode main {
            padding-top: 0;
        }

        /* ==========================================
           SHARED NAVBAR STYLES
        ========================================== */
        #main-navbar {
            z-index: 9999;
            background: #8d1919;
            border: 1.5px solid rgba(255,255,255,0.12);
            border-bottom: 2.5px solid rgba(251,191,36,0.55);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            overflow: visible !important;
            backdrop-filter: blur(12px);
        }
        @media (min-width: 640px) {
            #main-navbar { padding: 0 2rem; }
        }

        /* Logo shine effect */
        #navbar-logo-wrap {
            position: relative;
        }
        #navbar-logo-img {
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.35));
            transition: transform 0.3s ease, filter 0.3s ease;
        }
        #navbar-logo-img:hover {
            transform: scale(1.08) rotate(-2deg);
            filter: drop-shadow(0 4px 14px rgba(0,0,0,0.45));
        }

        /* Nav menu item styles */
        .nav-menu-item {
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            letter-spacing: 0.02em;
            color: rgba(255,255,255,0.85);
            transition: all 0.22s cubic-bezier(0.4,0,0.2,1);
            text-decoration: none;
        }
        .nav-menu-item:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            transform: translateY(-1px);
        }
        .nav-menu-item.active {
            background: rgba(255,255,255,0.2);
            color: #fff;
            box-shadow: 0 0 0 1.5px rgba(255,255,255,0.3), 0 4px 12px rgba(0,0,0,0.2);
        }
        .nav-menu-item.active::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 3px;
            background: #fbbf24;
            border-radius: 9999px;
        }

        /* Brand text */
        .navbar-brand-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1rem;
            color: #fff;
            letter-spacing: 0.01em;
            line-height: 1.2;
        }
        .navbar-brand-sub {
            font-weight: 500;
            font-size: 0.7rem;
            color: rgba(254,202,202,0.9);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        /* Divider */
        .navbar-divider {
            width: 1.5px;
            height: 32px;
            background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.25), transparent);
            border-radius: 9999px;
        }

        /* Login button */
        .navbar-login-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.92);
            color: #991b1b;
            padding: 8px 18px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            transition: all 0.22s ease;
            box-shadow: 0 2px 12px rgba(0,0,0,0.18);
            border: 1.5px solid rgba(255,255,255,0.8);
        }
        .navbar-login-btn:hover {
            background: #fff;
            color: #7f1d1d;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.22);
        }

        /* Scrollbar custom */
        .custom-scroll { scrollbar-width: thin; scrollbar-color: #dc2626 #f3f4f6; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 3px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #dc2626; border-radius: 3px; }
    </style>

    @stack('styles')
</head>

{{-- Body mode ditentukan oleh $navMode. Default 'map' untuk backward-compat --}}
<body class="font-sans antialiased min-h-screen flex flex-col {{ ($navMode ?? 'map') === 'normal' ? 'normal-mode bg-gray-50' : 'map-mode' }}">

{{-- ============================================================
     NAVBAR UTAMA (Fleksibel — works for both map & normal mode)
============================================================ --}}
<nav id="main-navbar" role="navigation" aria-label="Main Navigation">

    {{-- ===== KIRI: Logo ===== --}}
    <div class="flex items-center gap-3 shrink-0" id="navbar-logo-wrap">
        <a href="{{ route('geo') }}" class="flex items-center gap-3" aria-label="Beranda Geoportal">
            {{-- Logo container dengan background circle --}}
            <div class="relative">
                <div class="absolute inset-0 rounded-full bg-white/15 blur-sm scale-110"></div>
                <img src="{{ asset('Logo Provinsi Bengkulu.png') }}"
                     alt="Logo Provinsi Bengkulu"
                     id="navbar-logo-img"
                     class="relative w-11 h-11 object-contain"
                     onerror="this.src='https://placehold.co/44x44/b91c1c/ffffff?text=G'">
            </div>
            <div class="hidden sm:flex flex-col">
                <span class="navbar-brand-title">Geoportal</span>
                <span class="navbar-brand-sub">Provinsi Bengkulu</span>
            </div>
        </a>
    </div>

    {{-- ===== TENGAH: Menu Desktop ===== --}}
    <ul class="hidden md:flex items-center gap-1 lg:gap-2" role="menubar">
        @php
            $currentRoute = request()->route()?->getName() ?? '';
        @endphp

        @foreach([
            ['label' => 'Peta',     'route' => 'geo',     'icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'],
            ['label' => 'Katalog',  'route' => 'catalog', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
            ['label' => 'Tentang',  'route' => 'about',   'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as $item)
        <li role="none">
            <a href="{{ route($item['route']) }}"
               role="menuitem"
               class="nav-menu-item {{ $currentRoute === $item['route'] ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                </svg>
                {{ $item['label'] }}
            </a>
        </li>
        @endforeach
    </ul>

    {{-- ===== KANAN: Search Layer (hanya map-mode) + User Menu ===== --}}
    <div class="flex items-center gap-2 sm:gap-3">

        {{-- Search + Filter (map mode only) --}}
        @if(($navMode ?? 'map') === 'map')
        <div class="hidden lg:flex items-center gap-3 pl-4">
            <div class="navbar-divider"></div>

            {{-- Layer Search Dropdown (Vanilla JS) --}}
            <div class="relative w-64 xl:w-72" id="navSearchWrapper">

                <button onclick="toggleNavSearch(event)" type="button" id="navLayerSearchBtn"
                        class="w-full flex items-center justify-between pl-3.5 pr-3 py-2 bg-red-900/40 border border-red-500/60 rounded-full text-white text-sm hover:bg-red-900/70 transition focus:outline-none focus:ring-2 focus:ring-white/30">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <svg class="w-4 h-4 text-red-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="truncate text-red-100 text-sm" id="navSearchLabel">Cari data layer...</span>
                    </div>
                    <svg class="w-4 h-4 text-red-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div id="navSearchPanel"
                     class="absolute left-0 w-full bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden"
                     style="display:none; top: calc(100% + 12px); z-index: 10000;">

                    <div class="p-2.5 border-b border-gray-100 bg-gray-50">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <input type="text" id="navSearchInput" oninput="navSearchFilter(this.value)"
                                   placeholder="Ketik nama data..."
                                   class="w-full pl-9 pr-3 py-2.5 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-gray-800 transition-all">
                        </div>
                    </div>

                    <ul id="navSearchList" class="max-h-60 overflow-y-auto custom-scroll bg-white"></ul>

                    <div id="navSearchClearBox" class="px-3 py-2 border-t border-gray-100 bg-gray-50" style="display:none;">
                        <button onclick="navSearchClear()" type="button"
                                class="w-full text-xs text-gray-500 hover:text-red-600 py-1 transition font-medium">
                            ✕ Hapus pilihan layer
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filter Kategori & Tahun (Vanilla JS — no Alpine dependency) --}}
            <div class="relative" id="navFilterWrapper" style="position:relative;">
                <button onclick="toggleNavFilter(event)" type="button" id="navFilterBtn"
                        class="flex items-center gap-2 px-4 py-2 bg-red-900/40 border border-red-500/60 rounded-full text-white text-sm hover:bg-red-900/70 transition focus:outline-none focus:ring-2 focus:ring-white/30">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter</span>
                </button>

                <div id="navFilterPanel"
                     class="absolute right-0 w-72 bg-white rounded-xl shadow-2xl py-3 border border-gray-100"
                     style="display:none; top: calc(100% + 12px); z-index: 10000;">

                    <div class="px-4 pb-2.5 border-b border-gray-100 flex justify-between items-center">
                        <h4 class="font-bold text-gray-800 text-sm">Filter Peta</h4>
                        <button onclick="resetFilterPeta()" class="text-xs text-red-600 hover:underline font-medium">Reset</button>
                    </div>

                    <div class="px-4 py-3 space-y-4 max-h-80 overflow-y-auto custom-scroll">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Kategori</label>
                            <select id="filterCategory" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none bg-gray-50">
                                <option value="">Semua Kategori</option>
                                @foreach(\App\Models\Category::all() as $category)
                                    <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tahun</label>
                            <select id="filterYear" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none bg-gray-50">
                                <option value="">Semua Tahun</option>
                                @for($y = date('Y'); $y >= 2019; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="px-4 pt-2.5 border-t border-gray-100">
                        <button id="btnTerapkan" onclick="terapkanFilterPeta(); closeNavFilter();"
                                class="w-full px-4 py-2.5 text-sm font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>

        </div>
        {{-- END map-mode search/filter --}}
        @endif

        {{-- Mobile search icon (map mode only) --}}
        @if(($navMode ?? 'map') === 'map')
        <div class="lg:hidden">
            <button id="mobileSearchToggle" type="button"
                    class="p-2 text-white hover:text-gray-200 transition rounded-lg hover:bg-red-900/40"
                    aria-label="Buka pencarian">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </div>
        @endif

        {{-- Mobile hamburger (normal mode only) --}}
        @if(($navMode ?? 'map') === 'normal')
        <div class="md:hidden" x-data="{ mobileOpen: false }">
            <button @click="mobileOpen = !mobileOpen" type="button"
                    class="p-2 text-white hover:text-red-200 transition rounded-lg" aria-label="Toggle menu">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Mobile dropdown --}}
            <div x-show="mobileOpen" @click.outside="mobileOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-2xl py-2 border border-gray-100 z-[9999]"
                 style="display:none;">
                <a href="{{ route('geo') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-red-50 hover:text-red-700 transition">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    Peta Geospasial
                </a>
                <a href="{{ route('catalog') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-red-50 hover:text-red-700 transition">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Katalog Data
                </a>
                <a href="{{ route('about') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-red-50 hover:text-red-700 transition">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tentang Kami
                </a>
                <div class="h-px bg-gray-100 my-1"></div>
                <a href="{{ route('login') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Masuk / Login
                </a>
            </div>
        </div>
        @endif

        {{-- Tombol Login — hanya tampil jika belum login (pengunjung) --}}
        @guest
            <a href="{{ route('login') }}" class="navbar-login-btn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Masuk
            </a>
        @endguest

    </div>
</nav>

{{-- Mobile Search Panel (map mode only) --}}
@if(($navMode ?? 'map') === 'map')
<div id="mobileSearchPanel"
     class="lg:hidden fixed top-20 left-4 right-4 p-4 bg-white rounded-2xl shadow-2xl z-[1040] border border-gray-100"
     style="display: none;">

    <div class="relative mb-3" x-data="layerSearch" x-init="initData()" @reset-search.window="selected = null">
        <button @click="toggle()" type="button"
                class="w-full flex items-center justify-between px-4 py-3 bg-white text-gray-700 border border-gray-200 rounded-xl shadow-sm focus:outline-none">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="text-sm font-medium truncate" x-text="selected ? selected.name : 'Cari data layer...'"></span>
            </div>
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" @click.outside="open = false"
             class="absolute left-0 top-full mt-2 w-full bg-white rounded-xl shadow-xl border border-gray-200 z-[1060] overflow-hidden"
             style="display: none;">
            <div class="p-2.5 border-b border-gray-100 bg-gray-50">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" x-model="query" x-ref="searchInput" @input="search()"
                           placeholder="Ketik nama data..."
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded focus:outline-none focus:border-red-500 text-gray-800">
                </div>
            </div>
            <ul class="max-h-48 overflow-y-auto custom-scroll bg-white">
                <template x-for="layer in filteredLayers" :key="layer.id">
                    <li @click="selectLayer(layer); document.getElementById('mobileSearchPanel').style.display='none';"
                        class="px-4 py-3 text-sm text-gray-700 cursor-pointer hover:bg-red-50 border-b border-gray-50 last:border-0"
                        x-text="layer.name"></li>
                </template>
                <li x-show="filteredLayers.length === 0" class="px-4 py-4 text-sm text-gray-400 text-center">
                    Data tidak ditemukan
                </li>
            </ul>
        </div>
    </div>

    <div class="flex gap-2">
        <select id="mobileFilterCategory" class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-red-500 focus:outline-none">
            <option value="">Semua Kategori</option>
            @foreach(\App\Models\Category::all() as $category)
                <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
            @endforeach
        </select>
        <button onclick="terapkanFilterMobile()" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition whitespace-nowrap">
            Filter
        </button>
    </div>
</div>
@endif

{{-- ============================================================
     MAIN CONTENT
============================================================ --}}
<main class="flex-grow" id="main-content">
    @yield('content')
</main>

{{-- ============================================================
     FOOTER (normal mode only — not shown on full-map pages)
============================================================ --}}
@if(($navMode ?? 'map') === 'normal')
<footer class="bg-white border-t border-gray-100 py-6 mt-auto">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <div class="flex flex-col items-center space-y-2">
            <!-- Tambahan Logo Kecil di footer biar manis -->
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
@endif

{{-- ============================================================
     SCRIPTS
============================================================ --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

{{-- Map-mode specific JS --}}
@if(($navMode ?? 'map') === 'map')
<script>
// ==========================================
// NAV FILTER PANEL TOGGLE (Vanilla JS)
// ==========================================
window.toggleNavFilter = function(e) {
    e && e.stopPropagation();
    const panel = document.getElementById('navFilterPanel');
    if (!panel) return;
    const isVisible = panel.style.display === 'block';
    panel.style.display = isVisible ? 'none' : 'block';
};
window.closeNavFilter = function() {
    const panel = document.getElementById('navFilterPanel');
    if (panel) panel.style.display = 'none';
};
// Close filter panel when clicking outside
document.addEventListener('click', function(e) {
    const panel  = document.getElementById('navFilterPanel');
    const btn    = document.getElementById('navFilterBtn');
    const wrapper = document.getElementById('navFilterWrapper');
    if (panel && panel.style.display === 'block' && wrapper && !wrapper.contains(e.target)) {
        panel.style.display = 'none';
    }
});

// ==========================================
// FILTER PETA FUNCTIONS
// ==========================================
window.terapkanFilterPeta = function() {
    const btn = document.getElementById('btnTerapkan');
    const categoryId = document.getElementById('filterCategory')?.value ?? '';
    const year       = document.getElementById('filterYear')?.value ?? '';

    if (btn) { btn.textContent = 'Memuat...'; btn.disabled = true; }

    fetch(`/geospatial/filter?category_id=${categoryId}&year=${year}`)
        .then(res => {
            if (!res.ok) throw new Error("Gagal mengambil data");
            return res.json();
        })
        .then(data => {
            const dataPeta = Array.isArray(data) ? data : (data.layers || []);
            if (dataPeta.length === 0) alert("Tidak ada data peta untuk filter ini.");
            if (typeof window.loadMapData === 'function') window.loadMapData(dataPeta);
            else alert("Sistem peta belum siap.");
        })
        .catch(err => alert("Error: " + err.message))
        .finally(() => {
            if (btn) { btn.textContent = 'Terapkan Filter'; btn.disabled = false; }
        });
};

window.resetFilterPeta = function() {
    const cat  = document.getElementById('filterCategory');
    const year = document.getElementById('filterYear');
    if (cat)  cat.value  = '';
    if (year) year.value = '';
    window.dispatchEvent(new CustomEvent('reset-search'));
    window.terapkanFilterPeta();
};

window.terapkanFilterMobile = function() {
    const mobCat = document.getElementById('mobileFilterCategory');
    const cat    = document.getElementById('filterCategory');
    if (mobCat && cat) cat.value = mobCat.value;
    document.getElementById('mobileSearchPanel').style.display = 'none';
    window.terapkanFilterPeta();
};

// ==========================================
// LAYER SEARCH (Vanilla JS — no Alpine)
// ==========================================
(function() {
    var _layers = @json(\Illuminate\Support\Facades\DB::table('geospatial_layer')
                    ->select('geospatial_id as id', 'layer_name as name')
                    ->where('is_published', true)
                    ->get());
    var _selected = null;

    function renderList(list) {
        var ul = document.getElementById('navSearchList');
        if (!ul) return;
        if (list.length === 0) {
            ul.innerHTML = '<li class="px-4 py-6 flex flex-col items-center justify-center text-gray-400"><svg class="w-8 h-8 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm">Data tidak ditemukan</span></li>';
            return;
        }
        ul.innerHTML = list.map(function(l) {
            return '<li onclick="navSearchSelect(' + l.id + ')" class="px-4 py-3 text-sm text-gray-700 cursor-pointer hover:bg-red-50 transition border-b border-gray-50 last:border-0 flex items-center gap-2"><svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg><span class="truncate">' + l.name + '</span></li>';
        }).join('');
    }

    window.toggleNavSearch = function(e) {
        e && e.stopPropagation();
        var panel = document.getElementById('navSearchPanel');
        if (!panel) return;
        var visible = panel.style.display === 'block';
        panel.style.display = visible ? 'none' : 'block';
        if (!visible) {
            renderList(_layers);
            var inp = document.getElementById('navSearchInput');
            if (inp) { inp.value = ''; setTimeout(function(){ inp.focus(); }, 80); }
        }
    };

    window.navSearchFilter = function(q) {
        q = (q || '').toLowerCase().trim();
        renderList(q === '' ? _layers : _layers.filter(function(l){ return l.name.toLowerCase().includes(q); }));
    };

    window.navSearchSelect = function(id) {
        var layer = _layers.find(function(l){ return l.id == id; });
        if (!layer) return;
        _selected = layer;
        var label = document.getElementById('navSearchLabel');
        if (label) label.textContent = layer.name;
        var panel = document.getElementById('navSearchPanel');
        if (panel) panel.style.display = 'none';
        var clearBox = document.getElementById('navSearchClearBox');
        if (clearBox) clearBox.style.display = 'block';
        
        // Pass to the new PostGIS arch dynamic fetching API
        window.activeSearchLayerId = id;
        if (typeof window.loadMapData === 'function') {
            window.loadMapData();
        }
    };

    window.navSearchClear = function() {
        _selected = null;
        var label = document.getElementById('navSearchLabel');
        if (label) label.textContent = 'Cari data layer...';
        var clearBox = document.getElementById('navSearchClearBox');
        if (clearBox) clearBox.style.display = 'none';
        
        // Clear filter
        window.activeSearchLayerId = null;
        window.terapkanFilterPeta();
    };

    // Close on outside click
    document.addEventListener('click', function(e) {
        var panel   = document.getElementById('navSearchPanel');
        var wrapper = document.getElementById('navSearchWrapper');
        if (panel && panel.style.display === 'block' && wrapper && !wrapper.contains(e.target)) {
            panel.style.display = 'none';
        }
    });
})();

// Mobile search toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn  = document.getElementById('mobileSearchToggle');
    const searchPanel = document.getElementById('mobileSearchPanel');
    if (toggleBtn && searchPanel) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            searchPanel.style.display = searchPanel.style.display === 'block' ? 'none' : 'block';
        });
    }
});
</script>
@endif

@stack('scripts')
</body>
</html>