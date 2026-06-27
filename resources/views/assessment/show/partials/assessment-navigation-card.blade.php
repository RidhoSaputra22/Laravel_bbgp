<x-assessment::ui.card class="border border-[#dce9f4] bg-[#f8fbfe]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">


        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <x-assessment::ui.button type="button" variant="outline" icon="fas fa-arrow-left"
                x-show="!isFirstAssessment()" x-bind:disabled="isSubmitting"
                @click="goToAssessment(currentAssessmentIndex - 1)">
                Assessment Sebelumnya
            </x-assessment::ui.button>

            <x-assessment::ui.button type="button" icon="fas fa-flag-checkered"
                x-show="isLastAssessment()" x-bind:disabled="isSubmitting" @click="showFinishModal = true">
                Selesai Assessment
            </x-assessment::ui.button>

            <x-assessment::ui.button type="button" icon="fas fa-arrow-right"
                x-show="!isLastAssessment()" x-bind:disabled="isSubmitting"
                @click="goToAssessment(currentAssessmentIndex + 1)">
                Next Assessment
            </x-assessment::ui.button>
        </div>
    </div>
</x-assessment::ui.card>
