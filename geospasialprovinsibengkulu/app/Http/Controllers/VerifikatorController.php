<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeospatialLayer;
use App\Models\MetadataLayer;

class VerifikatorController extends Controller
{
    /**
     * Menampilkan halaman Dashboard Verifikator (sekaligus Monitoring Status)
     */
    public function dashboard(Request $request)
    {
        // Core stats
        $totalPending   = GeospatialLayer::where('status_verifikasi', 'pending')->count();
        $totalApproved  = GeospatialLayer::where('status_verifikasi', 'approved')->count();
        $totalRejected  = GeospatialLayer::where('status_verifikasi', 'rejected')->count();
        $totalPublished = GeospatialLayer::where('is_published', true)->count();
        $totalAll       = GeospatialLayer::count();

        // Recent activity (latest 20 updated)
        $recentActivity = GeospatialLayer::with(['category', 'metadata'])
            ->latest('updated_at')
            ->take(20)
            ->get();

        // Paginated table with filters
        $query = GeospatialLayer::with(['category', 'metadata']);

        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('layer_name', 'like', '%' . $request->search . '%');
        }

        $layers = $query->latest()->paginate(15)->withQueryString();

        return view('layouts.verifikator.dashboard', compact(
            'layers',
            'totalPending',
            'totalApproved',
            'totalRejected',
            'totalPublished',
            'totalAll',
            'recentActivity'
        ));
    }

    /**
     * Menampilkan halaman list geospasial untuk diverifikasi
     */
    public function geospasial()
    {
        $layers = GeospatialLayer::with(['category', 'metadata'])->latest()->paginate(10);
        return view('layouts.verifikator.periksageospasial', compact('layers'));
    }

    /**
     * Menampilkan halaman detail verifikasi satu layer
     */
    public function showVerification($id)
    {
        $layer = GeospatialLayer::with(['category', 'metadata'])->findOrFail($id);
        return view('layouts.verifikator.detailverifikasi', compact('layer'));
    }

    /**
     * Memproses keputusan verifikasi (Setuju / Tolak / Revisi)
     */
    public function processVerification(Request $request, $id)
    {
        $request->validate([
            'status_verifikasi' => 'required|in:approved,rejected,pending',
            'catatan'           => 'nullable|string|max:500',
        ]);

        $layer = GeospatialLayer::findOrFail($id);
        $layer->status_verifikasi = $request->status_verifikasi;
        $layer->catatan_verifikator = $request->catatan;

        // Jika approved, otomatis publish
        if ($request->status_verifikasi === 'approved') {
            $layer->is_published = true;
        } elseif ($request->status_verifikasi === 'rejected') {
            $layer->is_published = false;
        }

        $layer->save();

        return redirect()->route('verifikator.geospasial.index')
                         ->with('success', '✅ Status verifikasi berhasil diperbarui!');
    }

    /**
     * Menampilkan halaman Periksa Metadata
     */
    public function metadata()
    {
        $metadatas = MetadataLayer::with(['geospatial', 'geospatial.category'])->latest()->paginate(10);
        return view('layouts.verifikator.periksametadata', compact('metadatas'));
    }

    /**
     * Memproses keputusan verifikasi melalui halaman Metadata
     * (Mengubah status_verifikasi & is_published di geospatial_layer terkait)
     */
    public function processMetadataVerification(Request $request, $id)
    {
        $request->validate([
            'status_verifikasi' => 'required|in:approved,rejected,pending',
            'catatan'           => 'nullable|string|max:500',
        ]);

        $metadata = MetadataLayer::with('geospatial')->findOrFail($id);
        $layer    = $metadata->geospatial;

        if ($layer) {
            $layer->status_verifikasi = $request->status_verifikasi;
            $layer->catatan_verifikator = $request->catatan;
            $layer->is_published = ($request->status_verifikasi === 'approved');
            $layer->save();
        }

        return redirect()->route('verifikator.metadata.index')
                         ->with('success', '✅ Status metadata berhasil diperbarui!');
    }


    // ==========================================
    // FUNGSI UNTUK MENERIMA AKSI SETUJU/TOLAK (LEGACY - akan dihapus)
    // ==========================================

    public function keputusanGeospasial(Request $request, $id)
    {
        return $this->processVerification($request, $id);
    }

    public function keputusanMetadata(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $metadata = MetadataLayer::findOrFail($id);
        $metadata->save();

        return redirect()->back()->with('success', '✅ Status metadata berhasil diperbarui!');
    }

    // ==========================================
    // PROFIL VERIFIKATOR
    // ==========================================
    public function profile()
    {
        $user = auth()->user();
        $profile = $user->profile ?? new \App\Models\Profile(['user_id' => $user->user_id]);
        return view('layouts.verifikator.profile', compact('user', 'profile'));
    }

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

        $user->update(['name' => $validated['name']]);

        $profileData = [
            'instansi' => $validated['name'],
            'alamat'   => $validated['alamat'] ?? null,
            'no_hp'    => $validated['no_hp'] ?? null,
            'bio'      => $validated['bio'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $existingProfile = $user->profile;
            if ($existingProfile && $existingProfile->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($existingProfile->photo);
            }
            $file = $request->file('photo');
            $fileName = 'profile_photos/' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_photos', $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension(), 'public');
            $profileData['photo'] = $fileName;
        }

        \App\Models\Profile::updateOrCreate(
            ['user_id' => $user->user_id],
            $profileData
        );

        return redirect()->route('verifikator.profile')->with('success', 'Profil berhasil diperbarui');
    }

    // ==========================================
    // VERIFIKASI PROFIL PRODUSEN
    // ==========================================
    public function verifikasiProfilIndex()
    {
        // Ambil produsen yang memiliki data di kolom pending_data (tidak null atau kosong)
        $profiles = \App\Models\Profile::with('user')
            ->whereNotNull('pending_data')
            ->where('pending_data', '!=', '[]')
            ->orderBy('profile_id', 'desc')
            ->paginate(10);
            
        return view('layouts.verifikator.verifikasiprofil', compact('profiles'));
    }

    public function processProfilVerification(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $profile = \App\Models\Profile::findOrFail($id);
        $pendingData = $profile->pending_data;

        if (!$pendingData) {
            return redirect()->back()->with('error', 'Tidak ada data perubahan profil yang tertunda.');
        }

        if ($request->status === 'approved') {
            // Update nama user jika ada
            if (isset($pendingData['name'])) {
                $user = $profile->user;
                if ($user) {
                    $user->name = $pendingData['name'];
                    $user->save();
                }
            }

            // Hapus old photo from storage
            if (isset($pendingData['photo']) && $pendingData['photo'] != $profile->photo) {
                 if ($profile->photo) {
                     \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->photo);
                 }
                 $profile->photo = $pendingData['photo'];
            }

            $profile->instansi = $pendingData['instansi'] ?? $profile->instansi;
            $profile->alamat   = $pendingData['alamat'] ?? $profile->alamat;
            $profile->no_hp    = $pendingData['no_hp'] ?? $profile->no_hp;
            $profile->bio      = $pendingData['bio'] ?? $profile->bio;
            $profile->pending_data = null;
            $profile->save();

            return redirect()->back()->with('success', 'Perubahan profil instansi disetujui!');

        } else {
            // Ditolak: Hapus file foto pending jika ada dan hapus isian pending_data
            if (isset($pendingData['photo']) && $pendingData['photo'] != $profile->photo) {
                // If it's a new upload starts with 'profile_photos/pending_'
                if (str_starts_with($pendingData['photo'], 'profile_photos/pending_')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($pendingData['photo']);
                }
            }

            $profile->pending_data = null;
            $profile->save();

            return redirect()->back()->with('success', 'Perubahan profil instansi ditolak dan dibatalkan!');
        }
    }
}