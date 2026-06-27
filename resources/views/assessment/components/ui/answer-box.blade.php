@props([
    'empty' => false,
])

<div {{ $attributes->class(['break-words rounded-2xl px-[18px] py-4 leading-[1.8]', $empty ? 'bg-slate-100 text-[#6b7d8f]' : 'bg-[#eef6fb] text-[#0f3557]']) }}>
    {{ $slot }}
</div>
