@extends('layouts.produsen.produsennav')
@section('title', 'Kelola Geospasial')
@section('page-title', 'Kelola Geospasial')

@push('styles')
<style>
    /* ============================================================
       KELOLA GEOSPASIAL — DESIGN SYSTEM
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
    .card-geo:hover { transform: translateY(-3px); box-shadow: 0 14px 36px -6px rgba(99,102,241,0.12); }

    .status-stripe { height: 4px; width: 100%; }
    .status-stripe.pending  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .status-stripe.approved { background: linear-gradient(90deg, #10b981, #34d399); }
    .status-stripe.rejected { background: linear-gradient(90deg, #ef4444, #f87171); }

    .geo-badge { display: inline-flex; align-items: center; gap: 3px; padding: 3px 8px; border-radius: 99px; font-size: 10px; font-weight: 700; }
    .geo-badge.pending  { background: #fef3c7; color: #92400e; }
    .geo-badge.approved { background: #d1fae5; color: #065f46; }
    .geo-badge.rejected { background: #fee2e2; color: #991b1b; }

    .stat-pill { display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: white;
                 border-radius: 14px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px -2px rgba(0,0,0,0.05); }

    .file-drop-area {
        border: 2px dashed #cbd5e1; border-radius: 12px; padding: 2rem; text-align: center;
        background: #f8fafc; transition: all 0.2s; cursor: pointer; position: relative;
    }
    .file-drop-area:hover, .file-drop-area.dragover { border-color: #6366f1; background: #e0e7ff; }
    .file-input-hidden { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }

    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .anim-card { animation: fadeUp 0.4s ease-out both; }
</style>
@endpush

@section('content')
<div x-data="geoManager()" class="bg-gradient-to-br from-indigo-50/60 via-white to-blue-50/60 min-h-screen p-4 md:p-8">
<div class="max-w-7xl mx-auto space-y-6">

    {{-- ============================================================
         HEADER & TOOLS
    ============================================================ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 anim-card">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Kelola Data Geospasial</h1>
            <p class="text-sm text-gray-500 mt-1">Unggah, edit, atau hapus dataset geospasial milik organisasi Anda.</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:flex-none">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" placeholder="Cari nama layer..."
                    class="pl-9 pr-3 py-2.5 w-full md:w-64 bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition text-gray-700" />
            </div>
            <button @click="openCreateModal()" class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">Tambah Data</span>
            </button>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl flex items-center gap-3 anim-card">
        <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="font-semibold text-sm">{{ session('success') }}</span>
    </div>
    @endif
    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-3 rounded-xl anim-card">
        <div class="flex items-center gap-2 mb-2 font-bold">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Terdapat kesalahan input:
        </div>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    {{-- ============================================================
         GRID CONTENT
    ============================================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($layers as $i => $layer)
        @php $status = $layer->status_verifikasi; @endphp
        <div class="card-geo anim-card"
             style="animation-delay: {{ $i * 0.04 }}s"
             x-show="search === '' || '{{ strtolower($layer->layer_name) }}'.includes(search.toLowerCase())"
             x-transition>

            <div class="status-stripe {{ $status }}"></div>

            <div class="p-5 flex-1 flex flex-col">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0 border border-indigo-100">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <div class="flex flex-col items-end gap-1.5">
                        <span class="geo-badge {{ $status }}">
                            @if($status === 'approved') ✓ Disetujui
                            @elseif($status === 'rejected') ✗ Ditolak
                            @else ⏱ Menunggu
                            @endif
                        </span>
                        @if($layer->is_published)
                        <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">📡 Dipublikasi</span>
                        @endif
                    </div>
                </div>

                <h3 class="text-base font-bold text-gray-800 leading-snug line-clamp-2" title="{{ $layer->layer_name }}">
                    {{ $layer->layer_name }}
                </h3>
                <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md w-fit mt-2">
                    {{ $layer->category->category_name ?? 'Tak Berkategori' }}
                </span>

                <div class="mt-4 space-y-2 flex-1">
                    <div class="flex items-center gap-2 text-[11px] text-gray-500 bg-gray-50 p-2 rounded-lg border border-gray-100">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span class="truncate font-mono" title="{{ basename($layer->file_path) }}">{{ basename($layer->file_path) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[10px] text-gray-400 font-medium px-1">
                        <span>{{ strtoupper($layer->file_type) }} • {{ round($layer->file_size / 1024, 1) }} KB</span>
                        <span>{{ $layer->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
                    @php
                        $editData = [
                            'id' => $layer->geospatial_id,
                            'name' => $layer->layer_name,
                            'category_id' => $layer->category_id,
                            'description' => $layer->description,
                            'filename' => basename($layer->file_path)
                        ];
                    @endphp
                    <button type="button" @click='openEditModal(@json($editData))'
                        class="flex-1 py-1.5 flex items-center justify-center gap-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Edit
                    </button>
                    <button type="button" @click="openDeleteModal('{{ $layer->geospatial_id }}', '{{ addslashes($layer->layer_name) }}')"
                        class="flex-1 py-1.5 flex items-center justify-center gap-1.5 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg text-xs font-bold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-700">Belum ada data geospasial</h3>
            <p class="text-sm text-gray-400 mt-1 mb-4">Mulai kelola data dengan menambahkan dataset pertama Anda.</p>
            <button @click="openCreateModal()" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Data Sekarang
            </button>
        </div>
        @endforelse
    </div>

    @if($layers->hasPages())
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        {{ $layers->links() }}
    </div>
    @endif
</div>

{{-- ================================================================
     MODAL TAMBAH/EDIT DATA
================================================================ --}}
<div x-show="isFormOpen" style="display: none;"
     class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
     @click.self="closeForm()" @keydown.escape.window="closeForm()">
    
    <div x-show="isFormOpen"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[95vh] flex flex-col">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center shrink-0">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-text="isEdit ? 'Edit Data Geospasial' : 'Tambah Data Geospasial Baru'"></span>
            </h2>
            <button @click="closeForm()" class="text-gray-400 hover:text-gray-600 transition p-1 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form :action="formAction" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto">
            @csrf
            <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
            
            <div class="p-6 space-y-5">
                {{-- Nama Layer --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Nama Dataset <span class="text-red-500">*</span></label>
                    <input type="text" name="layer_name" x-model="formData.name" required placeholder="Contoh: Batas Administrasi Kota"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition text-sm">
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select name="category_id" x-model="formData.category_id" required
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition text-sm">
                        <option value="">Pilih Kategori...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Deskripsi Singkat <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <textarea name="description" x-model="formData.description" rows="3" placeholder="Informasi tambahan terkait dataset..."
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition text-sm resize-none"></textarea>
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">
                        File Geospasial <span x-show="!isEdit" class="text-red-500">*</span>
                    </label>
                    <div class="file-drop-area" :class="{ 'dragover': isDragging }"
                         @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="isDragging = false; handleDrop($event)">
                        <input type="file" name="geospatial_file" class="file-input-hidden" 
                               accept=".zip,.geojson,.json,.shp,.kml,.gpx" @change="handleFileSelect" :required="!isEdit">
                        
                        <div class="pointer-events-none flex flex-col items-center justify-center">
                            <div class="w-12 h-12 bg-indigo-100 text-indigo-500 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-700 mb-1" x-text="fileName ? fileName : 'Pilih file atau tarik & lepas di sini'"></p>
                            <p class="text-xs text-gray-500" x-show="!fileName">Format didukung: ZIP, GeoJSON, SHP, KML (Max 100MB)</p>
                            
                            <template x-if="isEdit && !fileName">
                                <div class="mt-3 inline-flex items-center gap-1.5 bg-yellow-50 text-yellow-700 px-3 py-1 rounded-md text-[11px] font-bold border border-yellow-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Biarkan kosong jika tidak ingin mengubah file (<span x-text="formData.filename"></span>)
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-2xl shrink-0">
                <button type="button" @click="closeForm()" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold bg-white hover:bg-gray-50 transition text-sm">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-bold hover:from-indigo-700 hover:to-blue-700 shadow-lg shadow-indigo-200 flex items-center gap-2 transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================
     MODAL HAPUS DATA
================================================================ --}}
<div x-show="isDeleteOpen" style="display: none;"
     class="fixed inset-0 z-[202] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
     @click.self="isDeleteOpen = false" @keydown.escape.window="isDeleteOpen = false">
    
    <div x-show="isDeleteOpen"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
        
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">Hapus Data Geospasial?</h3>
        <p class="text-sm text-gray-500 mb-6">Anda yakin ingin menghapus <strong class="text-gray-800" x-text="delName"></strong>? Tindakan ini tidak dapat dibatalkan dan file akan dihapus dari server.</p>
        
        <form :action="delAction" method="POST" class="flex justify-center gap-3">
            @csrf
            @method('DELETE')
            <button type="button" @click="isDeleteOpen = false" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition text-sm">
                Batal
            </button>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 shadow-lg shadow-red-200 transition text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Ya, Hapus
            </button>
        </form>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('geoManager', () => ({
        search: '',
        
        // Modal State
        isFormOpen: false,
        isEdit: false,
        formAction: '',
        
        formData: {
            id: null,
            name: '',
            category_id: '',
            description: '',
            filename: ''
        },
        
        // File UI
        isDragging: false,
        fileName: '',

        // Delete Modal
        isDeleteOpen: false,
        delAction: '',
        delName: '',

        openCreateModal() {
            this.isEdit = false;
            this.formAction = "{{ route('produsen.geospasial.store') }}";
            this.formData = { id: null, name: '', category_id: '', description: '', filename: '' };
            this.fileName = '';
            this.isFormOpen = true;
            document.body.style.overflow = 'hidden';
        },

        openEditModal(data) {
            this.isEdit = true;
            this.formAction = "{{ url('produsen/geospasial') }}/" + data.id;
            this.formData = { ...data };
            this.fileName = '';
            this.isFormOpen = true;
            document.body.style.overflow = 'hidden';
        },

        closeForm() {
            this.isFormOpen = false;
            document.body.style.overflow = '';
        },

        openDeleteModal(id, name) {
            this.delAction = "{{ url('produsen/geospasial') }}/" + id;
            this.delName = name;
            this.isDeleteOpen = true;
        },

        handleFileSelect(e) {
            if (e.target.files.length > 0) {
                this.fileName = e.target.files[0].name;
            } else {
                this.fileName = '';
            }
        },

        handleDrop(e) {
            if (e.dataTransfer.files.length > 0) {
                const input = document.querySelector('.file-input-hidden');
                input.files = e.dataTransfer.files;
                this.fileName = input.files[0].name;
            }
        }
    }));
});
</script>
@endpush
