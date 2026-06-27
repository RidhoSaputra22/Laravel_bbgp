@props([
    'name',
    'id' => null,
    'label' => null,
    'required' => false,
    'error' => null,
    'placeholder' => null,
    'placeholderValue' => '',
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

    <select
        {{ $attributes->except('class') }}
        id="{{ $id }}"
        name="{{ $name }}"
        @required($required)
        @class([
            'py-3 px-4 w-full rounded-sm border bg-white px-4 text-slate-800 outline-none transition focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15',
            '' => $label,
            'border-red-500 focus:border-red-500 focus:ring-red-500/15' => $errorMessage,
            'border-[#d7e3ee]' => ! $errorMessage,
        ])
    >
        @if (!is_null($placeholder))
            <option value="{{ $placeholderValue }}">{{ $placeholder }}</option>
        @endif

        {{ $slot }}
    </select>
</div>
