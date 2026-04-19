<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeospatialLayer;
use App\Models\MetadataLayer;
use App\Models\Category;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ProdusenController extends Controller
{
    /**
     * Menampilkan halaman Dashboard Produsen
     */
    public function dashboard()
    {
        $userId = auth()->id();
        $totalLayers = GeospatialLayer::where('user_id', $userId)->count();
        $totalPublished = GeospatialLayer::where('user_id', $userId)->where('is_published', 1)->count();
        $totalPending = GeospatialLayer::where('user_id', $userId)->where('status_verifikasi', 'pending')->count();
        $totalRejected = GeospatialLayer::where('user_id', $userId)->where('status_verifikasi', 'rejected')->count();
        $layers = GeospatialLayer::where('user_id', $userId)->with(['category', 'metadata'])->latest()->paginate(10);

        return view('layouts.produsen.dashboard', compact('totalLayers', 'totalPublished', 'totalPending', 'totalRejected', 'layers'));
    }

    /**
     * Menampilkan halaman Kelola Data Geospasial (list)
     */
    public function geospasial()
    {
        $layers = GeospatialLayer::where('user_id', auth()->id())->with('category')->latest()->paginate(10);
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

            $layer = GeospatialLayer::create([
                'user_id'           => auth()->id(),
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

            // PostGIS Parsing
            if (in_array($extension, ['json', 'geojson'])) {
                $this->parseAndInsertGeoJson($layer->geospatial_id, Storage::disk('public')->path($filePath));
            }

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
        $layer = GeospatialLayer::where('user_id', auth()->id())->findOrFail($id);
        $categories = Category::all();
        return view('layouts.produsen.edit', compact('layer', 'categories'));
    }

    /**
     * Update data geospasial
     */
    public function update(Request $request, $id)
    {
        $layer = GeospatialLayer::where('user_id', auth()->id())->findOrFail($id);

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

        // Parse ulang data jika ada file geojson baru yang diupload
        if ($request->hasFile('geospatial_file') && in_array(strtolower($data['file_type']), ['json', 'geojson'])) {
            $this->parseAndInsertGeoJson($layer->geospatial_id, Storage::disk('public')->path($data['file_path']));
        }

        return redirect()->route('produsen.geospasial.index')->with('success', '✅ Data berhasil diupdate!');
    }

    /**
     * Hapus data geospasial
     */
    public function destroy($id)
    {
        $layer = GeospatialLayer::where('user_id', auth()->id())->findOrFail($id);
        if ($layer->file_path) Storage::disk('public')->delete($layer->file_path);
        $layer->delete();
        return redirect()->route('produsen.geospasial.index')->with('success', '✅ Data berhasil dihapus!');
    }

    /**
     * Menampilkan halaman Kelola Metadata
     */
    public function metadata()
    {
        $layers = GeospatialLayer::where('user_id', auth()->id())->with('metadata')->latest()->get();
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
     * Tampilkan halaman profil produsen
     */
    public function showProfile()
    {
        $user    = auth()->user();
        $profile = $user->profile ?? new Profile(['user_id' => $user->user_id]);
        return view('layouts.produsen.profile', compact('user', 'profile'));
    }

    /**
     * Simpan perubahan profil produsen (foto, nama, bio, instansi)
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'alamat'   => 'nullable|string|max:500',
            'no_hp'    => 'nullable|string|max:20',
            'bio'      => 'nullable|string|max:1000',
            'photo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $pendingData = [
            'name'     => $validated['name'],
            'instansi' => $validated['name'],
            'alamat'   => $validated['alamat'] ?? null,
            'no_hp'    => $validated['no_hp'] ?? null,
            'bio'      => $validated['bio'] ?? null,
        ];

        // Handle upload foto
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = 'profile_photos/pending_' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_photos', 'pending_' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension(), 'public');
            $pendingData['photo'] = $fileName;
        } else {
             // Jika tidak ada foto diunggah, kita simpan old photo agar preview verifikator konsisten (meskipun null)
             $pendingData['photo'] = $user->profile?->photo ?? null;
        }

        // Simpan hanya ke pending_data
        Profile::updateOrCreate(
            ['user_id' => $user->user_id],
            ['pending_data' => $pendingData]
        );

        return redirect()->route('produsen.profile')->with('success', '✅ Perubahan profil disimpan dan sedang menunggu verifikasi!');
    }

    /**
     * Parse file GeoJSON dan Masukkan ke tabel layer_features via PostGIS
     */
    protected function parseAndInsertGeoJson($geospatial_id, $absolutePath)
    {
        try {
            // Hapus isi fitur yang lama dari layer ini jika ada
            DB::table('layer_features')->where('geospatial_id', $geospatial_id)->delete();

            if (!file_exists($absolutePath)) return;

            $content = file_get_contents($absolutePath);
            $geoJson = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON file for geospatial_id ' . $geospatial_id);
                return;
            }

            if (isset($geoJson['features']) && is_array($geoJson['features'])) {
                foreach ($geoJson['features'] as $feature) {
                    if (!isset($feature['geometry']) || empty($feature['geometry'])) continue;
                    
                    $geomJson = json_encode($feature['geometry']);
                    $propsJson = isset($feature['properties']) ? json_encode($feature['properties']) : null;

                    // Menggunakan ST_GeomFromGeoJSON untuk PostGIS
                    DB::insert(
                        "INSERT INTO layer_features (geospatial_id, properties, geom, created_at, updated_at) 
                         VALUES (?, ?::jsonb, ST_GeomFromGeoJSON(?), NOW(), NOW())",
                        [$geospatial_id, $propsJson, $geomJson]
                    );
                }
            } elseif (isset($geoJson['type']) && $geoJson['type'] === 'Feature') {
                 if (isset($geoJson['geometry'])) {
                     $geomJson = json_encode($geoJson['geometry']);
                     $propsJson = isset($geoJson['properties']) ? json_encode($geoJson['properties']) : null;
                     
                     DB::insert(
                         "INSERT INTO layer_features (geospatial_id, properties, geom, created_at, updated_at) 
                          VALUES (?, ?::jsonb, ST_GeomFromGeoJSON(?), NOW(), NOW())",
                         [$geospatial_id, $propsJson, $geomJson]
                     );
                 }
            }
        } catch (\Exception $e) {
            Log::error('Parsing GeoJSON PostGIS Error: ' . $e->getMessage());
        }
    }
}