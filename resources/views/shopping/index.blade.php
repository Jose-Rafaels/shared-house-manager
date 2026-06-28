@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Shopping') }}</span>
@endsection

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ __('Shopping') }}</h2>
            <p class="mt-1 text-slate-500">{{ __('Keep track of what the house needs to buy.') }}</p>
        </div>
        <button onclick="document.getElementById('add-shopping').showModal()" class="inline-flex items-center justify-center bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Add Item') }}</button>
    </div>

    <section class="mt-6 rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Priority') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Added By') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr class="border-t border-slate-100 hover:bg-slate-50/50 {{ $item->is_purchased ? 'opacity-60' : '' }}">
                            <td class="px-5 py-3 font-medium text-slate-900 {{ $item->is_purchased ? 'line-through' : '' }}">{{ $item->name }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $priorityColors = ['High' => 'bg-rose-100 text-rose-700', 'Medium' => 'bg-amber-100 text-amber-700', 'Low' => 'bg-emerald-100 text-emerald-700'];
                                @endphp
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $priorityColors[$item->priority] ?? 'bg-slate-100 text-slate-700' }}">{{ __($item->priority) }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->addedBy?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($item->is_purchased)
                                    <span class="text-sm text-emerald-700">{{ __('Purchased') }}</span>
                                @else
                                    <span class="text-sm text-slate-500">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <button onclick="document.getElementById('edit-shopping-{{ $item->id }}').showModal()" class="text-sm font-medium text-slate-700 hover:text-slate-900">{{ __('Edit') }}</button>
                                    <form method="POST" action="{{ route('shopping.togglePurchased', $item) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="text-sm font-medium {{ $item->is_purchased ? 'text-slate-600 hover:text-slate-800' : 'text-emerald-700 hover:text-emerald-800' }}">
                                            {{ $item->is_purchased ? __('Mark Pending') : __('Mark Purchased') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('shopping.destroy', $item) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-sm font-medium text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-slate-500">{{ __('No shopping items yet.') }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ __('Click "Add Item" to create one.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Add Item Dialog --}}
    <dialog id="add-shopping" class="rounded-2xl p-0 shadow-xl w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">{{ __('Add Shopping Item') }}</h3>
                <button type="button" onclick="document.getElementById('add-shopping').close()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none" aria-label="{{ __('Close') }}">×</button>
            </div>
            <form method="POST" action="{{ route('shopping.store') }}" class="space-y-3" novalidate>
                @csrf
                <input type="hidden" name="dialog_id" value="add-shopping">
                <div>
                    <input name="name" class="w-full border @error('name') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Detergent') }}" value="{{ old('name') }}">
                    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <select name="priority" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                        <option value="High">{{ __('High') }}</option>
                        <option value="Medium" selected>{{ __('Medium') }}</option>
                        <option value="Low">{{ __('Low') }}</option>
                    </select>
                </div>
                <div>
                    <select name="added_by_member_id" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                        <option value="">{{ __('No member') }}</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <textarea name="notes" class="w-full border @error('notes') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Notes') }}" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button class="w-full inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Add Item') }}</button>
            </form>
        </div>
    </dialog>

    {{-- Edit Item Dialogs --}}
    @foreach ($items as $item)
        <dialog id="edit-shopping-{{ $item->id }}" class="rounded-2xl p-0 shadow-xl w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">{{ __('Edit Shopping Item') }}</h3>
                    <button type="button" onclick="document.getElementById('edit-shopping-{{ $item->id }}').close()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none" aria-label="{{ __('Close') }}">×</button>
                </div>
                <form method="POST" action="{{ route('shopping.update', $item) }}" class="space-y-3" novalidate>
                    @csrf @method('PUT')
                    <input type="hidden" name="dialog_id" value="edit-shopping-{{ $item->id }}">
                    <div>
                        <input name="name" value="{{ old('name', $item->name) }}" class="w-full border @error('name') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Detergent') }}">
                        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <select name="priority" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                            <option value="High" {{ old('priority', $item->priority) === 'High' ? 'selected' : '' }}>{{ __('High') }}</option>
                            <option value="Medium" {{ old('priority', $item->priority) === 'Medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                            <option value="Low" {{ old('priority', $item->priority) === 'Low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                        </select>
                    </div>
                    <div>
                        <select name="added_by_member_id" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2">
                            <option value="">{{ __('No member') }}</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" {{ old('added_by_member_id', $item->added_by_member_id) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <textarea name="notes" class="w-full border @error('notes') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 py-2" placeholder="{{ __('Notes') }}" rows="3">{{ old('notes', $item->notes) }}</textarea>
                        @error('notes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
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
