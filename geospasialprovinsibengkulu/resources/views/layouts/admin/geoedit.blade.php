@extends('layouts.admin.adminnav')

@section('page-title', 'Edit Data Geospasial')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.geospasial.index') }}" class="text-red-600 hover:text-red-800 mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Edit Data Geospasial</h1>
    </div>

    <form action="{{ route('admin.geospasial.update', $layer->geospatial_id) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="bg-white p-6 rounded-lg shadow">
        @csrf
        @method('PUT')

        <!-- Layer Name -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Nama Layer *</label>
            <input type="text" name="layer_name" 
                   value="{{ old('layer_name', $layer->layer_name) }}" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 @error('layer_name') border-red-500 @enderror"
                   required>
            @error('layer_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Category -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Kategori *</label>
            <select name="category_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 @error('category_id') border-red-500 @enderror"
                    required>
                <option value="">Pilih Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->category_id }}" 
                            {{ old('category_id', $layer->category_id) == $category->category_id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Deskripsi</label>
            <textarea name="description" rows="4" 
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 @error('description') border-red-500 @enderror">{{ old('description', $layer->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- ✅ FILE PETA (GEOJSON/SHP) - TAMBAHAN BARU -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">File Peta (GeoJSON/SHP)</label>
            
            {{-- Info File Saat Ini --}}
            @if($layer->file_path && Storage::disk('public')->exists($layer->file_path))
                <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>📁 File saat ini:</strong><br>
                        {{ $layer->file_original_name ?? 'N/A' }}<br>
                        <small class="text-gray-600">
                            Tipe: {{ strtoupper($layer->file_type ?? 'N/A') }} | 
                            Ukuran: {{ number_format(($layer->file_size ?? 0) / 1024, 2) }} KB
                        </small>
                    </p>
                </div>
            @endif

            {{-- Upload Area --}}
            <div class="border-2 border-dashed border-red-300 rounded-lg p-6 text-center hover:border-red-400 transition cursor-pointer" 
                 onclick="document.getElementById('geospatial_file').click()">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                    <span class="font-medium text-red-600">Klik untuk upload file</span>
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Format: GeoJSON (.geojson, .json) atau Shapefile (.zip)
                </p>
                <p class="text-xs text-gray-400 mt-2">
                    * Kosongkan jika tidak ingin mengubah file
                </p>
            </div>
            
            <input type="file" 
                   id="geospatial_file" 
                   name="geospatial_file" 
                   accept=".geojson,.json,.zip,.shp"
                   class="hidden"
                   onchange="previewFileName(this)">
            
            <div id="file_name" class="mt-2 text-sm"></div>
            
            @error('geospatial_file')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <!-- ✅ END FILE PETA -->

        <!-- Status Verifikasi -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Status Verifikasi</label>
            <select name="status_verifikasi" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                <option value="pending" {{ old('status_verifikasi', $layer->status_verifikasi) == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ old('status_verifikasi', $layer->status_verifikasi) == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ old('status_verifikasi', $layer->status_verifikasi) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="draft" {{ old('status_verifikasi', $layer->status_verifikasi) == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>

        <!-- Is Published -->
        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_published" value="1" 
                       {{ old('is_published', $layer->is_published) ? 'checked' : '' }}
                       class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                <span class="ml-2 text-gray-700">Publikasi Data</span>
            </label>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button type="submit" 
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold">
                <i class="fas fa-save mr-2"></i>Simpan Perubahan
            </button>
            <a href="{{ route('admin.geospasial.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg font-semibold">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
// Preview nama file yang dipilih
function previewFileName(input) {
    const fileName = input.files[0]?.name;
    const fileNameDiv = document.getElementById('file_name');
    
    if(fileName) {
        fileNameDiv.innerHTML = `<span class="text-green-600">📄 File terpilih: <strong>${fileName}</strong></span>`;
    } else {
        fileNameDiv.innerHTML = '';
    }
}
</script>
@endsection