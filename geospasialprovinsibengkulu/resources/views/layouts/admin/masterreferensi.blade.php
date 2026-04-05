@extends('layouts.admin.adminnav')
@section('title', 'Master Referensi - Metadata')
@section('page-title', 'Master Referensi')

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
                    
                    {{-- Cek apakah data metadatanya sudah ada di database --}}
                    @if($layer->metadata)
                        {{-- TAMPILAN GAMBAR PREVIEW --}}
                        <td class="p-4">
                            @if($layer->metadata->preview_image)
                                <div class="relative group w-20 h-12">
                                    <img src="{{ asset('storage/' . $layer->metadata->preview_image) }}" 
                                         alt="Preview" 
                                         class="w-full h-full object-cover rounded border border-gray-300 shadow-sm transition-transform duration-300 group-hover:scale-[2.5] group-hover:z-50 relative cursor-zoom-in">
                                </div>
                            @else
                                <div class="w-20 h-12 bg-gray-100 rounded border border-gray-200 flex items-center justify-center text-xs text-gray-400 italic">
                                    Kosong
                                </div>
                            @endif
                        </td>
                        
                        {{-- Menampilkan Source (atau Organization) & Year (atau Publication Date) --}}
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
            <form action="{{ route('admin.metadata.store') }}" method="POST" id="metadataForm" enctype="multipart/form-data">
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

                    {{-- TAMBAHAN 4 KOLOM BARU --}}
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
                    {{-- END TAMBAHAN --}}

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

                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Preview Gambar Peta</label>
                        <input type="file" name="preview_image" id="preview_image" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" accept="image/*">
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG (Max: 10MB)</p>
                        <div id="previewContainer" class="mt-2 hidden">
                            <img id="imagePreview" src="" alt="Preview" class="max-h-32 rounded border shadow-sm">
                        </div>
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
@endsection

@push('scripts')
<script>
    const storeUrl = "{{ route('admin.metadata.store') }}";
    const selectedLayerFromURL = "{{ $selectedLayerId ?? '' }}";

    document.addEventListener("DOMContentLoaded", function() {
        if(selectedLayerFromURL) {
            openMetadataModal(selectedLayerFromURL);
        }

        document.getElementById('preview_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('previewContainer').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    });

    function openMetadataModal(layerId = null) {
        document.getElementById('metadataForm').reset();
        document.getElementById('metadataForm').action = storeUrl;
        document.getElementById('methodContainer').innerHTML = '';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-file-alt text-red-600 mr-2"></i> Tambah Metadata Geospasial';
        document.getElementById('geospatial_id').disabled = false;
        document.getElementById('previewContainer').classList.add('hidden');
        
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
        
        // 4 Kolom Baru
        document.getElementById('source').value = metadata.source || '';
        document.getElementById('year').value = metadata.year || '';
        document.getElementById('crs').value = metadata.crs || '';
        document.getElementById('scale').value = metadata.scale || '';
        
        document.getElementById('distribution_protocol').value = metadata.distribution_protocol || '';
        document.getElementById('distribution_url').value = metadata.distribution_url || '';
        document.getElementById('layer_name_service').value = metadata.layer_name_service || '';

        if(metadata.preview_image) {
            document.getElementById('imagePreview').src = '/storage/' + metadata.preview_image;
            document.getElementById('previewContainer').classList.remove('hidden');
        } else {
            document.getElementById('previewContainer').classList.add('hidden');
        }

        document.getElementById('metadataModal').classList.remove('hidden');
        document.getElementById('metadataModal').classList.add('flex');
    }

    function closeMetadataModal() {
        document.getElementById('metadataModal').classList.add('hidden');
        document.getElementById('metadataModal').classList.remove('flex');
        document.getElementById('previewContainer').classList.add('hidden');
    }
</script>
@endpush