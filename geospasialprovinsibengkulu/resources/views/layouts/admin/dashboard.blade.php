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
                <button class="bg-white text-red-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-50 transition-colors shadow-md">
                    <i class="fas fa-plus mr-2"></i>Tambah Data
                </button>
                <button class="bg-red-800 bg-opacity-50 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-opacity-70 transition-colors">
                    <i class="fas fa-download mr-2"></i>Export Laporan
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Grid dengan Hover Effects -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Total Pengguna')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Total Pengguna</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="1240">0</h3>
                    <p class="text-xs text-green-500 mt-2 flex items-center">
                        <i class="fas fa-arrow-up mr-1"></i>12% dari bulan lalu
                    </p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-red-600 h-1.5 rounded-full" style="width: 75%"></div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Data Geospasial')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Data Geospasial</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="856">0</h3>
                    <p class="text-xs text-blue-500 mt-2 flex items-center">
                        <i class="fas fa-arrow-up mr-1"></i>8% dari bulan lalu
                    </p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-map-marked-alt text-xl"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: 60%"></div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Publikasi')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Publikasi</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="124">0</h3>
                    <p class="text-xs text-green-500 mt-2 flex items-center">
                        <i class="fas fa-arrow-up mr-1"></i>24% dari bulan lalu
                    </p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-file-export text-xl"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-green-600 h-1.5 rounded-full" style="width: 45%"></div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Master Data')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Master Data</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">Active</h3>
                    <p class="text-xs text-purple-500 mt-2 flex items-center">
                        <i class="fas fa-check-circle mr-1"></i>Semua sistem normal
                    </p>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-database text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-xs text-gray-500">Online</span>
            </div>
        </div>
    </div>

    <!-- Table Section dengan Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="font-bold text-gray-800 text-lg">Aktivitas Terbaru</h3>
            
            <div class="flex items-center gap-3">
                <!-- Search Box -->
                <div class="relative">
                    <input type="text" id="searchActivity" placeholder="Cari aktivitas..." 
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 w-64 transition-all">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                </div>
                
                <!-- Filter Dropdown -->
                <select class="border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-red-500 cursor-pointer hover:border-red-300 transition-colors">
                    <option value="">Semua Tipe</option>
                    <option value="upload">Upload Data</option>
                    <option value="edit">Edit Data</option>
                    <option value="delete">Hapus Data</option>
                    <option value="publish">Publikasi</option>
                </select>
                
                <button class="text-sm text-red-600 hover:text-red-700 font-medium px-4 py-2 hover:bg-red-50 rounded-lg transition-colors">
                    Lihat Semua
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Activity List -->
            <div class="space-y-4" id="activityList">
                <!-- Activity Item 1 -->
                <div class="activity-item flex items-center gap-4 p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        <i class="fas fa-upload"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Upload Data Geospasial Baru</h4>
                        <p class="text-sm text-gray-500">Admin mengupload 5 dataset baru wilayah Jakarta</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Success</span>
                        <p class="text-xs text-gray-400 mt-1">5 menit yang lalu</p>
                    </div>
                </div>

                <!-- Activity Item 2 -->
                <div class="activity-item flex items-center gap-4 p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Update Profil Pengguna</h4>
                        <p class="text-sm text-gray-500">Produsen Data memperbarui informasi kontak</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">Updated</span>
                        <p class="text-xs text-gray-400 mt-1">15 menit yang lalu</p>
                    </div>
                </div>

                <!-- Activity Item 3 -->
                <div class="activity-item flex items-center gap-4 p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Permintaan Verifikasi</h4>
                        <p class="text-sm text-gray-500">Data menunggu verifikasi dari administrator</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">Pending</span>
                        <p class="text-xs text-gray-400 mt-1">1 jam yang lalu</p>
                    </div>
                </div>

                <!-- Activity Item 4 -->
                <div class="activity-item flex items-center gap-4 p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Publikasi Data</h4>
                        <p class="text-sm text-gray-500">Dataset Peta RBI telah dipublikasikan</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">Published</span>
                        <p class="text-xs text-gray-400 mt-1">2 jam yang lalu</p>
                    </div>
                </div>
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="emptyState" class="hidden text-center text-gray-400 py-8">
                <i class="fas fa-search text-4xl mb-3 opacity-50"></i>
                <p>Tidak ada aktivitas yang ditemukan.</p>
            </div>
        </div>
        
        <!-- Mini Chart Section -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            <h4 class="text-sm font-semibold text-gray-600 mb-3">Statistik Mingguan</h4>
            <div class="flex items-end gap-2 h-20">
                <div class="flex-1 bg-red-200 rounded-t hover:bg-red-300 transition-colors relative group" style="height: 40%">
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">40%</div>
                </div>
                <div class="flex-1 bg-red-300 rounded-t hover:bg-red-400 transition-colors relative group" style="height: 65%">
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">65%</div>
                </div>
                <div class="flex-1 bg-red-400 rounded-t hover:bg-red-500 transition-colors relative group" style="height: 50%">
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">50%</div>
                </div>
                <div class="flex-1 bg-red-500 rounded-t hover:bg-red-600 transition-colors relative group" style="height: 80%">
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">80%</div>
                </div>
                <div class="flex-1 bg-red-600 rounded-t hover:bg-red-700 transition-colors relative group" style="height: 75%">
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">75%</div>
                </div>
                <div class="flex-1 bg-red-500 rounded-t hover:bg-red-600 transition-colors relative group" style="height: 90%">
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">90%</div>
                </div>
                <div class="flex-1 bg-red-400 rounded-t hover:bg-red-500 transition-colors relative group" style="height: 60%">
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">60%</div>
                </div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-500">
                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
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
        showToast(`Mengambil detail ${cardName}...`);
        // Add your navigation or modal logic here
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

    // Add hover effect to activity items
    document.querySelectorAll('.activity-item').forEach(item => {
        item.addEventListener('click', function() {
            showToast('Membuka detail aktivitas...');
        });
    });
</script>
@endsection