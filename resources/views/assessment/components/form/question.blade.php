@props([
    'field',
    'number',
    'error' => null,
    'oldValue' => null,
    'checkboxValues' => [],
])

@php
    $inputType = match ($field['tipe_field']) {
        'number' => 'number',
        'date' => 'date',
        'email' => 'email',
        default => 'text',
    };
@endphp

<div class="mb-[18px] rounded-[20px] border p-[22px] last:mb-0 {{ $error ? 'border-red-500/50 bg-red-50/50' : 'border-[#e4edf4] bg-[#fbfdff]' }}">
    <x-assessment::ui.question-pill :number="$number" class="mb-[14px]" />

    <div class="mb-2 text-xl font-bold leading-[1.5] text-[#0d3557] sm:text-[21px]">
        {{ $field['label'] }}
        @if ($field['is_required'])
            <span class="text-red-600">*</span>
        @endif
    </div>

    @if (!empty($field['deskripsi']))
        <div class="mb-2 leading-[1.8] text-[#6c8092]">
            {{ $field['deskripsi'] }}
        </div>
    @endif

    @switch($field['tipe_field'])
        @case('textarea')
            <x-assessment::form.textarea
                :name="'answers['.$field['id'].']'"
                :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Tuliskan jawaban Anda'"
                :error="$error"
            />
        @break

        @case('select')
            <x-assessment::form.select
                :name="'answers['.$field['id'].']'"
                placeholder="Pilih jawaban"
                :error="$error"
            >
                @foreach ($field['opsi_field'] ?? [] as $option)
                    <option
                        value="{{ $option['value'] }}"
                        @selected((string) $oldValue === (string) $option['value'])
                    >
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </x-assessment::form.select>
        @break

        @case('radio')
            <div class="space-y-3">
                @foreach ($field['opsi_field'] ?? [] as $optionIndex => $option)
                    <x-assessment::form.choice-option
                        :id="'field-'.$field['id'].'-'.$optionIndex"
                        type="radio"
                        :name="'answers['.$field['id'].']'"
                        :value="$option['value']"
                        :checked="(string) $oldValue === (string) $option['value']"
                        :label="$option['label']"
                        :description="$option['value']"
                    />
                @endforeach
            </div>
        @break

        @case('checkbox')
            <div class="space-y-3">
                @foreach ($field['opsi_field'] ?? [] as $optionIndex => $option)
                    <x-assessment::form.choice-option
                        :id="'field-'.$field['id'].'-'.$optionIndex"
                        type="checkbox"
                        :name="'answers['.$field['id'].'][]'"
                        :value="$option['value']"
                        :checked="in_array((string) $option['value'], $checkboxValues, true)"
                        :label="$option['label']"
                    />
                @endforeach
            </div>
        @break

        @case('file')
            <x-assessment::form.file-input
                :name="'answers['.$field['id'].']'"
                :error="$error"
            />
        @break

        @default
            <x-assessment::form.input
                :type="$inputType"
                :name="'answers['.$field['id'].']'"
                :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Masukkan jawaban Anda'"
                :error="$error"
            />
    @endswitch

    @if ($error)
        <div class="mt-2 text-sm text-red-600">
            {{ $error }}
        </div>
    @endif

    @if (!empty($field['bantuan']))
        <div class="mt-2.5 text-sm leading-[1.8] text-[#6c8092]">
            <i class="far fa-lightbulb mr-1"></i>
            {{ $field['bantuan'] }}
        </div>
    @endif
</div>
