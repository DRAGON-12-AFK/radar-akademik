<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\ApiSyncHistory;
use App\Models\CatatanPembimbingan;
use App\Models\DosenWali;
use App\Models\Mahasiswa;
use App\Services\AcademicMonitoringService;
use App\Services\UnujaApiService;
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
     * Dashboard Dosen Wali: Daftar Mahasiswa Bimbingan & Mahasiswa Scope API UNUJA
     */
    public function index(Request $request, UnujaApiService $unujaApi): View
    {
        $dosenWali = $request->user()->dosenWali;

        if (!$dosenWali) {
            abort(404, 'Profil Dosen Wali tidak ditemukan.');
        }

        $mahasiswas = $dosenWali->mahasiswas()
            ->with(['peringatanAkademik' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $totalBimbingan = $mahasiswas->count();
        $risikoMerah = $mahasiswas->where('status_risiko', 'merah')->count();
        $risikoKuning = $mahasiswas->where('status_risiko', 'kuning')->count();
        $risikoHijau = $mahasiswas->where('status_risiko', 'hijau')->count();

        // Mengambil daftar mahasiswa yang diizinkan oleh kewenangan API UNUJA (Fakultas / Prodi)
        $apiMahasiswas = $unujaApi->getMahasiswasScope();

        return view('dosen.dashboard', [
            'dosen' => $dosenWali,
            'mahasiswas' => $mahasiswas,
            'apiMahasiswas' => $apiMahasiswas,
            'totalBimbingan' => $totalBimbingan,
            'risikoMerah' => $risikoMerah,
            'risikoKuning' => $risikoKuning,
            'risikoHijau' => $risikoHijau,
        ]);
    }

    /**
     * Tampilan Prioritas Mahasiswa dengan Risiko Tertinggi
     */
    public function prioritas(Request $request): View
    {
        $dosenWali = $request->user()->dosenWali;

        $mahasiswas = $dosenWali->mahasiswas()
            ->with(['peringatanAkademik' => fn ($q) => $q->latest()->limit(1)])
            ->get()
            ->sortByDesc(function ($m) {
                $priorityWeight = match ($m->status_risiko) {
                    'merah' => 300,
                    'kuning' => 200,
                    default => 100,
                };
                return $priorityWeight + (4.0 - (float) $m->ipk);
            });

        return view('dosen.prioritas', [
            'dosen' => $dosenWali,
            'mahasiswas' => $mahasiswas,
        ]);
    }

    /**
     * Buka dan sinkronkan data mahasiswa dari scope API UNUJA
     */
    public function viewScopeMahasiswa(Request $request, string $nim, UnujaApiService $unujaApi): RedirectResponse
    {
        $dosenWali = $request->user()->dosenWali;

        // Sinkronkan data transkrip mahasiswa dari API UNUJA
        $syncResult = $unujaApi->syncTranskripMahasiswa($nim, null);

        $mahasiswa = Mahasiswa::where('nim', $nim)->first();
        if (!$mahasiswa) {
            return back()->with('error', $syncResult['message'] ?? 'Data mahasiswa tidak ditemukan.');
        }

        // Tautkan dosen wali jika belum tertaut
        if (empty($mahasiswa->dosen_wali_id)) {
            $mahasiswa->dosen_wali_id = $dosenWali->id;
            $mahasiswa->save();
        }

        $this->monitoringService->updateIpkDanTotalSks($mahasiswa);
        $this->monitoringService->evaluasiPeringatanDini($mahasiswa);

        return redirect()->route('dosen.mahasiswa.detail', $mahasiswa->id);
    }

    /**
     * Detail Akademik Mahasiswa Tertentu untuk Dosen Wali
     */
    public function detailMahasiswa(Request $request, Mahasiswa $mahasiswa): View
    {
        $dosenWali = $request->user()->dosenWali;

        // Otorisasi: Dosen Wali hanya boleh melihat data yang diizinkan
        if ($mahasiswa->dosen_wali_id !== $dosenWali->id && $mahasiswa->fakultas !== $dosenWali->fakultas && !$request->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat data mahasiswa ini.');
        }

        $profilRadar = $this->monitoringService->hitungProfilKompetensi($mahasiswa);
        $nilaiList = $mahasiswa->nilai()->with(['mataKuliah', 'semester'])->latest()->get();
        $peringatan = $mahasiswa->peringatanAkademik()->latest()->first();
        $catatanList = $mahasiswa->catatanPembimbingan()->with('dosenWali')->latest()->get();

        $lastSync = ApiSyncHistory::where('filter_type', 'transkrip')
            ->where('identifier', $mahasiswa->nim)
            ->latest()
            ->first();

        return view('dosen.detail_mahasiswa', [
            'mahasiswa' => $mahasiswa,
            'radarLabels' => $profilRadar['labels'],
            'radarScores' => $profilRadar['scores'],
            'nilaiList' => $nilaiList,
            'peringatan' => $peringatan,
            'catatanList' => $catatanList,
            'lastSync' => $lastSync,
        ]);
    }

    /**
     * Sinkronkan Ulang Transkrip Mahasiswa dari API UNUJA
     */
    public function syncMahasiswa(Request $request, Mahasiswa $mahasiswa, UnujaApiService $unujaApi): RedirectResponse
    {
        $dosenWali = $request->user()->dosenWali;

        if ($mahasiswa->dosen_wali_id !== $dosenWali->id && $mahasiswa->fakultas !== $dosenWali->fakultas && !$request->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyinkronkan data mahasiswa ini.');
        }

        $result = $unujaApi->syncTranskripMahasiswa($mahasiswa->nim, $request->user()->id);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        $this->monitoringService->updateIpkDanTotalSks($mahasiswa);
        $this->monitoringService->evaluasiPeringatanDini($mahasiswa);

        return back()->with('success', 'Data akademik dan transkrip mahasiswa berhasil disinkronkan dari API UNUJA (' . $result['duration_ms'] . ' ms).');
    }

    /**
     * Simpan Catatan / Rekomendasi Pembimbingan Baru
     */
    public function storeCatatan(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $dosenWali = $request->user()->dosenWali;

        if ($mahasiswa->dosen_wali_id !== $dosenWali->id && !$request->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'catatan' => ['required', 'string'],
            'rekomendasi' => ['nullable', 'string'],
            'status' => ['required', 'in:menunggu,ditindaklanjuti,selesai'],
        ]);

        CatatanPembimbingan::create([
            'dosen_wali_id' => $dosenWali->id,
            'mahasiswa_id' => $mahasiswa->id,
            'tanggal' => now()->toDateString(),
            'catatan' => $validated['catatan'],
            'rekomendasi' => $validated['rekomendasi'],
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Catatan pembimbingan akademik berhasil disimpan.');
    }
}

