<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Services\AcademicMonitoringService;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    protected AcademicMonitoringService $monitoringService;

    public function __construct(AcademicMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function show(string $token): View
    {
        $mahasiswa = Mahasiswa::where('share_token', $token)
            ->where('share_token_active', true)
            ->firstOrFail();

        $profilRadar = $this->monitoringService->hitungProfilKompetensi($mahasiswa);
        $nilaiList = $mahasiswa->nilai()->with(['mataKuliah', 'semester'])->latest()->get();

        return view('public.profile', [
            'mahasiswa' => $mahasiswa,
            'radarLabels' => $profilRadar['labels'],
            'radarScores' => $profilRadar['scores'],
            'radarDetails' => $profilRadar['details'],
            'nilaiList' => $nilaiList,
        ]);
    }
}

