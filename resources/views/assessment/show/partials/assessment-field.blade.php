@php
    $fieldError = $errors->first('answers.' . $field['id']);
    $oldValue = old('answers.' . $field['id']);
    $checkboxValues = collect(old('answers.' . $field['id'], []))
        ->map(fn($value) => (string) $value)
        ->all();
    $answerName = 'answers[' . $field['id'] . ']';
    $inputType = match ($field['tipe_field']) {
        'number' => 'number',
        'date' => 'date',
        'email' => 'email',
        default => 'text',
    };
@endphp

<div class="mb-6 rounded-sm {{ $fieldError ? 'border-red-500/50 bg-red-50/50' : '' }}">
    @switch($field['tipe_field'])
        @case('textarea')
            <x-assessment::form.textarea :label="$field['label']" :description="$field['deskripsi']"
                :name="$answerName" :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Tuliskan jawaban Anda'" :required="(bool) $field['is_required']"
                :error="$fieldError" />
        @break

        @case('select')
            <x-assessment::form.select :label="$field['label']" :description="$field['deskripsi']"
                :name="$answerName" placeholder="Pilih jawaban"
                :required="(bool) $field['is_required']" :error="$fieldError">
                @foreach ($field['opsi_field'] ?? [] as $option)
                    <option value="{{ $option['value'] }}" @selected((string) $oldValue === (string) $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </x-assessment::form.select>
        @break

        @case('radio')
            <x-assessment::form.radio-group :label="$field['label']" :description="$field['deskripsi']" :name="$answerName" :options="$field['opsi_field'] ?? []"
                :selected="\Illuminate\Support\Arr::wrap($oldValue)" :id-prefix="'field-' . $field['id']" />


        @break

        @case('checkbox')
            <x-assessment::form.checkbox-group :label="$field['label']" :description="$field['deskripsi']" :name="$answerName" :options="$field['opsi_field'] ?? []"
                :selected="$checkboxValues" :id-prefix="'field-' . $field['id']" />


        @break

        @case('file')
            <x-assessment::form.file-input :label="$field['label']" :description="$field['deskripsi']"
                :name="$answerName" :required="(bool) $field['is_required']" :error="$fieldError" />
        @break

        @default
            <x-assessment::form.input :label="$field['label']" :description="$field['deskripsi']"
                :type="$inputType" :name="$answerName" :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Masukkan jawaban Anda'"
                :required="(bool) $field['is_required']" :error="$fieldError" />
    @endswitch

    @if ($fieldError)
        <div class="mt-2 text-sm text-red-600">
            {{ $fieldError }}
        </div>
    @endif
</div>
