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
                <div>
                    <input name="name" class="w-full border @error('name') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Name') }}" value="{{ old('name') }}">
                    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="date" name="joined_at" class="w-full border @error('joined_at') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" value="{{ old('joined_at') }}">
                    @error('joined_at')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button class="inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px]">{{ __('Save') }}</button>
            </form>
        </section>

        {{-- Member list --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Members') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse ($members as $member)
                    <div class="border border-slate-200 p-4">
                        <form method="POST" action="{{ route('members.update', $member) }}" class="flex flex-wrap items-center gap-3" novalidate>
                            @csrf @method('PUT')
                            <input name="name" value="{{ $member->name }}" class="flex-1 border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                            <button class="inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px]">{{ __('Update') }}</button>
                        </form>
                        <form method="POST" action="{{ route('members.destroy', $member) }}" class="mt-3">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center min-h-[44px] text-sm font-medium text-rose-600 transition hover:text-rose-800 active:scale-[0.97]">{{ __('Delete') }}</button>
                        </form>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <p class="mt-3 text-sm font-medium text-slate-500">{{ __('No members yet.') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Add one using the form on the left.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection