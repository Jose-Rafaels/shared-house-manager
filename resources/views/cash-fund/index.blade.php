@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Cash Fund') }}</span>
@endsection

@section('content')
    <h2 class="text-3xl font-bold tracking-tight">{{ __('Cash Fund') }}</h2>
    <p class="mt-1 text-slate-500">{{ __('Track shared money contributions and expenses.') }}</p>

    {{-- Balance summary --}}
    <div class="mt-6 rounded-2xl bg-white p-5 shadow-sm">
        <div class="text-sm text-slate-500">{{ __('Current Balance') }}</div>
        <div class="mt-2 text-3xl font-bold">Rp{{ number_format($balance) }}</div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[420px_1fr]">
        {{-- Add entry form --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Add Entry') }}</h3>
            <form method="POST" action="{{ route('cash-fund.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3" novalidate>
                @csrf
                <div>
                    <select name="type" class="w-full border @error('type') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="contribution">{{ __('contribution') }}</option>
                        <option value="expense">{{ __('expense') }}</option>
                    </select>
                    @error('type')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <select name="member_id" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="">{{ __('No member') }}</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input name="title" class="w-full border @error('title') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Water gallon') }}" value="{{ old('title') }}">
                    @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="number" name="amount" class="w-full border @error('amount') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Amount') }}" value="{{ old('amount') }}">
                    @error('amount')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="date" name="entry_date" class="w-full border @error('entry_date') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" value="{{ old('entry_date') }}">
                    @error('entry_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="file" name="receipt" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                </div>
                <div>
                    <textarea name="notes" class="w-full border @error('notes') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Notes') }}">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button class="inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px]">{{ __('Save Entry') }}</button>
            </form>
        </section>

        {{-- Entries list --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Entries') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse ($entries as $entry)
                    <div class="border border-slate-200 p-4">
                        <div class="flex items-center justify-between">
                            <div class="font-medium">{{ $entry->title }}</div>
                            <strong>Rp{{ number_format($entry->amount) }}</strong>
                        </div>
                        <div class="text-sm text-slate-500">
                            {{ __($entry->type) }} &bull; {{ $entry->entry_date->translatedFormat('d M Y') }}
                            @if($entry->member) &bull; {{ $entry->member->name }} @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-3 text-sm font-medium text-slate-500">{{ __('No entries yet.') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Add one using the form on the left.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection