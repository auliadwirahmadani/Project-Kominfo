<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerifikatorController extends Controller
{
    /**
     * Menampilkan halaman Dashboard Verifikator
     */
    public function dashboard()
    {
        return view('layouts.verifikator.dashboard');
    }

    /**
     * Menampilkan halaman Periksa Data Geospasial
     */
    public function geospasial()
    {
        return view('layouts.verifikator.periksageospasial');
    }

    /**
     * Menampilkan halaman Periksa Metadata
     */
    public function metadata()
    {
        return view('layouts.verifikator.periksametadata');
    }

    /**
     * Menampilkan halaman Monitoring Status
     */
    public function monitoring()
    {
        return view('layouts.verifikator.monitoringstatus');
    }

    // ==========================================
    // FUNGSI UNTUK MENERIMA AKSI SETUJU/TOLAK
    // ==========================================

    public function keputusanGeospasial(Request $request, $id)
    {
        // Logika untuk menyimpan status persetujuan geospasial (Setuju/Tolak/Revisi)
    }

    public function keputusanMetadata(Request $request, $id)
    {
        // Logika untuk menyimpan status persetujuan metadata (Setuju/Tolak/Revisi)
    }
}