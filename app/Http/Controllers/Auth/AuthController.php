<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DosenWali;
use App\Models\LoginHistory;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Services\UnujaApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected UnujaApiService $unujaApi;

    public function __construct(UnujaApiService $unujaApi)
    {
        $this->unujaApi = $unujaApi;
    }

    /**
     * Menampilkan satu halaman login tunggal untuk seluruh pengguna UNUJA (1 Input NIM/NIDN).
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $redisAvailable = $this->unujaApi->isRedisAvailable();

        return view('auth.login', [
            'redisAvailable' => $redisAvailable,
        ]);
    }

    /**
     * Memproses login dengan satu input identitas (NIM Mahasiswa / NIDN Dosen).
     * Role pengguna dan data profil ditentukan secara otomatis melalui API resmi UNUJA.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'min:3', 'max:50'],
        ], [
            'login.required' => 'Nomor Identitas (NIM Mahasiswa atau NIDN Dosen) wajib diisi.',
            'login.min' => 'Nomor identitas minimal 3 karakter.',
        ]);

        $loginInput = trim($validated['login']);
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Rate Limiter: Maksimal 5 percobaan per menit per IP & Identitas
        $throttleKey = Str::transliterate('login|' . $ip . '|' . $loginInput);
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->recordLoginHistory(null, $loginInput, 'guest', $ip, $userAgent, 'failed', "Rate limit: coba lagi dalam {$seconds} detik.");

            return back()->withInput()->with('error', "Terlalu banyak percobaan masuk. Silakan tunggu {$seconds} detik lagi.");
        }

        // 1. Otorisasi Akun Khusus Administrator
        if ($loginInput === 'admin' || $loginInput === 'admin@unuja.ac.id') {
            $adminUser = User::where('role', User::ROLE_ADMIN)->first();
            if ($adminUser) {
                RateLimiter::clear($throttleKey);
                Auth::login($adminUser, $request->boolean('remember'));
                if ($request->hasSession()) {
                    $request->session()->regenerate();
                }

                $this->recordLoginHistory($adminUser->id, $loginInput, User::ROLE_ADMIN, $ip, $userAgent, 'success', null);

                return redirect()->route('admin.dashboard')
                    ->with('success', 'Selamat datang kembali, Administrator Sistem.');
            }
        }

        // 2. Verifikasi Mahasiswa via API UNUJA (Berdasarkan NIM)
        $mhsCheck = $this->unujaApi->verifyAndGetMahasiswa($loginInput);
        if ($mhsCheck['success']) {
            $mhsData = $mhsCheck['data'];

            RateLimiter::clear($throttleKey);

            // Buat atau perbarui akun User Mahasiswa
            $user = User::firstOrCreate(
                ['username' => $loginInput],
                [
                    'name' => $mhsData['nama'],
                    'email' => $mhsData['email'] ?? ($loginInput . '@student.unuja.ac.id'),
                    'password' => Hash::make(Str::random(32)),
                    'role' => User::ROLE_MAHASISWA,
                    'is_active' => true,
                ]
            );

            if ($user->name !== $mhsData['nama']) {
                $user->name = $mhsData['nama'];
                $user->save();
            }

            // Pastikan entitas Mahasiswa minimal terbuat terlebih dahulu agar terhindar dari 404
            $angkatan = $mhsData['angkatan'] ?? (strlen($loginInput) >= 2 ? '20' . substr($loginInput, 0, 2) : date('Y'));
            Mahasiswa::updateOrCreate(
                ['nim' => $loginInput],
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $mhsData['nama'],
                    'program_studi' => $mhsData['prodi'] ?? 'Teknologi Informasi',
                    'fakultas' => $mhsData['fakultas'] ?? 'Teknik',
                    'angkatan' => $angkatan,
                    'semester_berjalan' => $mhsData['semester'] ?? 1,
                    'ipk' => $mhsData['ipk'] ?? 0.00,
                    'total_sks' => $mhsData['sks_ketuntasan'] ?? 0,
                    'status_akademik' => 'aktif',
                ]
            );

            // Sinkronisasi data transkrip akademik lengkap dari API UNUJA
            $this->unujaApi->syncTranskripMahasiswa($loginInput, $user->id);

            Auth::login($user, $request->boolean('remember'));
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $this->recordLoginHistory($user->id, $loginInput, User::ROLE_MAHASISWA, $ip, $userAgent, 'success', null);

            return redirect()->route('mahasiswa.dashboard')
                ->with('success', 'Selamat datang, ' . $mhsData['nama'] . '! Data akademik Anda berhasil diverifikasi dari API UNUJA.');
        }

        // 3. Verifikasi Dosen via API UNUJA (Berdasarkan NIDN)
        $dosenCheck = $this->unujaApi->verifyAndGetDosen($loginInput);
        if ($dosenCheck['success']) {
            $dosenData = $dosenCheck['data'];

            RateLimiter::clear($throttleKey);

            // Buat atau perbarui akun User Dosen
            $user = User::firstOrCreate(
                ['username' => $loginInput],
                [
                    'name' => $dosenData['nama'],
                    'email' => $dosenData['email'] ?? ($loginInput . '@unuja.ac.id'),
                    'password' => Hash::make(Str::random(32)),
                    'role' => User::ROLE_DOSEN_WALI,
                    'is_active' => true,
                ]
            );

            if ($user->name !== $dosenData['nama']) {
                $user->name = $dosenData['nama'];
                $user->save();
            }

            // Pastikan profil DosenWali tersimpan dan tertaut ke akun User
            DosenWali::updateOrCreate(
                ['nidn' => $loginInput],
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $dosenData['nama'],
                    'gelar' => $dosenData['gelar'],
                    'email' => $dosenData['email'],
                    'prodi' => $dosenData['prodi'] ?? 'Teknologi Informasi',
                ]
            );

            Auth::login($user, $request->boolean('remember'));
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $this->recordLoginHistory($user->id, $loginInput, User::ROLE_DOSEN_WALI, $ip, $userAgent, 'success', null);

            return redirect()->route('dosen.dashboard')
                ->with('success', 'Selamat datang, ' . $dosenData['nama'] . '! Identitas Dosen Pembimbing Anda berhasil diverifikasi melalui API UNUJA.');
        }

        // 4. Identitas Tidak Ditemukan atau Kendala Jaringan
        RateLimiter::hit($throttleKey, 60);

        $errorMessage = match ($mhsCheck['error_type'] ?? null) {
            'timeout' => 'Koneksi ke API UNUJA mengalami batas waktu (timeout). Silakan periksa jaringan dan coba lagi.',
            'api_unreachable', 'missing_credentials' => 'Layanan API UNUJA saat ini tidak dapat dihubungi. Silakan hubungi administrator.',
            default => "Nomor identitas [{$loginInput}] tidak terdaftar sebagai mahasiswa maupun dosen pada API UNUJA.",
        };

        $this->recordLoginHistory(null, $loginInput, 'guest', $ip, $userAgent, 'failed', $errorMessage);

        Log::warning('Login attempt failed via single NIM/NIDN input', [
            'identifier' => $loginInput,
            'ip' => $ip,
        ]);

        return back()->withInput()->with('error', $errorMessage);
    }

    /**
     * Keluar dari sesi.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Mengarahkan pengguna ke dashboard sesuai peran masing-masing.
     */
    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            User::ROLE_DOSEN_WALI => redirect()->route('dosen.dashboard'),
            User::ROLE_MAHASISWA => redirect()->route('mahasiswa.dashboard'),
            default => redirect()->route('login'),
        };
    }

    /**
     * Mencatat riwayat sesi login ke database MySQL secara permanen.
     */
    protected function recordLoginHistory(?int $userId, string $identifier, string $role, ?string $ip, ?string $ua, string $status, ?string $reason): void
    {
        try {
            LoginHistory::create([
                'user_id' => $userId,
                'identifier' => $identifier,
                'role' => $role,
                'ip_address' => $ip,
                'user_agent' => $ua,
                'status' => $status,
                'failure_reason' => $reason,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat login_histories', ['error' => $e->getMessage()]);
        }
    }
}
