<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeospatialLayer;
use App\Models\MetadataLayer;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProdusenController extends Controller
{
    /**
     * Menampilkan halaman Dashboard Produsen
     */
    public function dashboard()
    {
        $userId = auth()->id();
        $totalLayers = GeospatialLayer::count();
        $totalPublished = GeospatialLayer::where('is_published', 1)->count();
        $totalPending = GeospatialLayer::where('status_verifikasi', 'pending')->count();

        return view('layouts.produsen.dashboard', compact('totalLayers', 'totalPublished', 'totalPending'));
    }

    /**
     * Menampilkan halaman Kelola Data Geospasial (list)
     */
    public function geospasial()
    {
        $layers = GeospatialLayer::with('category')->latest()->paginate(10);
        $categories = Category::all();
        return view('layouts.produsen.kelolageospasial', compact('layers', 'categories'));
    }

    /**
     * Menampilkan form tambah data baru
     */
    public function create()
    {
        $categories = Category::all();
        return view('layouts.produsen.create', compact('categories'));
    }

    /**
     * Menyimpan data geospasial baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'layer_name'      => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,category_id',
            'geospatial_file' => 'required|file|max:102400',
            'description'     => 'nullable|string|max:1000',
        ]);

        $file = $request->file('geospatial_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = ['geojson', 'json', 'zip', 'shp', 'kml', 'gpx'];

        if (!in_array($extension, $allowed)) {
            return back()->withInput()->withErrors(['geospatial_file' => "Format .$extension tidak didukung."]);
        }

        try {
            $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '_') . '_' . time() . '.' . $extension;
            $filePath = $file->storeAs('geospatial', $fileName, 'public');

            GeospatialLayer::create([
                'layer_name'        => $validated['layer_name'],
                'category_id'       => $validated['category_id'],
                'description'       => $validated['description'],
                'status_verifikasi' => 'pending',
                'is_published'      => false,
                'file_path'         => $filePath,
                'file_original_name'=> $file->getClientOriginalName(),
                'file_type'         => $extension,
                'file_size'         => $file->getSize(),
                'file_mime'         => $file->getMimeType(),
            ]);

            return redirect()->route('produsen.geospasial.index')->with('success', '✅ Data berhasil dikirim untuk diverifikasi!');
        } catch (\Exception $e) {
            Log::error('ProdusenController@store: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data.']);
        }
    }

    /**
     * Menampilkan form edit data geospasial
     */
    public function edit($id)
    {
        $layer = GeospatialLayer::findOrFail($id);
        $categories = Category::all();
        return view('layouts.produsen.edit', compact('layer', 'categories'));
    }

    /**
     * Update data geospasial
     */
    public function update(Request $request, $id)
    {
        $layer = GeospatialLayer::findOrFail($id);

        $validated = $request->validate([
            'layer_name'  => 'required|string|max:255',
            'category_id' => 'required|exists:categories,category_id',
            'description' => 'nullable|string|max:1000',
            'geospatial_file' => 'nullable|file|max:102400',
        ]);

        $data = $request->only(['layer_name', 'category_id', 'description']);
        $data['status_verifikasi'] = 'pending'; // Reset ke pending setelah diedit

        if ($request->hasFile('geospatial_file')) {
            if ($layer->file_path) Storage::disk('public')->delete($layer->file_path);
            $file = $request->file('geospatial_file');
            $data['file_path'] = $file->store('geospatial', 'public');
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $layer->update($data);
        return redirect()->route('produsen.geospasial.index')->with('success', '✅ Data berhasil diupdate!');
    }

    /**
     * Hapus data geospasial
     */
    public function destroy($id)
    {
        $layer = GeospatialLayer::findOrFail($id);
        if ($layer->file_path) Storage::disk('public')->delete($layer->file_path);
        $layer->delete();
        return redirect()->route('produsen.geospasial.index')->with('success', '✅ Data berhasil dihapus!');
    }

    /**
     * Menampilkan halaman Kelola Metadata
     */
    public function metadata()
    {
        $layers = GeospatialLayer::with('metadata')->latest()->get();
        return view('layouts.produsen.kelolametadata', compact('layers'));
    }

    /**
     * Menyimpan metadata baru
     */
    public function storeMetadata(Request $request)
    {
        $validated = $request->validate([
            'geospatial_id'    => 'required|exists:geospatial_layer,geospatial_id',
            'title'            => 'required|string|max:255',
            'abstract'         => 'nullable|string',
            'organization'     => 'nullable|string|max:255',
            'data_type'        => 'nullable|string|max:100',
            'publication_date' => 'nullable|date',
            'keywords'         => 'nullable|string',
            'source'           => 'nullable|string|max:255',
            'year'             => 'nullable|integer',
            'crs'              => 'nullable|string|max:100',
            'scale'            => 'nullable|string|max:100',
        ]);

        MetadataLayer::updateOrCreate(
            ['geospatial_id' => $validated['geospatial_id']],
            $validated
        );

        return redirect()->route('produsen.metadata.index')->with('success', '✅ Metadata berhasil disimpan!');
    }

    /**
     * Update metadata
     */
    public function updateMetadata(Request $request, $id)
    {
        $metadata = MetadataLayer::findOrFail($id);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'abstract'         => 'nullable|string',
            'organization'     => 'nullable|string|max:255',
            'data_type'        => 'nullable|string|max:100',
            'publication_date' => 'nullable|date',
            'keywords'         => 'nullable|string',
            'source'           => 'nullable|string|max:255',
            'year'             => 'nullable|integer',
            'crs'              => 'nullable|string|max:100',
            'scale'            => 'nullable|string|max:100',
        ]);

        $metadata->update($validated);
        return redirect()->route('produsen.metadata.index')->with('success', '✅ Metadata berhasil diupdate!');
    }

    /**
     * Menampilkan halaman Monitoring Status
     */
    public function monitoring()
    {
        $layers = GeospatialLayer::with(['category', 'metadata'])->latest()->paginate(10);
        return view('layouts.produsen.monitoringstatus', compact('layers'));
    }
}