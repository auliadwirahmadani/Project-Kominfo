<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeospatialLayer;
use App\Models\MetadataLayer;

class VerifikatorController extends Controller
{
    /**
     * Menampilkan halaman Dashboard Verifikator
     */
    public function dashboard()
    {
        $totalPending  = GeospatialLayer::where('status_verifikasi', 'pending')->count();
        $totalApproved = GeospatialLayer::where('status_verifikasi', 'approved')->count();
        $totalRejected = GeospatialLayer::where('status_verifikasi', 'rejected')->count();

        return view('layouts.verifikator.dashboard', compact('totalPending', 'totalApproved', 'totalRejected'));
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
        $metadatas = MetadataLayer::with('geospatial')->latest()->paginate(10);
        return view('layouts.verifikator.periksametadata', compact('metadatas'));
    }

    /**
     * Menampilkan halaman Monitoring Status
     */
    public function monitoring()
    {
        $layers = GeospatialLayer::with(['category', 'metadata'])->latest()->paginate(10);
        return view('layouts.verifikator.monitoringstatus', compact('layers'));
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