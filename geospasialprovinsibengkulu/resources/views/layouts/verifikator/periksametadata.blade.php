@extends('layouts.verifikator.verifikatornav')
@section('title', 'Periksa Metadata')
@section('page-title', 'Periksa Metadata')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .mini-map-meta { height: 160px; width: 100%; border-radius: 12px 12px 0 0; }
    .mini-map-meta .leaflet-control-container { display: none !important; }
    .meta-loader {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        background: #f8fafc; z-index: 20; border-radius: 12px 12px 0 0;
    }
    .meta-row { display: flex; justify-content: space-between; gap: 8px; padding: 5px 0; border-bottom: 1px solid #f3f4f6; }
    .meta-row:last-child { border-bottom: none; }
    .meta-label { color: #6b7280; font-size: 11px; font-weight: 500; white-space: nowrap; }
    .meta-val { color: #1f2937; font-size: 11px; font-weight: 600; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 60%; }
</style>
@endpush

@section('content')
<div x-data="metadataManager()" class="bg-gradient-to-br from-violet-50 via-white to-purple-50 min-h-screen p-4 md:p-8">
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- HEADER BAR --}}
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Verifikasi Metadata Dataset</h1>
                <p class="text-sm text-gray-500 mt-1">Periksa kelengkapan metadata, lihat preview peta, lalu terima atau tolak.</p>
            </div>

            <div class="flex items-center gap-3 bg-gray-50 p-2 rounded-lg border border-gray-200 w-full md:w-auto">
                <input type="text" x-model="search" placeholder="Cari judul atau organisasi..."
                    class="px-4 py-2 w-full md:w-72 bg-white rounded-md focus:ring-2 focus:ring-violet-500 border border-gray-200 shadow-sm text-sm outline-none" />
                <select x-model="statusFilter"
                    class="px-4 py-2 bg-white rounded-md focus:ring-2 focus:ring-violet-500 border border-gray-200 shadow-sm text-sm text-gray-600 outline-none">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-5 py-3 rounded-xl flex items-center gap-3" role="alert">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
        @endif

        {{-- DATA GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($metadatas as $meta)

            @php
                $status     = $meta->geospatial->status_verifikasi ?? 'pending';
                $published  = $meta->geospatial->is_published ?? false;
                $layerName  = $meta->geospatial->layer_name ?? 'Dataset Tanpa Nama';
                $catName    = $meta->geospatial->category->category_name ?? 'Tanpa Kategori';
                $geoId      = $meta->geospatial_id;
            @endphp

            <div class="flex flex-col bg-white rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.08)] overflow-hidden hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.14)] transition-all duration-300 border border-gray-100"
                 x-show="
                    (search === '' || '{{ strtolower($meta->title ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($meta->organization ?? '') }}'.includes(search.toLowerCase())) &&
                    (statusFilter === '' || statusFilter === '{{ $status }}')
                 "
                 x-transition>

                {{-- MINI MAP PREVIEW --}}
                <div class="relative" style="height:160px;">
                    <div id="mmap-{{ $geoId }}" class="mini-map-meta"></div>
                    <div id="mloader-{{ $geoId }}" class="meta-loader">
                        <svg class="animate-spin h-6 w-6 text-violet-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    {{-- Status badges --}}
                    <div class="absolute top-2.5 left-2.5 z-30 flex flex-wrap gap-1.5">
                        @if($status === 'approved')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100/95 text-green-800 text-[11px] font-bold rounded-full backdrop-blur-sm">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Disetujui
                            </span>
                        @elseif($status === 'rejected')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100/95 text-red-800 text-[11px] font-bold rounded-full backdrop-blur-sm">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Ditolak
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-yellow-100/95 text-yellow-800 text-[11px] font-bold rounded-full backdrop-blur-sm">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Menunggu
                            </span>
                        @endif
                        @if($published)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100/95 text-blue-800 text-[11px] font-bold rounded-full backdrop-blur-sm">Dipublikasi</span>
                        @endif
                    </div>
                    <span class="absolute top-2.5 right-2.5 z-30 text-[10px] text-white/80 bg-black/40 px-2 py-0.5 rounded-full font-mono backdrop-blur-sm">#{{ $meta->metadata_id }}</span>
                </div>

                {{-- CONTENT --}}
                <div class="p-5 flex-1 flex flex-col gap-2.5">

                    <div>
                        <h3 class="text-base font-bold text-gray-800 leading-tight line-clamp-2" title="{{ $meta->title }}">
                            {{ $meta->title ?? 'Judul tidak tersedia' }}
                        </h3>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <span class="text-xs font-semibold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">{{ $catName }}</span>
                            @if($meta->data_type)
                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $meta->data_type }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- METADATA INFO TABLE --}}
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
                        @if($meta->source)
                        <div class="meta-row">
                            <span class="meta-label">Sumber</span>
                            <span class="meta-val">{{ $meta->source }}</span>
                        </div>
                        @endif
                        @if($meta->identifier)
                        <div class="meta-row">
                            <span class="meta-label">Identifier</span>
                            <span class="meta-val font-mono text-[10px]">{{ $meta->identifier }}</span>
                        </div>
                        @endif
                        @if($meta->publication_date)
                        <div class="meta-row">
                            <span class="meta-label">Tgl Publikasi</span>
                            <span class="meta-val">{{ $meta->publication_date }}</span>
                        </div>
                        @endif
                    </div>

                    @if($meta->abstract)
                    <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed">{{ $meta->abstract }}</p>
                    @endif

                    @if($meta->keywords)
                    <div class="flex flex-wrap gap-1">
                        @foreach(array_slice(explode(',', $meta->keywords), 0, 5) as $kw)
                        <span class="text-[10px] bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full font-medium">{{ trim($kw) }}</span>
                        @endforeach
                        @if(count(explode(',', $meta->keywords)) > 5)
                        <span class="text-[10px] text-gray-400">+{{ count(explode(',', $meta->keywords)) - 5 }} lainnya</span>
                        @endif
                    </div>
                    @endif

                    {{-- ACTIONS --}}
                    <div class="space-y-2 mt-auto pt-2">
                        <button type="button"
                            @click="openModal({{ $meta->metadata_id }}, '{{ addslashes($meta->title ?? 'Metadata') }}', '{{ $status }}')"
                            class="w-full py-2.5 flex justify-center items-center gap-2 bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white rounded-xl font-semibold shadow-md shadow-violet-200 transition-all text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Tindak Lanjut & Keputusan
                        </button>

                        @if($meta->geospatial)
                        <a href="{{ route('verifikator.geospasial.verify', $meta->geospatial_id) }}"
                            class="flex justify-center items-center gap-1 w-full py-2 bg-gray-50 border border-gray-200 text-gray-700 hover:bg-gray-100 hover:text-violet-700 rounded-xl text-xs font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Lihat Detail Geospasial
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            @empty
            <div class="col-span-full py-20 text-center bg-white rounded-2xl shadow-sm border border-gray-50">
                <div class="w-20 h-20 mx-auto bg-violet-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700">Belum ada metadata.</h3>
                <p class="text-gray-400 max-w-sm mx-auto mt-2 text-sm">Saat ini belum ada metadata yang perlu diperiksa oleh verifikator.</p>
            </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($metadatas->hasPages())
        <div class="mt-8 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            {{ $metadatas->links() }}
        </div>
        @endif

    </div>

    {{-- ================================================================ --}}
    {{-- VERIFICATION MODAL --}}
    {{-- ================================================================ --}}
    <div x-show="isModalOpen"
         style="display: none;"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">

        <div @click.away="closeModal()"
             x-show="isModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden">

            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-violet-600 to-purple-700 p-6 text-white text-center shrink-0">
                <h3 class="text-xl font-bold flex items-center justify-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Keputusan Verifikasi Metadata
                </h3>
                <p class="text-violet-200 mt-1.5 text-sm line-clamp-1 font-medium" x-text="activeMetadataTitle"></p>
            </div>

            {{-- Modal Body --}}
            <form :action="'{{ url('verifikator/metadata') }}/' + activeMetadataId + '/verify'" method="POST"
                  class="overflow-y-auto flex-1 p-6 md:p-8 space-y-6">
                @csrf

                {{-- STATUS OPTIONS --}}
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-3">Keputusan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-3">

                        {{-- TERIMA --}}
                        <label class="relative flex flex-col items-center justify-center p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-green-50/50 transition shadow-sm group">
                            <input type="radio" name="status_verifikasi" value="approved" class="sr-only peer" x-model="statusForm" required>
                            <div class="w-11 h-11 bg-green-100 rounded-full flex items-center justify-center mb-2 text-green-600 group-hover:bg-green-200 transition peer-checked:bg-green-500 peer-checked:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="font-bold text-sm text-gray-700 peer-checked:text-green-700">Terima</span>
                            <span class="text-[10px] mt-0.5 text-gray-400 peer-checked:text-green-500 font-medium text-center">(Auto Publish)</span>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-green-500 rounded-xl pointer-events-none transition-all"></div>
                        </label>

                        {{-- TOLAK --}}
                        <label class="relative flex flex-col items-center justify-center p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-red-50/50 transition shadow-sm group">
                            <input type="radio" name="status_verifikasi" value="rejected" class="sr-only peer" x-model="statusForm">
                            <div class="w-11 h-11 bg-red-100 rounded-full flex items-center justify-center mb-2 text-red-600 group-hover:bg-red-200 transition peer-checked:bg-red-500 peer-checked:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <span class="font-bold text-sm text-gray-700 peer-checked:text-red-700">Tolak</span>
                            <span class="text-[10px] mt-0.5 text-gray-400 peer-checked:text-red-500 font-medium text-center">(Batal Publish)</span>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-red-500 rounded-xl pointer-events-none transition-all"></div>
                        </label>

                        {{-- TUNDA --}}
                        <label class="relative flex flex-col items-center justify-center p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-yellow-50/50 transition shadow-sm group">
                            <input type="radio" name="status_verifikasi" value="pending" class="sr-only peer" x-model="statusForm">
                            <div class="w-11 h-11 bg-yellow-100 rounded-full flex items-center justify-center mb-2 text-yellow-600 group-hover:bg-yellow-200 transition peer-checked:bg-yellow-400 peer-checked:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="font-bold text-sm text-gray-700 peer-checked:text-yellow-700">Tunda</span>
                            <span class="text-[10px] mt-0.5 text-gray-400 peer-checked:text-yellow-600 font-medium text-center">(Perlu Revisi)</span>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-yellow-400 rounded-xl pointer-events-none transition-all"></div>
                        </label>

                    </div>
                </div>

                {{-- CATATAN --}}
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">
                        Catatan Verifikator
                        <span class="text-xs font-normal text-gray-400 ml-1">(Opsional)</span>
                    </label>
                    <p class="text-[11px] text-gray-500 mb-2">Tambahkan catatan, alasan penolakan, atau permintaan revisi untuk produsen data.</p>
                    <textarea name="catatan"
                              rows="4"
                              placeholder="Contoh: Metadata belum lengkap, mohon tambahkan informasi sumber data dan tahun publikasi..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none text-sm text-gray-700 placeholder-gray-400 resize-none bg-white transition-shadow"></textarea>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" @click="closeModal()"
                        class="px-5 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 font-bold hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 transition-all text-sm">
                        Batalkan
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 text-white font-bold hover:from-violet-700 hover:to-purple-700 focus:ring-4 focus:ring-violet-200 shadow-lg shadow-violet-200 flex items-center gap-2 transition-all text-sm">
                        Simpan Keputusan
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
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
            isModalOpen: false,
            activeMetadataId: null,
            activeMetadataTitle: '',
            statusForm: 'pending',

            openModal(id, title, currentStatus) {
                this.activeMetadataId   = id;
                this.activeMetadataTitle = title;
                this.statusForm = ['approved', 'rejected', 'pending'].includes(currentStatus) ? currentStatus : 'pending';
                this.isModalOpen = true;
                document.body.style.overflow = 'hidden';
            },

            closeModal() {
                this.isModalOpen = false;
                setTimeout(() => {
                    this.activeMetadataId   = null;
                    this.activeMetadataTitle = '';
                }, 300);
                document.body.style.overflow = '';
            }
        }))
    });

    // Load mini maps for metadata cards
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($metadatas as $meta)
            @if($meta->geospatial_id)
                loadMetaMap("{{ $meta->geospatial_id }}");
            @endif
        @endforeach
    });

    async function loadMetaMap(id) {
        var mapId = 'mmap-' + id;
        var loader = document.getElementById('mloader-' + id);
        var el = document.getElementById(mapId);
        if (!el) return;

        var miniMap = L.map(mapId, {
            zoomControl: false, attributionControl: false,
            dragging: false, scrollWheelZoom: false, doubleClickZoom: false
        }).setView([-3.8, 102.3], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);

        try {
            var res = await fetch('/geospatial/' + id + '/geojson');
            if (!res.ok) throw new Error('Not found');
            var data = await res.json();
            var geoLayer;
            if (data.is_shapefile) {
                var geojson = await shp(data.url);
                geoLayer = L.geoJSON(geojson);
            } else {
                geoLayer = L.geoJSON(data);
            }
            geoLayer.setStyle({ color: '#7c3aed', weight: 2, fillOpacity: 0.15, fillColor: '#8b5cf6' }).addTo(miniMap);
            if (geoLayer.getLayers().length > 0) miniMap.fitBounds(geoLayer.getBounds(), { padding: [15, 15] });
            if (loader) loader.style.display = 'none';
        } catch (e) {
            console.warn('Map preview gagal ID ' + id + ':', e);
            if (loader) loader.innerHTML = '<span class="text-xs text-gray-400">Preview tidak tersedia</span>';
        }
    }
</script>
@endpush