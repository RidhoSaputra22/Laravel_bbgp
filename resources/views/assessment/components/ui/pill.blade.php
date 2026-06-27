@props([
    'icon' => null,
    'tone' => 'sky',
])

@php
    $tones = [
        'glass' => 'bg-white/[0.14] text-white',
        'sky' => 'bg-[#eaf5fb] text-[#0d5f98]',
        'success' => 'bg-[#dff6e8] text-[#1f8b4d]',
    ];
@endphp

<div {{ $attributes->class(['inline-flex items-center rounded-full', $tones[$tone] ?? $tones['sky']]) }}>
    @if ($icon)
        <i class="{{ $icon }}"></i>
    @endif

    {{ $slot }}
</div>
