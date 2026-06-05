@extends('layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Add Housemate</h2>
            <form method="POST" action="{{ route('housemates.store') }}" class="mt-4 space-y-4">@csrf <input name="name" class="w-full rounded-xl border-slate-300" placeholder="Name" value="{{ old('name') }}"> <button class="rounded-xl bg-slate-900 px-4 py-2 text-white">Save</button></form>
        </section>
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Housemates</h2>
            <div class="mt-4 space-y-3">
                @foreach ($housemates as $housemate)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <form method="POST" action="{{ route('housemates.update', $housemate) }}" class="flex flex-wrap items-center gap-3">@csrf @method('PUT') <input name="name" value="{{ $housemate->name }}" class="flex-1 rounded-xl border-slate-300"> <button class="rounded-xl bg-slate-900 px-3 py-2 text-white">Update</button></form>
                        <form method="POST" action="{{ route('housemates.archive', $housemate) }}" class="mt-3">@csrf @method('PATCH') <button class="text-sm text-rose-600" {{ $housemate->archived_at ? 'disabled' : '' }}>{{ $housemate->archived_at ? 'Archived' : 'Archive' }}</button></form>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
