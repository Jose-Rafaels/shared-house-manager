@extends('layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[420px_1fr]">
        <section class="space-y-6">
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">Create Shared Expense</h2>
                <form method="POST" action="{{ route('debts.expenses.store') }}" class="mt-4 space-y-3">@csrf <input name="title" class="w-full rounded-xl border-slate-300" placeholder="Groceries run"> <select name="payer_housemate_id" class="w-full rounded-xl border-slate-300">@foreach ($housemates as $housemate)<option value="{{ $housemate->id }}">{{ $housemate->name }}</option>@endforeach</select> <input type="number" name="amount" class="w-full rounded-xl border-slate-300" placeholder="Amount"> <input type="date" name="expense_date" class="w-full rounded-xl border-slate-300"> <textarea name="notes" class="w-full rounded-xl border-slate-300" placeholder="Notes"></textarea> <div class="space-y-2">@foreach ($housemates as $housemate)<label class="flex items-center gap-2"><input type="checkbox" name="housemate_ids[]" value="{{ $housemate->id }}"> {{ $housemate->name }}</label>@endforeach</div> <button class="rounded-xl bg-slate-900 px-4 py-2 text-white">Create Expense</button></form>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">Record Settlement</h2>
                <form method="POST" action="{{ route('debts.settlements.store') }}" class="mt-4 space-y-3">@csrf <select name="debtor_housemate_id" class="w-full rounded-xl border-slate-300">@foreach ($housemates as $housemate)<option value="{{ $housemate->id }}">{{ $housemate->name }}</option>@endforeach</select> <select name="creditor_housemate_id" class="w-full rounded-xl border-slate-300">@foreach ($housemates as $housemate)<option value="{{ $housemate->id }}">{{ $housemate->name }}</option>@endforeach</select> <input type="number" name="amount" class="w-full rounded-xl border-slate-300" placeholder="Amount"> <input type="date" name="settled_on" class="w-full rounded-xl border-slate-300"> <textarea name="notes" class="w-full rounded-xl border-slate-300" placeholder="Notes"></textarea> <button class="rounded-xl bg-slate-900 px-4 py-2 text-white">Record Settlement</button></form>
            </div>
        </section>
        <section class="space-y-6">
            <div class="rounded-2xl bg-white p-5 shadow-sm"><h2 class="text-lg font-semibold">Outstanding Balances</h2><div class="mt-4 space-y-3">@forelse ($outstandingBalances as $balance)<div class="rounded-xl border border-slate-200 p-4">Housemate #{{ $balance->debtor_housemate_id }} owes Housemate #{{ $balance->creditor_housemate_id }} <strong>Rp{{ number_format($balance->amount) }}</strong></div>@empty <p class="text-sm text-slate-500">No outstanding balances.</p>@endforelse</div></div>
            <div class="rounded-2xl bg-white p-5 shadow-sm"><h2 class="text-lg font-semibold">Shared Expenses</h2><div class="mt-4 space-y-4">@foreach ($sharedExpenses as $expense)<div class="rounded-xl border border-slate-200 p-4"><div class="flex items-center justify-between"><div class="font-medium">{{ $expense->title }}</div><strong>Rp{{ number_format($expense->amount) }}</strong></div><div class="text-sm text-slate-500">Paid by {{ $expense->payer->name }} on {{ $expense->expense_date->format('d M Y') }}</div></div>@endforeach</div></div>
        </section>
    </div>
@endsection
