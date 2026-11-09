@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')
@section('page-title', 'Dashboard Akademik Mahasiswa')

@section('content')
<div class="space-y-6">
    <!-- Header Mahasiswa Banner & Status Sinkronisasi API UNUJA -->
    <div class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none text-white text-9xl">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/30 text-blue-200 border border-blue-400/30">
                        {{ $mahasiswa->program_studi }} • Angkatan {{ $mahasiswa->angkatan }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">
                        <i class="fa-solid fa-circle-check text-[10px]"></i> Terintegrasi API UNUJA
                    </span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $mahasiswa->nama_lengkap }}</h1>
                <p class="text-xs text-blue-200 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span>NIM: <strong class="font-mono">{{ $mahasiswa->nim }}</strong></span>
                    <span>•</span>
                    <span>Semester: <strong>{{ $mahasiswa->semester_berjalan }}</strong></span>
                    <span>•</span>
                    <span>Fakultas: <strong>{{ $mahasiswa->fakultas }}</strong></span>
                    <span>•</span>
                    <span>Dosen Wali: <strong>{{ $mahasiswa->dosenWali->nama_lengkap ?? '-' }}</strong></span>
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] text-blue-200/80">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Terakhir disinkronkan: <strong>{{ $lastSync ? $lastSync->created_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum disinkronkan' }}</strong></span>
                    @if($lastSync)
                        <span class="px-2 py-0.5 rounded bg-blue-950/60 font-mono text-[10px] text-blue-300 border border-blue-700/50">
                            {{ $lastSync->duration_ms }} ms
                        </span>
                    @endif
                </div>
            </div>

            <!-- Actions: Sinkronkan Ulang & Bagikan Profil -->
            <div class="flex flex-col sm:flex-row lg:flex-col gap-2 shrink-0">
                <!-- Tombol Sinkronkan Ulang dari API UNUJA -->
                <form action="{{ route('mahasiswa.sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-700/30 flex items-center justify-center gap-2 transition-all">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Sinkronkan Ulang dari UNUJA</span>
                    </button>
                </form>

                <!-- Bagikan Profil Publik -->
                <div class="bg-white/10 backdrop-blur-md rounded-xl p-2.5 border border-white/20">
                    <div class="text-[11px] font-medium text-blue-200 mb-1">Tautan Profil Publik (Read-Only)</div>
                    @if($mahasiswa->share_token_active && $mahasiswa->share_token)
                        <div class="flex items-center gap-1.5">
                            <input type="text" readonly value="{{ route('profil.publik', ['token' => $mahasiswa->share_token]) }}" id="shareUrlInput" class="text-xs bg-slate-900/60 text-blue-200 px-2 py-1.5 rounded-lg border border-white/20 w-44 focus:outline-none">
                            <button onclick="copyShareUrl()" title="Salin Tautan" class="px-2 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                            <form action="{{ route('mahasiswa.share.toggle') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="revoke">
                                <button type="submit" title="Cabut Tautan" class="px-2 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-semibold">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('mahasiswa.share.toggle') }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="generate">
                            <button type="submit" class="w-full px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-share-nodes"></i>
                                <span>Buat Tautan Berbagi</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Cards: IPK, Total SKS, Mata Kuliah, Status Risiko -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- IPK Kumulatif (Resmi API UNUJA) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">IPK Kumulatif</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($mahasiswa->ipk, 2) }}</h3>
                <span class="text-[11px] font-medium text-emerald-600">Nilai Resmi API UNUJA</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-award"></i>
            </div>
        </div>

        <!-- Total SKS Tempuh (Resmi API UNUJA) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total SKS Tempuh</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $mahasiswa->total_sks }} <span class="text-sm font-semibold text-slate-500">SKS</span></h3>
                <span class="text-[11px] font-medium text-slate-500">Standar kelulusan: 144 SKS</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-book-bookmark"></i>
            </div>
        </div>

        <!-- Jumlah Mata Kuliah Terdata -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Mata Kuliah</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $nilaiList->count() }} <span class="text-sm font-semibold text-slate-500">MK</span></h3>
                <span class="text-[11px] font-medium text-blue-600">{{ count($nilaiPerSemester) }} Semester Terdata</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>

        <!-- Status Risiko Studi -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status Risiko Studi</p>
                <div class="mt-1">
                    @if($mahasiswa->status_risiko === 'hijau')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <i class="fa-solid fa-circle-check"></i> Hijau (Aman)
                        </span>
                    @elseif($mahasiswa->status_risiko === 'kuning')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            <i class="fa-solid fa-triangle-exclamation"></i> Kuning (Waspada)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                            <i class="fa-solid fa-circle-exclamation"></i> Merah (Kritis)
                        </span>
                    @endif
                </div>
                <a href="{{ route('mahasiswa.peringatan') }}" class="text-[11px] text-blue-600 hover:underline font-medium inline-block mt-1">
                    Lihat Evaluasi Lengkap &rarr;
                </a>
            </div>
            <div class="w-12 h-12 rounded-xl {{ $mahasiswa->status_risiko === 'hijau' ? 'bg-emerald-50 text-emerald-600' : ($mahasiswa->status_risiko === 'kuning' ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }} flex items-center justify-center text-xl">
                <i class="fa-solid fa-shield-heart"></i>
            </div>
        </div>
    </div>

    <!-- Grafik Perkembangan IPS & Peringatan Dini -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Grafik Line IPS Tiap Semester -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Grafik Perkembangan IPS (Indeks Prestasi Semester)</h3>
                    <p class="text-xs text-slate-400">Tren pencapaian indeks prestasi per semester hasil studi resmi UNUJA</p>
                </div>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold border border-blue-200">
                    {{ count($ipsHistory) }} Semester Terekam
                </span>
            </div>
            <div class="h-64 sm:h-72">
                <canvas id="ipsChart"></canvas>
            </div>
        </div>

        <!-- Ringkasan Peringatan Dini & Rekomendasi -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800 text-base">Peringatan Dini Akademik</h3>
                    <i class="fa-solid fa-bell text-slate-400"></i>
                </div>
                @if($peringatan)
                    <div class="p-3.5 rounded-xl text-xs {{ $peringatan->status_risiko === 'hijau' ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : ($peringatan->status_risiko === 'kuning' ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-rose-50 text-rose-900 border border-rose-200') }} mb-4">
                        <div class="font-bold mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Evaluasi Terkini:</span>
                        </div>
                        <p class="leading-relaxed">{{ $peringatan->alasan }}</p>
                    </div>

                    <div class="space-y-2">
                        <span class="text-xs font-bold text-slate-700 block">Rekomendasi Tindakan:</span>
                        <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-200">
                            {{ $peringatan->rekomendasi }}
                        </p>
                    </div>
                @else
                    <p class="text-xs text-slate-500">Belum ada catatan evaluasi peringatan dini.</p>
                @endif
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex gap-2">
                <a href="{{ route('mahasiswa.radar') }}" class="flex-1 text-center py-2 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-semibold border border-blue-200 transition-all">
                    Radar Keahlian
                </a>
                <a href="{{ route('mahasiswa.simulasi') }}" class="flex-1 text-center py-2 px-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-all">
                    Simulasi IPK
                </a>
            </div>
        </div>
    </div>

    <!-- Integrasi Data Akademik: Mata Kuliah, Nilai, dan Transkrip -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- Tab Navigation Header -->
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Data Akademik & Transkrip Nilai</h3>
                <p class="text-xs text-slate-400">Data mata kuliah dan nilai resmi disinkronkan langsung dari API UNUJA</p>
            </div>
            <!-- Switcher Tabs: Berdasarkan Semester vs Transkrip Lengkap -->
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl self-start sm:self-auto">
                <button type="button" id="tabPerSemesterBtn" onclick="switchAcademicTab('per_semester')" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-white text-blue-700 shadow-xs transition-all">
                    <i class="fa-solid fa-layer-group mr-1.5"></i> Berdasarkan Semester
                </button>
                <button type="button" id="tabTranskripBtn" onclick="switchAcademicTab('transkrip')" class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition-all">
                    <i class="fa-solid fa-list-check mr-1.5"></i> Seluruh Transkrip ({{ $nilaiList->count() }} MK)
                </button>
            </div>
        </div>

        <!-- TAMPILAN 1: BERDASARKAN SEMESTER -->
        <div id="viewPerSemester" class="p-5 space-y-6">
            @forelse($nilaiPerSemester as $namaSemester => $daftarNilai)
                @php
                    $summary = $semesterSummaries[$namaSemester] ?? ['total_sks' => $daftarNilai->sum('mataKuliah.sks'), 'ips' => 0.00, 'jumlah_mk' => $daftarNilai->count()];
                @endphp
                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                    <!-- Semester Header Bar -->
                    <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <h4 class="font-bold text-slate-800 text-sm">{{ $namaSemester }}</h4>
                            <span class="text-xs text-slate-500">({{ $daftarNilai->count() }} Mata Kuliah)</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="bg-white px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700 font-medium">
                                Total: <strong>{{ $summary['total_sks'] }} SKS</strong>
                            </span>
                            <span class="bg-blue-50 text-blue-800 border border-blue-200 px-2.5 py-1 rounded-lg font-bold">
                                IPS: {{ number_format($summary['ips'], 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Semester Course Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-white text-slate-400 font-semibold uppercase text-[10px] border-b border-slate-100">
                                <tr>
                                    <th class="py-2.5 px-4">Kode MK</th>
                                    <th class="py-2.5 px-4">Nama Mata Kuliah</th>
                                    <th class="py-2.5 px-4 text-center">SKS</th>
                                    <th class="py-2.5 px-4 text-center">Nilai Huruf</th>
                                    <th class="py-2.5 px-4 text-center">Bobot / Indeks</th>
                                    <th class="py-2.5 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($daftarNilai as $n)
                                    <tr class="hover:bg-slate-50/70 transition-all">
                                        <td class="py-2.5 px-4 font-mono font-semibold text-slate-700">{{ $n->mataKuliah->kode_mk ?? '-' }}</td>
                                        <td class="py-2.5 px-4 font-medium text-slate-800">{{ $n->mataKuliah->nama_mk ?? '-' }}</td>
                                        <td class="py-2.5 px-4 text-center font-semibold text-slate-700">{{ $n->mataKuliah->sks ?? 0 }}</td>
                                        <td class="py-2.5 px-4 text-center">
                                            <span class="font-bold px-2 py-0.5 rounded text-xs {{ in_array($n->nilai_huruf, ['A', 'A-', 'B+', 'B']) ? 'bg-emerald-50 text-emerald-700' : (in_array($n->nilai_huruf, ['B-', 'C+', 'C']) ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                                {{ $n->nilai_huruf }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-center font-mono font-semibold text-slate-700">{{ number_format($n->bobot, 2) }}</td>
                                        <td class="py-2.5 px-4 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $n->status === 'lulus' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                {{ ucfirst($n->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300"></i>
                    <p>Belum ada data mata kuliah yang tersinkron.</p>
                </div>
            @endforelse
        </div>

        <!-- TAMPILAN 2: SELURUH TRANSKRIP NILAI -->
        <div id="viewTranskrip" class="hidden">
            <!-- Transkrip Summary Banner -->
            <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                <span class="font-semibold text-slate-700">
                    Transkrip Akademik Lengkap • Total: <strong>{{ $nilaiList->count() }} Mata Kuliah</strong>
                </span>
                <div class="flex items-center gap-3">
                    <span class="bg-white px-3 py-1 rounded-lg border border-slate-200 text-slate-700">
                        Total SKS Kumulatif: <strong class="text-blue-700">{{ $mahasiswa->total_sks }} SKS</strong>
                    </span>
                    <span class="bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1 rounded-lg font-bold">
                        IPK Kumulatif: {{ number_format($mahasiswa->ipk, 2) }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">No</th>
                            <th class="py-3 px-4">Kode MK</th>
                            <th class="py-3 px-4">Nama Mata Kuliah</th>
                            <th class="py-3 px-4 text-center">Semester</th>
                            <th class="py-3 px-4 text-center">SKS</th>
                            <th class="py-3 px-4 text-center">Nilai Huruf</th>
                            <th class="py-3 px-4 text-center">Bobot</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($nilaiList as $index => $nilai)
                            <tr class="hover:bg-slate-50/80 transition-all">
                                <td class="py-2.5 px-4 text-slate-400 text-center">{{ $index + 1 }}</td>
                                <td class="py-2.5 px-4 font-mono font-semibold text-slate-700">{{ $nilai->mataKuliah->kode_mk ?? '-' }}</td>
                                <td class="py-2.5 px-4 font-medium text-slate-800">{{ $nilai->mataKuliah->nama_mk ?? '-' }}</td>
                                <td class="py-2.5 px-4 text-center text-slate-500">{{ $nilai->semester->nama ?? '-' }}</td>
                                <td class="py-2.5 px-4 text-center font-semibold text-slate-700">{{ $nilai->mataKuliah->sks ?? 0 }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="font-bold px-2 py-0.5 rounded text-xs {{ in_array($nilai->nilai_huruf, ['A', 'A-', 'B+', 'B']) ? 'bg-emerald-50 text-emerald-700' : (in_array($nilai->nilai_huruf, ['B-', 'C+', 'C']) ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                        {{ $nilai->nilai_huruf }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono font-semibold text-slate-700">{{ number_format($nilai->bobot, 2) }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $nilai->status === 'lulus' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ ucfirst($nilai->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data nilai tersimpan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Aktivitas Login Mahasiswa -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Riwayat Aktivitas Masuk (Login)</h3>
                <p class="text-xs text-slate-400">Catatan riwayat sesi login Anda yang tersimpan aman di database</p>
            </div>
            <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg">
                5 Sesi Terakhir
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Identitas (NIM)</th>
                        <th class="py-3 px-4">Alamat IP</th>
                        <th class="py-3 px-4">Perangkat / Browser</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($loginHistories as $lh)
                        <tr class="hover:bg-slate-50/80 transition-all">
                            <td class="py-3 px-4 text-slate-700">{{ $lh->created_at->translatedFormat('d M Y, H:i:s') }}</td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800">{{ $lh->identifier }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $lh->ip_address ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-500 max-w-xs truncate" title="{{ $lh->user_agent }}">
                                {{ Str::limit($lh->user_agent ?? '-', 50) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($lh->status === 'success')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check"></i> Berhasil
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-circle-xmark"></i> Gagal
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat aktivitas login tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyShareUrl() {
        const input = document.getElementById('shareUrlInput');
        if (input) {
            input.select();
            navigator.clipboard.writeText(input.value);
            alert('Tautan berbagi profil berhasil disalin!');
        }
    }

    function switchAcademicTab(tab) {
        const perSemester = document.getElementById('viewPerSemester');
        const transkrip = document.getElementById('viewTranskrip');
        const btnPerSemester = document.getElementById('tabPerSemesterBtn');
        const btnTranskrip = document.getElementById('tabTranskripBtn');

        if (tab === 'per_semester') {
            perSemester.classList.remove('hidden');
            transkrip.classList.add('hidden');
            btnPerSemester.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold bg-white text-blue-700 shadow-xs transition-all';
            btnTranskrip.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition-all';
        } else {
            perSemester.classList.add('hidden');
            transkrip.classList.remove('hidden');
            btnTranskrip.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold bg-white text-blue-700 shadow-xs transition-all';
            btnPerSemester.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition-all';
        }
    }

    // Chart.js Line Chart for IPS History
    const ctx = document.getElementById('ipsChart').getContext('2d');
    const ipsLabels = @json($semesterLabels);
    const ipsData = @json($ipsHistory);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ipsLabels.length ? ipsLabels : ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
            datasets: [{
                label: 'IPS (Indeks Prestasi Semester)',
                data: ipsData.length ? ipsData : [0, 0, 0, 0],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#1d4ed8',
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    min: 0,
                    max: 4.0,
                    ticks: { stepSize: 0.5 }
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
