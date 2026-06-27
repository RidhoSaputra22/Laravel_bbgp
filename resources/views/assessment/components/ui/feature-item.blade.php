@props([
    'icon',
])

<li {{ $attributes->class(['flex items-start gap-[14px] text-white/[0.92]']) }}>
    <i class="{{ $icon }} mt-0.5 inline-flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-full bg-white/[0.12]"></i>

    <div class="leading-relaxed">
        {{ $slot }}
    </div>
</li>
