@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Dashboard') }}</span>
@endsection

@section('content')
    <h2 class="text-3xl font-bold tracking-tight">{{ __('Dashboard') }}</h2>
    <p class="mt-1 text-slate-500">{{ __('Overview of your shared house at a glance.') }}</p>

    <form method="GET" action="{{ route('dashboard') }}" class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-5 shadow-sm">
        <div>
            <label for="month" class="block text-sm text-slate-500">{{ __('Month') }}</label>
            <select id="month" name="month" class="mt-1 border border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                <option value="all" {{ ($selectedMonth ?? '') === 'all' ? 'selected' : '' }}>{{ __('All Months') }}</option>
                @foreach ($months as $ym)
                    <option value="{{ $ym }}" {{ ($selectedMonth ?? '') === $ym ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y') }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="category_id" class="block text-sm text-slate-500">{{ __('Category') }}</label>
            <select id="category_id" name="category_id" class="mt-1 border border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                <option value="">{{ __('All Categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ (string) ($selectedCategoryId ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="inline-flex items-center justify-center bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] rounded-lg min-h-[44px]">{{ __('Filter') }}</button>
        </div>
    </form>

    <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">
                @if (($selectedMonth ?? '') === 'all')
                    {{ __('Expenses') }} ({{ __('All Months') }})
                @else
                    {{ __('Expenses') }} — {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y') }}
                @endif
            </div>
            <div class="mt-2 text-3xl font-bold">{{ $currentExpenses->count() }}</div>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Expenses Total') }}</div>
            <div class="mt-2 text-3xl font-bold">Rp{{ number_format($expensesTotal) }}</div>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Shopping Pending') }}</div>
            <div class="mt-2 text-3xl font-bold">{{ $shoppingPending }}</div>
        </div>
    </div>

    <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm">
        <h3 class="text-lg font-semibold">{{ __('Outstanding Debts') }}</h3>
        <div class="mt-4 space-y-3">
            @forelse ($outstandingDebts as $entry)
                <div class="flex items-center justify-between bg-slate-50 px-4 py-3">
                    <span>{{ $entry->debtor_name }} → {{ $entry->creditor_name }}</span>
                    <strong>Rp{{ number_format($entry->amount) }}</strong>
                </div>
            @empty
                <div class="py-6 text-center">
                    <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">{{ __('No outstanding debts.') }}</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm">
        <h3 class="text-lg font-semibold">{{ __('Category Usage') }}</h3>
        <div class="mt-4 space-y-3">
            @forelse ($categoryBreakdown as $row)
                <div class="flex items-center justify-between bg-slate-50 px-4 py-3">
                    <span>
                        {{ $row->category?->name ?? __('No category') }}
                        <span class="text-xs text-slate-400">· {{ $row->count }}x</span>
                    </span>
                    <strong>Rp{{ number_format((int) $row->total) }}</strong>
                </div>
            @empty
                <div class="py-6 text-center">
                    <p class="mt-2 text-sm text-slate-500">{{ __('No expenses in this period.') }}</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm">
        <h3 class="text-lg font-semibold">{{ __('Recent Activity') }}</h3>
        <div class="mt-4 space-y-3">
            @forelse ($recentActivities as $activity)
                <div class="bg-slate-50 px-4 py-3">
                    <div class="font-medium">{{ $activity->description }}</div>
                    <div class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</div>
                </div>
            @empty
                <div class="py-6 text-center">
                    <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">{{ __('No activity yet.') }}</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
