@extends('layouts.admin.adminnav')
@section('page-title', 'Kelola Data Geospasial')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Layer Geospasial</h1>
        <button onclick="openModal()" 
           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 cursor-pointer">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
    </div>
</div>

@if($layers->count() > 0)
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="p-3 text-left font-semibold text-gray-700">No</th>
                <th class="p-3 text-left font-semibold text-gray-700">Nama Layer</th>
                <th class="p-3 text-left font-semibold text-gray-700">Kategori</th>
                <th class="p-3 text-left font-semibold text-gray-700">Deskripsi</th>
                <th class="p-3 text-left font-semibold text-gray-700">Status Verifikasi</th>
                <th class="p-3 text-left font-semibold text-gray-700">Status Publikasi</th>
                <th class="p-3 text-left font-semibold text-gray-700">Tanggal Dibuat</th>
                <th class="p-3 text-left font-semibold text-gray-700">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($layers as $index => $layer)
            <tr class="hover:bg-red-50 border-b border-gray-100">
                <td class="p-3">{{ $layers->firstItem() + $index }}</td>
                <td class="p-3 font-semibold text-gray-800">{{ $layer->layer_name }}</td>
                <td class="p-3">
                    @if($layer->category)
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                            {{ $layer->category->category_name ?? 'N/A' }}
                        </span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="p-3 text-gray-600 max-w-xs truncate" title="{{ $layer->description }}">
                    {{ Str::limit($layer->description, 50) ?? '-' }}
                </td>
                <td class="p-3">
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'approved' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'draft' => 'bg-gray-100 text-gray-700'
                        ];
                        $statusClass = $statusColors[$layer->status_verifikasi] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                        {{ ucfirst($layer->status_verifikasi ?? 'draft') }}
                    </span>
                </td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        {{ $layer->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ $layer->is_published ? 'Published' : 'Draft' }}
                    </span>
                </td>
                <td class="p-3 text-gray-600 text-sm">
                    {{ $layer->created_at ? $layer->created_at->format('d M Y') : '-' }}
                </td>
                <td class="p-3">
                    <div class="flex gap-2">
                        {{-- ✅ Tombol Lihat Peta --}}
                        @if($layer->file_path || $layer->geojson_data)
                        <button type="button" 
                                onclick="viewMap({{ $layer->geospatial_id }}, '{{ addslashes($layer->layer_name) }}')" 
                                class="text-green-600 hover:text-green-800 p-1" 
                                title="Lihat Peta">
                            <i class="fas fa-map"></i>
                        </button>
                        @endif
                
                        {{-- ✅ Tombol Edit (Popup) --}}
                        @php
                            $editData = [
                                'id' => $layer->geospatial_id,
                                'layer_name' => $layer->layer_name,
                                'category_id' => $layer->category_id,
                                'description' => $layer->description,
                                'status_verifikasi' => $layer->status_verifikasi,
                                'is_published' => $layer->is_published,
                                'filename' => $layer->file_original_name ?? basename($layer->file_path),
                                'file_size' => $layer->file_size,
                                'file_type' => $layer->file_type
                            ];
                        @endphp
                        <button type="button" 
                           onclick='openEditModal(@json($editData))' 
                           class="text-blue-600 hover:text-blue-800 p-1" 
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>

                        {{-- ✅ Tombol Hapus --}}
                        <form action="{{ route('admin.geospasial.destroy', $layer->geospatial_id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-4">
    {{ $layers->links() }}
</div>

@else
<div class="text-center py-12">
    <i class="fas fa-map-marked-alt text-6xl text-gray-300 mb-4"></i>
    <p class="text-gray-500 text-lg">Belum ada data geospasial</p>
    <button onclick="openModal()" 
       class="inline-block mt-4 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold cursor-pointer">
        <i class="fas fa-plus mr-2"></i>Tambah Data Pertama
    </button>
</div>
@endif

{{-- ... MODAL TAMBAH DATA DAN VIEWER PETA (Tidak ada yang diubah di bagian ini ke bawah) ... --}}
<div id="addDataModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Tambah Data Geospasial</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>

        @if($errors->any() || session('error'))
        <div class="px-6 pt-4">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                    <div>
                        @if(session('error'))
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        @endif
                        @if($errors->any())
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="p-6">
            <form action="{{ route('admin.geospasial.store') }}" method="POST" enctype="multipart/form-data" id="geospatialForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Layer *</label>
                    <input type="text" name="layer_name" 
                           value="{{ old('layer_name') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 @error('layer_name') border-red-500 @enderror"
                           placeholder="Masukkan nama layer" required>
                    @error('layer_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Kategori *</label>
                    <select name="category_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 @error('category_id') border-red-500 @enderror"
                            required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}" 
                                    {{ old('category_id') == $category->category_id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">File Peta (.geojson/.json/.zip) *</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-red-500 transition-colors">
                        <input type="file" name="geospatial_file" id="geospatial_file" 
                               accept=".geojson,.json,.shp,.zip" 
                               class="hidden" required onchange="handleFileSelect(event)">
                        <label for="geospatial_file" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600 font-medium">Klik untuk upload file</p>
                            <p class="text-sm text-gray-500 mt-1">Format: GeoJSON (.geojson, .json) atau Shapefile (.zip). Maksimal 100MB.</p>
                        </label>
                    </div>
                    <div id="fileInfo" class="hidden mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file text-green-600"></i>
                                <span id="fileName" class="text-sm font-medium text-gray-700"></span>
                                <span id="fileSize" class="text-xs text-gray-500"></span>
                            </div>
                            <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @error('geospatial_file') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Deskripsi</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 @error('description') border-red-500 @enderror"
                              placeholder="Masukkan deskripsi layer">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <input type="hidden" name="status_verifikasi" value="pending">
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_published" value="1" 
                               {{ old('is_published') ? 'checked' : '' }}
                               class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <span class="ml-2 text-gray-700">Publikasi Data</span>
                    </label>
                    <p class="text-sm text-gray-500 ml-6 mt-1">Centang jika data siap dipublikasikan</p>
                </div>

                {{-- Progress Bar Container (Sembunyi secara default) --}}
                <div id="uploadProgressContainer" class="hidden mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-700"><i class="fas fa-spinner fa-spin mr-2"></i>Mengupload Data...</span>
                        <span id="uploadPercentage" class="text-sm font-bold text-red-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="uploadProgressBar" class="bg-red-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg font-semibold">
                        Batal
                    </button>
                    <button type="submit" id="submitBtn"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== MODAL EDIT DATA ==================== --}}
<div id="editDataModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Edit Data Geospasial</h2>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6">
            <form action="" method="POST" enctype="multipart/form-data" id="geoEditForm">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Layer *</label>
                    <input type="text" name="layer_name" id="edit_layer_name"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Kategori *</label>
                    <select name="category_id" id="edit_category_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">File Peta (.geojson/.json/.zip)</label>
                    
                    {{-- Info File Saat Ini --}}
                    <div id="currentFileInfo" class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg hidden">
                        <p class="text-sm text-blue-800">
                            <strong>📁 File saat ini:</strong><br>
                            <span id="currentFileName"></span><br>
                            <small class="text-gray-600">Terlampir dan tidak akan berubah jika Anda tidak mengunggah file baru.</small>
                        </p>
                    </div>

                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors">
                        <input type="file" name="geospatial_file" id="edit_geospatial_file" 
                               accept=".geojson,.json,.shp,.zip" 
                               class="hidden" onchange="handleEditFileSelect(event)">
                        <label for="edit_geospatial_file" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600 font-medium">Klik untuk upload file pengganti</p>
                            <p class="text-xs text-gray-400 mt-2">* Kosongkan jika tidak ingin mengubah file</p>
                        </label>
                    </div>
                    <div id="editFileInfo" class="hidden mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file text-green-600"></i>
                                <span id="editFileNameDisplay" class="text-sm font-medium text-gray-700"></span>
                            </div>
                            <button type="button" onclick="removeEditFile()" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Deskripsi</label>
                    <textarea name="description" id="edit_description" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="mb-4">
                    <input type="hidden" name="status_verifikasi" id="edit_status_verifikasi" value="pending">
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_published" id="edit_is_published" value="1" 
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-gray-700">Publikasi Data</span>
                    </label>
                </div>

                <div class="flex gap-3 justify-end border-t border-gray-100 pt-4">
                    <button type="button" onclick="closeEditModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg font-semibold">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="mapModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[95vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center p-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-map-marked-alt mr-2"></i>
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
                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-semibold">
                Tutup
            </button>
            <a id="downloadGeojson" href="#" target="_blank" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center gap-2">
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
    .leaflet-container { width: 100%; height: 100%; border-radius: 0.5rem; }
    .leaflet-popup-content-wrapper { border-radius: 0.5rem; }
    .leaflet-popup-content { margin: 0; padding: 0; font-size: 0.875rem; }
    .leaflet-popup-content ul { list-style: none; padding: 8px 12px; margin: 0; }
    .leaflet-popup-content li { padding: 2px 0; border-bottom: 1px solid #eee; }
    .leaflet-popup-content li:last-child { border-bottom: none; }
    .leaflet-popup-content strong { color: #374151; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>

<script>
    let mapInstance = null;
    let currentLayer = null;

    // ==================== MODAL TAMBAH DATA ====================
    function openModal() {
        document.getElementById('addDataModal').classList.remove('hidden');
        document.getElementById('addDataModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('addDataModal').classList.add('hidden');
        document.getElementById('addDataModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
        resetForm();
    }

    document.getElementById('addDataModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!document.getElementById('addDataModal').classList.contains('hidden')) closeModal();
            if (!document.getElementById('mapModal').classList.contains('hidden')) closeMapModal();
        }
    });

    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            const validExtensions = ['.geojson', '.json', '.shp', '.zip'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!validExtensions.includes(fileExtension)) {
                alert('Format file tidak valid! Gunakan GeoJSON (.geojson, .json) atau Shapefile (.zip)');
                event.target.value = '';
                return;
            }

            // Batas ukuran 100MB
            const maxSize = 100 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Ukuran file terlalu besar! Maksimal 100MB');
                event.target.value = '';
                return;
            }

            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = '(' + formatFileSize(file.size) + ')';
            document.getElementById('fileInfo').classList.remove('hidden');
        }
    }

    function removeFile() {
        document.getElementById('geospatial_file').value = '';
        document.getElementById('fileInfo').classList.add('hidden');
    }

    function resetForm() {
        document.getElementById('geospatialForm')?.reset();
        removeFile();
    }

    // ==================== FUNGSI MODAL EDIT DATA ====================
    function openEditModal(data) {
        document.getElementById('editDataModal').classList.remove('hidden');
        document.getElementById('editDataModal').classList.add('flex');
        document.body.style.overflow = 'hidden';

        // Populate Form
        document.getElementById('geoEditForm').action = "/admin/geospasial/" + data.id;
        document.getElementById('edit_layer_name').value = data.layer_name || '';
        document.getElementById('edit_category_id').value = data.category_id || '';
        document.getElementById('edit_description').value = data.description || '';
        document.getElementById('edit_status_verifikasi').value = data.status_verifikasi || 'pending';
        document.getElementById('edit_is_published').checked = !!data.is_published;

        // Populate File Info
        if (data.filename) {
            document.getElementById('currentFileInfo').classList.remove('hidden');
            let sizeInfo = data.file_size ? `${(data.file_size / 1024).toFixed(2)} KB` : '';
            if (sizeInfo) sizeInfo = ` | ${sizeInfo}`;
            document.getElementById('currentFileName').textContent = `${data.filename} (${(data.file_type || '').toUpperCase()}${sizeInfo})`;
        } else {
            document.getElementById('currentFileInfo').classList.add('hidden');
        }
        
        removeEditFile();
    }

    function closeEditModal() {
        document.getElementById('editDataModal').classList.add('hidden');
        document.getElementById('editDataModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    function handleEditFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            document.getElementById('editFileNameDisplay').textContent = file.name;
            document.getElementById('editFileInfo').classList.remove('hidden');
        }
    }

    function removeEditFile() {
        if(document.getElementById('edit_geospatial_file')) {
            document.getElementById('edit_geospatial_file').value = '';
        }
        document.getElementById('editFileInfo')?.classList.add('hidden');
    }

    // Tutup Edit modal saat escape atau click luar area
    document.getElementById('editDataModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!document.getElementById('addDataModal').classList.contains('hidden')) closeModal();
            if (!document.getElementById('editDataModal').classList.contains('hidden')) closeEditModal();
            if (!document.getElementById('mapModal').classList.contains('hidden')) closeMapModal();
        }
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // ==================== FUNGSI VIEWER PETA (LEAFLET + SHAPEFILE) ====================
    
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

    // Logika menggambar GeoJSON ke Leaflet
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

        if (validFeatures.length === 0) throw new Error("Format koordinat file tidak valid");

        let safeGeoJSON = { type: "FeatureCollection", features: validFeatures };

        currentLayer = L.geoJSON(safeGeoJSON, {
            style: function(feature) {
                return { color: '#dc2626', weight: 2, opacity: 1, fillOpacity: 0.2, fillColor: '#dc2626' };
            },
            onEachFeature: function(feature, layer) {
                if (feature.properties && Object.keys(feature.properties).length > 0) {
                    let popupContent = '<div class="p-2"><strong>Informasi Layer</strong><ul>';
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
                throw new Error(errorData.error || 'Gagal memuat data peta');
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
            center: [-3.8, 102.3],
            zoom: 8,
            zoomControl: true,
            attributionControl: true
        });
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
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

    document.getElementById('mapModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeMapModal();
    });

    // ==================== UPLOAD PROGRESS HANDLER (AJAX) ====================
    document.getElementById('geospatialForm').addEventListener('submit', function(e) {
        e.preventDefault(); 

        const form = this;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('submitBtn');
        const progressContainer = document.getElementById('uploadProgressContainer');
        const progressBar = document.getElementById('uploadProgressBar');
        const progressText = document.getElementById('uploadPercentage');

        // 1. Siapkan UI ke mode loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        progressContainer.classList.remove('hidden');
        progressBar.style.width = '0%';
        progressText.textContent = '0%';

        // 2. Buat Request AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); 

        // 3. Pantau progres upload
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percentComplete + '%';
                progressText.textContent = percentComplete + '%';
                
                if(percentComplete === 100) {
                    progressText.textContent = 'Menyimpan...';
                }
            }
        });

        // 4. Tangani balasan dari server
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                window.location.reload();
            } else {
                let errorMessage = 'Terjadi kesalahan saat mengupload.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) errorMessage = response.message;
                    if (response.errors) {
                        const firstError = Object.values(response.errors)[0][0];
                        errorMessage = firstError; 
                    }
                } catch (e) {}

                alert(errorMessage);
                
                // Kembalikan UI ke awal
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan Data';
                progressContainer.classList.add('hidden');
            }
        };

        xhr.onerror = function() {
            alert('Gagal terhubung ke server. Periksa koneksi internet Anda.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan Data';
            progressContainer.classList.add('hidden');
        };

        xhr.send(formData);
    });
</script>
@endpusha