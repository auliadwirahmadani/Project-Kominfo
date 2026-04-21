<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GeospatialController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MetadataController;
use App\Http\Controllers\VerifikatorController;
use App\Http\Controllers\ProdusenController;
use App\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// Home / Main Map
Route::get('/', [MapController::class, 'index'])->name('geo');

// About Page
Route::get('/about', function () {
    $totalLayers  = \App\Models\GeospatialLayer::where('is_published', 1)->where('status_verifikasi', 'approved')->count();
    $totalKategori = \App\Models\Category::count();
    $totalPengguna = \App\Models\User::count();
    $totalMetadata  = \App\Models\MetadataLayer::count();

    // Ambil semua produsen data beserta profil dan foto mereka
    $producens = \App\Models\User::with('profile')
        ->whereHas('role', fn($q) => $q->where('role_name', 'produsen'))
        ->get();

    return view('about', compact('totalLayers', 'totalKategori', 'totalPengguna', 'totalMetadata', 'producens'));
})->name('about');


Route::get('/debug-role', function() {
    $user = \App\Models\User::where('email', 'produsendiskominfotik@gmail.com')->first();
    if (!$user) return 'User not found';
    return response()->json([
        'user' => $user->toArray(),
        'role' => $user->role ? $user->role->toArray() : null,
        'role_relation_id' => $user->role_id,
    ]);
});

// ✅ 1. Katalog Data Publik (Halaman Grid/Kotak-kotak)
Route::get('/catalog', [GeospatialController::class, 'katalogDataset'])->name('catalog');
Route::get('/dataset', [GeospatialController::class, 'katalogDataset'])->name('dataset');

// ✅ 2. Detail Dataset (Halaman Detail Maroon-Kuning)
Route::get('/dataset/{id}', [GeospatialController::class, 'showDetail'])->name('dataset.show');

// ✅ 2b. Download Dataset Publik (tanpa login)
Route::get('/dataset/{id}/download', [GeospatialController::class, 'download'])->name('dataset.download');

// ✅ 3. Detail Instansi (Katalog Peta per OPD)
Route::get('/instansi/{id}', [GeospatialController::class, 'showInstansi'])->name('instansi.show');

// GeoJSON Public (Mini Map) - Route ini HARUS sebelum /geospatial/filter
Route::get('/geospatial/{id}/geojson', [GeospatialController::class, 'getGeoJson'])
    ->name('geospatial.geojson.public');

// Titik Akhir Baru untuk PostGIS Bounding Box API
Route::get('/api/map/features', [MapController::class, 'getFeaturesInView'])->name('api.map.features');

// Filter AJAX
Route::get('/geospatial/filter', [GeospatialController::class, 'filterData'])
    ->name('geospatial.filter');


/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Lupa Kata Sandi
Route::get('/lupa-kata-sandi', [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/lupa-kata-sandi', [PasswordResetController::class, 'sendResetLink'])->name('password.forgot.send');
Route::get('/reset-kata-sandi', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-kata-sandi', [PasswordResetController::class, 'resetPassword'])->name('password.reset.process');


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Kelola Pengguna
        Route::get('/pengguna', [AdminController::class, 'kelolapengguna'])->name('kelolapengguna');
        Route::post('/pengguna/store', [AdminController::class, 'storeUser'])->name('storeUser');
        Route::put('/pengguna/update/{id}', [AdminController::class, 'updateUser'])->name('updateUser');
        Route::delete('/pengguna/delete/{id}', [AdminController::class, 'destroyUser'])->name('deleteUser');

        // Resource Geospasial
        Route::resource('geospasial', GeospatialController::class);

        // Preview & Download
        Route::get('/geospasial/{id}/geojson', [GeospatialController::class, 'getGeoJson'])
            ->name('geospatial.geojson');
        Route::get('/geospasial/{id}/download', [GeospatialController::class, 'download'])
            ->name('geospatial.download');

        // Master Referensi
        Route::get('/referensi', [AdminController::class, 'referensi'])->name('masterreferensi');

        // Metadata
        Route::get('/metadata', [MetadataController::class, 'index'])->name('metadata.index');
        Route::post('/metadata/store', [MetadataController::class, 'store'])->name('metadata.store');
        Route::put('/metadata/update/{id}', [MetadataController::class, 'update'])->name('metadata.update');
        Route::delete('/metadata/delete/{id}', [MetadataController::class, 'destroy'])->name('metadata.delete');

        // Profile
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::put('/profile/update/{id}', [AdminController::class, 'updateProfile'])->name('updateProfile');

        // Publikasi
        Route::get('/publikasi', [AdminController::class, 'publikasi'])->name('publikasi');
        Route::post('/publikasi/toggle/{id}', [AdminController::class, 'togglePublikasi'])->name('publikasi.toggle');
    });


/*
|--------------------------------------------------------------------------
| VERIFIKATOR ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('verifikator')
    ->middleware(['auth'])
    ->name('verifikator.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [VerifikatorController::class, 'dashboard'])->name('dashboard');

        // Verifikasi Geospasial
        Route::get('/geospasial', [VerifikatorController::class, 'geospasial'])->name('geospasial.index');

        Route::get('/geospasial/{id}/verify', [VerifikatorController::class, 'showVerification'])
            ->name('geospasial.verify');

        Route::post('/geospasial/{id}/verify', [VerifikatorController::class, 'processVerification'])
            ->name('geospasial.verify.process');

        // Metadata
        Route::get('/metadata', [VerifikatorController::class, 'metadata'])->name('metadata.index');
        Route::post('/metadata/{id}/verify', [VerifikatorController::class, 'processMetadataVerification'])
            ->name('metadata.verify.process');

        // Profile Verifikator
        Route::get('/profile', [VerifikatorController::class, 'profile'])->name('profile');
        Route::put('/profile/update', [VerifikatorController::class, 'updateProfile'])->name('profile.update');

        // Verifikasi Profil Produsen
        Route::get('/verifikasi-profil', [VerifikatorController::class, 'verifikasiProfilIndex'])->name('verifikasiprofil.index');
        Route::post('/verifikasi-profil/{id}', [VerifikatorController::class, 'processProfilVerification'])->name('verifikasiprofil.process');

    });


/*
|--------------------------------------------------------------------------
| PRODUSEN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('produsen')
    ->middleware(['auth'])
    ->name('produsen.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [ProdusenController::class, 'dashboard'])->name('dashboard');

        // Geospasial
        Route::get('/geospasial', [ProdusenController::class, 'geospasial'])->name('geospasial.index');

        Route::get('/geospasial/create', [ProdusenController::class, 'create'])->name('geospasial.create');
        Route::post('/geospasial', [ProdusenController::class, 'store'])->name('geospasial.store');

        Route::get('/geospasial/{id}/edit', [ProdusenController::class, 'edit'])->name('geospasial.edit');
        Route::put('/geospasial/{id}', [ProdusenController::class, 'update'])->name('geospasial.update');

        Route::delete('/geospasial/{id}', [ProdusenController::class, 'destroy'])->name('geospasial.destroy');

        // Metadata
        Route::get('/metadata', [ProdusenController::class, 'metadata'])->name('metadata.index');
        Route::post('/metadata', [ProdusenController::class, 'storeMetadata'])->name('metadata.store');
        Route::put('/metadata/{id}', [ProdusenController::class, 'updateMetadata'])->name('metadata.update');

        // Profil Produsen
        Route::get('/profile', [ProdusenController::class, 'showProfile'])->name('profile');
        Route::post('/profile', [ProdusenController::class, 'updateProfile'])->name('profile.update');

    });