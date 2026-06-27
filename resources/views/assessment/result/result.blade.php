@extends('assessment.layouts.app')

@section('content')
    @php
        $snapshot = $attempt->structure_snapshot ?? [];
        $submittedAt = $attempt->submitted_at?->format('d M Y H:i');
        $durationMinutes = (int) ($summary['duration_minutes'] ?? 0);
        $completionPercentage = (int) ($summary['completion_percentage'] ?? 0);
        $questionNumber = 0;
    @endphp

    <section class="bg-gradient-to-b from-[#eef7fc] to-white py-14 lg:py-[68px] lg:pb-20">
        <div class="container mx-auto px-4">
            @if (session('assessment_portal_success'))
                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('assessment_portal_success') }}
                </div>
            @endif

            <div
                class="mb-[30px] rounded-[30px] bg-gradient-to-br from-[#1068a6] to-[#123a60] p-[26px] text-white shadow-[0_28px_68px_rgba(18,58,96,0.22)] lg:p-[34px]">
                <div class="grid items-center gap-6 lg:grid-cols-12 lg:gap-8">
                    <div class="lg:col-span-8">
                        <div
                            class="mb-[18px] inline-flex items-center gap-2.5 rounded-full bg-white/[0.14] px-4 py-2.5 text-sm font-bold sm:text-base">
                            <i class="fas fa-check-circle"></i>
                            Assessment sudah berhasil dikirim
                        </div>

                        <h1 class="mb-2.5 text-[28px] font-bold leading-tight text-white lg:text-[34px]">
                            {{ $target->assignment->judul_penugasan }}
                        </h1>

                        <p class="leading-[1.8] text-white/[0.86]">
                            Hasil pengisian Anda tersimpan pada portal assessment. Gunakan halaman ini untuk melihat
                            ringkasan dan jawaban yang sudah dikirim.
                        </p>
                    </div>

                    <div class="lg:col-span-4 lg:text-right">
                        <a
                            href="{{ route('assessment.dashboard') }}"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-bold text-[#0d5f98] transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-white/30"
                        >
                            <i class="fas fa-th-large mr-2"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <div class="mb-4 h-full rounded-[24px] bg-white p-6 shadow-[0_18px_48px_rgba(14,63,101,0.10)]">
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
                    </div>
                </div>

                <div class="grid gap-[14px] sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[18px] bg-[#f5f9fc] p-[18px]">
                        <div class="mb-1.5 text-[13px] text-[#6f8496]">Total Soal</div>
                        <div class="text-[28px] font-bold leading-none text-[#0d3557]">
                            {{ $summary['total_questions'] ?? 0 }}
                        </div>
                    </div>

                    <div class="rounded-[18px] bg-[#f5f9fc] p-[18px]">
                        <div class="mb-1.5 text-[13px] text-[#6f8496]">Soal Wajib Terjawab</div>
                        <div class="text-[28px] font-bold leading-none text-[#0d3557]">
                            {{ $summary['answered_required_questions'] ?? 0 }}/{{ $summary['required_questions'] ?? 0 }}
                        </div>
                    </div>

                    <div class="rounded-[18px] bg-[#f5f9fc] p-[18px]">
                        <div class="mb-1.5 text-[13px] text-[#6f8496]">Persentase Terisi</div>
                        <div class="text-[28px] font-bold leading-none text-[#0d3557]">
                            {{ $completionPercentage }}%
                        </div>
                    </div>

                    <div class="rounded-[18px] bg-[#f5f9fc] p-[18px]">
                        <div class="mb-1.5 text-[13px] text-[#6f8496]">Durasi Pengerjaan</div>
                        <div class="text-[28px] font-bold leading-none text-[#0d3557]">
                            {{ $durationMinutes }}m
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <div class="h-full rounded-[24px] bg-white p-6 shadow-[0_18px_48px_rgba(14,63,101,0.10)]">
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
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <div class="h-full rounded-[24px] bg-white p-6 shadow-[0_18px_48px_rgba(14,63,101,0.10)]">
                        <h4 class="mb-3 text-lg font-bold text-slate-900">
                            Informasi Pengiriman
                        </h4>

                        <div class="border-t border-[#ebf1f6] py-[18px] first:border-t-0 first:pt-0">
                            <div class="mb-1 text-sm text-slate-500">Status</div>
                            <div class="font-bold text-slate-900">{{ $meta['label'] }}</div>
                        </div>

                        <div class="border-t border-[#ebf1f6] py-[18px]">
                            <div class="mb-1 text-sm text-slate-500">Periode Penugasan</div>
                            <div class="font-bold text-slate-900">{{ $meta['date_text'] }}</div>
                        </div>

                        <div class="border-t border-[#ebf1f6] py-[18px]">
                            <div class="mb-1 text-sm text-slate-500">Jumlah Assessment</div>
                            <div class="font-bold text-slate-900">
                                {{ $meta['assessment_total'] }} assessment / {{ $meta['form_total'] }} form
                            </div>
                        </div>

                        <div class="border-t border-[#ebf1f6] pt-[18px]">
                            <div class="mb-1 text-sm text-slate-500">Catatan</div>
                            <div class="text-slate-500">{{ $meta['description'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($snapshot['assessments'] ?? [] as $assessment)
                <div class="mt-7">
                    <div class="mb-[18px] rounded-[22px] bg-white px-6 py-6 shadow-[0_14px_36px_rgba(15,59,95,0.08)] sm:px-[26px]">
                        <div class="mb-2 text-sm uppercase text-slate-500">
                            {{ $assessment['kode_assessment'] }}
                        </div>
                        <h3 class="mb-1.5 text-xl font-bold text-[#0d3557]">
                            {{ $assessment['judul'] }}
                        </h3>
                        <p class="leading-relaxed text-slate-500">
                            {{ $assessment['deskripsi'] ?: 'Ringkasan jawaban untuk assessment ini ditampilkan di bawah.' }}
                        </p>
                    </div>

                    @foreach ($assessment['forms'] ?? [] as $form)
                        <div class="mb-[22px] rounded-[24px] bg-white p-6 shadow-[0_18px_48px_rgba(14,63,101,0.10)] sm:p-7">
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
                                    $payloadType = data_get($answer, 'payload.type');
                                    $checkboxValues = data_get($answer, 'payload.values', []);
                                @endphp

                                <div class="mb-4 rounded-[18px] border border-[#e6eef5] bg-[#fbfdff] p-5 last:mb-0">
                                    <div
                                        class="mb-3 inline-flex items-center gap-2 rounded-full bg-[#eaf5fb] px-[14px] py-2 text-[13px] font-bold text-[#0d5f98]">
                                        <i class="fas fa-check"></i>
                                        Soal {{ $questionNumber }}
                                    </div>

                                    <div class="mb-2 text-xl font-bold leading-[1.5] text-[#0d3557]">
                                        {{ $field['label'] }}
                                    </div>

                                    @if (!empty($field['deskripsi']))
                                        <div class="mb-3 leading-[1.8] text-[#6d8092]">
                                            {{ $field['deskripsi'] }}
                                        </div>
                                    @endif

                                    @if ($answer)
                                        <div class="break-words rounded-2xl bg-[#eef6fb] px-[18px] py-4 leading-[1.8] text-[#0f3557]">
                                            @if ($payloadType === 'checkbox')
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($checkboxValues as $value)
                                                        <span
                                                            class="inline-flex rounded-full bg-[#dff0fb] px-3 py-2 font-semibold text-[#0c5a90]">
                                                            {{ $value }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @elseif ($payloadType === 'file' && data_get($answer, 'file_url'))
                                                <a
                                                    href="{{ data_get($answer, 'file_url') }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="inline-flex min-h-[42px] items-center justify-center rounded-xl border border-[#1376bd] px-4 py-2 font-semibold text-[#1376bd] transition hover:bg-[#1376bd] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#1376bd]/20"
                                                >
                                                    <i class="fas fa-paperclip mr-2"></i>
                                                    {{ data_get($answer, 'payload.original_name', 'Lihat lampiran') }}
                                                </a>
                                            @else
                                                {{ $answer['text'] }}
                                            @endif
                                        </div>
                                    @else
                                        <div class="rounded-2xl bg-slate-100 px-[18px] py-4 leading-[1.8] text-[#6b7d8f]">
                                            Pertanyaan ini tidak diisi.
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>
@endsection
