@php
    $statusBadge = [
        'publish' => 'success',
        'draft' => 'warning',
        'nonaktif' => 'secondary',
    ][$assessment->status] ?? 'secondary';

    $activeForms = $assessment->forms
        ->filter(function ($form) {
            return $form->is_active && $form->fields->where('is_active', true)->isNotEmpty();
        })
        ->values();

    $activeFieldsCount = $activeForms->sum(function ($form) {
        return $form->fields->where('is_active', true)->count();
    });

    $generateChoiceLabel = function (int $index): string {
        $label = '';
        $number = $index + 1;

        while ($number > 0) {
            $number--;
            $label = chr(65 + ($number % 26)) . $label;
            $number = intdiv($number, 26);
        }

        return $label;
    };

    $normalizeDefaultValues = function ($value): array {
        return collect(preg_split('/[\r\n,]+/', (string) $value))
            ->map(function ($item) {
                return trim($item);
            })
            ->filter(function ($item) {
                return $item !== '';
            })
            ->values()
            ->all();
    };
@endphp

@push('styles')
    <style>
        .assessment-preview-page .custom-file-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endpush

<div class="assessment-preview-page">
    <div class="card card-primary shadow-sm border-0">
        <div class="card-header">
            <div>
                <h4 class="mb-1">Preview Form User</h4>
                <small class="text-muted">Tampilan ini mengikuti form dan pertanyaanaktif yang akan dilihat user.</small>
            </div>
        </div>
        <div class="card-body bg-light">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="text-muted small">Kode Assesment</div>
                            <div class="font-weight-bold">{{ $assessment->kode_assessment }}</div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="text-muted small">Slug</div>
                            <div class="font-weight-bold">{{ $assessment->slug }}</div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="text-muted small">Struktur Aktif</div>
                            <div class="font-weight-bold">{{ $activeForms->count() }} form</div>
                            <small class="text-muted">{{ $activeFieldsCount }} pertanyaanaktif</small>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="text-muted small">Status</div>
                            <div>
                                <span class="badge badge-{{ $statusBadge }}">{{ ucfirst($assessment->status) }}</span>
                                <span class="badge badge-{{ $assessment->is_active ? 'primary' : 'light' }}">
                                    {{ $assessment->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <h3 class="mb-2">{{ $assessment->judul }}</h3>

                    @if ($assessment->deskripsi)
                        <p class="text-muted mb-3">{{ $assessment->deskripsi }}</p>
                    @endif

                    @if ($assessment->petunjuk)
                        <div class="alert alert-light border mb-0">
                            <div class="font-weight-bold mb-1">Petunjuk Pengisian</div>
                            <div>{{ $assessment->petunjuk }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($activeForms->isEmpty())
                <div class="empty-state" data-height="260">
                    <div class="empty-state-icon bg-secondary">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                    <h2>Belum ada form aktif untuk dipreview</h2>
                    <p class="lead mb-0">
                        Aktifkan form dan field pada halaman edit agar preview tampil untuk admin.
                    </p>
                </div>
            @else
                @foreach ($activeForms as $form)
                    @php
                        $activeFields = $form->fields->where('is_active', true)->values();
                    @endphp
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white">
                            <div>
                                <h4 class="mb-1">{{ $form->judul_form }}</h4>
                                <small class="text-muted">Bagian {{ $loop->iteration }} • {{ $form->kode_form }}</small>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($form->deskripsi)
                                <p class="text-muted">{{ $form->deskripsi }}</p>
                            @endif

                            <div class="row">
                                @foreach ($activeFields as $field)
                                    @php
                                        $fieldWidth = $field->lebar_kolom ?: 'col-md-6';
                                        $fieldLabelId = 'preview-field-' . $form->id . '-' . $field->id;
                                    @endphp
                                    <div class="{{ $fieldWidth }}">
                                        <div class="form-group">
                                            <label for="{{ $fieldLabelId }}">
                                                {{ $field->label }}
                                                @if ($field->is_required)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>

                                            @switch($field->tipe_field)
                                                @case('textarea')
                                                    <textarea id="{{ $fieldLabelId }}" class="form-control" rows="3" placeholder="{{ $field->placeholder }}"
                                                        readonly>{{ $field->nilai_default }}</textarea>
                                                @break

                                                @case('select')
                                                    <select id="{{ $fieldLabelId }}" class="form-control" disabled>
                                                        <option value="" @selected(blank($field->nilai_default))>
                                                            {{ $field->placeholder ?: '-- Pilih salah satu --' }}
                                                        </option>
                                                        @foreach ($field->opsi_field ?? [] as $option)
                                                            <option value="{{ $option }}" @selected($option == $field->nilai_default)>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @break

                                                @case('radio')
                                                    @forelse ($field->opsi_field ?? [] as $option)
                                                        @php
                                                            $optionLabel = is_array($option) && array_key_exists('label', $option) ? $option['label'] : $generateChoiceLabel($loop->index);
                                                            $optionValue = is_array($option) && array_key_exists('value', $option) ? $option['value'] : (is_scalar($option) ? (string) $option : '');
                                                            $optionId = $fieldLabelId . '-' . $loop->index;
                                                        @endphp
                                                        <div class="custom-control custom-radio mb-2">
                                                            <input type="radio" class="custom-control-input" id="{{ $optionId }}"
                                                                name="{{ $field->nama_field }}" @checked($optionValue == $field->nilai_default) disabled>
                                                            <label class="custom-control-label" for="{{ $optionId }}">
                                                                {{ $optionLabel }}. {{ $optionValue }}
                                                            </label>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted">Belum ada opsi.</div>
                                                    @endforelse
                                                @break

                                                @case('checkbox')
                                                    @php
                                                        $selectedValues = $normalizeDefaultValues($field->nilai_default);
                                                    @endphp
                                                    @forelse ($field->opsi_field ?? [] as $option)
                                                        @php
                                                            $optionId = $fieldLabelId . '-' . $loop->index;
                                                        @endphp
                                                        <div class="custom-control custom-checkbox mb-2">
                                                            <input type="checkbox" class="custom-control-input" id="{{ $optionId }}"
                                                                name="{{ $field->nama_field }}[]" @checked(in_array($option, $selectedValues, true)) disabled>
                                                            <label class="custom-control-label" for="{{ $optionId }}">
                                                                {{ $option }}
                                                            </label>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted">Belum ada opsi.</div>
                                                    @endforelse
                                                @break

                                                @case('file')
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="{{ $fieldLabelId }}" disabled>
                                                        <label class="custom-file-label" for="{{ $fieldLabelId }}">
                                                            {{ $field->nilai_default ?: 'Pilih file' }}
                                                        </label>
                                                    </div>
                                                @break

                                                @default
                                                    <input type="{{ in_array($field->tipe_field, ['text', 'email', 'number', 'date'], true) ? $field->tipe_field : 'text' }}"
                                                        id="{{ $fieldLabelId }}" class="form-control" value="{{ $field->nilai_default }}"
                                                        placeholder="{{ $field->placeholder }}" readonly>
                                            @endswitch

                                            @if ($field->bantuan)
                                                <small class="form-text text-muted">{{ $field->bantuan }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
