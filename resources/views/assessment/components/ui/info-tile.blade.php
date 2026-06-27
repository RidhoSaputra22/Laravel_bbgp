@props([
    'label',
    'value',
    'valueClass' => 'text-[22px] font-bold leading-tight text-[#0d3557]',
])

<div {{ $attributes->class(['rounded-[18px] bg-[#f4f9fc] p-[18px]']) }}>
    <div class="mb-1.5 text-[13px] text-[#6f8496]">
        {{ $label }}
    </div>

    <div class="{{ $valueClass }}">
        {{ $value }}
    </div>
</div>
