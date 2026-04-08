<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeospatialLayer;

class DatasetController extends Controller
{
    /**
     * ========================================================
     * TAMPILKAN HALAMAN KATALOG (GRID BOX - DAFTAR PETA)
     * View: catalog.blade.php
     * ========================================================
     */
    public function index(Request $request)
    {
        $query = GeospatialLayer::with(['metadata', 'category'])
                    ->where('status_verifikasi', 'approved')
                    ->where('is_published', 1);

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('layer_name', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('metadata', function($qMeta) use ($search) {
                      $qMeta->where('abstract', 'LIKE', '%' . $search . '%')
                            ->orWhere('title', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        // Filter Tipe Data
        if ($request->filled('type') && $request->type !== 'semua') {
            $query->whereHas('metadata', function($qMeta) use ($request) {
                $qMeta->where('data_type', $request->type);
            });
        }

        // Mengirim variabel $datasets ke halaman catalog
        $datasets = $query->latest()->paginate(9)->withQueryString();

        // 👇 Arahkan ke file catalog.blade.php
        return view('catalog', compact('datasets')); 
    }

    /**
     * ========================================================
     * TAMPILKAN HALAMAN DATASET (DETAIL PETA & METADATA)
     * View: dataset.blade.php
     * ========================================================
     */
    public function show($id)
    {
        // Ambil data layer spesifik
        $dataset = GeospatialLayer::with(['metadata', 'category'])
                    ->where('status_verifikasi', 'approved')
                    ->where('is_published', 1)
                    ->findOrFail($id);

        // 👇 Arahkan ke file dataset.blade.php dengan variabel $dataset (Tunggal)
        return view('dataset', compact('dataset'));
    }
}