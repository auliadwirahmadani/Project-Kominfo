@extends('layouts.admin.adminnav')
@section('title', 'Master Referensi - Metadata')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-black text-gray-800">Katalog Metadata</h1>
        <p class="text-sm text-gray-500">Preview otomatis dari file geospasial di database.</p>
    </div>
    <button onclick="openMetadataModal()" class="bg-red-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg hover:bg-red-700 transition-all">
        <i class="fas fa-plus-circle mr-2"></i> Tambah Metadata
    </button>
</div>

{{-- Grid Card Layout --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @forelse($layers as $layer)
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 flex flex-col group">
        
        {{-- Mini Map Preview Section --}}
        <div class="relative h-52 w-full bg-slate-50 border-b border-gray-100">
            {{-- Div untuk Leaflet Mini Map --}}
            <div id="mini-map-{{ $layer->geospatial_id }}" class="w-full h-full z-0"></div>
            
            {{-- Overlay Keterangan --}}
            <div class="absolute top-4 left-4 z-10">
                <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black text-gray-800 shadow-sm border border-gray-100 uppercase tracking-widest">
                    {{ $layer->category->category_name ?? 'Layer' }}
                </span>
            </div>

            {{-- Indikator Loading --}}
            <div id="loader-{{ $layer->geospatial_id }}" class="absolute inset-0 flex items-center justify-center bg-slate-50 z-20">
                <i class="fas fa-circle-notch animate-spin text-gray-300 text-2xl"></i>
            </div>
        </div>

        {{-- Content Section --}}
        <div class="p-7 flex-1 flex flex-col">
            <div class="mb-5">
                <h3 class="font-black text-gray-800 text-xl leading-tight line-clamp-1">
                    {{ $layer->layer_name }}
                </h3>
                <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-tighter">
                    ID: {{ $layer->metadata->identifier ?? 'BELUM DISATUKAN' }}
                </p>
            </div>

            <div class="space-y-2 mb-6">
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-gray-400">INSTANSI</span>
                    <span class="text-gray-700">{{ $layer->metadata->organization ?? '-' }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-gray-400">TAHUN</span>
                    <span class="text-gray-700">{{ $layer->metadata->year ?? '-' }}</span>
                </div>
            </div>

            <div class="mt-auto space-y-2">
                {{-- ✅ TOMBOL BARU: Lihat Detail Katalog Publik --}}
               <a href="{{ route('dataset.show', $layer->geospatial_id) }}?from=adminreferensi" 
                   class="w-full py-2.5 bg-red-50 text-red-700 border border-red-100 rounded-xl font-bold text-xs hover:bg-red-600 hover:border-red-600 hover:text-white transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-external-link-alt"></i> Lihat Detail Dataset
                </a>

                {{-- Tombol Buka Full Screen Peta --}}
                <button onclick="viewMap({{ $layer->geospatial_id }}, '{{ addslashes($layer->layer_name) }}')" 
                        class="w-full py-2.5 bg-gray-900 text-white rounded-xl font-bold text-xs hover:bg-gray-800 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-expand-arrows-alt"></i> Buka Peta Full Screen
                </button>
                
                {{-- Tombol Edit & Hapus --}}
                <div class="flex gap-2 pt-1">
                    <button onclick="editMetadata({{ json_encode($layer->metadata) }}, {{ $layer->geospatial_id }})" 
                            class="flex-1 py-2 bg-slate-100 text-slate-600 rounded-lg font-bold text-[10px] hover:bg-slate-200 transition-all uppercase">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                    @if($layer->metadata)
                    <form action="{{ route('admin.metadata.delete', $layer->metadata->metadata_id) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Yakin ingin menghapus metadata ini?')" class="p-2 text-gray-400 hover:text-red-600 transition-all bg-slate-50 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-20 font-bold text-gray-400">Data tidak ditemukan.</div>
    @endforelse
</div>


{{-- ========================================================== --}}
{{-- MODAL KELOLA METADATA (TAMBAH / EDIT) --}}
{{-- ========================================================== --}}
<div id="metadataModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300" id="metadataModalContent">
        <div class="flex justify-between items-center p-6 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h2 class="text-xl font-bold text-gray-800" id="modalTitle">Tambah Metadata</h2>
            <button onclick="closeMetadataModal()" class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="metadataForm" method="POST" action="{{ route('admin.metadata.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Pilih Layer --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Layer Geospasial *</label>
                    <select name="geospatial_id" id="geospatial_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all" required>
                        <option value="">-- Pilih Layer Geospasial --</option>
                        @foreach($layers as $lyr)
                            <option value="{{ $lyr->geospatial_id }}">{{ $lyr->layer_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Judul & Identifier --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Judul / Title Metadata</label>
                    <input type="text" name="title" id="meta_title" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all" placeholder="Contoh: Peta Batas Administrasi">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Identifier</label>
                    <input type="text" name="identifier" id="meta_identifier" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all" placeholder="ID Data (Opsional)">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tipe Data</label>
                    <select name="data_type" id="meta_data_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all">
                        <option value="dataset">Dataset</option>
                        <option value="service">Service (WMS/WFS)</option>
                        <option value="map">Map</option>
                    </select>
                </div>

                {{-- Instansi & Tahun --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Instansi / Organisasi</label>
                    <input type="text" name="organization" id="meta_organization" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all" placeholder="Contoh: Bappeda">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tahun Data</label>
                    <input type="number" name="year" id="meta_year" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all" placeholder="Contoh: 2024">
                </div>

                {{-- Deskripsi/Abstrak --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Abstrak / Deskripsi</label>
                    <textarea name="abstract" id="meta_abstract" rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all" placeholder="Jelaskan secara singkat mengenai data ini..."></textarea>
                </div>

                {{-- Tanggal Publikasi & Sistem Koordinat --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Publikasi</label>
                    <input type="date" name="publication_date" id="meta_publication_date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">CRS (Sistem Koordinat)</label>
                    <input type="text" name="crs" id="meta_crs" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all" placeholder="Contoh: EPSG:4326">
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 pt-5 border-t border-gray-100">
                <button type="button" onclick="closeMetadataModal()" class="px-6 py-2.5 rounded-xl font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-200 transition-all">
                    <i class="fas fa-save mr-2"></i> Simpan Metadata
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ========================================================== --}}
{{-- MODAL FULL SCREEN MAP VIEWER --}}
{{-- ========================================================== --}}
<div id="fullMapModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-50 p-4 lg:p-8">
    <div class="bg-white rounded-2xl shadow-2xl w-full h-full max-w-7xl flex flex-col overflow-hidden relative">
        {{-- Header Modal Map --}}
        <div class="flex justify-between items-center p-4 lg:p-5 bg-white border-b border-gray-100 shadow-sm z-20">
            <div>
                <h2 class="text-xl font-black text-gray-800" id="fullMapLayerTitle">Preview Peta</h2>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Geoportal Map Viewer</p>
            </div>
            <button onclick="closeMapModal()" class="text-gray-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-xl transition-all">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        {{-- Map Container --}}
        <div class="flex-1 relative bg-slate-50">
            <div id="mainMapViewer" class="w-full h-full"></div>
            
            {{-- Loading State --}}
            <div id="fullMapLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center z-[1000] hidden">
                <div class="w-16 h-16 border-4 border-red-200 border-t-red-600 rounded-full animate-spin mb-4"></div>
                <p class="text-gray-600 font-bold tracking-widest text-sm animate-pulse">MEMUAT PETA...</p>
            </div>
        </div>
    </div>
</div>


{{-- ========================================================== --}}
{{-- CSS & JAVASCRIPT --}}
{{-- ========================================================== --}}
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Hilangkan kontrol Leaflet di mini map agar bersih */
    div[id^="mini-map-"] .leaflet-control-container { display: none !important; }
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>

<script>
    // ==========================================
    // RENDER MINI MAP KETIKA HALAMAN DIMUAT
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($layers as $layer)
            loadMiniPreview({{ $layer->geospatial_id }});
        @endforeach
    });

    async function loadMiniPreview(id) {
        const mapId = `mini-map-${id}`;
        const loader = document.getElementById(`loader-${id}`);
        
        const miniMap = L.map(mapId, {
            zoomControl: false, attributionControl: false,
            dragging: false, scrollWheelZoom: false, doubleClickZoom: false
        }).setView([-3.8, 102.3], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);

        try {
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
                color: "#dc2626", weight: 1.5, fillOpacity: 0.2, fillColor: "#ef4444"
            }).addTo(miniMap);

            miniMap.fitBounds(geoLayer.getBounds(), { padding: [10, 10] });
            loader.classList.add('hidden');
        } catch (e) {
            console.error("Preview gagal untuk ID: " + id);
            loader.innerHTML = '<i class="fas fa-exclamation-triangle text-red-300"></i>';
        }
    }

    // ==========================================
    // FUNGSI CRUD METADATA MODAL
    // ==========================================
    const modal = document.getElementById('metadataModal');
    const modalContent = document.getElementById('metadataModalContent');
    const form = document.getElementById('metadataForm');

    function openMetadataModal() {
        // Reset form to CREATE mode
        document.getElementById('modalTitle').textContent = 'Tambah Metadata Baru';
        form.reset();
        document.getElementById('methodField').value = 'POST';
        form.action = "{{ route('admin.metadata.store') }}";
        document.getElementById('geospatial_id').disabled = false;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => { modalContent.classList.remove('scale-95'); }, 10);
    }

    function editMetadata(metadata, layerId) {
        if(!metadata) {
            openMetadataModal();
            document.getElementById('geospatial_id').value = layerId;
            return;
        }

        // Set to UPDATE mode
        document.getElementById('modalTitle').textContent = 'Edit Metadata';
        document.getElementById('methodField').value = 'PUT';
        // Asumsi rute update: /admin/metadata/update/{id}
        form.action = `/admin/metadata/update/${metadata.metadata_id}`; 

        document.getElementById('geospatial_id').value = metadata.geospatial_id;
        document.getElementById('meta_title').value = metadata.title || '';
        document.getElementById('meta_identifier').value = metadata.identifier || '';
        document.getElementById('meta_data_type').value = metadata.data_type || 'dataset';
        document.getElementById('meta_organization').value = metadata.organization || '';
        document.getElementById('meta_year').value = metadata.year || '';
        document.getElementById('meta_abstract').value = metadata.abstract || '';
        document.getElementById('meta_publication_date').value = metadata.publication_date || '';
        document.getElementById('meta_crs').value = metadata.crs || '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => { modalContent.classList.remove('scale-95'); }, 10);
    }

    function closeMetadataModal() {
        modalContent.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    // ==========================================
    // FUNGSI FULL SCREEN MAP VIEWER
    // ==========================================
    let mainMapInstance = null;
    let mainMapLayer = null;

    function viewMap(layerId, layerName) {
        document.getElementById('fullMapLayerTitle').textContent = layerName;
        const fullMapModal = document.getElementById('fullMapModal');
        
        fullMapModal.classList.remove('hidden');
        fullMapModal.classList.add('flex');
        document.body.style.overflow = 'hidden'; // Kunci scroll web
        
        document.getElementById('fullMapLoading').classList.remove('hidden');
        
        // Timeout agar DOM render modal selesai sebelum inisialisasi Map
        setTimeout(() => loadMainMapData(layerId), 200);
    }

    function closeMapModal() {
        document.getElementById('fullMapModal').classList.add('hidden');
        document.getElementById('fullMapModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
        
        if (mainMapInstance) {
            mainMapInstance.remove();
            mainMapInstance = null;
        }
        mainMapLayer = null;
    }

    async function loadMainMapData(layerId) {
        try {
            const response = await fetch(`/admin/geospasial/${layerId}/geojson`);
            const data = await response.json();
            
            initMainMap();
            
            if (data.is_shapefile) {
                shp(data.url).then(function(geojson) {
                    if (Array.isArray(geojson)) {
                        geojson.forEach(g => drawMainGeoJSON(g)); 
                    } else {
                        drawMainGeoJSON(geojson);
                    }
                }).catch(function(err) {
                    alert("Gagal membaca Shapefile: " + err.message);
                });
            } else {
                drawMainGeoJSON(data);
            }
            
        } catch (error) {
            alert('Gagal memuat peta dari server.');
            console.error(error);
        } finally {
            document.getElementById('fullMapLoading').classList.add('hidden');
        }
    }

    function initMainMap() {
        if (mainMapInstance) mainMapInstance.remove();
        
        mainMapInstance = L.map('mainMapViewer', {
            center: [-3.8, 102.3], zoom: 8, zoomControl: true
        });
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(mainMapInstance);
    }

    function drawMainGeoJSON(geoData) {
        // Fungsi untuk menangani file yang strukturnya kurang standar
        function fixGeom(geom) {
            if (!geom || !geom.coordinates) return null;
            if (!geom.type) {
                let c = geom.coordinates;
                if (Array.isArray(c[0]) && Array.isArray(c[0][0]) && Array.isArray(c[0][0][0])) geom.type = "MultiPolygon";
                else if (Array.isArray(c[0]) && Array.isArray(c[0][0])) geom.type = "Polygon";
                else if (Array.isArray(c[0])) geom.type = "LineString";
                else geom.type = "Point";
            }
            return geom;
        }

        let features = [];
        if (geoData.type === "FeatureCollection" && geoData.features) {
            geoData.features.forEach(f => {
                f.geometry = fixGeom(f.geometry);
                if(f.geometry) features.push(f);
            });
        } else if (geoData.type === "Feature") {
            geoData.geometry = fixGeom(geoData.geometry);
            if(geoData.geometry) features.push(geoData);
        } else if (geoData.coordinates) { 
            let fixed = fixGeom(geoData);
            if (fixed) features.push({ type: "Feature", properties: {}, geometry: fixed });
        }

        const safeGeoJSON = { type: "FeatureCollection", features: features };

        mainMapLayer = L.geoJSON(safeGeoJSON, {
            style: { color: '#dc2626', weight: 2, opacity: 1, fillOpacity: 0.2, fillColor: '#dc2626' },
            onEachFeature: function(feature, layer) {
                if (feature.properties && Object.keys(feature.properties).length > 0) {
                    let content = '<div class="p-2 max-h-60 overflow-y-auto text-sm"><strong>Informasi Atribut</strong><ul class="mt-2 space-y-1">';
                    for (const [key, value] of Object.entries(feature.properties)) {
                        if (value !== null && value !== undefined) {
                            content += `<li class="border-b border-gray-100 pb-1"><span class="text-gray-500">${key}:</span> <b>${value}</b></li>`;
                        }
                    }
                    content += '</ul></div>';
                    layer.bindPopup(content);
                }
                
                layer.on({
                    mouseover: (e) => e.target.setStyle({ weight: 3, fillOpacity: 0.4 }),
                    mouseout: (e) => e.target.setStyle({ weight: 2, fillOpacity: 0.2 })
                });
            }
        }).addTo(mainMapInstance);
        
        if (mainMapLayer.getBounds().isValid()) {
            mainMapInstance.fitBounds(mainMapLayer.getBounds(), { padding: [50, 50] });
        }
    }
</script>
@endpush
@endsection