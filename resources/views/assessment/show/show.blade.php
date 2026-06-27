@extends('assessment.layouts.app')

@section('content')
    @php
        $snapshot = $attempt->structure_snapshot ?? [];
        $assessmentItems = collect($snapshot['assessments'] ?? [])
            ->values()
            ->map(function ($assessment, $index) {
                $forms = collect($assessment['forms'] ?? [])->values();

                return [
                    'index' => $index,
                    'data' => $assessment,
                    'form_count' => $forms->count(),
                    'question_count' => $forms->sum(fn ($form) => count($form['fields'] ?? [])),
                    'required_count' => $forms->sum(
                        fn ($form) => collect($form['fields'] ?? [])->where('is_required', true)->count()
                    ),
                ];
            })
            ->all();
        $assessmentCount = count($assessmentItems);
        $totalQuestions = (int) data_get($snapshot, 'meta.total_questions', 0);
        $requiredQuestions = (int) data_get($snapshot, 'meta.required_questions', 0);
        $questionNumber = 0;
        $session = $target->session;
        $sessionStartAt = $session?->waktu_mulai;
        $sessionEndAt = $session?->waktu_selesai;
        $attemptStartedAt = $attempt->started_at ?? $target->started_at;
        $assignmentDeadlineAt = $target->assignment->tanggal_selesai?->copy()->endOfDay();
        $countdownTargetAt = $sessionEndAt ?: $assignmentDeadlineAt;
        $countdownTitle = $sessionEndAt ? 'Sisa Waktu Sesi' : 'Sisa Waktu Penugasan';
        $countdownCaption = $sessionEndAt
            ? 'Gunakan hitung mundur ini sebagai acuan sebelum sesi assessment Anda ditutup.'
            : 'Timer mengikuti batas akhir penugasan karena waktu selesai sesi belum tersedia.';
        $formatDateTime = fn ($value) => $value ? $value->format('d M Y H:i').' WITA' : '-';
        $sessionDetails = [
            [
                'label' => 'Label Sesi',
                'value' => $meta['session_label'],
            ],
            [
                'label' => 'Jadwal Sesi',
                'value' => $meta['session_schedule_text'],
            ],
            [
                'label' => 'Mulai Sesi',
                'value' => $formatDateTime($sessionStartAt),
            ],
            [
                'label' => 'Batas Selesai',
                'value' => $sessionEndAt
                    ? $formatDateTime($sessionEndAt)
                    : ($assignmentDeadlineAt ? $formatDateTime($assignmentDeadlineAt) : 'Tanpa batas waktu'),
            ],
            [
                'label' => 'Mulai Dikerjakan',
                'value' => $formatDateTime($attemptStartedAt),
            ],
            [
                'label' => 'Status',
                'value' => $meta['label'],
            ],
            [
                'label' => 'Periode Penugasan',
                'value' => $meta['date_text'],
            ],
        ];
        $assessmentIndexByFieldId = [];

        foreach ($assessmentItems as $assessmentItem) {
            foreach ($assessmentItem['data']['forms'] ?? [] as $form) {
                foreach ($form['fields'] ?? [] as $field) {
                    $assessmentIndexByFieldId[(int) $field['id']] = $assessmentItem['index'];
                }
            }
        }

        $errorFieldKey = collect(array_keys($errors->getMessages()))
            ->first(fn ($key) => str_starts_with($key, 'answers.'));
        $errorFieldId = $errorFieldKey ? (int) str($errorFieldKey)->after('answers.')->before('.') : null;
        $errorAssessmentIndex = $errorFieldId !== null ? ($assessmentIndexByFieldId[$errorFieldId] ?? null) : null;
        $oldActiveAssessmentIndex = old('active_assessment_index');
        $initialAssessmentIndex = $assessmentCount > 0
            ? max(
                0,
                min(
                    $assessmentCount - 1,
                    is_numeric($errorAssessmentIndex)
                        ? (int) $errorAssessmentIndex
                        : (is_numeric($oldActiveAssessmentIndex) ? (int) $oldActiveAssessmentIndex : 0)
                )
            )
            : 0;
    @endphp

    <div>
        <div class="flex justify-between bg-[#1376BD] px-5 py-4 text-white">
            <div>
                <h1 class="text-xl font-medium">
                    Sesi Assessment Dimulai
                </h1>
                <p class="text-xs font-ligth">
                    Baca soal dengan baik lalu isi sesuai keninginan anda
                </p>
            </div>
            <div class="text-right text-sm">
                <div class="font-bold">{{ $guru->nama_lengkap }}</div>
                <div>
                    {{ $guru->satuan_pendidikan ?: '-' }}
                </div>
            </div>
        </div>
    </div>

    <section
        x-data="assessmentExamFlow({
            initialIndex: {{ $initialAssessmentIndex }},
            totalAssessments: {{ $assessmentCount }},
        })"
        class="grid gap-8 p-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)] lg:gap-10 lg:p-14"
    >
        <div class="space-y-8 lg:space-y-12" x-ref="assessmentFlowTop">
            <form
                id="assessment-exam-form"
                action="{{ route('assessment.portal.submit', $target->id) }}"
                method="POST"
                enctype="multipart/form-data"
                @submit.prevent="handleSubmit($event)"
            >
                @csrf
                <input type="hidden" name="active_assessment_index" x-model="currentAssessmentIndex">

                @if ($assessmentCount === 0)
                    <x-assessment::ui.card>
                        <h3 class="text-xl font-bold text-[#0d3557]">
                            Assessment belum tersedia
                        </h3>
                        <p class="mt-2 text-[#6a7e90]">
                            Saat ini belum ada assessment aktif yang bisa dikerjakan pada penugasan ini.
                        </p>
                    </x-assessment::ui.card>
                @endif

                @foreach ($assessmentItems as $assessmentItem)
                    @php
                        $assessment = $assessmentItem['data'];
                    @endphp

                    <div
                        x-show="isCurrent({{ $assessmentItem['index'] }})"
                        style="display: none;"
                        class="space-y-6"
                    >
                        <x-assessment::ui.card class="border border-[#dce9f4] bg-[#f8fbfe]">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#74899c]">
                                        Assessment {{ $loop->iteration }} dari {{ $assessmentCount }}
                                    </div>
                                    <h2 class="text-2xl font-bold text-[#0d3557]">
                                        {{ $assessment['judul'] }}
                                    </h2>
                                    <p class="mt-2 text-sm text-[#6a7e90]">
                                        Kerjakan assessment ini terlebih dahulu, lalu lanjutkan ke assessment berikutnya
                                        melalui tombol navigasi di bawah.
                                    </p>
                                </div>

                                <div class="rounded-sm bg-white px-4 py-3 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-[#7a90a3]">
                                        Progress Langkah
                                    </div>
                                    <div class="mt-1 text-2xl font-bold text-[#1376bd]">
                                        <span x-text="currentAssessmentIndex + 1"></span>/{{ $assessmentCount }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 h-2 overflow-hidden rounded-full bg-[#e2edf5]">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-[#1376bd] to-[#0d5f98] transition-all duration-300"
                                    x-bind:style="`width: ${progressWidth()}%`"
                                ></div>
                            </div>
                        </x-assessment::ui.card>

                        <x-assessment::ui.card>
                            <div class="mb-2 text-sm uppercase text-slate-500">
                                {{ $assessment['kode_assessment'] }}
                            </div>

                            <h3 class="mb-1.5 text-xl font-bold text-[#0d3557]">
                                {{ $assessment['judul'] }}
                            </h3>

                            <p class="mb-2.5 leading-[1.8] text-[#6a7e90]">
                                {{ $assessment['deskripsi'] ?: 'Silakan kerjakan seluruh form pada assessment ini.' }}
                            </p>

                            @if (!empty($assessment['petunjuk']))
                                <div
                                    class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-relaxed text-sky-800">
                                    <strong>Petunjuk:</strong> {{ $assessment['petunjuk'] }}
                                </div>
                            @endif
                        </x-assessment::ui.card>

                        @foreach ($assessment['forms'] ?? [] as $form)
                            <x-assessment::ui.card class="mb-4">
                                <h4 class="mb-1.5 text-lg font-bold text-[#0d3557]">
                                    {{ $form['judul_form'] }}
                                </h4>

                                <div class="mb-[22px] text-[#6c8092]">
                                    {{ $form['deskripsi'] ?: 'Isi pertanyaan pada bagian ini sesuai kondisi terbaru Anda.' }}
                                </div>

                                @foreach ($form['fields'] ?? [] as $field)
                                    @php
                                        $questionNumber++;
                                        $fieldError = $errors->first('answers.'.$field['id']);
                                        $oldValue = old('answers.'.$field['id']);
                                        $checkboxValues = collect(old('answers.'.$field['id'], []))
                                            ->map(fn ($value) => (string) $value)
                                            ->all();
                                    @endphp

                                    <div
                                        class="mb-1 rounded-sm space-y-10  {{ $fieldError ? 'border-red-500/50 bg-red-50/50' : '' }}"
                                    >


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
                                                    :required="(bool) $field['is_required']"
                                                    :error="$fieldError"
                                                />
                                            @break

                                            @case('select')
                                                <x-assessment::form.select
                                                    :name="'answers['.$field['id'].']'"
                                                    placeholder="Pilih jawaban"
                                                    :required="(bool) $field['is_required']"
                                                    :error="$fieldError"
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
                                                    :error="$fieldError"
                                                />
                                            @break

                                            @default
                                                <x-assessment::form.input
                                                    :type="$field['tipe_field'] === 'number' ? 'number' : ($field['tipe_field'] === 'date' ? 'date' : ($field['tipe_field'] === 'email' ? 'email' : 'text'))"
                                                    :name="'answers['.$field['id'].']'"
                                                    :value="$oldValue"
                                                    :placeholder="$field['placeholder'] ?: 'Masukkan jawaban Anda'"
                                                    :required="(bool) $field['is_required']"
                                                    :error="$fieldError"
                                                />
                                        @endswitch

                                        @if ($fieldError)
                                            <div class="mt-2 text-sm text-red-600">
                                                {{ $fieldError }}
                                            </div>
                                        @endif

                                        @if (!empty($field['bantuan']))
                                            <div class="mt-2.5 text-sm leading-[1.8] text-[#6c8092]">
                                                <i class="far fa-lightbulb mr-1"></i>
                                                {{ $field['bantuan'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </x-assessment::ui.card>
                        @endforeach

                        <x-assessment::ui.card class="border border-[#dce9f4] bg-[#f8fbfe]">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">
                                        Navigasi Assessment
                                    </h4>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $assessmentItem['form_count'] }} form dan {{ $assessmentItem['question_count'] }}
                                        soal ada pada tahap ini.
                                        @if ($loop->last)
                                            Jika semua sudah final, lanjutkan ke proses selesai assessment.
                                        @else
                                            Jika sudah selesai membaca dan mengisi tahap ini, lanjutkan ke assessment
                                            berikutnya.
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                    @unless ($loop->first)
                                        <x-assessment::ui.button
                                            type="button"
                                            variant="outline"
                                            icon="fas fa-arrow-left"
                                            class="rounded-xl px-5 py-3"
                                            x-bind:disabled="isSubmitting"
                                            @click="goToAssessment({{ $assessmentItem['index'] - 1 }})"
                                        >
                                            Assessment Sebelumnya
                                        </x-assessment::ui.button>
                                    @endunless

                                    @if ($loop->last)
                                        <x-assessment::ui.button
                                            type="button"
                                            icon="fas fa-flag-checkered"
                                            class="rounded-xl px-5 py-3"
                                            x-bind:disabled="isSubmitting"
                                            @click="showFinishModal = true"
                                        >
                                            Selesai Assessment
                                        </x-assessment::ui.button>
                                    @else
                                        <x-assessment::ui.button
                                            type="button"
                                            icon="fas fa-arrow-right"
                                            class="rounded-xl px-5 py-3"
                                            x-bind:disabled="isSubmitting"
                                            @click="goToAssessment({{ $assessmentItem['index'] + 1 }})"
                                        >
                                            Next Assessment
                                        </x-assessment::ui.button>
                                    @endif
                                </div>
                            </div>
                        </x-assessment::ui.card>
                    </div>
                @endforeach

                @if ($assessmentCount > 0)
                    <x-assessment::ui.modal
                        show="showFinishModal"
                        close-action="if (!isSubmitting) { showFinishModal = false }"
                        title="Konfirmasi Selesai Assessment"
                        description="Setelah dikirim, semua jawaban akan diproses dan halaman akan beralih ke hasil assessment."
                    >
                        <div class="space-y-4">
                            <div class="rounded-sm border border-[#dce9f4] bg-[#f8fbfe] p-4">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-sm bg-[#1376bd] text-white">
                                        <i class="fas fa-paper-plane"></i>
                                    </div>

                                    <div>
                                        <h4 class="text-base font-bold text-slate-900">
                                            Pastikan semua jawaban sudah final
                                        </h4>
                                        <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                            Sistem akan mengirim seluruh jawaban dari {{ $assessmentCount }} assessment
                                            sekaligus. Jika masih ada isian wajib yang kosong atau tidak valid, Anda akan
                                            dikembalikan ke assessment yang perlu diperbaiki.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-sm bg-[#f4f9fc] p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[#72879b]">
                                        Total Assessment
                                    </div>
                                    <div class="mt-2 text-3xl font-bold text-[#0d3557]">
                                        {{ $assessmentCount }}
                                    </div>
                                </div>

                                <div class="rounded-sm bg-[#f4f9fc] p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[#72879b]">
                                        Total Soal
                                    </div>
                                    <div class="mt-2 text-3xl font-bold text-[#0d3557]">
                                        {{ $totalQuestions }}
                                    </div>
                                </div>

                                <div class="rounded-sm bg-[#f4f9fc] p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[#72879b]">
                                        Soal Wajib
                                    </div>
                                    <div class="mt-2 text-3xl font-bold text-[#0d3557]">
                                        {{ $requiredQuestions }}
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm leading-relaxed text-slate-500">
                                Tekan tombol kirim jika Anda siap menyelesaikan seluruh assessment pada penugasan ini.
                            </p>
                        </div>

                        <x-slot name="footer">
                            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <x-assessment::ui.button
                                    type="button"
                                    variant="outline"
                                    class="rounded-xl px-5 py-3"
                                    x-bind:disabled="isSubmitting"
                                    @click="showFinishModal = false"
                                >
                                    Kembali Cek Jawaban
                                </x-assessment::ui.button>

                                <x-assessment::ui.button
                                    type="submit"
                                    class="rounded-xl px-5 py-3"
                                    x-bind:disabled="isSubmitting"
                                >
                                    <span x-show="!isSubmitting" class="inline-flex items-center">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Ya, Kirim Jawaban
                                    </span>

                                    <span x-show="isSubmitting" class="inline-flex items-center">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        Mengirim jawaban...
                                    </span>
                                </x-assessment::ui.button>
                            </div>
                        </x-slot>
                    </x-assessment::ui.modal>
                @endif
            </form>
        </div>

        <aside class="min-w-0 lg:sticky lg:top-6 lg:self-start">
            <x-assessment::ui.card class="overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-slate-300 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-clock text-[#1376BD]" aria-hidden="true"></i>
                        <h1 class="text-md font-medium">Informasi Sesi</h1>
                    </div>

                    <x-assessment::ui.status-badge :tone="$meta['badge']" class="rounded-full px-3 py-1">
                        {{ $meta['label'] }}
                    </x-assessment::ui.status-badge>
                </div>

                <div class="space-y-5 px-6 py-5">
                    <x-assessment::ui.countdown-timer
                        :title="$countdownTitle"
                        :target-at="$countdownTargetAt"
                        :caption="$countdownCaption"
                        expired-label="Waktu sesi sudah habis. Segera pastikan jawaban Anda telah tersimpan."
                        fallback-label="Jadwal selesai sesi belum tersedia, sehingga timer belum bisa ditampilkan."
                    />

                    <div class="divide-y divide-slate-100">
                        @foreach ($sessionDetails as $detail)
                            <x-assessment::ui.detail-row
                                :label="$detail['label']"
                                :value="$detail['value']"
                                class="py-3"
                                valueClass="mt-1 font-medium leading-relaxed text-slate-900"
                            />
                        @endforeach
                    </div>

                    @if ($assessmentCount > 0)
                        <div class="border-t border-slate-200 pt-5">
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">
                                        Urutan Assessment
                                    </h3>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                        Kerjakan assessment satu per satu hingga tahap terakhir.
                                    </p>
                                </div>

                                <div class="rounded-full bg-[#eaf5fb] px-3 py-1 text-xs font-semibold text-[#0d5f98]">
                                    <span x-text="currentAssessmentIndex + 1"></span>/{{ $assessmentCount }}
                                </div>
                            </div>

                            <div class="space-y-3">
                                @foreach ($assessmentItems as $assessmentItem)
                                    <div
                                        class="rounded-sm border px-4 py-4 transition"
                                        x-bind:class="stepClasses({{ $assessmentItem['index'] }})"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold transition"
                                                x-bind:class="stepCircleClasses({{ $assessmentItem['index'] }})"
                                            >
                                                {{ $loop->iteration }}
                                            </div>

                                            <div class="min-w-0">
                                                <div class="font-semibold text-slate-900">
                                                    {{ $assessmentItem['data']['judul'] }}
                                                </div>
                                                <div class="mt-1 text-xs text-slate-500">
                                                    {{ $assessmentItem['form_count'] }} form •
                                                    {{ $assessmentItem['question_count'] }} soal
                                                </div>
                                                <div
                                                    class="mt-2 text-xs font-medium"
                                                    x-bind:class="stepLabelClasses({{ $assessmentItem['index'] }})"
                                                    x-text="stepLabel({{ $assessmentItem['index'] }})"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </x-assessment::ui.card>
        </aside>
    </section>

    @push('scripts')
        <script>
            window.assessmentExamFlow = function(config) {
                return {
                    currentAssessmentIndex: Number(config.initialIndex ?? 0),
                    totalAssessments: Number(config.totalAssessments ?? 0),
                    showFinishModal: false,
                    isSubmitting: false,
                    handleSubmit(event) {
                        if (this.isSubmitting) {
                            return;
                        }

                        if (!this.showFinishModal) {
                            this.showFinishModal = true;
                            return;
                        }

                        this.isSubmitting = true;
                        event.target.submit();
                    },
                    isCurrent(index) {
                        return this.currentAssessmentIndex === index;
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
                    stepClasses(index) {
                        if (index < this.currentAssessmentIndex) {
                            return 'border-[#cfe3f3] bg-[#f4faff]';
                        }

                        if (index === this.currentAssessmentIndex) {
                            return 'border-[#1376bd] bg-[#eff7ff] shadow-sm';
                        }

                        return 'border-slate-200 bg-white';
                    },
                    stepCircleClasses(index) {
                        if (index < this.currentAssessmentIndex) {
                            return 'bg-[#1376bd] text-white';
                        }

                        if (index === this.currentAssessmentIndex) {
                            return 'bg-[#0d3557] text-white';
                        }

                        return 'bg-slate-100 text-slate-500';
                    },
                    stepLabel(index) {
                        if (index < this.currentAssessmentIndex) {
                            return 'Tahap sebelumnya';
                        }

                        if (index === this.currentAssessmentIndex) {
                            return 'Sedang dikerjakan';
                        }

                        return 'Menunggu giliran';
                    },
                    stepLabelClasses(index) {
                        if (index < this.currentAssessmentIndex) {
                            return 'text-[#1376bd]';
                        }

                        if (index === this.currentAssessmentIndex) {
                            return 'text-[#0d3557]';
                        }

                        return 'text-slate-400';
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
@endsection
