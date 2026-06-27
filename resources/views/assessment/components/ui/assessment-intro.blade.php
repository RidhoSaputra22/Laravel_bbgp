@props([
    'code',
    'title',
    'description' => null,
    'descriptionFallback' => null,
    'instruction' => null,
])

<x-assessment::ui.card
    {{ $attributes->class(['mb-[18px]']) }}
    padding="px-6 py-6 sm:px-[26px]"
    rounded="rounded-[22px]"
    shadow="shadow-[0_14px_36px_rgba(15,59,95,0.08)]"
>
    <div class="mb-2 text-sm uppercase text-slate-500">
        {{ $code }}
    </div>

    <h3 class="mb-1.5 text-xl font-bold text-[#0d3557]">
        {{ $title }}
    </h3>

    <p class="leading-[1.8] text-[#6a7e90]">
        {{ $description ?: $descriptionFallback }}
    </p>

    @if ($instruction)
        <x-assessment::ui.alert type="info" class="mt-4 leading-relaxed">
            <strong>Petunjuk:</strong> {{ $instruction }}
        </x-assessment::ui.alert>
    @endif
</x-assessment::ui.card>
