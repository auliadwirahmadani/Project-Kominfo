<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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

    <!-- ================= FOOTER ================= -->
    <footer class="bg-gradient-to-br from-red-600 to-red-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                <!-- About -->
                <div class="space-y-4">
                    <h3 class="text-xl font-bold mb-4">Geoportal Bengkulu</h3>
                    <p class="text-red-100 text-sm leading-relaxed">
                        Platform Informasi Geospasial Resmi Provinsi Bengkulu
                        untuk mendukung perencanaan pembangunan dan transparansi data.
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="space-y-3">
                    <h3 class="text-lg font-bold mb-4">Tautan Cepat</h3>
                    <a href="#" class="block text-red-100 hover:text-white transition text-sm">Beranda</a>
                    <a href="#" class="block text-red-100 hover:text-white transition text-sm">Peta Interaktif</a>
                    <a href="#" class="block text-red-100 hover:text-white transition text-sm">Data Geospasial</a>
                    <a href="#" class="block text-red-100 hover:text-white transition text-sm">Tentang Kami</a>
                    <a href="#" class="block text-red-100 hover:text-white transition text-sm">Kontak</a>
                </div>

                <!-- Contact -->
                <div class="space-y-3">
                    <h3 class="text-lg font-bold mb-4">Kontak Kami</h3>
                    <div class="text-red-100 text-sm space-y-2">
                        <p>Jl. Jend. Sudirman No. 35, Kota Bengkulu</p>
                        <p>info@geoportal.bengkuluprov.go.id</p>
                        <p>(0736) 123456</p>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="space-y-3">
                    <h3 class="text-lg font-bold mb-4">Berlangganan Newsletter</h3>
                    <p class="text-red-100 text-sm">
                        Dapatkan update terbaru tentang data geospasial Bengkulu
                    </p>
                    <form class="space-y-2">
                        <input type="email"
                            placeholder="Email Anda"
                            class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20
                                   focus:border-white focus:outline-none focus:ring-2 focus:ring-white/30
                                   text-white placeholder-red-200 text-sm">

                        <button type="submit"
                            class="w-full px-4 py-2 bg-white text-red-600 font-bold rounded-lg
                                   hover:bg-red-50 hover:scale-105 transition">
                            Berlangganan
                        </button>
                    </form>
                </div>

            </div>

            <!-- Copyright -->
            <div class="border-t border-white/20 mt-8 pt-8 text-center text-red-100 text-sm">
                &copy; {{ date('Y') }} Geoportal Provinsi Bengkulu. All rights reserved.
            </div>

        </div>
    </footer>

    <!-- AlpineJS -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</body>
</html>