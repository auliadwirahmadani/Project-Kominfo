@extends('layouts.verifikator.verifikatornav')
@section('title', 'Periksa Geospasial')
@section('page-title', 'Periksa Geospasial')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ============================================================
       PERIKSA GEOSPASIAL — DESIGN SYSTEM
    ============================================================ */
    .card-geo {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px -4px rgba(0,0,0,0.07);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.25s cubic-bezier(0.4,0,0.2,1), box-shadow 0.25s;
    }
    .card-geo:hover { transform: translateY(-3px); box-shadow: 0 14px 36px -6px rgba(79,70,229,0.12); }

    .status-stripe { height: 3px; width: 100%; }
    .status-stripe.pending  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .status-stripe.approved { background: linear-gradient(90deg, #10b981, #34d399); }
    .status-stripe.rejected { background: linear-gradient(90deg, #ef4444, #f87171); }

    .mini-map-geo { height: 175px; width: 100%; }
    .mini-map-geo .leaflet-control-container { display: none !important; }
    .map-loader {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        background: #f8fafc; z-index: 20;
    }
    .info-row { display: flex; justify-content: space-between; gap: 8px; padding: 5px 0; border-bottom: 1px solid #f3f4f6; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6b7280; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .info-value { color: #1f2937; font-size: 11px; font-weight: 600; text-align: right;
                  overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 62%; }

    .geo-badge { display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: 700; }
    .geo-badge.pending  { background: #fef3c7; color: #92400e; }
    .geo-badge.approved { background: #d1fae5; color: #065f46; }
    .geo-badge.rejected { background: #fee2e2; color: #991b1b; }

    /* Detail Map */
    #detail-map-geo { height: 300px; width: 100%; border-radius: 12px; }
    #detail-map-geo .leaflet-control-container { opacity: 0.7; }

    /* Modal scrollbar */
    .modal-body-scroll { overflow-y: auto; }
    .modal-body-scroll::-webkit-scrollbar { width: 5px; }
    .modal-body-scroll::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 3px; }

    /* Stats mini bar */
    .stat-pill { display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: white;
                 border-radius: 14px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px -2px rgba(0,0,0,0.05); }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .anim-card { animation: fadeUp 0.4s ease-out both; }
</style>
@endpush

@section('content')
<div x-data="geoVerifyManager()" class="bg-gradient-to-br from-indigo-50/60 via-white to-blue-50/60 min-h-screen p-4 md:p-8">
<div class="max-w-7xl mx-auto space-y-6">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 anim-card">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Verifikasi Dataset Geospasial</h1>
            <p class="text-sm text-gray-500 mt-1">Periksa layer peta & metadata, kemudian buat keputusan verifikasi.</p>
        </div>
        <div class="flex items-center gap-2 bg-gray-50 p-1.5 rounded-xl border border-gray-200 w-full md:w-auto">
            <div class="relative flex-1 md:flex-none">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" placeholder="Cari dataset..."
                    class="pl-8 pr-3 py-2 w-full md:w-56 bg-white rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-gray-700" />
            </div>
            <select x-model="statusFilter"
                class="px-3 py-2 bg-white rounded-lg border border-gray-200 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                <option value="">Semua</option>
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
            </select>
        </div>
    </div>

    {{-- SESSION ALERT --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl flex items-center gap-3 anim-card">
        <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="font-semibold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    {{-- STATS PILLS --}}
    <div class="flex flex-wrap gap-3">
        @php
            $cntPending  = $layers->where('status_verifikasi','pending')->count();
            $cntApproved = $layers->where('status_verifikasi','approved')->count();
            $cntRejected = $layers->where('status_verifikasi','rejected')->count();
        @endphp
        <div class="stat-pill">
            <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div><p class="text-lg font-black text-indigo-600">{{ $layers->total() }}</p><p class="text-[11px] text-gray-500">Total</p></div>
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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($layers as $i => $layer)
        @php
            $meta   = $layer->metadata;
            $status = $layer->status_verifikasi;
        @endphp

        <div class="card-geo anim-card"
             style="animation-delay: {{ $i * 0.04 }}s"
             x-show="(search === '' || '{{ strtolower($layer->layer_name) }}'.includes(search.toLowerCase())) && (statusFilter === '' || statusFilter === '{{ $status }}')"
             x-transition>

            {{-- Status accent --}}
            <div class="status-stripe {{ $status }}"></div>

            {{-- Mini Map --}}
            <div class="relative" style="height: 175px; background:#f8fafc;">
                <div id="vmap-{{ $layer->geospatial_id }}" class="mini-map-geo"></div>
                <div id="vloader-{{ $layer->geospatial_id }}" class="map-loader">
                    <svg class="animate-spin h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                {{-- Badges --}}
                <div class="absolute top-2.5 left-2.5 z-30 flex flex-wrap gap-1.5">
                    <span class="geo-badge {{ $status }}">
                        @if($status === 'approved') <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Disetujui
                        @elseif($status === 'rejected') <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg> Ditolak
                        @else <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu
                        @endif
                    </span>
                    @if($layer->is_published)
                    <span class="text-[10px] font-bold text-blue-700 bg-blue-100/95 px-2 py-0.5 rounded-full">📡 Publish</span>
                    @endif
                </div>
                <span class="absolute top-2.5 right-2.5 z-30 text-[10px] text-white/80 bg-black/40 px-2 py-0.5 rounded-full font-mono">#{{ $layer->geospatial_id }}</span>
            </div>

            {{-- Card Body --}}
            <div class="p-4 flex-1 flex flex-col gap-3">
                <div>
                    <h3 class="text-[15px] font-bold text-gray-800 leading-tight line-clamp-2" title="{{ $layer->layer_name }}">
                        {{ $layer->layer_name }}
                    </h3>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                            {{ $layer->category->category_name ?? 'Tanpa Kategori' }}
                        </span>
                        @if($meta?->data_type)
                        <span class="text-[11px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md">{{ $meta->data_type }}</span>
                        @endif
                        @if($meta)
                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">✓ Ada Metadata</span>
                        @else
                        <span class="text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">! Tanpa Metadata</span>
                        @endif
                    </div>
                </div>

                {{-- Info Table --}}
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <div class="info-row">
                        <span class="info-label">File</span>
                        <span class="info-value font-mono" title="{{ $layer->file_path }}">{{ $layer->file_path ? basename($layer->file_path) : '—' }}</span>
                    </div>
                    @if($meta)
                    <div class="info-row">
                        <span class="info-label">Organisasi</span>
                        <span class="info-value">{{ $meta->organization ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tahun</span>
                        <span class="info-value">{{ $meta->year ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">CRS</span>
                        <span class="info-value font-mono">{{ $meta->crs ?? '—' }}</span>
                    </div>
                    @else
                    <div class="py-2 text-center text-[11px] text-amber-500 italic">Metadata belum diisi</div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Diperbarui</span>
                        <span class="info-value">{{ $layer->updated_at->diffForHumans() }}</span>
                    </div>
                </div>

                @if($layer->description)
                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ $layer->description }}</p>
                @endif

                {{-- ACTION BUTTONS --}}
                <div class="space-y-2 mt-auto pt-1">
                    {{-- Keputusan --}}
                    <button type="button"
                        @click="openModal({{ $layer->geospatial_id }}, '{{ addslashes($layer->layer_name) }}', '{{ $status }}')"
                        class="w-full py-2.5 flex justify-center items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-md shadow-indigo-200/50 transition-all text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Tindak Lanjut & Keputusan
                    </button>

                    {{-- Detail Layer --}}
                    @php
                        $detailGeoData = [
                            "id"          => $layer->geospatial_id,
                            "name"        => $layer->layer_name,
                            "status"      => $status,
                            "is_published"=> $layer->is_published,
                            "category"    => $layer->category->category_name ?? "",
                            "file"        => basename($layer->file_path ?? ""),
                            "description" => $layer->description ?? "",
                            "created_at"  => $layer->created_at->format("d M Y"),
                            "updated_at"  => $layer->updated_at->diffForHumans(),
                            "has_meta"    => (bool)$meta,
                            "meta_title"  => $meta->title ?? "",
                            "org"         => $meta->organization ?? "",
                            "year"        => (string)($meta->year ?? ""),
                            "crs"         => $meta->crs ?? "",
                            "scale"       => $meta->scale ?? "",
                            "data_type"   => $meta->data_type ?? "",
                            "abstract"    => $meta->abstract ?? "",
                            "keywords"    => $meta->keywords ?? "",
                            "source"      => $meta->source ?? "",
                            "pub_date"    => $meta->publication_date ?? "",
                            "identifier"  => $meta->identifier ?? "",
                        ];
                    @endphp
                    <button type="button"
                        @click='openDetail(@json($detailGeoData))'
                        class="w-full py-2 flex justify-center items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 rounded-xl text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Lihat Detail Layer Peta
                    </button>
                </div>
            </div>
        </div>

        @empty
        <div class="col-span-full py-20 text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-700">Tidak ada dataset</h3>
            <p class="text-sm text-gray-400 mt-1">Belum ada dataset yang dapat diperiksa saat ini.</p>
        </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if($layers->hasPages())
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        {{ $layers->links() }}
    </div>
    @endif

</div>

{{-- ================================================================
     MODAL 1 — KEPUTUSAN VERIFIKASI
================================================================ --}}
<div x-show="isModalOpen"
     style="display: none;"
     @click.self="closeModal()"
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">

    <div x-show="isModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col">

        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-5 text-white text-center shrink-0 relative rounded-t-2xl">
            <button type="button" @click="closeModal()"
                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <h3 class="text-lg font-bold flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Keputusan Verifikasi
            </h3>
            <p class="text-indigo-200 mt-1 text-xs font-medium line-clamp-1" x-text="activeLayerName"></p>
        </div>

        <form :action="'{{ url('verifikator/geospasial') }}/' + activeLayerId + '/verify'" method="POST"
              class="p-5 overflow-y-auto flex-1">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-3">Keputusan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2.5">
                        {{-- TERIMA --}}
                        <label class="relative flex flex-col items-center justify-center p-3.5 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition shadow-sm">
                            <input type="radio" name="status_verifikasi" value="approved" class="sr-only peer" x-model="statusForm" required>
                            <div class="w-11 h-11 bg-green-100 rounded-full flex items-center justify-center mb-2 text-green-600 transition-colors peer-checked:bg-green-500 peer-checked:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="font-bold text-sm text-gray-700 peer-checked:text-green-700">Terima</span>
                            <span class="text-[10px] text-gray-400 mt-0.5 font-medium text-center">(Auto Publish)</span>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-green-500 rounded-xl pointer-events-none transition-all"></div>
                        </label>
                        {{-- TOLAK --}}
                        <label class="relative flex flex-col items-center justify-center p-3.5 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition shadow-sm">
                            <input type="radio" name="status_verifikasi" value="rejected" class="sr-only peer" x-model="statusForm">
                            <div class="w-11 h-11 bg-red-100 rounded-full flex items-center justify-center mb-2 text-red-600 transition-colors peer-checked:bg-red-500 peer-checked:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <span class="font-bold text-sm text-gray-700 peer-checked:text-red-700">Tolak</span>
                            <span class="text-[10px] text-gray-400 mt-0.5 font-medium text-center">(Batal Publish)</span>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-red-500 rounded-xl pointer-events-none transition-all"></div>
                        </label>
                        {{-- TUNDA --}}
                        <label class="relative flex flex-col items-center justify-center p-3.5 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 transition shadow-sm">
                            <input type="radio" name="status_verifikasi" value="pending" class="sr-only peer" x-model="statusForm">
                            <div class="w-11 h-11 bg-yellow-100 rounded-full flex items-center justify-center mb-2 text-yellow-600 transition-colors peer-checked:bg-yellow-400 peer-checked:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="font-bold text-sm text-gray-700 peer-checked:text-yellow-700">Tunda</span>
                            <span class="text-[10px] text-gray-400 mt-0.5 font-medium text-center">(Perlu Revisi)</span>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-yellow-400 rounded-xl pointer-events-none transition-all"></div>
                        </label>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Catatan <span class="text-xs font-normal text-gray-400">(Opsional)</span></label>
                    <textarea name="catatan" rows="3" placeholder="Tuliskan catatan, alasan penolakan, atau permintaan revisi..."
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm text-gray-700 placeholder-gray-400 resize-none bg-white transition-shadow"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2.5 mt-4 pt-4 border-t border-gray-100">
                <button type="button" @click="closeModal()"
                    class="px-5 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 font-bold hover:bg-gray-100 transition text-sm">Batalkan</button>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-indigo-200 flex items-center gap-2 transition text-sm">
                    Simpan Keputusan
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================
     MODAL 2 — DETAIL LAYER PETA
================================================================ --}}
<div x-show="isDetailOpen"
     style="display: none;"
     @click.self="closeDetail()"
     @keydown.escape.window="if(isDetailOpen) closeDetail()"
     class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/55 backdrop-blur-sm p-4">

    <div x-show="isDetailOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col">

        {{-- Detail Header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 shrink-0">
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base font-bold text-gray-800 truncate" x-text="dLayer.name"></h3>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    <span class="text-xs text-gray-400" x-text="'Kategori: ' + (dLayer.category || '—')"></span>
                    <span x-show="dLayer.is_published" class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-full">📡 Dipublikasi</span>
                    <span x-html="dStatusBadge()"></span>
                </div>
            </div>
            <button @click="closeDetail()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Detail Body --}}
        <div class="flex-1 modal-body-scroll">

            {{-- Interactive Map --}}
            <div class="p-4 pb-3">
                <div class="relative rounded-xl overflow-hidden bg-gray-100" style="height: 300px;">
                    <div id="detail-map-geo" style="height:300px; width:100%;"></div>
                    <div id="detail-map-loader" class="absolute inset-0 flex items-center justify-center bg-gray-50 z-10">
                        <div class="text-center">
                            <svg class="animate-spin h-7 w-7 text-indigo-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-xs text-gray-400">Memuat peta...</p>
                        </div>
                    </div>
                    <div class="absolute bottom-3 right-3 z-20 bg-white/90 text-[10px] text-gray-500 px-2 py-1 rounded-lg font-medium backdrop-blur-sm shadow">
                        Klik & drag untuk menjelajahi
                    </div>
                </div>
            </div>

            {{-- Info Cards --}}
            <div class="px-4 pb-4 grid grid-cols-1 md:grid-cols-2 gap-3">

                {{-- Layer Info --}}
                <div class="bg-indigo-50/50 rounded-xl p-4 border border-indigo-100">
                    <h4 class="text-[11px] font-black text-indigo-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Informasi Layer
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-xs gap-2">
                            <span class="text-gray-500 font-medium shrink-0">Kategori</span>
                            <span class="font-semibold text-indigo-600 text-right truncate" x-text="dLayer.category || '—'"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs gap-2">
                            <span class="text-gray-500 font-medium shrink-0">File</span>
                            <span class="font-semibold text-gray-700 font-mono text-right truncate" x-text="dLayer.file || '—'"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs gap-2">
                            <span class="text-gray-500 font-medium shrink-0">Diunggah</span>
                            <span class="font-semibold text-gray-700 text-right" x-text="dLayer.created_at || '—'"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs gap-2">
                            <span class="text-gray-500 font-medium shrink-0">Diperbarui</span>
                            <span class="font-semibold text-gray-700 text-right" x-text="dLayer.updated_at || '—'"></span>
                        </div>
                        <div x-show="dLayer.identifier" class="flex justify-between items-center text-xs gap-2">
                            <span class="text-gray-500 font-medium shrink-0">Identifier</span>
                            <span class="font-semibold text-gray-700 text-right font-mono text-[10px] truncate" x-text="dLayer.identifier"></span>
                        </div>
                    </div>
                </div>

                {{-- Metadata Info --}}
                <div class="rounded-xl p-4 border" :class="dLayer.has_meta ? 'bg-emerald-50/50 border-emerald-100' : 'bg-amber-50/50 border-amber-100'">
                    <h4 class="text-[11px] font-black uppercase tracking-wider mb-3 flex items-center gap-1.5"
                        :class="dLayer.has_meta ? 'text-emerald-600' : 'text-amber-600'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Metadata
                    </h4>
                    <template x-if="dLayer.has_meta">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-xs gap-2">
                                <span class="text-gray-500 font-medium shrink-0">Organisasi</span>
                                <span class="font-semibold text-gray-700 text-right truncate" x-text="dLayer.org || '—'"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs gap-2">
                                <span class="text-gray-500 font-medium shrink-0">Tahun</span>
                                <span class="font-semibold text-gray-700 text-right" x-text="dLayer.year || '—'"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs gap-2">
                                <span class="text-gray-500 font-medium shrink-0">CRS</span>
                                <span class="font-semibold text-gray-700 font-mono text-right" x-text="dLayer.crs || '—'"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs gap-2">
                                <span class="text-gray-500 font-medium shrink-0">Skala</span>
                                <span class="font-semibold text-gray-700 text-right" x-text="dLayer.scale || '—'"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs gap-2">
                                <span class="text-gray-500 font-medium shrink-0">Tipe Data</span>
                                <span class="font-semibold text-gray-700 text-right" x-text="dLayer.data_type || '—'"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs gap-2" x-show="dLayer.source">
                                <span class="text-gray-500 font-medium shrink-0">Sumber</span>
                                <span class="font-semibold text-gray-700 text-right truncate" x-text="dLayer.source"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="!dLayer.has_meta">
                        <div class="py-4 text-center">
                            <svg class="w-8 h-8 text-amber-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-amber-600 font-medium">Metadata belum diisi</p>
                        </div>
                    </template>
                </div>

                {{-- Abstract --}}
                <template x-if="dLayer.has_meta && dLayer.abstract">
                <div class="md:col-span-2 bg-white rounded-xl p-4 border border-gray-200">
                    <h4 class="text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">Abstrak / Deskripsi</h4>
                    <p class="text-xs text-gray-600 leading-relaxed" x-text="dLayer.abstract"></p>
                </div>
                </template>

                {{-- Keywords --}}
                <template x-if="dLayer.has_meta && dLayer.keywords">
                <div class="md:col-span-2 bg-white rounded-xl p-4 border border-gray-200">
                    <h4 class="text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">Kata Kunci</h4>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="kw in (dLayer.keywords || '').split(',').filter(k => k.trim())" :key="kw">
                            <span class="text-[11px] bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-full font-semibold" x-text="kw.trim()"></span>
                        </template>
                    </div>
                </div>
                </template>

                {{-- Description --}}
                <template x-if="dLayer.description">
                <div class="md:col-span-2 bg-white rounded-xl p-4 border border-gray-200">
                    <h4 class="text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">Deskripsi Layer</h4>
                    <p class="text-xs text-gray-600 leading-relaxed" x-text="dLayer.description"></p>
                </div>
                </template>
            </div>
        </div>

        {{-- Detail Footer --}}
        <div class="px-5 py-3.5 border-t border-gray-100 flex justify-between items-center shrink-0">
            <button @click="closeDetail()"
                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition">
                Tutup
            </button>
            <button type="button"
                @click="closeDetail(); $nextTick(() => openModal(dLayer.id, dLayer.name, dLayer.status))"
                class="px-5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-bold hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-indigo-200/50 flex items-center gap-2 transition">
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
    Alpine.data('geoVerifyManager', () => ({
        search: '',
        statusFilter: '',

        // --- Verification Modal ---
        isModalOpen: false,
        activeLayerId: null,
        activeLayerName: '',
        statusForm: 'pending',

        openModal(id, name, currentStatus) {
            this.activeLayerId   = id;
            this.activeLayerName = name;
            this.statusForm = ['approved','rejected','pending'].includes(currentStatus) ? currentStatus : 'pending';
            this.isModalOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeModal() {
            this.isModalOpen = false;
            setTimeout(() => { this.activeLayerId = null; this.activeLayerName = ''; }, 300);
            document.body.style.overflow = '';
        },

        // --- Detail Modal ---
        isDetailOpen: false,
        dLayer: {},
        _detailMap: null,

        openDetail(layerData) {
            this.dLayer = layerData;
            this.isDetailOpen = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                setTimeout(() => this._initDetailMap(layerData.id), 150);
            });
        },
        closeDetail() {
            this.isDetailOpen = false;
            if (this._detailMap) { this._detailMap.remove(); this._detailMap = null; }
            document.body.style.overflow = '';
        },

        async _initDetailMap(id) {
            const el     = document.getElementById('detail-map-geo');
            const loader = document.getElementById('detail-map-loader');
            if (!el) return;
            if (this._detailMap) { this._detailMap.remove(); this._detailMap = null; }

            const map = L.map('detail-map-geo', {
                zoomControl: true, attributionControl: false,
                dragging: true, scrollWheelZoom: true
            }).setView([-3.8, 102.3], 8);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            this._detailMap = map;

            try {
                const res = await fetch('/geospatial/' + id + '/geojson');
                if (!res.ok) throw new Error();
                const data = await res.json();
                let geo;
                if (data.is_shapefile) { geo = L.geoJSON(await shp(data.url)); }
                else { geo = L.geoJSON(data); }
                geo.setStyle({ color: '#4f46e5', weight: 2.5, fillOpacity: 0.18, fillColor: '#6366f1' }).addTo(map);
                if (geo.getLayers().length > 0) map.fitBounds(geo.getBounds(), { padding: [25, 25] });
                if (loader) loader.style.display = 'none';
            } catch (e) {
                if (loader) loader.innerHTML = '<span class="text-sm text-gray-400">Preview peta tidak tersedia</span>';
            }
        },

        dStatusBadge() {
            const s = this.dLayer.status;
            if (s === 'approved') return '<span class="geo-badge approved">✓ Disetujui</span>';
            if (s === 'rejected') return '<span class="geo-badge rejected">✗ Ditolak</span>';
            return '<span class="geo-badge pending">⏱ Menunggu</span>';
        }
    }));
});

// Mini maps for cards
document.addEventListener('DOMContentLoaded', () => {
    @foreach($layers as $layer)
        _loadMiniMap("{{ $layer->geospatial_id }}");
    @endforeach
});

async function _loadMiniMap(id) {
    const el     = document.getElementById('vmap-' + id);
    const loader = document.getElementById('vloader-' + id);
    if (!el) return;

    const map = L.map('vmap-' + id, {
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
        geo.setStyle({ color: '#4f46e5', weight: 2, fillOpacity: 0.14, fillColor: '#6366f1' }).addTo(map);
        if (geo.getLayers().length > 0) map.fitBounds(geo.getBounds(), { padding: [12, 12] });
        if (loader) loader.style.display = 'none';
    } catch(e) {
        if (loader) loader.innerHTML = '<span class="text-xs text-gray-400">Preview tidak tersedia</span>';
    }
}
</script>
@endpush
