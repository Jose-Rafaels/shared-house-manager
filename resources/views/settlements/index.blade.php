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
        <section class="mt-6 rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-lg font-semibold">{{ __('Outstanding Balances') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-5 py-3 font-medium">{{ __('From (debtor)') }}</th>
                            <th class="px-5 py-3 font-medium">{{ __('To (creditor)') }}</th>
                            <th class="px-5 py-3 font-medium">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($outstandingBalances as $entry)
                            <tr class="border-t border-slate-100">
                                <td class="px-5 py-3 text-slate-600">{{ $entry->debtor_name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $entry->creditor_name }}</td>
                                <td class="px-5 py-3 text-slate-900 font-medium tabular-nums">Rp{{ number_format($entry->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Settlements Table --}}
    <section class="mt-6 rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
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
                        <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $settlement->fromMember->name }} → {{ $settlement->toMember->name }}</td>
                            <td class="px-5 py-3 text-slate-900 font-medium tabular-nums">Rp{{ number_format($settlement->amount) }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $settlement->settlement_date->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ \Illuminate\Support\Str::limit($settlement->note, 60) }}</td>
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('settlements.destroy', $settlement) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" aria-label="{{ __('Delete') }}" class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] rounded-lg text-slate-400 transition hover:text-rose-600 hover:bg-rose-50 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300 focus-visible:ring-offset-1">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
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
                <x-floating-field name="from_member_id" label="From (debtor)" as="select">
                    <option value="">—</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" {{ old('from_member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                    @endforeach
                </x-floating-field>

                <x-floating-field name="to_member_id" label="To (creditor)" as="select">
                    <option value="">—</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" {{ old('to_member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                    @endforeach
                </x-floating-field>

                <x-floating-field name="amount" label="Amount" type="number" inputmode="numeric" :value="old('amount')" />

                <x-floating-field name="settlement_date" label="Date" type="date" :value="old('settlement_date')" />

                <x-floating-field name="note" label="Note (optional)" as="textarea" :value="old('note')" />
                <button class="w-full inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Record Settlement') }}</button>
            </form>
        </div>
    </dialog>

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