@extends('layouts.app', ['title' => 'Buat Penugasan Assesment'])

@section('content')
    @php
        $oldGuruIds = collect(old('guru_ids', []))
            ->map(fn($id) => (string) $id)
            ->all();
        $selectedAssessmentId = (string) old('assessment_id', '');
        $selectedDurationHours = (int) old('durasi_sesi_jam', $defaultSessionDurationHours);
    @endphp

    @push('styles')
        <style>
            .summary-value {
                font-size: 1.2rem;
                font-weight: 700;
                color: #34395e;
            }

            .select2-container--default .select2-selection--multiple {
                min-height: 44px;
                border-color: #e4e6fc;
            }
        </style>
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Buat Penugasan Assesment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="section-body">
                @if ($errors->has('assignment'))
                    <div class="alert alert-danger">
                        {{ $errors->first('assignment') }}
                    </div>
                @endif

                <form action="{{ route('assessment.assignment.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Informasi Penugasan</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Kode Penugasan</label>
                                                <input type="text" name="kode_penugasan"
                                                    class="form-control @error('kode_penugasan') is-invalid @enderror"
                                                    value="{{ old('kode_penugasan') }}"
                                                    placeholder="Kosongkan untuk generate otomatis">
                                                @error('kode_penugasan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Judul Penugasan <span class="text-danger">*</span></label>
                                                <input type="text" name="judul_penugasan"
                                                    class="form-control @error('judul_penugasan') is-invalid @enderror"
                                                    value="{{ old('judul_penugasan') }}"
                                                    placeholder="Contoh: Penugasan Monitoring Guru GP Angkatan 1">
                                                @error('judul_penugasan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Form Assesment <span class="text-danger">*</span></label>
                                        <select name="assessment_id" id="assessment_id"
                                            class="form-control select2 @error('assessment_id') is-invalid @enderror">
                                            <option value="">-- Pilih Form Assesment --</option>
                                            @foreach ($assessmentList as $assessment)
                                                <option value="{{ $assessment->id }}"
                                                    data-kode="{{ $assessment->kode_assessment }}"
                                                    data-judul="{{ $assessment->judul }}"
                                                    data-status="{{ $assessment->status }}"
                                                    data-forms="{{ $assessment->forms->count() }}"
                                                    data-fields="{{ $assessment->forms->sum(fn($form) => $form->fields->count()) }}"
                                                    @selected($selectedAssessmentId === (string) $assessment->id)>
                                                    {{ $assessment->kode_assessment }} - {{ $assessment->judul }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('assessment_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tanggal Mulai</label>
                                                <input type="date" name="tanggal_mulai"
                                                    class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                                    value="{{ old('tanggal_mulai') }}">
                                                @error('tanggal_mulai')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tanggal Selesai</label>
                                                <input type="date" name="tanggal_selesai"
                                                    class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                                    value="{{ old('tanggal_selesai') }}">
                                                @error('tanggal_selesai')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Durasi Sesi Assessment <span class="text-danger">*</span></label>
                                                <select name="durasi_sesi_jam" id="durasi_sesi_jam"
                                                    class="form-control @error('durasi_sesi_jam') is-invalid @enderror">
                                                    @foreach ($sessionDurationOptions as $durationHour)
                                                        <option value="{{ $durationHour }}"
                                                            @selected($selectedDurationHours === (int) $durationHour)>
                                                            {{ $durationHour }} jam
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('durasi_sesi_jam')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Kapasitas Guru Per Sesi</label>
                                                <input type="text" class="form-control" value="{{ $sessionCapacity }} guru"
                                                    readonly>
                                                <small class="text-muted">
                                                    Sistem otomatis membagi guru per {{ $sessionCapacity }} orang untuk setiap sesi.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Deskripsi Penugasan</label>
                                        <textarea name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror"
                                            placeholder="Catatan, instruksi, atau konteks penugasan untuk admin.">{{ old('deskripsi') }}</textarea>
                                        @error('deskripsi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4>Target Guru</h4>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                        <p class="mb-2 text-muted">
                                            Pilih satu atau banyak guru. Jika jumlah target lebih dari {{ $batchThreshold }}
                                            orang, distribusi otomatis diproses menggunakan batch job.
                                        </p>
                                        <div class="mb-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="select-all-guru">
                                                Pilih Semua
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-guru">
                                                Kosongkan
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group mb-2">
                                        <select name="guru_ids[]" id="guru_ids" multiple
                                            class="form-control select2 @error('guru_ids') is-invalid @enderror @error('guru_ids.*') is-invalid @enderror">
                                            @foreach ($guruList as $guru)
                                                <option value="{{ $guru->id }}" @selected(in_array((string) $guru->id, $oldGuruIds, true))>
                                                    {{ $guru->nama_lengkap }} | {{ $guru->satuan_pendidikan ?: 'Instansi belum diisi' }} |
                                                    {{ $guru->kabupaten ?: 'Kabupaten belum diisi' }}
                                                    @if ($guru->is_verif === 'sudah')
                                                        | Terverifikasi
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('guru_ids')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('guru_ids.*')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <small class="text-muted">
                                        Total guru terpilih:
                                        <span class="font-weight-bold" id="selected-guru-count">{{ count($oldGuruIds) }}</span>
                                    </small>
                                    <div class="mt-2 text-muted">
                                        Estimasi total sesi:
                                        <span class="font-weight-bold" id="estimated-session-count">0</span>
                                        sesi
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card card-body mb-4">
                                <h6 class="text-primary mb-3">Ringkasan Assesment</h6>
                                <div class="mb-3">
                                    <div class="text-muted small">Kode</div>
                                    <div class="summary-value" id="summary-kode">-</div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Judul</div>
                                    <div class="summary-value" id="summary-judul">Pilih assesment terlebih dahulu</div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Status</div>
                                    <div class="summary-value" id="summary-status">-</div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-muted small">Total Form</div>
                                        <div class="summary-value" id="summary-forms">0</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Total Pertanyaan</div>
                                        <div class="summary-value" id="summary-fields">0</div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-muted small">Kapasitas/Sesi</div>
                                        <div class="summary-value" id="summary-session-capacity">{{ $sessionCapacity }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Durasi/Sesi</div>
                                        <div class="summary-value" id="summary-session-duration">{{ $selectedDurationHours }}</div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="text-muted small">Estimasi Total Sesi</div>
                                    <div class="summary-value" id="summary-total-sessions">0</div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4>Aksi</h4>
                                </div>
                                <div class="card-body">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-paper-plane"></i> Simpan Penugasan
                                    </button>
                                    <a href="{{ route('assessment.assignment.index') }}" class="btn btn-light btn-block">
                                        Batal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const $assessmentSelect = $('#assessment_id');
            const $guruSelect = $('#guru_ids');
            const $durationSelect = $('#durasi_sesi_jam');
            const sessionCapacity = {{ $sessionCapacity }};

            $('.select2').select2({
                width: '100%',
            });

            function updateSelectedGuruCount() {
                const selectedGuru = $guruSelect.val() || [];
                $('#selected-guru-count').text(selectedGuru.length);
                updateSessionSummary(selectedGuru.length);
            }

            function updateSessionSummary(totalGuru) {
                const durationHours = Number($durationSelect.val() || {{ $defaultSessionDurationHours }});
                const totalSessions = totalGuru > 0 ? Math.ceil(totalGuru / sessionCapacity) : 0;

                $('#estimated-session-count').text(totalSessions);
                $('#summary-session-capacity').text(sessionCapacity + ' guru');
                $('#summary-session-duration').text(durationHours + ' jam');
                $('#summary-total-sessions').text(totalSessions);
            }

            function updateAssessmentSummary() {
                const selectedOption = $assessmentSelect.find('option:selected');

                if (!selectedOption.val()) {
                    $('#summary-kode').text('-');
                    $('#summary-judul').text('Pilih assesment terlebih dahulu');
                    $('#summary-status').text('-');
                    $('#summary-forms').text('0');
                    $('#summary-fields').text('0');
                    return;
                }

                $('#summary-kode').text(selectedOption.data('kode') || '-');
                $('#summary-judul').text(selectedOption.data('judul') || '-');
                $('#summary-status').text((selectedOption.data('status') || '-').toString());
                $('#summary-forms').text(selectedOption.data('forms') || 0);
                $('#summary-fields').text(selectedOption.data('fields') || 0);
            }

            $('#select-all-guru').on('click', function() {
                const allGuruIds = $guruSelect.find('option').map(function() {
                    return $(this).val();
                }).get();

                $guruSelect.val(allGuruIds).trigger('change');
            });

            $('#clear-guru').on('click', function() {
                $guruSelect.val([]).trigger('change');
            });

            $guruSelect.on('change', updateSelectedGuruCount);
            $assessmentSelect.on('change', updateAssessmentSummary);
            $durationSelect.on('change', function() {
                const selectedGuru = $guruSelect.val() || [];
                updateSessionSummary(selectedGuru.length);
            });

            updateSelectedGuruCount();
            updateAssessmentSummary();
        });
    </script>
@endpush
