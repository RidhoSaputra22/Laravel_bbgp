@props(['label', 'value', 'description'])

<x-assessment::ui.card padding="p-6" rounded="rounded-xs" shadow="shadow-xs" class="h-32">
    <div class="flex md:flex-col">
        <div class="mb-2.5 text-[13px] font-medium uppercase tracking-[0.12em] text-[#6b8194]">
            {{ $label }}
        </div>

        <div class="mb-2 text-4xl font-bold leading-none text-[#0c3556]">
            {{ $value }}
        </div>
    </div>

    <p class="text-[#667b8e]">
        {{ $description }}
    </p>
</x-assessment::ui.card>
