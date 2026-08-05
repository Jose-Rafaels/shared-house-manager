@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Settlements') }}</span>
@endsection

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ __('Settlements') }}</h2>
            <p class="mt-1 text-slate-500">{{ __('Record debt settlements and view outstanding balances.') }}</p>
        </div>
        <button onclick="document.getElementById('add-settlement').showModal()" class="inline-flex items-center justify-center bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Record Settlement') }}</button>
    </div>

    {{-- Outstanding Balances --}}
    @if ($outstandingBalances->count())
        <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Outstanding Balances') }}</h3>
            <div class="mt-4 space-y-3">
                @foreach ($outstandingBalances as $entry)
                    <div class="flex items-center justify-between bg-slate-50 px-4 py-3 rounded-lg">
                        <span>{{ $entry->debtor_name }} → {{ $entry->creditor_name }}</span>
                        <strong>Rp{{ number_format($entry->amount) }}</strong>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Settlements Table --}}
    <section class="mt-6 rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 hidden md:table-header-group">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('From → To') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Amount') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Date') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Note') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settlements as $settlement)
                        <tr class="hidden md:table-row border-t border-slate-100 hover:bg-slate-50/50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $settlement->fromMember->name }} → {{ $settlement->toMember->name }}</td>
                            <td class="px-5 py-3 text-slate-900 font-medium">Rp{{ number_format($settlement->amount) }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $settlement->settlement_date->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ \Illuminate\Support\Str::limit($settlement->note, 60) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <button onclick="document.getElementById('edit-settlement-{{ $settlement->id }}').showModal()" class="text-sm font-medium text-slate-700 hover:text-slate-900">{{ __('Edit') }}</button>
                                    <form method="POST" action="{{ route('settlements.destroy', $settlement) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-sm font-medium text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Mobile card --}}
                        <tr class="md:hidden">
                            <td colspan="5" class="px-0 py-2">
                                <div class="border border-slate-200 rounded-xl p-4 bg-white shadow-sm space-y-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-semibold text-slate-900 leading-tight">{{ $settlement->fromMember->name }} → {{ $settlement->toMember->name }}</h3>
                                        <span class="text-lg font-bold text-slate-900 whitespace-nowrap">Rp{{ number_format($settlement->amount) }}</span>
                                    </div>
                                    <div class="text-sm text-slate-500">{{ $settlement->settlement_date->translatedFormat('d M Y') }}</div>
                                    @if ($settlement->note)
                                        <p class="text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($settlement->note, 120) }}</p>
                                    @endif
                                    <div class="flex gap-2">
                                        <button onclick="document.getElementById('edit-settlement-{{ $settlement->id }}').showModal()" class="flex-1 inline-flex items-center justify-center min-h-[44px] px-3 text-sm font-medium text-slate-700 hover:text-slate-900 active:scale-[0.97] border border-slate-300 rounded-lg">{{ __('Edit') }}</button>
                                        <form method="POST" action="{{ route('settlements.destroy', $settlement) }}" class="flex-1">
                                            @csrf @method('DELETE')
                                            <button class="w-full inline-flex items-center justify-center min-h-[44px] px-3 text-sm font-medium text-rose-600 hover:text-rose-800 active:scale-[0.97] border border-rose-200 rounded-lg">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="hidden md:table-row">
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-slate-500">{{ __('No settlements yet.') }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ __('Click "Record Settlement" to add one.') }}</p>
                            </td>
                        </tr>
                        <tr class="md:hidden">
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-slate-500">{{ __('No settlements yet.') }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ __('Click "Record Settlement" to add one.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $settlements->links() }}

    {{-- Add Settlement Dialog --}}
    <dialog id="add-settlement" class="rounded-2xl p-0 shadow-xl w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">{{ __('Record Settlement') }}</h3>
                <button type="button" onclick="document.getElementById('add-settlement').close()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none" aria-label="{{ __('Close') }}">×</button>
            </div>
            <form method="POST" action="{{ route('settlements.store') }}" class="space-y-3" novalidate>
                @csrf
                <input type="hidden" name="dialog_id" value="add-settlement">
                <div>
                    <select name="from_member_id" class="w-full border @error('from_member_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                        <option value="">{{ __('From (debtor)') }}</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ old('from_member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @error('from_member_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <select name="to_member_id" class="w-full border @error('to_member_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                        <option value="">{{ __('To (creditor)') }}</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ old('to_member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @error('to_member_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="number" inputmode="numeric" name="amount" class="w-full border @error('amount') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Amount') }}" value="{{ old('amount') }}">
                    @error('amount')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="date" name="settlement_date" class="w-full border @error('settlement_date') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" value="{{ old('settlement_date') }}">
                    @error('settlement_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <textarea name="note" class="w-full border @error('note') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Note (optional)') }}" rows="3">{{ old('note') }}</textarea>
                    @error('note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button class="w-full inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Record Settlement') }}</button>
            </form>
        </div>
    </dialog>

    {{-- Edit Settlement Dialogs --}}
    @foreach ($settlements as $settlement)
        <dialog id="edit-settlement-{{ $settlement->id }}" class="rounded-2xl p-0 shadow-xl w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">{{ __('Edit Settlement') }}</h3>
                    <button type="button" onclick="document.getElementById('edit-settlement-{{ $settlement->id }}').close()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none" aria-label="{{ __('Close') }}">×</button>
                </div>
                <form method="POST" action="{{ route('settlements.update', $settlement) }}" class="space-y-3" novalidate>
                    @csrf @method('PUT')
                    <input type="hidden" name="dialog_id" value="edit-settlement-{{ $settlement->id }}">
                    <div>
                        <select name="from_member_id" class="w-full border @error('from_member_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" {{ old('from_member_id', $settlement->from_member_id) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        @error('from_member_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <select name="to_member_id" class="w-full border @error('to_member_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" {{ old('to_member_id', $settlement->to_member_id) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        @error('to_member_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <input type="number" inputmode="numeric" name="amount" value="{{ old('amount', $settlement->amount) }}" class="w-full border @error('amount') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Amount') }}">
                        @error('amount')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <input type="date" name="settlement_date" value="{{ old('settlement_date', $settlement->settlement_date->format('Y-m-d')) }}" class="w-full border @error('settlement_date') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                        @error('settlement_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <textarea name="note" class="w-full border @error('note') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Note (optional)') }}" rows="3">{{ old('note', $settlement->note) }}</textarea>
                        @error('note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button class="w-full inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Update') }}</button>
                </form>
            </div>
        </dialog>
    @endforeach

    {{-- Auto-reopen dialog on validation error --}}
    @once
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var dialogId = @json(old('dialog_id'));
                var dialog = dialogId && document.getElementById(dialogId);
                if (dialog && typeof dialog.showModal === 'function') {
                    dialog.showModal();
                }
            });
        </script>
    @endonce
@endsection