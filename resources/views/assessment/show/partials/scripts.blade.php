@push('scripts')
    <script>
        window.assessmentExamFlow = function(config) {
            return {
                currentAssessmentIndex: Number(config.initialIndex ?? 0),
                totalAssessments: Number(config.totalAssessments ?? 0),
                assessmentItems: Array.isArray(config.assessmentItems) ? config.assessmentItems : [],
                showFinishModal: false,
                isSubmitting: false,

                init() {
                    this.$nextTick(() => {
                        const form = this.formElement();

                        if (!form) {
                            return;
                        }

                        ['input', 'change'].forEach((eventName) => {
                            form.addEventListener(eventName, (event) => {
                                const fieldWrapper = event.target?.closest('[data-assessment-field]');

                                if (!fieldWrapper) {
                                    return;
                                }

                                this.clearFieldError(fieldWrapper);
                            });
                        });
                    });
                },
                formElement() {
                    return this.$refs.assessmentExamForm ?? null;
                },
                getAssessmentPanel(index) {
                    const form = this.formElement();

                    if (!form) {
                        return null;
                    }

                    return form.querySelector(`[data-assessment-panel="${index}"]`);
                },
                openFinishModal() {
                    if (this.isSubmitting) {
                        return;
                    }

                    if (!this.validateCurrentAssessment()) {
                        return;
                    }

                    this.showFinishModal = true;
                },
                submitConfirmedForm() {
                    if (this.isSubmitting) {
                        return;
                    }

                    const validation = this.validateAllAssessments();

                    if (!validation.valid) {
                        this.showFinishModal = false;
                        this.currentAssessmentIndex = validation.assessmentIndex;

                        this.$nextTick(() => {
                            this.focusFieldById(validation.fieldId);
                        });

                        return;
                    }

                    const form = this.formElement();

                    if (!form) {
                        return;
                    }

                    this.isSubmitting = true;
                    form.submit();
                },
                handleSubmit() {
                    if (this.isSubmitting) {
                        return;
                    }

                    if (!this.showFinishModal) {
                        this.openFinishModal();
                        return;
                    }

                    this.submitConfirmedForm();
                },
                isCurrent(index) {
                    return this.currentAssessmentIndex === index;
                },
                currentAssessmentMeta() {
                    return this.assessmentItems[this.currentAssessmentIndex] ?? {
                        index: 0,
                        form_count: 0,
                        question_count: 0,
                    };
                },
                isFirstAssessment() {
                    return this.currentAssessmentIndex <= 0;
                },
                isLastAssessment() {
                    return this.totalAssessments > 0
                        ? this.currentAssessmentIndex >= this.totalAssessments - 1
                        : true;
                },
                progressWidth() {
                    if (this.totalAssessments <= 0) {
                        return 0;
                    }

                    return Math.round(((this.currentAssessmentIndex + 1) / this.totalAssessments) * 100);
                },
                goToAssessment(index) {
                    if (this.isSubmitting || this.totalAssessments <= 0) {
                        return;
                    }

                    const boundedIndex = Math.max(0, Math.min(index, this.totalAssessments - 1));

                    if (boundedIndex === this.currentAssessmentIndex) {
                        return;
                    }

                    if (boundedIndex > this.currentAssessmentIndex && !this.validateCurrentAssessment()) {
                        return;
                    }

                    this.currentAssessmentIndex = boundedIndex;
                    this.showFinishModal = false;

                    this.$nextTick(() => {
                        this.scrollToTop();
                    });
                },
                validateCurrentAssessment() {
                    const validation = this.validateAssessment(this.currentAssessmentIndex);

                    if (validation.valid) {
                        return true;
                    }

                    this.focusFieldById(validation.fieldId);

                    return false;
                },
                validateAllAssessments() {
                    for (let assessmentIndex = 0; assessmentIndex < this.totalAssessments; assessmentIndex += 1) {
                        const validation = this.validateAssessment(assessmentIndex);

                        if (!validation.valid) {
                            return {
                                valid: false,
                                assessmentIndex,
                                fieldId: validation.fieldId,
                            };
                        }
                    }

                    return {
                        valid: true,
                        assessmentIndex: this.currentAssessmentIndex,
                        fieldId: null,
                    };
                },
                validateAssessment(index) {
                    const panel = this.getAssessmentPanel(index);

                    if (!panel) {
                        return {
                            valid: true,
                            fieldId: null,
                        };
                    }

                    const fieldWrappers = Array.from(panel.querySelectorAll('[data-assessment-field]'));

                    for (const fieldWrapper of fieldWrappers) {
                        const validation = this.validateField(fieldWrapper);

                        if (!validation.valid) {
                            return validation;
                        }
                    }

                    return {
                        valid: true,
                        fieldId: null,
                    };
                },
                validateField(fieldWrapper) {
                    this.clearFieldError(fieldWrapper);

                    const fieldId = fieldWrapper.dataset.fieldId ?? null;
                    const fieldType = fieldWrapper.dataset.fieldType ?? 'text';
                    const fieldLabel = fieldWrapper.dataset.fieldLabel ?? 'field ini';
                    const isRequired = fieldWrapper.dataset.required === '1';
                    let message = null;

                    if (fieldType === 'radio') {
                        const inputs = Array.from(fieldWrapper.querySelectorAll('input[type="radio"]'));
                        const hasSelection = inputs.some((input) => input.checked);

                        if (isRequired && !hasSelection) {
                            message = `Pilih satu jawaban untuk pertanyaan ${fieldLabel}.`;
                        }
                    } else if (fieldType === 'checkbox') {
                        const inputs = Array.from(fieldWrapper.querySelectorAll('input[type="checkbox"]'));
                        const hasSelection = inputs.some((input) => input.checked);

                        if (isRequired && !hasSelection) {
                            message = `Minimal pilih satu jawaban untuk pertanyaan ${fieldLabel}.`;
                        }
                    } else if (fieldType === 'file') {
                        const input = fieldWrapper.querySelector('input[type="file"]');
                        const uploadedFile = input?.files?.[0] ?? null;

                        if (isRequired && !uploadedFile) {
                            message = `File untuk pertanyaan ${fieldLabel} wajib diunggah.`;
                        } else if (uploadedFile && uploadedFile.size > 5 * 1024 * 1024) {
                            message = `File untuk pertanyaan ${fieldLabel} maksimal 5 MB.`;
                        }
                    } else {
                        const input = fieldType === 'textarea'
                            ? fieldWrapper.querySelector('textarea')
                            : (fieldType === 'select'
                                ? fieldWrapper.querySelector('select')
                                : fieldWrapper.querySelector('input:not([type="radio"]):not([type="checkbox"]):not([type="file"])'));

                        if (!input) {
                            return {
                                valid: true,
                                fieldId,
                            };
                        }

                        const rawValue = typeof input.value === 'string' ? input.value : '';
                        const value = rawValue.trim();

                        if (isRequired && value === '') {
                            message = `Jawaban untuk pertanyaan ${fieldLabel} wajib diisi.`;
                        } else if (fieldType === 'email' && value !== '' && !this.isValidEmail(value)) {
                            message = `Format email pada pertanyaan ${fieldLabel} tidak valid.`;
                        } else if (fieldType === 'number' && value !== '' && Number.isNaN(Number(value))) {
                            message = `Jawaban pada pertanyaan ${fieldLabel} harus berupa angka.`;
                        } else if (fieldType === 'date' && value !== '' && !this.isValidDate(value)) {
                            message = `Format tanggal pada pertanyaan ${fieldLabel} tidak valid.`;
                        }
                    }

                    if (!message) {
                        return {
                            valid: true,
                            fieldId,
                        };
                    }

                    this.setFieldError(fieldWrapper, message);

                    return {
                        valid: false,
                        fieldId,
                    };
                },
                setFieldError(fieldWrapper, message) {
                    fieldWrapper.classList.add('border-red-500/50', 'bg-red-50/50');

                    const errorElement = fieldWrapper.querySelector('[data-field-error]');

                    if (!errorElement) {
                        return;
                    }

                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                },
                clearFieldError(fieldWrapper) {
                    fieldWrapper.classList.remove('border-red-500/50', 'bg-red-50/50');

                    const errorElement = fieldWrapper.querySelector('[data-field-error]');

                    if (!errorElement) {
                        return;
                    }

                    errorElement.textContent = '';
                    errorElement.classList.add('hidden');
                },
                focusFieldById(fieldId) {
                    if (!fieldId) {
                        this.scrollToTop();

                        return;
                    }

                    const form = this.formElement();

                    if (!form) {
                        return;
                    }

                    const fieldWrapper = form.querySelector(`[data-field-id="${fieldId}"]`);

                    if (!fieldWrapper) {
                        this.scrollToTop();

                        return;
                    }

                    this.scrollToElement(fieldWrapper);

                    const focusTarget = this.resolveFocusTarget(fieldWrapper);

                    if (!focusTarget) {
                        return;
                    }

                    window.setTimeout(() => {
                        focusTarget.focus({
                            preventScroll: true,
                        });
                    }, 180);
                },
                resolveFocusTarget(fieldWrapper) {
                    return fieldWrapper.querySelector(
                        'input:not([type="hidden"]), select, textarea, button'
                    );
                },
                scrollToTop() {
                    const topAnchor = this.$refs.assessmentFlowTop;

                    if (!topAnchor) {
                        return;
                    }

                    this.scrollToElement(topAnchor, 24);
                },
                scrollToElement(element, offset = 120) {
                    const top = element.getBoundingClientRect().top + window.scrollY - offset;

                    window.scrollTo({
                        top: Math.max(top, 0),
                        behavior: 'smooth',
                    });
                },
                isValidEmail(value) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                },
                isValidDate(value) {
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                        return false;
                    }

                    const [year, month, day] = value.split('-').map((part) => Number(part));
                    const date = new Date(year, month - 1, day);

                    if (Number.isNaN(date.getTime())) {
                        return false;
                    }

                    return date.getFullYear() === year
                        && date.getMonth() === month - 1
                        && date.getDate() === day;
                },
            };
        };

        document.addEventListener('DOMContentLoaded', function() {
            ['wa-chat-container', 'wa-toggle-btn', 'back-to-top'].forEach(function(id) {
                document.getElementById(id)?.classList.add('hidden');
            });
        });
    </script>
@endpush
