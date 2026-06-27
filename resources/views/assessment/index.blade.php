@extends('assessment.layouts.app')

@section('content')
    @php
        $assignedCount = $dashboardCards->count();
        $activeCount = $dashboardCards->filter(
            fn ($item) => in_array($item['meta']['status'], ['ready', 'in_progress'], true),
        )->count();
        $submittedCount = $dashboardCards->filter(
            fn ($item) => $item['meta']['status'] === 'submitted',
        )->count();

        $badgeClasses = [
            'success' => 'bg-[#27ae60]/[0.12] text-[#1f8b4d]',
            'warning' => 'bg-[#f5a623]/[0.14] text-[#b36b00]',
            'primary' => 'bg-[#1376bd]/[0.14] text-[#0d5f98]',
            'secondary' => 'bg-[#6c7a89]/[0.14] text-[#5f6f7f]',
            'danger' => 'bg-[#da4453]/[0.14] text-[#bf3041]',
            'info' => 'bg-[#3498db]/[0.14] text-[#217cb5]',
            'dark' => 'bg-[#343a40]/[0.14] text-[#2f3438]',
        ];
    @endphp

    <section class="bg-gradient-to-b from-[#eef6fb] to-white py-14 lg:py-[72px]">
        <div class="container mx-auto px-4">
            @if (session('assessment_portal_success'))
                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('assessment_portal_success') }}
                </div>
            @endif

            @if ($errors->has('portal'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first('portal') }}
                </div>
            @endif

            <div
                class="mb-7 rounded-[30px] bg-gradient-to-br from-[#0d5f98] to-[#123a60] p-[26px] text-white shadow-[0_28px_70px_rgba(14,58,96,0.24)] lg:p-[34px]">
                <div class="grid items-center gap-6 lg:grid-cols-12 lg:gap-8">
                    <div class="lg:col-span-8">
                        <div
                            class="mb-5 inline-flex items-center gap-2.5 rounded-full bg-white/[0.14] px-[18px] py-3 text-sm font-semibold sm:text-base">
                            <i class="fas fa-user-check"></i>
                            {{ $guru->nama_lengkap }}
                        </div>

                        <h1 class="mb-2 text-[28px] font-bold leading-tight text-white lg:text-[34px]">
                            Dashboard Assessment Peserta
                        </h1>

                        <p class="leading-[1.8] text-white/[0.84]">
                            Lihat semua penugasan assessment yang sedang aktif untuk Anda, lanjutkan ujian yang sudah
                            dimulai, atau buka kembali hasil yang sudah dikirim.
                        </p>
                    </div>

                    <div class="lg:col-span-4">
                        <div class="rounded-xl bg-white p-4 text-slate-900 lg:p-5">
                            <div class="mb-2 text-sm text-slate-500">Informasi Peserta</div>
                            <div class="mb-1 font-bold">{{ $guru->nama_lengkap }}</div>
                            <div class="mb-1 text-sm text-slate-500">NIK: {{ $guru->no_ktp }}</div>
                            <div class="mb-1 text-sm text-slate-500">
                                Instansi: {{ $guru->satuan_pendidikan ?: '-' }}
                            </div>
                            <div class="mb-3 text-sm text-slate-500">
                                Kabupaten/Kota: {{ $guru->kabupaten ?: '-' }}
                            </div>

                            <form action="{{ route('assessment.logout') }}" method="POST">
                                @csrf

                                <button
                                    type="submit"
                                    class="flex min-h-[44px] w-full items-center justify-center rounded-xl border border-[#1376bd] px-4 py-2 text-sm font-semibold text-[#1376bd] transition hover:bg-[#1376bd] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#1376bd]/20"
                                >
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Keluar dari Portal
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 grid gap-4 md:grid-cols-3">
                <div class="h-full rounded-[22px] bg-white p-6 shadow-[0_18px_44px_rgba(17,76,121,0.10)]">
                    <div class="mb-2.5 text-[13px] font-medium uppercase tracking-[0.12em] text-[#6b8194]">
                        Total Penugasan
                    </div>
                    <div class="mb-2 text-4xl font-bold leading-none text-[#0c3556]">
                        {{ $assignedCount }}
                    </div>
                    <p class="text-[#667b8e]">
                        Seluruh assignment assessment yang terhubung ke akun Anda.
                    </p>
                </div>

                <div class="h-full rounded-[22px] bg-white p-6 shadow-[0_18px_44px_rgba(17,76,121,0.10)]">
                    <div class="mb-2.5 text-[13px] font-medium uppercase tracking-[0.12em] text-[#6b8194]">
                        Siap atau Berjalan
                    </div>
                    <div class="mb-2 text-4xl font-bold leading-none text-[#0c3556]">
                        {{ $activeCount }}
                    </div>
                    <p class="text-[#667b8e]">
                        Assessment yang bisa dimulai sekarang atau sedang Anda kerjakan.
                    </p>
                </div>

                <div class="h-full rounded-[22px] bg-white p-6 shadow-[0_18px_44px_rgba(17,76,121,0.10)]">
                    <div class="mb-2.5 text-[13px] font-medium uppercase tracking-[0.12em] text-[#6b8194]">
                        Selesai Dikirim
                    </div>
                    <div class="mb-2 text-4xl font-bold leading-none text-[#0c3556]">
                        {{ $submittedCount }}
                    </div>
                    <p class="text-[#667b8e]">
                        Assessment yang sudah selesai dan hasilnya dapat Anda lihat kembali.
                    </p>
                </div>
            </div>

            <div class="overflow-hidden rounded-[24px] bg-white shadow-[0_18px_54px_rgba(13,67,108,0.10)]">
                <div class="px-6 pb-0 pt-7 sm:px-[30px]">
                    <h3 class="mb-1.5 text-xl font-bold text-[#0c3556]">
                        Daftar Penugasan Assessment
                    </h3>
                    <p class="text-[#6d8092]">
                        Pilih penugasan yang ingin Anda kerjakan. Status dan akses akan menyesuaikan tanggal serta progres
                        pengerjaan.
                    </p>
                </div>

                @forelse ($dashboardCards as $item)
                    @php
                        $target = $item['target'];
                        $meta = $item['meta'];
                    @endphp

                    <div class="border-t border-[#eaf0f5] p-6 sm:p-[30px]">
                        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                            <div class="lg:pr-4">
                                <div class="mb-2 font-bold tracking-[0.04em] text-[#0d5f98]">
                                    {{ $target->assignment->kode_penugasan }}
                                </div>

                                <div class="mb-2 text-2xl font-bold leading-[1.3] text-[#0c3556] lg:text-[26px]">
                                    {{ $target->assignment->judul_penugasan }}
                                </div>

                                <div class="mb-[18px] leading-[1.8] text-[#687d90]">
                                    {{ $target->assignment->deskripsi ?: 'Penugasan ini belum memiliki deskripsi tambahan.' }}
                                </div>
                            </div>

                            <div class="shrink-0">
                                <span
                                    class="inline-flex items-center rounded-full px-[14px] py-2 text-[13px] font-bold {{ $badgeClasses[$meta['badge']] ?? $badgeClasses['secondary'] }}">
                                    {{ $meta['label'] }}
                                </span>
                            </div>
                        </div>

                        <div class="my-4 flex flex-wrap gap-x-[18px] gap-y-2.5 text-sm text-[#6a7e90]">
                            <span class="inline-flex items-center gap-2">
                                <i class="far fa-calendar-alt"></i>
                                {{ $meta['date_text'] }}
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <i class="fas fa-layer-group"></i>
                                {{ $meta['assessment_total'] }} assessment
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <i class="far fa-copy"></i>
                                {{ $meta['form_total'] }} form aktif
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <i class="fas fa-random"></i>
                                {{ $meta['question_total'] }} soal
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <i class="far fa-clock"></i>
                                {{ $meta['session_label'] }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-slate-500">
                                {{ $meta['description'] }}
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if ($meta['status'] === 'submitted')
                                    <a
                                        href="{{ route('assessment.result', $target->id) }}"
                                        class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-[#1376bd] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#0f619c] focus:outline-none focus:ring-4 focus:ring-[#1376bd]/25"
                                    >
                                        <i class="fas fa-poll mr-2"></i>
                                        Lihat Hasil
                                    </a>
                                @elseif (in_array($meta['status'], ['ready', 'in_progress'], true))
                                    <a
                                        href="{{ route('assessment.show', $target->id) }}"
                                        class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-[#1376bd] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#0f619c] focus:outline-none focus:ring-4 focus:ring-[#1376bd]/25"
                                    >
                                        <i class="fas fa-play-circle mr-2"></i>
                                        {{ $meta['status'] === 'in_progress' ? 'Lanjutkan Ujian' : 'Mulai Ujian' }}
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        disabled
                                        class="inline-flex min-h-[44px] cursor-not-allowed items-center justify-center rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400"
                                    >
                                        <i class="fas fa-lock mr-2"></i>
                                        Tidak Tersedia
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-[30px] pb-12 pt-11 text-center text-[#6d8092]">
                        <div
                            class="mb-[18px] inline-flex h-[78px] w-[78px] items-center justify-center rounded-full bg-[#eef6fb] text-[28px] text-[#1376bd]">
                            <i class="far fa-folder-open"></i>
                        </div>
                        <h4 class="mb-2 text-xl font-bold text-slate-900">
                            Belum ada assessment yang ditugaskan
                        </h4>
                        <p>
                            Saat admin menambahkan assignment baru untuk akun Anda, daftar assessment akan muncul di halaman
                            ini.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
