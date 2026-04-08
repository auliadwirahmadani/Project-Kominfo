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

    /* Hilangkan kontrol zoom Leaflet di mini map agar bersih */
    div[id^="mini-map-"] .leaflet-control-container { display: none !important; }

    /* =========================
       🎠 MODERN CAROUSEL
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
        background-repeat: no-repeat;
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
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
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

    .carousel-indicator:hover {
        background: rgba(255,255,255,0.8);
    }

    /* =========================
       🔍 SEARCH & FILTER BAR
    ========================= */
    .catalog-header {
        background: var(--glass);
        border-radius: var(--radius);
        padding: 1.5rem 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .search-container {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-input {
        flex: 1;
        min-width: 200px;
        padding: 0.875rem 1.25rem;
        border: 2px solid #e5e7eb;
        border-radius: 50px;
        font-size: 0.95rem;
        transition: var(--transition);
        background: white;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
    }

    .search-input::placeholder { color: #9ca3af; }

    .filter-select {
        padding: 0.875rem 2.5rem 0.875rem 1.25rem;
        border: 2px solid #e5e7eb;
        border-radius: 50px;
        font-size: 0.95rem;
        background: white;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em;
        cursor: pointer;
        transition: var(--transition);
        appearance: none;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
    }

    .filter-btn {
        padding: 0.875rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    .filter-btn.reset { background: #6b7280; }
    .filter-btn.reset:hover { background: #4b5563; }

    .active-filters {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }

    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.875rem;
        background: var(--primary-light);
        color: var(--primary-dark);
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .filter-tag button {
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        padding: 0;
        font-size: 1rem;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .filter-tag button:hover { opacity: 1; }

    /* =========================
       🗂️ MODERN CARD GRID
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
        position: relative;
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
        overflow: hidden;
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
        text-transform: uppercase;
        letter-spacing: 0.5px;
        z-index: 30;
    }

    .card-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.6), transparent 60%);
        opacity: 0;
        transition: var(--transition);
        display: flex;
        align-items: flex-end;
        padding: 1rem;
        z-index: 30;
        pointer-events: none; /* Agar tidak memblokir interaksi mouse ke peta */
    }

    .catalog-card:hover .card-overlay { opacity: 1; }

    .card-actions {
        display: flex;
        gap: 0.5rem;
        width: 100%;
        pointer-events: auto; /* Mengaktifkan klik tombol di atas overlay */
    }

    .card-action-btn {
        flex: 1;
        padding: 0.625rem;
        background: white;
        color: var(--secondary);
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        text-decoration: none;
    }

    .card-action-btn.primary {
        background: var(--primary);
        color: white;
    }

    .card-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
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
        line-height: 1.4;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .card-author {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .card-author-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .card-footer {
        padding: 0 1.25rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f3f4f6;
        margin-top: auto;
        padding-top: 1rem;
    }

    .card-tags {
        display: flex;
        gap: 0.375rem;
        flex-wrap: wrap;
    }

    .card-tag {
        padding: 0.25rem 0.625rem;
        background: #f9fafb;
        color: #4b5563;
        font-size: 0.75rem;
        border-radius: 4px;
        font-weight: 500;
    }

    .card-view-btn {
        padding: 0.5rem 1.25rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
    }

    .card-view-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        grid-column: 1 / -1;
    }

    .empty-state svg {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--secondary);
        margin-bottom: 0.5rem;
    }

    /* =========================
       📊 STATISTICS SECTION
    ========================= */
    .stats-section {
        background: linear-gradient(135deg, #fef2f2 0%, #fff 50%, #fef2f2 100%);
        padding: 4rem 0;
        margin-top: 3rem;
        border-radius: var(--radius);
    }

    .stats-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .stats-title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 700;
        color: var(--secondary);
        margin-bottom: 0.75rem;
    }

    .stats-desc {
        color: #6b7280;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
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
        background: white;
        padding: 1.75rem 1.5rem;
        border-radius: var(--radius);
        text-align: center;
        border: 2px solid transparent;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        opacity: 0;
        transition: var(--transition);
    }

    .stat-card:hover {
        border-color: var(--primary-light);
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card:hover::before { opacity: 1; }

    .stat-value {
        font-size: clamp(2rem, 5vw, 2.5rem);
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 0.5rem;
        line-height: 1;
    }

    .stat-label {
        color: #4b5563;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, var(--primary-light), #fff);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.5rem;
    }

    /* =========================
       🎬 ANIMATIONS
    ========================= */
    @keyframes countUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-value.animate {
        animation: countUp 0.6s ease-out forwards;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        50% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
    }

    .card-new::after {
        content: 'NEW';
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.25rem 0.625rem;
        background: var(--accent);
        color: #78350f;
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 4px;
        animation: pulse 2s infinite;
        z-index: 35;
    }

    /* =========================
       📱 RESPONSIVE
    ========================= */
    @media (max-width: 768px) {
        .carousel-slide { height: 45vh; }
        .carousel-content { padding: 1.5rem; }
        .catalog-header { padding: 1rem 1.5rem; }
        .search-container { flex-direction: column; align-items: stretch; }
        .filter-select, .search-input { width: 100%; }
        .catalog-grid { grid-template-columns: 1fr; }
        .stats-section { padding: 2.5rem 0; }
        .stat-card { padding: 1.5rem 1rem; }
    }

    /* =========================
       ♿ ACCESSIBILITY
    ========================= */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>

<!-- =========================
   🎠 HERO CAROUSEL
========================= -->
<div class="carousel container mx-auto px-4" style="max-width: 1600px;">
    <div class="carousel-track" id="carouselTrack">
        <!-- Slide 1 -->
        <div class="carousel-slide" style="background-image: url('https://images.unsplash.com/photo-1422190441165-ec2956dc9ecc?auto=format&fit=crop&w=1600&q=80');">
            <div class="carousel-content">
                <h1 class="carousel-title">Geoportal Provinsi Bengkulu</h1>
                <p class="carousel-desc">Akses data geospasial terintegrasi untuk perencanaan pembangunan, penelitian, dan pelayanan publik yang lebih baik.</p>
                <a href="#katalog" class="carousel-btn">
                    <span>Jelajahi Data</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>
        <!-- Slide 2 -->
        <div class="carousel-slide" style="background-image: url('https://images.unsplash.com/photo-1533090161767-e6ffed986c88?auto=format&fit=crop&w=1600&q=80');">
            <div class="carousel-content">
                <h1 class="carousel-title">Data Geospasial Terintegrasi</h1>
                <p class="carousel-desc">Ribuan layer data dari berbagai instansi, siap digunakan untuk analisis spasial dan pengambilan keputusan.</p>
                <a href="#katalog" class="carousel-btn">
                    <span>Lihat Katalog</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
    <!-- Navigation -->
    <button class="carousel-nav prev" id="prevBtn" aria-label="Slide sebelumnya">‹</button>
    <button class="carousel-nav next" id="nextBtn" aria-label="Slide berikutnya">›</button>
    <!-- Indicators -->
    <div class="carousel-indicators" id="indicators">
        <button class="carousel-indicator active" data-slide="0" aria-label="Slide 1"></button>
        <button class="carousel-indicator" data-slide="1" aria-label="Slide 2"></button>
    </div>
</div>

<!-- =========================
   🔍 CATALOG SECTION
========================= -->
<div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
    {{-- Map Preview with Badge --}}
    <div class="relative h-48 bg-gray-100">
        {{-- Badge Kategori --}}
        <div class="absolute top-3 left-3 z-10">
            <span class="px-3 py-1 bg-white text-gray-800 text-xs font-bold rounded-full shadow-md">
                BATAS ADMINISTRASI
            </span>
        </div>
        
        {{-- Map Image/Preview --}}
       <div id="mini-map-{{ $data->geospatial_id }}" class="w-full h-full"></div>
        {{-- Loading Indicator --}}
        <div id="loader-{{ $layer->geospatial_id }}" class="absolute inset-0 flex items-center justify-center bg-gray-50">
            <svg class="animate-spin h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    {{-- Card Content --}}
    <div class="p-5">
        {{-- Title & ID --}}
        <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $layer->layer_name }}</h3>
        <p class="text-sm text-gray-500 mb-4">ID: {{ $layer->identifier ?? 'BKL-' . strtoupper(substr($layer->layer_name, 0, 4)) . '-' . date('Y') }}</p>

        {{-- Info Grid (Instansi & Tahun) --}}
        <div class="grid grid-cols-2 gap-4 mb-5">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Instansi</p>
                <p class="text-sm font-medium text-gray-700 text-right">{{ $layer->metadata->organization ?? 'Bappeda Kabupaten/Kota' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Tahun</p>
                <p class="text-sm font-medium text-gray-700 text-right">{{ $layer->metadata->publication_date ? \Carbon\Carbon::parse($layer->metadata->publication_date)->format('Y') : date('Y') }}</p>
            </div>
        </div>

        {{-- Button 1: Lihat Detail Dataset --}}
        <a href="{{ route('dataset.show', $layer->geospatial_id) }}" 
           class="block w-full py-2.5 px-4 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-lg text-center transition-colors duration-200 mb-2">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Lihat Detail Dataset
        </a>

        {{-- Button 2: Buka Peta Full Screen --}}
        <a href="{{ route('geo', ['layer' => $layer->geospatial_id]) }}" 
           class="block w-full py-2.5 px-4 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-lg text-center transition-colors duration-200 mb-4">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
            </svg>
            Buka Peta Full Screen
        </a>

        {{-- Bottom Actions (Edit & Delete) --}}
        <div class="flex gap-2 pt-3 border-t border-gray-100">
            <button onclick="editMetadata({{ json_encode($layer->metadata) }}, {{ $layer->geospatial_id }})" 
                    class="flex-1 py-2 px-3 bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium rounded-lg text-sm transition-colors duration-200 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                EDIT
            </button>
            
            <form action="{{ route('admin.metadata.delete', $layer->metadata->metadata_id) }}" 
                  method="POST" 
                  onsubmit="return confirm('Yakin ingin menghapus metadata ini?');" 
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="py-2 px-3 bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 font-medium rounded-lg text-sm transition-colors duration-200 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
<!-- =========================
   📊 STATISTICS SECTION
========================= -->
<section class="stats-section">
    <div class="container mx-auto px-4">
        <div class="stats-header">
            <h2 class="stats-title">🎯 Pencapaian Geoportal Bengkulu</h2>
            <p class="stats-desc">Komitmen kami dalam menyediakan data geospasial berkualitas untuk mendukung pembangunan daerah yang berkelanjutan.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🗂️</div>
                <div class="stat-value" data-target="{{ $totalKategori ?? 0 }}">0</div>
                <div class="stat-label">Jenis Data</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🗺️</div>
                <div class="stat-value" data-target="{{ $totalPeta ?? 0 }}">0</div>
                <div class="stat-label">Peta Aktif</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value" data-target="{{ $totalPengguna ?? 0 }}">0</div>
                <div class="stat-label">Pengguna</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏛️</div>
                <div class="stat-value" data-target="{{ $totalInstansi ?? 0 }}">0</div>
                <div class="stat-label">Instansi Mitra</div>
            </div>
        </div>
    </div>
</section>


<!-- =========================
   🧠 JAVASCRIPT
========================= -->
<!-- Leaflet & SHP JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // =========================
    // 🗺️ LEAFLET MINI MAP LOGIC
    // =========================
    @forelse($katalogData ?? [] as $data)
    {{-- GANTI SEMUA $layer MENJADI $data --}}
    {{-- GANTI $data->geospatial_id MENJADI $data->id --}}
    
    <div id="mini-map-{{ $data->id }}">
    <div id="loader-{{ $data->id }}">
    <h3>{{ $data->judul }}</h3>
    <p>{{ $data->instansi }}</p>
    <p>{{ $data->tahun }}</p>
    <a href="{{ route('dataset.show', $data->id) }}">
    <a href="{{ route('geo', ['layer' => $data->id]) }}">
    @endforelse

    async function loadMiniPreview(id) {
        const mapId = `mini-map-${id}`;
        const loader = document.getElementById(`loader-${id}`);
        
        if (!document.getElementById(mapId)) return;

        const miniMap = L.map(mapId, {
            zoomControl: false, attributionControl: false,
            dragging: false, scrollWheelZoom: false, doubleClickZoom: false
        }).setView([-3.8, 102.3], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);

        try {
            // INGAT: Route ini harus bisa diakses publik (tanpa middleware auth)
            const response = await fetch(`/admin/geospasial/${id}/geojson`);
            const data = await response.json();

            let geoLayer;
            if (data.is_shapefile) {
                const geojson = await shp(data.url);
                geoLayer = L.geoJSON(geojson);
            } else {
                geoLayer = L.geoJSON(data);
            }

            geoLayer.setStyle({
                color: "#ef4444", weight: 1.5, fillOpacity: 0.2, fillColor: "#ef4444"
            }).addTo(miniMap);

            miniMap.fitBounds(geoLayer.getBounds(), { padding: [10, 10] });
            if(loader) loader.classList.add('hidden');
        } catch (e) {
            console.error("Preview gagal untuk ID: " + id);
            if(loader) {
                loader.innerHTML = '<span style="font-size: 0.75rem; color: #ef4444; font-weight: bold; background: #fef2f2; padding: 0.25rem 0.5rem; border-radius: 0.25rem; border: 1px solid #fca5a5;">Gagal Muat Peta</span>';
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
        track.style.transform = `translateX(-${index * 100}%)`;
        
        indicators.forEach((ind, i) => {
            ind.classList.toggle('active', i === index);
        });
        
        const content = slides[index].querySelector('.carousel-content');
        content.style.animation = 'none';
        setTimeout(() => content.style.animation = 'fadeInUp 0.6s ease-out', 10);
    }
    
    if(prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
        nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));
    }
    
    indicators.forEach(ind => {
        ind.addEventListener('click', () => {
            goToSlide(parseInt(ind.dataset.slide));
        });
    });
    
    let autoPlay = setInterval(() => goToSlide(currentSlide + 1), 6000);
    if(track) {
        track.addEventListener('mouseenter', () => clearInterval(autoPlay));
        track.addEventListener('mouseleave', () => {
            autoPlay = setInterval(() => goToSlide(currentSlide + 1), 6000);
        });
    }

    // =========================
    // 🔍 SEARCH & FILTER LOGIC
    // =========================
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const typeFilter = document.getElementById('typeFilter');
    const applyBtn = document.getElementById('applyFilter');
    const resetBtn = document.getElementById('resetFilter');
    const activeFiltersEl = document.getElementById('activeFilters');
    const catalogGrid = document.getElementById('catalogGrid');
    const emptyState = document.getElementById('emptyState');
    const cards = document.querySelectorAll('.catalog-card');
    
    function getFilterValues() {
        return {
            search: searchInput ? searchInput.value.toLowerCase().trim() : '',
            category: categoryFilter ? categoryFilter.value : '',
            type: typeFilter ? typeFilter.value : ''
        };
    }
    
    function updateActiveFiltersDisplay() {
        if(!activeFiltersEl) return;
        activeFiltersEl.innerHTML = '';
        const filters = getFilterValues();
        
        if(filters.search) {
            const tag = document.createElement('span');
            tag.className = 'filter-tag';
            tag.innerHTML = `🔍 "${filters.search}" <button type="button" data-clear="search">&times;</button>`;
            activeFiltersEl.appendChild(tag);
        }
        if(filters.category) {
            const tag = document.createElement('span');
            tag.className = 'filter-tag';
            tag.innerHTML = `📁 ${filters.category} <button type="button" data-clear="category">&times;</button>`;
            activeFiltersEl.appendChild(tag);
        }
        if(filters.type) {
            const tag = document.createElement('span');
            tag.className = 'filter-tag';
            tag.innerHTML = `📐 ${filters.type} <button type="button" data-clear="type">&times;</button>`;
            activeFiltersEl.appendChild(tag);
        }
        
        activeFiltersEl.querySelectorAll('button[data-clear]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const field = e.target.dataset.clear;
                if(field === 'search') searchInput.value = '';
                if(field === 'category') categoryFilter.value = '';
                if(field === 'type') typeFilter.value = '';
                applyFilters();
            });
        });
    }
    
    function applyFilters() {
        const filters = getFilterValues();
        updateActiveFiltersDisplay();
        
        let visibleCount = 0;
        
        cards.forEach(card => {
            const title = card.querySelector('.card-title').textContent.toLowerCase();
            const tags = Array.from(card.querySelectorAll('.card-tag')).map(t => t.textContent.toLowerCase());
            const category = card.dataset.category;
            const type = card.dataset.type;
            
            const matchesSearch = !filters.search || title.includes(filters.search) || tags.some(t => t.includes(filters.search));
            const matchesCategory = !filters.category || category === filters.category;
            const matchesType = !filters.type || type === filters.type;
            
            const show = matchesSearch && matchesCategory && matchesType;
            card.style.display = show ? 'flex' : 'none';
            if(show) visibleCount++;
        });
        
        if(emptyState && catalogGrid) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            catalogGrid.style.display = visibleCount === 0 ? 'none' : 'grid';
        }
    }
    
    function resetFilters() {
        if(searchInput) searchInput.value = '';
        if(categoryFilter) categoryFilter.value = '';
        if(typeFilter) typeFilter.value = '';
        updateActiveFiltersDisplay();
        
        cards.forEach(card => card.style.display = 'flex');
        if(emptyState && catalogGrid) {
            emptyState.style.display = 'none';
            catalogGrid.style.display = 'grid';
        }
    }
    
    if(applyBtn) applyBtn.addEventListener('click', applyFilters);
    if(resetBtn) resetBtn.addEventListener('click', resetFilters);
    document.getElementById('emptyResetBtn')?.addEventListener('click', resetFilters);
    
    let searchTimeout;
    if(searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 300);
        });
        searchInput.addEventListener('keypress', (e) => {
            if(e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
        searchInput.placeholder = '🔍 Cari... (Ctrl+K)';
    }
    updateActiveFiltersDisplay();

    // =========================
    // 📊 COUNTER ANIMATION
    // =========================
    function animateCounter(element, target, duration = 1500) {
        const start = 0;
        const increment = target / (duration / 16); 
        let current = start;
        
        const step = () => {
            current += increment;
            if(current < target) {
                element.textContent = Math.floor(current).toLocaleString('id-ID');
                requestAnimationFrame(step);
            } else {
                element.textContent = target.toLocaleString('id-ID');
            }
        };
        if (target > 0) step();
    }
    
    const statsSection = document.querySelector('.stats-section');
    const statValues = document.querySelectorAll('.stat-value');
    
    if('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    statValues.forEach(el => {
                        const target = parseInt(el.dataset.target) || 0;
                        if(!el.classList.contains('animated') && target > 0) {
                            el.classList.add('animated', 'animate');
                            animateCounter(el, target);
                        }
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        
        if(statsSection) observer.observe(statsSection);
    }

    // =========================
    // ♿ KEYBOARD SHORTCUTS
    // =========================
    document.addEventListener('keydown', (e) => {
        if((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if(searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        if(e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.blur();
            if(searchInput.value) {
                searchInput.value = '';
                applyFilters();
            }
        }
    });
});
</script>

@endsection