@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Chores') }}</span>
@endsection

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ __('Chores') }}</h2>
            <p class="mt-1 text-slate-500">{{ __('Assign and track household tasks.') }}</p>
        </div>
        <button onclick="document.getElementById('add-chore').showModal()" class="inline-flex items-center justify-center bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Add Chore') }}</button>
    </div>

    <section class="mt-6 rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Assigned To') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Date Assigned') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($chores as $chore)
                        <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $chore->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $chore->assignedTo?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($chore->assigned_for_date)
                                    <span class="text-slate-600">{{ $chore->assigned_for_date->translatedFormat('d M Y') }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                                @php
                                    $nextAssignment = $chore->assignments->sortBy('assigned_for_date')->firstWhere('completed_at', null);
                                @endphp
                                @if ($nextAssignment)
                                    <form method="POST" action="{{ route('chores.assignments.complete', $nextAssignment) }}" class="inline ml-2">
                                        @csrf @method('PATCH')
                                        <button class="text-xs font-medium text-emerald-700 hover:text-emerald-800">{{ __('Complete') }}</button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <button onclick="document.getElementById('edit-chore-{{ $chore->id }}').showModal()" class="text-sm font-medium text-slate-700 hover:text-slate-900">{{ __('Edit') }}</button>
                                    <form method="POST" action="{{ route('chores.destroy', $chore) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-sm font-medium text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-slate-500">{{ __('No chores yet.') }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ __('Click "Add Chore" to create one.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Add Chore Dialog --}}
    <dialog id="add-chore" class="rounded-2xl p-0 shadow-xl w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">{{ __('Create Chore') }}</h3>
                <button type="button" onclick="document.getElementById('add-chore').close()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none" aria-label="{{ __('Close') }}">×</button>
            </div>
            <form method="POST" action="{{ route('chores.store') }}" class="space-y-3" novalidate>
                @csrf
                <input type="hidden" name="dialog_id" value="add-chore">
                <div>
                    <input name="name" class="w-full border @error('name') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Clean bathroom') }}" value="{{ old('name') }}">
                    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <textarea name="description" class="w-full border @error('description') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Description') }}" rows="3">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Date Assigned') }}</label>
                    <input type="date" name="assigned_for_date" value="{{ old('assigned_for_date', today()->toDateString()) }}" class="w-full border @error('assigned_for_date') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                    @error('assigned_for_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <select name="assigned_to_member_id" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                        <option value="">{{ __('Unassigned') }}</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ old('assigned_to_member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_to_member_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button class="w-full inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Create Chore') }}</button>
            </form>
        </div>
    </dialog>

    {{-- Edit Chore Dialogs --}}
    @foreach ($chores as $chore)
        <dialog id="edit-chore-{{ $chore->id }}" class="rounded-2xl p-0 shadow-xl w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">{{ __('Edit Chore') }}</h3>
                    <button type="button" onclick="document.getElementById('edit-chore-{{ $chore->id }}').close()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none" aria-label="{{ __('Close') }}">×</button>
                </div>
                <form method="POST" action="{{ route('chores.update', $chore) }}" class="space-y-3" novalidate>
                    @csrf @method('PUT')
                    <input type="hidden" name="dialog_id" value="edit-chore-{{ $chore->id }}">
                    <div>
                        <input name="name" value="{{ old('name', $chore->name) }}" class="w-full border @error('name') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Clean bathroom') }}">
                        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <textarea name="description" class="w-full border @error('description') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Description') }}" rows="3">{{ old('description', $chore->description) }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Date Assigned') }}</label>
                        <input type="date" name="assigned_for_date" value="{{ old('assigned_for_date', $chore->assigned_for_date?->toDateString()) }}" class="w-full border @error('assigned_for_date') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                        @error('assigned_for_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <select name="assigned_to_member_id" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" {{ old('assigned_to_member_id', $chore->assigned_to_member_id) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to_member_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
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
