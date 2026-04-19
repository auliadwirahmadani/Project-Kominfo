@extends('layouts.datasetnav')

@section('title', ($dataset->metadata->title ?? $dataset->layer_name) . ' — Geoportal Bengkulu')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    .meta-badge {
        display: inline-flex; align-items: center; gap: 0.375rem;
        padding: 0.25rem 0.75rem; border-radius: 9999px;
        font-size: 0.7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .meta-row {
        display: flex; justify-content: space-between;
        align-items: flex-start; padding: 0.6rem 0;
        border-bottom: 1px solid #f3f4f6; gap: 1rem; font-size: 0.8125rem;
    }
    .meta-row:last-child { border-bottom: none; }
    .meta-label { color: #6b7280; font-weight: 600; min-width: 140px; flex-shrink: 0; }
    .meta-value  { color: #1f2937; font-weight: 500; text-align: right; word-break: break-word; }
    .info-card { background: white; border-radius: 1rem; box-shadow: 0 1px 12px rgba(0,0,0,0.08); overflow: hidden; }
    .info-card-header {
        background: #fef2f2; padding: 0.875rem 1.25rem;
        border-bottom: 1px solid #fee2e2;
        display: flex; align-items: center; gap: 0.5rem;
    }
    .info-card-header i { color: #dc2626; }
    .info-card-title { font-size: 0.875rem; font-weight: 700; color: #1f2937; }
    .info-card-body  { padding: 1rem 1.25rem; }
    .keyword-tag {
        display: inline-block; background: #fef2f2; color: #991b1b;
        border: 1px solid #fecaca; border-radius: 9999px;
        padding: 0.2rem 0.65rem; font-size: 0.7rem; font-weight: 600; margin: 0.15rem;
    }
    .data-type-badge { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .stat-pill {
        display: flex; flex-direction: column; align-items: center;
        padding: 0.75rem 1.5rem; background: rgba(255,255,255,0.12);
        border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.2);
        backdrop-filter: blur(4px); min-width: 90px;
    }
    .stat-pill-val { font-size: 1.3rem; font-weight: 800; color: #fcd34d; line-height: 1; }
    .stat-pill-lbl { font-size: 0.65rem; color: #fecaca; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem; }

    /* Peta besar */
    #big-map { height: 420px; width: 100%; }
    .map-overlay-btn {
        position: absolute; bottom: 14px; right: 14px; z-index: 999;
        display: flex; align-items: center; gap: 0.5rem;
        background: #8b0000; color: #fcd34d;
        padding: 0.6rem 1.1rem; border-radius: 9999px;
        font-size: 0.8rem; font-weight: 700;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        text-decoration: none; transition: background 0.2s;
    }
    .map-overlay-btn:hover { background: #6b0000; }
    .leaflet-control-zoom a {
        background-color: #8b0000 !important; color: #fcd34d !important;
    }
</style>


{{-- ==================== HERO HEADER ==================== --}}
<div class="bg-[#8b0000] pt-16 pb-8 px-4 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5">
        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
            </pattern>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto relative z-10">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-red-200 text-xs mb-4 opacity-80">
            <a href="{{ route('geo') }}" class="hover:text-white transition">🗺️ Peta</a>
            <span>/</span>
            <a href="{{ route('catalog') }}" class="hover:text-white transition">Katalog</a>
            <span>/</span>
            <span class="text-white font-medium truncate max-w-xs">{{ $dataset->metadata->title ?? $dataset->layer_name }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            {{-- Kiri: Judul & Badges --}}
            <div class="flex-1">
                <div class="flex flex-wrap gap-2 mb-3">
                    @if($dataset->category)
                    <span class="meta-badge bg-yellow-400 text-gray-900">
                        <i class="fas fa-layer-group text-xs"></i> {{ $dataset->category->category_name }}
                    </span>
                    @endif
                    @if($dataset->metadata?->data_type)
                    <span class="meta-badge bg-white text-red-800">
                        <i class="fas fa-database text-xs"></i> {{ strtoupper($dataset->metadata->data_type) }}
                    </span>
                    @endif
                    @if($dataset->metadata?->year)
                    <span class="meta-badge bg-red-800 text-white border border-red-600">
                        <i class="fas fa-calendar text-xs"></i> {{ $dataset->metadata->year }}
                    </span>
                    @endif
                    <span class="meta-badge bg-red-900 text-red-200 border border-red-700 uppercase">
                        <i class="fas fa-file-code text-xs"></i> {{ $dataset->file_type ?? 'file' }}
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-yellow-400 mb-2 leading-tight">
                    {{ $dataset->metadata->title ?? $dataset->layer_name }}
                </h1>
                <p class="text-red-100 text-sm flex items-center gap-2">
                    <i class="fas fa-building"></i>
                    {{ $dataset->metadata?->organization ?? 'Pemerintah Provinsi Bengkulu' }}
                </p>
            </div>

            {{-- Kanan: Stat Pills --}}
            <div class="flex flex-wrap gap-3 shrink-0">
                <div class="stat-pill">
                    <div class="stat-pill-val">{{ $dataset->metadata?->year ?? $dataset->created_at?->format('Y') ?? '-' }}</div>
                    <div class="stat-pill-lbl">Tahun</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-val">{{ $dataset->file_size ? number_format($dataset->file_size/1024, 0) : '-' }}</div>
                    <div class="stat-pill-lbl">KB</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-val">{{ strtoupper($dataset->metadata?->crs ?? 'WGS84') }}</div>
                    <div class="stat-pill-lbl">Proyeksi</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ==================== PETA INTERAKTIF BESAR ==================== --}}
<div class="relative shadow-xl border-y-4 border-[#6b0000]">
    <div id="big-map"></div>
    <a href="{{ route('geo', ['layer_id' => $dataset->geospatial_id]) }}" class="map-overlay-btn">
        <i class="fas fa-expand-arrows-alt"></i> Buka Peta Full Screen
    </a>
    {{-- Label --}}
    <div class="absolute top-3 left-14 z-[999] bg-[#8b0000]/90 text-yellow-400 text-xs font-bold px-3 py-1.5 rounded-full shadow backdrop-blur-sm">
        <i class="fas fa-map-marker-alt mr-1"></i> {{ $dataset->metadata?->title ?? $dataset->layer_name }}
    </div>
</div>

{{-- ==================== MAIN CONTENT ==================== --}}
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- ===== QUICK INFO BAR ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                <i class="fas fa-tags text-red-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">Kategori</p>
                <p class="text-sm font-bold text-gray-800 truncate">{{ $dataset->category?->category_name ?? '-' }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                <i class="fas fa-ruler-combined text-red-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">Skala</p>
                <p class="text-sm font-bold text-gray-800">{{ $dataset->metadata?->scale ?? '-' }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                <i class="fas fa-globe text-red-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">CRS</p>
                <p class="text-sm font-bold text-gray-800">{{ $dataset->metadata?->crs ?? 'EPSG:4326' }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-check text-red-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">Publikasi</p>
                <p class="text-sm font-bold text-gray-800">
                    @if($dataset->metadata?->publication_date)
                        {{ \Carbon\Carbon::parse($dataset->metadata->publication_date)->format('M Y') }}
                    @else {{ $dataset->created_at?->format('M Y') ?? '-' }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ===== GRID UTAMA ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== KOLOM KIRI (1/3) ===== --}}
        <div class="space-y-5">

            {{-- Info Teknis --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-cogs"></i>
                    <span class="info-card-title">Informasi Teknis</span>
                </div>
                <div class="info-card-body space-y-0">
                    <div class="meta-row">
                        <span class="meta-label"><i class="fas fa-fingerprint mr-1 text-red-300"></i>Identifier</span>
                        <span class="meta-value font-mono text-xs">{{ $dataset->metadata?->identifier ?? '-' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fas fa-globe mr-1 text-red-300"></i>CRS / Proyeksi</span>
                        <span class="meta-value">{{ $dataset->metadata?->crs ?? 'EPSG:4326' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fas fa-ruler-combined mr-1 text-red-300"></i>Skala</span>
                        <span class="meta-value">{{ $dataset->metadata?->scale ?? '-' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fas fa-database mr-1 text-red-300"></i>Tipe Data</span>
                        <span class="meta-value">
                            @if($dataset->metadata?->data_type)
                                <span class="keyword-tag data-type-badge">{{ $dataset->metadata->data_type }}</span>
                            @else - @endif
                        </span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fas fa-file-code mr-1 text-red-300"></i>Format File</span>
                        <span class="meta-value uppercase font-mono">{{ $dataset->file_type ?? '-' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fas fa-hdd mr-1 text-red-300"></i>Ukuran File</span>
                        <span class="meta-value">
                            @if($dataset->file_size)
                                {{ number_format($dataset->file_size / 1024, 1) }} KB
                            @else - @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Distribusi & Layanan --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-share-alt"></i>
                    <span class="info-card-title">Distribusi & Layanan</span>
                </div>
                <div class="info-card-body space-y-0">
                    <div class="meta-row">
                        <span class="meta-label">Protokol</span>
                        <span class="meta-value">{{ $dataset->metadata?->distribution_protocol ?? '-' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Layer Service</span>
                        <span class="meta-value font-mono text-xs">{{ $dataset->metadata?->layer_name_service ?? '-' }}</span>
                    </div>
                    <div class="meta-row flex-col items-start gap-1" style="padding-bottom:0.75rem;">
                        <span class="meta-label w-full">URL Distribusi</span>
                        @if($dataset->metadata?->distribution_url)
                            <a href="{{ $dataset->metadata->distribution_url }}" target="_blank"
                               class="text-red-700 underline underline-offset-2 hover:text-red-900 text-xs break-all mt-1">
                                {{ $dataset->metadata->distribution_url }}
                            </a>
                        @else
                            <span class="meta-value">-</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="space-y-2">
                @if($dataset->file_path)
                <a href="{{ route('admin.geospatial.download', $dataset->geospatial_id) }}"
                   class="flex items-center justify-center gap-2 w-full py-3 px-4 bg-[#8b0000] hover:bg-[#6b0000] text-yellow-400 font-bold rounded-xl text-sm shadow-md transition-colors">
                    <i class="fas fa-download"></i> Unduh Dataset
                </a>
                @endif
                <a href="{{ route('catalog') }}"
                   class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-white hover:bg-gray-50 text-gray-800 font-semibold rounded-xl text-sm shadow border border-gray-200 transition-colors">
                    <i class="fas fa-th-large text-red-600"></i> Kembali ke Katalog
                </a>
                <a href="{{ route('geo') }}"
                   class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-white hover:bg-gray-50 text-gray-800 font-semibold rounded-xl text-sm shadow border border-gray-200 transition-colors">
                    <i class="fas fa-map text-red-600"></i> Kembali ke Peta
                </a>
            </div>
        </div>

        {{-- ===== KOLOM KANAN (2/3) ===== --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Abstrak --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-align-left"></i>
                    <span class="info-card-title">Abstrak / Deskripsi</span>
                </div>
                <div class="info-card-body">
                    <p class="text-sm text-gray-600 leading-relaxed text-justify">
                        {{ $dataset->metadata?->abstract ?? $dataset->description ?? 'Deskripsi belum tersedia.' }}
                    </p>
                </div>
            </div>

            {{-- Informasi Umum + Kata Kunci dalam 2 kolom --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Informasi Umum --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-building"></i>
                        <span class="info-card-title">Informasi Umum</span>
                    </div>
                    <div class="info-card-body space-y-0">
                        <div class="meta-row flex-col items-start gap-0.5">
                            <span class="meta-label">Instansi / Organisasi</span>
                            <span class="text-gray-800 text-sm font-semibold">{{ $dataset->metadata?->organization ?? '-' }}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Sumber Data</span>
                            <span class="meta-value">{{ $dataset->metadata?->source ?? '-' }}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Tahun Data</span>
                            <span class="meta-value">{{ $dataset->metadata?->year ?? '-' }}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Tgl. Publikasi</span>
                            <span class="meta-value">
                                @if($dataset->metadata?->publication_date)
                                    {{ \Carbon\Carbon::parse($dataset->metadata->publication_date)->translatedFormat('d F Y') }}
                                @else - @endif
                            </span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Ditambahkan</span>
                            <span class="meta-value">{{ $dataset->created_at?->translatedFormat('d F Y') ?? '-' }}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Diperbarui</span>
                            <span class="meta-value">{{ $dataset->updated_at?->translatedFormat('d F Y') ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Kata Kunci + Status --}}
                <div class="space-y-5">
                    @if($dataset->metadata?->keywords)
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="fas fa-key"></i>
                            <span class="info-card-title">Kata Kunci</span>
                        </div>
                        <div class="info-card-body">
                            @foreach(explode(',', $dataset->metadata->keywords) as $kw)
                                <span class="keyword-tag">{{ trim($kw) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Status Publikasi --}}
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="fas fa-check-circle"></i>
                            <span class="info-card-title">Status Data</span>
                        </div>
                        <div class="info-card-body space-y-0">
                            <div class="meta-row">
                                <span class="meta-label">Status Verifikasi</span>
                                <span class="meta-value">
                                    @php $sv = $dataset->status_verifikasi; @endphp
                                    <span class="keyword-tag {{ $sv === 'approved' ? 'data-type-badge' : 'bg-yellow-50 text-yellow-700 border-yellow-200' }}">
                                        {{ $sv === 'approved' ? '✅ Disetujui' : ucfirst($sv) }}
                                    </span>
                                </span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Dipublikasikan</span>
                                <span class="meta-value">
                                    <span class="keyword-tag {{ $dataset->is_published ? 'data-type-badge' : 'bg-red-50 text-red-700 border-red-200' }}">
                                        {{ $dataset->is_published ? '✅ Ya' : '❌ Belum' }}
                                    </span>
                                </span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">ID Dataset</span>
                                <span class="meta-value font-mono text-xs">{{ $dataset->geospatial_id }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- end 2-col nested --}}

        </div>{{-- end kolom kanan --}}
    </div>{{-- end grid utama --}}
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {

    // ================================================
    // PETA BESAR
    // ================================================
    const bigMapEl = document.getElementById('big-map');
    if (bigMapEl) {
        const bigMap = L.map('big-map', {
            zoomControl: true,
            attributionControl: false,
            scrollWheelZoom: false
        }).setView([-3.8, 102.3], 8);

        // Tile layers
        const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 });
        const carto = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 });
        const sat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            subdomains: ['mt0','mt1','mt2','mt3'], maxZoom: 20
        });
        carto.addTo(bigMap);

        L.control.layers({
            "🗺️ Peta Dasar": carto,
            "🛣️ OpenStreetMap": osm,
            "🛰️ Satelit": sat
        }, null, { position: 'topright' }).addTo(bigMap);

        try {
            const res = await fetch(`/geospatial/{{ $dataset->geospatial_id }}/geojson`);
            if (!res.ok) throw new Error('Not found');
            const data = await res.json();

            let geoLayer;
            if (data.is_shapefile) {
                let geojson = await shp(data.url);
                if (Array.isArray(geojson)) geojson = geojson[0];
                data = geojson; // Override data we render
            }
            
            geoLayer = L.geoJSON(data, {
                onEachFeature: function(feature, layer) {
                    const p = feature.properties || {};
                    const name = p.NAMOBJ || p.Name || p.name || p.NAMA || '—';
                    if (name !== '—') {
                        layer.bindTooltip(name, { sticky: true, className: 'text-xs' });
                    }
                }
            });

            geoLayer.setStyle({
                color: '#8b0000', weight: 2,
                fillOpacity: 0.2, fillColor: '#dc2626'
            }).addTo(bigMap);

            if (geoLayer.getLayers().length > 0) {
                bigMap.fitBounds(geoLayer.getBounds(), { padding: [30, 30] });
            }
        } catch(e) {
            console.warn('Big map error:', e);
            bigMapEl.innerHTML = '<div class="w-full h-full flex items-center justify-center text-gray-400 text-sm bg-gray-50 font-medium">Preview peta tidak tersedia</div>';
        }
    }
});
</script>
@endpush