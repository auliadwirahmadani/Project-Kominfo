@extends('layouts.app')

@section('title', 'Katalog Data Geospasial')

@section('content')

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    /* =========================
       🎨 DESIGN SYSTEM
    ========================= */
    :root {
        --primary: #ef4444;
        --primary-dark: #dc2626;
        --primary-light: #fecaca;
        --secondary: #1f2937;
        --accent: #fbbf24;
        --glass: rgba(255, 255, 255, 0.9);
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        --shadow-lg: 0 8px 32px rgba(0,0,0,0.15);
        --radius: 16px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .work-sans { font-family: 'Work Sans', sans-serif; }
    div[id^="mini-map-"] .leaflet-control-container { display: none !important; }

    /* =========================
       🎠 CAROUSEL STYLES
    ========================= */
    .carousel {
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        margin-bottom: 2rem;
    }
    .carousel-track {
        display: flex;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
    }
    .carousel-slide {
        min-width: 100%;
        height: 50vh;
        position: relative;
        display: flex;
        align-items: center;
        background-size: cover;
        background-position: center;
    }

    .carousel-content {
        position: relative;
        z-index: 2;
        color: white;
        padding: 2rem 6rem; /* 6rem kiri-kanan agar luas dan bebas dari panah navigasi */
        max-width: 750px;
        animation: fadeInUp 0.6s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .carousel-title {
        font-size: clamp(1.5rem, 4vw, 2.25rem);
        font-weight: 700;
        margin-bottom: 1rem;
        line-height: 1.2;
    }
    .carousel-desc {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }
    .carousel-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        color: var(--primary);
        font-weight: 600;
        border-radius: 50px;
        text-decoration: none;
        transition: var(--transition);
        box-shadow: var(--shadow-md);
    }
    .carousel-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: var(--primary-light);
    }
    .carousel-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--glass);
        border: 2px solid white;
        color: var(--primary);
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: var(--transition);
        backdrop-filter: blur(8px);
    }
    .carousel-nav:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-50%) scale(1.1);
    }
    .carousel-nav.prev { left: 1.5rem; }
    .carousel-nav.next { right: 1.5rem; }
    
    /* Sembunyikan panah di layar kecil agar tidak sesak, fallback menggunakan indikator bullet */
    @media (max-width: 768px) {
        .carousel-nav { display: none; }
        .carousel-content { padding: 2rem 1.5rem; }
    }
    .carousel-indicators {
        position: absolute;
        bottom: 1.5rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 0.5rem;
        z-index: 10;
    }
    .carousel-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        border: 2px solid white;
        cursor: pointer;
        transition: var(--transition);
    }
    .carousel-indicator.active {
        background: white;
        transform: scale(1.2);
    }

    /* =========================
       🗂️ CARD GRID
    ========================= */
    .catalog-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1.5rem;
    }
    @media (min-width: 640px) {
        .catalog-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (min-width: 1024px) {
        .catalog-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (min-width: 1280px) {
        .catalog-grid { grid-template-columns: repeat(4, 1fr); }
    }

    /* === Base Card === */
    .catalog-card {
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        border: 2px solid transparent;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        position: relative;
    }
    /* Stripe warna di atas setiap kartu */
    .catalog-card::before {
        content: '';
        display: block;
        height: 5px;
        width: 100%;
        flex-shrink: 0;
    }

    /* === Variasi Warna Merah (1, 4, 7, ...) === */
    .catalog-card:nth-child(3n+1) {
        background: linear-gradient(160deg, #fff 55%, #fff5f5 100%);
        border-color: #fca5a5;
    }
    .catalog-card:nth-child(3n+1)::before {
        background: linear-gradient(90deg, #ef4444 0%, #f97316 100%);
    }
    .catalog-card:nth-child(3n+1):hover {
        border-color: #ef4444;
        box-shadow: 0 12px 30px -6px rgba(239, 68, 68, 0.30);
    }
    .catalog-card:nth-child(3n+1) .card-badge {
        color: #dc2626;
        border: 1.5px solid #fca5a5;
        background: #fff5f5;
    }
    .catalog-card:nth-child(3n+1) .card-image {
        background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
    }

    /* === Variasi Warna Biru (2, 5, 8, ...) === */
    .catalog-card:nth-child(3n+2) {
        background: linear-gradient(160deg, #fff 55%, #eff6ff 100%);
        border-color: #93c5fd;
    }
    .catalog-card:nth-child(3n+2)::before {
        background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
    }
    .catalog-card:nth-child(3n+2):hover {
        border-color: #3b82f6;
        box-shadow: 0 12px 30px -6px rgba(59, 130, 246, 0.28);
    }
    .catalog-card:nth-child(3n+2) .card-badge {
        color: #2563eb;
        border: 1.5px solid #93c5fd;
        background: #eff6ff;
    }
    .catalog-card:nth-child(3n+2) .card-image {
        background: linear-gradient(135deg, #eff6ff 0%, #fff 100%);
    }

    /* === Variasi Warna Hijau (3, 6, 9, ...) === */
    .catalog-card:nth-child(3n+3) {
        background: linear-gradient(160deg, #fff 55%, #ecfdf5 100%);
        border-color: #6ee7b7;
    }
    .catalog-card:nth-child(3n+3)::before {
        background: linear-gradient(90deg, #10b981 0%, #0ea5e9 100%);
    }
    .catalog-card:nth-child(3n+3):hover {
        border-color: #10b981;
        box-shadow: 0 12px 30px -6px rgba(16, 185, 129, 0.28);
    }
    .catalog-card:nth-child(3n+3) .card-badge {
        color: #059669;
        border: 1.5px solid #6ee7b7;
        background: #ecfdf5;
    }
    .catalog-card:nth-child(3n+3) .card-image {
        background: linear-gradient(135deg, #ecfdf5 0%, #fff 100%);
    }

    .catalog-card:hover { transform: translateY(-6px); }

    .card-image {
        position: relative;
        height: 200px;
    }
    .card-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        padding: 0.375rem 0.875rem;
        background: white;
        font-weight: 700;
        font-size: 0.75rem;
        border-radius: 50px;
        box-shadow: var(--shadow-sm);
        z-index: 10;
    }
    .card-body {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .card-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--secondary);
        margin: 0;
    }
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        grid-column: 1 / -1;
    }

    /* =========================
       📊 STATISTICS
    ========================= */
    .stats-section {
        padding: 4rem 1rem;
        background: white;
    }
    .stats-header { text-align: center; margin-bottom: 3rem; }
    .stats-title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 700;
        color: var(--secondary);
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        max-width: 1000px;
        margin: 0 auto;
    }
    @media (min-width: 768px) {
        .stats-grid { grid-template-columns: repeat(4, 1fr); }
    }
    .stat-card {
        text-align: center;
        padding: 1.5rem;
    }
    .stat-value {
        font-size: clamp(2rem, 5vw, 2.75rem);
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
        margin-bottom: 0.5rem;
        display: block;
    }
    .stat-label {
        color: #4b5563;
        font-weight: 500;
        font-size: 0.95rem;
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, var(--primary-light), #fff);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.5rem;
    }
    .stat-icon svg {
        width: 28px;
        height: 28px;
    }

    /* =========================
       ✨ DECORATIVE BLOBS
    ========================= */
    @keyframes float1 {
        0% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(40px, -60px) scale(1.1); }
        66% { transform: translate(-30px, 30px) scale(0.9); }
        100% { transform: translate(0, 0) scale(1); }
    }
    @keyframes float2 {
        0% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(-50px, 40px) scale(1.15); }
        66% { transform: translate(30px, -30px) scale(0.85); }
        100% { transform: translate(0, 0) scale(1); }
    }
    .blob-1 { animation: float1 18s infinite ease-in-out; }
    .blob-2 { animation: float2 22s infinite ease-in-out; }
    .blob-3 { animation: float1 25s infinite ease-in-out reverse; }

    /* =========================
       🎬 SCROLL ANIMATIONS
    ========================= */
    .animate-on-scroll {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.55s ease, transform 0.55s ease;
    }
    .animate-on-scroll.visible {
        opacity: 1;
        transform: translateY(0);
    }
    @media (prefers-reduced-motion: reduce) {
        .animate-on-scroll { opacity: 1 !important; transform: none !important; }
    }
</style>

<!-- =========================
   🎠 HERO CAROUSEL
========================= -->
<div class="carousel w-full">
    <div class="carousel-track" id="carouselTrack">
        <div class="carousel-slide" style="background-image: url('{{ asset('helmi mian.png') }}');">
            <div class="carousel-content">
                <h1 class="carousel-title">Geoportal Provinsi Bengkulu</h1>
                <p class="carousel-desc">Akses data geospasial terintegrasi untuk perencanaan pembangunan.</p>
                <a href="#katalog" class="carousel-btn">Jelajahi Data <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            </div>
        </div>
        <div class="carousel-slide" style="background-image: url('{{ asset('logo 2.png') }}');">
            <div class="carousel-content">
                <h1 class="carousel-title">Data Geospasial Terintegrasi</h1>
                <p class="carousel-desc">Ribuan layer data dari berbagai instansi.</p>
                <a href="#katalog" class="carousel-btn">Lihat Katalog <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            </div>
        </div>
    </div>
    <button class="carousel-nav prev" id="prevBtn"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
    <button class="carousel-nav next" id="nextBtn"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
    <div class="carousel-indicators" id="indicators">
        <button class="carousel-indicator active" data-slide="0"></button>
        <button class="carousel-indicator" data-slide="1"></button>
    </div>
</div>

<!-- =========================
   🔍 CATALOG SECTION
========================= -->
<section id="katalog" class="relative py-12 overflow-hidden mb-16">
    <!-- Hiasan Rona Merah Animatif -->
    <div class="absolute top-10 left-[-5%] w-[40vw] h-[40vw] max-w-[500px] bg-red-400/20 rounded-full mix-blend-multiply filter blur-[100px] blob-1 pointer-events-none z-0"></div>
    <div class="absolute bottom-10 right-[-5%] w-[35vw] h-[35vw] max-w-[400px] bg-rose-500/20 rounded-full mix-blend-multiply filter blur-[120px] blob-2 pointer-events-none z-0"></div>
    <div class="absolute top-1/3 left-1/2 w-[25vw] h-[25vw] max-w-[300px] bg-red-600/10 rounded-full mix-blend-multiply filter blur-[90px] blob-3 pointer-events-none z-0 hidden md:block"></div>

    <div class="container mx-auto px-4 relative z-10">

    {{-- ===== FILTER BAR ===== --}}
    <form action="{{ route('catalog') }}" method="GET" id="filterForm">
    <div class="flex flex-wrap items-center gap-3 mb-6 p-3 bg-white rounded-2xl relative" style="overflow: visible; z-index: 9999; border: 2px solid #fca5a5; box-shadow: 0 4px 16px rgba(239,68,68,0.10);">


        {{-- Search Input (Vanilla JS) --}}
        <div class="flex-1 min-w-[200px] relative" id="catalogSearchWrapper">
            <button onclick="toggleCatalogSearch(event)" type="button"
                    class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-full text-gray-700 text-sm hover:border-red-400 hover:bg-red-50/30 transition focus:outline-none">
                <div class="flex items-center gap-2 overflow-hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="truncate" id="catalogSearchLabel"
                          style="{{ request('search') ? 'color:#1f2937;font-weight:500' : 'color:#9ca3af' }}">{{ request('search') ?: 'Cari data layer...' }}</span>
                </div>
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            {{-- Hidden input untuk submit form --}}
            <input type="hidden" name="search" id="catalogSearchHidden" value="{{ request('search') }}">
            
            {{-- Dropdown Panel --}}
            <div id="catalogSearchPanel"
                 class="absolute left-0 top-full mt-2 w-full bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden"
                 style="display:none; z-index:1000; min-width:280px;">
                <div class="p-2.5 border-b border-gray-100 bg-gray-50">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="catalogSearchInput" oninput="catalogSearchFilter(this.value)"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();catalogSearchSubmitQuery(this.value);}"
                               placeholder="Ketik nama data..."
                               class="w-full pl-8 pr-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-red-400 text-gray-800">
                    </div>
                </div>
                <ul id="catalogSearchList" class="max-h-52 overflow-y-auto"></ul>
                <div id="catalogSearchClearBox" class="p-2 border-t border-gray-100 bg-gray-50" style="{{ request('search') ? '' : 'display:none;' }}">
                    <button type="button" onclick="catalogSearchClear()" class="w-full flex items-center justify-center gap-1 text-xs text-gray-500 hover:text-red-600 py-1.5 transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Hapus pilihan</button>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="hidden sm:block w-px h-8 bg-gray-200"></div>

        {{-- Filter Panel Button (Vanilla JS) --}}
        <div class="relative" id="catalogFilterWrapper">
            <button type="button" onclick="toggleCatalogFilter(event)"
                    id="catalogFilterBtn"
                    class="flex items-center gap-2 px-4 py-2.5 border rounded-full text-sm font-medium transition
                           {{ request()->hasAny(['category', 'year', 'type']) ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-200 hover:border-red-400 hover:text-red-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
                @if(request()->hasAny(['category', 'year', 'type']))
                    <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                @endif
            </button>

            {{-- Filter Dropdown Panel --}}
            <div id="catalogFilterPanel"
                 class="absolute left-0 sm:left-auto sm:right-0 top-full mt-2 w-72 bg-white rounded-xl shadow-2xl py-3 border border-gray-100"
                 style="display:none; z-index:300;">

                <div class="px-4 pb-2 border-b border-gray-100 flex items-center justify-between">
                    <h4 class="font-semibold text-gray-800 text-sm">Filter Data</h4>
                    @if(request()->hasAny(['category', 'year', 'type']))
                        <a href="{{ route('catalog') }}" class="text-xs text-red-600 hover:underline">Reset semua</a>
                    @endif
                </div>

                <div class="px-4 py-3 space-y-4">
                    {{-- Kategori --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Kategori</label>
                        <select name="category" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:outline-none bg-gray-50">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->category_id }}" {{ request('category') == $cat->category_id ? 'selected' : '' }}>
                                    {{ $cat->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tahun --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tahun</label>
                        <select name="year" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:outline-none bg-gray-50">
                            <option value="">Semua Tahun</option>
                            @for($y = date('Y'); $y >= 2019; $y--)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="px-4 pt-2 border-t border-gray-100 flex gap-2">
                    <a href="{{ route('catalog') }}" class="flex-1 text-center px-3 py-2 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Reset
                    </a>
                    <button type="submit" onclick="document.getElementById('catalogFilterPanel').style.display='none';"
                            class="flex-1 px-3 py-2 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                        Terapkan
                    </button>
                </div>
            </div>
        </div>

        {{-- Tombol Cari (submit form) --}}
        <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-full transition shadow-sm">
            Cari
        </button>

        {{-- Badge filter aktif --}}
        @if(request()->filled('search') || request()->hasAny(['category', 'year', 'type']))
        <a href="{{ route('catalog') }}" class="flex items-center gap-1.5 px-3 py-2 text-xs text-red-600 font-semibold bg-red-50 border border-red-200 rounded-full hover:bg-red-100 transition">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Hapus Filter
        </a>
        @endif

    </div>
    </form>

    <!-- ✅ Cards Grid -->
    <div class="catalog-grid relative z-0" id="catalogGrid">
        @forelse($datasets as $data)
        <article class="catalog-card animate-on-scroll">
            
            {{-- Map Preview --}}
            <div class="card-image">
                <div class="absolute top-3 left-3 z-10">
                    <span class="card-badge">
                        {{ strtoupper(substr($data->category->category_name ?? 'Peta', 0, 15)) }}
                    </span>
                </div>
                
                {{-- Mini Map Container --}}
                <div id="mini-map-{{ $data->geospatial_id }}" class="w-full h-full"></div>
                
                {{-- Loader --}}
                <div id="loader-{{ $data->geospatial_id }}" class="absolute inset-0 flex items-center justify-center bg-gray-50 z-20">
                    <svg class="animate-spin h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            {{-- Card Content --}}
            <div class="card-body">
                <h3 class="card-title line-clamp-2" title="{{ $data->metadata->title ?? $data->layer_name }}">
                    {{ $data->metadata->title ?? $data->layer_name }}
                </h3>
                <p class="text-xs text-gray-400 font-mono mt-1">ID: {{ $data->geospatial_id }}</p>

                {{-- ✅ Badge Kontributor & Tahun --}}
                <div class="flex flex-wrap items-center gap-2 my-2">
                    {{-- Badge Instansi / Kontributor --}}
                    <span class="inline-flex items-center gap-1.5 pl-1.5 pr-3.5 py-1 rounded-full text-sm font-semibold"
                          style="background:#fde8e8; color:#c0392b; border: 1px solid #fca5a5;">
                        @if($data->user?->profile?->photo)
                            <img src="{{ asset('storage/' . $data->user->profile->photo) }}" class="w-5 h-5 rounded-full object-cover shrink-0 bg-white border border-red-200" alt="Logo Instansi" onerror="this.outerHTML='<svg class=\'w-4 h-4 shrink-0 ml-1.5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'/></svg>'">
                        @else
                            <svg class="w-4 h-4 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        @endif
                        <span class="truncate max-w-[150px]" title="{{ $data->metadata->organization ?? ($data->user->profile->instansi ?? ($data->user->name ?? 'Pemprov Bengkulu')) }}">
                            {{ $data->metadata->organization ?? ($data->user->profile->instansi ?? ($data->user->name ?? 'Pemprov Bengkulu')) }}
                        </span>
                    </span>

                    {{-- Badge Tahun --}}
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold"
                          style="background:#fef3c7; color:#92400e; border: 1px solid #fcd34d;">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $data->metadata->year ?? $data->created_at->format('Y') }}
                    </span>
                </div>

                {{-- ✅ Route Detail Dataset --}}
                <a href="{{ route('dataset.show', ['id' => $data->geospatial_id]) }}"
                   class="block w-full py-2.5 px-4 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-lg text-center mb-2 transition-colors">
                    Lihat Detail Dataset
                </a>
                
                {{-- ✅ Route Full Map --}}
                <a href="{{ route('geo', ['layer_id' => $data->geospatial_id]) }}" 
                   class="block w-full py-2.5 px-4 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-lg text-center transition-colors">
                    Buka Peta Full Screen
                </a>
            </div>
        </article>
        @empty
        <div class="empty-state">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum ada data geospasial</h3>
            <p class="text-gray-500">Data yang dipublikasikan akan otomatis muncul di sini.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination dari Database --}}
    @if(isset($datasets) && method_exists($datasets, 'links'))
    <div class="mt-8">
        {{ $datasets->links() }}
    </div>
    @endif
    
    </div> <!-- End Container wrapper -->
</section>

<!-- =========================
   🏛️ INSTANSI / PRODUSEN DATA
========================= -->
<section class="py-16 bg-white relative">
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Produsen Data Geospasial</h2>
            <p class="text-gray-500 mt-2">Instansi pemerintah yang berkontribusi pada Geoportal Bengkulu</p>
        </div>

        @if(isset($producens) && $producens->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
            @foreach($producens as $p)
                @php
                    $profile = $p->profile;
                    $photoPath = $profile?->photo;
                    $hasPhoto = $photoPath && file_exists(public_path('storage/' . $photoPath));
                    $initial = strtoupper(substr($p->name ?? 'P', 0, 1));
                @endphp
                
                <a href="{{ route('instansi.show', $p->user_id) }}" class="group block bg-gray-50 hover:bg-red-50 border border-gray-100 rounded-2xl p-6 text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-red-200">
                    <div class="w-20 h-20 mx-auto rounded-full shadow-inner mb-4 overflow-hidden border-4 border-white bg-white group-hover:border-red-100 transition-colors flex items-center justify-center">
                        @if($hasPhoto)
                            <img src="{{ asset('storage/' . $photoPath) }}" alt="{{ $p->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl font-bold text-red-500">{{ $initial }}</span>
                        @endif
                    </div>
                    <h4 class="font-bold text-gray-800 text-lg group-hover:text-red-600 transition-colors line-clamp-2">
                        {{ $profile?->instansi ?? $p->name }}
                    </h4>
                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $profile?->bio ?? 'Penyedia data geospasial.' }}</p>

                    {{-- Badge Kontributor --}}
                    <div class="flex flex-wrap justify-center gap-2 mt-3">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold"
                              style="background:#fde8e8; color:#c0392b; border: 1px solid #fca5a5;">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Kontributor
                        </span>
                    </div>

                    <div class="mt-4 inline-flex items-center text-red-500 font-semibold text-sm group-hover:text-red-700">
                        Lihat Selengkapnya <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </div>
                </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-10 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p>Belum ada instansi terdaftar.</p>
        </div>
        @endif
    </div>
</section>

<!-- =========================
   📊 STATISTICS SECTION
========================= -->
<section class="stats-section">
    <div class="container mx-auto px-4">
        <div class="stats-header">
            <h2 class="stats-title">Pencapaian Geoportal Bengkulu</h2>
            <p class="stats-desc">Komitmen kami dalam menyediakan data geospasial berkualitas.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
                <div class="stat-value" data-target="{{ $totalPeta ?? 0 }}">0</div>
                <div class="stat-label">Layer Data Aktif</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div class="stat-value" data-target="{{ $totalKategori ?? 0 }}">0</div>
                <div class="stat-label">Kategori Data</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="stat-value" data-target="{{ $totalPengguna ?? 0 }}">0</div>
                <div class="stat-label">Pengguna Terdaftar</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="stat-value" data-target="{{ $totalMetadata ?? 0 }}">0</div>
                <div class="stat-label">Metadata Tersedia</div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<!-- Leaflet & SHP JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // =========================
    // 🗺️ LOAD MINI MAPS
    // =========================
    @foreach($datasets as $data)
        loadMiniPreview("{{ $data->geospatial_id }}");
    @endforeach

    async function loadMiniPreview(id) {
        const mapId = `mini-map-${id}`;
        const loader = document.getElementById(`loader-${id}`);
        const mapElement = document.getElementById(mapId);
        
        if (!mapElement) return;

        const miniMap = L.map(mapId, {
            zoomControl: false,
            attributionControl: false,
            dragging: false,
            scrollWheelZoom: false,
            doubleClickZoom: false
        }).setView([-3.8, 102.3], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);

        try {
            const response = await fetch(`/geospatial/${id}/geojson`);
            if (!response.ok) throw new Error('GeoJSON not found');
            
            const data = await response.json();
            
            let geoLayer;
            if (data.is_shapefile) {
                const geojson = await shp(data.url);
                geoLayer = L.geoJSON(geojson);
            } else {
                geoLayer = L.geoJSON(data);
            }

            geoLayer.setStyle({
                color: "#dc2626",
                weight: 1.5,
                fillOpacity: 0.2,
                fillColor: "#ef4444"
            }).addTo(miniMap);
            
            if (geoLayer.getLayers().length > 0) {
                miniMap.fitBounds(geoLayer.getBounds(), { padding: [15, 15] });
            }
            
            if (loader) loader.classList.add('hidden');
        } catch (error) {
            console.error(`Preview gagal untuk ID ${id}:`, error);
            if (loader) {
                loader.innerHTML = '<span class="text-xs text-gray-400 font-medium">Map Preview Tidak Tersedia</span>';
            }
        }
    }

    // =========================
    // 🎠 CAROUSEL LOGIC
    // =========================
    const track = document.getElementById('carouselTrack');
    const slides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.carousel-indicator');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    let currentSlide = 0;
    const totalSlides = slides.length;
    
    function goToSlide(index) {
        if(index < 0) index = totalSlides - 1;
        if(index >= totalSlides) index = 0;
        currentSlide = index;
        if(track) track.style.transform = `translateX(-${index * 100}%)`;
        indicators.forEach((ind, i) => ind.classList.toggle('active', i === index));
    }
    
    if(prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
        nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));
    }
    indicators.forEach(ind => {
        ind.addEventListener('click', () => goToSlide(parseInt(ind.dataset.slide)));
    });
    
    let autoPlay = setInterval(() => goToSlide(currentSlide + 1), 6000);
    if(track) {
        track.addEventListener('mouseenter', () => clearInterval(autoPlay));
        track.addEventListener('mouseleave', () => autoPlay = setInterval(() => goToSlide(currentSlide + 1), 6000));
    }

    // =========================
    // 📊 COUNTER ANIMATION
    // =========================
    const statValues = document.querySelectorAll('.stat-value');
    const statsSection = document.querySelector('.stats-section');
    
    if('IntersectionObserver' in window && statsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    statValues.forEach(el => {
                        const target = parseInt(el.dataset.target) || 0;
                        if(target > 0 && !el.classList.contains('animated')) {
                            el.classList.add('animated');
                            let current = 0;
                            const increment = Math.ceil(target / 30);
                            const animate = () => {
                                current += increment;
                                if(current < target) {
                                    el.textContent = Math.floor(current).toLocaleString('id-ID');
                                    requestAnimationFrame(animate);
                                } else {
                                    el.textContent = target.toLocaleString('id-ID');
                                }
                            };
                            animate();
                        } else if (target === 0) {
                            el.textContent = "0";
                        }
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        observer.observe(statsSection);
    }

    // =========================
    // 🎬 SCROLL REVEAL ANIMATION (sama seperti Tentang Kami)
    // =========================
    const scrollEls = document.querySelectorAll('.animate-on-scroll');
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const cards = Array.from(scrollEls);
                const idx = cards.indexOf(entry.target);
                const delay = (idx % 6) * 90; // stagger setiap 90ms, reset tiap 6 card
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, delay);
                scrollObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

    scrollEls.forEach(el => scrollObserver.observe(el));
});
</script>

<script>
// =============================================
// 🔍 CATALOG SEARCH DROPDOWN (Pure Vanilla JS)
// =============================================
(function() {
    var _layers = @json($layersForSearch ?? []);

    function renderList(list) {
        var ul = document.getElementById('catalogSearchList');
        if (!ul) return;
        if (list.length === 0) {
            ul.innerHTML = '<li class="px-4 py-5 text-sm text-gray-400 text-center">Data tidak ditemukan</li>';
            return;
        }
        ul.innerHTML = list.map(function(l) {
            return '<li onclick="catalogSearchSelectLayer(' + l.id + ')" class="px-4 py-2.5 text-sm text-gray-700 cursor-pointer hover:bg-red-50 border-b border-gray-50 last:border-0 flex items-center gap-2">'
                 + '<svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'
                 + '<span class="truncate">' + l.name + '</span></li>';
        }).join('');
    }

    window.toggleCatalogSearch = function(e) {
        e && e.stopPropagation();
        var panel = document.getElementById('catalogSearchPanel');
        if (!panel) return;
        var visible = panel.style.display === 'block';
        panel.style.display = visible ? 'none' : 'block';
        if (!visible) {
            renderList(_layers);
            var inp = document.getElementById('catalogSearchInput');
            if (inp) { inp.value = ''; setTimeout(function(){ inp.focus(); }, 80); }
        }
    };

    window.catalogSearchFilter = function(q) {
        q = (q || '').toLowerCase().trim();
        renderList(q === '' ? _layers : _layers.filter(function(l){ return l.name.toLowerCase().indexOf(q) !== -1; }));
    };

    window.catalogSearchSelectLayer = function(id) {
        var layer = _layers.find(function(l){ return l.id == id; });
        if (!layer) return;
        var label = document.getElementById('catalogSearchLabel');
        if (label) { label.textContent = layer.name; label.style.color = '#1f2937'; label.style.fontWeight = '500'; }
        var hidden = document.getElementById('catalogSearchHidden');
        if (hidden) hidden.value = layer.name;
        var panel = document.getElementById('catalogSearchPanel');
        if (panel) panel.style.display = 'none';
        var clearBox = document.getElementById('catalogSearchClearBox');
        if (clearBox) clearBox.style.display = 'block';
        // Auto-submit form
        document.getElementById('filterForm').submit();
    };

    window.catalogSearchSubmitQuery = function(q) {
        var hidden = document.getElementById('catalogSearchHidden');
        if (hidden) hidden.value = q;
        var label = document.getElementById('catalogSearchLabel');
        if (label) { label.textContent = q || 'Cari data layer...'; }
        var panel = document.getElementById('catalogSearchPanel');
        if (panel) panel.style.display = 'none';
        document.getElementById('filterForm').submit();
    };

    window.catalogSearchClear = function() {
        var label = document.getElementById('catalogSearchLabel');
        if (label) { label.textContent = 'Cari data layer...'; label.style.color = '#9ca3af'; label.style.fontWeight = ''; }
        var hidden = document.getElementById('catalogSearchHidden');
        if (hidden) hidden.value = '';
        var clearBox = document.getElementById('catalogSearchClearBox');
        if (clearBox) clearBox.style.display = 'none';
        document.getElementById('filterForm').submit();
    };

    // Close on outside click
    document.addEventListener('click', function(e) {
        var panel   = document.getElementById('catalogSearchPanel');
        var wrapper = document.getElementById('catalogSearchWrapper');
        if (panel && panel.style.display === 'block' && wrapper && !wrapper.contains(e.target)) {
            panel.style.display = 'none';
        }
    });
})();

// =============================================
// 🔽 CATALOG FILTER TOGGLE (Vanilla JS)
// =============================================
window.toggleCatalogFilter = function(e) {
    e && e.stopPropagation();
    var panel = document.getElementById('catalogFilterPanel');
    if (!panel) return;
    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
};
document.addEventListener('click', function(e) {
    var panel   = document.getElementById('catalogFilterPanel');
    var wrapper = document.getElementById('catalogFilterWrapper');
    if (panel && panel.style.display === 'block' && wrapper && !wrapper.contains(e.target)) {
        panel.style.display = 'none';
    }
});
</script>
@endpush