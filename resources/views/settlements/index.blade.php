@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Settlements') }}</span>
@endsection

@section('content')
    <h2 class="text-3xl font-bold tracking-tight">{{ __('Settlements') }}</h2>
    <p class="mt-1 text-slate-500">{{ __('Record debt settlements and view outstanding balances.') }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[420px_1fr]">
        {{-- Left column: settlement form --}}
        <section class="space-y-6">
            {{-- Record Settlement form --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold">{{ __('Record Settlement') }}</h3>
                <form method="POST" action="{{ route('settlements.store') }}" class="mt-4 space-y-3" novalidate>
                    @csrf
                    <div>
                        <select name="from_member_id" class="w-full border @error('from_member_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                            <option value="">{{ __('From (debtor)') }}</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" {{ old('from_member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        @error('from_member_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <select name="to_member_id" class="w-full border @error('to_member_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                            <option value="">{{ __('To (creditor)') }}</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" {{ old('to_member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        @error('to_member_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <input type="number" name="amount" class="w-full border @error('amount') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Amount') }}" value="{{ old('amount') }}">
                        @error('amount')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <input type="date" name="settlement_date" class="w-full border @error('settlement_date') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" value="{{ old('settlement_date') }}">
                        @error('settlement_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <textarea name="note" class="w-full border @error('note') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Note (optional)') }}">{{ old('note') }}</textarea>
                        @error('note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button class="inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px]">{{ __('Record Settlement') }}</button>
                </form>
            </div>

            {{-- Outstanding Balances --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold">{{ __('Outstanding Balances') }}</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($outstandingBalances as $entry)
                        <div class="flex items-center justify-between bg-slate-50 px-4 py-3">
                            <span>{{ $entry->debtor_name }} → {{ $entry->creditor_name }}</span>
                            <strong>Rp{{ number_format($entry->amount) }}</strong>
                        </div>
                    @empty
                        <div class="py-4 text-center">
                            <p class="text-sm text-slate-500">{{ __('No outstanding balances.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Right column: settlements history --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Settlements') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse ($settlements as $settlement)
                    <div class="border border-slate-200 p-4">
                        <form method="POST" action="{{ route('settlements.update', $settlement) }}" class="space-y-3" novalidate>
                            @csrf @method('PUT')
                            <div class="flex items-center justify-between">
                                <select name="from_member_id" class="border border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}" {{ old('from_member_id', $settlement->from_member_id) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-slate-500">→</span>
                                <select name="to_member_id" class="border border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}" {{ old('to_member_id', $settlement->to_member_id) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <input type="number" name="amount" value="{{ old('amount', $settlement->amount) }}" class="w-32 border border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                                <input type="date" name="settlement_date" value="{{ old('settlement_date', $settlement->settlement_date->format('Y-m-d')) }}" class="border border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                                <button class="inline-flex items-center justify-center bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px]">{{ __('Update') }}</button>
                            </div>
                            <textarea name="note" class="w-full border border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Note (optional)') }}">{{ old('note', $settlement->note) }}</textarea>
                        </form>
                        <form method="POST" action="{{ route('settlements.destroy', $settlement) }}" class="mt-2">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center min-h-[44px] text-sm font-medium text-rose-600 transition hover:text-rose-800 active:scale-[0.97]">{{ __('Delete') }}</button>
                        </form>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="mt-3 text-sm font-medium text-slate-500">{{ __('No settlements yet.') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Record one using the form on the left.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection