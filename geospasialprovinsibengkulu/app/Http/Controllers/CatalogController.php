<?php

namespace App\Http\Controllers;

use App\Models\GeospatialLayer;
use App\Models\MetadataLayer;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CatalogController extends Controller
{
    /**
     * Display the public catalog page with filtering and search.
     */
    public function index(Request $request)
    {
        // Ambil semua layer yang sudah dipublikasikan dengan relasi
        $query = GeospatialLayer::with(['category', 'metadata'])
            ->where('is_published', true);

        // 🔍 Filter berdasarkan kategori
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('category_name', 'like', '%' . $request->category . '%');
            });
        }

        // 🔍 Filter berdasarkan tipe geometry
        if ($request->filled('type')) {
            $query->where(function($q) use ($request) {
                $q->where('geometry', 'like', '%' . $request->type . '%')
                  ->orWhere('geometry_type', 'like', '%' . $request->type . '%');
            });
        }

        // 🔍 Filter berdasarkan tahun publikasi
        if ($request->filled('year')) {
            $query->whereHas('metadata', function($q) use ($request) {
                $q->whereYear('publication_date', $request->year);
            });
        }

        // 🔍 Search berdasarkan judul, deskripsi, atau identifier
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('layer_name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhereHas('metadata', function($sub) use ($searchTerm) {
                      $sub->where('identifier', 'like', $searchTerm)
                          ->orWhere('title', 'like', $searchTerm)
                          ->orWhere('keywords', 'like', $searchTerm);
                  });
            });
        }

        // 🔍 Filter berdasarkan instansi/organisasi
        if ($request->filled('organization')) {
            $query->whereHas('metadata', function($q) use ($request) {
                $q->where('organization', 'like', '%' . $request->organization . '%');
            });
        }

        // ✅ Pagination untuk performa lebih baik (12 item per halaman)
        $katalogData = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        // 🔄 Transform data untuk view dengan safe access
        $transformedData = $katalogData->map(function($layer) {
            return (object) [
                'id' => $layer->geospatial_id,
                'judul' => $layer->layer_name,
                'kategori' => $layer->category?->category_name ?? 'Umum',
                'kategori_id' => $layer->category?->category_id ?? null,
                'tipe_data' => $this->getGeometryType($layer->geometry),
                'geometry_type' => $layer->geometry,
                'instansi' => $layer->metadata?->organization ?? 'Pemerintah Provinsi',
                'deskripsi' => $layer->description,
                'abstract' => $layer->metadata?->abstract ?? null,
                'identifier' => $layer->metadata?->identifier ?? null,
                'tahun' => $this->getPublicationYear($layer),
                'created_at' => $layer->created_at,
                'updated_at' => $layer->updated_at,
                'file_path' => $layer->file_path,
                'file_type' => $layer->file_type ?? 'unknown',
                'preview_image' => $layer->metadata?->preview_image ?? null,
                'status_verifikasi' => $layer->status_verifikasi,
                'distribution_url' => $layer->metadata?->distribution_url ?? null,
                'keywords' => $layer->metadata?->keywords ?? null,
            ];
        });

        // 📊 Statistics untuk dashboard
        $stats = $this->getCatalogStatistics();

        // 📦 Available filters untuk dropdown
        $availableFilters = $this->getAvailableFilters();

        return view('catalog', [
            'katalogData' => $transformedData,
            'stats' => (object) $stats,
            'filters' => (object) $availableFilters,
            // Legacy variables for backward compatibility
            'totalKategori' => $stats['total_kategori'],
            'totalPeta' => $stats['total_peta'],
            'totalPengguna' => $stats['total_pengguna'],
            'totalInstansi' => $stats['total_instansi'],
        ]);
    }

    /**
     * Get publication year with fallback logic.
     */
    private function getPublicationYear($layer)
    {
        // Prioritas 1: publication_date dari metadata
        if ($layer->metadata?->publication_date) {
            return \Carbon\Carbon::parse($layer->metadata->publication_date)->format('Y');
        }
        
        // Prioritas 2: year dari metadata
        if ($layer->metadata?->year) {
            return $layer->metadata->year;
        }
        
        // Fallback: tahun created_at layer
        return $layer->created_at?->format('Y') ?? date('Y');
    }

    /**
     * Detect geometry type from geometry field.
     */
    private function getGeometryType($geometry)
    {
        if (empty($geometry)) return 'polygon';
        
        $geometry = strtolower(trim($geometry));
        
        // Mapping geometry types
        $types = [
            'point' => ['point', 'points', 'multipoint'],
            'polyline' => ['line', 'lines', 'polyline', 'polylines', 'linestring', 'multilinestring'],
            'polygon' => ['polygon', 'polygons', 'multipolygon'],
            'raster' => ['raster', 'tiff', 'tif', 'geotiff', 'grid', 'imagery'],
        ];

        foreach ($types as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($geometry, $keyword)) {
                    return $type;
                }
            }
        }
        
        return 'polygon'; // default
    }

    /**
     * Get statistics for the catalog page.
     */
    private function getCatalogStatistics()
    {
        return [
            'total_kategori' => Category::count(),
            'total_peta' => GeospatialLayer::where('is_published', true)->count(),
            'total_pengguna' => \App\Models\User::count(),
            'total_instansi' => DB::table('metadata_layer')
                ->whereNotNull('organization')
                ->where('organization', '!=', '')
                ->distinct('organization')
                ->count('organization'),
            'total_downloads' => DB::table('geospatial_layer')
                ->whereNotNull('download_count')
                ->sum('download_count') ?? 0,
        ];
    }

    /**
     * Get available filter options for dropdowns.
     */
    private function getAvailableFilters()
    {
        return [
            'categories' => Category::orderBy('category_name')->pluck('category_name', 'category_id'),
            'organizations' => DB::table('metadata_layer')
                ->whereNotNull('organization')
                ->where('organization', '!=', '')
                ->distinct()
                ->orderBy('organization')
                ->pluck('organization', 'organization'),
            'years' => DB::table('metadata_layer')
                ->selectRaw('YEAR(publication_date) as year')
                ->whereNotNull('publication_date')
                ->groupBy('year')
                ->orderByDesc('year')
                ->pluck('year', 'year'),
            'geometry_types' => collect(['point', 'polyline', 'polygon', 'raster'])->mapWithKeys(fn($t) => [ucfirst($t) => $t]),
        ];
    }

    /**
     * API endpoint untuk filter AJAX (opsional).
     */
    public function filterData(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'organization' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = GeospatialLayer::with(['category', 'metadata'])
            ->where('is_published', true);

        // Apply filters (same logic as index method)
        if (!empty($validated['category'])) {
            $query->whereHas('category', fn($q) => 
                $q->where('category_name', 'like', '%' . $validated['category'] . '%'));
        }

        if (!empty($validated['type'])) {
            $query->where('geometry', 'like', '%' . $validated['type'] . '%');
        }

        if (!empty($validated['year'])) {
            $query->whereHas('metadata', fn($q) => 
                $q->whereYear('publication_date', $validated['year']));
        }

        if (!empty($validated['organization'])) {
            $query->whereHas('metadata', fn($q) => 
                $q->where('organization', 'like', '%' . $validated['organization'] . '%'));
        }

        if (!empty($validated['search'])) {
            $term = '%' . $validated['search'] . '%';
            $query->where(function($q) use ($term) {
                $q->where('layer_name', 'like', $term)
                  ->orWhere('description', 'like', $term);
            });
        }

        $results = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12))
            ->withQueryString();

        // Transform for API response
        $transformed = $results->map(function($layer) {
            return [
                'id' => $layer->geospatial_id,
                'judul' => $layer->layer_name,
                'kategori' => $layer->category?->category_name ?? 'Umum',
                'tipe_data' => $this->getGeometryType($layer->geometry),
                'instansi' => $layer->metadata?->organization ?? '-',
                'tahun' => $this->getPublicationYear($layer),
                'preview_image' => $layer->metadata?->preview_image ? asset('storage/' . $layer->metadata->preview_image) : null,
                'url_detail' => route('dataset.show', $layer->geospatial_id),
                'url_map' => route('geo', ['layer' => $layer->geospatial_id]),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformed,
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'total' => $results->total(),
                'per_page' => $results->perPage(),
            ],
        ]);
    }
}