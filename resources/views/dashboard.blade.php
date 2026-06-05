@extends('layouts.app')

@section('content')
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm"><div class="text-sm text-slate-500">Current Month Bills</div><div class="mt-2 text-3xl font-bold">{{ $currentBills->count() }}</div></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm"><div class="text-sm text-slate-500">Unpaid Bill Summary</div><div class="mt-2 text-3xl font-bold">Rp{{ number_format($unpaidTotal) }}</div></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm"><div class="text-sm text-slate-500">Cash Fund Balance</div><div class="mt-2 text-3xl font-bold">Rp{{ number_format($cashBalance) }}</div></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm"><div class="text-sm text-slate-500">Shopping Pending</div><div class="mt-2 text-3xl font-bold">{{ $shoppingPending }}</div></div>
    </div>
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Outstanding Debts</h2>
            <div class="mt-4 space-y-3">
                @forelse ($outstandingDebts as $entry)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3"><span>Housemate #{{ $entry->debtor_housemate_id }} owes Housemate #{{ $entry->creditor_housemate_id }}</span><strong>Rp{{ number_format($entry->amount) }}</strong></div>
                @empty
                    <p class="text-sm text-slate-500">No outstanding debts.</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Current Chores</h2>
            <div class="mt-4 space-y-3">
                @forelse ($currentChores as $assignment)
                    <div class="rounded-xl bg-slate-50 px-4 py-3"><div class="font-medium">{{ $assignment->chore->name }}</div><div class="text-sm text-slate-500">{{ $assignment->housemate->name }} for week of {{ $assignment->assigned_for_date->format('d M Y') }}</div></div>
                @empty
                    <p class="text-sm text-slate-500">No chores assigned yet.</p>
                @endforelse
            </div>
        </section>
    </div>
    <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold">Recent Activity</h2>
        <div class="mt-4 space-y-3">
            @forelse ($recentActivities as $activity)
                <div class="rounded-xl bg-slate-50 px-4 py-3"><div class="font-medium">{{ $activity->description }}</div><div class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</div></div>
            @empty
                <p class="text-sm text-slate-500">No activity yet.</p>
            @endforelse
        </div>
    </section>
@endsection
