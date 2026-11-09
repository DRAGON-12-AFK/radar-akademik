<?php

namespace Database\Seeders;

use App\Models\CatatanPembimbingan;
use App\Models\DosenWali;
use App\Models\Keahlian;
use App\Models\Kehadiran;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\Semester;
use App\Models\User;
use App\Services\AcademicMonitoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@unuja.ac.id'],
            [
                'name' => 'Administrator Akademik',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        // 2. Akun Dosen Wali
        $dosenUser = User::firstOrCreate(
            ['email' => 'dosen@unuja.ac.id'],
            [
                'name' => 'Dr. Ahmad Fauzi, M.Kom.',
                'username' => '0712345601',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_DOSEN_WALI,
                'is_active' => true,
            ]
        );

        $dosenWali = DosenWali::firstOrCreate(
            ['nidn' => '0712345601'],
            [
                'user_id' => $dosenUser->id,
                'nama_lengkap' => 'Dr. Ahmad Fauzi, M.Kom.',
                'gelar' => 'M.Kom.',
                'email' => 'dosen@unuja.ac.id',
                'no_hp' => '081234567890',
                'prodi' => 'Teknologi Informasi',
            ]
        );

        // 3. Akun Mahasiswa
        $mhsUser = User::firstOrCreate(
            ['email' => 'mahasiswa@unuja.ac.id'],
            [
                'name' => 'Muhammad Yusuf',
                'username' => '221011001',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_MAHASISWA,
                'is_active' => true,
            ]
        );

        $mahasiswa = Mahasiswa::firstOrCreate(
            ['nim' => '221011001'],
            [
                'user_id' => $mhsUser->id,
                'dosen_wali_id' => $dosenWali->id,
                'nama_lengkap' => 'Muhammad Yusuf',
                'program_studi' => 'Teknologi Informasi',
                'fakultas' => 'Fakultas Teknologi Informasi',
                'angkatan' => '2022',
                'semester_berjalan' => 5,
                'status_akademik' => 'aktif',
                'ipk' => 3.52,
                'total_sks' => 84,
                'persentase_kehadiran_total' => 91.50,
                'status_risiko' => 'hijau',
                'share_token' => Str::random(40),
                'share_token_active' => true,
            ]
        );

        // 4. Mahasiswa ke-2 untuk pengujian risiko akademik (Kuning/Merah)
        $mhsUser2 = User::firstOrCreate(
            ['email' => 'rizki@unuja.ac.id'],
            [
                'name' => 'Rizki Pratama',
                'username' => '221011045',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_MAHASISWA,
                'is_active' => true,
            ]
        );

        $mahasiswa2 = Mahasiswa::firstOrCreate(
            ['nim' => '221011045'],
            [
                'user_id' => $mhsUser2->id,
                'dosen_wali_id' => $dosenWali->id,
                'nama_lengkap' => 'Rizki Pratama',
                'program_studi' => 'Teknologi Informasi',
                'fakultas' => 'Fakultas Teknologi Informasi',
                'angkatan' => '2022',
                'semester_berjalan' => 5,
                'status_akademik' => 'aktif',
                'ipk' => 2.45,
                'total_sks' => 64,
                'persentase_kehadiran_total' => 68.00,
                'status_risiko' => 'merah',
            ]
        );

        // 5. Data Semester
        $semesters = [
            ['kode' => '20221', 'nama' => 'Ganjil 2022/2023', 'tahun_ajaran' => '2022/2023', 'tipe' => 'ganjil', 'is_aktif' => false],
            ['kode' => '20222', 'nama' => 'Genap 2022/2023', 'tahun_ajaran' => '2022/2023', 'tipe' => 'genap', 'is_aktif' => false],
            ['kode' => '20231', 'nama' => 'Ganjil 2023/2024', 'tahun_ajaran' => '2023/2024', 'tipe' => 'ganjil', 'is_aktif' => false],
            ['kode' => '20232', 'nama' => 'Genap 2023/2024', 'tahun_ajaran' => '2023/2024', 'tipe' => 'genap', 'is_aktif' => false],
            ['kode' => '20241', 'nama' => 'Ganjil 2024/2025', 'tahun_ajaran' => '2024/2025', 'tipe' => 'ganjil', 'is_aktif' => true],
        ];

        $smtModels = [];
        foreach ($semesters as $s) {
            $smtModels[$s['kode']] = Semester::firstOrCreate(['kode' => $s['kode']], $s);
        }

        // 6. Data Keahlian (CPL TI UNUJA)
        $keahliansData = [
            ['kode' => 'PROG', 'nama' => 'Pemrograman', 'deskripsi' => 'Penguasaan logika, algoritma, pemrograman web, mobile, dan berorientasi objek.'],
            ['kode' => 'DB', 'nama' => 'Basis Data', 'deskripsi' => 'Perancangan, manipulasi, administrasi, dan optimasi database relasional serta NoSQL.'],
            ['kode' => 'NET', 'nama' => 'Jaringan Komputer', 'deskripsi' => 'Arsitektur jaringan komputer, routing, keamanan siber, dan infrastruktur cloud.'],
            ['kode' => 'RPL', 'nama' => 'Analisis & Desain Sistem', 'deskripsi' => 'Analisis kebutuhan sistem, pemodelan UML, UI/UX design, dan manajemen proyek perangkat lunak.'],
            ['kode' => 'RES', 'nama' => 'Penelitian & Data', 'deskripsi' => 'Kecerdasan buatan, data mining, metodologi riset ilmiah, dan analisis data.'],
            ['kode' => 'LEAD', 'nama' => 'Kepemimpinan & Etika', 'deskripsi' => 'Kepemimpinan tim, komunikasi profesional, etika profesi TI, dan technopreneurship.'],
            ['kode' => 'REL', 'nama' => 'Keilmuan Keagamaan', 'deskripsi' => 'Integrasi nilai kepesantrenan, Aswaja, dan etika Islam dalam pemanfaatan teknologi.'],
        ];

        $keahlianModels = [];
        foreach ($keahliansData as $k) {
            $keahlianModels[$k['kode']] = Keahlian::firstOrCreate(['kode' => $k['kode']], $k);
        }

        // 7. Data Mata Kuliah
        $mataKuliahs = [
            ['kode_mk' => 'TI101', 'nama_mk' => 'Algoritma & Pemrograman', 'sks' => 3, 'semester_paket' => 1, 'keahlian' => ['PROG' => 1.0]],
            ['kode_mk' => 'TI102', 'nama_mk' => 'Pengantar Teknologi Informasi', 'sks' => 2, 'semester_paket' => 1, 'keahlian' => ['RPL' => 0.7, 'NET' => 0.3]],
            ['kode_mk' => 'TI103', 'nama_mk' => 'Keaswajaan & Nilai Kepesantrenan', 'sks' => 2, 'semester_paket' => 1, 'keahlian' => ['REL' => 1.0]],
            ['kode_mk' => 'TI201', 'nama_mk' => 'Pemrograman Berorientasi Objek', 'sks' => 3, 'semester_paket' => 2, 'keahlian' => ['PROG' => 1.0]],
            ['kode_mk' => 'TI202', 'nama_mk' => 'Sistem Basis Data', 'sks' => 3, 'semester_paket' => 2, 'keahlian' => ['DB' => 1.0]],
            ['kode_mk' => 'TI203', 'nama_mk' => 'Jaringan Komputer Dasar', 'sks' => 3, 'semester_paket' => 2, 'keahlian' => ['NET' => 1.0]],
            ['kode_mk' => 'TI301', 'nama_mk' => 'Pemrograman Web Lanjut', 'sks' => 3, 'semester_paket' => 3, 'keahlian' => ['PROG' => 0.8, 'RPL' => 0.5]],
            ['kode_mk' => 'TI302', 'nama_mk' => 'Analisis & Perancangan Sistem', 'sks' => 3, 'semester_paket' => 3, 'keahlian' => ['RPL' => 1.0]],
            ['kode_mk' => 'TI303', 'nama_mk' => 'Administrasi Basis Data', 'sks' => 3, 'semester_paket' => 3, 'keahlian' => ['DB' => 1.0]],
            ['kode_mk' => 'TI401', 'nama_mk' => 'Keamanan Jaringan & Sistem', 'sks' => 3, 'semester_paket' => 4, 'keahlian' => ['NET' => 0.9, 'PROG' => 0.3]],
            ['kode_mk' => 'TI402', 'nama_mk' => 'Rekayasa Perangkat Lunak', 'sks' => 3, 'semester_paket' => 4, 'keahlian' => ['RPL' => 1.0]],
            ['kode_mk' => 'TI403', 'nama_mk' => 'Metodologi Penelitian TI', 'sks' => 2, 'semester_paket' => 4, 'keahlian' => ['RES' => 1.0]],
            ['kode_mk' => 'TI501', 'nama_mk' => 'Kecerdasan Buatan & Data Mining', 'sks' => 3, 'semester_paket' => 5, 'keahlian' => ['RES' => 0.8, 'PROG' => 0.6]],
            ['kode_mk' => 'TI502', 'nama_mk' => 'Technopreneurship & Kepemimpinan', 'sks' => 2, 'semester_paket' => 5, 'keahlian' => ['LEAD' => 1.0]],
        ];

        $mkModels = [];
        foreach ($mataKuliahs as $m) {
            $mk = MataKuliah::firstOrCreate(
                ['kode_mk' => $m['kode_mk']],
                [
                    'nama_mk' => $m['nama_mk'],
                    'sks' => $m['sks'],
                    'semester_paket' => $m['semester_paket'],
                ]
            );
            $mkModels[$m['kode_mk']] = $mk;

            // Hubungkan ke Keahlian
            foreach ($m['keahlian'] as $kKode => $bobot) {
                if (isset($keahlianModels[$kKode])) {
                    $mk->keahlians()->syncWithoutDetaching([
                        $keahlianModels[$kKode]->id => ['bobot' => $bobot],
                    ]);
                }
            }
        }

        // 8. Sample Nilai & Kehadiran Mahasiswa 1 (Muhammad Yusuf - Berprestasi Baik)
        $sampleNilaiMhs1 = [
            ['smt' => '20221', 'mk' => 'TI101', 'angka' => 88.0, 'huruf' => 'A', 'bobot' => 4.00, 'hadir' => 16, 'pertemuan' => 16],
            ['smt' => '20221', 'mk' => 'TI102', 'angka' => 82.0, 'huruf' => 'A-', 'bobot' => 3.75, 'hadir' => 15, 'pertemuan' => 16],
            ['smt' => '20221', 'mk' => 'TI103', 'angka' => 90.0, 'huruf' => 'A', 'bobot' => 4.00, 'hadir' => 16, 'pertemuan' => 16],

            ['smt' => '20222', 'mk' => 'TI201', 'angka' => 85.0, 'huruf' => 'A', 'bobot' => 4.00, 'hadir' => 15, 'pertemuan' => 16],
            ['smt' => '20222', 'mk' => 'TI202', 'angka' => 78.0, 'huruf' => 'B+', 'bobot' => 3.50, 'hadir' => 14, 'pertemuan' => 16],
            ['smt' => '20222', 'mk' => 'TI203', 'angka' => 74.0, 'huruf' => 'B', 'bobot' => 3.00, 'hadir' => 14, 'pertemuan' => 16],

            ['smt' => '20231', 'mk' => 'TI301', 'angka' => 87.0, 'huruf' => 'A', 'bobot' => 4.00, 'hadir' => 16, 'pertemuan' => 16],
            ['smt' => '20231', 'mk' => 'TI302', 'angka' => 80.0, 'huruf' => 'A-', 'bobot' => 3.75, 'hadir' => 15, 'pertemuan' => 16],
            ['smt' => '20231', 'mk' => 'TI303', 'angka' => 76.0, 'huruf' => 'B+', 'bobot' => 3.50, 'hadir' => 15, 'pertemuan' => 16],

            ['smt' => '20232', 'mk' => 'TI401', 'angka' => 78.0, 'huruf' => 'B+', 'bobot' => 3.50, 'hadir' => 14, 'pertemuan' => 16],
            ['smt' => '20232', 'mk' => 'TI402', 'angka' => 84.0, 'huruf' => 'A-', 'bobot' => 3.75, 'hadir' => 15, 'pertemuan' => 16],
            ['smt' => '20232', 'mk' => 'TI403', 'angka' => 82.0, 'huruf' => 'A-', 'bobot' => 3.75, 'hadir' => 15, 'pertemuan' => 16],

            ['smt' => '20241', 'mk' => 'TI501', 'angka' => 80.0, 'huruf' => 'A-', 'bobot' => 3.75, 'hadir' => 8, 'pertemuan' => 8],
            ['smt' => '20241', 'mk' => 'TI502', 'angka' => 86.0, 'huruf' => 'A', 'bobot' => 4.00, 'hadir' => 8, 'pertemuan' => 8],
        ];

        foreach ($sampleNilaiMhs1 as $row) {
            $smt = $smtModels[$row['smt']];
            $mk = $mkModels[$row['mk']];

            Nilai::updateOrCreate(
                [
                    'mahasiswa_id' => $mahasiswa->id,
                    'mata_kuliah_id' => $mk->id,
                    'semester_id' => $smt->id,
                ],
                [
                    'nilai_angka' => $row['angka'],
                    'nilai_huruf' => $row['huruf'],
                    'bobot' => $row['bobot'],
                    'status' => 'lulus',
                ]
            );

            $persen = round(($row['hadir'] / $row['pertemuan']) * 100, 2);
            Kehadiran::updateOrCreate(
                [
                    'mahasiswa_id' => $mahasiswa->id,
                    'mata_kuliah_id' => $mk->id,
                    'semester_id' => $smt->id,
                ],
                [
                    'total_pertemuan' => $row['pertemuan'],
                    'total_hadir' => $row['hadir'],
                    'total_alpa' => $row['pertemuan'] - $row['hadir'],
                    'persentase' => $persen,
                ]
            );
        }

        // 9. Sample Nilai Mahasiswa 2 (Rizki Pratama - Kategori Merah / Butuh Perhatian)
        $sampleNilaiMhs2 = [
            ['smt' => '20221', 'mk' => 'TI101', 'angka' => 52.0, 'huruf' => 'C', 'bobot' => 2.00, 'hadir' => 10, 'pertemuan' => 16],
            ['smt' => '20221', 'mk' => 'TI102', 'angka' => 42.0, 'huruf' => 'D', 'bobot' => 1.00, 'hadir' => 9, 'pertemuan' => 16],
            ['smt' => '20222', 'mk' => 'TI201', 'angka' => 35.0, 'huruf' => 'E', 'bobot' => 0.00, 'hadir' => 8, 'pertemuan' => 16],
            ['smt' => '20222', 'mk' => 'TI202', 'angka' => 48.0, 'huruf' => 'D', 'bobot' => 1.00, 'hadir' => 10, 'pertemuan' => 16],
            ['smt' => '20231', 'mk' => 'TI301', 'angka' => 60.0, 'huruf' => 'C+', 'bobot' => 2.50, 'hadir' => 11, 'pertemuan' => 16],
            ['smt' => '20232', 'mk' => 'TI401', 'angka' => 40.0, 'huruf' => 'D', 'bobot' => 1.00, 'hadir' => 9, 'pertemuan' => 16],
        ];

        foreach ($sampleNilaiMhs2 as $row) {
            $smt = $smtModels[$row['smt']];
            $mk = $mkModels[$row['mk']];

            Nilai::updateOrCreate(
                [
                    'mahasiswa_id' => $mahasiswa2->id,
                    'mata_kuliah_id' => $mk->id,
                    'semester_id' => $smt->id,
                ],
                [
                    'nilai_angka' => $row['angka'],
                    'nilai_huruf' => $row['huruf'],
                    'bobot' => $row['bobot'],
                    'status' => in_array($row['huruf'], ['D', 'E']) ? 'tidak_lulus' : 'lulus',
                ]
            );

            $persen = round(($row['hadir'] / $row['pertemuan']) * 100, 2);
            Kehadiran::updateOrCreate(
                [
                    'mahasiswa_id' => $mahasiswa2->id,
                    'mata_kuliah_id' => $mk->id,
                    'semester_id' => $smt->id,
                ],
                [
                    'total_pertemuan' => $row['pertemuan'],
                    'total_hadir' => $row['hadir'],
                    'total_alpa' => $row['pertemuan'] - $row['hadir'],
                    'persentase' => $persen,
                ]
            );
        }

        // 10. Catatan Pembimbingan Awal
        CatatanPembimbingan::create([
            'dosen_wali_id' => $dosenWali->id,
            'mahasiswa_id' => $mahasiswa->id,
            'tanggal' => now()->subDays(14)->toDateString(),
            'catatan' => 'Perkembangan studi semester 5 sangat konsisten. Progres radar keahlian pemrograman dan basis data sangat memuaskan.',
            'rekomendasi' => 'Dianjurkan mulai menentukan topik penelitian skripsi di bidang Rekayasa Perangkat Lunak atau Data Science.',
            'status' => 'selesai',
        ]);

        CatatanPembimbingan::create([
            'dosen_wali_id' => $dosenWali->id,
            'mahasiswa_id' => $mahasiswa2->id,
            'tanggal' => now()->subDays(5)->toDateString(),
            'catatan' => 'Presensi kehadiran di bawah 70% dan terdapat nilai E pada mata kuliah prasyarat Pemrograman Berorientasi Objek.',
            'rekomendasi' => 'Wajib mengikuti program remedial dan perbaikan kehadiran di semester ini. Dijadwalkan konseling lanjutan.',
            'status' => 'menunggu',
        ]);

        // 11. Jalankan evaluasi peringatan dini via service
        $monitoringService = new AcademicMonitoringService();
        $monitoringService->evaluasiPeringatanDini($mahasiswa);
        $monitoringService->evaluasiPeringatanDini($mahasiswa2);
    }
}

