<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeospatialLayer;

class DatasetController extends Controller
{
    /**
     * ========================================================
     * TAMPILKAN HALAMAN KATALOG DATASET (GRID BOX)
     * ========================================================
     */
    public function index(Request $request)
    {
        // 1. Ambil data layer yang sudah di-publish dan di-approve
        $query = GeospatialLayer::with(['metadata', 'category'])
                    ->where('status_verifikasi', 'approved')
                    ->where('is_published', 1);

        // 2. Logika Kotak Pencarian (Mencari judul layer atau abstrak metadata)
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

        // 3. Logika Filter Tipe Data (Vector / Raster / Service)
        if ($request->filled('type') && $request->type !== 'semua') {
            $query->whereHas('metadata', function($qMeta) use ($request) {
                $qMeta->where('data_type', $request->type);
            });
        }

        // Ambil data dengan paginasi, withQueryString() agar filter tidak hilang saat ganti halaman
        $datasets = $query->latest()->paginate(9)->withQueryString();

        return view('dataset', compact('datasets')); 
    }

    /**
     * ========================================================
     * TAMPILKAN HALAMAN DETAIL DATASET
     * ========================================================
     */
    public function show($id)
    {
        // Ambil data layer beserta relasinya, pastikan datanya memang sudah di-publish
        $layer = GeospatialLayer::with(['metadata', 'category'])
                    ->where('status_verifikasi', 'approved')
                    ->where('is_published', 1)
                    ->findOrFail($id);

        return view('dataset-detail', compact('layer'));
    }
}