@php
    $fieldError = $errors->first('answers.' . $field['id']);
    $oldValue = old('answers.' . $field['id']);
    $checkboxValues = collect(old('answers.' . $field['id'], []))
        ->map(fn($value) => (string) $value)
        ->all();
    $inputType = match ($field['tipe_field']) {
        'number' => 'number',
        'date' => 'date',
        'email' => 'email',
        default => 'text',
    };
    $isChoiceField = in_array($field['tipe_field'], ['radio', 'checkbox'], true);
@endphp

<div class="mb-6 rounded-sm {{ $fieldError ? 'border-red-500/50 bg-red-50/50' : '' }}">


    @switch($field['tipe_field'])
        @case('textarea')
            <x-assessment::form.textarea :label="$field['label']" :description="$field['deskripsi']"
                :name="'answers[' . $field['id'] . ']'" :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Tuliskan jawaban Anda'" :required="(bool) $field['is_required']"
                :error="$fieldError" />
        @break

        @case('select')
            <x-assessment::form.select :label="$field['label']" :description="$field['deskripsi']"
                :name="'answers[' . $field['id'] . ']'" placeholder="Pilih jawaban"
                :required="(bool) $field['is_required']" :error="$fieldError">
                @foreach ($field['opsi_field'] ?? [] as $option)
                    <option value="{{ $option['value'] }}" @selected((string) $oldValue === (string) $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </x-assessment::form.select>
        @break

        @case('radio')
            <div class="space-y-3">
                @foreach ($field['opsi_field'] ?? [] as $optionIndex => $option)
                    <x-assessment::form.choice-option :id="'field-' . $field['id'] . '-' . $optionIndex" type="radio"
                        :name="'answers[' . $field['id'] . ']'" :value="$option['value']"
                        :checked="(string) $oldValue === (string) $option['value']" :label="$option['label']"
                        :description="$option['value']" />
                @endforeach
            </div>

            @if (!empty($field['bantuan']))
                <div class="mt-2.5 text-sm leading-[1.8] text-[#6c8092]">
                    <i class="far fa-lightbulb mr-1"></i>
                    {{ $field['bantuan'] }}
                </div>
            @endif
        @break

        @case('checkbox')
            <div class="space-y-3">
                @foreach ($field['opsi_field'] ?? [] as $optionIndex => $option)
                    <x-assessment::form.choice-option :id="'field-' . $field['id'] . '-' . $optionIndex" type="checkbox"
                        :name="'answers[' . $field['id'] . '][]'" :value="$option['value']" :checked="in_array(
                            (string) $option['value'],
                            $checkboxValues,
                            true,
                        )"
                        :label="$option['label']" />
                @endforeach
            </div>

            @if (!empty($field['bantuan']))
                <div class="mt-2.5 text-sm leading-[1.8] text-[#6c8092]">
                    <i class="far fa-lightbulb mr-1"></i>
                    {{ $field['bantuan'] }}
                </div>
            @endif
        @break

        @case('file')
            <x-assessment::form.file-input :label="$field['label']" :description="$field['deskripsi']"
                :name="'answers[' . $field['id'] . ']'" :required="(bool) $field['is_required']" :error="$fieldError" />
        @break

        @default
            <x-assessment::form.input :label="$field['label']" :description="$field['deskripsi']"
                :type="$inputType" :name="'answers[' . $field['id'] . ']'" :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Masukkan jawaban Anda'"
                :required="(bool) $field['is_required']" :error="$fieldError" />
    @endswitch

    @if ($fieldError)
        <div class="mt-2 text-sm text-red-600">
            {{ $fieldError }}
        </div>
    @endif
</div>
