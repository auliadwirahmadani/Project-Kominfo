@extends('layouts.verifikator.verifikatornav')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Verifikator')

{{-- Section Content --}}
@section('content')
<div class="flex-1 p-8 overflow-y-auto bg-gray-50 transition-all duration-300">
    
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-6 mb-8 text-white transform hover:scale-[1.01] transition-transform duration-300 relative overflow-hidden group">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-10 -translate-y-10 group-hover:translate-x-5 group-hover:translate-y-5 transition-transform duration-500">
            <i class="fas fa-clipboard-check text-9xl"></i>
        </div>
        
        <div class="relative z-10">
            <h1 class="text-2xl font-bold mb-2">Selamat Datang di Panel Verifikasi</h1>
            <p class="text-blue-100">Pantau dan verifikasi data geospasial serta metadata yang diajukan oleh pengguna.</p>
            <div class="mt-4 flex gap-3">
                <a href="{{ route('verifikator.geospasial.index') }}" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-50 transition-colors shadow-md flex items-center">
                    <i class="fas fa-search-location mr-2"></i>Mulai Verifikasi Data
                </a>
                <button class="bg-blue-900 bg-opacity-50 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-opacity-70 transition-colors flex items-center">
                    <i class="fas fa-file-alt mr-2"></i>Riwayat Verifikasi
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Menunggu Verifikasi')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Menunggu Verifikasi</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="15">0</h3>
                    <p class="text-xs text-yellow-500 mt-2 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>Butuh tindakan segera
                    </p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg text-yellow-600 group-hover:bg-yellow-500 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-yellow-500 h-1.5 rounded-full" style="width: 15%"></div>
            </div>
        </div>

        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Data Disetujui')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Data Disetujui</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="342">0</h3>
                    <p class="text-xs text-green-500 mt-2 flex items-center">
                        <i class="fas fa-arrow-up mr-1"></i>4% dari minggu lalu
                    </p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-green-600 h-1.5 rounded-full" style="width: 80%"></div>
            </div>
        </div>

        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Perlu Revisi')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Perlu Revisi</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="8">0</h3>
                    <p class="text-xs text-orange-500 mt-2 flex items-center">
                        <i class="fas fa-undo mr-1"></i>Dikembalikan ke produsen
                    </p>
                </div>
                <div class="p-3 bg-orange-50 rounded-lg text-orange-600 group-hover:bg-orange-500 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-pencil-alt text-xl"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-orange-500 h-1.5 rounded-full" style="width: 10%"></div>
            </div>
        </div>

        <div class="stat-card bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer group" onclick="showDetail('Data Ditolak')">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Data Ditolak</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1 counter" data-target="24">0</h3>
                    <p class="text-xs text-red-500 mt-2 flex items-center">
                        <i class="fas fa-times mr-1"></i>Tidak sesuai standar
                    </p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-ban text-xl"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-red-600 h-1.5 rounded-full" style="width: 5%"></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="font-bold text-gray-800 text-lg">Antrean Verifikasi Terbaru</h3>
            
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" id="searchActivity" placeholder="Cari nama data..." 
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 w-64 transition-all">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                </div>
                
                <select class="border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-blue-500 cursor-pointer hover:border-blue-300 transition-colors">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
                
                <button class="text-sm text-blue-600 hover:text-blue-700 font-medium px-4 py-2 hover:bg-blue-50 rounded-lg transition-colors">
                    Lihat Semua
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="space-y-4" id="activityList">
                
                <div class="activity-item flex items-center gap-4 p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Peta Jaringan Jalan Kabupaten X</h4>
                        <p class="text-sm text-gray-500">Diajukan oleh: Dinas PUPR • Kategori: Infrastruktur</p>
                    </div>
                    <div class="text-right flex items-center gap-4">
                        <div class="text-right mr-2">
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">Menunggu</span>
                            <p class="text-xs text-gray-400 mt-1">Diajukan 2 jam lalu</p>
                        </div>
                        <button class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg text-sm transition-colors border border-blue-200 hover:border-transparent">
                            Periksa
                        </button>
                    </div>
                </div>

                <div class="activity-item flex items-center gap-4 p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Batas Administrasi Desa 2024</h4>
                        <p class="text-sm text-gray-500">Diajukan oleh: BAPPEDA • Kategori: Batas Wilayah</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Disetujui</span>
                        <p class="text-xs text-gray-400 mt-1">Diverifikasi oleh Anda (kemarin)</p>
                    </div>
                </div>

                <div class="activity-item flex items-center gap-4 p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                        <i class="fas fa-pencil-alt"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Sebaran Fasilitas Kesehatan</h4>
                        <p class="text-sm text-gray-500">Catatan: Metadata atribut tidak lengkap</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">Perlu Revisi</span>
                        <p class="text-xs text-gray-400 mt-1">Dikembalikan 2 hari lalu</p>
                    </div>
                </div>

            </div>

            <div id="emptyState" class="hidden text-center text-gray-400 py-8">
                <i class="fas fa-search text-4xl mb-3 opacity-50"></i>
                <p>Tidak ada data yang ditemukan.</p>
            </div>
        </div>
        
    </div>
</div>

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
        showToast(`Memuat data ${cardName}...`);
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
@endsection