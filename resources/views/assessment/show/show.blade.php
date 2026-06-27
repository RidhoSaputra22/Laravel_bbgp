@extends('assessment.layouts.app')



@section('content')
    @php
        $participantName = Auth::user()?->name ?? 'Peserta';
        $participantInitials = str($participantName)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn($part) => str($part)->substr(0, 1)->upper())
            ->implode('');
        $totalQuestions = count($examPayload['questions'] ?? []);
        $questionNavPageSize = 16;
    @endphp

    <div class="relative ">
        <div class="bg-white min-h-screen lg:hidden ">
            <div
                class="block  bg-white w-full h-14 sticky top-0 z-50 py-3 px-5  flex justify-between items-center shadow-sm">
                <div class="">
                    <p class="text-sm font-semibold text-deep-navy">NeoEduBrain</p>
                    <p class="text-xs text-slate-600">{{ $kuis->judul }}</p>
                </div>
                <div class="font-mono ">

                    @include('lms.components.timer', ['seconds' => $remainingSeconds])

                </div>

            </div>
            <div class=" min-h-[91.5vh] flex flex-col  p-4 space-y-4 **:text-xs ">

                <div class="space-y-1 ">
                    <div class="border-b border-slate-200 pb-4 ">

                        <p id="exam-question-counter" class="mt-2 text-sm font-semibold text-deep-navy">
                            Soal 1 dari {{ max($totalQuestions, 1) }}
                        </p>

                    </div>

                    <div id="exam-question-panel" class="question-panel max-h-[530px] ">
                        <div class="relative ">
                            <div id="exam-question-image-wrapper"
                                class="mb-4 h-52 overflow-clip overflow-y-scroll rounded-md border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-3 flex justify-end gap-2 absolute  bottom-0 right-3">
                                    <button type="button" data-exam-question-image-zoom-out
                                        class="rounded border border-slate-300 bg-white px-2 py-1 text-xs font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        aria-label="Perkecil gambar" title="Zoom out" disabled>
                                        -
                                    </button>
                                    <button type="button" data-exam-question-image-zoom-in
                                        class="rounded border border-slate-300 bg-white px-2 py-1 text-xs font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        aria-label="Perbesar gambar" title="Zoom in" disabled>
                                        +
                                    </button>
                                </div>
                                <div data-exam-question-image-viewport class="overflow-x-auto">
                                    <img id="exam-question-image" src="" alt="Gambar pendukung soal"
                                        class="mx-auto block max-h-96 w-full object-contain transition-all duration-150"
                                        loading="lazy">
                                </div>
                            </div>
                        </div>

                        <div id="exam-question-description" class=" text-sm  text-slate-700">
                        </div>
                    </div>
                </div>
                <span class="flex-1"></span>
                <div class="space-y-4 flex-1">
                    <div id="exam-answer-panel" class="space-y-3"></div>

                    <div id="exam-submit-error"
                        class="hidden rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    </div>


                </div>
            </div>
            <div class="sticky bg-white bottom-0 p-4 **:text-xs shadow-sm space-y-3">
                <p class="text-sm text-slate-500">Periksa kembali pilihan sebelum jawaban dikirim.
                </p>

                <div x-data="examQuestionPager({ totalQuestions: {{ $totalQuestions }}, pageSize: {{ $questionNavPageSize }} })" @exam-question-nav-sync.window="sync($event.detail)">
                    <div class="flex justify-end  gap-3">
                        <button type="button" id="exam-prev-button"
                            class="flex items-center gap-2 rounded-md border border-primary bg-white px-4 py-2 font-medium text-primary disabled:cursor-not-allowed disabled:opacity-50">
                            <div>Previous</div>
                        </button>

                        <button type="button" id="exam-next-button"
                            class="flex cursor-pointer items-center gap-2 rounded-md bg-primary px-4 py-2 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60">
                            <div id="exam-next-button-label">Next</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <div id="exam-shell"
            class="hidden sm:block  sm:min-h-screen sm:flex sm:justify-center sm:items-center bg-slate-100 p-4 **:text-xs md:p-6 md:**:text-sm lg:p-8">
            <div id="exam-content" class="w-full space-y-4 md:px-6 lg:px-8 ">
                <div class="grid gap-4 sm:grid-cols-[minmax(0,1.45fr)_minmax(220px,0.7fr)_minmax(260px,0.85fr)]">
                    <x-card header_text="Ruang Ujian" panel_style="col-span-full xl:col-span-1">
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <p class="text-2xl font-semibold text-deep-navy">NeoEduBrain</p>
                                <p class="text-base text-slate-600">{{ $kuis->judul }}</p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <div
                                    class="rounded-md border border-primary/15 bg-primary/5 px-3 py-2 text-sm font-medium text-primary">
                                    Ujian aktif
                                </div>
                                @unless ($quizSecurityEnabled ?? true)
                                    <div
                                        class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700">
                                        Mode dev debug: guard kuis dimatikan
                                    </div>
                                @endunless
                                <div
                                    class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                    {{ $totalQuestions }} soal tersedia
                                </div>
                            </div>
                        </div>
                    </x-card>

                    <x-card header_text="Waktu Ujian" panel_style="col-span-1 xl:col-span-1">
                        <div class="space-y-3">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">Sisa waktu</p>
                            <div class="text-2xl font-semibold text-deep-navy">
                                @include('lms.components.timer', ['seconds' => $remainingSeconds])
                            </div>
                            <p class="text-sm text-slate-500">Jawaban akan dikirim sekaligus saat ujian diselesaikan.</p>
                        </div>
                    </x-card>

                    <x-card header_text="Status Peserta" panel_style="col-span-2 xl:col-span-1">
                        <div class="space-y-4">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3">
                                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">IP Address</p>
                                    <p class="mt-2 text-sm font-semibold text-deep-navy">{{ $user_session->ip_address }}</p>
                                </div>
                                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-3">
                                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-emerald-700">Sesi</p>
                                    <p class="mt-2 text-sm font-semibold uppercase text-emerald-700">
                                        {{ $user_session->status }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm font-mono text-red-600">
                                <p id="exam-serious-violation-indicator" class="font-semibold text-red-600">Pelanggaran: 0/3
                                </p>
                                <p id="exam-chances-indicator" class="text-slate-500">Sisa kesempatan: 3</p>

                            </div>
                        </div>
                    </x-card>
                </div>


                <div class="grid gap-4 xl:grid-cols-[280px_minmax(0,1fr)] xl:items-stretch">
                    <div class="flex relative">
                        <x-card header_text="Peserta" class="flex w-full flex-col  " panel_style="h-fit sticky top-0"
                            body_class="flex h-full flex-col" x-data="examQuestionPager({ totalQuestions: {{ $totalQuestions }}, pageSize: {{ $questionNavPageSize }}, measureGrid: true, rows: 4, minButtonWidth: 48 })"
                            @exam-question-nav-sync.window="sync($event.detail)">
                            <div class="flex h-full flex-col gap-4">
                                <div
                                    class="flex h-40 items-center justify-center rounded-md bg-primary text-4xl font-semibold text-white">
                                    {{ $participantInitials ?: 'PS' }}
                                </div>

                                <div class="space-y-1 text-center">
                                    <p class="text-sm text-slate-500">ID Peserta</p>
                                    <p class="text-base font-semibold text-deep-navy">1102-7878-23299988</p>
                                    <p class="text-sm uppercase tracking-wide text-slate-600">{{ $participantName }}</p>
                                </div>

                                <div class="flex flex-1 flex-col  ">

                                    <div class="mt-4 flex-1">

                                        <div x-ref="navGrid" class="grid grid-cols-4 gap-3 content-start"
                                            style="grid-template-columns: repeat(var(--exam-nav-columns, 4), minmax(0, 1fr));"
                                            x-cloak x-show="hasQuestions()">
                                            <template x-for="question in visibleQuestions()" :key="question.index">
                                                <button type="button"
                                                    class="inline-flex h-12 w-full items-center justify-center rounded-lg border text-sm font-semibold shadow-sm transition duration-150 transform-gpu hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.97] focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 disabled:shadow-none disabled:hover:translate-y-0"
                                                    :class="questionButtonClass(question)"
                                                    :aria-label="`Buka soal nomor ${question.number}`"
                                                    :aria-current="question.current ? 'step' : null"
                                                    :disabled="isSubmitting" @click="goToQuestion(question.index)"
                                                    x-text="question.number"></button>
                                            </template>
                                        </div>

                                        <div x-cloak x-show="!hasQuestions()"
                                            class="rounded-md border border-dashed border-slate-300 bg-white px-3 py-4 text-sm text-slate-500">
                                            Soal belum tersedia.
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </x-card>
                    </div>

                    <div class="space-y-4">
                        <div id="exam-empty-state"
                            class="hidden rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-800">
                            Soal untuk kuis ini belum tersedia.
                        </div>

                        <div id="exam-main-panel" class="space-y-4">
                            <x-card header_text="Soal Saat Ini">

                                <div class="space-y-4">
                                    <div
                                        class="flex flex-col gap-3 border-b border-slate-200 pb-4 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">
                                                Pertanyaan
                                            </p>
                                            <p id="exam-question-counter"
                                                class="mt-2 text-sm font-semibold text-deep-navy">
                                                Soal 1 dari {{ max($totalQuestions, 1) }}
                                            </p>
                                        </div>

                                        <div
                                            class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                            Pilih satu jawaban terbaik.
                                        </div>
                                    </div>

                                    <div id="exam-question-panel" class="question-panel max-h-[530px] overflow-y-auto">
                                        <div id="exam-question-image-wrapper"
                                            class="mb-4 hidden  rounded-md border border-slate-200 bg-slate-50 p-3">
                                            <div class="mb-3 flex justify-end gap-2">
                                                <button type="button" data-exam-question-image-zoom-out
                                                    class="rounded border border-slate-300 bg-white px-2 py-1 text-xs font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                                    aria-label="Perkecil gambar" title="Zoom out" disabled>
                                                    -
                                                </button>
                                                <button type="button" data-exam-question-image-zoom-in
                                                    class="rounded border border-slate-300 bg-white px-2 py-1 text-xs font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                                    aria-label="Perbesar gambar" title="Zoom in" disabled>
                                                    +
                                                </button>
                                            </div>
                                            <div data-exam-question-image-viewport class="overflow-x-auto">
                                                <img id="exam-question-image" src="" alt="Gambar pendukung soal"
                                                    class="mx-auto block max-h-96 w-full object-contain transition-all duration-150"
                                                    loading="lazy">
                                            </div>
                                        </div>

                                        <div id="exam-question-description"
                                            class="space-y-3 text-sm leading-7 text-slate-700">
                                        </div>
                                    </div>
                                </div>

                            </x-card>

                            <x-card header_text="Jawaban">
                                <div class="space-y-4">
                                    <div id="exam-answer-panel" class="space-y-3"></div>

                                    <div id="exam-submit-error"
                                        class="hidden rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                    </div>

                                    <div
                                        class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-sm text-slate-500">Periksa kembali pilihan sebelum jawaban dikirim.
                                        </p>

                                        <div x-data="examQuestionPager({ totalQuestions: {{ $totalQuestions }}, pageSize: {{ $questionNavPageSize }} })" @exam-question-nav-sync.window="sync($event.detail)">
                                            <div x-cloak x-show="hasQuestions()" class="flex justify-between mb-3 gap-3">
                                                <button type="button" @click="goToPreviousPage()"
                                                    :disabled="!canGoToPreviousPage() || isSubmitting"
                                                    class="inline-flex h-12 w-12 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 shadow-sm transition duration-150 transform-gpu hover:-translate-y-0.5 hover:border-primary/40 hover:bg-primary/5 hover:text-primary active:translate-y-0 active:scale-[0.97] active:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none disabled:hover:translate-y-0 disabled:hover:border-slate-300 disabled:hover:bg-white disabled:hover:text-slate-700">
                                                    <x-heroicon-o-chevron-left class="h-5 w-5" />
                                                </button>
                                                <button type="button" @click="goToNextPage()"
                                                    :disabled="!canGoToNextPage() || isSubmitting"
                                                    class="inline-flex h-12 w-12 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 shadow-sm transition duration-150 transform-gpu hover:-translate-y-0.5 hover:border-primary/40 hover:bg-primary/5 hover:text-primary active:translate-y-0 active:scale-[0.97] active:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none disabled:hover:translate-y-0 disabled:hover:border-slate-300 disabled:hover:bg-white disabled:hover:text-slate-700">
                                                    <x-heroicon-o-chevron-right class="h-5 w-5" />
                                                </button>

                                                <button type="button" id="exam-prev-button"
                                                    class="flex items-center gap-2 rounded-md border border-primary bg-white px-4 py-2 font-medium text-primary disabled:cursor-not-allowed disabled:opacity-50">
                                                    <div>Previous</div>
                                                </button>

                                                <button type="button" id="exam-next-button"
                                                    class="flex cursor-pointer items-center gap-2 rounded-md bg-primary px-4 py-2 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60">
                                                    <div id="exam-next-button-label">Next</div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </x-card>
                        </div>
                    </div>
                </div>
            </div>

            <div id="exam-submit-overlay" role="alertdialog" aria-modal="true" aria-live="polite"
                class="fixed inset-0 z-[9998] hidden items-center justify-center bg-black/55 px-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-md bg-white p-6 shadow-2xl">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 h-10 w-10 animate-spin rounded-md border-4 border-primary/20 border-t-primary">
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary">Mengirim Jawaban</p>
                            <h2 class="text-xl font-semibold text-gray-900">Jawaban sedang disiapkan</h2>
                            <p id="exam-submit-overlay-message" class="text-sm leading-6 text-gray-600">
                                Mohon tunggu sebentar. Setelah ini anda akan diarahkan ke halaman hasil.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="exam-warning-overlay" role="alertdialog" aria-modal="true" aria-live="assertive"
                class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/55 px-4 backdrop-blur-sm">
                <div class="w-full max-w-lg rounded-md bg-white p-6 shadow-2xl">
                    <div class="space-y-3">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-600">Peringatan Ujian</p>
                        <h2 class="text-2xl font-semibold text-gray-900">Tetap di halaman ujian</h2>
                        <p id="exam-warning-message" class="text-sm leading-6 text-gray-600">
                            Anda harus tetap fokus pada halaman ujian. Jangan buka tab lain, refresh, atau keluar dari mode
                            ujian.
                        </p>

                        <p id="exam-warning-violations" class="text-sm font-semibold text-red-600">Pelanggaran sengaja:
                            0/3
                        </p>
                        <p id="exam-warning-chances" class="text-sm text-gray-500">Sisa kesempatan: 3</p>
                        <p id="exam-warning-type" class="text-sm font-semibold text-red-600">Tipe: Sengaja</p>
                        <p id="exam-warning-only-count" class="text-sm text-amber-600">Total peringatan: 0</p>

                        <p id="exam-warning-timer" class="text-sm font-medium text-primary">
                            Mohon tunggu sebentar...
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button" id="exam-warning-button"
                            class="rounded-md bg-primary px-5 py-2 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60"
                            disabled>
                            Tunggu...
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <template id="exam-page-payload">{!! \Illuminate\Support\Js::encode(array_merge($examPayload, ['csrfToken' => csrf_token()])) !!}</template>
    <template id="exam-security-payload">{!! \Illuminate\Support\Js::encode(
        $quizSecurityEnabled ?? true
            ? [
                'examId' => $kuis->id,
                'csrfToken' => csrf_token(),
                'disqualifyUrl' => route('kuis.disqualify', ['kuis' => $kuis]),
                'fallbackRedirectUrl' => route('paket.show', ['paket' => $kuis->paket_pembelajaran]),
            ]
            : null,
    ) !!}</template>
@endsection
