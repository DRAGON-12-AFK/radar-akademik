<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiSyncHistory;
use App\Models\DosenWali;
use App\Models\Keahlian;
use App\Models\LoginHistory;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Services\UnujaApiService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(UnujaApiService $unujaApi): View
    {
        $totalMahasiswa = Mahasiswa::count();
        $totalDosenWali = DosenWali::count();
        $totalMataKuliah = MataKuliah::count();
        $totalKeahlian = Keahlian::count();

        $risikoMerah = Mahasiswa::where('status_risiko', 'merah')->count();
        $risikoKuning = Mahasiswa::where('status_risiko', 'kuning')->count();
        $risikoHijau = Mahasiswa::where('status_risiko', 'hijau')->count();

        $recentMahasiswas = Mahasiswa::with('dosenWali')->latest()->limit(10)->get();

        // Riwayat Permanen MySQL untuk Audit Admin
        $apiSyncHistories = ApiSyncHistory::latest()->limit(15)->get();
        $loginHistories = LoginHistory::latest()->limit(15)->get();
        $redisAvailable = $unujaApi->isRedisAvailable();

        return view('admin.dashboard', [
            'totalMahasiswa' => $totalMahasiswa,
            'totalDosenWali' => $totalDosenWali,
            'totalMataKuliah' => $totalMataKuliah,
            'totalKeahlian' => $totalKeahlian,
            'risikoMerah' => $risikoMerah,
            'risikoKuning' => $risikoKuning,
            'risikoHijau' => $risikoHijau,
            'recentMahasiswas' => $recentMahasiswas,
            'apiSyncHistories' => $apiSyncHistories,
            'loginHistories' => $loginHistories,
            'redisAvailable' => $redisAvailable,
        ]);
    }
}

