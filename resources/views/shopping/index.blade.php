@extends('layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[420px_1fr]">
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Add Shopping Item</h2>
            <form method="POST" action="{{ route('shopping.store') }}" class="mt-4 space-y-3">@csrf <input name="name" class="w-full rounded-xl border-slate-300" placeholder="Detergent"> <select name="priority" class="w-full rounded-xl border-slate-300"><option>High</option><option>Medium</option><option>Low</option></select> <select name="added_by_housemate_id" class="w-full rounded-xl border-slate-300"><option value="">No housemate</option>@foreach ($housemates as $housemate)<option value="{{ $housemate->id }}">{{ $housemate->name }}</option>@endforeach</select> <textarea name="notes" class="w-full rounded-xl border-slate-300" placeholder="Notes"></textarea> <button class="rounded-xl bg-slate-900 px-4 py-2 text-white">Add Item</button></form>
        </section>
        <section class="space-y-5">
            @foreach ($items as $item)
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><div class="font-semibold">{{ $item->name }}</div><span class="rounded-full bg-slate-100 px-3 py-1 text-sm">{{ $item->priority }}</span></div>
                    <p class="mt-2 text-sm text-slate-500">{{ $item->notes }}</p>
                    @if (!$item->purchased_at)
                        <form method="POST" action="{{ route('shopping.purchase.store', $item) }}" class="mt-4 grid gap-3 md:grid-cols-4">@csrf <select name="purchased_by_housemate_id" class="rounded-xl border-slate-300 md:col-span-2"><option value="">No housemate</option>@foreach ($housemates as $housemate)<option value="{{ $housemate->id }}">{{ $housemate->name }}</option>@endforeach</select> <input type="number" name="amount" class="rounded-xl border-slate-300" placeholder="Amount"> <input type="date" name="purchased_on" class="rounded-xl border-slate-300"> <textarea name="notes" class="rounded-xl border-slate-300 md:col-span-4" placeholder="Notes"></textarea> <button class="rounded-xl bg-slate-900 px-4 py-2 text-white md:col-span-4">Mark Purchased</button></form>
                    @else
                        <div class="mt-4 text-sm text-emerald-700">Purchased on {{ $item->purchased_at->format('d M Y') }}</div>
                    @endif
                </div>
            @endforeach
        </section>
    </div>
@endsection
