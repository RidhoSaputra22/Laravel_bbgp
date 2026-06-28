@php
    $fieldError = $errors->first('answers.' . $field['id']);
    $oldValue = old('answers.' . $field['id']);
    $checkboxValues = collect(old('answers.' . $field['id'], []))
        ->map(fn($value) => (string) $value)
        ->all();
    $answerName = 'answers[' . $field['id'] . ']';
    $fieldType = $field['tipe_field'];
    $isRequired = (bool) ($field['is_required'] ?? false);
    $inputType = match ($field['tipe_field']) {
        'number' => 'number',
        'date' => 'date',
        'email' => 'email',
        default => 'text',
    };
@endphp

<div class="mb-6 rounded-sm " data-assessment-field
    data-field-id="{{ $field['id'] }}" data-field-type="{{ $fieldType }}" data-field-label="{{ $field['label'] }}"
    data-required="{{ $isRequired ? '1' : '0' }}">
    @switch($fieldType)
        @case('textarea')
            <x-assessment::form.textarea :label="$field['label']" :description="$field['deskripsi']"
                :name="$answerName" :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Tuliskan jawaban Anda'" :required="$isRequired"
                :error="$fieldError" />
        @break

        @case('select')
            <x-assessment::form.select :label="$field['label']" :description="$field['deskripsi']"
                :name="$answerName" placeholder="Pilih jawaban"
                :required="$isRequired" :error="$fieldError">
                @foreach ($field['opsi_field'] ?? [] as $option)
                    <option value="{{ $option['value'] }}" @selected((string) $oldValue === (string) $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </x-assessment::form.select>
        @break

        @case('radio')
            <x-assessment::form.radio-group :label="$field['label']" :description="$field['deskripsi']" :name="$answerName" :options="$field['opsi_field'] ?? []"
                :selected="\Illuminate\Support\Arr::wrap($oldValue)" :id-prefix="'field-' . $field['id']"
                :required="$isRequired" />


        @break

        @case('checkbox')
            <x-assessment::form.checkbox-group :label="$field['label']" :description="$field['deskripsi']" :name="$answerName" :options="$field['opsi_field'] ?? []"
                :selected="$checkboxValues" :id-prefix="'field-' . $field['id']"
                :required="$isRequired" />


        @break

        @case('file')
            <x-assessment::form.file-input :label="$field['label']" :description="$field['deskripsi']"
                :name="$answerName" :required="$isRequired" :error="$fieldError" />
        @break

        @default
            <x-assessment::form.input :label="$field['label']" :description="$field['deskripsi']"
                :type="$inputType" :name="$answerName" :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Masukkan jawaban Anda'"
                :required="$isRequired" :error="$fieldError" />
    @endswitch

    @if ($fieldError)
        <div class="mt-2 text-sm text-red-600" data-field-error role="alert">
            {{ $fieldError }}
        </div>
    @else
        <div class="mt-2 hidden text-sm text-red-600" data-field-error role="alert"></div>
    @endif
</div>
