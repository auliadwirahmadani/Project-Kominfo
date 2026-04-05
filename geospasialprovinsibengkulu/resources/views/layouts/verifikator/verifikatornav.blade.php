<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Geoportal Provinsi Bengkulu - Verifikator')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <aside id="sidebar" class="sidebar fixed left-0 top-0 w-64 sidebar-glass text-white h-screen flex flex-col z-50">

        <div class="h-20 flex items-center px-6 border-b border-white/10">
            <a href="{{ route('geo') ?? '#' }}" class="flex items-center gap-3 logo-hover">
                <img src="{{ asset('logo provinsi bengkulu.png') }}"
                     alt="Logo Geoportal"
                     class="h-10 w-auto object-contain"
                     onerror="this.src='https://via.placeholder.com/40x40/991b1b/ffffff?text=G'">

                <div class="leading-tight">
                    <div class="text-xs text-red-200 tracking-wider uppercase">Geoportal</div>
                    <div class="text-sm font-bold tracking-wide uppercase">Provinsi Bengkulu</div>
                </div>
            </a>

            <button onclick="toggleSidebar()" class="lg:hidden ml-auto text-white/80 hover:text-white p-2">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto sidebar-scroll py-4">
            <nav class="px-3 space-y-1">

                <a href="{{ route('verifikator.dashboard') }}"
                   class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg
                   {{ Request::routeIs('verifikator.dashboard') ? 'menu-active bg-white/10 text-white' : 'hover:bg-white/10 text-red-200' }}">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="{{ route('verifikator.geospasial.index') }}"
                   class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg
                   {{ Request::routeIs('verifikator.geospasial*') ? 'menu-active bg-white/10 text-white' : 'hover:bg-white/10 text-red-200' }}">
                    <i class="fas fa-map-marked-alt w-5"></i>
                    <span class="font-medium">Periksa Data Geospasial</span>
                </a>

                <a href="{{ route('verifikator.metadata.index') }}"
                   class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg
                   {{ Request::routeIs('verifikator.metadata*') ? 'menu-active bg-white/10 text-white' : 'hover:bg-white/10 text-red-200' }}">
                    <i class="fas fa-file-contract w-5"></i>
                    <span class="font-medium">Periksa Metadata</span>
                </a>

                <a href="{{ route('verifikator.monitoring.index') }}"
                   class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg
                   {{ Request::routeIs('verifikator.monitoring*') ? 'menu-active bg-white/10 text-white' : 'hover:bg-white/10 text-red-200' }}">
                    <i class="fas fa-clipboard-list w-5"></i>
                    <span class="font-medium">Monitoring Status</span>
                </a>

            </nav>
        </div>

        <div class="p-4 border-t border-white/10">
            @auth
            @php
                $user = auth()->user();
                $initial = strtoupper(substr($user->name ?? 'V', 0, 1));
            @endphp

            <div class="profile-card p-3 rounded-xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-red-700 font-bold shrink-0">
                    {{ $initial }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold truncate">{{ $user->name ?? 'Verifikator' }}</div>
                    <div class="text-xs text-red-200 truncate">{{ $user->email ?? 'verifikator@bengkulu.go.id' }}</div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hover:bg-white/10 p-2 rounded-lg transition-colors" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
            @endauth
        </div>

    </aside>

    <main class="main-content flex-1 ml-64 flex flex-col min-h-screen">

        <header class="h-16 topbar-glass flex items-center px-8 sticky top-0 z-30">
            <button onclick="toggleSidebar()" class="lg:hidden mr-4 text-gray-600 hover:text-gray-900">
                <i class="fas fa-bars text-xl"></i>
            </button>

            <h2 class="text-xl font-bold text-gray-800">
                @yield('page-title', 'Panel Verifikator')
            </h2>
        </header>

        <div class="p-8 flex-1">
            <div class="content-card p-6">

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

        <footer class="px-8 py-4 text-center text-sm text-gray-500 border-t border-gray-100">
            &copy; {{ date('Y') }} Geoportal Provinsi Bengkulu. All rights reserved.
        </footer>

    </main>

</div>

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