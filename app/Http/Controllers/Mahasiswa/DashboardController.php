<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ApiSyncHistory;
use App\Models\LoginHistory;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Semester;
use App\Services\AcademicMonitoringService;
use App\Services\UnujaApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected AcademicMonitoringService $monitoringService;

    public function __construct(AcademicMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Dashboard Utama Mahasiswa
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            $mahasiswa = Mahasiswa::where('nim', $user->username)->first();
            if ($mahasiswa) {
                $mahasiswa->user_id = $user->id;
                $mahasiswa->save();
            } else {
                $angkatan = strlen($user->username) >= 2 ? '20' . substr($user->username, 0, 2) : date('Y');
                $mahasiswa = Mahasiswa::create([
                    'user_id' => $user->id,
                    'nim' => $user->username,
                    'nama_lengkap' => $user->name,
                    'angkatan' => $angkatan,
                    'semester_berjalan' => 1,
                    'program_studi' => 'Teknologi Informasi',
                    'fakultas' => 'Teknik',
                    'status_akademik' => 'aktif',
                    'ipk' => 0.00,
                    'total_sks' => 0,
                ]);
            }
        }

        // Pastikan IPK & SKS sinkron
        $this->monitoringService->updateIpkDanTotalSks($mahasiswa);
        $peringatanTerbaru = $mahasiswa->peringatanAkademik()->latest()->first();

        // Hitung riwayat IPS per semester untuk grafik tren dan ringkasan semester
        $semesters = Semester::all()->sortBy(function ($smt) {
            return (int) preg_replace('/[^0-9]/', '', $smt->kode);
        });

        $semesterLabels = [];
        $ipsHistory = [];
        $semesterSummaries = [];

        foreach ($semesters as $smt) {
            $calc = $this->monitoringService->hitungIps($mahasiswa, $smt->id);
            if ($calc['jumlah_mk'] > 0) {
                $semesterLabels[] = $smt->nama;
                $ipsHistory[] = $calc['ips'];
                $semesterSummaries[$smt->nama] = [
                    'total_sks' => $calc['total_sks'],
                    'ips' => $calc['ips'],
                    'jumlah_mk' => $calc['jumlah_mk'],
                ];
            }
        }

        // Daftar nilai terurut berdasarkan semester dan kode mata kuliah
        $nilaiList = $mahasiswa->nilai()
            ->with(['mataKuliah', 'semester'])
            ->get()
            ->sortBy(function ($item) {
                $smtNum = (int) preg_replace('/[^0-9]/', '', $item->semester->kode ?? '1');
                return $smtNum * 1000 + ($item->mataKuliah->id ?? 0);
            });

        // Kelompokkan daftar mata kuliah berdasarkan semester
        $nilaiPerSemester = $nilaiList->groupBy(function ($item) {
            return $item->semester->nama ?? 'Semester 1';
        });

        // Riwayat sinkronisasi API UNUJA dan riwayat login mahasiswa
        $lastSync = ApiSyncHistory::where('filter_type', 'transkrip')
            ->where('identifier', $mahasiswa->nim)
            ->latest()
            ->first();

        $loginHistories = LoginHistory::where('identifier', $mahasiswa->nim)
            ->latest()
            ->limit(5)
            ->get();

        return view('mahasiswa.dashboard', [
            'mahasiswa' => $mahasiswa,
            'peringatan' => $peringatanTerbaru,
            'semesterLabels' => $semesterLabels,
            'ipsHistory' => $ipsHistory,
            'semesterSummaries' => $semesterSummaries,
            'nilaiList' => $nilaiList,
            'nilaiPerSemester' => $nilaiPerSemester,
            'lastSync' => $lastSync,
            'loginHistories' => $loginHistories,
        ]);
    }

    /**
     * Sinkronkan Ulang Data Transkrip Akademik dari API UNUJA
     */
    public function syncAkademik(Request $request, UnujaApiService $unujaApi): RedirectResponse
    {
        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa) {
            return back()->with('error', 'Profil data mahasiswa tidak ditemukan.');
        }

        $result = $unujaApi->syncTranskripMahasiswa($mahasiswa->nim, $request->user()->id);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        // Perbarui kalkulasi IPK & evaluasi dini
        $this->monitoringService->updateIpkDanTotalSks($mahasiswa);
        $this->monitoringService->evaluasiPeringatanDini($mahasiswa);

        return back()->with('success', $result['message'] . " (Waktu proses: {$result['duration_ms']} ms)");
    }

    /**
     * Tampilan Radar Kompetensi & Keahlian
     */
    public function radar(Request $request): View
    {
        $mahasiswa = $request->user()->mahasiswa;
        $profil = $this->monitoringService->hitungProfilKompetensi($mahasiswa);

        return view('mahasiswa.radar', [
            'mahasiswa' => $mahasiswa,
            'radarLabels' => $profil['labels'],
            'radarScores' => $profil['scores'],
            'radarDetails' => $profil['details'],
        ]);
    }

    /**
     * Tampilan & Kalkulasi Simulasi IPK
     */
    public function simulasi(Request $request): View
    {
        $mahasiswa = $request->user()->mahasiswa;
        $mataKuliahs = MataKuliah::orderBy('semester_paket', 'asc')->get();

        return view('mahasiswa.simulasi', [
            'mahasiswa' => $mahasiswa,
            'mataKuliahs' => $mataKuliahs,
        ]);
    }

    /**
     * Endpoint API JSON untuk Kalkulasi Simulasi IPK Realtime
     */
    public function hitungSimulasi(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama_mk' => ['required', 'string'],
            'items.*.sks' => ['required', 'integer', 'min:1', 'max:6'],
            'items.*.nilai_angka' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $mahasiswa = $request->user()->mahasiswa;
        $hasil = $this->monitoringService->simulasiIpk($mahasiswa, $request->input('items'));

        return response()->json($hasil);
    }

    /**
     * Tampilan Detail Peringatan Dini Akademik
     */
    public function peringatan(Request $request): View
    {
        $mahasiswa = $request->user()->mahasiswa;
        $peringatan = $this->monitoringService->evaluasiPeringatanDini($mahasiswa);

        return view('mahasiswa.peringatan', [
            'mahasiswa' => $mahasiswa,
            'peringatan' => $peringatan,
        ]);
    }

    /**
     * Kelola Tautan Berbagi Profil Publik (Generate / Cabut)
     */
    public function toggleShare(Request $request): RedirectResponse
    {
        $mahasiswa = $request->user()->mahasiswa;

        if ($request->input('action') === 'revoke') {
            $mahasiswa->revokeShareToken();
            return back()->with('success', 'Tautan berbagi profil publik telah dinonaktifkan.');
        }

        $token = $mahasiswa->generateShareToken();
        $shareUrl = route('profil.publik', ['token' => $token]);

        return back()->with('success', 'Tautan berbagi profil publik aktif: ' . $shareUrl);
    }
}

