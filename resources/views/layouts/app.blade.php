<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('Shared House Manager') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-screen overflow-hidden bg-slate-50">
    {{-- Sidebar (fixed, full-height) --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-white transform transition-transform duration-300 ease-out lg:translate-x-0 -translate-x-full flex flex-col" aria-label="{{ __('Main navigation') }}">
        {{-- Logo/Brand --}}
        <div class="flex flex-col items-start gap-1 px-6 py-6 border-b border-slate-800">
            <h1 class="text-xl font-bold">{{ __('Shared House Manager') }}</h1>
            <p class="text-sm text-slate-400">{{ __('Expenses, debts, chores, shopping, and shared cash') }}</p>
        </div>

        {{-- Navigation Links --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" aria-label="{{ __('Navigation') }}">
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white border-l-4 border-sky-400' : '' }}"
               aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>{{ __('Dashboard') }}</span>
            </a>

            {{-- Members --}}
            <a href="{{ route('members.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('members.*') ? 'bg-slate-800 text-white border-l-4 border-sky-400' : '' }}"
               aria-current="{{ request()->routeIs('members.*') ? 'page' : 'false' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>{{ __('Members') }}</span>
            </a>

            {{-- Finances Section --}}
            <div class="pt-2">
                <span class="block px-3 py-1 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Finances') }}</span>

                {{-- Expenses --}}
                <a href="{{ route('expenses.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('expenses.*') ? 'bg-slate-800 text-white border-l-4 border-sky-400' : '' }}"
                   aria-current="{{ request()->routeIs('expenses.*') ? 'page' : 'false' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>{{ __('Expenses') }}</span>
                </a>

                {{-- Settlements --}}
                <a href="{{ route('settlements.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('settlements.*') ? 'bg-slate-800 text-white border-l-4 border-sky-400' : '' }}"
                   aria-current="{{ request()->routeIs('settlements.*') ? 'page' : 'false' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>{{ __('Settlements') }}</span>
                </a>
            </div>

            {{-- Chores --}}
            <a href="{{ route('chores.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('chores.*') ? 'bg-slate-800 text-white border-l-4 border-sky-400' : '' }}"
               aria-current="{{ request()->routeIs('chores.*') ? 'page' : 'false' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>{{ __('Chores') }}</span>
            </a>

            {{-- Shopping --}}
            <a href="{{ route('shopping.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('shopping.*') ? 'bg-slate-800 text-white border-l-4 border-sky-400' : '' }}"
               aria-current="{{ request()->routeIs('shopping.*') ? 'page' : 'false' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>{{ __('Shopping') }}</span>
            </a>
        </nav>

        {{-- Mobile close button (hidden on desktop) --}}
        <button id="sidebar-close" class="lg:hidden flex items-center gap-2 px-6 py-4 text-slate-400 hover:text-white transition-colors" aria-label="{{ __('Close navigation') }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ __('Close') }}</span>
        </button>
    </aside>

    {{-- Backdrop for mobile --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden transition-opacity duration-200" aria-hidden="true"></div>

    {{-- Main wrapper --}}
    <div class="flex-1 flex flex-col lg:pl-64 min-h-screen">
        {{-- Mobile top bar (hamburger + page title) --}}
        <header class="sticky top-0 z-20 bg-white border-b border-slate-200 lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <button id="sidebar-toggle" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition min-h-[44px] min-w-[44px]" aria-label="{{ __('Open navigation') }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h2 class="text-lg font-semibold text-slate-900">{{ $title ?? __('Shared House Manager') }}</h2>
                <div class="w-10"></div> {{-- Spacer for alignment --}}
            </div>
        </header>

        {{-- Breadcrumbs --}}
        @hasSection('breadcrumbs')
        <nav class="px-6 py-3 bg-white border-b border-slate-200" aria-label="{{ __('Breadcrumb') }}">
            <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-500">
                <li>
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-700 transition">{{ __('Dashboard') }}</a>
                </li>
                @yield('breadcrumbs')
            </ol>
        </nav>
        @endif

        {{-- Main content --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{-- Status banner --}}
            @if (session('status'))
                <div class="mb-6 bg-emerald-100 px-4 py-3 text-emerald-800">{{ __(session('status')) }}</div>
            @endif

            {{-- Global error summary (backup; per-field errors shown inline) --}}
            @if ($errors->any())
                <div class="mb-6 bg-rose-100 px-4 py-3 text-rose-800">
                    <p class="font-medium">{{ __('Please fix the errors below.') }}</p>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Sidebar toggle script --}}
    <script>
        (function() {
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebar-backdrop');
            var toggleBtn = document.getElementById('sidebar-toggle');
            var closeBtn = document.getElementById('sidebar-close');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                document.body.style.overflow = '';
            }

            // Toggle button (mobile top bar)
            if (toggleBtn) {
                toggleBtn.addEventListener('click', openSidebar);
            }

            // Close button (inside sidebar)
            if (closeBtn) {
                closeBtn.addEventListener('click', closeSidebar);
            }

            // Backdrop click
            if (backdrop) {
                backdrop.addEventListener('click', closeSidebar);
            }

            // Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
                    closeSidebar();
                }
            });

            // Close sidebar on link click (mobile)
            var sidebarLinks = sidebar.querySelectorAll('a');
            sidebarLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }
                });
            });
        })();
    </script>
</body>
</html>