@props([
    'name',
    'id' => null,
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'error' => null,
])

@php
    $id = $id ?: trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    $errorKey = trim((string) preg_replace('/\[(.*?)\]/', '.$1', $name), '.');
    $errorMessage = $error ?: $errors->first($errorKey);
@endphp

<div {{ $attributes->only('class')->class(['space-y-2']) }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <input
        {{ $attributes->except('class') }}
        id="{{ $id }}"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        @required($required)
        @class([
            'min-h-[50px] w-full rounded-[14px] border bg-white px-4 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15',
            'min-h-[52px]' => $label,
            'border-red-500 focus:border-red-500 focus:ring-red-500/15' => $errorMessage,
            'border-[#d8e3ee]' => ! $errorMessage,
        ])
    >
</div>
