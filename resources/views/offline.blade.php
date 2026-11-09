<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900 text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mode Offline — Radar Studi UNUJA</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 text-center">
    <div class="max-w-md bg-white/10 backdrop-blur-md p-8 rounded-2xl border border-white/20 shadow-2xl space-y-4">
        <div class="w-16 h-16 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center text-2xl mx-auto">
            <i class="fa-solid fa-wifi-slash"></i>
        </div>
        <h1 class="text-xl font-bold">Koneksi Internet Terputus</h1>
        <p class="text-xs text-slate-300 leading-relaxed">
            Aplikasi sedang berjalan dalam mode <strong>Offline PWA</strong>. Data yang telah tersimpan di cache perangkat tetap dapat Anda akses.
        </p>
        <button onclick="window.location.reload()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold inline-flex items-center gap-2 transition-all">
            <i class="fa-solid fa-rotate-right"></i>
            <span>Coba Sambungkan Ulang</span>
        </button>
    </div>
</body>
</html>

