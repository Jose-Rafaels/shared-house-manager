@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Members') }}</span>
@endsection

@section('content')
    <h2 class="text-3xl font-bold tracking-tight">{{ __('Members') }}</h2>
    <p class="mt-1 text-slate-500">{{ __('Manage the people sharing your house.') }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[360px_1fr]">
        {{-- Add member form --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Add Member') }}</h3>
            <form method="POST" action="{{ route('members.store') }}" class="mt-4 space-y-4" novalidate>
                @csrf
                <x-floating-field name="name" label="Name" :value="old('name')" />

                <x-floating-field name="joined_at" label="Joined at" type="date" :value="old('joined_at')" />
                <button class="inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px]">{{ __('Save') }}</button>
            </form>
        </section>

        {{-- Member list --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Members') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse ($members as $member)
                    <div class="flex items-center justify-between gap-3 border border-slate-200 p-4">
                        <div class="flex-1">
                            <div class="font-medium text-slate-900">{{ $member->name }}</div>
                            @if ($member->joined_at)
                                <div class="text-xs text-slate-500">{{ __('Joined') }} {{ $member->joined_at->translatedFormat('d M Y') }}</div>
                            @endif
                        </div>
                        <button onclick="document.getElementById('edit-member-{{ $member->id }}').showModal()" class="inline-flex items-center justify-center min-h-[44px] px-3 text-sm font-medium text-slate-700 hover:text-slate-900 active:scale-[0.97]">{{ __('Edit') }}</button>
                        <form method="POST" action="{{ route('members.destroy', $member) }}">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center justify-center min-h-[44px] px-3 text-sm font-medium text-rose-600 transition hover:text-rose-800 active:scale-[0.97]">{{ __('Delete') }}</button>
                        </form>
                    </div>

                    {{-- Edit Member Dialog --}}
                    <dialog id="edit-member-{{ $member->id }}" class="rounded-2xl p-0 shadow-xl w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold">{{ __('Edit Member') }}</h3>
                                <button type="button" onclick="document.getElementById('edit-member-{{ $member->id }}').close()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none" aria-label="{{ __('Close') }}">×</button>
                            </div>
                            <form method="POST" action="{{ route('members.update', $member) }}" class="space-y-3" novalidate>
                                @csrf @method('PUT')
                                <input type="hidden" name="dialog_id" value="edit-member-{{ $member->id }}">
                                <x-floating-field name="name" label="Name" :value="old('name', $member->name)" />

                                <x-floating-field name="joined_at" label="Joined at" type="date" :value="old('joined_at', $member->joined_at?->toDateString())" />
                                <button class="w-full inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] rounded-lg">{{ __('Update') }}</button>
                            </form>
                        </div>
                    </dialog>
                @empty
                    <div class="py-8 text-center">
                        <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <p class="mt-3 text-sm font-medium text-slate-500">{{ __('No members yet.') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Add one using the form above.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

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