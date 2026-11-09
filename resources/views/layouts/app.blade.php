<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pemantau Studi') — Universitas Nurul Jadid (UNUJA)</title>

    <!-- PWA Manifest & Icons -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e40af">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Radar UNUJA">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tailwind CSS (CDN fallback for instant preview & full modern styles) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        unuja: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col antialiased text-slate-800 bg-slate-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Desktop -->
        <aside class="hidden md:flex md:flex-col md:w-64 bg-slate-900 text-white shrink-0 shadow-xl transition-all duration-300">
            <!-- Brand -->
            <div class="flex items-center gap-3 px-6 h-16 border-b border-slate-800 bg-slate-950/60">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-500 flex items-center justify-center font-bold text-lg text-white shadow-md shadow-blue-500/20">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="font-bold text-sm tracking-wide text-white leading-tight">Radar Akademik</h1>
                    <p class="text-xs text-blue-400 font-medium">TI — UNUJA</p>
                </div>
            </div>

            <!-- User Info Badge -->
            <div class="px-5 py-4 border-b border-slate-800/80 bg-slate-900/60">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600/30 text-blue-400 border border-blue-500/40 flex items-center justify-center font-semibold text-xs uppercase">
                        {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-xs font-semibold text-slate-200 truncate">{{ auth()->user()->name }}</div>
                        <div class="inline-flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ auth()->user()->role === 'admin' ? 'bg-amber-400' : (auth()->user()->role === 'dosen_wali' ? 'bg-purple-400' : 'bg-emerald-400') }}"></span>
                            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">
                                {{ str_replace('_', ' ', auth()->user()->role) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @if(auth()->user()->role === 'mahasiswa')
                    <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                        <span>Dashboard Studi</span>
                    </a>
                    <a href="{{ route('mahasiswa.radar') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('mahasiswa.radar') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-radar w-4 text-center"></i>
                        <span>Radar Kompetensi</span>
                    </a>
                    <a href="{{ route('mahasiswa.simulasi') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('mahasiswa.simulasi') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-calculator w-4 text-center"></i>
                        <span>Simulasi IPK</span>
                    </a>
                    <a href="{{ route('mahasiswa.peringatan') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('mahasiswa.peringatan') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-triangle-exclamation w-4 text-center"></i>
                        <span>Peringatan Dini</span>
                    </a>
                @elseif(auth()->user()->role === 'dosen_wali')
                    <a href="{{ route('dosen.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dosen.dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-users w-4 text-center"></i>
                        <span>Mahasiswa Bimbingan</span>
                    </a>
                    <a href="{{ route('dosen.prioritas') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dosen.prioritas') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-shield-halved w-4 text-center"></i>
                        <span>Prioritas Risiko</span>
                    </a>
                @elseif(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-gauge-high w-4 text-center"></i>
                        <span>Ringkasan Admin</span>
                    </a>
                @endif
            </nav>

            <!-- Bottom Action -->
            <div class="p-3 border-t border-slate-800">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition-all">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Keluar Akun</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header Navbar -->
            <header class="h-16 bg-white border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 z-10">
                <div class="flex items-center gap-3">
                    <!-- Mobile Hamburger -->
                    <button id="mobileMenuBtn" class="md:hidden text-slate-500 hover:text-slate-800 p-2 rounded-lg hover:bg-slate-100">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">@yield('page-title', 'Dashboard')</h2>
                        <p class="text-xs text-slate-400 hidden sm:block">Sistem Informasi Pemantau Studi Mahasiswa UNUJA</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Status Online/Offline Badge -->
                    <div id="connectionStatus" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span id="connectionText">Online</span>
                    </div>

                    <!-- User Pill -->
                    <div class="hidden sm:flex items-center gap-2 pl-3 border-l border-slate-200">
                        <span class="text-xs font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </header>

            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="mx-4 sm:mx-6 mt-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-emerald-800 text-sm">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="mx-4 sm:mx-6 mt-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl flex items-center justify-between text-rose-800 text-sm">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            <!-- Main Scrollable Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50/60">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Drawer Sidebar -->
    <div id="mobileDrawer" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs hidden transition-opacity">
        <div class="w-64 h-full bg-slate-900 text-white flex flex-col shadow-2xl">
            <div class="flex items-center justify-between px-6 h-16 border-b border-slate-800">
                <span class="font-bold text-sm text-white">Menu Navigasi</span>
                <button id="closeDrawerBtn" class="text-slate-400 hover:text-white p-2">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @if(auth()->user()->role === 'mahasiswa')
                    <a href="{{ route('mahasiswa.dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm text-slate-200 hover:bg-slate-800">Dashboard Studi</a>
                    <a href="{{ route('mahasiswa.radar') }}" class="block px-3 py-2.5 rounded-lg text-sm text-slate-200 hover:bg-slate-800">Radar Kompetensi</a>
                    <a href="{{ route('mahasiswa.simulasi') }}" class="block px-3 py-2.5 rounded-lg text-sm text-slate-200 hover:bg-slate-800">Simulasi IPK</a>
                    <a href="{{ route('mahasiswa.peringatan') }}" class="block px-3 py-2.5 rounded-lg text-sm text-slate-200 hover:bg-slate-800">Peringatan Dini</a>
                @elseif(auth()->user()->role === 'dosen_wali')
                    <a href="{{ route('dosen.dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm text-slate-200 hover:bg-slate-800">Mahasiswa Bimbingan</a>
                    <a href="{{ route('dosen.prioritas') }}" class="block px-3 py-2.5 rounded-lg text-sm text-slate-200 hover:bg-slate-800">Prioritas Risiko</a>
                @elseif(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm text-slate-200 hover:bg-slate-800">Ringkasan Admin</a>
                @endif
            </div>
            <div class="p-3 border-t border-slate-800">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 text-xs font-semibold rounded-lg text-rose-400 bg-rose-500/10">Keluar Akun</button>
                </form>
            </div>
        </div>
    </div>

    <!-- PWA Service Worker & Offline Sync Script -->
    <script>
        // Toggle Mobile Menu
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const drawer = document.getElementById('mobileDrawer');
        const closeBtn = document.getElementById('closeDrawerBtn');

        if (mobileBtn && drawer && closeBtn) {
            mobileBtn.addEventListener('click', () => drawer.classList.remove('hidden'));
            closeBtn.addEventListener('click', () => drawer.classList.add('hidden'));
            drawer.addEventListener('click', (e) => {
                if (e.target === drawer) drawer.classList.add('hidden');
            });
        }

        // Online / Offline State Listener
        function updateOnlineStatus() {
            const badge = document.getElementById('connectionStatus');
            const text = document.getElementById('connectionText');
            if (!badge || !text) return;

            if (navigator.onLine) {
                badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200';
                text.textContent = 'Online';
            } else {
                badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200';
                text.textContent = 'Offline (PWA)';
            }
        }

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();

        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then((reg) => {
                    console.log('PWA Service Worker registered:', reg.scope);
                }).catch((err) => {
                    console.warn('PWA Service Worker registration failed:', err);
                });
            });
        }
    </script>
    @stack('scripts')
</body>
</html>

