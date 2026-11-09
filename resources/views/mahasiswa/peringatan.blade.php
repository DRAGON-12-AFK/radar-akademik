@extends('layouts.app')

@section('title', 'Peringatan Dini Akademik')
@section('page-title', 'Sistem Peringatan Dini Akademik')

@section('content')
<div class="space-y-6">
    <!-- Status Utama Banner -->
    <div class="rounded-2xl p-6 border shadow-xs {{ $peringatan->status_risiko === 'hijau' ? 'bg-emerald-50/70 border-emerald-200 text-emerald-950' : ($peringatan->status_risiko === 'kuning' ? 'bg-amber-50/70 border-amber-200 text-amber-950' : 'bg-rose-50/70 border-rose-200 text-rose-950') }}">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0 {{ $peringatan->status_risiko === 'hijau' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : ($peringatan->status_risiko === 'kuning' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'bg-rose-500 text-white shadow-md shadow-rose-500/20') }}">
                    <i class="fa-solid {{ $peringatan->status_risiko === 'hijau' ? 'fa-circle-check' : ($peringatan->status_risiko === 'kuning' ? 'fa-triangle-exclamation' : 'fa-bell') }}"></i>
                </div>
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-1 {{ $peringatan->status_risiko === 'hijau' ? 'bg-emerald-200 text-emerald-800' : ($peringatan->status_risiko === 'kuning' ? 'bg-amber-200 text-amber-800' : 'bg-rose-200 text-rose-800') }}">
                        Status: {{ ucfirst($peringatan->status_risiko) }} ({{ $peringatan->status_risiko === 'hijau' ? 'Kondisi Baik' : ($peringatan->status_risiko === 'kuning' ? 'Perlu Perhatian' : 'Tindakan Segera') }})
                    </span>
                    <h2 class="text-xl font-black">
                        {{ $peringatan->status_risiko === 'hijau' ? 'Perkembangan Akademik Anda Berada Dalam Status Aman' : ($peringatan->status_risiko === 'kuning' ? 'Terdapat Beberapa Indikator Studi yang Perlu Anda Evaluasi' : 'Peringatan Kritis: Diperlukan Penanganan Akademik Segera') }}
                    </h2>
                    <p class="text-xs opacity-80 mt-1">
                        Dihitung pada: {{ $peringatan->dihitung_pada ? $peringatan->dihitung_pada->translatedFormat('d F Y H:i') : now()->translatedFormat('d F Y H:i') }} • Skor Risiko: <span class="font-bold">{{ $peringatan->skor_risiko }}/100</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Indikator Evaluasi Transparan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. IPK -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Indikator 1: IPK</span>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ number_format($mahasiswa->ipk, 2) }}</div>
            <div class="mt-2 text-xs">
                @if($mahasiswa->ipk >= 3.00)
                    <span class="text-emerald-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-check"></i> Memenuhi Target (>= 3.00)
                    </span>
                @elseif($mahasiswa->ipk >= 2.50)
                    <span class="text-amber-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation"></i> Batas Waspada (2.50 - 2.99)
                    </span>
                @else
                    <span class="text-rose-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-xmark"></i> Di Bawah Standar (< 2.50)
                    </span>
                @endif
            </div>
        </div>

        <!-- 2. Kehadiran -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Indikator 2: Kehadiran</span>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ number_format($mahasiswa->persentase_kehadiran_total, 1) }}%</div>
            <div class="mt-2 text-xs">
                @if($mahasiswa->persentase_kehadiran_total >= 80)
                    <span class="text-emerald-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-check"></i> Presensi Sangat Baik
                    </span>
                @elseif($mahasiswa->persentase_kehadiran_total >= 75)
                    <span class="text-amber-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation"></i> Mendekati Batas Minimal (75%)
                    </span>
                @else
                    <span class="text-rose-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-xmark"></i> Terancam Larangan Ujian
                    </span>
                @endif
            </div>
        </div>

        <!-- 3. Nilai D atau E -->
        @php
            $nilaiECount = $mahasiswa->nilai()->where('nilai_huruf', 'E')->count();
            $nilaiDCount = $mahasiswa->nilai()->where('nilai_huruf', 'D')->count();
        @endphp
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Indikator 3: Nilai D & E</span>
            <div class="text-2xl font-black text-slate-800 mt-1">
                {{ $nilaiECount }} <span class="text-xs font-normal text-slate-500">Nilai E</span> / {{ $nilaiDCount }} <span class="text-xs font-normal text-slate-500">Nilai D</span>
            </div>
            <div class="mt-2 text-xs">
                @if($nilaiECount === 0 && $nilaiDCount === 0)
                    <span class="text-emerald-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-check"></i> Bersih dari Nilai D & E
                    </span>
                @elseif($nilaiECount > 0)
                    <span class="text-rose-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i> Wajib Mengulang Nilai E
                    </span>
                @else
                    <span class="text-amber-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation"></i> Perlu Perbaikan Nilai D
                    </span>
                @endif
            </div>
        </div>

        <!-- 4. Beban SKS -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Indikator 4: Progres SKS</span>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $mahasiswa->total_sks }} SKS</div>
            <div class="mt-2 text-xs">
                <span class="text-slate-600 font-medium">Semester {{ $mahasiswa->semester_berjalan }} (Target ideal: {{ $mahasiswa->semester_berjalan * 18 }} SKS)</span>
            </div>
        </div>
    </div>

    <!-- Alasan & Rekomendasi Terinci -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-3">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-blue-600"></i>
                <span>Hasil Diagnosis & Alasan Penilaian</span>
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-200">
                {{ $peringatan->alasan }}
            </p>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-3">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-hand text-emerald-600"></i>
                <span>Rekomendasi Tindakan Nyata</span>
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed bg-emerald-50/50 p-4 rounded-xl border border-emerald-200 text-emerald-900">
                {{ $peringatan->rekomendasi }}
            </p>
        </div>
    </div>
</div>
@endsection

