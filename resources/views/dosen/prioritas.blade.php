@extends('layouts.app')

@section('title', 'Prioritas Risiko Akademik')
@section('page-title', 'Prioritas Tindakan & Risiko Akademik')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-rose-900 rounded-2xl p-6 text-white shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/30 text-rose-200 border border-rose-400/30 mb-2">
                Sistem Peringatan Dini Akademik
            </span>
            <h1 class="text-xl font-bold">Daftar Prioritas Pendampingan Mahasiswa</h1>
            <p class="text-xs text-rose-200 mt-1">
                Daftar mahasiswa bimbingan yang diurutkan berdasarkan tingkat risiko akademik tertinggi (Merah & Kuning).
            </p>
        </div>
        <a href="{{ route('dosen.dashboard') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-semibold self-start sm:self-auto border border-white/20">
            &larr; Kembali ke Daftar Semua
        </a>
    </div>

    <!-- Cards Prioritas Mahasiswa -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($mahasiswas as $m)
            @php
                $peringatan = $m->peringatanAkademik->first();
            @endphp
            <div class="bg-white rounded-2xl p-5 border {{ $m->status_risiko === 'merah' ? 'border-rose-300 shadow-rose-100' : ($m->status_risiko === 'kuning' ? 'border-amber-300 shadow-amber-100' : 'border-slate-200') }} shadow-md flex flex-col justify-between">
                <div>
                    <!-- Header Card -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div>
                            <span class="text-xs font-mono font-bold text-slate-500">{{ $m->nim }}</span>
                            <h4 class="font-bold text-slate-900 text-base leading-tight">{{ $m->nama_lengkap }}</h4>
                            <span class="text-[11px] text-slate-400">Angkatan {{ $m->angkatan }} • Smt {{ $m->semester_berjalan }}</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $m->status_risiko === 'merah' ? 'bg-rose-100 text-rose-800' : ($m->status_risiko === 'kuning' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                            {{ $m->status_risiko }}
                        </span>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-3 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-100 text-center mb-3">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">IPK</span>
                            <span class="text-sm font-black text-slate-800">{{ number_format($m->ipk, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">Kehadiran</span>
                            <span class="text-sm font-black {{ $m->persentase_kehadiran_total >= 75 ? 'text-slate-800' : 'text-rose-600' }}">
                                {{ number_format($m->persentase_kehadiran_total, 1) }}%
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">SKS</span>
                            <span class="text-sm font-black text-slate-800">{{ $m->total_sks }}</span>
                        </div>
                    </div>

                    <!-- Ringkasan Alasan Peringatan -->
                    @if($peringatan)
                        <div class="text-[11px] text-slate-600 bg-slate-50/80 p-3 rounded-xl border border-slate-200 mb-4 leading-relaxed">
                            <span class="font-bold text-slate-700 block mb-1">Diagnosis:</span>
                            {{ Str::limit($peringatan->alasan, 120) }}
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Prioritas Penanganan</span>
                    <a href="{{ route('dosen.mahasiswa.detail', $m->id) }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-all">
                        <span>Konseling & Detail</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 text-slate-400">
                Semua mahasiswa bimbingan dalam kondisi aman.
            </div>
        @endforelse
    </div>
</div>
@endsection

