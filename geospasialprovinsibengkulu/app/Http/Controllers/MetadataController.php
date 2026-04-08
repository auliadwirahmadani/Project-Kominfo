<?php

namespace App\Http\Controllers; // ⬅️ Ini WAJIB ada di baris ini

use Illuminate\Http\Request;
use App\Models\GeospatialLayer; // Pastikan nama model Anda benar ini
use App\Models\MetadataLayer;   // Pastikan nama model Anda benar ini
use Illuminate\Support\Facades\Storage;

class MetadataController extends Controller // ⬅️ Nama class WAJIB sama dengan nama file
{
    public function index()
    {
        $layers = GeospatialLayer::with(['category', 'metadata'])->get();
        return view('layouts.admin.masterreferensi', compact('layers'));
    }

    public function store(Request $request)
    {
        // Untuk tes awal, kita abaikan validasi dulu biar tahu ini nyambung atau tidak
        // dd($request->all()); 

        $validatedData = $request->validate([
            'geospatial_id'         => 'required',
            'identifier'            => 'required|string|max:255',
            'title'                 => 'required|string|max:255',
            'abstract'              => 'required|string',
            'organization'          => 'nullable|string|max:255',
            'data_type'             => 'required|string|max:100',
            'publication_date'      => 'required|date',
            'keywords'              => 'nullable|string',
            'distribution_protocol' => 'nullable|string|max:100',
            'distribution_url'      => 'nullable|url',
            'layer_name_service'    => 'nullable|string|max:255',
            'preview_image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'source'                => 'nullable|string|max:255',
            'year'                  => 'nullable|integer',
            'crs'                   => 'nullable|string|max:255',
            'scale'                 => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('preview_image')) {
            $validatedData['preview_image'] = $request->file('preview_image')->store('metadata/previews', 'public');
        }

        MetadataLayer::create($validatedData);

        return redirect()->back()->with('success', 'Metadata berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $metadata = MetadataLayer::findOrFail($id);

        $validatedData = $request->validate([
            'identifier'            => 'required|string|max:255',
            'title'                 => 'required|string|max:255',
            'abstract'              => 'required|string',
            'organization'          => 'nullable|string|max:255',
            'data_type'             => 'required|string|max:100',
            'publication_date'      => 'required|date',
            'keywords'              => 'nullable|string',
            'distribution_protocol' => 'nullable|string|max:100',
            'distribution_url'      => 'nullable|url',
            'layer_name_service'    => 'nullable|string|max:255',
            'preview_image'         => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'source'                => 'nullable|string|max:255',
            'year'                  => 'nullable|integer',
            'crs'                   => 'nullable|string|max:255',
            'scale'                 => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('preview_image')) {
            if ($metadata->preview_image && Storage::disk('public')->exists($metadata->preview_image)) {
                Storage::disk('public')->delete($metadata->preview_image);
            }
            $validatedData['preview_image'] = $request->file('preview_image')->store('metadata/previews', 'public');
        }

        $metadata->update($validatedData);

        return redirect()->back()->with('success', 'Metadata berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $metadata = MetadataLayer::findOrFail($id);

        if ($metadata->preview_image && Storage::disk('public')->exists($metadata->preview_image)) {
            Storage::disk('public')->delete($metadata->preview_image);
        }

        $metadata->delete();

        return redirect()->back()->with('success', 'Metadata berhasil dihapus!');
    }
}