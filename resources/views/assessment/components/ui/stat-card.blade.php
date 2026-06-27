@props([
    'label',
    'value',
    'description',
    'size' => 'sm',
])

@php
    $sizes = [
        'sm' => [
            'padding' => 'p-4',
            'label' => 'text-[11px]',
            'value' => 'text-2xl',
            'description' => 'text-xs',
            'gap' => 'gap-x-3 gap-y-1.5',
        ],

        'md' => [
            'padding' => 'p-6',
            'label' => 'text-[13px]',
            'value' => 'text-4xl',
            'description' => 'text-sm',
            'gap' => 'gap-x-4 gap-y-2',
        ],

        'lg' => [
            'padding' => 'p-7',
            'label' => 'text-sm',
            'value' => 'text-5xl',
            'description' => 'text-base',
            'gap' => 'gap-x-5 gap-y-3',
        ],

        'xl' => [
            'padding' => 'p-8',
            'label' => 'text-base',
            'value' => 'text-6xl',
            'description' => 'text-lg',
            'gap' => 'gap-x-6 gap-y-3',
        ],
    ];

    $style = $sizes[$size] ?? $sizes['md'];
@endphp

<x-assessment::ui.card
    :padding="$style['padding']"
    rounded="rounded-xs"
    shadow="shadow-xs"
    class="h-full"
>
    <div class="mb-3 flex items-start justify-between {{ $style['gap'] }}">
        <div
            class="min-w-0 flex-1 basis-32 font-medium uppercase tracking-[0.12em] text-[#6b8194] {{ $style['label'] }}"
        >
            {{ $label }}
        </div>

        <div class="shrink-0 font-bold leading-none text-[#0c3556] {{ $style['value'] }}">
            {{ $value }}
        </div>
    </div>

    <p class="text-[#667b8e] {{ $style['description'] }}">
        {{ $description }}
    </p>
</x-assessment::ui.card>
