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
        // Redirect user yang sudah login ke dashboard masing-masing.
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

        // Ambil semua layer yg dipublish untuk daftar.
        // Jika admin/pemilik yang login, tambahkan bypass di javascript BBOX API untuk layer tersebut.

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

    /**
     * POSTGIS: API Endpoint untuk mengambil fitur dalam Bounding Box
     */
    public function getFeaturesInView(Request $request)
    {
        $minLng = $request->input('min_lng');
        $minLat = $request->input('min_lat');
        $maxLng = $request->input('max_lng');
        $maxLat = $request->input('max_lat');

        if (!$minLng || !$minLat || !$maxLng || !$maxLat) {
            return response()->json(['error' => 'Missing bbox parameters'], 400);
        }

        // Ambil ID layer yang aktif
        $layerQuery = GeospatialLayer::query();

        // 🛡️ Jika user BUKAN admin dan BUKAN verifikator, filter ketat (Hanya Approved & Published)
        // 🔒 Jika Produsen, bisa lihat miliknya (Menunggu/Draft) + semua Approved & Published
        if (!Auth::check() || (!str_contains(Auth::user()->role_name ?? '', 'admin') && !str_contains(Auth::user()->role_name ?? '', 'verifikator'))) {
            $user = Auth::user();
            $layerQuery->where(function($q) use ($user) {
                $q->where('status_verifikasi', 'approved')
                  ->where('is_published', 1);
                
                if ($user && str_contains($user->role_name ?? '', 'produsen')) {
                    $q->orWhere('id', $user->id); // Bypass untuk lihat layer miliknya sendiri
                }
            });
        }
        
        if ($request->filled('category_id')) {
            $layerQuery->where('category_id', $request->category_id);
        }
        
        if ($request->filled('single_layer_id')) {
            $layerQuery->where('geospatial_id', $request->single_layer_id);
        }
        
        $validIds = $layerQuery->pluck('geospatial_id')->toArray();

        if (empty($validIds)) {
            return response()->json([
                'postgis_features' => ['type' => 'FeatureCollection', 'features' => [], 'metadata_dict' => []],
                'static_layers' => []
            ]);
        }

        $idString = implode(',', $validIds);

        // Kueri langsung ke PostGIS: Bangun GeoJSON di DB, filter by BBOX
        $sql = "
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', COALESCE(json_agg(
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(geom)::json,
                        'properties', COALESCE(properties, '{}'::jsonb) || jsonb_build_object(
                            '_geospatial_id', geospatial_id
                        )
                    )
                ), '[]'::json)
            ) as feature_collection
            FROM layer_features
            WHERE geospatial_id IN ($idString)
            AND ST_Intersects(geom, ST_MakeEnvelope(?, ?, ?, ?, 4326))
            LIMIT 5000;
        ";

        $result = \Illuminate\Support\Facades\DB::select($sql, [(float)$minLng, (float)$minLat, (float)$maxLng, (float)$maxLat]);

        if (empty($result) || !isset($result[0]->feature_collection)) {
            $featureCollection = ['type' => 'FeatureCollection', 'features' => []];
        } else {
            $featureCollection = json_decode($result[0]->feature_collection, true);
        }

        // Kirim mapping metadata secara global dengan fallback ke data GeospatialLayer
        $layers = GeospatialLayer::with(['metadata', 'user.profile'])->whereIn('geospatial_id', $validIds)->get()->keyBy('geospatial_id');
        $metadataDict = $layers->map(function($layer) {
            $meta = $layer->metadata ? $layer->metadata->toArray() : [];
            $meta['title'] = $meta['title'] ?? $layer->layer_name;
            $meta['abstract'] = $meta['abstract'] ?? $layer->description;
            
            if (empty($meta['organization']) && $layer->user && $layer->user->profile) {
                $meta['organization'] = $layer->user->profile->instansi ?? $layer->user->name;
            }
            return $meta;
        });

        $featureCollection['metadata_dict'] = $metadataDict;

        // Ambil Data statis untuk file-file Layer (GeoJSON/ZIP)
        $staticLayers = GeospatialLayer::whereIn('geospatial_id', $validIds)
                        ->select('geospatial_id', 'file_path')
                        ->get()
                        ->map(function ($l) {
                            return [
                                'id' => $l->geospatial_id,
                                'type' => strtolower(pathinfo($l->file_path, PATHINFO_EXTENSION)),
                                'url' => \Illuminate\Support\Facades\Storage::url($l->file_path)
                            ];
                        });

        return response()->json([
            'postgis_features' => $featureCollection,
            'static_layers' => $staticLayers
        ]);
    }
}