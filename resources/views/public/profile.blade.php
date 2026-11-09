<!DOCTYPE html>
<html lang="id" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Studi & Kompetensi — {{ $mahasiswa->nama_lengkap }} (UNUJA)</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="p-4 sm:p-8 max-w-5xl mx-auto space-y-6">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between no-print">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <i class="fa-solid fa-lock text-[10px]"></i>
                <span>Tautan Publik Terverifikasi (Read-Only)</span>
            </span>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-xs transition-all">
            <i class="fa-solid fa-print"></i>
            <span>Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Official Header Card -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="text-xs font-bold text-blue-700 uppercase tracking-widest mb-1">
                Universitas Nurul Jadid • Fakultas Teknologi Informasi
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900">{{ $mahasiswa->nama_lengkap }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
                NIM: <span class="font-mono font-semibold text-slate-800">{{ $mahasiswa->nim }}</span> • Program Studi: <span class="font-semibold text-slate-800">{{ $mahasiswa->program_studi }}</span> • Angkatan {{ $mahasiswa->angkatan }}
            </p>
        </div>
        <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div class="text-center px-2">
                <span class="text-[10px] uppercase font-semibold text-slate-400 block">IPK Kumulatif</span>
                <span class="text-2xl font-black text-slate-900">{{ number_format($mahasiswa->ipk, 2) }}</span>
            </div>
            <div class="h-10 w-px bg-slate-200"></div>
            <div class="text-center px-2">
                <span class="text-[10px] uppercase font-semibold text-slate-400 block">SKS Ditempuh</span>
                <span class="text-2xl font-black text-blue-600">{{ $mahasiswa->total_sks }}</span>
            </div>
        </div>
    </div>

    <!-- Radar Chart & Kompetensi -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Radar -->
        <div class="md:col-span-6 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex flex-col items-center">
            <h3 class="font-bold text-slate-800 text-sm mb-4 self-start">
                <i class="fa-solid fa-chart-radar text-blue-600 mr-1.5"></i>
                Radar Profil Kompetensi Lulusan (CPL TI UNUJA)
            </h3>
            <div class="w-full h-72 sm:h-80">
                <canvas id="publicRadarChart"></canvas>
            </div>
        </div>

        <!-- Detail Keahlian -->
        <div class="md:col-span-6 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-3">
            <h3 class="font-bold text-slate-800 text-sm mb-2">
                <i class="fa-solid fa-award text-amber-500 mr-1.5"></i>
                Rincian Pencapaian Tiap Bidang Keahlian
            </h3>
            <div class="space-y-2.5">
                @foreach($radarDetails as $item)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-xs text-slate-800 block">{{ $item['nama'] }}</span>
                            <span class="text-[10px] text-slate-400">{{ $item['mk_ditempuh'] }} mata kuliah terselesaikan</span>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-sm text-blue-700">{{ $item['skor'] }} / 100</span>
                            <span class="block text-[10px] font-semibold text-emerald-600">{{ $item['status'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Riwayat Mata Kuliah -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 text-sm">Riwayat Mata Kuliah yang Telah Ditempuh</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Mata Kuliah</th>
                        <th class="py-3 px-4 text-center">Semester</th>
                        <th class="py-3 px-4 text-center">SKS</th>
                        <th class="py-3 px-4 text-center">Nilai Huruf</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($nilaiList as $n)
                        <tr>
                            <td class="py-2.5 px-4 font-mono font-semibold">{{ $n->mataKuliah->kode_mk ?? '-' }}</td>
                            <td class="py-2.5 px-4 font-medium text-slate-800">{{ $n->mataKuliah->nama_mk ?? '-' }}</td>
                            <td class="py-2.5 px-4 text-center text-slate-500">{{ $n->semester->nama ?? '-' }}</td>
                            <td class="py-2.5 px-4 text-center font-semibold">{{ $n->mataKuliah->sks ?? 0 }}</td>
                            <td class="py-2.5 px-4 text-center font-bold text-slate-800">{{ $n->nilai_huruf }}</td>
                            <td class="py-2.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $n->status === 'lulus' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ ucfirst($n->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="text-center text-xs text-slate-400 pt-4 pb-8">
        Halaman profil ini diterbitkan resmi oleh Sistem Pemantau Studi Mahasiswa UNUJA. &bull; Terverifikasi digital.
    </div>

    <script>
        const pRadarCtx = document.getElementById('publicRadarChart').getContext('2d');
        new Chart(pRadarCtx, {
            type: 'radar',
            data: {
                labels: @json($radarLabels),
                datasets: [{
                    label: 'Skor Kompetensi',
                    data: @json($radarScores),
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
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: { stepSize: 20 }
                    }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>

