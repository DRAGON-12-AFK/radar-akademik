<?php

namespace App\Services;

use App\Models\Keahlian;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\PeringatanAkademik;
use App\Models\Semester;

class AcademicMonitoringService
{
    /**
     * Menghitung IPS (Indeks Prestasi Semester) mahasiswa untuk semester tertentu.
     */
    public function hitungIps(Mahasiswa $mahasiswa, int $semesterId): array
    {
        $nilaiList = Nilai::with('mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterId)
            ->get();

        $totalSks = 0;
        $totalMutu = 0.0;

        foreach ($nilaiList as $nilai) {
            $sks = $nilai->mataKuliah->sks ?? 0;
            $bobot = (float) $nilai->bobot;
            $totalSks += $sks;
            $totalMutu += ($sks * $bobot);
        }

        $ips = $totalSks > 0 ? round($totalMutu / $totalSks, 2) : 0.00;

        return [
            'total_sks' => $totalSks,
            'total_mutu' => $totalMutu,
            'ips' => $ips,
            'jumlah_mk' => $nilaiList->count(),
        ];
    }

    /**
     * Menghitung dan memperbarui IPK kumulatif serta total SKS mahasiswa.
     */
    public function updateIpkDanTotalSks(Mahasiswa $mahasiswa): Mahasiswa
    {
        $nilaiList = Nilai::with('mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->get();

        $totalSks = 0;
        $totalMutu = 0.0;

        foreach ($nilaiList as $nilai) {
            $sks = $nilai->mataKuliah->sks ?? 0;
            $bobot = (float) $nilai->bobot;
            $totalSks += $sks;
            $totalMutu += ($sks * $bobot);
        }

        // Jangan menimpa IPK dan Total SKS resmi jika sudah terisi dari API UNUJA
        if ((float) $mahasiswa->ipk <= 0 && $totalSks > 0) {
            $mahasiswa->ipk = round($totalMutu / $totalSks, 2);
        }

        if ((int) $mahasiswa->total_sks <= 0 && $totalSks > 0) {
            $mahasiswa->total_sks = $totalSks;
        }

        // Hitung rata-rata kehadiran
        $kehadiranList = $mahasiswa->kehadiran;
        $persentaseKehadiran = 0.00;
        if ($kehadiranList && $kehadiranList->count() > 0) {
            $persentaseKehadiran = round($kehadiranList->avg('persentase'), 2);
        }

        $mahasiswa->persentase_kehadiran_total = $persentaseKehadiran;
        $mahasiswa->save();

        return $mahasiswa;
    }

    /**
     * Sistem Peringatan Dini Akademik (Early Warning System).
     * Berbasis aturan transparan tanpa AI / Machine Learning.
     * Kategori: Hijau, Kuning, Merah.
     */
    public function evaluasiPeringatanDini(Mahasiswa $mahasiswa, ?int $semesterId = null): PeringatanAkademik
    {
        $this->updateIpkDanTotalSks($mahasiswa);

        $ipk = (float) $mahasiswa->ipk;
        $kehadiran = (float) $mahasiswa->persentase_kehadiran_total;

        $nilaiQuery = Nilai::with('mataKuliah')->where('mahasiswa_id', $mahasiswa->id);
        if ($semesterId) {
            $nilaiQuery->where('semester_id', $semesterId);
        }
        $nilaiList = $nilaiQuery->get();

        $jumlahNilaiE = $nilaiList->where('nilai_huruf', 'E')->count();
        $jumlahNilaiD = $nilaiList->where('nilai_huruf', 'D')->count();

        $skorRisiko = 0;
        $alasanList = [];
        $rekomendasiList = [];

        // 1. Evaluasi IPK
        if ($ipk < 2.00) {
            $skorRisiko += 40;
            $alasanList[] = "IPK berada di bawah batas minimum kelulusan ({$ipk} < 2.00).";
            $rekomendasiList[] = "Jadwalkan konseling akademik intensif dengan Dosen Wali untuk perencanaan program remedial.";
        } elseif ($ipk < 2.50) {
            $skorRisiko += 25;
            $alasanList[] = "IPK berada dalam rentang rawan ({$ipk} < 2.50).";
            $rekomendasiList[] = "Fokus pada perbaikan mata kuliah berbobot rendah di semester berikutnya.";
        } elseif ($ipk < 3.00) {
            $skorRisiko += 10;
            $alasanList[] = "IPK berada dalam batas standar ({$ipk}), namun perlu ditingkatkan untuk mencapai predikat sangat memuaskan.";
            $rekomendasiList[] = "Tingkatkan intensitas belajar pada mata kuliah berbobot SKS tinggi.";
        } else {
            $alasanList[] = "Pencapaian IPK sangat baik ({$ipk} >= 3.00).";
        }

        // 2. Evaluasi Kehadiran
        if ($kehadiran > 0 && $kehadiran < 70.00) {
            $skorRisiko += 35;
            $alasanList[] = "Rata-rata persentase kehadiran sangat rendah ({$kehadiran}%), terancam tidak memenuhi syarat ujian.";
            $rekomendasiList[] = "Segera hubungi dosen pengampu untuk konfirmasi presensi dan tugas pengganti.";
        } elseif ($kehadiran > 0 && $kehadiran < 80.00) {
            $skorRisiko += 15;
            $alasanList[] = "Persentase kehadiran mendekati batas toleransi minimal ({$kehadiran}%).";
            $rekomendasiList[] = "Hindari ketidakhadiran tanpa keterangan resmi pada perkuliahan mendatang.";
        } elseif ($kehadiran >= 80.00) {
            $alasanList[] = "Tingkat kehadiran disiplin dan memenuhi syarat ({$kehadiran}%).";
        }

        // 3. Evaluasi Nilai E dan D
        if ($jumlahNilaiE > 0) {
            $skorRisiko += ($jumlahNilaiE * 25);
            $alasanList[] = "Terdapat {$jumlahNilaiE} mata kuliah dengan nilai E yang wajib diulang.";
            $rekomendasiList[] = "Daftarkan ulang mata kuliah bernilai E pada semester berikutnya atau semester pendek.";
        }

        if ($jumlahNilaiD > 2) {
            $skorRisiko += 20;
            $alasanList[] = "Terdapat {$jumlahNilaiD} mata kuliah dengan nilai D (melebihi batas toleransi).";
            $rekomendasiList[] = "Prioritaskan mengulang mata kuliah inti prodi yang bernilai D.";
        } elseif ($jumlahNilaiD > 0) {
            $skorRisiko += 10;
            $alasanList[] = "Terdapat {$jumlahNilaiD} mata kuliah bernilai D.";
            $rekomendasiList[] = "Pertimbangkan untuk perbaikan nilai jika ingin menaikkan IPK.";
        }

        // 4. Evaluasi Beban SKS terhadap Semester Berjalan
        $semesterBerjalan = max(1, $mahasiswa->semester_berjalan);
        $sksIdeal = $semesterBerjalan * 18;
        if ($mahasiswa->total_sks < ($sksIdeal * 0.75) && $semesterBerjalan > 2) {
            $skorRisiko += 15;
            $alasanList[] = "Progres perolehan SKS ({$mahasiswa->total_sks} SKS) tertinggal dari target ideal ({$sksIdeal} SKS pada semester {$semesterBerjalan}).";
            $rekomendasiList[] = "Konsultasikan pengisian KRS dengan Dosen Wali untuk memaksimalkan kuota SKS yang dapat diambil.";
        }

        // Tentukan Kategori Risiko
        if ($skorRisiko >= 40 || $jumlahNilaiE > 0 || ($kehadiran > 0 && $kehadiran < 70) || $ipk < 2.00) {
            $statusRisiko = 'merah';
            if (empty($rekomendasiList)) {
                $rekomendasiList[] = "Diperlukan tindakan penanganan akademik segera bersama Dosen Wali dan Ketua Prodi.";
            }
        } elseif ($skorRisiko >= 15 || $jumlahNilaiD > 0 || ($kehadiran > 0 && $kehadiran < 80) || $ipk < 2.75) {
            $statusRisiko = 'kuning';
            if (empty($rekomendasiList)) {
                $rekomendasiList[] = "Pertahankan konsistensi belajar dan perhatikan evaluasi tengah semester.";
            }
        } else {
            $statusRisiko = 'hijau';
            $rekomendasiList[] = "Kondisi akademik memuaskan. Pertahankan prestasi dan kembangkan kompetensi unggulan.";
        }

        // Update status risiko di profil mahasiswa
        $mahasiswa->status_risiko = $statusRisiko;
        $mahasiswa->save();

        // Simpan riwayat peringatan akademik
        return PeringatanAkademik::create([
            'mahasiswa_id' => $mahasiswa->id,
            'semester_id' => $semesterId,
            'status_risiko' => $statusRisiko,
            'skor_risiko' => min(100.00, (float) $skorRisiko),
            'indikator' => [
                'ipk' => $ipk,
                'kehadiran' => $kehadiran,
                'nilai_e' => $jumlahNilaiE,
                'nilai_d' => $jumlahNilaiD,
                'total_sks' => $mahasiswa->total_sks,
                'semester' => $semesterBerjalan,
            ],
            'alasan' => implode(' ', $alasanList),
            'rekomendasi' => implode(' ', $rekomendasiList),
            'dihitung_pada' => now(),
        ]);
    }

    /**
     * Pemetaan Kompetensi Mahasiswa berbasis CPL Program Studi Teknologi Informasi UNUJA.
     * Mengembalikan data radar chart (label, skor 0-100, deskripsi).
     */
    public function hitungProfilKompetensi(Mahasiswa $mahasiswa): array
    {
        $keahlianList = Keahlian::with(['mataKuliahs'])->get();
        $nilaiMahasiswa = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('status', ['lulus', 'sedang_ditempuh'])
            ->get()
            ->keyBy('mata_kuliah_id');

        $labels = [];
        $scores = [];
        $details = [];

        foreach ($keahlianList as $keahlian) {
            $totalBobotMk = 0.0;
            $totalPoinDiperoleh = 0.0;
            $mkCount = 0;

            foreach ($keahlian->mataKuliahs as $mk) {
                $bobotKeahlian = (float) ($mk->pivot->bobot ?? 1.0);
                $totalBobotMk += $bobotKeahlian;

                if ($nilaiMahasiswa->has($mk->id)) {
                    $nilai = $nilaiMahasiswa->get($mk->id);
                    // Konversi bobot 4.0 ke skala 100
                    $poin = ((float) $nilai->bobot / 4.0) * 100.0;
                    $totalPoinDiperoleh += ($poin * $bobotKeahlian);
                    $mkCount++;
                }
            }

            $skorAkhir = $totalBobotMk > 0 && $mkCount > 0
                ? round($totalPoinDiperoleh / $totalBobotMk, 1)
                : 0.0;

            $labels[] = $keahlian->nama;
            $scores[] = $skorAkhir;
            $details[] = [
                'id' => $keahlian->id,
                'kode' => $keahlian->kode,
                'nama' => $keahlian->nama,
                'skor' => $skorAkhir,
                'mk_ditempuh' => $mkCount,
                'total_mk' => $keahlian->mataKuliahs->count(),
                'status' => $skorAkhir >= 75 ? 'Tinggi' : ($skorAkhir >= 60 ? 'Cukup' : 'Perlu Ditingkatkan'),
            ];
        }

        return [
            'labels' => $labels,
            'scores' => $scores,
            'details' => $details,
        ];
    }

    /**
     * Simulasi IPK interaktif.
     * Menghitung estimasi IPS dan dampak ke IPK kumulatif baru.
     */
    public function simulasiIpk(Mahasiswa $mahasiswa, array $mataKuliahSimulasi): array
    {
        $currentSks = (int) $mahasiswa->total_sks;
        $currentMutu = (float) ($mahasiswa->ipk * $currentSks);

        $simSks = 0;
        $simMutu = 0.0;
        $detailSimulasi = [];

        foreach ($mataKuliahSimulasi as $item) {
            $sks = (int) ($item['sks'] ?? 0);
            $nilaiAngka = (float) ($item['nilai_angka'] ?? 0);
            $konversi = Nilai::konversiNilai($nilaiAngka);

            $mutuMk = $sks * $konversi['bobot'];
            $simSks += $sks;
            $simMutu += $mutuMk;

            $detailSimulasi[] = [
                'nama_mk' => $item['nama_mk'] ?? 'Mata Kuliah',
                'sks' => $sks,
                'nilai_angka' => $nilaiAngka,
                'nilai_huruf' => $konversi['huruf'],
                'bobot' => $konversi['bobot'],
                'mutu' => $mutuMk,
            ];
        }

        $estimasiIps = $simSks > 0 ? round($simMutu / $simSks, 2) : 0.00;
        $totalSksBaru = $currentSks + $simSks;
        $totalMutuBaru = $currentMutu + $simMutu;
        $estimasiIpkBaru = $totalSksBaru > 0 ? round($totalMutuBaru / $totalSksBaru, 2) : 0.00;
        $selisihIpk = round($estimasiIpkBaru - (float) $mahasiswa->ipk, 2);

        return [
            'ipk_sekarang' => (float) $mahasiswa->ipk,
            'total_sks_sekarang' => $currentSks,
            'sks_simulasi' => $simSks,
            'estimasi_ips' => $estimasiIps,
            'total_sks_setelah_simulasi' => $totalSksBaru,
            'estimasi_ipk_baru' => $estimasiIpkBaru,
            'selisih_ipk' => $selisihIpk,
            'detail' => $detailSimulasi,
        ];
    }
}

