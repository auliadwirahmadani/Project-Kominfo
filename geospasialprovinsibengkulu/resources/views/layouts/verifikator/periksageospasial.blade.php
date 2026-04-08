@extends('layouts.verifikator.verifikatornav')
@section('title', 'Periksa Geospasial')
@section('page-title', 'Periksa Geospasial')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .mini-map-container { height: 180px; width: 100%; border-radius: 12px 12px 0 0; }
    .mini-map-container .leaflet-control-container { display: none !important; }
    .mini-map-loader {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        background: #f8fafc; z-index: 20; border-radius: 12px 12px 0 0;
    }
    .info-row { display: flex; justify-content: space-between; gap: 8px; padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6b7280; font-size: 12px; font-weight: 500; white-space: nowrap; }
    .info-value { color: #1f2937; font-size: 12px; font-weight: 600; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
@endpush

@section('content')
<div x-data="verificationManager()" class="bg-gradient-to-br from-indigo-50 via-white to-blue-50 min-h-screen p-4 md:p-8">
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- HEADER BAR --}}
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Verifikasi Dataset Geospasial</h1>
                <p class="text-sm text-gray-500 mt-1">Periksa layer peta, lihat metadata, lalu terima atau tolak dataset.</p>
            </div>
            
            <div class="flex items-center gap-3 bg-gray-50 p-2 rounded-lg border border-gray-200 w-full md:w-auto">
                <input type="text" x-model="search" placeholder="Cari dataset..." class="px-4 py-2 w-full md:w-64 bg-white border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 border-none shadow-sm text-sm" />
                <select x-model="statusFilter" class="px-4 py-2 bg-white border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 border-none shadow-sm text-sm text-gray-600">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative flex items-center justify-between" role="alert">
            <span class="block sm:inline font-medium"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</span>
        </div>
        @endif

        {{-- DATA GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($layers as $layer)
            @php
                $meta = $layer->metadata;
            @endphp
            <div class="flex flex-col bg-white rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.08)] overflow-hidden hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.12)] transition-all duration-300 border border-gray-100"
                 x-show="(search === '' || '{{ strtolower($layer->layer_name) }}'.includes(search.toLowerCase())) && (statusFilter === '' || statusFilter === '{{ $layer->status_verifikasi }}')"
                 x-transition>
                
                {{-- MINI MAP PREVIEW --}}
                <div class="relative" style="height:180px;">
                    <div id="vmap-{{ $layer->geospatial_id }}" class="mini-map-container"></div>
                    <div id="vloader-{{ $layer->geospatial_id }}" class="mini-map-loader">
                        <svg class="animate-spin h-7 w-7 text-indigo-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    {{-- Status badge overlay on map --}}
                    <div class="absolute top-3 left-3 z-30 flex flex-wrap gap-1.5">
                        @if($layer->status_verifikasi === 'pending')
                            <span class="px-2.5 py-1 bg-yellow-100/95 text-yellow-800 text-xs font-bold rounded-full backdrop-blur-sm flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Menunggu
                            </span>
                        @elseif($layer->status_verifikasi === 'approved')
                            <span class="px-2.5 py-1 bg-green-100/95 text-green-800 text-xs font-bold rounded-full backdrop-blur-sm flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Disetujui
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-red-100/95 text-red-800 text-xs font-bold rounded-full backdrop-blur-sm flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Ditolak
                            </span>
                        @endif
                        @if($layer->is_published)
                            <span class="px-2.5 py-1 bg-blue-100/95 text-blue-800 text-xs font-bold rounded-full backdrop-blur-sm">Dipublikasi</span>
                        @endif
                    </div>
                </div>

                {{-- CONTENT --}}
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="text-lg font-bold text-gray-800 mb-1 line-clamp-2" title="{{ $layer->layer_name }}">{{ $layer->layer_name }}</h3>
                    <p class="text-xs font-semibold text-indigo-600 mb-3 bg-indigo-50 inline-block px-2 py-1 rounded w-max">{{ $layer->category->category_name ?? 'Tanpa Kategori' }}</p>
                    
                    {{-- Layer Info --}}
                    <div class="bg-gray-50 rounded-xl p-3 mb-3 border border-gray-100">
                        <div class="info-row">
                            <span class="info-label">File Path</span>
                            <span class="info-value font-mono text-[11px]" title="{{ $layer->file_path }}">{{ $layer->file_path ? basename($layer->file_path) : '-' }}</span>
                        </div>
                        @if($meta)
                        <div class="info-row">
                            <span class="info-label">Organisasi</span>
                            <span class="info-value">{{ $meta->organization ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tahun</span>
                            <span class="info-value">{{ $meta->year ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">CRS</span>
                            <span class="info-value font-mono">{{ $meta->crs ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Skala</span>
                            <span class="info-value">{{ $meta->scale ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tipe Data</span>
                            <span class="info-value">{{ $meta->data_type ?? '-' }}</span>
                        </div>
                        @else
                        <div class="text-xs text-gray-400 text-center py-2 italic">Metadata belum diisi</div>
                        @endif
                    </div>

                    @if($layer->description)
                    <p class="text-sm text-gray-600 line-clamp-2 mb-4">{{ $layer->description }}</p>
                    @endif

                    <div class="space-y-2 mt-auto">
                        <button type="button" @click="openModal({{ $layer->geospatial_id }}, '{{ addslashes($layer->layer_name) }}', '{{ $layer->status_verifikasi }}')" 
                                class="w-full py-2.5 flex justify-center items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-md shadow-indigo-200 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Tindak Lanjut & Keputusan
                        </button>
                        
                        <a href="{{ route('verifikator.geospasial.verify', $layer->geospatial_id) }}" class="flex justify-center items-center gap-1 w-full py-2 bg-gray-50 border border-gray-200 text-gray-700 hover:bg-gray-100 hover:text-indigo-700 rounded-xl text-sm font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Lihat Detail Lengkap
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-16 text-center bg-white rounded-2xl shadow-sm border border-gray-50">
                <div class="w-20 h-20 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700">Tidak ada dataset.</h3>
                <p class="text-gray-500 max-w-sm mx-auto mt-2">Saat ini belum ada dataset geospasial yang tersedia untuk diperiksa.</p>
            </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($layers->hasPages())
        <div class="mt-8 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            {{ $layers->links() }}
        </div>
        @endif
        
    </div>

    {{-- VERIFICATION MODAL --}}
    <div x-show="isModalOpen" 
         style="display: none;"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 h-screen w-screen overflow-hidden">
        
        <div @click.away="closeModal()" 
             x-show="isModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden transform relative max-h-[90vh] flex flex-col">
            
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white text-center shrink-0">
                <h3 class="text-2xl font-bold flex items-center justify-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tindak Lanjut Dataset
                </h3>
                <p class="text-indigo-100 mt-2 font-medium line-clamp-1" x-text="activeLayerName"></p>
            </div>
            
            <form :action="'{{ url('verifikator/geospasial') }}/' + activeLayerId + '/verify'" method="POST" class="p-6 md:p-8 overflow-y-auto w-full">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-md font-bold text-gray-800 mb-3">Keputusan Verifikasi <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <label class="relative flex flex-col items-center justify-center p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 peer-checked:bg-green-50 focus-within:ring-2 focus-within:ring-green-500 transition shadow-sm">
                                <input type="radio" name="status_verifikasi" value="approved" class="sr-only peer" x-model="statusForm" required>
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-3 peer-checked:bg-green-500 peer-checked:text-white text-green-600 transition-colors shadow-sm">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="font-bold text-gray-800 peer-checked:text-green-800">Menerima</span>
                                <span class="text-[11px] text-gray-500 mt-1 text-center peer-checked:text-green-600 font-medium">(Otomatis Publish)</span>
                                <div class="absolute inset-0 border-2 border-transparent peer-checked:border-green-500 rounded-xl pointer-events-none transition-colors"></div>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 peer-checked:bg-red-50 focus-within:ring-2 focus-within:ring-red-500 transition shadow-sm">
                                <input type="radio" name="status_verifikasi" value="rejected" class="sr-only peer" x-model="statusForm">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-3 peer-checked:bg-red-500 peer-checked:text-white text-red-600 transition-colors shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </div>
                                <span class="font-bold text-gray-800 peer-checked:text-red-800">Menolak</span>
                                <span class="text-[11px] text-gray-500 mt-1 text-center peer-checked:text-red-600 font-medium">(Batal Publish)</span>
                                <div class="absolute inset-0 border-2 border-transparent peer-checked:border-red-500 rounded-xl pointer-events-none transition-colors"></div>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50 peer-checked:bg-yellow-50 focus-within:ring-2 focus-within:ring-yellow-500 transition shadow-sm">
                                <input type="radio" name="status_verifikasi" value="pending" class="sr-only peer" x-model="statusForm">
                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mb-3 peer-checked:bg-yellow-400 peer-checked:text-white text-yellow-600 transition-colors shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <span class="font-bold text-gray-800 peer-checked:text-yellow-800">Tunda / Revisi</span>
                                <span class="text-[11px] text-gray-500 mt-1 text-center peer-checked:text-yellow-700 font-medium">(Kembalikan status)</span>
                                <div class="absolute inset-0 border-2 border-transparent peer-checked:border-yellow-400 rounded-xl pointer-events-none transition-colors"></div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Verifikator <span class="text-xs font-medium text-gray-400 ml-1">(Opsional)</span></label>
                        <p class="text-[11px] text-gray-500 mb-2">Tambahkan catatan alasan penolakan atau masukan revisi untuk produsen data.</p>
                        <textarea name="catatan" 
                                  rows="3" 
                                  placeholder="Tuliskan catatan Anda di sini..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow text-gray-700 placeholder-gray-400 resize-none"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100 shrink-0">
                    <button type="button" @click="closeModal()" class="px-5 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 font-bold hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-100 transition-all">Batalkan</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 transition-all shadow-lg shadow-indigo-200 flex items-center gap-2">
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
    // Alpine component
    document.addEventListener('alpine:init', () => {
        Alpine.data('verificationManager', () => ({
            search: '',
            statusFilter: '',
            isModalOpen: false,
            activeLayerId: null,
            activeLayerName: '',
            statusForm: 'pending',

            openModal(id, name, currentStatus) {
                this.activeLayerId = id;
                this.activeLayerName = name;
                this.statusForm = ['approved', 'rejected', 'pending'].includes(currentStatus) ? currentStatus : 'pending';
                this.isModalOpen = true;
                document.body.style.overflow = 'hidden';
            },

            closeModal() {
                this.isModalOpen = false;
                setTimeout(() => {
                    this.activeLayerId = null;
                    this.activeLayerName = '';
                }, 300);
                document.body.style.overflow = '';
            }
        }))
    });

    // Load mini maps
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($layers as $layer)
            loadVerifyMap("{{ $layer->geospatial_id }}");
        @endforeach
    });

    async function loadVerifyMap(id) {
        var mapId = 'vmap-' + id;
        var loader = document.getElementById('vloader-' + id);
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
            geoLayer.setStyle({ color: '#4f46e5', weight: 2, fillOpacity: 0.15, fillColor: '#6366f1' }).addTo(miniMap);
            if (geoLayer.getLayers().length > 0) miniMap.fitBounds(geoLayer.getBounds(), { padding: [15, 15] });
            if (loader) loader.style.display = 'none';
        } catch (e) {
            console.warn('Map preview gagal ID ' + id + ':', e);
            if (loader) loader.innerHTML = '<span class="text-xs text-gray-400">Preview tidak tersedia</span>';
        }
    }
</script>
@endpush
