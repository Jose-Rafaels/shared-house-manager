<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Shared House Manager' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen">
        <header class="bg-slate-900 text-white">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-6 py-5">
                <div>
                    <h1 class="text-2xl font-bold">Shared House Manager</h1>
                    <p class="text-sm text-slate-300">Bills, debts, chores, shopping, and shared cash in one place.</p>
                </div>
                <nav class="flex flex-wrap gap-3 text-sm">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <a href="{{ route('housemates.index') }}">Housemates</a>
                    <a href="{{ route('bills.index') }}">Bills</a>
                    <a href="{{ route('cash-fund.index') }}">Cash Fund</a>
                    <a href="{{ route('debts.index') }}">Debts</a>
                    <a href="{{ route('chores.index') }}">Chores</a>
                    <a href="{{ route('shopping.index') }}">Shopping</a>
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-6 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-xl bg-emerald-100 px-4 py-3 text-emerald-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-rose-100 px-4 py-3 text-rose-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
