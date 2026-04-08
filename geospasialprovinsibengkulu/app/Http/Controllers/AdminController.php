<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\GeospatialLayer; // ✅ Ditambahkan untuk memanggil data peta
use App\Models\Category; // ✅ Ditambahkan untuk menghitung total kategori
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ===============================
    // DASHBOARD
    // ===============================
    public function dashboard()
    {
        // 1. Menghitung Statistik
        $totalUsers = User::count();
        $totalLayers = GeospatialLayer::count();
        $totalPublished = GeospatialLayer::where('is_published', 1)->count();
        $totalCategories = Category::count();

        // 2. Mengambil 5 Data Geospasial terbaru untuk dijadikan "Aktivitas Terbaru"
        $recentActivities = GeospatialLayer::with('category')
                            ->latest()
                            ->take(5)
                            ->get();

        // 3. Kirim data ke view
        return view('layouts.admin.dashboard', compact(
            'totalUsers', 
            'totalLayers', 
            'totalPublished', 
            'totalCategories',
            'recentActivities'
        ));
    }

    // ===============================
    // LIST USER
    // ===============================
    public function kelolapengguna(Request $request)
    {
        $query = User::with('role');

        // SEARCH
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // FILTER ROLE
        if ($request->role_id) {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->orderBy('id', 'desc')->get();

        // Ambil roles dari database
        $roles = Role::orderBy('role_name')->get();

        return view('layouts.admin.kelolapengguna', compact('users', 'roles'));
    }

    // ===============================
    // STORE USER
    // ===============================
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_name' => 'required|exists:roles,role_name'
        ]);

        // Ambil role dari database
        $role = Role::where('role_name', $validated['role_name'])->firstOrFail();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->role_id,
            'role_name' => $role->role_name,
            'status' => 'aktif'
        ]);

        return redirect()->route('admin.kelolapengguna')
            ->with('success', 'User berhasil ditambahkan');
    }

    // ===============================
    // UPDATE USER
    // ===============================
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_name' => 'required|exists:roles,role_name',
            'password' => 'nullable|min:6'
        ]);

        // Ambil role dari database
        $role = Role::where('role_name', $validated['role_name'])->firstOrFail();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $role->role_id,
            'role_name' => $role->role_name
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.kelolapengguna')
            ->with('success', 'User berhasil diupdate');
    }

    // ===============================
    // DELETE USER
    // ===============================
    public function destroyUser($id)
    {
        User::destroy($id);

        return redirect()->route('admin.kelolapengguna')
            ->with('success', 'User berhasil dihapus');
    }

    // ===============================
    // MASTER REFERENSI (KELOLA METADATA)
    // ===============================
    public function referensi(Request $request) 
    {
        // Mengambil semua data layer beserta kategori dan metadatanya
        $layers = GeospatialLayer::with(['category', 'metadata'])->latest()->get();
        
        // Menangkap parameter jika ada dari URL
        $selectedLayerId = $request->query('layer_id');

        // Mengirim variabel ke view
        return view('layouts.admin.masterreferensi', compact('layers', 'selectedLayerId'));
    }

    // ===============================
    // PROFILE
    // ===============================
    public function profile()
    {
        $user = auth()->user();
        return view('layouts.admin.adminnav', compact('user'));
    }

    public function updateProfile(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6'
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
            $user->save();
        }

        return redirect()->route('admin.profile')->with('success', 'Profil berhasil diperbarui');
    }

    // ===============================
    // PUBLIKASI DATA (TAMBAHAN BARU)
    // ===============================
    public function publikasi()
    {
        // Menggunakan view layouts.admin.publikasi (sesuaikan jika nama foldernya berbeda)
        return view('layouts.admin.publikasi'); 
    }
}