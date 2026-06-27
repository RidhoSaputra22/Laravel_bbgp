@extends('assessment.layouts.app')

@section('content')
    @php
        $snapshot = $attempt->structure_snapshot ?? [];
        $totalQuestions = (int) data_get($snapshot, 'meta.total_questions', 0);
        $requiredQuestions = (int) data_get($snapshot, 'meta.required_questions', 0);
        $questionNumber = 0;
    @endphp

    <section class="bg-gradient-to-b from-[#edf6fb] to-white py-[52px] lg:py-16 lg:pb-[76px]">
        <div class="container mx-auto px-4">
            @if ($errors->has('portal'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first('portal') }}
                </div>
            @endif

            @if ($totalQuestions === 0)
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Assessment ini belum memiliki soal aktif yang bisa dikerjakan.
                </div>
            @endif

            <div
                class="mb-[30px] rounded-[30px] bg-gradient-to-br from-[#0c5a90] to-[#0f3557] p-[26px] text-white shadow-[0_24px_60px_rgba(11,53,87,0.24)] lg:p-8">
                <div class="grid items-center gap-6 lg:grid-cols-12 lg:gap-8">
                    <div class="lg:col-span-8">
                        <div
                            class="mb-[18px] inline-flex items-center gap-2.5 rounded-full bg-white/[0.14] px-4 py-2.5 text-sm font-semibold sm:text-base">
                            <i class="fas fa-random"></i>
                            Urutan soal sudah diacak khusus untuk attempt Anda
                        </div>

                        <h1 class="mb-2.5 text-[28px] font-bold leading-tight text-white lg:text-[32px]">
                            {{ $target->assignment->judul_penugasan }}
                        </h1>

                        <p class="leading-[1.8] text-white/[0.84]">
                            {{ $target->assignment->deskripsi ?: 'Kerjakan seluruh pertanyaan dengan teliti lalu kirim jawaban setelah selesai.' }}
                        </p>
                    </div>

                    <div class="lg:col-span-4 lg:text-right">
                        <a
                            href="{{ route('assessment.dashboard') }}"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-bold text-[#0c5a90] transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-white/30"
                        >
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <div class="mb-4 grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <div class="h-full rounded-[24px] bg-white p-6 shadow-[0_18px_48px_rgba(14,63,101,0.10)]">
                        <h4 class="mb-3 text-lg font-bold text-slate-900">Informasi Ujian</h4>

                        <div class="grid gap-[14px] sm:grid-cols-2">
                            <div class="rounded-[18px] bg-[#f4f9fc] p-[18px]">
                                <div class="mb-1.5 text-[13px] text-[#6f8496]">Peserta</div>
                                <div class="text-[22px] font-bold leading-tight text-[#0d3557]">
                                    {{ $guru->nama_lengkap }}
                                </div>
                            </div>

                            <div class="rounded-[18px] bg-[#f4f9fc] p-[18px]">
                                <div class="mb-1.5 text-[13px] text-[#6f8496]">Sesi</div>
                                <div class="text-[22px] font-bold leading-tight text-[#0d3557]">
                                    {{ $meta['session_label'] }}
                                </div>
                            </div>

                            <div class="rounded-[18px] bg-[#f4f9fc] p-[18px]">
                                <div class="mb-1.5 text-[13px] text-[#6f8496]">Total Soal</div>
                                <div class="text-[28px] font-bold leading-none text-[#0d3557]">
                                    {{ $totalQuestions }}
                                </div>
                            </div>

                            <div class="rounded-[18px] bg-[#f4f9fc] p-[18px]">
                                <div class="mb-1.5 text-[13px] text-[#6f8496]">Soal Wajib</div>
                                <div class="text-[28px] font-bold leading-none text-[#0d3557]">
                                    {{ $requiredQuestions }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="h-full rounded-[24px] bg-white p-6 shadow-[0_18px_48px_rgba(14,63,101,0.10)]">
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
                    </div>
                </div>
            </div>

            <form
                id="assessment-exam-form"
                action="{{ route('assessment.submit', $target->id) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                @foreach ($snapshot['assessments'] ?? [] as $assessment)
                    <div class="mt-[30px]">
                        <div class="mb-[18px] rounded-[22px] bg-white px-6 py-6 shadow-[0_14px_36px_rgba(15,59,95,0.08)] sm:px-[26px]">
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
                                <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-relaxed text-sky-800">
                                    <strong>Petunjuk:</strong> {{ $assessment['petunjuk'] }}
                                </div>
                            @endif
                        </div>

                        @foreach ($assessment['forms'] ?? [] as $form)
                            <div class="mb-[22px] rounded-[24px] bg-white p-6 shadow-[0_18px_50px_rgba(14,63,101,0.10)] sm:p-7">
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
                                        class="mb-[18px] rounded-[20px] border p-[22px] last:mb-0 {{ $fieldError ? 'border-red-500/50 bg-red-50/50' : 'border-[#e4edf4] bg-[#fbfdff]' }}"
                                    >
                                        <div
                                            class="mb-[14px] inline-flex items-center gap-2 rounded-full bg-[#eaf5fb] px-[14px] py-2 text-[13px] font-bold text-[#0d5f98]">
                                            <i class="fas fa-question-circle"></i>
                                            Soal {{ $questionNumber }}
                                        </div>

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
                                                <textarea
                                                    name="answers[{{ $field['id'] }}]"
                                                    rows="5"
                                                    placeholder="{{ $field['placeholder'] ?: 'Tuliskan jawaban Anda' }}"
                                                    class="min-h-[50px] w-full rounded-[14px] border bg-white px-4 py-3 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15 {{ $fieldError ? 'border-red-500 focus:border-red-500 focus:ring-red-500/15' : 'border-[#d7e3ee]' }}"
                                                >{{ old('answers.'.$field['id']) }}</textarea>
                                            @break

                                            @case('select')
                                                <select
                                                    name="answers[{{ $field['id'] }}]"
                                                    class="min-h-[50px] w-full rounded-[14px] border bg-white px-4 text-slate-800 outline-none transition focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15 {{ $fieldError ? 'border-red-500 focus:border-red-500 focus:ring-red-500/15' : 'border-[#d7e3ee]' }}"
                                                >
                                                    <option value="">Pilih jawaban</option>
                                                    @foreach ($field['opsi_field'] ?? [] as $option)
                                                        <option
                                                            value="{{ $option['value'] }}"
                                                            @selected((string) $oldValue === (string) $option['value'])
                                                        >
                                                            {{ $option['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @break

                                            @case('radio')
                                                <div class="space-y-3">
                                                    @foreach ($field['opsi_field'] ?? [] as $optionIndex => $option)
                                                        <label
                                                            for="field-{{ $field['id'] }}-{{ $optionIndex }}"
                                                            class="block cursor-pointer rounded-2xl border border-[#dce8f1] bg-white px-4 py-[14px] transition hover:border-[#1376bd]/60"
                                                        >
                                                            <span class="flex items-start gap-3">
                                                                <input
                                                                    id="field-{{ $field['id'] }}-{{ $optionIndex }}"
                                                                    type="radio"
                                                                    name="answers[{{ $field['id'] }}]"
                                                                    value="{{ $option['value'] }}"
                                                                    @checked((string) $oldValue === (string) $option['value'])
                                                                    class="mt-1 h-4 w-4 border-slate-300 text-[#1376bd] focus:ring-[#1376bd]/30"
                                                                >

                                                                <span class="block">
                                                                    <strong class="block text-slate-800">
                                                                        {{ $option['label'] }}
                                                                    </strong>
                                                                    <span class="mt-0.5 block text-sm text-slate-500">
                                                                        {{ $option['value'] }}
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @break

                                            @case('checkbox')
                                                <div class="space-y-3">
                                                    @foreach ($field['opsi_field'] ?? [] as $optionIndex => $option)
                                                        <label
                                                            for="field-{{ $field['id'] }}-{{ $optionIndex }}"
                                                            class="block cursor-pointer rounded-2xl border border-[#dce8f1] bg-white px-4 py-[14px] transition hover:border-[#1376bd]/60"
                                                        >
                                                            <span class="flex items-start gap-3">
                                                                <input
                                                                    id="field-{{ $field['id'] }}-{{ $optionIndex }}"
                                                                    type="checkbox"
                                                                    name="answers[{{ $field['id'] }}][]"
                                                                    value="{{ $option['value'] }}"
                                                                    @checked(in_array((string) $option['value'], $checkboxValues, true))
                                                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-[#1376bd] focus:ring-[#1376bd]/30"
                                                                >

                                                                <span class="text-slate-800">
                                                                    {{ $option['label'] }}
                                                                </span>
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @break

                                            @case('file')
                                                <input
                                                    type="file"
                                                    name="answers[{{ $field['id'] }}]"
                                                    class="block w-full cursor-pointer rounded-[14px] border bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-[#eaf5fb] file:px-3 file:py-2 file:font-semibold file:text-[#0d5f98] hover:file:bg-[#dff0fb] {{ $fieldError ? 'border-red-500' : 'border-[#d7e3ee]' }}"
                                                >
                                            @break

                                            @default
                                                <input
                                                    type="{{ $field['tipe_field'] === 'number' ? 'number' : ($field['tipe_field'] === 'date' ? 'date' : ($field['tipe_field'] === 'email' ? 'email' : 'text')) }}"
                                                    name="answers[{{ $field['id'] }}]"
                                                    value="{{ old('answers.'.$field['id']) }}"
                                                    placeholder="{{ $field['placeholder'] ?: 'Masukkan jawaban Anda' }}"
                                                    class="min-h-[50px] w-full rounded-[14px] border bg-white px-4 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15 {{ $fieldError ? 'border-red-500 focus:border-red-500 focus:ring-red-500/15' : 'border-[#d7e3ee]' }}"
                                                >
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
                            </div>
                        @endforeach
                    </div>
                @endforeach

                @if ($totalQuestions > 0)
                    <div class="mt-2 rounded-[24px] bg-white p-6 shadow-[0_18px_48px_rgba(14,63,101,0.10)]">
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
                                <button
                                    type="submit"
                                    class="inline-flex min-h-[54px] w-full items-center justify-center rounded-2xl bg-[#1376bd] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#0f619c] focus:outline-none focus:ring-4 focus:ring-[#1376bd]/25"
                                >
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Kirim Jawaban Assessment
                                </button>
                            </div>
                        </div>
                    </div>
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
