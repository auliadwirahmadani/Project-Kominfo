@extends('layouts.admin.adminnav')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Admin')

{{-- Section Content --}}
@section('content')
<div class="flex-1 p-8 overflow-y-auto bg-gray-50 transition-all duration-300">
    
    <!-- Welcome Card dengan Animasi -->
    <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-xl shadow-lg p-6 mb-8 text-white transform hover:scale-[1.01] transition-transform duration-300 relative overflow-hidden group">
        <!-- Background Pattern -->
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-10 -translate-y-10 group-hover:translate-x-5 group-hover:translate-y-5 transition-transform duration-500">
            <i class="fas fa-globe-asia text-9xl"></i>
        </div>
        
        <div class="relative z-10">
            <h1 class="text-2xl font-bold mb-2">Selamat Datang di Portal Data Geospasial</h1>
            <p class="text-red-100">Kelola data geospasial dan referensi wilayah dalam satu platform terintegrasi.</p>
            <div class="mt-4 flex gap-3">
                <a href="{{ route('admin.geospasial.index') }}" class="bg-white text-red-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-50 transition-colors shadow-md inline-block">
                    <i class="fas fa-plus mr-2"></i>Tambah Data
                </a>
                <button class="bg-red-800 bg-opacity-50 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-opacity-70 transition-colors">
                    <i class="fas fa-download mr-2"></i>Export Laporan
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Grid dengan Hover Effects -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Total Pengguna -->
        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Total Pengguna')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Total Pengguna</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="{{ $totalUsers }}">0</h3>
                    <p class="text-xs text-green-500 mt-2 flex items-center">
                        <i class="fas fa-check-circle mr-1"></i>Sistem Aktif
                    </p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-red-600 h-1.5 rounded-full" style="width: 100%"></div>
            </div>
        </div>

        <!-- Card 2: Data Geospasial -->
        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Data Geospasial')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Data Geospasial</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="{{ $totalLayers }}">0</h3>
                    <p class="text-xs text-blue-500 mt-2 flex items-center">
                        <i class="fas fa-layer-group mr-1"></i>Layer Tersimpan
                    </p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-map-marked-alt text-xl"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: 100%"></div>
            </div>
        </div>

        <!-- Card 3: Publikasi -->
        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Publikasi')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Publikasi</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="{{ $totalPublished }}">0</h3>
                    <p class="text-xs text-green-500 mt-2 flex items-center">
                        <i class="fas fa-globe mr-1"></i>Tampil di Web Publik
                    </p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-file-export text-xl"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                @php
                    $publishPercentage = $totalLayers > 0 ? ($totalPublished / $totalLayers) * 100 : 0;
                @endphp
                <div class="bg-green-600 h-1.5 rounded-full" style="width: {{ $publishPercentage }}%"></div>
            </div>
        </div>

        <!-- Card 4: Kategori Data -->
        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Kategori Data')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Kategori Data</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="{{ $totalCategories }}">0</h3>
                    <p class="text-xs text-purple-500 mt-2 flex items-center">
                        <i class="fas fa-tags mr-1"></i>Grup Klasifikasi
                    </p>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-folder-open text-xl"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-purple-600 h-1.5 rounded-full" style="width: 100%"></div>
            </div>
        </div>
    </div>

    <!-- ADVANCED DASHBOARD SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- KOLOM KIRI (Aktivitas MIngguan & Terbaru) - Lebar 2/3 -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Grafik Mingguan ApexCharts -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800 text-lg">Statistik Upload (7 Hari Terakhir)</h3>
                    <span class="text-xs font-semibold text-red-600 bg-red-50 px-3 py-1 rounded-full">Real-time Data</span>
                </div>
                <div id="weeklyChart" class="w-full h-64"></div>
            </div>

            <!-- Tabel Aktivitas Terbaru -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <h3 class="font-bold text-gray-800 text-lg">Aktivitas Upload Terbaru</h3>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchActivity" placeholder="Cari aktivitas..." 
                                class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 w-full md:w-56 transition-all">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <a href="{{ route('admin.geospasial.index') }}" class="text-sm text-red-600 hover:text-red-700 font-medium px-4 py-2 hover:bg-red-50 rounded-lg transition-colors whitespace-nowrap">
                            Semua
                        </a>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4" id="activityList">
                        @forelse($recentActivities as $activity)
                            <div class="activity-item flex items-center gap-4 p-4 rounded-lg bg-gray-50 border border-transparent hover:bg-white hover:shadow-md hover:border-gray-200 transition-all cursor-pointer">
                                
                                {{-- Icon Dinamis --}}
                                @if($activity->status_verifikasi == 'approved')
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0">
                                        <i class="fas fa-check"></i>
                                    </div>
                                @elseif($activity->status_verifikasi == 'pending')
                                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 shrink-0">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                                        <i class="fas fa-upload"></i>
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-800 truncate">{{ $activity->layer_name }}</h4>
                                    <p class="text-sm text-gray-500 truncate">Kategori: {{ $activity->category->category_name ?? 'Tanpa Kategori' }}</p>
                                </div>
                                
                                <div class="text-right shrink-0">
                                    @if($activity->is_published)
                                        <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-[10px] font-bold rounded-full uppercase tracking-wide">Publik</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-gray-200 text-gray-700 text-[10px] font-bold rounded-full uppercase tracking-wide">Draft</span>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1.5">{{ $activity->created_at ? $activity->created_at->diffForHumans() : 'Waktu tidak diketahui' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-8">
                                <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                                <p>Belum ada aktivitas.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Empty State untuk Search -->
                    <div id="emptyState" class="hidden text-center text-gray-400 py-8">
                        <i class="fas fa-search text-4xl mb-3 opacity-50"></i>
                        <p>Tidak ada aktivitas yang ditemukan.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN (Distribusi Kategori & User Baru) - Lebar 1/3 -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Card Kategori Top -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="font-bold text-gray-800 text-md">Distribusi Kategori</h3>
                    <i class="fas fa-chart-pie text-gray-400"></i>
                </div>
                
                <div class="space-y-4">
                    @forelse($categoryDistribution as $cat)
                        @php
                            $percentage = $totalLayers > 0 ? ($cat->geospatial_layers_count / $totalLayers) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between items-center text-sm font-medium mb-1.5">
                                <span class="text-gray-700">{{ $cat->category_name }}</span>
                                <span class="text-gray-900 font-bold">{{ $cat->geospatial_layers_count }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">Belum ada kategori terisi data.</p>
                    @endforelse
                </div>
            </div>

            <!-- Card Pengguna Baru -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="font-bold text-gray-800 text-md">Pengguna Baru</h3>
                    <a href="{{ route('admin.kelolapengguna') }}" class="text-xs text-red-600 font-semibold hover:underline">Kelola</a>
                </div>

                <div class="space-y-4">
                    @forelse($recentUsers as $newuser)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center shrink-0 border border-gray-200">
                                <span class="text-gray-600 font-bold text-sm">{{ strtoupper(substr($newuser->name, 0, 1)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800 truncate">{{ $newuser->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $newuser->role_name ?? 'Verifikator' }}</p>
                            </div>
                            <span class="text-[10px] text-gray-400 shrink-0">{{ $newuser->created_at ? $newuser->created_at->format('d M') : '-' }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">Belum ada user baru.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-5 right-5 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 z-50">
    <div class="flex items-center gap-3">
        <i class="fas fa-info-circle text-blue-400"></i>
        <span id="toastMessage">Notification message</span>
    </div>
</div>

<script>
    // Counter Animation for Stats
    function animateCounters() {
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            
            // Cegah error jika datanya 0
            if (target === 0) {
                counter.innerText = "0";
                return;
            }

            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16); // 60fps
            
            let current = 0;
            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    counter.innerText = Math.ceil(current).toLocaleString();
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.innerText = target.toLocaleString();
                }
            };
            updateCounter();
        });
    }

    // Run counter animation on load
    document.addEventListener('DOMContentLoaded', animateCounters);

    // Show Detail on Card Click
    function showDetail(cardName) {
        showToast(`Detail ${cardName} bisa dilihat di menu samping.`);
    }

    // Search Functionality
    document.getElementById('searchActivity').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const activities = document.querySelectorAll('.activity-item');
        let hasResults = false;

        activities.forEach(activity => {
            const text = activity.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                activity.style.display = 'flex';
                hasResults = true;
            } else {
                activity.style.display = 'none';
            }
        });

        // Show/hide empty state
        document.getElementById('emptyState').classList.toggle('hidden', hasResults);
    });

    // Toast Notification
    function showToast(message) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
        toastMessage.textContent = message;
        toast.classList.remove('translate-y-20', 'opacity-0');
        
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
    }
</script>

<!-- ApexCharts Script -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var rawDates = @json($weeklyDates);
        var rawCounts = @json($weeklyCounts);

        var options = {
            series: [{
                name: 'Dataset Baru',
                data: rawCounts
            }],
            chart: {
                height: 250,
                type: 'area',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#dc2626'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: {
                categories: rawDates,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#94a3b8' } }
            },
            yaxis: {
                labels: { style: { colors: '#94a3b8' }, formatter: (val) => { return Math.floor(val) } }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } }
            },
            tooltip: {
                theme: 'light',
                y: { formatter: function (val) { return val + " Layers" } }
            }
        };

        var chart = new ApexCharts(document.querySelector("#weeklyChart"), options);
        chart.render();
    });
</script>
@endsection