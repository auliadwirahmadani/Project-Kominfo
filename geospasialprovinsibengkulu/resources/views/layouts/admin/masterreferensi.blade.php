@extends('layouts.admin.adminnav')
@section('title', 'Master Referensi - Metadata')
@section('page-title', 'Kelola Data Metadata')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Layer Metadata</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola informasi detail (metadata) untuk setiap data geospasial.</p>
        </div>
        <button onclick="openMetadataModal()" 
           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 cursor-pointer transition-colors">
            <i class="fas fa-plus"></i> Tambah Metadata
        </button>
    </div>
</div>

{{-- Notifikasi --}}
@if(session('success'))
<div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r shadow-sm">
    <div class="flex items-center">
        <i class="fas fa-check-circle text-green-500 mr-3"></i>
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
</div>
@endif

@if($errors->any() || session('error'))
<div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r shadow-sm">
    <div class="flex items-start">
        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
        <div>
            @if(session('error'))
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            @endif
            @if($errors->any())
                <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endif

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="p-4 text-left font-semibold text-gray-700">No</th>
                    <th class="p-4 text-left font-semibold text-gray-700">Nama Layer Peta</th>
                    <th class="p-4 text-left font-semibold text-gray-700">Preview Peta</th>
                    <th class="p-4 text-left font-semibold text-gray-700">Sumber Data</th>
                    <th class="p-4 text-left font-semibold text-gray-700">Tahun</th>
                    <th class="p-4 text-left font-semibold text-gray-700">Status Metadata</th>
                    <th class="p-4 text-center font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($layers as $index => $layer)
                <tr class="hover:bg-gray-50 border-b border-gray-100">
                    <td class="p-4">{{ $loop->iteration }}</td>
                    <td class="p-4">
                        <span class="font-semibold text-gray-800">{{ $layer->layer_name }}</span>
                        <p class="text-xs text-gray-500">Kategori: {{ $layer->category->category_name ?? 'N/A' }}</p>
                    </td>
                    
                    {{-- TOMBOL PREVIEW PETA INTERAKTIF --}}
                    <td class="p-4">
                        @if($layer->file_path)
                            <button type="button" 
                                    onclick="viewMap({{ $layer->geospatial_id }}, '{{ addslashes($layer->layer_name) }}')" 
                                    class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-sm font-semibold">
                                <i class="fas fa-map-marked-alt"></i> Lihat Peta
                            </button>
                        @else
                            <span class="text-xs text-gray-400 italic">File belum diupload</span>
                        @endif
                    </td>

                    @if($layer->metadata)
                        <td class="p-4 text-gray-600">{{ $layer->metadata->source ?? $layer->metadata->organization ?? '-' }}</td>
                        <td class="p-4 text-gray-600">{{ $layer->metadata->year ?? ($layer->metadata->publication_date ? \Carbon\Carbon::parse($layer->metadata->publication_date)->format('Y') : '-') }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold flex w-max items-center gap-1">
                                <i class="fas fa-check-circle"></i> Lengkap
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editMetadata({{ json_encode($layer->metadata) }}, {{ $layer->geospatial_id }})" 
                                        class="text-blue-600 hover:text-blue-800 p-1.5 bg-blue-50 rounded-md" 
                                        title="Edit Metadata">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <form action="{{ route('admin.metadata.delete', $layer->metadata->metadata_id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus metadata ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-1.5 bg-red-50 rounded-md" title="Hapus Metadata">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    @else
                        <td class="p-4 text-gray-400 italic">-</td>
                        <td class="p-4 text-gray-400 italic">-</td>
                        <td class="p-4">
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold flex w-max items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i> Belum Ada
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <button onclick="openMetadataModal({{ $layer->geospatial_id }})" 
                                    class="text-purple-600 hover:text-purple-800 p-1.5 bg-purple-50 rounded-md text-sm font-medium flex items-center gap-1 mx-auto">
                                <i class="fas fa-plus"></i> Isi Metadata
                            </button>
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-500">
                        <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                        <p>Belum ada data layer geospasial.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Form Metadata --}}
<div id="metadataModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center p-6 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-bold text-gray-800" id="modalTitle">
                <i class="fas fa-file-alt text-red-600 mr-2"></i> Tambah Metadata Geospasial
            </h2>
            <button onclick="closeMetadataModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1">
            <form action="{{ route('admin.metadata.store') }}" method="POST" id="metadataForm">
                @csrf
                <div id="methodContainer"></div>
                <input type="hidden" name="metadata_id" id="metadata_id">

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Pilih Layer Peta *</label>
                    <select name="geospatial_id" id="geospatial_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" required>
                        <option value="">-- Pilih Layer Geospasial --</option>
                        @foreach($layers as $layerData)
                            <option value="{{ $layerData->geospatial_id }}">{{ $layerData->layer_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Identifier *</label>
                        <input type="text" name="identifier" id="identifier" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: KSB2025" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Judul *</label>
                        <input type="text" name="title" id="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: Kawasan Strategis Provinsi Bengkulu" required>
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Abstrak (Deskripsi Peta) *</label>
                        <textarea name="abstract" id="abstract" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Jelaskan secara singkat isi dan tujuan peta ini..." required></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Organisasi/Instansi</label>
                        <input type="text" name="organization" id="organization" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: BAPPEDA Provinsi Bengkulu">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Tipe Data *</label>
                        <select name="data_type" id="data_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" required>
                            <option value="">-- Pilih Tipe Data --</option>
                            <option value="dataset">Dataset</option>
                            <option value="service">Service</option>
                            <option value="vector">Vector</option>
                            <option value="raster">Raster</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Waktu Publikasi *</label>
                        <input type="date" name="publication_date" id="publication_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Kata Kunci</label>
                        <input type="text" name="keywords" id="keywords" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: kawasan, strategis, tata ruang">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Sumber Data (Source)</label>
                        <input type="text" name="source" id="source" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: BIG / Dinas PUPR">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Tahun</label>
                        <input type="number" name="year" id="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: 2026">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">CRS (Sistem Koordinat)</label>
                        <input type="text" name="crs" id="crs" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: EPSG:4326">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Skala</label>
                        <input type="text" name="scale" id="scale" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: 1:50000">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Protokol Distribusi</label>
                        <select name="distribution_protocol" id="distribution_protocol" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                            <option value="">-- Pilih Protokol --</option>
                            <option value="OGC:WMS">OGC:WMS</option>
                            <option value="OGC:WFS">OGC:WFS</option>
                            <option value="OGC:WCS">OGC:WCS</option>
                            <option value="HTTP">HTTP</option>
                            <option value="FTP">FTP</option>
                        </select>
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">URL Distribusi</label>
                        <input type="url" name="distribution_url" id="distribution_url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: https://geo.bengkuluprov.go.id/geoserver/palapa/wms">
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Nama Layer Service</label>
                        <input type="text" name="layer_name_service" id="layer_name_service" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Cth: palapa:kawasan_strategis_probkl">
                    </div>
                </div>
            </form>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
            <button type="button" onclick="closeMetadataModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg font-semibold transition-colors">
                Batal
            </button>
            <button type="submit" form="metadataForm" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i> Simpan Metadata
            </button>
        </div>
    </div>
</div>

{{-- MODAL VIEWER PETA LEAFLET --}}
<div id="mapModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[95vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center p-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-map-marked-alt mr-2 text-red-600"></i>
                <span id="mapLayerTitle">Preview Peta</span>
            </h2>
            <button onclick="closeMapModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex-1 relative bg-gray-100">
            <div id="mapViewer" class="w-full h-full min-h-[500px]"></div>
            
            <div id="mapLoading" class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center z-10 hidden">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-red-600 mb-3"></i>
                    <p class="text-gray-600 font-medium">Memuat data peta...</p>
                </div>
            </div>
            
            <div id="mapError" class="absolute inset-0 bg-red-50 flex items-center justify-center z-10 hidden">
                <div class="text-center p-6">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-3"></i>
                    <p id="mapErrorMsg" class="text-red-700 font-medium"></p>
                    <button onclick="closeMapModal()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-2">
            <button type="button" onclick="closeMapModal()"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-semibold transition-colors">
                Tutup
            </button>
            <a id="downloadGeojson" href="#" target="_blank" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center gap-2 transition-colors">
                <i class="fas fa-download"></i> Download File
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #mapViewer { min-height: 500px; border-radius: 0.5rem; }
    .leaflet-container { width: 100%; height: 100%; border-radius: 0.5rem; z-index: 1; }
    .leaflet-popup-content-wrapper { border-radius: 0.5rem; }
    .leaflet-popup-content { margin: 0; padding: 0; font-size: 0.875rem; }
    .leaflet-popup-content ul { list-style: none; padding: 8px 12px; margin: 0; max-height: 200px; overflow-y: auto;}
    .leaflet-popup-content li { padding: 4px 0; border-bottom: 1px solid #eee; }
    .leaflet-popup-content li:last-child { border-bottom: none; }
    .leaflet-popup-content strong { color: #374151; display: inline-block; min-width: 80px;}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>

<script>
    const storeUrl = "{{ route('admin.metadata.store') }}";
    const selectedLayerFromURL = "{{ $selectedLayerId ?? '' }}";

    document.addEventListener("DOMContentLoaded", function() {
        if(selectedLayerFromURL) {
            openMetadataModal(selectedLayerFromURL);
        }
    });

    // ==================== FUNGSI MODAL METADATA ====================
    function openMetadataModal(layerId = null) {
        document.getElementById('metadataForm').reset();
        document.getElementById('metadataForm').action = storeUrl;
        document.getElementById('methodContainer').innerHTML = '';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-file-alt text-red-600 mr-2"></i> Tambah Metadata Geospasial';
        document.getElementById('geospatial_id').disabled = false;
        
        if(layerId) {
            document.getElementById('geospatial_id').value = layerId;
        }

        document.getElementById('metadataModal').classList.remove('hidden');
        document.getElementById('metadataModal').classList.add('flex');
    }

    function editMetadata(metadata, layerId) {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-blue-600 mr-2"></i> Edit Metadata Geospasial';
        
        document.getElementById('metadataForm').action = "{{ url('admin/metadata/update') }}/" + metadata.metadata_id;
        document.getElementById('methodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        document.getElementById('metadata_id').value = metadata.metadata_id;
        document.getElementById('geospatial_id').value = layerId;
        document.getElementById('geospatial_id').disabled = true; 
        
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'geospatial_id';
        hiddenInput.value = layerId;
        document.getElementById('methodContainer').appendChild(hiddenInput);

        document.getElementById('identifier').value = metadata.identifier || '';
        document.getElementById('title').value = metadata.title || '';
        document.getElementById('abstract').value = metadata.abstract || '';
        document.getElementById('organization').value = metadata.organization || '';
        document.getElementById('data_type').value = metadata.data_type || '';
        document.getElementById('publication_date').value = metadata.publication_date || '';
        document.getElementById('keywords').value = metadata.keywords || '';
        document.getElementById('source').value = metadata.source || '';
        document.getElementById('year').value = metadata.year || '';
        document.getElementById('crs').value = metadata.crs || '';
        document.getElementById('scale').value = metadata.scale || '';
        document.getElementById('distribution_protocol').value = metadata.distribution_protocol || '';
        document.getElementById('distribution_url').value = metadata.distribution_url || '';
        document.getElementById('layer_name_service').value = metadata.layer_name_service || '';

        document.getElementById('metadataModal').classList.remove('hidden');
        document.getElementById('metadataModal').classList.add('flex');
    }

    function closeMetadataModal() {
        document.getElementById('metadataModal').classList.add('hidden');
        document.getElementById('metadataModal').classList.remove('flex');
    }

    // ==================== FUNGSI VIEWER PETA (LEAFLET) ====================
    let mapInstance = null;
    let currentLayer = null;

    function viewMap(layerId, layerName) {
        document.getElementById('mapLayerTitle').textContent = layerName;
        document.getElementById('mapModal').classList.remove('hidden');
        document.getElementById('mapModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
        
        document.getElementById('downloadGeojson').href = `/admin/geospasial/${layerId}/download`;
        
        hideMapError();
        document.getElementById('mapLoading').classList.remove('hidden');
        
        setTimeout(() => loadMapData(layerId), 100);
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
        document.getElementById('mapModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
        
        if (mapInstance) {
            mapInstance.remove();
            mapInstance = null;
        }
        currentLayer = null;
    }

    function drawGeoJSON(geoData) {
        function fixGeometry(geom) {
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

        let validFeatures = [];
        if (geoData.type === "FeatureCollection" && geoData.features) {
            geoData.features.forEach(f => {
                f.geometry = fixGeometry(f.geometry);
                if(f.geometry) validFeatures.push(f);
            });
        } else if (geoData.type === "Feature") {
            geoData.geometry = fixGeometry(geoData.geometry);
            if(geoData.geometry) validFeatures.push(geoData);
        } else if (geoData.coordinates) { 
            let fixedGeom = fixGeometry(geoData);
            if (fixedGeom) validFeatures.push({ type: "Feature", properties: {}, geometry: fixedGeom });
        }

        if (validFeatures.length === 0) throw new Error("Format koordinat file tidak valid/kosong");

        let safeGeoJSON = { type: "FeatureCollection", features: validFeatures };

        currentLayer = L.geoJSON(safeGeoJSON, {
            style: function(feature) {
                return { color: '#dc2626', weight: 2, opacity: 1, fillOpacity: 0.2, fillColor: '#dc2626' };
            },
            onEachFeature: function(feature, layer) {
                if (feature.properties && Object.keys(feature.properties).length > 0) {
                    let popupContent = '<div class="p-2"><strong>Informasi Atribut</strong><ul>';
                    for (const [key, value] of Object.entries(feature.properties)) {
                        if (value !== null && value !== undefined) {
                            popupContent += `<li><strong>${key}:</strong> ${value}</li>`;
                        }
                    }
                    popupContent += '</ul></div>';
                    layer.bindPopup(popupContent);
                }
                
                layer.on({
                    mouseover: function(e) { e.target.setStyle({ weight: 3, fillOpacity: 0.4 }); },
                    mouseout: function(e) { e.target.setStyle({ weight: 2, fillOpacity: 0.2 }); }
                });
            }
        }).addTo(mapInstance);
        
        if (currentLayer.getBounds().isValid()) {
            mapInstance.fitBounds(currentLayer.getBounds(), { padding: [30, 30], maxZoom: 15 });
        }
    }

    async function loadMapData(layerId) {
        try {
            const response = await fetch(`/admin/geospasial/${layerId}/geojson`);
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || 'Gagal memuat data file peta dari server');
            }
            
            const data = await response.json();
            initMap();
            
            if (data.is_shapefile) {
                shp(data.url).then(function(geojson) {
                    if (Array.isArray(geojson)) {
                        geojson.forEach(g => drawGeoJSON(g)); 
                    } else {
                        drawGeoJSON(geojson);
                    }
                }).catch(function(err) {
                    showMapError("Gagal membaca isi Shapefile ZIP: " + err.message);
                });
            } 
            else {
                if (!data || !data.type) throw new Error('Format data GeoJSON tidak valid');
                drawGeoJSON(data);
            }
            
        } catch (error) {
            console.error('Error loading map:', error);
            showMapError(error.message);
        } finally {
            document.getElementById('mapLoading').classList.add('hidden');
        }
    }

    function initMap() {
        if (mapInstance) {
            mapInstance.remove();
        }
        
        mapInstance = L.map('mapViewer', {
            center: [-3.8, 102.3], // Koordinat tengah Provinsi Bengkulu
            zoom: 8,
            zoomControl: true,
            attributionControl: true
        });
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(mapInstance);
        
        L.control.scale({ metric: true, imperial: false }).addTo(mapInstance);
    }

    function showMapError(message) {
        document.getElementById('mapLoading').classList.add('hidden');
        document.getElementById('mapErrorMsg').textContent = message;
        document.getElementById('mapError').classList.remove('hidden');
    }

    function hideMapError() {
        document.getElementById('mapError').classList.add('hidden');
    }
</script>
@endpush