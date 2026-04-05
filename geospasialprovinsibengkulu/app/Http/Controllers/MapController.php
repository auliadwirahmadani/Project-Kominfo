<?php

namespace App\Http\Controllers;

use App\Models\GeospatialLayer;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * Memuat halaman awal (geo.blade.php) beserta data layer awal
     */
    public function index()
    {
        // ✅ Ambil layer yang Approved DAN Published untuk tampilan awal
        $layers = GeospatialLayer::where('status_verifikasi', 'approved')
                    ->where('is_published', 1)
                    ->select('geospatial_id', 'layer_name', 'description', 'category_id', 'file_path', 'created_at')
                    ->get();

        return view('geo', compact('layers'));
    }

    /**
     * API Endpoint untuk mengambil data peta berdasarkan Filter
     * (Kategori dan Tahun)
     */
    public function getFilteredLayers(Request $request)
    {
        // 1. Mulai query dengan syarat wajib (Approved & Published)
        $query = GeospatialLayer::where('status_verifikasi', 'approved')
                    ->where('is_published', 1)
                    ->select('geospatial_id', 'layer_name', 'description', 'category_id', 'file_path', 'created_at');

        // 2. Filter berdasarkan Category ID (jika user memilih kategori)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 3. Filter berdasarkan Tahun Pembuatan (jika user memilih tahun)
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // 4. Eksekusi query
        $layers = $query->get();

        // 5. Format ulang data agar menyertakan URL file yang bisa dibaca Leaflet
        $formattedLayers = $layers->map(function($layer) {
            return [
                'id' => $layer->geospatial_id,
                'name' => $layer->layer_name,
                'description' => $layer->description,
                'category_id' => $layer->category_id,
                'url' => asset('storage/geospatial/' . $layer->file_path),
            ];
        });

        // Kembalikan data dalam format JSON
        return response()->json(['layers' => $formattedLayers]);
    }

    /**
     * API Endpoint untuk mencari data peta berdasarkan Nama Layer (Search Bar)
     */
    public function searchLayers(Request $request)
    {
        $keyword = $request->input('q');

        // Mulai query dengan syarat wajib (Approved & Published)
        $query = GeospatialLayer::where('status_verifikasi', 'approved')
                    ->where('is_published', 1)
                    ->select('geospatial_id', 'layer_name', 'description', 'category_id', 'file_path', 'created_at');

        // Jika ada inputan di search bar, cari berdasarkan nama layernya
        if (!empty($keyword)) {
            $query->where('layer_name', 'LIKE', '%' . $keyword . '%');
        }

        $layers = $query->get();

        // Format datanya agar menyertakan URL file yang bisa dibaca Leaflet
        $formattedLayers = $layers->map(function($layer) {
            return [
                'id' => $layer->geospatial_id,
                'name' => $layer->layer_name,
                'description' => $layer->description,
                'category_id' => $layer->category_id,
                'url' => asset('storage/geospatial/' . $layer->file_path),
            ];
        });

        // Kembalikan data dalam format JSON
        return response()->json(['layers' => $formattedLayers]);
    }
}