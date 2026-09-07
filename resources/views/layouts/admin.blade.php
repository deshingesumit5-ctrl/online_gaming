<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fun 2 Win</title>
    <!-- Favicon / Logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/gaming-theme.css') }}">
    @stack('styles')
</head>
<body class="bg-[#090d16] text-slate-100 min-h-screen flex flex-col selection:bg-amber-500 selection:text-black">
    <!-- Top Horizontal Navigation Bar (Laptop / Desktop Upward Direction) -->
    <header class="bg-[#0d1322] border-b border-slate-800/80 sticky top-0 z-40">
        <div class="px-4 sm:px-6 py-2.5 flex items-center justify-between gap-4">
            <!-- Brand & Mobile Toggle -->
            <div class="flex items-center gap-3 shrink-0">
                <!-- Mobile Toggle Button (opens left-side sidebar on mobile) -->
                <button id="mobile-menu-toggle" type="button" class="md:hidden p-2 rounded-lg bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-700 transition" aria-label="Open Navigation">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 sm:gap-3 text-decoration-none">
                    <img src="{{ asset('images/logo.png') }}" alt="Fun 2 Win" class="w-8 h-8 sm:w-9 sm:h-9 object-contain rounded-lg shadow-md shadow-amber-500/20">
                    <div>
                        <h1 class="font-bold text-sm sm:text-base font-royal text-amber-300 tracking-wider leading-tight">FUN 2 WIN</h1>
                        <span class="text-[9px] sm:text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">Admin Management</span>
                    </div>
                </a>
            </div>

            <!-- Top Horizontal Menu Bar (Laptop and Desktop view) -->
            <nav class="hidden md:flex items-center gap-1 lg:gap-2 text-xs lg:text-sm font-medium overflow-x-auto no-scrollbar">
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 {{ request()->routeIs('admin.users.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    User Approvals
                </a>
                <a href="{{ route('admin.game.control.index') }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 {{ request()->routeIs('admin.game.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Game Control
                </a>
                <a href="{{ route('admin.games.index') }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 {{ request()->routeIs('admin.games.*') || request()->routeIs('admin.rooms.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Game Management
                </a>
                <a href="{{ route('admin.wallet.index') }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 {{ request()->routeIs('admin.wallet.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Points
                </a>
                <a href="{{ route('admin.withdrawals.index') }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 {{ request()->routeIs('admin.withdrawals.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Withdrawals
                </a>
                <a href="{{ route('admin.reports.index') }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 {{ request()->routeIs('admin.reports.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Reports
                </a>
            </nav>

            <!-- Right Controls: Bell, Profile Icon & Logout -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <!-- Notifications Bell Icon with Dropdown -->
                <x-notifications-dropdown />

                <!-- Profile (Men Icon Only) -->
                <a href="{{ route('profile') }}" class="p-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-amber-400 text-xs font-semibold transition border border-slate-700/60 flex items-center justify-center cursor-pointer shrink-0 {{ request()->routeIs('profile*') ? 'border-amber-500/60 text-amber-400 bg-slate-800 shadow-sm' : '' }}" title="Profile">
                    <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </a>

                <!-- Logout -->
                <a href="{{ route('admin.logout') }}" class="p-1.5 sm:px-2.5 sm:py-1.5 rounded-lg bg-red-950/70 hover:bg-red-900/90 text-red-200 text-xs font-semibold transition border border-red-800/60 flex items-center gap-1 cursor-pointer whitespace-nowrap shrink-0" title="Logout">
                    <span>🚪</span> <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Mobile Drawer Backdrop -->
    <div id="mobile-sidebar-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden md:hidden transition-opacity"></div>

    <!-- Mobile Left-Side Sidebar (Shown on left side in mobile) -->
    <aside id="mobile-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-[#0d1322] border-r border-slate-800 p-5 flex flex-col justify-between transform -translate-x-full md:hidden transition-transform duration-300 ease-in-out shadow-2xl">
        <div>
            <!-- Brand & Close button -->
            <div class="flex items-center justify-between pb-5 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Fun 2 Win" class="w-9 h-9 object-contain rounded-lg shadow-md shadow-amber-500/20">
                    <div>
                        <h2 class="font-bold text-sm font-royal text-amber-300 tracking-wider">FUN 2 WIN</h2>
                        <span class="text-[10px] text-slate-500 font-semibold uppercase">Admin Portal</span>
                    </div>
                </div>
                <button id="mobile-menu-close" type="button" class="p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white">
                    ✕
                </button>
            </div>

            <!-- Nav Links (Left side on mobile) -->
            <nav class="mt-5 flex flex-col gap-2 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">
                    <span class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.users.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">
                    <span class="whitespace-nowrap">User Approvals</span>
                </a>
                <a href="{{ route('admin.game.control.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.game.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">
                    <span class="whitespace-nowrap">Game Control</span>
                </a>
                <a href="{{ route('admin.games.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.games.*') || request()->routeIs('admin.rooms.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">
                    <span class="whitespace-nowrap">Game Management</span>
                </a>
                <a href="{{ route('admin.wallet.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.wallet.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">
                    <span class="whitespace-nowrap">Points</span>
                </a>
                <a href="{{ route('admin.withdrawals.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.withdrawals.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">
                    <span class="whitespace-nowrap">Withdrawals</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition whitespace-nowrap {{ request()->routeIs('admin.reports.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">
                    <span class="whitespace-nowrap">Reports</span>
                </a>
                <a href="{{ route('profile') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition whitespace-nowrap {{ request()->routeIs('profile*') ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">
                    <span class="whitespace-nowrap">My Profile</span>
                </a>
            </nav>
        </div>

        <!-- Mobile Footer Info -->
        <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
            <div>
                <span class="text-slate-300 block font-semibold whitespace-nowrap">{{ Auth::user()->name }}</span>
                <span class="text-amber-500 uppercase font-bold text-[10px] whitespace-nowrap">Super Admin</span>
            </div>
            <a href="{{ route('admin.logout') }}" class="px-3 py-1.5 rounded-lg bg-red-950/70 hover:bg-red-900 text-red-300 font-semibold transition border border-red-800/60 cursor-pointer whitespace-nowrap shrink-0">
                Logout
            </a>
        </div>
    </aside>

    <!-- Sub-header Bar -->
    <div class="bg-[#0b101c]/90 border-b border-slate-800/70 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button type="button" class="text-slate-400 hover:text-emerald-400 transition text-sm font-mono px-1.5 py-0.5 rounded bg-slate-900 border border-slate-800" title="Toggle Sidebar">
                &laquo;
            </button>
            <h1 class="text-base sm:text-lg font-bold font-royal text-white flex items-center gap-2">
                @yield('page-title', 'Dashboard')
            </h1>
        </div>
    </div>

    <!    <!-- Centered Square Alert Banner in Center of Laptop -->
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div id="alert-overlay" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background: rgba(0,0,0,0.65); backdrop-filter: blur(5px);">
        <div id="alert-banner" class="relative flex flex-col items-center justify-between p-6 sm:p-7 rounded-3xl shadow-2xl border transition-all"
             style="width: 360px; height: 360px; max-width: 92vw; max-height: 92vw; animation: alertPopIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
             @if(session('success'))
                 background: radial-gradient(circle at 50% 20%, #064e3b 0%, #022c22 60%, #061814 100%); border-color: #10b981; box-shadow: 0 0 50px rgba(16,185,129,0.35);
             @elseif(session('error'))
                 background: radial-gradient(circle at 50% 20%, #7f1d1d 0%, #450a0a 60%, #1a0505 100%); border-color: #ef4444; box-shadow: 0 0 50px rgba(239,68,68,0.35);
             @elseif(session('warning'))
                 background: radial-gradient(circle at 50% 20%, #78350f 0%, #451a03 60%, #1f0b01 100%); border-color: #f59e0b; box-shadow: 0 0 50px rgba(245,158,11,0.35);
             @else
                 background: radial-gradient(circle at 50% 20%, #1e3a5f 0%, #0c1a3a 60%, #050b18 100%); border-color: #3b82f6; box-shadow: 0 0 50px rgba(59,130,246,0.35);
             @endif
             ">

            {{-- Close X Button at Top-Right --}}
            <button onclick="dismissAlertBanner()" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-lg font-bold transition border border-white/20 hover:scale-110 active:scale-95" title="Close (X)">
                ✕
            </button>

            {{-- Icon --}}
            <div class="mt-3">
                @if(session('success'))
                    <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 border-2 border-emerald-400 flex items-center justify-center text-emerald-400 text-3xl font-black shadow-lg shadow-emerald-500/30">
                        ✓
                    </div>
                @elseif(session('error'))
                    <div class="w-16 h-16 rounded-2xl bg-red-500/20 border-2 border-red-400 flex items-center justify-center text-red-400 text-3xl font-black shadow-lg shadow-red-500/30">
                        ✕
                    </div>
                @elseif(session('warning'))
                    <div class="w-16 h-16 rounded-2xl bg-amber-500/20 border-2 border-amber-400 flex items-center justify-center text-amber-400 text-3xl font-black shadow-lg shadow-amber-500/30">
                        🔔
                    </div>
                @else
                    <div class="w-16 h-16 rounded-2xl bg-blue-500/20 border-2 border-blue-400 flex items-center justify-center text-blue-400 text-3xl font-black shadow-lg shadow-blue-500/30">
                        ℹ
                    </div>
                @endif
            </div>

            {{-- Message Content --}}
            <div class="text-center px-2 flex flex-col items-center justify-center flex-grow my-2">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-300 mb-1.5">
                    @if(session('success')) Result Notice @elseif(session('error')) Alert @elseif(session('warning')) Warning @else Notification @endif
                </h3>
                <p class="text-white text-sm sm:text-base font-semibold leading-relaxed">
                    {{ session('success') ?? session('error') ?? session('warning') ?? session('info') }}
                </p>
            </div>

            {{-- Bottom Action Button --}}
            <button onclick="dismissAlertBanner()" class="w-full py-2.5 rounded-xl font-black text-xs uppercase tracking-wider text-white transition shadow-lg hover:brightness-110 active:scale-95
                @if(session('success')) bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/40
                @elseif(session('error')) bg-red-600 hover:bg-red-500 shadow-red-600/40
                @elseif(session('warning')) bg-amber-600 hover:bg-amber-500 shadow-amber-600/40
                @else bg-blue-600 hover:bg-blue-500 shadow-blue-600/40 @endif">
                OK
            </button>
        </div>
    </div>
    <style>
        @keyframes alertPopIn {
            from { opacity: 0; transform: scale(0.85); }
            to   { opacity: 1; transform: scale(1); }
        }
    </style>
    <script>
        function dismissAlertBanner() {
            var overlay = document.getElementById('alert-overlay');
            if (overlay) {
                overlay.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                overlay.style.opacity = '0';
                overlay.style.transform = 'scale(0.95)';
                setTimeout(function() { overlay.remove(); }, 250);
            }
        }
        document.getElementById('alert-overlay')?.addEventListener('click', function(e) {
            if (e.target === this) dismissAlertBanner();
        });
    </script>
    @endif

    <!-- Main Page Content -->
    <main class="max-w-7xl w-full mx-auto p-6 flex-grow">
        @yield('content')
    </main>

    <script src="{{ asset('js/global-validation.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('mobile-menu-toggle');
            const closeBtn = document.getElementById('mobile-menu-close');
            const sidebar = document.getElementById('mobile-sidebar');
            const backdrop = document.getElementById('mobile-sidebar-backdrop');

            function openSidebar() {
                if (sidebar && backdrop) {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                }
            }

            function closeSidebar() {
                if (sidebar && backdrop) {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                }
            }

            if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);
        });
    </script>
    @stack('scripts')
</body>
</html>
