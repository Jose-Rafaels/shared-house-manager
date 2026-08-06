@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Expenses') }}</span>
@endsection

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ __('Expenses') }}</h2>
            <p class="mt-1 text-slate-500">{{ __('Track shared expenses and see who owes what.') }}</p>
        </div>
        <button onclick="document.getElementById('add-expense').showModal()" class="inline-flex items-center justify-center bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Add Expense') }}</button>
    </div>

    <section class="mt-6 rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('Description') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Payer') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Category') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Amount') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Date') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Split Between') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $expense->description }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $expense->payer->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $expense->category?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-900 font-medium tabular-nums">Rp{{ number_format($expense->amount) }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $expense->expense_date->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3 text-slate-600">
                                <ul class="space-y-1">
                                    @foreach ($expense->splits as $split)
                                        <li class="flex items-center gap-2">
                                            <span>{{ $split->member?->name ?? '—' }}</span>
                                            <span class="text-xs text-slate-400">Rp{{ number_format($split->amount_owed) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline">
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
                            <td colspan="7" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-slate-500">{{ __('No expenses yet.') }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ __('Click "Add Expense" to create one.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $expenses->links() }}

    {{-- Add Expense Dialog --}}
    <dialog id="add-expense" class="rounded-2xl p-0 shadow-xl w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">{{ __('Create Expense') }}</h3>
                <button type="button" onclick="document.getElementById('add-expense').close()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none" aria-label="{{ __('Close') }}">×</button>
            </div>
            <form method="POST" action="{{ route('expenses.store') }}" class="space-y-3" novalidate>
                @csrf
                <input type="hidden" name="dialog_id" value="add-expense">
                <x-floating-field name="description" label="Description" :value="old('description')" />

                <x-floating-field name="payer_id" label="Payer" as="select">
                    <option value="">—</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" {{ old('payer_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                    @endforeach
                </x-floating-field>

                <x-floating-field name="category_id" label="Category" as="select">
                    <option value="">{{ __('No category') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </x-floating-field>

                <x-floating-field name="amount" label="Amount" type="number" inputmode="numeric" :value="old('amount')" />

                <x-floating-field name="expense_date" label="Date" type="date" :value="old('expense_date')" />

                <x-floating-field name="notes" label="Notes" as="textarea" :value="old('notes')" />
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
                <button class="w-full inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Create Expense') }}</button>
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