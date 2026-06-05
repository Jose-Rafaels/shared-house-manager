@extends('layouts.app')

@section('content')
    <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm"><div class="text-sm text-slate-500">Current Balance</div><div class="mt-2 text-3xl font-bold">Rp{{ number_format($balance) }}</div></div>
    <div class="grid gap-6 lg:grid-cols-[420px_1fr]">
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Add Entry</h2>
            <form method="POST" action="{{ route('cash-fund.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3">@csrf <select name="type" class="w-full rounded-xl border-slate-300"><option value="contribution">Contribution</option><option value="expense">Expense</option></select> <select name="housemate_id" class="w-full rounded-xl border-slate-300"><option value="">No housemate</option>@foreach ($housemates as $housemate)<option value="{{ $housemate->id }}">{{ $housemate->name }}</option>@endforeach</select> <input name="title" class="w-full rounded-xl border-slate-300" placeholder="Water gallon"> <input type="number" name="amount" class="w-full rounded-xl border-slate-300" placeholder="Amount"> <input type="date" name="entry_date" class="w-full rounded-xl border-slate-300"> <input type="file" name="receipt" class="w-full rounded-xl border-slate-300"> <textarea name="notes" class="w-full rounded-xl border-slate-300" placeholder="Notes"></textarea> <button class="rounded-xl bg-slate-900 px-4 py-2 text-white">Save Entry</button></form>
        </section>
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Entries</h2>
            <div class="mt-4 space-y-3">@foreach ($entries as $entry)<div class="rounded-xl border border-slate-200 p-4"><div class="flex items-center justify-between"><div class="font-medium">{{ $entry->title }}</div><strong>Rp{{ number_format($entry->amount) }}</strong></div><div class="text-sm text-slate-500">{{ ucfirst($entry->type) }} • {{ $entry->entry_date->format('d M Y') }} @if($entry->housemate) • {{ $entry->housemate->name }} @endif</div></div>@endforeach</div>
        </section>
    </div>
@endsection
