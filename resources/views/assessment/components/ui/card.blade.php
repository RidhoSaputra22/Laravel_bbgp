@props([
    'padding' => 'p-6',
    'rounded' => 'rounded-xs',
    'shadow' => 'shadow-sm',
])

<div {{ $attributes->class(['bg-white', $rounded, $shadow, $padding]) }}>
    {{ $slot }}
</div>
