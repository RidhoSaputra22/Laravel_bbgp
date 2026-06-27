@extends('assesment.layouts.app')

@section('content')
    <section
        class="bg-center bg-cover pt-[72px] pb-[52px] text-white lg:pt-24 lg:pb-[72px]"
        style="background-image: linear-gradient(135deg, rgba(19, 118, 189, 0.95), rgba(8, 58, 97, 0.95)), url('{{ asset('landing/images/slider-main/bg1.jpg') }}');"
    >
        <div class="container mx-auto px-4">
            <div class="grid items-center gap-8 lg:grid-cols-12 lg:gap-10">
                <div class="lg:col-span-6">
                    <div class="mb-8 lg:mb-0 lg:pr-6">
                        <span
                            class="mb-6 inline-flex items-center gap-2.5 rounded-full bg-white/[0.14] px-[18px] py-2.5 text-sm font-semibold text-white sm:text-base">
                            <i class="fas fa-layer-group"></i>
                            Portal Assessment BBGTK Sulawesi Selatan
                        </span>

                        <h1 class="mb-[18px] text-[34px] font-bold leading-[1.15] text-white lg:text-[44px]">
                            Masuk ke portal assessment untuk mulai ujian sesuai penugasan Anda.
                        </h1>

                        <p class="text-[17px] leading-[1.8] text-white/[0.88]">
                            Portal ini disiapkan untuk peserta eksternal yang sudah mendapatkan penugasan assessment dari
                            admin. Login menggunakan NIK, password akun, dan peran peserta yang sesuai.
                        </p>

                        <ul class="mt-8 space-y-4">
                            <li class="flex items-start gap-[14px] text-white/[0.92]">
                                <i
                                    class="fas fa-random mt-0.5 inline-flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-full bg-white/[0.12]">
                                </i>

                                <div class="leading-relaxed">
                                    Urutan soal ditampilkan acak per peserta agar pengerjaan lebih terjaga.
                                </div>
                            </li>

                            <li class="flex items-start gap-[14px] text-white/[0.92]">
                                <i
                                    class="fas fa-clipboard-check mt-0.5 inline-flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-full bg-white/[0.12]">
                                </i>

                                <div class="leading-relaxed">
                                    Setelah selesai, hasil pengisian dapat dibuka kembali melalui portal yang sama.
                                </div>
                            </li>

                            <li class="flex items-start gap-[14px] text-white/[0.92]">
                                <i
                                    class="fas fa-shield-alt mt-0.5 inline-flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-full bg-white/[0.12]">
                                </i>

                                <div class="leading-relaxed">
                                    Tampilan mengikuti landing page sistem tanpa menambah library baru di project.
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-5 lg:col-start-8">
                    <div class="overflow-hidden rounded-[28px] bg-white shadow-[0_24px_70px_rgba(2,35,64,0.25)]">
                        <div class="p-[34px]">
                            <div class="mb-2.5 text-[28px] font-bold text-[#0b3557]">
                                Login Assessment
                            </div>

                            <div class="mb-6 leading-[1.7] text-[#6a7c8f]">
                                Masukkan data akun yang aktif untuk melihat daftar assessment yang ditugaskan kepada Anda.
                            </div>

                            @if (session('assessment_portal_notice'))
                                <div
                                    class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    {{ session('assessment_portal_notice') }}
                                </div>
                            @endif

                            @if (session('assessment_portal_success'))
                                <div
                                    class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                                    {{ session('assessment_portal_success') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                    <ul class="list-disc space-y-1 pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('assesment.login') }}" class="space-y-5">
                                @csrf

                                <div class="space-y-2">
                                    <label for="assessment-nik" class="block text-sm font-medium text-slate-700">
                                        NIK
                                    </label>

                                    <input
                                        id="assessment-nik"
                                        type="text"
                                        name="nik"
                                        value="{{ old('nik') }}"
                                        placeholder="Masukkan NIK"
                                        required
                                        class="min-h-[52px] w-full rounded-[14px] border border-[#d8e3ee] bg-white px-4 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15 @error('nik') border-red-500 focus:border-red-500 focus:ring-red-500/15 @enderror"
                                    >
                                </div>

                                <div class="space-y-2">
                                    <label for="assessment-password" class="block text-sm font-medium text-slate-700">
                                        Password
                                    </label>

                                    <input
                                        id="assessment-password"
                                        type="password"
                                        name="password"
                                        placeholder="Masukkan password akun"
                                        required
                                        class="min-h-[52px] w-full rounded-[14px] border border-[#d8e3ee] bg-white px-4 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/15 @enderror"
                                    >
                                </div>

                                <div class="space-y-2">
                                    <label for="assessment-role" class="block text-sm font-medium text-slate-700">
                                        Peran Peserta
                                    </label>

                                    <select
                                        id="assessment-role"
                                        name="role"
                                        required
                                        class="min-h-[52px] w-full rounded-[14px] border border-[#d8e3ee] bg-white px-4 text-slate-800 outline-none transition focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15 @error('role') border-red-500 focus:border-red-500 focus:ring-red-500/15 @enderror"
                                    >
                                        <option value="">Pilih peran peserta</option>

                                        @foreach ($roleOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('role') === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button
                                    type="submit"
                                    class="flex min-h-[52px] w-full items-center justify-center rounded-[14px] border border-[#1376bd] bg-[#1376bd] px-5 text-sm font-bold tracking-[0.2px] text-white transition hover:bg-[#0f619c] focus:outline-none focus:ring-4 focus:ring-[#1376bd]/25"
                                >
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    Masuk ke Portal
                                </button>
                            </form>
                        </div>

                        <div
                            class="flex flex-wrap justify-between gap-x-4 gap-y-2 bg-[#f5f9fc] px-[34px] pb-7 pt-[18px] text-sm text-[#61778a]">
                            <span>Gunakan akun peserta yang sama dengan sistem BBGTK.</span>

                            <a
                                href="{{ route('user.index') }}"
                                class="font-semibold text-[#1376bd] transition hover:text-[#0f619c]"
                            >
                                Kembali ke beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="relative z-10 -mt-[26px]">
        <div class="container mx-auto px-4">
            <div class="rounded-[22px] bg-white px-7 py-[26px] shadow-[0_18px_48px_rgba(12,53,87,0.14)]">
                <div class="grid items-center gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <h4 class="mb-2 text-xl font-semibold text-[#0b3557]">
                            Belum bisa login?
                        </h4>

                        <p class="leading-relaxed text-[#607489]">
                            Pastikan NIK, password, dan peran peserta sesuai dengan akun yang didaftarkan. Jika belum
                            mendapatkan penugasan assessment, silakan hubungi admin BBGTK terlebih dahulu.
                        </p>
                    </div>

                    <div class="lg:col-span-4 lg:text-right">
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-[#1376bd] px-5 py-2.5 font-semibold text-[#1376bd] transition hover:bg-[#1376bd] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#1376bd]/20"
                        >
                            Login Umum Sistem
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
