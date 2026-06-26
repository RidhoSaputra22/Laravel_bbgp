@extends('layouts.app', ['title' => 'Penugasan Assesment'])

@section('content')
    @php
        $totalTargets = $datas->sum('total_target');
        $completedAssignments = $datas->where('status_distribusi', 'selesai')->count();
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Penugasan Assesment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Penugasan
                    </a>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Penugasan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $datas->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Penugasan Selesai</h4>
                                </div>
                                <div class="card-body">
                                    {{ $completedAssignments }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Guru Ditugasi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalTargets }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Penugasan Form Assesment</h4>
                    </div>
                    <div class="card-body">
                        @if ($datas->isEmpty())
                            <div class="empty-state" data-height="320">
                                <div class="empty-state-icon bg-primary">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <h2>Belum ada penugasan assesment</h2>
                                <p class="lead">
                                    Buat penugasan baru untuk mendistribusikan form assesment ke guru yang dipilih.
                                </p>
                                <a href="{{ route('assessment.assignment.create') }}" class="btn btn-primary mt-3">
                                    Buat Penugasan
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Kode</th>
                                            <th>Penugasan</th>
                                            <th>Assesment</th>
                                            <th>Periode</th>
                                            <th>Distribusi</th>
                                            <th>Update</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datas as $data)
                                            @php
                                                $statusBadge = [
                                                    'draft' => 'secondary',
                                                    'diproses' => 'warning',
                                                    'selesai' => 'success',
                                                    'gagal' => 'danger',
                                                ][$data->status_distribusi] ?? 'secondary';
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->kode_penugasan }}</div>
                                                    @if ($data->job_batch_id)
                                                        <small class="text-muted">Batch: {{ $data->job_batch_id }}</small>
                                                    @else
                                                        <small class="text-muted">Distribusi langsung</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->judul_penugasan }}</div>
                                                    <small class="text-muted">
                                                        {{ \Illuminate\Support\Str::limit($data->deskripsi, 80) ?: 'Tanpa deskripsi tambahan.' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->assessment->judul }}</div>
                                                    <small class="text-muted">{{ $data->assessment->kode_assessment }}</small>
                                                </td>
                                                <td>
                                                    <div>
                                                        {{ $data->tanggal_mulai ? \App\Helpers\Helper::dateIndo($data->tanggal_mulai) : '-' }}
                                                    </div>
                                                    <small class="text-muted">
                                                        s/d
                                                        {{ $data->tanggal_selesai ? \App\Helpers\Helper::dateIndo($data->tanggal_selesai) : '-' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $statusBadge }}">
                                                        {{ ucfirst($data->status_distribusi) }}
                                                    </span>
                                                    <div class="text-muted mt-1">
                                                        {{ $data->total_ditugaskan }}/{{ $data->total_target }} guru
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>{{ \App\Helpers\Helper::dateIndo($data->updated_at) }}</div>
                                                    <small class="text-muted">
                                                        {{ optional($data->creator)->name ?: 'Sistem' }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('assessment.assignment.show', $data->id) }}"
                                                        class="btn btn-info btn-sm my-1">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
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
