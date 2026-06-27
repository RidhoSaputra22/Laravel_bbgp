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
                    'question_count' => $forms->sum(fn($form) => count($form['fields'] ?? [])),
                ];
            })
            ->all();
        $assessmentCount = count($assessmentItems);
        $totalQuestions = (int) data_get($snapshot, 'meta.total_questions', 0);
        $requiredQuestions = (int) data_get($snapshot, 'meta.required_questions', 0);
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
        $formatDateTime = fn($value) => $value ? $value->format('d M Y H:i') . ' WITA' : '-';
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
                    : ($assignmentDeadlineAt
                        ? $formatDateTime($assignmentDeadlineAt)
                        : 'Tanpa batas waktu'),
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

        $assessmentNavigationItems = collect($assessmentItems)
            ->map(fn($assessmentItem) => [
                'index' => $assessmentItem['index'],
                'form_count' => $assessmentItem['form_count'],
                'question_count' => $assessmentItem['question_count'],
            ])
            ->values()
            ->all();

        $errorFieldKey = collect(array_keys($errors->getMessages()))->first(
            fn($key) => str_starts_with($key, 'answers.'),
        );
        $errorFieldId = null;

        if (is_string($errorFieldKey) && preg_match('/^answers\.(\d+)(?:\.|$)/', $errorFieldKey, $matches) === 1) {
            $errorFieldId = (int) $matches[1];
        }

        $errorAssessmentIndex = $errorFieldId !== null ? $assessmentIndexByFieldId[$errorFieldId] ?? null : null;
        $oldActiveAssessmentIndex = old('active_assessment_index');
        $initialAssessmentIndex =
            $assessmentCount > 0
                ? max(
                    0,
                    min(
                        $assessmentCount - 1,
                        is_numeric($errorAssessmentIndex)
                            ? (int) $errorAssessmentIndex
                            : (is_numeric($oldActiveAssessmentIndex)
                                ? (int) $oldActiveAssessmentIndex
                                : 0),
                    ),
                )
                : 0;
    @endphp

    @include('assessment.show.partials.portal-header', ['guru' => $guru])

    <section x-data="assessmentExamFlow({
        initialIndex: {{ $initialAssessmentIndex }},
        totalAssessments: {{ $assessmentCount }},
        assessmentItems: @js($assessmentNavigationItems),
    })" class="grid gap-8 p-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)] lg:gap-10 lg:p-14">
        <div class="space-y-8 lg:space-y-12" x-ref="assessmentFlowTop">
            <form id="assessment-exam-form" x-ref="assessmentExamForm"
                action="{{ route('assessment.portal.submit', $target->id) }}" method="POST"
                enctype="multipart/form-data" novalidate @submit.prevent="handleSubmit($event)">
                @csrf
                <input type="hidden" name="active_assessment_index" x-model="currentAssessmentIndex">

                @if ($assessmentCount === 0)
                    @include('assessment.show.partials.empty-state')
                @endif

                @foreach ($assessmentItems as $assessmentItem)
                    @include('assessment.show.partials.assessment-item', [
                        'assessmentItem' => $assessmentItem,
                        'assessment' => $assessmentItem['data'],
                    ])
                @endforeach

                @if ($assessmentCount > 0)
                    @include('assessment.show.partials.finish-modal', [
                        'assessmentCount' => $assessmentCount,
                        'totalQuestions' => $totalQuestions,
                        'requiredQuestions' => $requiredQuestions,
                    ])
                @endif
            </form>
        </div>

        @include('assessment.show.partials.session-sidebar', [
            'assessmentCount' => $assessmentCount,
            'meta' => $meta,
            'countdownTitle' => $countdownTitle,
            'countdownTargetAt' => $countdownTargetAt,
            'countdownCaption' => $countdownCaption,
            'sessionDetails' => $sessionDetails,
        ])
    </section>

      @include('assessment.show.partials.session-bottom-nav', [
            'assessmentCount' => $assessmentCount,
            'meta' => $meta,
            'countdownTitle' => $countdownTitle,
            'countdownTargetAt' => $countdownTargetAt,
            'countdownCaption' => $countdownCaption,
            'sessionDetails' => $sessionDetails,
        ])

    @include('assessment.show.partials.scripts')
@endsection
