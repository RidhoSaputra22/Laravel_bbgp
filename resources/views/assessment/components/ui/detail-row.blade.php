@props([
    'label',
    'value' => null,
    'valueClass' => 'font-bold text-slate-900',
    'first' => false,
])

<div {{ $attributes->class(['border-t border-[#ebf1f6] py-[18px]', 'border-t-0 pt-0' => $first]) }}>
    <div class="mb-1 text-sm text-slate-500">
        {{ $label }}
    </div>

    <div class="{{ $valueClass }}">
        @if ($slot->isEmpty())
            {{ $value }}
        @else
            {{ $slot }}
        @endif
    </div>
</div>
