@extends('layouts.app')

@section('title', 'Katalog Data Geospasial')

@section('content')

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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

    .filter-btn.reset {
        background: #6b7280;
    }
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
    }

    .card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .catalog-card:hover .card-image img {
        transform: scale(1.05);
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
    }

    .card-meta {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        background: white;
        color: var(--secondary);
        font-size: 0.8rem;
        font-weight: 500;
        border-radius: 50px;
        box-shadow: var(--shadow-sm);
    }

    .card-meta svg { width: 1rem; height: 1rem; }

    .card-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.6), transparent 60%);
        opacity: 0;
        transition: var(--transition);
        display: flex;
        align-items: flex-end;
        padding: 1rem;
    }

    .catalog-card:hover .card-overlay { opacity: 1; }

    .card-actions {
        display: flex;
        gap: 0.5rem;
        width: 100%;
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
        z-index: 5;
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

    /* Focus visible for keyboard navigation */
    .filter-select:focus-visible,
    .search-input:focus-visible,
    .filter-btn:focus-visible,
    .card-view-btn:focus-visible,
    .carousel-nav:focus-visible {
        outline: 3px solid var(--accent);
        outline-offset: 2px;
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

        <!-- Slide 3 -->
        <div class="carousel-slide" style="background-image: url('https://images.unsplash.com/photo-1519327232521-1ea2c736d34d?auto=format&fit=crop&w=1600&q=80');">
            <div class="carousel-content">
                <h1 class="carousel-title">Pelayanan Informasi Publik</h1>
                <p class="carousel-desc">Transparansi data untuk masyarakat. Unduh, visualisasikan, dan manfaatkan informasi geospasial secara gratis.</p>
                <a href="#katalog" class="carousel-btn">
                    <span>Mulai Eksplorasi</span>
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
        <button class="carousel-indicator" data-slide="2" aria-label="Slide 3"></button>
    </div>
</div>


<!-- =========================
   🔍 CATALOG SECTION
========================= -->
<section id="katalog" class="container mx-auto px-4 py-8">
    
    <!-- Search & Filter -->
    <div class="catalog-header">
        <div class="search-container">
            <input type="text" 
                   class="search-input" 
                   id="searchInput"
                   placeholder="🔍 Cari peta, wilayah, atau kata kunci..."
                   aria-label="Cari data geospasial">
            
            <select class="filter-select" id="categoryFilter" aria-label="Filter kategori">
                <option value="">Semua Kategori</option>
                <option value="administrasi">Administrasi</option>
                <option value="infrastruktur">Infrastruktur</option>
                <option value="lingkungan">Lingkungan</option>
                <option value="ekonomi">Ekonomi</option>
                <option value="sosial">Sosial</option>
            </select>
            
            <select class="filter-select" id="typeFilter" aria-label="Filter tipe data">
                <option value="">Semua Tipe</option>
                <option value="polygon">Polygon</option>
                <option value="polyline">Polyline</option>
                <option value="point">Point</option>
                <option value="raster">Raster</option>
            </select>
            
            <button class="filter-btn" id="applyFilter">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
            </button>
            <button class="filter-btn reset" id="resetFilter">Reset</button>
        </div>
        
        <!-- Active Filters Tags -->
        <div class="active-filters" id="activeFilters"></div>
    </div>

    <!-- Cards Grid -->
    <div class="catalog-grid" id="catalogGrid">
        
        <!-- Card 1 -->
        <article class="catalog-card card-new" data-category="lingkungan" data-type="polygon">
            <div class="card-image">
                <img src="https://i.imgur.com/9Z5Zf7L.png" alt="Kawasan Ekosistem RIMBA" loading="lazy">
                <span class="card-badge">KW</span>
                <span class="card-meta">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.566a1 1 0 01.745.334l3.898 3.898a1 1 0 001.414 0l3.898-3.898a1 1 0 01.745-.334H17a2 2 0 002-2V5a1 1 0 00-1-1H3zm8.5 2.5a.5.5 0 01.5.5v3.75a.5.5 0 01-1 0V6a.5.5 0 01.5-.5z" clip-rule="evenodd"/></svg>
                    2 Layer
                </span>
                <div class="card-overlay">
                    <div class="card-actions">
                        <button class="card-action-btn">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh
                        </button>
                        <button class="card-action-btn primary">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Preview
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h3 class="card-title">Kawasan Ekosistem RIMBA</h3>
                <div class="card-author">
                    <div class="card-author-avatar">KW</div>
                    <span>Kusmana H Wirawan</span>
                </div>
            </div>
            <div class="card-footer">
                <div class="card-tags">
                    <span class="card-tag">Lingkungan</span>
                    <span class="card-tag">2024</span>
                </div>
                <button class="card-view-btn">View Map →</button>
            </div>
        </article>

        <!-- Card 2 -->
        <article class="catalog-card" data-category="administrasi" data-type="polygon">
            <div class="card-image">
                <img src="https://i.imgur.com/8XJ4ZqG.png" alt="Batas Administrasi Bengkulu" loading="lazy">
                <span class="card-badge">ADM</span>
                <div class="card-overlay">
                    <div class="card-actions">
                        <button class="card-action-btn">Unduh</button>
                        <button class="card-action-btn primary">Preview</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h3 class="card-title">Batas Administrasi Provinsi</h3>
                <div class="card-author">
                    <div class="card-author-avatar">B</div>
                    <span>Badan Pusat Statistik</span>
                </div>
            </div>
            <div class="card-footer">
                <div class="card-tags">
                    <span class="card-tag">Administrasi</span>
                    <span class="card-tag">2024</span>
                </div>
                <button class="card-view-btn">View Map →</button>
            </div>
        </article>

        <!-- Card 3 -->
        <article class="catalog-card" data-category="infrastruktur" data-type="polyline">
            <div class="card-image">
                <img src="https://i.imgur.com/1Jj2X7d.png" alt="Jaringan Jalan Bengkulu" loading="lazy">
                <span class="card-badge">INF</span>
                <span class="card-meta">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.566a1 1 0 01.745.334l3.898 3.898a1 1 0 001.414 0l3.898-3.898a1 1 0 01.745-.334H17a2 2 0 002-2V5a1 1 0 00-1-1H3zm8.5 2.5a.5.5 0 01.5.5v3.75a.5.5 0 01-1 0V6a.5.5 0 01.5-.5z" clip-rule="evenodd"/></svg>
                    3 Layer
                </span>
                <div class="card-overlay">
                    <div class="card-actions">
                        <button class="card-action-btn">Unduh</button>
                        <button class="card-action-btn primary">Preview</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h3 class="card-title">Jaringan Jalan Utama</h3>
                <div class="card-author">
                    <div class="card-author-avatar">D</div>
                    <span>Dinas PUPR Bengkulu</span>
                </div>
            </div>
            <div class="card-footer">
                <div class="card-tags">
                    <span class="card-tag">Infrastruktur</span>
                    <span class="card-tag">2023</span>
                </div>
                <button class="card-view-btn">View Map →</button>
            </div>
        </article>

        <!-- Card 4 -->
        <article class="catalog-card" data-category="ekonomi" data-type="polygon">
            <div class="card-image">
                <img src="https://i.imgur.com/5YdRq4P.png" alt="Kawasan Industri" loading="lazy">
                <span class="card-badge">ECO</span>
                <div class="card-overlay">
                    <div class="card-actions">
                        <button class="card-action-btn">Unduh</button>
                        <button class="card-action-btn primary">Preview</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h3 class="card-title">Kawasan Industri & Ekonomi</h3>
                <div class="card-author">
                    <div class="card-author-avatar">D</div>
                    <span>Dinas Perdagangan</span>
                </div>
            </div>
            <div class="card-footer">
                <div class="card-tags">
                    <span class="card-tag">Ekonomi</span>
                    <span class="card-tag">2024</span>
                </div>
                <button class="card-view-btn">View Map →</button>
            </div>
        </article>

        <!-- Card 5 -->
        <article class="catalog-card" data-category="lingkungan" data-type="raster">
            <div class="card-image">
                <img src="https://i.imgur.com/7JcD2kX.png" alt="Tutupan Lahan" loading="lazy">
                <span class="card-badge">SAT</span>
                <div class="card-overlay">
                    <div class="card-actions">
                        <button class="card-action-btn">Unduh</button>
                        <button class="card-action-btn primary">Preview</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h3 class="card-title">Tutupan Lahan 2024</h3>
                <div class="card-author">
                    <div class="card-author-avatar">L</div>
                    <span>DLH Provinsi</span>
                </div>
            </div>
            <div class="card-footer">
                <div class="card-tags">
                    <span class="card-tag">Lingkungan</span>
                    <span class="card-tag">Satelit</span>
                </div>
                <button class="card-view-btn">View Map →</button>
            </div>
        </article>

        <!-- Card 6 -->
        <article class="catalog-card" data-category="sosial" data-type="point">
            <div class="card-image">
                <img src="https://i.imgur.com/9Z5Zf7L.png" alt="Fasilitas Publik" loading="lazy">
                <span class="card-badge">SOC</span>
                <span class="card-meta">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.566a1 1 0 01.745.334l3.898 3.898a1 1 0 001.414 0l3.898-3.898a1 1 0 01.745-.334H17a2 2 0 002-2V5a1 1 0 00-1-1H3zm8.5 2.5a.5.5 0 01.5.5v3.75a.5.5 0 01-1 0V6a.5.5 0 01.5-.5z" clip-rule="evenodd"/></svg>
                    12 POI
                </span>
                <div class="card-overlay">
                    <div class="card-actions">
                        <button class="card-action-btn">Unduh</button>
                        <button class="card-action-btn primary">Preview</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h3 class="card-title">Fasilitas Publik & Layanan</h3>
                <div class="card-author">
                    <div class="card-author-avatar">S</div>
                    <span>Dinas Sosial</span>
                </div>
            </div>
            <div class="card-footer">
                <div class="card-tags">
                    <span class="card-tag">Sosial</span>
                    <span class="card-tag">POI</span>
                </div>
                <button class="card-view-btn">View Map →</button>
            </div>
        </article>

    </div>

    <!-- Empty State (hidden by default) -->
    <div class="empty-state" id="emptyState" style="display: none;">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3>Tidak ada data yang ditemukan</h3>
        <p>Coba ubah kata kunci atau reset filter untuk melihat semua data.</p>
        <button class="filter-btn reset" id="emptyResetBtn" style="margin-top: 1rem;">Reset Filter</button>
    </div>

</section>


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
            
            <!-- Stat 1 -->
            <div class="stat-card">
                <div class="stat-icon">🗂️</div>
                <div class="stat-value" data-target="50">0</div>
                <div class="stat-label">Jenis Data</div>
            </div>

            <!-- Stat 2 -->
            <div class="stat-card">
                <div class="stat-icon">🗺️</div>
                <div class="stat-value" data-target="120">0</div>
                <div class="stat-label">Peta Aktif</div>
            </div>

            <!-- Stat 3 -->
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value" data-target="15000">0</div>
                <div class="stat-label">Pengguna</div>
            </div>

            <!-- Stat 4 -->
            <div class="stat-card">
                <div class="stat-icon">🏛️</div>
                <div class="stat-value" data-target="25">0</div>
                <div class="stat-label">Instansi Mitra</div>
            </div>

        </div>

    </div>
</section>


<!-- =========================
   🧠 JAVASCRIPT
========================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
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
        
        // Update indicators
        indicators.forEach((ind, i) => {
            ind.classList.toggle('active', i === index);
        });
        
        // Reset animation for content
        const content = slides[index].querySelector('.carousel-content');
        content.style.animation = 'none';
        setTimeout(() => content.style.animation = 'fadeInUp 0.6s ease-out', 10);
    }
    
    // Event listeners
    prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
    nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));
    
    indicators.forEach(ind => {
        ind.addEventListener('click', () => {
            goToSlide(parseInt(ind.dataset.slide));
        });
    });
    
    // Auto-play (optional)
    let autoPlay = setInterval(() => goToSlide(currentSlide + 1), 6000);
    
    // Pause on hover
    track.addEventListener('mouseenter', () => clearInterval(autoPlay));
    track.addEventListener('mouseleave', () => {
        autoPlay = setInterval(() => goToSlide(currentSlide + 1), 6000);
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if(e.key === 'ArrowLeft') goToSlide(currentSlide - 1);
        if(e.key === 'ArrowRight') goToSlide(currentSlide + 1);
    });


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
    
    let activeFilters = {};
    
    function getFilterValues() {
        return {
            search: searchInput.value.toLowerCase().trim(),
            category: categoryFilter.value,
            type: typeFilter.value
        };
    }
    
    function updateActiveFiltersDisplay() {
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
        
        // Clear button listeners
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
        activeFilters = filters;
        updateActiveFiltersDisplay();
        
        let visibleCount = 0;
        
        cards.forEach(card => {
            const title = card.querySelector('.card-title').textContent.toLowerCase();
            const tags = Array.from(card.querySelectorAll('.card-tag')).map(t => t.textContent.toLowerCase());
            const category = card.dataset.category;
            const type = card.dataset.type;
            
            const matchesSearch = !filters.search || 
                title.includes(filters.search) || 
                tags.some(t => t.includes(filters.search));
            
            const matchesCategory = !filters.category || category === filters.category;
            const matchesType = !filters.type || type === filters.type;
            
            const show = matchesSearch && matchesCategory && matchesType;
            card.style.display = show ? 'flex' : 'none';
            
            if(show) visibleCount++;
        });
        
        // Show/hide empty state
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        catalogGrid.style.display = visibleCount === 0 ? 'none' : 'grid';
        
        // Dispatch event for external listeners
        window.dispatchEvent(new CustomEvent('catalog:filtered', { detail: { filters, visibleCount } }));
    }
    
    function resetFilters() {
        searchInput.value = '';
        categoryFilter.value = '';
        typeFilter.value = '';
        activeFilters = {};
        updateActiveFiltersDisplay();
        
        cards.forEach(card => card.style.display = 'flex');
        emptyState.style.display = 'none';
        catalogGrid.style.display = 'grid';
    }
    
    // Event listeners
    applyBtn.addEventListener('click', applyFilters);
    resetBtn.addEventListener('click', resetFilters);
    document.getElementById('emptyResetBtn')?.addEventListener('click', resetFilters);
    
    // Real-time search with debounce
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    });
    
    // Enter key to apply
    searchInput.addEventListener('keypress', (e) => {
        if(e.key === 'Enter') {
            e.preventDefault();
            applyFilters();
        }
    });
    
    // Initialize
    updateActiveFiltersDisplay();


    // =========================
    // 📊 COUNTER ANIMATION
    // =========================
    function animateCounter(element, target, duration = 1500) {
        const start = 0;
        const increment = target / (duration / 16); // ~60fps
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
        
        step();
    }
    
    // Trigger animation when stats section is visible
    const statsSection = document.querySelector('.stats-section');
    const statValues = document.querySelectorAll('.stat-value');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                statValues.forEach(el => {
                    const target = parseInt(el.dataset.target);
                    if(!el.classList.contains('animated')) {
                        el.classList.add('animated', 'animate');
                        animateCounter(el, target);
                    }
                });
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    
    if(statsSection) observer.observe(statsSection);


    // =========================
    // 🎴 CARD INTERACTIONS
    // =========================
    // View button - navigate to map
    document.querySelectorAll('.card-view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.catalog-card');
            const title = card.querySelector('.card-title').textContent;
            
            // Show feedback
            this.textContent = 'Memuat...';
            this.disabled = true;
            
            // Simulate navigation (replace with actual route)
            setTimeout(() => {
                // window.location.href = `/geo?layer=${encodeURIComponent(title)}`;
                alert(`🗺️ Membuka peta: ${title}\n\n(Integrasi dengan route geoportal)`);
                this.textContent = 'View Map →';
                this.disabled = false;
            }, 800);
        });
    });
    
    // Preview button - open modal or preview pane
    document.querySelectorAll('.card-action-btn.primary').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const card = this.closest('.catalog-card');
            const title = card.querySelector('.card-title').textContent;
            const img = card.querySelector('img').src;
            
            // Simple preview notification (replace with modal)
            showNotification(`👁️ Preview: ${title}`, 'info');
        });
    });
    
    // Download button
    document.querySelectorAll('.card-action-btn:not(.primary)').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            showNotification('⬇️ Memulai unduhan...', 'success');
            // Add actual download logic here
        });
    });


    // =========================
    // 🔔 NOTIFICATION HELPER
    // =========================
    function showNotification(message, type = 'info') {
        // Remove existing
        document.getElementById('catalog-notif')?.remove();
        
        const colors = {
            success: 'bg-green-600',
            error: 'bg-red-600',
            info: 'bg-gray-800'
        };
        
        const icons = { success: '✅', error: '⚠️', info: 'ℹ️' };
        
        const notif = document.createElement('div');
        notif.id = 'catalog-notif';
        notif.className = `fixed bottom-6 right-6 px-5 py-3 rounded-xl shadow-2xl z-50 text-white text-sm font-medium ${colors[type]} flex items-center gap-3 animate-[slideIn_0.3s_ease]`;
        notif.innerHTML = `<span class="text-lg">${icons[type]}</span><span>${message}</span>`;
        
        document.body.appendChild(notif);
        
        // Auto remove
        setTimeout(() => {
            notif.style.opacity = '0';
            notif.style.transform = 'translateY(10px)';
            notif.style.transition = 'all 0.3s ease';
            setTimeout(() => notif.remove(), 300);
        }, 2500);
    }
    
    // Add slideIn animation keyframes if not exists
    if(!document.querySelector('#notif-styles')) {
        const style = document.createElement('style');
        style.id = 'notif-styles';
        style.textContent = `@keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); }}`;
        document.head.appendChild(style);
    }


    // =========================
    // ♿ KEYBOARD SHORTCUTS
    // =========================
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + K to focus search
        if((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        // Escape to clear search
        if(e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.blur();
            if(searchInput.value) {
                searchInput.value = '';
                applyFilters();
            }
        }
    });
    
    // Add shortcut hint to placeholder
    searchInput.placeholder = '🔍 Cari... (Ctrl+K)';
    
});
</script>

@endsection