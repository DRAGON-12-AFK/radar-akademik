<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Aplikasi Pemantau Studi Mahasiswa UNUJA</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e40af">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950">
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 items-center justify-center shadow-lg shadow-blue-500/30 mb-4 text-white text-2xl">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Pemantau Studi Mahasiswa</h1>
            <p class="text-sm text-blue-300 mt-1">Universitas Nurul Jadid (UNUJA) Paiton</p>
            <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-semibold bg-blue-500/10 text-blue-300 border border-blue-400/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Terintegrasi API Resmi SSO UNUJA</span>
            </div>
        </div>

        <!-- Single Input Login Card Form -->
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-6 sm:p-8 border border-white/20">
            <!-- Informasi Akun Hint -->
            <div class="mb-5 p-3.5 bg-blue-50/70 border border-blue-100 rounded-xl text-xs text-blue-900 leading-relaxed">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-blue-800">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Verifikasi Identitas Resmi UNUJA</span>
                </div>
                <p class="text-[11px] text-blue-700">
                    Sistem mendeteksi otomatis peran Anda sebagai <strong>Mahasiswa</strong> atau <strong>Dosen</strong> langsung dari pangkalan data resmi UNUJA.
                </p>
                <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-blue-200/60 text-[10px] text-blue-600 font-semibold">
                    <div>• Mahasiswa: Input <strong>NIM</strong></div>
                    <div>• Dosen: Input <strong>NIDN</strong></div>
                </div>
            </div>

            <!-- Error Notifications -->
            @if(session('error') || (isset($errors) && $errors->any()))
                <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 mt-0.5 text-sm shrink-0"></i>
                    <div class="flex-1 leading-relaxed">
                        {{ session('error') ?? (isset($errors) ? $errors->first() : '') }}
                    </div>
                </div>
            @endif

            <!-- Success Notification -->
            @if(session('success'))
                <div class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Redis Status Notice -->
            @if(isset($redisAvailable) && !$redisAvailable)
                <div class="mb-4 px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 text-[11px] flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-slate-400"></i>
                    <span>Layanan cache: <strong>Database Cache</strong> (Redis cadangan).</span>
                </div>
            @endif

            <!-- FORM LOGIN TUNGGAL: 1 INPUT IDENTITAS (NIM / NIDN) -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Input Nomor Identitas Tunggal -->
                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Identitas (NIM / NIDN)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-id-card text-sm"></i>
                        </div>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                            placeholder="Contoh: 1921300014 atau 0703109403"
                            class="w-full pl-10 pr-3.5 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1.5">
                        Masukkan NIM untuk akun Mahasiswa atau NIDN untuk Dosen Wali.
                    </p>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-blue-500/25 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket text-xs"></i>
                    <span>Verifikasi Identitas & Masuk</span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-xs text-slate-500">
            &copy; {{ date('Y') }} Universitas Nurul Jadid (UNUJA). Hak Cipta Dilindungi.
        </div>
    </div>
</body>
</html>
