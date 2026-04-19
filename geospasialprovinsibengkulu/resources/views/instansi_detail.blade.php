@extends('layouts.datasetnav')

@section('title', ($instansi->profile->instansi ?? $instansi->name) . ' - Geoportal Bengkulu')

@section('content')

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    :root {
        --primary: #ef4444;
        --primary-dark: #dc2626;
        --secondary: #1f2937;
        --radius: 16px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .work-sans { font-family: 'Work Sans', sans-serif; }
    
    /* Header hero */
    .instansi-hero {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        position: relative;
        overflow: hidden;
    }
    .instansi-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url('https://www.transparenttextures.com/patterns/cubes.png');
        opacity: 0.1;
    }
    
    /* Card Styles adapted from Catalog */
    .catalog-card {
        background: white;
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #fecaca;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .catalog-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.3);
        border-color: var(--primary);
    }
    .card-image {
        position: relative;
        height: 180px;
        background-color: #f8fafc;
    }
    div[id^="mini-map-"] .leaflet-control-container { display: none !important; }
</style>

@php
    $profile = $instansi->profile;
    $photoPath = $profile?->photo;
    $hasPhoto = $photoPath && file_exists(public_path('storage/' . $photoPath));
    $initial = strtoupper(substr($instansi->name ?? 'P', 0, 1));
@endphp

<!-- Hero Profile -->
<div class="instansi-hero text-white pt-24 pb-20 px-4 relative">
    <div class="container mx-auto max-w-5xl relative z-10 flex flex-col md:flex-row items-center gap-8">
        <div class="w-32 h-32 md:w-40 md:h-40 shrink-0 rounded-full border-4 border-white shadow-2xl overflow-hidden bg-white flex items-center justify-center">
            @if($hasPhoto)
                <img src="{{ asset('storage/' . $photoPath) }}" alt="{{ $instansi->name }}" class="w-full h-full object-cover">
            @else
                <span class="text-5xl font-bold text-red-500">{{ $initial }}</span>
            @endif
        </div>
        <div class="text-center md:text-left">
            <h1 class="text-3xl md:text-4xl font-extrabold mb-2">{{ $profile?->instansi ?? $instansi->name }}</h1>
            <p class="text-red-100 text-lg max-w-2xl">{{ $profile?->bio ?? 'Penyedia data geospasial resmi Provinsi Bengkulu.' }}</p>
            <div class="mt-6 inline-flex gap-4">
                <div class="bg-black/20 rounded-lg px-4 py-2 backdrop-blur-sm">
                    <span class="block text-2xl font-bold">{{ $datasets->total() }}</span>
                    <span class="text-xs text-red-100 uppercase tracking-wider">Total Dataset Peta</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dataset Section -->
<div class="container mx-auto px-4 py-16 max-w-7xl">
    <div class="flex justify-between items-center mb-8 border-b border-gray-200 pb-4">
        <h2 class="text-2xl font-bold text-gray-800">Katalog Peta Instansi</h2>
        <a href="{{ route('catalog') }}" class="text-sm font-semibold text-red-600 hover:text-red-800">← Kembali ke Katalog Publik</a>
    </div>

    @if($datasets->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($datasets as $data)
            <article class="catalog-card">
                {{-- Map Preview --}}
                <div class="card-image">
                    <div class="absolute top-3 left-3 z-10">
                        <span class="px-3 py-1 bg-white text-red-600 font-bold text-xs rounded-full shadow-sm">
                            {{ strtoupper(substr($data->category->category_name ?? 'Peta', 0, 15)) }}
                        </span>
                    </div>
                    
                    <div id="mini-map-{{ $data->geospatial_id }}" class="w-full h-full"></div>
                    
                    <div id="loader-{{ $data->geospatial_id }}" class="absolute inset-0 flex items-center justify-center bg-gray-50 z-20">
                        <svg class="animate-spin h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Card Content --}}
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="text-lg font-bold text-gray-800 line-clamp-2" title="{{ $data->metadata->title ?? $data->layer_name }}">
                        {{ $data->metadata->title ?? $data->layer_name }}
                    </h3>
                    <p class="text-xs text-gray-400 font-mono mt-1 mb-4">ID: {{ $data->geospatial_id }}</p>

                    {{-- Badge Tahun & Tipe Data --}}
                    <div class="flex flex-wrap gap-2 mt-1 mb-4">
                        {{-- Badge Tahun --}}
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                              style="background:#fef3c7; color:#92400e; border: 1px solid #fcd34d;">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $data->metadata->year ?? $data->created_at->format('Y') }}
                        </span>

                        {{-- Badge Tipe Data --}}
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                              style="background:#ede9fe; color:#5b21b6; border: 1px solid #c4b5fd;">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            {{ $data->metadata->data_type ?? 'Vektor' }}
                        </span>
                    </div>

                    <a href="{{ route('dataset.show', ['id' => $data->geospatial_id]) }}"
                       class="block w-full py-2.5 px-4 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-lg text-center mb-2 transition-colors">
                        Lihat Detail Dataset
                    </a>
                    
                    <a href="{{ route('geo', ['layer_id' => $data->geospatial_id]) }}" 
                       class="block w-full py-2.5 px-4 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-lg text-center transition-colors">
                        Buka Peta Full Screen
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $datasets->links() }}
        </div>
    @else
        <div class="text-center py-20 bg-gray-50 rounded-2xl border border-gray-100">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Publikasi</h3>
            <p class="text-gray-500">Instansi ini belum mempublikasikan data geospasial apa pun.</p>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<!-- Leaflet & SHP JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
            console.error(`Preview gagal:`, error);
            if (loader) loader.innerHTML = '<span class="text-xs text-gray-400">Map Preview Tidak Tersedia</span>';
        }
    }
});
</script>
@endpush
