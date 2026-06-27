@props([
    'field',
    'answer' => null,
    'number',
])

@php
    $payloadType = data_get($answer, 'payload.type');
    $checkboxValues = data_get($answer, 'payload.values', []);
@endphp

<div class="mb-4 rounded-[18px] border border-[#e6eef5] bg-[#fbfdff] p-5 last:mb-0">
    <x-assessment::ui.question-pill
        :number="$number"
        icon="fas fa-check"
        class="mb-3"
    />

    <div class="mb-2 text-xl font-bold leading-[1.5] text-[#0d3557]">
        {{ $field['label'] }}
    </div>

    @if (!empty($field['deskripsi']))
        <div class="mb-3 leading-[1.8] text-[#6d8092]">
            {{ $field['deskripsi'] }}
        </div>
    @endif

    @if ($answer)
        <x-assessment::ui.answer-box>
            @if ($payloadType === 'checkbox')
                <div class="flex flex-wrap gap-2">
                    @foreach ($checkboxValues as $value)
                        <span class="inline-flex rounded-full bg-[#dff0fb] px-3 py-2 font-semibold text-[#0c5a90]">
                            {{ $value }}
                        </span>
                    @endforeach
                </div>
            @elseif ($payloadType === 'file' && data_get($answer, 'file_url'))
                <x-assessment::ui.button
                    :href="data_get($answer, 'file_url')"
                    variant="outline"
                    icon="fas fa-paperclip"
                    minHeight="min-h-[42px]"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {{ data_get($answer, 'payload.original_name', 'Lihat lampiran') }}
                </x-assessment::ui.button>
            @else
                {{ $answer['text'] }}
            @endif
        </x-assessment::ui.answer-box>
    @else
        <x-assessment::ui.answer-box empty>
            Pertanyaan ini tidak diisi.
        </x-assessment::ui.answer-box>
    @endif
</div>
