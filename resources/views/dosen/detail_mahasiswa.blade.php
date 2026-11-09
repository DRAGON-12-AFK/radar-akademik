@extends('layouts.app')

@section('title', 'Detail Mahasiswa - ' . $mahasiswa->nama_lengkap)
@section('page-title', 'Detail Akademik Mahasiswa Bimbingan')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('dosen.dashboard') }}" class="text-xs text-blue-600 hover:underline flex items-center gap-1 font-semibold">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Bimbingan
                </a>
                <span class="text-slate-300">•</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $mahasiswa->status_risiko === 'merah' ? 'bg-rose-100 text-rose-800' : ($mahasiswa->status_risiko === 'kuning' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                    Status: {{ $mahasiswa->status_risiko }}
                </span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $mahasiswa->nama_lengkap }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                NIM: <span class="font-mono font-semibold">{{ $mahasiswa->nim }}</span> • Prodi: {{ $mahasiswa->program_studi }} • Angkatan {{ $mahasiswa->angkatan }} • Semester {{ $mahasiswa->semester_berjalan }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('dosen.mahasiswa.sync', $mahasiswa->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Sinkronkan Ulang dari UNUJA</span>
                </button>
            </form>
            <div class="bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 text-right">
                <span class="text-[10px] text-slate-400 block uppercase font-semibold">IPK Kumulatif</span>
                <span class="text-xl font-black text-slate-800">{{ number_format($mahasiswa->ipk, 2) }}</span>
            </div>
            <div class="bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 text-right">
                <span class="text-[10px] text-slate-400 block uppercase font-semibold">Total SKS</span>
                <span class="text-xl font-black text-slate-800">{{ $mahasiswa->total_sks }}</span>
            </div>
        </div>
    </div>

    <!-- Peringatan & Radar Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Diagnosis Peringatan Akademik -->
        <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                    <span>Diagnosis Peringatan Dini Akademik</span>
                </h3>

                @if($peringatan)
                    <div class="p-4 rounded-xl text-xs {{ $peringatan->status_risiko === 'hijau' ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : ($peringatan->status_risiko === 'kuning' ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-rose-50 text-rose-900 border border-rose-200') }} mb-3">
                        <span class="font-bold block mb-1">Alasan Penilaian:</span>
                        <p class="leading-relaxed">{{ $peringatan->alasan }}</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                        <span class="font-bold text-slate-700 block mb-1">Rekomendasi Terjadwal:</span>
                        <p class="text-slate-600 leading-relaxed">{{ $peringatan->rekomendasi }}</p>
                    </div>
                @else
                    <p class="text-xs text-slate-400">Belum ada evaluasi peringatan dini tersimpan.</p>
                @endif
            </div>

            <!-- Tombol Konseling Cepat -->
            <a href="#formKonseling" class="w-full text-center py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-xs transition-all">
                <i class="fa-solid fa-pen-to-square mr-1.5"></i>
                <span>Tulis Catatan / Rekomendasi Bimbingan</span>
            </a>
        </div>

        <!-- Radar Kompetensi Mahasiswa -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex flex-col items-center justify-center">
            <h3 class="font-bold text-slate-800 text-sm mb-2 self-start flex items-center gap-2">
                <i class="fa-solid fa-chart-radar text-blue-600"></i>
                <span>Radar Pemetaan Kompetensi Mahasiswa</span>
            </h3>
            <div class="w-full h-72 sm:h-80">
                <canvas id="dosenRadarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Riwayat Nilai & Mata Kuliah -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 text-base">Riwayat Hasil Studi Mahasiswa</h3>
            <p class="text-xs text-slate-400">Daftar nilai seluruh mata kuliah yang telah ditempuh</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Kode MK</th>
                        <th class="py-3 px-4">Nama Mata Kuliah</th>
                        <th class="py-3 px-4 text-center">Semester</th>
                        <th class="py-3 px-4 text-center">SKS</th>
                        <th class="py-3 px-4 text-center">Nilai Angka</th>
                        <th class="py-3 px-4 text-center">Huruf</th>
                        <th class="py-3 px-4 text-center">Bobot</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($nilaiList as $nilai)
                        <tr class="hover:bg-slate-50 transition-all">
                            <td class="py-3 px-4 font-mono font-semibold">{{ $nilai->mataKuliah->kode_mk ?? '-' }}</td>
                            <td class="py-3 px-4 font-medium text-slate-800">{{ $nilai->mataKuliah->nama_mk ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">{{ $nilai->semester->nama ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">{{ $nilai->mataKuliah->sks ?? 0 }}</td>
                            <td class="py-3 px-4 text-center font-semibold">{{ number_format($nilai->nilai_angka, 1) }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-bold px-2 py-0.5 rounded text-xs {{ in_array($nilai->nilai_huruf, ['A', 'A-', 'B+', 'B']) ? 'bg-emerald-50 text-emerald-700' : (in_array($nilai->nilai_huruf, ['B-', 'C+', 'C']) ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                    {{ $nilai->nilai_huruf }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono">{{ number_format($nilai->bobot, 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $nilai->status === 'lulus' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ ucfirst($nilai->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">Belum ada riwayat nilai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form Catatan Pembimbingan & Riwayat Catatan -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" id="formKonseling">
        <!-- Form Tambah Catatan -->
        <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-pen-nib text-blue-600"></i>
                <span>Tulis Catatan / Rekomendasi Pembimbingan</span>
            </h3>

            <form action="{{ route('dosen.mahasiswa.catatan.store', $mahasiswa->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pembimbingan / Hasil Konseling</label>
                    <textarea name="catatan" rows="3" required placeholder="Contoh: Mahasiswa diarahkan untuk memperbaiki kehadiran pada mata kuliah PBO..." class="w-full text-xs p-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rekomendasi Tindak Lanjut (Opsional)</label>
                    <textarea name="rekomendasi" rows="2" placeholder="Contoh: Mengulang mata kuliah di semester pendek, asistensi lab..." class="w-full text-xs p-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Penanganan</label>
                    <select name="status" class="w-full text-xs p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="menunggu">Menunggu Tindak Lanjut</option>
                        <option value="ditindaklanjuti">Sedang Ditindaklanjuti</option>
                        <option value="selesai">Selesai / Teratasi</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition-all shadow-xs">
                    Simpan Catatan Pembimbingan
                </button>
            </form>
        </div>

        <!-- Riwayat Catatan Konseling -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                <span>Riwayat Catatan & Rekomendasi Sebelumnya</span>
            </h3>

            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                @forelse($catatanList as $c)
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-800">{{ $c->tanggal->translatedFormat('d F Y') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $c->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($c->status === 'ditindaklanjuti' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ ucfirst(str_replace('_', ' ', $c->status)) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-700 leading-relaxed">{{ $c->catatan }}</p>
                        @if($c->rekomendasi)
                            <div class="text-[11px] text-blue-700 bg-blue-50/80 p-2.5 rounded-lg border border-blue-100">
                                <span class="font-semibold">Rekomendasi: </span>{{ $c->rekomendasi }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center">Belum ada riwayat catatan pembimbingan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const dRadarCtx = document.getElementById('dosenRadarChart').getContext('2d');
    const dLabels = @json($radarLabels);
    const dScores = @json($radarScores);

    new Chart(dRadarCtx, {
        type: 'radar',
        data: {
            labels: dLabels,
            datasets: [{
                label: 'Capaian Kompetensi',
                data: dScores,
                backgroundColor: 'rgba(37, 99, 235, 0.25)',
                borderColor: '#2563eb',
                borderWidth: 2,
                pointBackgroundColor: '#1d4ed8',
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

