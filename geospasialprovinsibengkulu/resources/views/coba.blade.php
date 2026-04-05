<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Geoportal') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* Map fullscreen */
        #map {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
        }

        /* Navbar position */
        nav {
            position: fixed;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Smooth scrollbar untuk filter panel & search suggest */
        .custom-scroll {
            scrollbar-width: thin;
            scrollbar-color: #dc2626 #f3f4f6;
        }
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 3px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #dc2626;
            border-radius: 3px;
        }
    </style>

    @stack('styles')

</head>

<body class="font-sans antialiased min-h-screen flex flex-col bg-gray-50">

<nav class="fixed top-4 left-1/2 -translate-x-1/2 w-[95vw] max-w-[1440px] h-16
            bg-red-700 border border-red-800
            rounded-full shadow-lg flex items-center justify-between
            px-4 sm:px-6 z-[9999]">

    <div class="flex items-center gap-3">
        <a href="{{ route('geo') }}" class="flex items-center gap-2">
            <img src="{{ asset('Logo Provinsi Bengkulu.png') }}"
                 alt="Logo Bengkulu"
                 class="w-8 h-8 object-contain"
                 onerror="this.src='https://via.placeholder.com/32x32/b91c1c/ffffff?text=G'">
            <span class="text-white font-bold text-lg tracking-wide hidden sm:block">
                Geoportal Provinsi Bengkulu
            </span>
        </a>
    </div>

    <ul class="hidden md:flex items-center gap-8 text-white font-medium">
        @foreach($menuItems ?? [
            ['label' => 'Peta Geospasial', 'url' => '/'],
            ['label' => 'Katalog', 'url' => '/catalog'],
            ['label' => 'Tentang Kami', 'url' => '/about']
        ] as $item)
            <li>
                <a href="{{ $item['url'] }}" class="hover:text-gray-200 transition">
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="flex items-center gap-3 sm:gap-4">

        <div class="hidden lg:flex items-center gap-3 ml-2 pl-4 border-l border-red-600">
            
            <div class="relative w-72" x-data="layerSearch" x-init="initData()" @reset-search.window="selected = null">
                
                <button @click="toggle()" type="button" 
                        class="w-full flex items-center justify-between pl-4 pr-3 py-2 bg-red-800/50 border border-red-500 rounded-full text-white text-sm hover:bg-red-800 transition focus:outline-none">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="truncate text-red-100" x-text="selected ? selected.name : 'Cari data layer...'"></span>
                    </div>
                    <svg class="w-4 h-4 text-red-200 shrink-0 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div x-show="open" 
                     @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"
                     class="absolute left-0 top-full mt-5 w-full bg-white rounded-xl shadow-xl border border-gray-200 z-[1050] overflow-hidden"
                     style="display: none;">
                    
                    <div class="p-3 border-b border-gray-100 bg-gray-50/80 backdrop-blur-sm">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" x-model="query" x-ref="searchInput" @input="search()"
                                   placeholder="Ketik nama data..." 
                                   class="w-full pl-9 pr-3 py-2.5 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-gray-800 transition-all shadow-sm">
                        </div>
                    </div>

                    <ul class="max-h-60 overflow-y-auto custom-scroll bg-white">
                        <template x-for="layer in filteredLayers" :key="layer.id">
                            <li @click="selectLayer(layer)" 
                                class="px-4 py-3 text-sm text-gray-700 cursor-pointer hover:bg-red-50 transition border-b border-gray-50 last:border-0 flex items-center gap-2">
                                <span class="truncate" x-text="layer.name"></span>
                            </li>
                        </template>
                        <li x-show="filteredLayers.length === 0" class="px-4 py-6 flex flex-col items-center justify-center text-gray-500 bg-gray-50/50">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm">Data tidak ditemukan</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="relative" x-data="{ filterOpen: false }">
                <button @click="filterOpen = !filterOpen"
                        class="flex items-center gap-2 px-4 py-2 bg-red-800/50 border border-red-500 
                               rounded-full text-white text-sm hover:bg-red-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                </button>
                
                <div x-show="filterOpen" 
                     @click.outside="filterOpen = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-full mt-5 w-72 bg-white rounded-xl shadow-2xl py-3 z-[1050]"
                     style="display: none;">
                    
                    <div class="px-4 pb-2 border-b border-gray-100">
                        <h4 class="font-semibold text-gray-800 text-sm">Filter Data</h4>
                    </div>
                    
                    <div class="px-4 py-3 space-y-4 max-h-80 overflow-y-auto custom-scroll">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">Kategori</label>
                            <select id="filterCategory" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none">
                                <option value="">Semua Kategori</option>
                                @foreach(\App\Models\Category::all() as $category)
                                    <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">Tahun</label>
                            <select id="filterYear" 
                                    class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none">
                                <option value="">Semua Tahun</option>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    
                    <div class="px-4 pt-2 border-t border-gray-100 flex gap-2">
                        <button id="resetFilter" 
                                class="flex-1 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 
                                       rounded-lg hover:bg-gray-200 transition">
                            Reset
                        </button>
                        <button id="btnTerapkan" 
                                class="flex-1 px-3 py-2 text-xs font-medium text-white bg-red-600 
                                       rounded-lg hover:bg-red-700 transition">
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:hidden flex items-center">
            <button id="mobileSearchToggle" 
                    class="p-2 text-white hover:text-gray-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </div>

        @guest
            <a href="{{ route('login') }}"
               class="flex items-center gap-2 bg-white text-red-700 px-4 py-2 rounded-full
                      font-medium text-sm hover:bg-red-100 transition shadow">
                Masuk
            </a>
        @else
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center gap-2 text-white hover:text-gray-200">
                    <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                </button>
            </div>
        @endguest
    </div>

    <div id="mobileSearchPanel" 
         x-data="{ open: false }"
         x-show="open"
         @click.outside="open = false"
         class="lg:hidden absolute top-full left-4 right-4 mt-4 p-4 bg-white rounded-xl shadow-2xl z-[1050]"
         style="display: none;">
        
        <div class="relative mb-3" x-data="layerSearch" x-init="initData()" @reset-search.window="selected = null">
            <button @click="toggle()" type="button" 
                    class="w-full flex items-center justify-between px-4 py-3 bg-white text-gray-800 border border-gray-300 rounded-lg shadow-sm focus:outline-none">
                <span class="truncate text-sm font-medium text-gray-500" x-text="selected ? selected.name : 'Cari data layer...'"></span>
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="open" 
                 @click.outside="open = false"
                 class="absolute left-0 top-full mt-2 w-full bg-white rounded-xl shadow-xl border border-gray-200 z-[1060] overflow-hidden" style="display: none;">
                
                <div class="p-3 border-b border-gray-100 bg-gray-50">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" x-model="query" x-ref="searchInput" @input="search()"
                               placeholder="Ketik nama data..." 
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 text-gray-800">
                    </div>
                </div>
                
                <ul class="max-h-48 overflow-y-auto custom-scroll bg-white">
                    <template x-for="layer in filteredLayers" :key="layer.id">
                        <li @click="selectLayer(layer); document.getElementById('mobileSearchPanel').style.display='none';" 
                            class="px-4 py-3 text-sm text-gray-700 cursor-pointer hover:bg-red-50 border-b border-gray-50 last:border-0"
                            x-text="layer.name">
                        </li>
                    </template>
                    <li x-show="filteredLayers.length === 0" class="px-4 py-4 text-sm text-gray-500 text-center italic flex flex-col items-center">
                        Data tidak ditemukan
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="flex gap-2">
            <select id="mobileFilterCategory" class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none">
                <option value="">Semua Kategori</option>
                @foreach(\App\Models\Category::all() as $category)
                    <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
            <button id="mobileApplyFilter" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition whitespace-nowrap">
                Filter
            </button>
        </div>
    </div>

</nav>

<main class="flex-grow">
    @yield('content')
</main>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// ==========================================
// ALPINE.JS KOMPONEN DROPDOWN SEARCH
// ==========================================
document.addEventListener('alpine:init', () => {
    Alpine.data('layerSearch', () => ({
        open: false,
        query: '',
        layers: [],          // Data asli dari database
        filteredLayers: [],  // Data hasil filter ketikan
        selected: null,      // Layer yang dipilih

        initData() {
            // Mengambil data secara langsung menggunakan DB Facade Blade ke dalam JSON Array.
            // PENTING: Jika nama kolom judul layer di DB Anda BUKAN 'name' (misal 'nama_layer'), 
            // ubah kode di bawah menjadi: select('id', 'nama_layer as name')
            this.layers = @json(\Illuminate\Support\Facades\DB::table('geospatial_layer')
                            ->select('geospatial_id as id', 'layer_name as name')
                            ->get());
            
            this.filteredLayers = this.layers;
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.query = ''; 
                this.filteredLayers = this.layers;
                // Fokus ke input text
                setTimeout(() => this.$refs.searchInput.focus(), 50);
            }
        },

        search() {
            if (this.query.trim() === '') {
                this.filteredLayers = this.layers;
            } else {
                const q = this.query.toLowerCase();
                this.filteredLayers = this.layers.filter(l => 
                    l.name.toLowerCase().includes(q)
                );
            }
        },

        selectLayer(layer) {
            this.selected = layer;
            this.open = false;
            
            // Perintahkan file blade lainnya (contoh: geo.blade.php) untuk menggambar layer ini di peta
            if (typeof window.loadMapData !== 'undefined') {
                window.loadMapData([layer]);
            } else {
                console.warn("Fungsi window.loadMapData belum terpasang di peta Anda.");
            }
        }
    }));
});

// ==========================================
// VANILLA JS UNTUK FILTER LAINNYA
// ==========================================
document.addEventListener('DOMContentLoaded', function() {

    // Mobile Search Panel Toggle
    const mobileSearchToggle = document.getElementById('mobileSearchToggle');
    const mobileSearchPanel = document.getElementById('mobileSearchPanel');
    
    if(mobileSearchToggle && mobileSearchPanel) {
        mobileSearchToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = mobileSearchPanel.style.display === 'block';
            mobileSearchPanel.style.display = isOpen ? 'none' : 'block';
        });
    }

    // Tombol Reset Filter
    const resetBtn = document.getElementById('resetFilter');
    if(resetBtn) {
        resetBtn.addEventListener('click', () => {
            const categorySelect = document.getElementById('filterCategory');
            const yearSelect = document.getElementById('filterYear');
            if(categorySelect) categorySelect.value = '';
            if(yearSelect) yearSelect.value = '';
            
            // Mereset label pada komponen Search Alpine
            window.dispatchEvent(new CustomEvent('reset-search'));

            // Memanggil klik terapkan agar peta direset
            document.getElementById('btnTerapkan').click();
        });
    }

    // Tombol Apply Filter Mobile
    const mobileApplyBtn = document.getElementById('mobileApplyFilter');
    if(mobileApplyBtn) {
        mobileApplyBtn.addEventListener('click', () => {
            document.getElementById('filterCategory').value = document.getElementById('mobileFilterCategory').value;
            document.getElementById('btnTerapkan').click();
            mobileSearchPanel.style.display = 'none';
        });
    }
});
</script>

@stack('scripts')

</body>
</html>