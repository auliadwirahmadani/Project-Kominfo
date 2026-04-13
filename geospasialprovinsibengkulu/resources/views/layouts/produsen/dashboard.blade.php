@extends('layouts.produsen.produsennav')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Produsen Data')

@section('content')
<div class="bg-gradient-to-br from-red-50 via-white to-red-50 min-h-screen p-4 md:p-8">
    <div class="max-w-7xl mx-auto space-y-8">

        {{-- ================================================================
             GREETING BANNERS
        ================================================================ --}}
        <div class="bg-gradient-to-r from-red-800 to-[#8b0000] rounded-2xl p-8 shadow-xl shadow-red-900/20 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 opacity-10 pointer-events-none translate-x-1/4 -translate-y-1/4">
                <svg width="400" height="400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
            </div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30 shrink-0">
                    <i class="fas fa-satellite-dish text-3xl text-yellow-300"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black mb-2">Selamat Datang, {{ auth()->user()->name ?? 'Produsen' }}!</h1>
                    <p class="text-red-100 text-lg leading-relaxed max-w-2xl">
                        Kelola, unggah, dan distribusikan data geospasial Anda untuk mendukung perencanaan pembangunan Provinsi Bengkulu yang lebih baik.
                    </p>
                </div>
            </div>
        </div>

        {{-- ================================================================
             STATISTIK UTAMA
        ================================================================ --}}
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-red-600"></i> Ringkasan Data Anda
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                
                {{-- Total Data --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Total Data Dikirim</p>
                            <h3 class="text-4xl font-black text-gray-800">{{ $totalLayers ?? 0 }}</h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 text-xl shadow-inner">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                </div>

                {{-- Sedang Diverifikasi --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-yellow-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Menunggu Verifikasi</p>
                            <h3 class="text-4xl font-black text-yellow-600">{{ $totalPending ?? 0 }}</h3>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center text-yellow-600 text-xl shadow-inner animate-pulse">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>

                {{-- Ditolak (Baru) --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-red-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Ditolak / Revisi</p>
                            <h3 class="text-4xl font-black text-red-600">{{ $totalRejected ?? 0 }}</h3>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center text-red-600 text-xl shadow-inner">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================================================================
             MONITORING PUBLIKASI & RIWAYAT (BARU)
        ================================================================ --}}
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-satellite text-red-600"></i> Monitoring Status Publikasi
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Donut Chart Visual --}}
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 text-sm mb-4">Distribusi Data Geospasial Secara Keseluruhan</h3>

                    @php $total = max($totalLayers ?? 0, 1); @endphp
                    <div class="rounded-xl overflow-hidden h-5 w-full flex mb-4 border border-gray-200">
                        @if(($totalPublished ?? 0) > 0)
                        <div class="h-full bg-green-400 transition-all" style="width: {{ (($totalPublished ?? 0)/$total)*100 }}%" title="Dipublikasikan: {{ $totalPublished }}"></div>
                        @endif
                        @if(($totalPending ?? 0) > 0)
                        <div class="h-full bg-yellow-400 transition-all" style="width: {{ (($totalPending ?? 0)/$total)*100 }}%" title="Menunggu: {{ $totalPending }}"></div>
                        @endif
                        @if(($totalRejected ?? 0) > 0)
                        <div class="h-full bg-red-400 transition-all" style="width: {{ (($totalRejected ?? 0)/$total)*100 }}%" title="Ditolak: {{ $totalRejected }}"></div>
                        @endif
                        @if(($totalLayers ?? 0) === 0)
                        <div class="h-full bg-gray-200 w-full"></div>
                        @endif
                    </div>

                    <div class="space-y-3 mt-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-green-400"></span>
                                <span class="text-sm font-semibold text-gray-600">Dipublikasikan (Disetujui)</span>
                            </div>
                            <span class="text-sm font-black text-gray-800">{{ $totalPublished ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-yellow-400"></span>
                                <span class="text-sm font-semibold text-gray-600">Terpending (Menunggu)</span>
                            </div>
                            <span class="text-sm font-black text-gray-800">{{ $totalPending ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-red-400"></span>
                                <span class="text-sm font-semibold text-gray-600">Ditolak / Perlu Revisi</span>
                            </div>
                            <span class="text-sm font-black text-gray-800">{{ $totalRejected ?? 0 }}</span>
                        </div>
                    </div>
                    
                </div>

                {{-- Full Laporan Tabular --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-md border border-gray-100 relative overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm">Riwayat Laporan & Status Publikasi</h3>
                    </div>
                    
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-100">#</th>
                                    <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-100">Dataset</th>
                                    <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-100">Status</th>
                                    <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-100">Catatan</th>
                                    <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-100">Publikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($layers as $i => $layer)
                                    @php $s = $layer->status_verifikasi; @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-4 px-4 text-xs text-gray-400 font-mono border-b border-gray-50">
                                            {{ ($layers->currentPage() - 1) * $layers->perPage() + $i + 1 }}
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-50">
                                            <p class="text-sm font-bold text-gray-800 line-clamp-1" title="{{ $layer->layer_name }}">{{ $layer->layer_name }}</p>
                                            <p class="text-[11px] text-indigo-600 font-bold mt-0.5">{{ $layer->category->category_name ?? '-' }}</p>
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-50">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold
                                                {{ $s === 'approved' ? 'bg-green-100 text-green-700' : ($s === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                {{ $s === 'approved' ? 'Disetujui' : ($s === 'rejected' ? 'Ditolak' : 'Menunggu') }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-50">
                                            @if($layer->catatan_verifikator)
                                                <p class="text-[11px] text-orange-700 bg-orange-50 p-2 rounded-lg max-w-[200px] line-clamp-2" title="{{ $layer->catatan_verifikator }}">
                                                    {{ $layer->catatan_verifikator }}
                                                </p>
                                            @else
                                                <span class="text-[11px] text-gray-400 italic">Tidak ada catatan</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-50">
                                            @if($layer->is_published)
                                                <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-md"><i class="fas fa-globe mr-1"></i> Publik</span>
                                            @else
                                                <span class="text-[11px] font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded-md"><i class="fas fa-lock mr-1"></i> Private</span>
                                            @endif
                                            <p class="text-[10px] text-gray-400 mt-1">{{ $layer->updated_at->format('d M, H:i') }}</p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center">
                                            <i class="fas fa-folder-open text-3xl text-gray-300 mb-2"></i>
                                            <p class="text-sm text-gray-500">Belum ada dataset geospasial.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination Form --}}
                    @if($layers->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                        {{ $layers->links() }}
                    </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- ================================================================
             JALUR CEPAT (QUICK ACTIONS)
        ================================================================ --}}
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-red-600"></i> Aksi Cepat
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Kelola Data Geospasial --}}
                <a href="{{ route('produsen.geospasial.index') }}" class="group block bg-white rounded-2xl border border-gray-100 p-1 shadow-md hover:shadow-xl transition-all content-center content">
                    <div class="bg-slate-50 rounded-xl p-6 h-full border border-transparent group-hover:border-red-100 group-hover:bg-red-50/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-[#8b0000] text-white rounded-xl flex items-center justify-center text-2xl shadow-md group-hover:scale-110 transition-transform">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 group-hover:text-[#8b0000] transition-colors">Kelola Geospasial</h3>
                                <p class="text-sm text-gray-500 mt-1">Unggah file shapefile/geojson baru atau hapus revisi data peta Anda.</p>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- Kelola Metadata --}}
                <a href="{{ route('produsen.metadata.index') }}" class="group block bg-white rounded-2xl border border-gray-100 p-1 shadow-md hover:shadow-xl transition-all">
                    <div class="bg-slate-50 rounded-xl p-6 h-full border border-transparent group-hover:border-red-100 group-hover:bg-red-50/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-red-600 text-white rounded-xl flex items-center justify-center text-2xl shadow-md group-hover:scale-110 transition-transform">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 group-hover:text-red-700 transition-colors">Informasi Metadata</h3>
                                <p class="text-sm text-gray-500 mt-1">Lengkapi informasi dasar, sumber instansi, resolusi, dan sistem koordinat dataset.</p>
                            </div>
                        </div>
                    </div>
                </a>

            </div>
        </div>

    </div>
</div>
@endsection
