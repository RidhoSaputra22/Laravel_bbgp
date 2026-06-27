@props([
    'name',
    'id' => null,
    'error' => null,
])

@php
    $id = $id ?: trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    $errorKey = trim((string) preg_replace('/\[(.*?)\]/', '.$1', $name), '.');
    $errorMessage = $error ?: $errors->first($errorKey);
@endphp

<input
    {{ $attributes }}
    id="{{ $id }}"
    type="file"
    name="{{ $name }}"
    @class([
        'block w-full cursor-pointer rounded-sm border bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-[#eaf5fb] file:px-3 file:py-2 file:font-semibold file:text-[#0d5f98] hover:file:bg-[#dff0fb]',
        'border-red-500' => $errorMessage,
        'border-[#d7e3ee]' => ! $errorMessage,
    ])
>
