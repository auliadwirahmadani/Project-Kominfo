@extends('layouts.datasetnav')

{{-- Cek apakah menampilkan detail atau katalog untuk judul tab --}}
@section('title', $dataset ? ($dataset->metadata->title ?? $dataset->layer_name) : 'Katalog Dataset Geospasial')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if($dataset)
            {{-- ======================================================== --}}
            {{-- TAMPILAN 1: DETAIL DATASET (Saat User Klik Detail)       --}}
            {{-- ======================================================== --}}
            <div class="flex items-center justify-between mb-8">
                <a href="{{ route('dataset.katalog') }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-red-600 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Katalog
                </a>
                <div class="flex gap-2">
                    <span class="px-3 py-1 bg-white border border-gray-200 rounded-full text-xs font-mono text-gray-500">
                        UUID: {{ $dataset->geospatial_id }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="px-3 py-1 rounded-md text-xs font-bold bg-red-100 text-red-700 uppercase tracking-wider">
                                {{ $dataset->metadata->data_type ?? 'Dataset Geospasial' }}
                            </span>
                            <span class="text-gray-400 text-sm italic">Terakhir diperbarui: {{ $dataset->updated_at->format('d M Y') }}</span>
                        </div>
                        <h1 class="text-3xl font-extrabold text-gray-900 mb-6 leading-tight">
                            {{ $dataset->metadata->title ?? $dataset->layer_name }}
                        </h1>
                        <div class="prose max-w-none text-gray-600">
                            <h4 class="text-gray-900 font-bold mb-2 uppercase text-xs tracking-widest">Abstrak / Deskripsi Data</h4>
                            <p class="leading-relaxed text-justify">
                                {{ $dataset->metadata->abstract ?? 'Deskripsi detail belum tersedia untuk dataset ini.' }}
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-microchip text-red-600"></i> Metadata Teknis (Layer)
                            </h3>
                        </div>
                        <table class="min-w-full divide-y divide-gray-100">
                            <tbody class="bg-white divide-y divide-gray-100">
                                @php
                                    $details = [
                                        ['label' => 'Sistem Proyeksi', 'value' => $dataset->metadata->coordinate_system ?? 'EPSG:4326 - WGS 84'],
                                        ['label' => 'Skala Data', 'value' => $dataset->metadata->scale ?? '-'],
                                        ['label' => 'Tahun Perolehan', 'value' => $dataset->metadata->data_year ?? '-'],
                                        ['label' => 'Format', 'value' => $dataset->file_type ? strtoupper($dataset->file_type) : 'GeoJSON'],
                                    ];
                                @endphp
                                @foreach($details as $item)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-4 text-sm font-medium text-gray-500 w-1/3">{{ $item['label'] }}</td>
                                    <td class="px-8 py-4 text-sm text-gray-900 font-semibold">{{ $item['value'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Akses Dataset</h4>
                        <div class="space-y-3">
                            <a href="{{ route('geo', ['layer_id' => $dataset->geospatial_id]) }}" class="flex items-center justify-center w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-red-100">
                                <i class="fas fa-map-location-dot mr-2"></i> Buka di Web Map
                            </a>
                            @if($dataset->file_path)
                                <a href="{{ route('dataset.download', $dataset->geospatial_id) }}" class="flex items-center justify-center w-full bg-white border-2 border-gray-200 hover:border-red-600 hover:text-red-600 text-gray-700 font-bold py-3.5 rounded-xl transition-all">
                                    <i class="fas fa-cloud-download-alt mr-2"></i> Unduh Dataset
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @else
            {{-- ======================================================== --}}
            {{-- TAMPILAN 2: GRID KATALOG (Muncul saat Filter/Pencarian)   --}}
            {{-- ======================================================== --}}
            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">Hasil Pencarian Dataset</h1>
                <p class="text-gray-500 text-sm">Menampilkan {{ $datasets->total() }} dataset yang ditemukan</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($datasets as $item)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                        <div class="p-6 flex-grow">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2 py-0.5 rounded bg-red-50 text-red-600 text-[10px] font-bold uppercase">
                                    {{ $item->metadata->data_type ?? 'Vector' }}
                                </span>
                                <span class="text-gray-400 text-[10px]">{{ $item->created_at->format('Y') }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 h-12">
                                {{ $item->metadata->title ?? $item->layer_name }}
                            </h3>
                            <p class="text-gray-500 text-xs line-clamp-3 leading-relaxed">
                                {{ $item->metadata->abstract ?? $item->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-[10px] font-medium text-gray-400 uppercase tracking-tighter">
                                <i class="fas fa-landmark mr-1"></i> {{ Str::limit($item->metadata->organization ?? 'Pemprov', 15) }}
                            </span>
                            <a href="{{ route('dataset.show', $item->geospatial_id) }}" class="text-red-600 text-xs font-bold hover:underline">
                                Lihat Detail <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <i class="fas fa-search-minus text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">Dataset tidak ditemukan. Coba kata kunci lain.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-10">
                {{ $datasets->links() }}
            </div>
        @endif

    </div>
</div>
@endsection