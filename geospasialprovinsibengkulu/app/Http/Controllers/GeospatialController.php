<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\GeospatialLayer;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GeospatialController extends Controller
{
    /**
     * ========================================================
     * BAGIAN 1: KELOLA DATA (ADMIN)
     * ========================================================
     */

    public function index()
    {
        $layers = GeospatialLayer::with('category')->latest()->paginate(10);
        $categories = Category::all();

        return view('layouts.admin.kelolageospasial', compact('layers', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'layer_name'        => 'required|string|max:255',
            'category_id'       => 'required|exists:categories,category_id',
            'geospatial_file'   => 'required|file|max:102400', // 100MB
            'description'       => 'nullable|string|max:1000',
            'status_verifikasi' => 'nullable|in:pending,approved,rejected,draft',
            'is_published'      => 'nullable|boolean',
        ], [
            'geospatial_file.required' => 'File geospasial wajib diunggah.',
            'geospatial_file.max'      => 'Ukuran file tidak boleh lebih dari 100MB.',
        ]);

        $file = $request->file('geospatial_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = ['geojson', 'json', 'zip', 'shp', 'kml', 'gpx'];

        if (!in_array($extension, $allowed)) {
            return back()->withInput()->withErrors(['geospatial_file' => "Format .$extension tidak didukung."]);
        }

        try {
            DB::beginTransaction();

            $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '_') . '_' . time() . '.' . $extension;
            $filePath = $file->storeAs('geospatial', $fileName, 'public');

            GeospatialLayer::create([
                'layer_name'         => $validated['layer_name'],
                'category_id'        => $validated['category_id'],
                'description'        => $validated['description'],
                'status_verifikasi'  => $validated['status_verifikasi'] ?? 'pending',
                'is_published'       => $request->has('is_published'),
                'id'                 => auth()->id() ?? 1,
                'file_path'          => $filePath,
                'file_original_name' => $file->getClientOriginalName(),
                'file_type'          => $extension,
                'file_size'          => $file->getSize(),
                'file_mime'          => $file->getMimeType(),
            ]);

            DB::commit();
            return redirect()->route('admin.geospasial.index')->with('success', '✅ Data berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($filePath)) Storage::disk('public')->delete($filePath);
            Log::error('Upload Error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data ke database.']);
        }
    }

    public function edit($id)
    {
        $layer = GeospatialLayer::findOrFail($id);
        $categories = Category::all();
        return view('layouts.admin.geoedit', compact('layer', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $layer = GeospatialLayer::findOrFail($id);
        $validated = $request->validate([
            'layer_name'  => 'required|string|max:255',
            'category_id' => 'required|exists:categories,category_id',
            'geospatial_file' => 'nullable|file|max:102400',
        ]);

        try {
            DB::beginTransaction();
            $data = $request->only(['layer_name', 'category_id', 'description', 'status_verifikasi']);
            $data['is_published'] = $request->has('is_published');

            if ($request->hasFile('geospatial_file')) {
                if ($layer->file_path) Storage::disk('public')->delete($layer->file_path);
                
                $file = $request->file('geospatial_file');
                $data['file_path'] = $file->store('geospatial', 'public');
                $data['file_type'] = $file->getClientOriginalExtension();
                $data['file_size'] = $file->getSize();
            }

            $layer->update($data);
            DB::commit();
            return redirect()->route('admin.geospasial.index')->with('success', '✅ Data berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal update: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $layer = GeospatialLayer::findOrFail($id);
        if ($layer->file_path) Storage::disk('public')->delete($layer->file_path);
        $layer->delete();
        return back()->with('success', '✅ Data berhasil dihapus!');
    }

    /**
     * ========================================================
     * BAGIAN 2: API & PREVIEW (AJAX)
     * ========================================================
     */

    public function getGeoJson($id): JsonResponse
    {
        try {
            $layer = GeospatialLayer::where('geospatial_id', $id)->firstOrFail();
            $fullPath = storage_path('app/public/' . $layer->file_path);

            if (!file_exists($fullPath)) return response()->json(['error' => 'File tidak ditemukan'], 404);

            if (strtolower($layer->file_type) === 'zip') {
                return response()->json(['is_shapefile' => true, 'url' => asset('storage/' . $layer->file_path)]);
            }

            return response()->json(json_decode(file_get_contents($fullPath), true));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memproses file'], 500);
        }
    }

    public function download($id): StreamedResponse
    {
        $layer = GeospatialLayer::where('geospatial_id', $id)->firstOrFail();
        return Storage::disk('public')->download($layer->file_path, $layer->file_original_name);
    }

    public function serveFile($id)
    {
        $layer = GeospatialLayer::where('geospatial_id', $id)->firstOrFail();
        return response()->file(storage_path('app/public/' . $layer->file_path));
    }

    /**
     * ========================================================
     * BAGIAN 3: HALAMAN PUBLIK (KATALOG & DETAIL)
     * ========================================================
     */

    // Menampilkan LIST Dataset (Pencarian & Filter)
    public function katalogDataset(Request $request)
    {
        $query = GeospatialLayer::with(['metadata', 'category'])
                    ->where('status_verifikasi', 'approved')
                    ->where('is_published', 1);

        // Filter Pencarian di Navbar/Searchbar
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('layer_name', 'LIKE', "%$search%")
                  ->orWhereHas('metadata', function($qm) use ($search) {
                      $qm->where('abstract', 'LIKE', "%$search%")->orWhere('title', 'LIKE', "%$search%");
                  });
            });
        }

        // Filter Dropdown Tipe (Vector, Raster, etc)
        if ($request->filled('type') && $request->type !== 'semua') {
            $query->whereHas('metadata', function($q) use ($request) {
                $q->where('data_type', $request->type);
            });
        }

        $datasets = $query->latest()->paginate(9)->withQueryString();

        /**
         * 💡 SOLUSI ERROR:
         * Mengirimkan 'dataset' => null agar line 3 di view dataset.blade.php
         * ($dataset->metadata->title) tidak error saat membuka halaman katalog.
         */
        return view('dataset', [
            'datasets' => $datasets,
            'dataset'  => null 
        ]);
    }

    // Menampilkan DETAIL Dataset tunggal
    public function showDetail($id)
    {
        $dataset = GeospatialLayer::with(['metadata', 'category'])
                    ->where('geospatial_id', $id)
                    ->where('status_verifikasi', 'approved')
                    ->firstOrFail();

        /**
         * 💡 SOLUSI ERROR:
         * Mengirimkan 'datasets' => collect([]) agar loop daftar di View 
         * tidak error saat membuka halaman detail.
         */
        return view('dataset', [
            'dataset'  => $dataset,
            'datasets' => collect([]) 
        ]);
    }
    // ===========================================
    // TAMBAHKAN FUNGSI INI UNTUK HALAMAN KATALOG
    // ===========================================
   // ===========================================
    // TAMBAHKAN FUNGSI INI UNTUK HALAMAN KATALOG
    // ===========================================
    public function katalog()
    {
        // Mengambil data layer yang sudah di-approve dan di-publish
        $katalogData = GeospatialLayer::with('category')
                            ->where('status_verifikasi', 'approved')
                            ->where('is_published', true)
                            ->latest()
                            ->get(); 

        // Data untuk bagian statistik di bawah
        $totalPeta = GeospatialLayer::where('is_published', true)->count();
        $totalKategori = GeospatialLayer::distinct('category_id')->count('category_id');
        
        // KARENA BELUM ADA KOLOM INSTANSI DI DATABASE, KITA BUAT STATIS DULU:
        $totalInstansi = 1; // Contoh: Anggap saja 1 instansi (Pemerintah Provinsi Bengkulu)
        
        // Total Pengguna
        $totalPengguna = \App\Models\User::count();

        return view('catalog', compact(
            'katalogData', 
            'totalPeta', 
            'totalKategori', 
            'totalInstansi', 
            'totalPengguna'
        ));
    }
}