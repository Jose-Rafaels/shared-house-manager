@extends('layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[420px_1fr]">
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Create Chore</h2>
            <form method="POST" action="{{ route('chores.store') }}" class="mt-4 space-y-3">@csrf <input name="name" class="w-full rounded-xl border-slate-300" placeholder="Clean bathroom"> <textarea name="description" class="w-full rounded-xl border-slate-300" placeholder="Description"></textarea> <input type="date" name="rotation_start_date" class="w-full rounded-xl border-slate-300"> <div class="space-y-2">@foreach ($housemates as $housemate)<label class="flex items-center gap-2"><input type="checkbox" name="housemate_ids[]" value="{{ $housemate->id }}"> {{ $housemate->name }}</label>@endforeach</div> <button class="rounded-xl bg-slate-900 px-4 py-2 text-white">Create Chore</button></form>
        </section>
        <section class="space-y-5">
            @foreach ($chores as $chore)
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">{{ $chore->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $chore->description }}</p>
                    <div class="mt-4"><h3 class="font-medium">Upcoming Assignments</h3><div class="mt-2 space-y-2">@foreach ($chore->assignments->take(4) as $assignment)<div class="flex items-center justify-between rounded-xl border border-slate-200 p-3"><span>{{ $assignment->housemate->name }} for {{ $assignment->assigned_for_date->format('d M Y') }}</span>@if (!$assignment->completed_at)<form method="POST" action="{{ route('chores.assignments.complete', $assignment) }}">@csrf @method('PATCH') <button class="text-sm text-emerald-700">Mark complete</button></form>@else <span class="text-sm text-slate-500">Done</span>@endif</div>@endforeach</div></div>
                </div>
            @endforeach
        </section>
    </div>
@endsection
