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

class GeospatialController extends Controller
{
    // ========================================================
    // TAMPILKAN HALAMAN KELOLA GEOSPASIAL
    // ========================================================
    public function index()
    {
        $layers = GeospatialLayer::with('category')
                    ->latest()
                    ->paginate(10);

        $categories = Category::all();

        return view('layouts.admin.kelolageospasial', compact('layers', 'categories'));
    }

    // ========================================================
    // PROSES TAMBAH DATA (UPLOAD)
    // ========================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'layer_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,category_id',
            'geospatial_file' => 'required|file|max:102400', // 🔥 Batas 100MB
            'description' => 'nullable|string|max:1000',
            'status_verifikasi' => 'nullable|in:pending,approved,rejected,draft',
            'is_published' => 'nullable|boolean',
        ], [
            'geospatial_file.required' => 'File harus diupload',
            'geospatial_file.max' => 'Ukuran file maksimal 100MB',
        ]);

        $file = $request->file('geospatial_file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedExtensions = ['geojson', 'json', 'zip', 'shp', 'kml', 'gpx'];
        
        if (!in_array($extension, $allowedExtensions)) {
            return back()
                ->withInput()
                ->withErrors([
                    'geospatial_file' => 'Format file "' . $extension . '" tidak didukung. Gunakan: geojson, json, atau zip'
                ]);
        }

        try {
            DB::beginTransaction();

            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME), '_') . '_' . time() . '.' . $extension;
            $filePath = $file->storeAs('geospatial', $fileName, 'public');

            if (!$filePath) {
                throw new \Exception('Gagal menyimpan file ke storage');
            }

            GeospatialLayer::create([
                'layer_name' => $validated['layer_name'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'] ?? null,
                'status_verifikasi' => $validated['status_verifikasi'] ?? 'pending',
                'is_published' => $request->has('is_published'),
                // ID User pembuat (Pastikan ada user yang login, fallback ke 1)
                'id' => auth()->check() ? auth()->id() : 1,
                'file_path' => $filePath,
                'file_original_name' => $originalName,
                'file_type' => $extension,
                'file_size' => $file->getSize(),
                'file_mime' => $file->getMimeType(),
                // geojson_data dihapus agar tidak error SQL
            ]);

            DB::commit();

            return redirect()->route('admin.geospasial.index')
                ->with('success', '✅ Data geospasial berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            \Log::error('Geospatial upload error: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['geospatial_file' => 'Gagal upload: ' . $e->getMessage()]);
        }
    }

    // ========================================================
    // TAMPILKAN FORM EDIT
    // ========================================================
    public function edit($id)
    {
        $layer = GeospatialLayer::findOrFail($id);
        $categories = Category::all();
        
        return view('layouts.admin.geoedit', compact('layer', 'categories'));
    }

    // ========================================================
    // PROSES UPDATE DATA
    // ========================================================
    public function update(Request $request, $id)
    {
        $layer = GeospatialLayer::findOrFail($id);
        
        $validated = $request->validate([
            'layer_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,category_id',
            'geospatial_file' => 'nullable|file|max:102400', // 🔥 Batas 100MB
            'description' => 'nullable|string|max:1000',
            'status_verifikasi' => 'nullable|in:pending,approved,rejected,draft',
            'is_published' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'layer_name' => $validated['layer_name'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'] ?? null,
                'status_verifikasi' => $validated['status_verifikasi'] ?? $layer->status_verifikasi,
                'is_published' => $request->has('is_published'),
            ];

            if($request->hasFile('geospatial_file')) {
                // Hapus file lama jika ada
                if($layer->file_path && Storage::disk('public')->exists($layer->file_path)) {
                    Storage::disk('public')->delete($layer->file_path);
                }

                $file = $request->file('geospatial_file');
                $originalName = $file->getClientOriginalName();
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME), '_') . '_' . time() . '.' . $extension;
                $filePath = $file->storeAs('geospatial', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_original_name'] = $originalName;
                $data['file_type'] = $extension;
                $data['file_size'] = $file->getSize();
                $data['file_mime'] = $file->getMimeType();
                // geojson_data processing dihapus
            }

            $layer->update($data);

            DB::commit();

            return redirect()->route('admin.geospasial.index')
                ->with('success', '✅ Data geospasial berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Geospatial update error: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal update: ' . $e->getMessage()]);
        }
    }

    // ========================================================
    // PROSES HAPUS DATA
    // ========================================================
    public function destroy($id)
    {
        try {
            $layer = GeospatialLayer::findOrFail($id);

            if($layer->file_path && Storage::disk('public')->exists($layer->file_path)) {
                Storage::disk('public')->delete($layer->file_path);
            }

            $layer->delete();

            return redirect()->route('admin.geospasial.index')
                ->with('success', '✅ Data geospasial berhasil dihapus!');

        } catch (\Exception $e) {
            \Log::error('Geospatial delete error: ' . $e->getMessage());

            return redirect()->route('admin.geospasial.index')
                ->with('error', '❌ Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // ========================================================
    // FUNGSI UNTUK PREVIEW PETA DI ADMIN (DIPANGGIL VIA AJAX)
    // ========================================================
    public function getGeoJson($id): JsonResponse
    {
        try {
            // Gunakan primary key yang benar
            $layer = GeospatialLayer::where('geospatial_id', $id)->firstOrFail();
            
            if (!$layer->file_path) {
                return response()->json([
                    'error' => 'Data peta tidak tersedia untuk layer ini.'
                ], 404);
            }

            $fullPath = storage_path('app/public/' . $layer->file_path);
            
            if (!file_exists($fullPath)) {
                return response()->json([
                    'error' => 'File fisik tidak ditemukan di server.',
                    'path' => $layer->file_path
                ], 404);
            }

            // ✅ Deteksi ZIP Shapefile
            if (str_ends_with(strtolower($layer->file_path), '.zip')) {
                return response()->json([
                    'is_shapefile' => true,
                    // Kita gunakan asset() yang lebih standar
                    'url' => asset('storage/' . $layer->file_path)
                ]);
            }
            
            // ✅ Baca isi file json dari hardisk
            $content = file_get_contents($fullPath);
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'File rusak atau bukan format JSON yang valid.',
                    'details' => json_last_error_msg()
                ], 422);
            }
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            \Log::error('GeoJSON fetch error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan sistem server.',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    // ========================================================
    // FUNGSI UNTUK TOMBOL DOWNLOAD PETA (ADMIN & USER)
    // ========================================================
    public function download($id): StreamedResponse
    {
        $layer = GeospatialLayer::where('geospatial_id', $id)->firstOrFail();
        
        if (!$layer->file_path) {
            abort(404, 'Path file tidak tersedia di Database.');
        }
        
        $disk = Storage::disk('public');
        
        if (!$disk->exists($layer->file_path)) {
            abort(404, 'File fisik tidak ditemukan di Server Storage.');
        }
        
        $filename = $layer->file_original_name ?? Str::slug($layer->layer_name) . '.' . ($layer->file_type ?? 'geojson');
        
        return $disk->download($layer->file_path, $filename);
    }

    // ========================================================
    // FILTER DATA UNTUK PETA UTAMA (HALAMAN DEPAN)
    // ========================================================
    public function filterData(Request $request)
    {
        try {
            $query = GeospatialLayer::query();

            if ($request->filled('id')) {
                $query->where('geospatial_id', $request->id);
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }

            $layers = $query->get();

            return response()->json([
                'status' => 'success',
                'layers' => $layers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data filter: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================================
    // SERVE FILE PETA LANGSUNG (ANTI GAGAL/BYPASS SYMLINK)
    // ========================================================
    public function serveFile($id)
    {
        $layer = GeospatialLayer::where('geospatial_id', $id)->firstOrFail();
        $path = storage_path('app/public/' . $layer->file_path);

        if (!file_exists($path)) {
            abort(404, 'File fisik peta tidak ditemukan di server.');
        }

        return response()->file($path);
    }
}