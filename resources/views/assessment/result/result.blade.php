@extends('assessment.layouts.app')

@section('content')
    @php
        $snapshot = $attempt->structure_snapshot ?? [];
        $submittedAt = $attempt->submitted_at?->format('d M Y H:i');
        $durationMinutes = (int) ($summary['duration_minutes'] ?? 0);
        $completionPercentage = (int) ($summary['completion_percentage'] ?? 0);
        $questionNumber = 0;
    @endphp

    <section class="bg-gradient-to-b from-[#eef7fc] to-white py-14 lg:pb-20 lg:pt-[68px]">
        <div class="container mx-auto px-4">
            @if (session('assessment_portal_success'))
                <x-assessment::ui.alert type="success" class="mb-4">
                    {{ session('assessment_portal_success') }}
                </x-assessment::ui.alert>
            @endif

            <x-assessment::ui.hero
                class="mb-[30px] shadow-[0_28px_68px_rgba(18,58,96,0.22)]"
                title="{{ $target->assignment->judul_penugasan }}"
                description="Hasil pengisian Anda tersimpan pada portal assessment. Gunakan halaman ini untuk melihat ringkasan dan jawaban yang sudah dikirim."
                pill="Assessment sudah berhasil dikirim"
                pill-icon="fas fa-check-circle"
                gradient="from-[#1068a6] to-[#123a60]"
                titleClass="mb-2.5 text-[28px] font-bold leading-tight text-white lg:text-[34px]"
                descriptionClass="leading-[1.8] text-white/[0.86]"
                pillClass="mb-[18px] gap-2.5 px-4 py-2.5 text-sm font-bold sm:text-base"
                rightCols="lg:col-span-4 lg:text-right"
            >
                <x-slot name="aside">
                    <x-assessment::ui.button
                        :href="route('assessment.portal.dashboard')"
                        variant="white"
                        icon="fas fa-th-large"
                        class="font-bold"
                    >
                        Kembali ke Dashboard
                    </x-assessment::ui.button>
                </x-slot>
            </x-assessment::ui.hero>

            <x-assessment::ui.card class="mb-4 h-full">
                <div class="mb-4 grid items-center gap-3 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <h3 class="mb-1 text-xl font-bold text-slate-900">
                            Ringkasan Hasil Assessment
                        </h3>
                        <p class="text-slate-500">
                            Peserta: {{ $guru->nama_lengkap }} | Kode Penugasan:
                            {{ $target->assignment->kode_penugasan }}
                        </p>
                    </div>

                    <div class="text-sm text-slate-500 lg:col-span-4 lg:text-right">
                        <div>Dikirim pada: {{ $submittedAt ?: '-' }}</div>
                        <div>Sesi: {{ $meta['session_label'] }}</div>
                        <div>Jadwal: {{ $meta['session_schedule_text'] }}</div>
                    </div>
                </div>

                <div class="grid gap-[14px] sm:grid-cols-2 xl:grid-cols-4">
                    <x-assessment::ui.info-tile
                        label="Total Soal"
                        :value="$summary['total_questions'] ?? 0"
                        valueClass="text-[28px] font-bold leading-none text-[#0d3557]"
                    />
                    <x-assessment::ui.info-tile
                        label="Soal Wajib Terjawab"
                        :value="($summary['answered_required_questions'] ?? 0).'/'.($summary['required_questions'] ?? 0)"
                        valueClass="text-[28px] font-bold leading-none text-[#0d3557]"
                    />
                    <x-assessment::ui.info-tile
                        label="Persentase Terisi"
                        :value="$completionPercentage.'%'"
                        valueClass="text-[28px] font-bold leading-none text-[#0d3557]"
                    />
                    <x-assessment::ui.info-tile
                        label="Durasi Pengerjaan"
                        :value="$durationMinutes.'m'"
                        valueClass="text-[28px] font-bold leading-none text-[#0d3557]"
                    />
                </div>
            </x-assessment::ui.card>

            <div class="mb-4 grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <x-assessment::ui.card class="h-full">
                        <h4 class="mb-3 text-lg font-bold text-slate-900">
                            Breakdown per Assessment
                        </h4>

                        @forelse ($summary['assessment_breakdown'] ?? [] as $assessmentItem)
                            <div class="border-t border-[#ebf1f6] py-[18px] first:border-t-0 first:pt-0">
                                <div class="mb-2 flex flex-wrap justify-between gap-2">
                                    <div>
                                        <div class="font-bold text-slate-900">
                                            {{ $assessmentItem['judul'] }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ $assessmentItem['kode_assessment'] }}
                                        </div>
                                    </div>

                                    <div class="mt-2 font-bold text-[#1376bd] sm:mt-0">
                                        {{ $assessmentItem['answered_questions'] }}/{{ $assessmentItem['total_questions'] }} soal
                                    </div>
                                </div>

                                @foreach ($assessmentItem['forms'] ?? [] as $formItem)
                                    <div class="mb-1 flex justify-between gap-3 text-sm text-slate-500">
                                        <span>{{ $formItem['judul_form'] }}</span>
                                        <span class="shrink-0">
                                            {{ $formItem['answered_questions'] }}/{{ $formItem['total_questions'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <p class="text-slate-500">
                                Belum ada ringkasan assessment yang tersedia.
                            </p>
                        @endforelse
                    </x-assessment::ui.card>
                </div>

                <div class="lg:col-span-7">
                    <x-assessment::ui.card class="h-full">
                        <h4 class="mb-3 text-lg font-bold text-slate-900">
                            Informasi Pengiriman
                        </h4>

                        <x-assessment::ui.detail-row label="Status" :value="$meta['label']" :first="true" />
                        <x-assessment::ui.detail-row label="Periode Penugasan" :value="$meta['date_text']" />
                        <x-assessment::ui.detail-row label="Jadwal Sesi" :value="$meta['session_schedule_text']" />
                        <x-assessment::ui.detail-row
                            label="Jumlah Assessment"
                            :value="$meta['assessment_total'].' assessment / '.$meta['form_total'].' form'"
                        />
                        <x-assessment::ui.detail-row
                            label="Catatan"
                            :value="$meta['description']"
                            valueClass="text-slate-500"
                        />
                    </x-assessment::ui.card>
                </div>
            </div>

            @foreach ($snapshot['assessments'] ?? [] as $assessment)
                <div class="mt-7">
                    <x-assessment::ui.assessment-intro
                        :code="$assessment['kode_assessment']"
                        :title="$assessment['judul']"
                        :description="$assessment['deskripsi'] ?? null"
                        descriptionFallback="Ringkasan jawaban untuk assessment ini ditampilkan di bawah."
                    />

                    @foreach ($assessment['forms'] ?? [] as $form)
                        <x-assessment::ui.card class="mb-[22px]" padding="p-6 sm:p-7">
                            <h4 class="mb-1.5 text-lg font-bold text-[#0d3557]">
                                {{ $form['judul_form'] }}
                            </h4>
                            <p class="mb-4 text-slate-500">
                                {{ $form['deskripsi'] ?: 'Daftar jawaban yang Anda kirim untuk form ini.' }}
                            </p>

                            @foreach ($form['fields'] ?? [] as $field)
                                @php
                                    $questionNumber++;
                                    $answer = $answerLookup[$field['id']] ?? null;
                                @endphp

                                <x-assessment::ui.result-field
                                    :field="$field"
                                    :answer="$answer"
                                    :number="$questionNumber"
                                />
                            @endforeach
                        </x-assessment::ui.card>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>
@endsection
