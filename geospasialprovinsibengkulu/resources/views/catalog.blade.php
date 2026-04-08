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
        border-radius: 0 0 var(--radius) var(--radius);
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
    .carousel-slide::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(239,68,68,0.85) 0%, rgba(220,38,38,0.7) 50%, transparent 100%);
        z-index: 1;
    }
    .carousel-content {
        position: relative;
        z-index: 2;
        color: white;
        padding: 2rem;
        max-width: 600px;
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
    .carousel-nav.prev { left: 1rem; }
    .carousel-nav.next { right: 1rem; }
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
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }
    .catalog-card {
        background: white;
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid #f3f4f6;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }
    .catalog-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-light);
    }
    .card-image {
        position: relative;
        height: 200px;
        background-color: #f8fafc;
    }
    .card-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        padding: 0.375rem 0.875rem;
        background: white;
        color: var(--primary);
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
        background: linear-gradient(135deg, #fef2f2 0%, #fff 50%, #fef2f2 100%);
        padding: 4rem 0;
        margin-top: 3rem;
        border-radius: var(--radius);
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
    @media (min-width: 768px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
    .stat-card {
        background: white;
        padding: 1.75rem 1.5rem;
        border-radius: var(--radius);
        text-align: center;
    }
    .stat-value {
        font-size: clamp(2rem, 5vw, 2.5rem);
        font-weight: 800;
        color: var(--primary);
    }
    .stat-label { color: #4b5563; font-weight: 500; }
</style>

<!-- =========================
   🎠 HERO CAROUSEL
========================= -->
<div class="carousel container mx-auto px-4" style="max-width: 1600px;">
    <div class="carousel-track" id="carouselTrack">
        <div class="carousel-slide" style="background-image: url('https://images.unsplash.com/photo-1422190441165-ec2956dc9ecc?auto=format&fit=crop&w=1600&q=80');">
            <div class="carousel-content">
                <h1 class="carousel-title">Geoportal Provinsi Bengkulu</h1>
                <p class="carousel-desc">Akses data geospasial terintegrasi untuk perencanaan pembangunan.</p>
                <a href="#katalog" class="carousel-btn">Jelajahi Data →</a>
            </div>
        </div>
        <div class="carousel-slide" style="background-image: url('https://images.unsplash.com/photo-1533090161767-e6ffed986c88?auto=format&fit=crop&w=1600&q=80');">
            <div class="carousel-content">
                <h1 class="carousel-title">Data Geospasial Terintegrasi</h1>
                <p class="carousel-desc">Ribuan layer data dari berbagai instansi.</p>
                <a href="#katalog" class="carousel-btn">Lihat Katalog →</a>
            </div>
        </div>
    </div>
    <button class="carousel-nav prev" id="prevBtn">‹</button>
    <button class="carousel-nav next" id="nextBtn">›</button>
    <div class="carousel-indicators" id="indicators">
        <button class="carousel-indicator active" data-slide="0"></button>
        <button class="carousel-indicator" data-slide="1"></button>
    </div>
</div>

<!-- =========================
   🔍 CATALOG SECTION
========================= -->
<section id="katalog" class="container mx-auto px-4 py-8">

    {{-- ===== FILTER BAR (style geo page) ===== --}}
    <form action="{{ route('catalog') }}" method="GET" id="filterForm">
    <div class="flex flex-wrap items-center gap-3 mb-6 p-3 bg-white rounded-2xl shadow-sm border border-gray-100">

        {{-- Search Input --}}
        <div class="flex-1 min-w-[200px] relative" x-data="catalogSearch" x-init="initData()">
            <button @click="toggle()" type="button"
                    class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-full text-gray-700 text-sm hover:border-red-400 hover:bg-red-50/30 transition focus:outline-none">
                <div class="flex items-center gap-2 overflow-hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="truncate" x-text="selected ? selected.name : (searchQuery || 'Cari data layer...')" :class="selected ? 'text-gray-800 font-medium' : 'text-gray-400'"></span>
                </div>
                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            {{-- Hidden input untuk submit form --}}
            <input type="hidden" name="search" :value="selected ? selected.name : searchQuery">

            {{-- Dropdown --}}
            <div x-show="open" @click.outside="open = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="absolute left-0 top-full mt-2 w-full bg-white rounded-xl shadow-xl border border-gray-200 z-[200] overflow-hidden"
                 style="display:none;">
                <div class="p-2.5 border-b border-gray-100 bg-gray-50">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" x-model="query" x-ref="searchInput" @input="search()"
                               placeholder="Ketik nama data..."
                               class="w-full pl-8 pr-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-red-400 text-gray-800">
                    </div>
                </div>
                <ul class="max-h-52 overflow-y-auto">
                    <template x-for="layer in filteredLayers" :key="layer.id">
                        <li @click="selectLayer(layer)"
                            class="px-4 py-2.5 text-sm text-gray-700 cursor-pointer hover:bg-red-50 border-b border-gray-50 last:border-0 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            <span class="truncate" x-text="layer.name"></span>
                        </li>
                    </template>
                    <li x-show="filteredLayers.length === 0" class="px-4 py-5 text-sm text-gray-400 text-center">
                        <svg class="w-8 h-8 mx-auto mb-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Data tidak ditemukan
                    </li>
                </ul>
                <div x-show="selected" class="p-2 border-t border-gray-100 bg-gray-50">
                    <button type="button" @click="clearSearch()" class="w-full text-xs text-gray-500 hover:text-red-600 py-1 transition">✕ Hapus pilihan</button>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="hidden sm:block w-px h-8 bg-gray-200"></div>

        {{-- Filter Panel Button --}}
        <div class="relative" x-data="{ filterOpen: false }">
            <button type="button" @click="filterOpen = !filterOpen"
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
            <div x-show="filterOpen" @click.outside="filterOpen = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute left-0 sm:left-auto sm:right-0 top-full mt-2 w-72 bg-white rounded-xl shadow-2xl py-3 z-[200] border border-gray-100"
                 style="display:none;">

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

                    {{-- Tipe Data --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tipe Data</label>
                        <select name="type" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:outline-none bg-gray-50">
                            <option value="">Semua Tipe</option>
                            <option value="vector" {{ request('type') == 'vector' ? 'selected' : '' }}>Vector</option>
                            <option value="raster" {{ request('type') == 'raster' ? 'selected' : '' }}>Raster</option>
                            <option value="wms"    {{ request('type') == 'wms'    ? 'selected' : '' }}>WMS</option>
                            <option value="wfs"    {{ request('type') == 'wfs'    ? 'selected' : '' }}>WFS</option>
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
                    <button type="submit" class="flex-1 px-3 py-2 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
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

    <!-- ✅ Cards Grid - MENGGUNAKAN VARIABEL DATABASE YANG BENAR -->
    <div class="catalog-grid" id="catalogGrid">
        @forelse($datasets as $data)
        <article class="catalog-card">
            
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

                <div class="grid grid-cols-2 gap-4 my-4">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Instansi</p>
                        <p class="text-sm font-medium text-gray-700 truncate" title="{{ $data->metadata->organization ?? 'Pemprov Bengkulu' }}">
                            {{ $data->metadata->organization ?? 'Pemprov Bengkulu' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tahun</p>
                        <p class="text-sm font-medium text-gray-700">
                            {{ $data->metadata->data_year ?? $data->created_at->format('Y') }}
                        </p>
                    </div>
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
</section>

<!-- =========================
   📊 STATISTICS SECTION
========================= -->
<section class="stats-section">
    <div class="container mx-auto px-4">
        <div class="stats-header">
            <h2 class="stats-title">🎯 Pencapaian Geoportal Bengkulu</h2>
            <p class="stats-desc">Komitmen kami dalam menyediakan data geospasial berkualitas.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" data-target="{{ $totalKategori ?? 0 }}">0</div>
                <div class="stat-label">Kategori Data</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" data-target="{{ $totalPeta ?? 0 }}">0</div>
                <div class="stat-label">Peta Aktif</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" data-target="{{ $totalPengguna ?? 0 }}">0</div>
                <div class="stat-label">Pengguna</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" data-target="{{ $totalInstansi ?? 0 }}">0</div>
                <div class="stat-label">Instansi Mitra</div>
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
});
</script>

<script>
// =========================
// 🔍 ALPINE COMPONENT: catalogSearch (sama dengan layerSearch di geo)
// =========================
document.addEventListener('alpine:init', () => {
    Alpine.data('catalogSearch', () => ({
        open: false,
        query: '',
        searchQuery: @json(request("search") ?? ''),
        layers: @json($layersForSearch),
        filteredLayers: [],
        selected: null,

        initData() {
            this.filteredLayers = this.layers;

            // Tandai pilihan jika ada pencarian aktif dari URL
            const currentSearch = @json(request("search") ?? '');
            if (currentSearch) {
                const found = this.layers.find(l => l.name === currentSearch);
                if (found) this.selected = found;
                else this.searchQuery = currentSearch;
            }
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.query = '';
                this.filteredLayers = this.layers;
                setTimeout(() => this.$refs.searchInput?.focus(), 80);
            }
        },

        search() {
            const q = this.query.toLowerCase().trim();
            this.filteredLayers = q === ''
                ? this.layers
                : this.layers.filter(l => l.name.toLowerCase().includes(q));
        },

        selectLayer(layer) {
            this.selected = layer;
            this.searchQuery = layer.name;
            this.open = false;
            this.$nextTick(() => {
                document.getElementById('filterForm')?.submit();
            });
        },

        clearSearch() {
            this.selected = null;
            this.searchQuery = '';
            this.query = '';
            this.filteredLayers = this.layers;
        }
    }));
});
</script>
@endpush