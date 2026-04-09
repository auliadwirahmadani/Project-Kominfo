@extends('layouts.verifikator.verifikatornav')
@section('title', 'Periksa Metadata')
@section('page-title', 'Periksa Metadata')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ============================================================
       PERIKSA METADATA — DESIGN SYSTEM
    ============================================================ */
    .card-meta {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px -4px rgba(0,0,0,0.07);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.25s cubic-bezier(0.4,0,0.2,1), box-shadow 0.25s;
    }
    .card-meta:hover { transform: translateY(-3px); box-shadow: 0 14px 36px -6px rgba(124,58,237,0.12); }

    .status-stripe { height: 3px; width: 100%; }
    .status-stripe.pending  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .status-stripe.approved { background: linear-gradient(90deg, #10b981, #34d399); }
    .status-stripe.rejected { background: linear-gradient(90deg, #ef4444, #f87171); }

    .mini-map-meta { height: 160px; width: 100%; }
    .mini-map-meta .leaflet-control-container { display: none !important; }
    .map-loader-meta {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        background: #f8fafc; z-index: 20;
    }

    .meta-row { display: flex; justify-content: space-between; gap: 8px; padding: 5px 0; border-bottom: 1px solid #f3f4f6; }
    .meta-row:last-child { border-bottom: none; }
    .meta-label { color: #6b7280; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .meta-val { color: #1f2937; font-size: 11px; font-weight: 600; text-align: right;
                overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 62%; }

    .meta-badge { display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: 700; }
    .meta-badge.pending  { background: #fef3c7; color: #92400e; }
    .meta-badge.approved { background: #d1fae5; color: #065f46; }
    .meta-badge.rejected { background: #fee2e2; color: #991b1b; }

    /* Detail metadata popup map */
    #detail-map-meta { height: 250px; width: 100%; border-radius: 12px; }

    /* Section divider */
    .meta-section { background: #faf5ff; border: 1px solid #ede9fe; border-radius: 14px; padding: 16px; }
    .meta-section-title { font-size: 10px; font-weight: 800; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 10px; display: flex; align-items: center; gap: 5px; }

    /* Detail field row */
    .dfield { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; padding: 5px 0; border-bottom: 1px solid #ede9fe; font-size: 12px; }
    .dfield:last-child { border-bottom: none; }
    .dfield-label { color: #6b7280; font-weight: 600; white-space: nowrap; flex-shrink: 0; }
    .dfield-value { color: #1f2937; font-weight: 700; text-align: right; word-break: break-word; max-width: 65%; }

    .modal-scroll::-webkit-scrollbar { width: 5px; }
    .modal-scroll::-webkit-scrollbar-thumb { background: #ddd6fe; border-radius: 3px; }

    .stat-pill { display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: white;
                 border-radius: 14px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px -2px rgba(0,0,0,0.05); }

    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .anim-card { animation: fadeUp 0.4s ease-out both; }
</style>
@endpush

@section('content')
<div x-data="metadataManager()" class="bg-gradient-to-br from-violet-50/60 via-white to-purple-50/60 min-h-screen p-4 md:p-8">
<div class="max-w-7xl mx-auto space-y-6">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 anim-card">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Verifikasi Metadata Dataset</h1>
            <p class="text-sm text-gray-500 mt-1">Periksa kelengkapan metadata, lihat preview peta, lalu buat keputusan verifikasi.</p>
        </div>
        <div class="flex items-center gap-2 bg-gray-50 p-1.5 rounded-xl border border-gray-200 w-full md:w-auto">
            <div class="relative flex-1 md:flex-none">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" placeholder="Cari judul / organisasi..."
                    class="pl-8 pr-3 py-2 w-full md:w-64 bg-white rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 transition text-gray-700" />
            </div>
            <select x-model="statusFilter"
                class="px-3 py-2 bg-white rounded-lg border border-gray-200 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-violet-400 transition">
                <option value="">Semua</option>
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
            </select>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl flex items-center gap-3 anim-card">
        <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="font-semibold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    {{-- STATS PILLS --}}
    @php
        $cntPending  = $metadatas->filter(fn($m) => ($m->geospatial->status_verifikasi ?? '') === 'pending')->count();
        $cntApproved = $metadatas->filter(fn($m) => ($m->geospatial->status_verifikasi ?? '') === 'approved')->count();
        $cntRejected = $metadatas->filter(fn($m) => ($m->geospatial->status_verifikasi ?? '') === 'rejected')->count();
    @endphp
    <div class="flex flex-wrap gap-3">
        <div class="stat-pill">
            <div class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div><p class="text-lg font-black text-violet-600">{{ $metadatas->total() }}</p><p class="text-[11px] text-gray-500">Total</p></div>
        </div>
        <div class="stat-pill">
            <div class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><p class="text-lg font-black text-amber-500">{{ $cntPending }}</p><p class="text-[11px] text-gray-500">Menunggu</p></div>
        </div>
        <div class="stat-pill">
            <div class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><p class="text-lg font-black text-emerald-500">{{ $cntApproved }}</p><p class="text-[11px] text-gray-500">Disetujui</p></div>
        </div>
        <div class="stat-pill">
            <div class="w-7 h-7 bg-red-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><p class="text-lg font-black text-red-500">{{ $cntRejected }}</p><p class="text-[11px] text-gray-500">Ditolak</p></div>
        </div>
    </div>

    {{-- ============================================================
         CARD GRID
    ============================================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($metadatas as $i => $meta)
        @php
            $status    = $meta->geospatial->status_verifikasi ?? 'pending';
            $published = $meta->geospatial->is_published ?? false;
            $layerName = $meta->geospatial->layer_name ?? 'Dataset Tanpa Nama';
            $catName   = $meta->geospatial->category->category_name ?? 'Tanpa Kategori';
            $geoId     = $meta->geospatial_id;
        @endphp

        <div class="card-meta anim-card"
             style="animation-delay: {{ $i * 0.04 }}s"
             x-show="
                (search === '' || '{{ strtolower($meta->title ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($meta->organization ?? '') }}'.includes(search.toLowerCase())) &&
                (statusFilter === '' || statusFilter === '{{ $status }}')
             "
             x-transition>

            {{-- Status stripe --}}
            <div class="status-stripe {{ $status }}"></div>

            {{-- Mini Map --}}
            <div class="relative" style="height: 160px; background: #f8fafc;">
                <div id="mmap-{{ $geoId }}" class="mini-map-meta"></div>
                <div id="mloader-{{ $geoId }}" class="map-loader-meta">
                    <svg class="animate-spin h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                {{-- Badges --}}
                <div class="absolute top-2.5 left-2.5 z-30 flex flex-wrap gap-1.5">
                    <span class="meta-badge {{ $status }}">
                        @if($status === 'approved') ✓ Disetujui
                        @elseif($status === 'rejected') ✗ Ditolak
                        @else ⏱ Menunggu
                        @endif
                    </span>
                    @if($published)
                    <span class="text-[10px] font-bold text-blue-700 bg-blue-100/95 px-2 py-0.5 rounded-full">📡 Publish</span>
                    @endif
                </div>
                <span class="absolute top-2.5 right-2.5 z-30 text-[10px] text-white/80 bg-black/40 px-2 py-0.5 rounded-full font-mono">#{{ $meta->metadata_id }}</span>
            </div>

            {{-- Card Body --}}
            <div class="p-4 flex-1 flex flex-col gap-3">
                <div>
                    <h3 class="text-[15px] font-bold text-gray-800 leading-tight line-clamp-2" title="{{ $meta->title }}">
                        {{ $meta->title ?? 'Judul tidak tersedia' }}
                    </h3>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <span class="text-[11px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded-md">{{ $catName }}</span>
                        @if($meta->data_type)
                        <span class="text-[11px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md">{{ $meta->data_type }}</span>
                        @endif
                    </div>
                </div>

                {{-- Metadata Summary --}}
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <div class="meta-row">
                        <span class="meta-label">Layer Dataset</span>
                        <span class="meta-val" title="{{ $layerName }}">{{ $layerName }}</span>
                    </div>
                    @if($meta->organization)
                    <div class="meta-row">
                        <span class="meta-label">Organisasi</span>
                        <span class="meta-val">{{ $meta->organization }}</span>
                    </div>
                    @endif
                    @if($meta->year)
                    <div class="meta-row">
                        <span class="meta-label">Tahun</span>
                        <span class="meta-val">{{ $meta->year }}</span>
                    </div>
                    @endif
                    @if($meta->crs)
                    <div class="meta-row">
                        <span class="meta-label">CRS</span>
                        <span class="meta-val font-mono">{{ $meta->crs }}</span>
                    </div>
                    @endif
                    @if($meta->scale)
                    <div class="meta-row">
                        <span class="meta-label">Skala</span>
                        <span class="meta-val">{{ $meta->scale }}</span>
                    </div>
                    @endif
                </div>

                @if($meta->abstract)
                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ $meta->abstract }}</p>
                @endif

                @if($meta->keywords)
                <div class="flex flex-wrap gap-1">
                    @foreach(array_slice(explode(',', $meta->keywords), 0, 4) as $kw)
                    <span class="text-[10px] bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full font-medium">{{ trim($kw) }}</span>
                    @endforeach
                    @if(count(explode(',', $meta->keywords)) > 4)
                    <span class="text-[10px] text-gray-400 self-center">+{{ count(explode(',', $meta->keywords)) - 4 }} lagi</span>
                    @endif
                </div>
                @endif

                {{-- ACTION BUTTONS --}}
                <div class="space-y-2 mt-auto pt-1">
                    {{-- Keputusan --}}
                    <button type="button"
                        @click="openModal({{ $meta->metadata_id }}, '{{ addslashes($meta->title ?? 'Metadata') }}', '{{ $status }}')"
                        class="w-full py-2.5 flex justify-center items-center gap-2 bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white rounded-xl font-semibold shadow-md shadow-violet-200/50 transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Tindak Lanjut & Keputusan
                    </button>

                    {{-- Detail Metadata --}}
                    @php
                        $detailMetaData = [
                            "metadata_id"   => $meta->metadata_id,
                            "geo_id"        => $geoId,
                            "title"         => $meta->title ?? "",
                            "layer_name"    => $layerName,
                            "category"      => $catName,
                            "status"        => $status,
                            "is_published"  => $published,
                            "identifier"    => $meta->identifier ?? "",
                            "organization"  => $meta->organization ?? "",
                            "year"          => (string)($meta->year ?? ""),
                            "crs"           => $meta->crs ?? "",
                            "scale"         => $meta->scale ?? "",
                            "data_type"     => $meta->data_type ?? "",
                            "source"        => $meta->source ?? "",
                            "abstract"      => $meta->abstract ?? "",
                            "keywords"      => $meta->keywords ?? "",
                            "pub_date"      => $meta->publication_date ?? "",
                            "dist_protocol" => $meta->distribution_protocol ?? "",
                            "dist_url"      => $meta->distribution_url ?? "",
                            "layer_service" => $meta->layer_name_service ?? "",
                            "preview_img"   => $meta->preview_image ?? "",
                        ];
                    @endphp
                    <button type="button"
                        @click='openMetaDetail(@json($detailMetaData))'
                        class="w-full py-2 flex justify-center items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-violet-50 hover:text-violet-700 hover:border-violet-200 rounded-xl text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Lihat Detail Metadata Lengkap
                    </button>
                </div>
            </div>
        </div>

        @empty
        <div class="col-span-full py-20 text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-violet-50 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-700">Belum ada metadata</h3>
            <p class="text-sm text-gray-400 mt-1">Saat ini belum ada metadata yang perlu diperiksa.</p>
        </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if($metadatas->hasPages())
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        {{ $metadatas->links() }}
    </div>
    @endif

</div>

{{-- ================================================================
     MODAL 1 — KEPUTUSAN VERIFIKASI METADATA
================================================================ --}}
<div x-show="isModalOpen"
     style="display: none;"
     @click.self="closeModal()"
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">

    <div x-show="isModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col">

        <div class="bg-gradient-to-r from-violet-600 to-purple-700 p-5 text-white text-center shrink-0 relative rounded-t-2xl">
            <button type="button" @click="closeModal()"
                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <h3 class="text-lg font-bold flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Keputusan Verifikasi Metadata
            </h3>
            <p class="text-violet-200 mt-1 text-xs font-medium line-clamp-1" x-text="activeMetadataTitle"></p>
        </div>

        <form :action="'{{ url('verifikator/metadata') }}/' + activeMetadataId + '/verify'" method="POST"
              class="overflow-y-auto flex-1 p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-3">Keputusan <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-2.5">
                    <label class="relative flex flex-col items-center justify-center p-3.5 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition shadow-sm">
                        <input type="radio" name="status_verifikasi" value="approved" class="sr-only peer" x-model="statusForm" required>
                        <div class="w-11 h-11 bg-green-100 rounded-full flex items-center justify-center mb-2 text-green-600 transition-colors peer-checked:bg-green-500 peer-checked:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="font-bold text-sm text-gray-700 peer-checked:text-green-700">Terima</span>
                        <span class="text-[10px] text-gray-400 mt-0.5 font-medium">(Auto Publish)</span>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-green-500 rounded-xl pointer-events-none transition-all"></div>
                    </label>
                    <label class="relative flex flex-col items-center justify-center p-3.5 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition shadow-sm">
                        <input type="radio" name="status_verifikasi" value="rejected" class="sr-only peer" x-model="statusForm">
                        <div class="w-11 h-11 bg-red-100 rounded-full flex items-center justify-center mb-2 text-red-600 transition-colors peer-checked:bg-red-500 peer-checked:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <span class="font-bold text-sm text-gray-700 peer-checked:text-red-700">Tolak</span>
                        <span class="text-[10px] text-gray-400 mt-0.5 font-medium">(Batal Publish)</span>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-red-500 rounded-xl pointer-events-none transition-all"></div>
                    </label>
                    <label class="relative flex flex-col items-center justify-center p-3.5 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition shadow-sm">
                        <input type="radio" name="status_verifikasi" value="pending" class="sr-only peer" x-model="statusForm">
                        <div class="w-11 h-11 bg-yellow-100 rounded-full flex items-center justify-center mb-2 text-yellow-600 transition-colors peer-checked:bg-yellow-400 peer-checked:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="font-bold text-sm text-gray-700 peer-checked:text-yellow-700">Tunda</span>
                        <span class="text-[10px] text-gray-400 mt-0.5 font-medium">(Perlu Revisi)</span>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-yellow-400 rounded-xl pointer-events-none transition-all"></div>
                    </label>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Catatan <span class="text-xs font-normal text-gray-400">(Opsional)</span></label>
                <p class="text-[11px] text-gray-500 mb-2">Tambahkan catatan, alasan penolakan, atau permintaan revisi untuk produsen.</p>
                <textarea name="catatan" rows="3"
                    placeholder="Contoh: Metadata belum lengkap, mohon tambahkan informasi sumber data..."
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none text-sm text-gray-700 placeholder-gray-400 resize-none bg-white transition-shadow"></textarea>
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" @click="closeModal()"
                    class="px-5 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 font-bold hover:bg-gray-100 transition text-sm">Batalkan</button>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 text-white font-bold hover:from-violet-700 hover:to-purple-700 shadow-lg shadow-violet-200 flex items-center gap-2 transition text-sm">
                    Simpan Keputusan
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================
     MODAL 2 — DETAIL METADATA LENGKAP
================================================================ --}}
<div x-show="isMetaDetailOpen"
     style="display: none;"
     @click.self="closeMetaDetail()"
     @keydown.escape.window="if(isMetaDetailOpen) closeMetaDetail()"
     class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/55 backdrop-blur-sm p-4">

    <div x-show="isMetaDetailOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 shrink-0">
            <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base font-bold text-gray-800 truncate" x-text="dMeta.title || 'Detail Metadata'"></h3>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    <span class="text-xs text-gray-400" x-text="dMeta.layer_name"></span>
                    <span x-show="dMeta.is_published" class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-full">📡 Dipublikasi</span>
                    <span x-html="dMetaBadge()"></span>
                </div>
            </div>
            <button @click="closeMetaDetail()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 modal-scroll overflow-y-auto">

            {{-- Mini Map --}}
            <div class="p-4 pb-3" x-show="dMeta.geo_id">
                <div class="relative rounded-xl overflow-hidden bg-gray-100" style="height: 250px;">
                    <div id="detail-map-meta" style="height:250px; width:100%;"></div>
                    <div id="detail-meta-map-loader" class="absolute inset-0 flex items-center justify-center bg-gray-50 z-10">
                        <div class="text-center">
                            <svg class="animate-spin h-6 w-6 text-violet-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-xs text-gray-400">Memuat peta...</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Preview Image (if any) --}}
            <template x-if="dMeta.preview_img">
            <div class="px-4 pb-3">
                <img :src="'/storage/' + dMeta.preview_img" alt="Preview" class="w-full rounded-xl border border-gray-200 object-cover max-h-40" onerror="this.style.display='none'">
            </div>
            </template>

            {{-- Content Grid --}}
            <div class="px-4 pb-4 grid grid-cols-1 md:grid-cols-2 gap-3">

                {{-- Identitas Dataset --}}
                <div class="meta-section">
                    <div class="meta-section-title">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Identitas Dataset
                    </div>
                    <div class="space-y-1">
                        <div class="dfield"><span class="dfield-label">Layer Dataset</span><span class="dfield-value" x-text="dMeta.layer_name || '—'"></span></div>
                        <div class="dfield"><span class="dfield-label">Judul Metadata</span><span class="dfield-value" x-text="dMeta.title || '—'"></span></div>
                        <div class="dfield"><span class="dfield-label">Kategori</span><span class="dfield-value text-violet-600 font-bold" x-text="dMeta.category || '—'"></span></div>
                        <div class="dfield" x-show="dMeta.identifier"><span class="dfield-label">Identifier</span><span class="dfield-value font-mono text-[10px]" x-text="dMeta.identifier"></span></div>
                    </div>
                </div>

                {{-- Informasi Teknis --}}
                <div class="meta-section">
                    <div class="meta-section-title">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Informasi Teknis
                    </div>
                    <div class="space-y-1">
                        <div class="dfield"><span class="dfield-label">Tipe Data</span><span class="dfield-value" x-text="dMeta.data_type || '—'"></span></div>
                        <div class="dfield"><span class="dfield-label">CRS / Proyeksi</span><span class="dfield-value font-mono" x-text="dMeta.crs || '—'"></span></div>
                        <div class="dfield"><span class="dfield-label">Skala</span><span class="dfield-value" x-text="dMeta.scale || '—'"></span></div>
                        <div class="dfield"><span class="dfield-label">Tahun Data</span><span class="dfield-value" x-text="dMeta.year || '—'"></span></div>
                    </div>
                </div>

                {{-- Organisasi & Sumber --}}
                <div class="meta-section">
                    <div class="meta-section-title">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Organisasi & Sumber
                    </div>
                    <div class="space-y-1">
                        <div class="dfield"><span class="dfield-label">Organisasi</span><span class="dfield-value" x-text="dMeta.organization || '—'"></span></div>
                        <div class="dfield"><span class="dfield-label">Sumber Data</span><span class="dfield-value" x-text="dMeta.source || '—'"></span></div>
                        <div class="dfield" x-show="dMeta.pub_date"><span class="dfield-label">Tgl Publikasi</span><span class="dfield-value" x-text="dMeta.pub_date"></span></div>
                    </div>
                </div>

                {{-- Distribusi --}}
                <div class="meta-section" x-show="dMeta.dist_protocol || dMeta.dist_url || dMeta.layer_service">
                    <div class="meta-section-title">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Distribusi & Layanan
                    </div>
                    <div class="space-y-1">
                        <div class="dfield" x-show="dMeta.dist_protocol"><span class="dfield-label">Protokol</span><span class="dfield-value font-mono text-[10px]" x-text="dMeta.dist_protocol"></span></div>
                        <div class="dfield" x-show="dMeta.dist_url"><span class="dfield-label">URL Distribusi</span>
                            <a :href="dMeta.dist_url" target="_blank" class="dfield-value text-violet-600 underline text-[10px] truncate" x-text="dMeta.dist_url"></a>
                        </div>
                        <div class="dfield" x-show="dMeta.layer_service"><span class="dfield-label">Nama Layer Service</span><span class="dfield-value font-mono text-[10px]" x-text="dMeta.layer_service"></span></div>
                    </div>
                </div>

                {{-- Abstract --}}
                <template x-if="dMeta.abstract">
                <div class="md:col-span-2 bg-white rounded-xl p-4 border border-gray-200">
                    <h4 class="text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">Abstrak / Deskripsi</h4>
                    <p class="text-xs text-gray-600 leading-relaxed" x-text="dMeta.abstract"></p>
                </div>
                </template>

                {{-- Keywords --}}
                <template x-if="dMeta.keywords">
                <div class="md:col-span-2 bg-white rounded-xl p-4 border border-gray-200">
                    <h4 class="text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">Kata Kunci</h4>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="kw in (dMeta.keywords || '').split(',').filter(k => k.trim())" :key="kw">
                            <span class="text-[11px] bg-violet-100 text-violet-700 px-2.5 py-0.5 rounded-full font-semibold" x-text="kw.trim()"></span>
                        </template>
                    </div>
                </div>
                </template>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3.5 border-t border-gray-100 flex justify-between items-center shrink-0">
            <button @click="closeMetaDetail()"
                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition">
                Tutup
            </button>
            <button type="button"
                @click="closeMetaDetail(); $nextTick(() => openModal(dMeta.metadata_id, dMeta.title, dMeta.status))"
                class="px-5 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-bold hover:from-violet-700 hover:to-purple-700 shadow-lg shadow-violet-200/50 flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Lanjut ke Verifikasi
            </button>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('metadataManager', () => ({
        search: '',
        statusFilter: '',

        // Verification Modal
        isModalOpen: false,
        activeMetadataId: null,
        activeMetadataTitle: '',
        statusForm: 'pending',

        openModal(id, title, currentStatus) {
            this.activeMetadataId    = id;
            this.activeMetadataTitle = title;
            this.statusForm = ['approved','rejected','pending'].includes(currentStatus) ? currentStatus : 'pending';
            this.isModalOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeModal() {
            this.isModalOpen = false;
            setTimeout(() => { this.activeMetadataId = null; this.activeMetadataTitle = ''; }, 300);
            document.body.style.overflow = '';
        },

        // Detail Metadata Modal
        isMetaDetailOpen: false,
        dMeta: {},
        _metaDetailMap: null,

        openMetaDetail(data) {
            this.dMeta = data;
            this.isMetaDetailOpen = true;
            document.body.style.overflow = 'hidden';
            if (data.geo_id) {
                this.$nextTick(() => {
                    setTimeout(() => this._initMetaDetailMap(data.geo_id), 150);
                });
            }
        },
        closeMetaDetail() {
            this.isMetaDetailOpen = false;
            if (this._metaDetailMap) { this._metaDetailMap.remove(); this._metaDetailMap = null; }
            document.body.style.overflow = '';
        },

        async _initMetaDetailMap(geoId) {
            const el     = document.getElementById('detail-map-meta');
            const loader = document.getElementById('detail-meta-map-loader');
            if (!el) return;
            if (this._metaDetailMap) { this._metaDetailMap.remove(); this._metaDetailMap = null; }

            const map = L.map('detail-map-meta', {
                zoomControl: true, attributionControl: false,
                dragging: true, scrollWheelZoom: true
            }).setView([-3.8, 102.3], 8);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            this._metaDetailMap = map;

            try {
                const res = await fetch('/geospatial/' + geoId + '/geojson');
                if (!res.ok) throw new Error();
                const data = await res.json();
                let geo;
                if (data.is_shapefile) { geo = L.geoJSON(await shp(data.url)); }
                else { geo = L.geoJSON(data); }
                geo.setStyle({ color: '#7c3aed', weight: 2.5, fillOpacity: 0.18, fillColor: '#8b5cf6' }).addTo(map);
                if (geo.getLayers().length > 0) map.fitBounds(geo.getBounds(), { padding: [20, 20] });
                if (loader) loader.style.display = 'none';
            } catch(e) {
                if (loader) loader.innerHTML = '<span class="text-sm text-gray-400">Preview peta tidak tersedia</span>';
            }
        },

        dMetaBadge() {
            const s = this.dMeta.status;
            if (s === 'approved') return '<span class="meta-badge approved">✓ Disetujui</span>';
            if (s === 'rejected') return '<span class="meta-badge rejected">✗ Ditolak</span>';
            return '<span class="meta-badge pending">⏱ Menunggu</span>';
        }
    }));
});

// Mini maps for metadata cards
document.addEventListener('DOMContentLoaded', () => {
    @foreach($metadatas as $meta)
        @if($meta->geospatial_id)
        _loadMetaMiniMap("{{ $meta->geospatial_id }}");
        @endif
    @endforeach
});

async function _loadMetaMiniMap(id) {
    const el     = document.getElementById('mmap-' + id);
    const loader = document.getElementById('mloader-' + id);
    if (!el) return;

    const map = L.map('mmap-' + id, {
        zoomControl: false, attributionControl: false,
        dragging: false, scrollWheelZoom: false, doubleClickZoom: false
    }).setView([-3.8, 102.3], 8);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    try {
        const res = await fetch('/geospatial/' + id + '/geojson');
        if (!res.ok) throw new Error();
        const data = await res.json();
        let geo;
        if (data.is_shapefile) { geo = L.geoJSON(await shp(data.url)); }
        else { geo = L.geoJSON(data); }
        geo.setStyle({ color: '#7c3aed', weight: 2, fillOpacity: 0.15, fillColor: '#8b5cf6' }).addTo(map);
        if (geo.getLayers().length > 0) map.fitBounds(geo.getBounds(), { padding: [12, 12] });
        if (loader) loader.style.display = 'none';
    } catch(e) {
        if (loader) loader.innerHTML = '<span class="text-xs text-gray-400">Preview tidak tersedia</span>';
    }
}
</script>
@endpush