@extends('layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[420px_1fr]">
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Create Bill</h2>
            <form method="POST" action="{{ route('bills.store') }}" class="mt-4 space-y-3">
                @csrf
                <input name="title" class="w-full rounded-xl border-slate-300" placeholder="Internet Bill">
                <select name="type" class="w-full rounded-xl border-slate-300"><option>Electricity</option><option>Water</option><option>Internet</option><option>Gas</option><option>Custom</option></select>
                <input type="number" name="amount" class="w-full rounded-xl border-slate-300" placeholder="300000">
                <input type="date" name="billing_month" class="w-full rounded-xl border-slate-300">
                <input type="date" name="due_date" class="w-full rounded-xl border-slate-300">
                <textarea name="notes" class="w-full rounded-xl border-slate-300" placeholder="Notes"></textarea>
                <div class="space-y-2">@foreach ($housemates as $housemate)<label class="flex items-center gap-2"><input type="checkbox" name="housemate_ids[]" value="{{ $housemate->id }}"> {{ $housemate->name }}</label>@endforeach</div>
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-white">Create Bill</button>
            </form>
        </section>
        <section class="space-y-5">
            @foreach ($bills as $bill)
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><div><h2 class="text-lg font-semibold">{{ $bill->title }}</h2><p class="text-sm text-slate-500">{{ $bill->type }} • {{ $bill->billing_month->format('F Y') }}</p></div><strong>Rp{{ number_format($bill->amount) }}</strong></div>
                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                        <div><h3 class="font-medium">Participants</h3><ul class="mt-2 space-y-2 text-sm">@foreach ($bill->participants as $participant)<li>{{ $participant->housemate->name }} • Rp{{ number_format($participant->share_amount) }}</li>@endforeach</ul></div>
                        <div>
                            <h3 class="font-medium">Record Payment</h3>
                            <form method="POST" action="{{ route('bills.payments.store', $bill) }}" class="mt-2 space-y-2">@csrf <select name="housemate_id" class="w-full rounded-xl border-slate-300">@foreach ($bill->participants as $participant)<option value="{{ $participant->housemate_id }}">{{ $participant->housemate->name }}</option>@endforeach</select> <input type="number" name="amount" class="w-full rounded-xl border-slate-300" placeholder="Amount"> <input type="date" name="payment_date" class="w-full rounded-xl border-slate-300"> <button class="rounded-xl bg-slate-900 px-4 py-2 text-white">Mark Paid</button></form>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    </div>
@endsection
