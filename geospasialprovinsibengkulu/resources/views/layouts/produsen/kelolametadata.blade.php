@extends('layouts.produsen.produsennav')
@section('title', 'Kelola Metadata')
@section('page-title', 'Kelola Metadata')

@push('styles')
<style>
    /* ============================================================
       KELOLA METADATA — DESIGN SYSTEM
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

    .status-stripe { height: 4px; width: 100%; }
    .status-stripe.filled { background: linear-gradient(90deg, #10b981, #34d399); }
    .status-stripe.empty  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }

    .meta-badge { display: inline-flex; align-items: center; gap: 3px; padding: 3px 8px; border-radius: 99px; font-size: 10px; font-weight: 700; }
    .meta-badge.filled { background: #d1fae5; color: #065f46; }
    .meta-badge.empty  { background: #fef3c7; color: #92400e; }

    .form-group label { display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 6px; }
    .form-input { w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm }
    
    .modal-scroll::-webkit-scrollbar { width: 5px; }
    .modal-scroll::-webkit-scrollbar-thumb { background: #ddd6fe; border-radius: 3px; }

    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .anim-card { animation: fadeUp 0.4s ease-out both; }
</style>
@endpush

@section('content')
<div x-data="metadataManager()" class="bg-gradient-to-br from-violet-50/60 via-white to-purple-50/60 min-h-screen p-4 md:p-8">
<div class="max-w-7xl mx-auto space-y-6">

    {{-- ============================================================
         HEADER & SEARCH
    ============================================================ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 anim-card">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Kelola Metadata</h1>
            <p class="text-sm text-gray-500 mt-1">Lengkapi informasi deskriptif untuk setiap dataset geospasial Anda.</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-72">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" placeholder="Cari nama layer atau judul metadata..."
                    class="pl-9 pr-3 py-2.5 w-full bg-gray-50 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:bg-white transition text-gray-700" />
            </div>
            <select x-model="statusFilter" class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 transition cursor-pointer text-gray-700 font-medium">
                <option value="">Semua Status</option>
                <option value="filled">Sudah Dilengkapi</option>
                <option value="empty">Belum Diisi</option>
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
    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-3 rounded-xl anim-card">
        <div class="flex items-center gap-2 mb-2 font-bold">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Gagal menyimpan metadata:
        </div>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    {{-- ============================================================
         GRID CONTENT
    ============================================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($layers as $i => $layer)
        @php 
            $hasMeta = $layer->metadata ? true : false; 
            $status = $hasMeta ? 'filled' : 'empty';
        @endphp
        
        <div class="card-meta anim-card relative"
             style="animation-delay: {{ $i * 0.04 }}s"
             x-show="
                (search === '' || '{{ strtolower($layer->layer_name) }}'.includes(search.toLowerCase()) || '{{ strtolower($layer->metadata->title ?? '') }}'.includes(search.toLowerCase())) &&
                (statusFilter === '' || statusFilter === '{{ $status }}')
             "
             x-transition>

            <div class="status-stripe {{ $status }}"></div>

            <div class="p-5 flex flex-col h-full">
                <div class="flex justify-between items-start mb-3 gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border {{ $hasMeta ? 'bg-violet-50 border-violet-100' : 'bg-gray-50 border-gray-100' }}">
                        <svg class="w-5 h-5 {{ $hasMeta ? 'text-violet-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="meta-badge {{ $status }}">
                        @if($hasMeta) ✓ Metadata Tersedia @else ℹ️ Belum Diisi @endif
                    </span>
                </div>

                <div class="mb-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Layer Geospasial</p>
                    <h3 class="text-sm font-bold text-gray-800 leading-snug line-clamp-2" title="{{ $layer->layer_name }}">{{ $layer->layer_name }}</h3>
                </div>

                @if($hasMeta)
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 mb-4 flex-1">
                    <p class="text-[10px] font-bold text-violet-600 uppercase mb-1">Judul Metadata</p>
                    <p class="text-xs font-semibold text-gray-700 line-clamp-2 mb-2">{{ $layer->metadata->title }}</p>
                    <div class="flex items-center gap-2 text-[10px] text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Last updated: {{ $layer->metadata->updated_at->format('d M Y') }}
                    </div>
                </div>
                @else
                <div class="bg-amber-50/50 rounded-xl p-3 border border-amber-100 border-dashed mb-4 flex-1 flex flex-col justify-center items-center text-center">
                    <p class="text-[11px] text-amber-600 font-medium">Lengkapi metadata agar dataset ini dapat diverifikasi dan dipublikasikan.</p>
                </div>
                @endif

                @php
                    $metaData = [
                        'geospatial_id' => $layer->geospatial_id,
                        'layer_name' => addslashes($layer->layer_name),
                        'has_meta' => $hasMeta,
                        'id' => $layer->metadata->metadata_id ?? null,
                        'title' => $layer->metadata->title ?? '',
                        'abstract' => $layer->metadata->abstract ?? '',
                        'organization' => $layer->metadata->organization ?? '',
                        'data_type' => $layer->metadata->data_type ?? '',
                        'publication_date' => $layer->metadata->publication_date ?? '',
                        'keywords' => $layer->metadata->keywords ?? '',
                        'source' => $layer->metadata->source ?? '',
                        'year' => $layer->metadata->year ?? '',
                        'crs' => $layer->metadata->crs ?? '',
                        'scale' => $layer->metadata->scale ?? '',
                    ];
                @endphp
                <div class="flex flex-col gap-2">
                    <button type="button" @click='openForm(@json($metaData))'
                        class="w-full py-2.5 flex justify-center items-center gap-2 rounded-xl text-sm font-bold transition
                        {{ $hasMeta ? 'bg-white border-2 border-violet-100 text-violet-600 hover:bg-violet-50 hover:border-violet-200' : 'bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white shadow-lg shadow-violet-200/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ $hasMeta ? 'Edit Metadata' : 'Isi Metadata Sekarang' }}
                    </button>
                    
                    <a href="{{ url('/dataset/' . $layer->geospatial_id) }}?from=kelolametadata"
                        class="w-full py-2 flex justify-center items-center gap-2 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold border border-gray-200 transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Lihat Dataset
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-gray-50 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-600 mb-1">Belum ada dataset</h3>
            <p class="text-sm text-gray-400">Anda harus mengunggah dataset geospasial terlebih dahulu sebelum mengisi metadata.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- ================================================================
     MODAL FORM METADATA
================================================================ --}}
<div x-show="isFormOpen" style="display: none;"
     class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
     @click.self="closeForm()" @keydown.escape.window="closeForm()">
    
    <div x-show="isFormOpen"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[95vh] flex flex-col">
        
        <div class="px-6 py-4 bg-gradient-to-r from-violet-600 to-purple-600 rounded-t-2xl flex justify-between items-center shrink-0">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span x-text="formData.has_meta ? 'Edit Metadata Lengkap' : 'Isi Metadata Baru'"></span>
                </h2>
                <p class="text-xs text-violet-200 mt-0.5 font-medium flex items-center gap-1">
                    Layer: <span class="text-white font-bold" x-text="formData.layer_name"></span>
                </p>
            </div>
            <button @click="closeForm()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form :action="formAction" method="POST" class="flex-1 overflow-hidden flex flex-col">
            @csrf
            <template x-if="formData.has_meta"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="geospatial_id" x-model="formData.geospatial_id">
            
            <div class="flex-1 overflow-y-auto modal-scroll p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    {{-- Row 1 --}}
                    <div class="md:col-span-2 form-group">
                        <label>Judul Metadata <span class="text-red-500">*</span></label>
                        <input type="text" name="title" x-model="formData.title" required placeholder="Judul representatif untuk dataset ini..."
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm">
                    </div>

                    {{-- Row 2 --}}
                    <div class="form-group">
                        <label>Organisasi / Instansi</label>
                        <input type="text" name="organization" x-model="formData.organization" placeholder="Nama instansi pemilik data..."
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm">
                    </div>
                    <div class="form-group">
                        <label>Sumber Peta (Source)</label>
                        <input type="text" name="source" x-model="formData.source" placeholder="Contoh: BAPPEDA Prov. Bengkulu"
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm">
                    </div>

                    {{-- Row 3 --}}
                    <div class="form-group">
                        <label>Tipe Data (Data Type)</label>
                        <input type="text" name="data_type" x-model="formData.data_type" placeholder="Contoh: Vector / Raster"
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm">
                    </div>
                    <div class="form-group">
                        <label>Tahun Data</label>
                        <input type="number" name="year" x-model="formData.year" placeholder="Contoh: 2023"
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm">
                    </div>

                    {{-- Row 4 --}}
                    <div class="form-group">
                        <label>Sistem Koordinat (CRS)</label>
                        <input type="text" name="crs" x-model="formData.crs" placeholder="Contoh: EPSG:4326 (WGS 84)"
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm text-mono">
                    </div>
                    <div class="form-group">
                        <label>Skala</label>
                        <input type="text" name="scale" x-model="formData.scale" placeholder="Contoh: 1:50.000"
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm">
                    </div>

                    {{-- Row 5 --}}
                    <div class="form-group">
                        <label>Tanggal Publikasi Asli</label>
                        <input type="date" name="publication_date" x-model="formData.publication_date"
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm">
                    </div>
                    <div class="form-group">
                        <label>Kata Kunci (Keywords)</label>
                        <input type="text" name="keywords" x-model="formData.keywords" placeholder="Pisahkan dengan koma (batas, wilayah, administrasi)"
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm">
                    </div>

                    {{-- Row 6 --}}
                    <div class="md:col-span-2 form-group">
                        <label>Abstrak / Deskripsi Lengkap</label>
                        <textarea name="abstract" x-model="formData.abstract" rows="4" placeholder="Jelaskan secara detail mengenai dataset, referensi metodologi, atau batasan penggunaan..."
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:bg-white transition text-sm resize-none"></textarea>
                    </div>

                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-2xl shrink-0">
                <button type="button" @click="closeForm()" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold bg-white hover:bg-gray-50 transition text-sm">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 text-white font-bold hover:from-violet-700 hover:to-purple-700 shadow-lg shadow-violet-200 flex items-center gap-2 transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Simpan Metadata
                </button>
            </div>
        </form>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('metadataManager', () => ({
        search: '',
        statusFilter: '',
        
        isFormOpen: false,
        formAction: '',
        
        formData: {},

        openForm(data) {
            this.formData = { ...data };
            if (data.has_meta) {
                this.formAction = "{{ url('produsen/metadata') }}/" + data.id;
            } else {
                this.formAction = "{{ route('produsen.metadata.store') }}";
            }
            this.isFormOpen = true;
            document.body.style.overflow = 'hidden';
        },

        closeForm() {
            this.isFormOpen = false;
            document.body.style.overflow = '';
        }
    }));
});
</script>
@endpush
