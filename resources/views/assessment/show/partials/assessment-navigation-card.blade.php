<x-assessment::ui.card class="border border-[#dce9f4] bg-[#f8fbfe]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h4 class="text-lg font-bold text-slate-900">
                Navigasi Assessment
            </h4>
            <p class="mt-1 text-sm text-slate-500">
                {{ $assessmentItem['form_count'] }} form dan
                {{ $assessmentItem['question_count'] }}
                soal ada pada tahap ini.
                @if ($isLastAssessment)
                    Jika semua sudah final, lanjutkan ke proses selesai assessment.
                @else
                    Jika sudah selesai membaca dan mengisi tahap ini, lanjutkan ke assessment berikutnya.
                @endif
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            @unless ($isFirstAssessment)
                <x-assessment::ui.button type="button" variant="outline" icon="fas fa-arrow-left"
                    class="rounded-xl px-5 py-3" x-bind:disabled="isSubmitting"
                    @click="goToAssessment({{ $assessmentItem['index'] - 1 }})">
                    Assessment Sebelumnya
                </x-assessment::ui.button>
            @endunless

            @if ($isLastAssessment)
                <x-assessment::ui.button type="button" icon="fas fa-flag-checkered" class="rounded-xl px-5 py-3"
                    x-bind:disabled="isSubmitting" @click="showFinishModal = true">
                    Selesai Assessment
                </x-assessment::ui.button>
            @else
                <x-assessment::ui.button type="button" icon="fas fa-arrow-right" class="rounded-xl px-5 py-3"
                    x-bind:disabled="isSubmitting" @click="goToAssessment({{ $assessmentItem['index'] + 1 }})">
                    Next Assessment
                </x-assessment::ui.button>
            @endif
        </div>
    </div>
</x-assessment::ui.card>
