@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Expenses') }}</span>
@endsection

@section('content')
    <h2 class="text-3xl font-bold tracking-tight">{{ __('Expenses') }}</h2>
    <p class="mt-1 text-slate-500">{{ __('Track shared expenses and see who owes what.') }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[420px_1fr]">
        {{-- Create expense form --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Create Expense') }}</h3>
            <form method="POST" action="{{ route('expenses.store') }}" class="mt-4 space-y-3" novalidate>
                @csrf
                <div>
                    <input name="description" class="w-full border @error('description') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Description') }}" value="{{ old('description') }}">
                    @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <select name="payer_id" class="w-full border @error('payer_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="">{{ __('Select payer') }}</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ old('payer_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @error('payer_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <select name="category_id" class="w-full border @error('category_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="">{{ __('No category') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="number" name="amount" class="w-full border @error('amount') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Amount') }}" value="{{ old('amount') }}">
                    @error('amount')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="date" name="expense_date" class="w-full border @error('expense_date') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" value="{{ old('expense_date') }}">
                    @error('expense_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <textarea name="notes" class="w-full border @error('notes') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Notes') }}">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-slate-700">{{ __('Split between') }}</p>
                    @foreach ($members as $member)
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="rounded border-slate-300" {{ in_array($member->id, old('member_ids', [])) ? 'checked' : '' }}>
                            {{ $member->name }}
                        </label>
                    @endforeach
                    @error('member_ids')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button class="inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px]">{{ __('Create Expense') }}</button>
            </form>
        </section>

        {{-- Expense list --}}
        <section class="space-y-5">
            @forelse ($expenses as $expense)
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $expense->description }}</h3>
                            @if ($expense->category)
                                <span class="text-sm text-slate-500">{{ $expense->category->name }}</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold">Rp{{ number_format($expense->amount) }}</div>
                            <div class="text-sm text-slate-500">{{ $expense->expense_date->translatedFormat('d M Y') }}</div>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-slate-500">{{ __('Paid by :name', ['name' => $expense->payer->name]) }}</div>
                    @if ($expense->notes)
                        <p class="mt-1 text-sm text-slate-400">{{ $expense->notes }}</p>
                    @endif
                    <div class="mt-3 space-y-1">
                        @foreach ($expense->splits as $split)
                            <div class="flex items-center justify-between text-sm">
                                <span>{{ $split->member->name }}</span>
                                <span>Rp{{ number_format($split->amount_owed) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white p-8 shadow-sm text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-slate-500">{{ __('No expenses yet.') }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Create one using the form on the left.') }}</p>
                </div>
            @endforelse
        </section>
    </div>
@endsection