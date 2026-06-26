@php
    $builderSeed = old('forms', $formBuilderData ?? []);
    $fieldTypeBadges = $fieldTypes ?? [];
    $validationErrors = $errors->getMessages();
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="font-weight-bold mb-2">Periksa kembali input assesment berikut:</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@push('styles')
    <style>
        .assessment-required {
            color: #fc544b;
            font-weight: 700;
        }

        .assessment-invalid-wrapper {
            border: 1px solid #fc544b;
            border-radius: 0.5rem;
            padding: 0.75rem;
        }

        .assessment-invalid-list {
            border: 1px dashed #fc544b;
            border-radius: 0.5rem;
            padding: 0.75rem;
        }

        .invalid-feedback {
            display: block;
            font-size: 0.8rem;
        }

        .multiple-choice-option-row {
            border: 1px solid #e4e6fc;
            border-radius: .1rem;
            padding: 0.75rem;
            background: #fff;
        }

        .assessment-builder-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .assessment-builder-actions .custom-switch {
            padding-left: 2.25rem;
            margin-bottom: 0;
        }

        .assessment-builder-actions .custom-control-label {
            white-space: nowrap;
        }
    </style>
@endpush

<form action="{{ $formAction }}" method="POST" id="assessment-builder-form">
    @csrf
    @if ($httpMethod !== 'POST')
        @method($httpMethod)
    @endif

    <div class="card">
        <div class="card-header">
            <h4>{{ $pageTitle }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kode Assesment <span class="assessment-required">*</span></label>
                        <input type="text" name="kode_assessment"
                            class="form-control @error('kode_assessment') is-invalid @enderror"
                            value="{{ old('kode_assessment', $assessment->kode_assessment) }}"
                            placeholder="Contoh: ASM-001" required>
                        @error('kode_assessment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Judul Assesment <span class="assessment-required">*</span></label>
                        <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                            value="{{ old('judul', $assessment->judul) }}"
                            placeholder="Masukkan judul assesment" required>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status <span class="assessment-required">*</span></label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="draft"
                                @selected(old('status', $assessment->status ?: 'draft') == 'draft')>Draft</option>
                            <option value="publish"
                                @selected(old('status', $assessment->status) == 'publish')>Publish</option>
                            <option value="nonaktif"
                                @selected(old('status', $assessment->status) == 'nonaktif')>Nonaktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="4"
                            placeholder="Deskripsi singkat assesment">{{ old('deskripsi', $assessment->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Petunjuk Pengisian</label>
                        <textarea name="petunjuk" class="form-control @error('petunjuk') is-invalid @enderror" rows="4"
                            placeholder="Petunjuk untuk pengguna form">{{ old('petunjuk', $assessment->petunjuk) }}</textarea>
                        @error('petunjuk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="custom-control custom-switch mt-2">
                <input type="checkbox" class="custom-control-input" id="assessment-active" name="is_active"
                    value="1" @checked(old('is_active', $assessment->is_active))>
                <label class="custom-control-label" for="assessment-active">Aktifkan assesment</label>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Form Builder</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-light border">
                <div class="font-weight-bold mb-2">Petunjuk Penggunaan</div>
                <ul class="mb-0 pl-3">
                    <li>Isi informasi assesment di bagian atas terlebih dahulu.</li>
                    <li>Klik <strong>Tambah Form</strong> di bagian bawah untuk membuat bagian form, lalu tambahkan field di bawah form terkait.</li>
                    <li>Untuk field <strong>Daftar Pilihan</strong> dan <strong>Kotak Centang</strong>, pisahkan opsi dengan koma atau baris baru.</li>
                    <li>Untuk field <strong>Pilihan Ganda</strong>, isi label dan value pada opsi yang tersedia.</li>
                    <li>Nama field akan dibuat otomatis dari label yang Anda isi.</li>
                    <li>Aktifkan hanya form dan field yang ingin ditampilkan ke pengguna.</li>
                </ul>
            </div>

            <div id="form-builder-empty" class="empty-state d-none" data-height="220">
                <div class="empty-state-icon bg-primary">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h2>Belum ada form</h2>
                <p class="lead">Tambahkan form pertama untuk mulai menyusun struktur assesment dinamis.</p>
            </div>

            <div id="form-builder-list"></div>
            @error('forms')
                <div class="invalid-feedback mt-2">{{ $message }}</div>
            @enderror

            <div class="text-right mt-3">
                <button type="button" class="btn btn-primary btn-sm" id="btn-add-form">
                    <i class="fas fa-plus"></i> Tambah Form
                </button>
            </div>
        </div>
    </div>

    <div class="text-right">
        <a href="{{ route('assessment.index') }}" class="btn btn-light mr-2">Kembali</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ $submitLabel }}
        </button>
    </div>
</form>

@push('scripts')
    <script>
        $(document).ready(function() {
            const assessmentFieldTypes = @json($fieldTypes);
            const initialForms = @json($builderSeed);
            const validationErrors = @json($validationErrors);
            const textOptionFieldTypes = ['select', 'checkbox'];
            const multipleChoiceFieldType = 'radio';
            const columnOptions = ['col-md-12', 'col-md-8', 'col-md-6', 'col-md-4'];
            const $previewPanel = $('#assessment-preview-panel');
            const $previewContent = $('#assessment-preview-content');
            const $previewToggleButton = $('.btn-toggle-preview-panel');
            const requiredMarker = '<span class="assessment-required">*</span>';
            const errorKeys = Object.keys(validationErrors || {});
            let formIndexCounter = 0;
            let previewRenderTimer = null;

            const escapeHtml = (value) => $('<div>').text(value ?? '').html();
            const joinClasses = (...classes) => classes.filter(Boolean).join(' ');

            const nameToErrorKey = (name) => String(name || '')
                .replace(/\[(.*?)\]/g, '.$1')
                .replace(/^\./, '');

            const getFieldError = (name) => {
                const messages = validationErrors[nameToErrorKey(name)] || [];
                return Array.isArray(messages) && messages.length ? messages[0] : '';
            };

            const hasError = (name) => Boolean(getFieldError(name));

            const hasNestedErrors = (prefix) => {
                const normalizedPrefix = nameToErrorKey(prefix);
                return errorKeys.some((key) => key === normalizedPrefix || key.startsWith(`${normalizedPrefix}.`));
            };

            const getInputClass = (name, baseClass = 'form-control') => {
                return joinClasses(baseClass, hasError(name) ? 'is-invalid' : '');
            };

            const buildInvalidFeedback = (name, extraClass = '') => {
                const message = getFieldError(name);

                if (!message) {
                    return '';
                }

                return `<div class="${joinClasses('invalid-feedback', 'd-block', extraClass)}">${escapeHtml(message)}</div>`;
            };

            const buildRequiredLabel = (label) => `${label} ${requiredMarker}`;

            const slugifyFieldName = (value) => String(value || '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .replace(/_+/g, '_');



            const normalizeChecked = (value) => {
                return value === true || value === 1 || value === '1' || value === 'on';
            };

            const buildFieldTypeOptions = (selectedValue) => {
                return Object.entries(assessmentFieldTypes).map(([value, label]) => {
                    const selected = value === selectedValue ? 'selected' : '';
                    return `<option value="${value}" ${selected}>${label}</option>`;
                }).join('');
            };

            const buildColumnOptions = (selectedValue) => {
                return columnOptions.map((value) => {
                    const selected = value === selectedValue ? 'selected' : '';
                    return `<option value="${value}" ${selected}>${value}</option>`;
                }).join('');
            };

            const generateChoiceLabel = (index) => {
                let label = '';
                let number = index + 1;

                while (number > 0) {
                    number -= 1;
                    label = String.fromCharCode(65 + (number % 26)) + label;
                    number = Math.floor(number / 26);
                }

                return label;
            };

            const normalizeRadioOptions = (options = []) => {
                if (!Array.isArray(options) || !options.length) {
                    return [{
                        label: 'A',
                        value: ''
                    }, {
                        label: 'B',
                        value: ''
                    }];
                }

                const normalizedOptions = options.map((option, index) => {
                    if (typeof option === 'string') {
                        return {
                            label: generateChoiceLabel(index),
                            value: option,
                        };
                    }

                    return {
                        label: option?.label || generateChoiceLabel(index),
                        value: option?.value || '',
                    };
                });

                while (normalizedOptions.length < 2) {
                    normalizedOptions.push({
                        label: generateChoiceLabel(normalizedOptions.length),
                        value: '',
                    });
                }

                return normalizedOptions;
            };

            const buildRadioOptionRow = (formIndex, fieldIndex, optionIndex, optionData = {}) => {
                const optionLabel = optionData.label || generateChoiceLabel(optionIndex);
                const optionValue = optionData.value || '';
                const optionLabelName = `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][label]`;
                const optionValueName = `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][value]`;

                return `
                    <div class="multiple-choice-option-row mb-2" data-option-index="${optionIndex}">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label>${buildRequiredLabel('Label')}</label>
                                    <input type="text" class="${getInputClass(optionLabelName, 'form-control radio-option-label')}"
                                        name="${optionLabelName}"
                                        value="${escapeHtml(optionLabel)}"
                                        maxlength="10"
                                        placeholder="A">
                                    ${buildInvalidFeedback(optionLabelName)}
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group mb-md-0">
                                    <label>${buildRequiredLabel('Value')}</label>
                                    <input type="text" class="${getInputClass(optionValueName, 'form-control radio-option-value')}"
                                        name="${optionValueName}"
                                        value="${escapeHtml(optionValue)}"
                                        placeholder="Contoh: Jawaban 1">
                                    ${buildInvalidFeedback(optionValueName)}
                                </div>
                            </div>
                            <div class="col-md-1 text-md-right">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-radio-option">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            };

            const buildFieldCard = (formIndex, fieldIndex, fieldData = {}) => {
                const fieldType = fieldData.tipe_field || 'text';
                const showTextOptions = textOptionFieldTypes.includes(fieldType);
                const showMultipleChoiceOptions = fieldType === multipleChoiceFieldType;
                const radioOptions = normalizeRadioOptions(fieldData.radio_options);
                const fieldPrefix = `forms[${formIndex}][fields][${fieldIndex}]`;
                const labelName = `${fieldPrefix}[label]`;
                const deskripsiName = `${fieldPrefix}[deskripsi]`;
                const tipeFieldName = `${fieldPrefix}[tipe_field]`;
                const placeholderName = `${fieldPrefix}[placeholder]`;
                const urutanName = `${fieldPrefix}[urutan]`;
                const opsiFieldTextName = `${fieldPrefix}[opsi_field_text]`;
                const radioOptionsName = `${fieldPrefix}[radio_options]`;
                const bantuanName = `${fieldPrefix}[bantuan]`;
                const fieldCardClass = joinClasses(
                    'card',
                    'border',
                    'assessment-field-card',
                    'mb-3',
                    hasNestedErrors(fieldPrefix) ? 'border-danger' : ''
                );
                const standardOptionWrapperClass = joinClasses(
                    'form-group',
                    'standard-option-wrapper',
                    showTextOptions ? '' : 'd-none',

                );
                const multipleChoiceWrapperClass = joinClasses(
                    'multiple-choice-wrapper',
                    showMultipleChoiceOptions ? '' : 'd-none',

                );

                return `
                    <div class="${fieldCardClass}" data-field-index="${fieldIndex}" data-radio-option-counter="${radioOptions.length}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Pertanyaan ${fieldIndex + 1}</h6>
                                <div class="assessment-builder-actions">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input"
                                            id="field-required-${formIndex}-${fieldIndex}"
                                            name="forms[${formIndex}][fields][${fieldIndex}][is_required]"
                                            value="1" ${normalizeChecked(fieldData.is_required) ? 'checked' : ''}>
                                        <label class="custom-control-label"
                                            for="field-required-${formIndex}-${fieldIndex}">Field wajib diisi</label>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input"
                                            id="field-active-${formIndex}-${fieldIndex}"
                                            name="forms[${formIndex}][fields][${fieldIndex}][is_active]"
                                            value="1" ${fieldData.is_active === undefined || normalizeChecked(fieldData.is_active) ? 'checked' : ''}>
                                        <label class="custom-control-label"
                                            for="field-active-${formIndex}-${fieldIndex}">Field aktif</label>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-field">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>${buildRequiredLabel('Label Field')}</label>
                                        <input type="text" class="${getInputClass(labelName, 'form-control field-label-input')}"
                                            name="${labelName}"
                                            value="${escapeHtml(fieldData.label)}"
                                            placeholder="Contoh: Nama Lengkap"
                                            required>
                                        ${buildInvalidFeedback(labelName)}

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>${buildRequiredLabel('Tipe Pertanyaan')}</label>
                                        <select class="${getInputClass(tipeFieldName, 'form-control field-type-select')}"
                                            name="${tipeFieldName}"
                                            required>
                                            ${buildFieldTypeOptions(fieldType)}
                                        </select>
                                        ${buildInvalidFeedback(tipeFieldName)}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Urutan</label>
                                        <input type="number" min="1" class="${getInputClass(urutanName)}"
                                            name="${urutanName}"
                                            value="${escapeHtml(fieldData.urutan || fieldIndex + 1)}">
                                        ${buildInvalidFeedback(urutanName)}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Deskripsi Pertanyaan</label>
                                        <textarea class="${getInputClass(deskripsiName)} field-description-input"
                                            name="${deskripsiName}"
                                            rows="3"
                                            placeholder="Tambahkan deskripsi pertanyaan bila diperlukan">${escapeHtml(fieldData.deskripsi)}</textarea>
                                        ${buildInvalidFeedback(deskripsiName)}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Placeholder</label>
                                        <input type="text" class="${getInputClass(placeholderName)}"
                                            name="${placeholderName}"
                                            value="${escapeHtml(fieldData.placeholder)}"
                                            placeholder="Placeholder field">
                                        ${buildInvalidFeedback(placeholderName)}
                                    </div>
                                </div>
                            </div>

                            <div class="${standardOptionWrapperClass}">
                                <label>${buildRequiredLabel('Opsi Field (Daftar Pilihan / Kotak Centang)')}</label>
                                <textarea class="${getInputClass(opsiFieldTextName)}"
                                    name="${opsiFieldTextName}"
                                    rows="2"
                                    placeholder="Contoh: Ya, Tidak, Mungkin">${escapeHtml(fieldData.opsi_field_text)}</textarea>
                                ${buildInvalidFeedback(opsiFieldTextName)}
                            </div>

                            <div class="${multipleChoiceWrapperClass}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="mb-0">${buildRequiredLabel('Opsi Pilihan Ganda')}</label>
                                    <button type="button" class="btn btn-light btn-sm btn-add-radio-option">
                                        <i class="fas fa-plus"></i> Tambah Opsi
                                    </button>
                                </div>
                                <div class="radio-option-list">
                                    ${radioOptions.map((option, optionIndex) => buildRadioOptionRow(formIndex, fieldIndex, optionIndex, option)).join('')}
                                </div>
                                ${buildInvalidFeedback(radioOptionsName, 'mt-2')}
                                <small class="text-muted d-block mt-2">
                                    Hasil akan ditampilkan seperti: A. Jawaban 1, B. Jawaban 2, dan seterusnya.
                                </small>

                            </div>

                            <div class="form-group">
                                <label>Bantuan / Keterangan</label>
                                <textarea class="${getInputClass(bantuanName)}"
                                    name="${bantuanName}"
                                    rows="2"
                                    placeholder="Catatan tambahan untuk pengguna">${escapeHtml(fieldData.bantuan)}</textarea>
                                ${buildInvalidFeedback(bantuanName)}
                            </div>
                        </div>
                    </div>
                `;
            };

            const buildFormCard = (formIndex, formData = {}) => {
                const formPrefix = `forms[${formIndex}]`;
                const judulFormName = `${formPrefix}[judul_form]`;
                const kodeFormName = `${formPrefix}[kode_form]`;
                const urutanName = `${formPrefix}[urutan]`;
                const deskripsiName = `${formPrefix}[deskripsi]`;
                const fieldsName = `${formPrefix}[fields]`;
                const formCardClass = joinClasses(
                    'card',
                    'border',
                    'assessment-form-card',
                    'mb-4',
                    hasNestedErrors(formPrefix) ? 'border-danger' : ''
                );

                return `
                    <div class="${formCardClass}" data-form-index="${formIndex}" data-field-counter="0">
                        <div class="card-header">
                            <h4>Form ${formIndex + 1}</h4>
                            <div class="assessment-builder-actions">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input"
                                        id="form-active-${formIndex}"
                                        name="forms[${formIndex}][is_active]"
                                        value="1" ${formData.is_active === undefined || normalizeChecked(formData.is_active) ? 'checked' : ''}>
                                    <label class="custom-control-label" for="form-active-${formIndex}">Aktif</label>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-form">
                                    <i class="fas fa-trash-alt"></i> Hapus Form
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>${buildRequiredLabel('Judul Form')}</label>
                                        <input type="text" class="${getInputClass(judulFormName)}"
                                            name="${judulFormName}"
                                            value="${escapeHtml(formData.judul_form)}"
                                            placeholder="Contoh: Profil Peserta"
                                            required>
                                        ${buildInvalidFeedback(judulFormName)}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kode Form</label>
                                        <input type="text" class="${getInputClass(kodeFormName)}"
                                            name="${kodeFormName}"
                                            value="${escapeHtml(formData.kode_form)}"
                                            placeholder="Contoh: FORM-PROFIL">
                                        ${buildInvalidFeedback(kodeFormName)}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Urutan</label>
                                        <input type="number" min="1" class="${getInputClass(urutanName)}"
                                            name="${urutanName}"
                                            value="${escapeHtml(formData.urutan || formIndex + 1)}">
                                        ${buildInvalidFeedback(urutanName)}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Deskripsi Form</label>
                                <textarea class="${getInputClass(deskripsiName)} form-description-input"
                                    name="${deskripsiName}"
                                    rows="2"
                                    placeholder="Deskripsi singkat form">${escapeHtml(formData.deskripsi)}</textarea>
                                ${buildInvalidFeedback(deskripsiName)}
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Daftar Pertanyaan</h6>
                            </div>

                            <div class="${joinClasses('assessment-field-list', hasError(fieldsName) ? 'assessment-invalid-list' : '')}"></div>
                            ${buildInvalidFeedback(fieldsName, 'mt-2')}

                            <div class="text-right mt-3">
                                <button type="button" class="btn btn-primary btn-sm btn-add-field">
                                    <i class="fas fa-plus"></i> Tambah Field
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            };

            const appendField = ($formCard, fieldData = {}) => {
                const formIndex = Number($formCard.data('form-index'));
                const fieldIndex = Number($formCard.attr('data-field-counter'));

                $formCard.find('.assessment-field-list').append(buildFieldCard(formIndex, fieldIndex, fieldData));
                $formCard.attr('data-field-counter', fieldIndex + 1);

                const $fieldCard = $formCard.find('.assessment-field-card').last();
                toggleOptionWrapper($fieldCard);
                updateAutoFieldNameHint($fieldCard);
            };

            const appendForm = (formData = {}) => {
                const formIndex = formIndexCounter++;

                $('#form-builder-list').append(buildFormCard(formIndex, formData));
                const $formCard = $('.assessment-form-card').last();
                const fields = Array.isArray(formData.fields) && formData.fields.length ? formData.fields : [{}];

                fields.forEach((field) => appendField($formCard, field));
                toggleEmptyState();
            };

            const appendRadioOption = ($fieldCard, optionData = {}) => {
                const formIndex = Number($fieldCard.closest('.assessment-form-card').data('form-index'));
                const fieldIndex = Number($fieldCard.data('field-index'));
                const optionIndex = Number($fieldCard.attr('data-radio-option-counter') || 0);
                const normalizedOption = {
                    label: optionData.label || generateChoiceLabel(optionIndex),
                    value: optionData.value || '',
                };

                $fieldCard.find('.radio-option-list').append(buildRadioOptionRow(formIndex, fieldIndex, optionIndex,
                    normalizedOption));
                $fieldCard.attr('data-radio-option-counter', optionIndex + 1);
                reindexRadioOptions($fieldCard);
            };

            const updateRemoveRadioOptionState = ($fieldCard) => {
                const shouldDisable = $fieldCard.find('.multiple-choice-option-row').length <= 2;

                $fieldCard.find('.btn-remove-radio-option')
                    .prop('disabled', shouldDisable)
                    .toggleClass('disabled', shouldDisable);
            };

            const reindexRadioOptions = ($fieldCard) => {
                const formIndex = Number($fieldCard.closest('.assessment-form-card').data('form-index'));
                const fieldIndex = Number($fieldCard.data('field-index'));

                $fieldCard.find('.multiple-choice-option-row').each(function(optionIndex) {
                    const $optionRow = $(this);
                    const generatedLabel = generateChoiceLabel(optionIndex);
                    const $labelInput = $optionRow.find('.radio-option-label');
                    const $valueInput = $optionRow.find('.radio-option-value');

                    $optionRow.attr('data-option-index', optionIndex);
                    $labelInput
                        .attr('name', `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][label]`)
                        .attr('placeholder', generatedLabel);

                    if (!$labelInput.val()?.trim()) {
                        $labelInput.val(generatedLabel);
                    }

                    $valueInput.attr(
                        'name',
                        `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][value]`
                    );
                });

                $fieldCard.attr('data-radio-option-counter', $fieldCard.find('.multiple-choice-option-row').length);
                updateRemoveRadioOptionState($fieldCard);
            };

            const ensureMultipleChoiceOptions = ($fieldCard, minimum = 2) => {
                const optionCount = $fieldCard.find('.multiple-choice-option-row').length;

                if (optionCount >= minimum) {
                    return;
                }

                for (let index = optionCount; index < minimum; index += 1) {
                    appendRadioOption($fieldCard, {
                        label: generateChoiceLabel(index),
                        value: '',
                    });
                }

                reindexRadioOptions($fieldCard);
            };

            const toggleOptionWrapper = ($fieldCard) => {
                const selectedType = $fieldCard.find('.field-type-select').val();
                const showTextOptions = textOptionFieldTypes.includes(selectedType);
                const showMultipleChoiceOptions = selectedType === multipleChoiceFieldType;

                $fieldCard.find('.standard-option-wrapper')
                    .toggleClass('d-none', !showTextOptions)
                    .find('textarea')
                    .prop('disabled', !showTextOptions);

                $fieldCard.find('.multiple-choice-wrapper')
                    .toggleClass('d-none', !showMultipleChoiceOptions)
                    .find('input')
                    .prop('disabled', !showMultipleChoiceOptions);

                if (showMultipleChoiceOptions) {
                    ensureMultipleChoiceOptions($fieldCard);
                } else {
                    updateRemoveRadioOptionState($fieldCard);
                }
            };

            const updateAutoFieldNameHint = ($fieldCard) => {
                const labelValue = $fieldCard.find('.field-label-input').val()?.trim() || '';
                $fieldCard.find('.auto-field-name-hint').html(buildAutoFieldNameHint(labelValue));
            };

            const toggleEmptyState = () => {
                const hasForms = $('.assessment-form-card').length > 0;
                $('#form-builder-empty').toggleClass('d-none', hasForms);
            };

            const parseOptionText = (value) => {
                if (!value) {
                    return [];
                }

                return value
                    .split(/[\r\n,]+/)
                    .map((item) => item.trim())
                    .filter(Boolean);
            };

            const getMultipleChoiceOptions = ($fieldCard) => {
                return $fieldCard.find('.multiple-choice-option-row').map(function(optionIndex) {
                    const $optionRow = $(this);

                    return {
                        label: ($optionRow.find('.radio-option-label').val()?.trim() || generateChoiceLabel(
                            optionIndex)).toUpperCase(),
                        value: $optionRow.find('.radio-option-value').val()?.trim() || '',
                    };
                }).get().filter((option) => option.label || option.value);
            };

            const getBadgeClass = (status) => {
                if (status === 'publish') {
                    return 'success';
                }

                if (status === 'draft') {
                    return 'warning';
                }

                return 'secondary';
            };

            const formatStatusLabel = (status) => {
                if (!status) {
                    return 'Draft';
                }

                return status.charAt(0).toUpperCase() + status.slice(1);
            };

            const sanitizePreviewKey = (value) => {
                return String(value || 'field')
                    .toLowerCase()
                    .replace(/[^a-z0-9_-]/g, '-');
            };

            const isPreviewPanelVisible = () => !$previewPanel.hasClass('d-none');

            const updatePreviewToggleButton = () => {
                const isVisible = isPreviewPanelVisible();

                $previewToggleButton
                    .toggleClass('btn-primary', !isVisible)
                    .toggleClass('btn-secondary', isVisible);

                $previewToggleButton.find('.preview-toggle-label').text(isVisible ? 'Tutup Preview' :
                    'Preview Form');
            };

            const getPreviewState = () => {
                const forms = [];

                $('.assessment-form-card').each(function() {
                    const $formCard = $(this);
                    const isFormActive = $formCard.find('input[name$="[is_active]"]').first().is(':checked');

                    if (!isFormActive) {
                        return;
                    }

                    const formData = {
                        title: $formCard.find('input[name$="[judul_form]"]').val()?.trim() ||
                            'Child form tanpa judul',
                        code: $formCard.find('input[name$="[kode_form]"]').val()?.trim() || '-',
                        description: $formCard.find('.form-description-input').first().val()?.trim() || '',
                        fields: [],
                    };

                    $formCard.find('.assessment-field-card').each(function() {
                        const $fieldCard = $(this);
                        const isFieldActive = $fieldCard.find('input[name$="[is_active]"]').is(':checked');

                        if (!isFieldActive) {
                            return;
                        }

                        formData.fields.push({
                            label: $fieldCard.find('input[name$="[label]"]').val()?.trim() || 'Field tanpa label',
                            description: $fieldCard.find('.field-description-input').val()?.trim() || '',
                            name: slugifyFieldName($fieldCard.find('input[name$="[label]"]').val()?.trim() || ''),
                            type: $fieldCard.find('select[name$="[tipe_field]"]').val() || 'text',
                            placeholder: $fieldCard.find('input[name$="[placeholder]"]').val()?.trim() || '',
                            helpText: $fieldCard.find('textarea[name$="[bantuan]"]').val()?.trim() || '',
                            options: $fieldCard.find('select[name$="[tipe_field]"]').val() === multipleChoiceFieldType ?
                                getMultipleChoiceOptions($fieldCard) :
                                parseOptionText($fieldCard.find('textarea[name$="[opsi_field_text]"]').val()),
                            widthClass: $fieldCard.find('select[name$="[lebar_kolom]"]').val() || 'col-md-6',
                            required: $fieldCard.find('input[name$="[is_required]"]').is(':checked'),
                        });
                    });

                    if (formData.fields.length) {
                        forms.push(formData);
                    }
                });

                return {
                    title: $('input[name="judul"]').val()?.trim() || 'Judul assesment belum diisi',
                    code: $('input[name="kode_assessment"]').val()?.trim() || '-',
                    description: $('textarea[name="deskripsi"]').val()?.trim() || '',
                    instruction: $('textarea[name="petunjuk"]').val()?.trim() || '',
                    status: $('select[name="status"]').val() || 'draft',
                    isActive: $('#assessment-active').is(':checked'),
                    forms: forms,
                };
            };

            const renderPreviewFieldInput = (field, previewKey) => {
                const fieldLabel = `${escapeHtml(field.label)}${field.required ? ' <span class="text-danger">*</span>' : ''}`;
                const placeholder = escapeHtml(field.placeholder);
                let inputHtml = '';

                if (field.type === 'textarea') {
                    inputHtml = `
                        <textarea class="form-control" rows="3" placeholder="${placeholder}"></textarea>
                    `;
                } else if (field.type === 'select') {
                    const options = field.options.length ? field.options : ['Belum ada opsi'];
                    const optionsHtml = options.map((option) => {
                        return `<option value="${escapeHtml(option)}">${escapeHtml(option)}</option>`;
                    }).join('');

                    inputHtml = `
                        <select class="form-control">
                            <option value="" selected>${placeholder || '-- Pilih salah satu --'}</option>
                            ${optionsHtml}
                        </select>
                    `;
                } else if (field.type === 'radio') {
                    const options = field.options.length ? field.options : [{
                        label: 'A',
                        value: 'Belum ada opsi'
                    }];

                    inputHtml = options.map((option, index) => {
                        const optionLabel = typeof option === 'object' ? (option.label || generateChoiceLabel(index)) :
                            generateChoiceLabel(index);
                        const optionValue = typeof option === 'object' ? (option.value || '') : option;
                        const inputId = `${sanitizePreviewKey(previewKey)}-${index}`;

                        return `
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" class="custom-control-input"
                                    id="${inputId}"
                                    name="${sanitizePreviewKey(previewKey)}">
                                <label class="custom-control-label"
                                    for="${inputId}">
                                    ${escapeHtml(optionLabel)}. ${escapeHtml(optionValue)}
                                </label>
                            </div>
                        `;
                    }).join('');
                } else if (field.type === 'checkbox') {
                    const options = field.options.length ? field.options : ['Belum ada opsi'];

                    inputHtml = options.map((option, index) => {
                        const inputId = `${sanitizePreviewKey(previewKey)}-${index}`;

                        return `
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input"
                                    id="${inputId}"
                                    name="${sanitizePreviewKey(previewKey)}[]">
                                <label class="custom-control-label"
                                    for="${inputId}">
                                    ${escapeHtml(option)}
                                </label>
                            </div>
                        `;
                    }).join('');
                } else if (field.type === 'file') {
                    inputHtml = `
                        <div class="custom-file">
                            <input type="file" class="custom-file-input">
                            <label class="custom-file-label">
                                Pilih file
                            </label>
                        </div>
                    `;
                } else {
                    const typeMap = {
                        text: 'text',
                        email: 'email',
                        number: 'number',
                        date: 'date',
                    };

                    inputHtml = `
                        <input type="${typeMap[field.type] || 'text'}" class="form-control"
                            value="" placeholder="${placeholder}">
                    `;
                }

                return `
                    <div class="form-group">
                        <label>${fieldLabel}</label>
                        ${field.description ? `<small class="form-text text-muted mb-2">${escapeHtml(field.description)}</small>` : ''}
                        ${inputHtml}
                        ${field.helpText ? `<small class="form-text text-muted">${escapeHtml(field.helpText)}</small>` : ''}
                    </div>
                `;
            };

            const renderPreview = () => {
                const data = getPreviewState();
                let contentHtml = `
                    <div class="card  border-0 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div class="mb-3">
                                    <div class="text-muted small">Kode Assesment</div>
                                    <div class="font-weight-bold">${escapeHtml(data.code)}</div>
                                </div>
                                <div class="mb-3">
                                    <span class="badge badge-${getBadgeClass(data.status)}">${escapeHtml(formatStatusLabel(data.status))}</span>
                                    <span class="badge badge-${data.isActive ? 'primary' : 'light'}">
                                        ${data.isActive ? 'Aktif' : 'Nonaktif'}
                                    </span>
                                </div>
                            </div>
                            <h3 class="mb-2">${escapeHtml(data.title)}</h3>
                            ${data.description ? `<p class="text-muted mb-3">${escapeHtml(data.description)}</p>` : ''}
                            ${data.instruction ? `
                                <div class="alert alert-light border mb-0">
                                    <div class="font-weight-bold mb-1">Petunjuk Pengisian</div>
                                    <div>${escapeHtml(data.instruction)}</div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;

                if (!data.forms.length) {
                    contentHtml += `
                        <div class="empty-state" data-height="260">
                            <div class="empty-state-icon bg-secondary">
                                <i class="fas fa-eye-slash"></i>
                            </div>
                            <h2>Belum ada form aktif untuk dipreview</h2>
                            <p class="lead mb-0">
                                Aktifkan form dan field yang ingin ditampilkan ke user.
                            </p>
                        </div>
                    `;

                    $previewContent.html(contentHtml);
                    return;
                }

                data.forms.forEach((form, index) => {
                    const fieldsHtml = form.fields.map((field, fieldIndex) => {
                        return `
                            <div class="${escapeHtml(field.widthClass || 'col-md-6')}">
                                ${renderPreviewFieldInput(field, `${form.code || form.title}-${field.name || fieldIndex}`)}
                            </div>
                        `;
                    }).join('');

                    contentHtml += `
                        <div class="card  border-0 mb-4">
                            <div class="card-header bg-white">
                                <div>
                                    <h4 class="mb-1">${escapeHtml(form.title)}</h4>
                                    <small class="text-muted">Bagian ${index + 1} • ${escapeHtml(form.code)}</small>
                                </div>
                            </div>
                            <div class="card-body">
                                ${form.description ? `<p class="text-muted">${escapeHtml(form.description)}</p>` : ''}
                                <div class="row">
                                    ${fieldsHtml}
                                </div>
                            </div>
                        </div>
                    `;
                });

                $previewContent.html(contentHtml);
            };

            const syncPreviewFileInput = () => {
                $previewContent.off('change', '.custom-file-input').on('change', '.custom-file-input',
                    function() {
                        const fileName = this.files && this.files.length ? this.files[0].name :
                            'Pilih file';
                        $(this).next('.custom-file-label').text(fileName);
                    });
            };

            const openPreviewPanel = () => {
                renderPreview();
                syncPreviewFileInput();
                $previewPanel.removeClass('d-none');
                updatePreviewToggleButton();

                $('html, body').animate({
                    scrollTop: $previewPanel.offset().top - 90
                }, 250);
            };

            const closePreviewPanel = () => {
                $previewPanel.addClass('d-none');
                updatePreviewToggleButton();
            };

            const schedulePreviewRender = () => {
                if (!isPreviewPanelVisible()) {
                    return;
                }

                clearTimeout(previewRenderTimer);
                previewRenderTimer = setTimeout(function() {
                    renderPreview();
                    syncPreviewFileInput();
                }, 120);
            };

            $('#btn-add-form').on('click', function() {
                appendForm();
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-add-field', function() {
                appendField($(this).closest('.assessment-form-card'));
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-add-radio-option', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');
                appendRadioOption($fieldCard);
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-remove-form', function() {
                $(this).closest('.assessment-form-card').remove();
                toggleEmptyState();
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-remove-field', function() {
                $(this).closest('.assessment-field-card').remove();
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-remove-radio-option', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');

                if ($fieldCard.find('.multiple-choice-option-row').length <= 2) {
                    return;
                }

                $(this).closest('.multiple-choice-option-row').remove();
                reindexRadioOptions($fieldCard);
                schedulePreviewRender();
            });

            $(document).on('input', '.radio-option-label', function() {
                const sanitizedValue = $(this).val().toUpperCase().replace(/\s+/g, '');
                $(this).val(sanitizedValue);
                schedulePreviewRender();
            });

            $(document).on('input', '.field-label-input', function() {
                updateAutoFieldNameHint($(this).closest('.assessment-field-card'));
            });

            $(document).on('change', '.field-type-select', function() {
                toggleOptionWrapper($(this).closest('.assessment-field-card'));
                schedulePreviewRender();
            });

            $previewToggleButton.on('click', function() {
                if (isPreviewPanelVisible()) {
                    closePreviewPanel();
                    return;
                }

                openPreviewPanel();
            });

            $('.btn-close-preview-panel').on('click', function() {
                closePreviewPanel();
            });

            $('.btn-refresh-preview-panel').on('click', function() {
                renderPreview();
                syncPreviewFileInput();
            });

            $('#assessment-builder-form').on('input change', 'input, textarea, select', function() {
                schedulePreviewRender();
            });

            if (Array.isArray(initialForms) && initialForms.length) {
                initialForms.forEach((form) => appendForm(form));
            } else {
                appendForm();
            }

            updatePreviewToggleButton();
        });
    </script>
@endpush
