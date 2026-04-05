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
        // ✅ TAMBAHKAN with('metadata') agar data dari tabel metadata_layer ikut terbawa
        $layers = GeospatialLayer::with('metadata')
                    ->where('status_verifikasi', 'approved')
                    ->where('is_published', 1)
                    ->get();

        return view('geo', compact('layers'));
    }

    /**
     * API Endpoint untuk mengambil data peta berdasarkan Filter
     */
    public function getFilteredLayers(Request $request)
    {
        // 1. Mulai query dengan relasi metadata
        $query = GeospatialLayer::with('metadata')
                    ->where('status_verifikasi', 'approved')
                    ->where('is_published', 1);

        // 2. Filter berdasarkan Category ID
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 3. Filter berdasarkan Tahun
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        $layers = $query->get();

        // 4. Format ulang data agar menyertakan objek metadata lengkap untuk Javascript
        $formattedLayers = $layers->map(function($layer) {
            return [
                'geospatial_id' => $layer->geospatial_id,
                'layer_name'    => $layer->layer_name,
                'description'   => $layer->description,
                'file_path'     => $layer->file_path,
                'url'           => asset('storage/' . $layer->file_path),
                'created_at'    => $layer->created_at,
                // ✅ Kirim data metadata ke JSON
                'metadata'      => $layer->metadata 
            ];
        });

        return response()->json(['layers' => $formattedLayers]);
    }

    /**
     * API Endpoint untuk mencari data peta (Search Bar)
     */
    public function searchLayers(Request $request)
    {
        $keyword = $request->input('q');

        $query = GeospatialLayer::with('metadata')
                    ->where('status_verifikasi', 'approved')
                    ->where('is_published', 1);

        if (!empty($keyword)) {
            $query->where('layer_name', 'LIKE', '%' . $keyword . '%');
        }

        $layers = $query->get();

        $formattedLayers = $layers->map(function($layer) {
            return [
                'geospatial_id' => $layer->geospatial_id,
                'layer_name'    => $layer->layer_name,
                'description'   => $layer->description,
                'file_path'     => $layer->file_path,
                'url'           => asset('storage/' . $layer->file_path),
                'created_at'    => $layer->created_at,
                // ✅ Kirim data metadata ke JSON
                'metadata'      => $layer->metadata 
            ];
        });

        return response()->json(['layers' => $formattedLayers]);
    }
}