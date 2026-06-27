@extends('assessment.layouts.app')

@section('content')
    @php
        $assignedCount = $dashboardCards->count();
        $activeCount = $dashboardCards
            ->filter(fn($item) => in_array($item['meta']['status'], ['ready', 'in_progress'], true))
            ->count();
        $submittedCount = $dashboardCards->filter(fn($item) => $item['meta']['status'] === 'submitted')->count();
    @endphp

    <div>
        <div class="py-4 px-5 bg-[#1376BD] flex justify-between text-white">
            <div class="">
                <h1 class="text-xl font-medium">
                    Dashboard Assessment Peserta
                </h1>
                <p class="text-xs font-ligth">
                    Lihat semua penugasan assessment yang sedang aktif untuk Anda, lanjutkan ujian yang sudah dimulai,
                    atau buka kembali hasil yang sudah dikirim.
                </p>
            </div>
            <div class="text-right text-xs">
                <div class="font-bold">{{ $guru->nama_lengkap }}</div>
                <div class="">NIK: {{ $guru->no_ktp }}</div>
                <div class="">
                    Instansi: {{ $guru->satuan_pendidikan ?: '-' }}
                </div>
            </div>
        </div>
    </div>
    <section class="p-14 grid grid-cols-[2fr_1fr] gap-10">

        <x-assessment::ui.card
            class="overflow-hidden">
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

                <x-assessment::ui.assignment-card :target="$target" :meta="$meta" />
            @empty
                <x-assessment::ui.empty-state icon="far fa-folder-open" title="Belum ada assessment yang ditugaskan"
                    description="Saat admin menambahkan assignment baru untuk akun Anda, daftar assessment akan muncul di halaman ini." />
            @endforelse
        </x-assessment::ui.card>
        <div class="mb-4 grid grid-cols-3 gap-4 ">
            <x-assessment::ui.stat-card label="Total Penugasan" :value="$assignedCount"
                description="Seluruh assignment assessment yang terhubung ke akun Anda." />

            <x-assessment::ui.stat-card label="Siap atau Berjalan" :value="$activeCount"
                description="Assessment yang bisa dimulai sekarang atau sedang Anda kerjakan." />

            <x-assessment::ui.stat-card label="Selesai Dikirim" :value="$submittedCount"
                description="Assessment yang sudah selesai dan hasilnya dapat Anda lihat kembali." />
        </div>

    </section>
@endsection
