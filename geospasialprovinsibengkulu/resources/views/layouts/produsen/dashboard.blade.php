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
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
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

                {{-- Dipublikasikan --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-green-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Dipublikasikan</p>
                            <h3 class="text-4xl font-black text-green-600">{{ $totalPublished ?? 0 }}</h3>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600 text-xl shadow-inner">
                            <i class="fas fa-globe-asia"></i>
                        </div>
                    </div>
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
