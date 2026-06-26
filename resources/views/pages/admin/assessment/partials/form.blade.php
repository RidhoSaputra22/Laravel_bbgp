@php
    $builderSeed = old('forms', $formBuilderData ?? []);
    $fieldTypeBadges = $fieldTypes ?? [];
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

<form action="{{ $formAction }}" method="POST">
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
                        <label>Kode Assesment</label>
                        <input type="text" name="kode_assessment" class="form-control"
                            value="{{ old('kode_assessment', $assessment->kode_assessment) }}"
                            placeholder="Contoh: ASM-001">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Judul Assesment</label>
                        <input type="text" name="judul" class="form-control"
                            value="{{ old('judul', $assessment->judul) }}"
                            placeholder="Masukkan judul assesment">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="draft"
                                @selected(old('status', $assessment->status ?: 'draft') == 'draft')>Draft</option>
                            <option value="publish"
                                @selected(old('status', $assessment->status) == 'publish')>Publish</option>
                            <option value="nonaktif"
                                @selected(old('status', $assessment->status) == 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"
                            placeholder="Deskripsi singkat assesment">{{ old('deskripsi', $assessment->deskripsi) }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Petunjuk Pengisian</label>
                        <textarea name="petunjuk" class="form-control" rows="4"
                            placeholder="Petunjuk untuk pengguna form">{{ old('petunjuk', $assessment->petunjuk) }}</textarea>
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
            <h4>Child Form dan Form Field</h4>
            <div class="card-header-action">
                <button type="button" class="btn btn-primary btn-sm" id="btn-add-form">
                    <i class="fas fa-plus"></i> Tambah Child Form
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-light border">
                <div class="font-weight-bold mb-2">Tipe field yang tersedia</div>
                <div>
                    @foreach ($fieldTypeBadges as $key => $label)
                        <span class="badge badge-primary mb-1">{{ $label }} ({{ $key }})</span>
                    @endforeach
                </div>
                <small class="d-block text-muted mt-2">
                    Gunakan koma atau baris baru untuk memisahkan opsi pada tipe field select, radio, dan checkbox.
                </small>
            </div>

            <div id="form-builder-empty" class="empty-state d-none" data-height="220">
                <div class="empty-state-icon bg-primary">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h2>Belum ada child form</h2>
                <p class="lead">Tambahkan child form pertama untuk mulai menyusun struktur assesment dinamis.</p>
            </div>

            <div id="form-builder-list"></div>
        </div>
    </div>

    <div class="text-right">
        <a href="{{ route('assessment.index') }}" class="btn btn-light mr-2">Kembali</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ $submitLabel }}
        </button>
    </div>
</form>

<div class="modal fade" id="assessmentPreviewModal" tabindex="-1" role="dialog" aria-labelledby="assessmentPreviewLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assessmentPreviewLabel">Preview Form User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">
                <div class="alert alert-primary border-0 mb-4">
                    Preview ini menampilkan child form dan form-field aktif seperti yang akan dilihat user.
                </div>
                <div id="assessment-preview-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            const assessmentFieldTypes = @json($fieldTypes);
            const initialForms = @json($builderSeed);
            const optionFieldTypes = ['select', 'radio', 'checkbox'];
            const columnOptions = ['col-md-12', 'col-md-8', 'col-md-6', 'col-md-4'];
            let formIndexCounter = 0;

            const escapeHtml = (value) => $('<div>').text(value ?? '').html();

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

            const buildFieldCard = (formIndex, fieldIndex, fieldData = {}) => {
                const fieldType = fieldData.tipe_field || 'text';
                const showOptions = optionFieldTypes.includes(fieldType);

                return `
                    <div class="card border assessment-field-card mb-3" data-field-index="${fieldIndex}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Form Field ${fieldIndex + 1}</h6>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-field">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Label Field</label>
                                        <input type="text" class="form-control"
                                            name="forms[${formIndex}][fields][${fieldIndex}][label]"
                                            value="${escapeHtml(fieldData.label)}"
                                            placeholder="Contoh: Nama Lengkap">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nama Field</label>
                                        <input type="text" class="form-control"
                                            name="forms[${formIndex}][fields][${fieldIndex}][nama_field]"
                                            value="${escapeHtml(fieldData.nama_field)}"
                                            placeholder="Contoh: nama_lengkap">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tipe Field</label>
                                        <select class="form-control field-type-select"
                                            name="forms[${formIndex}][fields][${fieldIndex}][tipe_field]">
                                            ${buildFieldTypeOptions(fieldType)}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Placeholder</label>
                                        <input type="text" class="form-control"
                                            name="forms[${formIndex}][fields][${fieldIndex}][placeholder]"
                                            value="${escapeHtml(fieldData.placeholder)}"
                                            placeholder="Placeholder field">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nilai Default</label>
                                        <input type="text" class="form-control"
                                            name="forms[${formIndex}][fields][${fieldIndex}][nilai_default]"
                                            value="${escapeHtml(fieldData.nilai_default)}"
                                            placeholder="Opsional">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Lebar Kolom</label>
                                        <select class="form-control"
                                            name="forms[${formIndex}][fields][${fieldIndex}][lebar_kolom]">
                                            ${buildColumnOptions(fieldData.lebar_kolom || 'col-md-6')}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Urutan</label>
                                        <input type="number" min="1" class="form-control"
                                            name="forms[${formIndex}][fields][${fieldIndex}][urutan]"
                                            value="${escapeHtml(fieldData.urutan || fieldIndex + 1)}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group option-field-wrapper ${showOptions ? '' : 'd-none'}">
                                <label>Opsi Field</label>
                                <textarea class="form-control"
                                    name="forms[${formIndex}][fields][${fieldIndex}][opsi_field_text]"
                                    rows="2"
                                    placeholder="Contoh: Ya, Tidak, Mungkin">${escapeHtml(fieldData.opsi_field_text)}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Bantuan / Keterangan</label>
                                <textarea class="form-control"
                                    name="forms[${formIndex}][fields][${fieldIndex}][bantuan]"
                                    rows="2"
                                    placeholder="Catatan tambahan untuk pengguna">${escapeHtml(fieldData.bantuan)}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input"
                                            id="field-required-${formIndex}-${fieldIndex}"
                                            name="forms[${formIndex}][fields][${fieldIndex}][is_required]"
                                            value="1" ${normalizeChecked(fieldData.is_required) ? 'checked' : ''}>
                                        <label class="custom-control-label"
                                            for="field-required-${formIndex}-${fieldIndex}">Field wajib diisi</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input"
                                            id="field-active-${formIndex}-${fieldIndex}"
                                            name="forms[${formIndex}][fields][${fieldIndex}][is_active]"
                                            value="1" ${fieldData.is_active === undefined || normalizeChecked(fieldData.is_active) ? 'checked' : ''}>
                                        <label class="custom-control-label"
                                            for="field-active-${formIndex}-${fieldIndex}">Field aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            };

            const buildFormCard = (formIndex, formData = {}) => {
                return `
                    <div class="card border assessment-form-card mb-4" data-form-index="${formIndex}" data-field-counter="0">
                        <div class="card-header">
                            <h4>Child Form ${formIndex + 1}</h4>
                            <div class="card-header-action">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-form">
                                    <i class="fas fa-trash-alt"></i> Hapus Form
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Judul Child Form</label>
                                        <input type="text" class="form-control"
                                            name="forms[${formIndex}][judul_form]"
                                            value="${escapeHtml(formData.judul_form)}"
                                            placeholder="Contoh: Profil Peserta">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kode Form</label>
                                        <input type="text" class="form-control"
                                            name="forms[${formIndex}][kode_form]"
                                            value="${escapeHtml(formData.kode_form)}"
                                            placeholder="Contoh: FORM-PROFIL">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Urutan</label>
                                        <input type="number" min="1" class="form-control"
                                            name="forms[${formIndex}][urutan]"
                                            value="${escapeHtml(formData.urutan || formIndex + 1)}">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-center">
                                    <div class="custom-control custom-switch mt-4">
                                        <input type="checkbox" class="custom-control-input"
                                            id="form-active-${formIndex}"
                                            name="forms[${formIndex}][is_active]"
                                            value="1" ${formData.is_active === undefined || normalizeChecked(formData.is_active) ? 'checked' : ''}>
                                        <label class="custom-control-label" for="form-active-${formIndex}">Aktif</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Deskripsi Child Form</label>
                                <textarea class="form-control"
                                    name="forms[${formIndex}][deskripsi]"
                                    rows="2"
                                    placeholder="Deskripsi singkat child form">${escapeHtml(formData.deskripsi)}</textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Daftar Form Field</h6>
                                <button type="button" class="btn btn-primary btn-sm btn-add-field">
                                    <i class="fas fa-plus"></i> Tambah Field
                                </button>
                            </div>

                            <div class="assessment-field-list"></div>
                        </div>
                    </div>
                `;
            };

            const appendField = ($formCard, fieldData = {}) => {
                const formIndex = Number($formCard.data('form-index'));
                const fieldIndex = Number($formCard.attr('data-field-counter'));

                $formCard.find('.assessment-field-list').append(buildFieldCard(formIndex, fieldIndex, fieldData));
                $formCard.attr('data-field-counter', fieldIndex + 1);
            };

            const appendForm = (formData = {}) => {
                const formIndex = formIndexCounter++;

                $('#form-builder-list').append(buildFormCard(formIndex, formData));
                const $formCard = $('.assessment-form-card').last();
                const fields = Array.isArray(formData.fields) && formData.fields.length ? formData.fields : [{}];

                fields.forEach((field) => appendField($formCard, field));
                toggleEmptyState();
            };

            const toggleOptionWrapper = ($fieldCard) => {
                const selectedType = $fieldCard.find('.field-type-select').val();
                const shouldShow = optionFieldTypes.includes(selectedType);
                $fieldCard.find('.option-field-wrapper').toggleClass('d-none', !shouldShow);
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

            const getBadgeClass = (status) => {
                if (status === 'publish') {
                    return 'success';
                }

                if (status === 'draft') {
                    return 'warning';
                }

                return 'secondary';
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
                        description: $formCard.find('textarea[name$="[deskripsi]"]').val()?.trim() || '',
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
                            name: $fieldCard.find('input[name$="[nama_field]"]').val()?.trim() || '',
                            type: $fieldCard.find('select[name$="[tipe_field]"]').val() || 'text',
                            placeholder: $fieldCard.find('input[name$="[placeholder]"]').val()?.trim() || '',
                            defaultValue: $fieldCard.find('input[name$="[nilai_default]"]').val()?.trim() || '',
                            helpText: $fieldCard.find('textarea[name$="[bantuan]"]').val()?.trim() || '',
                            options: parseOptionText($fieldCard.find('textarea[name$="[opsi_field_text]"]').val()),
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

            const renderPreviewFieldInput = (field) => {
                const fieldLabel = `${escapeHtml(field.label)}${field.required ? ' <span class="text-danger">*</span>' : ''}`;
                const placeholder = escapeHtml(field.placeholder);
                const defaultValue = escapeHtml(field.defaultValue);
                let inputHtml = '';

                if (field.type === 'textarea') {
                    inputHtml = `
                        <textarea class="form-control" rows="3" placeholder="${placeholder}" disabled>${defaultValue}</textarea>
                    `;
                } else if (field.type === 'select') {
                    const options = field.options.length ? field.options : ['Belum ada opsi'];
                    const optionsHtml = options.map((option) => {
                        const selected = option === field.defaultValue ? 'selected' : '';
                        return `<option value="${escapeHtml(option)}" ${selected}>${escapeHtml(option)}</option>`;
                    }).join('');

                    inputHtml = `
                        <select class="form-control" disabled>
                            <option value="">${placeholder || '-- Pilih salah satu --'}</option>
                            ${optionsHtml}
                        </select>
                    `;
                } else if (field.type === 'radio' || field.type === 'checkbox') {
                    const options = field.options.length ? field.options : ['Belum ada opsi'];
                    const selectedValues = parseOptionText(field.defaultValue);
                    const choiceType = field.type === 'radio' ? 'radio' : 'checkbox';

                    inputHtml = options.map((option, index) => {
                        const isChecked = field.type === 'radio'
                            ? option === field.defaultValue
                            : selectedValues.includes(option);

                        return `
                            <div class="custom-control custom-${choiceType} mb-2">
                                <input type="${choiceType}" class="custom-control-input"
                                    id="preview-${escapeHtml(field.name || 'field')}-${index}"
                                    ${isChecked ? 'checked' : ''} disabled>
                                <label class="custom-control-label"
                                    for="preview-${escapeHtml(field.name || 'field')}-${index}">
                                    ${escapeHtml(option)}
                                </label>
                            </div>
                        `;
                    }).join('');
                } else if (field.type === 'file') {
                    inputHtml = `
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" disabled>
                            <label class="custom-file-label">
                                ${defaultValue || 'Pilih file'}
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
                            value="${defaultValue}" placeholder="${placeholder}" disabled>
                    `;
                }

                return `
                    <div class="form-group">
                        <label>${fieldLabel}</label>
                        ${inputHtml}
                        ${field.helpText ? `<small class="form-text text-muted">${escapeHtml(field.helpText)}</small>` : ''}
                    </div>
                `;
            };

            const renderPreview = () => {
                const data = getPreviewState();
                let contentHtml = `
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div class="mb-3">
                                    <div class="text-muted small">Kode Assesment</div>
                                    <div class="font-weight-bold">${escapeHtml(data.code)}</div>
                                </div>
                                <div class="mb-3">
                                    <span class="badge badge-${getBadgeClass(data.status)}">${escapeHtml(data.status)}</span>
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
                                Aktifkan child form dan field yang ingin ditampilkan ke user.
                            </p>
                        </div>
                    `;

                    $('#assessment-preview-content').html(contentHtml);
                    return;
                }

                data.forms.forEach((form, index) => {
                    const fieldsHtml = form.fields.map((field) => {
                        return `
                            <div class="col-md-6">
                                ${renderPreviewFieldInput(field)}
                            </div>
                        `;
                    }).join('');

                    contentHtml += `
                        <div class="card shadow-sm border-0 mb-4">
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

                $('#assessment-preview-content').html(contentHtml);
            };

            $('#btn-add-form').on('click', function() {
                appendForm();
            });

            $(document).on('click', '.btn-add-field', function() {
                appendField($(this).closest('.assessment-form-card'));
            });

            $(document).on('click', '.btn-remove-form', function() {
                $(this).closest('.assessment-form-card').remove();
                toggleEmptyState();
            });

            $(document).on('click', '.btn-remove-field', function() {
                $(this).closest('.assessment-field-card').remove();
            });

            $(document).on('change', '.field-type-select', function() {
                toggleOptionWrapper($(this).closest('.assessment-field-card'));
            });

            $('#assessmentPreviewModal').on('show.bs.modal', function() {
                renderPreview();
            });

            if (Array.isArray(initialForms) && initialForms.length) {
                initialForms.forEach((form) => appendForm(form));
            } else {
                appendForm();
            }
        });
    </script>
@endpush
