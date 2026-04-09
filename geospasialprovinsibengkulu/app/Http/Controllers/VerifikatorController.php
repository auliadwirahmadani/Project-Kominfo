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
}