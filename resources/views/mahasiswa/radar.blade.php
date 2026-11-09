@extends('layouts.app')

@section('title', 'Radar Kompetensi Mahasiswa')
@section('page-title', 'Pemetaan Kompetensi & Radar Keahlian')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
                Capaian Pembelajaran Lulusan (CPL) TI UNUJA
            </span>
            <h1 class="text-xl font-bold text-slate-800">Profil Radar Kompetensi Mahasiswa</h1>
            <p class="text-xs text-slate-500 mt-1">
                Visualisasi integrasi nilai mata kuliah terhadap 7 pilar profil keahlian lulusan Teknologi Informasi.
            </p>
        </div>
        <div class="text-right">
            <span class="text-xs text-slate-400 block">Mahasiswa:</span>
            <span class="font-bold text-sm text-slate-800">{{ $mahasiswa->nama_lengkap }} ({{ $mahasiswa->nim }})</span>
        </div>
    </div>

    <!-- Radar Chart & Summary Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Radar Chart Container -->
        <div class="lg:col-span-6 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex flex-col items-center justify-center">
            <h3 class="font-bold text-slate-800 text-sm mb-4 self-start flex items-center gap-2">
                <i class="fa-solid fa-chart-radar text-blue-600"></i>
                <span>Grafik Radar Kompetensi (Skala 0 - 100)</span>
            </h3>
            <div class="w-full h-80 sm:h-96">
                <canvas id="radarChart"></canvas>
            </div>
            <p class="text-[11px] text-slate-400 text-center mt-2">
                Grafik dihitung otomatis berdasarkan akumulasi bobot dan perolehan nilai mata kuliah terkait.
            </p>
        </div>

        <!-- Kompetensi Cards & Rekomendasi Pengembangan -->
        <div class="lg:col-span-6 space-y-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-lightbulb text-amber-500"></i>
                <span>Rincian & Rekomendasi Peningkatan Kompetensi</span>
            </h3>

            <div class="space-y-3">
                @foreach($radarDetails as $item)
                    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-blue-300 transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="font-bold text-sm text-slate-800">{{ $item['nama'] }}</span>
                                <span class="text-[11px] text-slate-400 block font-mono">Kode: {{ $item['kode'] }} • {{ $item['mk_ditempuh'] }}/{{ $item['total_mk'] }} MK ditempuh</span>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-black {{ $item['skor'] >= 75 ? 'text-blue-600' : ($item['skor'] >= 60 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ $item['skor'] }}
                                </span>
                                <span class="text-[10px] block font-semibold px-2 py-0.5 rounded-full {{ $item['status'] === 'Tinggi' ? 'bg-emerald-50 text-emerald-700' : ($item['status'] === 'Cukup' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                    {{ $item['status'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-100 rounded-full h-2 mb-2 overflow-hidden">
                            <div class="h-2 rounded-full {{ $item['skor'] >= 75 ? 'bg-blue-600' : ($item['skor'] >= 60 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min(100, $item['skor']) }}%"></div>
                        </div>

                        <!-- Rekomendasi Praktis -->
                        <div class="text-[11px] text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 flex items-start gap-2">
                            <i class="fa-solid fa-arrow-turn-up text-blue-500 mt-0.5"></i>
                            <div>
                                <span class="font-semibold text-slate-700">Rekomendasi: </span>
                                @if($item['skor'] >= 80)
                                    Pertahankan performa unggul. Direkomendasikan mengambil proyek capstone atau sertifikasi profesional di bidang ini.
                                @elseif($item['skor'] >= 65)
                                    Tingkatkan jam praktikum mandiri dan aktif berdiskusi dalam kelompok belajar untuk pemantapan konsep.
                                @else
                                    Perlu pendampingan belajar tambahan. Ikuti asistensi laboratorium, pelajari kembali modul dasar, dan konsultasi dengan dosen pengampu.
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const radarCtx = document.getElementById('radarChart').getContext('2d');
    const radarLabels = @json($radarLabels);
    const radarScores = @json($radarScores);

    new Chart(radarCtx, {
        type: 'radar',
        data: {
            labels: radarLabels,
            datasets: [{
                label: 'Skor Kompetensi Mahasiswa',
                data: radarScores,
                backgroundColor: 'rgba(37, 99, 235, 0.25)',
                borderColor: '#2563eb',
                borderWidth: 2,
                pointBackgroundColor: '#1d4ed8',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#1d4ed8',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: { color: '#e2e8f0' },
                    grid: { color: '#e2e8f0' },
                    pointLabels: {
                        font: { size: 11, weight: '600' },
                        color: '#334155'
                    },
                    suggestedMin: 0,
                    suggestedMax: 100,
                    ticks: { stepSize: 20 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>
@endpush
@endsection

