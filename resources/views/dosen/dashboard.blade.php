@extends('layouts.app')

@section('title', 'Dashboard Dosen Wali')
@section('page-title', 'Dashboard Pembimbingan Akademik')

@section('content')
<div class="space-y-6">
    <!-- Header Dosen Wali -->
    <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-blue-900 rounded-2xl p-6 text-white shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-purple-200 border border-white/20">
                    Dosen Pembimbing Akademik
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                    <i class="fa-solid fa-circle-check text-[10px]"></i> Kewenangan API Aktif
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $dosen->nama_lengkap }}</h1>
            <p class="text-xs text-purple-200 mt-1">
                NIDN: <span class="font-mono font-semibold">{{ $dosen->nidn }}</span> • Program Studi: {{ $dosen->prodi ?? 'Fakultas Teknik' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dosen.prioritas') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/30 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Cek Prioritas Risiko</span>
            </a>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Mahasiswa Bimbingan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Bimbingan</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalBimbingan }}</h3>
                <span class="text-[11px] text-slate-500">Mahasiswa Aktif</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Risiko Merah -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Risiko Merah</p>
                <h3 class="text-2xl font-black text-rose-600 mt-1">{{ $risikoMerah }}</h3>
                <span class="text-[11px] text-rose-600 font-semibold">Tindakan Segera</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
        </div>

        <!-- Risiko Kuning -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Risiko Kuning</p>
                <h3 class="text-2xl font-black text-amber-600 mt-1">{{ $risikoKuning }}</h3>
                <span class="text-[11px] text-amber-600 font-semibold">Perlu Perhatian</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <!-- Cakupan API UNUJA -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kewenangan API</p>
                <h3 class="text-2xl font-black text-indigo-600 mt-1">{{ count($apiMahasiswas) }}</h3>
                <span class="text-[11px] text-slate-500">Mahasiswa Scope API</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-network-wired"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Mahasiswa: Bimbingan & Kewenangan API UNUJA -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Data Mahasiswa Terintegrasi API UNUJA</h3>
                <p class="text-xs text-slate-400">Pantau perkembangan akademik mahasiswa sesuai kewenangan API dan bimbingan Anda</p>
            </div>
            <!-- Switcher Tabs -->
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl self-start sm:self-auto">
                <button type="button" id="tabBimbinganBtn" onclick="switchDosenTab('bimbingan')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-purple-700 shadow-xs transition-all">
                    <i class="fa-solid fa-user-check mr-1.5"></i> Mahasiswa Bimbingan ({{ $totalBimbingan }})
                </button>
                <button type="button" id="tabScopeBtn" onclick="switchDosenTab('scope')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition-all">
                    <i class="fa-solid fa-globe mr-1.5"></i> Kewenangan API UNUJA ({{ count($apiMahasiswas) }})
                </button>
            </div>
        </div>

        <!-- TAB 1: MAHASISWA BIMBINGAN LOKAL -->
        <div id="viewBimbingan" class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">NIM</th>
                        <th class="py-3 px-4">Nama Mahasiswa</th>
                        <th class="py-3 px-4 text-center">Angkatan / Smt</th>
                        <th class="py-3 px-4 text-center">IPK</th>
                        <th class="py-3 px-4 text-center">SKS Tempuh</th>
                        <th class="py-3 px-4 text-center">Kehadiran</th>
                        <th class="py-3 px-4 text-center">Status Risiko</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mahasiswas as $m)
                        <tr class="hover:bg-slate-50/80 transition-all">
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">{{ $m->nim }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800 block">{{ $m->nama_lengkap }}</span>
                                <span class="text-[11px] text-slate-400">{{ $m->program_studi }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center text-slate-600">
                                {{ $m->angkatan }} / <span class="font-semibold">Smt {{ $m->semester_berjalan }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ number_format($m->ipk, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-700">
                                {{ $m->total_sks }} SKS
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-semibold {{ $m->persentase_kehadiran_total >= 75 ? 'text-emerald-600' : 'text-rose-600 font-bold' }}">
                                    {{ number_format($m->persentase_kehadiran_total, 1) }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($m->status_risiko === 'merah')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        <i class="fa-solid fa-circle-exclamation"></i> Merah
                                    </span>
                                @elseif($m->status_risiko === 'kuning')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Kuning
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-circle-check"></i> Hijau
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('dosen.mahasiswa.detail', $m->id) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition-all shadow-xs">
                                    <i class="fa-regular fa-folder-open"></i>
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada mahasiswa bimbingan yang terdaftar pada akun Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TAB 2: MAHASISWA SESUAI KEWENANGAN API UNUJA -->
        <div id="viewScope" class="hidden overflow-x-auto">
            <div class="bg-indigo-50/50 p-3 px-5 border-b border-indigo-100 flex items-center justify-between text-xs text-indigo-900">
                <span>Daftar mahasiswa yang diizinkan untuk diakses melalui Developer API Token UNUJA.</span>
                <span class="font-semibold">{{ count($apiMahasiswas) }} Mahasiswa Terdata</span>
            </div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">NIM</th>
                        <th class="py-3 px-4">Nama Mahasiswa</th>
                        <th class="py-3 px-4">Prodi & Fakultas</th>
                        <th class="py-3 px-4">Dosen Wali (API)</th>
                        <th class="py-3 px-4 text-center">IPK (API)</th>
                        <th class="py-3 px-4 text-center">SKS (API)</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($apiMahasiswas as $item)
                        <tr class="hover:bg-slate-50 transition-all">
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">{{ $item['nim'] ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $item['nama'] ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="block font-medium text-slate-700">{{ $item['prodi'] ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $item['fakultas'] ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <span>{{ $item['wali'] ?? '-' }}</span>
                                @if(!empty($item['nidn']))
                                    <span class="block text-[10px] font-mono text-slate-400">NIDN: {{ $item['nidn'] }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ !empty($item['ipk']) ? number_format($item['ipk'], 2) : '0.00' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-700">
                                {{ $item['sks_ketuntasan'] ?? 0 }} SKS
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('dosen.scope.view', $item['nim']) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition-all shadow-xs">
                                    <i class="fa-solid fa-arrows-rotate"></i>
                                    <span>Buka / Sinkronkan</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data mahasiswa pada kewenangan API UNUJA saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchDosenTab(tab) {
        const viewBimbingan = document.getElementById('viewBimbingan');
        const viewScope = document.getElementById('viewScope');
        const btnBimbingan = document.getElementById('tabBimbinganBtn');
        const btnScope = document.getElementById('tabScopeBtn');

        if (tab === 'bimbingan') {
            viewBimbingan.classList.remove('hidden');
            viewScope.classList.add('hidden');
            btnBimbingan.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-purple-700 shadow-xs transition-all';
            btnScope.className = 'px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition-all';
        } else {
            viewBimbingan.classList.add('hidden');
            viewScope.classList.remove('hidden');
            btnScope.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-purple-700 shadow-xs transition-all';
            btnBimbingan.className = 'px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition-all';
        }
    }
</script>
@endpush
@endsection
