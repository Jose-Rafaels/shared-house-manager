@extends('layouts.app')

@section('breadcrumbs')
    <span class="text-slate-400" aria-hidden="true">/</span>
    <span class="text-slate-900 font-medium" aria-current="page">{{ __('Shopping') }}</span>
@endsection

@section('content')
    <h2 class="text-3xl font-bold tracking-tight">{{ __('Shopping') }}</h2>
    <p class="mt-1 text-slate-500">{{ __('Keep track of what the house needs to buy.') }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[420px_1fr]">
        {{-- Add item form --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold">{{ __('Add Shopping Item') }}</h3>
            <form method="POST" action="{{ route('shopping.store') }}" class="mt-4 space-y-3" novalidate>
                @csrf
                <div>
                    <input name="name" class="w-full border @error('name') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Detergent') }}" value="{{ old('name') }}">
                    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <select name="priority" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="High">{{ __('High') }}</option>
                        <option value="Medium">{{ __('Medium') }}</option>
                        <option value="Low">{{ __('Low') }}</option>
                    </select>
                </div>
                <div>
                    <select name="added_by_member_id" class="w-full border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                        <option value="">{{ __('No member') }}</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <textarea name="notes" class="w-full border @error('notes') border-rose-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Notes') }}">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button class="inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px]">{{ __('Add Item') }}</button>
            </form>
        </section>

        {{-- Items list --}}
        <section class="space-y-5">
            @forelse ($items as $item)
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">{{ $item->name }}</div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm">{{ __($item->priority) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">{{ $item->notes }}</p>
                    @if (!$item->purchased_at)
                        <form method="POST" action="{{ route('shopping.purchase.store', $item) }}" class="mt-4 grid gap-3 md:grid-cols-4" novalidate>
                            @csrf
                            <select name="purchased_by_member_id" class="border-slate-300 md:col-span-2 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                                <option value="">{{ __('No member') }}</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="amount" class="border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Amount') }}">
                            <input type="date" name="purchased_on" class="border-slate-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition">
                            <textarea name="notes" class="border-slate-300 md:col-span-4 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition" placeholder="{{ __('Notes') }}"></textarea>
                            <button class="inline-flex items-center justify-center bg-slate-900 px-5 py-3 text-base font-medium text-white transition hover:bg-slate-800 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 min-h-[44px] md:col-span-4">{{ __('Mark Purchased') }}</button>
                        </form>
                    @else
                        <div class="mt-4 text-sm text-emerald-700">{{ __('Purchased on :date', ['date' => $item->purchased_at->translatedFormat('d M Y')]) }}</div>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl bg-white p-8 shadow-sm text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-slate-500">{{ __('No shopping items yet.') }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Add one using the form on the left.') }}</p>
                </div>
            @endforelse
        </section>
    </div>
@endsection
