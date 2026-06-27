@push('scripts')
    <script>
        window.assessmentExamFlow = function(config) {
            return {
                currentAssessmentIndex: Number(config.initialIndex ?? 0),
                totalAssessments: Number(config.totalAssessments ?? 0),
                assessmentItems: Array.isArray(config.assessmentItems) ? config.assessmentItems : [],
                showFinishModal: false,
                isSubmitting: false,
                submitConfirmedForm() {
                    if (this.isSubmitting) {
                        return;
                    }

                    const form = this.$refs.assessmentExamForm;

                    if (!form) {
                        return;
                    }

                    this.isSubmitting = true;
                    form.submit();
                },
                handleSubmit(event) {
                    if (this.isSubmitting) {
                        return;
                    }

                    if (!this.showFinishModal) {
                        this.showFinishModal = true;
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
                    this.currentAssessmentIndex = boundedIndex;
                    this.showFinishModal = false;

                    this.$nextTick(() => {
                        const topAnchor = this.$refs.assessmentFlowTop;

                        if (!topAnchor) {
                            return;
                        }

                        const top = topAnchor.getBoundingClientRect().top + window.scrollY - 24;
                        window.scrollTo({
                            top,
                            behavior: 'smooth',
                        });
                    });
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
