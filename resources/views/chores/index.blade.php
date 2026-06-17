@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Chores') }}</span>
@endsection

@section('content')
    <h2 class="text-3xl font-bold tracking-tight">{{ __('Chores') }}</h2>
    <p class="mt-1 text-slate-500">{{ __('Assign and track household tasks.') }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[420px_1fr]">
        {{-- Create chore form --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Create Chore') }}</h3>
            <form method="POST" action="{{ route('chores.store') }}" class="mt-4 space-y-3" novalidate>
                @csrf
                <div>
                    <input name="name" class="w-full border @error('name') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Clean bathroom') }}" value="{{ old('name') }}">
                    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <textarea name="description" class="w-full border @error('description') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Description') }}">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="date" name="rotation_start_date" class="w-full border @error('rotation_start_date') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" value="{{ old('rotation_start_date') }}">
                    @error('rotation_start_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    @foreach ($members as $member)
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="rounded border-slate-300">
                            {{ $member->name }}
                        </label>
                    @endforeach
                </div>
                <button class="inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px]">{{ __('Create Chore') }}</button>
            </form>
        </section>

        {{-- Chore list — Principle #5: fixed heading hierarchy --}}
        <section class="space-y-5">
            @forelse ($chores as $chore)
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-semibold">{{ $chore->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $chore->description }}</p>
                    <div class="mt-4">
                        <h4 class="font-medium">{{ __('Upcoming Assignments') }}</h4>
                        <div class="mt-2 space-y-2">
                            @foreach ($chore->assignments->take(4) as $assignment)
                                <div class="flex items-center justify-between border border-slate-200 p-3">
                                    <span>{{ __(':name for :date', ['name' => $assignment->member->name, 'date' => $assignment->assigned_for_date->translatedFormat('d M Y')]) }}</span>
                                    @if (!$assignment->completed_at)
                                        <form method="POST" action="{{ route('chores.assignments.complete', $assignment) }}">
                                            @csrf @method('PATCH')
                                            <button class="inline-flex items-center min-h-[44px] text-sm font-medium text-emerald-700 transition hover:text-emerald-800 active:scale-[0.97]">{{ __('Mark complete') }}</button>
                                        </form>
                                    @else
                                        <span class="text-sm text-slate-500">{{ __('Done') }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white p-8 shadow-sm text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-slate-500">{{ __('No chores yet.') }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Create one using the form on the left.') }}</p>
                </div>
            @endforelse
        </section>
    </div>
@endsection
