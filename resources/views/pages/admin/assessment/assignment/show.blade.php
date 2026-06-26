@extends('layouts.app', ['title' => 'Detail Penugasan Assesment'])

@section('content')
    @php
        $statusBadge = [
            'draft' => 'secondary',
            'diproses' => 'warning',
            'selesai' => 'success',
            'gagal' => 'danger',
        ][$assignment->status_distribusi] ?? 'secondary';

        $assessment = $assignment->assessment;
        $totalForms = $assessment->forms->count();
        $totalFields = $assessment->forms->sum(fn($form) => $form->fields->count());
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Penugasan Assesment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('assessment.assignment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Penugasan Baru
                    </a>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Target</h4>
                                </div>
                                <div class="card-body">
                                    {{ $assignment->total_target }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Sudah Ditugaskan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $assignment->total_ditugaskan }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Sesi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $assignment->total_sesi }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Durasi Per Sesi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $assignment->durasi_sesi_jam }} jam
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Penugasan</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="text-muted small">Kode Penugasan</div>
                                    <div class="font-weight-bold">{{ $assignment->kode_penugasan }}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Judul</div>
                                    <div class="font-weight-bold">{{ $assignment->judul_penugasan }}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Status Distribusi</div>
                                    <div>
                                        <span class="badge badge-{{ $statusBadge }}">
                                            {{ ucfirst($assignment->status_distribusi) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Periode</div>
                                    <div>
                                        {{ $assignment->tanggal_mulai ? \App\Helpers\Helper::dateIndo($assignment->tanggal_mulai) : '-' }}
                                        s/d
                                        {{ $assignment->tanggal_selesai ? \App\Helpers\Helper::dateIndo($assignment->tanggal_selesai) : '-' }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Dibuat Oleh</div>
                                    <div>{{ optional($assignment->creator)->name ?: 'Sistem' }}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Pengaturan Sesi</div>
                                    <div>{{ $assignment->total_sesi }} sesi</div>
                                    <small class="text-muted">
                                        {{ $assignment->kapasitas_per_sesi }} guru per sesi / {{ $assignment->durasi_sesi_jam }} jam per sesi
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Batch ID</div>
                                    <div>{{ $assignment->job_batch_id ?: 'Distribusi langsung' }}</div>
                                </div>
                                <div class="mb-0">
                                    <div class="text-muted small">Deskripsi</div>
                                    <div>{{ $assignment->deskripsi ?: 'Tidak ada deskripsi tambahan.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h4>Assesment Yang Ditugaskan</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="text-muted small">Kode Assesment</div>
                                    <div class="font-weight-bold">{{ $assessment->kode_assessment }}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Judul</div>
                                    <div class="font-weight-bold">{{ $assessment->judul }}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Status</div>
                                    <div>
                                        <span
                                            class="badge badge-{{ $assessment->status === 'publish' ? 'success' : ($assessment->status === 'draft' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($assessment->status) }}
                                        </span>
                                        <span class="badge badge-{{ $assessment->is_active ? 'primary' : 'light' }}">
                                            {{ $assessment->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <div class="text-muted small">Struktur Assesment</div>
                                    <div class="mb-3">{{ $totalForms }} form / {{ $totalFields }} pertanyaan</div>
                                </div>
                                <div class="mb-0">
                                    <div class="text-muted small">Deskripsi</div>
                                    <div>{{ $assessment->deskripsi ?: 'Tidak ada deskripsi assesment.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Pembagian Sesi Assessment</h4>
                    </div>
                    <div class="card-body">
                        @if ($assignment->sessions->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Sesi assessment belum terbentuk pada penugasan ini.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Label Sesi</th>
                                            <th>Kapasitas</th>
                                            <th>Alokasi Guru</th>
                                            <th>Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assignment->sessions as $session)
                                            <tr>
                                                <td class="text-center">{{ $session->nomor_sesi }}</td>
                                                <td class="font-weight-bold">{{ $session->label_sesi }}</td>
                                                <td>{{ $session->kapasitas_peserta }} guru</td>
                                                <td>{{ $session->total_peserta }} guru</td>
                                                <td>{{ $session->durasi_sesi_jam }} jam</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Guru Yang Ditugasi</h4>
                    </div>
                    <div class="card-body">
                        @if ($assignment->targets->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Penugasan ini masih diproses atau belum memiliki target guru tersimpan.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Nama Guru</th>
                                            <th>Instansi</th>
                                            <th>Kabupaten</th>
                                            <th>Sesi Assessment</th>
                                            <th>Status Target</th>
                                            <th>Waktu Ditugaskan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assignment->targets as $target)
                                            @php
                                                $targetBadge = [
                                                    'ditugaskan' => 'primary',
                                                    'dikerjakan' => 'warning',
                                                    'selesai' => 'success',
                                                    'dibatalkan' => 'secondary',
                                                ][$target->status] ?? 'secondary';
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">
                                                        {{ optional($target->guru)->nama_lengkap ?: 'Guru tidak ditemukan' }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ optional($target->guru)->email ?: '-' }}
                                                    </small>
                                                </td>
                                                <td>{{ optional($target->guru)->satuan_pendidikan ?: '-' }}</td>
                                                <td>{{ optional($target->guru)->kabupaten ?: '-' }}</td>
                                                <td>
                                                    <div class="font-weight-bold">
                                                        {{ optional($target->session)->label_sesi ?: 'Belum dipetakan' }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ optional($target->session)->durasi_sesi_jam ? optional($target->session)->durasi_sesi_jam.' jam' : '-' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $targetBadge }}">
                                                        {{ ucfirst($target->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $target->assigned_at ? $target->assigned_at->format('d M Y H:i') : '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
