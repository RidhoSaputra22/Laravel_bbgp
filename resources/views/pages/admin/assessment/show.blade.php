@extends('layouts.app', ['title' => 'Detail Assesment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Assesment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.edit', $assessment->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ $assessment->judul }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="text-muted">Kode Assesment</div>
                                    <div class="font-weight-bold">{{ $assessment->kode_assessment }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="text-muted">Slug</div>
                                    <div class="font-weight-bold">{{ $assessment->slug }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="text-muted">Status</div>
                                    <div>
                                        <span
                                            class="badge badge-{{ $assessment->status == 'publish' ? 'success' : ($assessment->status == 'draft' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($assessment->status) }}
                                        </span>
                                        @if ($assessment->is_active)
                                            <span class="badge badge-primary">Aktif</span>
                                        @else
                                            <span class="badge badge-light">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted">Deskripsi</div>
                            <div>{{ $assessment->deskripsi ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="text-muted">Petunjuk</div>
                            <div>{{ $assessment->petunjuk ?: '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Child Form dan Form Field</h4>
                    </div>
                    <div class="card-body">
                        @foreach ($assessment->forms as $form)
                            <div class="card border shadow-none">
                                <div class="card-header">
                                    <h4>{{ $form->judul_form }}</h4>
                                    <div class="card-header-action">
                                        <span class="badge badge-primary">{{ $form->kode_form }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if ($form->deskripsi)
                                        <p class="text-muted">{{ $form->deskripsi }}</p>
                                    @endif

                                    <div class="row">
                                        @foreach ($form->fields as $field)
                                            <div class="{{ $field->lebar_kolom ?: 'col-md-6' }}">
                                                <div class="border rounded p-3 mb-3 h-100">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <div class="font-weight-bold">{{ $field->label }}</div>
                                                            <small class="text-muted">{{ $field->nama_field }}</small>
                                                        </div>
                                                        <span class="badge badge-light">{{ $field->tipe_field }}</span>
                                                    </div>

                                                    @if ($field->placeholder)
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">Placeholder</small>
                                                            <span>{{ $field->placeholder }}</span>
                                                        </div>
                                                    @endif

                                                    @if ($field->bantuan)
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">Bantuan</small>
                                                            <span>{{ $field->bantuan }}</span>
                                                        </div>
                                                    @endif

                                                    @if ($field->opsi_field)
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">Opsi</small>
                                                            @foreach ($field->opsi_field as $opsi)
                                                                <span class="badge badge-primary mb-1">{{ $opsi }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    <div class="mt-3">
                                                        @if ($field->is_required)
                                                            <span class="badge badge-success">Wajib</span>
                                                        @else
                                                            <span class="badge badge-secondary">Opsional</span>
                                                        @endif

                                                        @if ($field->is_active)
                                                            <span class="badge badge-primary">Aktif</span>
                                                        @else
                                                            <span class="badge badge-light">Nonaktif</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
