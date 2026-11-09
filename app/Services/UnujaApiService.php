<?php

namespace App\Services;

use App\Models\ApiSyncHistory;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UnujaApiService
{
    protected string $baseUrl;
    protected ?string $token;
    protected ?string $devId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.unuja.base_url', 'https://sso.unuja.ac.id'), '/');
        $this->token = config('services.unuja.token');
        $this->devId = config('services.unuja.dev_id');
    }

    /**
     * Memeriksa apakah konfigurasi X-Token dan dev_id sudah tersedia di .env.
     */
    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->devId);
    }

    /**
     * Menguji koneksi dan otorisasi API UNUJA dengan mengirimkan X-Token dan dev_id di JSON body.
     */
    public function authorize(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'status' => null,
                'error_type' => 'missing_credentials',
                'message' => 'Konfigurasi UNUJA_API_TOKEN atau DEV_ID belum lengkap pada file .env',
                'data' => null,
            ];
        }

        $url = $this->baseUrl . '/portal/data/authorize';

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(12)->post($url, [
                'X-Token' => $this->token,
                'dev_id' => $this->devId,
            ]);

            $status = $response->status();
            $decodedJson = $response->json();

            if (!$response->successful()) {
                Log::warning('UNUJA API Authorization Failed', [
                    'url' => $url,
                    'status' => $status,
                ]);
            }

            $safeData = is_array($decodedJson) ? $this->maskSensitiveData($decodedJson) : null;

            return [
                'success' => $response->successful(),
                'status' => $status,
                'message' => $response->successful()
                    ? 'Koneksi ke API UNUJA berhasil diotorisasi.'
                    : "Server API UNUJA merespons dengan status HTTP {$status}.",
                'data' => $safeData ?? ['raw_response' => 'Non-JSON response received.'],
                'raw_authorized_data' => $response->successful() ? $decodedJson : null,
            ];
        } catch (ConnectionException $e) {
            Log::error('UNUJA API Connection Timeout / Host Unreachable', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => null,
                'error_type' => 'timeout',
                'message' => 'Koneksi ke API UNUJA mengalami batas waktu (timeout). Silakan periksa jaringan Anda.',
                'data' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('UNUJA API Unexpected Exception', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => null,
                'error_type' => 'exception',
                'message' => 'Terjadi kesalahan sistem saat menghubungi API UNUJA: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Mendapatkan URL query data sesi terotorisasi.
     * Menggunakan cache 30 menit agar tidak memanggil /portal/data/authorize berulang kali.
     */
    public function getDataUrl(): ?string
    {
        return Cache::remember('unuja_session_data_url', 1800, function () {
            $auth = $this->authorize();
            if ($auth['success'] && isset($auth['raw_authorized_data']['data']['info']['urls']['data'])) {
                return $auth['raw_authorized_data']['data']['info']['urls']['data'];
            }
            return null;
        });
    }

    /**
     * Mengambil dan menyinkronkan seluruh riwayat Transkrip Akademik Mahasiswa dari API UNUJA.
     * Menyimpan ke database MySQL: Mahasiswa, Mata Kuliah, Nilai, dan Riwayat Sinkronisasi.
     */
    public function syncTranskripMahasiswa(string $nim, ?int $userId = null): array
    {
        $nim = trim($nim);
        $startTime = hrtime(true);
        $dataUrl = $this->getDataUrl();

        if (!$dataUrl) {
            Cache::forget('unuja_session_data_url');
            $dataUrl = $this->getDataUrl();
        }

        if (!$dataUrl) {
            $this->recordSyncHistory('transkrip', $nim, $userId, 'failed', 0, 0, 'Gagal memperoleh endpoint data sesi dari API UNUJA.');
            return [
                'success' => false,
                'error_type' => 'data_url_missing',
                'message' => 'Layanan API UNUJA tidak dapat dihubungi saat ini.',
            ];
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-Token' => $this->token,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(15)
                ->post($dataUrl, [
                    'filter' => 'transkrip',
                    'nim' => $nim,
                ]);

            $durationMs = (int) round((hrtime(true) - $startTime) / 1e6);

            if (!$response->successful()) {
                $errorMsg = "Gagal sinkronisasi transkrip: Server merespons HTTP {$response->status()}.";
                $this->recordSyncHistory('transkrip', $nim, $userId, 'failed', 0, $durationMs, $errorMsg);

                return [
                    'success' => false,
                    'status' => $response->status(),
                    'error_type' => 'http_error_' . $response->status(),
                    'message' => $errorMsg,
                ];
            }

            $json = $response->json();
            $data = $json['data'] ?? [];

            if (empty($data) || empty($data['mahasiswa'])) {
                $errorMsg = "Data transkrip untuk NIM [{$nim}] tidak ditemukan pada API UNUJA.";
                $this->recordSyncHistory('transkrip', $nim, $userId, 'failed', 0, $durationMs, $errorMsg);

                return [
                    'success' => false,
                    'error_type' => 'data_empty',
                    'message' => $errorMsg,
                ];
            }

            $mhsInfo = $data['mahasiswa'];
            $ketuntasan = $data['ketuntasan'] ?? [];
            $matakuliahList = $data['matakuliah'] ?? [];

            $angkatan = $mhsInfo['angkatan'] ?? (strlen($nim) >= 2 ? '20' . substr($nim, 0, 2) : date('Y'));
            $semesterBerjalan = !empty($mhsInfo['smt']) ? (int) $mhsInfo['smt'] : (!empty($mhsInfo['semester']) ? (int) $mhsInfo['semester'] : 1);

            // 1. Simpan/Update Mahasiswa
            $mahasiswa = Mahasiswa::updateOrCreate(
                ['nim' => $nim],
                [
                    'user_id' => $userId,
                    'nama_lengkap' => $mhsInfo['nama'] ?? 'Mahasiswa UNUJA',
                    'program_studi' => $mhsInfo['prodi'] ?? 'Teknologi Informasi',
                    'fakultas' => $mhsInfo['fakultas'] ?? 'Teknik',
                    'angkatan' => $angkatan,
                    'semester_berjalan' => $semesterBerjalan,
                    'ipk' => !empty($ketuntasan['ipk_ketuntasan']) ? (float) $ketuntasan['ipk_ketuntasan'] : 0.00,
                    'total_sks' => !empty($ketuntasan['sks_ketuntasan']) ? (int) $ketuntasan['sks_ketuntasan'] : 0,
                    'status_akademik' => 'aktif',
                ]
            );

            // 2. Simpan Mata Kuliah & Nilai
            $syncedCount = 0;
            foreach ($matakuliahList as $mk) {
                if (empty($mk['kode_matkul'])) continue;

                $smtNumber = !empty($mk['smt']) ? (int) $mk['smt'] : 1;

                // Pastikan semester ada
                $semester = Semester::firstOrCreate(
                    ['kode' => 'SMT' . $smtNumber],
                    [
                        'nama' => 'Semester ' . $smtNumber,
                        'tahun_ajaran' => '2024/2025',
                        'tipe' => ($smtNumber % 2 === 1) ? 'ganjil' : 'genap',
                    ]
                );

                // Pastikan Mata Kuliah tersimpan
                $mataKuliah = MataKuliah::updateOrCreate(
                    ['kode_mk' => $mk['kode_matkul']],
                    [
                        'nama_mk' => $mk['nama_matkul'] ?? $mk['kode_matkul'],
                        'sks' => !empty($mk['sks_mk']) ? (int) $mk['sks_mk'] : 3,
                        'semester_paket' => $smtNumber,
                    ]
                );

                // Simpan Nilai
                Nilai::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa->id,
                        'mata_kuliah_id' => $mataKuliah->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'nilai_huruf' => $mk['n_jenjang_huruf'] ?? 'E',
                        'bobot' => !empty($mk['n_indeks']) ? (float) $mk['n_indeks'] : 0.00,
                        'status' => in_array($mk['n_jenjang_huruf'] ?? 'E', ['D', 'E']) ? 'tidak_lulus' : 'lulus',
                    ]
                );

                $syncedCount++;
            }

            // 3. Catat Riwayat Sinkronisasi Sukses
            $this->recordSyncHistory('transkrip', $nim, $userId, 'success', $syncedCount, $durationMs, null);

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan {$syncedCount} mata kuliah transkrip dari API UNUJA.",
                'data' => [
                    'mahasiswa' => $mahasiswa,
                    'ketuntasan' => $ketuntasan,
                    'total_matakuliah' => $syncedCount,
                ],
                'duration_ms' => $durationMs,
            ];

        } catch (ConnectionException $e) {
            $durationMs = (int) round((hrtime(true) - $startTime) / 1e6);
            $this->recordSyncHistory('transkrip', $nim, $userId, 'timeout', 0, $durationMs, 'Batas waktu koneksi (timeout) terlampaui.');

            return [
                'success' => false,
                'error_type' => 'timeout',
                'message' => 'Koneksi ke API UNUJA mengalami batas waktu (timeout).',
            ];
        } catch (\Throwable $e) {
            $durationMs = (int) round((hrtime(true) - $startTime) / 1e6);
            $this->recordSyncHistory('transkrip', $nim, $userId, 'failed', 0, $durationMs, 'Kesalahan: ' . $e->getMessage());

            return [
                'success' => false,
                'error_type' => 'exception',
                'message' => 'Terjadi kendala saat menyinkronkan data: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Memverifikasi dan mengambil data profil mahasiswa berdasarkan NIM.
     */
    public function verifyAndGetMahasiswa(string $nim): array
    {
        $nim = trim($nim);
        if (empty($nim)) {
            return [
                'success' => false,
                'error_type' => 'empty_nim',
                'message' => 'NIM wajib diisi.',
            ];
        }

        $dataUrl = $this->getDataUrl();

        if (!$dataUrl) {
            Cache::forget('unuja_session_data_url');
            $dataUrl = $this->getDataUrl();
        }

        if (!$dataUrl) {
            return [
                'success' => false,
                'error_type' => 'api_unreachable',
                'message' => 'Layanan API UNUJA tidak dapat dihubungi saat ini.',
            ];
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-Token' => $this->token,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(12)
                ->post($dataUrl, [
                    'filter' => 'mahasiswa',
                    'search' => $nim,
                ]);

            if ($response->status() === 401) {
                Cache::forget('unuja_session_data_url');
            }

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'error_type' => 'http_error_' . $response->status(),
                    'message' => "Server API UNUJA merespons dengan kode HTTP {$response->status()}.",
                ];
            }

            $json = $response->json();
            $items = $json['data']['data'] ?? [];

            foreach ($items as $item) {
                if (isset($item['nim']) && (string) $item['nim'] === $nim) {
                    return [
                        'success' => true,
                        'data' => [
                            'nim' => (string) $item['nim'],
                            'nama' => (string) ($item['nama'] ?? 'Mahasiswa UNUJA'),
                            'prodi' => (string) ($item['prodi'] ?? 'Teknologi Informasi'),
                            'fakultas' => (string) ($item['fakultas'] ?? 'Teknik'),
                            'email' => $item['email'] ?? null,
                            'id_wali' => $item['id_wali'] ?? null,
                            'wali' => $item['wali'] ?? null,
                            'nidn' => $item['nidn'] ?? null,
                            'ipk' => !empty($item['ipk']) ? (float) $item['ipk'] : 0.00,
                            'sks_ketuntasan' => !empty($item['sks_ketuntasan']) ? (int) $item['sks_ketuntasan'] : 0,
                            'semester' => !empty($item['semester']) ? (int) $item['semester'] : 1,
                        ],
                    ];
                }
            }

            return [
                'success' => false,
                'error_type' => 'nim_not_found',
                'message' => "NIM [{$nim}] tidak ditemukan pada pangkalan data akademik UNUJA.",
            ];

        } catch (ConnectionException $e) {
            return [
                'success' => false,
                'error_type' => 'timeout',
                'message' => 'Koneksi ke API UNUJA mengalami batas waktu (timeout).',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error_type' => 'server_error',
                'message' => 'Terjadi kendala saat memverifikasi NIM: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Memverifikasi dan mengambil data profil dosen berdasarkan NIDN dari API UNUJA.
     */
    public function verifyAndGetDosen(string $nidn): array
    {
        $nidn = trim($nidn);
        if (empty($nidn)) {
            return [
                'success' => false,
                'error_type' => 'empty_nidn',
                'message' => 'NIDN wajib diisi.',
            ];
        }

        $dataUrl = $this->getDataUrl();
        if (!$dataUrl) {
            Cache::forget('unuja_session_data_url');
            $dataUrl = $this->getDataUrl();
        }

        if (!$dataUrl) {
            return [
                'success' => false,
                'error_type' => 'api_unreachable',
                'message' => 'Layanan API UNUJA tidak dapat dihubungi saat ini.',
            ];
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-Token' => $this->token,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(12)
                ->post($dataUrl, [
                    'filter' => 'dosen',
                    'search' => $nidn,
                ]);

            if ($response->status() === 401) {
                Cache::forget('unuja_session_data_url');
            }

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'error_type' => 'http_error_' . $response->status(),
                    'message' => "Server API UNUJA merespons status HTTP {$response->status()}.",
                ];
            }

            $json = $response->json();
            $items = $json['data']['data'] ?? [];

            foreach ($items as $item) {
                if (isset($item['nidn']) && (string) $item['nidn'] === $nidn) {
                    $namaLengkap = trim(($item['gelar_depan'] ?? '') . ' ' . ($item['nama_penduduk'] ?? '') . (!empty($item['gelar_belakang']) ? ', ' . $item['gelar_belakang'] : ''));

                    return [
                        'success' => true,
                        'data' => [
                            'nidn' => (string) $item['nidn'],
                            'nama' => !empty($namaLengkap) ? $namaLengkap : ($item['nama_penduduk'] ?? 'Dosen UNUJA'),
                            'gelar' => $item['gelar_belakang'] ?? null,
                            'prodi' => $item['prodi'] ?? 'Teknologi Informasi',
                            'fakultas' => $item['fakultas'] ?? 'Teknik',
                            'email' => $item['email'] ?? null,
                            'no_hp' => $item['no_hp'] ?? null,
                        ],
                    ];
                }
            }

            return [
                'success' => false,
                'error_type' => 'nidn_not_found',
                'message' => "NIDN [{$nidn}] tidak terdaftar sebagai dosen pada pangkalan data akademik UNUJA.",
            ];
        } catch (ConnectionException $e) {
            return [
                'success' => false,
                'error_type' => 'timeout',
                'message' => 'Koneksi ke API UNUJA mengalami batas waktu (timeout).',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error_type' => 'server_error',
                'message' => 'Terjadi kendala saat memverifikasi NIDN: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengambil daftar mahasiswa dalam kewenangan prodi/fakultas untuk Dosen Wali.
     */
    public function getMahasiswasScope(?string $prodiFilter = null): array
    {
        return Cache::remember('unuja_mhs_scope_' . ($prodiFilter ?? 'all'), 600, function () use ($prodiFilter) {
            $dataUrl = $this->getDataUrl();
            if (!$dataUrl) return [];

            try {
                $payload = ['filter' => 'mahasiswa'];
                if ($prodiFilter) {
                    $payload['search'] = $prodiFilter;
                }

                $res = Http::acceptJson()
                    ->withHeaders([
                        'X-Token' => $this->token,
                        'Content-Type' => 'application/json',
                    ])
                    ->timeout(12)
                    ->post($dataUrl, $payload);

                if ($res->successful()) {
                    return $res->json()['data']['data'] ?? [];
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal mengambil daftar mahasiswa scope UNUJA', ['error' => $e->getMessage()]);
            }

            return [];
        });
    }

    /**
     * Memeriksa ketersediaan koneksi Redis secara aman tanpa menyebabkan fatal error.
     */
    public function isRedisAvailable(): bool
    {
        if (!class_exists('\Redis') && !class_exists('\Predis\Client')) {
            return false;
        }

        try {
            \Illuminate\Support\Facades\Redis::connection()->ping();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Mencatat riwayat sinkronisasi API ke database secara permanen.
     */
    protected function recordSyncHistory(string $filterType, ?string $identifier, ?int $userId, string $status, int $count, int $durationMs, ?string $error): void
    {
        try {
            ApiSyncHistory::create([
                'endpoint' => $this->baseUrl . '/portal/data/data',
                'filter_type' => $filterType,
                'identifier' => $identifier,
                'user_id' => $userId,
                'status' => $status,
                'records_count' => $count,
                'duration_ms' => $durationMs,
                'error_message' => $error,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat api_sync_histories', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Menyensor token, dev_id, password, dan kunci rahasia lainnya secara rekursif.
     */
    protected function maskSensitiveData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->maskSensitiveData($value);
            } elseif (is_string($key) && preg_match('/(token|secret|password|dev_id)/i', $key)) {
                $data[$key] = '***[REDACTED]***';
            }
        }

        return $data;
    }
}
