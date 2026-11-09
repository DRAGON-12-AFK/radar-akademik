<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dosen\DashboardController as DosenDashboardController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\PublicProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Aplikasi Pemantau Studi Mahasiswa UNUJA
|--------------------------------------------------------------------------
*/

// Root Redirect & Fallbacks
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'dosen_wali' => redirect()->route('dosen.dashboard'),
            'mahasiswa' => redirect()->route('mahasiswa.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'dosen_wali' => redirect()->route('dosen.dashboard'),
            'mahasiswa' => redirect()->route('mahasiswa.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
})->name('dashboard');

Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// PWA Offline Fallback
Route::view('/offline', 'offline')->name('offline');

// Tautan Berbagi Profil Publik (Read-Only)
Route::get('/profil/{token}', [PublicProfileController::class, 'show'])->name('profil.publik');

// Pengujian Koneksi API UNUJA menggunakan X-Token
Route::get('/api-test', [\App\Http\Controllers\ApiTestController::class, 'testConnection'])->name('api.test');

// 1. Modul Mahasiswa
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
    Route::post('/sync', [MahasiswaDashboardController::class, 'syncAkademik'])->name('sync');
    Route::get('/radar', [MahasiswaDashboardController::class, 'radar'])->name('radar');
    Route::get('/simulasi', [MahasiswaDashboardController::class, 'simulasi'])->name('simulasi');
    Route::post('/simulasi/hitung', [MahasiswaDashboardController::class, 'hitungSimulasi'])->name('simulasi.hitung');
    Route::get('/peringatan', [MahasiswaDashboardController::class, 'peringatan'])->name('peringatan');
    Route::post('/share/toggle', [MahasiswaDashboardController::class, 'toggleShare'])->name('share.toggle');
});

// 2. Modul Dosen Wali
Route::middleware(['auth', 'role:dosen_wali,admin'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [DosenDashboardController::class, 'index'])->name('dashboard');
    Route::get('/prioritas', [DosenDashboardController::class, 'prioritas'])->name('prioritas');
    Route::get('/scope/{nim}', [DosenDashboardController::class, 'viewScopeMahasiswa'])->name('scope.view');
    Route::get('/mahasiswa/{mahasiswa}', [DosenDashboardController::class, 'detailMahasiswa'])->name('mahasiswa.detail');
    Route::post('/mahasiswa/{mahasiswa}/sync', [DosenDashboardController::class, 'syncMahasiswa'])->name('mahasiswa.sync');
    Route::post('/mahasiswa/{mahasiswa}/catatan', [DosenDashboardController::class, 'storeCatatan'])->name('mahasiswa.catatan.store');
});

// 3. Modul Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});
