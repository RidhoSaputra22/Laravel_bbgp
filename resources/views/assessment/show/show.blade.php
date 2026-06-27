@extends('assessment.layouts.app')

@section('content')
    @php
        $snapshot = $attempt->structure_snapshot ?? [];
        $totalQuestions = (int) data_get($snapshot, 'meta.total_questions', 0);
        $requiredQuestions = (int) data_get($snapshot, 'meta.required_questions', 0);
        $questionNumber = 0;
    @endphp

    <section class="bg-gradient-to-b from-[#edf6fb] to-white py-[52px] lg:pb-[76px] lg:pt-16">
        <div class="container mx-auto px-4">
            @if ($errors->has('portal'))
                <x-assessment::ui.alert type="danger" class="mb-4">
                    {{ $errors->first('portal') }}
                </x-assessment::ui.alert>
            @endif

            @if ($totalQuestions === 0)
                <x-assessment::ui.alert type="warning" class="mb-4">
                    Assessment ini belum memiliki soal aktif yang bisa dikerjakan.
                </x-assessment::ui.alert>
            @endif

            <x-assessment::ui.hero
                class="mb-[30px] shadow-[0_24px_60px_rgba(11,53,87,0.24)]"
                title="{{ $target->assignment->judul_penugasan }}"
                description="{{ $target->assignment->deskripsi ?: 'Kerjakan seluruh pertanyaan dengan teliti lalu kirim jawaban setelah selesai.' }}"
                pill="Urutan soal sudah diacak khusus untuk attempt Anda"
                pill-icon="fas fa-random"
                gradient="from-[#0c5a90] to-[#0f3557]"
                padding="p-[26px] lg:p-8"
                rightCols="lg:col-span-4 lg:text-right"
                titleClass="mb-2.5 text-[28px] font-bold leading-tight text-white lg:text-[32px]"
                descriptionClass="leading-[1.8] text-white/[0.84]"
                pillClass="mb-[18px] gap-2.5 px-4 py-2.5 text-sm font-semibold sm:text-base"
            >
                <x-slot name="aside">
                    <x-assessment::ui.button
                        :href="route('assessment.portal.dashboard')"
                        variant="white"
                        icon="fas fa-arrow-left"
                        class="font-bold"
                    >
                        Kembali ke Dashboard
                    </x-assessment::ui.button>
                </x-slot>
            </x-assessment::ui.hero>

            <div class="mb-4 grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <x-assessment::ui.card class="h-full">
                        <h4 class="mb-3 text-lg font-bold text-slate-900">Informasi Ujian</h4>

                        <div class="grid gap-[14px] sm:grid-cols-2">
                            <x-assessment::ui.info-tile label="Peserta" :value="$guru->nama_lengkap" />
                            <x-assessment::ui.info-tile label="Sesi" :value="$meta['session_label']" />
                            <x-assessment::ui.info-tile
                                label="Total Soal"
                                :value="$totalQuestions"
                                valueClass="text-[28px] font-bold leading-none text-[#0d3557]"
                            />
                            <x-assessment::ui.info-tile
                                label="Soal Wajib"
                                :value="$requiredQuestions"
                                valueClass="text-[28px] font-bold leading-none text-[#0d3557]"
                            />
                        </div>
                    </x-assessment::ui.card>
                </div>

                <div class="lg:col-span-4">
                    <x-assessment::ui.card class="h-full">
                        <h4 class="mb-3 text-lg font-bold text-slate-900">Catatan Pengerjaan</h4>
                        <p class="mb-2 text-slate-500">
                            Periode penugasan: {{ $meta['date_text'] }}
                        </p>
                        <p class="mb-2 text-slate-500">
                            Status saat ini: {{ $meta['label'] }}
                        </p>
                        <p class="text-slate-500">
                            Pastikan seluruh jawaban sudah final sebelum menekan tombol kirim. Setelah hasil dikirim,
                            halaman ini akan berubah menjadi tampilan hasil assessment.
                        </p>
                    </x-assessment::ui.card>
                </div>
            </div>

            <form
                id="assessment-exam-form"
                action="{{ route('assessment.portal.submit', $target->id) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                @foreach ($snapshot['assessments'] ?? [] as $assessment)
                    <div class="mt-[30px]">
                        <x-assessment::ui.assessment-intro
                            :code="$assessment['kode_assessment']"
                            :title="$assessment['judul']"
                            :description="$assessment['deskripsi'] ?? null"
                            descriptionFallback="Silakan kerjakan seluruh form pada assessment ini."
                            :instruction="$assessment['petunjuk'] ?? null"
                        />

                        @foreach ($assessment['forms'] ?? [] as $form)
                            <x-assessment::ui.card
                                class="mb-[22px]"
                                padding="p-6 sm:p-7"
                                shadow="shadow-[0_18px_50px_rgba(14,63,101,0.10)]"
                            >
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

                                    <x-assessment::form.question
                                        :field="$field"
                                        :number="$questionNumber"
                                        :error="$fieldError"
                                        :old-value="$oldValue"
                                        :checkbox-values="$checkboxValues"
                                    />
                                @endforeach
                            </x-assessment::ui.card>
                        @endforeach
                    </div>
                @endforeach

                @if ($totalQuestions > 0)
                    <x-assessment::ui.card class="mt-2">
                        <div class="grid items-center gap-3 lg:grid-cols-12">
                            <div class="lg:col-span-8">
                                <h4 class="mb-2 text-lg font-bold text-slate-900">
                                    Selesaikan assessment Anda
                                </h4>
                                <p class="text-slate-500">
                                    Periksa kembali jawaban sebelum dikirim. Pertanyaan bertanda
                                    <span class="text-red-600">*</span> wajib diisi.
                                </p>
                            </div>

                            <div class="lg:col-span-4 lg:text-right">
                                <x-assessment::ui.button
                                    type="submit"
                                    icon="fas fa-paper-plane"
                                    minHeight="min-h-[54px]"
                                    rounded="rounded-2xl"
                                    paddingX="px-5"
                                    paddingY="py-3"
                                    class="w-full font-bold"
                                >
                                    Kirim Jawaban Assessment
                                </x-assessment::ui.button>
                            </div>
                        </div>
                    </x-assessment::ui.card>
                @endif
            </form>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                ['wa-chat-container', 'wa-toggle-btn', 'back-to-top'].forEach(function(id) {
                    document.getElementById(id)?.classList.add('hidden');
                });

                const examForm = document.getElementById('assessment-exam-form');

                if (!examForm) {
                    return;
                }

                examForm.addEventListener('submit', function() {
                    const submitButton = examForm.querySelector('button[type="submit"]');

                    if (!submitButton) {
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim jawaban...';
                });
            });
        </script>
    @endpush
@endsection
