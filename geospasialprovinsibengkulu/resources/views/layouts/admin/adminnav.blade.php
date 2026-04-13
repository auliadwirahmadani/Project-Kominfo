<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Geoportal Provinsi Bengkulu')</title>

    <!-- ✅ Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ✅ Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ✅ Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc, #eef2f7);
        }

        .sidebar-glass {
            background: linear-gradient(180deg, #991b1b 0%, #7f1d1d 100%);
            backdrop-filter: blur(10px);
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.05),
                0 20px 50px rgba(0,0,0,0.3);
        }

        .menu-item {
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: white;
            opacity: 0;
            transition: 0.3s;
        }

        .menu-item:hover::before { opacity: 0.4; }
        .menu-active::before { opacity: 1; }
        .menu-item:hover { transform: translateX(4px); }

        .profile-card {
            background: linear-gradient(135deg,
                rgba(255,255,255,0.08),
                rgba(255,255,255,0.02));
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(6px);
        }

        .topbar-glass {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .content-card {
            background: white;
            border-radius: 16px;
            box-shadow:
                0 10px 25px rgba(0,0,0,0.05),
                0 2px 5px rgba(0,0,0,0.03);
        }

        .sidebar-scroll::-webkit-scrollbar { width: 6px; }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
        }

        .logo-hover { transition: 0.3s; }
        .logo-hover:hover { transform: scale(1.05); }

        /* ✅ Responsive Sidebar */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 40;
            }
            .sidebar-overlay.active { display: block; }
        }
    </style>

    @stack('styles')
</head>

<body>

<div class="flex min-h-screen">

    <!-- ✅ Overlay untuk Mobile -->
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="sidebar fixed left-0 top-0 w-64 sidebar-glass text-white h-screen flex flex-col z-50">

        <!-- LOGO -->
        <div class="h-20 flex items-center px-6 border-b border-white/10">
            <a href="{{ route('geo') }}" class="flex items-center gap-3 logo-hover">
                <!-- ✅ Logo dengan fallback & tanpa spasi di nama file -->
                <img src="{{ asset('logo provinsi bengkulu.png') }}"
                     alt="Logo Geoportal"
                     class="h-10 w-auto object-contain"
                     onerror="this.src='https://via.placeholder.com/40x40/991b1b/ffffff?text=G'">

                <div class="leading-tight">
                    <div class="text-xs text-red-200 tracking-wider uppercase">Geoportal</div>
                    <div class="text-sm font-bold tracking-wide uppercase">Provinsi Bengkulu</div>
                </div>
            </a>

            <!-- ✅ Close button mobile -->
            <button onclick="toggleSidebar()" class="lg:hidden ml-auto text-white/80 hover:text-white p-2">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- PROFILE CARD DI ATAS --}}
        @auth
        @php
            $user = auth()->user();
            $initial = strtoupper(substr($user->name ?? 'A', 0, 1));
            // Cek foto profile jika ada
            $profile = $user->profile ?? null;
            $photoUrl = ($profile && $profile->photo && file_exists(public_path('storage/' . $profile->photo)))
                ? asset('storage/' . $profile->photo)
                : null;
        @endphp
        <div class="p-4 border-b border-white/10 flex-shrink-0">
            <div class="profile-card p-3 rounded-xl flex items-center gap-3 mb-3">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Foto" class="w-10 h-10 rounded-full object-cover border-2 border-white/50 shrink-0">
                @else
                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-red-700 font-bold shrink-0">
                        {{ $initial }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold truncate">{{ $user->name }}</div>
                    <div class="text-xs text-red-200 truncate">{{ $user->email }}</div>
                </div>
            </div>

            {{-- Tombol Edit Profil --}}
            <a href="{{ route('admin.profile') }}" 
               class="w-full flex items-center justify-center gap-2 py-2 rounded-lg {{ Request::routeIs('admin.profile*') ? 'bg-white/20 text-white' : 'bg-white/10 text-red-100 hover:bg-white/20 hover:text-white' }} transition-colors text-sm font-medium">
                <i class="fas fa-user-edit"></i> Edit Profil Saya
            </a>
        </div>
        @endauth

        <!-- MENU -->
        <div class="flex-1 overflow-y-auto sidebar-scroll py-4">
            <nav class="px-3 space-y-1">

                <!-- DASHBOARD -->
                <a href="{{ route('admin.dashboard') }}"
                   class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg
                   {{ Request::routeIs('admin.dashboard') ? 'menu-active bg-white/10 text-white' : 'hover:bg-white/10 text-red-200' }}">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <!-- KELOLA PENGGUNA -->
                <a href="{{ route('admin.kelolapengguna') }}" 
                   class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg
                   {{ Request::routeIs('admin.kelolapengguna*') ? 'menu-active bg-white/10 text-white' : 'hover:bg-white/10 text-red-200' }}">
                    <i class="fas fa-users-cog w-5"></i>
                    <span class="font-medium">Kelola Pengguna</span>
                </a>

                <!-- ✅ DATA GEOSPASIAL (Dipindah mendahului Data Metadata) -->
                <a href="{{ route('admin.geospasial.index') }}"
                   class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg
                   {{ Request::routeIs('admin.geospasial*') ? 'menu-active bg-white/10 text-white' : 'hover:bg-white/10 text-red-200' }}">
                    <i class="fas fa-map-marked-alt w-5"></i>
                    <span class="font-medium">Data Geospasial</span>
                </a>

                <!-- MASTER REFERENSI (Data Metadata) -->
                <a href="{{ route('admin.masterreferensi') }}"
                   class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg
                   {{ Request::routeIs('admin.masterreferensi*') ? 'menu-active bg-white/10 text-white' : 'hover:bg-white/10 text-red-200' }}">
                    <i class="fas fa-book w-5"></i>
                    <span class="font-medium">Data Metadata</span>
                </a>

                <!-- PUBLIKASI DATA -->
               <a href="{{ route('admin.publikasi') }}"
                   class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg
                   {{ Request::routeIs('admin.publikasi*') ? 'menu-active bg-white/10 text-white' : 'hover:bg-white/10 text-red-200' }}">
                    <i class="fas fa-globe w-5"></i>
                    <span class="font-medium">Publikasi Data</span>
                </a>

            </nav>
        </div>

        {{-- LOGOUT DI PALING BAWAH --}}
        <div class="p-4 border-t border-white/10 flex-shrink-0">
            @auth
            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center justify-center gap-2 hover:bg-white/10 p-3 rounded-lg transition-colors text-red-100 hover:text-white" 
                        title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="font-medium">Keluar / Logout</span>
                </button>
            </form>
            @endauth
        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content flex-1 ml-64 flex flex-col min-h-screen">

        <!-- TOPBAR -->
        <header class="h-16 topbar-glass flex items-center px-8 sticky top-0 z-30">
            <!-- ✅ Toggle button mobile -->
            <button onclick="toggleSidebar()" class="lg:hidden mr-4 text-gray-600 hover:text-gray-900">
                <i class="fas fa-bars text-xl"></i>
            </button>

            <h2 class="text-xl font-bold text-gray-800">
                @yield('page-title', 'Panel Admin')
            </h2>
        </header>

        <!-- CONTENT -->
        <div class="p-8 flex-1">
            <div class="content-card p-6">

                <!-- ✅ Flash Messages -->
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 flex items-start gap-3 alert-dismissible">
                        <i class="fas fa-check-circle mt-0.5"></i>
                        <span>{{ session('success') }}</span>
                        <button onclick="this.closest('.alert-dismissible').remove()" class="ml-auto text-green-500 hover:text-green-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 flex items-start gap-3 alert-dismissible">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <span>{{ session('error') }}</span>
                        <button onclick="this.closest('.alert-dismissible').remove()" class="ml-auto text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                <!-- ✅ Validation Errors -->
                @if($errors->any())
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-700">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Terjadi kesalahan:</strong>
                        </div>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')

            </div>
        </div>

        <!-- FOOTER -->
        <footer class="px-8 py-4 text-center text-sm text-gray-500 border-t border-gray-100">
            &copy; {{ date('Y') }} Geoportal Provinsi Bengkulu. All rights reserved.
        </footer>

    </main>

</div>

<!-- ✅ JavaScript untuk Sidebar Toggle & Auto-hide Alerts -->
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    }

    // Auto-hide flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    });
</script>

@stack('scripts')
</body>
</html>