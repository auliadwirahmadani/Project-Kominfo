<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProdusenController extends Controller
{
    /**
     * Menampilkan halaman Dashboard Produsen
     */
    public function dashboard()
    {
        // Pastikan nanti kamu membuat file ini di resources/views/layouts/produsen/dashboard.blade.php
        return view('layouts.produsen.dashboard');
    }

    /**
     * Menampilkan halaman Kelola Data Geospasial
     */
    public function geospasial()
    {
        // Pastikan nanti kamu membuat file ini di resources/views/layouts/produsen/kelolageospasial.blade.php
        return view('layouts.produsen.kelolageospasial');
    }

    /**
     * Menampilkan halaman Kelola Metadata
     */
    public function metadata()
    {
        // Pastikan nanti kamu membuat file ini di resources/views/layouts/produsen/kelolametadata.blade.php
        return view('layouts.produsen.kelolametadata');
    }

    /**
     * Menampilkan halaman Monitoring Status
     */
    public function monitoring()
    {
        // Pastikan nanti kamu membuat file ini di resources/views/layouts/produsen/monitoringstatus.blade.php
        return view('layouts.produsen.monitoringstatus');
    }
}