@extends('layouts.admin.adminnav')
@section('title', 'Publikasi Data')
@section('page-title', 'Manajemen Publikasi Data')

@push('styles')
<style>
    .pub-card {
        background: white; border-radius: 20px; padding: 24px;
        box-shadow: 0 4px 20px -4px rgba(0,0,0,0.06); border: 1px solid #f1f5f9;
    }
    
    .pub-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .pub-table th {
        background: #f8fafc; padding: 14px 16px; text-align: left;
        font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0;
    }
    .pub-table th:first-child { border-top-left-radius: 12px; }
    .pub-table th:last-child { border-top-right-radius: 12px; }
    
    .pub-table td {
        padding: 16px; font-size: 13px; color: #334155;
        border-bottom: 1px solid #f1f5f9; vertical-align: middle;
        transition: background 0.2s;
    }
    .pub-table tr:hover td { background: #fdf8ff; }
    .pub-table tr:last-child td { border-bottom: none; }

    /* Verification Badge (Mirrors Verifikator visually) */
    .v-badge { display: inline-flex; items-center; gap: 4px; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; }
    .v-badge.approved { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .v-badge.pending  { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .v-badge.rejected { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Custom Toggle Switch */
    .toggle-switch {
        position: relative; display: inline-block; width: 44px; height: 24px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e1;
        transition: .4s; border-radius: 24px;
    }
    .toggle-slider:before {
        position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
        background-color: white; transition: .4s; border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    input:checked + .toggle-slider { background-color: #3b82f6; }
    input:checked + .toggle-slider:before { transform: translateX(20px); }
    input:disabled + .toggle-slider { background-color: #f1f5f9; cursor: not-allowed; opacity: 0.7; }
    
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up { animation: fadeUp 0.5s ease-out forwards; }
</style>
@endpush

@section('content')
<div class="bg-gradient-to-br from-slate-50 via-white to-blue-50/30 min-h-screen p-4 md:p-8">
<div class="max-w-7xl mx-auto space-y-6 animate-fade-up">

    {{-- HEADER --}}
    <div class="pub-card flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen Publikasi Data</h1>
            </div>
            <p class="text-sm text-slate-500 ml-13">Kontrol akhir status publikasi dataset yang telah melewati proses verifikasi.</p>
        </div>
        
        {{-- Stats Mini --}}
        <div class="flex gap-4">
            <div class="bg-blue-50 px-4 py-2 rounded-xl border border-blue-100 text-center">
                <span class="block text-2xl font-black text-blue-600">{{ $layers->where('is_published', true)->count() }}</span>
                <span class="block text-[10px] font-bold text-blue-500 uppercase">Dipublikasi</span>
            </div>
            <div class="bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100 text-center">
                <span class="block text-2xl font-black text-emerald-600">{{ $layers->where('status_verifikasi', 'approved')->count() }}</span>
                <span class="block text-[10px] font-bold text-emerald-500 uppercase">Disetujui Verifikator</span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="font-semibold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    {{-- MAIN CONTENT --}}
    <div class="pub-card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="pub-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Dataset Info</th>
                        <th>Kategori</th>
                        <th>Kelengkapan</th>
                        <th>Status Verifikator</th>
                        <th class="text-center">Aksi Publikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($layers as $i => $layer)
                    @php 
                        $status = $layer->status_verifikasi; 
                        $isPub = $layer->is_published;
                    @endphp
                    <tr>
                        <td class="font-mono text-xs text-slate-400 font-semibold">{{ $layers->firstItem() + $i }}</td>
                        <td>
                            <p class="font-bold text-slate-800 text-sm mb-0.5 line-clamp-1" title="{{ $layer->layer_name }}">{{ $layer->layer_name }}</p>
                            <p class="text-[11px] text-slate-400 font-mono">ID: {{ $layer->geospatial_id }}</p>
                        </td>
                        <td>
                            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">
                                {{ $layer->category->category_name ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5">
                                @if($layer->metadata)
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span><span class="text-xs text-slate-600">Ada Metadata</span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span><span class="text-xs text-slate-600">Tanpa Meta</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="v-badge {{ $status }}">
                                @if($status === 'approved') ✓ Disetujui
                                @elseif($status === 'rejected') ✗ Ditolak
                                @else ⏱ Menunggu Ulasan
                                @endif
                            </span>
                        </td>
                        <td class="text-center align-middle">
                            <form action="{{ route('admin.publikasi.toggle', $layer->geospatial_id) }}" method="POST" class="inline-block" onchange="this.submit()">
                                @csrf
                                <label class="toggle-switch" title="{{ $status !== 'approved' ? 'Hanya data yang disetujui verifikator yang ideal dipublikasi' : 'Toggle Publikasi' }}">
                                    <input type="checkbox" name="is_published" {{ $isPub ? 'checked' : '' }} {{ $status === 'rejected' ? 'disabled' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </form>
                            @if($isPub)
                            <p class="text-[10px] font-bold text-blue-600 mt-1">DIPUBLIKASI</p>
                            @else
                            <p class="text-[10px] font-medium text-slate-400 mt-1">DRAFT</p>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12">
                            <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <p class="text-slate-500 font-medium">Belum ada dataset yang terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($layers->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $layers->links() }}
        </div>
        @endif
    </div>

</div>
</div>
@endsection