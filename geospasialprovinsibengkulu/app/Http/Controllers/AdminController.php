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

        // 3. Distribusi Kategori (menghitung jumlah layer tiap kategori)
        $categoryDistribution = Category::has('geospatialLayers')
                                ->withCount('geospatialLayers')
                                ->orderByDesc('geospatial_layers_count')
                                ->take(5)
                                ->get();

        // 4. Pengguna Baru Bergabung
        $recentUsers = User::latest()->take(5)->get();

        // 5. Statistik Mingguan (7 Hari Terakhir)
        $weeklyDates = collect();
        $weeklyCounts = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i);
            $weeklyDates->push($date->translatedFormat('D, d M')); 
            $count = GeospatialLayer::whereDate('created_at', $date->format('Y-m-d'))->count();
            $weeklyCounts->push($count);
        }

        // 6. Kirim data ke view
        return view('layouts.admin.dashboard', compact(
            'totalUsers', 
            'totalLayers', 
            'totalPublished', 
            'totalCategories',
            'recentActivities',
            'categoryDistribution',
            'recentUsers',
            'weeklyDates',
            'weeklyCounts'
        ));
    }

    // ===============================
    // LIST USER
    // ===============================
    public function kelolapengguna(Request $request)
    {
        $query = User::with(['role', 'profile']);

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

        $users = $query->orderBy('user_id', 'desc')->get();

        // Siapkan data untuk Alpine.js (hindari closure di Blade @json)
        $usersJson = $users->map(function ($u) {
            $profile   = $u->profile;
            $photoPath = $profile ? $profile->photo : null;
            $hasPhoto  = $photoPath && file_exists(public_path('storage/' . $photoPath));
            return [
                'user_id'   => $u->user_id,
                'name'      => $u->name ?? '',
                'email'     => $u->email ?? '',
                'role_name' => $u->role_name ?? 'Pengunjung',
                'instansi'  => $profile ? $profile->instansi : null,
                'photo_url' => $hasPhoto ? asset('storage/' . $photoPath) : null,
            ];
        })->values()->toArray();

        // Ambil roles dari database
        $roles = Role::orderBy('role_name')->get();

        return view('layouts.admin.kelolapengguna', compact('users', 'usersJson', 'roles'));
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
            'email' => 'required|email|unique:users,email,' . $id . ',user_id',
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
        $profile = $user->profile ?? new \App\Models\Profile(['user_id' => $user->user_id]);
        return view('layouts.admin.profile', compact('user', 'profile'));
    }

    public function updateProfile(Request $request, $id)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id . ',user_id',
            'alamat'   => 'nullable|string|max:500',
            'no_hp'    => 'nullable|string|max:20',
            'bio'      => 'nullable|string|max:1000',
            'photo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

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

        return redirect()->route('admin.profile')->with('success', 'Profil berhasil diperbarui');
    }

    // ===============================
    // PUBLIKASI DATA (TAMBAHAN BARU)
    // ===============================
    public function publikasi()
    {
        // Mengambil semua data geospasial dengan relasi kategori dan metadata untuk dikelola status publikasinya
        $layers = GeospatialLayer::with(['category', 'metadata'])->orderBy('updated_at', 'desc')->paginate(10);
        return view('layouts.admin.publikasi', compact('layers'));
    }

    public function togglePublikasi(Request $request, $id)
    {
        $layer = GeospatialLayer::findOrFail($id);
        
        // Toggle is_published boolean
        $layer->is_published = !$layer->is_published;
        $layer->save();

        $statusStr = $layer->is_published ? 'dipublikasikan' : 'ditarik dari publikasi';
        return redirect()->back()->with('success', "Dataset berhasil {$statusStr}!");
    }
}