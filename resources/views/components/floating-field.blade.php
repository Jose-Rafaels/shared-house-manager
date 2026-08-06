@props([
    'name' => null,
    'label' => null,
    'type' => 'text',
    'as' => 'input',      // 'input' | 'textarea' | 'select'
    'id' => null,
    'value' => null,
    'rows' => 3,
    'inputmode' => null,
    'step' => null,
])

@php
    $id = $id ?: $name;
    $hasPlaceholder = $as !== 'select' && $type !== 'date';
    $errorBorder = $errors->has($name) ? 'border-rose-500' : 'border-slate-300';
    $fieldClass = 'peer w-full border '.$errorBorder.' focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition rounded-md px-3 pt-5 pb-2 text-sm text-slate-900';
    // Floated (focus or filled): label sits on the top border, small, border-cut bg.
    // Inline (empty + unfocused): label sits inside, larger — acts as the placeholder.
    // date/select never match :placeholder-shown, so they are always floated.
    $labelClass = 'absolute left-3 top-3.5 text-sm text-slate-400 transition-all bg-white px-1 '.
        'peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-emerald-600 '.
        'peer-[:not(:placeholder-shown)]:top-0 peer-[:not(:placeholder-shown)]:-translate-y-1/2 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-slate-600';
@endphp

<div class="relative mt-2">
    @if ($as === 'select')
        <select id="{{ $id }}" name="{{ $name }}" class="{{ $fieldClass }}">
            {{ $slot }}
        </select>
    @elseif ($as === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" placeholder=" " class="{{ $fieldClass }}">{{ $value }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
            @if($inputmode) inputmode="{{ $inputmode }}" @endif
            @if($step) step="{{ $step }}" @endif
            @if($hasPlaceholder) placeholder=" " @endif
            @if($value !== null) value="{{ $value }}" @endif
            class="{{ $fieldClass }}" />
    @endif

    <label for="{{ $id }}" class="{{ $labelClass }}">{{ __($label) }}</label>

    @error($name)
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>