@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Dashboard') }}</span>
@endsection

@section('content')
    <h2 class="text-3xl font-bold tracking-tight">{{ __('Dashboard') }}</h2>
    <p class="mt-1 text-slate-500">{{ __('Overview of your shared house at a glance.') }}</p>

    <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Current Month Expenses') }}</div>
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

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm">
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

        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Current Chores') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse ($currentChores as $assignment)
                    <div class="bg-slate-50 px-4 py-3">
                        <div class="font-medium">{{ $assignment->chore->name }}</div>
                        <div class="text-sm text-slate-500">{{ __(':name for week of :date', ['name' => $assignment->member->name, 'date' => $assignment->assigned_for_date->translatedFormat('d M Y')]) }}</div>
                    </div>
                @empty
                    <div class="py-6 text-center">
                        <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-500">{{ __('No chores assigned yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

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