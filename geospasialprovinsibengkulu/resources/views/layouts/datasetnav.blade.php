<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Geoportal Bengkulu'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased min-h-screen flex flex-col">

    <!-- ================= NAVBAR ================= -->
    <header class="fixed top-0 w-full z-50">
        <nav class="bg-red-700 shadow-sm">
            <div class="max-w-screen-xl mx-auto px-4 py-2 flex items-center justify-between">

                <!-- Logo -->
                <a href="{{ route('geo') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('Logo Provinsi Bengkulu.png') }}" class="h-6" alt="Logo">
                    <span class="text-sm font-semibold text-white tracking-wide">
                        GEOPORTAL PROVINSI BENGKULU
                    </span>
                </a>

                <!-- Tombol Close / Kembali -->
                <div class="flex items-center">
                    <a href="javascript:history.back()" 
                       class="text-white hover:text-red-200 hover:bg-red-800 p-2 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white"
                       title="Kembali">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>

            </div>
        </nav>
    </header>

    <!-- ================= MAIN ================= -->
    <main class="pt-10 flex-grow">
        @yield('content')
    </main>

    <!-- ================= FOOTER (Hanya Copyright) ================= -->
    <footer class="bg-white border-t border-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex flex-col items-center space-y-2">
                <!-- Tambahan Logo Kecil di footer biar manis -->
                <img src="{{ asset('Logo Provinsi Bengkulu.png') }}" class="h-8 mb-2 grayscale opacity-50" alt="Logo">
                
                <p class="text-gray-500 text-sm">
                    &copy; {{ date('Y') }} <strong>Geoportal Provinsi Bengkulu</strong>. All rights reserved.
                </p>
                <p class="text-gray-400 text-[10px] uppercase tracking-widest">
                    Pemerintah Provinsi Bengkulu
                </p>
            </div>
        </div>
    </footer>

    <!-- AlpineJS -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Scripts Stack dari child view -->
    @stack('scripts')

</body>
</html>