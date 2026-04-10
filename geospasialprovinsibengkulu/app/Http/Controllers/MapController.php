<?php

namespace App\Http\Controllers;

use App\Models\GeospatialLayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapController extends Controller
{
    /**
     * Memuat halaman awal (geo.blade.php) beserta data layer awal.
     * Jika user sudah login sebagai admin/produsen/verifikator,
     * redirect langsung ke dashboard masing-masing.
     */
    public function index()
    {
        // Redirect user yang sudah login ke dashboard masing-masing
        if (Auth::check()) {
            $role = strtolower(Auth::user()->role_name ?? '');
            if (str_contains($role, 'admin')) {
                return redirect()->route('admin.dashboard');
            } elseif (str_contains($role, 'produsen')) {
                return redirect()->route('produsen.dashboard');
            } elseif (str_contains($role, 'verifikator')) {
                return redirect()->route('verifikator.dashboard');
            }
        }

        // ✅ Load data beserta relasi metadatanya
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
                'url'           => asset('storage/' . str_replace('public/', '', $layer->file_path)),
                'created_at'    => $layer->created_at,
                // ✅ Kirim data metadata ke JSON agar terbaca saat difilter
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

        // 1. Mulai query dengan relasi metadata
        $query = GeospatialLayer::with('metadata')
                    ->where('status_verifikasi', 'approved')
                    ->where('is_published', 1);

        // 2. Filter berdasarkan keyword nama layer
        if (!empty($keyword)) {
            $query->where('layer_name', 'LIKE', '%' . $keyword . '%');
        }

        $layers = $query->get();

        // 3. Format ulang data untuk response JSON AJAX
        $formattedLayers = $layers->map(function($layer) {
            return [
                'geospatial_id' => $layer->geospatial_id,
                'layer_name'    => $layer->layer_name,
                'description'   => $layer->description,
                'file_path'     => $layer->file_path,
                'url'           => asset('storage/' . str_replace('public/', '', $layer->file_path)),
                'created_at'    => $layer->created_at,
                // ✅ Kirim data metadata ke JSON agar terbaca saat dicari
                'metadata'      => $layer->metadata 
            ];
        });

        return response()->json(['layers' => $formattedLayers]);
    }
}