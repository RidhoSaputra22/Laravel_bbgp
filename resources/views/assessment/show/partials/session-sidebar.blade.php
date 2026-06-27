<aside class="min-w-0 space-y-6 lg:sticky lg:top-6 lg:self-start">
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
            <x-assessment::ui.countdown-timer :title="$countdownTitle" :target-at="$countdownTargetAt" :caption="$countdownCaption"
                expired-label="Waktu sesi sudah habis. Segera pastikan jawaban Anda telah tersimpan."
                fallback-label="Jadwal selesai sesi belum tersedia, sehingga timer belum bisa ditampilkan." />

            @if ($assessmentCount > 0)
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-[#74899c]">
                                Progress Assessment
                            </div>
                            <h2 class="mt-2 text-xl font-bold text-[#0d3557]">
                                Tahap <span x-text="currentAssessmentIndex + 1"></span> dari {{ $assessmentCount }}
                            </h2>
                            <p class="mt-2 text-sm leading-relaxed text-[#6a7e90]">
                                Pantau langkah yang sedang dikerjakan dan lanjutkan assessment secara berurutan sampai
                                tahap terakhir.
                            </p>
                        </div>

                        <div class="rounded-sm bg-white px-4 py-3 text-right shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-[#7a90a3]">
                                Langkah Aktif
                            </div>
                            <div class="mt-1 text-2xl font-bold text-[#1376bd]">
                                <span x-text="currentAssessmentIndex + 1"></span>/{{ $assessmentCount }}
                            </div>
                        </div>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-[#e2edf5]">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#1376bd] to-[#0d5f98] transition-all duration-300"
                            x-bind:style="`width: ${progressWidth()}%`"></div>
                    </div>
                </div>
            @endif

            <div class="divide-y divide-slate-100">
                @foreach ($sessionDetails as $detail)
                    <x-assessment::ui.detail-row :label="$detail['label']" :value="$detail['value']" class="py-3"
                        valueClass="mt-1 font-medium leading-relaxed text-slate-900" />
                @endforeach
            </div>
        </div>
    </x-assessment::ui.card>
</aside>
