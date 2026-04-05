<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GeospatialController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MetadataController;
use App\Http\Controllers\VerifikatorController;
use App\Http\Controllers\ProdusenController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [MapController::class, 'index'])->name('geo');
Route::get('/catalog', fn() => view('catalog'))->name('catalog');
Route::get('/about', fn() => view('about'))->name('about');

// ===============================
// ROUTE UNTUK FILTER PETA
// ===============================
Route::get('/geospatial/filter', [GeospatialController::class, 'filterData'])->name('geospatial.filter');


/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {

        // ===============================
        // DASHBOARD
        // ===============================
        Route::get('/dashboard', [AdminController::class, 'dashboard'])
            ->name('dashboard');

        // ===============================
        // KELOLA PENGGUNA
        // ===============================
        Route::get('/pengguna', [AdminController::class, 'kelolapengguna'])
            ->name('kelolapengguna');

        Route::post('/pengguna/store', [AdminController::class, 'storeUser'])
            ->name('storeUser');

        Route::put('/pengguna/update/{id}', [AdminController::class, 'updateUser'])
            ->name('updateUser');

        Route::delete('/pengguna/delete/{id}', [AdminController::class, 'destroyUser'])
            ->name('deleteUser');

        // ===============================
        // KELOLA GEOSPASIAL (ADMIN)
        // ===============================
        Route::resource('geospasial', GeospatialController::class);

        // ===============================
        // GEOEDIT - CUSTOM EDIT ROUTE
        // ===============================
        Route::get('/geoedit/{id}', [GeospatialController::class, 'edit'])
            ->name('geoedit');
        
        Route::put('/geoedit/{id}', [GeospatialController::class, 'update'])
            ->name('geoedit.update');

        // ===============================
        // ROUTE PREVIEW & DOWNLOAD PETA 
        // ===============================
        Route::get('/geospasial/{id}/geojson', [GeospatialController::class, 'getGeoJson'])
            ->name('geospasial.geojson');

        Route::get('/geospasial/{id}/download', [GeospatialController::class, 'download'])
            ->name('geospasial.download');

        // ===============================
        // MASTER REFERENSI
        // ===============================
        Route::get('/referensi', [AdminController::class, 'referensi'])
            ->name('masterreferensi');

        // ===============================
        // KELOLA METADATA
        // ===============================
        Route::get('/metadata', [MetadataController::class, 'index'])
            ->name('metadata.index');

        Route::post('/metadata/store', [MetadataController::class, 'store'])
            ->name('metadata.store');

        Route::put('/metadata/update/{id}', [MetadataController::class, 'update'])
            ->name('metadata.update');

        Route::delete('/metadata/delete/{id}', [MetadataController::class, 'destroy'])
            ->name('metadata.delete');

        // ===============================
        // PROFILE
        // ===============================
        Route::get('/profile', [AdminController::class, 'profile'])
            ->name('profile');
            
        Route::put('/profile/update/{id}', [AdminController::class, 'updateProfile'])
            ->name('updateProfile');

        // ===============================
        // PUBLIKASI DATA
        // ===============================
        Route::get('/publikasi', [AdminController::class, 'publikasi'])
            ->name('publikasi');

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

        // ===============================
        // DASHBOARD VERIFIKATOR
        // ===============================
        Route::get('/dashboard', [VerifikatorController::class, 'dashboard'])
            ->name('dashboard');

        // ===============================
        // VERIFIKASI DATA GEOSPASIAL
        // ===============================
        Route::get('/geospasial', [VerifikatorController::class, 'geospasial'])
            ->name('geospasial.index');

        // ===============================
        // PERIKSA METADATA
        // ===============================
        Route::get('/metadata', [VerifikatorController::class, 'metadata'])
            ->name('metadata.index');

        // ===============================
        // MONITORING STATUS
        // ===============================
        Route::get('/monitoring', [VerifikatorController::class, 'monitoring'])
            ->name('monitoring.index');

    }); // ✅ TANDA KURUNG PENUTUP VERIFIKATOR YANG BENAR


/*
|--------------------------------------------------------------------------
| PRODUSEN DATA ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('produsen')
    ->middleware(['auth'])
    ->name('produsen.')
    ->group(function () {

        // ===============================
        // DASHBOARD PRODUSEN
        // ===============================
        Route::get('/dashboard', [ProdusenController::class, 'dashboard'])
            ->name('dashboard');

        // ===============================
        // KELOLA DATA GEOSPASIAL
        // ===============================
        Route::get('/geospasial', [ProdusenController::class, 'geospasial'])
            ->name('geospasial.index');

        // ===============================
        // KELOLA METADATA
        // ===============================
        Route::get('/metadata', [ProdusenController::class, 'metadata'])
            ->name('metadata.index');

        // ===============================
        // MONITORING STATUS
        // ===============================
        Route::get('/monitoring', [ProdusenController::class, 'monitoring'])
            ->name('monitoring.index');

    }); // ✅ TANDA KURUNG PENUTUP PRODUSEN YANG BENAR