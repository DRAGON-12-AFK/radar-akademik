@extends('layouts.app')

@section('title', 'Dashboard Administrator')
@section('page-title', 'Pusat Kontrol Administrator')

@section('content')
<div class="space-y-6">
    <!-- Header Admin -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 rounded-2xl p-6 text-white shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-400/30">
                    Administrator Sistem Akademik
                </span>
                @if($redisAvailable)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Redis Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-500/30 text-slate-300 border border-slate-400/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Cache Database
                    </span>
                @endif
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Ringkasan Sistem Pemantau Studi</h1>
            <p class="text-xs text-slate-300 mt-1">
                Universitas Nurul Jadid (UNUJA) — Terintegrasi API Resmi SSO UNUJA
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('api.test') }}" target="_blank" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-network-wired"></i>
                <span>Uji Koneksi API</span>
            </a>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Mahasiswa</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalMahasiswa }}</h3>
                <span class="text-[11px] text-slate-500">Terdaftar di Sistem</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Dosen Wali</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalDosenWali }}</h3>
                <span class="text-[11px] text-slate-500">Pembimbing Aktif</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Mata Kuliah</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalMataKuliah }}</h3>
                <span class="text-[11px] text-slate-500">Tersinkronisasi</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-book-open"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status Sinkronisasi</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $apiSyncHistories->where('status', 'success')->count() }}</h3>
                <span class="text-[11px] text-slate-500">Sinkron Berhasil</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-cloud-arrow-down"></i>
            </div>
        </div>
    </div>

    <!-- Distribusi Risiko Akademik Seluruh Prodi -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-simple text-blue-600"></i>
            <span>Distribusi Peringatan Dini Akademik Mahasiswa</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-800">Status Hijau (Baik)</span>
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                </div>
                <div class="text-2xl font-black text-emerald-700 mt-2">{{ $risikoHijau }} Mahasiswa</div>
                <p class="text-[11px] text-emerald-600 mt-1">IPK >= 3.00 & kehadiran disiplin</p>
            </div>

            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-800">Status Kuning (Waspada)</span>
                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                </div>
                <div class="text-2xl font-black text-amber-700 mt-2">{{ $risikoKuning }} Mahasiswa</div>
                <p class="text-[11px] text-amber-600 mt-1">Perlu perhatian dosen wali</p>
            </div>

            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-800">Status Merah (Kritis)</span>
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                </div>
                <div class="text-2xl font-black text-rose-700 mt-2">{{ $risikoMerah }} Mahasiswa</div>
                <p class="text-[11px] text-rose-600 mt-1">Tindakan akademik segera</p>
            </div>
        </div>
    </div>

    <!-- Riwayat Sinkronisasi API UNUJA (MySQL Permanent Log) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Riwayat Sinkronisasi API UNUJA</h3>
                <p class="text-xs text-slate-400">Log audit riwayat penarikan data dari API UNUJA yang tersimpan di MySQL</p>
            </div>
            <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg">
                {{ $apiSyncHistories->count() }} Data Terbaru
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Tipe Filter</th>
                        <th class="py-3 px-4">Identifier / NIM</th>
                        <th class="py-3 px-4 text-center">Data Terambil</th>
                        <th class="py-3 px-4 text-center">Waktu Proses</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Catatan / Pesan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($apiSyncHistories as $sync)
                        <tr class="hover:bg-slate-50 transition-all">
                            <td class="py-2.5 px-4 text-slate-700 whitespace-nowrap">{{ $sync->created_at->translatedFormat('d M Y, H:i:s') }}</td>
                            <td class="py-2.5 px-4 font-mono font-semibold text-blue-700">{{ $sync->filter_type }}</td>
                            <td class="py-2.5 px-4 font-mono font-semibold text-slate-800">{{ $sync->identifier ?? '-' }}</td>
                            <td class="py-2.5 px-4 text-center font-semibold text-slate-700">{{ $sync->records_count }} baris</td>
                            <td class="py-2.5 px-4 text-center font-mono text-slate-600">{{ $sync->duration_ms }} ms</td>
                            <td class="py-2.5 px-4 text-center">
                                @if($sync->status === 'success')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check"></i> Sukses
                                    </span>
                                @elseif($sync->status === 'timeout')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock"></i> Timeout
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-circle-xmark"></i> Gagal
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-slate-500 max-w-xs truncate" title="{{ $sync->error_message }}">
                                {{ $sync->error_message ?? 'Sinkronisasi berhasil tanpa kendala.' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">Belum ada riwayat sinkronisasi API.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Login Pengguna (MySQL Permanent Log) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Riwayat Login Pengguna</h3>
                <p class="text-xs text-slate-400">Catatan autentikasi mahasiswa, dosen, dan staf</p>
            </div>
            <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg">
                {{ $loginHistories->count() }} Data Terbaru
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Identitas (NIM/NIDN/Email)</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Alamat IP</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($loginHistories as $log)
                        <tr class="hover:bg-slate-50 transition-all">
                            <td class="py-2.5 px-4 text-slate-700 whitespace-nowrap">{{ $log->created_at->translatedFormat('d M Y, H:i:s') }}</td>
                            <td class="py-2.5 px-4 font-mono font-semibold text-slate-800">{{ $log->identifier }}</td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $log->role === 'admin' ? 'bg-amber-100 text-amber-800' : ($log->role === 'dosen_wali' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ str_replace('_', ' ', $log->role ?? 'mahasiswa') }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 font-mono text-slate-600">{{ $log->ip_address ?? '-' }}</td>
                            <td class="py-2.5 px-4 text-center">
                                @if($log->status === 'success')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check"></i> Sukses
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-circle-xmark"></i> Gagal
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-slate-500 max-w-xs truncate" title="{{ $log->failure_reason }}">
                                {{ $log->failure_reason ?? 'Berhasil masuk sesi.' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Belum ada riwayat aktivitas login.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
