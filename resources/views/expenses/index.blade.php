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
                <thead class="bg-slate-50 text-slate-600 hidden md:table-header-group">
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
                        <tr class="hidden md:table-row border-t border-slate-100 hover:bg-slate-50/50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $expense->description }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $expense->payer->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $expense->category?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-900 font-medium">Rp{{ number_format($expense->amount) }}</td>
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
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center justify-center min-h-[44px] px-3 text-sm font-medium text-rose-600 hover:text-rose-800 active:scale-[0.97]">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>

                        {{-- Mobile card --}}
                        <tr class="md:hidden">
                            <td colspan="7" class="px-0 py-2">
                                <div class="border border-slate-200 rounded-xl p-4 bg-white shadow-sm space-y-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-semibold text-slate-900 leading-tight">{{ $expense->description }}</h3>
                                        <span class="text-lg font-bold text-slate-900 whitespace-nowrap">Rp{{ number_format($expense->amount) }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                                        <span><span class="text-slate-400">{{ __('Payer') }}:</span> {{ $expense->payer->name }}</span>
                                        @if ($expense->category)
                                            <span><span class="text-slate-400">{{ __('Category') }}:</span> {{ $expense->category->name }}</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-slate-500">{{ $expense->expense_date->translatedFormat('d M Y') }}</div>
                                    <ul class="space-y-1 text-sm">
                                        @foreach ($expense->splits as $split)
                                            <li class="flex items-center justify-between gap-2">
                                                <span class="text-slate-700">{{ $split->member?->name ?? '—' }}</span>
                                                <span class="text-xs text-slate-400">Rp{{ number_format($split->amount_owed) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}">
                                        @csrf @method('DELETE')
                                        <button class="w-full inline-flex items-center justify-center min-h-[44px] px-3 text-sm font-medium text-rose-600 hover:text-rose-800 active:scale-[0.97] border border-rose-200 rounded-lg">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="hidden md:table-row">
                            <td colspan="7" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-slate-500">{{ __('No expenses yet.') }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ __('Click "Add Expense" to create one.') }}</p>
                            </td>
                        </tr>
                        <tr class="md:hidden">
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
                <div>
                    <input name="description" class="w-full border @error('description') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Description') }}" value="{{ old('description') }}">
                    @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <select name="payer_id" class="w-full border @error('payer_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                        <option value="">{{ __('Select payer') }}</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ old('payer_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @error('payer_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <select name="category_id" class="w-full border @error('category_id') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                        <option value="">{{ __('No category') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="number" inputmode="numeric" name="amount" class="w-full border @error('amount') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Amount') }}" value="{{ old('amount') }}">
                    @error('amount')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="date" name="expense_date" class="w-full border @error('expense_date') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" value="{{ old('expense_date') }}">
                    @error('expense_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <textarea name="notes" class="w-full border @error('notes') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Notes') }}" rows="3">{{ old('notes') }}</textarea>
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