@props([
    'number',
    'label' => 'Soal',
    'icon' => 'fas fa-question-circle',
])

<x-assessment::ui.pill
    :icon="$icon"
    tone="sky"
    {{ $attributes->class(['mb-[14px] gap-2 px-[14px] py-2 text-[13px] font-bold']) }}
>
    {{ $label }} {{ $number }}
</x-assessment::ui.pill>
